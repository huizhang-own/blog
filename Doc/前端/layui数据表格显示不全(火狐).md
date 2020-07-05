#layui数据表格显示不全(火狐)

##### 1. 问题描述
> 自动渲染出来的数据表格发现只能显示几行而且还很不固定，操作一番发现当点击表头的时候才能显示全，正常的姿势都尝试了无果，只能采用非正常姿势。
![image.png](https://upload-images.jianshu.io/upload_images/10306662-b9fd77c81c15e67b.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

##### 2. 错误姿势

> 想直接⤵️，但是不生效，猜测是它的执行速度要比渲染的快。
```
  $('表头隐藏元素').trigger('click');
```

> 给table.render 增加⬇️不管用
```
 ,height : 'full-200'
```

> ⬇️ 不管用
```
    table.reload('table');
```
`
最后只能使用s姿势
`

> ⬆️s:帅的意思不要理解成别的。
##### 3.姿势教学

> 目的是为了延时一秒
```
t = setInterval(function (args) {
            $('表头隐藏元素').trigger('click');
            clearInterval(t); // 执行一次后停止
        }, 1000);
```


