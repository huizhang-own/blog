#Easyswoole源码分析-6-reload

### 1.知识点
1.[主要看里面的SIGUSR1和SIGUSR2信号的作用](https://wiki.swoole.com/wiki/page/20.html)
2.[向指定pid进程发送信号](https://wiki.swoole.com/wiki/page/219.html)




### 2.代码分析
```
public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
        $all = false;
        //---------------------------------1.判断是重启task_worker还是平稳地restart所有Worker进程------------------------------
        if(in_array('all',$args)){
            $all = true;
        }
        //---------------------------------2.这里不知道要干啥------------------------------
        if(in_array('produce',$args)){
            Core::getInstance()->setIsDev(false);
        }
        $Conf = Config::getInstance();
        $res = '';
        //---------------------------------3.获取pidfile------------------------------
        $pidFile = $Conf->getConf("MAIN_SERVER.SETTING.pid_file");
        if (file_exists($pidFile)) {
            //---------------------------------4.判断是重启task_worker还是平稳地restart所有Worker进程------------------------------
            if (!$all) {
                $sig = SIGUSR2;
                $res = $res.Utility::displayItem('reloadType',"only-task")."\n";
            } else {
                $sig = SIGUSR1;
                $res = $res.Utility::displayItem('reloadType',"all-worker")."\n";
            }
            //---------------------------------5.清理cli面板------------------------------
            Utility::opCacheClear();
            $pid = file_get_contents($pidFile);
            //---------------------------------6.判断是否有此进程------------------------------
            if (!\swoole_process::kill($pid, 0)) {
                return "pid :{$pid} not exist ";
            }
            //---------------------------------7.这个方法可以向进程发送信号，不要看到kill理解成杀死------------------------------
            \swoole_process::kill($pid, $sig);
            return $res. "send server reload command at " . date("Y-m-d H:i:s");
        } else {
            return "PID file does not exist, please check whether to run in the daemon mode!";
        }
    }
```