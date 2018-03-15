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
class ScheduleController extends Base
{
    public function sudent(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type == 'studentClassTable'){
            $user = M('user')->field('gradeId,class')->where(array('scId' => $scId,'id' => $userId))->find();
            $gradeId = $user['gradeId'];
            $classId = $user['class'];
            $data = M('pk_class_result')->where(array('scId' => $scId, 'gradeId' => $gradeId, 'classId' => $classId))->select();
            $nowTime = date('Y-m-d');
            $pkList = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1,'startTime' => array('ELT',$nowTime),'endTime' => array('EGT',$nowTime)))->select();
            $pk = array();
            foreach($pkList as $key => $value){
                $pk[$value['id']] = $value;
            }
            $week = date('W',strtotime($nowTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $pkListId = 0;
            foreach($data as $key => $value){
                if($pk[$value['pkListId']]){
                    if($value['ifWeek'] == 0){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                    if($value['ifWeek'] == $ifWeek){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                }
            }
            if(!$pkListId){
                $this->returnJson(array('statu' => 2,'message' => '该班级暂无课表'),true);
            }
            $table = unserialize($data['schedule']);
            $time = M('pk_time')->where(array('scId' => $scId, 'pkListId' => $pkListId))->find();
            $time = unserialize($time['ClassSet']);
            $time = $time['day'];
            $timeRe = array();
            $jie = array();
            $i = 1;
            foreach ($time as $key => $value) {
                $startTimeRe = explode(' ', $value['startTime']);
                $endTimeRe = explode(' ', $value['endTime']);
                $startTimeRe = explode(':', $startTimeRe[4]);
                $startTimeRe = $startTimeRe[0] . ':' . $startTimeRe[1];
                $endTimeRe = explode(':', $endTimeRe[4]);
                $endTimeRe = $endTimeRe[0] . ':' . $endTimeRe[1];
                $timeRe[] = $startTimeRe . '-' . $endTimeRe;
                $jie[] = '第' . $i . '节';
                $i++;
            }
            $returnList = array();
            $returnList[0] = $timeRe;
            $returnList[1] = $jie;
            $i = 2;
            foreach ($table as $key => $value) {
                $returnList[$key][0] = array('subjectName' => $jie[$key], 'teacherName' => '');
                $returnList[$key][1] = array('subjectName' => $timeRe[$key], 'teacherName' => '');
                $i = 2;
                foreach ($value as $key1 => $value1) {
                    if (is_array($value1)) {
                        $returnList[$key][$i] = $value1;
                    } else {
                        $returnList[$key][$i] = array(
                            'subjectName' => '',
                            'subjectId' => '',
                            'statu' => (int)$value1,
                            'teacherName' => '',
                            'teacherId' => '',
                        );
                    }
                    $i++;
                }
            }
            $this->returnJson(array('statu' => 1, 'data' => $returnList), true);
        }
        if($type == 'studentClassTableExport'){
            $user = M('user')->field('gradeId,class')->where(array('scId' => $scId,'id' => $userId))->find();
            $gradeId = $user['gradeId'];
            $classId = $user['class'];
            $data = M('pk_class_result')->where(array('scId' => $scId, 'gradeId' => $gradeId, 'classId' => $classId))->select();
            $nowTime = date('Y-m-d');
            $pkList = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1,'startTime' => array('ELT',$nowTime),'endTime' => array('EGT',$nowTime)))->select();
            $pk = array();
            foreach($pkList as $key => $value){
                $pk[$value['id']] = $value;
            }
            $week = date('W',strtotime($nowTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $pkListId = 0;
            foreach($data as $key => $value){
                if($pk[$value['pkListId']]){
                    if($value['ifWeek'] == 0){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                    if($value['ifWeek'] == $ifWeek){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                }
            }
            if(!$pkListId){
                $this->returnJson(array('statu' => 2,'message' => '该班级暂无课表'),true);
            }
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
    }
    public function teacher(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type == 'getTeacherTable'){
            if(I('get.teacherId')){
                $userId = I('get.teacherId');
            }
            $data = M('pk_teacher_result')->where(array('scId' => $scId,'teacherId' => $userId))->select();
            $nowTime = date('Y-m-d');
            $pkList = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1,'startTime' => array('ELT',$nowTime),'endTime' => array('EGT',$nowTime)))->select();
            $pk = array();
            foreach($pkList as $key => $value){
                $pk[$value['id']] = $value;
            }
            $week = date('W',strtotime($nowTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $pkListId = 0;
            foreach($data as $key => $value){
                if(isset($pk[$value['pkListId']])){
                    if($value['ifWeek'] == 0){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                    if($value['ifWeek'] == $ifWeek){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                }
            }
            if(!$pkListId){
                $this->returnJson(array('statu' => 2,'message' => '该教师暂无课表'),true);
            }
            $techerId = $userId;
            $table = unserialize($data['classSet']);
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
            $hb = $this->hbToData($this->hb($pkListId));//合班
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
            /*$returnRel = array();
            $i = 0;
            foreach($returnList as $key => $value){
                $returnRel[$i]['jie'] = $value[0]['subjectName'];
                $returnRel[$i]['time'] = $value[1]['subjectName'];
                $returnRel[$i]['one'] = $value[0]['subjectName'];
                $returnRel[$i]['jie'] = $value[0]['subjectName'];
                $returnRel[$i]['jie'] = $value[0]['subjectName'];
                $returnRel[$i]['jie'] = $value[0]['subjectName'];
                $returnRel[$i]['jie'] = $value[0]['subjectName'];
                $returnRel[$i]['jie'] = $value[0]['subjectName'];
                $returnRel[$i]['jie'] = $value[0]['subjectName'];
                $i++;
            }
            print_r($returnList);
            exit();*/
            $this->returnJson(array('statu' => 1 ,'data' => $returnList,'message' => 'success'),true);
        }
        if($type == 'teacherExport'){
            $user = M('user')->field('gradeId,class')->where(array('scId' => $scId,'id' => $userId))->find();
            $gradeId = $user['gradeId'];
            $classId = $user['class'];
            $data = M('pk_teacher_result')->where(array('scId' => $scId,'teacherId' => $userId))->select();
            $nowTime = date('Y-m-d');
            $pkList = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1,'startTime' => array('ELT',$nowTime),'endTime' => array('EGT',$nowTime)))->select();
            $pk = array();
            foreach($pkList as $key => $value){
                $pk[$value['id']] = $value;
            }
            $week = date('W',strtotime($nowTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $pkListId = 0;
            foreach($data as $key => $value){
                if($pk[$value['pkListId']]){
                    if($value['ifWeek'] == 0){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                    if($value['ifWeek'] == $ifWeek){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                }
            }
            if(!$pkListId){
                $this->returnJson(array('statu' => 2,'message' => '该班级暂无课表'),true);
            }
            $techerId = $userId;
            $table = unserialize($data['classSet']);
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
        if($type == 'getGradeAndClass'){
            $data = M('jw_schedule')->where(array('scId' => $scId,'techerId' => $userId))->group('gradeId,classId')->order('gradeName,className')->select();
            $relData = array();
            foreach($data as $key => $value){
                $relData[$value['gradeName']]['className'] = $this->gradeToZhong($value['gradeName']);
                $relData[$value['gradeName']]['data'][] = $value;
            }
            sort($relData);
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $relData),true);
        }
        /*if($type == 'getGrade'){

        }
        if($type == 'getClass'){

        }*/
        if($type == 'getTeacherClassTable'){
            $gradeId = I('get.gradeId');
            $classId = I('get.classId');
            $data = M('pk_class_result')->where(array('scId' => $scId, 'gradeId' => $gradeId, 'classId' => $classId))->select();
            $nowTime = date('Y-m-d');
            $pkList = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1,'startTime' => array('ELT',$nowTime),'endTime' => array('EGT',$nowTime)))->select();
            $pk = array();
            foreach($pkList as $key => $value){
                $pk[$value['id']] = $value;
            }
            $week = date('W',strtotime($nowTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $pkListId = 0;
            foreach($data as $key => $value){
                if($pk[$value['pkListId']]){
                    if($value['ifWeek'] == 0){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                    if($value['ifWeek'] == $ifWeek){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                }
            }
            if(!$pkListId){
                $this->returnJson(array('statu' => 2,'message' => '该班级暂无课表'),true);
            }
            $table = unserialize($data['schedule']);
            $time = M('pk_time')->where(array('scId' => $scId, 'pkListId' => $pkListId))->find();
            $time = unserialize($time['ClassSet']);
            $time = $time['day'];
            $timeRe = array();
            $jie = array();
            $i = 1;
            foreach ($time as $key => $value) {
                $startTimeRe = explode(' ', $value['startTime']);
                $endTimeRe = explode(' ', $value['endTime']);
                $startTimeRe = explode(':', $startTimeRe[4]);
                $startTimeRe = $startTimeRe[0] . ':' . $startTimeRe[1];
                $endTimeRe = explode(':', $endTimeRe[4]);
                $endTimeRe = $endTimeRe[0] . ':' . $endTimeRe[1];
                $timeRe[] = $startTimeRe . '-' . $endTimeRe;
                $jie[] = '第' . $i . '节';
                $i++;
            }
            $returnList = array();
            $returnList[0] = $timeRe;
            $returnList[1] = $jie;
            $i = 2;
            foreach ($table as $key => $value) {
                $returnList[$key][0] = array('subjectName' => $jie[$key], 'teacherName' => '');
                $returnList[$key][1] = array('subjectName' => $timeRe[$key], 'teacherName' => '');
                $i = 2;
                foreach ($value as $key1 => $value1) {
                    if (is_array($value1)) {
                        $returnList[$key][$i] = $value1;
                    } else {
                        $returnList[$key][$i] = array(
                            'subjectName' => '',
                            'subjectId' => '',
                            'statu' => (int)$value1,
                            'teacherName' => '',
                            'teacherId' => '',
                        );
                    }
                    $i++;
                }
            }
            $this->returnJson(array('statu' => 1, 'data' => $returnList), true);
        }
        if($type == 'getTeacherClassTableExport'){
            $user = M('user')->field('gradeId,class')->where(array('scId' => $scId,'id' => $userId))->find();
            $gradeId = $user['gradeId'];
            $classId = $user['class'];
            $data = M('pk_teacher_result')->where(array('scId' => $scId,'teacherId' => $userId))->select();
            $nowTime = date('Y-m-d');
            $pkList = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1,'startTime' => array('ELT',$nowTime),'endTime' => array('EGT',$nowTime)))->select();
            $pk = array();
            foreach($pkList as $key => $value){
                $pk[$value['id']] = $value;
            }
            $week = date('W',strtotime($nowTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $pkListId = 0;
            foreach($data as $key => $value){
                if($pk[$value['pkListId']]){
                    if($value['ifWeek'] == 0){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                    if($value['ifWeek'] == $ifWeek){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                }
            }
            if(!$pkListId){
                $this->returnJson(array('statu' => 2,'message' => '该班级暂无课表'),true);
            }
            $table = unserialize($data['classSet']);
            $gradeIdClass = array();
            foreach($table as $key => $value){
                foreach($value as $key1 => $value1){
                    if($value1['statu'] == 5){
                        $gradeIdClass[$value1['gradeId']][$value1['classId']] = 1;
                    }
                }
            }
            $classData = M('pk_class_result')->where(array('scId' => $scId,'pkListId' => $pkListId))->select();
            $classDataRe = array();
            foreach($classData as $key => $value){
                if(isset($gradeIdClass[$value['gradeId']][$value['classId']])){
                    $classDataRe[] = $value;
                }
            }
            $data = $classDataRe;
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
    private function hbToData($data){
        foreach($data as $key => $value){
            $data[$key]['data'] = unserialize($value['data']);
        }
        return $data;
    }
    public function admin(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type == 'getGradeClass'){
            $data  = M('')->query("SELECT * FROM mks_jw_schedule WHERE scId=$scId GROUP BY classId order BY gradeName,className");
            $relData = array();
            foreach($data as $key => $value){
                $relData[$value['gradeName']]['className'] = $this->gradeToZhong($value['gradeName']);
                $relData[$value['gradeName']]['data'][] = $value;
            }
            $i=0;
            $return = array();
            foreach($relData as $key => $value){
                $return[$i] = $value;
                $i++;
            }
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $return),true);
        }
        if($type == 'classTable'){
            $gradeId = $_GET['gradeId'];
            $classId = $_GET['classId'];
            $data = M('pk_class_result')->where(array('scId' => $scId, 'gradeId' => $gradeId, 'classId' => $classId))->select();
            $nowTime = date('Y-m-d');
            $pkList = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1,'startTime' => array('ELT',$nowTime),'endTime' => array('EGT',$nowTime)))->select();
            $pk = array();
            foreach($pkList as $key => $value){
                $pk[$value['id']] = $value;
            }
            $week = date('W',strtotime($nowTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $pkListId = 0;
            foreach($data as $key => $value){
                if($pk[$value['pkListId']]){
                    if($value['ifWeek'] == 0){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                    if($value['ifWeek'] == $ifWeek){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                }
            }
            if(!$pkListId){
                $this->returnJson(array('statu' => 2,'message' => '该班级暂无课表'),true);
            }
            $table = unserialize($data['schedule']);
            $time = M('pk_time')->where(array('scId' => $scId, 'pkListId' => $pkListId))->find();
            $time = unserialize($time['ClassSet']);
            $time = $time['day'];
            $timeRe = array();
            $jie = array();
            $i = 1;
            foreach ($time as $key => $value) {
                $startTimeRe = explode(' ', $value['startTime']);
                $endTimeRe = explode(' ', $value['endTime']);
                $startTimeRe = explode(':', $startTimeRe[4]);
                $startTimeRe = $startTimeRe[0] . ':' . $startTimeRe[1];
                $endTimeRe = explode(':', $endTimeRe[4]);
                $endTimeRe = $endTimeRe[0] . ':' . $endTimeRe[1];
                $timeRe[] = $startTimeRe . '-' . $endTimeRe;
                $jie[] = '第' . $i . '节';
                $i++;
            }
            $returnList = array();
            $returnList[0] = $timeRe;
            $returnList[1] = $jie;
            $i = 2;
            foreach ($table as $key => $value) {
                $returnList[$key][0] = array('subjectName' => $jie[$key], 'teacherName' => '');
                $returnList[$key][1] = array('subjectName' => $timeRe[$key], 'teacherName' => '');
                $i = 2;
                foreach ($value as $key1 => $value1) {
                    if (is_array($value1)) {
                        $returnList[$key][$i] = $value1;
                    } else {
                        $returnList[$key][$i] = array(
                            'subjectName' => '',
                            'subjectId' => '',
                            'statu' => (int)$value1,
                            'teacherName' => '',
                            'teacherId' => '',
                        );
                    }
                    $i++;
                }
            }
            $this->returnJson(array('statu' => 1, 'data' => $returnList), true);
        }
        if($type == 'exportClassTable'){
            $gradeId = $_GET['gradeId'];
            $classId = $_GET['classId'];
            $data = M('pk_class_result')->where(array('scId' => $scId, 'gradeId' => $gradeId, 'classId' => $classId))->select();
            $nowTime = date('Y-m-d');
            $pkList = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1,'startTime' => array('ELT',$nowTime),'endTime' => array('EGT',$nowTime)))->select();
            $pk = array();
            foreach($pkList as $key => $value){
                $pk[$value['id']] = $value;
            }
            $week = date('W',strtotime($nowTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $pkListId = 0;
            foreach($data as $key => $value){
                if($pk[$value['pkListId']]){
                    if($value['ifWeek'] == 0){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                    if($value['ifWeek'] == $ifWeek){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                }
            }
            if(!$pkListId){
                $this->returnJson(array('statu' => 2,'message' => '该班级暂无课表'),true);
            }
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
        if($type == 'exportTeacherTable'){
            $techerId = I('get.techerId');
            $data = M('pk_teacher_result')->where(array('scId' => $scId,'teacherId' => $techerId ))->select();
            $nowTime = date('Y-m-d');
            $pkList = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1,'startTime' => array('ELT',$nowTime),'endTime' => array('EGT',$nowTime)))->select();
            $pk = array();
            foreach($pkList as $key => $value){
                $pk[$value['id']] = $value;
            }
            $week = date('W',strtotime($nowTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $pkListId = 0;
            foreach($data as $key => $value){
                if($pk[$value['pkListId']]){
                    if($value['ifWeek'] == 0){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                    if($value['ifWeek'] == $ifWeek){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                }
            }
            if(!$pkListId){
                $this->returnJson(array('statu' => 2,'message' => '该班级暂无课表'),true);
            }
            $techerId = $userId;
            $table = unserialize($data['classSet']);
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
        if($type == 'getTeacherList'){
            $data  = M('')->query("SELECT * FROM mks_jw_schedule WHERE scId=$scId order BY gradeName,className");
            $relData = array();
            foreach($data as $key => $value){
                if($value['techerId']){
                    $relData[$value['gradeName']]['techerName'] = $this->gradeToZhong($value['gradeName']);
                    $relData[$value['gradeName']]['data'][$value['className']]['techerName'] = $value['className'];
                    $relData[$value['gradeName']]['data'][$value['className']]['data'][] = $value;
                }
            }
            $i = 0;
            $dataRe = array();
            foreach($relData as $key => $value){
                $dataRe[] = $value;
            }
            $relData = $dataRe;
            foreach($relData as $key => $value){
                sort($relData[$key]['data']);
            }
            $this->returnJson(array('statu' => 1,'data' => $relData),true);
        }
        if($type == 'teacherTable'){
            $techerId = I('get.techerId');
            $data = M('pk_teacher_result')->where(array('scId' => $scId,'teacherId' => $techerId))->select();
            $nowTime = date('Y-m-d');
            $pkList = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1,'startTime' => array('ELT',$nowTime),'endTime' => array('EGT',$nowTime)))->select();
            $pk = array();
            foreach($pkList as $key => $value){
                $pk[$value['id']] = $value;
            }
            $week = date('W',strtotime($nowTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $pkListId = 0;
            foreach($data as $key => $value){
                if($pk[$value['pkListId']]){
                    if($value['ifWeek'] == 0){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                    if($value['ifWeek'] == $ifWeek){
                        $data = $value;
                        $pkListId = $value['pkListId'];
                        break;
                    }
                }
            }
            if(!$pkListId){
                $this->returnJson(array('statu' => 2,'message' => '该教师暂无课表'),true);
            }
            $techerId = $userId;
            $table = unserialize($data['classSet']);
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
            $hb = $this->hbToData($this->hb($pkListId));//合班
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
        if($type == 'allClass'){
            $data = M('pk_class_result')->where(array('scId' => $scId))->order('gradeName,className')->select();
            $nowTime = date('Y-m-d');
            $pkList = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1,'startTime' => array('ELT',$nowTime),'endTime' => array('EGT',$nowTime)))->select();
            $pk = array();
            foreach($pkList as $key => $value){
                $pk[$value['id']] = $value;
            }
            $week = date('W',strtotime($nowTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $pkListId = array();
            foreach($pkList as $key => $value){
                if(isset($pk[$value['id']])){
                    if($value['ifWeek'] == 0 || $value['ifWeek'] === $ifWeek){
                        $pkListId[] = $value['id'];
                    }
                }
            }
            if(count($pkListId)<1){
                $this->returnJson(array('statu' => 2,'message' => '该班级暂无课表'),true);
            }
            $kkkk = array();
            foreach($pkListId as $key => $value){
                $kkkk[$value] = $value;
            }
            $relData = array();
            foreach($data as $key => $value){
                if(isset($kkkk[$value['pkListId']])){
                    $relData[] = $value;
                }
            }
            $data = $relData;
            unset($pk);
            unset($relData);
            $returnList = array();
            $i = 0;
            //
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
            //
            $this->returnJson(array('statu' => 1 ,'data' => $returnList,'message' => 'success'),true);
        }
    }
}