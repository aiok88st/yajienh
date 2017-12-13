<?php

namespace app\index\controller;

use think\Exception;
use think\Request;
use app\index\model\RedList;
use app\index\model\Open;
class Game extends Fater{
    public function _initialize(){
        parent::_initialize();
        if(!UID) {
            $this->redirect(url('Weixin/index'));
        }
    }
    public function honbao(){
        return view();
    }
    public function put_honbao(Request $request,RedList $list){
        try{
//            $value=$request->param()['value'];

            $value=input('post.value',2);
            $result = $this->validate(['value'=>$value], [
                'value|金额' => ['require','float']
            ]);
            if(true !== $result){
                // 验证失败 输出错误信息
                return rejson(0,$result);
            }
            $c_amount=$list->sum('amount');
            $set=cache('set');
            if($c_amount>=$set){
                $this->sendMsg(0);
                return rejson(0,'红包抢完了');
            }
            $list->allowField(true)->insert([
                'amount'=>$value,
                'user_id'=>UID
            ]);

            $this->sendMsg(($set-($c_amount+$value)));
            return rejson(1,'抢红包成功');
        }catch (Exception $e){
            return rejson(0,$e->getMessage());
        }
    }
    public function rank_honbao(RedList $list){
        //单个红包金额最大的
        $maximum=$list->order('amount desc')->find();
        //最多
        $most=$list->group('user_id')->field('count(id) as a,user_id')->order('a desc')->find();
        //总金额最多
        $total=$list->group('user_id')->field('sum(amount) as a,user_id')->order('a desc')->find();
        return json([
            'maximum'=>$maximum,
            'most'=>$most,
            'total'=>$total,
        ]);
    }
    public function money(){

    }
    public function put_money(){

    }
    public function sendMsg($num){
        try{
            // 指明给谁推送，为空表示向所有在线用户推送
            $to_uid = 5250;
            // 推送的url地址，使用自己的服务器地址
            $push_api_url = "http://127.0.0.1:2121";
            $post_data = array(
                "type" => "publish",
                "content" => $num,
                "to" => $to_uid,
            );
            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $push_api_url );
            curl_setopt ( $ch, CURLOPT_POST, 1 );
            curl_setopt ( $ch, CURLOPT_HEADER, 0 );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
            curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Expect:"));
            $return = curl_exec ( $ch );
            curl_close ( $ch );
        }catch(Exception $e){
            var_dump($e->getMessage());
        }
    }
}
?>