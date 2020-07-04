#php PDO getlastsql写法

> php PDO getlastsql写法
有些时候 运行失败需要查看 sql语句 原型有没有语法错误 这个时候就用
下面的函数就是把问号替换成 值 就可以看到原型了

```
function getrepairsql($sql,$replacement){
    $count=substr_count($sql,'?');
    $pattern = array_fill(0,$count,'/\?/');
    foreach ($replacement as $k=>$v){
        if(!is_int($v)){
            $replacement[$k]="'".$v."'";
        }
    }
    $res = preg_replace($pattern, $replacement, $sql , 1);
    print_r($res);
    exit();
}
```