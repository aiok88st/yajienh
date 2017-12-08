<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
class Fater extends Controller
{
    public function _initialize()
    {
        $member_id=session('user')['user_id'];
//        define('UID',$member_id);
        define('UID',1);
        if(!UID){
            $this->redirect(url('weixin/index'));
        }

        if(Request::instance()->isPost()){
            $result = $this->validate(
                [
                    '__token__'=>input('post.__token__')
                ],
                [
                    '__token__|令牌数据'=>'require|token'
                ]);

            if(true !== $result){
                return ['code'=>0,'msg'=>$result];
            }
        }


    }
}
