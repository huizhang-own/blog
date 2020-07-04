#Easyswoole源码分析-11-异步任务

### 1.简介
> 在服务启动后的任意一个地方，都可以进行异步任务的投递，为了简化异步任务的投递，框架封装了任务管理器，用于投递同步/异步任务，投递任务有两种方式，一是直接投递闭包，二是投递任务模板类

### 2.知识点
> 1.[异步任务](https://easyswoole.com/Manual/3.x/Cn/_book/BaseUsage/async_task.html?q=)
> 2.[Demo](https://github.com/easy-swoole/demo)

### 3.代码分析
> 注意对比手册中的几种方式，核心代码在Core.php中。
```
EventHelper::on($server,EventRegister::onTask,function (\swoole_server $server, Task $task){
            $finishData = null;
            $taskObj = $task->data;
            if(is_string($taskObj) && class_exists($taskObj)){
                $ref = new \ReflectionClass($taskObj);
                // 判断是否使用使用快速任务模板
                if($ref->implementsInterface(QuickTaskInterface::class)){
                    try{
                        // 执行快速任务模板的run方法
                        $finishData = $taskObj::run($server,$task->id,$task->worker_id,$task->flags);
                    }catch (\Throwable $throwable){
                        Trigger::getInstance()->throwable($throwable);
                    }
                    goto finish;
                    // 是否为投递任务模板类
                }else if($ref->isSubclassOf(AbstractAsyncTask::class)){
                    // 实例化
                    $taskObj = new $taskObj;
                }
            }
            // 是否为投递任务模板类
            if($taskObj instanceof AbstractAsyncTask){
                try{
                    // 执行对应的__onTaskHook和__onFinishHook方法
                    $ret = $taskObj->__onTaskHook($task->id,$task->worker_id,$task->flags);
                    $finishData = $taskObj->__onFinishHook($ret,$task->id);
                }catch (\Throwable $throwable){
                    Trigger::getInstance()->throwable($throwable);
                }
            // 待定
            }else if($taskObj instanceof SuperClosure){
                try{
                    $finishData = $taskObj( $server, $task->id,$task->worker_id,$task->flags);
                }catch (\Throwable $throwable){
                    Trigger::getInstance()->throwable($throwable);
                }
            // 是否为回调函数
            }else if(is_callable($taskObj)){
                try{
                    // 执行回调
                    $finishData =  call_user_func($taskObj,$server,$task->id,$task->worker_id,$task->flags);
                }catch (\Throwable $throwable){
                    Trigger::getInstance()->throwable($throwable);
                }
            }
            finish :{
                //禁止 process执行回调
                if(($server->setting['worker_num'] + $server->setting['task_worker_num']) > $task->worker_id){
                    $task->finish($finishData);
                }
            }
        });
```

