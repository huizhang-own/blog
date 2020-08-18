<?php
/**
 * @CreateTime:   2020/8/16 12:03 上午
 * @Author:       huizhang  <2788828128@qq.com>
 * @Copyright:    copyright(2020) Easyswoole all rights reserved
 * @Description:  邮件进程
 */
namespace Library\Process;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Smtp\Mailer;
use EasySwoole\Smtp\MailerConfig;
use EasySwoole\Smtp\Message\Text;
use Library\Config\FastCacheKeys;
use Swoole\Coroutine;
use Swoole\Process;
use EasySwoole\FastCache\Cache;
use Library\Comm\IniConfig;

class EmailProcess extends AbstractProcess {

    protected function run($arg)
    {
        go(function (){
//            Cache::getInstance()->enQueue(FastCacheKeys::QUEUE_EMAIL, [
//                'subject'=>123,
//                'body' => 456
//            ]);
            while (true)
            {
                $size = Cache::getInstance()->queueSize(FastCacheKeys::QUEUE_EMAIL);
                if (empty($size))
                {
                    Coroutine::sleep(30);
                    continue;
                }
                $data = [];
                for($i=0;$i<50;$i++)
                {
                    $emailInfo = Cache::getInstance()->deQueue(FastCacheKeys::QUEUE_EMAIL);
                    if (empty($emailInfo))
                    {
                        continue;
                    }
                    $data[] = $emailInfo;
                }

                if (!empty($data)) {
                    foreach ($data as $item)
                    {
                        go(function () use ($item){
                            $config = new MailerConfig();
                            $config->setServer('smtp.163.com');
                            $config->setPort('25');
                            $config->setSsl(false);
                            $config->setUsername(IniConfig::getInstance()->getConf('blog', 'email.username'));
                            $config->setPassword(IniConfig::getInstance()->getConf('blog', 'email.password'));
                            $config->setMailFrom('tuzisir@163.com');
                            $config->setTimeout(10);//设置客户端连接超时时间
                            $config->setMaxPackage(1024*1024*5);//设置包发送的大小：5M
                            $mailer = new Mailer($config);

                            $mimeBean = new Text();
                            $mimeBean->setSubject($item['subject']);
                            $mimeBean->setBody($item['body']);
                            $mailer->sendTo('2788828128@qq.com', $mimeBean);
                        });
                    }
                }
                Coroutine::sleep(30);
            }
        });
    }

    protected function onPipeReadable(Process $process)
    {
    }

    protected function onShutDown()
    {

    }

    protected function onException(\Throwable $throwable, ...$args)
    {
    }
}
