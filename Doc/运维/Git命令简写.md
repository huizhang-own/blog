#Git命令简写

###1.简写对应表
|    简写     |      完整命令      |
|:-------------: |:-------------:|
| g      | git | 
| gst     | git status      |
|    gd      |  git diff                    |
|     gdc     | git diff --cached                     |
|    gdv      |   git diff -w "$@" \| view -                   |
|   gl       |            git pull          |
|   gup       |          git pull --rebase            |
|      gp    |               git push       |
|    gc      |       git commit -v               |
|   gc!       |          git comit -v --amend            |
|      gca    |       git commit -v -a               |
|   gca!       |          git commit -v -a --amend            |
|       gcmsg   |         git commit -m             |
|    gco      |         git checkout             |
|     gcm     |          git checkout master            |
|    gr      |         git remote             |
|    grv      |           git remote -v           |
|     grmv     |        git remote rename              |
|    grrm      |          git remote remove            |
|     grset     |       git remote set-url               |
|  grup        |      git remote update                |
|     grbi     |          git rebase -i            |
|    grbc      |     git rebase --continue                 |
|     brba     |        git rebase --abort              |
|     gb     |          git branch           |
|     gba     |          git branch -a            |
|gcount|git shortlog -sn|
|gcl|git config --list|
|gcp|git cherry-pick|
|glg|git log --stat --max-count=10|
|glgg|git log --graph --max-count=10|
|glgga|git log --graph --decorate --all|
|glo|git log --oneline --decorate --color|
|glog|git log --oneline --decorate --color --graph|
|gss|git status -s|
|ga|git add|
|gm|git merge|
|grh|git reset HEAD|
|grhh|git reset HEAD --hard|
|gclean|git reset --hard && git clean -dfx|
|gwc|git whatchanged -p --abbrev-commit --pretty=medium|
|gpoat|git push origin --all && git push origin --tags|
|gmt|git mergetool --no-prompt|
|gg|git gui citool|
|gga|git gui citool --amend|
|gk|gitk --all --branches|
|gsts|git stash show --text|
|gsta|git stash|
|gstp|git stash pop|
|gstd|git stash drop|
|grt|cd $(git rev-parse --show-toplevel \|\| echo \|\| echo ".")|
|git-svn-dcommit-push|git svn dcommit && git push github master:svntrunk|
|gsr|git svn rebase|
|gsd|git svn dcommit|

> 编辑.gitconfig文件，可以copy下边的直接替换，根据喜好可以增减和修改

```
# This is Git's per-user configuration file.
[user]
# Please adapt and uncomment the following lines:
#       name = hanya
#       email = hanya@MacBook-Pro
 
[merge]
    summary = true
    tool = vimdiff
[diff]
    renames = copy
[color]
    diff = auto
    status = true
    branch = auto
    interactive = auto
    ui = auto
    log = true
[status]
    submodulesummary = -1
[mergetool "vimdiff"]
    cmd = "vim --noplugin \"$PWD/$MERGED\" \
          +\":split $PWD/$REMOTE\" +\":set buftype=nowrite\" \
          +\":vertical diffsplit $PWD/$LOCAL\" +\":set buftype=nowrite\" \
          +\":vertical diffsplit $PWD/$BASE\" +\":set buftype=nowrite\" \
          +\":wincmd l\""
[format]
    numbered = auto
[alias]
    co = checkout
    ci = commit
    st = status
    pl = pull
    ps = push
    dt = difftool
    l = log --stat
    cp = cherry-pick
    ca = commit -a
    b = branch
    pso = push origin
    plo = pull origin
    cm = commit -m
    gst = git status
    gd = git diff
    gl = git pull
    gp = git push
    glo = git pull origin
    gpo = git push origin
    gcm = git common -m
    gc = git checkout
    gcm = git checkout master
    gcd = git checkout develop
    gb = git branch
    ga = git add .
[user]
        name = hanya
        email = hanya-mac@xiaomi.com
[core]
        excludesfile = /Users/hanya/.gitignore_global
[difftool "sourcetree"]
        cmd = opendiff \"$LOCAL\" \"$REMOTE\"
        path =
[mergetool "sourcetree"]
        cmd = /Applications/SourceTree.app/Contents/Resources/opendiff-w.sh \"$LOCAL\" \"$REMOTE\" -ancestor \"$BASE\" -merge \"$MERGED\"
        trustExitCode = true

```