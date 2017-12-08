<?php

namespace app\admin\model;

use think\Exception;
use think\Model;
class Member extends Model
{
    //
    protected $pk = 'id';
    protected $table = 'admin_member';
    protected $field = ['username','phone','sex','store_name','province','city','area','addr','add_time','ip','status','image'];
    protected $insert = ['ip','add_time'];
    protected $update = ['ip'];
    protected function setIpAttr()
    {
        return request()->ip();
    }
    protected function setAddTimeAttr(){
        return date('Y-m-d H:i:s');
    }
    protected function setSexAttr($value){
        $attr=[
            1=>'男',
            0=>'女',
            2=>'保密'
        ];
        return $attr[$value];
    }

    protected $veri=[
        'username|姓名' => 'require|max:25',
        'phone|手机号码' => ['require', "regex:/^1[34578]{1}[0-9]{9}$/"],
        'sex|性别' => 'require',
        'store_name|门店名' => 'require|max:255',
        'province|省' => 'require|max:255',
        'city|市' => 'require|max:255',
        'area|区' => 'require|max:255',
        'addr|详细地址' => 'require|max:255',
    ];


    public function getImageAttr($value){
        if($value != ''){
            return '<img src="__ROOT__'.$value.'" width="80" title="头像" date-name="头像"/>';
        }else{
            return '<img src="__ROOT__/uploads/user.png" width="80" title="头像" date-name="头像"/>';
        }
    }

    public function addData($param){
        try{
            $member=$this->where('phone',$param['phone'])->find();
            if($member){
                return rejson(0,'请不要重复添加');
            }
            $adminLog=new Log;
            $result=$this->allowField(true)->validate($this->veri)->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return rejson(0,$this->getError());
            }
            return $adminLog->admin_log(1,'提交成功','add',$param,UID);
        }catch (Exception $e){
            return rejson(0,$e->getMessage());
        }
    }

}