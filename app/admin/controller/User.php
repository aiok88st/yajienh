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
class User extends Fater
{
    public function index(Table $table,MemberModel $member,Search $search,Tool $tool){
        $this->userauth('activity');
        $table->init($member);  //传入一个模型
        $table->column('image','头像');
        $table->column('username','姓名');
        $table->column('phone','电话');
        $table->column('sex','性别');
        $table->column('store_name','门店名');
        $table->column('province','省');
        $table->column('city','市');
        $table->column('area','区');
        $table->column('addr','详细地址');
        $table->column('add_time','添加时间');
        $table->searchs(function() use ($search){
            $search->set_name('username','姓名')->rule('LIKE')->text();
            $search->set_name('phone','电话')->rule('=')->text();
        },$search);
        $table->tool(function() use ($tool){
            $tool->export_add(url('add_csv'));
        },$tool);
        return $table->start();

    }
    public function create(Request $request,MemberModel $member){
        $id=$request->param('id');
        if($id == ''){
            $this->redirect(url('add'));
        }
        $data = $member::get($id);
        $image = db('member')->where('id',$id)->field('image')->find();
        $province = db('region')->where('pid',1)->select();
        return view('',[
            'token'=>$request->token(),
            'province'=>$province,
            'data'=>$data,
            'city'=>$data->city,
            'area'=>$data->area,
            'image'=>$image['image'],
        ]);
    }

    public function add(Request $request){
        $province = db('region')->where('pid',1)->select();
        return view('',[
            'token'=>$request->token(),
            'province'=>$province,
        ]);
    }
    public function insert(Request $request,MemberModel $member){
        try {
            $data = $request->param();
            return $member->addData($data);
        } catch (\Exception $e) {
            return rejson(0,$e->getMessage());
        }
    }


    public function save(Request $request,Log $log){
        try {
            $data = $request->param();
            $result = $this->validate(
                $data,
                [
                    'username|姓名' => 'require|max:25',
                    'phone|手机号码' => ['require', "regex:/^1[34578]{1}[0-9]{9}$/"],
                    'sex|性别' => 'require',
                    'store_name|门店名' => 'require|max:255',
                    'province|省' => 'require|max:255',
                    'city|市' => 'require|max:255',
                    'area|区' => 'require|max:255',
                    'addr|详细地址' => 'require|max:255',
                ]);
            if (true !== $result) {
                // 验证失败 输出错误信息
                return json(['code'=>0, 'msg'=>$result,'token'=>Request::instance()->token()]);
            }
            $m = db('member')->where('id',$data['id'])->find();
            if(!$m) return rejson(0,'您查询的数据不存在');
            unset($data['__token__']);
            unset($data['token']);
            unset($data['file']);
            if($data['sex'] == 1){
                $data['sex'] = '男';
            }elseif($data['sex'] == 0){
                $data['sex'] = '女';
            }else{
                $data['sex'] = '保密';
            }
            $re = db('member')->where('id',$data['id'])->update($data);
            if($re !== false){
                return $log->admin_log(1,'修改成功','edit',$data,UID);
            }else{
                return $log->admin_log(0,'修改失败','edit',$data,UID);
            }
        } catch (\Exception $e) {
            return rejson(0,$e->getMessage());
        }
    }

    //省市区三级联动
    public function getAddrs(){
        $list = getLocation('region',input('pid'),input('type'));
        echo json_encode($list);
    }


    public function dele(Request $request,From $from,MemberModel $member){
        $param=$request->param()['ids'];
        return $from->dele($param,$member);
    }

    //导入数据
    public function add_csv(Request $request){
        return view('',[
            'token'=>$request->token(),
        ]);
    }
    public function save_csv(Request $request){
        if($_FILES['file']['error'] == 0){
            $member = db('member');
            $path = $_FILES['file']['tmp_name'];
            $handle=fopen($path,"r");
            while($data=fgetcsv($handle,10000,",")){
                foreach($data as $k=>$v){
                    $data[$k] = mb_convert_encoding($v, "UTF-8", "GBK");
                }
                $content[] = $data;
            }
            fclose($handle);
            unset($content[0]);
            foreach($content as $key=>$val){
                $arr = [
                    'username'=>$val[0],
                    'phone'=>$val[1],
                    'sex'=>$val[2],
                    'store_name'=>$val[3],
                    'province'=>$val[4],
                    'city'=>$val[5],
                    'area'=>$val[6],
                    'addr'=>$val[7],
                    'status'=>0,
                    'add_time'=>date('Y-m-d H:i:s'),
                    'ip'=>request()->ip(),
                    'image'=>''
                ];
                $m = $member->where('phone',$val[1])->find();
                if($m){
                    continue;
                }
                $re = $member->insert($arr);
                if($re){
                    openWindow('导入成功',url('add_csv'));
                }else{
                    openWindow('导入失败',url('add_csv'));
                }
            }

        }
    }

}