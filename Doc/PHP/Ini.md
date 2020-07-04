uuid:356113557370175488
title:EasySwoole 使用*.ini格式的配置文件
description:本文章带领大家学习如何在EasySwoole使用ini格式的配置文件。
author:huizhang
<<<easyswoole

# EasySwoole 使用*.ini格式的配置文件

### 简介
本文章带领大家学习如何在EasySwoole使用ini格式的配置文件。

### ini的优缺点

优点：线性的、简单、简练、方便

缺点：复杂类型的数据配置无力

### 目录结构
只需要关注标红的两个地方，Config/Ini目录是用来存放ini文件的，
Ini是我写的读取ini配置文件的包。

![](../../.vuepress/public/image/ini-dir.png)

### Ini包源码
非常简单
````php
<?php
/**
 * @CreateTime:   2020/5/3 下午6:30
 * @Author:       huizhang  <tuzisir@163.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  解析ini配置文件
 */
namespace EasySwoole\Ini;

use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Spl\SplArray;

class Ini
{
    use Singleton;

    protected $iniDir;

    public function __construct()
    {
        $this->iniDir = Config::getInstance()->getConf('INI_DIR');
    }

    public function setDir($iniDir)
    {
        $this->iniDir = $iniDir;
        return $this;
    }

    public function getConf(string $fileName, $key)
    {
        return $this->parseConf($fileName, $key);
    }

    private function parseConf($fileName, $key)
    {
        $config = parse_ini_file($this->iniDir.'/'.$fileName.'.ini', true);

        if ($key == null) {
            return $config;
        }

        if (empty($key)) {
            return null;
        }
        if (strpos($key, '.') > 0) {
            $temp = explode('.', $key);
            if (is_array($config)) {
                $data = new SplArray($config);
                return $data->get(implode('.', $temp));
            }
        }

        return $config[$key];
    }
}
````

### 配置文件格式

database.ini
````ini
; 订单数据库
[order]
host=127.0.0.1
port=3306
user=admin
password=123456
database=order

; 用户数据库
[user]
host=127.0.0.1
port=3306
user=admin
password=123456
database=user
````

### 配置Ini配置文件的默认目录

配置ini文件的目录有两种方式

##### 1. 在EasySwoole的配置文件中，比如dev.php

````php
<?php
return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        xxx
    ],
    'INI_DIR' => EASYSWOOLE_ROOT.'/Config/Ini' // 指定ini配置文件的目录
];
````

##### 2. 在mainServerCreate方法中注册
````php
public static function mainServerCreate(EventRegister $register)
{
    Ini::getInstance()->setDir(EASYSWOOLE_ROOT.'/Config/Ini');
}
````

### 使用方式

````php 
// 获取database.ini中的所有配置
Ini::getInstance()->getConf('database');

// 获取database.ini中的一块配置
Ini::getInstance()->getConf('database', 'order');

// 获取database.ini中的一块配置的某一项
Ini::getInstance()->getConf('database', 'order.host');
````

### EasySwoole

官网：http://www.easyswoole.com

交流群：932625047

![](../../.vuepress/public/image/website.png)
