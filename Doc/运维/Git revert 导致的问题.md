#Git revert 导致的问题

### 1. 问题过程
> 将已开发好的功能合并到master

>![2F7891AFC602A505CD14D107C40624D6.jpg](https://upload-images.jianshu.io/upload_images/10306662-a8cd8fab1a6a1ca7.jpg?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

> 上线后发现有问题咋整、直接revert掉刚才的那次merge，也就是revert掉①那个点
`revert 的过程是将你合并的那个分支的所有改动，全部删除重新commit (覆盖)②，记住是commit，并不是撤销了你的合并和提交`

>![8CDA307835C972AF85B14DD073B3F509.jpg](https://upload-images.jianshu.io/upload_images/10306662-857d70665efb14af.jpg?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

> 然后继续在Feat-x 分支解决你的bug，其它同事继续往master提交（③④）,
这时候你解决了Bug打算再次合并⑤、发现和master没有diff了，急的你想各种骚操作。

>![28504E6BB7B815CF6269394E6C0ABF7A.jpg](https://upload-images.jianshu.io/upload_images/10306662-013361e1a533ac5b.jpg?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

1. 从master重新开一个分支、然后将Feat-x分支合并到新分支然后merge master，发现没有diff  `不可行`

2. 从Feat-x 重新开个分支合并到master `不可行`

3. 在master分支重新开个分支，将Feat-x分支改动的文件和代码复制到新分支,重新commit、然后merge到master `这种方式是没问题的，但是如果代码量很多，你慌不慌？

> 分析到3会发现、如果重新commit能解决问题，但是有什么办法不通过手动复制粘贴来解决呢。

### 解决过程

> 1.在master新开一个分支 -> 2.revert掉一个第一次revert的那个点(就是反向revert) -> 3.开始解决冲突(因为第一次revert后面可能会有别人的提交) -> 4.commit ->  5.将 Feat-x merge 过来 -> 重新发起对master的merge  

`关键第二步的操作`
```
git revert --no-commit 第一次revert的那个点的commit id
```

### 总结
> 正确使用revert能减少回滚代码的代价，但是还是需要了解revert究竟做了哪些工作。
