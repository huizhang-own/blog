#1.有人说
 > 今天写着写着代码用到了unset所以想整理一下.

>有的人说PHP的unset并不真正释放内存, 有的说, PHP的unset只是在释放大变量(大量字符串, 大数组)的时候才会真正free内存, 更有人说, 在PHP层面讨论内存是没有意义的.
也有人说：
unset()函数只能在变量值占用内存空间超过256字节时才会释放内存空间。
只有当指向该变量的所有变量（如引用变量）都被销毁后，才会释放内存。

#2.辟谣，测试环境php7.2
>第一个例子
```
$s=str_repeat('1',255); //产生由255个1组成的字符串
$m=memory_get_usage(); //获取当前占用内存
unset($s);
$mm=memory_get_usage(); //unset()后再查看当前占用内存
echo $m-$mm;
```
> 结果
```
➜  sites php index.php
320
```

> 第二个例子，和第一个例子一样，只不过产生10个
```
$s=str_repeat('1',10); //产生由255个1组成的字符串
$m=memory_get_usage(); //获取当前占用内存
unset($s);
$mm=memory_get_usage(); //unset()后再查看当前占用内存
echo $m-$mm;
```
> 结果
```
➜  sites php index.php
48
```
> 说明unset释放了内存，也并没有256字节的限制。

> 第三个例子
```
$s=str_repeat('1',256); //这和第二个例子完全相同
$p=&$s;
$m=memory_get_usage();
unset($s); //销毁$s
$mm=memory_get_usage();
echo $p."\n";
echo $m-$mm;
```
> 结果
```
➜  sites php index.php
1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111
0
```

> 说明这个例子并没有释放内存。

#3.php变量的内存分配
#####简介
>首先我们要知道php的内存分配是隐式的，并不像c语言那样显示调用内存分配API就会分配内存。
比如我们 定义变量：$i = 'How are you!';
隐式分配有两个过程：1.为变量分配内存，存入符号表。2 .为变量值分配内存。
PHP是一个弱类型，动态的脚本语言。所谓弱类型，就是说PHP并不严格验证变量类型(严格来讲，PHP是一个中强类型语言,这部分内容会在以后的文章中叙述)，在申明一个变量的时候，并不需要显示指明它保存的数据的类型：

```
  $var = 1; //int
  $var = "laruence"; //string
  $var = 1.0002; //float
  $var = array(); // array
  $var = new Exception('error'); //object;
```
>动态语言，就是说，PHP的语言结构在运行期是可以改变的，比如我们在运行期require一个函数定义文件，从而导致语言的函数表动态的改变。
所谓脚本语言，就是说，PHP并不是独立运行的，要运行PHP我们需要PHP解析器。PHP的执行是通过Zend engine(ZE, Zend引擎), ZE是用C编写的，大家都知道C是一个强类型语言，也就是说，在C中所有的变量在它被声明到最终销毁，都只能保存一种类型的数据。 那么PHP是如何在ZE的基础上实现弱类型的呢？

>在PHP中，所有的变量都是用一个结构-zval来保存的， 在Zend/zend.h中我们可以看到zval的定义：
```
typedef struct _zval_struct {
    zvalue_value value; // 值
    zend_uint refcount; // 赋值的次数
    zend_uchar type; // 存放类型
    zend_uchar is_ref; // 是否引用了0，1
  } zval;
```

> 首先介绍zvalue_value,其中zvalue_value是真正保存数据的关键部分，现在到了揭晓谜底的时候了，PHP是如何在ZE的基础上实现弱类型的呢？ 因为zvalue_value是个联合体(union),
```
typedef union _zvalue_value {
    long lval;
    double dval;
    struct {
        char *val;
        int len;
    } str;
    HashTable *ht;
    zend_object_value obj;
} zvalue_value;
```
> 那么这个结构是如何储存PHP中的多种类型的呢？
PHP中常见的变量类型有：
```
1. 整型/浮点/长整型/bool值 等等
2. 字符串
3. 数组/关联数组
4. 对象
5. 资源
```
> PHP根据zval中的type字段来储存一个变量的真正类型，然后根据type来选择如何获取zvalue_value的值，比如对于整型和bool值:
```
 zval.type = IS_LONG;//整形
 zval.type = IS_BOOL;//布尔值
```

