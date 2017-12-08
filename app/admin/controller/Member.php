<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\common\controller\Table;
use app\common\controller\From;
use app\common\controller\Search;
use app\common\controller\Tool;
use app\admin\model\Member as MemberModel;
use app\admin\model\Log;
class Member extends Fater
{
    public function index(Table $table,MemberModel $member,Search $search,Tool $tool){
        $this->userauth('activity');
        $table->init($member);  //传入一个模型
        $table->editAction();  //禁用修改按钮
        $table->createAction=false; //禁用添加按钮
        $table->column('image','头像');
        $table->column('username','姓名');
        $table->column('phone','电话');
        $table->column('sex','性别');
        $table->column('store_name','门店名');
        $table->column('province','省');
        $table->column('city','市');
        $table->column('area','区');
        $table->column('addr','详细地址');
        $table->column('add_time','报名时间');
        $search->where=[
            'status'=>1
        ];
        $table->searchs(function() use ($search){
            $search->set_name('username','姓名')->rule('LIKE')->text();
            $search->set_name('phone','电话')->rule('=')->text();
        },$search);
        $table->tool(function() use ($tool){
            $tool->export(url('member/e_csv'));
        },$tool);
        return $table->start();

    }

    public function dele(Request $request,From $from,MemberModel $member){
        $param=$request->param()['ids'];
        return $from->dele($param,$member);
    }

    //导出数据
    public function e_csv(){
        $file_name='雅洁年会签到.csv';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$file_name);
        header('Cache-Control: max-age=0');
        //接收条件，查询表
        $user = db("member");
        $data='姓名,手机,性别,门店,省,市,区,详细地址'."\r\n";
        $arr = $user->where('status',1)->order('id desc')->select();
        if(!$arr){
            $data .='没有找到相应的数据'."\r\n";
        }
        foreach($arr as $k=>$v){
            $province=$this->strG($v['province']);
            $city=$this->strG($v['city']);
            $area=$this->strG($v['area']);
            $addr=$this->strG($v['addr']);
            $data .= "{$v['username']},{$v['phone']},{$v['sex']},{$v['store_name']},{$province},{$city},{$area},{$addr}"."\r\n";
        }
        return $data;
    }

    public function strG($data){
        $data=str_replace(',','，',$data);
        $data=str_replace("\r\n",'',$data);
        $data=str_replace("\r",'',$data);
        $data=str_replace("\n",'',$data);
        $data=str_replace("\"",'',$data);
        return $data;
    }

}