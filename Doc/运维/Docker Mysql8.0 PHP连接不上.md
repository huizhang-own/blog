#Docker Mysql8.0 PHP连接不上

###1.描述
> 这个问题废了我好长时间，我用的dnmp(docker,ngnix,mysql,php)一键安装PHP docker 环境
具体安装方式[点我](https://github.com/yeszao/dnmp)
```
SQLSTATE[HY000] [2006] MySQL server has gone away
```
![image.png](https://upload-images.jianshu.io/upload_images/10306662-ba526a8c668a2b0d.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

###2.分析
> 百度了很多，只有两种可能

>1.mysql 8.0 root 密码的加密方式发生了改变
在mysql8之前的版本使用的密码加密规则是mysql_native_password，但是在mysql8则是caching_sha2_password。`此博文不仔细讲这个问题，因为最终不是它导致的`

>2.PHP访问不到mysql
```
在mysql.cnf 添加, bind-address = 0.0.0.0 这样就都可以访问了
```
![image.png](https://upload-images.jianshu.io/upload_images/10306662-de067ad72aa6de73.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)


> 重新启动
![image.png](https://upload-images.jianshu.io/upload_images/10306662-31280513b973fb6f.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)
