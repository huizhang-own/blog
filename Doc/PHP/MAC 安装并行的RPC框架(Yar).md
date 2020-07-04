# MAC 安装并行的RPC框架(Yar) 

##1.简介`鸟哥语录`
```java
传统的Web应用, 一个进程, 一个请求, 天经地义. 然而, 当一个请求的处理中, 涉及到多出数据源, 并且他们之间具有一定的不依赖性.
还是传统的Web应用, 一个应用随着业务快速增长, 开发人员的流转, 就会慢慢的进入一个恶性循环, 代码量上只有加法没有了减法. 因为随着系统变复杂, 牵一发就会动全局, 而新来的维护者, 对原有的体系并没有那么多时间给他让他全面掌握. 即使有这么多时间, 要想掌握以前那么多的维护者的思维的结合, 也不是一件容易的事情…
那么, 长次以往, 这个系统将会越来越不可维护…. 到一个大型应用进入这个恶性循环, 那么等待他的只有重构了.
那么, 能不能对这个系统做解耦呢?
我们已经做了很多解耦了, 数据, 中间件, 业务, 逻辑, 等等, 各种分层. 但到Web应用这块, 还能怎么分呢, MVC我们已经做过了….
基于此, Yar或许能解决你遇到的这俩个问题…
```
##2.安装
`yar依赖msgpack扩展，下面是提供的两种方式`
```
1.pecl install msgpack
2.brew install msgpack 

注意把msgpack放到php.ini中
```
```
1.yum install git
2.git clone https://github.com/laruence/yar.git
3.然后 进入yar 目录
4.开始编译安装
        4.1 phpize
        4.2./configure --with-php-config=/usr/bin/php-config (如果不知道php-config 在什么目录，则执行命令 which php-config)
        4.3 make && make install
5.php.ini 中放入extension=yar
6.重启apache
```
##3.安装结果
![image.png](https://upload-images.jianshu.io/upload_images/10306662-50e1b913fad9c246.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)
##4.如何使用
######1.文件目录
![image.png](https://upload-images.jianshu.io/upload_images/10306662-f419ad06388a4d82.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)
######1.服务端代码
```
<?php
/**
 * User: 郭玉朝
 * CreateTime: 2018/7/26 下午6:05
 * Description:
 */
class YarServer {
    public function support($uid,$feedId){
        return "uid = ".$uid.", feedId = ".$feedId;
    }

    public function support1($uid,$feedId){
        return "uid = ".$uid.", feedId = ".$feedId;
    }

    public function support2($uid,$feedId){
        return "uid = ".$uid.", feedId = ".$feedId;
    }
}

$yar_server = new Yar_server(new YarServer());
$yar_server->handle();
```
######2.客户端代码
```
<?php

class YarClient {
    // RPC 服务地址映射表
    public static $rpcConfig = array(
        "YarServer"    => "http://127.0.0.1/test/yar-test/YarServer.php",
    );

    public static function init($server){
        if (array_key_exists($server, self::$rpcConfig)) {
            $uri = self::$rpcConfig[$server];
            return new Yar_Client($uri);
        }
    }
}

$RewardScoreService = YarClient::init("YarServer");
/**@var $RewardScoreService YarServer */
var_dump($RewardScoreService->support(1, 2));
```

######3.启动服务端
![image.png](https://upload-images.jianshu.io/upload_images/10306662-0f0524d53a1b73a3.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

######4.客户端调用
![image.png](https://upload-images.jianshu.io/upload_images/10306662-28bc2a42a9ea9af1.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)


#5.相关学习资源
[鸟哥博客](http://www.laruence.com/2012/09/15/2779.html)
[https://lvtao.net/yaf/yar.html](https://lvtao.net/yaf/yar.html)
