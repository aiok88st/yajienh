<?php

namespace app\index\controller;

use think\Controller;
use think\Exception;
use think\Request;
use app\index\model\Member;
use app\index\model\Log;
class Index extends Fater
{
    public function _initialize(){
        parent::_initialize();
        if(!UID) {
            $this->redirect(url('Weixin/index'));
        }
    }

    public function index(Request $request,Member $member)
    {
        return view('',[
            'token'=>$request->token(),
        ]);
    }

    public function edit(Request $request,Member $member,Log $log){
        try {
            $data = $request->param();
            $result = $this->validate($data, [
                'phone|手机号码' => ['require', "regex:/^1[34578]{1}[0-9]{9}$/"],
            ]);
            if(true !== $result){
                // 验证失败 输出错误信息
                return rejson(0,$result);
            }
            $arr = $member->where('phone',$data['phone'])->find();
            if(!$arr)return rejson(0,'您查询的数据不存在');
            if($arr['status']==1)return rejson(0,'请不要重复签到');
            $m = ['status'=>1,'open_id'=>UID];
            $re = $member->where('phone',$data['phone'])->update($m);
            if($re){
                $content = $member::get($arr['id']);
                if($content['image'] != ''){
                    $image = __ROOT__.$content['image'];
                }else{
                    $image = $content['open_id'];
                }
                $name = $content['username'];
                $this->sendMsg($name,$image);
                return $log->admin_log(1,'签到成功','edit',$data,UID);
            }else{
                return $log->admin_log(0,'签到失败','edit',$data,UID);
            }
        } catch (\Exception $e) {
            return rejson(0,$e->getMessage());
        }
    }

    public function sendMsg($name,$image){
        try{
            // 指明给谁推送，为空表示向所有在线用户推送
            $to_uid = 123;
            // 推送的url地址，使用自己的服务器地址
            $push_api_url = "http://127.0.0.1:2121";
            $post_data = array(
                "type" => "publish",
                "content" => $name.";".$image,
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
