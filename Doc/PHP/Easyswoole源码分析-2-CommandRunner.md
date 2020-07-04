#Easyswoole源码分析-2-CommandRunner

### 1. 分析CommandRunner类
> 分析CommandRunner，主要关注Singleton 组合、CommandContainer、Core类
### 2. 知识点
[1.new static()和new self()的区别](https://www.cnblogs.com/shizqiang/p/6277091.html)
[2.get_called_class()函数与get_class()函数的区别](https://www.cnblogs.com/liuwanqiu/p/6736863.html)
[3.Trait详解及其应用](https://blog.csdn.net/qq_34908844/article/details/78851583)
[4.Hook](https://www.jianshu.com/p/0718871b98dd)

### 3.Singleton组合

`核心代码`

```php
trait Singleton
{
    private static $instance;

    static function getInstance(...$args)
    {
        if(!isset(self::$instance)){
            self::$instance = new static(...$args);
        }
        return self::$instance;
    }
}
```
> 根据上面代码可以当执行easyswoole入口文件`$ret = CommandRunner::getInstance()->run($args);`的时候、首先CommandRunner::getINstance() 获取CommandRunner 对象，进而执行此对象的run方法。

### 4.CommandContainer 类
`核心代码`
```php
class CommandContainer
{
    // 每个类都引入了此Trait，目的是为了获取当前对象
    use Singleton;
    // 用于存储注入的对象们
    private $container = [];

    // 注入对象
    public function set(CommandInterface $command)
    {
        $this->container[strtolower($command->commandName())] = $command;
    }
    
    // 获取对象
    function get($key): ?CommandInterface
    {
        $key = strtolower($key);
        if (isset($this->container[$key])) {
            return $this->container[$key];
        } else {
            return null;
        }
    }
    // 获取对象列表
    function getCommandList()
    {
        return array_keys($this->container);
    }
    // 执行某一command对象中的exec方法
    function hook($commandName, array $args):?string
    {
        $handler = $this->get($commandName);
        if($handler){
            return $handler->exec($args);
        }
        return null;
    }
}
```

>  通过此类可以将command相关的对象注入到container变量中

### 5.Core (核心)类
`核心代码`
```php
class Core
{
    use Singleton;

    private $isDev = true;

    // 初始化一些常量
    function __construct()
    {
        defined('SWOOLE_VERSION') or define('SWOOLE_VERSION',intval(phpversion('swoole')));
        defined('IN_PHAR') or define('IN_PHAR', boolval(\Phar::running(false)));
        defined('RUNNING_ROOT') or define('RUNNING_ROOT', realpath(getcwd()));
        defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', IN_PHAR ? \Phar::running() : realpath(getcwd()));
        defined('EASYSWOOLE_SERVER') or define('EASYSWOOLE_SERVER',1);
        defined('EASYSWOOLE_WEB_SERVER') or define('EASYSWOOLE_WEB_SERVER',2);
        defined('EASYSWOOLE_WEB_SOCKET_SERVER') or define('EASYSWOOLE_WEB_SOCKET_SERVER',3);
    }
    // 设置是否加载dev配置文件
    function setIsDev(bool $isDev)
    {
        $this->isDev = $isDev;
        return $this;
    }


    function initialize()
    {
        //检查全局文件是否存在.
        $file = EASYSWOOLE_ROOT . '/EasySwooleEvent.php';
        if(file_exists($file)){
            require_once $file;
            try{
                $ref = new \ReflectionClass('EasySwoole\EasySwoole\EasySwooleEvent');
                if(!$ref->implementsInterface(Event::class)){
                    die('global file for EasySwooleEvent is not compatible for EasySwoole\EasySwoole\EasySwooleEvent');
                }
                unset($ref);
            }catch (\Throwable $throwable){
                die($throwable->getMessage());
            }
        }else{
            die('global event file missing');
        }
        //先加载配置文件dev和produce两种
        $this->loadEnv();
        //执行框架初始化事件、主要初始化了时区
        EasySwooleEvent::initialize();
        //临时文件和Log目录初始化
        $this->sysDirectoryInit();
        //注册错误回调
        $this->registerErrorHandler();
        return $this;
    }
}
```
 > 此类目前在CommandRunner类中主要作用就是初始化一些必要信息

### 6.CommandRunner类
> 了解了以上信息后，接下来介绍CommandRunner
`核心代码`
```php
class CommandRunner
{
    use Singleton;

    // 注入处理相应命令的对象
    function __construct()
    {
        CommandContainer::getInstance()->set(new Help());
        CommandContainer::getInstance()->set(new Install());
        CommandContainer::getInstance()->set(new Start());
        CommandContainer::getInstance()->set(new Stop());
        CommandContainer::getInstance()->set(new Reload());
        CommandContainer::getInstance()->set(new Console());
        CommandContainer::getInstance()->set(new Phar());
    }

    // 根据启动命令执行相应的对象方法
    function run(array $args):?string
    {
        $command = array_shift($args);
        if(empty($command)){
            $command = 'help';
        }else if($command != 'install'){
            //预先加载配置
            if(in_array('produce',$args)){
                Core::getInstance()->setIsDev(false);
            }
            Core::getInstance()->initialize();
        }
        if(!CommandContainer::getInstance()->get($command)){
            $command = 'help';
        }
        return CommandContainer::getInstance()->hook($command,$args);
    }
}
```
> 此类完成的功能主要有1.注入Help、Install、Start、Stop、Reload、Console、Phar 对象 2.根据cli 指令信息看执行哪个对象中的exec方法、处理不同的业务。

`后面会逐个分析Help、Install、Start、Stop、Reload、Console、Phar 类`
### 7.相关地址
[EasySwoole文档地址](http://www.easyswoole.com/)
