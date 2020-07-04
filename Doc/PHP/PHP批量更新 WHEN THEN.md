# PHP批量更新 WHEN THEN

```
/**
     * Description: 拼装sql
     * CreateTime: 2018/8/24 下午3:21
     * @param $contents eg: array(
                               250(表id，当然你也可以换成其它字段，但是需要改动一下代码 => 需要更新的内容)
                                  )
     * @return string
     */
    private function packSql( $contents) {
        //获取所有的id
        $ids = "'";
        $ids .= implode("','",array_keys($contents));
        //拼接批量更新sql语句
        $sql = "UPDATE 表名 SET ";
        //合成sql语句
        $sql .= "`content` = CASE `id` ";
        foreach ($contents as $key => $value) {
            $sql .= sprintf("WHEN %s THEN %s ", "'".$key."'", "'" . $value . "'");
        }
        $sql .= "END";
        //拼接sql
        $sql .= " WHERE  `id` IN ({$ids}')";
        return $sql;
    }
```
> 先看where条件，当id在这个$ids 里面的查找出来，再看set，会根据条件查出来的数据去case 里面对比，满足的将val赋值给content，但是切记如果sql语句过于长会导致更新失败，所以请根据实际情况拆分contents的数据大小，分段更新。