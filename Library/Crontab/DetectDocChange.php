<?php

namespace Library\Crontab;

use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use EasySwoole\Utility\SnowFlake;
use Library\Comm\File;
use Library\Comm\IniConfig;
use Library\Model\ArticleInfoModel;
use Library\Model\MenusModel;

class DetectDocChange extends AbstractCronTask
{

    public static function getRule(): string
    {
        return '*/1 * * * *';
    }

    public static function getTaskName(): string
    {
        return  'DetectDocChange';
    }

    function run(int $taskId, int $workerIndex)
    {
        $trees = File::getInstance()->trees('/Users/yuzhao3/sites/blog/Doc');

        [$menus, $articlesInfo] = $this->getMenusAndArticlesInfo($trees);

        $this->updateMenus($menus);

        $this->updateArticlesInfo($articlesInfo);
    }

    private function updateArticlesInfo($articlesInfo)
    {
        $uuids = array_column($articlesInfo, 'uuid');

        $articlesInfoDb = ArticleInfoModel::create()->all();

        $articleInfoDbUuid = [];
        foreach ($articlesInfoDb as $articleInfoDb)
        {
            $articleInfo = $articleInfoDb->toArray();
            $articleInfoDbUuid[] = $articleInfo['uuid'];
        }

        $intersect = array_intersect($uuids, $articleInfoDbUuid);

        foreach ($articlesInfo as $item)
        {
            if (in_array($item['uuid'], $intersect, false)) {
                ArticleInfoModel::create()->update([
                    'title' => $item['title']??'',
                    'description' => $item['description']??'',
                    'author' => $item['author']??'',
                    'cover' => $item['cover']??'cover.png',
                    'utime' => date('Y-m-d H:i:s'),
                    'menu_name' => $item['menu_name'],
                    'file_name' => $item['file_name'],
                ], ['uuid' =>  $item['uuid']]);
            } else {
                ArticleInfoModel::create()->data([
                    'title' => $item['title']??'',
                    'description' => $item['description']??'',
                    'author' => $item['author']??'',
                    'cover' => $item['cover']??'cover.png',
                    'utime' => date('Y-m-d H:i:s'),
                    'uuid' => $item['uuid'],
                    'menu_name' => $item['menu_name'],
                    'file_name' => $item['file_name'],
                ], false)->save();
            }
        }

        foreach ($articleInfoDbUuid as $uuid)
        {
            if (!in_array($uuid, $intersect, false)) {
                ArticleInfoModel::create()->destroy([
                    'uuid' => $uuid
                ]);
            }
        }

        return true;
    }

    private function updateMenus(array $menus)
    {
        $menusModel = MenusModel::create();
        $menusDb = $menusModel->all();
        if (empty($menusDb)) {
            return false;
        }

        $menuDbArr = [];
        foreach ($menusDb as $menu)
        {
            $menu = $menu->toArray();
            $menuDbArr[] = $menu['menu_name'];
        }

        $intersect = array_intersect($menus, $menuDbArr);

        foreach ($menus as $menu)
        {
            if (!in_array($menu, $intersect, false)) {
                $menusModel->data([
                    'menu_name' => $menu
                ]);
                $menusModel->save();
            }
        }

        foreach ($menuDbArr as $menuDb)
        {
            if (!in_array($menuDb, $intersect, false)) {
                $menusModel->destroy([
                    'menu_name' => $menuDb
                ]);
            }
        }

        return true;
    }

    private function articleInfo(string $file) : array
    {
        $articleInfo = [];
        $fileArr = explode('/', $file);
        $articleInfo['file_name'] = $fileArr[count($fileArr)-1];
        $ext = substr(strrchr($file, '.'), 1);
        if ($ext === 'md')
        {
            $fileResource = fopen($file, 'a+');
            while (!feof($fileResource))
            {
                $line = trim(fgets($fileResource));
                if (empty($line)) {
                    continue;
                }
                $lineArr = explode(':', $line);

                if (count($lineArr)>1 && in_array($lineArr[0], ['uuid', 'title', 'description', 'cover', 'author'], false))
                {
                    $content = substr($line, strlen($lineArr[0])+1);
                    if ($lineArr[0] === 'description')
                    {
                        $content = mb_substr($content, 0, 150, 'utf-8');
                    }
                    $articleInfo[$lineArr[0]] = trim($content);
                }

                if ($line === IniConfig::getInstance()->getConf('blog', 'markdown.separator')) {
                    if (!isset($articleInfo['uuid'])) {
                        $articleInfo['uuid'] = SnowFlake::make();
                        rewind($fileResource);
                        $articleContent = file_get_contents($file);
                        file_put_contents($file, 'uuid:'.$articleInfo['uuid'].PHP_EOL.$articleContent);
                    }
                    break;
                }
            }
            fclose($fileResource);
        }

        return $articleInfo;
    }

    private function getMenusAndArticlesInfo(array $trees)
    {
        $menus = [];
        $articlesInfo = [];
        foreach ($trees as $menu => $files)
        {
            if (is_dir($menu))
            {
                $menuArr = explode('/', $menu);
                $menus[] = $menuArr[count($menuArr)-1];
                foreach ($files as $file)
                {
                    $articleInfo = $this->articleInfo($file);
                    $articleInfo['menu_name'] = $menuArr[count($menuArr)-1];
                    if (!empty($articleInfo)) {
                        $articlesInfo[] = $articleInfo;
                    }
                }
            }
        }

        return [$menus, $articlesInfo];
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        echo $throwable->getMessage();
    }
}