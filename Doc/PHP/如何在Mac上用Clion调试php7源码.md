# 如何在 Mac 上用 Clion 调试 php7 源码

### 1、下载 Clion 软件
http://www.jetbrains.com/clion/
试用30天，有条件的可以购买

### 2、编译 php 源码

##### 下载代码

````text
版本号是7.1.10
https://www.php.net/distributions/php-7.1.10.tar.gz
````

##### 配置 mac

````text
安装 xcode ，保证cmake命令可用
安装libiconv
brew install libiconv
配置环境变量
echo 'export PATH="/usr/local/opt/libiconv/bin:$PATH"' >> ~/.zshrc
export LDFLAGS="-L/usr/local/opt/libiconv/lib"
export CPPFLAGS="-I/usr/local/opt/libiconv/include"
编译 php7
mkdir php7
./configure --prefix=~/php7 --enable-fpm --enable-debug --with-iconv=/usr/local/opt/libiconv
--prefix：编译好的文件存放的路径
--with-iconv：自己安装的libiconv路径，mac自带的与php用的不兼容，需要使用自己的

修改Makefile文件
查找 -liconv，删除 -liconv，结果长这样
EXTRA_LIBS = -lresolv -lm -lxml2 -lz -licucore -lm -lxml2 -lz -licucore -lm -lxml2 -lz -licucore -lm -lxml2 -lz -licucore -lm -lxml2 -lz -licucore -lm -lxml2 -lz -licucore -lm /usr/local/opt/libiconv/lib/libiconv.dylib
删除-liconv后，将/usr/local/opt/libiconv/lib/libiconv.dylib放在了最后

Make
Make install
````

### 配置 Clion

导入项目,如下图

![:](View/Static/Images/php-source-code-1.png)

![:](View/Static/Images/php-source-code-2.png)

> CMakeLists 导入完成后，Clion会自动生成 CMakeLists.txt，这个要根据自己的路径改下，修改示例如下

````text
cmake_minimum_required(VERSION 3.15)
project(php_7_1_10)

set(CMAKE_CXX_STANDARD 14)

set(PHP_SOURCE ~/php-test/php-7.1.10)

