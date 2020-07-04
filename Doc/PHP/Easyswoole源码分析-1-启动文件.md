# Easyswoole源码分析-1-启动文件

### es 目录结构

![image.png](https://upload-images.jianshu.io/upload_images/10306662-75437caccf944d8e.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

####  启动文件
> 先看easyswoole启动文件,这里没什么东西，主要是定义了几个常量，引入自动加载机制，根据不同命令运行相应hook

```php
#!/usr/bin/env php
<?php

use EasySwoole\EasySwoole\Command\CommandRunner;
// 是否安装phar扩展,这里先不介绍等以后用到的时候再说
defined('IN_PHAR') or define('IN_PHAR', boolval(\Phar::running(false)));
// 定义运行根目录
defined('RUNNING_ROOT') or define('RUNNING_ROOT', realpath(getcwd()));
// es目录，如果有phar扩展则用生成的包的地址，如果没有则使用原始地址
defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', IN_PHAR ? \Phar::running() : realpath(getcwd()));
// 自动加载机制
$file = EASYSWOOLE_ROOT.'/vendor/autoload.php';
if (file_exists($file)) {
    require $file;
}else{
    die("include composer autoload.php fail\n");
}

$args = $argv;
//trim first command
array_shift($args);
// 根据不同命令运行相应hook(钩子)
$ret = CommandRunner::getInstance()->run($args);
if(!empty($ret)){
    echo $ret."\n";
}
```