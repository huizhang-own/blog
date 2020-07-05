#MAC 安装MemcacheQ 安装与使用

#1.特点：
- 单易用。
- 处理速度快。
- 可创建多条队列。
- 并发性能高。
- 与memcache协议兼容。
- MemcacheQ 依赖 Berkeley DB 和 libevent
Berkeley DB用于持久化存储队列数据，避免当MemcacheQ崩溃或服务器死机时发生数据丢失。

#2.1.安装Berkeley DB
```java
1.brew install libevent
2.brew install berkeley-db
2.安装MemcacheQ
        wget http://download.oracle.com/berkeley-db/db-4.7.25.NC.tar.gz
        tar zxvf db-4.7.25.NC.tar.gz
        cd db-4.7.25.NC
        cd build_unix/ 
        ../dist/configure  
        make  
        make install 
4.运行memcacheQ
memcacheq -d -r -H /data1/memcacheq -N -R -v -L 1024 -B 1024 > /data1/mq_error.log 2>&1   (mq_error.log 自己指定路径)
```
`注:`
```java
 -p <num>      TCP监听端口(default: 22201)
-U <num>      UDP监听端口(default: 0, off)
-s <file>     unix socket路径(不支持网络)
-a <mask>     unix socket访问掩码(default 0700)
-l <ip_addr>  监听网卡
-d            守护进程
-r            最大化核心文件限制
-u <username> 以用户身份运行(only when run as root)
-c <num>      最大并发连接数(default is 1024)
-v            详细输出 (print errors/warnings while in event loop)
-vv           更详细的输出 (also print client commands/reponses)
-i            打印许可证信息
-P <file>     PID文件
-t <num>      线程数(default 4)
--------------------BerkeleyDB Options-------------------------------
-m <num>      BerkeleyDB内存缓存大小, default is 64MB
-A <num>      底层页面大小, default is 4096, (512B ~ 64KB, power-of-two)
-H <dir>      数据库家目录, default is '/data1/memcacheq'
-L <num>      日志缓冲区大小, default is 32KB
-C <num>      多少秒checkpoint一次, 0 for disable, default is 5 minutes
-T <num>      多少秒memp_trickle一次, 0 for disable, default is 30 seconds
-S <num>      多少秒queue stats dump一次, 0 for disable, default is 30 seconds
-e <num>      达到缓存百分之多少需要刷新, default is 60%
-E <num>      一个单一的DB文件有多少页, default is 16*1024, 0 for disable
-B <num>      指定消息体的长度,单位字节, default is 1024
-D <num>      多少毫秒做一次死锁检测(deadlock detecting), 0 for disable, default is 100ms
-N            开启DB_TXN_NOSYNC获得巨大的性能改善, default is off
-R            自动删除不再需要的日志文件, default is off
```
#3.安装PHP-memcache扩展
`不做阐述`
#4.PHP代码
```java
<?php
$memcache_obj = new Memcache;
$memcache_obj->connect('127.0.0.1', 22201);

$memcache_obj->set('a',time(),0,0);//入栈
echo $memcache_obj->get('a');      //出栈
```
#5.PHP前沿学习群: 257948349



