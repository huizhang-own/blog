#PHP探针

##1.代码
```java
<?php
header("content-Type: text/html; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
error_reporting(0);
ob_end_flush();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Pragma" content="No-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta http-equiv="cache-control" content="private" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PHP 探针 v1.0</title>
    <style type="text/css">
        <!--
        body{text-align:center;margin-top:20px;background-color:#a9b674;}
        #overview{width:700px;margin:0 auto;text-align:left;}
        a{text-decoration:underline;color:#992700;}
        .strong{color:#992700;}
        .basew{width:300px;}
        -->
    </style>
</head>
<body>
<div id="overview">
    <div id="copyright">
        <?php
        if (empty($_GET['typ'])){
            baseinfo();}
        else{
            switch ($_GET['typ']){
                case 'phpinfo':
                    phpinfoview();
                    break;
                case 'superinfo':
                    superinfo();
                    break;
                case 'baseinfo':
                    baseinfo();
                    break;
                default:
                    baseinfo();}
        }
        function getime()
        {
            $t = gettimeofday();
            return (float)($t['sec'] + $t['usec']/1000000);
        }
        function baseinfo(){
            echo '<h1>基本信息</h1>';
            $arr[]=array("Current PHP version:",phpversion());
            $arr[]=array("Zend engine version:",zend_version());
            $arr[]=array("服务器版本",$_SERVER['SERVER_SOFTWARE']);
            $arr[]=array("ip地址",$_SERVER['REMOTE_HOST']);//ip
            $arr[]=array("域名",$_SERVER['HTTP_HOST']);
            $arr[]=array("协议端口",$_SERVER['SERVER_PROTOCOL'].' '.$_SERVER['SERVER_PORT']);
            $arr[]=array("站点根目录",$_SERVER['PATH_TRANSLATED']);
            $arr[]=array("服务器时间",date('Y年m月d日,H:i:s,D'));
            $arr[]=array("当前用户",get_current_user());
            $arr[]=array("操作系统",php_uname('s').php_uname('r').php_uname('v'));
            $arr[]=array("include_path",ini_get('include_path'));
            $arr[]=array("Server API",php_sapi_name());
            $arr[]=array("error_reporting level",ini_get("display_errors"));
            $arr[]=array("POST提交限制",ini_get('post_max_size'));
            $arr[]=array("upload_max_filesize",ini_get('upload_max_filesize'));
            $arr[]=array("脚本超时时间",ini_get('max_execution_time').'秒');
            if (ini_get("safe_mode")==0){
                $arr[]=array("PHP安全模式(Safe_mode)",'off');}
            else{
                $arr[]=array("PHP安全模式(Safe_mode)",'on');}
            if (function_exists('memory_get_usage')){
                $arr[]=array("memory_get_usage",ini_get('memory_get_usage'));}
//$arr[]=array("可用空间",intval(diskfreespace('/')/(1024 * 1024))."M");
            echo'<table>';
            for($i=0;$i<count($arr);$i++)
            {
                $overview='<tr><td class="basew">'.$arr[$i][0].'</td><td>'.$arr[$i][1].'</td></tr>';
                echo $overview;
            }
            echo'</table>';
            echo '<h2>服务器性能测试</h2>';
            echo'<table><tr><td>服务器</td><td>整数运算<br />50万次加法(1+1)</td><td>浮点运算<br />50万次平方根(3.14开方)</td></tr>';
            echo'<tr><td>MKDuse的机子(P4 1.5G 256DDR winxp sp2)</td><td>465.08ms</td><td>466.66ms</td></tr>';
            $time_start=getime();
            for($i=0;$i<=500000;$i++);
            {$count=1+1;}
            $timea=round((getime()-$time_start)*1000,2);
            echo '<tr class="strong"><td>当前服务器</td><td>'.$timea.'ms</td>';
            $time_start=getime();
            for($i=0;$i<=500000;$i++);
            {sqrt(3.14);}
            $timea=round((getime()-$time_start)*1000,2);
            echo '<td>'.$timea.'ms</td></tr></table>';
            ?>
            <script language="javascript" type="text/javascript">
                function gettime()
                {
                    var time;
                    time=new Date();
                    return time.getTime();
                }
                start_time=gettime();
            </script>
        <?php
        echo '<h2>带宽测试</h2>';
        for ($i=0;$i<100;$i++){
            print "<!--1234567890#########0#########0#########0#########0#########0#########0#########0#########012345-->";}
        ?>
            <p id="dk"></p>
            <script language="javascript" type='text/javascript'>
                var timea;
                var netspeed;
                timea=gettime()-start_time;
                netspeed=Math.round(10/timea*1000);
                document.getElementByIdx("dk").innerHTML="向客户端发送10KB数据，耗时"+timea+"ms<br />您与此服务器的连接速度为"+netspeed+"kb/s";
            </script>
            <?php
            echo'<h2>已加载的扩展库(enable)</h2><div>';
            $arr =get_loaded_extensions();
            foreach($arr as $value){
                echo $value.'<br />';}
            echo'</div><h2>禁用的函数</h2><p>';
            $disfun=ini_get('disable_functions');
            if (empty($disfun)){
                echo'没有禁用</p>';}
            else{
                echo ini_get('disable_functions').'</p>';}
        }//关闭
        function superinfo(){
            echo'<h1>高级信息</h1><p>PHP_INI_USER 1 配置选项可用在用户的 PHP 脚本或Windows 注册表中<br> PHP_INI_PERDIR 2 配置选项可在 php.ini, .htaccess 或 httpd.conf 中设置 <br>PHP_INI_SYSTEM 4 配置选项可在 php.ini or httpd.conf 中设置 <br>PHP_INI_ALL 7 配置选项可在各处设置</p>';
            $arr1=ini_get_all();
            for ($i=0;$i<count($arr1);$i++)
            {
                $arr2=array_slice($arr1,$i,1);
                print_r($arr2);
                echo '<br />';
            }
        }
        function phpinfoview(){
            phpinfo();
        }
        ?>
    </div>
</body>
</html>
```

##2.效果
![image.png](https://upload-images.jianshu.io/upload_images/10306662-dee934a0ceb489e4.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

