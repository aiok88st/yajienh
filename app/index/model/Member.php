<?php

namespace app\index\model;

use think\Model;
class Member extends Model
{
    //
    protected $pk = 'id';
    protected $table = 'admin_member';
    protected $field = ['username','phone','sex','store_name','province','city','area','addr','add_time','ip','status','open_id','image'];

    public function getOpenIdAttr($value)
    {
        $pro=Open::get($value);
        return $pro['open_face'];
    }

}