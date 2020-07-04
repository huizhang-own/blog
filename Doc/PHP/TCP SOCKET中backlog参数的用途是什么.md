#TCP SOCKET中backlog参数的用途是什么？

###1. 简介
> 2013年12月14日发布的php5.5.6中，changelog中有一条变更这条改动是在10月28日改的，patch提交者认为，提高backlog数量，哪怕出现timeout之类的错误，也比因为backlog满了以后，悄悄的忽略tcp syn的请求要好。

> 2014年7月22日fpm的默认backlog已经不是65535了，现在是511了。其中理由是“backlog值为65535太大了。会导致前面的nginx（或者其他客户端）超时”，而且提交者举例计算了一下，假设FPM的QPS为5000，那么65535个请求全部处理完需要13s的样子。但前端的nginx（或其他客户端）已经等待超时，关闭了这个连接。当FPM处理完之后，再往这个SOCKET ID 写数据时，却发现连接已关闭，得到的是“error: Broken Pipe”，在nginx、redis、apache里，默认的backlog值都是511。故这里也建议改为511。

> backlog的定义是已连接但未进行accept处理的SOCKET队列大小，已是（并非syn的SOCKET队列）。如果这个队列满了，将会发送一个ECONNREFUSED错误信息给到客户端,即 linux 头文件 /usr/include/asm-generic/errno.h中定义的“Connection refused”，（如果协议不支持重传，该请求会被忽略

> 在linux 2.2以前，backlog大小包括了半连接状态和全连接状态两种队列大小。linux 2.2以后，分离为两个backlog来分别限制半连接SYN_RCVD状态的未完成连接队列大小跟全连接ESTABLISHED状态的已完成连接队列大小。互联网上常见的TCP SYN FLOOD恶意DOS攻击方式就是用/proc/sys/net/ipv4/tcp_max_syn_backlog来控制的

> 在使用listen函数时，内核会根据传入参数的backlog跟系统配置参数/proc/sys/net/core/somaxconn中，二者取最小值，作为“ESTABLISHED状态之后，完成TCP连接，等待服务程序ACCEPT”的队列大小。在kernel 2.4.25（Kernel 操作系统内核）之前，是写死在代码常量SOMAXCONN，默认值是128。在kernel 2.4.25之后，在配置文件/proc/sys/net/core/somaxconn (即 /etc/sysctl.conf 之类 )中可以修改。
![image.png](https://upload-images.jianshu.io/upload_images/10306662-e35cd7598136bc74.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)
`如图，服务端收到客户端的syn请求后，将这个请求放入syns queue中，然后服务器端回复syn+ack给客户端，等收到客户端的ack后，将此连接放入accept queue。`

`系统默认参数`

```
root@vmware-cnxct:/home/cfc4n# cat /proc/sys/net/core/somaxconn
128
```

`fpm`
```
root@vmware-cnxct:/home/cfc4n# ss -lt
State      Recv-Q Send-Q         Local Address:Port                    Peer Address:Port
LISTEN     0      128                        *:ssh                           *:*
LISTEN     0      128                 0.0.0.0:9000                           *:*
LISTEN     0      128                       *:http                           *:*
LISTEN     0      128                       :::ssh                           :::*
LISTEN     0      128                      :::http                           :::*

```

> 在FPM的配置中，listen.backlog值默认为511，而如上结果中看到的Send-Q却是128，可见确实是以/proc/sys/net/core/somaxconn跟listen参数的最小值作为backlog的值。

> 当backlog为某128时，accept queue队列塞满后，TCP建立的三次握手完成，连接进入ESTABLISHED状态，客户端（nginx）发送给PHP-FPM的数据，FPM处理不过来，没有调用accept将其从accept quque队列取出时，那么就没有ACK包返回给客户端nginx，nginx那边根据TCP 重传机制会再次发从尝试…报了“111: Connection refused”错。当SYNS QUEUE满了时，，不停重传SYN包。

> 对于已经调用accept函数，从accept queue取出，读取其数据的TCP连接，由于FPM本身处理较慢，以至于NGINX等待时间过久，直接终止了该fastcgi请求，返回“110: Connection timed out”。当FPM处理完成后，往FD里写数据时，发现前端的nginx已经断开连接了，就报了“Write broken pipe”。当ACCEPT QUEUE满了时，TCPDUMP的结果如下，不停重传PSH SCK包。

###2.backlog大小设置为多少合适？
> 这跟FPM的处理能力有关，backlog太大了，导致FPM处理不过来，nginx那边等待超时，断开连接，报504 gateway timeout错。同时FPM处理完准备write 数据给nginx时，发现TCP连接断开了，报“Broken pipe”。backlog太小的话，NGINX之类client，根本进入不了FPM的accept queue，报“502 Bad Gateway”错。所以，这还得去根据FPM的QPS来决定backlog的大小。计算方式最好为QPS=backlog。对了这里的QPS是正常业务下的QPS，千万别用echo hello world这种结果的QPS去欺骗自己。当然，backlog的数值，如果指定在FPM中的话，记得把操作系统的net.core.somaxconn设置的起码比它大。另外，ubuntu server 1404上/proc/sys/net/core/somaxconn 跟/proc/sys/net/ipv4/tcp_max_syn_backlog 默认值都是128，这个问题，我为了抓数据，测了好几遍才发现。
对于测试时，TCP数据包已经drop掉的未进入syns queue，以及未进入accept queue的数据包，可以用netstat -s来查看


###3.参考地址
http://www.cnxct.com/something-about-phpfpm-s-backlog/

