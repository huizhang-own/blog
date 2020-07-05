#SSH 无法登陆

###1.描述`就跟吃了香蕉和枣的感觉一样`
> 某天下午登陆ssh突然就上不去了，提示如下
```
ssh: connect to host  port 22: Operation timed out
```
>ping端口不通`这时候我非常诧异`
```
➜  ~ telnet IP 22
Trying ...
telnet: connect to address: Connection refused
telnet: Unable to connect to remote host
```

>打开宝塔面板查看端口`这时候我怀疑了人生`
![image.png](https://upload-images.jianshu.io/upload_images/10306662-339016c8df11b735.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

>突发奇想要不用阿里云面板远程登录然后把ssh重启一下`这时候貌似发现了大陆`

![image.png](https://upload-images.jianshu.io/upload_images/10306662-888064d57cb4c37d.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

`重置了N次密码，重启了N次服务器，麻蛋告诉我登录失败，这时候已经凌晨一点多，熬不住了睡觉吧。`

>Today 上完课继续弄还是登录失败，只好打了阿里云人工客服，客服态度很好赞一个，但是态度好不一定就能解决了问题啊，，，急急忙忙10分钟解决不了，让我提交工单，感觉太麻烦然后想再尝试一下。
>> 我用宝塔的shell控制台`注意这个控制台的权限有限`
![image.png](https://upload-images.jianshu.io/upload_images/10306662-3ffe866193766a9d.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

> 查到了一条命令，查到了一条命令，查到了一条命令`这里是重点`
```
 /usr/sbin/sshd -T `提示我权限过大`
```
> 然后
```
chmod -R 600  路径
```
> 重启ssh,完美解决`这些命令都是在宝塔shell控制台输入的`
```
云服务器 ECS Linux CentOS 7 下重启服务不再通过 service  操作，而是通过 systemctl 操作。

查看：systemctl status sshd.service

启动：systemctl start sshd.service

重启：systemctl restart sshd.service

自启：systemctl enable sshd.service
```

> 登录  `完美`
```
➜  ~ ssh root@IP
root@IP's password:
Last login: Mon Oct  8 10:59:32 2018 from 183.197.99.197

Welcome to Alibaba Cloud Elastic Compute Service !

[root@tuzisir ~]#
```

###2.救命地址
[点我](https://blog.csdn.net/woailyoo0000/article/details/79782986)