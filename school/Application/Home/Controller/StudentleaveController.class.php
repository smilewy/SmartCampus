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
class StudentleaveController extends Base
{
    private function stateToZn($i){
        $array = array(
            1 => '待处理',
            2 => '已维修',
            3 => '维修失败',
            4 => '已验收'
        );
        return $array[$i];
    }
    public function createLeave(){ //新建请假
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'createLeave'){
            $user = M('user')->field('class,className,gradeId,grade')->where(array('scId' => $scId,'id' => $userId))->find();
            //$class = M('class')->where(array('scId' => $scId,'classid' => $user['class']))->find();
            //$approverId = $class['userid'];
            if(M('student_leave_list')->add(array(
                'title' => I('post.title'),
                'leaveTypeId' => I('post.leaveTypeId'),
                'startTime' => I('post.startTime'),
                'endTime' => I('post.endTime'),
                'times' => round((strtotime(I('post.endTime')) - strtotime(I('post.startTime')))/86400,1),
                'approverId' => 0,
                'reason' => I('post.reason'),
                'scId' => $scId,
                'createTime' => date('Y-m-d H:i:s'),
                'userId' => $userId,
                'state' => 0,
                'leaveState' => 0,
                'replaceState' => 0,
                'replaceUserId' => null,
                'gradeId' => $user['gradeId'],
                'classId' => $user['class'],
                'gradeName' => $user['grade'],
                'className' => $user['className']
            ))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
    }
    public function getLeaveList(){//请假记录
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getLeaveList'){
            $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $userId))->find();
            $startTime = '2010-09-13 00:00:00';
            $endTime = '2088-09-16 00:00:00';
            if($_GET['startTime']){
                $startTime = $_GET['startTime'];
            }
            if($_GET['endTime']){
                $endTime = $_GET['endTime'];
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $data = M('student_leave_list')->where(array('userId' => $userId,'scId' => $scId))->select();
            foreach($data as $key => $value){
                if($startTime>$value['endTime'] or  $endTime<$value['startTime']){
                    unset($data[$key]);
                }else{
                    $appName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['approverId']))->find();
                    $data[$key]['appName'] = $appName['name'];
                    $data[$key]['userName'] = $userName['name'];
                    $data[$key]['createTime'] = date('Y-m-d H:i',strtotime($value['startTime'])).'  到  '.date('Y-m-d H:i',strtotime($value['endTime']));
                }
            }
            sort($data);
            $this->returnJson(array('stata' => 1,'message' => 'success','data' => $data),true);
        }
        if($type == 'export'){
            $startTime = '2010-09-13 00:00:00';
            $endTime =  '2088-09-16 00:00:00';
            if($_GET['startTime']){
                $startTime = $_GET['startTime'];
            }
            if($_GET['endTime']){
                $endTime = $_GET['endTime'];
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $data = M('student_leave_list')->field('leaveId,leaveTypeId,title,times,state,createTime,userId')->where(array('userId' => $userId,'scId' => $scId,'createTime' => array(array('gt',$startTime),array('lt',$endTime))))->select();
            foreach($data as $key => $value){
                $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['userId']))->find();
                $data[$key]['userName'] = $userName['name'];
                $data[$key]['type'] = $this->toLeave($data[$key]['leaveTypeId']);
                $data[$key]['state'] = $this->state($data[$key]['state']);
            }
            $tr = array(
                '0' => array(
                    'en' => 'title',
                    'zh' => '标题'
                ),
                '1' => array(
                    'en' => 'type',
                    'zh' => '类型'
                ),  '2' => array(
                    'en' => 'createTime',
                    'zh' => '创建时间'
                ), '3' => array(
                    'en' => 'userName',
                    'zh' => '申请人'
                ), '4' => array(
                    'en' => 'state',
                    'zh' => '审批状态'
                )
            );
            $this->export($data,$tr);
        }
    }
    public function replaceLe(){//代学生请假
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'createLeave'){
            $user = M('user')->field('class,className,gradeId,grade')->where(array('scId' => $scId,'id' =>I('post.userId')))->find();
            //$class = M('class')->where(array('scId' => $scId,'classid' => $user['class']))->find();
            //$approverId = $class['userid'];
            if(M('student_leave_list')->add(array(
                'title' => I('post.title'),
                'leaveTypeId' => I('post.leaveTypeId'),
                'startTime' => I('post.startTime'),
                'endTime' => I('post.endTime'),
                'times' => date('d',strtotime(I('post.endTime')) - strtotime( I('post.startTime'))),
                'approverId' => 0,
                'reason' => I('post.reason'),
                'scId' => $scId,
                'createTime' => date('Y-m-d H:i:s'),
                'userId' => I('post.userId'),
                'state' => 0,
                'leaveState' => 0,
                'replaceState' => 1,
                'replaceUserId' => $userId,
                'gradeId' => $user['gradeId'],
                'classId' => $user['class'],
                'gradeName' => $user['grade'],
                'className' => $user['className']
            ))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'getStudentList'){
            if($userRoleId == $this::$teacherRoleId){
                $grade = M('jw_schedule')->field('gradeName,gradeId,classId')->where(array('scId' => $scId,'techerId' => $userId))->group('gradeId')->order('gradeName')->select();
                $class = M('class')->field('classname,grade,classid')->where(array('scId' => $scId))->order('classname')->select();
                $classrel = array();
                foreach ($class as $key => $value){
                    $classrel[$value['classid']] = $value['classname'];
                }
                $gradeRe = array();
                foreach($grade as $key => $value){
                    $gradeRe[$value['gradeId']][$value['classId']] =1;
                }
                if($grade =  M('grade')->field('name,gradeid')->where(array('userId' => $userId,'scId' => $scId))->select()){
                    foreach ($grade as $key => $value){
                        foreach ($class as $key1 => $value1){
                            if($value['gradeid'] == $value1['grade']){
                                $gradeRe[$value['gradeid']][$value1['classid']] = 1;
                            }
                        }
                    }
                }if($return = M('class')->field('classname,grade,classid')->where(array('userid' => $userId,'scId' => $scId))->select()){
                    foreach ($return as $key => $value){
                        $gradeRe[$value['grade']][$value['classid']] =  1;
                    }
                }
                $data = M('user')->field('id,name,class,grade,className,gradeId')->where(array('scId' => $scId,'roleId' => $this::$studentRoleId))->order('grade,ClassName')->select();
                $array = array();
                foreach($data as $key => $value){
                    if($gradeRe[$value['gradeId']][$value['class']]){
                        $array[$value['grade']][$value['className']][] = $value;
                    }
                }
                $return = array();
                $j = 0;
                foreach($array as $key => $value){


                    $return[$j]['name'] = $this->gradeToZhong($key);
                    $i = 0;
                    foreach($value as $key1 => $value1){
                        $return[$j]['data'][$i]['name'] = $key1;
                        foreach($value1 as $key2 => $value2){
                            $value2['grade'] = $this->gradeToZhong($value2['grade']);
                            $value2['className'] = $value2['className'].'班';
                            $return[$j]['data'][$i]['data'][] = $value2;
                        }
                        $i++;
                    }
                    $j++;

                }
                $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
            }
            else if($userRoleId == $this::$jZroleId){
                $user = M('user')->field('name,id,childId,childName,className,grade,class,gradeId')->where(array('id' => $userId,'scId' => $scId))->find();
                $re = array();
                $re[0]['name'] = $this->gradeToZhong($user['grade']);
                $re[0]['data'][0]['name'] = $user['className'];
                $re[0]['data'][0]['data'][0] = array(
                    'name' => $user['childName'],
                    'id' => $user['childId'],
                    'class' => $user['class'],
                    'grade' => $user['grade'],
                    'gradeId' => $user['gradeId'],
                    'className' => $user['className'],
                );
                $this->returnJson(array('statu' =>1,'message' =>'success','data' => $re),true);
            }
            else{
                $data = M('user')->field('id,name,class,grade,className,gradeId')->where(array('scId' => $scId,'roleId' => $this::$studentRoleId))->order('grade,ClassName')->select();
                $array = array();
                foreach($data as $key => $value){
                    $array[$value['grade']][$value['className']][] = $value;
                }
                $return = array();
                $j = 0;
                foreach($array as $key => $value){
                    $return[$j]['name'] = $this->gradeToZhong($key);
                    $i = 0;
                    foreach($value as $key1 => $value1){
                        $return[$j]['data'][$i]['name'] = $key1;
                        foreach($value1 as $key2 => $value2){
                            $return[$j]['data'][$i]['data'][] = $value2;
                        }
                        $i++;
                    }
                    $j++;
                }
                $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
            }
        }
        if($type == 'valueStudent'){
            $valuePost = $_GET['value'];
            if($userRoleId == $this::$teacherRoleId){
                $class = M('jw_schedule')->field('classId')->where(array('techerId' => $userId,'scId' => $scId))->group('classId')->order('gradeName,className')->select();
                $count = count($class);
                $classStr = '';
                $i = 1;
                foreach($class as $key => $value){
                    if($count == $i){
                        $classStr = $classStr.$value['classId'];
                    }else{
                        $classStr = $value['classId'].','.$classStr;
                    }
                    $i++;
                }
                $strole = $this::$studentRoleId;
                $user = M('')->query("select name,id from mks_user where scId=$scId and roleId=$strole and  name LIKE '%$valuePost%' and class IN ($classStr)");
                $this->returnJson(array('statu' =>1,'message' =>'success','data' => $user),true);
            }
            if($userRoleId == $this::$jZroleId){
                $user = M('user')->field('name,id,childId,childName')->where(array('id' => $userId,'scId' => $scId))->find();
                $userRe[0] = array(
                    'name' => $user['childName'],
                    'id' => $user['childId']
                );
                $this->returnJson(array('statu' =>1,'message' =>'success','data' => $userRe),true);
            }
        }
    }
    public function replaceRecord(){//代请假记录
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getLeaveList'){
            $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $userId))->find();
            $startTime = '2010-09-13 00:00:00';
            $endTime =  '2088-09-16 00:00:00';
            if($_GET['startTime']){
                $startTime = $_GET['startTime'];
            }
            if($_GET['endTime']){
                $endTime = $_GET['endTime'];
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $data = M('student_leave_list')->where(array('replaceUserId' => $userId,'scId' => $scId,'createTime' => array(array('gt',$startTime),array('lt',$endTime))))->select();
            foreach($data as $key => $value){
                $appName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['approverId']))->find();
                $data[$key]['appName'] = $appName['name'];
                $data[$key]['userName'] = $userName['name'];
            }
            $this->returnJson(array('stata' => 1,'message' => 'success','data' => $data),true);
        }
        if($type == 'export'){
            $startTime = '2010-09-13 00:00:00';
            $endTime =  '2088-09-16 00:00:00';
            if($_GET['startTime']){
                $startTime = $_GET['startTime'];
            }
            if($_GET['endTime']){
                $endTime = $_GET['endTime'];
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $data = M('student_leave_list')->field('leaveId,leaveTypeId,title,times,state,createTime,userId')->where(array('replaceUserId' => $userId,'scId' => $scId,'createTime' => array(array('gt',$startTime),array('lt',$endTime))))->select();
            foreach($data as $key => $value){
                $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['userId']))->find();
                $data[$key]['userName'] = $userName['name'];
                $data[$key]['type'] = $this->toLeave($data[$key]['leaveTypeId']);
                $data[$key]['state'] = $this->state($data[$key]['state']);
            }
            $tr = array(
                '0' => array(
                    'en' => 'title',
                    'zh' => '标题'
                ),
                '1' => array(
                    'en' => 'type',
                    'zh' => '类型'
                ),  '2' => array(
                    'en' => 'times',
                    'zh' => '请假天数'
                ), '3' => array(
                    'en' => 'userName',
                    'zh' => '申请人'
                ), '4' => array(
                    'en' => 'state',
                    'zh' => '审批状态'
                ), '5' => array(
                    'en' => 'createTime',
                    'zh' => '申请时间'
                )
            );
            $this->export($data,$tr);
        }
    }
    private function state($id){
        $array = array(
            0 => '未审批',
            1 => '已审批'
        );
        return $array[$id];
    }
    public function leaveApproval(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getGrade'){
            if($this::$teacherRoleId == $userRoleId){
                if($grade[0] = M('grade')->where(array('scId' => $scId,'userId' => $userId))->find()){
                    //$class = M('class')->where(array('scId' => $scId,'grade' => $grade[0]['gradeid']))->order('classname asc')->select();
                    $this->returnJson(array('message' => 'success','statu' => 1,'data' => $grade),true);
                }
                if($class[] = M('class')->where(array('scId' => $scId,'userid' => $userId))->find()){
                    $grade[0] = M('grade')->where(array('scId' => $scId,'gradeid' => $class[0]['grade']))->find();
                    $this->returnJson(array('message' => 'success','statu' => 1,'data' => $grade),true);
                }
            }else{
                $grade = M('grade')->where(array('scId' => $scId))->order('name')->select();
                $this->returnJson(array('message' => 'success','statu' => 1,'data' => $grade),true);
            }
        }
        if($type == 'getClass'){
            $gradeid = $_GET['gradeid'];
            if($this::$teacherRoleId == $userRoleId){
                if($grade[] = M('grade')->where(array('scId' => $scId,'userId' => $userId))->find()){
                    $class = M('class')->where(array('scId' => $scId,'grade' => $grade[0]['gradeid']))->order('classname asc')->select();
                    foreach($class as $key => $value){
                        $class[$key]['classname']= $value['classname'].'班';
                    }
                    $this->returnJson(array('message' => 'success','statu' => 1,'data' => $class),true);
                }
                if($class[] = M('class')->where(array('scId' => $scId,'userid' => $userId))->find()){
                    //$grade[] = M('grade')->where(array('scId' => $scId,'gradeid' => $class[0]['grade']))->find();
                    foreach($class as $key => $value){
                        $class[$key]['classname'] = $value['classname'].'班';
                    }
                    $this->returnJson(array('message' => 'success','statu' => 1,'data' => $class),true);
                }
            }else{
                $class = M('class')->where(array('scId' => $scId,'grade' => $gradeid))->order('classname')->select();
                foreach($class as $key => $value){
                    $class[$key]['classname'] = $value['classname'].'班';
                }
                $this->returnJson(array('message' => 'success','statu' => 1,'data' => $class),true);
            }
        }
        if($type == 'approvalList'){
            $startTime = '2010-09-13 00:00:00';
            $endTime =  '2088-09-16 00:00:00';
            if($_GET['startTime']){
                $startTime = I('get.startTime');
            }
            if($_GET['endTime']){
                $endTime = I('get.endTime');
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $classId = I('get.classid');
            $gradeId = I('get.gradeid');
            $state = I('get.state');
            if($state ==0){
                $data = M('')->query("select * from mks_student_leave_list WHERE gradeId=$gradeId AND state=0 AND scId = $scId AND createTime> '$startTime' AND createTime< '$endTime' AND classId IN($classId) ");
            }else{
                $data = M('')->query("select * from mks_student_leave_list WHERE gradeId=$gradeId AND state!=0 AND scId = $scId AND createTime> '$startTime' AND createTime< '$endTime' AND classId IN($classId) ");
            }
            foreach($data as $key => $value){
                $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['userId']))->find();
                $data[$key]['userName'] = $userName['name'];
                $appName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['approverId']))->find();
                $data[$key]['appName'] = $appName['name'];
                $data[$key]['type'] = $this->toLeave($data[$key]['leaveTypeId']);
            }
            $this->returnJson(array('stata' => 1,'message' => 'success','data' => $data),true);
        }
        if($type == 'approvalHandle'){
            $leaveId = I('post.leaveId');
            $advice = I('post.advice');
            $yesOrNo = I('post.yesOrNo');
            $state = 2;
            if($yesOrNo == 1){
                $state = 1;
            }
            if(M('student_leave_list')->where(array('leaveId' => $leaveId,'scId' => $scId))->setField(array('state' => $state,'advice' => $advice,'approverId' => $userId,'appTime' => date('Y-m-d H:i:s')))){
                $this->returnJson(array('stata' => 1,'message' => 'success'),true);
            }
            $this->returnJson(array('stata' => 0,'message' => 'fail'),true);
        }
        if($type == 'export'){
            $startTime = '2010-09-13 00:00:00';
            $endTime =  '2088-09-16 00:00:00';
            if($_GET['startTime']){
                $startTime = I('get.startTime');
            }
            if($_GET['endTime']){
                $endTime = I('get.endTime');
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $classId = I('get.classid');
            $gradeId = I('get.gradeid');
            $state = I('get.state');
            $data = M('')->query("select leaveId,title,leaveTypeId,times,state,createTime,userId,leaveState from mks_student_leave_list WHERE gradeId=$gradeId AND state=$state AND scId = $scId AND createTime> '$startTime' AND createTime< '$endTime' AND classId IN($classId) ");
            foreach($data as $key => $value){
                $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['userId']))->find();
                $data[$key]['userName'] = $userName['name'];
                $data[$key]['type'] = $this->toLeave($data[$key]['leaveTypeId']);
                $data[$key]['state'] = $this->state($data[$key]['state']);
            }
            $tr = array(
                '0' => array(
                    'en' => 'title',
                    'zh' => '标题'
                ),
                '1' => array(
                    'en' => 'type',
                    'zh' => '类型'
                ),  '2' => array(
                    'en' => 'times',
                    'zh' => '请假天数'
                ), '3' => array(
                    'en' => 'userName',
                    'zh' => '申请人'
                ), '4' => array(
                    'en' => 'state',
                    'zh' => '审批状态'
                ), '5' => array(
                    'en' => 'createTime',
                    'zh' => '申请时间'
                )
            );
            $this->export($data,$tr);
        }
    }
    public function leaveSchool(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'approvalList'){
            $startTime = '2010-09-13 00:00:00';
            $endTime =  '2088-09-16 00:00:00';
            if($_GET['startTime']){
                $startTime = I('get.startTime');
            }
            if($_GET['endTime']){
                $endTime = I('get.endTime');
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $classId = I('get.classid');
            $gradeId = I('get.gradeid');
            $data = M('')->query("select * from mks_student_leave_list WHERE gradeId=$gradeId AND scId = $scId AND createTime> '$startTime' AND createTime< '$endTime' AND classId IN($classId) ");
            foreach($data as $key => $value){
                $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['userId']))->find();
                $data[$key]['userName'] = $userName['name'];
                $appName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['approverId']))->find();
                $data[$key]['appName'] = $appName['name'];
                $data[$key]['type'] = $this->toLeave($data[$key]['leaveTypeId']);
            }
            $this->returnJson(array('stata' => 1,'message' => 'success','data' => $data),true);
        }
        if($type == 'leaveSchoolHandle'){
            $leaveId = I('post.leaveId');
            if(M('student_leave_list')->where(array('leaveId' => $leaveId,'scId' => $scId))->setField(array('leaveState' => 1,'lxTime' => date('Y-m-d H:i:s'),'leaveUserId' => $userId))){
                $this->returnJson(array('stata' => 1,'message' => 'success'),true);
            }
            $this->returnJson(array('stata' => 0,'message' => 'fail'),true);
        }
        if($type == 'export'){
            $startTime = '2010-09-13 00:00:00';
            $endTime =  '2088-09-16 00:00:00';
            if($_GET['startTime']){
                $startTime = I('get.startTime');
            }
            if($_GET['endTime']){
                $endTime = I('get.endTime');
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $classId = I('get.classid');
            $gradeId = I('get.gradeid');
            $data = M('')->query("select * from mks_student_leave_list WHERE gradeId=$gradeId AND scId = $scId AND createTime> '$startTime' AND createTime< '$endTime' AND classId IN($classId) ");
            foreach($data as $key => $value){
                $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['userId']))->find();
                $data[$key]['userName'] = $userName['name'];
                $appName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['approverId']))->find();
                $data[$key]['appName'] = $appName['name'];
                $data[$key]['type'] = $this->toLeave($data[$key]['leaveTypeId']);
                $data[$key]['cz'] = $this->tocz($value['leaveState']);
            }
            $tr = array(
                '0' => array(
                    'en' => 'title',
                    'zh' => '标题'
                ),
                '1' => array(
                    'en' => 'type',
                    'zh' => '类型'
                ),  '2' => array(
                    'en' => 'times',
                    'zh' => '请假天数'
                ), '3' => array(
                    'en' => 'userName',
                    'zh' => '申请人'
                ), '4' => array(
                    'en' => 'state',
                    'zh' => '审批状态'
                ), '5' => array(
                    'en' => 'createTime',
                    'zh' => '创建时间'
                ), '6' => array(
                    'en' => 'cz',
                    'zh' => '操作'
                )

            );
            $this->export($data,$tr);
        }
    }
    private function tocz($dd){
        if($dd){
            return '已离校';
        }
        return '未离校';
    }
    public function leaveSelect(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'approvalList'){
            $startTime = '2010-09-13 00:00:00';
            $endTime = date('Y-m-d H:i:s');
            if($_GET['startTime']){
                $startTime = I('get.startTime');
            }
            if($_GET['endTime']){
                $endTime = I('get.endTime');
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $classId = I('get.classid');
            $gradeId = I('get.gradeid');
            $data = M('')->query("select * from mks_student_leave_list WHERE gradeId=$gradeId AND scId = $scId AND createTime> '$startTime' AND createTime< '$endTime' AND classId IN($classId) ");
            foreach($data as $key => $value){
                $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['userId']))->find();
                $data[$key]['type'] = $this->toLeave($data[$key]['leaveTypeId']);
                $data[$key]['userName'] = $userName['name'];
                $appName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['approverId']))->find();
                $data[$key]['appName'] = $appName['name'];
            }
            $this->returnJson(array('stata' => 1,'message' => 'success','data' => $data),true);
        }
        if($type == 'export'){
            $startTime = '2010-09-13 00:00:00';
            $endTime =  '2088-09-16 00:00:00';
            if($_GET['startTime']){
                $startTime = I('get.startTime');
            }
            if($_GET['endTime']){
                $endTime =  I('get.endTime');
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $classId = I('get.classid');
            $gradeId =  I('get.gradeid');
            $data = M('')->query("select leaveId,leaveTypeId,title,times,state,createTime,userId,leaveState from mks_student_leave_list WHERE gradeId=$gradeId AND scId = $scId AND createTime> '$startTime' AND createTime< '$endTime' AND classId IN($classId) ");
            foreach($data as $key => $value){
                $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $value['userId']))->find();
                $data[$key]['userName'] = $userName['name'];
                $data[$key]['type'] = $this->toLeave($data[$key]['leaveTypeId']);
                $data[$key]['state'] = $this->state($data[$key]['state']);
                $data[$key]['leaveState'] = $this->leaveState($data[$key]['leaveState']);
            }
            $tr = array(
                '0' => array(
                    'en' => 'title',
                    'zh' => '标题'
                ),
                '1' => array(
                    'en' => 'type',
                    'zh' => '类型'
                ),  '2' => array(
                    'en' => 'times',
                    'zh' => '请假天数'
                ), '3' => array(
                    'en' => 'userName',
                    'zh' => '申请人'
                ), '4' => array(
                    'en' => 'state',
                    'zh' => '审批状态'
                ), '5' => array(
                    'en' => 'createTime',
                    'zh' => '申请时间'
                ), '6' => array(
                    'en' => 'leaveState',
                    'zh' => '离校状态'
                )
            );
            $this->export($data,$tr);
        }
    }
    private function leaveState($id){
        $array = array(
            0 => '未离校',
            1 => '已离校'
        );
        return $array[$id];
    }
    private function toLeave($id){
        $array = array(
            1 => '事假',
            2 => '病假',
            3 => '其他',
        );
        return $array[$id];
    }
    public function leaveStatistics(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'approvalList') {
            $startTime = '2010-09-13 00:00:00';
            $endTime =  '2088-09-16 00:00:00';
            if ($_GET['startTime']) {
                $startTime = I('get.startTime');
            }
            if ($_GET['endTime']) {
                $endTime = I('get.endTime');
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $classId = I('get.classid');
            $gradeId = I('get.gradeid');
            $data = M('')->query("select * from mks_student_leave_list WHERE state=1 AND gradeId=$gradeId AND scId = $scId AND createTime> '$startTime' AND createTime< '$endTime' AND classId IN($classId)");
            $dataRe = array();
            $classAll = 0;
            $timeAll = 0;
            foreach($data as $key => $value){
                $dataRe[$value['className']]['allCount']++;
                $dataRe[$value['className']]['classId'] = $value['classId'];
                $dataRe[$value['className']]['gradeName'] = $this->gradeToZhong($value['gradeName']);
                $dataRe[$value['className']]['allTimes'] = $dataRe[$value['className']]['allTimes']+$value['times'];
                $dataRe[$value['className']][$value['leaveTypeId']]++;
            }
            foreach($dataRe as $key => $value){
                $dataRe[$key]['className'] = $key;
                $dataRe[$key]['gradeId'] = $gradeId;
                if(!isset($value[1])){
                    $dataRe[$key][1] = 0;
                }
                if(!isset($value[2])){
                    $dataRe[$key][2] = 0;
                }
                if(!isset($value[3])){
                    $dataRe[$key][3] = 0;
                }
            }
            $return = array();
            foreach($dataRe as $key => $value){
                $return[] = $value;
            }
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $return),true);
        }
        if($type == 'export') {
            $startTime = '2010-09-13 00:00:00';
            $endTime =  '2088-09-16 00:00:00';
            if ($_GET['startTime']) {
                $startTime = I('get.startTime');
            }
            if ($_GET['endTime']) {
                $endTime = I('get.endTime');
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $classId = I('get.classid');
            $gradeId = I('get.gradeid');
            $data = M('')->query("select * from mks_student_leave_list WHERE gradeId=$gradeId AND scId = $scId AND createTime> '$startTime' AND createTime< '$endTime' AND classId IN($classId)");
            $dataRe = array();
            $classAll = 0;
            $timeAll = 0;
            foreach($data as $key => $value){
                $dataRe[$value['className']]['allCount']++;
                $dataRe[$value['className']]['classId'] = $value['classId'];
                $dataRe[$value['className']]['gradeName'] = $this->gradeToZhong($value['gradeName']);
                $dataRe[$value['className']]['allTimes'] = $dataRe[$value['className']]['allTimes']+$value['times'];
                $dataRe[$value['className']][$value['leaveTypeId']]++;
            }
            foreach($dataRe as $key => $value){
                $dataRe[$key]['className'] = $key;
                $dataRe[$key]['gradeId'] = $gradeId;
                if(!isset($value[1])){
                    $dataRe[$key][1] = 0;
                }
                if(!isset($value[2])){
                    $dataRe[$key][2] = 0;
                }
                if(!isset($value[3])){
                    $dataRe[$key][3] = 0;
                }
            }
            $return = array();
            foreach($dataRe as $key => $value){
                $return[] = $value;
            }
            $tr = array(
                '0' => array(
                    'en' => 'className',
                    'zh' => '班级'
                ),
                '1' => array(
                    'en' => 'allCount',
                    'zh' => '总计次数'
                ),  '2' => array(
                    'en' => 'allTimes',
                    'zh' => '总计天数'
                ), '3' => array(
                    'en' => '1',
                    'zh' => '事假'
                ), '4' => array(
                    'en' => '2',
                    'zh' => '病假'
                ), '5' => array(
                    'en' => '3',
                    'zh' => '其他'
                )
            );
            $this->export($return,$tr);
        }
        if($type == 'getXq'){
            $classId = I('get.classId');
            $gradeId = I('get.gradeId');
            $startTime = '2010-09-13 00:00:00';
            $endTime =  date('Y-m-d H:i:s');
            if ($_GET['startTime']) {
                $startTime =I('get.startTime');
            }
            if($_GET['endTime']) {
                $endTime = I('get.endTime');
            }
            if($_GET['startTime'] && $_GET['endTime']){
                if($_GET['startTime'] == $_GET['endTime']){
                    $endTime = $_GET['endTime'].' 23:59:59';
                }
            }
            $data = M('')->query("select leaveTypeId,userId,times,startTime,endTime from mks_student_leave_list WHERE state=1 AND gradeId=$gradeId AND scId = $scId AND createTime> '$startTime' AND createTime< '$endTime' AND classId IN($classId)");
            $return = array();
            foreach($data as $key => $value){
                $return[$value['userId']]['data'][$value['leaveTypeId']]['times'] = strtotime($value['endTime'])-strtotime($value['startTime'])+$return[$value['userId']]['data'][$value['leaveTypeId']]['times'];
                $return[$value['userId']]['data'][$value['leaveTypeId']]['num']++;
                $return[$value['userId']]['count']++;
            }
            $returnArr = array();
            $i = 0;
            foreach($return as $key => $value){
                $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $key))->find();
                $returnArr[$i]['name']  = $userName['name'];
                $returnArr[$i]['all'] = $value['count'];
                $returnArr[$i]['sj'] = $value['data'][1]['num'];
                $returnArr[$i]['sjTime'] = round($value['data'][1]['times']/86400,1);
                $returnArr[$i]['bj'] = $value['data'][2]['num'];
                $returnArr[$i]['bjTime'] = round($value['data'][2]['times']/86400,1);
                $returnArr[$i]['qt'] = $value['data'][3]['num'];
                $returnArr[$i]['qtTime'] = round($value['data'][3]['times']/86400,1);
                if(!$returnArr[$i]['sj']){
                    $returnArr[$i]['sj'] = 0;
                }
                if(!$returnArr[$i]['bj']){
                    $returnArr[$i]['bj'] = 0;
                }
                if(!$returnArr[$i]['qt']){
                    $returnArr[$i]['qt'] = 0;
                }
                $i++;
            }
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $returnArr),true);
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
}