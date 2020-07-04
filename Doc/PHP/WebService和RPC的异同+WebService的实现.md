#WebService和RPC的异同+WebService的实现

###1.原理
> RPC，远程过程调用,RPC总的来说是一个Client/Server的结构，提供服务的一方称为Server，消费服务的一方称为Client。 

 `下图是本地过程调用，所有的过程都在本地服务器上，依次调用即可。`
![image.png](https://upload-images.jianshu.io/upload_images/10306662-376dd569a8ce3b0f.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

`下图则是所谓的远程过程调用，需要在Client和Server中交互。`
![image.png](https://upload-images.jianshu.io/upload_images/10306662-b610e814dadf5bf4.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

`两种调用方式的区别`
> 1、网络传输的开销和编程的额外复杂性。
2、本地过程调用中，过程在同一块物理内存中，因此就可以传递指针了。而远程过程调用则不能，因为远程过程与调用者运行在完全不同的地址空间中。 
3、远程过程不能共享调用者的环境，所以它就无法直接访问调用者的I/O和操作系统API。 

> 简单来说，就是远程过程调用会比本地过程调用复杂。除了性能的额外开销之外，编程也复杂得多。 

至少可以想到，交互双方需要能够封装数据结构，理解协议，处理连接等等，确实是很麻烦的。可能一个很简单的调用，却需要做很多的编程工作。所以，为了简化RPC调用的编程，就提出了一个RPC的标准模型。 

`下面是RPC的原理草图。 `
![image.png](https://upload-images.jianshu.io/upload_images/10306662-14d2d7b513c307f5.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

> 可以看到，该模型中多了一个stub的组件，这个是约定的接口，也就是server提供的服务。对客户端来说，有了这个stub，RPC调用过程对client code来说就变成透明的了，客户端代码不需要关心沟通的协议是什么，网络连接是怎么建立的。对客户端来说，它甚至不知道自己调用的是一个远程过程，还是一个本地过程。
然后，前面说的理解协议，处理连接的工作，总是要有人做的，这个工作就是在下面的RPC Interface里完成的。 

`下面是web service的原理草图 `

![image.png](https://upload-images.jianshu.io/upload_images/10306662-516cad8860e57c69.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

> 对比一下RPC草图，就会发现非常的接近。在组件层次，和交互时序上完全没有差别，只是方框内的字不一样，但是实际上承担的职责却是完全对应的。
web service接口就是RPC中的stub组件，规定了server能够提供的服务（web service），这在server和client上是一致的，但是也是跨语言跨平台的。同时，由于web service规范中的WSDL文件的存在，现在各平台的web service框架，都可以基于WSDL文件，自动生成web service接口。
 因此，我认为RPC和web service非常得接近，只是RPC的传输层协议，以及应用层协议，可以自行实现，所以选择的余地更大一点。可能会在性能和传输效率上，有更大的优势（不一定） 。


`总结来说，要实现远程过程调用，需要有3要素： `
> 1、server必须发布服务。 
2、在client和server两端都需要有模块来处理协议和连接。 
3、server发布的服务，需要将接口给到client。 

###2.PHP不使用WSDL格式Soap通信
`server1.php`
```
<?php
// 改成自己的地址
$soap = new SoapClient(null,array('location'=>"http://localhost:8080/soap/server1.php",'uri'=>'server1.php'));
//两种调用方式，直接调用方法，和用__soapCall简接调用
$result1 = $soap->getName();
$result2 = $soap->__soapCall("getName",array());
echo $result1."<br/>";
echo $result2;
```
`client1.php`
```
<?php
// 改成自己的地址
$soap = new SoapClient(null,array('location'=>"http://localhost:8080/soap/server1.php",'uri'=>'server1.php'));
//两种调用方式，直接调用方法，和用__soapCall简接调用
$result1 = $soap->getName();
$result2 = $soap->__soapCall("getName",array());
echo $result1."<br/>";
echo $result2;
```

`结果`


###3.PHP使用WSDL格式Soap通信

`creat_wsdl.php`
```
<?php
/**
 * User: yuzhao
 * CreateTime: 2019/3/5 下午3:45
 * Description:
 */
ini_set('soap.wsdl_cache_enabled', 0); //关闭wsdl缓存
include("Service.php");
include("SoapDiscovery.class.php");
$disco = new SoapDiscovery('myapi', 'soap'); //第一个参数是类名（生成的wsdl文件就是以它来命名的），即Service类，第二个参数是服务的名字（这个可以随便写）。
$disco->getWSDL();
```

`Service.php`
```
<?php
/**
 * User: yuzhao
 * CreateTime: 2019/3/5 下午4:03
 * Description:
 */
class myapi {

    public function HelloWorld() {
        return "Hello";
    }

    public function Add($a) {
        return $a;
    }

    public function Bdd($a) {
        return $a;
    }
}
$server = new SoapServer('myapi.wsdl', array('soap_version' => SOAP_1_2));
$server->setClass("myapi"); // 注册Service类的所有方法
$server->handle(); // 处理请求
```

`SoapDiscovery.class.php`
```
<?php

/**
 * Copyright (c) 2005, Braulio Jos?Solano Rojas
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 *     Redistributions of source code must retain the above copyright notice, this list of
 *     conditions and the following disclaimer.
 *     Redistributions in binary form must reproduce the above copyright notice, this list of
 *     conditions and the following disclaimer in the documentation and/or other materials
 *     provided with the distribution.
 *     Neither the name of the Solsoft de Costa Rica S.A. nor the names of its contributors may
 *     be used to endorse or promote products derived from this software without specific
 *     prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
 * CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * @version $Id$
 * @copyright 2005
 */

/**
 * SoapDiscovery Class that provides Web Service Definition Language (WSDL).
 *
 * @package SoapDiscovery
 * @author Braulio Jos?Solano Rojas
 * @copyright Copyright (c) 2005 Braulio Jos?Solano Rojas
 * @version $Id$
 * @access public
 **/
class SoapDiscovery {
    private $class_name = '';
    private $service_name = '';

    /**
     * SoapDiscovery::__construct() SoapDiscovery class Constructor.
     *
     * @param string $class_name
     * @param string $service_name
     **/
    public function __construct($class_name = '', $service_name = '') {
        $this->class_name = $class_name;
        $this->service_name = $service_name;
    }

    /**
     * SoapDiscovery::getWSDL() Returns the WSDL of a class if the class is instantiable.
     *
     * @return string
     **/
    public function getWSDL() {
        if (empty($this->service_name)) {
            throw new Exception('No service name.');
        }
        $headerWSDL = "<?xml version=\"1.0\" ?>\n";
        $headerWSDL.= "<definitions name=\"$this->service_name\" targetNamespace=\"urn:$this->service_name\" xmlns:wsdl=\"http://schemas.xmlsoap.org/wsdl/\" xmlns:soap=\"http://schemas.xmlsoap.org/wsdl/soap/\" xmlns:tns=\"urn:$this->service_name\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\" xmlns=\"http://schemas.xmlsoap.org/wsdl/\">\n";
        $headerWSDL.= "<types xmlns=\"http://schemas.xmlsoap.org/wsdl/\" />\n";

        if (empty($this->class_name)) {
            throw new Exception('No class name.');
        }

        $class = new ReflectionClass($this->class_name);

        if (!$class->isInstantiable()) {
            throw new Exception('Class is not instantiable.');
        }

        $methods = $class->getMethods();

        $portTypeWSDL = '<portType name="'.$this->service_name.'Port">';
        $bindingWSDL = '<binding name="'.$this->service_name.'Binding" type="tns:'.$this->service_name."Port\">\n<soap:binding style=\"rpc\" transport=\"http://schemas.xmlsoap.org/soap/http\" />\n";
        $serviceWSDL = '<service name="'.$this->service_name."\">\n<documentation />\n<port name=\"".$this->service_name.'Port" binding="tns:'.$this->service_name."Binding\"><soap:address location=\"http://".$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['PHP_SELF']."\" />\n</port>\n</service>\n";
        $messageWSDL = '';
        foreach ($methods as $method) {
            if ($method->isPublic() && !$method->isConstructor()) {
                $portTypeWSDL.= '<operation name="'.$method->getName()."\">\n".'<input message="tns:'.$method->getName()."Request\" />\n<output message=\"tns:".$method->getName()."Response\" />\n</operation>\n";
                $bindingWSDL.= '<operation name="'.$method->getName()."\">\n".'<soap:operation soapAction="urn:'.$this->service_name.'#'.$this->class_name.'#'.$method->getName()."\" />\n<input><soap:body use=\"encoded\" namespace=\"urn:$this->service_name\" encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\" />\n</input>\n<output>\n<soap:body use=\"encoded\" namespace=\"urn:$this->service_name\" encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\" />\n</output>\n</operation>\n";
                $messageWSDL.= '<message name="'.$method->getName()."Request\">\n";
                $parameters = $method->getParameters();
                foreach ($parameters as $parameter) {
                    $messageWSDL.= '<part name="'.$parameter->getName()."\" type=\"xsd:string\" />\n";
                }
                $messageWSDL.= "</message>\n";
                $messageWSDL.= '<message name="'.$method->getName()."Response\">\n";
                $messageWSDL.= '<part name="'.$method->getName()."\" type=\"xsd:string\" />\n";
                $messageWSDL.= "</message>\n";
            }
        }
        $portTypeWSDL.= "</portType>\n";
        $bindingWSDL.= "</binding>\n";
        //return sprintf('%s%s%s%s%s%s', $headerWSDL, $portTypeWSDL, $bindingWSDL, $serviceWSDL, $messageWSDL, '</definitions>');
        $fso = fopen($this->class_name . ".wsdl", "w");
        fwrite($fso, sprintf('%s%s%s%s%s%s', $headerWSDL, $portTypeWSDL, $bindingWSDL, $serviceWSDL, $messageWSDL, '</definitions>'));
    }

    /**
     * SoapDiscovery::getDiscovery() Returns discovery of WSDL.
     *
     * @return string
     **/
    public function getDiscovery() {
        return "<?xml version=\"1.0\" ?>\n<disco:discovery xmlns:disco=\"http://schemas.xmlsoap.org/disco/\" xmlns:scl=\"http://schemas.xmlsoap.org/disco/scl/\">\n<scl:contractRef ref=\"http://".$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['PHP_SELF']."?wsdl\" />\n</disco:discovery>";
    }
}

?>
```

`client.php`
```
<?php
ini_set('default_socket_timeout',80);
ini_set('soap.wsdl_cache_enabled', 0); //关闭wsdl缓存
$soap = new SoapClient('http://localhost:8080/soap/daemo2/myapi.wsdl?wsdl');
var_dump($soap->__getFunctions()); // 输出暴露的方法
var_dump($soap->__getTypes()); // 输出每个方法参数
$res = $soap->Bdd('123'); // 调用
var_dump($res);
```

`使用过程`

> 1. 先用浏览器访问creat_wsdl.php 生成myapi.wsdl 文件
2.执行client.php
![image.png](https://upload-images.jianshu.io/upload_images/10306662-09b8ca3be1237748.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)


###4.学习地址
https://www.cnblogs.com/hujun1992/p/wsdl.html
https://www.cnblogs.com/AloneSword/p/3501543.html






