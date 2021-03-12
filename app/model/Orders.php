<?php

namespace app\model;

use think\Model;
use app\model\Users;

class Orders extends Model
{
    public function Users()
    {
        return $this->hasOne(Users::class,'uid','uid');
    }
}