# Easyswoole源码分析-12-定时器
### 1.简介
> 框架对原生的毫秒级定时器进行了封装，以便开发者快速调用 Swoole 的原生定时器，定时器类的命名空间为 EasySwoole\Component\Timer

### 2.知识点
> [1.设置定时器](https://wiki.swoole.com/wiki/page/480.html)
[2.清除定时器](https://wiki.swoole.com/wiki/page/415.html)
[3.延时定时器](https://wiki.swoole.com/wiki/page/319.html)

### 3.代码分析
> 最好对照es的手册进行分析。
```
class Timer
{
    use Singleton;
    /**
     * 定时器列表，key为swoole_timer_tick返回的id
     *
     * @var array
     * CreateTime: 2019/6/27 下午1:20
     */
    protected $timerList = [];

    /**
     * 定时器map，key为md5 name后的值
     *
     * @var array
     * CreateTime: 2019/6/27 下午1:21
     */
    protected $timerMap = [];

    /**
     * 添加定时器
     *
     * @param int $ms 时间
     * @param callable $callback 回调函数
     * @param null $name 自定义定时器名称
     * @return int swoole_timer_tick返回的id
     * CreateTime: 2019/6/27 下午1:22
     */
    function loop(int $ms, callable $callback, $name = null): int
    {
        // 添加定时器
        $id = swoole_timer_tick($ms, $callback);
        // 将定时器+入定时器列表
        $this->timerList[$id] = $id;
        // 如果自定义了定时器名称，则添加进map
        if ($name !== null) {
            $this->timerMap[md5($name)] = $id;
        }
        return $id;
    }

    /**
     * 根据定时器的id或名称清除定时器
     *
     * @param $timerIdOrName
     * @return bool
     * CreateTime: 2019/6/27 下午1:24
     */
    function clear($timerIdOrName): bool
    {
        // 判断map和定时器列表中是否存在此定时器
        if (!isset($this->timerMap[md5($timerIdOrName)]) && !isset($this->timerList[$timerIdOrName])) {
            return false;
        }
        // 判断参数是否为原始定时器id
        if (is_numeric($timerIdOrName)) {
            // 定时器列表中是否存在
            if (isset($this->timerList[$timerIdOrName])) {
                // 清理定时器
                swoole_timer_clear($timerIdOrName);
                // map中是否存在
                $key = array_search($timerIdOrName, $this->timerMap);
                if ($key !== null) {
                    // 清除相应map
                    unset($this->timerMap[$key]);
                }
                return true;
            }
        }
        $timerIdOrName = md5($timerIdOrName);
        // map中是否存在
        if (!isset($this->timerMap[$timerIdOrName])) {
            return false;
        }
        // 存在即删
        $id = $this->timerMap[$timerIdOrName];
        swoole_timer_clear($id);
        unset($this->timerList[$id]);
        unset($this->timerMap[$timerIdOrName]);
        return true;
    }

    /**
     * 清理所有定时器
     *
     * @return bool
     * CreateTime: 2019/6/27 下午1:34
     */
    function clearAll(): bool
    {
        // 遍历clear定时器
        foreach ($this->timerList as $id) {
            swoole_timer_clear($id);
        }
        // 释放相应存储
        $this->timerList = [];
        $this->timerMap = [];
        return true;
    }

    /**
     * 延时定时器
     *
     * @param int $ms
     * @param callable $callback
     * @return int
     * CreateTime: 2019/6/27 下午1:34
     */
    function after(int $ms, callable $callback): int
    {
        return swoole_timer_after($ms, $callback);
    }
}

```

