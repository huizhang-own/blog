#MAC 自动登录ssh

* 在某目录下新建文件夹ssh`/Users/tuzisir/language/linux/ssh` 这个是我的目录
* 新建三个文件
```
login.sh      password.lst  ssh_login.exp
```
`login.sh`
```
#!/bin/bash


direc=`dirname $0`
function color(){
    blue="\033[0;36m"
    red="\033[0;31m"
    green="\033[0;32m"
    close="\033[m"
    case $1 in
        blue)
            echo "$blue $2 $close"
        ;;
        red)
            echo "$red $2 $close"
        ;;
        green)
            echo "$green $2 $close"
        ;;
        *)
            echo "Input color error!!"
        ;;
    esac
}

function copyright(){
    echo ""
    color green "   SSH 登录平台   "
    echo ""
}

function underline(){
    echo "-----------------------------------------"
}

function main(){

while [ True ];do

    underline
    echo "序号 |       主机      | 说明"
    underline
    awk 'BEGIN {FS=":"} {printf("\033[0;31m% 3s \033[m | %15s | %s\n",$1,$2,$6)}' $direc/password.lst
    underline
    read -p '[*] 选择主机: ' number
    pw="$direc/password.lst"
    ipaddr=$(awk -v num=$number 'BEGIN {FS=":"} {if($1 == num) {print $2}}' $pw)
    port=$(awk -v num=$number 'BEGIN {FS=":"} {if($1 == num) {print $3}}' $pw)
    username=$(awk -v num=$number 'BEGIN {FS=":"} {if($1 == num) {print $4}}' $pw)
    passwd=$(awk -v num=$number 'BEGIN {FS=":"} {if($1 == num) {print $5}}' $pw)

    case $number in
        [0-9]|[0-9][0-9]|[0-9][0-9][0-9])
            echo $passwd | grep -q ".pem$"
            RETURN=$?
            if [[ $RETURN == 0 ]];then
                ssh -i $direc/keys/$passwd $username@$ipaddr -p $port
                echo "ssh -i $direc/$passwd $username@$ipaddr -p $port"
            else
                expect -f $direc/ssh_login.exp $ipaddr $username $passwd $port
            fi
        ;;
        "q"|"quit")
            exit
        ;;

        *)
            echo "Input error!!"
        ;;
    esac
done
}

copyright
main
```

`ssh_login.exp`
```
#!/usr/bin/expect -f
set TARGET [lindex $argv 0]
set USER [lindex $argv 1]
set PASSWD [lindex $argv 2]
set PORT [lindex $argv 3]
set timeout 10

spawn ssh $USER@$TARGET -p $PORT
expect {
    "*yes/no" {send "yes\r"; exp_continue}
    "*password:" {send "$PASSWD\r"}
}
interact
```
`password.lst 只有这个文件需要改`
```
序号:ip地址:22:账号:密码:简介
```

* 自定义mac命令

```
vi ~/.profile
alias sl="/bin/sh /Users/tuzisir/language/linux/ssh/login.sh"
. ~/.profile
```

* 效果
![image.png](https://upload-images.jianshu.io/upload_images/10306662-800927698ed3bdf8.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

