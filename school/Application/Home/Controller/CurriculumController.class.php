<?php
/**
 * Created by PhpStorm.
 * User: xiaolong
 * Date: 2017/6/22
 * Time: 14:15
 * 排课管理
 */
namespace Home\Controller;
//use Think\Controller;
//use Vendor\PHPExcel\PHPExcel;
class CurriculumController extends Base {
    private $classTable = array();
    private $teacherTable = array();
    private $jxPlanTable = array();
    private $subjectLimit = array(); //科目排课限制
    private $teacherLimitData = array(); //教师限制
    private $hbClass = array(); //合班
    private $checkXh = 0;
    private $allWeek = 0;
    private $allDayM = 0;
    private $y = 0;
    private $x = 0;
    private $yxLevel = array();
    public function pkList(){

    }
    public function getSubjectTeacher(){
        $type = I('get.type');
        if($type == 'pkList'){
            $subjectId = I('get.subjectId');
            $pkListId = I('get.pkListId');
            $session = $this->get_session('loginCheck',false);
            $scId = $session['scId'];
            $list = M('pk_list')->where(array('id' => $pkListId,'scId' => $scId))->find();
            $rangeList = $list['pkRange'];
            $data = M('')->query("SELECT * FROM mks_jw_schedule WHERE gradeId in($rangeList) AND scId=$scId AND subjectId=$subjectId  order BY gradeName");
            $hb = M('pk_hb_set')->where(array('pkListId' => $pkListId,'subjectId' => $subjectId,'scId' => $scId))->select();
            foreach($data as $key => $value){
                foreach($hb as $key1 => $value1){
                    if($value['techerId'] ==  $value1['teacherId'] && $value['subjectId'] ==  $value1['subjectId']){
                        $data11 = unserialize($value1['classSet']);
                        foreach($data11 as $key2 => $value2){
                            if($value['gradeId'] == $value2['gradeId'] && $value['classId'] == $value2['classId'] ){
                                unset($data[$key]);
                            }
                        }
                    }
                }
            }
            $return = array();
            $i = 0;
           foreach($data as $key => $value){
               if($value['techerId']){
                   $return[$i] = $value;
                   $i++;
               }
           }
            $this->returnJson(array('statu' => 1,'message' => 'success' ,'data' => $return),true);
        }
    }
    public function getTeacherList(){//得到教师上课列表
        $type = I('get.type');
        if($type == 'pkList'){
            $session = $this->get_session('loginCheck',false);
            $scId = $session['scId'];
            $pkListId = I('get.pkListId');
            $list = M('pk_list')->where(array('id' => $pkListId,'scId' => $scId))->find();
            $rangeList = explode(',',$list['pkRange']);
            $len = count($rangeList);
            $i = 1;
            $str = '';
            foreach($rangeList as $key => $value){
                $i++;
                if($i<=$len){
                    $str = 'gradeId='.$value.' or '.$str;
                }else{
                    $str = $str.'gradeId='.$value;
                }
            }
            $data  = M('')->query("SELECT * FROM mks_jw_schedule WHERE scId=$scId and ($str) GROUP BY techerName");
            $return  = array();
            foreach($data as $key => $value){
                $return[$value['subjectId']][] = $value;
            }
            $returnData = array();
            foreach($return as $key => $value){
                $returnData[] = $value;
            }
            $returnDataTrue = array();
            foreach($returnData as $key => $value){
                $returnDataTrue[$key]['techerName'] = $returnData[$key][0]['subject'];
                foreach($value as $key1 => $value1){
                    unset($value1['subject']);
                    $returnDataTrue[$key]['data'][] =  $value1;
                }
            }
            //print_r($returnData);
            $data = M('pk_jx_plan')->where(array('scId' => $scId,'pkListId' => $pkListId))->find();
            $data = unserialize($data['classSet']);
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $returnDataTrue),true);
        }
    }
    public function getSubjectList(){//得到课程列表
        $type = I('get.type');
        if($type == 'pkList'){
            $session = $this->get_session('loginCheck',false);
            $scId = $session['scId'];
            if($data = M('subject')->where(array('scId' => $scId))->select()){
                $this->returnJson(array('statu' => 1,'message' => 'success','data' => $data),true);
            }else{
                $this->returnJson(array('statu' => 0,'message' => 'fail','data' => ''),true);
            }
        }
    }
        public function deletePkList(){
        $type = I('get.type');
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        if($type == 'pkDelete'){
            $model = M('pk_list');
            /*
             * 外键已经建立用户表外键
             * */
            $model->startTrans();
            $id = I('post.id');
            $true = M('pk_list')->where(array('id' => $id,'scId' => $scId))->delete();
            $model->commit();
            if($true){
                $this->returnJson(array('statu' => 1, 'message' => 'delete success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'delete fail'),true);
        }
    }
    public function getGradeAndClass(){//得到年级班级
        $type = I('get.type');
        if($type== 'getList'){
            $session = $this->get_session('loginCheck',false);
            $scId = $session['scId'];
            $pkListId = I('get.pkListId');
            $list = M('pk_list')->where(array('id' => $pkListId,'scId' => $scId))->find();
            $rangeList = explode(',',$list['pkRange']);
            $data = array();
            $i = 0;
            foreach($rangeList as $key => $value){
                $grade = M('grade')->where(array('scId' => $scId,'gradeid'=> $value))->find();
                $gradeName = $grade['name'];
                $class = M('class')->where(array('scId' => $scId,'grade'=> $value))->order('classname')->select();
                foreach($class as $key1 => $value1){
                    $class[$key1]['name'] = $class[$key1]['classname'];
                    unset($class[$key1]['classname']);
                    $class[$key1]['gradeId'] = $class[$key1]['grade'];
                    unset($class[$key1]['grade']);
                }
                $data[$i]['name'] = $this->gradeToZhong($gradeName);
                $data[$i]['gradeId'] =  $value;
                $data[$i]['data'] = $class;
                $i++;
            }
            $this->returnJson(array('statu' => 1,'data' => $data,'message' => 'success'),true);
        }
    }
    private function gradeToZhong($gradeName){
        $return = array(
            '1' => '一年级',
            '2' => '二年级',
            '3' => '三年级',
            '4' => '四年级',
            '5' => '五年级',
            '6' => '六年级',
            '7' => '初一',
            '8' => '初二',
            '9' => '初三',
            '10' => '高一',
            '11' => '高二',
            '12' => '高三',
        );
        return $return[$gradeName];
    }
    private function gradeToEn($str){
        $return = array(
            '1' => '一年级',
            '2' => '二年级',
            '3' => '三年级',
            '4' => '四年级',
            '5' => '五年级',
            '6' => '六年级',
            '7' => '初一',
            '8' => '初二',
            '9' => '初三',
            '10' => '高一',
            '11' => '高二',
            '12' => '高三',
        );
        foreach($return as $key => $value){
            if($value == $str){
                return $key;
            }
        }
    }
    public function pkPlan(){
        $type = I('get.type');
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        if($type == 'pkList'){
            $data = M('pk_list')->where(array('scId' => $scId))->select();
            $grade = M('grade')->where(array('scId' => $scId ))->select();
            //print_r($grade);
            //print_r($data);
            foreach($data as $key => $value){
                $pkRang = explode(',',$value['pkRange']);
                $name = null;
                //print_r($pkRang);
                $count = count($pkRang);
                $i =1;
                $name = '';
                foreach($pkRang as $key1 => $value1){
                    foreach($grade as $key2 => $value2){
                        if($value1 == $value2['gradeid']){
                            if($i==$count){
                                $name=$name.$value2['name'];
                            }else{
                                $name=$name.$value2['name'].',';
                            }

                        }
                    }
                    $i++;
                }
                $data[$key]['name'] = $name;
                $data[$key]['createTime'] = $value['startTime'].'到'.$value['endTime'];
            }
            $this->returnJson(array('statu' =>1,'message' => 'success','data' =>$data),true);
        }
        if($type == 'getPkRang'){
            $data = M('grade')->where(array('scId' => $scId))->order('name')->select();
            $this->returnJson($data,true);
        }
        if($type == 'pkCreate'){
            $st = explode('(',I('post.startTime'));
            $st1 = explode('(',I('post.endTime'));
            $data = array(
                'pkPlanName' => I('post.pkPlanName'),
                'pkRange' => I('post.pkRange'),
                'startTime' => date('Y-m-d',strtotime($st[0])),
                'endTime' => date('Y-m-d',strtotime($st1[0])),
                'createTime' =>  date('Y-m-d H:i:s'),
                'ifStartUp' =>  0,
                'scId' => $scId
            );
            if($pkListId = M('pk_list')->add($data)){
                if(I('post.id')){
                    $jxPlan = M('pk_jx_plan')->where(array('pkListId' => I('post.id'),'scId' => $scId))->find();
                    unset($jxPlan['id']);
                    $jxPlan['pkListId'] = $pkListId;
                    M('pk_jx_plan')->add($jxPlan);
                    $pk_time = M('pk_time')->where(array('pkListId' => I('post.id'),'scId' => $scId))->find();
                    unset($pk_time['id']);
                    $pk_time['pkListId'] = $pkListId;
                    M('pk_time')->add($pk_time);
                }
                $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'add fail'),true);
        }
    }
    public function test(){
        $data = M('pk_time')->find();
        $data = unserialize($data['ClassSet']);
        $pkList = M('pk_list')->where(array('scId' => 2, 'id' => 32))->find();
        $this::createPlanGo($scId =2, $pk_week_limit = $data['week'], $data['section']['morningCount'],$data['section']['noon'],$data['section']['night'],32,$pkList['pkRange']);
    }
    public function createPkPlan(){//排课时间
        $type = I('get.type');
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        if($type == 'pkTimeList'){//得到排课时间设置
            $pkListId = I('get.pkListId');
            $ddd = M('pk_list')->where(array('scId' => $scId,'id' => $pkListId))->find();
            $ddd = $ddd['ifStartUp'];
            if($data = M('pk_time')->where(array('pkListId' => $pkListId,'scId' => $scId))->find()){
                $wwww = unserialize($data['ClassSet']);
                foreach($wwww['week'] as $key => $value){
                    foreach($value as $key1 => $value1){
                        if($value1){
                            $wwww['week'][$key][$key1] =true;
                        }else{
                            $wwww['week'][$key][$key1] =false;
                        }
                    }
                }
                if($ddd){
                    $ddd = true;
                }else{
                    $ddd = false;
                }
                $this->returnJson(array(
                    'statu' =>1,
                    'id' => $data['id'],
                    'pkListId' => $data['pkListId'],
                    'data' => $wwww,
                    'ifStartUp' => $ddd
                ),true);
            }else{
                if($ddd){
                    $ddd = true;
                }else{
                    $ddd = false;
                }
                $this->returnJson(array(
                    'statu' =>1,
                    'id' => '',
                    'pkListId' => $pkListId,
                    'data' => false,
                    'ifStartUp' => $ddd
                ),true);
            }
        }
        if($type == 'timeSet'){ //创建学习时间
            $data = I('post.data');
            $pkListId = I('post.pkListId');
            if(M('pk_time')->where(array('pkListId' => $pkListId,'scId' => $scId))->find()){
                if(M('pk_time')->where(array('pkListId' => $pkListId,'scId' => $scId))->setField(array(
                    'ClassSet' => serialize($data)
                )) ===false) {
                    $this->returnJson(array('statu' => 0, 'message' => 'add fail'),true);
                }else{
                    $pkList = M('pk_list')->where(array('scId' => $scId, 'id' => $pkListId))->find();
                    $this::createPlanGo($scId, $pk_week_limit = $data['week'], $data['section']['morningCount'],$data['section']['noon'],$data['section']['night'],$pkListId,$pkList['pkRange']);
                    $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
                }
            }else{
                /*$data = array(
                    'week' =>array(
                        '0' => array(
                            1,1,1,1,1,0,0
                        ),
                        '1' => array(
                            1,1,1,1,1,0,0
                        ),
                        '2' => array(
                            1,1,1,1,1,0,0
                        )
                    ),
                    'section'=>array(
                        'morningCount' => 4,
                        'noon' => 4,
                        'night' =>1
                    ),
                    'day' =>array(
                        '0' => array('startTime' => '9:00','endTime' => '9:40'),
                        '1' => array('startTime' => '10:00','endTime' => '10:40'),
                        '2' => array('startTime' => '11:00','endTime' => '11:40'),
                        '3' => array('startTime' => '14:00','endTime' => '14:40'),
                        '4' => array('startTime' => '15:00','endTime' => '15:40'),
                        '5' => array('startTime' => '16:00','endTime' => '16:40'),
                        '6' => array('startTime' => '17:00','endTime' => '17:40'),
                        '7' => array('startTime' => '19:00','endTime' => '19:40'),
                        '8' => array('startTime' => '20:00','endTime' => '20:40'),
                    )
                );*/
                if( M('pk_time')->add(array(
                    'pkListId' => $pkListId,
                    'ClassSet' => serialize($data),
                    'scId' => $scId,
                    'createTime' => strtotime(date('Y-m-d'))
                ))) {
                    $pkList = M('pk_list')->where(array('scId' => $scId, 'id' => $pkListId))->find();
                    $this::createPlanGo($scId, $pk_week_limit = $data['week'], $data['section']['morningCount'],$data['section']['noon'],$data['section']['night'],$pkListId,$pkList['pkRange']);
                    /*$this::createPlanGo($scId =2, $pk_week_limit = array(
                        '0' => array(
                            1,1,1,1,1,0,0
                        ),
                        '1' => array(
                            1,1,1,1,1,0,0
                        ),
                        '2' => array(
                            0,0,0,0,0,0,0
                        )
                    ),$morningCount = 4,$noongCount = 4,$nightCount =2,$pkListId=1,$grade=1);*/
                    $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
                }
            }
            $this->returnJson(array('statu' => 0, 'message' => 'add fail'),true);
        }
    }
    public function createPkTime(){
        $type = I('get.type');
        $data = I('get.data');
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        if($type== 'createPkTime'){
            $pkListId = I('get.pkListId');
            $array = array(
                'pkListId' => $pkListId,
                'scId' =>$scId,
                'weekSet' => serialize($data),
                'createTime' => strtotime(date('Y-m-d'))
            );
            if(M('pk_list')->add($array)){
                $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'add fail'),true);
        }

    }
    public function jxPlan(){//创建教学计划
        $type = I('get.type');
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        if($type == 'createJxPlan'){//创建教学计划表
            $data = I('post.data');
            $pkListId = I('post.pkListId');
            if(M('pk_jx_plan')->where(array('pkListId' =>I('post.pkListId'),'scId' =>$scId))->find()){
                if(M('pk_jx_plan')->where(array('pkListId' => I('post.pkListId'),'scId' =>$scId))->setField(array(
                    'classSet' => serialize($data)
                ))){
                    M('pk_class_before_plan')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
                    M('pk_class_time_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
                    M('pk_curriculum_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
                    M('pk_teacher_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
                    M('pk_teacher_result')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
                    M('pk_class_result')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
                    M('pk_hb_set')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
                    $this->returnJson(array('statu' => 1, 'message' => 'updata success'),true);
                }else{
                    $this->returnJson(array('statu' => 0, 'message' => 'updata success'),true);
                }
            }
            $array = array(
                'scId' => $scId,
                'pkListId' =>I('post.pkListId'),
                'classSet' => serialize($data),
                'createTime' => strtotime(date('Y-m-d'))
            );
            if(M('pk_jx_plan')->add($array)){
                $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'add fail'),true);
        }
        if($type == 'jsPlanList'){//得到教学计划表
            $pkListId = I('get.pkListId');
            $ifCz = M('pk_list')->where(array('scId' => $scId,'id' => $pkListId))->find();
            $checkdd = 0;
            if($ifCz['ifStartUp']){
                $checkdd = 1;
            }
            $subject = M('subject')->where(array('scId' => $scId))->order('subjectid')->select();
            $all = M('pk_default_limit')->where(array('scId' => $scId,'pkListId' => $pkListId))->find();
            $all = unserialize($all['classSet']);
            $allCount = 0;
            foreach($all as $Key => $value){
                foreach($value as $key1 => $value1){
                    if($value1 == 1){
                        $allCount++;
                    }
                }
            }
            if( $return = M('pk_jx_plan')->where(array('pkListId' => $pkListId,'scId' => $scId))->find()){
                $dataReturn = unserialize($return['classSet']);
                $this->returnJson(array('statu' => 1,'message' => 'success','subject' => $subject,'data' => $dataReturn,'allCount' => $allCount,'check'=>$checkdd), true);
            }
            //
            $session = $this->get_session('loginCheck',false);
            $scId = $session['scId'];
            $list = M('pk_list')->where(array('id' => $pkListId,'scId' => $scId))->find();
            $rangeList = explode(',',$list['pkRange']);
            $len = count($rangeList);
            $i = 1;
            $str = '';
            foreach($rangeList as $key => $value){
                $i++;
                if($i<=$len){
                    $str = 'gradeId='.$value.' or '.$str;
                }else{
                    $str = $str.'gradeId='.$value;
                }
            }
            $data  = M('')->query("SELECT * FROM mks_jw_schedule WHERE scId=$scId and ($str) order by gradeName,className");
            $return  = array();
            foreach($data as $key => $value){
                $value['count'] = 0;
                $return[$value['gradeName']][$value['className']][] = $value;
            }
            $array = array();
            foreach($return as $key => $value){
                foreach($value as $key1 => $value1){
                    $array[] = $value1;
                }
            }
            $i = 0;
            $array1 =array();
            foreach($array as $key => $value){
                foreach($subject as $key2 => $value2){
                    $true = 0;
                    foreach($value as $key1 => $value1){
                        if($value2['subjectid'] == $value1['subjectId']){
                            $array1[$i]['data'][] = $value1;
                            $true = 1;
                        }
                    }
                    if(!$true){
                        $array1[$i]['data'][] = array('id' => '','className' => '','subject' => $value2['subjectname'],'techerId' => '','techerName' => '','subjectId' => $value2['subjectid'],'createTime' =>'',
                            'userId' => '','gradeId' =>'','classId' => '','scId' => '','gradeName' =>'','count' =>0
                        );
                    }
                }
                $array1[$i]['className'] = $value1['className'];
                $array1[$i]['gradeId'] = $value1['gradeId'];
                $array1[$i]['classId'] = $value1['classId'];
                $array1[$i]['gradeName'] = $value1['gradeName'];
                $i++;
            }
            $this->returnJson(array('statu' => 1,'message' => 'success','subject' => $subject,'data' => $array1,'allCount' => $allCount,'check' => $checkdd),true);
        }
    }
    public function limitCreateList(){//排课限制.
        $type = I('get.type');
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        if($type == 'classSkTimeLimitList'){//得到班级上课限制列表
            $pkListId = I('get.pkListId');
            //得到班级总课时
            $all = M('pk_default_limit')->where(array('scId' => $scId,'pkListId' => $pkListId))->find();
            $all = unserialize($all['classSet']);
            $allCount = 0;
            foreach($all as $Key => $value){
                foreach($value as $key1 => $value1){
                    if($value1 == 1){
                        $allCount++;
                    }
                }
            }
            //得到班级上课总课时
            $allClassCount = M('pk_jx_plan')->where(array('pkListId' => $pkListId,'scId' => $scId))->find();
            $allClass = unserialize($allClassCount['classSet']);
            $thisClass = array();
            $allClassCountCount = 0;
            foreach($allClass as $key => $value){
                if($value['gradeId'] ==  I('get.gradeId') && $value['classId'] == I('get.classId')){
                    $thisClass = $value['data'];
                    foreach($thisClass as $key1 => $value1){
                        $allClassCountCount = $allClassCountCount+$value1['count'];
                    }
                }
            }
            $array = array();
            if($data = M('pk_class_before_plan')->where(array('pkListId' => $pkListId,'scId' =>$scId,'gradeId' => I('get.gradeId'),'classId' => I('get.classId')))->find()){
                $data['classSet'] = unserialize($data['classSet']);
                foreach($data['classSet'] as $key => $value){
                    foreach($value as $key1 => $value1){
                        if($value1['statu'] == 5){
                            $value1['statu'] = 5;
                            $data['classSet'][$key][$key1] = $value1;
                        }
                        if($value1 == 1){
                            $data['classSet'][$key][$key1] = array(
                                'subject' => '',
                                'subjectId' => '',
                                'statu' => 1,
                                'techerName' => '',
                            );
                        }
                        if($value1 == 0){
                            $data['classSet'][$key][$key1] = array(
                                'subject' => '',
                                'subjectId' => '',
                                'statu' => 0,
                                'techerName' => '',
                            );
                        }
                        if($value1 == 2){
                            $data['classSet'][$key][$key1] = array(
                                'subject' => '',
                                'subjectId' => '',
                                'statu' => 2,
                                'techerName' => '',
                            );
                        }
                    }
                }
                $this->returnJson(array('statu' => 1,'allTableCount' => $allCount,'allClassCount' => $allClassCountCount, 'message' => 'success','data' => $data),true);
            }
            if( $data = M('pk_class_time_limit')->where(array('pkListId' => $pkListId,'scId' =>$scId,'gradeId' => I('get.gradeId'),'classId' => I('get.classId')))->find()){
                $data['classSet'] = unserialize($data['classSet']);
                foreach($data['classSet'] as $key => $value){
                    foreach($value as $key1 => $value1){
                        if($value1 == 1){
                            $data['classSet'][$key][$key1] = array(
                                'subject' => '',
                                'subjectId' => '',
                                'statu' => 1,
                                'techerName' => '',
                            );
                        }
                        if($value1 == 0){
                            $data['classSet'][$key][$key1] = array(
                                'subject' => '',
                                'subjectId' => '',
                                'statu' => 0,
                                'techerName' => '',
                            );
                        }
                        if($value1 == 2){
                            $data['classSet'][$key][$key1] = array(
                                'subject' => '',
                                'subjectId' => '',
                                'statu' => 2,
                                'techerName' => '',
                            );
                        }
                    }
                }
                $this->returnJson(array('statu' => 1,'allTableCount' => $allCount,'allClassCount' => $allClassCountCount, 'message' => 'success','data' => $data),true);
            }
            if($data = M('pk_default_limit')->where(array('pkListId' => $pkListId,'scId' =>$scId))->find()){
                $data = M('pk_default_limit')->where(array('pkListId' => $pkListId,'scId' =>$scId))->find();
                $data['classSet'] = unserialize($data['classSet']);
                foreach( $data['classSet'] as $key => $value){
                    foreach($value as $key1 => $value1){
                        if($value1 == 1){
                            $data['classSet'][$key][$key1] = array(
                                'subject' => '',
                                'subjectId' => '',
                                'statu' => 1,
                                'techerName' => '',
                            );
                        }
                        if($value1 == 0){
                            $data['classSet'][$key][$key1] = array(
                                'subject' => '',
                                'subjectId' => '',
                                'statu' => 0,
                                'techerName' => '',
                            );
                        }
                    }
                }
                $this->returnJson(array('statu' => 1,'allTableCount' => $allCount,'allClassCount' => $allClassCountCount,'message' => 'success','data' => $data),true);
            }
            //print_r(array('statu' => 1, 'message' => 'success','data' => $data));
            $this->returnJson(array('statu' => 0, 'message' => 'fail','data' => ''),true);
        }
        if($type == 'classSkTimeLimit'){//创建班级上课时间限制
            $data = I('post.data');
            $pkListId = I('post.pkListId');
            $className = I('post.className');
            $gradeName = (int)$this->gradeToEn(I('post.gradeName'));
            $classId = I('post.classId');
            $gradeId = I('post.gradeId');
            $createTime = strtotime(date('Y-m-d'));
            //科目预排
            if($km = M('pk_class_before_plan')->where(array('pkListId' => $pkListId,'gradeId' => $gradeId,'classId' => $classId,'scId' => $scId))->find()){
                $km = unserialize($km['classSet']);
                foreach($data as $key => $value){
                    foreach($value as $key1 => $value1){
                        if($value1['statu'] == 2){
                            $km[$key][$key1] = array(
                                'subject' => '',
                                'subjectId' => '',
                                'statu' => 2,
                                'techerName' =>'',
                                'techerId' => ''
                            );
                        }
                        if($value1['statu'] == 1){
                            $km[$key][$key1] = array(
                                'subject' => '',
                                'subjectId' => '',
                                'statu' => 1,
                                'techerName' =>'',
                                'techerId' => ''
                            );
                        }
                        if($value1['statu'] == 0){
                            $km[$key][$key1] = array(
                                'subject' => '',
                                'subjectId' => '',
                                'statu' => 0,
                                'techerName' =>'',
                                'techerId' => ''
                            );
                        }
                    }
                }
                M('pk_class_before_plan')->where(array('pkListId' => $pkListId,'gradeId' => $gradeId,'classId' => $classId,'scId' => $scId))->setField(array('classSet' => serialize($km)));
            }
            //教师排课时间限制
            /*$teacherLimit = M('pk_teacher_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->select();
            foreach($teacherLimit as $key2 => $value2){
                $classSet = unserialize($value2['classSet']);
                foreach($data as $key => $value){
                    foreach($value as $key1 => $value1){
                        if($value1 == 2){
                            $classSet[$key][$key1] = 2;
                        }
                        if($value1 == 1){
                            if($classSet[$key][$key1] == 2){
                                $classSet[$key][$key1] = 1;
                            }
                        }
                    }
                }
                M('pk_teacher_limit')->where(array('pkListId' => $pkListId,'scId' => $scId,'teacherId' => $value2['teacherId']))->setField(array('classSet' => serialize($classSet)));
            }*/
            //科目不排课
            $teacherLimit = M('pk_curriculum_limit')->where(array('pkListId' => $pkListId,'scId' => $scId,'gradeId' => $gradeId))->select();
            foreach($teacherLimit as $key2 => $value2){
                $classSet = unserialize($value2['classSet']);
                foreach($data as $key => $value){
                    foreach($value as $key1 => $value1){
                        if($value1['statu'] == 2){
                            $classSet[$key][$key1] = 2;
                        }
                        if($value1['statu'] == 1){
                            if($classSet[$key][$key1] == 2){
                                $classSet[$key][$key1] = 1;
                            }
                        }
                    }
                }
                M('pk_curriculum_limit')->where(array('pkListId' => $pkListId,'scId' => $scId,'classId' => $classId,'gradeId' => $gradeId,'curriculumId' => $value2['curriculumId']))->setField(array('classSet' => serialize($classSet)));
            }
            //班级排课时间限制
            foreach($data as $key => $value){
                foreach($value as $key1 => $value1){
                    if($value1['statu'] == 2){
                        $data[$key][$key1] = 2;
                    }
                    else if($value1['statu'] ==1){
                        $data[$key][$key1] = 1;
                    }
                    else if($value1['statu'] ==0){
                        $data[$key][$key1] = 0;
                    }
                    else{
                        $data[$key][$key1] = 1;
                    }
                }
            }
            $array = array(
                'scId' => $scId,
                'pkListId' => $pkListId,
                'classSet' => serialize($data),
                'className' => $className,
                'gradeName' => $gradeName,
                'classId' => $classId,
                'gradeId' => $gradeId,
                'createTime' => $createTime
            );
            M('pk_class_time_limit')->where(array('pkListId' => $pkListId,'gradeId' => I('post.gradeId'),'classId' => I('post.classId'),'scId' => $scId))->delete();
            if(M('pk_class_time_limit')->add($array)){
                $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
            }
            //班级排课时间限制
            $this->returnJson(array('statu' => 0, 'message' => 'add fail'),true);
        }
        if($type == 'subjectSkTimeList'){//得到课程上课时间列表
            $pkListId = I('get.pkListId');
            $array = array();
            //得到教学计划表
            $allClassCount = M('pk_jx_plan')->where(array('pkListId' => $pkListId,'scId' => $scId))->find();
            $allClass = unserialize($allClassCount['classSet']);
            $thisClass = array();
            $allClassCountCount = 0;
            $subjectIdId = I('get.curriculumId');
            foreach($allClass as $key => $value){
                if($value['gradeId'] ==  I('get.gradeId') && $value['classId'] == I('get.classId')){
                    $thisClass = $value['data'];
                    foreach($thisClass as $key1 => $value1){
                        if($value1['subjectId'] == $subjectIdId){
                            $allClassCountCount = $value1['count'];
                        }
                    }
                }
            }
            //得到班级总课时
            $all = M('pk_default_limit')->where(array('scId' => $scId,'pkListId' => $pkListId))->find();
            $all = unserialize($all['classSet']);
            $allCount = 0;
            foreach($all as $Key => $value){
                foreach($value as $key1 => $value1){
                    if($value1 == 1){
                        $allCount++;
                    }
                }
            }
            //
            $susuId = I('get.curriculumId');
            $teyu = array();
            if($data = M('pk_class_before_plan')->where(array('pkListId' => $pkListId,'scId' =>$scId,'gradeId' =>I('get.gradeId'),'classId' => I('get.classId')))->find()){
                $data['classSet'] = unserialize($data['classSet']);
                foreach($data['classSet'] as $key => $value){
                    foreach($value as $key1 => $value1){
                        if($value1['subjectId'] == $susuId){
                            $teyu[] = array(
                                'x' => $key,
                                'y' => $key1,
                                'subject' => $value1['subject'],
                                'teacherName' => $value1['teacherName'],
                            );
                        }
                    }
                }
            }
            $rrrrel = array();
            if($teacherId11 = M('jw_schedule')->field('techerId')->where(array('subjectId' =>I('get.curriculumId'),'scId' =>$scId,'gradeId' =>I('get.gradeId'),'classId' =>I('get.classId')))->find()){
                $teacherId11 = $teacherId11['techerId'];
                $ttt = M('pk_teacher_limit')->where(array('teacherId' =>$teacherId11,'pkListId' => $pkListId,'scId' =>$scId))->find();
                $teacherLimit = unserialize($ttt['classSet']);
                foreach ($teacherLimit as $key => $value){
                    foreach ($value as $key1 => $value1){
                        if($value1 == 4){
                            $rrrrel[] = array(
                                'x' => $key,
                                'y' => $key1
                            );
                        }
                    }
                }
            }
            if( $data = M('pk_curriculum_limit')->where(array('pkListId' => $pkListId,'curriculumId' =>I('get.curriculumId'),'scId' =>$scId,'gradeId' =>I('get.gradeId'),'classId' =>I('get.classId')))->find()){
                $data['classSet'] = unserialize($data['classSet']);
                foreach($data['classSet'] as $key => $value){
                    foreach($value as $key1 => $value1){
                        $data['classSet'][$key][$key1] = array(
                            'statu' => (int)$value1,
                            'subject' => '',
                            'teacherName' => ''
                        );
                    }
                }
                foreach ($rrrrel as $key => $value) {
                    $data['classSet'][$value['x']][$value['y']]['statu'] = 3;
                    $data['classSet'][$value['x']][$value['y']]['subject'] = '';
                    $data['classSet'][$value['x']][$value['y']]['teacherName'] = '';
                }
                foreach($teyu as $key => $value){
                    $data['classSet'][$value['x']][$value['y']]['statu'] = 5;
                    $data['classSet'][$value['x']][$value['y']]['subject'] = $value['subject'];
                    $data['classSet'][$value['x']][$value['y']]['teacherName'] = $value['teacherName'];
                }
                $this->returnJson(array('statu' => 1,'allCurriculumCount' => $allClassCountCount,'classCount' => $allCount, 'message' => 'success','data' => $data),true);
            }
            if($data = M('pk_class_time_limit')->where(array('pkListId' => $pkListId,'scId' =>$scId,'gradeId' => I('get.gradeId'),'classId' => I('get.classId')))->find()){
                $data['classSet'] = unserialize($data['classSet']);
                foreach($data['classSet'] as $key => $value){
                    foreach($value as $key1 => $value1){
                        $data['classSet'][$key][$key1] = array(
                            'statu' => (int)$value1,
                            'subject' => '',
                            'teacherName' => ''
                        );
                    }
                }
                foreach ($rrrrel as $key => $value) {
                    $data['classSet'][$value['x']][$value['y']]['statu'] = 3;
                    $data['classSet'][$value['x']][$value['y']]['subject'] = '';
                    $data['classSet'][$value['x']][$value['y']]['teacherName'] = '';
                }
                foreach($teyu as $key => $value){
                    $data['classSet'][$value['x']][$value['y']]['statu'] = 5;
                    $data['classSet'][$value['x']][$value['y']]['subject'] = $value['subject'];
                    $data['classSet'][$value['x']][$value['y']]['teacherName'] = $value['teacherName'];
                }
                $this->returnJson(array('statu' => 1, 'allCurriculumCount' => $allClassCountCount,'classCount' => $allCount,'message' => 'success','data' => $data),true);
            }
            if($data =  M('pk_default_limit')->where(array('pkListId' => $pkListId,'scId' =>$scId))->find()){
                $data['classSet'] = unserialize($data['classSet']);
                foreach($data['classSet'] as $key => $value){
                    foreach($value as $key1 => $value1){
                        $data['classSet'][$key][$key1] = array(
                            'statu' => (int)$value1,
                            'subject' => '',
                            'teacherName' => ''
                        );
                    }
                }
                foreach ($rrrrel as $key => $value) {
                    $data['classSet'][$value['x']][$value['y']]['statu'] = 3;
                    $data['classSet'][$value['x']][$value['y']]['subject'] = '';
                    $data['classSet'][$value['x']][$value['y']]['teacherName'] = '';
                }
                foreach($teyu as $key => $value){
                    $data['classSet'][$value['x']][$value['y']]['statu'] = 5;
                    $data['classSet'][$value['x']][$value['y']]['subject'] = $value['subject'];
                    $data['classSet'][$value['x']][$value['y']]['teacherName'] = $value['teacherName'];
                }
                $this->returnJson(array('statu' => 1,'allCurriculumCount' => $allClassCountCount,'classCount' => $allCount, 'message' => 'success','data' => $data),true);
            }
            //print_r(array('statu' => 1, 'message' => 'success','data' => $data));
            $this->returnJson(array('statu' => 0,'allCurriculumCount' => $allClassCountCount,'classCount' => $allCount, 'message' => 'fail','data' => ''),true);
        }
        if($type == 'subjectSkTimeLimit'){//创建课程上课时间限制
            $pkListId = I('post.pkListId');
            $data = I('post.data');
            foreach($data as $key => $value){
                foreach($value as $key1 => $value1){
                    if($value1['statu'] == 5){
                        $data[$key][$key1] = 1;
                    }else{
                        $data[$key][$key1] = $value1['statu'];
                    }
                }
            }
            $array = array(
                'scId' => $scId,
                'pkListId' => $pkListId,
                'classSet' => serialize($data),
                'className' => I('post.className'),
                'gradeName' => (int)$this->gradeToEn(I('post.gradeName')),
                'classId' => I('post.classId'),
                'gradeId' => I('post.gradeId'),
                'createTime' => strtotime(date('Y-m-d')),
                'curriculumName' => I('post.curriculumName'),
                'curriculumId' => I('post.curriculumId')
            );
            M('pk_curriculum_limit')->where(array('pkListId' => $pkListId,'curriculumId' =>I('post.curriculumId'),'gradeId' =>I('post.gradeId'),'classId' =>I('post.classId'),'scId' => $scId))->delete();
            if(M('pk_curriculum_limit')->add($array)){
                $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'add fail'),true);
        }
        if($type == 'getTeacherListLimit'){//得到教师上课时间限制
            $pkListId = I('get.pkListId');
            //得到总课时
            $all = M('pk_default_limit')->where(array('scId' => $scId,'pkListId' => $pkListId))->find();
            $all = unserialize($all['classSet']);
            $allCount = 0;
            foreach($all as $Key => $value){
                foreach($value as $key1 => $value1){
                    if($value1 == 1){
                        $allCount++;
                    }
                }
            }
            //得到教师总课时
            $allClassCount = M('pk_jx_plan')->where(array('pkListId' => $pkListId,'scId' => $scId))->find();
            $allClass = unserialize($allClassCount['classSet']);
            $thisClass = array();
            $allClassCountCount = 0;
            $allTeacherCount = 0;
            foreach($allClass as $key => $value){
                foreach($value['data'] as $key1 => $value1){
                    if($value1['techerId'] == I('get.teacherId')){
                        $allTeacherCount = $value1['count'] +$allTeacherCount;
                    }
                }
            }
            $array = array();
            //得到教师已经预排的科目
            $ttId = I('get.teacherId');
            $ttArray = array();
            $yPtable = M('pk_class_before_plan')->where(array('scId' => $scId,'pkListId' => $pkListId))->select();
            foreach($yPtable as $key => $value){
                $gDdata = unserialize($value['classSet']);
                foreach($gDdata as $key1 => $value1){
                    foreach($value1 as $key2 => $value2){
                        if(is_array($value2)){
                            if($value2['teacherId'] == $ttId){
                                $ttArray[] = array(
                                    'x' => $key1,
                                    'y' => $key2
                                );
                            }
                        }
                    }
                }
            }
            if( $data = M('pk_teacher_limit')->where(array('pkListId' => $pkListId,'teacherId' => I('get.teacherId'),'scId' =>$scId))->find()){
                $data['classSet'] = unserialize($data['classSet']);
                foreach($ttArray as $key => $value){
                    $data['classSet'][$value['x']][$value['y']] = 6;
                }
                foreach($data['classSet'] as $key => $value){
                    foreach($value as $key1 => $value1){
                        $data['classSet'][$key][$key1] = (int)$value1;
                    }
                }
                $this->returnJson(array('statu' => 1,'teacherCount' => $allTeacherCount,'allCount'=>$allCount, 'message' => 'success','data' => $data),true);
            }
            if($data =  M('pk_default_limit')->where(array('pkListId' => $pkListId,'scId' =>$scId))->find()){
                $data['classSet'] = unserialize($data['classSet']);
                foreach($ttArray as $key => $value){
                    $data['classSet'][$value['x']][$value['y']] = 6;
                }
                foreach($data['classSet'] as $key => $value){
                    foreach($value as $key1 => $value1){
                        $data['classSet'][$key][$key1] = (int)$value1;
                    }
                }
                $this->returnJson(array('statu' => 1,'teacherCount' => $allTeacherCount,'allCount'=>$allCount, 'message' => 'success','data' => $data),true);
            }
            //print_r(array('statu' => 1, 'message' => 'success','data' => $data));
            $this->returnJson(array('statu' => 1, 'message' => 'fail','data' => ''),true);
        }
        if($type == 'jsSkTimeLimit'){//教师上课时间限制
            $pkListId = I('post.pkListId');
            $data = I('post.data');
            $array = array(
                'scId' => $scId,
                'pkListId' => $pkListId,
                'classSet' => serialize($data),
                'createTime' => strtotime(date('Y-m-d')),
                'teacherName' => I('post.teacherName'),
                'teacherId' => I('post.teacherId')
            );
            M('pk_teacher_limit')->where(array('scId' => $scId, 'pkListId' => $pkListId,'teacherId' =>  I('post.teacherId')))->delete();
            if(M('pk_teacher_limit')->add($array)){
                $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'add fail'),true);
        }
        if($type == 'getArrangementList'){//得到预先安排课表设置
            $pkListId = I('get.pkListId');
            $array = array();
            //得到教学计划表
            $allClassCount = M('pk_jx_plan')->where(array('pkListId' => $pkListId,'scId' => $scId))->find();
            $allClass = unserialize($allClassCount['classSet']);
            $thisClass = array();
            $allClassCountCount = 0;
            $subjectIdId = I('get.subjectId');
            foreach($allClass as $key => $value){
                if($value['gradeId'] ==  I('get.gradeId') && $value['classId'] == I('get.classId')){
                    $thisClass = $value['data'];
                    foreach($thisClass as $key1 => $value1){
                        if($value1['subjectId'] == $subjectIdId){
                            $allClassCountCount = $value1['count'];
                        }
                    }
                }
            }
            $rrrrrrrrlllllll = array();
            if($datattttt = M('pk_curriculum_limit')->where(array('pkListId' => $pkListId,'curriculumId' =>I('get.subjectId'),'scId' =>$scId,'gradeId' =>I('get.gradeId'),'classId' =>I('get.classId')))->find()){
                $teacherLimit11 = unserialize($datattttt['classSet']);
                foreach ($teacherLimit11 as $key => $value){
                    foreach ($value as $key1 => $value1){
                        if($value1 == 3){
                            $rrrrrrrrlllllll[] = array(
                                'x' => $key,
                                'y' => $key1
                            );
                        }
                    }
                }
            }
            $rrrrel = array();
            if($teacherId11 = M('jw_schedule')->field('techerId')->where(array('subjectId' =>I('get.subjectId'),'scId' =>$scId,'gradeId' =>I('get.gradeId'),'classId' =>I('get.classId')))->find()){
                $teacherId11 = $teacherId11['techerId'];
                $ttt = M('pk_teacher_limit')->where(array('teacherId' =>$teacherId11,'pkListId' => $pkListId,'scId' =>$scId))->find();
                $teacherLimit = unserialize($ttt['classSet']);
                foreach ($teacherLimit as $key => $value){
                    foreach ($value as $key1 => $value1){
                        if($value1 == 4){
                            $rrrrel[] = array(
                                'x' => $key,
                                'y' => $key1
                            );
                        }
                    }
                }
            }
            if( $data = M('pk_class_before_plan')->where(array('pkListId' => $pkListId,'scId' =>$scId,'gradeId' => I('get.gradeId'),'classId' => I('get.classId')))->find()){
                $data['classSet'] = unserialize($data['classSet']);
                foreach ($rrrrel as $key => $value) {
                    if($data['classSet'][$value['x']][$value['y']]['statu'] ==1){
                        $data['classSet'][$value['x']][$value['y']]['statu'] = 4;
                        $data['classSet'][$value['x']][$value['y']]['subject'] = '';
                        $data['classSet'][$value['x']][$value['y']]['subjectId'] = '';
                        $data['classSet'][$value['x']][$value['y']]['techerId'] = '';
                        $data['classSet'][$value['x']][$value['y']]['techerName'] = '';
                    }
                }
                foreach ($rrrrrrrrlllllll as $key => $value) {
                    if($data['classSet'][$value['x']][$value['y']]['statu'] ==1){
                        $data['classSet'][$value['x']][$value['y']]['statu'] = 3;
                        $data['classSet'][$value['x']][$value['y']]['subject'] = '';
                        $data['classSet'][$value['x']][$value['y']]['subjectId'] = '';
                        $data['classSet'][$value['x']][$value['y']]['techerId'] = '';
                        $data['classSet'][$value['x']][$value['y']]['techerName'] = '';
                    }
                }
                foreach($data['classSet'] as $key => $value){
                    foreach($value as $key1 => $value1){
                        $data['classSet'][$key][$key1]['statu'] = (int)$data['classSet'][$key][$key1]['statu'];
                    }
                }
                $data['teacherName'] = $teacherId11['techerName'];
                $data['techerId'] = $teacherId11['techerId'];
                $this->returnJson(array('statu' => 1,'allSubjectCount' => $allClassCountCount, 'message' => 'success','data' => $data),true);
            }
            $return  = array();
            if( $data = M('pk_class_time_limit')->where(array('pkListId' => $pkListId,'scId' =>$scId,'gradeId' => I('get.gradeId'),'classId' => I('get.classId')))->find()){
                $return = unserialize($data['classSet']);
                $arrayData = array();
                foreach ($rrrrel as $key => $value) {
                    if($return[$value['x']][$value['y']] ==1){
                        $return[$value['x']][$value['y']] = 4;
                    }
                }
                foreach ($rrrrrrrrlllllll as $key => $value) {
                    if($return[$value['x']][$value['y']] ==1){
                        $return[$value['x']][$value['y']] = 3;
                    }
                }
                foreach($return as $key => $value){
                    foreach($value as $key1 => $value1){
                        $arrayData['classSet'][$key][$key1] = array(
                            'subject' => '',
                            'subjectId' => '',
                            'statu' => (int)$value1,
                            'techerName' =>'',
                            'techerId' => ''
                        );
                    }
                }
                $arrayData['techerId'] =  $teacherId11['techerId'];
                $arrayData['teacherName'] =  $teacherId11['techerName'];
                $this->returnJson(array('statu' => 1,'allSubjectCount' => $allClassCountCount, 'message' => 'success','data' => $arrayData),true);
            }
            /*if($data = M('pk_curriculum_limit')->where(array('pkListId' => $pkListId,'scId' =>$scId,'gradeId' => I('get.gradeId'),'classId' => I('get.classId')))->select()){
                foreach($data as $key => $value){
                    $classSet = unserialize($value['classSet']);
                    foreach($classSet as $key1 => $value1){
                        foreach($value1 as $key2 => $value2){
                            if($value2 == 2){
                                $return[$key1][$key2] = $value2;
                            }
                        }
                    }
                }
            }*/
            /*if(count($return)>1){
                $this->returnJson($return,true);
            }*/
            $data = M('pk_default_limit')->where(array('pkListId' => $pkListId,'scId' =>$scId))->find();
            $data['classSet'] = unserialize($data['classSet']);
            $return =  $data['classSet'];
            $arrayData = array();
            foreach ($rrrrel as $key => $value) {
                if($data['classSet'][$value['x']][$value['y']]['statu'] ==1){
                    $data['classSet'][$value['x']][$value['y']]['statu'] = 4;
                    $data['classSet'][$value['x']][$value['y']]['subject'] = '';
                    $data['classSet'][$value['x']][$value['y']]['subjectId'] = '';
                    $data['classSet'][$value['x']][$value['y']]['techerId'] = '';
                    $data['classSet'][$value['x']][$value['y']]['techerName'] = '';
                }
            }
            foreach($return as $key => $value){
                foreach($value as $key1 => $value1){
                    $arrayData['classSet'][$key][$key1] = array(
                        'subject' => '',
                        'subjectId' => '',
                        'statu' => (int)$value1,
                        'techerName' =>'',
                        'techerId' => ''
                    );
                }
            }
            $arrayData['techerId'] =  $teacherId11['techerId'];
            $arrayData['teacherName'] =  $teacherId11['techerName'];
            $this->returnJson(array('statu' => 1, 'allSubjectCount' => $allClassCountCount,'message' => 'success','data' => $arrayData),true);
            //print_r(array('statu' => 1, 'message' => 'success','data' => $data));
        }
        if($type == 'kcArrangement'){//课程预先安排创建
            $data = I('post.data');
            $pkListId = I('post.pkListId');
            $ttte = M('jw_schedule')->field('techerId,techerName,subjectId')->where(array('scId' =>$scId,'gradeId' =>I('post.gradeId'),'classId' =>I('post.classId')))->select();
            $ttttteeee = array();
            foreach($ttte as $key => $value){
                $ttttteeee[$value['subjectId']] = array('techerId' => $value['techerId'],'techerName' => $value['techerName']);
            }
            foreach($data as $key => $value){
                foreach($value as $key1 => $value1){
                    if($value1['statu'] == 5){
                        $data[$key][$key1]['techerId'] = $ttttteeee[$value1['subjectId']]['techerId'];
                        $data[$key][$key1]['teacherName'] = $ttttteeee[$value1['subjectId']]['techerName'];
                        $data[$key][$key1]['teacherId'] = $ttttteeee[$value1['subjectId']]['techerId'];
                        $data[$key][$key1]['techerName'] = $ttttteeee[$value1['subjectId']]['techerName'];
                    }
                }
            }
            $array = array(
                'scId' => $scId,
                'pkListId' => $pkListId,
                'classSet' => serialize($data),
                'className' => I('post.className'),
                'gradeName' => (int)$this->gradeToEn(I('post.gradeName')),
                'classId' =>  I('post.classId'),
                'gradeId' => I('post.gradeId'),
                'createTime' => strtotime(date('Y-m-d'))
            );
            M('pk_class_before_plan')->where(array('pkListId' => $pkListId,'gradeId' => I('post.gradeId'),'classId' =>I('post.classId'),'scId' => $scId))->delete();
            if(M('pk_class_before_plan')->add($array)){
                $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'add fail'),true);
        }
        if($type == 'getHbKcSet'){//得到合班课程设置
            $pkListId = I('get.pkListId');
            $data = M('pk_hb_set')->where(array('scId' => $scId,'pkListId' => $pkListId))->select();
            foreach($data as $key => $value){
                $data[$key]['classSet'] = unserialize($value['classSet']);
            }
            $this->returnJson(array('data' => $data,'message' => 'success' ,'statu' => 1),true);
        }
        if($type == 'createHbKcSet'){//合班课程设置
            $pkListId = I('post.pkListId');
            $array = array(
                'scId' => $scId,
                'pkListId' => $pkListId,
                'teacherId' => I('post.teacherId'),
                'teacherName' => I('post.teacherName'),
                'subjectName' => I('post.subjectName'),
                'subjectId' => I('post.subjectId'),
                'classSet' => serialize(I('post.data')),
                'createTime' => strtotime(date('Y-m-d'))
            );
            if(M('pk_hb_set')->add($array)){
                $this->returnJson(array('message' => 'add success' ,'statu' => 1),true);
            }
            $this->returnJson(array('message' => 'add fail' ,'statu' => 0),true);
        }
        if($type == 'delHb'){//删除合班限制
            $id = I('post.id');
            if(M('pk_hb_set')->where(array('id' => $id,'scId' => $scId))->delete()){
                $this->returnJson(array('message' => 'del success' ,'statu' => 1),true);
            }
            $this->returnJson(array('message' => 'del fail' ,'statu' => 0),true);
        }
    }
    public function createPlanGo($scId =2, $pk_week_limit = array(
        '0' => array(
            1,1,1,1,1,0,0
        ),
        '1' => array(
            1,1,1,1,1,0,0
        ),
        '2' => array(
            0,0,0,0,0,0,0
        )
    ),$morningCount = 4,$noongCount = 4,$nightCount =2,$pkListId=1,$grade=1){
        //$morningCount = 4;//$timeType['morningCount']; //默认早上课数
        //$noongCount = 4;//$timeType['noon']; //默认下午课数
        //$nightCount = 2;//$timeType['night']; //默认晚上课数
        $data = array();
        foreach($pk_week_limit as $key => $value){
            foreach($value as $key1 => $value1){
                if($key == 0){
                    //$data[$key1]['']
                    $trueOrfalse = 0;
                    if($value1 == 1){
                        $trueOrfalse = 1;
                    }
                    for($i = 0 ; $i<$morningCount; $i++){
                        $data[$key1][$i] = $trueOrfalse;
                    }
                }
                if($key == 1){
                    //$data[$key1]['']
                    $trueOrfalse = 0;
                    if($value1 == 1){
                        $trueOrfalse = 1;
                    }
                    for($i = 0,$j = $morningCount; $i<$noongCount; $i++,$j++){
                        $data[$key1][$j] = $trueOrfalse;
                    }
                }
                if($key == 2){
                    //$data[$key1]['']
                    $trueOrfalse = 0;
                    if($value1 == 1){
                        $trueOrfalse = 1;
                    }
                    for($i = 0,$j = $noongCount+$morningCount; $i<$nightCount; $i++,$j++){
                        $data[$key1][$j] = $trueOrfalse;
                    }
                }
            }
        }
        $array = array();
        for($j = 0; $j<count($data[0]);$j++){
            for($i = 0; $i<7; $i++){
                $array[$j][$i] = $data[$i][$j];
            }
        }
        M('pk_default_limit')->where(array('pkListId' => $pkListId))->delete();
        M('pk_default_limit')->add(array(
            'pkListId' => $pkListId,
            'classSet' => serialize($array),
            'createTime' => strtotime(date('Y-m-d')),
            'scId' => $scId
        ));
        //删除限制条件
        M('pk_class_before_plan')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
        M('pk_class_time_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
        M('pk_curriculum_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
        M('pk_teacher_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
        M('pk_teacher_result')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
        M('pk_class_result')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
        M('pk_hb_set')->where(array('pkListId' => $pkListId,'scId' => $scId))->delete();
        //删除
        /*foreach($rangeList as $key => $value){
            $grade = M('grade')->where(array('gradeid' => $value,'scId' => $scId))->find();
            $gradeName = $grade['name'];
            if($cheack = M('class')->where(array('grade' => $value,'scId' => $scId))->select()){
                foreach($cheack as $key1 => $value1){
                    M('pk_class_time_limit')->add(
                        $array = array(
                            'scId' => $scId,
                            'pkListId' => $pkListId,
                            'classSet' => serialize($data),
                            'classId' => $value1['classid'],
                            'className' => $value1['classname'],
                            'gradeId' => $value,
                            'gradeName' => $gradeName,
                            'createTime' => strtotime(date('Y-m-d')),
                        )
                    );
                }
            }
        }*/
    }
    public function createPlan(){
        //默认限号时间
        $pk_class_time_limit = array();
        $setClass = unserialize($pk_class_time_limit['ClassSet']);
        $pk_day_jie_limit = $setClass['day'];
        $pk_week_limit = $setClass['week'];
        $timeType = $setClass['section'];
        $morningCount = 4;//$timeType['morningCount']; //默认早上课数
        $noongCount = 4;//$timeType['noon']; //默认下午课数
        $nightCount = 2;//$timeType['night']; //默认晚上课数
        $data = array();
        $pk_week_limit = array( //默认周
            '0' => array(
                1,1,1,1,1,0,0
            ),
               '1' => array(
            1,1,1,1,1,0,0
        ),
               '2' => array(
            0,0,0,0,0,0,0
        )
        );
        foreach($pk_week_limit as $key => $value){
            foreach($value as $key1 => $value1){
                if($key == 0){
                    //$data[$key1]['']
                    $trueOrfalse = 0;
                    if($value1 == 1){
                        $trueOrfalse = 1;
                    }
                    for($i = 0 ; $i<$morningCount; $i++){
                        $data[$key1][$i] = $trueOrfalse;
                    }
                }
                if($key == 1){
                    //$data[$key1]['']
                    $trueOrfalse = 0;
                    if($value1 == 1){
                        $trueOrfalse = 1;
                    }
                    for($i = 0,$j = $morningCount; $i<$noongCount; $i++,$j++){
                        $data[$key1][$j] = $trueOrfalse;
                    }
                }
                if($key == 2){
                    //$data[$key1]['']
                    $trueOrfalse = 0;
                    if($value1 == 1){
                        $trueOrfalse = 1;
                    }
                    for($i = 0,$j = $noongCount+$morningCount; $i<$nightCount; $i++,$j++){
                        $data[$key1][$j] = $trueOrfalse;
                    }
                }
            }
        }
        //M('')
        $pkListId = I('get.pkListId');
        $array = array(
           // 'scId' => $scId,
            'pkListId' => $pkListId,
            'classSet' => serialize($data),
            'className' => I('get.className'),
            'gradeName' => I('get.gradeName'),
            'createTime' => strtotime(date('Y-m-d')),
            'curriculum' => I('get.curriculum'),
            'curriculumId' => I('get.curriculumId')
        );
        if(M('pk_class_time_limit')->add($array)){
            $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
        }
        $this->returnJson(array('statu' => 0, 'message' => 'add fail'),true);
        M('pk_class_time_limit')->add(

        );

        //计划教学初始设置
        /*M('jw_schedule')->where(array('className'));
        $pkListId = I('get.pkListId');
        $pk_list = M('pk_list')->where(array('id' => $pkListId))->find();
        $range = $pk_list['pkRange'];
        $rangeList = explode(',','1,2,3');
        $returnData = array();
        foreach($rangeList as $key => $value){
            $returnData[] = M('')->query("SELECT subject,techerId,techerName,grade,className FROM mks_jw_schedule where grade=$value ORDER BY className");
        }
        $returnDataTrue = array();
        foreach($returnData as $key => $value){
            foreach($value as $key1 => $value1){
                $value1['section'] = 0;
                $returnDataTrue[$value1['grade']][$value1['className']][] = $value1;
            }
        }
        $return = array();
        //print_r($returnDataTrue);
        foreach($returnDataTrue as $key => $value){
            foreach($value as $key1 => $value1){
                $return[] = $value1;
            }
        }
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        M('pk_jx_plan')->add(array(
          'scId' => $scId,
          'pkListId' => 1,
          'classSet' => serialize($return),
          'createTime' => strtotime(date('Y-m-d'))
      ));
        $Data = M('pk_jx_plan')->where(array('pkListId' =>1 ))->find();
        print_r(unserialize($Data['classSet']));*/
    }
    //动态规划法排课开始
    public function pkStart(){
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        $pkListId = I('get.pkListId');
        $pkList = M('pk_list')->where(array('id' => $pkListId,'scId' => $scId))->find();
        $rangeList = explode(',',$pkList['pkRange']);
        $jbLimit = M('pk_default_limit')->where(array('pkListId' => $pkListId,'scId' =>$scId))->find();
        $classLimit = M('pk_class_time_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->select();
        $pk_class_before_plan = M('pk_class_before_plan')->where(array('pkListId' => $pkListId,'scId' => $scId))->select();
        $curriculum_limit = M('pk_curriculum_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->select();
        $pk_teacher_limit = M('pk_teacher_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->select();
        $pk_jh_limit = M('pk_jx_plan')->where(array('pkListId' => $pkListId,'scId' => $scId))->find();
        $pk_time = M('pk_time')->where(array('pkListId' => $pkListId,'scId' => $scId))->find();
        //
        $data = array();
        $i = 0;
        foreach($rangeList as $key => $value){
            $grade = M('grade')->where(array('scId' => $scId,'gradeid'=> $value))->find();
            $gradeName = $grade['name'];
            $class = M('class')->where(array('scId' => $scId,'grade'=> $value))->order('classname')->select();
            $data[$i]['gradeName'] = $gradeName;
            $data[$i]['gradeId'] =  $value;
            $data[$i]['data'] = $class;
            $i++;
        }
        foreach($data as $key => $value){
            if(is_array($value['data'])){
                foreach($value['data'] as $key1 => $value1){

                }
            }
        }
    }
    //预先排课生成初步班级教室课程表
    private function pkAlgorithm($gradeId,$gradeName,$classId,$className,$jbLimit,$classLimit,$pk_class_before_plan,$curriculum_limit,$pk_teacher_limit){
        $pk_class_before_plan_data = array();
        $pk_class_before_plan_check = 0;
        foreach($pk_class_before_plan as $key => $value){
            if($value['gradeId'] == $gradeId && $value['classId'] == $classId){
                $pk_class_before_plan_data = $value;
                $pk_class_before_plan_check = 1;
            }
        }
        $teacherClassTable = array();
        $classTable = array();
        if($pk_class_before_plan_check){
            foreach($pk_class_before_plan_data as $key => $value){

            }
        }
    }
    //预先排课生成教师数组表，生成班级数组表
    public function createArrayClassAndTeacher(){
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        $pkListId = I('get.pkListId');
        $pkList = M('pk_list')->where(array('id' => $pkListId,'scId' => $scId))->find();
        $rangeList = explode(',',$pkList['pkRange']);
        $i = 0;
        foreach($rangeList as $key => $value){
            $grade = M('grade')->where(array('scId' => $scId,'gradeid'=> $value))->find();
            $gradeName = $grade['name'];
            $class = M('class')->where(array('scId' => $scId,'grade'=> $value))->order('classname')->select();
            $data[$i]['gradeName'] = $gradeName;
            $data[$i]['gradeId'] =  $value;
            $data[$i]['data'] = $class;
            $i++;
        }
        $allClass = array();
        foreach($data as $key => $value){
            if(is_array($value['data'])){
                foreach($value['data'] as $key1 => $value1){
                    $value1['gradeName'] = $value['gradeName'];
                    unset($value1['createTime']);
                    unset($value1['number']);
                    unset($value1['branch']);
                    unset($value1['levelid']);
                    unset($value1['major']);
                    unset($value1['scId']);
                    unset($value1['lastRecordTime']);
                    unset($value1['userid']);
                    $allClass[] = $value1;
                }
            }
        }
        $allTeacher = array();
        $dataTeacher = M('pk_jx_plan')->where(array('pkListId' => $pkListId,'scId' => $scId))->find();
        $dataTeacher = $dataTeacher['classSet'];
        $this::createClassKcTable($allClass,15);
    }
    //创建班级,教师,教学计划,临时课程表
    public function createClassKcAandTeacherTable($pkListId){
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        $data = M('pk_default_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->find();
        $limit = unserialize($data['classSet']);
        $this->classTable = $this->pkRang($pkListId,1);
        $classTable = array();
        foreach($this->classTable as $key => $value){
            $value['data'] = $limit;
            $classTable[$value['gradeId']][$value['classId']] = $value;
        }
        $this->classTable = $classTable;
        unset($classTable);
        $list = M('pk_list')->where(array('id' => $pkListId,'scId' => $scId))->find();
        $rangeList = $list['pkRange'];
        $this->teacherTable = M('')->query("SELECT techerId,techerName FROM mks_jw_schedule WHERE gradeId in($rangeList) AND scId=$scId  GROUP BY techerId");
        $teacherTable = array();
        foreach($this->teacherTable as $key => $value){
            $value['data'] = $limit;
            $teacherTable[$value['techerId']] = $value;
        }
        $this->teacherTable = $teacherTable;
        $this->jxPlanTable = $this->getJxPlanTable($pkListId);
    }
    public function testyx($pkListId){
        $data = M('pk_class_before_plan')->where(array('pkListId' => 1,'scId' => 2))->select();
        print_r(unserialize($data[0]['classSet']));
    }
    //教师限制排课，科目限制排课，合班处理
    public function subjectTeacherHbHanle($pkListId){
        $this->subjectLimit = $this->getLimitTeacerAndclass($pkListId); //科目排课限制
        $this->teacherLimitData = $this->teacherLimit($pkListId);//教师限制
        $this->hbClass = $this->hbToData($this->hb($pkListId));//合班
        foreach($this->teacherLimitData as $key => $value){
            if($this->teacherTable[$value['teacherId']]['data'][$value['y'][$value['x']]] == 1){
                $this->teacherTable[$value['teacherId']]['data'][$value['y'][$value['x']]] = 0;
            }
        }
        //print_r($this->classTable);
        foreach($this->subjectLimit as $key => $value){
            if($this->classTable[$value['gradeId']][$value['classId']]['data'][$value['y']][$value['x']] == 1){
                $jxjh = $this->jxPlanTable[$value['gradeId']][$value['classId']]['data'];
                sort($jxjh);
                $this->sjJxJh($jxjh,$value['curriculumId'],$value['y'],$value['x']);
            }
        }
        //print_r($this->classTable);
        //print_r($this->hbClass);
        //exit(0);
        foreach($this->hbClass as $key => $value){
            $hbData = $value['data'];
            $this->hejxHand($hbData);
        }
        //print_r($this->teacherLimitData);
        //print_r($this->classTable);
    }
    //合班教学处理
    private function hejxHand($hbData){
        $class = array();
        if(isset($this->classTable[$hbData[0]['gradeId']][$hbData[0]['classId']]['data'])){
            $class = $this->classTable[$hbData[0]['gradeId']][$hbData[0]['classId']]['data'];
            $y = count($class);
            $x = count($class[0]);
        }
        $jxJh = array();
        foreach($hbData as $key => $value){
            $jxJh[] = $this->jxPlanTable[$value['gradeId']][$value['classId']]['data'][$value['subjectId']];
        }
        $this->hbSjJxJh($jxJh,$y,$x);
    }
    private function hbSjJxJh($jxJh,$y,$x){//需要测试
        $xxx = $this->yxLevel[$jxJh[0]['gradeId']][$jxJh[0]['classId']][$jxJh[0]['subjectId']];
        $start = 0;
        $otherStart = 0;
        $otherEnd = 0;
        if($xxx>=4){
            $otherStart = $this->allDayM;
            $otherEnd = $y-1;
            $y = $y-$this->allDayM;
        }else{
            $otherStart = 0;
            $otherEnd = $y -$this->allDayM-1;
            $start = $y-$this->allDayM;
        }
        $relyY = $y-1;
        $relyX =  $this->allWeek-1;;
        $array = array();
        for($ii = 0;$ii<=$relyX;$ii++){
            for($jj = $start;$jj<=$relyY;$jj++){
                $array[$ii][$jj] = array($ii,$jj);
            }
        }
        //echo $xx;s
        $dl = array();
        for($jj = $start;$jj<=$relyY;$jj++){
            for($ii = 0;$ii<=$relyX;$ii++){
                $zj = array_rand($array[$ii],1);
                $dl[] = $array[$ii][$zj];
                unset($array[$ii][$zj]);
            }
        }
        for($jjj = $otherStart; $jjj<=$otherEnd;$jjj++){
            for($iii = 0; $iii<=$relyX;$iii++){
                $dl[] = array($iii,$jjj);
            }
        }
        $count = $jxJh[0]['count'];
        for($i = 0; $i<$count; $i++){
            $xxx = 0;
            foreach($dl as $key1 => $value1){
                $yy = $value1[1];
                $xx = $value1[0];
                $trueOrFalse = 1;
                //foreach($ii = $)
                unset($dl[$key1]);
                foreach ($jxJh as $key => $value) {
                    if ($this->classTable[$value['gradeId']][$value['classId']]['data'][$yy][$xx] != 1) {
                        $trueOrFalse = 0;
                    }
                }
                if ($trueOrFalse == 1) {
                    $xxx = 1;
                    $this->updataTeacherClassJxTable($jxJh, $xx, $yy);
                    break;
                }
            }
            if(!$xxx){
                $this->returnJson(array('statu' => 2,'message' => '合班设置不合理'),true);
            }
           /* while(1) {
                $yy = rand($start,$relyY);
                $xx = rand(0, $relyX);
                $trueOrFalse = 1;
                //foreach($ii = $)
                foreach ($jxJh as $key => $value) {
                    if ($this->classTable[$value['gradeId']][$value['classId']]['data'][$yy][$xx] != 1) {
                        $trueOrFalse = 0;
                    }
                }
                if ($trueOrFalse == 1) {
                    $this->updataTeacherClassJxTable($jxJh, $xx, $yy);
                    break;
                }
            }*/
        }
    }
    //得到随机教学计划
    private function sjJxJh($jxjh,$subjectId,$y,$x){
        foreach($jxjh as $key => $value){
            if($value['subjectId'] == $subjectId){
                unset($jxjh[$key]);
                sort($jxjh);
                break;
            }
        }
        foreach($this->hbClass as $key => $value){
            foreach($value['data'] as $key1 => $value1){
                foreach($jxjh as $key2 => $value2){
                    if($value1['subjectId'] == $value2['subjectId'] && $value1['gradeId'] == $value2['gradeId'] && $value1['classId'] == $value2['classId'] && $value1['classId'] == $value2['classId']){
                        unset($jxjh[$key2]);
                        sort($jxjh);
                        break;
                    }
                }
            }
        }
        while(1){
            $sj = rand(0,count($jxjh)-1);
            $trueOrFalse = 1;
            if($jxjh[$sj]['count']<=0){
                $trueOrFalse = 0;
            }
            if(isset($this->teacherTable[$jxjh[$sj]['techerId']]['data'][$y][$x])){
                if($this->teacherTable[$jxjh[$sj]['techerId']]['data'][$y][$x] != 1){
                    $trueOrFalse = 0;
                }
            }
            foreach($this->subjectLimit as $key => $value){
                if($value['curriculumId'] == $jxjh[$sj]['subjectId'] && $y == $value['y'] && $x == $value['x'] && $value['gradeId'] == $jxjh[$sj]['gradeId'] && $value['classId'] == $jxjh[$sj]['classId']){
                    $trueOrFalse = 0;
                    unset($jxjh[$sj]);
                    sort($jxjh);
                    break;
                }
            }
            /*foreach($this->hbClass as $key => $value){
                if($value['subjectId'] == $jxjh[$sj]['subjectId'] && $value['teacherId'] == $jxjh[$sj]['techerId']){
                    foreach($value['data'] as $key1 => $value1){
                        if($value1['gradeId'] == $jxjh[$sj]['gradeId'] && $value1['classId'] == $jxjh[$sj]['classId']){
                            $trueOrFalse = 0;
                        }
                    }
                }
            }*/
            if($trueOrFalse == 1){
                $updata[] = $jxjh[$sj];
                $this->updataTeacherClassJxTable($updata,$x,$y);
                break;
            }
        }
    }
    //开始排课
    public function startPktoGo(){
        $pkListId = I('get.pkListId');
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        if(M('pk_list')->where(array('scId' => $scId,'ifStartUp' => 1,'id' => $pkListId))->find()){
            $this->returnJson(array('message' => '该排课方案已经排课成功，并且已发布','statu' =>2),true);
        }
        $this->rePk($pkListId);
        M('pk_class_result')->where(array('scId' => $scId,'pkListId' => $pkListId))->delete();
        M('pk_teacher_result')->where(array('scId' => $scId,'pkListId' => $pkListId))->delete();
        $classTable = array();
        $teacherTable = array();
        foreach($this->classTable as $key => $value){
            foreach($value as $key1 => $value1){
                $classTable[] = array(
                    'className' => $value1['className'],
                    'gradeName' => $value1['gradeName'],
                    'createTime' => strtotime(date('Y-m-d H:i;s')),
                    'scId' => $scId,
                    'pkListId' => $pkListId,
                    'gradeId' => $value1['gradeId'],
                    'classId' => $value1['classId'],
                    'schedule' => serialize($value1['data'])
                );
            }
        }
        foreach($this->teacherTable as $key =>$value){
            $teacherTable[] =array(
                'teacherId' => $value['techerId'],
                'teacherName' => $value['techerName'],
                'classSet' => serialize($value['data']),
                'createTime' => strtotime(date('Y-m-d H:i:s')),
                'scId' => $scId,
                'pkListId' => $pkListId
            );
        }
        if(M('pk_class_result')->addAll($classTable) && M('pk_teacher_result')->addAll($teacherTable)){
            $this->returnJson(array('message' => '排课成功','statu' =>1),true);
        }
        $this->returnJson(array('message' => '排课失败','statu' =>0),true);
    }
    private function rePk($pkListId){
        $this->createClassKcAandTeacherTable($pkListId);//创建班级教师教学计划临时课表
        $this->classLimitHandle($pkListId);   //班级限制操；
        $this->yxPk($pkListId);   //预先排课处理
        $this->subjectTeacherHbHanle($pkListId);
        $this->checkXh++;
        if($this->checkXh > 50){
            $this->returnJson(array('message' => '排课失败，预设条件不对，请合理设置预设条件','statu' =>2),true);
        }
        $i = 0;
        foreach($this->classTable as $key => $value){
            foreach($value as $key1 => $value1){
                $jxJh = $this->jxPlanTable[$key][$key1]['data'];
                $class = $value1['data'];
                if(!$this->pkHandle($class,$jxJh,$key,$key1)){
                    $this->rePk($pkListId);
                }
            }
        }
    }
    public function mD(){//
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type == 'getTeacherList'){
            $pkListId = I('get.pkListId');
            $gradeGrade = M('pk_list')->where(array('scId' => $scId,'id' => $pkListId))->find();
            $gradeGrade = $gradeGrade['pkRange'];
            $sql = "SELECT * FROM mks_jw_schedule  WHERE scId=$scId AND gradeId IN ($gradeGrade)  GROUP BY techerId,subjectId";
            $data = M('')->query($sql);
            $dataList = array();
            foreach($data as $key => $value){
                $dataList[$value['subjectId']]['data'][] = $value;
                $dataList[$value['subjectId']]['techerName'] = $value['subject'];
            }
            rsort($dataList);
            $this->returnJson(array('data' => $dataList,'message' => 'success','statu' => 1),true);
        }
        if($type == 'getTeacherTable'){
            $techerId = I('get.techerId');
            $pkListId = I('get.pkListId');
            $table = M('pk_teacher_result')->where(array('teacherId' => $techerId,'scId' => $scId,'pkListId' => $pkListId))->find();
            $table = unserialize($table['classSet']);
            $gradeClass = array();
            foreach($table as $key => $value){
                foreach($value as $key1 => $value1){
                    if(is_array($value1)){
                        $gradeClass[$value1['gradeId']]['gradeName'] = $value1['gradeName'];
                        $gradeClass[$value1['gradeId']]['gradeId'] = $value1['gradeId'];
                        $table[$key][$key1]['gradeName'] = $this->gradeToZhong($value1['gradeName']);
                        $table[$key][$key1]['className'] = $value1['className'].'班';
                    }else{
                        $table[$key][$key1] = array(
                            'subjectName' => '',
                            'subjectId' => '',
                            'statu' => (int)$value1,
                            'gradeId' => '',
                            'gradeName' => '',
                            'classId' => '',
                            'className' => ''
                        );
                    }
                }
            }
            rsort($gradeClass);
            if(!$table){
                $table = array();
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success' ,'gradeAndClass' => $gradeClass,'data' => $table),true);
        }
        if($type == 'getClassTable'){
            $classid = I('get.classId');
            $gradeid = I('get.gradeId');
            $pkListId = I('get.pkListId');
            $data = M('pk_class_result')->where(array('scId' => $scId,'gradeId' => $gradeid,'classId' => $classid,'pkListId' => $pkListId))->find();
            $dataRe = unserialize($data['schedule']);
            foreach($dataRe as $key => $value){
                foreach($value as $key1 => $value1){
                    if(is_array($value1)){
                        $dataRe[$key][$key1]['statu'] = (int)$dataRe[$key][$key1]['statu'];
                    }else{
                        $dataRe[$key][$key1] = array(
                            'subjectName' => '',
                            'subjectId' => '',
                            'statu' => (int)$value1,
                            'teacherName' => '',
                            'teacherId' => '',
                        );
                        if((int)$value1 == 0){
                            $dataRe[$key][$key1]['subjectName'] =  '不上课';
                        }
                    }
                }
            }
            if(!$dataRe){
                $dataRe = array();
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success' , 'data' => $dataRe),true);
        }
        if($type == 'adjustment'){
            $pkListId = I('post.pkListId');
            $classId = I('post.classId');
            $gradeId = I('post.gradeId');
            $data = I('post.data');
            $teacherId11 = I('post.techerId');
            $subjectId11 = I('post.subjectId');
            $tz = array();
            foreach($data as $key  => $value){
                foreach($value as $key1 => $value1){
                    if($value1['statu'] == 6){
                        $value1['x'] = $key;
                        $value1['y'] = $key1;
                        $tz[] = $value1;
                    }
                }
            }
            $tea = array();
            $check = 0;
            foreach($tz as $key => $value1){
                if($value1['subjectId'] == $subjectId11 && $value1['teacherId'] == $teacherId11){
                    $check = 1;
                }
                $zj = M('pk_teacher_result')->where(array('teacherId' => $value1['teacherId'],'scId' => $scId,'pkListId' => $pkListId))->find();
                $tea[] = unserialize($zj['classSet']);
            }
            if(!$check){
                $this->returnJson(array('statu' => 3,'message' => '只能调选中老师的课'),true);
            }
            $x = $tz[1]['x'];
            $y = $tz[1]['y'];
            $trueOrfalse = 1;
            if($tea[0][$x][$y] == 0 || is_array($tea[0][$x][$y])){
                $trueOrfalse = 0;
            }
            $x1 = $tz[0]['x'];
            $y1 = $tz[0]['y'];
            if($tea[1][$x1][$y1] == 0 ||is_array($tea[1][$x1][$y1])){
                $trueOrfalse = 0;
            }
            if($trueOrfalse){
                $tttt[0] = $tz[0];
                $tttt[1] = $tz[1];
                $tttt[1]['statu'] = 5;
                $tttt[0]['statu'] = 5;
                unset($tttt[0]['x']);
                unset($tttt[0]['y']);
                unset($tttt[1]['x']);
                unset($tttt[1]['y']);
                $data = M('pk_class_result')->where(array('scId' => $scId,'gradeId' =>$gradeId,'classId' => $classId,'pkListId' => $pkListId))->find();
                $data = unserialize($data['schedule']);
                $data[$x][$y] = $tttt[0];
                $data[$x1][$y1] = $tttt[1];
                $data  = serialize($data);
                if(M('pk_class_result')->where(array('scId' => $scId,'gradeId' => $gradeId,'classId' => $classId,'pkListId' => $pkListId))->setField(array('schedule' =>$data))){
                    $tea[0][$tz[1]['x']][$tz[1]['y']] = $tea[0][$tz[0]['x']][$tz[0]['y']];
                    $tea[0][$tz[0]['x']][$tz[0]['y']] = 1;
                    $ii = 0;
                    if(M('pk_teacher_result')->where(array('teacherId' => $tz[0]['teacherId'],'scId' => $scId,'pkListId' => $pkListId))->setField(array('classSet' => serialize($tea[0])))){
                        $ii++;
                    }
                    $tea[1][$tz[0]['x']][$tz[0]['y']] = $tea[1][$tz[1]['x']][$tz[1]['y']];
                    $tea[1][$tz[1]['x']][$tz[1]['y']] = 1;
                    if(M('pk_teacher_result')->where(array('teacherId' => $tz[1]['teacherId'],'scId' => $scId,'pkListId' => $pkListId))->setField(array('classSet' => serialize($tea[1])))){
                        $ii++;
                    }
                    if($ii == 2){
                        $this->returnJson(array('statu' => 1 ,'message' => '调课成功'),true);
                    }
                    $this->returnJson(array('statu' => 0 ,'message' => '调课失败'),true);
                }
            }
            $this->returnJson(array('statu' => 2, 'message' => '调课有冲突！'),true);
        }
        if($type == 'getGrade'){
            $grade = M('grade')->where(array('scId' => $scId))->select();
            $this->returnJson(array('data' =>$grade,'statu' =>1 ,'message' =>'success'),true);
        }
        if($type == 'getClass'){
            $techerId = $_GET['techerId'];
            $pkListId = I('get.pkListId');
            $gradeId = I('get.gradeId');
            $table = M('pk_teacher_result')->where(array('teacherId' => $techerId,'scId' => $scId,'pkListId' => $pkListId))->find();
            $table = unserialize($table['classSet']);
            $gradeClass = array();
            foreach($table as $key => $value){
                foreach($value as $key1 => $value1){
                    if(is_array($value1)){
                        $gradeClass[$value1['gradeId']]['gradeName'] = $value1['gradeName'];
                        $gradeClass[$value1['gradeId']]['gradeId'] = $value1['gradeId'];
                        $gradeClass[$value1['gradeId']]['data'][$value1['classId']] = $value1;
                    }
                }
            }
            $return = array();
            foreach($gradeClass as $key => $value){
                if($value['gradeId'] == $gradeId){
                    $return = $value['data'];
                }
            }
            rsort($return);
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
        }
    }
    public function release(){//发布
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type == 'getYear'){
            $data = M('school_year')->where(array('scId' => $scId))->select();
            $this->returnJson(array('data' =>$data,'statu' =>1 ,'message' =>'success'),true);
        }
        if($type == 'release'){
            $yearid = $_GET['yearId'];
            $ifWeek = $_GET['ifWeek'];
            if($ifWeek == '单周'){
                $ifWeek = 1;
            }
            else if($ifWeek == '双周'){
                $ifWeek = 2;
            }else{
                $ifWeek = 0;
            }
            $pkListId = I('get.pkListId');
            $all =M('pk_list')->where(array('scId' => $scId,'yearid' => $yearid,'ifStartUp' =>1))->select();
            if(!$ifWeek){
                $pk = M('pk_list')->where(array('scId' => $scId,'id' =>$pkListId,'ifStartUp' =>1,'yearid' => $yearid))->find();
                $this->returnJson(array('statu' => 2, 'message' => 'have existence'),true);

            }else{
                $pk = M('pk_list')->where(array('scId' => $scId,'id' =>$pkListId,'ifStartUp' =>1,'yearid' => $yearid,'ifWeek' => array(array('eq',0),array('eq',$ifWeek),'or')))->find();
            }
            $pkGrade = explode(',',$pk['pkRange']);
            $allGrade = array();
            $checkWeek = array();
            foreach($all as $key => $value){
                $rang = explode(',',$value['pkRange']);
                $checkWeek[] = $value['ifWeek'];
                foreach($rang as $key1 => $value1){
                    $allGrade[$value1] = 1;
                }
            }
            foreach($pkGrade as $key => $value){
                if(isset($allGrade[$value])){
                    $this->returnJson(array('statu' => 2, 'message' => 'have existence'),true);
                }
            }
            /*if(in_array($ifWeek,$checkWeek)){
                $this->returnJson(array('statu' => 2, 'message' => 'have existence'),true);
            }*/
            if(M('pk_list')->where(array('scId' => $scId,'id' =>$pkListId))->setField(array('ifWeek' => $ifWeek,'yearid' => $yearid,'ifStartUp' => 1))){
                $this->returnJson(array('statu' =>1 ,'message' =>'success'),true);
            }
            $this->returnJson(array('statu' =>0 ,'message' =>'fail'),true);
        }
    }
    public function getYe(){
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $pkListId = I('get.pkListId');
        $data = M('pk_list')->where(array('scId' => $scId,'id' => $pkListId))->find();
        $pkRange = $data['pkRange'];
        $grade = "select znName from mks_grade where scId=$scId and gradeid in($pkRange)";
        $grade = M('')->query($grade);
        $gradeStr = '';
        foreach($grade as $key => $value){
            $gradeStr = $gradeStr.' '.$value['znName'];
        }
        $yers = M('school_year')->where(array('scId' => $scId,'yearid' => $data['yearid']))->find();
        if($data['ifWeek']==1) {
            $data['ifWeek'] = '单周';
        }elseif($data['ifWeek']==2){
            $data['ifWeek'] = '双周';
        }else{
            $data['ifWeek'] = '不分单双周';
        }
        $array = array();
        $str = '';
        $array['pkPlanName'] = $data['pkPlanName'];
        $array['pkRange']  =$gradeStr;
        $array['yeerName'] = $yers['yearname'];
        $array['week'] = $data['ifWeek'];
        $array['startTime'] = $data['startTime'];
        $array['endTime'] = $data['endTime'];
        $this->returnJson(array('statu' => 1,'data' => $array),true);
    }
    public function table(){//课表操作
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type == 'getGradeAndClass'){
            $pkListId = I('get.pkListId');
            $pk = M('pk_list')->where(array('scId' => $scId,'id' =>$pkListId))->find();
            $pkGrade = $pk['pkRange'];
            $sql = "SELECT gradeid,name,znName FROM mks_grade WHERE scId=$scId and gradeid in($pkGrade) order BY name";
            $gradeAll = M('')->query($sql);
            $sql = "SELECT * FROM mks_class WHERE scId=$scId and grade in($pkGrade) order by classname";
            $classAll = M('')->query($sql);
            $return = array();
            $gradeTr = array();
            foreach($gradeAll as $key => $value){
                $gradeTr[$value['gradeid']] = array('znName'=>$value['znName'],'grade' => $value['gradeid'],'classname' => $value['znName']);
            }
            foreach($classAll as $key => $value){
                $gradeTr[$value['grade']]['data'][] = $value;
               // $return[$value['grade']]['grade'] = $value['grade'];
                //$return[$value['grade']]['classname'] = $gradeTr[$value['grade']]['znName'];
            }
            $i = 0;
            foreach($gradeTr as $key => $value){
                $gradeTr[$i] = $value;
                unset($gradeTr[$key]);
                $i++;
            }
            $this->returnJson(array('statu' =>1 ,'message' =>'success','data' => $gradeTr),true);
        }
        if($type == 'getClassTable'){
            $gradeId = $_GET['grade'];
            $classId = I('get.classid');
            $pkListId = I('get.pkListId');
            $data = M('pk_class_result')->where(array('scId' => $scId,'pkListId' => $pkListId,'gradeId' => $gradeId,'classId' => $classId))->find();
            $table = unserialize($data['schedule']);
            $time = M('pk_time')->where(array('scId' => $scId,'pkListId' => $pkListId))->find();
            $time = unserialize($time['ClassSet']);
            $time = $time['day'];
            $timeRe = array();
            $jie = array();
            $i = 1;
            foreach($time as $key => $value){
                $startTimeRe = explode(' ',$value['startTime']);
                $endTimeRe = explode(' ',$value['endTime']);
                $startTimeRe = explode(':',$startTimeRe[4]);
                $startTimeRe = $startTimeRe[0].':'.$startTimeRe[1];
                $endTimeRe = explode(':',$endTimeRe[4]);
                $endTimeRe = $endTimeRe[0].':'.$endTimeRe[1];
                $timeRe[] = $startTimeRe.'-'.$endTimeRe;
                $jie[] = '第'.$i.'节';
                $i++;
            }
            $returnList = array();
            $returnList[0] = $timeRe;
            $returnList[1] = $jie;
            $i = 2;
            foreach($table as $key => $value){
                $returnList[$key][0] = array('subjectName' =>$jie[$key],'teacherName' => '');
                $returnList[$key][1] = array('subjectName' =>$timeRe[$key],'teacherName' => '');
                $i = 2;
                foreach($value as $key1 => $value1){
                    if(is_array($value1)){
                        $returnList[$key][$i] = $value1;
                    }else{
                        $returnList[$key][$i] = array(
                            'subjectName' => '',
                            'subjectId' => '',
                            'statu' => (int)$value1,
                            'teacherName' => '',
                            'teacherId' => '',
                        );
                        if((int)$value1 == 0){
                            $returnList[$key][$i]['subjectName'] =  '不上课';
                        }
                    }
                    $i++;
                }
            }
            $this->returnJson(array('statu' => 1 ,'data' => $returnList),true);
        }
        if($type == 'getClassTableExport'){
            $gradeId = $_GET['grade'];
            $classId = I('get.classid');;
            $pkListId = I('get.pkListId');
            $data = M('pk_class_result')->where(array('scId' => $scId,'pkListId' => $pkListId,'gradeId' => $gradeId,'classId' => $classId))->find();
            $table = unserialize($data['schedule']);
            $time = M('pk_time')->where(array('scId' => $scId,'pkListId' => $pkListId))->find();
            $time = unserialize($time['ClassSet']);
            $time = $time['day'];
            $timeRe = array();
            $jie = array();
            $i = 1;
            foreach($time as $key => $value){
                $startTimeRe = explode(' ',$value['startTime']);
                $endTimeRe = explode(' ',$value['endTime']);
                $startTimeRe = explode(':',$startTimeRe[4]);
                $startTimeRe = $startTimeRe[0].':'.$startTimeRe[1];
                $endTimeRe = explode(':',$endTimeRe[4]);
                $endTimeRe = $endTimeRe[0].':'.$endTimeRe[1];
                $timeRe[] = $startTimeRe.'-'.$endTimeRe;
                $jie[] = '第'.$i.'节';
                $i++;
            }
            $returnList = array();
            $i = 2;
            foreach($table as $key => $value){
                $returnList[$key]['subjectName0'] = $jie[$key];
                $returnList[$key]['subjectName1'] =$timeRe[$key];
                $i = 2;
                foreach($value as $key1 => $value1){
                    $pin = 'subjectName'.$i;
                    $te = $value1['teacherName'];
                    if($value1){
                        $returnList[$key][$pin] = $value1['subjectName']."($te)";
                    }else{
                        $returnList[$key][$pin] ='';
                    }
                    $i++;
                }
            }
            $tr = array(
                0 => array( 'en' => 'subjectName0',
                'zh' => '节/周'),
                1 => array( 'en' => 'subjectName1',
                    'zh' => '上课时间'),
                2 => array( 'en' => 'subjectName2',
                    'zh' => '星期一'),
                3 => array( 'en' => 'subjectName3',
                    'zh' => '星期二'),
                4 => array( 'en' => 'subjectName4',
                    'zh' => '星期三'),
                5 => array( 'en' => 'subjectName5',
                    'zh' => '星期四'),
                6 => array( 'en' => 'subjectName6',
                    'zh' => '星期五'),
                7 => array( 'en' => 'subjectName7',
                    'zh' => '星期六'),
                8 => array( 'en' => 'subjectName8',
                    'zh' => '星期天'),
            );
            $this->export($returnList,$tr);
        }
        if($type == 'getTeacherTable'){ //待修改
            $techerId = $_GET['techerId'];
            $pkListId = I('get.pkListId');
            $table = M('pk_teacher_result')->where(array('teacherId' => $techerId, 'scId' => $scId, 'pkListId' => $pkListId))->find();
            $table = unserialize($table['classSet']);
            $time = M('pk_time')->where(array('scId' => $scId,'pkListId' => $pkListId))->find();
            $time = unserialize($time['ClassSet']);
            $time = $time['day'];
            $timeRe = array();
            $jie = array();
            $i = 1;
            foreach($time as $key => $value){
                $startTimeRe = explode(' ',$value['startTime']);
                $endTimeRe = explode(' ',$value['endTime']);
                $startTimeRe = explode(':',$startTimeRe[4]);
                $startTimeRe = $startTimeRe[0].':'.$startTimeRe[1];
                $endTimeRe = explode(':',$endTimeRe[4]);
                $endTimeRe = $endTimeRe[0].':'.$endTimeRe[1];
                $timeRe[] = $startTimeRe.'-'.$endTimeRe;
                $jie[] = '第'.$i.'节';
                $i++;
            }
            $returnList = array();
            $returnList[0] = $timeRe;
            $returnList[1] = $jie;
            $i = 2;
            $hb =  $this->hbToData($this->hb($pkListId));//合班
            $dataLL = array();
            foreach($hb as $key => $value){
                if($value['teacherId'] == $techerId){
                    foreach($value['data'] as $key1 => $value1){
                        $dataLL[$key][] = $value1;
                    }
                }
            }
            foreach($table as $key => $value){
                $returnList[$key][0] = array('subjectName' =>$jie[$key],'teacherName' => '');
                $returnList[$key][1] = array('subjectName' =>$timeRe[$key],'teacherName' => '');
                $ii = 2;
                foreach($value as $key1 => $value1){
                    if(is_array($value1)){
                        if(count($dataLL)>0){
                            foreach($dataLL as $key2 => $value2){
                                $true = 0;
                                foreach($value2 as $key3 => $value3){
                                    if($value3['gradeId'] == $value1['gradeId'] && $value3['classId'] == $value1['classId'] && $value3['subjectId'] == $value1['subjectId']){
                                        $true = 1;
                                    }
                                }
                                if($true){
                                    $allClassGrade = '(合班)';
                                    $str = '';
                                    $count = count($dataLL);
                                    $i = 0;
                                    foreach($dataLL[$key2] as $key5 => $value5){
                                        if($count == $i){
                                            $str = $str.$this->gradeToZhong($value5['gradeName']).$value5['className'].'班';
                                        }else{
                                            $str = $str.$this->gradeToZhong($value5['gradeName']).$value5['className'].'班,';
                                        }
                                        $i++;
                                    }
                                    $allClassGrade = $allClassGrade.$str;
                                    $value1['subjectName'] = $allClassGrade;
                                    $returnList[$key][$ii] = $value1;
                                }else{
                                    $value1['subjectName'] = $this->gradeToZhong($value1['gradeName']).$value1['className'].'班';
                                    $returnList[$key][$ii] = $value1;
                                }
                            }
                        }
                        else{
                            $value1['subjectName'] = $this->gradeToZhong($value1['gradeName']).$value1['className'].'班';
                            $returnList[$key][$ii] = $value1;
                        }
                    }else{
                        $returnList[$key][$ii] = array(
                            'subjectName' => '',
                            'subjectId' => '',
                            'statu' => (int)$value1,
                            'gradeId' => '',
                            'gradeName' => '',
                            'classId' => '',
                            'className' => '',
                        );
                    }
                    $ii++;
                }
            }
            $this->returnJson(array('statu' => 1 ,'data' => $returnList,'message' => 'success'),true);
        }
        if($type == 'getTeacherTableExport'){
            $techerId = $_GET['techerId'];
            $pkListId = I('get.pkListId');
            $table = M('pk_teacher_result')->where(array('teacherId' => $techerId, 'scId' => $scId, 'pkListId' => $pkListId))->find();
            $table = unserialize($table['classSet']);
            $time = M('pk_time')->where(array('scId' => $scId,'pkListId' => $pkListId))->find();
            $time = unserialize($time['ClassSet']);
            $time = $time['day'];
            $timeRe = array();
            $jie = array();
            $i = 1;
            $hb = $this->hbClass = $this->hbToData($this->hb($pkListId));//合班
            $dataLL = array();
            foreach($hb as $key => $value){
                if($value['teacherId'] == $techerId){
                    foreach($value['data'] as $key1 => $value1){
                        $dataLL[$key][] = $value1;
                    }
                }
            }
            foreach($time as $key => $value){
                $startTimeRe = explode(' ',$value['startTime']);
                $endTimeRe = explode(' ',$value['endTime']);
                $startTimeRe = explode(':',$startTimeRe[4]);
                $startTimeRe = $startTimeRe[0].':'.$startTimeRe[1];
                $endTimeRe = explode(':',$endTimeRe[4]);
                $endTimeRe = $endTimeRe[0].':'.$endTimeRe[1];
                $timeRe[] = $startTimeRe.'-'.$endTimeRe;
                $jie[] = '第'.$i.'节';
                $i++;
            }
            $returnList = array();
            $i = 2;
            //print_r($table);
            foreach($table as $key => $value){
                $returnList[$key]['subjectName0'] = $jie[$key];
                $returnList[$key]['subjectName1'] =$timeRe[$key];
                $ii = 2;
                foreach($value as $key1 => $value1){
                    $pin = 'subjectName' . $ii;
                    if(is_array($value1)){
                        if(count($dataLL)>0){
                            foreach ($dataLL as $key2 => $value2) {
                                $true = 0;
                                foreach ($value2 as $key3 => $value3) {
                                    if ($value3['gradeId'] == $value1['gradeId'] && $value3['classId'] == $value1['classId'] && $value3['subjectId'] == $value1['subjectId']) {
                                        $true = 1;
                                    }
                                }
                                if ($true) {
                                    $allClassGrade = '(合班)';
                                    $str = '';
                                    $count = count($dataLL);
                                    $i = 0;
                                    foreach ($dataLL[$key2] as $key5 => $value5) {
                                        if ($count == $i) {
                                            $str = $str . $this->gradeToZhong($value5['gradeName']) . $value5['className'] . '班' . '班'.'('.$value1['subjectName'].')';
                                        } else {
                                            $str = $str . $this->gradeToZhong($value5['gradeName']) . $value5['className'] . '班,' . '班'.'('.$value1['subjectName'].')';
                                        }
                                        $i++;
                                    }
                                    $allClassGrade = $allClassGrade . $str;
                                    $returnList[$key][$pin] = $allClassGrade;
                                } else {
                                    $returnList[$key][$pin] = $this->gradeToZhong($value1['gradeName']) . $value1['className'] . '班' . '班'.'('.$value1['subjectName'].')';
                                }
                            }
                        }else{
                            $returnList[$key][$pin] = $this->gradeToZhong($value1['gradeName']) . $value1['className'] . '班'.'('.$value1['subjectName'].')';
                        }
                    }else{
                        $returnList[$key][$pin] = '';
                    }
                    $ii++;
                }
            }
            $tr = array(
                0 => array( 'en' => 'subjectName0',
                    'zh' => '节/周'),
                1 => array( 'en' => 'subjectName1',
                    'zh' => '上课时间'),
                2 => array( 'en' => 'subjectName2',
                    'zh' => '星期一'),
                3 => array( 'en' => 'subjectName3',
                    'zh' => '星期二'),
                4 => array( 'en' => 'subjectName4',
                    'zh' => '星期三'),
                5 => array( 'en' => 'subjectName5',
                    'zh' => '星期四'),
                6 => array( 'en' => 'subjectName6',
                    'zh' => '星期五'),
                7 => array( 'en' => 'subjectName7',
                    'zh' => '星期六'),
                8 => array( 'en' => 'subjectName8',
                    'zh' => '星期天'),
            );
            $this->export($returnList,$tr);
        }
        if($type == 'getAllClassTable'){
            $pkListId = I('get.pkListId');
            $data = M('pk_class_result')->where(array('scId' => $scId,'pkListId' => $pkListId))->order('gradeName,className')->select();
            $returnList = array();
            $i = 0;
            foreach($data as $key => $value){
                $returnList[$i]['name'] = $this->gradeToZhong($value['gradeName']).$value['className'].'班';
                $zjData = unserialize($value['schedule']);
                $z = 1;
                foreach($zjData as $key1 => $value1){
                    $j = 0;
                    $returnList[$i]['data'][$j][$key1]['subjectName'] = '第'.($z).'节';
                    $returnList[$i]['data'][$j][$key1]['teacherName'] = '';
                    $j++;
                    foreach($value1 as $Key2 => $value2){
                        if(is_array($value2)){
                            $returnList[$i]['data'][$j][$key1] = $value2;
                        }else{
                            $returnList[$i]['data'][$j][$key1] = array(
                                'subjectName' => '不上课',
                                'teacherName' => ''
                            );
                        }
                        $j++;
                    }
                    $z++;
                }
                $i++;
            }
            $this->returnJson(array('statu' => 1 ,'data' => $returnList,'message' => 'success'),true);
        }
        if($type == 'getAllClassTableExport'){
            $pkListId = I('get.pkListId');
            $data = M('pk_class_result')->where(array('scId' => $scId,'pkListId' => $pkListId))->order('gradeName,className')->select();
            $returnList = array();
            $i = 0;
            foreach($data as $key => $value){
                $returnList[$i]['name'] = $this->gradeToZhong($value['gradeName']).$value['className'].'班';
                $zjData = unserialize($value['schedule']);
                $z = 1;
                foreach($zjData as $key1 => $value1){
                    $j = 0;
                    $returnList[$i]['data'][$key1][$j]['subjectName'] = '第'.($z).'节';
                    $returnList[$i]['data'][$key1][$j]['teacherName'] = '';
                    $j++;
                    foreach($value1 as $Key2 => $value2){
                        if(is_array($value2)){
                            $returnList[$i]['data'][$key1][$j] = $value2;
                        }else{
                            $returnList[$i]['data'][$key1][$j] = array(
                                'subjectName' => '',
                                'teacherName' => ''
                            );
                        }
                        $j++;
                    }
                    $z++;
                }
                $i++;
            }
            //print_r($returnList);
            vendor('PHPExcel.PHPExcel');
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("jianglong")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
            $objPHPExcel->setActiveSheetIndex(0);
            $getobj1 = $objPHPExcel->getActiveSheet();
            $getobj1->getDefaultRowDimension()->setRowHeight(20);
            $j = 1;
            $i=1;
            $getobj1->setCellValue('A1','班级');
            $getobj1->setCellValue('B1','节/周');
            $getobj1->setCellValue('C1','星期一');
            $getobj1->setCellValue('D1','星期二');
            $getobj1->setCellValue('E1','星期三');
            $getobj1->setCellValue('F1','星期四');
            $getobj1->setCellValue('G1','星期五');
            $getobj1->setCellValue('H1','星期六');
            $getobj1->setCellValue('I1','星期日');
            $jj = 2;
            //print_r($returnList);
            $getobj1->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $getobj1->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            foreach($returnList as $key => $value){
                foreach($value['data'] as $key1 => $value1){
                    $ii = 66;
                    foreach($value1 as $key2 => $value2){
                        if($value2['teacherName']){
                            $getobj1->setCellValue(chr($ii).$jj,$value2['subjectName'].'('.$value2['teacherName'].')');
                        }else{
                            $getobj1->setCellValue(chr($ii).$jj,$value2['subjectName']);
                        }
                        $ii++;
                    }
                    $getobj1->setCellValue(chr(65).$jj,$value['name']);
                    $jj++;
                }
                $cc = count($value['data']);
                $cc = $jj - $cc;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$cc.':'.'A'.$jj);
                $jj++;
            }
            //$getobj1->setCellValue(chr(64 + $i).$j,$value1);
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $excelname = date('Y-m-d');
            $excelname = "export.xls";
            ob_end_clean();
            // Redirect output to a client’s web browser (Excel5)
            //header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$excelname.'"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
        }
    }
    private function pkHandle($class,$jxJh,$gradeId,$classId,$check = 0){
        /*foreach($jxJh as $key => $value){
            if(!$value['count']){
                unset($jxJh[$key]);
            }
        }
        foreach($jxJh as $key => $value){
            $xxx = $this->yxLevel[$value['gradeId']][$value['classId']][$value['subjectId']];
            $start = 0;
            $otherStart = 0;
            $otherEnd = 0;
            $y = $this->y;
            if($xxx>=4){
                $otherStart = $this->allDayM;
                $otherEnd = $this->y-1;
                $y = $y -($this->allDayM);
            }else{
                $otherStart = 0;
                $otherEnd = $this->y - $this->allDayM-1;
                $start =$this->y - $this->allDayM;
            }
            $relyY = $y-1;
            $relyX =  $this->allWeek-1;;
            $array = array();
            for($ii = 0;$ii<=$relyX;$ii++){
                for($jj = $start;$jj<=$relyY;$jj++){
                    $array[$ii][$jj] = array($ii,$jj);
                }
            }
            //echo $xx;s
            $dl = array();
            for($jj = $start;$jj<=$relyY;$jj++){
                for($ii = 0;$ii<=$relyX;$ii++){
                    $zj = array_rand($array[$ii],1);
                    $dl[] = $array[$ii][$zj];
                    unset($array[$ii][$zj]);
                }
            }
            for($jjj = $otherStart; $jjj<=$otherEnd;$jjj++){
                for($iii = 0; $iii<=$relyX;$iii++){
                    $dl[] = array($iii,$jjj);
                }
            }
            $count = $jxJh[$key]['count'];
            for($i = 0; $i<$count; $i++) {
                $xxx = 0;
                foreach ($dl as $key1 => $value1) {
                    $yy = $value1[1];
                    $xx = $value1[0];
                    $trueOrFalse = 1;
                    //foreach($ii = $)
                    if ($this->classTable[$gradeId][$classId]['data'][$yy][$xx] != 1) {
                        $trueOrFalse = 0;
                    }
                    if($this->teacherTable[$value['techerId']]['data'][$yy][$xx] != 1){
                        $trueOrFalse = 0;
                    }
                    if ($trueOrFalse) {
                        $xxx = 1;
                        $updata[] = $value;
                        $this->updataTeacherClassJxTable($updata, $xx, $yy);
                        break;
                    }
                }
                if (!$xxx) {
                    //$this->returnJson(array('statu' => 2, 'message' => '合班设置不合理'), true);
                    if($check>10){
                        return false;
                    }
                    $this->pkHandle($class,$jxJh,$gradeId,$classId,$check++);
                }
            }
        }
        return true;
        */
        $listDl = array();
        foreach($jxJh as $key => $value){
            $listDl[$key] = 1;
        }
        $listDM = array();
        $listDA = array();
        foreach($this->yxLevel[$gradeId][$classId] as $key => $value){
            if($value>=4){
                $listDM[$key] = 1;
            }else{
                $listDA[$key] = 1;
            }
        }
        $dayM = $this->allDayM-1;
        foreach($class as $key => $value){
            foreach($value as $key1 => $value1){
                $listBy = array();
                if($key<=$dayM){
                    $listDlUse = $listDM;
                    $listBy = $listDA;
                }else{
                    $listDlUse = $listDA;
                    $listBy = $listDM;
                }
                $chech = 1;
                if($value1 == 1){
                    while(1){
                        foreach($jxJh as $key2 => $value2){
                            if($value2['count'] <= 0){
                                unset($jxJh[$key2]);
                                unset($listDlUse[$key2]);
                            }
                            if(!$value2['gradeId']){
                                unset($jxJh[$key2]);
                                unset($listDlUse[$key2]);
                            }
                        }
                        if(!count($listDlUse)){
                            if($chech){
                                $listDlUse = $listBy;
                                $chech = 0;
                            }else{
                                break;
                            }
                        }
                        $jxJhCell = array_rand($listDlUse,1);
                        $data = $jxJh[$jxJhCell];
                        if($this->teacherTable[$data['techerId']]['data'][$key][$key1] == 1){
                            $jxJh[$jxJhCell]['count']--;
                            $updata = array();
                            $updata[] = $data;
                            $this->updataTeacherClassJxTable($updata,$key1,$key);
                            break;
                        }
                        unset($listDlUse[$jxJhCell]);
                    }
                }
            }
        }
        if(count($jxJh)){
            return false;
        }
        return true;
    }
    private function pdPkTrueOrFalse($class,$jxJh){
        $jxJhSort = array();
        $i =0;
        foreach($jxJh['data'] as $key => $value){
            $jxJhSort[] = $value;
        }
        foreach($class['data'] as $key => $value){
            foreach($value as $key1 => $value1){
                if($value1 == 0){

                }
                if($value1 == 1){
                    /*foreach($jxJhSort as $key => $value){
                        $newJxJh = $jxJhSort;

                    }*/
                    $this->handle($jxJhSort,$key,$key1,$key);
                }
            }
        }
    }
    private function handle($jxJhSort,$key,$key1,$keyEnd){
        $maxStart = count($jxJhSort)-1;
        $jxJhCell = $this->cellJxPlan($jxJhSort,$keyEnd,0,$maxStart);
        if(!$jxJhCell){
            return 1;
        }
        $x = $key1;
        $y = $key;
        //判断是否合班教学
        //print_r($jxJhCell);
        //print_r($this->hbClass);
        $hbReturn = $this->pkHeban($jxJhCell);
        //print_r($this->hbClass);
        //print_r($this->jxPlanTable);
        if($hbReturn){
            foreach($hbReturn as $key3 => $value3){
                $jxJhCellArray[] = $this->jxPlanTable[$value3['gradeId']][$value3['classId']]['data'][$value3['subjectId']];
                foreach($jxJhCellArray as $key4 => $value4){
                    if($value4['count']<=0){
                         $this->handle($jxJhSort,$key,$key1,$keyEnd++);
                    }
                    if(!$this->classKpTrueOrFalse($value4,$x,$y)){
                         $this->handle($jxJhSort,$key,$key1,$keyEnd++);
                    }
                    if(!$this->teacherTrueOrfalse($value4,$x,$y)){
                         $this->handle($jxJhSort,$key,$key1,$keyEnd++);
                    }
                }
            }
        }else{
            if($this->classKpTrueOrFalse($jxJhCell,$x,$y)){//课程上课时间限制
                if($this->teacherTrueOrfalse($jxJhCell,$x,$y)){
                    $cell[] = $jxJhCell;
                    $this->updataTeacherClassJxTable($cell,$x,$y);
                }
            }
        }
    }
    private function updataTeacherClassJxTable($jxJhCell,$x,$y){
        foreach($jxJhCell as $key => $value){
            if(isset($this->classTable[$value['gradeId']][$value['classId']]['data'][$y][$x])){
                $this->classTable[$value['gradeId']][$value['classId']]['data'][$y][$x] =  array(
                    'subjectName' => $value['subject'],
                    'subjectId' => $value['subjectId'],
                    'statu' => 5,
                    'teacherName' => $value['techerName'],
                    'teacherId' => $value['techerId']
                );
            }
            if(isset($this->teacherTable[$value['techerId']]['data'][$y][$x])){
                $this->teacherTable[$value['techerId']]['data'][$y][$x] = array(
                    'subjectName' => $value['subject'],
                    'subjectId' => $value['subjectId'],
                    'statu' => 5,
                    'gradeId' => $value['gradeId'],
                    'gradeName' => $value['gradeName'],
                    'classId' => $value['classId'],
                    'className' => $value['className'],
                );
            }
            if(isset($this->jxPlanTable[$value['gradeId']][$value['classId']]['data'][$value['subjectId']]['count'])){
                $this->jxPlanTable[$value['gradeId']][$value['classId']]['data'][$value['subjectId']]['count']--;
            }
        }
    }
    //教师限制
    private function teacherTrueOrfalse($jxJhCell,$x,$y){
        foreach($this->teacherLimitData as $key => $value){
            if($jxJhCell['techerId'] == $value['teacherId'] && $value['x'] == $x && $value['y'] == $y){
                return 0;
            }
        }
        return 1;
    }
    //课程限制
    private function classKpTrueOrFalse($hbReturn,$x,$y){
        if($hbReturn['statu'] == 0){
            foreach($this->subjectLimit as $key => $value){
                if($value['classId'] == $hbReturn['data']['classId'] && $value['gradeId'] == $hbReturn['data']['gradeId'] && $value['curriculumId'] == $hbReturn['data']['subjectId'] && $x == $value['x'] && $y == $value['y']){
                    return 0;
                }
            }
            return 1;
        }
    }
    private function pkHebanHandel(){

    }
    //得到教学计划
    private function cellJxPlan($jxJh,$key,$start,$maxStart){
        if(isset($jxJh[$key])){
            if($jxJh[$key]['count']<=0){
                $key++;
                if($key>$maxStart){
                    $key = 0;
                }
                if($start++>$maxStart){
                    return 0;
                }
                $this->cellJxPlan($jxJh,$key,$start++,$maxStart);
            }else{
                return $jxJh[$key];
            }
        }else{
            $key = $maxStart;
            $this->cellJxPlan($jxJh,$key,$start++,$maxStart);
        }
    }
    //排课合班判断
    private function pkHeban($jxJhCell){
        foreach($this->hbClass as $key2 => $value){
            if($value['subjectId'] == $jxJhCell['subjectId'] && $value['teacherId'] == $jxJhCell['techerId']){
                foreach($value['data'] as $key3 => $value3){
                    if($value3['gradeId'] == $jxJhCell['gradeId'] && $value3['classId'] == $jxJhCell['classId']){
                        unset($this->hbClass[$key2]);
                        return array(
                            'statu' => 1,
                            'data' => $value['data']
                        );
                    }
                }
                return 0;
                break;
            }
        }
        return 0;
    }
    //得到教学计划表
    public function getJxPlanTable($pkListId){
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        $data = M('pk_jx_plan')->where(array('pkListId' => $pkListId,'scId' => $scId))->find();
        $data = unserialize($data['classSet']);
        /*foreach($data[0]['data'] as $key => $value){
            $data[0]['data'][$key]['className'] = 1;
            $data[0]['data'][$key]['gradeId'] = 1;
            $data[0]['data'][$key]['classId'] = 7;
            $data[0]['data'][$key]['scId'] = 2;
            $data[0]['data'][$key]['gradeName'] = 1;
            if(!$value['techerId']){
                $data[0]['data'][$key]['techerId'] = rand(100,120);
                $data[0]['data'][$key]['techerName'] = rand(10000,100000);
            }
        }
        M('pk_jx_plan')->where(array('pkListId' => $pkListId))->setField(array('classSet' => serialize($data)));*/
        $return = array();
        $jxPx = array();
        $classTime = M('pk_time')->where(array('scId' => $scId,'pkListId' => $pkListId))->find();
        $classTime = unserialize($classTime['ClassSet']);
        $no = 0;
        foreach($classTime['week'][0] as $key => $value){
            if($value){
                $no++;
            }
        }
        $this->allWeek = $no;
        $classTime = $classTime['section'];
        $this->allDayM = $classTime['morningCount'];
        $this->x = $no;
        $this->y = $classTime['morningCount']+$classTime['noon']+$classTime['night'];
        foreach($data as $key => $value){
            foreach($value['data'] as $key2 => $value2){
                if($value2['count']!=0 && $value2['techerId']){
                    $return[$value['gradeId']][$value['classId']]['data'][$value2['subjectId']] = $value2;
                    $jxPx[$value['gradeId']][$value['classId']][$value2['subjectId']] = $value2['count'];
                }
            }
            $return[$value['gradeId']][$value['classId']]['className'] = $value['className'];
            $return[$value['gradeId']][$value['classId']]['gradeName'] = $value['gradeName'];
        }
        $this->yxLevel = $jxPx;
        return $return;
    }
    //班级不排课处理
    private function classLimitHandle($pkListId){
        $data = $this->classLimit($pkListId);
        foreach($data as $key => $value) {
            if(isset( $this->classTable[$value['gradeId']][$value['classId']]['data'][$value['y']][$value['x']])){
                $this->classTable[$value['gradeId']][$value['classId']]['data'][$value['y']][$value['x']] = 0;
            }
        }
    }
    //自动排课预先排课处理
    private function yxPk($pkListId){
        $this->hbClass = $this->hbToData($this->hb($pkListId));//合班
        $data = $this->prvPk($pkListId);
        $count = count($data);
        $dataHb =array();
        foreach($data as $key => $value){
            foreach($this->hbClass as $key1 => $value1){
                if($value['subjectId'] == $value1['subjectId'] && $value['teacherId'] == $value1['teacherId']){
                    $true0rFalse = 0;
                    foreach($value1['data'] as $key2 => $value2){
                        if($value2['classId'] == $value['classId'] && $value2['gradeId'] == $value['gradeId']){
                            $true0rFalse = 1;
                        }
                    }
                    if($true0rFalse){
                        unset($data[$key]);
                        foreach($value1['data'] as $key3 => $value3){
                            $value['className'] = $value3['className'];
                            $value['gradeName'] = $value3['gradeName'];
                            $value['classId'] = $value3['classId'];
                            $value['gradeId'] = $value3['gradeId'];
                            $dataHb[] = $value;
                        }
                    }
                }
            }
        }
        $data = array_merge($data,$dataHb);
        foreach($data as $key => $value){
            $this->classTable[$value['gradeId']][$value['classId']]['data'][$value['y']][$value['x']] =  array(
                'subjectName' => $value['subjectName'],
                'subjectId' => $value['subjectId'],
                'statu' => $value['statu'],
                'teacherName' => $value['teacherName'],
                'teacherId' => $value['teacherId']
            );
            if($value['teacherId']){
                $this->teacherTable[$value['teacherId']]['data'][$value['y']][$value['x']] = array(
                    'subjectName' => $value['subjectName'],
                    'subjectId' => $value['subjectId'],
                    'statu' => $value['statu'],
                    'gradeId' => $value['gradeId'],
                    'gradeName' => $value['gradeName'],
                    'classId' => $value['classId'],
                    'className' => $value['className'],
                );
            }
            if($value['subjectId']){
                $this->jxPlanTable[$value['gradeId']][$value['classId']]['data'][$value['subjectId']]['count']--;
            }
        }
    }
    //得到自动排课
    public function zdPk(){
        $type = I('get.type');
        if($type == 'getZdPkList'){
            $pkListId = I('get.pkListId');
            $classLimit = $this->classLimit($pkListId);
            $classLimitReturn = $this->kcTimeToZn($classLimit,'class');
            $subjectLimit = $this->getLimitTeacerAndclass($pkListId);
            $subjectLimitReturn = $this->kcTimeToZn($subjectLimit,'subject');
            $pkRange = $this->pkRang($pkListId,0);
            $pkRange = $this->njToGoZn($pkRange);
            $weekAndCount = $this->weekJie($pkListId);
            $weekAndCount[]['days'] = $weekAndCount['days'];
            $weekAndCount[]['count'] = $weekAndCount['count'];
            $teacherLimit = $this->teacherLimit($pkListId);
            $teacherLimit = $this->kcTimeToZn($teacherLimit,'teacher');
            $yP = $this->prvPk($pkListId);
            $yP = $this->kmYpk($yP);
            $hbClass = $this->hb($pkListId);
            $hbClass = $this->getHbPx($hbClass);
            $this->returnJson(array('classSet' => array(
                'classLimit' => $classLimitReturn,
                'subjectLimitReturn' => $subjectLimitReturn,
                'pkRange' => $pkRange,
                'weekAndCount' => $weekAndCount,
                'teacherLimit' => $teacherLimit,
                'yP' =>$yP,
                'hbClass' => $hbClass
            ),'message' => 'success','statu' => 1),true);
        }
    }
    //合班课程班级转化
    private function hbToData($data){
        foreach($data as $key => $value){
            $data[$key]['data'] = unserialize($value['data']);
        }
        return $data;
    }
    private function kmYpk($data){
        $array = array();
        $arrayRE = array();
        foreach($data as $key => $value){
            $arrayRE[$value['gradeId']][$value['classId']][] = $value;
        }
        $arrRel = array();
        $i = 0;
        foreach($arrayRE as $key => $value){
            foreach($value as $key1 => $value1){
                $arrRel[$i]['name'] = $this->toWeekArray($value1[0]['gradeName'],'year').$value1[0]['className'];
                foreach($value1 as $key2 => $value2){
                    $arrRel[$i]['data'][] =  $value2['subjectName'].'('.$this->toWeekArray($value2['x'],'wk').$this->toWeekArray($value2['y'],'js').')';
                }
            }
        }
        return $arrRel;
    }
    private function getHbPx($hbClass){
        $array = array();
        $i = 0;
        foreach($hbClass as $key => $value){
            $data = unserialize($value['data']);
            $array[$i]['subject'] = $value['subjectName'];
            foreach($data as $key1 => $value1){
                $array[$i]['data'][] = $this->toWeekArray($value1['gradeName'],'year').$value1['className'].'班（'.$value1['techerName'].')';
            }
            $i++;
        }
        return $array;
    }
    private function njToGoZn($pkRange){
        $str = array();
        foreach($pkRange as $key => $value){
            $str[] = $this::toWeekArray($value,'year').'    ';
        }
        return $str;
    }
    private function kcTimeToZn($data,$str){
        $return = array();
        if($str == 'class'){
            foreach($data as $key => $value){
                $value['gradeId'];
                $grade = $this->toWeekArray($value['gradeId'],'year');
                $week = $this->toWeekArray($value['x'],'wk');
                $js = $this->toWeekArray($value['y'],'js');
                $class = $value['className'];
                $return[] = $grade.$class.'('.$week.$js.')';
            }
        }
        if($str == 'teacher'){
            foreach($data as $key => $value){
                $value['gradeId'];
                $week = $this->toWeekArray($value['x'],'wk');
                $js = $this->toWeekArray($value['y'],'js');
                $teacher = $value['teacherName'];
                $return[] =$teacher.'('.$week.$js.')';
            }
        }
        if($str == 'subject'){
            $i = 0;
            foreach($data as $key => $value){
                $value['gradeId'];
                $grade = $this->toWeekArray($value['gradeId'],'year');
                $week = $this->toWeekArray($value['x'],'wk');
                $js = $this->toWeekArray($value['y'],'js');
                $class = $value['className'];
                $return[$i]['data'] = $grade.$class.'('.$week.$js.')';
                $return[$i]['curriculumName'] = $value['curriculumName'];
                $i++;
            }
        }
        return $return;
    }
    private function toWeekArray($i,$str){
        $return = array();
        if($str == 'year'){
            $return = array(
                '1' => '一年级',
                '2' => '二年级',
                '3' => '三年级',
                '4' => '四年级',
                '5' => '五年级',
                '6' => '六年级',
                '7' => '初一',
                '8' => '初二',
                '9' => '初三',
                '10' => '高一',
                '11' => '高二',
                '12' => '高三',
            );
        }
        if($str == 'wk'){
            $return = array(
                '0' => '周一',
                '1' => '周二',
                '2' => '周三',
                '3' => '周四',
                '4' => '周五',
                '5' => '周六',
                '6' => '周末',
            );
        }
        if($str == 'js'){
            $return = array(
                '0' => '第一节',
                '1' => '第二节',
                '2' => '第三节',
                '3' => '第四节',
                '4' => '第五节',
                '5' => '第六节',
                '6' => '第七节',
                '7' => '第八节',
                '8' => '第九节',
                '9' => '第十节',
                '10' => '第十一节',
                '11' => '第十二节',
                '12' => '第十三节',
                '13' => '第十四节',
                '14' => '第十五节',
                '15' => '第十六节',
                '16' => '第十七节',
            );
        }
        return $return[$i];
    }
    //班级不排课
    public function classLimit($pkListId){
        $classLimitLimit = array();
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        $classLimit = M('pk_class_time_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->select();
        foreach($classLimit as $key => $value){
            $data = unserialize($value['classSet']);
            foreach($data as $key1 => $value1){
                foreach($value1 as $key2  => $value2){
                    if($value2 == 2){
                        $classLimitLimit[] = array(
                            'gradeId' => $value['gradeId'],
                            'classId' => $value['classId'],
                            'className' => $value['className'],
                            'gradeName' => $value['gradeName'],
                            'y' => $key1,  //代表纵坐标
                            'x' => $key2   //代表横坐标，x=0相当于星期一，x=1相当于星期二
                        );
                    }
                }
            }
        }
        return $classLimitLimit;
    }
    //科目不排课
    private function getLimitTeacerAndclass($pkListId){
        $subjectLimitLimit = array();
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        $classLimit = M('pk_curriculum_limit')->where(array('pkListId' => $pkListId,'scId' => $scId))->select();
        foreach($classLimit as $key => $value){
            $data = unserialize($value['classSet']);
            foreach($data as $key1 => $value1){
                foreach($value1 as $key2  => $value2){
                    if($value2 == 3){
                        $subjectLimitLimit[] = array(
                            'className' => $value['className'],
                            'gradeName' => $value['gradeName'],
                            'classId' => $value['classId'],
                            'gradeId' => $value['gradeId'],
                            'curriculumName' => $value['curriculumName'],
                            'curriculumId' => $value['curriculumId'],
                            'y' => $key1,  //代表纵坐标
                            'x' => $key2   //代表横坐标，x=0相当于星期一，x=1相当于星期二
                        );
                    }
                }
            }
        }
        return $subjectLimitLimit;
    }
    //教师不排课
    private function teacherLimit($pkListId){
        $teacherLimitLimit = array();
        $classLimit = M('pk_teacher_limit')->where(array('pkListId' => $pkListId))->select();
        foreach($classLimit as $key => $value){
            $data = unserialize($value['classSet']);
            foreach($data as $key1 => $value1){
                foreach($value1 as $key2  => $value2){
                    if($value2 == 4){
                        $teacherLimitLimit[] = array(
                            'teacherId' => $value['teacherId'],
                            'teacherName' => $value['teacherName'],
                            'y' => $key1,  //代表纵坐标
                            'x' => $key2   //代表横坐标，x=0相当于星期一，x=1相当于星期二
                        );
                    }
                }
            }
        }
        return $teacherLimitLimit;
    }
    //排课范围
    public function pkRang($pkListId,$true){ //true 0 为年级1为班级加年级
        $pkList = M('pk_list')->where(array('id' => $pkListId))->find();
        $scId = $pkList['scId'];
        $rangeList = explode(',',$pkList['pkRange']);
        $i = 0;
        $data = array();
        foreach($rangeList as $key => $value){
            $grade = M('grade')->where(array('scId' => $scId,'gradeid'=> $value))->find();
            $gradeName = $grade['name'];
            if($true){
                $class = M('class')->where(array('scId' => $scId,'grade'=> $value))->order('classname')->select();
                foreach($class as $key1 => $value1){
                    unset($value1['major']);
                    unset($value1['levelid']);
                    unset($value1['branch']);
                    unset($value1['number']);
                    unset($value1['scId']);
                    unset($value1['lastRecordTime']);
                    unset($value1['createTime']);
                    unset($value1['grade']);
                    unset($value1['userid']);
                    $value1['classId'] =  $value1['classid'];
                    unset($value1['classid']);
                    $value1['className'] =  $value1['classname'];
                    unset($value1['classname']);
                    $value1['gradeName'] = $gradeName;
                    $value1['gradeId'] = $value;
                    $data[] = $value1;
                }
            }else{
                $data[] = $gradeName;
            }
            $i++;
        }
        return $data;
    }
    //课程预排
    private function prvPk($pkListId){
        $preGradeClassPk = array();
        $preClass = M('pk_class_before_plan')->where(array('pkListId' => $pkListId))->select();
        foreach($preClass as $key => $value){
            $data = unserialize($value['classSet']);
            foreach($data as $key1 => $value1){
                foreach($value1 as $key2 => $value2){
                    if(is_array($value2)){
                        if($value2['statu'] == 5){
                            $preGradeClassPk[] = array(
                                'subjectName' => $value2['subject'],
                                'subjectId' =>$value2['subjectId'],
                                'statu' => $value2['statu'],
                                'teacherName' =>$value2['teacherName'],
                                'teacherId' => $value2['teacherId'],
                                'className' => $value['className'],
                                'gradeName' => $value['gradeName'],
                                'classId' => $value['classId'],
                                'gradeId' => $value['gradeId'],
                                'y' => $key1,  //代表纵坐标
                                'x' => $key2   //代表横坐标，x=0相当于星期一，x=1相当于星期二
                            );
                        }
                    }
                }
            }
        }
        return $preGradeClassPk;
    }
    //周课时
    public function weekJie($pkListId){
        $pkList = M('pk_time')->where(array('pkListId' => $pkListId))->find();
        $data = unserialize($pkList['ClassSet']);
        $maxDay = 0;
        foreach($data['week'] as $key => $value){
            $i = 0;
            foreach($value as $key1 => $value1){
                if($value1 == 1){
                   $i++;
                }
            }
            if($i>$maxDay){
                $maxDay = $i;
            }
        }
        $count = 0;
        foreach($data['section'] as $key => $value){
            $count = $count + $value;
        }
        return array('days' => $maxDay,'count' =>$maxDay*$count );
    }
    //合班
    private function hb($pkListId){
        $hbClass = M('pk_hb_set')->where(array('pkListId' => $pkListId))->select();
        $return = array();
        $i = 0;
        foreach($hbClass as $key => $value){
            $return[$i]['subjectId'] = $value['subjectId'];
            $return[$i]['teacherId'] = $value['teacherId'];
            $return[$i]['teacherName'] = $value['teacherName'];
            $return[$i]['subjectName'] = $value['subjectName'];
            $return[$i]['data'] = $value['classSet'];
            $i++;
        }
        return $return;
    }
    //判断教师课程是否已经排课
    private function pdTeacherPk($X,$Y,$class,$grade){
        $teacherTable ='';
    }
    //判断课程节数是否可拍
    private function pdClassCount(){

    }
    private function Jb($jie){
        $return = array(
            0 => '语文',
            1 => '英语',
            2 => '数学',
            3 => '物理',
            4 => '政治',
            5 => '化学',
            6 => '历史',
            7 => '生物',
            8 => '地理',
            9 => '音乐',
            10 => '美术',
            11 => '科技'
        );
    }
    //public
    //得到教师，和教师所交课程
    public function getTeacherAndSubject(){
        $type = I('get.type');
        $gradeId = I('get.gradeId');
        $classId = I('get.classId');
        if($type == 'pkList'){
            $session = $this->get_session('loginCheck',false);
            $scId = $session['scId'];
            $pkListId = I('get.pkListId');
            $data  = M('')->query("SELECT * FROM mks_jw_schedule WHERE scId=$scId and gradeId=$gradeId AND classId = $classId GROUP BY techerName");
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $data),true);
        }
    }
    //教学计划默认
    public function classTimeSeting(){
        //M('jw_schedule')->where(array('className'));
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        $pkListId = I('get.pkListId');
        $pk_list = M('pk_list')->where(array('id' => $pkListId,'scId' => $scId))->find();
        $range = $pk_list['pkRange'];
        $rangeList = explode(',',$range);
        $returnData = array();
        foreach($rangeList as $key => $value){
            $returnData[] = M('')->query("SELECT subject,techerId,techerName,grade,className FROM mks_jw_schedule where grade=$value ORDER BY className");
        }
        $returnDataTrue = array();
        foreach($returnData as $key => $value){
            foreach($value as $key1 => $value1){
                $value1['section'] = 0;
                $returnDataTrue[$value1['grade']][$value1['className']][] = $value1;
            }
        }
        $return = array();
        //print_r($returnDataTrue);
        foreach($returnDataTrue as $key => $value){
            foreach($value as $key1 => $value1){
                $return[] = $value1;
            }
        }
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        M('pk_jx_plan')->add(array(
            'scId' => $scId,
            'pkListId' => 1,
            'classSet' => serialize($return),
            'createTime' => strtotime(date('Y-m-d'))
        ));
        $Data = M('pk_jx_plan')->where(array('pkListId' =>1 ))->find();
    }
    //
    public function getTest(){
        $data = M('pk_default_limit')->where(array('pkListId' => 15))->find();
        $data = unserialize($data['classSet']);
        for($j = 0; $j<count($data[]);$j++){
            for($i = 0; $i<7; $i++){
                echo $data[$i][$j];
            }
        }
    }
}