>就去取zval.value.lval,对于bool值来说lval∈(0|1);
如果是双精度，或者float则会去取zval.value的dval。
而如果是字符串，那么:这个时候，就会取:
zval.value.str
而这个也是个结构，存有C分格的字符串和字符串的长度。
而对于数组和对象，则type分别对应IS_ARRAY, IS_OBJECT, 相对应的则分别取zval.value.ht和obj
比较特别的是资源，在PHP中，资源是个很特别的变量，任何不属于PHP内建的变量类型的变量，都会被看作成资源来进行保存，比如，数据库句柄，打开的文件句柄等等。 对于资源:
```
type = IS_RESOURCE
```
>这个时候，会去取zval.value.lval， 此时的lval是个整型的指示器， 然后PHP会再根据这个指示器在PHP内建的一个资源列表中查询相对应的资源,目前，你只要知道此时的lval就好像是对应于资源链表的偏移值。
```
 ZEND_FETCH_RESOURCE(con, type, zval *, default, resource_name, resource_type);
```
`借用这样的机制，PHP就实现了弱类型，因为对于ZE的来说，它所面对的永远都是同一种类型，那就是zval。
`

> php就是这样实现了弱类型，那变量内存究竟是如何分配的呢?
ZE是如何把我的变量var和内部结构zval联系起来的呢？
PHP内部都是使用zval来表示变量的，但是对于上面的脚本，我们的变量是有名字的, var。而zval中并没有相应的字段来体现变量名。

>如果你想到了PHP内部一定有一个机制，来实现变量名到zval的映射。
在PHP中，所有的变量都会存储在一个数组中(确切的说是hash table), 并且，PHP也是通过不同的数组来实现变量的作用域的。
当你创建一个变量的时候，PHP会为这个变量分配一个zval，填入相应的变量值，然后将这个变量的名字，和指向这个zval的指针填入一个数组中。然后，当你获取这个变量的时候，PHP会通过查找这个数组，获得对应的zval。

> 查看_zend_executor_globals结构(这个结构在PHP的执行器保存一些执行相关的上下文信息)

```
struct _zend_executor_globals {
 
     ....
    HashTable *active_symbol_table;/*活动符号表*/
    HashTable symbol_table;     /*全局符号表*/
 
    HashTable included_files;   
 
    jmp_buf *bailout;
    int error_reporting;
     .....
}
```
> 其中，全局符号表，保存了在顶层作用域(就是不在任何函数，对象内)的变量。每当调用一个函数(对象的方法)的时候，就会为这个函数创建一个活动符号表，所有在这个函数内定义的变量，都会保存在这个活动符号表中。
对,这就是PHP的变量作用域的实现方式! 举个列子

```
  <?php
     $var = "I am in the global symbol table";
    function sample($para){
        $var = "I am in the active symbol table";
          echo $var;
      }
    sample($var);
    echo $var;
  ?>
```
> 在函数sample外面的变量\$var,它会被填入全局符号表中，与他对应的有一个zval指针，这个zval保存了一个字符串”I am in the global symbol table”.
函数内的\$var, 它会被填入属于函数sample的活动符号表中，一样的，与他对应的zval中，保存着字符串”I am in the active symbol table
比较特殊的，就是函数sample的参数$para了，这个$para是保存在sample的活动符号表的，但是与他对应的zval指针，会指向一个保存一份全局变量$var的copy的zval(严格来讲不是copy，是引用，这个涉及到变量的copy on write机制，我会在以后介绍)。

> 我们来回顾：

```
struct _zval_struct {
        /* Variable information */
        zvalue_value value;             /* value */
        zend_uint refcount;
        zend_uchar type;        /* active type */
        zend_uchar is_ref;
};
```
> 其中的refcount和is_ref字段我们一直都没有介绍过，我们知道PHP是一个长时间运行的服务器端的脚本解释器。那么对于它来说，效率和资源占用率是一个很重要的衡量标准，也就是说，PHP必须尽量介绍内存占用率，考虑下面这段代码：

```
<?php
   $var = "How are you";
   $var_dup = $var;
   unset($var);
?>
```
> 第一行代码创建了一个字符串变量，申请了一个大小为12字节的内存，保存了字符串”how are you”和一个NULL(\0)的结尾。
第二行定义了一个新的字符串变量，并将变量var的值”复制”给这个新的变量。
第三行unset了变量var
这样的代码在我们平时的脚本中是很常见的，如果PHP对于每一个变量赋值都重新分配内存，copy数据的话，那么上面的这段代码公要申请18个字节的内存空间，而我们也很容易的看出来，上面的代码其实根本没有必要申请俩份空间，呵呵，PHP的开发者也看出来了：


