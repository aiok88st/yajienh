<?php

namespace app\index\model;

use think\Model;
class RedList extends Model{
    protected $pk = 'id';
    protected $field = ['user_id','add_time','amount'];
    public function getUserIdAttr($value){
        $open=new Open();
        $res_open=$open->where('id',$value)->field('open_name,open_face')->find();
        $member=new Member();
        $res_member=$member->where('user_id',$value)->field('addr,username,phone,image')->find();
        return [
            'open_name'=>$res_open['open_name'],
            'face'=>!empty($res_member['image'])?$res_member['image']:$res_open['open_face'],
            'user_name'=>$res_member['username']
        ];
    }
}
?>