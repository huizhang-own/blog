#解决Antd post请求跨域问题

> 服务端设置
```php
       header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers:content-type");
        header("Access-Control-Allow-Methods:POST, GET, OPTIONS");
```