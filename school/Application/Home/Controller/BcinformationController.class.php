<?php
namespace Home\Controller;
use Think\Controller;
ob_end_clean();
class BcinformationController extends Base {
    private function import($fiel){
        if (!empty($fiel)) {
            $name=$this->randomkeys(5).time();
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath = './'; // 设置附件上传根目录
            $upload->savePath = 'Public/upload/logo/'; // 设置附件上传（子）目录
            $upload->replace = true; // 设置附件是否替换
            //$upload->savePath  =     'cdr/'; // 设置附件上传（子）目录
            $upload->saveName = $name;
            $upload->autoSub = false;
            $info = $upload->uploadOne($fiel['photo']);
            if (!$info) {// 上传错误提示错误信息
                return false;
            }else{
                $string =$_FILES['photo']['name'];
                $arraym = explode('.',$string);
                $exs=$arraym[count($arraym)-1];
                return $name.'.'.$exs;
            }
        }else{
            return false;
        }
    }
    private function inspectBranch($branchid){
        $dao=M();
        $sql="SELECT scId FROM `mks_class_branch` WHERE branchid=".$branchid;
        $f=$dao->query($sql);
        if($f['0']['scId']==0){
            return false;
        }else{
            return true;
        }
    }
    private function inspectMajor($majorid){
        $dao=M();
        $sql="SELECT scId FROM `mks_class_major` WHERE majorid=".$majorid;
        $f=$dao->query($sql);
        if($f['0']['scId']==0){
            return false;
        }else{
            return true;
        }
    }
    private function inspectGrade($gradeid){
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql="SELECT COUNT(*) AS num FROM mks_user WHERE gradeId=".$gradeid." and scId=".$scId;
        $f=$dao->query($sql);
        if($f['0']['num'] || empty($f) || $f===false){
            return false;
        }else{
            return true;
        }
    }
    private function make_tree1($list,$pk='departmentId',$pid='parentId',$child='child',$root=0){/*****************将数组排成树形图格式*****************/
        $tree=array();
        foreach($list as $key=> $val){
            if($val[$pid]==$root){
                //获取当前$pid所有子类
                unset($list[$key]);
                if(! empty($list)){
                    $child=$this->make_tree1($list,$pk,$pid,$child,$val[$pk]); //来来来 找北京的子栏目 递归 空
                    if(!empty($child)){
                        $val['child']=$child;
                    }
                }
                $tree[]=$val;
            }
        }
        return $tree;
    }
    private function make_tree12($list,$root=5,$pk='departmentId',$pid='parentId',$child='child'){/*****************将数组排成树形图格式*****************/
        $tree=array();
        foreach($list as $key=> $val){
            if($val[$pid]==$root){
                //获取当前$pid所有子类
                unset($list[$key]);
                if(! empty($list)){
                    $child=$this->make_tree12($list,$val[$pk],$pk,$pid,$child); //来来来 找北京的子栏目 递归 空
                    if(!empty($child)){
                        foreach($child as $k=>$v){
                            $tree[]=$v[$pk];
                        }
                    }
                }
                $tree[]=$val[$pk];
            }
        }
        return $tree;
    }
    private function randomkeys($length){
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $key='';
        for($i=0;$i<$length;$i++) {
            $key .= $pattern{mt_rand(0,35)};    //生成php随机数
        }
        return $key;
    }
    private function logodel($logo){
        $f=unlink('.'.parent::$uploadUrl.'logo/'.$logo);
        return $f;
    }
/********************************************************************************************************学校信息**********************************************************************************************/
    public function schoolinformation(){
        $getSession = $this->get_session('loginCheck',false);
        $url=$this::$downUrl;
        //$urlHeader='http://localhost/';
        if (I('get.type') == 'schoolfind'){
            /************************学校信息展示**********************************/
            $dao = M('school');
            $where['scId'] = $getSession['scId'];
            $f = $dao->field('scName,enName,telephone,mail,province,city,area,ministries,type,logo')->where($where)->select();
            foreach ($f as $k => $v) {
                foreach ($v as $m => $n){
                    $value[$m] = $n;
                }
            }
            $value['logo']=$url.parent::$uploadUrl.'logo/'.$value['logo'];
            $this->ajaxReturn($value);
        }elseif(I('get.type') == 'schoolupdate') {
            /********************学校信息修改*********************/
            $id['scId'] = $getSession['scId'];
            $arr['scName'] = I('post.scName');
            $arr['telephone'] = I('post.telephone');
            $arr['mail'] = I('post.mail');
            $arr['province'] = I('post.province');
            $arr['city'] = I('post.city');
            $arr['area'] = I('post.area');
            $arr['ministries'] = I('post.ministries');
            $arr['enName'] = I('post.enName');
            $arr['type'] = I('post.type');
            $arr['lastRecordTime'] = time();
            $dao = M('school');
            $m['return'] = true;
            $f = $dao->where($id)->save($arr);
            if ($f === false) {
                $m['return'] = false;
            }
            $this->ajaxReturn($m);
        }elseif (I('get.type') == 'schoolinsert') {
            /********************新建学校*********************/
            if (!empty($_FILES)){
                $a=$this->import($_FILES);
                $re['return']=false;
                if($a){
                    $arr['scName'] = I('post.scName');
                    $arr['enName'] = I('post.enName');
                    $arr['logo'] = $a;
                    $arr['telephone'] = I('post.telephone');
                    $arr['mail'] = I('post.mail');
                    $arr['province'] = I('post.province');
                    $arr['city'] = I('post.city');
                    $arr['area'] = I('post.area');
                    $arr['ministries'] = I('post.ministries');
                    $arr['type'] = I('post.type');
                    $arr['lastRecordTime'] = time();
                    $arr['createTime'] = time();
                    $dao = M('school');
                    $m['return'] = true;
                    $f = $dao->data($arr)->add();
                    if($f!==false){
                        $re['return']=true;
                    }
                }
            }
            $this->ajaxReturn($re);
        }elseif (I('get.type') == 'schoollogo') {/********************学校logo修改*********************/
            $scId=$getSession['scId'];
            $re['return']=false;
            if (!empty($_FILES)){
                $a=$this->import($_FILES);
                if($a){
                    $dao = M('school');
                    $where['scId']=$scId;
                    $f1=$dao->where($where)->field('logo')->select();
                    $logo=$f1['0']['logo'];
                    $arr['lastRecordTime'] = time();
                    $arr['logo']=$a;
                    $this->logodel($logo);
                    $f=$dao->where($where)->save($arr);
                    if($f!==false){
                        $re['return']=true;
                    }
                }
            }
            $this->ajaxReturn($re);
        }
    }
    /********************************************************************************************************年级信息**********************************************************************************************/
    public function grade(){
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if (I('get.type') == 'gradefind'){/*****************************年级信息查找*********************************/
            if(I('post.typename')=='on'){
                $scId=$scId;
            }elseif(I('post.typename')=='off'){
                $scId=-$scId;
            }
            $field=I('post.field');
            if(!$field){
                $field='name';
            }else{
                $order=I("post.order");
            }
            if($order=='descending'){
                $order='desc';
            }else{
                $order='asc';
            }
            $dao=M('grade');
            $where['scId']=$scId;
            $where['state']=1;
            $f=$dao->field('gradeid,code,znName,autoupdate,highestgrade,name')->where($where)->order($field.' '.$order)->select();
            if(!$f){
                $f=array();
            }
            if(I('post.typename')=='on'){
                $sqla="SELECT gradeId FROM `mks_user` WHERE scId=".$scId." AND roleId=".$this::$studentRoleId." AND gradeId<>'' GROUP BY gradeId";
                $fa=$dao->query($sqla);
                foreach($fa as $k=>$v){
                    $grade[]=$v['gradeId'];
                }
            }
            foreach($f as $k=>$v){
                $f[$k]['znName']=$arrgrade[$v['name']-1];
                unset($f[$k]['name']);
                if(in_array($v['gradeid'],$grade)){
                    $f[$k]['state']=false;
                }else{
                    $f[$k]['state']=true;
                }
            }
            if(!$f){
                $f=array();
            }
            $this->ajaxReturn($f);
        }elseif(I('get.type') == 'gradedelete'){/*****************************年级信息删除*********************************/
            $dao=M('grade');
            if(I('post.typename')=='on'){
                $scId=$scId;
            }elseif(I('post.typename')=='off'){
                $scId=-$scId;
                $where['scId']=$scId;
                $where['gradeid']=I('post.gradeid');
                $data['state']=0;
                $f=$dao->where($where)->save($data);
                $m['return']=true;
                if($f===false){
                    $m['return']=false;
                }
                $this->ajaxReturn($m);
                exit;
            }
            $where['scId']=$scId;
            $where['gradeid']=I('post.gradeid');
            $az=$this->inspectGrade($where['gradeid']);
            if(!$az){
                $m['return']=false;
                $this->ajaxReturn($m);
                exit;
            }
            $m['return']=true;
            $f=$dao->where($where)->delete();
            if($f===false){
                $m['return']=false;
            }
            $this->ajaxReturn($m);
        }elseif(I('get.type') == 'gradeupin'){/*****************************年级信息修改添加*********************************/
            $dao=M('grade');
            $gradeid=I('post.gradeid');
            $znName=I('post.znName');
            $code=I('post.code');
            $autoupdate=I('post.autoupdate');
            $highestgrade=I('post.highestgrade');
            if(!$gradeid){
                $gradeid=0;
            }
            $where1['gradeid']=array('neq',$gradeid);
            $where1['code']=$code;
            $where1['scId']=$scId;
            $f1=$dao->where($where1)->count();
            if($f1===false){
                $m['return']=false;
                $m['msg']='操作失败!';
                $this->ajaxReturn($m);
                exit;
            }
            if($f1){
                $m['return']=false;
                $m['msg']='年级代码重复!';
                $this->ajaxReturn($m);
                exit;
            }
            $m['return']=true;
            $xq=substr($code,0,1);
            $year=substr($code,1);
            $yenow=date('Y');
            if($xq=='X'){
                $nstart=1;
            }elseif($xq=='C'){
                $nstart=7;
            }elseif($xq=='G'){
                $nstart=10;
            }else{
                $arr['return']=false;
                $arr['msg']='年级代码错误!';
                $this->ajaxReturn($arr);
                exit;
            }
            $nend=0;
            if($year>$yenow){
                $arr['return']=false;
                $arr['msg']='年级代码对应的年级不存在!';
                $this->ajaxReturn($arr);
                exit;
            }
            if(date('m')<8){
                if($year==$yenow){
                    $arr['return']=false;
                    $arr['msg']='年级代码对应的年级不存在!';
                    $this->ajaxReturn($arr);
                    exit;
                }
            }
            $nend=$yenow-$year;
            if(date('m')<8){
                $nend-=1;
            }
            $name=$nstart+$nend;
            if($xq=='X'){
                if($name>6||$name<1){
                    $m['return']=false;
                    $m['msg']='年级代码对应的年级错误!';
                    $this->ajaxReturn($m);
                    exit;
                }
            }elseif($xq=='C'){
                if($name>9||$name<7){
                    $m['return']=false;
                    $m['msg']='年级代码对应的年级错误!';
                    $this->ajaxReturn($m);
                    exit;
                }
            }elseif($xq=='G'){
                if($name>12||$name<10){
                    $m['return']=false;
                    $m['msg']='年级代码对应的年级错误!';
                    $this->ajaxReturn($m);
                    exit;
                }
            }
            if($gradeid){
                $az=$this->inspectGrade($gradeid);
                if(!$az){
                    $m['return']=false;
                    $m['msg']='年级下有学生不能操作!';
                    $this->ajaxReturn($m);
                    exit;
                }
                $where2['gradeid']=$gradeid;
                $where2['scId']=$scId;
                $data['name']=$name;
                $data['code']=$code;
                $data['znName']=$arrgrade[$name-1];
                $data['autoupdate']=$autoupdate;
                $data['highestgrade']=$highestgrade;
                $data['lastRecordTime']=time();
                $f2=$dao->where($where2)->save($data);
            }else{
                $data['name']=$name;
                $data['code']=$code;
                $data['scId']=$scId;
                $data['znName']=$arrgrade[$name-1];
                $data['autoupdate']=$autoupdate;
                $data['highestgrade']=$highestgrade;
                $data['lastRecordTime']=time();
                $data['createTime']=time();
                $f2=$dao->add($data);
            }
            if($f2===false){
                $m['return']=false;
                $m['msg']='操作失败!';
            }
            $this->ajaxReturn($m);
        }
    }

