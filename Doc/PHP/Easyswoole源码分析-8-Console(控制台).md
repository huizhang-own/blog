# Easyswoole源码分析-8-Console(控制台)

### 1.简介
> EasySwoole 提供了console控制台组件,在项目运行的时候,可通过命令和服务端进行通讯,查看服务端运行状态,实时推送运行逻辑等

> 知识点
1.[swoole_event_add](https://wiki.swoole.com/wiki/page/119.html)
2.[addListener](https://wiki.swoole.com/wiki/page/16.html)
3.[EasySwoole CONSOLE组件](http://www.easyswoole.com/Manual/3.x/Cn/_book/BaseUsage/Console/Introduction.html)

### 2.流程

> 启动Easyswoole时会启动主服务，根据配置启动其它服务，比如Console和Crontab服务，客户端执行php easyswoole console 会连接console服务，连接成功后发送相应指令服务器执行后返回，客户端输出结果。

>![image.png](https://upload-images.jianshu.io/upload_images/10306662-af66a60e9a9ece02.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)


```
➜  easyswoole php easyswoole console
connect to  tcp://127.0.0.1:9500 success
Welcome to EasySwoole Console
auth root 123456 // 根据配置文件配置的登录信息登录
auth success
server // 命令，下面为返回的结果
进行服务端的管理

用法: 命令 [命令参数]

server status                    | 查看服务当前的状态
server hostIp                    | 显示服务当前的IP地址
server reload                    | 重载服务端
server shutdown                  | 关闭服务端
server clientInfo [fd]           | 查看某个链接的信息
server close [fd]                | 断开某个链接
```
> console服务启动流程。 `这个图最好对着代码看，不然我自己都看不懂。`

> ![image.png](https://upload-images.jianshu.io/upload_images/10306662-33f4e252726ece4c.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)


> Console Client 执行流程,主要完成创建client、连接console服务、发送指令、返回执行结果。

> ![image.png](https://upload-images.jianshu.io/upload_images/10306662-c0de1caa5d3aff14.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

> Console Server receive 数据处理流程

> ![image.png](https://upload-images.jianshu.io/upload_images/10306662-9b1a4f50b97de9b8.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

### 3.代码分析

###### 3.1 启动服务
> Core.php 中的extraHandler为启动console的核新代码
```
private function extraHandler()
{
        $serverName = Config::getInstance()->getConf('SERVER_NAME');
        //注册Console
        if(Config::getInstance()->getConf('CONSOLE.ENABLE')){
            // 获取console配置信息
            $config = Config::getInstance()->getConf('CONSOLE');
            // 添加服务
            ServerManager::getInstance()->addServer('CONSOLE',$config['PORT'],SWOOLE_TCP,$config['LISTEN_ADDRESS']);
            // 赋予服务功能
            Console::getInstance()->attachServer(ServerManager::getInstance()->getSwooleServer('CONSOLE'),new ConsoleConfig());
            // 将创建的服务set给Console
            Console::getInstance()->setServer(ServerManager::getInstance()->getSwooleServer());
            // 注册close方法
            ServerManager::getInstance()->getSwooleServer('CONSOLE')->on('close',function (){
                Auth::$authTable->set(Config::getInstance()->getConf('CONSOLE.USER'),[
                    'fd'=>0
                ]);
            });
            // console对象容器里面注册auth、server、log对象
            ConsoleModuleContainer::getInstance()->set(new Auth());
            ConsoleModuleContainer ::getInstance()->set(new Server());
            ConsoleModuleContainer ::getInstance()->set(new Log());
        }
        //注册crontab进程
        Crontab::getInstance()->__run();
}
```
> 添加服务
```
public function addServer(string $serverName,int $port,int $type = SWOOLE_TCP,string $listenAddress = '0.0.0.0',array $setting = [
        "open_eof_check"=>false,
    ]):EventRegister
{
        ···
        // 增加监听的端口。业务代码中可以通过调用 [Server->getClientInfo](https://wiki.swoole.com/wiki/page/p-connection_info.html) 来获取某个连接来自于哪个端口。
        $subPort = $this->swooleServer->addlistener($listenAddress,$port,$type);
       ···
}
```
>  赋予服务功能，感兴趣的话可以去看看ConsoleProtocolParser这个类
```
public function attachServer($server,Config $config)
{
        $this->config = $config;
        // 是否为swoole_server
        if($server instanceof \swoole_server){
            $this->server = $server;
            $server = $server->addlistener($config->getListenAddress(),$config->getListenPort(),SWOOLE_TCP);
        }
        $server->set(array(
            "open_eof_split" => true, // 启用EOF自动分包
            'package_eof' => "\r\n", // 以\r\n分包
        ));
        // new socket config
        $conf = new DispatcherConfig();
        // 设置解包、打包类
        $conf->setParser(new ConsoleProtocolParser());
        // 设置通信类型
        $conf->setType($conf::TCP);
        // 将socket config 对象给Dispatcher
        $dispatcher = new Dispatcher($conf);
        // 注册receive方法
        $server->on('receive', function (\swoole_server $server, $fd, $reactor_id, $data) use ($dispatcher) {
            $dispatcher->dispatch($server, $data, $fd, $reactor_id);
        });
        // 注册connect方法
        $server->on('connect', function (\swoole_server $server, int $fd, int $reactorId) {
            $hello = 'Welcome to ' . $this->config->getServerName();
            $this->send($fd,$hello);
        });
}
```

###### 3.2 console客户端
> 这一块主要完成的功能是，连接server、接收、发送、返回、输出相应信息。
```
public function exec(array $args): ?string
{
        // TODO: Implement exec() method.
        // 获取console配置信息
        $conf = Config::getInstance()->getConf('CONSOLE');
        // 协程执行
        go(function ()use($conf){
            // 创建client对象
            $client = new Client($conf['LISTEN_ADDRESS'],$conf['PORT']);
            // 连接console服务器
            if($client->connect()){
                echo "connect to  tcp://".$conf['LISTEN_ADDRESS'].":".$conf['PORT']." success \n";
                // 协程接收console服务返回的数据
                go(function ()use($client){
                    while (1){
                        // 接收数据
                        $data = $client->recv(-1);
                        if(!empty($data)){
                            echo $data."\n";
                        }else if($client !== false){
                            exit();
                        }
                    };
                });
                // 将STDIN加入到底层的reactor事件监听中
                swoole_event_add(STDIN,function()use($client){
                    $ret = trim(fgets(STDIN));
                    if(!empty($ret)){
                        // 协程发送指令到console 服务
                        go(function ()use($client,$ret){
                            $client->sendCommand($ret);
                        });
                    }
                });
            }else{
                echo "connect to  tcp://".$conf['LISTEN_ADDRESS'].":".$conf['PORT']." fail \n";
            }
        });
        return null;
}
```
###### 3.3 receive数据
> 这个方法为receive流程的核心代码
```
function dispatch(\swoole_server $server ,string $data, ...$args):void
    {
        $clientIp = null;
        $type = $this->config->getType();
        // switch连接类型
        switch ($type){
            case Config::TCP:{
                $client = new Tcp( ...$args);
                break;
            }
            case Config::WEB_SOCKET:{
                $client = new WebSocket( ...$args);
                break;
            }
            case Config::UDP:{
                $client = new Udp( ...$args);
                break;
            }
            default:{
                throw new \Exception('dispatcher type error : '.$type);
            }
        }
        $caller = null;
        $response = new Response();
        try{
            // decode数据(这里的decode和php中的json_decode 是不同的)
            $caller = $this->config->getParser()->decode($data,$client);
        }catch (\Throwable $throwable){
            //注意，在解包出现异常的时候，则调用异常处理，默认是断开连接，服务端抛出异常
            $this->hookException($server,$throwable,$data,$client,$response);
            goto response;
        }
        //如果成功返回一个调用者，那么执行调用逻辑
        if($caller instanceof Caller){
            // 将$client 对象 set给caller
            $caller->setClient($client);
            // 获取控制器名称(ConsoleTcpController)
            $controllerClass = $caller->getControllerClass();
            try{
                // 获取控制器对象(ConsoleTcpController对象)
                $controller = $this->getController($controllerClass);
            }catch (\Throwable $throwable){
                $this->hookException($server,$throwable,$data,$client,$response);
                goto response;
            }
            // ConsoleTcpController是否继承自Controller
            if($controller instanceof Controller){
                try{
                    // hook ConsoleTcpController 中的方法
                    $controller->__hook( $server,$this->config, $caller, $response);
                }catch (\Throwable $throwable){
                    $this->hookException($server,$throwable,$data,$client,$response);
                }finally {
                    $this->recycleController($controllerClass,$controller);
                }
            }else{
                $throwable = new ControllerPoolEmpty('controller pool empty for '.$controllerClass);
                $this->hookException($server,$throwable,$data,$client,$response);
            }
        }
        // 返回数据
        response :{
            switch ($response->getStatus()){
                case Response::STATUS_OK:{
                    $this->response($server,$client,$response);
                    break;
                }
                case Response::STATUS_RESPONSE_AND_CLOSE:{
                    $this->response($server,$client,$response);
                    $this->close($server,$client);
                    break;
                }
                case Response::STATUS_CLOSE:{
                    $this->close($server,$client);
                    break;
                }
            }
        }
    }
```
> 感觉还是有必要将ConsoleProtocolParser贴一下，因为接收数据decode的时候setControllerClass的类为ConsoleTcpController，这也是为什么我一直在注释中提到的这个方法。
```
class ConsoleProtocolParser implements ParserInterface
{
    public function decode($raw, $client): ?Caller
    {
        // TODO: Implement decode() method.
        $data = trim($raw);
        $arr = explode(" ",$data);
        $caller = new Caller();
        $caller->setAction(array_shift($arr));
        // 设置controller,这里是重点
        $caller->setControllerClass(ConsoleTcpController::class);
        $caller->setArgs($arr);
        return $caller;
    }

    public function encode(Response $response, $client): ?string
    {
        // TODO: Implement encode() method.
        $str = $response->getMessage();
        if(empty($str)){
            $str = 'empty response';
        }
        return $str."\r\n";
    }

}
```


### 4.结语
>这样当自己项目中想搭建这样一套console的时候，可以仿照Easyswoole中的这种方式。

`仅仅记录学习`


