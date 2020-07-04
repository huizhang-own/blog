#mac快速安装PHP扩展

###1.下载安装包(什么方式下载都可以)
###2.解压源文件(不用我多说)
###3.编译源文件
- 进入你解压的目录
- 运行phpize命令生成配置文件：sudo phpize
- 运行./configure:
      1.需要找一下php-config的位置：
        `which php-conifg`
      2.执行命令
        `./configure --with-php-config=/usr/bin/php-config`(注意改成自己的目录)

###4.编译 执行命令:` make`
###5.安装 执行命令: `make install`
###6. 配置php.ini
- 再php.ini中添加:extension = 扩展名称
###7.重启apache
###8.[参考地址](https://www.jianshu.com/p/1fa69f30519a)
