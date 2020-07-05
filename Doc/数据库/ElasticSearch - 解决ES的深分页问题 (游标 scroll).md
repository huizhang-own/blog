#ElasticSearch - 解决ES的深分页问题 (游标 scroll)

####1.简介
>ES为了避免深分页，不允许使用分页(from&size)查询10000条以后的数据，因此如果要查询第10000条以后的数据，要使用ES提供的 scroll(游标) 来查询

> 假设取的页数较大时(深分页)，如请求第20页，Elasticsearch不得不取出所有分片上的第1页到第20页的所有文档，并做排序，最终再取出from后的size条结果作爲最终的返回值

> 假设你有16个分片，则需要在coordinate node彙总到 shards* (from+size)条记录，即需要16*(20+10)记录后做一次全局排序

> 所以，当索引非常非常大(千万或亿)，是无法使用from + size 做深分页的，分页越深则越容易OOM，即便不OOM，也很消耗CPU和内存资源

> 因此ES使用index.max_result_window:10000作爲保护措施 ，即默认 from + size 不能超过10000，虽然这个参数可以动态修改，也可以在配置文件配置，但是最好不要这麽做，应该改用ES游标来取得数据

####2.scroll游标原理

> 可以把 scroll 理解爲关系型数据库里的 cursor，因此，scroll 并不适合用来做实时搜索，而更适用于后台批处理任务，比如群发

> scroll 具体分爲初始化和遍历两步

> 初始化时将所有符合搜索条件的搜索结果缓存起来，可以想象成快照

> 在遍历时，从这个快照里取数据

> 也就是说，在初始化后对索引插入、删除、更新数据都不会影响遍历结果

> 游标可以增加性能的原因，是因为如果做深分页，每次搜索都必须重新排序，非常浪费，使用scroll就是一次把要用的数据都排完了，分批取出，因此比使用from+size还好

####3.具体实例

> 初始化

> 请求

> 注意要在URL中的search后加上scroll=1m，不能写在request body中，其中1m表示这个游标要保持开启1分钟

> 可以指定size大小，就是每次回传几笔数据，当回传到没有数据时，仍会返回200成功，只是hits裡的hits会是空list

> 在初始化时除了回传_scroll_id，也会回传前100笔(假设size=100)的数据

> request body和一般搜索一样，因此可以说在初始化的过程中，除了加上scroll设置游标开启时间之外，其他的都跟一般的搜寻没有两样 (要设置查询条件，也会回传前size笔的数据)

```
POST 127.0.0.1:9200/my_index/_search?scroll=1m `注意这里的地址`
{
    "query":{
        "range":{
            "createTime": {
                "gte": 1522229999999
            }
        }
    },
    "size": 1000
}
```

> 返回结果

```

{
    "_scroll_id": "DnF1ZXJ5VGhlbkZldGNoBQAAAAAAfv5-FjNOamF0Mk1aUUhpUnU5ZWNMaHJocWcAAAAAAH7-gBYzTmphdDJNWlFIaVJ1OWVjTGhyaHFnAAAAAAB-_n8WM05qYXQyTVpRSGlSdTllY0xocmhxZwAAAAAAdsJxFmVkZTBJalJWUmp5UmI3V0FYc2lQbVEAAAAAAHbCcBZlZGUwSWpSVlJqeVJiN1dBWHNpUG1R",
    "took": 2,
    "timed_out": false,
    "_shards": {
        "total": 5,
        "successful": 5,
        "skipped": 0,
        "failed": 0
    },
    "hits": {
        "total": 84,
        "max_score": 1,
        "hits": [
            {
                "_index": "video1522821719",
                "_type": "doc",
                "_id": "84056",
                "_score": 1,
                "_source": {
                    "title": "三个院子",
                    "createTime": 1522239744000
                }
            }
            ....99 data
        ]
    }
}
```
>遍历数据

> 请求

> 使用初始化返回的_scroll_id来进行请求，每一次请求都会继续返回初始化中未读完数据，并且会返回一个_scroll_id，这个_scroll_id可能会改变，因此每一次请求应该带上上一次请求返回的_scroll_id

> 要注意返回的是_scroll_id，但是放在请求裡的是scroll_id，两者拼写上有不同

> 且每次发送scroll请求时，都要再重新刷新这个scroll的开启时间，以防不小心超时导致数据取得不完整

```
POST 127.0.0.1:9200/_search/scroll?scroll=1m `注意地址`
{
    "scroll_id": "DnF1ZXJ5VGhlbkZldGNoBQAAAAAAdsMqFmVkZTBJalJWUmp5UmI3V0FYc2lQbVEAAAAAAHbDKRZlZGUwSWpSVlJqeVJiN1dBWHNpUG1RAAAAAABpX2sWclBEekhiRVpSRktHWXFudnVaQ3dIQQAAAAAAaV9qFnJQRHpIYkVaUkZLR1lxbnZ1WkN3SEEAAAAAAGlfaRZyUER6SGJFWlJGS0dZcW52dVpDd0hB"
}
```
> 返回结果

> 如果没有数据了，就会回传空的hits，可以用这个判断是否遍历完成了数据
```
{
    "_scroll_id": "DnF1ZXJ5VGhlbkZldGNoBQAAAAAAdsMqFmVkZTBJalJWUmp5UmI3V0FYc2lQbVEAAAAAAHbDKRZlZGUwSWpSVlJqeVJiN1dBWHNpUG1RAAAAAABpX2sWclBEekhiRVpSRktHWXFudnVaQ3dIQQAAAAAAaV9qFnJQRHpIYkVaUkZLR1lxbnZ1WkN3SEEAAAAAAGlfaRZyUER6SGJFWlJGS0dZcW52dVpDd0hB",
    "took": 2,
    "timed_out": false,
    "_shards": {
        "total": 5,
        "successful": 5,
        "skipped": 0,
        "failed": 0
    },
    "hits": {
        "total": 84,
        "max_score": null,
        "hits": []
    }
}
```
> 优化scroll查询

> 在一般场景下，scroll通常用来取得需要排序过后的大笔数据，但是有时候数据之间的排序性对我们而言是没有关系的，只要所有数据都能取出来就好，这时能够对scroll进行优化

> 初始化

> 使用_doc去sort得出来的结果，这个执行的效率最快，但是数据就不会有排序，适合用在只想取得所有数据的场景

```
POST 127.0.0.1:9200/my_index/_search?scroll=1m
{
    "query": {
        "match_all" : {}
    },
    "sort": [
        "_doc"
        ]
    }
}
```
> 清除scroll

> 虽然我们在设置开启scroll时，设置了一个scroll的存活时间，但是如果能够在使用完顺手关闭，可以提早释放资源，降低ES的负担

```
DELETE 127.0.0.1:9200/_search/scroll
{
    "scroll_id": "DnF1ZXJ5VGhlbkZldGNoBQAAAAAAdsMqFmVkZTBJalJWUmp5UmI3V0FYc2lQbVEAAAAAAHbDKRZlZGUwSWpSVlJqeVJiN1dBWHNpUG1RAAAAAABpX2sWclBEekhiRVpSRktHWXFudnVaQ3dIQQAAAAAAaV9qFnJQRHpIYkVaUkZLR1lxbnZ1WkN3SEEAAAAAAGlfaRZyUER6SGJFWlJGS0dZcW52dVpDd0hB"
}

```


####4.转发连接
> https://blog.csdn.net/weixin_40341116/article/details/80821655