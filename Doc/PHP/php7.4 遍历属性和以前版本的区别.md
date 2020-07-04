#php7.4 遍历属性和以前版本的区别

### 1. 简介
>  终于腾出一点时间来解决近段时间遗留的问题，前几天有位同学在Easyswoole下提了一个[issue](https://github.com/easy-swoole/easyswoole/issues/262)。因此做了几个case 用来验证

```
PHP 7.4.0 (cli) (built: Dec  6 2019 23:00:14) ( NTS )
Copyright (c) The PHP Group
Zend Engine v3.4.0, Copyright (c) Zend Technologies
```
### 2. Case
> 不约束类型,不赋初值
```
class Test
{
    public $name;
}

$obj = new Test();

$format = "【%s】==>【%s】";
foreach ($obj as $key => $value)
{
    $res = sprintf($format, $key, $value);
    var_dump($res);
}
```
`结果`
```
string(19) "【name】==>【】"
```
> 不约束类型,赋初值
```
class Test
{
    public $name='zhangsan';
}

$obj = new Test();

$format = "【%s】==>【%s】";
foreach ($obj as $key => $value)
{
    $res = sprintf($format, $key, $value);
    var_dump($res);
}

```
`结果`
```
string(27) "【name】==>【zhangsan】"
```
> 约束类型,不赋初值
```
class Test
{
    public string $name;
}

$obj = new Test();
var_dump($obj);
$format = "【%s】==>【%s】";
foreach ($obj as $key => $value)
{
    $res = sprintf($format, $key, $value);
    var_dump($res);
}
```
`结果`
```
啥也没有,把$obj 打印出来, 循环不到
object(Test)#1 (0) {
  ["name"]=>
  uninitialized(string)
}
```
> 约束类型,赋初值
```
class Test
{
    public string $name='zhangsan';
}

$obj = new Test();
var_dump($obj);
$format = "【%s】==>【%s】";
foreach ($obj as $key => $value)
{
    $res = sprintf($format, $key, $value);
    var_dump($res);
}

```
`结果`
```
string(27) "【name】==>【zhangsan】"
```

### 结论

> 对于之前的版本是都能拿到的,但7.4定义`有约束类型`属性时且`不赋初始值`的情况下是拿不到的。
