<?php
namespace app\controller;

use app\BaseController;
use app\model\Comment as CommentModel;
use app\Request;
use think\facade\Validate;
use think\facade\Db;
use think\facade\Log;
class Comment extends BaseController
{

    public function write(Request $request)
    {
        $requestData = [
            'skey' => $request->param('skey')  ,
            'content' =>$request->param('content')   ,
            'bookid' => $request->param('bookid') ,
        ];
        $validate = Validate::rule([
            'skey'  => 'require',
            'content' => 'require',
            'bookid' => 'require',
        ]);
        if (!$validate->check($requestData))
            return retJson(1, $validate->getError());

        $app = getWxMiniProgramFactory();
        $result = $app->content_security->checkText($requestData['content']);
        if (!isset($result["errcode"]) || $result["errcode"] != 0)
            return retJson(Config('statuscode.FAIL'),'评论中含有敏感词，请重新评论！',[]);
        Log::info("comment result:");
        Log::info($result);
        $sql = 'insert into comment (uid,uname,uavatar,bkid,bkname,ccontent)' .
               'select uid,uname,uavatar,?,(select bkname from books where bkid=?),? from users where users.skey=?';

        Db::execute($sql,[$requestData['bookid'],$requestData['bookid'],$requestData['content'],$requestData['skey']]);
        return retJson(Config('statuscode.SUCCESS'),'OK',[]);
    }
}
