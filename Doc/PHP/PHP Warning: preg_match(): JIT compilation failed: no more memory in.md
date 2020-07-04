#PHP Warning: preg_match(): JIT compilation failed: no more memory in

`解决方案修改 /usr/local/etc/php/7.3/php.ini：`
```
php -i | grep php.ini

拿到php.ini的路径后将
将
;pcre.jit=1
改为：
pcre.jit=0
```