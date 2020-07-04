#PHP实现简单RPC

####1.什么是rpc
> RPC全称为Remote Procedure Call，翻译过来为“远程过程调用”。目前，主流的平台中都支持各种远程调用技术，以满足分布式系统架构中不同的系统之间的远程通信和相互调用。远程调用的应用场景极其广泛，实现的方式也各式各样。

####2.从通信协议的层面
> 基于HTTP协议的（例如基于文本的SOAP（XML）、Rest（JSON），基于二进制Hessian（Binary））
基于TCP协议的（通常会借助Mina、Netty等高性能网络框架）
####3.从不同的开发语言和平台层面
> 单种语言或平台特定支持的通信技术(例如Java平台的RMI、.NET平台Remoting)
支持跨平台通信的技术（例如HTTP Rest、Thrift等）

####4.从调用过程来看
> 同步通信调用（同步RPC）
异步通信调用（MQ、异步RPC）
####5.常见的几种通信方式
> 远程数据共享（例如：共享远程文件，共享数据库等实现不同系统通信）
  消息队列
  RPC（远程过程调用）

####6.php实现简单的rpc
> 目录结构
![image.png](https://upload-images.jianshu.io/upload_images/10306662-a75b9f4083ecbf5d.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

> rpc服务端
```
<?php
/**
 * User: yuzhao
 * CreateTime: 2018/11/15 下午11:46
 * Description: Rpc服务端
 */
class RpcServer {

    /**
     * User: yuzhao
     * CreateTime: 2018/11/15 下午11:51
     * @var array
     * Description: 此类的基本配置
     */
    private $params = [
        'host'  => '',  // ip地址，列出来的目的是为了友好看出来此变量中存储的信息
        'port'  => '', // 端口
        'path'  => '' // 服务目录
    ];

    /**
     * User: yuzhao
     * CreateTime: 2018/11/16 上午12:14
     * @var array
     * Description: 本类常用配置
     */
    private $config = [
        'real_path' => '',
        'max_size'  => 2048 // 最大接收数据大小
    ];

    /**
     * User: yuzhao
     * CreateTime: 2018/11/15 下午11:50
     * @var nul
     * Description:
     */
    private $server = null;

    /**
     * Rpc constructor.
     */
    public function __construct($params)
    {
        $this->check();
        $this->init($params);
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/11/16 上午12:0
     * Description: 必要验证
     */
    private function check() {
        $this->serverPath();
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/11/15 下午11:48
     * Description: 初始化必要参数
     */
    private function init($params) {
        // 将传递过来的参数初始化
        $this->params = $params;
        // 创建tcpsocket服务
        $this->createServer();
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/11/16 上午12:0
     * Description: 创建tcpsocket服务

     */
    private function createServer() {
        $this->server = stream_socket_server("tcp://{$this->params['host']}:{$this->params['port']}", $errno,$errstr);
        if (!$this->server) exit([
            $errno,$errstr
        ]);
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/11/15 下午11:57
     * Description: rpc服务目录
     */
    public function serverPath() {
        $path = $this->params['path'];
        $realPath = realpath(__DIR__ . $path);
        if ($realPath === false ||!file_exists($realPath)) {
            exit("{$path} error!");
        }
        $this->config['real_path'] = $realPath;
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/11/15 下午11:51
     * Description: 返回当前对象
     */
    public static function instance($params) {
        return new RpcServer($params);
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/11/16 上午12:06
     * Description: 运行
     */
    public function run() {
        while (true) {
            $client = stream_socket_accept($this->server);
            if ($client) {
                echo "有新连接\n";
                $buf = fread($client, $this->config['max_size']);
                print_r('接收到的原始数据:'.$buf."\n");
                // 自定义协议目的是拿到类方法和参数(可改成自己定义的)
                $this->parseProtocol($buf,$class, $method,$params);
                // 执行方法
                $this->execMethod($client, $class, $method, $params);
                //关闭客户端
                fclose($client);
                echo "关闭了连接\n";
            }
        }
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/11/16 上午12:19
     * @param $class
     * @param $method
     * @param $params
     * Description: 执行方法
     */
    private function execMethod($client, $class, $method, $params) {
        if($class && $method) {
            // 首字母转为大写
            $class = ucfirst($class);
            $file = $this->params['path'] . '/' . $class . '.php';
            //判断文件是否存在，如果有，则引入文件
            if(file_exists($file)) {
                require_once $file;
                //实例化类，并调用客户端指定的方法
                $obj = new $class();
                //如果有参数，则传入指定参数
                if(!$params) {
                    $data = $obj->$method();
                } else {
                    $data = $obj->$method($params);
                }
                // 打包数据
                $this->packProtocol($data);
                //把运行后的结果返回给客户端
                fwrite($client, $data);
            }
        } else {
            fwrite($client, 'class or method error');
        }
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/11/16 上午12:10
     * Description: 解析协议
     */
    private function parseProtocol($buf, &$class, &$method, &$params) {
        $buf = json_decode($buf, true);
        $class = $buf['class'];
        $method = $buf['method'];
        $params = $buf['params'];
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/11/16 上午12:30
     * @param $data
     * Description: 打包协议
     */
    private function packProtocol(&$data) {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    }

}

RpcServer::instance([
    'host'  => '127.0.0.1',
    'port'  => 8888,
    'path'  => './api'
])->run();
```

> rpc 客户端

```
<?php
/**
 * User: yuzhao
 * CreateTime: 2018/11/16 上午12:2
 * Description: Rpc客户端
 */
class RpcClient {

    /**
     * User: yuzhao
     * CreateTime: 2018/11/16 上午12:21
     * @var array
     * Description: 调用的地址
     */
    private $urlInfo = array();

    /**
     * RpcClient constructor.
     */
    public function __construct($url)
    {
        $this->urlInfo = parse_url($url);
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/11/16 上午12:2
     * Description: 返回当前对象
     */
    public static function instance($url) {
        return new RpcClient($url);
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        //创建一个客户端
        $client = stream_socket_client("tcp://{$this->urlInfo['host']}:{$this->urlInfo['port']}", $errno, $errstr);
        if (!$client) {
            exit("{$errno} : {$errstr} \n");
        }
        $data = [
            'class'  => basename($this->urlInfo['path']),
            'method' => $name,
            'params' => $arguments
        ];
        //向服务端发送我们自定义的协议数据
        fwrite($client, json_encode($data));
        //读取服务端传来的数据
        $data = fread($client, 2048);
        //关闭客户端
        fclose($client);
        return $data;
    }
}
$cli = new RpcClient('http://127.0.0.1:8888/test');
echo $cli->tuzisir1()."\n";
echo $cli->tuzisir2(array('name' => 'tuzisir', 'age' => 23));
```

> 提供服务的文件
```
<?php
/**
 * User: yuzhao
 * CreateTime: 2018/11/16 上午12:28
 * Description:
 */

class Test {

    public function tuzisir1() {
        return '我是无参方法';
    }
    public function tuzisir2($params) {
        return $params;
    }
}
```

> 效果
![image.png](https://upload-images.jianshu.io/upload_images/10306662-062da1e00c7c8bc8.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)


####7.RPC的注意事项
>性能:影响RPC性能的主要在几个方面：
1.序列化/反序列化的框架
2.网络协议，网络模型，线程模型等

>安全
RPC安全的主要在于服务接口的鉴权和访问控制支持。

>跨平台
跨不同的操作系统，不同的编程语言和平台。