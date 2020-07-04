#Easyswoole源码分析-4-start

```

////////////////////////////////////////////////////////////////////
//                          _ooOoo_                               //
//                         o8888888o                              //
//                         88" . "88                              //
//                         (| ^_^ |)                              //
//                         O\  =  /O                              //
//                      ____/`---'\____                           //
//                    .'  \\|     |//  `.                         //
//                   /  \\|||  :  |||//  \                        //
//                  /  _||||| -:- |||||-  \                       //
//                  |   | \\\  -  /// |   |                       //
//                  | \_|  ''\---/''  |   |                       //
//                  \  .-\__  `-`  ___/-. /                       //
//                ___`. .'  /--.--\  `. . ___                     //
//            \  \ `-.   \_ __\ /__ _/   .-` /  /                 //
//      ========`-.____`-.___\_____/___.-`____.-'========         //
//                           `=---='                              //
//      ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^        //
//         佛祖保佑       永无BUG       永不修改                     //
////////////////////////////////////////////////////////////////////
```
### 1.简介
> 启动算是框架的核心部分了，阅读起来有些地方理解起来还是有点困难的，这些部分单独拿出来解析。

### 2.知识点
1.[apc_clear_cache](https://www.php.net/manual/zh/function.apc-clear-cache.php)
2.[opcache_reset](https://www.jianshu.com/p/f089b6d19382)
3.[final](https://www.jb51.net/article/23324.htm)
4.[PHP中Closure类详解
](http://www.php.cn/php-weizijiaocheng-389627.html)



### 3. 核心代码
`start类下的exec方法的核心代码`
```
public function exec(array $args): ?string
{
        // TODO: Implement exec() method.
        //---------------------------------1.清理apc缓存------------------------------
        Utility::opCacheClear();
        //---------------------------------2.展示Logo------------------------------
        $response = Utility::easySwooleLog();
        $mode = 'develop';
        //---------------------------------3.线上|开发配置------------------------------
        if(!Core::getInstance()->isDev()){
            $mode = 'produce';
        }
        $conf = Config::getInstance();
        //---------------------------------4.是否daemon化------------------------------
        if(in_array("d",$args) || in_array("daemonize",$args)){
            $conf->setConf("MAIN_SERVER.SETTING.daemonize", true);
        }
        //---------------------------------5.创建主服务------------------------------
        Core::getInstance()->createServer();
        $serverType = $conf->getConf('MAIN_SERVER.SERVER_TYPE');
        //---------------------------------6.cli展示信息------------------------------
        ·
        · 省略
        ·
        //---------------------------------7.启动------------------------------
        Core::getInstance()->start();
        return null;
}
```

### 3.清理apc缓存
`核心代码`
```
public static function opCacheClear()
{
        if (function_exists('apc_clear_cache')) {
            // https://www.php.net/manual/zh/function.apc-clear-cache.php
            // 1.根据官方手册来看，apc_clear_cache函数如果传递的参数为omitted或者其它字符将会清除系统缓存
            // 2.如果运行在cli则只清除命令行缓存
            // 3.如果http请求则清除的是http缓存
            apc_clear_cache();
        }
        // https://www.jianshu.com/p/f089b6d19382
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
}
```
`展示logo略略略`
`线上|开发配置略略略`
`是否daemon化略略略`
> 额。都略略略了，，，，其实核心部分在创建主服务。。。。
### 4. 创建主服务
`Core::getInstance()->createServer();`
```
function createServer()
{
        $conf = Config::getInstance()->getConf('MAIN_SERVER');
        //---------------------------------1.创建swoole server------------------------------
        ServerManager::getInstance()->createSwooleServer(
            $conf['PORT'],$conf['SERVER_TYPE'],$conf['LISTEN_ADDRESS'],$conf['SETTING'],$conf['RUN_MODEL'],$conf['SOCK_TYPE']
        );
        //---------------------------------2.注册默认的回调事件------------------------------
        $this->registerDefaultCallBack(ServerManager::getInstance()->getSwooleServer(),$conf['SERVER_TYPE']);
        //---------------------------------3.hook mainServerCreate方法------------------------------
        EasySwooleEvent::mainServerCreate(ServerManager::getInstance()->getMainEventRegister());
        //---------------------------------4.注册Console和crontab(这两个以后单独拿出来分析)------------------------------
        $this->extraHandler();
        return $this;
}
```
`创建swoole server`
> 这里面的Dispatcher、Request、Response类后面单独拿出来介绍。写文章的时候会把人绕晕
```
/**
     *  根据type创建不同的swoole服务，详细参数参考swoole手册
     *
     * @param $port
     * @param $type
     * @param string $address
     * @param array $setting
     * @param array ...$args
     * @return bool
     * CreateTime: 2019/5/27 下午3:11
     */
    function createSwooleServer($port,$type ,$address = '0.0.0.0',array $setting = [],...$args):bool
    {
        switch ($type){
            // https://wiki.swoole.com/wiki/page/p-server.html
            case EASYSWOOLE_SERVER:{
                $this->swooleServer = new \swoole_server($address,$port,...$args);
                break;
            }
            // https://wiki.swoole.com/wiki/page/327.html
            case EASYSWOOLE_WEB_SERVER:{
                $this->swooleServer = new \swoole_http_server($address,$port,...$args);
                break;
            }
            // https://wiki.swoole.com/wiki/page/397.html
            case EASYSWOOLE_WEB_SOCKET_SERVER:{
                $this->swooleServer = new \swoole_websocket_server($address,$port,...$args);
                break;
            }
            default:{
                Trigger::getInstance()->error('"unknown server type :{$type}"');
                return false;
            }
        }
        if($this->swooleServer){
            // https://wiki.swoole.com/wiki/page/13.html
            $this->swooleServer->set($setting);
        }
        return true;
    }
