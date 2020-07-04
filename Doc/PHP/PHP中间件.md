#PHP中间件

#1.什么是中间件
> 中间件是一个闭包，而且返回一个闭包。中间件为过滤进入应用的HTTP请求提供了一套便利的机制，可以分为前置中间件和后置中间件。常用于验证用户是否经
过认证，添加响应头（跨域），记录请求日志等。
![image.png](https://upload-images.jianshu.io/upload_images/10306662-431cfade2605abe0.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)
#2.代码示例

```
<?php
// 框架核心应用层
$application = function($name) {
    echo "this is a {$name} application\n";
};

// 前置校验中间件
$auth = function($handler) {
    return function($name) use ($handler) {
        echo "{$name} need a auth middleware\n";
        return $handler($name);
    };
};

// 前置过滤中间件
$filter = function($handler) {
    return function($name) use ($handler) {
        echo "{$name} need a filter middleware\n";
        return $handler($name);
    };
};

// 后置日志中间件
$log = function($handler) {
    return function($name) use ($handler) {
        $return = $handler($name);
        echo "{$name} need a log middleware\n";
        return $return;
    };
};

// 中间件栈
$stack = [];

// 打包
function pack_middleware($handler, $stack)
{
    foreach (array_reverse($stack) as $key => $middleware)
    {
        $handler = $middleware($handler);
    }
    return $handler;
}

// 注册中间件
// 这里用的都是全局中间件，实际应用时还可以为指定路由注册局部中间件
$stack['log'] = $log;
$stack['filter'] = $filter;
$stack['auth'] = $auth;

$run = pack_middleware($application, $stack);
$run('tuzisir');

```

#3.打包程序
> 中间件的执行顺序是由打包函数(pack_middleware)决定，这里返回的闭包实际上相当于:

```
$run = $log($filter($auth($application)));
$run('tuzisir');
```
#4.编写规范
> 中间件要要满足一定的规范：总是返回一个闭包，闭包中总是传入相同的参数（由主要逻辑决定）， 闭包总是返回句柄(handler)的执行结果；
如果中间件的逻辑在返回句柄return $handler($name)前完成，就是前置中间件，否则为后置中间件。
