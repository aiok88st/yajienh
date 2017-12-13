<?php
namespace app\index\controller;
use think\Db;
use app\index\model\Commont;
use app\index\model\Praise;
use think\Request;

use think\Controller;
class Pc extends Controller{
    public function msglist(Commont $commont,Praise $praise){  //留言列表
        $list=Db::table('admin_msg')
            ->alias('a')
            ->join('hy_member_open b','a.member_id = b.member_id','LEFT')
            ->where('a.status',1)
            ->field('a.*,b.open_face,b.open_name')
            ->order('a.updateTime desc,a.id desc')->select();
        $data=[];

        foreach ($list as $k=>$v){
            $v['face']=$v['open_face'];
            $v['name']=$v['open_name'];
            $v['images']=unserialize($v['images']);
            $v['time']=format_date($v['addTime']);
            $v['PraiseList']=$praise
                ->where('msgId',$v['id'])
                ->order('id asc')
                ->select();
            $v['commont']=$commont->where('msgId',$v['id'])->order('id asc')->select();

            if(!empty($v['PraiseList']) || !empty($v['commont'])){
                $v['interaction']=true;
            }else{
                $v['interaction']=false;
            }
            $data[$k]=$v;
        }
        return json([
            'last_page'=>1,
            'list'=>$data
        ]);
    }
}
?>