<?php

namespace app\controller;

use app\BaseController;
use app\Request;
use app\validate\Book as bookValid;
use think\exception\ValidateException;
use app\model\Books;
use think\facade\Validate;
use app\model\Comment;
use app\model\Orders;
//use app\service\Book as BookService;

class Book extends BaseController
{
//    private $bookService;
//    public function __construct(BookService $bookService)
//    {
//        $this->bookService = $bookService;
//    }

    public function getBooks(Request $request)
    {
        $is_all = $request->param('is_all');
        try {
            validate(bookValid::class)->check([
                'is_all' => $is_all,
            ]);
        } catch (ValidateException $e) {
            return retJson(Config('statuscode.FAIL'), ['error' => $e->getError()]);
        } catch (\Exception $e) {
            return retJson(Config('statuscode.FAIL'), ['error' => $e->getMessage()]);
        }
        if ($is_all == 1)
            return $this->getAllBooks();
        else
            return $this->getBookById($request);
    }

    /**
     * 根据用户skey标识，查询用户是否购买书籍并返回评论列表
     */
    public function queryBook(Request $request)
    {
        //
        $data = [
            'bookid' => $request['bookid'] ,
            'skey' =>  $request['skey'] ,
        ];
        $validate = Validate::rule([
            'bookid'  => 'require',
            'skey' => 'require',
        ]);
        if (!$validate->check($data))
            return retJson(Config('statuscode.FAIL'), $validate->getError());


        //
        $count = Orders::hasWhere('Users', ['skey'=>$data['skey']])->select()->count();
        $retData['is_buy'] = $count == 0 ? 0 : 1;
        //
        $retData['lists'] = Comment::where('bkid', $data['bookid'])->select()->toArray();
        return retJson(Config('statuscode.SUCCESS'),"OK",$retData);
    }

    /**
     * 获取所有书籍信息
     */
    private function getAllBooks()
    {
        $result = Books::select()->toArray();
        $data = [];
        foreach ($result as $item) {
            array_push($data, $this->formatResult($item));
        }
        return retJson(Config('statuscode.SUCCESS'), 'ok', $data);
    }
    /*
     * 格式化输出字段
     */
    private function formatResult($result)
    {
        if (empty($result))
            return [];

        $data = [
            'book_id'=> $result['bkid'],
            'author' => $result['bkauthor'],
            'category' => $result['bkclass'],
            'cover_url' => $result['bkcover'],
            'file_url' => $result['bkfile'],
            'book_name' => $result['bkname'],
            'book_price' => $result['bkprice'],
            'book_publisher' => $result['bkpublisher'],
        ];

        return $data;
    }


    /**
     * 根据bookid获取当前书籍信息
     */
    private function getBookById(Request $request)
    {
        $bookid = $request->param('bookid');
        $validate = \think\facade\Validate::rule('bookid', 'require|number');
        $data = [
            'bookid' => $bookid
        ];

        if (!$validate->check($data))
            return retJson(Config('statuscode.FAIL'), $validate->getError());

        $result = Books::findOrEmpty($bookid)->toArray();
        $this->formatResult($result);
        return retJson(Config('statuscode.SUCCESS'), 'ok', $result);
    }
}