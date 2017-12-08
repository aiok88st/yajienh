<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\common\controller\Table;
use app\common\controller\From;
use app\admin\model\Prize as AdminPrize;
class Prize extends Fater
{
    public function index(Table $table,AdminPrize $adminPrize){
        $this->userauth('activity');
        $table->init($adminPrize);

        $table->deleteAction();
        $table->column('id','ID');
        $table->column('name','奖品名称');
        $table->column('group','奖品类型',[
            '上市红利'=>'layui-btn-warm',
            '上市好礼'=>'layui-btn-normal',
            '代金券'=>''
        ]);
        $table->column('pro','中奖概率');
        $table->column('number','奖品数量');
        $table->column('dl','每日上限');
        $table->column('mld','单人数量');
        return $table->start();
    }
    public function create(Request $request,From $from,AdminPrize $adminPrize){
        $id=$request->param('id',null);
        $option=[
            ['value'=>1,'name'=>'上市好礼'],
            ['value'=>2,'name'=>'上市红利'],
            ['value'=>3,'name'=>'代金券'],
        ];
        $from->init($adminPrize);  //实例化一个类
        $from->id($id);
        $from->field('text')->render('name','奖品名称')->ruls('require|max:255');
        $from->field('number')->render('pro','中奖概率')->ruls('require|number');
        $from->field('radio')->option($option)->defaults(1)->render('group','奖品类型')->ruls('require|in:1,2,3');
        $from->field('number')->render('number','奖品数量')->ruls('require|number');
        $from->field('number')->render('dl','每日上限')->ruls('require|number');
        $from->field('number')->defaults(1)->render('mld','单人数量')->ruls('require|number');
        return $from->start();
    }
    public function save(Request $request,From $from,AdminPrize $adminPrize){
        $param=$request->param();
        return $from->save($param,$adminPrize);
    }
    public function dele(Request $request,From $from,AdminPrize $adminPrize){
        $param=$request->param()['ids'];

        return $from->dele($param,$adminPrize);
    }


}