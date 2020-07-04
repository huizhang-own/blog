#PHP异步执行脚本

##1.简介
> 上传文件时要对文件中的内容进行处理，但是这个处理是耗时的，所以异步执行php脚本。解决此问题有多种方式，队列、cron、线程等等或者php中的shell_exec 或者 exec等。
##2.使用pclose和popen
```
/**
     * Description: 异步请求脚本
     * CreateTime: 2018/8/21 下午5:22
     */
	private function synCurl() {
        $php = '/usr/local/bin/php';
        $execFile = ' php文件的路径';
        $params = "1 2 3"; // 传递的三个参数用$argv 来接收
        // 并且popen是异步的,返回进程通道的句柄，只能用pclose关闭
        $res = pclose(popen($php . $execFile . " >/dev/null 2>&1 &", 'r')); // >/dev/null /dev/null 被称为位桶(bit bucket)或者黑洞(black hole)。空设备通常被用于丢弃不需要的输出流，或作为用于输入流的空文件。
        if ($res) {
            return true;
        }
        return false;
    }
```
