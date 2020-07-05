#jQuery关键词高亮显示

###1.要实现的效果
> 关键词搜索时内容结果高亮显示

###2.js文件
```
/**
 * Created by dell on 2018/2/28.
 */
(function ($) {
    $.fn.GL = function (options) {
        var dataop = {
            ocolor:'red',
            oshuru:'高亮',
        };
        var chuancan = $.extend(dataop,options);
        $(this).each(function()
        {
            var _this = $(this)
            _this.find($(".glnow")).each(function()
            {
                $(this).css({color:""});
            });
        });
        if(chuancan.oshuru==''){
            return false;
        }else{
            var regExp = new RegExp("(" + chuancan.oshuru.replace(/[(){}.+*?^$|\\\[\]]/g, "\\$&") + ")", "ig");
            $(this).each(function()
            {
                var _this1 = $(this)
                var html = _this1.html();
                var newHtml = html.replace(regExp, '<span class="glnow" style="color:'+chuancan.ocolor+'">'+chuancan.oshuru+'</span>');
                _this1.html(newHtml);
            });
        }
    }
})(jQuery);



```
> 将上面代码保存到js文件，然后再前端引入
###3.使用方式
```
// 文字高亮
  function textHighlight() {
        var text=$("#high-light-text").val();
        if (text.length > 0) {
            $('.content').GL({ 
                ocolor: 'red',//设置关键词高亮颜色
                oshuru: text//设置要显示的关键词
            });
        }
    }
```

###4.效果
![image.png](https://upload-images.jianshu.io/upload_images/10306662-0ce7c49c6c95ff29.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)
