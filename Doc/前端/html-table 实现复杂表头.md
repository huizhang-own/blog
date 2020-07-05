#html-table 实现复杂表头

```
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">   
<html xmlns="http://www.w3.org/1999/xhtml">   
<head>   
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />   
    <title>多表头表格</title>   
</head>   
<body>   
<table id="tab" cellpadding="1" cellspacing="1" border="1">   
<tr>   
	<th rowspan="2">序号</th>
	<th rowspan="2">监测位置</th>
	<th rowspan="2">供电通路</th>
	<th rowspan="2">供电电压</th>
	<th rowspan="2">负载电流</th>
	<th rowspan="2">雷击次数</th>
	<th rowspan="2">最近一次雷击时间</th>
	<th colspan="2">后备保护空开状态</th>
	<th rowspan="2">SPD损害数量</th>   
	<th colspan="2">输出空开状态</th>
</tr>   
<tr>   
	<th>B级</th> 
	<th>C级</th>
	<th>1路</th> 
	<th>2路</th> 	
</tr> 
<tr> <th rowspan="4">1</th>
</tr>  
<tr>   
    <th>1</th>   
    <th>78</th>   
    <th>96</th>   
    <th>67</th>   
    <th>98</th>   
    <th>88</th>   
    <th>75</th>   
    <th>94</th>   
    <th>69</th>   
    <th>23 </th>   
	<th>33 </th> 
</tr> 
<tr>
	<th colspan="2">提示建议</th>   
    <th colspan="2">智能防雷箱状态</th>   
    <th colspan="2">防雷箱型号</th>   
    <th colspan="3">防雷箱序列号</th>   
    <th colspan="2">防雷箱版本</th>
</tr>  
<tr>   
    <th colspan="2">建议整机按规程检测</th>   
    <th colspan="2">在线</th>   
    <th colspan="2">2018041201-035PF</th>   
    <th colspan="3">2018041201-256</th>   
    <th colspan="2">V1.0.0</th>   
</tr>    
</table>   
</body>   
</html>

```