    /********************************************************************************************************科目信息**********************************************************************************************/
    public function subject(){
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(I('get.type')=='subjectfind'){/*****************************科目信息查找*********************************/
            $dao=M('subject');
            $field=I('post.field');
            if(!$field){
                $field='subjectid';
            }else{
                $order=I("post.order");
            }
            if($order=='descending'){
                $order='desc';
            }else{
                $order='asc';
            }
            $where['scId']=$scId;
            $f=$dao->field('subjectid,subjectname,fullcredit')->where($where)->order($field.' '.$order)->select();
            if(!$f){
                $f=array();
            }
            $this->ajaxReturn($f);
        }elseif(I('get.type')=='subjectupdate'){/*****************************科目信息修改添加*********************************/
            $subjectid=I('post.subjectid');
            $subjectname=I('post.subjectname');
            $fullcredit=I('post.fullcredit');
            $dao=M('subject');
            if(!$subjectid){
                $subjectid=0;
            }
            $sql1="select count(subjectid) as num from mks_subject where subjectname='".$subjectname."' and scId=".$scId." and subjectid<>".$subjectid;
            $f1=$dao->query($sql1);
            if($f1===false){
                $m['return']=false;
                $this->ajaxReturn($m);
                exit;
            }
            if($f1['0']['num']){
                $m['return']='error';
                $this->ajaxReturn($m);
                exit;
            }
            $m['return']=true;
            if($subjectid){
                $where['subjectid']=$subjectid;
                $where['scId']=$scId;
                $data['subjectname']=$subjectname;
                $data['fullcredit']=$fullcredit;
                $data['lastRecordTime'] = time();
                $f=$dao->where($where)->save($data);
                if($f===false){
                    $m['return']=false;
                }
            }else{
                $data['scId']=$scId;
                $data['subjectname']=$subjectname;
                $data['fullcredit']=$fullcredit;
                $data['lastRecordTime'] = time();
                $data['createTime'] = time();
                $f=$dao->data($data)->add();
                if($f===false){
                    $m['return']=false;
                }
            }
            $this->ajaxReturn($m);
        }elseif(I('get.type')=='subjectdel'){/*****************************科目信息删除*********************************/
            $where['subjectid']=I('post.subjectid');
            $where['scId']=$scId;
            $dao=M('subject');
            $m['return']=true;
            $f=$dao->where($where)->delete();
            if($f===false){
                $m['return']=false;
            }
            $this->ajaxReturn($m);
        }
    }
    /********************************************************************************************************专业信息**********************************************************************************************/
    public function major(){
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(I('get.type')=='majorselect'){/*****************************专业信息查找*********************************/
            $dao=M();
            $field=I('post.field');
            if($field=='majorname'){
                $field='m.'.$field;
            }else{
                $field='b.scId';
                $order=I("post.order");
            }
            if($order=='descending'){
                $order='desc';
            }else{
                $order='asc';
            }
            $sql="SELECT m.majorid,m.majorname,b.branch,m.scId FROM mks_class_major AS m,mks_class_branch AS b WHERE m.branch=b.branchid and b.scId IN(0,".$scId.") AND m.scId IN(0,".$scId.") order by ".$field.' '.$order;
            $f=$dao->query($sql);
            $sql2="select branch,branchid from mks_class_branch WHERE scId IN(0,".$scId.")";
            $f2=$dao->query($sql2);
            if(!$f2){
                $f2=array();
            }
            $arr['branchlist']=$f2;
            if(!$f){
                $f=array();
            }
            foreach($f as $k=>$v){
                if($v['scId']==0){
                    $f[$k]['state']=false;
                }else{
                    $f[$k]['state']=true;
                }
                unset($f[$k]['state']['scId']);
            }
            $arr['data']=$f;
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='majorupdate'){/*****************************专业信息修改添加*********************************/
            $table='class_major';
            $majorid=I('post.majorid');
            $majorname=I('post.majorname');
            $branchid=I('post.branchid');
            $dao=M($table);
            if(!$majorid){
                $majorid=0;
            }
            $sql1="select count(majorid) as num from mks_class_major where majorname='".$majorname."' and majorid<>".$majorid." and ((branch=".$branchid." and scId=".$scId.") or( scId=0))";
            $f1=$dao->query($sql1);
            if($f1===false){
                $m['return']=false;
                $this->ajaxReturn($m);
                exit;
            }
            if($f1['0']['num']){
                $m['return']='error';
                $this->ajaxReturn($m);
                exit;
            }
            $m['return']=true;
            if($majorid){
                if(!$this->inspectMajor($majorid)){
                    $m['return']=false;
                    $this->ajaxReturn($m);
                    exit;
                }
                $where['majorid']=$majorid;
                $where['scId']=$scId;
                $data['majorname']=$majorname;
                $data['branch']=$branchid;
                $data['lastRecordTime'] = time();
                $f=$dao->where($where)->save($data);
                if($f===false){
                    $m['return']=false;
                }
            }else{
                $data['scId']=$scId;
                $data['majorname']=$majorname;
                $data['branch']=$branchid;
                $data['lastRecordTime'] = time();
                $data['createTime'] = time();
                $f=$dao->data($data)->add();
                if($f===false){
                    $m['return']=false;
                }
            }
            $this->ajaxReturn($m);
        }elseif(I('get.type')=='majordelete'){/*****************************专业信息删除*********************************/
            $table='class_major';
            $where['majorid']=I('post.majorid');
            $where['scId']=$scId;
            if(!$this->inspectMajor($where['majorid'])){
                $m['return']=false;
                $this->ajaxReturn($m);
                exit;
            }
            $dao=M($table);
            $m['return']=true;
            $f=$dao->where($where)->delete();
            if($f===false){
                $m['return']=false;
            }
            $this->ajaxReturn($m);
        }
        if(I('get.type')=='branchfind'){/*****************************科类信息查找*********************************/
            $dao=M('class_branch');
            $where['scId']=array('in','0,'.$scId);
            $f=$dao->where($where)->field('branchid,branch')->select();
            if(!$f){
                $f=array();
            }
            $this->ajaxReturn($f);
        }
    }
    /********************************************************************************************************科类信息**********************************************************************************************/
    public function branch(){
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(I('get.type')=='branchselect'){/*****************************科类信息查找*********************************/
            $dao=M('class_branch');
            $where['scId']=array('in','0,'.$scId);
            $order=I("post.order");
            if($order=='descending'){
                $order='desc';
            }else{
                $order='asc';
            }
            $field='scId';
            $f=$dao->where($where)->field('branchid,branch,scId')->order($field.' '.$order)->select();
            foreach($f as $k=>$v){
                if($v['scId']==0){
                    $f[$k]['state']=false;
                }else{
                    $f[$k]['state']=true;
                }
                unset($f[$k]['scId']);
            }
            if(!$f){
                $f=array();
            }
            $this->ajaxReturn($f);
        }elseif(I('get.type')=='branchupdate'){/*****************************科类信息修改添加*********************************/
            $branchid=I('post.branchid');
            $branch=I('post.branch');
            $dao=M('class_branch');
            if(!$branchid){
                $branchid=0;
            }
            $scIda=$scId.',0';
            $sql1="select count(branchid) as num from mks_class_branch where branch='".$branch."' and scId in(".$scIda.") and branchid<>".$branchid;
            $f1=$dao->query($sql1);
            if($f1===false){
                $m['return']=false;
                $this->ajaxReturn($m);
                exit;
            }
            if($f1['0']['num']){
                $m['return']='error';
                $this->ajaxReturn($m);
                exit;
            }
            $m['return']=true;
                if($branchid){
                    if(!$this->inspectBranch($branchid)){
                        $m['return']=false;
                        $this->ajaxReturn($m);
                        exit;
                    }
                    $where['branchid']=$branchid;
                    $where['scId']=$scId;
                    $data['branch']=$branch;
                    $data['lastRecordTime'] = time();
                    $f=$dao->where($where)->save($data);
                    if($f===false){
                        $m['return']=false;
                    }
                }else{
                    $data['scId']=$scId;
                    $data['branch']=$branch;
                    $data['lastRecordTime'] = time();
                    $data['createTime'] = time();
                    $f=$dao->data($data)->add();
                    if($f===false){
                        $m['return']=false;
                    }
                }
            $this->ajaxReturn($m);
        }elseif(I('get.type')=='branchdel'){/*****************************科类信息删除*********************************/
            $where['branchid']=I('post.branchid');
            if(!$this->inspectBranch($where['branchid'])){
                $m['return']=false;
                $this->ajaxReturn($m);
                exit;
            }
            $where['scId']=$scId;
            $dao=M('class_branch');
            $m['return']=true;
            $f=$dao->where($where)->delete();
            if($f===false){
                $m['return']=false;
            }
            $this->ajaxReturn($m);
        }
    }
    /********************************************************************************************************班级级别信息**********************************************************************************************/
    public function level(){
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(I('get.type')=='levelselect'){/*****************************班级级别信息查找*********************************/
            $table="class_level";
            $dao=M($table);
            $where['scId']=$scId;
            $order=I("post.order");
            if($order=='descending'){
                $order='desc';
            }else{
                $order='asc';
            }
            $field='levelname';
            $f=$dao->where($where)->field('levelid,levelname')->order($field.' '.$order)->select();
            if(!$f){
                $f=array();
            }
            $this->ajaxReturn($f);
        }elseif(I('get.type')=='levelupdate'){/*****************************班级级别修改添加*********************************/
            $levelid=I('post.levelid');
            $levelname=I('post.levelname');
            $dao=M('class_level');
            if(!$levelid){
                $levelid=0;
            }
            $sql1="select count(levelid) as num from mks_class_level where levelname='".$levelname."' and scId=".$scId." and levelid<>".$levelid;
            $f1=$dao->query($sql1);
            if($f1===false){
                $m['return']=false;
                $this->ajaxReturn($m);
                exit;
            }
            if($f1['0']['num']){
                $m['return']='error';
                $this->ajaxReturn($m);
                exit;
            }
            $m['return']=true;
            if($levelid){
                $where['levelid']=$levelid;
                $where['scId']=$scId;
                $data['levelname']=$levelname;
                $data['lastRecordTime'] = time();
                $f=$dao->where($where)->save($data);
                if($f===false){
                    $m['return']=false;
                }
            }else{
                $data['scId']=$scId;
                $data['levelname']=$levelname;
                $data['lastRecordTime'] = time();
                $data['createTime'] = time();
                $f=$dao->data($data)->add();
                if($f===false){
                    $m['return']=false;
                }
            }
            $this->ajaxReturn($m);
        }elseif(I('get.type')=='leveldel'){/*****************************班级级别删除*********************************/
            $table='class_level';
            $where['levelid']=I('post.levelid');
            $where['scId']=$scId;
            $dao=M($table);
            $m['return']=true;
            $f=$dao->where($where)->delete();
            if($f===false){
                $m['return']=false;
            }
            $this->ajaxReturn($m);
        }
    }


