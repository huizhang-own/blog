#PHP开发API接口签名及验证

```
<?php
// 设置一个密钥(secret)，只有发送方，和接收方知道
/*----发送方和接收方- start ----*/
$secret = "28c8edde3d61a0411511d3b1866f0636";
/*----发送方和接收方- end ----*/
 
 
/*----发送方待发送数据- start ----*/
// 待发送的数据包
$data = array(
　　'username' => '123@qq.com',
　　'sex' => '1',
　　'age' => '16',
　　'addr' => 'zhongguo',
　　'timestamp' => time(),
);
 
// 获取sign
function getSign($secret, $data) {
　　// 对数组的值按key排序
　　ksort($data);
　　// 生成url的形式
　　$params = http_build_query($data);
　　// 生成sign
　　$sign = md5($params . $secret);
　　return $sign;
}
 
// 发送的数据加上sign
$data['sign'] = getSign($secret, $data);
/*----发送方待发送数据- end ----*/
 
 
 
/*----接收方待处理验证数据- start ----*/
/**
* 后台验证sign是否合法
* @param [type] $secret [description]
* @param [type] $data [description]
* @return [type] [description]
*/
function verifySign($secret, $data) {
　　// 验证参数中是否有签名
　　if (!isset($data['sign']) || !$data['sign']) {
　　　　return '发送的数据签名不存在';
　　}
　　if (!isset($data['timestamp']) || !$data['timestamp']) {
　　　　return '发送的数据参数不合法';
　　}
　　// 验证请求， 10分钟失效
　　if (time() - $data['timestamp'] > 600) {
　　　　return '验证失效， 请重新发送请求';
　　}
　　$sign = $data['sign'];
　　unset($data['sign']);
　　ksort($data);
　　$params = http_build_query($data);
　　// $secret是通过key在api的数据库中查询得到
　　$sign2 = md5($params . $secret);
　　if ($sign == $sign2) {
　　　　return '验证通过';
　　} else {
　　　　return '请求不合法';
　　}
}
/*----接收方待处理验证数据- end ----*/
?>
```