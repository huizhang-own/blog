#Mysql基本架构

####1.mysql的组成
简图
> ![FA77AB34A02F0399C8AA7B39EA010BA2.jpg](https://upload-images.jianshu.io/upload_images/10306662-99796a26af10303d.jpg?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

逻辑架构

![image.png](https://upload-images.jianshu.io/upload_images/10306662-d493102b854646a9.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)


客户端
> PHP  JAVA Go 等

服务层
>连接器：管理连接，权限验证。
查询缓存： 命中则直接返回结果。
分析器：词法分析，语法分析。
优化器：执行计划生成，索引选择。
执行器：操作引擎，返回结果。

存储引擎层
>存储引擎：存储数据提供读写接口。

数据存储层
> 主要是将数据存储在运行于裸设备的文件系统之上，并完成与存储引擎的交互。

####2.客户端
> 首先以PHP为例连接mysql
```
$db = mysql_connect(ip:port,user,password);
```
>这个php连接mysql语句就是客户端工具，用来跟数据库服务端建立tcp连接。

####3.连接器
> 当客户端和服务端建立起tcp握手后，连接器开始认证身份。

> 用户名或密码不对，会收到`Access denied for user`错误，客户端结束执行。
> 如通过，连接器在权限表中查出拥有的权限。

> 连接完成后，如果没有后续的动作，这个连接就处于sleep状态。
![image.png](https://upload-images.jianshu.io/upload_images/10306662-e989fb54225ee287.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

> 客户端如果8小时(默认时间,由wait_timeout控制)没有动静则自动断开。

####4. 查询缓存

> MySQL的查询缓存是MySQL内置的一种缓存机制。比如我们发送select * from user where name=tuzisir 这么一个查询，MySQL首先检索内存中是否有数据并且数据是否过期，如果没有数据或者数据已经过期就去数据库中查找，如果有数据并且没有过期就直接返回数据。对于sql的匹配规则非常简单，就是字符串的比较，只要字符串相同，那么就认为是同一个查询。

####5.分析器
> 跟语言的分析是一样的。

> 词法分析: sql 关键词是否正确

```
(拿php举例，一条语句 if else 解析出这是正确的而不是 fi else)
```
> 语法分析: 格式是否正确 

```
(拿php举例，一条语句 if else 解析出这是正确的而不是 else if)
```

####6.优化器
> MySQL采用了基于开销的优化器，以确定处理查询的最解方式，也就是说执行查询之前，都会先选择一条自以为最优的方案，然后执行这个方案来获取结果。在很多情况下，MySQL能够计算最佳的可能查询计划，但在某些情况下，MySQL没有关于数据的足够信息，或者是提供太多的相关数据信息，估测就不那么友好了。 

####7.执行器
> 执行器包括执行查询语句，返回查询结果，生成执行计划包括与存储引擎的一些处理操作。 

####8.存储引擎
> InnoDb
> MylSAM
> Archive
> Blackhole
> CSV
> Memory
> Federated
> Mrg_MylSAM
> NDB集群













