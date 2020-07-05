#Elasticsearch 随机返回数据 API

```
{
  "from": 0,
  "size": 10,//返回十条数据
  "query": {
    "bool": {
      "must": {
        "term": {
          "level": 1//查询条件。
        }
      }
    }
  },
  "_source": {
    "includes": [
      "title"	//返回的字段
    ],
    "excludes": []
  },
  "sort": {//排序
    "_script": { // key原封不动
      "script": "Math.random()",//随机排序
      "type": "number", 
      "params": {}, // 如果这个参数报错直接干掉
      "order": "asc"
    }
  }
}
```