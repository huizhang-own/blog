### 1.简介
> EasySwoole支持用户根据Crontab规则去添加定时器。时间最小粒度是1分钟。

### 2. 代码分析
#### 2-1添加cron
###### 2-1-1 流程
> ![image.png](https://upload-images.jianshu.io/upload_images/10306662-7de9a46e4184329d.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)
###### 2-1-2 上半部分核心代码
`注意:此例请参照es手册`
> 在mainServerCreate 添加cron
```
public static function mainServerCreate(EventRegister $register)
{
        // TODO: Implement mainServerCreate() method.
        /**
         * **************** Crontab任务计划 **********************
         */
        // 开始一个定时任务计划
        Crontab::getInstance()->addTask(TaskOne::class);
        // 开始一个定时任务计划
        Crontab::getInstance()->addTask(TaskTwo::class);
}
```
> 将cron添加到tasks列表中的过程。Crontab.php 中的addTask方法
```
function addTask(string $cronTaskClass): Crontab
{
        try {
            // 获取自定义cron类
            $ref = new \ReflectionClass($cronTaskClass);
            // AbstractCronTask是否为$cronTaskClass的父类
            if ($ref->isSubclassOf(AbstractCronTask::class)) {
                // cron名称
                $taskName = $cronTaskClass::getTaskName();
                // cron规则
                $taskRule = $cronTaskClass::getRule();
                // 验证cron规则
                if (CronExpression::isValidExpression($taskRule)) {
                    // 将cron加到tasks中
                    $this->tasks[$taskName] = $cronTaskClass;
                } else {
                    throw new CronTaskRuleInvalid($taskName, $taskRule);
                }
                return $this;
            } else {
                throw new \InvalidArgumentException("the cron task class {$cronTaskClass} is invalid");
            }
        } catch (\Throwable $throwable) {
            throw new \InvalidArgumentException("the cron task class {$cronTaskClass} is invalid");
        }
}
```
###### 2-1-3 下半部分核心代码
> es启动时会检查是否注册了cron任务，代码在Core.php的extraHandler方法中。
```
function __run()
{
        // 判断是否有crontab注册
        if (!empty($this->tasks)) {
            // 获取主server
            $server = ServerManager::getInstance()->getSwooleServer();
            $name = Config::getInstance()->getConf('SERVER_NAME');
            // 创建cron子进程
            $runner = new CronRunner("{$name}.Crontab", $this->tasks);

            // 将当前任务的初始规则全部添加到swoole table中管理
            TableManager::getInstance()->add(self::$__swooleTableName, [
                'taskRule' => ['type' => Table::TYPE_STRING, 'size' => 35],
                'taskRunTimes' => ['type' => Table::TYPE_INT, 'size' => 4],
                'taskNextRunTime' => ['type' => Table::TYPE_INT, 'size' => 4]
            ], 1024);

            $table = TableManager::getInstance()->get(self::$__swooleTableName);

            // 由于添加时已经确认过任务均是AbstractCronTask的子类 这里不再去确认
            foreach ($this->tasks as $cronTaskName => $cronTaskClass) {
                $taskRule = $cronTaskClass::getRule();
                $nextTime = CronExpression::factory($taskRule)->getNextRunDate()->getTimestamp();
                // 将cron基本信息set到table
                $table->set($cronTaskName, ['taskRule' => $taskRule, 'taskRunTimes' => 0, 'taskNextRunTime' => $nextTime]);
            }
            // 将cron子进程添加到主进程
            $server->addProcess($runner->getProcess());
        }
}
```
#### 2-2 执行cron
> 主任务加入子服务后，所执行的回调为AbstractProcess.php 中的 __start方法，这个方法最终会执行run方法。
```
function __start(Process $process)
{
  ···
        try{
            $this->run($this->config->getArg());
        }catch (\Throwable $throwable){
            $this->onException($throwable);
        }
}
```
###### 2-2-1 流程
> ![image.png](https://upload-images.jianshu.io/upload_images/10306662-35391694fb422f21.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

###### 2-2-2 代码分析
> Cron执行流程核心代码在CronRunner.php 中
```
class CronRunner extends AbstractProcess
{
    protected $tasks;

    public function run($arg)
    {
        $this->tasks = $arg;
        $this->cronProcess();
        // 每29秒执行一次定时器
        Timer::getInstance()->loop(29 * 1000, function () {
            $this->cronProcess();
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
    }

    private function cronProcess()
    {
        // 获取crontab的table配置
        $table = TableManager::getInstance()->get(Crontab::$__swooleTableName);
        // 循环每个crontab
        foreach ($table as $taskName => $task) {
            // cron规则
            $taskRule = $task['taskRule'];
            // 下次cron执行的时间
            $nextRunTime = CronExpression::factory($task['taskRule'])->getNextRunDate();
            // 间隔时间
            $distanceTime = $nextRunTime->getTimestamp() - time();
            // 小于30的时候放入定时器的延时执行
            if ($distanceTime < 30) {
                // 定时器延时执行
                Timer::getInstance()->after($distanceTime * 1000, function () use ($taskName, $taskRule) {
                    $nextRunTime = CronExpression::factory($taskRule)->getNextRunDate();
                    $table = TableManager::getInstance()->get(Crontab::$__swooleTableName);
                    // 执行次数
                    $table->incr($taskName, 'taskRunTimes', 1);
                    // 覆盖下次执行的时间
                    $table->set($taskName, ['taskNextRunTime' => $nextRunTime->getTimestamp()]);
                    // 异步执行任务
                    TaskManager::processAsync($this->tasks[$taskName]);
                });
            }
        }
    }
}
```
