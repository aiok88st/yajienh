<?php
namespace app\index\model;
use think\Model;
class Commont extends Model
{

    protected $pk = 'id';
    protected $table = 'admin_commont';
    public function getMemberIdAttr($value)
    {
        $open=db('admin_open')->where('id',$value)->value('open_name');
        return [
            'member_id'=>$value,
            'open_name'=>$open
        ];
    }
    public function getToMemberIdAttr($value)
    {
        $open=db('admin_open')->where('id',$value)->field('open_name,open_face')->find();
        return [
            'member_id'=>$value,
            'open_name'=>$open['open_name'],
            'open_face'=>$open['open_face']
        ];
    }
}
?>