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
class StudentsgrowthrecordController extends Base
{
    /**公用的不设置权限*/
    public function getSubjectList()
    {//得到课程列表//公用的怎么写
        $type = $_GET['type'];
        $scId = $this->get_session('loginCheck', false);
        $scId = $scId['scId'];
        if ($type == 'getSubjectList') { //得到课程列表
            $this->returnJson(array('data' => M('subject')->where(array('scId' => $scId))->select(), 'staut' => 1, 'message' => 'success'), true);
        }
        if ($type == 'getGradeList') { //得到年级列表
            $this->returnJson(array('data' => M('grade')->where(array('scId' => $scId))->select(), 'statu' => 1, 'message' => 'success'), true);
        }
        if ($type == 'getClassList') { //得到班级列表
            $this->returnJson(array('data' => M('class')->where(array('scId' => $scId, 'grade' => $_GET['grade']))->select(), 'message' => 'success', 'statu' => 1), true);
        }
        if ($type == 'getTeacherList') {//得到教师列表
            $field = $_GET['sortType'];
            $sort = $_GET['sort'];
            $this->returnJson(array('statu' => 1, 'message' => 'sucess', 'data' => M('user')->field('id,teachingSubjects,name,jobNumber,phone')->where(array('scId' => $scId, 'roleId' => $this::$teacherRoleId))->order("$field $sort")->select()), true);
        }
    }
    public function basicsSet(){//写评语
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getGradeClassStudent'){//得到年级班级学生
            $grade = array();
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
                $data = M('student_info')->field('userId,name,gradeId,classId,grade,className')->where(array('scId' => $scId))->order('grade,ClassName')->select();
                $array = array();
                foreach($data as $key => $value){
                    if($gradeRe[$value['gradeId']][$value['classId']]){
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
                            $return[$j]['data'][$i]['data'][] = $value2;
                        }
                        $i++;
                    }
                    $j++;
                }
                $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
            }else{
                $data = M('student_info')->field('userId,name,gradeId,classId,grade,className')->where(array('scId' => $scId))->order('grade,ClassName')->select();
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
        if($type == 'addMoBan'){
            $templateId = I('post.templateId');
            if(M('student_growth_template')->where(array('templateId' => $templateId,'scId' => $scId))->setInc('frequency',1)){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }else{
                $this->returnJson(array('statu' => 0, 'message' => 'false'),true);
            }
        }
        if($type == 'valueSelect'){
            $grade = array();
            if($userRoleId == $this::$teacherRoleId){
                $grade = M('jw_schedule')->field('gradeName')->where(array('scId' => $scId,'techerId' => $userId))->group('gradeId')->select();
            }else{
                $grade = M('grade')->field('name')->where(array('scId' => $scId))->group('gradeid')->select();
                foreach($grade as $key => $value){
                    $grade[$key]['gradeName'] = $value['name'];
                }
            }
            $value = I('get.value');
            $data = M('')->query("SELECT userId,name,gradeId,classId,grade,className FROM mks_student_info WHERE `name` LIKE '%$value%' order by grade,className");
            $array = array();
            foreach($data as $key => $value){
                $array[$value['grade']][$value['className']][] = $value;
            }
            $array1 = array();
            foreach($grade as $key => $value){
                $array1[$value['gradeName']] = $array[$value['gradeName']];
            }
            $array = $array1;
            $return = array();
            $j = 0;
            foreach($array as $key => $value){
                $return[$j]['gradeName'] = $this->gradeToZhong($key);
                $i = 0;
                foreach($value as $key1 => $value1){
                    $return[$j]['data'][$i]['className'] = $key1;
                    foreach($value1 as $key2 => $value2){
                        $return[$j]['data'][$i]['data'][] = $value2;
                    }
                    $i++;
                }
                $j++;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
        }
        if($type == 'uploda'){
            if($url = $this->uploads()){
                $this->returnJson(array('statu' => 1,'url' => $url),true);
            }else{
                $this->returnJson(array('statu' => 0),true);
            }
        }
        if($type == 'createStudentRecord'){//创建学生记录
            $title = I('post.title');
            $label = I('post.label');
            $chechkk = 0;
            $allllb = M('student_growth_label')->field('labelId')->where(array('weight' => 2, 'scId' => $scId))->find();
            $allllb = $allllb['labelId'];
            $ii = 0;
            $str = '';
            foreach($label as $key => $value){
                if($value == $allllb){
                    $chechkk = 1;
                }
                if($ii == 0){
                    $str = $value;
                }else{
                    $str = $str.','.$value;
                }
                $ii++;
            }
            $content = I('post.content');
            $userId = I('post.userId');
            $url = I('post.url');
            if(!count($url)>0){
                $url = array();
            }
            $sssss = M('user')->field('teachingSubjects,post')->where(array('scId' => $scId,'id' => $userId))->find();
            if(M('student_growth_record')->add(array(
                'title' => $title,
                'label' => $str,
                'content' => $content,
                'userId' => $userId,
                'scId' => $scId,
                'createTime' => date('Y-m-d H:i:s'),
                'url' => json_encode($url),
                'teacherId' => $jbXn['userId'],
                'teacherName' => $jbXn['name'],
                'subject' =>$sssss['teachingSubjects'],
                'post' => $sssss['post'],
                'ifQm' => $chechkk
            ))){
                $this->returnJson(array('statu' => 1,'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
        }
        if($type == 'uploadFile'){//上传文件
            if($url = $this::uploads()){
                $this->returnJson(array('statu' => 1,'message' => 'success','url' => $url),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'fail','url' => ''),true);
        }
        if($type == 'createLabel'){//创建label
            $labelName = I('post.labelName');
            if(M('student_growth_label')->where(array('scId' => $scId,'labelName' => $labelName))->find()){
                $this->returnJson(array('statu' => 2, 'message' => '该标签已经存在'),true);
            }
            if(!$labelName){
                $this->returnJson(array('statu' => 2, 'message' => '标签名必填'),true);
            }
            if(M('student_growth_label')->add(array(
                'labelName' => $labelName,
                'scId' => $scId,
                'createTime' => strtotime(date('Y-m-d H:i:s')),
                'weight' => 1
            ))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'deleteLabel'){//删除label
            $labelId = I('post.labelId');
            if(M('student_growth_label')->where(array('scId' => array(array('eq',$scId),array('eq',0),'or'),'weight' => 1,'labelId' => $labelId))->delete()){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => '“该标签”标签是系统预置的不能删除'),true);
        }
        if($type == 'getLabel'){//得到larbel
            $data = M('student_growth_label')->where(array('scId' => array(array('eq',$scId),array('eq',0),'or')))->order('weight')->select();
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'createModel'){//创建评语
            $modelTypeId = I('post.modelTypeId');
            $content = I('post.content');
            if(M('student_growth_template')->add(array(
                    'modelTypeId' => $modelTypeId,
                    'content' => $content,
                    'frequency' => 0,
                    'scId' => $scId,
                    'lastRecordTime' => strtotime(date('Y-m-d H:i:s')),
                    'createTime' => strtotime(date('Y-m-d H:i:s'))
                )
            )){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'delModel'){
            $templateId = I('post.templateId');
            if(M('student_growth_template')->where(array('scId' => $scId,'templateId' => $templateId))->delete()){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'false'),true);
        }
        if($type == 'getModelType'){
            $array = array(
                0 => array(
                    'modelTypeId' => 1,
                    'modelTypeName' => '开头'
                ),
                1 => array(
                    'modelTypeId' => 2,
                    'modelTypeName' => '思想'
                ),
                2 => array(
                    'modelTypeId' => 3,
                    'modelTypeName' => '学习'
                ),
                3 => array(
                    'modelTypeId' => 4,
                    'modelTypeName' => '生活'
                ),
                4 => array(
                    'modelTypeId' => 5,
                    'modelTypeName' => '体育'
                ),
                5 => array(
                    'modelTypeId' => 6,
                    'modelTypeName' => '性格'
                ),
                6 => array(
                    'modelTypeId' => 7,
                    'modelTypeName' => '优点'
                ),
                7 => array(
                    'modelTypeId' => 8,
                    'modelTypeName' => '缺点'
                ),
                8 => array(
                    'modelTypeId' => 9,
                    'modelTypeName' => '寄语'
                ),
                9 => array(
                    'modelTypeId' => 10,
                    'modelTypeName' => '其他'
                )
            );
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $array),true);
        }
        if($type == 'modelList'){//得到模板列表
            $modelTypeId = $_GET['modelTypeId'];
            $data = M('student_growth_template')->where(array('scId' => $scId,'modelTypeId' => $modelTypeId))->order('lastRecordTime desc')->select();
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
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $data),true);
        }
        if($type == 'modelToTop'){//置顶模板
            $templateId = $_GET['templateId'];
            if(M('student_growth_template')->where(array('templateId' => $templateId,'scId' => $scId))->setField(array('lastRecordTime' => strtotime(date('Y-m-d H:i:s'))))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'createModelType'){//创建模板类型
            if(M('student_growth_model_type')->add(array(
                'modelTypeName' => I('post.modelTypeName'),
                'scId' => $scId,
                'createTime' => strtotime(date('Y-m-d H:i:s'))
            ))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'getTestList'){ //得到考试列表
            $gradeId = $_GET['gradeId'];
            $classId = $_GET['classId'];
            $tests = M('examination')->where(array('gradeid' => $gradeId,'scId' => $scId,'release' =>1))->select();
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
        if($type == 'getTestGrade'){ //得到各科考试分数
            $userId = I('get.userId');
            $examinationid = I('get.examinationid');
            $examination = I('get.examination');
            $sore = M('examination_results')->where(array('examinationid' => $examinationid,'userid' => $userId,'scId' => $scId))->order('subjectid')->select();
            $subject = M('subject')->where(array('scId' => $scId))->select();
            $classAvg = M('')->query("SELECT avg(results) as soce,subjectid FROM mks_examination_results WHERE examinationid=$examinationid and scId=$scId GROUP BY subjectid");
            $classAvgTr = array();
            $classAll = 0;
            $personAll = 0;
            foreach($classAvg as $key => $value){
                $classAvgTr[$value['subjectid']] = $value;
                $classAll = $value['soce']+$classAll;
            }
            $classAll = round($classAll,2);
            foreach($sore as $key => $value){
                foreach($subject as $key1 => $value1){
                    if($value['subjectid'] == $value1['subjectid']){
                        $sore[$key]['subjectname'] = $value1['subjectname'];
                        $sore[$key]['classAvg'] = round($classAvgTr[$value['subjectid']]['soce'],2);
                        $personAll = $personAll+$value['results'];
                    }
                }
            }
            $class = array();
            $person = array();
            $subject = array();
            foreach($sore as $key => $value){
                $class[$key]['classAvg'] = $value['classAvg'];
                $person[$key]['user'] = $value['results'];
                $subject[$key]['subjectName'] = $value['subjectname'];
            }
            $return = array('data' => array('class' => $class,'person' => $person,'subject' => $subject),'classAll' => $classAll,'userAll' => $personAll);
            $this->returnJson(array('data' => $return,'statu' => 1,'message' => 'success'),true);
        }
        if($type == 'gradeThink'){

        }
    }
    public function myRecord(){//我的成长记录 //学生家长特有
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getList'){
            if($this::$jZroleId == $userRoleId){
                $jz = M('user')->field('childId')->where(array('id' => $userId,'scId' => $scId))->find();
                $stuId = $jz['childId'];
                $data = M('student_growth_record')->where(array('scId' => $scId,'userId' => $stuId))->order('createTime desc')->select();
                $labl = M('student_growth_label')->where(array('scId' => array(array('eq',$scId),array('eq',0),'or')))->select();
                $lablRrue = array();
                foreach($labl as $key => $value){
                    $lablRrue[$value['labelId']] = $value;
                }
                foreach($data as $key => $value){
                    $data[$key]['url'] = json_decode($data[$key]['url'],true);
                    $la = $value['label'];
                    $la = explode(',',$la);
                    foreach($la as $key1 => $value1){
                        $data[$key]['la'][] = $lablRrue[$value1];
                    }
                }
                if(count($data)<1){
                    $data = array();
                }
                $this->returnJson(array('statu' => 1,'message' => 'success','data' => $data),true);
                //M('')
            }else{
                $data = M('student_growth_record')->where(array('scId' => $scId,'userId' => $userId))->order('createTime desc')->select();
                $labl = M('student_growth_label')->where(array('scId' => array(array('eq',$scId),array('eq',0),'or')))->select();
                $lablRrue = array();
                foreach($labl as $key => $value){
                    $lablRrue[$value['labelId']] = $value;
                }
                foreach($data as $key => $value){
                    $data[$key]['url'] = json_decode( $data[$key]['url'],true);
                    $la = $value['label'];
                    $la = explode(',',$la);
                    foreach($la as $key1 => $value1){
                        $data[$key]['la'][] = $lablRrue[$value1];
                    }
                }
                $this->returnJson(array('statu' => 1,'message' => 'success','data' => $data),true);
            }
        }
    }
    public function studentsRecord(){//学生成长记录
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getGradeClassStudent'){//得到年级班级学生
            $grade = array();
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
                $data = M('student_info')->field('userId,name,gradeId,classId,grade,className')->where(array('scId' => $scId))->order('grade,ClassName')->select();
                $array = array();
                foreach($data as $key => $value){
                    if($gradeRe[$value['gradeId']][$value['classId']]){
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
                            $return[$j]['data'][$i]['data'][] = $value2;
                        }
                        $i++;
                    }
                    $j++;
                }
                $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
            }else{
                $data = M('student_info')->field('userId,name,gradeId,classId,grade,className')->where(array('scId' => $scId))->order('grade,ClassName')->select();
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
        if($type == 'getUserList'){//得到用户记录
            $userId = $_GET['userId'];
            $data = M('student_growth_record')->where(array('scId' => $scId,'userId' => $userId))->order('createTime desc')->select();
            $labl = M('student_growth_label')->where(array('scId' => array(array('eq',$scId),array('eq',0),'or')))->select();
            $lablRrue = array();
            foreach($labl as $key => $value){
                $lablRrue[$value['labelId']] = $value;
            }
            foreach($data as $key => $value){
                $data[$key]['url'] = json_decode( $data[$key]['url'],true);
                $la = $value['label'];
                if($la){
                    $la = explode(',',$la);
                    if(count($la)>0){
                        foreach($la as $key1 => $value1){
                            $data[$key]['la'][] = $lablRrue[$value1];
                            $data[$key]['label'] = $la;
                        }
                    }
                }else{
                    $data[$key]['la'] = array();
                    $data[$key]['label'] = array();
                }
            }
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $data),true);
        }
        if($type == 'uploda'){
            if($url = $this->uploads()){
                $this->returnJson(array('statu' => 1,'url' => $url),true);
            }else{
                $this->returnJson(array('statu' => 0),true);
            }
        }
        if($type == 'updataRecord'){
            $recordId = I('post.recordId');
            $title = I('post.title');
            $label = I('post.label');
            $content = I('post.content');
            $ii = 0;
            $str = '';
            foreach($label as $key => $value){
                if($ii == 0){
                    $str = $value;
                }else{
                    $str = $str.','.$value;
                }
                $ii++;
            }
            $url = I('post.url');
            if(!count($url)>0){
                $url = array();
            }
            if(M('student_growth_record')->where(array('recordId' => $recordId,'scId' => $scId))->setField(
                    array(
                        'title' => $title,
                        'label' => $str,
                        'content' => $content,
                        'scId' => $scId,
                        'url' => json_encode($url)
                    )
                ) === false){
                $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
            }
            $this->returnJson(array('statu' => 1, 'message' => 'fail'),true);
        }
        if($type == 'delete'){
            $recordId = I('post.recordId');
            if(M('student_growth_record')->where(array('recordId' => $recordId,'scId' => $scId))->delete()){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
    }
    public function agumentLarblSet(){//评语标签
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'createLabel'){
            $labelName = I('post.labelName');
            if(!$labelName){
                $this->returnJson(array('statu' => 1, 'message' => '标签名必填'),true);
            }
            if(M('student_growth_label')->add(array(
                'labelName' => $labelName,
                'scId' => $scId,
                'createTime' => strtotime(date('Y-m-d H:i:s')),
                'weight' => 2
            ))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'deleteLabel'){//删除label
            $labelId = I('post.labelId');
            $i = 0;
            if($labelId){
                foreach($labelId as $key => $value){
                    if(!M('student_growth_label')->where(array('scId' => $scId,'weight' => 2,'labelId' => $value))->delete()){
                        $i = 1;
                    }
                }
                if($i){
                    $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
                }
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'getLabel'){//得到larbel
            $labelId = I('post.labelId');
            $data = M('student_growth_label')->where(array('scId' => array(array('eq',$scId),array('eq',0),'or'),'weight' => 2))->select();
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
    }
    public function familyRecord(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getGradeClassStudent'){//得到年级班级学生
            $grade = array();
            //前端。。。。
            if($userRoleId == $this::$teacherRoleId){
                $grade = M('jw_schedule')->field('gradeName,gradeId,classId')->where(array('scId' => $scId,'techerId' => $userId))->group('gradeId')->order('gradeName')->select();
                $class = M('class')->field('classname,grade,classid')->where(array('scId' => $scId))->order('classname')->select();
                $classrel = array();
                foreach ($class as $key => $value){
                    $classrel[$value['classid']] = $value['classname'];
                }
                $gradeRe = array();
                foreach($grade as $key => $value){
                    $gradeRe[$value['gradeId']][$value['classId']] = array(
                        'gradeId' => $value['gradeId'],
                        'grade' => $value['gradeId'],
                        'classId' => $value['classId'],
                        'classname' => $classrel[$value['classId']].'班',
                        'gradeName' => $value['gradeName']
                    );
                }
                if($grade =  M('grade')->field('name,gradeid')->where(array('userId' => $userId,'scId' => $scId))->select()){
                    foreach ($grade as $key => $value){
                        foreach ($class as $key1 => $value1){
                            if($value['gradeid'] == $value1['grade']){
                                $gradeRe[$value['gradeid']][$value1['classid']] = array(
                                    'gradeId' => $value1['grade'],
                                    'grade' => $value1['grade'],
                                    'classId' => $value1['classid'],
                                    'classname' => $value1['classname'].'班',
                                    'gradeName' => $value['name']
                                );
                            }
                        }
                    }
                }if($return = M('class')->field('classname,grade,classid')->where(array('userid' => $userId,'scId' => $scId))->order('classname')->select()){
                    foreach ($return as $key => $value){
                        $ggggg = M('grade')->field('name')->where(array('scId' => $scId,'gradeid' => $value['grade']))->order('name')->find();
                        $gradeRe[$value['grade']][$value['classid']] =  array(
                            'gradeId' => $value['grade'],
                            'grade' => $value['grade'],
                            'classId' => $value['classid'],
                            'classname' => $value['classname'].'班',
                            'gradeName' => $ggggg['name']
                        );
                    }
                }
                $returnRel = array();
                $iii = 0;
                foreach ($gradeRe as $key => $value){
                    foreach ($value as $key1 => $value1){
                        $returnRel[$iii]['classname'] = $this->gradeToZhong($value1['gradeName']);
                        $returnRel[$iii]['data'][] = $value1;
                    }
                    $iii++;
                }
                $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $returnRel),true);
            }else{
                $grade = M('grade')->field('name,gradeid')->where(array('scId' => $scId))->group('gradeid')->order('name')->select();
                foreach($grade as $key => $value){
                    $grade[$key]['gradeName'] = $value['name'];
                    $grade[$key]['gradeId']  = $value['gradeid'];
                }
                $gradeRe = array();
                foreach($grade as $key => $value){
                    $gradeRe[$value['gradeId']]['classname'] = $this->gradeToZhong($value['gradeName']);
                }
                $class = M('class')->field('classname,grade,classid')->where(array('scId' => $scId))->order('classname')->select();
                //print_r($class);
                $return = array();
                foreach($class as $key => $value){
                    if(isset($gradeRe[$value['grade']])){
                        $value['classId'] = $value['classid'];
                        $value['gradeId'] = $value['grade'];
                        $value['classname'] = $value['classname'].'班';
                        unset($value['classid']);
                        $gradeRe[$value['grade']]['data'][] = $value;
                    }
                }
                $return = array();
                foreach($gradeRe as $key => $value){
                    $return[] = $value;
                }
                $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
            }
        }
        if($type == 'create'){//得到年级班级学生
            $gradeId =I('post.gradeId');
            $classId= I('post.classId');
            $examinationid = I('post.examinationid');
            $comment = I('post.comment');
            $final= I('post.final');
            $jzShow= I('post.jzShow');
            if(M('student_growth_family')->add(
                array(
                    'examinationid' =>$examinationid,
                    'comment' => $comment,
                    'scId' => $scId,
                    'jzShow' => $jzShow,
                    'final' => $final,
                    'gradeId' => $gradeId,
                    'classId' => $classId,
                    'createTime' => strtotime(date('Y-m-d H:i:s')),
                    'lastRecordTime' => strtotime(date('Y-m-d H:i:s'))
                )
            )){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'getTestList'){ //得到考试列表
            $gradeId = $_GET['gradeId'];
            $classId = $_GET['classId'];
            $tests = M('examination')->where(array('gradeid' => $gradeId,'scId' => $scId,'release' =>1))->select();
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
        if($type == 'prew'){
            $examinationid = $_GET['examinationid'];
            $gradeId =$_GET['gradeId'];
            $classId = $_GET['classId'];
            $allUser = M('user')->field('name,id,className,grade')->where(array('scId' => $scId,'roleId' => $this::$studentRoleId,'gradeId' => $gradeId,'class' => $classId))->select();
            $grademn = $allUser[0]['grade'];
            $relUser = array();
            foreach($allUser as $key => $value){
                $relUser[$value['id']] = $value['name'];
            }
            $relut = M('examination_results')->field('userid,subjectid,results')->where(array('scId' => $scId,'examinationid' => $examinationid))->select();
            $subject = M('subject')->where(array('scId' => $scId))->select();
            $dated = date('Y');
            $datedEnd = date('Y');
            $data = M('student_growth_record')->field('userId,content,createTime')->where(array('scId' => $scId,'ifQm' => 1))->order('createTime')->select();
            $redd = array();
            foreach($data as $key => $value){
                $redd[$value['userId']] = $value['content'];
            }
            $subjectName = array();
            foreach($subject as $key => $value){
                $subjectName[$value['subjectid']] = $value['subjectname'];
            }
            $allRe = array();
            $i = 0;
            $branch = M('class')->where(array('classid' => $classId , 'scId' => $scId))->find();
            //$sore = M('examination_subject')->where(array('examinationid' => $examinationid,'scId' => $scId,'branchid' => $branch['branch']))->sum('results');
            foreach($relut as $key => $value){
                if(isset($relUser[$value['userid']])) {
                    $allRe[$value['userid']]['userId'] = $value['userid'];
                    $allRe[$value['userid']]['name'] = $relUser[$value['userid']];
                    $value['subjectName'] = $subjectName[$value['subjectid']];
                    $allRe[$value['userid']][$value['subjectid']] = $value['results'];
                    $allRe[$value['userid']]['className'] = $allUser[0]['className'];
                    $allRe[$value['userid']]['sore'] +=$value['results'];
                    $i++;
                }
            }
            $tr = array();
            $ii = 0;
            sort($allRe);
            foreach($allRe[0] as $key => $value){
                if($key != 'name' && $key!='className' && $key!='content' && $key!='userId'&& $key!='sore'){
                    $tr[$ii] = array(
                        'id' => $key,
                        'subjectName' => $subjectName[$key]
                    );
                }
                $ii++;
            }
            $cccc = array();
            foreach ($allRe as $key =>$value){
                   $cccc[$value['userId']] = $value;
            }
            $ddddddd = array();
            $iii = 0;
            foreach ($allUser as $key =>$value){
                $qm = $redd[$value['id']];
                if(isset($cccc[$value['id']])){
                    $ddddddd[$iii] = $cccc[$value['id']];
                    $ddddddd[$iii]['className']=$this->gradeToZhong($grademn).$value['className'].'班';
                    $ddddddd[$iii]['content'] = $qm;
                }else{
                    $ddddddd[$iii] = array(
                        'name' => $value['name'],
                        'className' => $this->gradeToZhong($grademn).$value['className'].'班',
                        'content' => $qm
                    );
                }
                $iii++;
            }
            rsort($tr);
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => array('tr' => $tr,'data' => $ddddddd)),true);
        }
        if($type == 'contentPrew'){
            $examinationid = $_GET['examinationid'];
            $gradeId =$_GET['gradeId'];
            $classId = $_GET['classId'];
            $contentGd = $_GET['contentGd'];
            $jzshow = $_GET['jzshow'];
            $list = $_GET['list'];
            $qmShow = $_GET['qmShow'];
            $listrel = array();
            foreach($list as $key => $value){
                $listrel[$value['userId']] = $value['contentgogo'];
            }
            $allUser = M('user')->field('name,id,className,grade')->where(array('scId' => $scId,'gradeId' => $gradeId,'class' => $classId,'roleId'=> $this::$studentRoleId))->select();
            $grade = $this->gradeToZhong($allUser[0]['grade']);
            $claaaa = $allUser[0]['className'].'班';
            $relUser = array();
            foreach($allUser as $key => $value){
                $relUser[$value['id']] = $value['name'];
            }

            $relut = M('examination_results')->field('userid,subjectid,results')->where(array('scId' => $scId,'examinationid' => $examinationid))->select();
            $exName = M('examination')->field('examination')->where(array('scId' => $scId,'examinationid' => $examinationid))->find();
            $subject = M('subject')->where(array('scId' => $scId))->select();
            $dated = date('Y');
            $datedEnd = date('Y');
            if(date('m')>=9){
                $dated = $dated.'-09-00 00:00:00';
                $datedEnd++;
                $datedEnd = $datedEnd.'-02-29 23:59:59';
            }else{
                $dated = $dated.'-03-00 00:00:00';
                $datedEnd = $datedEnd.'-08-29 23:59:59';
            }
            $branch = M('class')->where(array('classid' => $classId , 'scId' => $scId))->find();
            //$sore = M('examination_subject')->where(array('examinationid' => $examinationid,'scId' => $scId,'branchid' => $branch['branch']))->sum('results');
            //$reList = M('student_growth_record')->field('label,content,userId')->where(array('scId' => $scId,'ifQm' => 1,'createTime' => array(array('lt',$datedEnd),array('gt',$dated),'and')))->select();
            $leve = M('student_leave_list')->field('userId')->where(array('scId' => $scId,'state' => 1,'classId' => $classId,'gradeId' => $gradeId,'createTime' => array(array('lt',$datedEnd),array('gt',$dated),'and')))->select();
            $leveList = array();
            foreach($leve as $key => $value){
                $leveList[$value['userId']]['count']++;
            }
            /*$redd = array();
            foreach($reList as $key => $value){
                $redd[$value['userId']] = $value;
            }*/
            $subjectName = array();
            foreach($subject as $key => $value){
                $subjectName[$value['subjectid']] = $value['subjectname'];
            }
            $allRe = array();
            $i = 0;
            foreach($relut as $key => $value){
                if(isset($relUser[$value['userid']])) {
                    $allRe[$value['userid']]['qm'] =  $listrel[$value['userid']];
                    $allRe[$value['userid']]['content'] = $contentGd;
                    $allRe[$value['userid']]['leavecount'] = $leveList[$value['userid']]['count'];
                    $allRe[$value['userid']]['name'] = $relUser[$value['userid']];
                    $value['subjectName'] = $subjectName[$value['subjectid']];
                    $allRe[$value['userid']][$value['subjectid']] = $value['results'];
                    $allRe[$value['userid']]['className'] = $allUser[0]['className'];
                    $allRe[$value['userid']]['all'] += $value['results'];
                    $allRe[$value['userid']]['userId'] = $value['userid'];
                    $i++;
                }
            }
            $tr = array();
            $ii = 0;
            $jj = 0;
            $xxxx = array();
            foreach($allRe as $key => $value){
                $xxxx[$jj] = $value;
                $jj++;
            }
            $allRe = $xxxx;
            foreach($allRe[0] as $key => $value){
                if($key != 'name' && $key!='className' && $key!='content' && $key!='leavecount'&& $key!='all'&& $key!='qm'&& $key!='userId'){
                    $tr[$ii] = array(
                        'id' => $key,
                        'subjectName' => $subjectName[$key]
                    );
                }
                $ii++;
            }
            $tr['all']['id']='all';
            $tr['all']['subjectName']='总分';
            $tt = array();
            $cccc = array();
            foreach ($allRe as $key =>$value){
                $cccc[$value['userId']] = $value;
            }
            $iii = 0;
            foreach ($allUser as $key =>$value){
                if(isset($cccc[$value['id']])){
                    $ddddddd[$iii] = $cccc[$value['id']];
                    $ddddddd[$iii]['className']=$this->gradeToZhong($grademn).$value['className'].'班';
                }else{
                    $ddddddd[$iii] = array(
                        'name' => $value['name'],
                        'className' => $this->gradeToZhong($grademn).$value['className'].'班',
                        'content' => '',
                    );
                }
                $iii++;
            }
            $schoolName = M('school')->field('scName')->where(array('scId' => $scId))->find();
            $schoolName = $schoolName['scName'];
            foreach ($allRe as $key => $value){
                $ssss = '<div style="width: 100%;"><div style="font-size: 20px; font-weight:bold;text-align: center; margin-top: 60px;">'."$schoolName 家庭报告书".'</div>'
        .'<div style="font-size: 16px;text-align:left; color:#000000; margin-top: 28px;">尊敬的'.$value['name'].'同学家长</div>'
        .'<div style="font-size: 16px;text-align:left; color:#000000; min-height: 50px; line-height: "><p style="margin-top: 2px; line-height: 26px; font-size: 14px;">'.$value['content'].'</p></div><div style="font-size: 16px;text-align:left; color:#000000">学业水平：</div><div style="font-size: 16px;text-align:left; color:#000000; margin-top: 10px;">'
           . '<table style="width: 100%;" rules="all" frame="border"><tr>';
                    foreach ($tr as $key1 => $value1){
                        $ssss.='<td style="font-weight: bold">'.$value1['subjectName'].'</td>';
                    }
                $ssss.='</tr><tr>';
                    foreach ($tr as $key1 => $value1){
                        $ssss.= '<td>'.$value[$value1['id']].'</td>';
                    }
                $ssss.= '</tr></table></div>';
                $ssss .= '<div style="font-size: 16px;text-align:left; color:#000000; margin-top: 10px;">学期评语：</div><div style="font-size: 16px;text-align:left; color:#000000; margin-top: 10px;border: 1px #aaa solid; min-height: 80px;"><p style="margin-top: 2px; line-height: 26px; font-size: 14px; margin-left: 6px;">'.$value['qm'].'</p></div>';
                if($jzshow){
                    $ssss.= '<div style="font-size: 16px;text-align:left; color:#000000; margin-top: 60px;">家长意见栏：</div><div style="font-size: 16px;text-align:left; color:#000000; margin-top: 10px;border: 1px #aaa solid; min-height: 120px;"> </div>';
                }
                $ssss.= '<div style="margin-top: 10px; margin-bottom: 10px; font-size: 12px; text-align: center;">'.$grade.$claaaa.$exName['examination'].'</div>';
                $ssss .='</div>';
                $tt[] = $ssss;
            }
            $this->returnJson(array('statu' => 1,'data' => $tt),true);
        }
        if($type == 'export'){

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