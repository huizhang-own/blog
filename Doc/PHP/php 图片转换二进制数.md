#php 图片转换二进制数

```
$image   = "1.jpg"; //图片地址
$fp      = fopen($image, 'rb');
$content = fread($fp, filesize($image)); //二进制数据
```