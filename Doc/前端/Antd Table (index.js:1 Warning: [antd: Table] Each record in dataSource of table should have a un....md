#Antd Table (index.js:1 Warning: [antd: Table] Each record in dataSource of table should have a un...

### 1.简介
> 在用Antd的Table组件时报错, 那是因为数据源中默认必须每条记录的key为主键
```
Warning: [antd: Table] Each record in dataSource of table should have a unique `key` prop, or set `rowKey` of Table to an unique primary key
```

```json
[
{
	"key": 1,
	"name": "zhangsan"
}, {
	"key": 2,
	"name": "zhangsan"
}, {
	"key": 3,
	"name": "zhangsan"
}
]
```

### 2.解决方法
```
<Table columns={columns} dataSource={data} rowKey="改成自己的主键id" />
```