>PHP中的变量是用一个存储在symbol_table中的符号名，对应一个zval来实现的，比如对于上面的第一行代码，会在symbol_table中存储一个值”var”, 对应的有一个指针指向一个zval结构，变量值”how are you”保存在这个zval中，所以不难想象，对于上面的代码来说，我们完全可以让”var”和”var_dup”对应的指针都指向同一个zval就可以了。PHP也是这样做的，这个时候就需要介绍我们之前一直没有介绍过的zval结构中的refcount字段了。
refcount,顾名思义，记录了当前的zval被引用的计数。
比如对于代码:

``
<?php
   $var = 1;
   $var_dup = $var;
?>
``
> 第一行，创建了一个整形变量，变量值是1。 此时保存整形1的这个zval的refcount为1。
第二行，创建了一个新的整形变量，变量也指向刚才创建的zval，并将这个zval的refcount加1，此时这个zval的refcount为2。

> 现在我们回头看文章开头的代码， 当执行了最后一行unset($var)以后，会发生什么呢？ 对，既是refcount减1，上代码：
```
<?php
   $var = "how are you";
   $var_dup = $var;
   unset($var);
?>
```

>这就是PHP的copy on write机制：
PHP在修改一个变量以前，会首先查看这个变量的refcount，如果refcount大于1，PHP就会执行一个分离的例程， 对于上面的代码，当执行到第三行的时候，PHP发现$var指向的zval的refcount大于1，那么PHP就会复制一个新的zval出来，将原zval的refcount减1，并修改symbol_table，使得$var和$var_dup分离(Separation)。这个机制就是所谓的copy on write(写时复制)。

> 现在我们知道，当使用变量复制的时候 ，PHP内部并不是真正的复制，而是采用指向相同的结构来尽量节约开销。那么，对于PHP中的引用，那又是如何实现呢？

```
<?php
   $var = "how are you";
   $var_ref = &$var;
   $var_ref = 1;
?>
```
> 这段代码结束以后，$var也会被间接的修改为1，这个过程称作(change on write:写时改变)。那么ZE是怎么知道，这次的复制是不需要Separation的呢？
这个时候就要用到zval中的is_ref字段了：
对于上面的代码，当第二行执行以后，$var所代表的zval的refcount变为2，并且同时置is_ref为1。
到第三行的时候，PHP先检查var_ref代表的zval的is_ref字段，如果为1，则不分离，大体逻辑示意如下：

```
if((*val)->is_ref || (*val)->refcount<2){
          //不执行Separation
        ... ;//process
  }
```
>但是：
```
<?php
   $var = "laruence";
   $var_dup = $var;
   $var_ref = &$var;
?>
```
> 对于上面的代码，存在一对copy on write的变量$var和$var_dup, 又有一对change on write机制的变量对$var和$var_ref，这个情况又是如何运作的呢？
当第二行执行的时候，和前面讲过的一样，$var_dup 和 $var 指向相同的zval， refcount为2.
当执行第三行的时候，PHP发现要操作的zval的refcount大于1，则，PHP会执行Separation, 将$var_dup分离出去，并将$var和$var_ref做change on write关联。也就是，refcount=2, is_ref=1;
所以不要怀疑unset的释放内存的能力，但这个释放不是C编程意义上的释放, 不是交回给OS.对于PHP来说, 它自身提供了一套和C语言对内存分配相似的内存管理API:
```
emalloc(size_t size);
efree(void *ptr);
ecalloc(size_t nmemb, size_t size);
erealloc(void *ptr, size_t size);
estrdup(const char *s);
estrndup(const char *s, unsigned int length);
```
> 这些API和C的API意义对应, 在PHP内部都是通过这些API来管理内存的.

>当我们调用emalloc申请内存的时候, PHP并不是简单的向OS要内存, 而是会像OS要一个大块的内存, 然后把其中的一块分配给申请者, 这样当再有逻辑来申请内存的时候, 就不再需要向OS申请内存了, 避免了频繁的系统调用.

>同样的, 在我们调用efree释放内存的时候, PHP也不会把内存还给OS, 而会把这块内存, 归入自己维护的空闲内存列表. 而对于小块内存来说, 更可能的是, 把它放到内存缓存列表中去(后记, 某些版本的PHP, 比如我验证过的PHP5.2.4, 5.2.6, 5.2.8, 在调用get_memory_usage()的时候, 不会减去内存缓存列表中的可用内存块大小, 导致看起来, unset以后内存不变,)但是 php5.5验证，会减少内存。.


