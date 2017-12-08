<?php
namespace app\admin\controller;


use think\Controller;
use think\Request;
use app\common\controller\Table;

use app\admin\model\AdminLottery;
use app\admin\model\AdminPrize;
use app\common\controller\Search;
use app\common\controller\Tool;

use app\common\controller\From;
class Lottery extends Fater{
    public function index(Table $table,AdminLottery $adminLottery,Search $search,AdminPrize $adminPrize,Tool $tool){
        $this->userauth('activity');
        $table->init($adminLottery);  //传入一个模型
        $table->editAction();  //禁用修改按钮
        $table->deleteAction(); //禁用删除按钮
        $table->createAction=false; //禁用添加按钮
        $table->column('code','编码'); //设置表单 （字段值，字段别名，按钮【值=>按钮的类】）
        $table->column('prize_id','奖品名称');
        $table->column('status','是否兑换',[
            '未兑换'=>'layui-btn-warm',
            '已兑换'=>'layui-btn-normal',
        ]);
        $table->column('member_id','中奖用户');
        $table->column('add_time','中奖时间');
        $table->column('name','姓名');
        $table->column('mobile','电话');
        $table->column('city','地区');
        $table->column('addres','地址');

        $table->searchs(function() use ($search,$adminPrize){
            $option=[
                ['value'=>0,'name'=>'请选择']
            ];
            $prize=$adminPrize->field('id,name')->select();
            $k=1;
            foreach ($prize as $key=>$v){
                $option[$k]['value']=$v['id'];
                $option[$k]['name']=$v['name'];
                $k++;
            }
            $search->set_name('prize_id')->rule('=')->option($option)->select();
            $search->set_name('name')->rule('LIKE')->text();

        },$search);

        return $table->start();
    }
    public function export(Request $request,From $from){
        $from->field('select')->option([
            ['value'=>11,'name'=>'1288元购GW-1561'],
            ['value'=>10,'name'=>'100代金券'],
            ['value'=>9,'name'=>'安迪人偶'],
            ['value'=>8,'name'=>'抱枕'],
            ['value'=>7,'name'=>'炫彩简约晾衣架'],
            ['value'=>6,'name'=>'垃圾桶'],
            ['value'=>5,'name'=>'落地晾衣架GW-5823'],
            ['value'=>4,'name'=>'智能晾衣机GW-1583']
        ])->render('prize_id','奖品');
        $from->field('l_date')->render('date','日期');
        $from->addAction(url('Lottery/save'));
        return $from->start();

    }
    public function save(Request $request,AdminLottery $adminLottery){
        $param=$request->param();
        $prize_id=$param['prize_id'];
        if(!$param['date']){
            $this->error('请选择日期',url('lottery/export'));
        }
        $date=date('Y-m-d',strtotime($param['date'])+86400);

        $list=$adminLottery->where('prize_id',$prize_id)
            ->where('add_time','between',[$param['date'],$date])
            ->select();
        $res=togbk("ID,微信昵称,微信ID,奖品类型,奖品编号,中奖时间,姓名,电话,城市,地址")."\r\n";
        $pre='/title="(.*?)"/';
        $pre2='/date-name="(.*?)"/';
        foreach ($list as $key=>$value){
            $id=togbk($value['id']);
            preg_match($pre,$value['member_id'],$open_id);

            preg_match($pre2,$value['member_id'],$open_name);

            $open_name=togbk($open_name[1]);
            $open_id=togbk($open_id[1]);
            $prize=togbk($value['prize_id']);
            $code=togbk($value['code']);
            $add_time=togbk($value['add_time']);
            $name=togbk($value['name']);
            $mobile=togbk($value['mobile']);
            $city=togbk($value['city']);
            $addres=togbk($value['addres']);
            $res .=$id.','.$open_name.','.$open_id.','.$prize.','.$code.','.$add_time.','.$name.','.$mobile.','.$city.','.$addres."\r\n";
        }
        $file_name='list-'.date('Y-m-d').'.csv';
        header("Content-type: text/html; charset=utf-8");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$file_name);
        header('Cache-Control: max-age=0');
        echo $res;
    }
   
}
