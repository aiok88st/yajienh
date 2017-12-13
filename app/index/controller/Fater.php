<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
class Fater extends Controller
{
    public function _initialize()
    {
        $member_id=session('user')['user_id'];
        define('UID',$member_id);
//        define('UID',1);
        if(!UID){
            $this->redirect(url('weixin/index'));
        }

        $set=cache('set');
        if(!$set){
            $value=db('set')->where('id',1)->value('value');
            cache('set',unserialize($value));
        }
    }
    public function upload($data){
        try{
            if(empty($data)){
                return urlencode('error');
            }
            $ip = $_SERVER["REMOTE_ADDR"];
            if($ip!=="120.78.206.118"){
                return urlencode('error');
            }

            $filePath = base64_decode($data);
            $toDay=date('Ymd');

            if(!file_exists("uploads/{$toDay}")){
                mkdir("uploads/{$toDay}/",0777,true);
            }
            $thumbNname=rand(999,10000) . date('YmdHis') . rand(0, 9999) . '.' . 'jpg';
            $keys = "uploads/{$toDay}/".$thumbNname;
            $content=$this->SearchBOM($filePath);
            file_put_contents($keys,$content);
            return ['code'=>1,'path'=>$keys];
        }catch(\Exception $e){
            return ['code'=>0,'msg'=>$e->getMessage()];
        }
    }
    public function SearchBOM($string) {
        if(substr($string,0,3) == pack("CCC",0xef,0xbb,0xbf)){
            $content = substr($string,3);
            return $content;
        }
        return $string;
    }
}
