#Mac 下nginx 切换brew下载的php版本

#1.简介
> mac下自带的php5.6挺烦人的，用brew安装了php7.2后，phpinfo出来的竟然还是5.6

#2.尝试的解决方法
> 第一种
```
brew unlink php
bre link php72
`失败`
```
> 第二种，以为是nginx配置的原因，最后百度了几篇文章修改如下，应该是没有错误了。
```
      location ~ \.php$ {
            root           /Users/guoyuzhao/sites;
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            #fastcgi_param  SCRIPT_FILENAME  /scripts$fastcgi_script_name;
            fastcgi_param SCRIPT_FILENAME /Users/guoyuzhao/sites/$fastcgi_script_name;
            include        fastcgi_params;
        }
```
> nginx -v 查看nginx是否有错误

```
➜  7.2 sudo nginx -t
Password:
nginx: the configuration file /usr/local/etc/nginx/nginx.conf syntax is ok
nginx: configuration file /usr/local/etc/nginx/nginx.conf test is successful
```

> 第三种，完美解决，因为sudo php-fpm start 启动的是mac自带的。而我用brew下载的，所以需要这样启动
```
sudo killall php-fpm // 先杀死所有php-fpm 进程
sudo /usr/local/sbin/php-fpm &
```

#3.结果
![image.png](https://upload-images.jianshu.io/upload_images/10306662-62671a24406a49d0.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

