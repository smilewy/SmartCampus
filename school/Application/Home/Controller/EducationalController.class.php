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
class EducationalController extends Base{
    /**公用的不设置权限*/
    public function getSubjectList(){//得到课程列表//公用的怎么写
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck',false);
        $scId = $scId['scId'];
        if($type == 'getSubjectList'){ //得到课程列表
            $this->returnJson(array('data' =>M('subject')->where(array('scId' => $scId))->select(),'staut' =>1 ,'message' => 'success'),true);
        }
        if($type == 'getGradeList'){ //得到年级列表
            $this->returnJson(array('data' =>M('grade')->where(array('scId' => $scId))->order('name')->select(),'statu' =>1 ,'message' =>'success'),true);
        }
        if($type == 'getClassList'){ //得到班级列表
            $this->returnJson(array('data' =>M('class')->where(array('scId' =>$scId,'grade' => $_GET['grade']))->select(),'message' => 'success','statu' =>1),true);
        }
        if($type == 'getTeacherList'){
            $field = $_GET['sortType'];
            $sort = $_GET['sort'];
            $studentRole = $this::$studentRoleId;
            $jzRole = $this::$jZroleId;
            $adminRole = $this::$adminRoleId;
            $data = M('')->query("select id,teachingSubjects,name,jobNumber,phone,post from mks_user where scId= $scId AND roleId!=$studentRole AND roleId!=$jzRole AND roleId!=$adminRole");
            $sort = $_GET['sort'];
            $returnList = array();
            $data1 = $data;
            foreach($data as $key => $value){
                $max = array();
                $kk = 0;
                foreach($data1 as $key1 => $value1){
                    if($value1[$field]>= $max[$field]){
                        $max = $value1;
                        $kk = $key1;
                    }
                }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if($sort == 'ascending'){
                krsort($returnList);
            }
            $data = $returnList;
            if($_GET['valueData']){
                $valueData = $_GET['valueData'];
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
                            unset($value['state']);
                            $returnList[] = $value;
                        }
                    }
                    $data = $returnList;
                }
            }
            $data = array_values($data);
            $this->returnJson(array('statu' => 1,'message' => 'sucess','data' => $data),true);
        }
    }
    public function teacherSubject(){//任课教师
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck',false);
        $userId = $scId['userId'];
        $scId = $scId['scId'];
        if($type == 'createTeacherSubject'){
            $data = I('post.data');
            $gradeId = I('post.gradeId');
            $gradeName = M('grade')->where(array('scId' => $scId,'gradeid' => $gradeId))->find();
            $gradeName = $gradeName['name'];
            $subjectId = I('post.subjectId');
            $subjectName = M('subject')->where(array('scId' => $scId,'subjectid' => $subjectId))->find();
            $subjectName = $subjectName['subjectname'];
            $model = M('jw_schedule');
            //$data['userId'] = $session['userId'];
            $model->startTrans();
            $model->where(array('gradeId' => $gradeId,'subjectId' => $subjectId,'scId' => $scId))->delete();
            foreach($data as $key => $value){
                $value['subject'] = $subjectName;
                $value['subjectId'] = $subjectId;
                $value['gradeName'] = $gradeName;
                $value['userId'] = $userId;
                $value['createTime'] = strtotime(date('Y-m-d H:i:s'));
                $value['scId'] = $scId;
                $value['gradeId'] = $gradeId;
                if(!$model->add($value)){
                    $this->returnJson(array('statu' => 1, 'message' => 'add fail'),true);
                }
            }
            $model->commit();
            $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
        }
        if($type == 'clearsTask'){
            $gradeId = I('post.gradeId');
            $subjectId = I('post.subjectId');
            if(M('jw_schedule')->where(array('scId' => $scId,'gradeId' => $gradeId,'subjectId' => $subjectId))->delete() === false){
                $this->returnJson(array('statu' => 0, 'message' => '删除失败'),true);
            }
            $this->returnJson(array('statu' => 1, 'message' => '删除成功'),true);
        }
        if($type == 'teacherSubjectCreateAll'){
            $data = I('post.data');
            foreach($data as $key => $value){
                foreach($value['data'] as $key1 => $value1){
                    if($value1['id']){
                        M('jw_schedule')->add($value1);
                    }else{
                        unset($value1['id']);
                        unset($value1['scId']);
                        M('jw_schedule')->where(array('scId' => $scId,'id' => $value1['id']))->setField($value1);
                    }
                }
            }
            $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
        }
        if($type == 'teacherList'){
            $subject = M('subject')->where(array('scId' => $scId))->order('subjectid')->select();
            $gradeId = I('get.gradeId');
            $data  = M('')->query("SELECT * FROM mks_jw_schedule WHERE scId=$scId and gradeId=$gradeId  order by gradeName");
            $class = M('class')->where(array('grade' => $gradeId,'scId' => $scId))->select();
            $teacher = M('user')->field('name,id')->where(array('scId' => $scId,'roleId' => $this::$teacherRoleId))->select();
            $classHeader = array();
            $teacherName = array();
            foreach($teacher as $key => $value){
                $teacherName[$value['id']] = $value;
            }
            foreach($class as $key => $value){
                $classHeader[$value['classid']] = $value;
                $classHeader[$value['classid']]['teacherName'] = $teacherName[$value['userid']]['name'];
            }
            $return  = array();
            foreach($data as $key => $value){
                $return[$value['gradeName']][$value['className']][] = $value;
            }
            $array = array();
            foreach($return as $key => $value){
                foreach($value as $key1 => $value){
                    $array[] = $value;
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
                        $array1[$i]['data'][] = array('id' => '','className' => $value1['className'],'subject' => $value2['subjectname'],'techerId' => '','techerName' => '','subjectId' => $value2['subjectid'],'createTime' =>'',
                            'userId' => '','gradeId' =>$value1['gradeId'],'classId' => $value1['classId'],'scId' => $scId,'gradeName' =>$value1['gradeName']
                        );
                    }
                }
                $array1[$i]['className'] = $value1['className'];
                $array1[$i]['gradeId'] = $value1['gradeId'];
                $array1[$i]['classId'] = $value1['classId'];
                $array1[$i]['gradeName'] = $value1['gradeName'];
                $array1[$i]['techerName'] = $classHeader[$value1['classId']]['teacherName'];
                $i++;
            }
            $arrayData = array();
            $ii = 0;
            foreach($array1 as $key => $value){
                $arrayData[$ii]['className'] = $value['className'];
                $arrayData[$ii]['techerName'] = $value['techerName'];
                foreach($value['data'] as $key1 => $value1){
                    $arrayData[$ii][$value1['subject']] = $value1['techerName'];
                }
                $ii++;
            }
            $subjectList = array();
            $i = 0;
            foreach($subject as $key => $value){
                $subjectList[$i]['subjectname'] = $value['subjectname'];
                $i++;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $arrayData,'suject' => $subjectList),true);
        }
        if($type == 'export'){
            $subject = M('subject')->where(array('scId' => $scId))->order('subjectid')->select();
            $gradeId = I('get.gradeId');
            $data  = M('')->query("SELECT * FROM mks_jw_schedule WHERE scId=$scId and gradeId=$gradeId  order by gradeName");
            $class = M('class')->where(array('grade' => $gradeId,'scId' => $scId))->select();
            $teacher = M('user')->field('name,id')->where(array('scId' => $scId,'roleId' => $this::$teacherRoleId))->select();
            $classHeader = array();
            $teacherName = array();
            foreach($teacher as $key => $value){
                $teacherName[$value['id']] = $value;
            }
            foreach($class as $key => $value){
                $classHeader[$value['classid']] = $value;
                $classHeader[$value['classid']]['teacherName'] = $teacherName[$value['userid']]['name'];
            }
            $return  = array();
            foreach($data as $key => $value){
                $return[$value['gradeName']][$value['className']][] = $value;
            }
            $array = array();
            foreach($return as $key => $value){
                foreach($value as $key1 => $value){
                    $array[] = $value;
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
                        $array1[$i]['data'][] = array('id' => '','className' => $value1['className'],'subject' => $value2['subjectname'],'techerId' => '','techerName' => '','subjectId' => $value2['subjectid'],'createTime' =>'',
                            'userId' => '','gradeId' =>$value1['gradeId'],'classId' => $value1['classId'],'scId' => $scId,'gradeName' =>$value1['gradeName']
                        );
                    }
                }
                $array1[$i]['className'] = $value1['className'];
                $array1[$i]['gradeId'] = $value1['gradeId'];
                $array1[$i]['classId'] = $value1['classId'];
                $array1[$i]['gradeName'] = $value1['gradeName'];
                $array1[$i]['techerName'] = $classHeader[$value1['classId']]['teacherName'];
                $i++;
            }
            $arrayData = array();
            $ii = 0;
            foreach($array1 as $key => $value){
                $arrayData[$ii]['名称'] = $value['className'];
                $arrayData[$ii]['班主任'] = $value['techerName'];
                foreach($value['data'] as $key1 => $value1){
                    $arrayData[$ii][$value1['subject']] = $value1['techerName'];
                }
                $ii++;
            }
            $subjectList = array();
            $subjectList[0]  =array(
                'subjectname' =>  '名称'
            );
            $subjectList[1]  =array(
                'subjectname' =>  '班主任'
            );
            $i = 2;
            foreach($subject as $key => $value){
                $subjectList[$i]['subjectname'] = $value['subjectname'];
                $i++;
            }
            $tr = array();
            foreach($subjectList  as $key => $value){
                $tr[] = array(
                    'en' => $value['subjectname'],
                    'zh' => $value['subjectname']
                );
            }
            $this->export($arrayData,$tr);
        }
        if($type == 'getTeacherSubject'){//得到
            $gradeId = $_GET['gradeId'];
            $class = M('class')->where(array('scId' => $scId,'grade' => $gradeId))->order('classname')->select();
            $subjectId = $_GET['subjectId'];
            $data = M('jw_schedule')->where(array('gradeId' => $gradeId,'subjectId' => $subjectId,'scId' => $scId))->order('className')->select();
            $array = array();
            foreach($class as $key => $value){
                $true = 0;
                foreach($data as $key1 => $value1){
                    if($value['classid'] == $value1['classId']){
                        //       $class
                        $array[] = array('classId' => $value1['classId'],'className' => $value1['className'],'techerId' => $value1['techerId'],'techerName' => $value1['techerName']);
                        $true = 1;
                    }
                }
                if(!$true){
                    $array[] = array('classId' => $value['classid'],'className' => $value['classname'],'techerId' => '','techerName' => '');
                }
            }
            $this->returnJson(array('statu' => 1,'data' => $array ,'message' => 'success'),true);
        }
        if($type == 'getClass'){
            $gradeId = I('get.gradeid');
            $class = M('class')->field('classid,classname,grade')->where(array('scId' => $scId,'grade' => $gradeId))->order('classname')->select();
            foreach($class as $key => $value){
                $class[$key]['classname'] = $value['classname'].'班';
            }
            $this->returnJson(array('statu' => 1,'data' => $class ,'message' => 'success'),true);
        }
        if($type == 'getClassTeacherSubject'){
            $gradeId = I('get.gradeid');
            $classId = I('get.classid');
            $subject = M('subject')->field('subjectid,subjectname')->where(array('scId' => $scId))->order('subjectid')->select();
            //print_r($subject);
            $jx = M('jw_schedule')->field('techerId,techerName,subjectId')->where(array('scId' => $scId,'gradeId' => $gradeId,'classId' => $classId))->select();
            $dataRe = array();
            foreach($subject as $key => $value){
                $dataRe[$value['subjectid']] = $value;
            }
            foreach($jx as $key => $value){
                $dataRe[$value['subjectId']]['techerId'] = $value['techerId'];
                $dataRe[$value['subjectId']]['techerName'] = $value['techerName'];
            }
            $returnTrue = array();
            foreach($dataRe as $key => $value){
                if(isset($value['techerId'])){
                    $returnTrue[] = $value;
                }else{
                    $value['techerId'] = '';
                    $value['techerName'] = '';
                    $returnTrue[] = $value;
                }
            }
            $this->returnJson(array('status' => 1,'data' => $returnTrue,'message' => 'success'),true);
        }
        if($type == 'getClassTeacherSubjectCreate'){
            $gradeId = I('post.gradeid');
            $classId = I('post.classid');
            $className = M('class')->field('classname')->where(array('scId' => $scId,'classid' => $classId))->find();
            $className = $className['classname'];
            $gradeName = M('grade')->field('name')->where(array('scId' => $scId,'gradeid' => $gradeId))->find();
            $gradeName = $gradeName['name'];
            $data = I('post.data');
            M('')->startTrans();
            $DD = M('jw_schedule')->where(array('scId' => $scId,'gradeId' => $gradeId,'classId' => $classId))->delete();
            $dataTrue = array();
            $time = strtotime(date('Y-m-d H:i:s'));
            foreach($data as $key => $value){
                $dataTrue[] = array(
                    'className' => $className,
                    'subject' => $value['subjectname'],
                    'techerId' => $value['techerId'],
                    'techerName' => $value['techerName'],
                    'createTime' =>$time,
                    'gradeId' => $gradeId,
                    'subjectId' => $value['subjectid'],
                    'classId' => $classId,
                    'scId' => $scId,
                    'gradeName' => $gradeName
                );
            }
            $jj = M('jw_schedule')->addAll($dataTrue);
            if($DD===false || !$jj){
                M()->rollback();
                $this->returnJson(array('status' => 0,'message' => '失败'),true);
            }else{
                M()->commit();
                $this->returnJson(array('status' => 1,'message' => '成功'),true);
            }
        }
        if($type == 'clearsAll'){
            $gradeId = I('post.gradeid');
            $classId = I('post.classid');
            if($DD = M('jw_schedule')->where(array('scId' => $scId,'gradeId' => $gradeId,'classId' => $classId))->delete()){
                $this->returnJson(array('status' => 1,'message' => '删除成功'),true);
            }
            $this->returnJson(array('status' => 1,'message' => '删除失败'),true);
        }
    }
    public function getGradeName(){
    }
    //班级年级管理
    public function classAndgradeGl(){//班级年级管理
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck',false);
        $userId = $scId['userId'];
        $scId = $scId['scId'];
        if($type == 'createGrade'){
            $code = strtoupper(I('post.code'));
            if(M('grade')->where(array('scId' => $scId,'code' => $code))->find()){
                $this->returnJson(array('statu' => 2,'message' => '年级代码重复不能创建年级'),true);
            }
            $znName = I('post.znName');
            $highestgrade = I('post.highestgrade');
            $autoupdate = I('post.autoupdate');
            $xiao = explode('X',$code);
            $chu = explode('C',$code);
            $gao = explode('G',$code);
            $name = 0;
            $year = date('Y');
            $uploadData = null;
            $check = 0;
            if((int)date('m')>=8){
                if(count($xiao) == 2){
                    $name = $year-$xiao[1]+1;
                    $uploadData = $xiao[1];
                    $check = 'X';
                }
                if(count($chu) == 2){
                    $name = $year-$chu[1]+7;
                    $uploadData = $chu[1];
                    $check = 'C';
                }
                if(count($gao) == 2){
                    $name = $year-$gao[1]+10;
                    $uploadData = $gao[1];
                    $check = 'G';
                }
                if($uploadData> $year){
                    $this->returnJson(array('statu' => 2,'message' => '下半学期只能录取小于等于当前年的'),true);
                }
            }else{
                if(count($xiao) == 2){
                    $name = $year-$xiao[1];
                    $uploadData = $xiao[1];
                    $check = 'X';
                }
                if(count($chu) == 2){
                    $name = $year-$chu[1]+6;
                    $uploadData = $chu[1];
                    $check = 'C';
                }
                if(count($gao) == 2){
                    $name = $year-$gao[1]+9;
                    $uploadData = $gao[1];
                    $check = 'G';
                }
                if($uploadData>=$year){
                    $this->returnJson(array('statu' => 2,'message' => '上半学期只能录小于当前年的'),true);
                }
            }
            if($check == 'G'){
                if($name>12 || $name<10){
                    $this->returnJson(array('statu' => 2,'message' => '请输入正确的年级代码'),true);
                }
            }
            if($check == 'C'){
                if($name>9 || $name<7){
                    $this->returnJson(array('statu' => 2,'message' => '请输入正确的年级代码'),true);
                }
            }
            if($check == 'X'){
                if($name>6 || $name<1){
                    $this->returnJson(array('statu' => 2,'message' => '请输入正确的年级代码'),true);
                }
            }
            if(!$check){
                $this->returnJson(array('statu' => 2,'message' => '输入数据出错，请按照提示输入'),true);
            }
            if(M('grade')->where(array('scId' => $scId,'code' => $code))->find()){
                if(M('grade')->where(array('scId' => $scId,'code' => $code))->setField(
                    array(
                        'code' => $code,
                        'name' => $name,
                        'znName' => $this::gradeToZhong($name),
                        'autoupdate' => $autoupdate,
                        'highestgrade' => $highestgrade,
                    )
                )){
                    $this->returnJson(array('statu' => 1,'message' => 'success'),true);
                }
                $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
            }else{
                if(M('grade')->add(
                    array(
                        'code' => $code,
                        'name' => $name,
                        'znName' =>  $this::gradeToZhong($name),
                        'scId' => $scId,
                        'autoupdate' => $autoupdate,
                        'highestgrade' => $highestgrade,
                        'createTime' => strtotime(date('Y-m-d H:i:s'))
                    )
                )){
                    $this->returnJson(array('statu' => 1,'message' => 'success'),true);
                }
                $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
            }
        }
        if($type == 'getClassList'){
            $type11 = I('get.type11');
            if($type11 == 'getMajor'){
                $branchid = I('get.branchid');
                $major = M('')->query("select * from mks_class_major where (branch=$branchid and scId=$scId) or scId=0");
                $this->returnJson(array('statu' => 1, 'message' => 'success', 'data' => $major),true);
            }else{
                $gradeId = $_GET['gradeid'];
                $data  = M('class')->where(array('scId' => $scId,'grade' =>$gradeId))->order('classname')->select();
                $classLeavl = M('class_level')->where(array('scId' => $scId))->select();
                $branch = M('class_branch')->where(array('scId' => array(array('eq' , $scId),array('eq', 0),'or')))->select();
                foreach($data as $key => $value){
                    foreach($classLeavl as $key1 => $value1){
                        if($value1['levelid'] == $value['levelid']){
                            $data[$key]['levelname'] = $value1['levelname'];
                        }
                    }
                    foreach($branch as $key1 => $value1){
                        if($value1['branchid'] == $value['branch']){
                            $data[$key]['branchname'] = $value1['branch'];
                        }
                    }
                    if(!isset($data[$key]['majorname'])){
                        $data[$key]['majorname'] = '';
                    }
                    if(!isset($data[$key]['branchname'])){
                        $data[$key]['branchname'] = '';
                    }
                    if(!isset($data[$key]['levelname'])){
                        $data[$key]['levelname'] = '';
                    }
                }
                $this->returnJson(array('classLeavl' => $classLeavl,'data' => $data,'branch' => $branch,'statu' =>1),true);
            }
        }
        if($type == 'createOrUpdataClass'){
            $classname = I('post.classname');
            $number = I('post.number');
            $branch = I('post.branch');
            $levelid = I('post.levelid');
            $major = I('post.major');
            $classid = I('post.classid');
            $grade = I('post.grade');
            $classname = $classname+0;
            if($classname<=0 || !is_int($classname)){
                $this->returnJson(array('statu' => 3,'message' => '班级名称不合法，只能是正整数'),true);
            }
            if($classid){
                $studentCount = M('student_info')->where(array('scId' => $scId,'gradeId' => $grade,'classId' => $classid))->count('id');
                if($studentCount>$number){
                    $this->returnJson(array('statu' => 3,'message' => '班级人数应该大于等于该班级已有的学生数'),true);
                }
                if($iiii = M('class')->where(array('scId' => $scId,'grade' => $grade,'classname' => $classname))->find()){
                    $iiii['classid'];
                }
                if($iiii['classid'] == $classid || !$iiii['classid']){
                    if(M('class')->where(array('classid' =>$classid,'scId' => $scId))->setField(array(
                            'classname' => $classname,
                            'number' => $number,
                            'branch' => $branch,
                            'levelid' => $levelid,
                            'major' => $major,
                            'grade' => $grade,
                        )) ===false){
                        $this->returnJson(array('statu' => 0,'message' => '修改失败'),true);
                    }
                    $this->returnJson(array('statu' => 1,'message' => '修改成功'),true);
                }
                $this->returnJson(array('statu' => 0,'message' => '班级名已经存在，修改失败'),true);
            }else{
                if($iiii = M('class')->where(array('scId' => $scId,'grade' => $grade,'classname' => $classname))->find()){
                    $this->returnJson(array('statu' => 0,'message' => '班级名已经存在'),true);
                }
                if(M('class')->add(array(
                    'classname' => $classname,
                    'number' => $number,
                    'branch' => $branch,
                    'levelid' => $levelid,
                    'major' => $major,
                    'scId' => $scId,
                    'grade' => $grade
                ))){
                    $this->returnJson(array('statu' => 1,'message' => '新增成功'),true);
                }
                $this->returnJson(array('statu' => 0,'message' => '新增失败'),true);
            }
        }
        if($type == 'export'){
            $gradeId = $_GET['gradeid'];
            $data  = M('class')->where(array('scId' => $scId,'grade' =>$gradeId))->select();
            $classLeavl = M('class_level')->where(array('scId' => $scId))->select();
            $branch = M('class_branch')->where(array('scId' => $scId))->select();
            $major = M('class_major')->where(array('scId' => $scId))->select();
            foreach($data as $key => $value){
                foreach($classLeavl as $key1 => $value1){
                    if($value1['levelid'] == $value['levelid']){
                        $data[$key]['levelname'] = $value1['levelname'];
                    }
                }
                foreach($branch as $key1 => $value1){
                    if($value1['branchid'] == $value['branch']){
                        $data[$key]['branchname'] = $value1['branch'];
                    }
                }
                foreach($major as $key1 => $value1){
                    if($value1['majorid'] == $value['major']){
                        $data[$key]['majorname'] = $value1['majorname'];
                    }
                }
                if(!isset($data[$key]['majorname'])){
                    $data[$key]['majorname'] = '';
                }
                if(!isset($data[$key]['branchname'])){
                    $data[$key]['branchname'] = '';
                }
                if(!isset($data[$key]['levelname'])){
                    $data[$key]['levelname'] = '';
                }
            }
            $tr = array(
               0 => array(
                    'en' => 'classname',
                    'zh' => '班级名称'
                ),
                1 => array(
                    'en' => 'number',
                    'zh' => '班级人数'
                ),
                2 => array(
                    'en' => 'branchname',
                    'zh' => '科类'
                ),
                3 => array(
                    'en' => 'majorname',
                    'zh' => '班级专业'
                ),
                4 => array(
                    'en' => 'levelname',
                    'zh' => '班级级别'
                ),

            );
            $this->export($data,$tr);
        }
        if($type == 'deleteClass'){
            $classId = I('post.classid');
            $i = 0;
            $true = 0;
            foreach($classId as $key => $value){
                if($true = M('user')->where(array('scId' => $scId,'class' => $value,'roleId' => $this::$studentRoleId))->find()){
                    $true = 1;
                    $this->returnJson(array('statu' => 2,'message' => '班级下面有学生不能删除提示“该班级不能删除'),true);
                }
            }
            foreach($classId as $key => $value){
                if(M('class')->where(array('scId' => $scId,'classid' =>$value))->delete()){
                    $i = 1;
                }else{
                }
            }
            if($i){
                $this->returnJson(array('statu' => 1,'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
        }
    }
    public function classHeadAndGradeHead(){//班级班主任
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck',false);
        $scId = $scId['scId'];
        if($type == 'getList'){
            $gradeId = $_GET['gradeid'];
            $grade = M('grade')->where(array('scId' => $scId,'gradeid' => $gradeId))->find();
            if($grade['userId']){
                $user = M('user')->where(array('id' => $grade['userId'],'scId' => $scId))->find();
                $userName = $user['name'];
                $gradeRe = array('userName' => $userName,'name' => '年级主任','classid' =>'','userId' => $grade['userId']);
            }else{
                $gradeRe = array('userName' => '','name' => '年级主任','userId' => '');
            }
            $class = M('class')->where(array('scId' => $scId,'grade' => $gradeId))->select();
            $classRe = array();
            foreach($class as $key => $value){
                if($value['userid']){
                    $user = M('user')->where(array('id' => $value['userid'],'scId' => $scId))->find();
                    $userName = $user['name'];
                    $classRe[] = array('userName' => $userName,'name' => $value['classname'],'classid' => $value['classid'],'userId' => $value['userid']);
                }else{
                    $classRe[] = array('userName' => '','name' => $value['classname'],'classid' => $value['classid'],'userId' => '');
                }
            }
            $classRe[count($classRe)] = $gradeRe;
            $this->returnJson(array('statu' => 1,'messge' => 'success','data' =>$classRe),true);
        }
        if($type == 'create'){
            $data = I('post.data');
            $gradeId = I('post.gradeid');
            foreach($data as $key => $value){
                if($value['name'] == '年级主任'){
                    M('grade')->where(array('gradeid' => $gradeId,'scId' => $scId))->setField(array('userId' => $value['userId']));
                    M('user')->where(array('gradeId' => $gradeId,'gradeDirector' => 1,'scId' => $scId))->setField(array('gradeDirector' => 1));
                    M('user')->where(array('id' => $value['userId'],'scId' => $scId))->setField(array('gradeDirector' => 1));
                }else{
                    M('class')->where(array('classid' => $value['classid'],'scId' => $scId))->setField(array('userid' => $value['userId']));
                    M('user')->where(array('class' => $value['classid'],'scId' => $scId,'headMaster' => 1))->setField(array('headMaster' => 0));
                    M('user')->where(array('id' => $value['userId'],'scId' => $scId))->setField(array('headMaster' => 1));
                }
            }
            $this->returnJson(array('statu' => 1,'messge' => 'success'),true);
        }
        if($type == 'export'){
            $grade = M('grade')->where(array('scId' => $scId))->order('name')->select();
            $class = M('class')->where(array('scId' => $scId))->order('classname')->select();
            $array = array();
            $teacher = M('user')->field('name,id')->where(array('scId' => $scId,'roleId' => $this::$teacherRoleId))->select();
            $teacherTr = array();
            $gradeTr = array();
            foreach($teacher as $key => $value){
                $teacherTr[$value['id']] = $value;
            }
            foreach($grade as $key => $value){
                $value['name'] = $this->gradeToZhong($value['name']);
                $value['userid'] = $value['userId'];
                $value['teacherName'] = $teacherTr[$value['userId']]['name'];
                $value['gradeName'] = $value['name'];
                unset($value['name']);
                unset($value['userId']);
                $gradeTr[$value['gradeid']] = $value;
            }
            //print_r($teacherTr);
            foreach($class as $key1 => $value1){
                $value1['teacherName'] = $teacherTr[$value1['userid']]['name'];
                $gradeTr[$value1['grade']]['data'][] = $value1;
            }
            foreach($gradeTr as $key => $value){
                if(!$value['gradeid']){
                    unset($gradeTr[$key]);
                }
            }
            $gradeTr = array_values($gradeTr);
            $gradeGo = array();
            $ii = 0;
            $dataRe = array();
            $allClass = array();
            foreach($gradeTr as $key => $value){
                foreach($value['data'] as $key1 => $value1){
                    $allClass[] = $value1;
                }
                unset($value['data']);
                $gradeGo[] = $value;
            }
            $xun = ceil(count($allClass)/count($gradeTr));
            $dataReR = array();
            $gradeGoG = array();
            for($i = 0 ; $i< $xun; $i++){
                foreach($gradeGo as $key => $value){
                    foreach($allClass as $key1 => $value1){
                        if($value['gradeid'] == $value1['grade']){
                            $class = 'class'.$value1['grade'];
                            $te = 'teacherName'.$value1['grade'];
                            $dataReR[$i][$class] = $value1['classname'];
                            $dataReR[$i][$te] = $value1['teacherName'];
                            $dataReR[$i]['gradeId'] = $value1['grade'];
                            unset($allClass[$key1]);
                            break;
                        }
                    }
                }
            }
            $i = 0;
            $tr = array();
            foreach($gradeGo as $key => $value){
                $tr[$i] = array(
                    'en' => 'class'.$value['gradeid'],
                    'zh' => $value['gradeName']
                );
                $i++;
                $tr[$i] = array(
                    'en' => 'teacherName'.$value['gradeid'],
                    'zh' => $value['gradeName']
                );
                $i++;
            }
            $returnList = array();
            $i = 0;
            $year = array();
            foreach($gradeGo as $key => $value){
                $classclass = 'class'.$value['gradeid'];
                $year[$classclass] = '年级主任';
                $returnList[0][$classclass] = '名称';
                $teacher = 'teacherName'.$value['gradeid'];
                $returnList[0][$teacher] = '班主任';
                $year[$teacher] = $value['teacherName'];
            }
            $i++;
            foreach($dataReR as $key => $value){
                $returnList[$i] = $value;
                $i++;
            }
            $returnList[$i] = $year;
           $this->export($returnList,$tr);
        }
        if($type == 'classHeadAndGradeHeader'){//得到年级主任，班主任一览表
            $grade = M('grade')->where(array('scId' => $scId))->order('name')->select();
            $class = M('class')->where(array('scId' => $scId))->order('classname')->select();
            $array = array();
            $teacher = M('user')->field('name,id')->where(array('scId' => $scId,'roleId' => $this::$teacherRoleId))->select();
            $teacherTr = array();
            $gradeTr = array();
            foreach($teacher as $key => $value){
                $teacherTr[$value['id']] = $value;
            }
            foreach($grade as $key => $value){
                $value['name'] = $this->gradeToZhong($value['name']);
                $value['userid'] = $value['userId'];
                $value['teacherName'] = $teacherTr[$value['userId']]['name'];
                $value['gradeName'] = $value['name'];
                unset($value['name']);
                unset($value['userId']);
                $gradeTr[$value['gradeid']] = $value;
            }
            //print_r($teacherTr);
            foreach($class as $key1 => $value1){
                $value1['teacherName'] = $teacherTr[$value1['userid']]['name'];
                $gradeTr[$value1['grade']]['data'][] = $value1;
            }
            foreach($gradeTr as $key => $value){
                if(!$value['gradeid']){
                    unset($gradeTr[$key]);
                }
            }
            $gradeTr = array_values($gradeTr);
            $gradeGo = array();
            $ii = 0;
            $dataRe = array();
            $allClass = array();
            foreach($gradeTr as $key => $value){
                foreach($value['data'] as $key1 => $value1){
                    $allClass[] = $value1;
                }
                unset($value['data']);
                $gradeGo[] = $value;
            }
            $xun = ceil(count($allClass)/count($gradeTr));
            $dataReR = array();
            $gradeGoG = array();
            for($i = 0 ; $i< $xun; $i++){
                foreach($gradeGo as $key => $value){
                    foreach($allClass as $key1 => $value1){
                        if($value['gradeid'] == $value1['grade']){
                            $class = 'class'.$value1['grade'];
                            $te = 'teacherName'.$value1['grade'];
                            $dataReR[$i][$class] = $value1['classname'];
                            $dataReR[$i][$te] = $value1['teacherName'];
                            $dataReR[$i]['gradeId'] = $value1['grade'];
                            unset($allClass[$key1]);
                            break;
                        }
                    }
                }
            }
            foreach($gradeGo as $key => $value){
                $gradeGoG[$key]['gradeId'] = $value['gradeid'];
                $gradeGoG[$key]['gradeName'] = $value['gradeName'];
            }
            $returnList = array();
            $i = 0;
            $year = array();
            foreach($gradeGo as $key => $value){
                $classclass = 'class'.$value['gradeid'];
                $year[$classclass] = '年级主任';
                $teacher = 'teacherName'.$value['gradeid'];
                $year[$teacher] = $value['teacherName'];
            }
            $i++;
            foreach($dataReR as $key => $value){
                $returnList[$i] = $value;
                $i++;
            }
            $returnList[$i] = $year;
            $returnList = array_values($returnList);
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $returnList,'grade' => $gradeGoG),true);
        }
        if($type == 'classHeadAndGradeHeaderExport'){
            $grade = M('grade')->where(array('scId' => $scId))->order('name')->select();
            $class = M('class')->where(array('scId' => $scId))->order('classname')->select();
            $array = array();
            $teacher = M('user')->field('name,id')->where(array('scId' => $scId,'roleId' => $this::$teacherRoleId))->select();
            $teacherTr = array();
            $gradeTr = array();
            foreach($teacher as $key => $value){
                $teacherTr[$value['id']] = $value;
            }
            foreach($grade as $key => $value){
                $value['name'] = $this->gradeToZhong($value['name']);
                $value['userid'] = $value['userId'];
                $value['teacherName'] = $teacherTr[$value['userId']]['name'];
                $value['gradeName'] = $value['name'];
                unset($value['name']);
                unset($value['userId']);
                $gradeTr[$value['gradeid']] = $value;
            }
            //print_r($teacherTr);
            foreach($class as $key1 => $value1){
                $value1['teacherName'] = $teacherTr[$value1['userid']]['name'];
                $gradeTr[$value1['grade']]['data'][] = $value1;
            }
            $gradeTr = array_values($gradeTr);
            print_r($gradeTr);
        }
        if($type == 'clearsHeader'){
            $gradeId = I('post.gradeId');
            $true = 1;
            if($ONE = M('grade')->where(array('gradeid' => $gradeId,'scId' => $scId))->setField(array('userId' => null))){
            }
            if($TOW =M('class')->where(array('grade' => $gradeId,'scId' => $scId))->setField(array('userid' => null))){
            }
            $this->returnJson(array('statu' => 1, 'message' => '清空成功'),true);
        }
    }
    public function achievementPro(){//成绩证明
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck',false);
        $userRoleId = $scId['roleId'];
        $userId = $scId['userId'];
        $scId = $scId['scId'];
        if($type == 'getGradeClassStudent'){
            $grade = array();
            if($userRoleId == $this::$teacherRoleId){
                $grade = M('jw_schedule')->field('gradeName')->where(array('scId' => $scId,'techerId' => $userId))->group('gradeId')->order('gradeName')->select();
            }else{
                $grade = M('grade')->field('name')->where(array('scId' => $scId))->group('gradeid')->order('name')->select();
                foreach($grade as $key => $value){
                    $grade[$key]['gradeName'] = $value['name'];
                }
            }
            $data = M('student_info')->field('userId,name,gradeId,classId,grade,className')->where(array('scId' => $scId))->order('grade,ClassName')->select();
            $array = array();
            foreach($data as $key => $value){
                if($value['className']){
                    $array[$value['grade']][$value['className']][] = $value;
                }else{
                    $array[$value['grade']]['未分'][] = $value;
                }
            }
            $array1 = array();
            foreach($grade as $key => $value){
                $array1[$value['gradeName']] = $array[$value['gradeName']];
            }
            $array = $array1;
            $return = array();
            $j = 0;
            foreach($array as $key => $value){
                $return[$j]['name'] = $this->gradeToZhong($key);
                $i = 0;
                foreach($value as $key1 => $value1){
                    $return[$j]['data'][$i]['name'] = $key1.'班';
                    foreach($value1 as $key2 => $value2){
                        $return[$j]['data'][$i]['data'][] = $value2;
                    }
                    $i++;
                }
                $j++;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
        }
        if($type == 'getTestList'){
            $gradeId = I('get.gradeId');
            $classId =I('get.classId');
            $tests = M('examination')->where(array('gradeid' => $gradeId,'scId' => $scId))->select();
            $return = array();
            foreach($tests as $key => $value){
                $panduan = explode(',',$value['class']);
                foreach($panduan as $key1 => $value1){
                    if($value1 == $classId){
                        $return[] = $value;
                    }
                }
            }
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $return),true);
        }
        if($type == 'getTestGrade'){
            $userId = I('get.userId');
            $examinationid = I('get.examinationid');
            $count = count($examinationid);
            $idid = '';
            $i = 1;
            foreach($examinationid as $key => $value){
                if($count == $i){
                    $idid = $idid.$value;
                }else{
                    $idid = $value.','.$idid;
                }
                $i++;
            }
            $examinationid = $idid;
            $sql = "select * from mks_examination_results where scId=$scId and userid=$userId AND examinationid in($examinationid) ORDER BY subjectid";
            $sore = M('')->query($sql);
            $exName = "select examinationid,examination from mks_examination where scId=$scId  AND examinationid in($examinationid)";
            $exName = M('')->query($exName);
            //$sore = M('examination_results')->where(array('examinationid' => $examinationid,'userid' => $userId,'scId' => $scId))->order('subjectid')->select();
            $subject = M('subject')->where(array('scId' => $scId))->select();
            foreach($sore as $key => $value){
                foreach($subject as $key1 => $value1){
                    if($value['subjectid'] == $value1['subjectid']){
                        $sore[$key]['subjectname'] = $value1['subjectname'];
                    }
                }
            }
            $dataRe = array();
            foreach($sore as $key => $value){
                $dataRe[$value['subjectid']][$value['examinationid']] = $value;
            }
            foreach($dataRe as $key => $value){
                foreach($value as $key1 => $value1){
                    $true = 0;
                    foreach($exName as $key2 => $value2){
                        if(isset($dataRe[$key][$value2['examinationid']])){
                        }else{
                            $dataRe[$key][$value2['examinationid']] = array(
                                'examinationid' => $value2['examinationid'],
                                'results' => 0,
                            );
                        }
                    }
                }
            }
            $array11 = array();
            foreach($dataRe as $key => $value){
                $zj = array();
                $true = 0;
                foreach($value as $key1 => $value1){
                    if(isset($value1['subjectname'])){
                        if($true == 0){
                            $zj = $value1;
                            $true = 1;
                        }
                    }
                    $zzz = 'test'.$value1['examinationid'];
                    $zj[$zzz] = $value1['results'];
                }
                $array11[] = $zj;
            }
            foreach($exName as $key => $value){
                $zj = 'test'.$value['examinationid'];
                $exName[$key]['name'] = $zj;
            }
            $return = array(
                'name' => $exName,
                'data' => $array11,
            );
            $this->returnJson(array('data' => $return,'statu' => 1,'message' => 'success'),true);
        }
        if($type == 'exportTestGrade'){
            $userId = I('get.userId');
            $examinationid = I('get.examinationid');
            $count = count($examinationid);
            $idid = '';
            $examinationid = explode(',',$examinationid);
            $i = 1;
            foreach($examinationid as $key => $value){
                if($count == $i){
                    $idid = $idid.$value;
                }else{
                    $idid = $value.','.$idid;
                }
                $i++;
            }
            $examinationid = $idid;
            $sql = "select * from mks_examination_results where scId=$scId and userid=$userId AND examinationid in($examinationid) ORDER BY subjectid";
            $sore = M('')->query($sql);
            $exName = "select examinationid,examination from mks_examination where scId=$scId  AND examinationid in($examinationid)";
            $exName = M('')->query($exName);
            //$sore = M('examination_results')->where(array('examinationid' => $examinationid,'userid' => $userId,'scId' => $scId))->order('subjectid')->select();
            $subject = M('subject')->where(array('scId' => $scId))->select();
            foreach($sore as $key => $value){
                foreach($subject as $key1 => $value1){
                    if($value['subjectid'] == $value1['subjectid']){
                        $sore[$key]['subjectname'] = $value1['subjectname'];
                    }
                }
            }
            $dataRe = array();
            foreach($sore as $key => $value){
                $dataRe[$value['subjectid']][$value['examinationid']] = $value;
            }
            foreach($dataRe as $key => $value){
                foreach($value as $key1 => $value1){
                    $true = 0;
                    foreach($exName as $key2 => $value2){
                        if(isset($dataRe[$key][$value2['examinationid']])){
                        }else{
                            $dataRe[$key][$value2['examinationid']] = array(
                                'examinationid' => $value2['examinationid'],
                                'results' => 0,
                            );
                        }
                    }
                }
            }
            $array11 = array();
            foreach($dataRe as $key => $value){
                $zj = array();
                $true = 0;
                foreach($value as $key1 => $value1){
                    if(isset($value1['subjectname'])){
                        if($true == 0){
                            $zj = $value1;
                            $true = 1;
                        }
                    }
                    $zzz = 'test'.$value1['examinationid'];
                    $zj[$zzz] = $value1['results'];
                }
                $array11[] = $zj;
            }
            $exNamego = array();
            foreach($exName as $key => $value){
                $zj = 'test'.$value['examinationid'];
                $exNamego['subjectname'] = '科目';
                $exNamego[$zj] =  $value['examination'];
            }
            $tr = array();
            foreach($exNamego as $key => $value){
                $tr[] = array(
                    'en' => $key,
                    'zh' => $value
                );
            }
            $this->export($array11,$tr);
        }
    }
    public function zdPro(){
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck',false);
        $scId = $scId['scId'];
        if($type == 'create'){
            $userId = I('post.userId');
            $date =  I('post.data');
            if(M('student_growth_zd')->where(array('scId' => $scId,'userId' => $userId))->find()){
                if(M('student_growth_zd')->where(array('scId' => $scId,'userId' => $userId))->setField(array(
                    'data' => json_encode($date)
                )) === false){
                    $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
                }
                $this->returnJson(array('statu' => 1,'message' => 'success'),true);
            }else{
                if(M('student_growth_zd')->add(array(
                    'userId' => $userId,
                    'scId' => $scId,
                    'data' => json_encode($date)
                ))){
                    $this->returnJson(array('statu' => 1,'message' => 'success'),true);
                }
                $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
            }
        }
        if($type == 'getUser'){
            $userId = $_GET['userId'];
            if($dataGo = M('student_growth_zd')->where(array('scId' => $scId,'userId' => $userId))->find()){
                $this->returnJson(array('statu' => 1,'message' => 'success','data' => json_decode($dataGo['data'],true)),true);
            }
            $school = M('school')->where(array('scId' => $scId))->find();
            $schoolName = $school['scName'];
            $user = M('student_info')->field('name,sex,birthday,grade,className')->where(array('userId' => $userId,'scId' => $scId))->find();
            $gradeName = $this->gradeToZhong($user['grade']);
            $date = date('Y-m-d');
            $user['name'] = explode('(',$user['name']);
            $user['name'] = $user['name'][0];
            $zn = array(
                'name' => $user['name'],
                'sex' => $user['sex'],
                'birthday' => $user['birthday'],
                'className' => $user['className'].'班',
                'date' => $date,
                'gradeName' => $gradeName,
                'schoolName' => $schoolName
            );
            //中文转拼音
            header("Content-Type:text/html;charset=UTF-8");
            date_default_timezone_set("PRC");
            $showapi_appid = 45730;  //替换此值,在官网的"我的应用"中找到相关值
            $showapi_secret = 'bcf80b1f062f4214a336872fe8cd61e0';  //替换此值,在官网的"我的应用"中找到相关值
            $paramArr = array(
                'showapi_appid'=> $showapi_appid,
                'content'=> $user['name']
                //添加其他参数
            );
            //创建参数(包括签名的处理)
            function createParam ($paramArr,$showapi_secret){
                $paraStr = "";
                $signStr = "";
                ksort($paramArr);
                foreach ($paramArr as $key => $val) {
                    if ($key != '' && $val != '') {
                        $signStr .= $key.$val;
                        $paraStr .= $key.'='.urlencode($val).'&';
                    }
                }
                $signStr .= $showapi_secret;//排好序的参数加上secret,进行md5
                $sign = strtolower(md5($signStr));
                $paraStr .= 'showapi_sign='.$sign;//将md5后的值作为参数,便于服务器的效验
                return $paraStr;
            }
            $param = createParam($paramArr,$showapi_secret);
            $url = 'http://route.showapi.com/99-38?'.$param;
            $result = file_get_contents($url);
            $result = json_decode($result,true);
            $englishName = $result['showapi_res_body']['data'];
            $en = array(
                'birthday' => $user['birthday'],
                'className' => $user['className'],
                'name' => $englishName,
                'date' => $date,
                'gradeName' => $user['grade'],
                'schoolName' => $school['enName'],
                'sex' => $this->to_enSex($user['sex'])
            );
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => array('en' => $en,'zn' => $zn)),true);
        }
    }
    public function printCenter(){ //套打中心
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck',false);
        $userRoleId = $scId['roleId'];
        $userId = $scId['userId'];
        $scId = $scId['scId'];
        if($type == ''){

        }
    }
    private function to_enSex($sex){
        if($sex == '男'){
            return 'male';
        }else{
            return 'female';
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
}