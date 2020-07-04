#Easyswoole源码分析-7-help

### 介绍
> 所有command类都实现了CommandInterface接口
```
interface CommandInterface
{
    public function commandName():string;
    public function exec(array $args):?string ;
    public function help(array $args):?string ;
}
```

### 分析
> CommandRunner类里面当输入不存在的command时，会默认为help
```
function run(array $args):?string 
{
       ···
        if(!CommandContainer::getInstance()->get($command)){
            $command = 'help';
        }
       ···
}
```

> help类
```
class Help implements CommandInterface
{

    public function commandName(): string
    {
        // TODO: Implement commandName() method.
        return 'help';
    }

    public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
        // 如果没有任何command，则直接调用本类的help方法
        if (!isset($args[0])) {
            return $this->help($args);
        } else {
            $actionName = $args[0];
            array_shift($args);
            // 获取相应命令的对象
            $call = CommandContainer::getInstance()->get($actionName);
            // 是否实现了CommandInterface接口
            if ($call instanceof CommandInterface) {
                // 执行相应command类的help方法
                return $call->help($args);
            } else {
                return "no help message for command {$actionName} was found";
            }
        }
    }

    public function help(array $args): ?string
    {
        // TODO: Implement help() method.
        // 获取所有command
        $allCommand = implode(PHP_EOL, CommandContainer::getInstance()->getCommandList());
        // 展示es log
        $logo = Utility::easySwooleLog();
        return $logo.<<<HELP
Welcome To EASYSWOOLE Command Console!
Usage: php easyswoole [command] [arg]
Get help : php easyswoole help [command]
Current Register Command:
{$allCommand}
HELP;
    }
}
```
