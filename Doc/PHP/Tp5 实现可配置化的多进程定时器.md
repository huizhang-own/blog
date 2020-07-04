#Tp5 实现可配置化的多进程定时器

> 做毕设过程中，一些功能需要定期同步相关数据，但是linux自带的cron使用起来又不是很不方便，所以简单写了一个基于tp5的可配置化的多进程定时器，也可以在此基础上做成后台可管理的。

`核心代码`

```
<?php
/**
 * User: yuzhao
 * Description: 可配置化的定时器工具: php xx/public/index.php /daemon/start_timer/main [-s]
 */

namespace app\daemon\controller;

use app\common\base\BaseController;
use app\common\config\SelfConfig;
use app\common\tool\ProcessTool;
use app\common\tool\ShellTool;

class StartTimerController extends BaseController {

    private static $taskNextTime=[];

    public function main() {
        global $argv;
        // kill进程
        if (isset($argv[2]) && $argv[2] == '-s') {
            ShellTool::kill($argv[1]);die();
        }
        // 先干掉所有任务，防止重复启动
        ShellTool::kill($argv[1]);;
        // 守护进程
        ProcessTool::daemonStart();
        // 获取自定义配置
        $timerConf = SelfConfig::getConfig('Source.timer');
        $time = time();
        // 时间统一管理
        foreach ($timerConf as $key => $value) {
            if ($value['status'] == 1) {
                self::$taskNextTime[$key] = $time+$value['time'];
            }
        }
        // 不断循环查看有没有到点的任务
        while (true) {
            $nowTime = time();
            foreach (self::$taskNextTime as $key => $value) {
                $nowTimerConf = $timerConf[$key];
                if ($nowTime >= $value) {
                    // 初始化下次需要执行的时间
                    self::$taskNextTime[$key] = $value+$nowTimerConf['time'];
                    $pid = pcntl_fork();
                    if( $pid < 0 ){
                        exit();
                    } else if( 0 == $pid ) { // 子进程负责执行任务
                        $classPath = str_replace('/','\\',"/app/daemon/timer/");
                        $classFunc = explode('@', $nowTimerConf['class_func']);
                        $class = $classPath.$classFunc[0];
                        call_user_func_array(array(new $class,$classFunc[1]),array());
                        exit();
                    } else if( $pid > 0 ) {

                    }
                }
            }
        }
    }
}
```

`自定义配置`
```
'timer' => [
        'up_exam_topic_status' => [
            'time' => 3, // 每隔多长时间执行一次
            'class_func' => 'TimerTest@test', // 类名+方法名
            'status' => 1 // 1为启动状态，其它为停止状态
        ]
    ]
```

`测试文件`
```
<?php
/**
 * User: yuzhao
 * Description:
 */
namespace app\daemon\timer;

class TimerTest {
    public function test() {
        var_dump(123);
    }
}
```

`执行`
```
php xxx/public/index.php /daemon/start_timer/main
```
![image.png](https://upload-images.jianshu.io/upload_images/10306662-0f4bd1dd371fab18.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)
