#Easyswoole源码分析-5-stop

### 1.简介
> stop 的过程其实非常简单，主要是停止主服务。
### 2.知识点
[1.swoole中的kill](https://wiki.swoole.com/wiki/page/219.html)
[2.Esayswoole的服务管理](https://www.easyswoole.com/Manual/3.x/Cn/_book/Introduction/server.html?h=stop)
[3.Swoole中的配置选项](https://wiki.swoole.com/wiki/page/274.html)

### 3.代码分析
`核心代码`
```
 public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
        $force = false;
        if(in_array('force',$args)){
            $force = true;
        }
        // 这里说实话并没有搞懂在这里加判断，因为在CommandRunner里面已经进行了相应配置加载
        if(in_array('produce',$args)){
            Core::getInstance()->setIsDev(false);
        }
        //---------------------------------1.获取pid_file路径------------------------------
        $Conf = Config::getInstance();
        $pidFile = $Conf->getConf("MAIN_SERVER.SETTING.pid_file");
        //---------------------------------2.检测文件是否存在------------------------------
        if (file_exists($pidFile)) {
            //---------------------------------3.获取pid并杀死------------------------------
            $pid = intval(file_get_contents($pidFile));
            //---------------------------------4.检测进程是否存在，不会发送信号------------------------------
            if (!\swoole_process::kill($pid, 0)) {
                return "PID :{$pid} not exist ";
            }
            //---------------------------------5.判断以什么方式杀死进程（看上知识点）------------------------------
            if ($force) {
                \swoole_process::kill($pid, SIGKILL);
            } else {
                \swoole_process::kill($pid);
            }
            //---------------------------------6.最长检测15秒查看服务是否已经被干掉------------------------------
            $time = time();
            while (true) {
                //-----7.当时比较好奇为什么usleep不放到下面的else下，最后猜想应该是防止第五步杀死服务后，下面的代码立马检测会得到错误的结果------------------------------
                usleep(1000);
                //---------------------------------8.判断服务是否已经被干掉------------------------------
                if (!\swoole_process::kill($pid, 0)) {
                    if (is_file($pidFile)) {
                        unlink($pidFile);
                    }
                    return "server stop at " . date("Y-m-d H:i:s") ;
                    break;
                } else {
                    if (time() - $time > 15) {
                        return "stop server fail.try -f again ";
                        break;
                    }
                }
            }
            return 'stop server fail';
        } else {
            return "PID file does not exist, please check whether to run in the daemon mode!";
        }
    }
```

### 4.相关链接
[Easyswoole手册](https://www.easyswoole.com/Manual/3.x/Cn/_book/BaseUsage/crontab.html)
[Swoole手册](https://wiki.swoole.com/wiki/page/p-server.html)