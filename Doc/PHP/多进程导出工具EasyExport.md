# 多进程导出工具EasyExport

### 1.简介
> 为解决导出大批量数据、加深对进程相关知识学习而开发的一套简单多进程框架(`不喜勿喷`)，主要为了迎合php3.2.x，当然高版本的php也支持，支持5个处理相关业务的回调函数，分别为框架启动时onStart、每个fork进程前的onForkBefore、每个fork进程后的onChildProcess和所有进程完成后的onEnd，php5.2.x版本主要支持可配置化多进程、管道通信、导出任务模块化、文件写入原子化、php5.3支持消息队列通信。

[Git地址](https://github.com/tuzisir-php/EasyExport)
### 2.框架相关
`框架目录`
```
.
├── app
│   └── test.php // 业务文件
├── config // 框架配置
│   └── config.php
├── example // 测试样例
│   ├── CallBackTest.php
│   ├── FileTest.php
│   └── UsePipe.php
├── lib
│   ├── Autoloader.php // 自动加载
│   ├── EasyExport.php // 框架主文件
│   ├── base
│   │   ├── BaseTool.php // 工具基类
│   │   └── CallBackInter.php // 回调interface
│   └── tool
│       ├── ConfigTool.php // 框架配置工具
│       ├── FileTool.php // 文件工具
│       ├── LogTool.php // 日志工具
│       ├── MsgQueue.php // 消息队列工具
│       ├── PipeTool.php // 管道工具
│       └── SignalMemory.php // 共享内存工具(未验证)
├── runtime
│   ├── cache // 缓存
│   │   └── pipe // 管道临时文件
│   ├── data // 框架所需数据和结果数据
│   │   └── FileTest // 每个业务模块会生成一个文件夹
│   └── log // 日志
└── start.php // 启动文件
```
`框架执行流程`
![image.png](https://upload-images.jianshu.io/upload_images/10306662-edc32c4a74d39343.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

### 3.简单使用
`clone框架`
```
1.git clone git@github.com:tuzisir-php/EasyExport.git
```
`先看config.php`
```
<?php
return array(
    // 是否ui显示
    'is_ui' => false,
    // 进程数
    'worker_num' => 3,
    // 业务处理路径(下面的例子这里想着改一下，这样就可以保证所有的导出脚本都集中放到app目录下，方便相同业务复用)
    'business_path' => 'app/EasyStudy.php@EasyStudy',
);
```
`在app目录下新建EasyTest.php,代码如下`

```
<?php
/**
 * @CreateTime:   2019/5/19 上午11:12
 * @Author:       yuzhao  <tuzisir@163.com>
 * @Copyright:    copyright(2019) yuzhao all rights reserved
 * @Description:  流程学习
 */

class EasyStudy implements CallBackInter {

    /**
     * 开始回调
     *
     * @return mixed
     * CreateTime: 2019/5/18 上午9:56
     */
    public function onStart()
    {
        // TODO: Implement onStart() method.
        var_dump('onStart');
    }

    /**
     * 每个fock之前的回调
     *
     * @param $data
     * @return mixed
     * CreateTime: 2019/5/18 上午9:57
     */
    public function onForkBefore($data)
    {
        // TODO: Implement onForkBefore() method.
        var_dump('onForkBefore-'.$data['id']);
    }

    /**
     * 子进程处理回调
     *
     * @param $data
     * @return mixed
     * CreateTime: 2019/5/18 上午9:57
     */
    public function onChildProcess($data)
    {
        // TODO: Implement onChildProcess() method.
        var_dump('onChildProcess-'.$data['id']);
    }

    /**
     * 结束回调
     *
     * @return mixed
     * CreateTime: 2019/5/18 上午9:57
     */
    public function onEnd()
    {
        // TODO: Implement onEnd() method.
        var_dump('onEnd');
    }
}
```

`启动`
```
php start.php [start][stop] [-d]
```
![image.png](https://upload-images.jianshu.io/upload_images/10306662-38235ccd5cec1ad2.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

`总结`
> onStart执行了一次，框架启动时,onForkBefore/onChildProcess分别执行了三次后面的数字分别代表第几个进程从0开始，所有进程退出后执行的onEnd

### 4.实际应用
###### 4.1 导出2018年全年数据(较简单)
`需求`
> 2018年全年数据、按月分表、表名格式order-20181、写入文件data.txt

` 需求分析`
>1.先按每个进程处理1个月的数据导出进行分配任务
>2.查库
3.存入文件
4.邮件或短信通知

`代码实现`
```
<?php
/**
 * @CreateTime:   2019/5/19 上午11:12
 * @Author:       yuzhao  <tuzisir@163.com>
 * @Copyright:    copyright(2019) yuzhao all rights reserved
 * @Description:  导出2018年数据(过程写的可能有点复杂，目的是为了介绍更复杂的业务怎么使用)
 */

class EasyStudy implements CallBackInter {

    /**
     * 开始回调
     *
     * @return mixed
     * CreateTime: 2019/5/18 上午9:56
     */
    public function onStart()
    {
        // TODO: Implement onStart() method.
    }

    /**
     * 每个fock之前的回调
     *
     * @param $data
     * @return mixed
     * CreateTime: 2019/5/18 上午9:57
     */
    public function onForkBefore($data)
    {
        // TODO: Implement onForkBefore() method.
        // 这里的return可以将数据返回到相应进程中
        return array(
            'table_name' => 'order-2018'.($data['id']+1)
        );
    }

    /**
     * 子进程处理回调
     *
     * @param $data
     * @return mixed
     * CreateTime: 2019/5/18 上午9:57
     */
    public function onChildProcess($data)
    {
        // TODO: Implement onChildProcess() method.
        // 拼装sql
        $sql = "select * from {$data['table_name']}";
        // 查询(假逻辑)
        $res = $sql;
        FileTool::instance()->wFile("data.txt", $res);
    }

    /**
     * 结束回调
     *
     * @return mixed
     * CreateTime: 2019/5/18 上午9:57
     */
    public function onEnd()
    {
        // TODO: Implement onEnd() method.
        var_dump('发邮件');
    }
}
```
`执行结果`
> 数据顺序混乱是因为进程调度先后问题
```
select * from order-20184
select * from order-20185
select * from order-20186
select * from order-20182
select * from order-20187
select * from order-20181
select * from order-20183
select * from order-20188
select * from order-201810
select * from order-20189
select * from order-201812
select * from order-201811
```

###### 4.2 导出2018年全年数据(去重)
`需求`
> 导出2018年全年数据，进行去重，存入data.txt文件

`需求分析`
> 1.先按每个进程处理1个月的数据导出进行分配任务
> 2.+1个进程用管道方式接收1步骤进程中数据(总共13个进程)
> 3.接收完毕后进行去重写入文件


`代码实现`
```
<?php
/**
 * @CreateTime:   2019/5/19 下午3:39
 * @Author:       yuzhao  <tuzisir@163.com>
 * @Copyright:    copyright(2019) yuzhao all rights reserved
 * @Description:  导出2018年数据，管道接收、去重
 */

class Export2018DataOnlyTest implements CallBackInter {

    /**
     * 管道名称
     *
     * @var string
     * CreateTime: 2019/5/19 下午3:46
     */
    private static $pipeName = 'only';

    /**
     * 开始回调
     *
     * @return mixed
     * CreateTime: 2019/5/18 上午9:56
     */
    public function onStart()
    {
        // TODO: Implement onStart() method.
        // 初始化管道
        PipeTool::instance()->iniPipe(self::$pipeName);
    }

    /**
     * 每个fock之前的回调
     *
     * @param $data
     * @return mixed
     * CreateTime: 2019/5/18 上午9:57
     */
    public function onForkBefore($data)
    {
        // TODO: Implement onForkBefore() method.
        // 这里的return可以将数据返回到相应进程中
        return array(
            'table_name' => 'order-2018'.($data['id']+1)
        );
    }

    /**
     * 子进程处理回调
     *
     * @param $data
     * @return mixed
     * CreateTime: 2019/5/18 上午9:57
     */
    public function onChildProcess($data)
    {
        // TODO: Implement onChildProcess() method.
        // 用于查询数据
        if ($data['id'] < 12) {
            // 拼装sql
            $sql = "select * from {$data['table_name']}";
            // 查询(假逻辑)
            $res = $sql;
            PipeTool::instance()->wPipe(self::$pipeName, $res);
        } else if ($data['id'] === 12) { // 负责接收所有数据、去重
            $allData = array();
            while (true) {
                // 阻塞方式从管道中获取数据
                $res = PipeTool::instance()->gPipe(self::$pipeName);
                $res=str_replace("\n","",$res);
                $allData[] = $res;
                // 全部进程导出完毕退出
                if (count($allData) === 12) {
                    break;
                }
            }
            // 去重
            $allData = array_unique($allData);
            // 写入文件
            FileTool::instance()->wFile("data.txt", $allData);
        }

    }

    /**
     * 结束回调
     *
     * @return mixed
     * CreateTime: 2019/5/18 上午9:57
     */
    public function onEnd()
    {
        // TODO: Implement onEnd() method.
        var_dump('发送通知邮件');
    }
}
```

`结果`
```
➜  ProcessesExport git:(master) ✗ php start.php start

                           EasyExport
-----------------------------------------------------------------
进程ID          开始时间               结束时间           状态
75079     2019-05-19 16:00:48     2019-05-19 16:00:49     stop
75080     2019-05-19 16:00:48     2019-05-19 16:00:49     stop
75081     2019-05-19 16:00:48     2019-05-19 16:00:50     stop
75082     2019-05-19 16:00:48     2019-05-19 16:00:50     stop
75083     2019-05-19 16:00:48     2019-05-19 16:00:50     stop
75084     2019-05-19 16:00:48     2019-05-19 16:00:51     stop
75085     2019-05-19 16:00:48     2019-05-19 16:00:51     stop
75086     2019-05-19 16:00:48     2019-05-19 16:00:50     stop
75087     2019-05-19 16:00:48     2019-05-19 16:00:49     stop
75088     2019-05-19 16:00:48     2019-05-19 16:00:51     stop
75089     2019-05-19 16:00:48     2019-05-19 16:00:51     stop
75090     2019-05-19 16:00:48     2019-05-19 16:00:51     stop
75091     2019-05-19 16:00:48     2019-05-19 16:00:51     stop
进程退出%
```

`文件内容`
```
select * from order-20184
select * from order-20181
select * from order-20182
select * from order-20185
select * from order-20183
select * from order-20186
select * from order-20187
select * from order-20188
select * from order-20189
select * from order-201810
select * from order-201811
select * from order-201812
```

###### 3.往往我们导数据的时候都是依赖自己的项目(只针对低版本php、因为没有命名空间的概念)、直接引入项目目录加载文件

`代码实现`
```
<?php
/**
 * @CreateTime:   2019/5/19 下午3:39
 * @Author:       yuzhao  <tuzisir@163.com>
 * @Copyright:    copyright(2019) yuzhao all rights reserved
 * @Description:  依赖自己的项目
 */
// 引入后代码中可使用项目中的功能(方法)
include_once '头文件.php';

class Export2018DataOnlyTest implements CallBackInter {
·
·
·
}
```