#Easyswoole源码分析-3-Install

![image.png](https://upload-images.jianshu.io/upload_images/10306662-f214ba39a697a21c.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)


### 1.简介
> 下载Easyswoole框架后，需要执行`php vendor/bin/easyswoole install`,接下来介绍一些执行这个命令后具体执行了哪些操作。
![image.png](https://upload-images.jianshu.io/upload_images/10306662-d22c9f56b59d0004.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

### 2. 先看CommandRunner类的run方法

`核心代码`
```
function run(array $args):?string
    {
        ·
        ·
        ·
        // 如果执行命令为install
        }else if($command != 'install'){
            //预先加载配置(dev为线上配置，produce为开发配置)
            if(in_array('produce',$args)){
                Core::getInstance()->setIsDev(false);
            }
            // 主要完成的操作1
            Core::getInstance()->initialize();
        }
        ·
        ·
        ·
        // 主要完成的操作2(请先阅读前几章)
        return CommandContainer::getInstance()->hook($command,$args);
    }
```

> 1.根据install去执行相应操作。
2.根据是否有produce参数来决定是否使用开发|线上配置。
3.执行install类的exec方法。

`接下来具体来分析一下代码注释中的操作1和操作2`

### 3.Core::getInstance()->initialize();
`知识点`
> 1.  [ReflectionClass (反射机制)](https://www.jianshu.com/p/d02cbde1cdd7)
> 2.  [类名|接口名|组合名::class ](https://segmentfault.com/q/1010000009987451)
> 3. [php7的异常和错误的改变](https://segmentfault.com/a/1190000009504337)
> 4. [php spl 扩展的ArrayObject](https://blog.csdn.net/qq_34908844/article/details/79216563)
> 5.[php预定义常量](https://php.net/manual/zh/errorfunc.constants.php)
> 6.[设计模式:适配器模式](http://larabase.com/collection/5/post/152)






`核心代码`
```
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
        //先加载配置文件
        $this->loadEnv();
        //执行了业务处理类的initialize方法
        EasySwooleEvent::initialize();
        //临时文件和Log目录初始化
        $this->sysDirectoryInit();
        //注册错误回调
        $this->registerErrorHandler();
        return $this;
}
```
`接口是否实现`
```
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
      // 如果没有EasySwooleEvent.php 直接die
      die('global event file missing');
}
```
`loadEnv方法`
```
public function loadEnv()
    {
        //加载之前，先清空原来的
        if($this->isDev){
            $file  = EASYSWOOLE_ROOT.'/dev.php';
        }else{
            $file  = EASYSWOOLE_ROOT.'/produce.php';
        }
        Config::getInstance()->loadEnv($file);
    }
```
`Config的loadEnv方法`
```
/**
* $this->conf = new SplArray(); 这里有时间会单独剖析一下一峰大佬写的
* SplArray工具类，这个类继承了spl扩展的ArrayObject，看下上面的知识点应该就能看懂。
*
**/
public function loadEnv(string $file)
{
        if(file_exists($file)){
            $data = require $file;
            // 将ArrayObject对象给conf
            $this->conf->loadArray($data);
        }else{
            throw new \Exception("config file : {$file} is miss");
        }
}
```


`$this->sysDirectoryInit();`
> 我再注释当中解释了过程
```
private function sysDirectoryInit():void
    {
        //--------------------------------1.创建temp目录-------------------------------
        //创建临时目录    请以绝对路径，不然守护模式运行会有问题
        $tempDir = Config::getInstance()->getConf('TEMP_DIR');
        // 是否具有此配置项
        if(empty($tempDir)){
            // 没有配置则默认路径
            $tempDir = EASYSWOOLE_ROOT.'/Temp';
            // 设置默认路径
            Config::getInstance()->setConf('TEMP_DIR',$tempDir);
        }else{
            // 如果最后一个字符为/则删除
            $tempDir = rtrim($tempDir,'/');
        }
        if(!is_dir($tempDir)){
            // 创建目录
            mkdir($tempDir);
        }
        defined('EASYSWOOLE_TEMP_DIR') or define('EASYSWOOLE_TEMP_DIR',$tempDir);
        //---------------------------------2.创建Log目录------------------------------
        $logDir = Config::getInstance()->getConf('LOG_DIR');
        if(empty($logDir)){
            $logDir = EASYSWOOLE_ROOT.'/Log';
            Config::getInstance()->setConf('LOG_DIR',$logDir);
        }else{
            $logDir = rtrim($logDir,'/');
        }
        if(!is_dir($logDir)){
            mkdir($logDir);
        }
        defined('EASYSWOOLE_LOG_DIR') or define('EASYSWOOLE_LOG_DIR',$logDir);

        //设置默认文件目录值
        Config::getInstance()->setConf('MAIN_SERVER.SETTING.pid_file',$tempDir.'/pid.pid');
        Config::getInstance()->setConf('MAIN_SERVER.SETTING.log_file',$logDir.'/swoole.log');
    }
```
`$this->registerErrorHandler();`
> 具体的你们追下去看吧，写的真的不错，尤其是set_error_handler和register_shutdown_function
```
private function registerErrorHandler()
    {
        // 显示错误
        ini_set("display_errors", "On");
        // E_STRICT出外的所有错误和警告信息。E_STRICT：启用 PHP 对代码的修改建议，以确保代码具有最佳的互操作性和向前兼容性。
        error_reporting(E_ALL | E_STRICT);
        //---------------------------------1.初始化配置Logger------------------------------
        //初始化配置Logger，install指令的时候这里获取不到logger对象，因此执行new \EasySwoole\Trace\Logger(EASYSWOOLE_LOG_DIR);
        $logger = Di::getInstance()->get(SysConst::LOGGER_HANDLER);
        if(!$logger instanceof LoggerInterface){
            $logger = new \EasySwoole\Trace\Logger(EASYSWOOLE_LOG_DIR);
        }
        // 这里的Logger和\EasySwoole\Trace\Logger不是一个
        Logger::getInstance($logger);
        //---------------------------------2.初始化追踪器------------------------------
        //初始化追踪器
        $trigger = Di::getInstance()->get(SysConst::TRIGGER_HANDLER);
        if(!$trigger instanceof TriggerInterface){
            $display = Config::getInstance()->getConf('DISPLAY_ERROR');
            $trigger = new \EasySwoole\Trace\Trigger(Logger::getInstance(),$display);
        }
        // 这里的Trigger和\EasySwoole\Trace\Trigger 不是一个
        Trigger::getInstance($trigger);
        //---------------------------------3.set_error_handler------------------------------
        //在没有配置自定义错误处理器的情况下，转化为trigger处理
        $errorHandler = Di::getInstance()->get(SysConst::ERROR_HANDLER);
        if(!is_callable($errorHandler)){
            $errorHandler = function($errorCode, $description, $file = null, $line = null){
                $l = new Location();
                $l->setFile($file);
                $l->setLine($line);
                Trigger::getInstance()->error($description,$errorCode,$l);
            };
        }
        set_error_handler($errorHandler);
        //---------------------------------3.register_shutdown_function------------------------------
        $func = Di::getInstance()->get(SysConst::SHUTDOWN_FUNCTION);
        if(!is_callable($func)){
            $func = function (){
                $error = error_get_last();
                if(!empty($error)){
                    $l = new Location();
                    $l->setFile($error['file']);
                    $l->setLine($error['line']);
                    Trigger::getInstance()->error($error['message'],$error['type'],$l);
                }
            };
        }
        register_shutdown_function($func);
    }
```

### 3. Install类的exec方法
>         file_put_contents(EASYSWOOLE_ROOT . '/easyswoole',file_get_contents(__DIR__.'/../../../bin/easyswoole'));
> 这里还不知道要干啥，后面用到的时候再说。

`核心代码`
```
public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
        // 输出logo
        echo Utility::easySwooleLog();
        // 更新easyswoole文件
        if(is_file(EASYSWOOLE_ROOT . '/easyswoole')){
            unlink(EASYSWOOLE_ROOT . '/easyswoole');
        }
        // 将easyswoole内容拷贝到__DIR__.'/../../../bin/easyswoole'中,这里我还不知道目的是什么，下面用到后再解释
        file_put_contents(EASYSWOOLE_ROOT . '/easyswoole',file_get_contents(__DIR__.'/../../../bin/easyswoole'));
        // 将下面的文件copy到/../../Resource/下
        Utility::releaseResource(__DIR__ . '/../../Resource/EasySwooleEvent.tpl', EASYSWOOLE_ROOT . '/EasySwooleEvent.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Config.tpl', EASYSWOOLE_ROOT . '/dev.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Config.tpl', EASYSWOOLE_ROOT . '/produce.php');
        echo "install success,enjoy! \n";
        return null;
    }
```

`easySwooleLog 方法`
> 虽然没啥逻辑，就当宣传一下easyswoole吧
```
public static function easySwooleLog()
    {
        return <<<LOGO
  ______                          _____                              _
 |  ____|                        / ____|                            | |
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |
                         |___/

LOGO;
    }
```

### 4.相关链接

[Easyswoole手册](https://www.easyswoole.com/Manual/3.x/Cn/_book/Introduction/install.html)
