<?php
namespace app\controller;

use app\BaseController;

use EasyWeChat\Factory;
use app\Request;
use app\model\Users;
use think\db\Where;
use think\facade\Log;
use think\facade\Validate;
class Login extends BaseController
{
    public function index(Request $request){
        $code = $request->param('code');
        $iv = $request->param('iv');
        $encryptedData = $request->param('encryptedData');

        $data = [
            'code' => $code ,
            'iv' => $iv ,
            'encryptedData' => $encryptedData ,
        ];
        $validate = Validate::rule([
            'code'  => 'require',
            'iv' => 'require',
            'encryptedData' => 'require',
        ]);
        if (!$validate->check($data))
            return retJson(1, $validate->getError());

        /* 小程序登录 */
        return $this->wxLogin($data);
    }

    public function wxLogin(array $params){
        $config = [
            'app_id' => config("wechat.app_id"),
            'secret' => config("wechat.app_secret"),
            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
        ];
        $app = Factory::miniProgram($config);

        $reqData = $app->auth->session($params['code']);
        Log::info($reqData);
        if (!isset($reqData['openid']) || !isset($reqData['session_key']) || isset($reqData['errcode'])){
            return retJson(1,'返回数据字段不完整');
        }
        $session_key = $reqData['session_key'];
        $decryptedData = $app->encryptor->decryptData($session_key, $params['iv'], $params['encryptedData']);
        Log::info($decryptedData);

        //saveUserInfo
       return $this->saveUserInfo($decryptedData, encryptSha256($session_key), $session_key);

    }

    private function saveUserInfo(array $userInfo, string $skey, string $session_key)
    {
        //官方推荐使用的方法，先查找后更新，系统自动判断是否存在数据，有则更新，无则插入
        $user = Users::where('uid', $userInfo['openId'])->findOrEmpty();
        $create_time = date("Y-m-d H:i:s", time());
        $balance = $user->isEmpty() ? config('wechat.credit') : $user->ubalance;
        $user->save([
            'uid'  =>  $userInfo['openId'],
            'create_time' => $create_time,
            'uname' =>  $userInfo['nickName'],
            'ugender' => $userInfo['gender'],
            'uaddress' => $userInfo['province'].','.$userInfo['country'],
            'update_time' =>  $create_time,
            'ubalance' =>  $balance,
            'skey' =>  $skey,
            'sessionkey' => $session_key,
            'uavatar' => $userInfo['avatarUrl'],
        ]);
        $userInfo['balance'] = $balance;
        $reqData = [
            'userInfo' => $userInfo,
            'skey' => $skey,
        ];
        return retJson(0, 'error', $reqData);
    }
}