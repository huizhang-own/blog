#SELECT list is not in GROUP BY clause and contains nonaggregated column

### 1. 问题描述
> MySQL 5.7.5及以上功能依赖检测功能。如果启用了ONLY_FULL_GROUP_BY SQL模式（默认情况下），MySQL将拒绝选择列表，HAVING条件或ORDER BY列表的查询引用在GROUP BY子句中既未命名的非集合列，也不在功能上依赖于它们。`说白了就是select  后面不能直接跟非在group by 后面出现的字段 `

`错误示例`
```
select id,name,age from student group by age 
```

### 2. 解决方法
> 这种只是屏蔽报错，并不能真正解决问题, 但有些业务确实需要上面那种错误示例。

```
select any_value(id) as id, any_value(name) as name, `age` from student group by age
```