```
`注册默认的回调事件`

```php
private function registerDefaultCallBack(\swoole_server $server,int $serverType)
    {
        //---------------------------------1.非swoole_server------------------------------
        if($serverType !== EASYSWOOLE_SERVER){
                     ···
            //---------------------------------1.1具体这里的Dispatcher有啥用还不清楚，等用到的时候再分析------------------------------
            $dispatcher = new Dispatcher($namespace,$depth,$max);
            $dispatcher->setControllerPoolWaitTime($waitTime);
            $httpExceptionHandler = Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER);
            if(!is_callable($httpExceptionHandler)){
                $httpExceptionHandler = function ($throwable,$request,$response){
                    $response->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
                    $response->write(nl2br($throwable->getMessage()."\n".$throwable->getTraceAsString()));
                    Trigger::getInstance()->throwable($throwable);
                };
                Di::getInstance()->set(SysConst::HTTP_EXCEPTION_HANDLER,$httpExceptionHandler);
            }
            //---------------------------------1.2设置异常处理------------------------------
            $dispatcher->setHttpExceptionHandler($httpExceptionHandler);
            //---------------------------------1.3注册回调onRequest方法------------------------------
            EventHelper::on($server,EventRegister::onRequest,function (\swoole_http_request $request,\swoole_http_response $response)use($dispatcher){
                $request_psr = new Request($request);
                $response_psr = new Response($response);
                try{
                    //---------------------------------1.4 hook EasySwooleEvent的onRequest方法------------------------------
                    if(EasySwooleEvent::onRequest($request_psr,$response_psr)){
                        $dispatcher->dispatch($request_psr,$response_psr);
                    }
                }catch (\Throwable $throwable){
                    call_user_func(Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER),$throwable,$request_psr,$response_psr);
                }finally{
                    try{
                        //---------------------------------1.5 hook EasySwooleEvent的afterRequest方法------------------------------
                        EasySwooleEvent::afterRequest($request_psr,$response_psr);
                    }catch (\Throwable $throwable){
                        call_user_func(Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER),$throwable,$request_psr,$response_psr);
                    }
                }
                $response_psr->__response();
            });
        }
        //---------------------------------2 注册onTask方法------------------------------
        EventHelper::on($server,EventRegister::onTask,function (\swoole_server $server, Task $task){
            $finishData = null;
            $taskObj = $task->data;

            //---------------------------------2.1 判断是否为使用快速任务模板------------------------------
            // 可通过继承EasySwoole\EasySwoole\Swoole\Task\QuickTaskInterface,增加run方法,即可实现一个任务模板,通过直接投递类名运行任务:
            if(is_string($taskObj) && class_exists($taskObj)){
                          ···
            }
            //---------------------------------2.2 判断是否为异步任务模板------------------------------
            // 当任务比较复杂，逻辑较多而且固定时，可以预先创建任务模板，并直接投递任务模板，以简化操作和方便在多个不同的地方投递相同的任务，首先需要创建一个任务模板
            if($taskObj instanceof AbstractAsyncTask){
                         ···
            //---------------------------------2.3 SuperClosure是否继承SuperClosure(这里的知识点很重要，也不是很好理解，Closure类)------------------------------
            }else if($taskObj instanceof SuperClosure){
                        ···
            //---------------------------------2.4如果传递的为方法 ------------------------------
            }else if(is_callable($taskObj)){
                        ···
            }
            finish :{
                $task->finish($finishData);
            }
        });
        //---------------------------------3 注册onFinish方法 ------------------------------
        EventHelper::on($server,EventRegister::onFinish,function (\swoole_server $serv, int $task_id,$data){
            return $data;
        });

        //---------------------------------4 注册默认的worker start ------------------------------
        EventHelper::registerWithAdd(ServerManager::getInstance()->getMainEventRegister(),EventRegister::onWorkerStart,function (\swoole_server $server,$workerId){
          ···
        });
    }
```
### 5.cli展示信息
> 额。这里自己去看吧。

### 6. 启动
`        Core::getInstance()->start();
`
```php
function start()
    {
        //给主进程也命名
        $serverName = Config::getInstance()->getConf('SERVER_NAME');
        if(PHP_OS != 'Darwin'){
            cli_set_process_title($serverName);
        }
        //启动
        ServerManager::getInstance()->start();
    }
```

`        ServerManager::getInstance()->start();
`
```php
function start()
    {
        // 将EventHelper::registerWithAdd注册的回调绑定到服务,启动的时候只注册了workerStart
        $events = $this->getMainEventRegister()->all();
        foreach ($events as $event => $callback){
            $this->getSwooleServer()->on($event, function (...$args) use ($callback) {
                foreach ($callback as $item) {
                    call_user_func($item,...$args);
                }
            });
        }
        //  子服务，启动的时候只有CONSOLE(这里单独拿出来讲)
        $this->registerSubPortCallback();
        $this->isStart = true;
        // 启动
        $this->getSwooleServer()->start();
    }
```

### 7.结语
> 主要记录学习，不当地方请指出

### 8.相关连接
[Easyswoole手册](https://www.easyswoole.com/Manual/3.x/Cn/_book/BaseUsage/crontab.html)
[Swoole手册](https://wiki.swoole.com/wiki/page/p-server.html)


