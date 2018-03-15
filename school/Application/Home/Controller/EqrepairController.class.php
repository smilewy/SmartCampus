<?php
/**
 * Created by PhpStorm.
 * User: xiaolong
 * Date: 2017/6/22
 * Time: 14:15
 * 教务管理
 */
namespace Home\Controller;
//use Think\Controller;
//use Vendor\PHPExcel\PHPExcel;
class EqrepairController extends Base
{
    /**公用的不设置权限*/
    public function basicsSet(){//基础设施
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck', false);
        $scId = $scId['scId'];
        if($type == 'getRepairType'){
            $data = M('repair_type')->where(array('scId' => $scId))->select();
            foreach($data as $key => $value){
                $data[$key]['user'] = unserialize($value['user']);
                $str = '';
                foreach($data[$key]['user'] as $key1 => $value1){
                    $str = $str.' '.$value1['name'];
                }
                $data[$key]['user'] = $str;
            }
            $valueData = $_GET['valueData'];
            if($valueData){
                $returnList = array();
                if($valueData){
                    foreach($data as $key => $value){
                        $i = 0;
                        foreach($value as $key1 => $value1){
                            if(count(explode($valueData,$value1))>1){
                                $i++;
                            }
                        }
                        if($i>=1){
                            unset($value['scId']);
                            $returnList[] = $value;
                        }
                    }
                    $data = $returnList;
                }
            }
            $returnList = array();
            $sort = $_GET['sort'];
            $sortData = $_GET['sortData'];
            if($sort){
                $returnList = array();
                $data1 = $data;
                foreach ($data as $key => $value) {
                    $max = array();
                    $kk = 0;
                    foreach ($data1 as $key1 => $value1) {
                        if ($value1[$sort] >= $max[$sort]) {
                            $max = $value1;
                            $kk = $key1;
                        }
                    }
                    $returnList[] = $max;
                    unset($data1[$kk]);
                }
                if ($sortData == 'inc') {
                    krsort($returnList);
                }
                $data = $returnList;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'createRepairType'){
            $repairType = I('post.repairType');
            if(M('repair_type')->add(array(
                'repairType' => $repairType,
                'scId' => $scId,
                'user' => serialize(array()),
                'createTime' => strtotime(date('Y-m-d H:i:s'))
            ))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'deleteRepairType'){
            $id = I('post.id');
            foreach($id as $key => $value){
                M('repair_type')->where(array('id' => $value,'scId' => $scId))->delete();
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
        }
        if($type == 'updataRepairType'){
            $id = I('post.id');
            $user = I('post.user');
            $repairType = I('post.repairType');
            if(M('repair_type')->where(array('id' => $id,'scId' => $scId))->setField(array(
                'user' => serialize($user),
                'repairType' => $repairType
            ))){
                $this->returnJson(array('statu' => 1,'message' => 'succeess'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
        }
        if($type == 'getZgList'){
            $student = $this::$studentRoleId;
            $jz = $this::$jZroleId;
            $admin = $this::$adminRoleId;
            $id = $_GET['id'];
            $user = M('')->query("SELECT name,id,post from mks_user where scId=$scId AND roleId!=$student AND roleId!= $admin AND roleId!=$jz");
            $type = M('repair_type')->where(array('id' => $id,'scId' => $scId))->find();
            $reUser = unserialize($type['user']);
            foreach($user as $key => $value){
                if(!$value['post']){
                    $user[$key]['post'] = '教师';
                }
            }
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => array('zgList' => $user,'user' => $reUser)),true);
        }
        if($type == 'deleteUser'){
            $id = I('post.id');
            $userId = I('post.userId');
            $type = M('repair_type')->where(array('id' => $id,'scId' => $scId))->find();
            $reUser = unserialize($type['user']);
            $reUserRe =array();
            foreach($reUser as $key => $value){
                if($value['id'] == $userId){
                }else{
                    $reUserRe[] = $value;
                }
            }
            if(M('repair_type')->where(array('id' => $id,'scId' => $scId))->setField(array('user' => serialize($reUserRe)))){
                $this->returnJson(array('statu' => 1,'message' => 'del  succeess'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'del  fail'),true);
        }
        if($type == 'export'){
            $data = M('repair_type')->where(array('scId' => $scId))->select();
            foreach($data as $key => $value){
                $user  = unserialize($value['user']);
                $strName = '';
                foreach($user as $key1 => $value1){
                    $strName = $value1['name'].'  '.$strName;
                }
                $data[$key]['user'] = $strName;
            }
            $tr = array(
                '0' => array(
                    'en' => 'repairType',
                    'zh' => '报修类别'
                ),
                '1' => array(
                    'en' => 'user',
                    'zh' => '维修人员'
                )
            );
            $this->export($data,$tr);
        }
    }
    private function stateToZn($i){
        $array = array(
            1 => '待处理',
            2 => '已维修',
            3 => '维修失败',
            4 => '已验收'
        );
        return $array[$i];
    }
    public function myRepair(){//我的保修单
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck',false);
        $userId = $scId['userId'];
        $usernamename = $scId['name'];
        $scId = $scId['scId'];
        if($type == 'delImg'){
            $url = I('get.url');
            $url = explode('img/',$url);
            if($this::deleteImg($url[1])){
                $this->returnJson(array('statu' => 1,'message' => '删除成功'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => '删除失败'),true);
        }
        if($type == 'getRepairList'){
            $repairType = $_GET['repairType'];
            $id = $_GET['id'];
            $state = $_GET['state'];
            $startTime = '2010-09-13 00:00:00';
            $endTime = date('Y-m-d H:i:s');
            if($_GET['startTime']){
                $startTime = $_GET['startTime'];
            }
            if($_GET['endTime']){
                $endTime = $_GET['endTime'];
            }
            if($state == 2){

            }
            if($id == -1){
                if($state == 2){
                    $data = M('repair_list')->where(array('scId' => $scId,'userId' => $userId,'state' =>array(array('eq',2),array('eq',3),'or'),'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }else{
                    $data = M('repair_list')->where(array('scId' => $scId,'userId' => $userId,'state' =>$state,'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }
            }else{
                if($state == 2){
                    $data = M('repair_list')->where(array('repairTypeId' => $id,'scId' => $scId,'userId' => $userId,'state' =>array(array('eq',2),array('eq',3),'or'),'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }else{
                    $data = M('repair_list')->where(array('repairTypeId' => $id,'scId' => $scId,'userId' => $userId,'state' =>$state,'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }
            }
            $allTypeRel = array();
            $ddd = M('repair_type')->where(array('scId' => $scId))->select();
            foreach ($ddd as $key => $value){
                $allTypeRel[$value['id']]['type'] = $value['repairType'];
                $user = unserialize($value['user']);
                foreach($user as $key1 => $value1){
                    $allTypeRel[$value['id']]['user'][$value1['id']] = $value1['name'];
                }
            }
            foreach($data as $key => $value) {
                $zn = 0;
                if ($data[$key]['repairReturn'] == 0) {
                    $zn = '无';
                } else {
                    $zn = '已反馈';
                }
                $data[$key]['repairType'] = $allTypeRel[$value['repairTypeId']]['type'];
                $data[$key]['repairUserName'] = $allTypeRel[$value['repairTypeId']]['user'][$value['receiveUserId']];
                $data[$key]['repairReturnZn'] = $zn;
                $data[$key]['stateName'] = $this->stateToZn($data[$key]['state']);
                $valueData = $_GET['valueData'];
                if ($valueData) {
                    $returnList = array();
                    if ($valueData) {
                        foreach ($data as $key => $value) {
                            $i = 0;
                            foreach ($value as $key1 => $value1) {
                                if (count(explode($valueData, $value1)) > 1) {
                                    $i++;
                                }
                            }
                            if ($i >= 1) {
                                unset($value['scId']);
                                $returnList[] = $value;
                            }
                        }
                        $data = $returnList;
                    }
                }
            }
            $returnList = array();
            $sort = $_GET['sort'];
            $sortData = $_GET['sortData'];
            if($sort){
                $returnList = array();
                $data1 = $data;
                foreach ($data as $key => $value) {
                    $max = array();
                    $kk = 0;
                    foreach ($data1 as $key1 => $value1) {
                        if ($value1[$sort] >= $max[$sort]) {
                            $max = $value1;
                            $kk = $key1;
                        }
                    }
                    $returnList[] = $max;
                    unset($data1[$kk]);
                }
                if ($sortData == 'inc') {
                    krsort($returnList);
                }
                $data = $returnList;
            }
            if($repairType == 'list'){
                foreach($data as $key => $value){
                    $data[$key]['logo'] = unserialize($data[$key]['logo']);
                    if(!is_array($data[$key]['logo'])){
                        $data[$key]['logo'] = array();
                    }
                }
                $this->returnJson(array('statu' => 1,'message' =>1,'data' => $data),true);
            }
            if($repairType == 'export'){
                $tr = $this->exportTr();
                $this->export($data,$tr);
            }
        }
        if($type == 'restartApp'){
            $id = I('post.id');
            $restartExplain = I('post.restartExplain');
            if(M('repair_list')->where(array('id' => $id,'scId' => $scId))->setField(array(
                'restartExplain' => $restartExplain,
                'state' => 1,
                'reciveTime' => null,
                'applyTime' => date('Y-m-d H:i:s'),
                'arriveTime' => null,
                'repairReture' => 0,
                'repairState' => 0,
                'receiveUserId' => null,
                'completionTime' => null
            ))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'back'){
            $id = I('post.id');
            if(M('repair_list')->where(array('id' => $id,'scId' => $scId))->delete()){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'getTypeList'){
            $data = M('repair_type')->field('repairType,id')->where(array('scId' => $scId))->select();
            $array = array(
                'repairType' => '全部',
                'id' => -1
            );
            $i = 1;
            $return = array();
            $return[0] =$array;
            foreach($data as $key => $value){
                $return[$i] = $value;
                $i++;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
        }
        if($type == 'getTypeListOne'){
            $data = M('repair_type')->field('repairType,id')->where(array('scId' => $scId))->select();
            $i = 0;
            foreach($data as $key => $value){
                $return[$i] = $value;
                $i++;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
        }
        if($type == 'createOrUpdataRepairList'){
            $myRepairId = I('post.id');
            $repairTypeId = I('post.repairTypeId');
            $repairName = I('post.repairName');
            $repairAddress = I('post.repairAddress');
            $phone = I('post.phone');
            $repairContent = I('post.repairContent');
            if(I('post.logo')){
                $logo = serialize(I('post.logo'));
            }else{
                $logo = serialize(array());
            }
            if($myRepairId){
                $data = array(
                    'repairTypeId' => $repairTypeId,
                    'repairName' => $repairName,
                    'repairTypeId' => $repairTypeId,
                    'repairAddress' => $repairAddress,
                    'phone' => $phone,
                    'repairContent' => $repairContent,
                    'logo' => $logo,
                    'assetsNumber' => I('post.assetsNumber')
                );
                if(M('repair_list')->where(array('id' => $myRepairId,'scId' => $scId))->setField($data)){
                    $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
                }
                $this->returnJson(array('statu' => 1, 'message' => 'fail'),true);
            }else{
                $data = array(
                    'repairTypeId' => $repairTypeId,
                    'repairName' => $repairName,
                    'repairTypeId' => $repairTypeId,
                    'repairAddress' => $repairAddress,
                    'phone' => $phone,
                    'repairContent' => $repairContent,
                    'logo' => $logo,
                    'assetsNumber' => I('post.assetsNumber')
                );
                $data['repairNumber'] = uniqid();
                $data['applyTime'] = date('Y-m-d H:i:s');
                $data['state'] = 1;
                $data['userId'] =$userId;
                $data['userName'] = $usernamename;
                $data['scId'] = $scId;
                if(M('repair_list')->add($data)){
                    $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
                }
                $this->returnJson(array('statu' => 0, 'message' => '资产编号填写出错'),true);
            }
        }
        if($type == 'deleteRepairList'){
            $id = I('post.id');
            foreach ($id as $key => $value){
                if(M('repair_list')->where(array('id' => $value,'scId' => $scId))->delete()){
                    M('repair_handle')->where(array('repairId' => $value))->delete();
                }
            }
            $this->returnJson(array('statu' => 1, 'message' => '成功'),true);
        }
        if($type == 'check'){
            $id = I('post.id');
            if(M('repair_list')->where(array('id' => $id,'scId' => $scId))->setField(array('state' =>4,'repairState' =>3,'acceptanceTime' => date('Y-m-d H:i:s')))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'uploda'){
            if($url = $this->uploads()){
                $this->returnJson(array('statu' => 1,'url' => $url),true);
            }else{
                $this->returnJson(array('statu' => 0),true);
            }
        }
    }
    public function repairList(){//报修空间
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck',false);
        $userId = $scId['userId'];
        $scId = $scId['scId'];
        if($type == 'getRepairList'){
            $repairType = $_GET['repairType'];
            $id = $_GET['id'];
            $state = $_GET['state'];
            $startTime = '2010-09-13 00:00:00';
            $endTime = date('Y-m-d H:i:s');
            if($_GET['startTime']){
                $startTime = $_GET['startTime'];
            }
            if($_GET['endTime']){
                $endTime = $_GET['endTime'];
            }
            if($id == -1){
                if($state == 2){
                    $data = M('repair_list')->where(array('scId' => $scId,'state' =>array(array('eq',2),array('eq',3),'or'),'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }else{
                    $data = M('repair_list')->where(array('scId' => $scId,'state' =>$state,'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }
            }else{
                if($state == 2){
                    $data = M('repair_list')->where(array('repairTypeId' => $id,'scId' => $scId,'state' =>array(array('eq',2),array('eq',3),'or'),'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }else{
                    $data = M('repair_list')->where(array('repairTypeId' => $id,'scId' => $scId,'state' =>$state,'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }
            }
            $allTypeRel = array();
            $ddd = M('repair_type')->where(array('scId' => $scId))->select();
            foreach ($ddd as $key => $value){
                $allTypeRel[$value['id']]['type'] = $value['repairType'];
                $user = unserialize($value['user']);
                foreach($user as $key1 => $value1){
                    $allTypeRel[$value['id']]['user'][$value1['id']] = $value1['name'];
                }
            }
            foreach($data as $key => $value){
                $zn = 0;
                if($data[$key]['repairReturn'] == 0){
                    $zn = '无';
                }else{
                    $zn = '已反馈';
                }
                $data[$key]['repairType'] = $allTypeRel[$value['repairTypeId']]['type'];
                $data[$key]['repairUserName'] =$allTypeRel[$value['repairTypeId']]['user'][$value['receiveUserId']];
                $data[$key]['repairReturnZn'] = $zn;
                $data[$key]['stateName'] = $this->stateToZn($data[$key]['state']);
                $valueData = $_GET['valueData'];
                if($valueData){
                    $returnList = array();
                    if($valueData){
                        foreach($data as $key => $value){
                            $i = 0;
                            foreach($value as $key1 => $value1){
                                if(count(explode($valueData,$value1))>1){
                                    $i++;
                                }
                            }
                            if($i>=1){
                                unset($value['scId']);
                                $returnList[] = $value;
                            }
                        }
                        $data = $returnList;
                    }
                }
                $returnList = array();
                $sort = $_GET['sort'];
                $sortData = $_GET['sortData'];
                if($sort){
                    $returnList = array();
                    $data1 = $data;
                    foreach ($data as $key => $value){
                        $max = array();
                        $kk = 0;
                        foreach ($data1 as $key1 => $value1) {
                            if ($value1[$sort] >= $max[$sort]) {
                                $max = $value1;
                                $kk = $key1;
                            }
                        }
                        $returnList[] = $max;
                        unset($data1[$kk]);
                    }
                    if ($sortData == 'inc') {
                        krsort($returnList);
                    }
                    $data = $returnList;
                }
            }
            if($repairType == 'list'){
                foreach($data as $key => $value){
                    $data[$key]['logo'] = unserialize($data[$key]['logo']);
                    if(!is_array($data[$key]['logo'])){
                        $data[$key]['logo'] = array();
                    }
                }
                $this->returnJson(array('statu' => 1,'message' =>1,'data' => $data),true);
            }
            if($repairType == 'export'){
                $tr = $this->exportTr();
                $this->export($data,$tr);
            }
        }
        if($type == 'getTypeList'){
            $data = M('repair_type')->field('repairType,id')->where(array('scId' => $scId))->select();
            $array = array(
                'repairType' => '全部',
                'id' => -1
            );
            $i = 1;
            $return = array();
            $return[0] =$array;
            foreach($data as $key => $value){
                $return[$i] = $value;
                $i++;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
        }
    }
    public function repairTask(){//维修任务
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck',false);
        $userId = $scId['userId'];
        $scId = $scId['scId'];
        $array = array();
        if($type == 'uploda'){
            if($url = $this->uploads()){
                $this->returnJson(array('statu' => 1,'url' => $url),true);
            }else{
                $this->returnJson(array('statu' => 0),true);
            }
        }
        if($type == 'getRepairTaskList'){
            $repairType = $_GET['repairType'];
            $id = $_GET['id'];
            $state = $_GET['state'];
            $startTime = '2010-09-13 00:00:00';
            $endTime = date('Y-m-d H:i:s');
            if($_GET['startTime']){
                $startTime = $_GET['startTime'];
            }
            if($_GET['endTime']){
                $endTime = $_GET['endTime'];
            }
            if($id == -1){
                if($state == 2){
                    $data = M('repair_list')->where(array('scId' => $scId,'state' =>array(array('eq',2),array('eq',3),'or'),'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }else{
                    $data = M('repair_list')->where(array('scId' => $scId,'state' =>$state,'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }
            }else{
                if($state == 2){
                    $data = M('repair_list')->where(array('repairTypeId' => $id,'scId' => $scId,'state' =>array(array('eq',2),array('eq',3),'or'),'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }else{
                    $data = M('repair_list')->where(array('repairTypeId' => $id,'scId' => $scId,'state' =>$state,'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                }
            }
            $allTypeRel = array();
            $ddd = M('repair_type')->where(array('scId' => $scId))->select();
            foreach ($ddd as $key => $value){
                $allTypeRel[$value['id']]['type'] = $value['repairType'];
                $user = unserialize($value['user']);
                foreach($user as $key1 => $value1){
                    $allTypeRel[$value['id']]['user'][$value1['id']] = $value1['name'];
                }
            }
            foreach($data as $key => $value) {
                $statu = 0;
                if($allTypeRel[$value['repairTypeId']]['user'][$userId]) {
                    $statu = 1;
                }
                if($statu) {
                    $zn = 0;
                    if ($value['repairReturn'] == 0) {
                        $zn = '无';
                    } else {
                        $zn = '已反馈';
                    }
                    $value['repairUserName'] = $allTypeRel[$value['repairTypeId']]['user'][$value['receiveUserId']];
                    $value['repairType'] = $allTypeRel[$value['repairTypeId']]['type'];
                    $value['repairReturnZn'] = $zn;
                    $value['logo'] = unserialize($value['logo']);
                    $value['stateName'] = $this->stateToZn($value['state']);
                    $array[] = $value;
                }
            }
            $data = $array;
            $valueData = $_GET['valueData'];
            if($valueData){
                $returnList = array();
                if($valueData){
                    foreach($data as $key => $value){
                        $i = 0;
                        foreach($value as $key1 => $value1){
                            if(count(explode($valueData,$value1))>1){
                                $i++;
                            }
                        }
                        if($i>=1){
                            unset($value['scId']);
                            $returnList[] = $value;
                        }
                    }
                    $data = $returnList;
                }
            }
            $returnList = array();
            $sort = $_GET['sort'];
            $sortData = $_GET['sortData'];
            if($sort) {
                $returnList = array();
                $data1 = $data;
                foreach ($data as $key => $value) {
                    $max = array();
                    $kk = 0;
                    foreach ($data1 as $key1 => $value1) {
                        if ($value1[$sort] >= $max[$sort]) {
                            $max = $value1;
                            $kk = $key1;
                        }
                    }
                    $returnList[] = $max;
                    unset($data1[$kk]);
                }
                if ($sortData == 'inc') {
                    krsort($returnList);
                }
                $data = $returnList;
            }
            if($repairType == 'list'){
                foreach($data as $key => $value){
                    $data[$key]['logo'] = unserialize($data[$key]['logo']);
                    if(!is_array($data[$key]['logo'])){
                        $data[$key]['logo'] = array();
                    }
                }
                $this->returnJson(array('statu' => 1,'message' =>1,'data' => $data),true);
            }
            if($repairType == 'export'){
                $tr = $this->exportTr();
                $this->export($data,$tr);
            }
        }
        if($type == 'getTypeList'){
            $data = M('repair_type')->field('repairType,id')->where(array('scId' => $scId))->select();
            $array = array(
                'repairType' => '全部',
                'id' => -1
            );
            $i = 1;
            $return = array();
            $return[0] =$array;
            foreach($data as $key => $value){
                $return[$i] = $value;
                $i++;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
        }
        if($type == 'Orders'){
            $id = I('post.id');
            if(M('repair_list')->where(array('id' => $id,'scId' => $scId))->setField(array('repairState' =>1,'receiveUserId'=> $userId,'reciveTime' => date('Y-m-d H:i:s')))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'handle'){
            $id = I('post.id');
            if($_POST['logo']){
                $logo = serialize(I('post.logo'));
            }else{
                $logo = serialize(array());
            }
            if(M('repair_handle')->add(array(
                'repairId' => $id,
                'state' =>I('post.state'),
                'method' => I('post.method'),
                'reason' => I('post.reason'),
                'replaceType' => I('post.replaceType'),
                'logo' => $logo,
                'createTime' => date('Y-m-d H:i:s'),
                'feedback' => I('post.feedback'),
                'userId' => $userId
            ))){
                if($_POST['state'] ==1){
                    if(M('repair_list')->where(array('id' => $id,'scId' => $scId))->setField(array(
                        'repairState' =>2,
                        'completionTime' => date('Y-m-d H:i:s'),
                        'method' => I('post.method'),
                        'reason' => I('post.reason'),
                        'replaceType' => I('post.replaceType'),
                        'feedback' => I('post.feedback'),
                        'arriveTime' => I('post.arriveTime'),
                    ))){
                        if(M('repair_list')->where(array('id' => $id,'scId' => $scId))->setField(array('state' =>2))){
                            $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
                        }
                    }
                }else{
                    if(M('repair_list')->where(array('id' => $id,'scId' => $scId))->setField(array(
                        'repairState' =>2,
                        'completionTime' => date('Y-m-d H:i:s'),
                        'method' => I('post.method'),
                        'reason' => I('post.reason'),
                        'replaceType' => I('post.replaceType'),
                        'feedback' => I('post.feedback'),
                        'arriveTime' => I('post.arriveTime'),
                    ))){
                        if(M('repair_list')->where(array('id' => $id,'scId' => $scId))->setField(array('state' =>3))){
                            $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
                        }
                    }
                }
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }else{
                $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
            }
        }
        if($type == 'upload'){
            if($url = $this->uploads()){
                $this->returnJson(array('statu' => 1,'message' => 'uploda  succeess','url' => $url),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'uploda  fail','url' => ''),true);
        }
        if($type == 'getRepairList'){
            $id = $_GET['id'];
            $data = M('repair_list')->where(array('id' => $id,'scId' => $scId))->find();
            $typeId = $data['repairTypeId'];
            $user = M('repair_type')->where(array('id' => $typeId,'scId' => $scId))->find();
            $data['repairType'] = $user['repairType'];
            $data['logo'] = unserialize($data['logo']);
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $data),true);
        }
    }

    public function repairStatistics(){//维修统计
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck',false);
        $userId = $scId['userId'];
        $scId = $scId['scId'];
        if($type == 'getList'){
            $startTime = '2010-09-13 00:00:00';
            $endTime = date('Y-m-d H:i:s');
            if($_GET['startTime']){
                $startTime = $_GET['startTime'];
            }
            if($_GET['endTime']){
                $endTime = $_GET['endTime'];
            }
            $typeList = $_GET['typeList'];
            if($typeList == 1){//类别
                $data = M('repair_list')->field('repairTypeId,state,repairState')->where(array('scId' => $scId,'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                $type = M('repair_type')->field('id,repairType')->where(array('scId' => $scId))->select();
                $typeRe = array();
                foreach($type as $key => $value){
                    $typeRe[$value['id']] = $value['repairType'];
                }
                $array = array();
                $allCount = 0;
                $wait = 0;
                $have = 0;
                $haved = 0;
                $check = 0;
                foreach($data as $key => $value){
                    $allCount++;
                    $array[$value['repairTypeId']][$value['repairState']]['count']++;
                    $array[$value['repairTypeId']][$value['repairState']]['type'] = $this->getWxState($value['repairState']);
                    $array[$value['repairTypeId']][$value['repairState']]['repairState'] = $value['repairState'];
                    if($value['repairState'] == 0){
                        $wait++;
                    }
                    if($value['repairState'] == 1){
                        $have++;
                    }
                    if($value['repairState'] == 2){
                        $haved++;
                    }
                    if($value['repairState'] == 3){
                        $check++;
                    }
                }
                $arrayRe = array();
                $i = 0;
                foreach($array as $key => $value){
                    $arrayRe[$i]['type'] = $typeRe[$key];
                    if(isset($value[0])){
                        $arrayRe[$i]['data'][0] = $array[$key][0];
                    }else{
                        $arrayRe[$i]['data'][0] = array(
                            'count' =>0,
                            'type' => $this->getWxState(0),
                            'repairState' => 0
                        );
                    }
                    if(isset($value[1])){
                        $arrayRe[$i]['data'][1] = $array[$key][1];
                    }else{
                        $arrayRe[$i]['data'][1] = array(
                            'count' =>0,
                            'type' => $this->getWxState(1),
                            'repairState' => 1
                        );
                    }
                    if(isset($value[2])){
                        $arrayRe[$i]['data'][2] = $array[$key][2];
                    }else{
                        $arrayRe[$i]['data'][2] = array(
                            'count' =>0,
                            'type' => $this->getWxState(2),
                            'repairState' => 2
                        );
                    }
                    if(isset($value[3])){
                        $arrayRe[$i]['data'][3] = $array[$key][3];
                    }else{
                        $arrayRe[$i]['data'][3] = array(
                            'count' =>0,
                            'type' => $this->getWxState(3),
                            'repairState' => 3
                        );
                    }
                    $i++;
                }
                $count = count($arrayRe);
                $arrayRe[$count]['type'] = '合计';
                $arrayRe[$count]['data'][0] =array('count' => $wait,'type' => '待维修','repairState' => 0);
                $arrayRe[$count]['data'][1] =array('count' => $have,'type' => '维修中','repairState' => 1);
                $arrayRe[$count]['data'][2] =array('count' => $haved,'type' => '已维修','repairState' => 2);
                $arrayRe[$count]['data'][3] =array('count' => $check,'type' => '已验收','repairState' => 3);
                foreach($arrayRe as $key => $value){
                    $count = 0;
                    foreach($value['data'] as $key1 => $value1){
                        $count = $count + $value1['count'];
                    }
                    $arrayRe[$key]['count'] = $count;
                }
                $table = array();
                $bt = array();
                $wait = array();
                $middle = array();
                $have = array();
                $ys  = array();
                foreach($arrayRe as $key => $value){
                    $bt[] = $value['type'];
                    $wait[] =  $value['data'][0]['count'];
                    $middle[] =  $value['data'][1]['count'];
                    $have[] =  $value['data'][2]['count'];
                    $ys[] =  $value['data'][3]['count'];
                    $table[$key]['type'] = $value['type'];
                    $table[$key]['wait'] = $value['data'][0]['count'];
                    $table[$key]['middle'] = $value['data'][1]['count'];
                    $table[$key]['have'] = $value['data'][2]['count'];
                    $table[$key]['ys'] = $value['data'][3]['count'];
                }
                $tb = array();
                foreach($arrayRe as $key => $value){

                }
                $data = array(
                    'table' => $table,
                    'tb' => array(
                        'bt' => $bt,
                        'wait' => $wait,
                        'middle' => $middle,
                        'have' => $have,
                        'ys' => $ys,
                    ),
                );
                $this->returnJson(array('statu' =>1,'message' =>'success','data' => $data),true);
            }
            if($typeList == 2){//用户
                $wait = 0;
                $have = 0;
                $haved = 0;
                $check = 0;
                $data = M('repair_list')->field('repairTypeId,state,repairState,receiveUserId')->where(array('scId' => $scId,'applyTime' => array(array('gt',$startTime),array('lt',$endTime))))->order('applyTime desc')->select();
                $type = M('repair_type')->field('id,user')->where(array('scId' => $scId))->select();
                $typeRe = array();
                foreach($type as $key => $value){
                    $user = unserialize($value['user']);
                    foreach($user as $key1 => $value1){
                        $typeRe[$value1['id']] = $value1['name'];
                    }
                }
                $array = array();
                foreach($data as $key => $value){
                    //$array[$value['receiveUserId']][$value['repairState']]['data'][] = $value;
                    $array[$value['receiveUserId']][$value['repairState']]['count']++;
                    $array[$value['receiveUserId']][$value['repairState']]['type'] = $this->getWxState($value['repairState']);
                    $array[$value['receiveUserId']][$value['repairState']]['repairState'] = $value['repairState'];
                    if($value['repairState'] == 0){
                        $wait++;
                    }
                    if($value['repairState'] == 1){
                        $have++;
                    }
                    if($value['repairState'] == 2){
                        $haved++;
                    }
                    if($value['repairState'] == 3){
                        $check++;
                    }
                }
                $i = 0;
                foreach($array as $key => $value){
                    $arrayRe[$i]['type'] = $typeRe[$key];
                    if(isset($value[0])){
                        $arrayRe[$i]['data'][0] = $array[$key][0];
                    }else{
                        $arrayRe[$i]['data'][0] = array(
                            'count' =>0,
                            'type' => $this->getWxState(0),
                            'repairState' => 0
                        );
                    }
                    if(isset($value[1])){
                        $arrayRe[$i]['data'][1] = $array[$key][1];
                    }else{
                        $arrayRe[$i]['data'][1] = array(
                            'count' =>0,
                            'type' => $this->getWxState(1),
                            'repairState' => 1
                        );
                    }
                    if(isset($value[2])){
                        $arrayRe[$i]['data'][2] = $array[$key][2];
                    }else{
                        $arrayRe[$i]['data'][2] = array(
                            'count' =>0,
                            'type' => $this->getWxState(2),
                            'repairState' => 2
                        );
                    }
                    if(isset($value[3])){
                        $arrayRe[$i]['data'][3] = $array[$key][3];
                    }else{
                        $arrayRe[$i]['data'][3] = array(
                            'count' =>0,
                            'type' => $this->getWxState(3),
                            'repairState' => 3
                        );
                    }
                    $i++;
                }
                $count = count($arrayRe);
                $arrayRe[$count]['type'] = '合计';
                $arrayRe[$count]['data'][0] =array('count' => $wait,'type' => '待维修','repairState' => 0);
                $arrayRe[$count]['data'][1] =array('count' => $have,'type' => '维修中','repairState' => 1);
                $arrayRe[$count]['data'][2] =array('count' => $haved,'type' => '已维修','repairState' => 2);
                $arrayRe[$count]['data'][3] =array('count' => $check,'type' => '已验收','repairState' => 3);
                foreach($arrayRe as $key => $value){
                    $count = 0;
                    foreach($value['data'] as $key1 => $value1){
                        $count = $count + $value1['count'];
                    }
                    $arrayRe[$key]['count'] = $count;
                }
                $table = array();
                $bt = array();
                $wait = array();
                $middle = array();
                $have = array();
                $ys  = array();
                foreach($arrayRe as $key => $value){
                    $bt[] = $value['type'];
                    $wait[] =  $value['data'][0]['count'];
                    $middle[] =  $value['data'][1]['count'];
                    $have[] =  $value['data'][2]['count'];
                    $ys[] =  $value['data'][3]['count'];
                    $table[$key]['type'] = $value['type'];
                    $table[$key]['wait'] = $value['data'][0]['count'];
                    $table[$key]['middle'] = $value['data'][1]['count'];
                    $table[$key]['have'] = $value['data'][2]['count'];
                    $table[$key]['ys'] = $value['data'][3]['count'];
                }
                $tb = array();
                foreach($arrayRe as $key => $value){

                }
                $data = array(
                    'table' => $table,
                    'tb' => array(
                        'bt' => $bt,
                        'wait' => $wait,
                        'middle' => $middle,
                        'have' => $have,
                        'ys' => $ys,
                    ),
                );
                $this->returnJson(array('statu' =>1,'message' =>'success','data' => $data),true);
            }
        }
    }
    private function getWxState($i){
        $array = array(
            0 => '待维修',
            1 => '维修中',
            2 => '已维修',
            3 => '已验收',
        );
        return $array[$i];
    }
    private function exportTr(){
        $tr = array(
            '0' => array(
                'en' => 'repairNumber',
                'zh' => '保修单号'
            ),
            '1' => array(
                'en' => 'repairName',
                'zh' => '报修物品'
            ),
            '2' => array(
                'en' => 'repairContent',
                'zh' => '报修内容'
            ),
            '3' => array(
                'en' => 'phone',
                'zh' => '联系方式'
            ),
            '4' => array(
                'en' => 'repairType',
                'zh' => '报修类别'
            ),
            '5' => array(
                'en' => 'repairAddress',
                'zh' => '维修地点'
            ),
            '6' => array(
                'en' => 'repairUserName',
                'zh' => '维修人员'
            ),
            '7' => array(
                'en' => 'applyTime',
                'zh' => '申请时间'
            ),
            '8' => array(
                'en' => 'reciveTime',
                'zh' => '接单时间'
            ),
            '9' => array(
                'en' => 'arriveTime',
                'zh' => '到达时间'
            ),
            '10' => array(
                'en' => 'stateName',
                'zh' => '维修状态'
            ),
            '11' => array(
                'en' => 'stateName',
                'zh' => '维修状态'
            ),
            '11' => array(
                'en' => 'repairReturnZn',
                'zh' => '维修反馈'
            )
        );
        return $tr;
    }
}