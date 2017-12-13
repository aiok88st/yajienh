<?php
namespace app\index\controller;
use think\Db;
use think\Exception;
use think\Request;
use app\index\model\Commont;
use app\index\model\Praise;
class Msg extends Fater{

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $hasAjax=Request::instance()->isAjax();
        if(!UID){
            return json(['code'=>408]);
        }
    }

    public function index(){
        $url='http://'.$_SERVER['HTTP_HOST'].'/api/party/index.html';
        $this->redirect($url);
    }
    public function hasAuth(Commont $commont,praise $praise){
        if(!UID){
            return json(['code'=>408]);
        }
        $find=db('member_open')->where('member_id',UID)->field('open_face,open_name')->find();
        $cc=$commont->where('to_member_id',UID)->where('member_id','neq',UID)->where('status',0)->count();
        $pc=$praise->where('to_member_id',UID)->where('member_id','neq',UID)->where('status',0)->count();
        $tc=$cc+$pc;
        $ga=[];
        if($tc>0){
            $kc=$cc>0?1:2;
            if($kc==1){
                $gm=$commont->where('to_member_id',UID)->order('id desc')->value('member_id');
                $gf=db('member_open')->where('member_id',$gm)->value('open_face');
            }else{
                $gm=$praise->where('to_member_id',UID)->order('id desc')->value('member_id');
                $gf=db('member_open')->where('member_id',$gm)->value('open_face');
            }
            $ga=[
                'tc'=>$tc,
                'gf'=>$gf
            ];
        }

        return json([
            'code'=>1,
            'face'=>$find['open_face'],
            'name'=>$find['open_name'],
            'member_id'=>UID,
            'ga'=>$ga
        ]);
    }
    public function create(){ //新建
        try{
            if(!UID){
                return json(['code'=>408]);
            }
            $db=db('msg');
            $data=input();
            if(empty($data['content'])){
                return json([
                    'code'=>0,
                    'msg'=>'随便写点什么吧～'
                ]);
            }
            if(isset($data['images'])){
//                $imgaes=$this->uploadBase64($data['images']);
                $imgaes['thumb']=$data['images'];
            }else{
                $imgaes['thumb']='';
            }

            $input=[
                'member_id'=>UID,
                'content'=>$data['content'],
                'images'=>serialize($imgaes['thumb']),
                'addTime'=>time(),
                'status'=>0
            ];

            $id=$db->insertGetId($input);
//            $open=db('member_open')->where('member_id',UID)->field('open_face,open_name')->find();
//            $webMsg=[];
//            $webMsg['face']=$open['open_face'];
//            $webMsg['name']=$open['open_name'];
//            $webMsg['images']=isset($data['images'])?serialize($data['images']):'';
//
//            $webMsg['time']=format_date(time()-1);
//            $webMsg['commont']=[];
//            $webMsg['content']=$data['content'];
//            $webMsg['PraiseList']=[];
//            $webMsg['interaction']=false;
//            $webMsg['id']=$id;
//            $webMsg['member_id']=UID;
//            $put=[
//                'data'=>$webMsg,
//                'type'=>1
//            ];
//
//            webMsg(json_encode($put));
            return json(['code'=>1,'msg'=>'发布成功']);
        }catch (\Exception $e){
            return json(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }
    public function msglist(Commont $commont,Praise $praise){  //留言列表

        $list=Db::table('hy_msg')
            ->alias('a')
            ->join('hy_member_open b','a.member_id = b.member_id','LEFT')
            ->where('a.status',1)
            ->field('a.*,b.open_face,b.open_name')
            ->order('a.updateTime desc,a.id desc')->paginate(10)->toArray();
        $data=[];

        foreach ($list['data'] as $k=>$v){
            $v['face']=$v['open_face'];
            $v['name']=$v['open_name'];
            $v['images']=unserialize($v['images']);
            $v['time']=format_date($v['addTime']+1);
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
            'last_page'=>$list['last_page'],
            'list'=>$data
        ]);
    }
    public function msgPraise(Praise $praise,Commont $commont){  //点赞
        Db::startTrans();
        try{
            if(!UID){
                return json(['code'=>408]);
            }
            $id=input('msgId');
            $c=$praise->where('member_id',UID)
                ->where('msgId',$id)
                ->value('id');

            if($c){
                $praise::destroy($c);
            }else{

                $tmid=db('msg')->where('id',$id)->value('member_id');
                $input=[
                    'member_id'=>UID,
                    'to_member_id'=>$tmid,
                    'msgId'=>$id,
                    'addTime'=>time()
                ];
                $praise->allowField(true)->save($input);
            }

            Db::commit();

            $list=$praise->where('msgId',$id)->order('id desc')->select();
            $c=$commont->where('msgId',$id)->count();

            if(!empty($list)||$c > 0){
                $interaction=true;
            }else{
                $interaction=false;
            }
            $put=[
                'type'=>2,
                'data'=>$list,
                'msgId'=>$id
            ];

            webMsg(json_encode($put));
            return json([
                'code'=>1,
                'PraiseList'=>$list,
                'interaction'=>$interaction
            ]);
        }catch (Exception $e){
            Db::rollback();
            return json(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }
    public function comment(Request $request,Commont $commont){ //评论
        Db::startTrans();
        try{
            if(!UID){
                return json(['code'=>408]);
            }
            if($request::instance()->isPost()){
                $param=$request->param();
                $param['member_id']=UID;
                $param['addTime']=time();
                if(!$param['to_member_id']){
                    $param['to_member_id']=db('msg')->where('id',$param['msgId'])->value('member_id');
                }
                $commont->allowField(true)->save($param);
                $id=$commont->id;
                Db::commit();
                $list=$v['commont']=$commont->where('msgId',$param['msgId'])->order('id asc')->select();

                $put=[
                    'type'=>3,
                    'data'=>$commont->where('id',$id)->find(),
                    'msgId'=>$param['msgId']
                ];
                webMsg(json_encode($put));
                return json([
                    'code'=>1,
                    'commont'=>$list,
                    'interaction'=>!empty($list)?true:false
                ]);
            }else{
                return json(['code'=>0,'msg'=>'网络链接失败']);
            }
        }catch (\Exception $exception){
            return json(['code'=>0,'msg'=>'网络链接失败']);
        }
    }
    public function newsList(Commont $commont,Praise $praise){  //新消息列表
        $cc=$commont->where('to_member_id',UID)->where('member_id','neq',UID)->where('status',0)->order('id desc')->select();
        $pc=$praise->where('to_member_id',UID)->where('member_id','neq',UID)->where('status',0)->order('id desc')->select();
        $msgDb=db('msg');

        $cid=[];
        foreach ($cc as $k=>$v){
            $cc[$k]['addTime']=date('H:i',$v['addTime']);
            $msg=$msgDb->where('id',$v['msgId'])->find();
            $images=unserialize($msg['images']);
            if($images){
                $cc[$k]['thumb']=$images[0];
            }
            $cc[$k]['content']=$msg['content'];
            $cid[$k]=$v['id'];
        }
        $commont->where('id','IN',$cid)->update(['status'=>1]);
        $pid=[];
        foreach ($pc as $k=>$v){
            $pc[$k]['addTime']=date('i:s',$v['addTime']);
            $msg=$msgDb->where('id',$v['msgId'])->find();
            $images=unserialize($msg['images']);
            if($images){
                $pc[$k]['thumb']=$images[0];
            }
            $pc[$k]['content']=$msg['content'];
            $pid[$k]=$v['id'];
        }
        $praise->where('id','IN',$pid)->update(['status'=>1]);

        $list=array_merge($cc,$pc);
        return json([
            'code'=>1,
            'list'=>$list
        ]);
    }
    public function descNews(Request $request,Commont $commont,Praise $praise){
        try{
            $msgId=$request->param()['msgId'];
            $data=Db::table('hy_msg')
                ->alias('a')
                ->join('hy_member_open b','a.member_id = b.member_id','LEFT')
                ->field('a.*,b.open_name,b.open_face')

                ->where('a.id',$msgId)
                ->find();
            $v=[];
            $v['id']=$data['id'];
            $v['content']=$data['content'];
            $v['face']=$data['open_face'];
            $v['name']=$data['open_name'];
            $v['images']=unserialize($data['images']);
            $v['time']=format_date($data['addTime']);

            $v['PraiseList']=$praise
                ->where('msgId',$data['id'])
                ->order('id asc')
                ->select();

            $v['commont']=$commont->where('msgId',$data['id'])->order('id asc')->select();

            if(!empty($v['PraiseList']) || !empty($v['commont'])){
                $v['interaction']=true;
            }else{
                $v['interaction']=false;
            }
            return json(['code'=>1,'data'=>$v]);
        }catch (Exception $exception){
            return json([
                'code'=>0,
                'msg'=>$exception->getMessage()
            ]);
        }
    }
}
?>