    /********************************************************************************************************学年学期**********************************************************************************************/
    public function schoolyear(){
        $getSession = $this->get_session('loginCheck',false);
        if(I('get.type')=='schoolyearselect'){/*****************************学年学期信息查找*********************************/
            $table="school_year";
            $dao=M($table);
            $where['scId']=$getSession['scId'];
            $field=I('post.field');
            if(!$field){
                $field='yearid';
            }else{
                $order=I('post.order');
            }
            if($order=='descending'){
                $order='desc';
            }else{
                $order='asc';
            }
            $aa=$field." ".$order;
            $f=$dao->where($where)->field('yearid,yearname,startTime,endTime,term,lastRecordTime')->order($aa)->select();
            foreach($f as $k=>$v){
                $f[$k]['lastRecordTime']=date('Y-m-d H:i:s',$v['lastRecordTime']);
                $f[$k]['startTime']=date('Y-m-d',$v['startTime']);
                $f[$k]['endTime']=date('Y-m-d',$v['endTime']);
                $startime=$v['startTime'];
                $endtime=$v['endTime'];
                $time=time();
                if($startime<=$time && $endtime>=$time){
                    $f[$k]['atPresent']=true;
                }else{
                    $f[$k]['atPresent']=false;
                }
            }
            if(!$f){
                $f=array();
            }
            $this->ajaxReturn($f);
        }elseif(I('get.type')=='yearupdate'){/*****************************学年学期修改添加*********************************/
            $table='school_year';
            $scId=$getSession['scId'];
            $dao=M($table);
            $m['return']=true;
            $yearid=I('post.yearid');
            if(!$yearid){
                $yearid=0;
            }
            $startTime=strtotime(I('post.startTime'));
            $endTime=strtotime(I('post.endTime'));
            $sqla="SELECT count(*) as `count` FROM `mks_school_year` WHERE scId=".$scId." and yearid<>".$yearid." and ((".$startTime.">=startTime and ".$startTime."<=endTime) or (".$endTime.">=startTime and ".$endTime."<=endTime) or  (".$startTime."<=startTime and ".$endTime.">=endTime))";
            $fa=$dao->query($sqla);
            if($fa['0']['count']){
                $m['return']='error';
                $this->ajaxReturn($m);
                exit;
            }
            if($yearid){
                $where['yearid']=$yearid;
                $where['scId']=$scId;
                $data['yearname']=I('post.yearname');
                $data['term']=I('post.term');
                $data['startTime']=$startTime;
                $data['endTime']=$endTime;
                $data['lastRecordTime'] = time();
                $f=$dao->where($where)->save($data);
                if($f===false){
                    $m['return']=false;
                }
            }else{
                $data['scId']=$scId;
                $data['yearname']=I('post.yearname');
                $data['term']=I('post.term');
                $data['startTime']=$startTime;
                $data['endTime']=$endTime;
                $data['lastRecordTime'] = time();
                $data['createTime'] = time();
                $f=$dao->data($data)->add();
                if($f===false){
                    $m['return']=false;
                }
            }
            $this->ajaxReturn($m);
        }elseif(I('get.type')=='yeardel'){/*****************************学年学期删除*********************************/
            $table='school_year';
            $where['yearid']=I('post.yearid');
            $where['scId']=$getSession['scId'];
            $dao=M($table);
            $m['return']=true;
            $f=$dao->where($where)->delete();
            if($f===false){
                $m['return']=false;
            }
            $this->ajaxReturn($m);
        }
    }
    /********************************************************************************************************部门信息**********************************************************************************************/
    public function department(){
        $getSession = $this->get_session('loginCheck',false);
        if(I('get.type')=='departmentselect'){/*****************************部门信息查找*********************************/
            $table="department";
            $dao=M($table);
            $where['scId']=$getSession['scId'];
            $f=$dao->where($where)->field('departmentId,departmentName,parentId,level')->select();
            $arr=$this->make_tree1($f);
            if(!$arr){
                $arr=array();
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='departmentupdate'){/*****************************部门信息修改添加*********************************/
            $table='department';
            $scId=$getSession['scId'];
            $departmentId=I('post.departmentId');
            $departmentName=I('post.departmentName');
            $level=I('post.level');
            $parentId=I('post.parentId');
            $dao=M($table);
            $m['return']=true;
            if(!$departmentId){
                $departmentId=0;
            }
            $sqln="SELECT COUNT(*) as num FROM `mks_department` WHERE departmentName='".$departmentName."' AND scId=".$scId." AND departmentId<>".$departmentId;
            $fn=$dao->query($sqln);
            if($fn===false){
                $m['return']=false;
                $this->ajaxReturn($m);
                exit;
            }else{
                if($fn['0']['num']){
                    $m['return']='error';
                    $this->ajaxReturn($m);
                    exit;
                }
            }
            if($departmentId){
                $where['departmentId']=$departmentId;
                $where['scId']=$scId;
                $data['departmentName']=$departmentName;
                $data['lastRecordTime'] = time();
                $f=$dao->where($where)->save($data);
                if($f===false){
                    $m['return']=false;
                }
            }else{
                $data['scId']=$scId;
                $data['departmentName']=$departmentName;
                if(!$parentId&&!$level){
                    $parentId=0;
                    $level=0;
                }
                $data['parentId']=$parentId;
                $data['level']=$level+1;
                $data['lastRecordTime'] = time();
                $data['createTime'] = time();
                $f=$dao->data($data)->add();
                if($f===false){
                    $m['return']=false;
                }
            }
            $this->ajaxReturn($m);
        }elseif(I('get.type')=='departmentdel'){/*****************************部门删除*********************************/
            $table='department';
            $dao=M($table);
            $departmentId=I('post.departmentId');
            $where['scId']=$getSession['scId'];
            $f=$dao->where($where)->field('departmentId,parentId')->select();
            $ar=$this->make_tree12($f,$departmentId);
            $st=implode(',',$ar);
            if($st){
                $st.=",".$departmentId;
            }else{
                $st=$departmentId;
            }
            $m['return']=true;
            $where['departmentId']=array('in',$st);
            $f1=$dao->where($where)->delete();
            if($f1===false){
                $m['return']=false;
            }
            $this->ajaxReturn($m);
        }
    }
    /**************************角色信息***************************/
    public function role(){
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        //$unRoleId[]=$this::$adminRoleId;
        //$unRoleId[]=$this::$jZroleId;
        //$unRoleId[]=$this::$teacherRoleId;
        //$unRoleId[]=$this::$studentRoleId;
        $dao=M();
        $sql="SELECT roleId FROM mks_role WHERE scId=0";
        $f=$dao->query($sql);
        $sqla="SELECT roleId FROM mks_user WHERE scId=".$scId." GROUP BY roleId";
        $fa=$dao->query($sqla);
        foreach($f as $k=>$v){
            $unRoleId[]=$v['roleId'];
        }
        foreach($fa as $k=>$v){
            if(!in_array($v['roleId'],$unRoleId)){
                $unRoleId[]=$v['roleId'];
            }
        }
        if(I('get.type')=='find'){//查询
            $dao=M('role');
            $where['scId']=array('in',array('0',$scId));
            $order=I('post.order');
            if($order=='descending'){
                $order='roleName desc';
            }elseif(($order=='ascending')){
                $order='roleName asc';
            }else{
                $order='scId,roleId desc';
            }

            $f1=$dao->where($where)->field('roleId,roleName')->order($order)->select();
            /*$sqln="SELECT departmentId,departmentName FROM `mks_department` WHERE scId=".$scId;
            $fn=$dao->query($sqln);
            foreach($fn as $k=>$v){
                $fa[$v['departmentId']]=$v['departmentName'];
            }*/
            foreach($f1 as $k=>$v){
                //$f1[$k]['departmentName']=$fa[$v['departmentId']];
                if(in_array($v['roleId'],$unRoleId)){
                    $f1[$k]['state']=false;
                }else{
                    $f1[$k]['state']=true;
                }
            }
            /*$field=I('post.field');
            foreach ($f1 as $k => $v) {
                $fieldArr[$k] = $v[$field];
            }
            array_multisort($fieldArr, $ordera, $array);*/
            if(!$f1){
                $f1=array();
            }
            $this->ajaxReturn($f1);
        }elseif(I('get.type')=='upin'){//修改添加
            $roleId=I('post.roleId');
            $roleName=I('post.roleName');
            if(!$roleId){
                $roleId=0;
            }
            if(in_array($roleId,$unRoleId)){
                $m['return']=false;
                $this->ajaxReturn($m);
                exit;
            }
            $dao=M('role');
            $where1['scId']=array('in',array('0',$scId));
            $where1['roleId']=array('neq',$roleId);
            $where1['roleName']=$roleName;
            $f1=$dao->where($where1)->count();
            if($f1===false){
                $m['return']=false;
                $this->ajaxReturn($m);
                exit;
            }
            if($f1){
                $m['return']='error';
                $this->ajaxReturn($m);
                exit;
            }
            $m['return']=true;
            if($roleId){
                $where2['roleId']=$roleId;
                $where2['scId']=$scId;
                $data['roleName']=$roleName;
                $f=$dao->where($where2)->save($data);
                if($f===false){
                    $m['return']=false;
                }
            }else{
                $data['scId']=$scId;
                $data['roleName']=$roleName;
                $data['createTime']=date('Y-m-d H:i:s');
                $f=$dao->add($data);
                if($f===false){
                    $m['return']=false;
                }
            }
            $this->ajaxReturn($m);
            exit;
        }elseif(I('get.type')=='del'){//删除
            $roleId=I('post.roleId');
            if(in_array($roleId,$unRoleId)){
                $m['return']=false;
                $this->ajaxReturn($m);
                exit;
            }
            $dao=M('role');
            $where['roleId']=$roleId;
            $where['scId']=$scId;
            $f=$dao->where($where)->delete();
            if($f===false){
                $m['return']=false;
            }else{
                $m['return']=true;
            }
            $this->ajaxReturn($m);
        }
    }
}