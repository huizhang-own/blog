#JS 获取当前方法名

```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<span onclick="tuzisir()">点我</span>
</body>
</html>
<script>
    function tuzisir() {
        var tmp = arguments.callee.toString();
        var re = /function\s*(\w*)/i;
        var matches = re.exec(tmp);//方法名
        var id=matches[1];
        alert(id);
    }
</script>
```