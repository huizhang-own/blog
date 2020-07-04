#MAC Iterm 支持命令行翻译中英文

###1.简介
> 遇到不认识的单词或需要翻译的中文还得打开浏览器百度翻译等进行翻译，今天来介绍自想的命令行翻译法。`借用了github开源代码，自己改动了一下`

###2. 效果
![image.png](https://upload-images.jianshu.io/upload_images/10306662-2d198edeee04b793.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

###3.实现步骤
> clone 代码到/Users/tuzisir/language/php/tool `我自己的目录`
```
git clone git@github.com:tuzisir-php/dic-in-php.git
```

> 自定义系统命令

```
sudo vi ~/.bash_profile

顶部加入 alias fy="php /Users/tuzisir/language/php/tool/dic-in-php/dic.php"

sudo vi ~/.zshrc

最底部加入 source ~/.bash_profile

执行 source ~/.zshrc
```

> 这样就可以随时随地 fy  XXX 了
