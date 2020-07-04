#yaf的安装与配置

##1.下载yaf
[点我下载](http://pecl.php.net/package/yaf)
`我的php版本为php7.2.1,yaf下载的版本是为3.0.8`
##2.解压
>tar -zxvf yaf-3.0.8.tgz
cd yaf-3.0.8
##3.执行
>/usr/local/Cellar/php/7.2.1_12/bin/phpize  && ./configure --with-php-config=/usr/local/Cellar/php/7.2.1_12/bin/php-config && make && make install

`请根据自己的php路径做相应的修改`

>执行完你看到了这么一句,说明你第一步Yaf编译部分是ok了.
Installing shared extensions:     /usr/local/Cellar/php72/7.2.1_12/lib/php/extensions/no-debug-non-zts-20170718/
##4.查看确认编译后的文件
>ll /usr/local/Cellar/php/7.2.1_12/lib/php/extensions/no-debug-non-zts-20170718
`看到下面的内容说明就已经安装成功`
  ![image.png](https://upload-images.jianshu.io/upload_images/10306662-b0634e58fbd3a16c.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

##5.配置 php.ini
```java
[yaf]
yaf.environ = product
yaf.library = NULL
yaf.cache_config = 0
yaf.name_suffix = 1
yaf.name_separator = ""
yaf.forward_limit = 5
yaf.use_namespace = 0
yaf.use_spl_autoload = 0
extension=yaf.so //关键步骤:载入yaf.so ,上面也可忽略
```
##6.重启PHP或者重启apache\ngnix

##7.![image.png](https://upload-images.jianshu.io/upload_images/10306662-f8d8e6c3979778a6.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)



