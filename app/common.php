<?php
// 应用公共文件
use EasyWeChat\Factory;

/**
 * 通用化API数据格式输出
 * @param string $result
 * @param array $data
 * @return \think\response\Json
 */
function retJson(int $result, string $msg,  $data=[])
{
    $ret = [
        'result' => $result,
        'msg' => $msg,
        'data' => $data
    ];
    return json($ret);
}


/**
 * 小程序加密生成小程序自己的用户登录标识
 * @param $data
 */
function encryptSha256($data)
{
    return hash('sha256', $data);//sha-256加密
}

function getWxMiniProgramFactory(){
    $config = [
        'app_id' => config("wechat.app_id"),
        'secret' => config("wechat.app_secret"),
        // 下面为可选项
        // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
        'response_type' => 'array',
    ];
    $app = Factory::miniProgram($config);
    return $app;
}

