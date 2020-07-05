#Layui js生成上传图片按钮不起作用

`几个要素`
> 1. append html后要用upload.render();
> 2. upload.render(); 后面再跟，不要写在js append的上面否则失效
```
upload.render({
            elem: '#upload-pic'
            ,url: ''
            .
            .
            . 省略
});
```
