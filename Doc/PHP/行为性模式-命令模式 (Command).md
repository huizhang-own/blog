#行为性模式-命令模式 (Command)

### 1.简介
> 我们想实现的是，只需要输入一个字符串式的指令，就可以执行相应的逻辑，而不用if else什么来判断。
PHP Cli命令的设计就会用到这个模式。

### 2.代码实现
> 我们来实现一个电视机开关的指令：
```
//命令接口
interface Command{
    public function excecute();
}
//开电视指令
class turnOnTVCommand extends Command{
    private $controller;
    public function __construct(Controller $controller){
        $this->controller = $controller;
    }
    public function excecute(){
        $this->controller->turnOnTV();
    }
}
//关电视指令
class turnOffTVCommand extends Command{
    private $controller;
    public function __construct(Controller $controller){
        $this->controller = $controller;
    }
    public function excecute(){
        $this->controller->turnOffTV();
    }
}
//指令库控制器（储存所有具体执行逻辑）
class Controller {
    public function turnOnTV(){
        echo '打开电视';
    }
    public function turnOffTV(){
        echo '关闭电视';
    }
}
```

### 3.使用
```
$command_string = 'turnOnTV'.'Command';
$command = new $command_string(new Controller());
$command->excecute();
```