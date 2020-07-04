#Easyswoole源码分析-9-phar

### 1. 简介
> PHP5.3之后支持了类似Java的jar包，名为phar。用来将多个PHP文件打包为一个文件。
### 2.知识点
1. [spl标准库](https://segmentfault.com/a/1190000019197353?utm_source=tag-newest)
2. [phar的认识与使用](https://blog.csdn.net/Leroi_Liu/article/details/86293701)

3. [FilesystemIterator迭代器中的常量](https://www.php.net/manual/en/class.filesystemiterator.php#filesystemiterator.constants.skip-dots)

### 3. 代码分析
> phar 核心代码分析
```
public function exec(array $args): ?string
{
        $name = array_shift($args);
        if (empty($name)) {
            $name = 'easyswoole.phar';
        } else {
            $name = "{$name}.phar";
        }
        $phar = new \Phar($name);
        $pharConfig = Config::getInstance()->getConf('PHAR');
        $excludes = $pharConfig['EXCLUDE'] ?? [];
        // 递归目录文件遍历器，可实现列出所有目录层次结构，而不是只操作一个目录,第二个参数Skips dot files (. and ..).
        $rdi = new \RecursiveDirectoryIterator(EASYSWOOLE_ROOT, \FilesystemIterator::SKIP_DOTS);
        // 在RecursiveIterator迭代器上进行递归操作，同时执行过滤和回调操作，在找到一个匹配的元素之后会调用回调函数。
        $rcfi = new \RecursiveCallbackFilterIterator($rdi, function (\SplFileInfo $current, $key, $iterator) use ($excludes) {
            $ei = new \ArrayIterator($excludes);
            // 将phar 配置中的EXCLUDE中的文件、目录过滤
            foreach ($ei as $exclude) {
                if (is_file($exclude)) {
                    $fileFullPath = EASYSWOOLE_ROOT . '/' . ltrim($exclude, '/');
                    if ($current->getPathname() == $fileFullPath) {
                        return false;
                    }
                }
                if (is_dir($exclude)) {
                    $dirFullPath = EASYSWOOLE_ROOT . '/' . ltrim($exclude, '/');
                    if (substr($current->getPathname(), 0, strlen($dirFullPath)) == $dirFullPath) {
                        return false;
                    }
                }
            }
            return true;
        });
        $phar->buildFromIterator(new \RecursiveIteratorIterator($rcfi), EASYSWOOLE_ROOT);
        // setStub() 用来创建stub文件，stub文件用来告诉Phar在被加载时干什么, 返回字符串包含自定义引导加载程序（存根）内容的字符串
        $phar->setStub($phar->createDefaultStub('vendor/easyswoole/easyswoole/bin/easyswoole'));
        return "build {$name} finish";
}
```
![image.png](https://upload-images.jianshu.io/upload_images/10306662-f9add3535fcdd38f.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)
