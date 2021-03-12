<?php
namespace app\controller;

use app\BaseController;
use app\model\Comment as CommentModel;
use app\Request;
use think\facade\Validate;
use think\facade\Db;

class Comment extends BaseController
{

    public function write(Request $request)
    {
        $code = $request->param('bkid');
        $data = [
            'skey' => $request->param('skey')  ,
            'content' =>$request->param('content')   ,
            'bookid' => $request->param('bookid') ,
        ];
        $validate = Validate::rule([
            'skey'  => 'require',
            'content' => 'require',
            'bookid' => 'require',
        ]);
        if (!$validate->check($data))
            return retJson(1, $validate->getError());

        $sql = 'insert into comment (uid,uname,uavatar,bkid,bkname,ccontent)' .
               'select uid,uname,uavatar,?,(select bkname from books where bkid=?),? from users where users.skey=?';

        Db::execute($sql,[$data['bookid'],$data['bookid'],$data['content'],$data['skey']]);
        return retJson(Config('statusCode.SUCCESS'),'OK',[]);
    }
}
