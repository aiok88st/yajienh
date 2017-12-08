<?php
namespace app\admin\model;
use think\Exception;
use think\Model;
class AdminLottery extends Model{
    protected $pk = 'id';
    protected $table = 'admin_lottery';
    public function getPrizeIdAttr($value){
        $prize=AdminPrize::get($value);
        return $prize['name'];
    }
    public function getMemberIdAttr($value){
        $open=AdminOpen::get($value);

        return '<img src="'.$open['open_face'].'" width="50" title="'.$open['open_id'].'" date-name="'.$open['open_name'].'"/>&nbsp;&nbsp;'.$open['open_name'];
    }
    public function getStatusAttr($value){
        $status=[
            0=>'未兑换',
            1=>'已兑换',
        ];
        return $status[$value];
    }

}