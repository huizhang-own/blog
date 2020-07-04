#JS-SDK QQ 登录

#1.QQ开放平台申请个人开发者和网站应用
[QQ开放平台](https://connect.qq.com/manage.html)
![image.png](https://upload-images.jianshu.io/upload_images/10306662-93bd541b4336a4df.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

![image.png](https://upload-images.jianshu.io/upload_images/10306662-e5d1ec9e266ef13c.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

![image.png](https://upload-images.jianshu.io/upload_images/10306662-cac3a79068ae02d2.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

#2.代码配置
> 引入
```
    <script src="https://qzonestyle.gtimg.cn/qzone/openapi/qc_loader.js" data-appid="1233211234567" data-redirecturi="回调地址就是上面的网站回调域" charset="utf-8">></script>
```
> 将按钮放置到你的页面中
```
<a href="#" id="qqLoginBtn"></a>
```
> js代码

```
//调用QC.Login方法，指定btnId参数将按钮绑定在容器节点中
    QC.Login({
        //btnId：插入按钮的节点id，必选
        btnId:"qqLoginBtn",
        //用户需要确认的scope授权项，可选，默认all
        scope:"all",
        //按钮尺寸，可用值[A_XL| A_L| A_M| A_S|  B_M| B_S| C_S]，可选，默认B_S
        size: "A_XL"
    }, function(reqData, opts){
        //根据返回数据，更换按钮显示状态方法
        var dom = document.getElementById(opts['btnId']),
            _logoutTemplate=[
                //头像
                '<span><img src="{figureurl}" class="{size_key}"/></span>',
                //昵称
                '<span>{nickname}</span>',
                //退出
                '<span><a style="color: #0a6cd6;" href="javascript:QC.Login.signOut();">退出</a></span>'
            ].join("");
        dom && (dom.innerHTML = QC.String.format(_logoutTemplate, {
            nickname : QC.String.escHTML(reqData.nickname), //做xss过滤
            figureurl : reqData.figureurl
        }));
    }, function(opts){ // 注销成功
        $('.searchbox').css({'padding-top':'1rem'});
        alert('QQ登录 注销成功');
    });
```
> 控制器`这里对于一些没有接触过的同学可能有些懵,但是记住跳转到控制器的这个回调方法，就是要处理自己的逻辑了。`
```
<?php
/**
 * User: yuzhao
 * CreateTime: 2018/10/21 下午10:03
 * Description:
 */
namespace app\blog\controller;

use think\Controller;
use think\facade\Request;
use tool\MyRequest;
use tool\MyResponse;
use app\blog\service\LoginService;

class LoginController extends Controller {

    use MyResponse;
    use MyRequest;

    /**
     * User: yuzhao
     * CreateTime: 2018/10/23 下午10:17
     * @return string
     * Description: qq login
     */
    public function qqLogin() {
        // Callback calls this method first.
        if (Request::isGet()) {
            return view();
        }
        // qq注册
        if (LoginService::instance()->qqLogin($this->postParams)) {
            return $this->sendMsg(200, '登录成功');
        }
        return $this->sendMsg(400, '登录失败');
    }
}
```
> 回调到的html页面,目的是拿到openid和access_token
```
{include file='/common/header' /}
<script type="text/javascript">
    QC.Login.getMe(function(openId, accessToken){
        var userInfo = {open_id:openId,access_token:accessToken};
        // 请求一下后台将openid 入库
        curlAjax("{:url('/blog/login/qqLogin')}", userInfo, 'commonReturn');
    });

    function commonReturn(data) {
        if (data.code !== 200) {
            QC.Login.signOut();
            tipMsg(data.code, data.msg,0,true);
        }
        closeView();
    }
</script>
```

 > service 层,目的是用access_token 和openid 得到用户基本信息入库。

```
<?php
/**
 * User: yuzhao
 * CreateTime: 2018/10/23 下午10:19
 * Description: login service
 */

namespace app\blog\service;

use app\common\model\UserModel;
use app\common\third_party_api\qq\QqApi;
use tool\MyCurl;
use tool\MyTime;

class LoginService {

    /**
     * User: yuzhao
     * CreateTime: 2018/10/23 下午10:27
     * @var
     * Description: user model
     */
    private $userModel;

    /**
     * LoginService constructor.
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/10/23 下午10:26
     * Description: back now obj
     */
    public static function instance() {
        return new LoginService();
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/10/23 下午10:20
     * Description: qq login
     */
    public function qqLogin($params) {
        // get user base info
        $qqUserInfo = QqApi::instance()->getUserInfo($params['access_token'], $params['open_id']);
        if ($qqUserInfo === false) {
            return false;
        }
        // pack condition and data
        $this->packConData($qqUserInfo, $params, $condition, $data);
        // find user info
        $userInfo = $this->userModel->findUser($condition);
        // Determine whether the user is disabled.
        if ($userInfo['status'] === 0) {
            return false;
        }
        // if user info is empty
        if (empty($userInfo)) {
            $data = array_merge($data, [
                'c_time'    => MyTime::getDataTime(),
                'qq_open_id'=> $params['open_id'],

            ]);
            if (!$this->userModel->addUser($data)) {
                return false;
            }
        } else {
            $this->userModel->updateUser($condition, $data);
        }
        return true;
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/10/24 上午10:26
     * @param $qqUserInfo
     * @param $params
     * @param $condition
     * @param $data
     * Description: pack register condition and data
     */
    private function packConData($qqUserInfo, $params, &$condition, &$data) {
        // check whether there is openid
        $condition = [
            'qq_open_id'    => $params['open_id']
        ];
        $data = [
            'u_time'    => MyTime::getDataTime(),
            'nickname'  => $qqUserInfo['nickname'],
            'city'      => $qqUserInfo['city'],
            'year'      => $qqUserInfo['year'],
            'province'  => $qqUserInfo['province']
        ];
        if ($qqUserInfo['gender'] === '女') {
            $data['gender'] = 0;
        }
    }
}
```

> 模型层省略，就是查找添加数据更新数据等操作。

> 里面用到的方法

`js 封装的ajax请求方法`
```
/**
 * 数据是否成功
 * @param  {String}  url       [请求的地址]
 * @param  {String}  data      [请求的数据]
 * @param  {String}  data      [传入的回调函数名称]
 * @param  {String}  type      [请求的类型]
 * @param  {String}  async     [异步传输还是同步]
 * @param  {String}  data_type [请求的数据类型]
 */
function curlAjax(url="", data="", call_back="result", type="POST", async=true, data_type="json"){
    layer.load();
    var return_result = 99;
    var is_result = is_result;
    var aj = $.ajax({
        type: type,
        url: url,
        async: async,
        dataType:data_type,
        data: data,//也可以是字符串链接"name=张三&sex=1"，建议用对象
        success: function(data){
            data = JSON.parse(data);
            layer.closeAll('loading');
            if(call_back != null){
                var call_back_func = eval(call_back);
                new call_back_func(data);
            }else{
                return_result = data;
            }
        }
    });
    return return_result;
}
```
`封装qq获取用户信息方法`
```
<?php
/**
 * User: 郭玉朝
 * CreateTime: 2018/10/24 上午9:58
 * Description:
 */

namespace app\common\third_party_api\qq;

use tool\MyCurl;

class QqApi {

    /**
     * QqApi constructor.
     */
    public function __construct()
    {
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/10/24 上午9:59
     * Description: get obj
     */
    public static function instance() {
        return new QqApi();
    }

    /**
     * User: yuzhao
     * CreateTime: 2018/10/24 上午9:59
     * Description: get qq user info
     */
    public function getUserInfo($accessToken, $openId) {
        $qqUserInfo = MyCurl::instance(config('myself.qq.qq_api.get_user_info').'?access_token='.
            $accessToken.'&oauth_consumer_key=' . config('myself.qq.app_id') .
            '&openid='.$openId)->get();
        if (empty($qqUserInfo)) {
            return false;
        }
        return json_decode($qqUserInfo, JSON_UNESCAPED_UNICODE);
    }
}
```

[GitHub项目地址](https://github.com/tuzisir-php/blog_tool)

#4.效果
![image.png](https://upload-images.jianshu.io/upload_images/10306662-f0a1aeba65507d61.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

![image.png](https://upload-images.jianshu.io/upload_images/10306662-0e0b534506e50362.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)

![image.png](https://upload-images.jianshu.io/upload_images/10306662-44da246983ae2b70.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240)



>仅仅学习记录一下，不好的地方还望指出。








