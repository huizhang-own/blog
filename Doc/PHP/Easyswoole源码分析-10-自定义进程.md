#Easyswoole源码分析-10-自定义进程

### 1.简介
> 处理耗时任务，比如处理死循环队列消费，清除多余redis中的token数据等等。

### 2.知识点
> [Server->addProcess](https://wiki.swoole.com/wiki/page/390.html)
[Process::__construct](https://wiki.swoole.com/wiki/page/214.html)
[swoole_event_del](https://wiki.swoole.com/wiki/page/120.html)
[swoole_event_add](https://wiki.swoole.com/wiki/page/119.html)
[Process::signal](https://wiki.swoole.com/wiki/page/362.html)
[Process->exit](https://wiki.swoole.com/wiki/page/218.html)
[Coroutine\Channel->push](https://wiki.swoole.com/wiki/page/843.html)
[Coroutine\Channel->pop](https://wiki.swoole.com/wiki/page/844.html)

### 3.代码剖析
> 实现自定义进程需要关注三个地方1.AbstractProcess。2.自定义一个Process类(Process1)。3.在mainServerCreate方法中添加子进程。

`目录`
>![image.png](https://upload-images.jianshu.io/upload_images/10306662-d0e4b0c6b7c0d543.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

`AbstractProcess核心代码`
```
<?php
abstract class AbstractProcess
{
    ···

    /**
     * AbstractProcess constructor.
     * @param string $processName
     * @param null $arg
     * @param bool $redirectStdinStdout
     * @param int $pipeType
     * @param bool $enableCoroutine
     */
    function __construct(...$args)
    {
        $arg1 = array_shift($args);
        // 参数是否为EasySwoole\Component\Process\Config类型
        if($arg1 instanceof Config){
            $this->config = $arg1;
        }else{
            // 定义EasySwoole\Component\Process\Config类型参数
            $this->config = new Config();
            $this->config->setProcessName($arg1);
            $arg = array_shift($args);
            $this->config->setArg($arg);
            $redirectStdinStdout = (bool)array_shift($args) ?: false;
            $this->config->setRedirectStdinStdout($redirectStdinStdout);
            $pipeType = array_shift($args);
            $pipeType = $pipeType === null ? Config::PIPE_TYPE_SOCK_DGRAM : $pipeType;
            $this->config->setPipeType($pipeType);
            $enableCoroutine = (bool)array_shift($args) ?: false;
            $this->config->setEnableCoroutine($enableCoroutine);
        }
        // 创建子进程https://wiki.swoole.com/wiki/page/214.html
        $this->swooleProcess = new \swoole_process([$this,'__start'],$this->config->isRedirectStdinStdout(),$this->config->getPipeType(),$this->config->isEnableCoroutine());
    }
···
    function __start(Process $process)
    {
        // os如果不为win并且进程名称不为空，则设置进程名称
        if(PHP_OS != 'Darwin' && !empty($this->getProcessName())){
            $process->name($this->getProcessName());
        }
        // 监听SIGTERM信号
        Process::signal(SIGTERM,function ()use($process){
            go(function ()use($process){
                // 删除管道
                swoole_event_del($process->pipe);
                $channel = new Channel(8);
                go(function ()use($channel){
                    try{
                        // 执行自定义进程中的onShutDown方法，并将返回结果push到channel中
                        $channel->push($this->onShutDown());
                    }catch (\Throwable $throwable){
                        // 执行自定义进程中的onException方法
                        $this->onException($throwable);
                    }
                });
                // onShutDown最大执行时间内未结束强制停止
                $channel->pop($this->config->getMaxExitWaitTime());
                // 退出事件轮询，此函数仅在Client程序中有效。
                swoole_event_exit();
                // $callback如果为null，表示移除信号监听
                Process::signal(SIGTERM,null);
                // 退出子进程
                $this->getProcess()->exit(0);
            });
        });
        // 将一个socket加入到底层的reactor事件监听中。此函数可以用在Server或Client模式下。
        swoole_event_add($this->swooleProcess->pipe, function(){
            try{
                // 执行自定义进程中的onPipeReadable方法
                $this->onPipeReadable($this->swooleProcess);
            }catch (\Throwable $throwable){
                // 执行自定义进程中的onException方法
                $this->onException($throwable);
            }
        });
        try{
            // 执行自定义进程中的run方法,并将参数传入
            $this->run($this->config->getArg());
        }catch (\Throwable $throwable){
            $this->onException($throwable);
        }
    }

  ···
}
```

`自定义一个Process类(Process1)`
```
<?php
/**
 * @CreateTime:   2019/6/20 下午3:35
 * @Author:       yuzhao  <tuzisir@163.com>
 * @Copyright:    copyright(2019) yuzhao all rights reserved
 * @Description:
 */
namespace App\Process;
use EasySwoole\Component\Process\AbstractProcess;

class Process1 extends AbstractProcess
{

    protected function run($arg)
    {
        //当进程启动后，会执行的回调
        var_dump($this->getProcessName()." run");
        var_dump($arg);
    }

    protected function onPipeReadable(\Swoole\Process $process)
    {
        /*
         * 该回调可选
         * 当有主进程对子进程发送消息的时候，会触发的回调，触发后，务必使用
         * $process->read()来读取消息
         */
    }

    protected function onShutDown()
    {
        /*
         * 该回调可选
         * 当该进程退出的时候，会执行该回调
         */
    }


    protected function onException(\Throwable $throwable, ...$args)
    {
        /*
         * 该回调可选
         * 当该进程出现异常的时候，会执行该回调
         */
    }

}
```
`在mainServerCreate方法中添加子进程。`
```
<?php
namespace EasySwoole\EasySwoole;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use App\Process\Process1; // 重点1
use EasySwoole\Component\Process\Config; // 重点2
class EasySwooleEvent implements Event
{
    public static function initialize()
    {
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // 自定义子进程配置
        $processConfig = new Config();
        $processConfig->setProcessName('testProcess');
        $processConfig->setArg([
            'arg1'=>time()
        ]);
        // 添加子进程
        ServerManager::getInstance()->getSwooleServer()->addProcess((new Process1($processConfig))->getProcess());
    }

    public static function onRequest(Request $request, Response $response): bool
    {
    }
    public static function afterRequest(Request $request, Response $response): void
    {
    }
}
```