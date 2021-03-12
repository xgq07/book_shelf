<?php

namespace app\controller;

use app\BaseController;
use app\Request;
use think\Config;
use think\facade\Db;
class User extends BaseController
{
    public function getBoughtBooks(Request $request)
    {
        $reqParams  = [
          'skey' => $request->param('skey'),
        ];

        $resData['list'] = Db::table('books')
            ->alias('b')
            ->join('orders o','b.bkid = o.bkid')
            ->join('users u', 'u.uid = o.uid')
            ->where('u.skey', $reqParams['skey'])
            ->select()
            ->toArray();

        return retJson(Config('statusCode.SUCCESS'),'ok', $resData);
    }
}
//select books.bkid,bkname,bkfile,bkcover from books right join orders on books.bkid=orders.bkid right join users on users.uid=orders.uid where users.skey=?