include_directories(${PHP_SOURCE}/ext/bcmath)
include_directories(${PHP_SOURCE}/ext/bcmath/libbcmath)
include_directories(${PHP_SOURCE}/ext/bcmath/libbcmath/src)
include_directories(${PHP_SOURCE}/ext/bz2)
include_directories(${PHP_SOURCE}/ext/calendar)
include_directories(${PHP_SOURCE}/ext/com_dotnet)
include_directories(${PHP_SOURCE}/ext/ctype)
include_directories(${PHP_SOURCE}/ext/curl)
include_directories(${PHP_SOURCE}/ext/date)
include_directories(${PHP_SOURCE}/ext/date/lib)
include_directories(${PHP_SOURCE}/ext/dba)
include_directories(${PHP_SOURCE}/ext/dba/libcdb)
include_directories(${PHP_SOURCE}/ext/dba/libflatfile)
include_directories(${PHP_SOURCE}/ext/dba/libinifile)
include_directories(${PHP_SOURCE}/ext/dom)
include_directories(${PHP_SOURCE}/ext/enchant)
include_directories(${PHP_SOURCE}/ext/exif)
include_directories(${PHP_SOURCE}/ext/fileinfo)
include_directories(${PHP_SOURCE}/ext/fileinfo/libmagic)
include_directories(${PHP_SOURCE}/ext/filter)
include_directories(${PHP_SOURCE}/ext/ftp)
include_directories(${PHP_SOURCE}/ext/gd)
include_directories(${PHP_SOURCE}/ext/gd/libgd)
include_directories(${PHP_SOURCE}/ext/gettext)
include_directories(${PHP_SOURCE}/ext/gmp)
include_directories(${PHP_SOURCE}/ext/hash)
include_directories(${PHP_SOURCE}/ext/iconv)
include_directories(${PHP_SOURCE}/ext/imap)
include_directories(${PHP_SOURCE}/ext/interbase)
include_directories(${PHP_SOURCE}/ext/intl)
include_directories(${PHP_SOURCE}/ext/intl/breakiterator)
include_directories(${PHP_SOURCE}/ext/intl/calendar)
include_directories(${PHP_SOURCE}/ext/intl/collator)
include_directories(${PHP_SOURCE}/ext/intl/common)
include_directories(${PHP_SOURCE}/ext/intl/converter)
include_directories(${PHP_SOURCE}/ext/intl/dateformat)
include_directories(${PHP_SOURCE}/ext/intl/formatter)
include_directories(${PHP_SOURCE}/ext/intl/grapheme)
include_directories(${PHP_SOURCE}/ext/intl/idn)
include_directories(${PHP_SOURCE}/ext/intl/locale)
include_directories(${PHP_SOURCE}/ext/intl/msgformat)
include_directories(${PHP_SOURCE}/ext/intl/normalizer)
include_directories(${PHP_SOURCE}/ext/intl/resourcebundle)
include_directories(${PHP_SOURCE}/ext/intl/spoofchecker)
include_directories(${PHP_SOURCE}/ext/intl/timezone)
include_directories(${PHP_SOURCE}/ext/intl/transliterator)
include_directories(${PHP_SOURCE}/ext/intl/uchar)
include_directories(${PHP_SOURCE}/ext/json)
include_directories(${PHP_SOURCE}/ext/ldap)
include_directories(${PHP_SOURCE}/ext/libxml)
include_directories(${PHP_SOURCE}/ext/mbstring)
include_directories(${PHP_SOURCE}/ext/mbstring/libmbfl/filters)
include_directories(${PHP_SOURCE}/ext/mbstring/libmbfl/mbfl)
include_directories(${PHP_SOURCE}/ext/mbstring/libmbfl/nls)
include_directories(${PHP_SOURCE}/ext/mbstring/oniguruma)
include_directories(${PHP_SOURCE}/ext/mbstring/oniguruma/win32)
include_directories(${PHP_SOURCE}/ext/mcrypt)
include_directories(${PHP_SOURCE}/ext/mysqli)
include_directories(${PHP_SOURCE}/ext/mysqlnd)
include_directories(${PHP_SOURCE}/ext/oci8)
include_directories(${PHP_SOURCE}/ext/odbc)
include_directories(${PHP_SOURCE}/ext/opcache)
include_directories(${PHP_SOURCE}/ext/opcache/Optimizer)
include_directories(${PHP_SOURCE}/ext/openssl)
include_directories(${PHP_SOURCE}/ext/pcntl)
include_directories(${PHP_SOURCE}/ext/pcre)
include_directories(${PHP_SOURCE}/ext/pcre/pcrelib)
include_directories(${PHP_SOURCE}/ext/pcre/pcrelib/sljit)
include_directories(${PHP_SOURCE}/ext/pdo)
include_directories(${PHP_SOURCE}/ext/pdo_dblib)
include_directories(${PHP_SOURCE}/ext/pdo_firebird)
include_directories(${PHP_SOURCE}/ext/pdo_mysql)
include_directories(${PHP_SOURCE}/ext/pdo_oci)
include_directories(${PHP_SOURCE}/ext/pdo_odbc)
include_directories(${PHP_SOURCE}/ext/pdo_pgsql)
include_directories(${PHP_SOURCE}/ext/pdo_sqlite)
include_directories(${PHP_SOURCE}/ext/pgsql)
include_directories(${PHP_SOURCE}/ext/phar)
include_directories(${PHP_SOURCE}/ext/posix)
include_directories(${PHP_SOURCE}/ext/pspell)
include_directories(${PHP_SOURCE}/ext/readline)
include_directories(${PHP_SOURCE}/ext/recode)
include_directories(${PHP_SOURCE}/ext/reflection)
include_directories(${PHP_SOURCE}/ext/session)
include_directories(${PHP_SOURCE}/ext/shmop)
include_directories(${PHP_SOURCE}/ext/simplexml)
include_directories(${PHP_SOURCE}/ext/skeleton)
include_directories(${PHP_SOURCE}/ext/snmp)
include_directories(${PHP_SOURCE}/ext/soap)
include_directories(${PHP_SOURCE}/ext/sockets)
include_directories(${PHP_SOURCE}/ext/spl)
include_directories(${PHP_SOURCE}/ext/sqlite3)
include_directories(${PHP_SOURCE}/ext/sqlite3/libsqlite)
include_directories(${PHP_SOURCE}/ext/standard)
include_directories(${PHP_SOURCE}/ext/sysvmsg)
include_directories(${PHP_SOURCE}/ext/sysvsem)
include_directories(${PHP_SOURCE}/ext/sysvshm)
include_directories(${PHP_SOURCE}/ext/tidy)
include_directories(${PHP_SOURCE}/ext/tokenizer)
include_directories(${PHP_SOURCE}/ext/wddx)
include_directories(${PHP_SOURCE}/ext/xml)
include_directories(${PHP_SOURCE}/ext/xmlreader)
include_directories(${PHP_SOURCE}/ext/xmlrpc)
include_directories(${PHP_SOURCE}/ext/xmlrpc/libxmlrpc)
include_directories(${PHP_SOURCE}/ext/xmlwriter)
include_directories(${PHP_SOURCE}/ext/xsl)
include_directories(${PHP_SOURCE}/ext/zip)
include_directories(${PHP_SOURCE}/ext/zip/lib)
include_directories(${PHP_SOURCE}/ext/zlib)
include_directories(${PHP_SOURCE}/main)
include_directories(${PHP_SOURCE}/main/streams)
include_directories(${PHP_SOURCE}/netware)
include_directories(${PHP_SOURCE}/sapi/apache2handler)
include_directories(${PHP_SOURCE}/sapi/cli)
include_directories(${PHP_SOURCE}/sapi/embed)
include_directories(${PHP_SOURCE}/sapi/fpm/fpm)
include_directories(${PHP_SOURCE}/sapi/fpm/fpm/events)
include_directories(${PHP_SOURCE}/sapi/litespeed)
include_directories(${PHP_SOURCE}/sapi/phpdbg)
include_directories(${PHP_SOURCE}/TSRM)
include_directories(${PHP_SOURCE}/win32)
include_directories(${PHP_SOURCE}/Zend)
include_directories(${PHP_SOURCE})

add_custom_target(makefile COMMAND make && make install WORKING_DIRECTORY ${PROJECT_SOURCE_DIR})
````

> 配置 profile,右上角会生成如下的配置（重写CMakelist后是makefile）

![:](View/Static/Images/php-source-code-3.png)

> 点击 Edit Configurations,配置如下：
  
![:](View/Static/Images/php-source-code-4.png)

````text
1、创建了 CMake Applicaiton
如果没有自己可以从template中创建

2、target 选择CMakelist中自己配置的

3、找到编译文件的路径，选择生成的二进制文件

4、要运行的php文件路径参数
-f ~/test/test.php

5、工作目录，php测试文件的父目录即可
~/test/test.php

6、最后点击apply，点击ok
````

### 运行测试

![:](View/Static/Images/php-source-code-5.png)

> 有的可能断点不成功，改下Debugger试试：preference->Build,Exexuction...->Toolchains->Debugger,有lldb和gdb切换下试试，这个和电脑上安装的软件有关
  
> 运行成功的效果图如下：

 ![:](View/Static/Images/php-source-code-6.png)

### Copy地址

> 按此文章一跑就通，copy下来留作记录

https://www.jianshu.com/p/f6af567b25a7
