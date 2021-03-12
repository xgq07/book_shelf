<?php
namespace app\service\Book;

use app\model\Books;
use app\Request;

class Book
{
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
        return retJson(0, 'ok', $data);
    }
    /*
     * 格式化输出字段
     */
    private function formatResult($result)
    {
        if (empty($result))
            return [];

        $data = [
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
            return retJson(1, $validate->getError());

        $result = Books::findOrEmpty($bookid)->toArray();
        $this->formatResult($result);
        return retJson(0, 'ok', $result);
    }
}