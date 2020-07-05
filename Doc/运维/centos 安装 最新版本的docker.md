#centos 安装 最新版本的docker

###1.第一种
> yum update

 
>  vim /etc/yum.repos.d/docker.repo

```
//添加以下内容

[dockerrepo]

name=Docker Repository

baseurl=https://yum.dockerproject.org/repo/main/centos/7/

enabled=1

gpgcheck=1

gpgkey=https://yum.dockerproject.org/gpg

 ```

> yum install docker-engine -y

###2.第二种
>查看CentOS的内核版本`Docker 要求 CentOS 系统的内核版本高于 3.10 ,查看CentOS的内核版本`
```
uname -a
```
>删除旧的版本的docker
```
sudo yum remove docker  docker-common docker-selinux docker-engine
```
> 安装需要的软件包， yum-util 提供yum-config-manager功能，另外两个是devicemapper驱动依赖的

```
sudo yum install -y yum-utils device-mapper-persistent-data lvm2
```
> 设置docker yum源
```
sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
```

>查看所有仓库中所有docker版本，并选择特定版本安装

```
yum list docker-ce --showduplicates | sort -r
```
> 安装docker
```
sudo yum install docker-ce  #由于repo中默认只开启stable仓库，故这里安装的是最新稳18.03.0.ce-1.el7.centos
$ sudo yum install <FQPN>  #安装指定的版本 例如：sudo yum install docker-ce.x86_64.0.18.03.0.ce-1.el7.centos
```
>启动docker
```
systemctl start docker
systemctl status docker
```
> 查看version
```
docker version
```
