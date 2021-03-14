<?php

namespace app\controller;

use app\BaseController;
use app\model\Books;
use app\model\Users;
use app\Request;
use think\facade\Validate;
use app\model\Orders;

class Order extends BaseController
{

    public function buy(Request $request){
        $reqParams = [
            'skey'=>$request->param('skey'),
            'bookid'=>$request->param('bookid')
        ];

        $validate = Validate::rule([
            'skey'  => 'require',
            'bookid' => 'require',
        ]);
        if (!$validate->check($reqParams))
            return retJson(Config('statuscode.FAIL'), $validate->getError());


        //根据skey查询用户积分, 查出书籍所需购买积分
        $user = Users::where('skey', $reqParams['skey'])->findOrEmpty();
        if ($user->isEmpty())
            return retJson(Config('statuscode.FAIL'),'查找不到此用户信息', []);
        $balance = $user->ubalance;
        $uid = $user->uid;

        $book = Books::find($reqParams['bookid']);
        if ($book->isEmpty())
            return retJson(Config('statuscode.FAIL'),'找不到书籍信息', []);
        $bkPrice = $book->bkprice;

        //用户是否已经购买过书籍
        $orderCount = Orders::where('bkid', '=',  $reqParams['bookid'])->
                              where('uid', '=', $uid)->
                              count();
        if ($orderCount > 0)
            return retJson(Config('statuscode.FAIL'),'您已经兑换过此书籍了呀', []);

        //写入订单
        $order = new Orders();
        $order->uid = $uid;
        $order->oprice = $bkPrice;
        $order->bkid = $reqParams['bookid'];
        $order->save();
        //扣除积分
        if ($balance < $bkPrice)
            return retJson(Config('statuscode.FAIL'),'余额不足，无法兑换', []);
        $balance = $balance - $bkPrice;
        $user->ubalance = $balance;
        $user->save();
        //返回积分
        $retData = [
            'balance' =>$balance
        ];
        //返回评论
        return retJson(Config('statuscode.SUCCESS'), 'ok', $retData);
    }
}