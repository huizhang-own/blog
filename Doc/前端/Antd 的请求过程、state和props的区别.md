#Antd 的请求过程、state和props的区别

### 1. 简介
> 很多小伙伴可能刚接触antd的时候会遇到这种困惑，这一坨代码到底是个什么鬼，不是类似MVC的开发方式吗，为毛我把代码都写到了page(view)层呢


### 2.项目目录结构
```php
--src
   --pages
     --State
       --models  // model层
         --state.js
       --State.js  // view层
   --services
     --state.js // service层
```
### 3. 请求流程

###### 1. 请求过程
![image.png](https://upload-images.jianshu.io/upload_images/10306662-2861e7fbb0cca4eb.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

###### 2. 详细代码
> Pages: State.js

```
import React, { PureComponent } from 'react';
import { connect } from 'dva/index';

// 这里的state对应models层namespace名称
@connect(({ state, loading }) => ({
  state,
  loading: loading.models.state,
}))
class State extends PureComponent {
  constructor(props) {
    super(props);
      this.state = {
          msg:'我是pages层的state'
      };

  }

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch({
      type: `state/fetch`, // 请求models层的fetch方法
      payload: { name: 'zhangsan send' }, // 携带过去的参数
    });
  }

  render() {
    // 这里取出models层中reducers中的save方法返回过来的数据
    const {state} = this.props;
    console.log(state);
    // 这里取出pages层中的state
      const pages = this.state;
      console.log(pages);
    return <div>state</div>;
  }
}

export default State;



```

> Models: state.js，处理数据尽量放到model层完成
```
// 对应services层的state.js
import { getTestData } from '@/services/state';

export default {
  namespace: 'state', // models 层的命名空间
  state: {
    msg:'我是models层中的state'
  },

  effects: {
    // 接收page请求的fetch
    *fetch({ payload }, { call, put }) {
      // 调用services层的getTestData方法，payload参数传递过去
      const response = yield call(getTestData, payload);
      console.log(response);
      // 将数据返回给reducers中的save方法
      yield put({
        type: 'save',
        payload: response,
      });
    },
  },

  reducers: {
    save(state, action) {
      // 最终将state和data的数据合并返回给pages层，在page层中取出的话为用 this.props，参数名称为models层的命名空间名称
      return {
        ...state, // model层中的state
        data: action.payload, // effects中的fetch方法的返回数据
      };
    },
  },
};

```

> Services: state.js
```
import { stringify } from 'qs';
import request from '@/utils/request';

export async function getTestData(params = {}) {
  return request(`http://127.0.0.1:8080/index.php?${stringify(params)}`);
}
```

> index.php

```
<?php
       header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers:content-type");
        header("Access-Control-Allow-Methods:POST, GET, OPTIONS");
$data = ['李四收到了张三的数据'.$_GET['name']];
var_dump(json_encode($data));die;

```

![image.png](https://upload-images.jianshu.io/upload_images/10306662-7747d96a22ed055c.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)


### 3.结语
> 通过上面的分析可以了解整个的执行流程，但是注意的是：models  层中的state和pages中的state不是同一个，如果pages中想要用models层中的state要通过reducers里面的方法返回，并在pages中使用this.props 取出。

