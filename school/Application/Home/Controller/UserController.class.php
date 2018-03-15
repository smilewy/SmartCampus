<?php
/**
 * Created by PhpStorm.
 * User: xiaolong
 * Date: 2017/6/22
 * Time: 14:15
 * 用户管理
 */
namespace Home\Controller;
//use Think\Controller;
//use Vendor\PHPExcel\PHPExcel;
//use Org\Util\String;

class UserController extends Base {
    public function login(){
        //vendor('PHPExcel','');
    }
    public function teacherToZg(){
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getSubject'){
            $data = M('subject')->field('subjectid,subjectname')->where(array('scId' => $scId))->select();
            $this->returnJson(array('statu' => 1,'message' => 'success' ,'data' => $data),true);
        }
        if($type == 'getTeacher'){
            $subjectId = I('get.subjectid');
            $teacher = M('user')->field('name,id,teachingSubjects')->where(array('scId' => $scId,'teachingSubjectsId' => $subjectId,'roleId' => $this::$teacherRoleId))->select();
            $this->returnJson(array('statu' => 1,'message' => 'seuccess' ,'data' => $teacher),true);
        }
        if($type == 'getRole'){
            $data = M('role')->field('roleId,roleName')->where(array('scId' => array(array('eq',$scId),array('eq',0),'or'),'roleId' => array(array('neq',$this::$adminRoleId),array('neq',$this::$studentRoleId),array('neq',$this::$jZroleId),array('neq',$this::$teacherRoleId))))->select();
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $data),true);
        }
        if($type == 'create'){
            $data = I('get.data');
            foreach ($data as $key => $value){
                M('user')->where(array('id' => $value['id'],'scId' => $scId))->setField(array('roleId' => $value['roleId'],'post' => $value['roleName']));
            }
            $this->returnJson(array('statu' => 1,'message' => 'success'),true);
        }
    }
    private function createToken(){
        return md5(uniqid().rand(0.9).rand(0.9).rand(0.9));
    }
    public function allUse(){
        $type = I('get.type');
        if($type == 'uploadGoOn'){
            $token = I('post.token');
            $jbXn = $this->get_session('loginCheck', false);
            $scId = $jbXn['scId'];
            $data = json_decode($this::redis_operation($token,0,0,3),true);
            if(count($data)<=0){
                $this->returnJson(array('statu' => 0,'message' => '批量导入失败'),true);
            }
            $this::redis_operation($token,0,0,4);
            $strs = $data['strs'];
            $newck = $data['newck'];
            $roleNameEn = $data['roleNameEn'];
            $newck1 = $data['newck1'];
            $createTime = strtotime(date('Y-m-d H:i:s'));
            $prefix = M('school')->field('prefix')->where(array('scId' => $scId))->find();
            $prefix = $prefix['prefix'];
            $gradeId = $data['gradeId'];
            $maxNumber = M('user')->where(array('scId' =>array(array('eq',$scId),array('eq', -$scId),'or')))->max('number');
            if($roleNameEn == 'xs'){
                $maxNumber++;
                $serNmuRel = array();
                $serNmu = M('')->query("select MAX(serialNumber) as num,className FROM mks_user WHERE scId = $scId AND gradeId=$gradeId GROUP BY className");
                $classAll = M('')->query("select  className from mks_class WHERE grade=$gradeId and scId =$scId");
                foreach($classAll as $key => $value) {
                    $serNmuRel[$value['className']] = 0;
                }
                foreach($serNmu as $key => $value){
                    $serNmuRel[$value['className']] = $value['num'];
                }
                foreach($strs as $key => $value){
                    $maxNumber++;
                    $strs[$key]['account'] = $prefix.$maxNumber;
                    $strs[$key]['number'] = $maxNumber;
                    $strs[$key]['scPrefix'] = $prefix;
                    $serNmuRel[$value['className']]++;
                    $strs[$key]['serialNumber'] = $serNmuRel[$value['className']];
                    $maxNumber++;
                }
            }
            if($roleNameEn == 'js'){
                foreach($strs as $key => $value){
                    $maxNumber++;
                    $strs[$key]['account'] = $prefix.$maxNumber;
                    $strs[$key]['number'] = $maxNumber;
                    $strs[$key]['scPrefix'] = $prefix;
                }
            }
            if($roleNameEn == 'zg'){
                $maxNumber++;
                foreach($strs as $key => $value){
                    $maxNumber++;
                    $strs[$key]['account'] = $prefix.$maxNumber;
                    $strs[$key]['number'] = $maxNumber;
                    $strs[$key]['scPrefix'] = $prefix;
                }
            }
            foreach($strs as $key => $value){
                $relName = $value['name'];
                if(isset($newck[$value['name']]) || count($newck1[$value['name']])>=2){
                    $relName = $relName.'('.substr($value["number"],6,9).')';
                    $value['name'] = $relName;
                }
                if($roleNameEn == 'xs'){
                    $isResSchool = $value['isResSchool'];
                    unset($value['isResSchool']);
                    $id = M('user')->add($value);
                    $password = $this->InitialPassword();
                    $userJz[] = array(
                        'className' => $value['className'],
                        'grade' => $value['grade'],
                        'name' => $relName.'家长',
                        'number' => $value['number']-1,
                        'scPrefix' => $value['scPrefix'],
                        'scId' => $value['scId'],
                        'roleId' => $this::$jZroleId,
                        'password' => $this->create_password($password,self::$password_key,self::$password_key1),
                        'InitialPassword' => $password,
                        'account' => $prefix.($value['number']-1),
                        'childId' => $id,
                        'childName' => $value['name'],
                        'gradeId' => $value['gradeId'],
                        'class' => $value['class'],
                        'createTime' => $createTime
                    );
                    $student_info[] = array(
                        'userId' => $id,
                        'scId' => $value['scId'],
                        'gradeId' => $value['gradeId'],
                        'classId' => $value['class'],
                        'name' => $relName,
                        'className' =>  $value['className'],
                        'grade' =>  $value['grade'],
                        'idCard' =>  $value['idCard'],
                        'enrolTime' => date('Y-m-d H:i:s'),
                        'createTime' =>  $value['createTime'],
                        'phone' =>  $value['phone'],
                        'sex' =>  $value['sex'],
                        'birthday' => $value['birth'],
                        'serialNumber' => $value['serialNumber'],
                        'isResSchool' => $isResSchool
                    );
                    $cen_reg_info[] = array(
                        'userId' => $id,
                        'scId' => $value['scId'],
                        'createTime' =>  $value['createTime'],
                        'cenRegType' =>  $value['hklx']
                    );
                    $school_rollinfo[] = array(
                        'userId' => $id,
                        'scId' => $value['scId'],
                        'createTime' =>  $value['createTime'],
                        'sex' => $value['sex'],
                        'admCategory' => $value['admCategory']
                    );
                    $parents_info[] = array(
                        'userId' => $id,
                        'scId' => $value['scId'],
                        'createTime' =>  $value['createTime'],
                    );
                    $other_info[] = array(
                        'userId' => $id,
                        'scId' => $value['scId'],
                        'createTime' =>  $value['createTime'],
                    );
                    $tuition_info[] = array(
                        'userId' => $id,
                        'scId' => $value['scId'],
                        'createTime' =>  $value['createTime'],
                    );
                }else{
                    $zg[] = $value;
                }
            }
            if($roleNameEn == 'xs'){
                M('user')->addAll($userJz);
                M('student_info')->addAll($student_info);
                M('cen_reg_info')->addAll($cen_reg_info);
                M('school_rollinfo')->addAll($school_rollinfo);
                M('parents_info')->addAll($parents_info);
                M('other_info')->addAll($other_info);
                M('tuition_info')->addAll($tuition_info);
            }else{
                $role = M('role')->field('roleId,roleName')->where(array('scId' => array(array('eq',0),array('eq',$scId),'or')))->select();
                $roleRel = array();
                foreach ($role as $key2 => $value2){
                    $roleRel[$value2['roleName']] = $value2['roleId'];
                }
                if($roleNameEn == 'zg'){
                    foreach ($zg as $key => $value){
                        if(isset($roleRel[$value['post']])){
                            $zg[$key]['roleId'] = $roleRel[$value['post']];
                        }else{
                            $this->returnJson(array('statu' => 3,'message' =>'请填写基本信息-权限设置中创建好的角色' ),true);
                        }
                    }
                }else{
                    foreach ($zg as $key => $value){
                        if(!$value['teachingSubjectsId']){
                            $this->returnJson(array('statu' => 3,'message' =>'请填写基本信息-科目信息中创建好的科目' ),true);
                        }
                    }
                }
                M('user')->addAll($zg);
            }
            $this->returnJson(array('statu' => 1, 'message' => '导入成功'),true);
        }
        $url = I('get.url');
        $model = M('model')->field('modelId,parentId,level,url')->select();
        $modelId = 0;
        $parentsId = 0;
        if($url){
            foreach($model as $key => $value){
                if($value['url'] == $url){
                    $modelId = $value['modelId'];
                    $parentsId = $value['parentId'];
                    $level = $value['level'];
                    break;
                }
            }
            $return = array();
            $nav = $this->returnNav($parentsId,$model);
            $navone = $this->returnNavList($modelId,$model);
            foreach($nav as $key => $value){
                $return[$value['level']] = $value['modelId'];
            }
            foreach($navone as $key => $value){
                $return[$value['level']] = $value['modelId'];
            }
            $return[$level] = $modelId;
        }else{
            $return = array();
        }
        $this->returnJson(array('statu' => 1,'data' => $return),true);
    }
    private function returnNav($parentsId,$model){
        global $returngo;
        foreach($model as $key => $value){
            if($value['modelId'] == $parentsId){
                $returngo[] = $value;
                $this->returnNav($value['parentId'],$model);
            }
        }
        return $returngo;
    }
    private function returnNavList($modelId,$model){
        global $returngogo;
        foreach($model as $key => $value){
            if($value['parentId'] == $modelId){
                $returngogo[] = $value;
                $this->returnNav($value['modelId'],$model);
                break;
            }
        }
        return $returngogo;
    }
    public function exportStudent(){
        $type = I('get.type');
        $return = $this->get_session('loginCheck',false);
        $data = M('user')->field('name,className,serialNumber,sex,phone')->where(array('scId' => $return['scId'],'roleId' => $this::$studentRoleId))->order('className')->select();
        $this::export($data,$this::getTr('studentExport'));
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
    public function getvalue(){
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
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
    }
    public function getStudentList(){ // xin zheng
        $type =  I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
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
    public function uploadUserLogo() //上次图片logo
    {
        $type =I('get.type');
        $return = $this->get_session('loginCheck',false);
        if ($type = 'uploadLogo'){
            $userId = I('post.userId');
            $url = $this::uploads('userLogo', $fileTypes = array('png', 'jpg', 'jpeg'));
            if($url){
                M('student_info')->where(array('userId' => $userId,'scId' => $return['scId']))->setField(array('logo' => $url));
                M('user')->where(array('userId' => $userId,'scId' => $return['scId']))->setField(array('logo' => $url));
                $this->returnJson(array('statu' => 1, 'message' => 'success', 'url' => urlencode($url)), true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail','url' => ''),true);
        }
    }
    public function exportExcel(){
        $type = I('get.type');
        $return = $this->get_session('loginCheck',false);
        if($type == 'jbXi'){
            $data = $this::exportExcelData('mks_student_info',$return['scId']);
            $tr = $this::getTr('mks_student_info');
            $this->export($data,$tr);
        }
        if($type == 'xjXi'){//学籍信息
            $data =$this::exportExcelData('mks_school_rollinfo',$return['scId']);
            $tr = $this::getTr('mks_school_rollinfo');
            $this->export($data,$tr);
        }
        if($type == 'hjXi'){//户籍
            $data =$this::exportExcelData('mks_cen_reg_info',$return['scId']);
            $tr = $this::getTr('mks_cen_reg_info');
            $this->export($data,$tr);
        }
        if($type == 'jzXi'){//家长
            $data = $this::exportExcelData('mks_parents_info',$return['scId']);
            $tr = $this::getTr('mks_parents_info');
            $this->export($data,$tr);
        }
        if($type == 'xfXi'){//学费信息
            $data = $this::exportExcelData('mks_tuition_info',$return['scId']);
            $tr = $this::getTr('mks_tuition_info');
            $this->export($data,$tr);
        }
        if($type == 'qtXi'){//其他信息
            $data = $this::exportExcelData('mks_other_info',$return['scId']);
            $tr = $this::getTr('mks_other_info');
            $this->export($data,$tr);
        }
    }
    public function exportTeacherOrZg(){
        $type = I('get.type');
        $return = $this->get_session('loginCheck',false);
        if($type == 'teacher'){
            $data = M('user')->where(array('scId' => $return['scId'],'roleId' => $this::$teacherRoleId))->order('teachingSubjectsId')->select();
            $this::export($data, $this::getTr('js'));
        }
        if($type == 'worker'){
            $scId = $return['scId'];
            $teacherRoleId = $this::$teacherRoleId;
            $stuentId = $this::$studentRoleId;
            $zjId = $this::$jZroleId;
            $admin = $this::$adminRoleId;
            $data = M('')->query("SELECT * FROM mks_user WHERE  roleId!= $stuentId AND roleId!= $teacherRoleId  AND roleId!= $admin AND roleId!= $zjId AND scId=$scId order by roleId");
            foreach ($data as $key => $value){
                $ori  = unserialize($value['origin']);
                $data[$key]['origin'] = $ori['province'].$ori['city'];
                $regis = unserialize($value['registerAddress']);
                $data[$key]['registerAddress'] = $regis['province'].$regis['city'].$regis['area'].$regis['detail'];
                $homeA = unserialize($value['homeAddress']);
                $data[$key]['homeAddress'] = $homeA['province'].$homeA['city'].$homeA['area'].$homeA['detail'];
                $nowAdd = unserialize($value['nowAddress']);
                $data[$key]['nowAddress'] = $nowAdd['province'].$nowAdd['city'].$nowAdd['area'].$nowAdd['detail'];
            }
            $this::export($data,$this::getTr('zg'));
        }
    }
    private function exportExcelData($table,$scId){
        $grade = I('get.grade');
        $class = I('get.class');
        $class = explode(',',$class);
        $str = '';
        $len = count($class);
        $i = 1;
        //
        foreach($class as $key => $value){
            $i++;
            if($i<=$len){
                $str = 'classId='.$value.' or '.$str;
            }else{
                $str = $str.'classId='.$value;
            }
        }
        $userAll = "SELECT userId,className,grade,name FROM mks_student_info WHERE scId=$scId and gradeId=$grade and  ($str)";
        $schoolAllUser =  M('')->query($userAll);
        $schoolAllUserCheck = array();
        foreach($schoolAllUser as $key => $value){
            $schoolAllUserCheck[$value['userId']] = $value;
        }
        $allReturn = array();
        $allReturn = M('')->query("select * from $table where scId=$scId");
        $allReturnTrue = array();
        foreach($allReturn as $key => $value){
            if(isset($schoolAllUserCheck[$value['userId']])){
                $value['className'] = $schoolAllUserCheck[$value['userId']]['className'];
                $value['grade'] = $schoolAllUserCheck[$value['userId']]['grade'];
                $value['name'] = $schoolAllUserCheck[$value['userId']]['name'];
                $allReturnTrue[] = $value;
            }
        }
        $data = $allReturnTrue;
        //
        $valueData = I('get.value');
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
        $sortData = I('get.sort');
        $sort =I('get.sortType');
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
                $zzz = array();
                foreach($returnList as $key => $value){
                    $zzz[] = $value;
                }
                $returnList = $zzz;
            }
            if($sortData == 'ascending'){
                krsort($returnList);
                $zzz = array();
                foreach($returnList as $key => $value){
                    $zzz[] = $value;
                }
                $returnList = $zzz;
            }
            $data = $returnList;
        }
        return $data;
    }
    private function getValueReturn($acc){
        $privateKeyFilePath = 'Public/key/rsa_private_key.pem';
        $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyFilePath));
        $data = '';
        openssl_private_decrypt(base64_decode($acc), $data, $privateKey);
        return $data;
    }
    private function setValueReturn($acc){
        $publicKeyFilePath = "Public/key/rsa_public_key.pem";
        $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyFilePath));
        openssl_public_encrypt($acc, $encryptData, $publicKey);
        return $encryptData;
    }
    /*用户登录*/
    public function doLogin(){
        $account = $this->getValueReturn(I('post.account'));
        $password = $this->getValueReturn(I('post.password'));
        $return = $this->checkLogin($account,$password);
        if($return['statu']){
            $navList = $return['scId'].'school'.$return['roleId'].'returndata';
            $model = json_decode($this->redis_operation($navList,0,0,3),true);
            //echo $this->redis_operation($modelName,0,0,3);
            //print_r(json_decode($this->redis_operation($modelName,0,0,3),true));
            /*$allModel = M('model')->select();
            $allTe = array();
            foreach($allModel as $key => $value){
                $allTe[$value['modelId']] = $value['url'];
            }
            $modelRe = array();
            foreach($model as $key => $value){
                if(!$value['hidden']){
                    $value['url'] = $allTe[$value['modelId']];
                    $modelRe[] = $value;
                }
            }*/
            $returnLL = array();
            foreach($model as $key => $value){
                $maxKey = $key;
                $max = 0;
                foreach($model as $key1 =>$value1){
                    if($value1['modelId']>$max){
                        $max = $value1['modelId'];
                        $maxKey = $key1;
                    }
                }
                $returnLL[] = $model[$maxKey];
                unset($model[$maxKey]);
            }
            $return['model'] =array_reverse($returnLL);
            $this->returnJson($return,true);
        }
    }
    private function dg($model){//递归
        while(isset($model[0])){
            foreach($model as $key => $vlaue){

            }
        }
    }
    public function loginOut(){
        $this->clear_session('loginCheck');
        $this->returnJson(array('statu' => 1 ,'success' => 'login out success'),true);
    }
    public function doRegister(){

    }
    //角色开始
    public function role(){
        $this->assign('model',$this->getModelListshow(0,0));
        //print_r($this->getModelListshow(0,0));
        //print_r($this->getModelListshow(0,0));
        $this->display();
    }
    //返回一级导航
    public function getOneNav(){
        $this->returnJson(M('model')->where(array('level' => 1,'modelName' => '校园管理系统'))->select(),true);
    }
    //
    public function getModelListGo(){
        $return = $this->get_session('loginCheck',false);
        $roleId = I('get.roleId');
        $modelName = $return['scId'].'school'.$roleId.'navList';
        $modelId = I('get.modelId');
        $allModel = M('model')->order('createTime')->select();
        $model = json_decode($this->redis_operation($modelName,0,0,3),true);
        //$return['allModel'] = $allModel;
        $return['userModel'] = $model;
        $return['oneModel'] = $this->getModelListPaiXu($modelId,$allModel);
        $count = count($return['oneModel']);
        foreach($allModel as $key => $value){
            if($value['modelId'] == $modelId){
                $return['oneModel'][$count] = $value;
                break;
            }
        }
        $allUser = array();
        foreach ($return['userModel'] as $key => $value){
            $allUser[$value['modelId']] = 1;
        }
        foreach($return['oneModel'] as $key => $value){
            $true =0;
            if(isset($allUser[$value['modelId']])){
                $return['oneModel'][$key]['statu'] = 1;
            }else{
                $return['oneModel'][$key]['statu'] = 0;
            }
            $return['oneModel'][$key]['ifHave'] = false;
            $return['oneModel'][$key]['checked'] = false;
        }
        //print_r($return['oneModel']);
        $returngo = $this::getModelListReturn(-1,$return['oneModel']);
        //print_r($returngo);
        $this->returnJson($returngo,true);
    }
    public function test(){
       $user = M('user')->where(array('roleId' => $this::$studentRoleId))->select();
        $data = array();
        foreach($user as $key => $value){
            $data[] = array(
                'userId' => $value['id'],
                'name' => $value['name'],
                'className' => $value['className'],
                'grade' => $value['grade'],
                'gradeId' => $value['gradeId'],
                'enrolTime' => date('Y-m-d H:i:s'),
                'scId' => $value['scId'],
                'classId' => $value['class'],
                'idCard' => $value['idCard'],
                'isResSchool' => $value['ifExtern'],
                'sex' => $value['sex'],
                'phone' => $value['phone'],
            );
        }
        $i =0;
        $xxx = array();
        foreach($data as $key => $value){
            $xxx[] = $value;
            if($i==1000){
                M('student_info')->addAll($xxx);
                $xxx = array();
                $i=0;
            }
            $i++;
        }
        M('student_info')->addAll($xxx);
    }
    private function findChild(&$arr,$id){
        $childs=array();
        foreach ($arr as $k => $v){
            if($v['parentId']== $id){
                $childs[]=$v;
            }
        }
        return $childs;
    }
    private function build_tree($rows,$root_id){
        $childs=$this->findChild($rows,$root_id);
        if(empty($childs)){
            return null;
        }
        foreach ($childs as $k => $v){
            $rescurTree=$this->build_tree($rows,$v['modelId']);
            if( null != $rescurTree){
                $childs[$k]['childs']=$rescurTree;
            }
        }
        return $childs;
    }
    //递归得到当前modelId下面的所有数组
    private function getModelListPaiXu($modelId,$allModel){
        global  $dataReturn;
        foreach($allModel as $key => $value){
            if($value['parentId'] == $modelId){
                $dataReturn[] = $value;
                unset($allModel[$key]);
                $this::getModelListPaiXu($value['modelId'],$allModel);
            }
        }
        return $dataReturn;
    }
    //创建角色接口
    public function createRole(){
        $scId = $this->get_session('loginCheck',false);
        $scId = $scId['scId'];
        if(isset($_POST['roleName'])){
            if(M('role')->where(array('scId' => $scId,'roleName' => I('post.roleName')))->find()){
                $this->returnJson(array('statu' => 2, 'message' => '该角色已经存在'),true);
            }
            $reg = M('role')->add(array('roleName' => I('post.roleName'),'scId' => $scId,'createTime' => date('Y-m-d H:i:s')));
            if($reg){
                $this->returnJson(array('statu' => 1, 'message' => 'add success'),true);
            }
            else{
                $this->returnJson(array('statu' => 0, 'message' => 'add fail'),true);
            }
        }
    }
    //得到角色表
    public function getRoleList(){
        $scId = $this->get_session('loginCheck',false);
        $scId = $scId['scId'];
        $this->returnJson(M('role')->where(array('scId' => array(array('eq',0),array('eq',$scId),'or')))->select(),1);
    }
    public function model(){
        $this->display();
    }
    //角色结束
    //model开始
    //创建权限
    public function getRoleListUser(){
        $type = I('get.type');
        if($type = 'list'){
            $scId = $this->get_session('loginCheck',false);
            $scId = $scId['scId'];
            $data = M('role')->where(array('scId' => array(array('eq',0),array('eq',$scId),'or')))->select();
            foreach($data as $key => $value){
                if($value['roleId'] == $this::$teacherRoleId || $value['roleId'] == $this::$studentRoleId  || $value['roleId'] == $this::$jZroleId || $value['roleId'] == $this::$adminRoleId){
                    unset($data[$key]);
                }
            }
            $array = array();
            foreach($data as $key => $value){
                $array[] = $value;
            }

            $this->returnJson(array('data' =>$array,'statu' =>1,'message' => 1),1);
        }
    }
    public function  createModel(){
        if($modelName = I('post.oneNav')){
            M('model')->add(array('modelName' => $modelName,'createTime' => date('Y-m-d H:i:s'),'parentId' => 0 ,'level' => 1));
        }
        if(isset($_POST['towNav']) &&isset($_POST['towNavTo']) ){
            $modelName = I('post.towNav');
            $bt = I('post.towNavTo');
            M('model')->add(array('modelName' => $modelName,'createTime' => date('Y-m-d H:i:s'),'parentId' => $bt,'level' => 2,'userId' => session('userId')));
        }
        if(isset($_POST['threeNav'])&& isset($_POST['threeNavTo'])&& isset($_POST['threecontroller']) && isset($_POST['threefunction'])){
            $function = I('post.threefunction');
            $modelName = I('post.threeNav');
            $bt = I('post.threeNavTo');
            $controller = I('post.threecontroller');
            M('model')->add(array('modelName' => $modelName,'level' => 3,'createTime' => date('Y-m-d H:i:s'),'controller' => $controller,'function' => $function,'userId' => session('userId'),'parentId' => $bt));
        }
        if(isset($_POST['fourNav'])&& isset($_POST['fourNavTo'])&&isset($_POST['fourcontroller']) && isset($_POST['fourfunction'])){
            $modelName =I('post.fourNav');
            $bt = I('post.fourNavTo');
            $controller = I('post.fourcontroller');
            $function = I('post.fourfunction');
            M('model')->add(array('modelName' => $modelName,'level' => 4,'createTime' => date('Y-m-d H:i:s'),'controller' => $controller,'userId' => session('userId'),'function' => $function,'parentId' => $bt));
        }
        if(isset($_POST['fiveNav']) && isset($_POST['fiveNavTo']) && isset($_POST['fivetype'])){
            $modelName = I('post.fiveNav');
            $bt = I('post.fiveNavTo');
            $type = I('post.fivetype');
            $parent = M('model')->field('controller,function')->where(array('modelId' => $bt))->find();
            M('model')->add(array('controller' => $parent['controller'],'level' => 5,'function' => $parent['function'],'userId' => session('userId'),'modelName' => $modelName,'type' => $type,'createTime' => date('Y-m-d H:i:s'),'parentId' => $bt));
        }
        $data = M('model')->select();
        $dataRetrun = array();
        foreach($data as $key => $value){
            $dataRetrun[] = array(
                'modelId' => $value['modelId'],
                'modelName' => $value['modelName'],
                'controller' => $value['controller'],
                'function' => $value['function'],
                'createTime' => $value['createTime'],
                'userId' => $value['userId'],
                'parentId' => $value['parentId'],
                'type' => $value['type'],
                'level' => $value['level'],
                'scId' => $value['scId'],
                'hidden' => $value['hidden']
            );
        }
        //$scId = $this->get_session('loginCheck',false);
        //$qxModelName = $scId['scId'].'school'.$this::$adminRoleId;
        //$modelRetrun = $scId['scId'].'school'.$this::$adminRoleId.'returndata';
        //$modelTetrunData = $this->getModelListReturn(-1,$dataRetrun);
        //$this->redis_operation($qxModelName,json_encode($dataRetrun),0,2);
        //$this->redis_operation($modelRetrun,json_encode($modelTetrunData),0,2);
    }
    //得到所有权限组
    public function getModelList(){
        $this->returnJson($this->getModelListReturn(0,0),true);
    }
    //得到子model
    public function getChildModel(){
        $modelId =I('post.modelId');
        $data = M('model')->where(array('parentId' => $modelId))->select();
        $this->returnJson($data,true);
    }
    private function getModelListshow($roleId,$data){
        $roleId = $roleId;
        $scId = $this->get_session('loginCheck',false);
        $scId = $scId['scId'];
        $oneNav = array();
        if($roleId == 0 || $roleId == $this::$adminRoleId){
            $oneNav = M('model')->select();
        }
        if($roleId>=1 && $roleId!=$this::$adminRoleId){
            $oneNav = M()->query("SELECT mks_model.controller as controller,mks_model.modelName as modelName, mks_model.`function` as function,mks_model.modelId as modelId,mks_model.type as type,mks_model.parentId as parentId,mks_model.`level` as level FROM mks_role_model JOIN mks_model ON mks_role_model.modelId = mks_model.modelId where roleId = $roleId");
            $modelName = 'school'.$roleId;
        }
        if($roleId == -1){
            $oneNav = $data;
        }
        $data = array();
        foreach($oneNav as $key => $value){
            if($value['level'] == 1){
                $data[] = $value;
                unset($oneNav[$key]);
            }
        }
        foreach($oneNav as $key => $value){
            if($value['level'] == 2){
                foreach($data as $key1 => $value1){
                    if($value1['modelId'] == $value['parentId']){
                        $data[$key1][] = $value;
                        unset($oneNav[$key]);
                    }
                }
            }
        }
        foreach($oneNav as $key => $value){
            if($value['level'] == 3){
                foreach($data as $key1 => $value1){
                    foreach($value1 as $key2 => $value2){
                        if($value2['modelId'] == $value['parentId']){
                            $data[$key1][$key2][] = $value;
                            unset($oneNav[$key]);
                        }
                    }
                }
            }
        }
        foreach($oneNav as $key => $value){
            if($value['level'] == 4){
                foreach($data as $key1 => $value1){
                    foreach($value1 as $key2 => $value2){
                        foreach($value2 as $key3 => $value3) {
                            if ($value3['modelId'] == $value['parentId']) {
                                $data[$key1][$key2][$key3][] = $value;
                                unset($oneNav[$key]);
                            }
                        }
                    }
                }
            }
        }
        foreach($oneNav as $key => $value){
            if($value['level'] == 5){
                foreach($data as $key1 => $value1){
                    foreach($value1 as $key2 => $value2){
                        foreach($value2 as $key3 => $value3) {
                            foreach($value3 as $key4 => $value4) {
                                if ($value4['modelId'] == $value['parentId']) {
                                    $data[$key1][$key2][$key3][$key4][] = $value;
                                    unset($oneNav[$key]);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $data;
        //print_r($data);
        //$this->returnJson($data,1);
    }

    //分离出不同角色的规范权限组
    private function getModelListReturn($roleId,$data){
        $roleId = $roleId;
        $scId = $this->get_session('loginCheck',false);
        $scId = $scId['scId'];
        $oneNav = array();
        if($roleId == 0 || $roleId == $this::$adminRoleId){
            $oneNav = M('model')->select();
        }
        if($roleId>=1 && $roleId!=$this::$adminRoleId){
            $oneNav = M()->query("SELECT mks_model.controller as controller,mks_model.modelName as modelName, mks_model.`function` as function,mks_model.modelId as modelId,mks_model.type as type,mks_model.parentId as parentId,mks_model.`level` as level FROM mks_role_model JOIN mks_model ON mks_role_model.modelId = mks_model.modelId where roleId = $roleId and scId=$scId");
            $modelName = 'school'.$roleId;
        }
        if($roleId == -1){
            $oneNav = $data;
        }
        $data = array();
        foreach($oneNav as $key => $value){
            if($value['level'] == 1){
                $data[] = $value;
            }
        }
        foreach($oneNav as $key => $value){
            if($value['level'] == 2){
                foreach($data as $key1 => $value1){
                    if($value1['modelId'] == $value['parentId']){
                        $data[$key1]['child'][] = $value;
                    }
                }
            }
        }
        foreach($oneNav as $key => $value){
            if($value['level'] == 3){
                foreach($data as $key1 => $value1){
                    foreach($value1['child'] as $key2 => $value2){
                        if($value2['modelId'] == $value['parentId']){
                            $data[$key1]['child'][$key2]['child'][] = $value;
                        }
                    }
                }
            }
        }
        foreach($oneNav as $key => $value){
            if($value['level'] == 4){
                foreach($data as $key1 => $value1){
                    foreach($value1['child'] as $key2 => $value2){
                        foreach($value2['child'] as $key3 => $value3) {
                            if ($value3['modelId'] == $value['parentId']) {
                                $data[$key1]['child'][$key2]['child'][$key3]['child'][] = $value;
                            }
                        }
                    }
                }
            }
        }
        foreach($oneNav as $key => $value){
            if($value['level'] == 5){
                foreach($data as $key1 => $value1){
                    foreach($value1['child'] as $key2 => $value2){
                        foreach($value2['child'] as $key3 => $value3) {
                            foreach($value3['child'] as $key4 => $value4) {
                                if ($value4['modelId'] == $value['parentId']) {
                                    $data[$key1]['child'][$key2]['child'][$key3]['child'][$key4]['child'][] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $data;
        //print_r($data);
        //$this->returnJson($data,1);
    }
    //找到分级的nav
    public function searchNav(){
        $this->returnJson(M('model')->where(array('level' => I('post.level')))->select(),1);
    }
    //model结束


    //跟新角色权限
    private function updateRoleModel($roleId){
        $modelName = 'school'.$roleId;
        $data = $this->getModelListReturn($roleId,0);
        if($this->redis_operation($modelName,json_encode($data),0,2)){
            return true;
        }
    }
    //得到叫色权限Id
    public function  getRoleModelList(){
        $roleId = I('post.roleId');
        $scId = $this->get_session('loginCheck',false);
        $data = M('role_model')->field('modelId')->where(array('roleId' => $roleId,'scId' => $scId['scId']))->select();
        $this->returnJson($data,1);
    }
    public function createRoleModelGo(){
        $scId = $this->get_session('loginCheck',false);
        $list = I('post.modelId');
        $roleId = I('post.roleId');
        if($roleId&& $list){
            $model = M('role_model');
            $re1 = $model->where(array('roleId' => $roleId,'scId' => $scId['scId']))->delete();
            $re2 = 1;
            $listData = array();
            $date = date('Y-m-d H:i:s');
            $modelAll = M('model')->order('createTime')->select();
            $listRel = array();
            foreach($list as $key => $value){
                $listRel[$value] = 1;
            }
            $list = array();
            foreach($modelAll as $key => $value){
                if(isset($listRel[$value['modelId']])){
                    $list[] = $value['modelId'];
                }
            }
            foreach($list as $key => $value){
                $listData[] =array('roleId' => (int)$roleId,'scId' => $scId['scId'],'modelId' => (int)$value,'createTime' => $date);
            }
            $model->addAll($listData);
            $qxModel = array();
            $modelQx = array();
            $nav_list = array();
            foreach($list as $key1 => $value1){
                foreach($modelAll as $key => $value){
                    if($value1 == $value['modelId']){
                        $modelQx[$value['controller']][$value['function']][$value['type']] = 1;
                        $qxModel[] = array(
                            'modelId' => $value['modelId'],
                            'modelName' => $value['modelName'],
                            'controller' => $value['controller'],
                            'function' => $value['function'],
                            'parentId' => $value['parentId'],
                            'type' => $value['type'],
                            'level' => $value['level'],
                            'hidden' => $value['hidden'],
                            'url' => $value['url'],
                            'logoUrl' => $value['logoUrl'],
                            'urlImg' => $value['urlImg'],
                            'ifHave' => 0
                        );
                        if($value['hidden'] == 0){
                            $nav_list[] = array(
                                'modelId' => $value['modelId'],
                                'modelName' => $value['modelName'],
                                'controller' => $value['controller'],
                                'function' => $value['function'],
                                'parentId' => $value['parentId'],
                                'type' => $value['type'],
                                'level' => $value['level'],
                                'hidden' => $value['hidden'],
                                'url' => $value['url'],
                                'logoUrl' => $value['logoUrl'],
                                'urlImg' => $value['urlImg'],
                                'ifHave' => 0
                            );
                        }
                        break;
                    }
                }
            }
            $modelNameRetrun = $scId['scId'].'school'.$roleId.'returndata';
            $navList = $scId['scId'].'school'.$roleId.'navList';
            $qxModelName = $scId['scId'].'school'.$roleId;
            $model_return = $this->build_tree($nav_list,27);
            $this->redis_operation($modelNameRetrun,json_encode($model_return),0,2);
            $this->redis_operation($qxModelName,json_encode($modelQx),0,2);
            $this->redis_operation($navList,json_encode($qxModel),0,2);
            if($re2){
                if($this->updateRoleModel($roleId)){
                    $this->returnJson(array('statu' => 1,'message' => 'success'),true);
                }else{
                    $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
                }
            }else{
                $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
            }
        }
        /*$aaaaaddddddd = array();
        $createTime =  date('Y-m-d H:i:s');
        $all_model_id = M('model')->field('modelId')->order('createTime')->select();
        $modelList = array();
        foreach($all_model_id as $key => $value){
            foreach($modelId as $key1 => $value1){
                if($value['modelId'] == $value1){
                    $modelList[] = $value1;
                    break;
                }
            }
        }
        $modelId = $modelList;
        foreach($modelId as $key => $value){
            if(M('role_model')->where(array('roleId' => $roleId,'scId' => $scId['scId'],'modelId' =>$value))->find()){
                if($value == 424){
                    $this->returnJson(array('statu' => 0, 'message' => '权限设置不能删除'), true);
                }
                M('role_model')->where(array('roleId' => $roleId,'scId' => $scId['scId'],'modelId' =>$value))->delete();
            }else {
                $aaaaaddddddd[] = array(
                    'roleId' => $roleId,
                    'scId' => $scId['scId'],
                    'modelId' => $value,
                    'createTime' =>$createTime
                );
            }
        }
        M('role_model')->addAll($aaaaaddddddd);
        $newModel = M('role_model')->where(array('roleId' => $roleId,'scId' => $scId['scId']))->select();
        $allModel = M('model')->order('createTime')->select();
        $allmodelList = array();
        foreach ($allModel as $key => $value) {
            $value['ifHave'] = 0;
            $allmodelList[$value['modelId']] = $value;
        }
        $relModel = array();
        $nav_list = array();
        foreach ($newModel as $key => $value) {
            if (isset($allmodelList[$value['modelId']])) {
                $relModel[] = $allmodelList[$value['modelId']];
                if (!$allmodelList[$value['modelId']]['hidden']) {
                    $nav_list[] = $allmodelList[$value['modelId']];
                }
            }
        }
        $modelQx = array();
        foreach ($relModel as $key => $value) {
            $modelQx[$value['controller']][$value['function']][$value['type']] = 1;
        }
        $qxlist = $scId['scId'] . 'school' . $roleId;
        $qxModelName = $scId['scId'] . 'school' . $roleId . 'navList';
        //
        $modelNameRetrun = $scId['scId'] . 'school' . $roleId . 'returndata';
        $model_return = $this->build_tree($nav_list, 27);
        $this->redis_operation($modelNameRetrun, json_encode($model_return), 0, 2);
        //
        $this->redis_operation($qxlist, json_encode($modelQx), 0, 2);
        $this->redis_operation($qxModelName, json_encode($relModel), 0, 2);
        $this->returnJson(array('statu' => 1, 'message' => 'delete success'), true);
        */
    }
    private function getClass($scId){
        $class = M('class')->where(array('scId' => $scId))->select();
        $grade= M('grade')->where(array('scId' => $scId))->select();
        $classre = array();
        $gradeRe = array();
        foreach($grade as $key => $value){
            $gradeRe[$value['gradeid']] = $value['name'];
        }
        foreach($class as $key => $value){
            $gradeName = $gradeRe[$value['grade']];
            $classre[$gradeName][$value['classname']] = array(
                'classId' => $value['classid'],
                'gradeId' => $value['grade']
            );
        }
        return $classre;
    }
    private function getClass1($scId){
        $class = M('class')->where(array('scId' => $scId))->select();
        $grade= M('grade')->where(array('scId' => $scId))->select();
        $classre = array();
        $gradeRe = array();
        foreach($grade as $key => $value){
            $gradeRe[$value['gradeid']] = $value['name'];
        }
        foreach($class as $key => $value){
            $gradeName = $gradeRe[$value['grade']];
            $classre[$value['grade']][$value['classname']] = array(
                'classId' => $value['classid'],
                'gradeId' => $value['grade'],
                'gradeName' =>$gradeName,
            );
        }
        return $classre;
    }
    //创建用户权限
    public function createRoleModel(){
        if(isset($_POST['roleId'])&& isset($_POST['list'])){
            $list = I('post.list');
            $roleId = I('post.roleId');
            $scId = $this->get_session('loginCheck',false);
            $model = M('role_model');
            $re1 = $model->where(array('roleId' => $roleId,'scId' => $scId['scId']))->delete();
            $re2 = 1;
            $listData = array();
            $date = date('Y-m-d H:i:s');
            foreach($list as $key => $value){
                $listData[] =array('roleId' => (int)$roleId,'scId' => $scId['scId'],'modelId' => (int)$value,'createTime' => $date);
            }
            $model->addAll($listData);
            $modelAll = M('model')->order('createTime')->select();
            $qxModel = array();
            $modelQx = array();
            $nav_list = array();
            foreach($list as $key1 => $value1){
                foreach($modelAll as $key => $value){
                    if($value1 == $value['modelId']){
                        $modelQx[$value['controller']][$value['function']][$value['type']] = 1;
                        $qxModel[] = array(
                            'modelId' => $value['modelId'],
                            'modelName' => $value['modelName'],
                            'controller' => $value['controller'],
                            'function' => $value['function'],
                            'parentId' => $value['parentId'],
                            'type' => $value['type'],
                            'level' => $value['level'],
                            'hidden' => $value['hidden'],
                            'url' => $value['url'],
                            'logoUrl' => $value['logoUrl'],
                            'urlImg' => $value['urlImg'],
                            'ifHave' => 0
                        );
                        if($value['hidden'] == 0){
                            $nav_list[] = array(
                                'modelId' => $value['modelId'],
                                'modelName' => $value['modelName'],
                                'controller' => $value['controller'],
                                'function' => $value['function'],
                                'parentId' => $value['parentId'],
                                'type' => $value['type'],
                                'level' => $value['level'],
                                'hidden' => $value['hidden'],
                                'url' => $value['url'],
                                'logoUrl' => $value['logoUrl'],
                                'urlImg' => $value['urlImg'],
                                'ifHave' => 0
                            );
                        }
                        break;
                    }
                }
            }
            $modelNameRetrun = $scId['scId'].'school'.$roleId.'returndata';
            $navList = $scId['scId'].'school'.$roleId.'navList';
            $qxModelName = $scId['scId'].'school'.$roleId;
            $model_return = $this->build_tree($nav_list,27);
            $this->redis_operation($modelNameRetrun,json_encode($model_return),0,2);
            $this->redis_operation($qxModelName,json_encode($modelQx),0,2);
            $this->redis_operation($navList,json_encode($qxModel),0,2);
            if($re2){
                if($this->updateRoleModel($roleId)){
                    $this->returnJson(array('statu' => 1,'message' => 'success'),true);
                }else{
                    $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
                }
            }else{
                $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
            }
        }
    }
    private function getNavList(){

    }
    //modelrole结束
    //是否操作成功判断
    private function successOrtrue($reg,$success,$fail){
        if($reg){
            return $success;
        }
        return $fail;
    }
    //递归求导航
    private function studentXi($scId,$model){
        $value = I('get.value');
        $sort = I('get.sort');
        $sortType = I('get.sortType');
        $dataReturn = $this->pageListSql($scId,$model);
        return $dataReturn;
    }
    private function getWorkPage($scId,$table,$valueData = 0,$strLike ='',$sort = 0,$sortString =''){
        $teacherRole = $this::$teacherRoleId;
        $jz = $this::$jZroleId;
        $studentRole = $this::$studentRoleId;
        $adminRole = $this::$adminRoleId;
        $userSql = "SELECT * FROM $table WHERE roleId!=$teacherRole AND roleId!=$studentRole AND roleId!=$adminRole AND roleId!=$jz AND scId=$scId order by createTime DESC";
        /*foreach($data as $key => $value){
            $data[$key]['maxpage'] = $maxpage;
            $data[$key]['page'] = $page;
        }*/
        $userSql = M('user')->query($userSql);
        foreach($userSql as $key => $value){
            unset($userSql[$key]['password']);
            unset($userSql[$key]['InitialPassword']);
            unset($userSql[$key]['account']);
            unset($userSql[$key]['scPrefix']);
            unset($userSql[$key]['number']);
            $userSql[$key]['origin'] = unserialize($userSql[$key]['origin']);
            $userSql[$key]['registerAddress'] = unserialize($userSql[$key]['registerAddress']);
            $userSql[$key]['homeAddress'] = unserialize($userSql[$key]['homeAddress']);
            $userSql[$key]['nowAddress'] = unserialize($userSql[$key]['nowAddress']);
        }

        //
        $data = $userSql;
        $valueData = I('get.value');
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
        $sortData = I('get.sort');
        $sort =I('get.sortType');
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
                $zzz = array();
                foreach($returnList as $key => $value){
                    $zzz[] = $value;
                }
                $returnList = $zzz;
            }
            if($sortData == 'ascending'){
                krsort($returnList);
                $zzz = array();
                foreach($returnList as $key => $value){
                    $zzz[] = $value;
                }
                $returnList = $zzz;
            }
            $data = $returnList;
        }
        $page = I('get.page');
        $pageSize = I('get.pageSize');
        $maxPage = ceil(count($data)/$pageSize);
        if($page>$maxPage){
            $page = $maxPage;
        }
        if($page<1){
            $page = 1;
        }
        $start = ($page-1)*$pageSize;
        $end = ($page)*$pageSize;
        $return = array();
        $allList = count($data);
        for($i = $start ; $i < $end; $i++){
            if(isset($data[$i])){
                /*if($data[$i]['grade']){
                }else{
                }*/
                $return[] = $data[$i];
            }
        }
        $data = array();
        //
        $data['data'] =  $return;
        $data['maxpage'] = $maxPage;
        $data['page'] = $page;
        $data['count'] = (int)$allList;
        //$data['maxpage']['maxpage'] = $maxpage;
        // $data['page']['page'] = $page;
        return $data;
    }
    private function getWorkerPassword($scId,$table,$valueData = 0,$strLike ='',$sort = 0,$sortString =''){
        $teacherRole = $this::$teacherRoleId;
        $jz = $this::$jZroleId;
        $studentRole = $this::$studentRoleId;
        $adminRole = $this::$adminRoleId;
        if($valueData == 0 && $sort==0){
            $allcount = "SELECT count(id) as count FROM $table WHERE roleId!=$teacherRole AND roleId!=$studentRole AND roleId!=$adminRole AND roleId!=$jz AND scId=$scId";
        }
        if($valueData!=0 && $sort!=0){
            $allcount = "SELECT count('id') as count FROM $table WHERE roleId!=$teacherRole AND roleId!=$studentRole AND roleId!=$adminRole AND roleId!=$jz AND scId=$scId ".$strLike.$sortString;
        }
        if($valueData!=0 && $sort==0){
            $allcount = "SELECT count('id') as count FROM $table WHERE roleId!=$teacherRole AND roleId!=$studentRole AND roleId!=$adminRole AND roleId!=$jz AND scId=$scId ".$strLike;
        }
        if($valueData==0 && $sort!=0){
            $allcount = "SELECT count('id') as count FROM $table WHERE roleId!=$teacherRole AND roleId!=$studentRole AND roleId!=$adminRole AND roleId!=$jz AND scId=$scId  $sortString";
        }
        $allList = M('user')->query($allcount);
        $allList = $allList[0]['count'];
        $pagesize = I('get.pageSize');
        $maxpage = ceil($allList/$pagesize);
        $page = I('get.page');
        if($page <= 1){
            $page = 1;
        } else if($page>=$maxpage){
            $page = $maxpage;
        } else{
            $page = I('get.page');
        }
        if($maxpage==0){
            $maxpage =1;
        }
        $startpage = ($page-1)*$pagesize;
        if($valueData == 0 && $sort==0){
            $userSql = "SELECT id,account,InitialPassword,name,phone,state,post FROM mks_user WHERE roleId!=$teacherRole AND roleId!=$studentRole AND roleId!=$adminRole AND roleId!=$jz AND scId=$scId limit $startpage,$pagesize";
        }
        if($valueData!=0 && $sort!=0){
            $userSql = "SELECT id,account,InitialPassword,name,phone,state,post FROM $table WHERE scId=$scId and roleId!=$teacherRole AND roleId!=$studentRole AND roleId!=$jz AND roleId!=$adminRole  $strLike $sortString LIMIT $startpage,$pagesize";
        }
        if($valueData!=0 && $sort==0){
            $userSql = "SELECT id,account,InitialPassword,name,phone,state,post FROM $table WHERE scId=$scId and roleId!=$teacherRole AND roleId!=$studentRole AND roleId!=$jz AND roleId!=$adminRole  $strLike LIMIT $startpage,$pagesize";
        }
        if($valueData==0 && $sort!=0){
            $userSql = "SELECT id,account,InitialPassword,name,phone,state,post FROM $table WHERE scId=$scId and roleId!=$teacherRole AND roleId!=$studentRole AND roleId!=$jz AND roleId!=$adminRole   $sortString LIMIT $startpage,$pagesize";
        }
        /*foreach($data as $key => $value){
            $data[$key]['maxpage'] = $maxpage;
            $data[$key]['page'] = $page;
        }*/
        $data['data'] =  M('user')->query($userSql);
        $data['maxpage'] = $maxpage;
        $data['page'] = $page;
        $data['count'] = (int)$allList;
        $data['tr'] = array(
            '0' => array(
                'en' => 'account',
                'zh' => '用户名'
            ),
            '1' => array(
                'en' => 'InitialPassword',
                'zh' => '初始密码'
            ),  '2' => array(
                'en' => 'name',
                'zh' => '名字'
            ), '3' => array(
                'en' => 'phone',
                'zh' => '手机'
            ), '4' => array(
                'en' => 'state',
                'zh' => '使用状态'
            ), '5' => array(
                'en' => 'post',
                'zh' => '岗位'
            )
        );
        //$data['maxpage']['maxpage'] = $maxpage;
        // $data['page']['page'] = $page;
        return $data;
    }
    private function  pageListPassword($scId,$table,$roleId,$valueData = 0,$strLike ='',$sort = 0,$sortString =''){
        $i = 1;
        //获取当前页数
        $teacherRoleId = $roleId;
        if($valueData == 0 && $sort==0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId";
        }
        if($valueData!=0 && $sort!=0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId  ".$strLike.$sortString;
        }
        if($valueData!=0 && $sort==0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId   ".$strLike;
        }
        if($valueData==0 && $sort!=0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $sortString";
        }
        $allList = M('user')->query($allcount);
        $allList = $allList[0]['count'];
        $pagesize = I('get.pageSize');
        $maxpage = ceil($allList/$pagesize);
        $page = I('get.page');
        if($page <= 1){
            $page = 1;
        } else if($page>=$maxpage){
            $page = $maxpage;
        } else{
            $page = I('get.page');
        }
        if($maxpage==0){
            $maxpage =1;
        }
        $startpage = ($page-1)*$pagesize;
        if($valueData == 0 && $sort==0){
            $userSql = "SELECT id,account,InitialPassword,name,phone,state,teachingSubjects FROM $table WHERE scId=$scId and roleId = $teacherRoleId  LIMIT $startpage,$pagesize";
        }
        if($valueData!=0 && $sort!=0){
            $userSql = "SELECT id,account,InitialPassword,name,phone,state,teachingSubjects FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $strLike $sortString LIMIT $startpage,$pagesize";
        }
        if($valueData!=0 && $sort==0){
            $userSql = "SELECT id,account,InitialPassword,name,phone,state,teachingSubjects FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $strLike LIMIT $startpage,$pagesize";
        }
        if($valueData==0 && $sort!=0){
            $userSql = "SELECT id,account,InitialPassword,name,phone,state,teachingSubjects FROM $table WHERE scId=$scId and roleId = $teacherRoleId   $sortString LIMIT $startpage,$pagesize";
        }
        /*foreach($data as $key => $value){
            $data[$key]['maxpage'] = $maxpage;
            $data[$key]['page'] = $page;
        }*/
        $data['data'] =  M('user')->query($userSql);
        $data['maxpage'] = $maxpage;
        $data['count'] = (int)$allList;
        $data['page'] = $page;
        $data['tr'] = array(
            '0' => array(
                'en' => 'account',
                'zh' => '用户名'
            ),
            '1' => array(
                'en' => 'InitialPassword',
                'zh' => '初始密码'
            ),  '2' => array(
                'en' => 'name',
                'zh' => '姓名'
            ),  '3' => array(
                'en' => 'teachingSubjects',
                'zh' => '任教科目'
            ),
            '4' => array(
                'en' => 'phone',
                'zh' => '手机'
            ), '5' => array(
                'en' => 'state',
                'zh' => '使用状态'
            )
        );
        //$data['maxpage']['maxpage'] = $maxpage;
        // $data['page']['page'] = $page;
        return $data;
    }
    private function  studentPageListPassword($scId,$table,$roleId,$valueData = 0,$strLike ='',$sort = 0,$sortString =''){
        $i = 1;
        //获取当前页数
        $teacherRoleId = $roleId;
        if($valueData == 0 && $sort==0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId";
        }
        if($valueData!=0 && $sort!=0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId  ".$strLike.$sortString;
        }
        if($valueData!=0 && $sort==0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId   ".$strLike;
        }
        if($valueData==0 && $sort!=0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $sortString";
        }
        $allList = M('user')->query($allcount);
        $allList = $allList[0]['count'];
        $pagesize = I('get.pageSize');
        $maxpage = ceil($allList/$pagesize);
        $page = I('get.page');
        if($page <= 1){
            $page = 1;
        } else if($page>=$maxpage){
            $page = $maxpage;
        } else{
            $page = I('get.page');
        }
        if($maxpage==0){
            $maxpage =1;
        }
        $startpage = ($page-1)*$pagesize;
        if($valueData == 0 && $sort==0){
            $userSql = "SELECT id,grade,className,account,InitialPassword,name,phone,state FROM $table WHERE scId=$scId and roleId = $teacherRoleId  LIMIT $startpage,$pagesize";
        }
        if($valueData!=0 && $sort!=0){
            $userSql = "SELECT id,grade,className,account,InitialPassword,name,phone,state FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $strLike $sortString LIMIT $startpage,$pagesize";
        }
        if($valueData!=0 && $sort==0){
            $userSql = "SELECT id,grade,className,account,InitialPassword,name,phone,state FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $strLike LIMIT $startpage,$pagesize";
        }
        if($valueData==0 && $sort!=0){
            $userSql = "SELECT id,grade,className,account,InitialPassword,name,phone,state FROM $table WHERE scId=$scId and roleId = $teacherRoleId   $sortString LIMIT $startpage,$pagesize";
        }
        /*foreach($data as $key => $value){
            $data[$key]['maxpage'] = $maxpage;
            $data[$key]['page'] = $page;
        }*/
        $returnData =  M('user')->query($userSql);
        foreach($returnData as $key => $value){
            $returnData[$key]['grade'] = $this::gradeToZhong($returnData[$key]['grade']);
        }
        $data['data'] =  $returnData;
        $data['maxpage'] = $maxpage;
        $data['count'] = (int)$allList;
        $data['page'] = $page;
        $data['tr'] = array(
            '0' => array(
                'en' => 'grade',
                'zh' => '年级'
            ),
            '1' => array(
                'en' => 'className',
                'zh' => '班级'
            ),  '2' => array(
                'en' => 'name',
                'zh' => '名字'
            ),
            '3' => array(
                'en' => 'account',
                'zh' => '学生用户名'
            ),
            '4' => array(
                'en' => 'InitialPassword',
                'zh' => '初始密码'
            ),
            '5' => array(
                'en' => 'phone',
                'zh' => '手机'
            ),
            '6' => array(
                'en' => 'state',
                'zh' => '使用状态'
            )
        );
        //$data['maxpage']['maxpage'] = $maxpage;
        // $data['page']['page'] = $page;
        return $data;
    }
    private function  jZPageListPassword($scId,$table,$roleId,$valueData = 0,$strLike ='',$sort = 0,$sortString =''){
        $i = 1;
        //获取当前页数
        $teacherRoleId = $roleId;
        if($valueData == 0 && $sort==0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId";
        }
        if($valueData!=0 && $sort!=0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId  ".$strLike.$sortString;
        }
        if($valueData!=0 && $sort==0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId   ".$strLike;
        }
        if($valueData==0 && $sort!=0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $sortString";
        }
        $allList = M('user')->query($allcount);
        $allList = $allList[0]['count'];
        $pagesize = I('get.pageSize');
        $maxpage = ceil($allList/$pagesize);
        $page = I('get.page');
        if($page <= 1){
            $page = 1;
        } else if($page>=$maxpage){
            $page = $maxpage;
        } else{
            $page = I('get.page');
        }
        if($maxpage==0){
            $maxpage =1;
        }
        $startpage = ($page-1)*$pagesize;
        if($valueData == 0 && $sort==0){
            $userSql = "SELECT id,grade,className,account,InitialPassword,name,phone,childName,state FROM $table WHERE scId=$scId and roleId = $teacherRoleId  LIMIT $startpage,$pagesize";
        }
        if($valueData!=0 && $sort!=0){
            $userSql = "SELECT id,grade,className,account,InitialPassword,name,phone,childName,state FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $strLike $sortString LIMIT $startpage,$pagesize";
        }
        if($valueData!=0 && $sort==0){
            $userSql = "SELECT id,grade,className,account,InitialPassword,name,phone,childName,state FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $strLike LIMIT $startpage,$pagesize";
        }
        if($valueData==0 && $sort!=0){
            $userSql = "SELECT id,grade,className,account,InitialPassword,name,phone,childName,state FROM $table WHERE scId=$scId and roleId = $teacherRoleId   $sortString LIMIT $startpage,$pagesize";
        }
        /*foreach($data as $key => $value){
            $data[$key]['maxpage'] = $maxpage;
            $data[$key]['page'] = $page;
        }*/
        $returnData =  M('user')->query($userSql);
        foreach($returnData as $key => $value){
            $returnData[$key]['grade'] = $this::gradeToZhong($returnData[$key]['grade']);
        }
        $data['data'] =  $returnData;
        $data['maxpage'] = $maxpage;
        $data['count'] = (int)$allList;
        $data['page'] = $page;
        $data['tr'] = array(
            '0' => array(
                'en' => 'grade',
                'zh' => '年级'
            ),
            '1' => array(
                'en' => 'className',
                'zh' => '班级'
            ),  '2' => array(
                'en' => 'childName',
                'zh' => '学生姓名'
            ),
            '3' => array(
                'en' => 'account',
                'zh' => '家长用户名'
            ),
            '4' => array(
                'en' => 'InitialPassword',
                'zh' => '初始密码'
            ),
            '5' => array(
                'en' => 'phone',
                'zh' => '手机'
            ),
            '6' => array(
                'en' => 'state',
                'zh' => '使用状态'
            )
        );
        //$data['maxpage']['maxpage'] = $maxpage;
        // $data['page']['page'] = $page;
        return $data;
    }
    public function userPassword(){
        $type = I('get.type');
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        $userRoleId = $session['roleId'];
        if($type == 'getUserType'){
            $array = array(
                0 => array(
                    'name' => '职工',
                    'nameId' => 1
                ),
                 1 => array(
                'name' => '教师',
                'nameId' => 2
                ),
                 2 => array(
                'name' => '学生',
                'nameId' => 3
                ),
                 3 => array(
                'name' => '家长',
                'nameId' => 4
               )
            );
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $array),true);
        }
        if($type == 'getList'){
            $nameId = I('get.nameId');
            $value = I('get.value');
            $sort = I('get.sort');
            $sortType = I('get.sortType');
            if($nameId == 1){
                if($_GET['sort']&&$_GET['value'] ){
                    if($sort=='ascending'){
                        $sort = 'asc';
                    }
                    if($sort == 'descending'){
                        $sort = 'desc';
                    }
                    $return = $this->getWorkerPassword($scId,'mks_user',1," and (name like '%$value%' OR account like '%$value%' OR phone like '%$value%')",1," order by $sortType $sort");
                    $this->returnJson($return,true);
                }
                if(!$_GET['sort'] &&$_GET['value'] ){
                    $return = $this->getWorkerPassword($scId,'mks_user',1," and (name like '%$value%' OR account like '%$value%' OR phone like '%$value%')");
                    $this->returnJson($return,true);
                }
                if($_GET['sort']&& !$_GET['value'] ){
                    if($sort=='ascending'){
                        $sort = 'asc';
                    }
                    if($sort == 'descending'){
                        $sort = 'desc';
                    }
                    $return = $this->getWorkerPassword($scId,'mks_user',0,'',1," order by $sortType $sort");
                    $this->returnJson($return,true);
                }
                $return = $this->getWorkerPassword($scId,'mks_user');
                $this->returnJson($return,true);
            }
            if($nameId == 2){
                if($_GET['sort'] &&$_GET['value']){
                    if($sort=='ascending'){
                        $sort = 'asc';
                    }
                    if($sort == 'descending'){
                        $sort = 'desc';
                    }
                    $return = $this->pageListPassword($scId,'mks_user',$this::$teacherRoleId,1," and (name like '%$value%' OR account like '%$value%')",1," order by $sortType $sort");
                    $this->returnJson($return,true);
                }
                if(!$_GET['sort'] &&$_GET['value'] ){
                    $return = $this->pageListPassword($scId,'mks_user',$this::$teacherRoleId,1," and (name like '%$value%' OR account like '%$value%')");
                    $this->returnJson($return,true);
                }
                if($_GET['sort'] && !$_GET['value'] ){
                    if($sort=='ascending'){
                        $sort = 'asc';
                    }
                    if($sort == 'descending'){
                        $sort = 'desc';
                    }
                    $return = $this->pageListPassword($scId,'mks_user',$this::$teacherRoleId,0,'',1," order by $sortType $sort");
                    $this->returnJson($return,true);
                }
                $return = $this->pageListPassword($scId,'mks_user',$this::$teacherRoleId);
                $this->returnJson($return,true);
            }
            if($nameId == 3){
                if($_GET['sort'] &&$_GET['value']){
                    if($sort=='ascending'){
                        $sort = 'asc';
                    }
                    if($sort == 'descending'){
                        $sort = 'desc';
                    }
                    $return = $this->studentPageListPassword($scId,'mks_user',$this::$studentRoleId,1," and (name like '%$value%' OR account like '%$value%')",1," order by $sortType,grade $sort");
                    $this->returnJson($return,true);
                }
                if(!$_GET['sort'] &&$_GET['value'] ){
                    $return = $this->studentPageListPassword($scId,'mks_user',$this::$studentRoleId,1," and (name like '%$value%' OR account like '%$value%')");
                    $this->returnJson($return,true);
                }
                if($_GET['sort'] && !$_GET['value'] ){
                    if($sort=='ascending'){
                        $sort = 'asc';
                    }
                    if($sort == 'descending'){
                        $sort = 'desc';
                    }
                    $return = $this->studentPageListPassword($scId,'mks_user',$this::$studentRoleId,0,'',1," order by $sortType,grade $sort");
                    $this->returnJson($return,true);
                }
                $return = $this->studentPageListPassword($scId,'mks_user',$this::$studentRoleId);
                $this->returnJson($return,true);
            }
            if($nameId == 4){
                if($_GET['sort'] &&$_GET['value']){
                    if($sort=='ascending'){
                        $sort = 'asc';
                    }
                    if($sort == 'descending'){
                        $sort = 'desc';
                    }
                    $return = $this->jZPageListPassword($scId,'mks_user',$this::$jZroleId,1," and (name like '%$value%' OR childName like '%$value%')",1," order by $sortType,grade $sort");
                    $this->returnJson($return,true);
                }
                if(!$_GET['sort'] &&$_GET['value'] ){
                    $return = $this->jZPageListPassword($scId,'mks_user',$this::$jZroleId,1," and (name like '%$value%' OR childName like '%$value%')");
                    $this->returnJson($return,true);
                }
                if($_GET['sort'] && !$_GET['value'] ){
                    if($sort=='ascending'){
                        $sort = 'asc';
                    }
                    if($sort == 'descending'){
                        $sort = 'desc';
                    }
                    $return = $this->jZPageListPassword($scId,'mks_user',$this::$jZroleId,0,'',1," order by $sortType,grade $sort");
                    $this->returnJson($return,true);
                }
                $return = $this->jZPageListPassword($scId,'mks_user',$this::$jZroleId);
                $this->returnJson($return,true);
            }
        }
        if($type == 'export'){
            $nameId = I('get.nameId');
            if($nameId == 1){
                $teacherRole = $this::$teacherRoleId;
                $jz = $this::$jZroleId;
                $studentRole = $this::$studentRoleId;
                $adminRole = $this::$adminRoleId;
                $userSql = "SELECT id,account,InitialPassword,name,phone,state,post FROM mks_user WHERE roleId!=$teacherRole AND roleId!=$studentRole AND roleId!=$adminRole AND roleId!=$jz AND scId=$scId";
                $data = M('')->query($userSql);
                $tr = array(
                    '0' => array(
                        'en' => 'account',
                        'zh' => '用户名'
                    ),
                    '1' => array(
                        'en' => 'InitialPassword',
                        'zh' => '初始密码'
                    ),  '2' => array(
                        'en' => 'name',
                        'zh' => '名字'
                    ), '3' => array(
                        'en' => 'phone',
                        'zh' => '手机'
                    ), '4' => array(
                        'en' => 'post',
                        'zh' => '岗位'
                    )
                );
                $this->export($data,$tr);
            }
            if($nameId == 2){
                $teacherRoleId = $this::$teacherRoleId;
                $userSql = "SELECT id,account,InitialPassword,name,phone,state,teachingSubjects FROM mks_user WHERE scId=$scId and roleId = $teacherRoleId ORDER by teachingSubjects";
                $data = M('')->query($userSql);
                $tr = array(
                    '0' => array(
                        'en' => 'account',
                        'zh' => '用户名'
                    ),
                    '1' => array(
                        'en' => 'InitialPassword',
                        'zh' => '初始密码'
                    ),  '2' => array(
                        'en' => 'name',
                        'zh' => '姓名'
                    ),
                  '3' => array(
                        'en' => 'teachingSubjects',
                        'zh' => '任教科目'
                    ),'4' => array(
                        'en' => 'phone',
                        'zh' => '手机'
                    )
                );
                $this->export($data,$tr);
            }
            if($nameId == 3){
                $studentRole = $this::$studentRoleId;
                $userSql = "SELECT id,grade,className,account,InitialPassword,name,phone,state FROM mks_user WHERE scId=$scId and roleId = $studentRole order BY grade,className";
                $data = M('')->query($userSql);
                foreach($data as $key => $value){
                    $data[$key]['grade'] = $this::gradeToZhong($value['grade']);
                }

                $tr = array(
                    '0' => array(
                        'en' => 'grade',
                        'zh' => '年级'
                    ),
                    '1' => array(
                        'en' => 'className',
                        'zh' => '班级'
                    ),  '2' => array(
                        'en' => 'name',
                        'zh' => '名字'
                    ),
                    '3' => array(
                        'en' => 'account',
                        'zh' => '学生用户名'
                    ),
                      '4' => array(
                    'en' => 'InitialPassword',
                    'zh' => '初始密码'
                    ),
                      '5' => array(
                    'en' => 'phone',
                    'zh' => '手机'
                    )
                );
                $this->export($data,$tr);
            }
            if($nameId == 4){
                $jZroleId = $this::$jZroleId;
                $userSql = "SELECT id,grade,className,account,InitialPassword,name,phone,childName FROM mks_user WHERE scId=$scId and roleId = $jZroleId  order BY grade,className";
                $data = M('')->query($userSql);
                $tr = array(
                    '0' => array(
                        'en' => 'grade',
                        'zh' => '年级'
                    ),
                    '1' => array(
                        'en' => 'className',
                        'zh' => '班级'
                    ),  '2' => array(
                        'en' => 'childName',
                        'zh' => '学生姓名'
                    ),
                    '3' => array(
                        'en' => 'account',
                        'zh' => '家长用户名'
                    ),
                    '4' => array(
                        'en' => 'InitialPassword',
                        'zh' => '初始密码'
                    ),
                    '5' => array(
                        'en' => 'phone',
                        'zh' => '手机'
                    )
                );
                $this->export($data,$tr);
            }
        }
        if($type == 'updataPassword'){
            $id = I('get.id');
            $password = $this->InitialPassword();
            if(M('user')->where(array('id' => $id,'scId' => $scId))->setField(array(
                'password' => $this->create_password($password,self::$password_key,self::$password_key1),
                'InitialPassword' => $password
            ))){
                $this->returnJson(array('statu' => 1,'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
        }
        if($type == 'stopUse'){
            $id = I('get.id');
            $user = M('user')->where(array('id' => $id,'scId' => $scId))->find();
            $state = 0;
            if($user['state']){
                $state = 0;
            }else{
                $state = 1;
            }
            if(M('user')->where(array('id' => $id,'scId' => $scId))->setField(array(
                'state' => $state
            ))){
                $this->returnJson(array('statu' => 1,'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
        }
    }
    public function userGl(){
        $type = I('get.type');
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        $userRoleId = $session['roleId'];
        $userUserId = $session['userId'];
        if(isset($_GET['roleId'])){
            $roleId = I('get.roleId');
            $roleNameEn = M('role')->field('roleNameEn')->where(array('roleId' => $roleId))->find();
            $roleNameEn = $roleNameEn['roleNameEn'];
        }
        if($type == 'teacherList'){
            //每一页显示的数据条数
            if($_GET['sort'] &&$_GET['value']){
                $value = I('get.value');
                $sort = I('get.sort');
                if($sort=='ascending'){
                    $sort = 'asc';
                }
                if($sort == 'descending'){
                    $sort = 'desc';
                }
                $sortType = I('get.sortType');
                $return = $this->pageList($scId,'mks_user',$this::$teacherRoleId,1," and (name like '%$value%' OR teachingSubjects like '%$value%')",1," order by $sortType $sort");
                $this->returnJson($return,true);
            }
            if(!$_GET['sort'] &&$_GET['value'] ){
                $value = I('get.value');
                $return = $this->pageList($scId,'mks_user',$this::$teacherRoleId,1," and (name like '%$value%' OR teachingSubjects like '%$value%')");
                $this->returnJson($return,true);
            }
            if($_GET['sort'] && !$_GET['value'] ){
                $sort = I('get.sort');
                if($sort=='ascending'){
                    $sort = 'asc';
                }
                if($sort == 'descending'){
                    $sort = 'desc';
                }
                $sortType = I('get.sortType');
                $return = $this->pageList($scId,'mks_user',$this::$teacherRoleId,0,'',1," order by $sortType $sort");
                $this->returnJson($return,true);
            }
            $return = $this->pageList($scId,'mks_user',$this::$teacherRoleId);
            $this->returnJson($return,true);
        }
        if($type == 'studentPersonalData'){
            $userId = I('get.userId');
            $studentInfo = M('student_info')->where(array('userId' => $userId,'scId' => $scId))->find();
            $class = M('class')->where(array('scId' => $scId,'classid' => $studentInfo['classId']))->find();
            $bra = M('class_branch')->where(array('branchid' => $class['branch']))->find();
            $may = M('class_major')->where(array('majorid' => $class['major']))->find();
            $xj = M('cen_reg_info')->where(array('userId' => $userId,'scId' => $scId))->find();
            $roll = M('school_rollinfo')->where(array('userId' => $userId,'scId' => $scId))->find();
            $roll['major'] = $may['majorname'];
            $roll['stream'] = $bra['branch'];
            $data = array(
                'student_info' =>   M('student_info')->where(array('userId' => $userId,'scId' => $scId))->find(),
                'cen_reg_info' => $xj,
                'school_rollinfo' =>  $roll,
                'parents_info'=> M('parents_info')->where(array('userId' => $userId,'scId' => $scId))->find(),
                'other_info' =>  M('other_info')->where(array('userId' => $userId,'scId' => $scId))->find(),
                'tuition_info' =>  M('tuition_info')->where(array('userId' => $userId,'scId' => $scId))->find(),
                'statu' => 1,
                'message' =>  'success',
            );
            $this->returnJson($data,true);
        }
        if($type == 'myPersonalData'){
            $session = $this->get_session('loginCheck',false);
            $userId = $session['userId'];
            if($session['roleId'] == $this::$jZroleId){
                $userId = M('user')->field('childId')->where(array('scId' => $scId,'id' => $userId))->find();
                $userId = $userId['childId'];
            }
            $data = array(
                'student_info' =>   M('student_info')->where(array('userId' => $userId,'scId' => $scId))->find(),
                'cen_reg_info' => M('cen_reg_info')->where(array('userId' => $userId,'scId' => $scId))->find(),
                'school_rollinfo' =>  M('school_rollinfo')->where(array('userId' => $userId,'scId' => $scId))->find(),
                'parents_info'=> M('parents_info')->where(array('userId' => $userId,'scId' => $scId))->find(),
                'other_info' =>  M('other_info')->where(array('userId' => $userId,'scId' => $scId))->find(),
                'tuition_info' =>  M('tuition_info')->where(array('userId' => $userId,'scId' => $scId))->find(),
                'statu' => 1,
                'message' =>  'success',
            );
            $this->returnJson($data,true);
        }
        if($type == 'jsWorkerPersonalData'){
            $session = $this->get_session('loginCheck',false);
            $userId = $session['userId'];
            $data = M('user')->where(array('scId' => $scId,'id' => $userId))->find();
            unset($data['account']);
            unset($data['password']);
            unset($data['InitialPassword']);
            unset($data['state']);
            unset($data['headMaster']);
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $data),true);
        }
        if($type == 'updataPersonalZgJsData'){
            $session = $this->get_session('loginCheck',false);
            $userId = $session['userId'];
            $data = I('post.data');
            unset($data['account']);
            unset($data['password']);
            unset($data['InitialPassword']);
            unset($data['state']);
            unset($data['roleId']);
            unset($data['post']);
            if(M('user')->where(array('scId' => $scId,'id' => $userId))->setField($data)){
                $this->returnJson(array('statu' => 1,'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
        }
        if($type == 'updataPersonalStudentData'){
            $ListType = I('post.ListType');
            $session = $this->get_session('loginCheck',false);
            $userId = $session['userId'];
            $roleId =$session['roleId'];
            if($roleId == $this::$jZroleId){
                $userId = M('user')->field('childId')->where(array('scId' => $scId,'id' => $userId))->find();
                $userId = $userId['childId'];
            }
            $table = '';
            if($ListType == 'jbXi'){
                $table = 'student_info';
            }
            if($ListType == 'student_info'){
                $table = 'student_info';
            }
            if($ListType == 'xjXi'){//学籍信息
                $table = 'school_rollinfo';
            }
            if($ListType == 'school_rollinfo'){
                $table = 'school_rollinfo';
            }
            if($ListType == 'hjXi'){//户籍
                $table = 'cen_reg_info';
            }
            if($ListType == 'cen_reg_info'){
                $table = 'cen_reg_info';
            }
            if($ListType == 'jzXi'){//家长
                $table = 'parents_info';
            }
            if($ListType == 'parents_info'){
                $table = 'parents_info';
            }
            if($ListType == 'xfXi'){//学费信息
                $table = 'tuition_info';
            }
            if($ListType == 'tuition_info'){//学费信息
                $table = 'tuition_info';
            }
            if($ListType == 'qtXi'){//其他信息
                $table = 'other_info';
            }
            if($ListType == 'other_info'){//其他信息
                $table = 'other_info';
            }
            if(!isset($_POST['updataData'])){
                $this->returnJson(array('statu' => 0, 'message' => 'updata fail'),true);
            }
            $data = I('post.updataData');
            $url = $data['logo'];
            if($url){
                M('user')->where(array('scId' => $scId,'id' => $userId))->setField(array(
                    'logo' => $url
                ));
            }
            if(M($table)->where(array('userId' => $userId,'scId' => $scId))->setField($data)){
                $this->returnJson(array('statu' => 1, 'message' => 'updata success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'updata fail'),true);
        }
        if($type == 'uploadStudentLogo'){
            $url = $this->uploads();
            $this->returnJson(array('statu' => 1,'message' => 'success','logo' => $url),true);
        }
        if($type == 'studentPersonalUpdata'){
            $ListType = I('post.ListType');
            $table = '';
            $data = I('post.updataData');
            if(isset($data['userId'])){
                $userId = $data['userId'];
            }else{
                $userId = I('post.userId');
            }
            unset($data['major']);
            unset($data['stream']);
            if($ListType == 'jbXi' || $ListType == 'student_info'){
                M('user')->where(array('scId' => $scId,'id' => $userId))->setField(array('sex' => $data['sex'],'serialNumber' => $data['serialNumber'],'birth' => $data['birthday'],'name' => $data['name']));
                $table = 'student_info';
                $gggggId = M('student_info')->field('gradeId')->where(array('scId' => $scId,'userId' => $data['userId']))->find();
                if($gggggId['gradeId'] == $data['gradeId']){
                }else{
                    $gradeNameName = M('grade')->field('name')->where(array('scId' => $scId,'gradeid' => $data['gradeId']))->find();
                    $data['grade'] = $gradeNameName['name'];
                    M('user')->where(array('id' => $userId,'scId' => $scId))->setField(array('gradeId' => $data['gradeId'],'grade' => $gradeNameName['name']));
                    //M('school_rollinfo')->where(array('userId' => $userId,'scId' => $scId))->setField(array('grade' => $gradeNameName['name']));
                    //M('parents_info')->where(array('userId' => $userId,'scId' => $scId))->setField(array('grade' => $gradeNameName['name']));
                    //M('tuition_info')->where(array('userId' => $userId,'scId' => $scId))->setField(array('grade' => $gradeNameName['name']));
                    //M('other_info')->where(array('userId' => $userId,'scId' => $scId))->setField(array('grade' => $gradeNameName['name']));
                    //M('cen_reg_info')->where(array('userId' => $userId,'scId' => $scId))->setField(array('grade' => $gradeNameName['name']));
                }
                $classccccId = M('student_info')->field('classId')->where(array('scId' => $scId,'userId' => $data['userId']))->find();
                if($classccccId['classId'] == $data['classId']){
                }else{
                    $classclass = M('class')->field('classname')->where(array('scId' => $scId,'classid' => $data['classId']))->find();
                    $data['className'] = $classclass['classname'];
                    $data['serialNumber'] = '';
                    M('user')->where(array('id' => $userId,'scId' => $scId))->setField(array('class' => $data['classId'],'className' =>$classclass['classname'],'serialNumber' => ''));
                    //M('school_rollinfo')->where(array('userId' => $userId,'scId' => $scId))->setField(array('className' =>$classclass['classname']));
                    //M('parents_info')->where(array('userId' => $userId,'scId' => $scId))->setField(array('className' =>$classclass['classname']));
                    //M('tuition_info')->where(array('userId' => $userId,'scId' => $scId))->setField(array('className' => $classclass['classname']));
                    //M('other_info')->where(array('userId' => $userId,'scId' => $scId))->setField(array('className' => $classclass['classname']));
                    //M('cen_reg_info')->where(array('userId' => $userId,'scId' => $scId))->setField(array('className' => $classclass['classname']));
                }
            }else{
                unset($data['className']);
                unset($data['grade']);
                unset($data['name']);
            }
            if($ListType == 'xjXi' || $ListType == 'school_rollinfo'){//学籍信息
                $table = 'school_rollinfo';
                M('user')->where(array('id' => $userId,'scId' => $scId))->setField(array('admCategory' => $data['admCategory'],'isAtSchool' => $data['isAtSchool']));

            }
            if($ListType == 'hjXi' || $ListType == 'cen_reg_info'){//户籍
                $table = 'cen_reg_info';
            }
            if($ListType == 'jzXi' || $ListType == 'parents_info'){//家长
                $table = 'parents_info';
            }
            if($ListType == 'xfXi' ||$ListType == 'tuition_info'){//学费信息
                $table = 'tuition_info';
            }
            if($ListType == 'qtXi' || $ListType == 'other_info'){//其他信息
                $table = 'other_info';
            }
            if(!isset($_POST['updataData'])){
                $this->returnJson(array('statu' => 0, 'message' => 'updata fail'),true);
            }
            $url = $data['logo'];
            if($url){
                M('user')->where(array('scId' => $scId,'id' => $userId))->setField(array(
                    'logo' => $url
                ));
            }
            if(M($table)->where(array('userId' => $userId,'scId' => $scId))->setField($data)){
                $this->returnJson(array('statu' => 1, 'message' => 'updata success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'updata fail'),true);
        }
        if($type == 'getMajor'){
            $branchid = I('get.branchid');
            //$data = M('class_major')->where(array('scId' => $scId,'branch' => $branchid))->select();
            $data = M('')->query("select * from mks_class_major where scId=0 or(scId=$scId and branch=$branchid)");
            if(!$data){
                $data = array();
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getBranch'){
            $data = M('class_branch')->where(array('scId' => array(array('eq',$scId),array('eq',0),'or')))->select();
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'personData'){
            $session = $this->get_session('loginCheck',false);
            $scId = $session['scId'];
            $userRoleId = $session['roleId'];
            if($this::$studentRoleId == $userRoleId){

            }
        }
        if($type == 'getStudentsListData'){
            $value = I('get.value');
            $sort = I('get.sort');
            $sortType = I('get.sortType');
            if($_GET['sort'] &&$_GET['value']){
                if($sort=='ascending'){
                    $sort = 'asc';
                }
                if($sort == 'descending'){
                    $sort = 'desc';
                }
                $return = $this->pageList($scId,'mks_user',$this::$studentRoleId,1," and (name like '%$value%' OR className like '%$value%' OR phone like '%$value%')",1," order by $sortType $sort");
                $this->returnJson($return,true);
            }
            if(!$_GET['sort'] &&$_GET['value'] ){
                $return = $this->pageList($scId,'mks_user',$this::$studentRoleId,1," and (name like '%$value%' OR className like '%$value%' OR phone like '%$value%')");
                $this->returnJson($return,true);
            }
            if($_GET['sort'] && !$_GET['value'] ){
                if($sort=='ascending'){
                    $sort = 'asc';
                }
                if($sort == 'descending'){
                    $sort = 'desc';
                }
                $return = $this->pageList($scId,'mks_user',$this::$studentRoleId,0,'',1," order by $sortType $sort");
                $this->returnJson($return,true);
            }
            $return = $this->pageList($scId,'mks_user',$this::$studentRoleId);
            $this->returnJson($return,true);
        }
        if($type == 'workerList'){
            $return = $this->getWorkPage($scId,'mks_user');
            $this->returnJson($return,true);
        }
        if($type == 'download'){
            $downType = I('get.downType');
            $url = $this::$downUrl;
            if($downType == 'student'){
                //$this::download($this::$uploadUrl.'downloadexcel','student.xlsx');
                header("Location: $url/Public/upload/downloadexcel/student.xlsx");
            }if($downType == 'teacher'){
                //$this::download($this::$uploadUrl.'downloadexcel','teacher.xlsx');
                header("Location: $url/Public/upload/downloadexcel/teacher.xls");
            }if($downType == 'worker'){
                header("Location: $url/Public/upload/downloadexcel/worker.xls");
            }
        }
        if($type == 'studentStatisticsExport'){
            $grade = I('get.grade');
            $allUser = M('student_info')->field('userId,sex,className')->where(array('scId' => $scId,'gradeId' => $grade))->select();
            $allUserCheck = array();
            foreach($allUser as $key => $value){
                $allUserCheck[$value['userId']] = $value;
            }
            $allStudent = M('school_rollinfo')->where(array('scId' => $scId))->select();
            $relllStudent = array();
            foreach($allStudent as $key => $value){
                if(isset($allUserCheck[$value['userId']])){
                    $value['sex'] = $allUserCheck[$value['userId']]['sex'];
                    $value['className'] = $allUserCheck[$value['userId']]['className'];
                    $relllStudent[$value['className']][] = $value;
                }
            }
            $newReturn = array();
            $iiii = 0;
            foreach($relllStudent as $key => $value){
                $newReturn[$iiii]['count'] = count($value);
                $newReturn[$iiii]['className'] = $key;
                $isLeaveCount = 0;
                $isTempStudyCount = 0;
                $isSuborCount = 0;
                $man = 0;
                foreach($value as $key => $value){
                    $isLeaveCount+= $value['isLeave'];
                    $isTempStudyCount+= $value['isTempStudy'];
                    $isSuborCount+= $value['isSubor'];
                    if($value['sex'] == '男'){
                        $man++;
                    }
                }
                $newReturn[$iiii]['isLeave'] = $isLeaveCount;
                $newReturn[$iiii]['isTempStudy'] = $isTempStudyCount;
                $newReturn[$iiii]['isSubor'] = $isSuborCount;
                $newReturn[$iiii]['man'] = $man;
                $newReturn[$iiii]['woman'] = $newReturn[$iiii]['count'] - $man;
                $iiii++;
            }
            $tr = array(
                '0' => array(
                    'en' => 'className',
                    'zh' => '班级'
                ),
                '1' => array(
                    'en' => 'count',
                    'zh' => '在读'
                ),  '2' => array(
                    'en' => 'isTempStudy',
                    'zh' => '借读'
                ), '3' => array(
                    'en' => 'isLeave',
                    'zh' => '休学'
                ), '4' => array(
                    'en' => 'isSubor',
                    'zh' => '靠挂'
                ), '5' => array(
                    'en' => 'man',
                    'zh' => '男生'
                ), '5' => array(
                    'en' => 'woman',
                    'zh' => '女生'
                )
            );
            $newReturn = $this->pageGo($newReturn);
            $this::export($newReturn['data'],$tr);
        }
        if($type == 'studentStatisticsValue'){
            $grade = I('get.grade');
            $allUser = M('student_info')->field('userId,sex,className')->where(array('scId' => $scId,'gradeId' => $grade))->select();
            $allUserCheck = array();
            foreach($allUser as $key => $value){
                $allUserCheck[$value['userId']] = $value;
            }
            $allStudent = M('school_rollinfo')->where(array('scId' => $scId))->select();
            $relllStudent = array();
            foreach($allStudent as $key => $value){
                if(isset($allUserCheck[$value['userId']])){
                    $value['sex'] = $allUserCheck[$value['userId']]['sex'];
                    $value['className'] = $allUserCheck[$value['userId']]['className'];
                    $relllStudent[$value['className']][] = $value;
                }
            }
            $newReturn = array();
            $iiii = 0;
            foreach($relllStudent as $key => $value){
                $newReturn[$iiii]['count'] = count($value);
                $newReturn[$iiii]['className'] = $key;
                $isLeaveCount = 0;
                $isTempStudyCount = 0;
                $isSuborCount = 0;
                $man = 0;
                foreach($value as $key => $value){
                    $isLeaveCount+= $value['isLeave'];
                    $isTempStudyCount+= $value['isTempStudy'];
                    $isSuborCount+= $value['isSubor'];
                    if($value['sex'] == '男'){
                        $man++;
                    }
                }
                $newReturn[$iiii]['isLeave'] = $isLeaveCount;
                $newReturn[$iiii]['isTempStudy'] = $isTempStudyCount;
                $newReturn[$iiii]['isSubor'] = $isSuborCount;
                $newReturn[$iiii]['man'] = $man;
                $newReturn[$iiii]['woman'] = $newReturn[$iiii]['count'] - $man;
                $iiii++;
            }
            $this::returnJson($this->pageGo($newReturn),true);
        }
        if($type == 'getstudentAllSort'){
            $gradeId = I('get.gradeid');
            $strId = $this::$studentRoleId;
            //$data = M('user')->field('className,name,sex,phone,hklx,birth,idCard,admCategory')->where(array('scId' => $scId,'gradeId' => $gradeId,'roleId' => $this::$studentRoleId))->order('id desc')->select();
            $data = M('')->query("select mks_user.className,mks_user.name,mks_user.sex,mks_user.phone,mks_user.hklx,mks_user.birth,mks_user.idCard,mks_user.admCategory,isResSchool from mks_user JOIN mks_student_info ON mks_user.id= mks_student_info.userId where mks_user.scId=$scId AND mks_user.gradeId = $gradeId AND mks_user.roleId=$strId");
            $nowdata = $this::sortPageValue($data);
            $nowdata['tr'] = $this::getTr('student_xi_rel');
            $this->returnJson(array('statu' => 1,'data' => $nowdata),true);
        }
        if($type == 'studentStatistics'){
            $grade = I('get.grade');
            $allUser = M('student_info')->field('userId,sex,className')->where(array('scId' => $scId,'gradeId' => $grade))->select();
            $allUserCheck = array();
            foreach($allUser as $key => $value){
                $allUserCheck[$value['userId']] = $value;
            }
            $allStudent = M('school_rollinfo')->where(array('scId' => $scId))->select();
            $relllStudent = array();
            foreach($allStudent as $key => $value){
                if(isset($allUserCheck[$value['userId']])){
                    $value['sex'] = $allUserCheck[$value['userId']]['sex'];
                    $value['className'] = $allUserCheck[$value['userId']]['className'];
                    $relllStudent[$value['className']][] = $value;
                }
            }
            $newReturn = array();
            $iiii = 0;
            foreach($relllStudent as $key => $value){
                $newReturn[$iiii]['count'] = count($value);
                $newReturn[$iiii]['className'] = $key;
                $isLeaveCount = 0;
                $isTempStudyCount = 0;
                $isSuborCount = 0;
                $man = 0;
                foreach($value as $key => $value){
                    $isLeaveCount+= $value['isLeave'];
                    $isTempStudyCount+= $value['isTempStudy'];
                    $isSuborCount+= $value['isSubor'];
                    if($value['sex'] == '男'){
                        $man++;
                    }
                }
                $newReturn[$iiii]['isLeave'] = $isLeaveCount;
                $newReturn[$iiii]['isTempStudy'] = $isTempStudyCount;
                $newReturn[$iiii]['isSubor'] = $isSuborCount;
                $newReturn[$iiii]['man'] = $man;
                $newReturn[$iiii]['woman'] = $newReturn[$iiii]['count'] - $man;
                $iiii++;
            }
            $this::returnJson($this->pageGo($newReturn),true);
        }
        if($type == 'studentList'){
            $ListType = I('get.ListType');
            if($ListType == 'jbXi'){
                $this->returnJson($this::studentXi($scId,'mks_student_info'),true);
            }
            if($ListType == 'xjXi'){//学籍信息
                $this->returnJson($this::studentXi($scId,'mks_school_rollinfo'),true);
            }
            if($ListType == 'hjXi'){//户籍
                $this->returnJson($this::studentXi($scId,'mks_cen_reg_info'),true);
            }
            if($ListType == 'jzXi'){//家长
                $this->returnJson($this::studentXi($scId,'mks_parents_info'),true);
            }
            if($ListType == 'xfXi'){//学费信息
                $this->returnJson($this::studentXi($scId,'mks_tuition_info'),true);
            }
            if($ListType == 'qtXi'){//其他信息
                $this->returnJson($this::studentXi($scId,'mks_other_info'),true);
            }
        }
        if($type == 'deleteStaff'){//删除职工
            $delUserid = I('post.userId');
            if(!is_array($delUserid)){
                $delUserid = (array)$delUserid;
            }
            foreach($delUserid as $key => $value){
                if($roleId != $this::$adminRoleId){
                    M('user')->where(array('id' =>$value,'scId' => $scId))->delete();
                }
            }
            $this->returnJson(array('statu' => 1,'message' => 'delete success'),true);
        }
        if($type == 'deleteTethe'){//删除教师
            $delUserid = I('post.userId');
            if(!is_array($delUserid)){
                $delUserid = (array)$delUserid;
            }
            foreach($delUserid as $key => $value){
                M('user')->where(array('id' =>$value,'roleId' => $this::$teacherRoleId,'scId' => $scId))->delete();
            }
            $this->returnJson(array('statu' => 1,'message' => 'delete success'),true);
        }
        if($type == 'deleteStudent'){
        //删除学生
            /*
             * 外键已经建立用户表外键
             * */
            $delUserid = I('post.userId');
            if(!is_array($delUserid)){
                $delUserid = (array)$delUserid;
            }
            $delUser = array();
            foreach($delUserid as $key => $value){
                $delUser[] = $value;
                $this->delStudent($value);
            }
            M('user')->where(array('id' =>array('in',$delUser),'roleId' => $this::$studentRoleId,'scId' => $scId))->delete();
            M('user')->where(array('childId' => array('in',$delUser),'roleId' => $this::$jZroleId))->delete();
            $this->returnJson(array('statu' => 1,'message' => 'delete success'),true);
        }
        $createTime = strtotime(date('Y-m-d H:i:s'));
        $prefix = M('school')->field('prefix')->where(array('scId' => $scId))->find();
        $prefix = $prefix['prefix'];
        $maxNumber = M('user')->where(array('scId' =>array(array('eq',$scId),array('eq', -$scId),'or')))->max('number');
        if(!$maxNumber){
            $maxNumber = (int)date('Y').'000000';
        }else{
            $maxNumber++;
        }
        //$password = $this->create_password($password,self::$password_key,self::$password_key1);
        if($type == 'export'){
            define('MAX_SIZE_FILE_UPLOAD', '10000000' );
            header('content-type:text/html;charset=utf-8');
            //$verifyToken = md5('unique_salt' . $_POST['timestamp']);
            if (!empty($_FILES)/* && $_POST['token'] == $verifyToken*/){
                $tempFile = $_FILES['userFile']['tmp_name'];
                //$targetFile = $targetPath.$_FILES['userFile']['name'];
                //Validate the file type
                $fileTypes = array('xlsx','doc','xls'); // File extensions
                $fileParts = pathinfo($_FILES['userFile']['name']);
                $flieExtension = $fileParts['extension'];
                $targetFile = md5(uniqid('gsfdg')).'.'.$flieExtension;
                if (in_array($flieExtension,$fileTypes)){
                    if(move_uploaded_file($tempFile,$targetFile)){
                        vendor('PHPExcel.PHPExcel');
                        //vendor('PHPExcel.PHPExcel.IOFactory');
                        //vendor('PHPExcel.PHPExcel.Reader.IOFactory');
                        //vendor('PHPExcel.PHPExcel.Reader.Excel2007');
                        $objReader = 0;
                        if($flieExtension == 'xls'){
                            $objReader = new \PHPExcel_Reader_Excel5();
                        }
                        if($flieExtension == 'xlsx'){
                            $objReader = new \PHPExcel_Reader_Excel2007();
                        }
                        $objPHPExcel = $objReader->load($targetFile); //Excel 路径
                        $sheet = $objPHPExcel->getSheet(0);
                        $highestRow = $sheet->getHighestRow(); // 取得总行数
                        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
                        $strs=array();//USER表信息；
                        $hujixinxi = array();//户籍信息
                        $b = 0;
                        $getSession = $this->get_session('loginCheck',false);
                        $qxModelName = $getSession['scId'].'school'.$getSession['roleId'].'navList';
                        $userModelList = json_decode($this->redis_operation($qxModelName,0,0,3),true);
                        $roleNameEn = I('get.roleNameEn');
                        if($roleNameEn == 'xs'){
                            $trueorfalse = 0;
                            foreach($userModelList as $key => $value){
                                if($value['url'] == 'studentImport'){
                                    $trueorfalse =1;
                                    break;
                                }
                            }
                            if($getSession['roleId'] == $this::$adminRoleId){
                                $trueorfalse =1;
                            }
                            if(!$trueorfalse){
                                $this->returnJson(array('statu' => 0,'message' => 'No permissions'),true);
                            }
                            for ($j=1;$j<=$highestRow;$j++){
                                $i = 0;
                                for($k='A';$k<=$highestColumn;$k++){//从A列读取数据
                                    //实测在excel中，如果某单元格的值包含了||||||导入的数据会为空
                                    $i++;
                                    if($k == 'A'){
                                        $strs[$b]['className'] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'B'){
                                        $strs[$b]['name'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'C'){
                                        $strs[$b]['sex'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'D'){
                                        $strs[$b]['phone'] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'E'){
                                        $strs[$b]['hklx'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'F'){
                                        $strs[$b]['birth'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'G'){
                                        $strs[$b]['idCard'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'H'){
                                        $strs[$b]['admCategory'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'I'){
                                        $zx = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue() == '是';
                                        if($zx == '否'){
                                            $zx = '否';
                                        }else{
                                            $zx = '是';
                                        }
                                        $strs[$b]['isResSchool'] = $zx;
                                    }
                                }
                                $strs[$b]['gradeId'] = I('post.gradeid');
                                $password = $this->InitialPassword();
                                $maxNumber++;
                                $strs[$b]['account'] = $prefix. $maxNumber;
                                $strs[$b]['password'] =  $this->create_password($password,self::$password_key,self::$password_key1);
                                $strs[$b]['InitialPassword'] = $password;
                                $strs[$b]['roleId'] = $this::$studentRoleId;
                                $strs[$b]['createTime'] = $createTime;
                                $strs[$b]['scId'] = $scId;
                                $strs[$b]['scPrefix'] = $prefix;
                                $strs[$b]['number'] = $maxNumber;
                                $b++;
                                $maxNumber++;
                            }
                            unset($strs[0]);
                            $class = $this->getClass1($scId);
                            $serNmu = array();
                            $ddddd = I('post.gradeid');
                            $serNmuRel = array();
                            $serNmu = M('')->query("select MAX(serialNumber) as num,className FROM mks_user WHERE scId = $scId AND gradeId=$ddddd GROUP BY className");
                            $classAll = M('')->query("select  className from mks_class WHERE grade=$ddddd and scId =$scId");
                            foreach($classAll as $key => $value) {
                                $serNmuRel[$value['className']] = 0;
                            }
                            foreach($serNmu as $key => $value){
                                $serNmuRel[$value['className']] = $value['num'];
                            }
                            foreach($strs as $key => $value){
                                if($value['name']){
                                    if(!$value['sex']){
                                        $this->returnJson(array('statu' => 0,'message' => '学生性别为必填项，导入数据有学生性别的未填'),true);
                                    }
                                    $strs[$key]['grade'] = $class[$value['gradeId']][$value['className']]['gradeName'];
                                    $strs[$key]['class'] = $class[$value['gradeId']][$value['className']]['classId'];
                                    /*if(! $strs[$key]['grade']){
                                        $this->returnJson(array('statu' => 0,'message' => '学生年级必填，且和数据库一致'),true);
                                    }*/
                                    if(!$strs[$key]['class']){
                                        $this->returnJson(array('statu' => 0,'message' => '班级必填，且和数据库一致'),true);
                                    }
                                    $serNmuRel[$value['className']]++;
                                    $strs[$key]['serialNumber'] = $serNmuRel[$value['className']];
                                }else{
                                    unset($strs[$key]);
                                }
                            }
                        }
                        else if($roleNameEn == 'js'){
                            $trueorfalse = 0;
                            $subjectName = M('subject')->where(array('scId' => $scId))->select();
                            $subjectNameRr = array();
                            foreach($subjectName as $key => $value){
                                $subjectNameRr[$value['subjectname']] = $value['subjectid'];
                            }
                            foreach($userModelList as $key => $value){
                                if($value['url'] == 'teacherImport'){
                                    $trueorfalse =1;
                                    break;
                                }
                            }
                            if($getSession['roleId'] == $this::$adminRoleId){
                                $trueorfalse =1;
                            }
                            if(!$trueorfalse){
                                $this->returnJson(array('statu' => 0,'message' => 'No permissions'),true);
                            }
                            for ($j=1;$j<=$highestRow;$j++){//从第一行开始读取  数据
                                $i = 0;
                                for($k='A';$k<=$highestColumn;$k++){//从A列读取数据
                                    //实测在excel中，如果某单元格的值包含了||||||导入的数据会为空
                                    $i++;
                                    if($k == 'A'){
                                        $strs[$b]['name'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'B'){
                                        $strs[$b]['sex'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'C'){
                                        $strs[$b]['teachingSubjects'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                        $strs[$b]['teachingSubjectsId'] = $subjectNameRr[$strs[$b]['teachingSubjects']];
                                    }
                                    if($k == 'D'){
                                        $strs[$b]['politics'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'E'){
                                        $strs[$b]['phone'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'F'){
                                        $strs[$b]['nation'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'G'){
                                        $strs[$b]['birth'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'H'){
                                        $strs[$b]['idCardType'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'I'){
                                        $strs[$b]['idCard'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                }
                                $password = $this->InitialPassword();
                                $strs[$b]['account'] = $prefix. $maxNumber;
                                $strs[$b]['password'] =  $this->create_password($password,self::$password_key,self::$password_key1);
                                $strs[$b]['roleId'] = $this::$teacherRoleId;
                                $strs[$b]['InitialPassword'] = $password;
                                $strs[$b]['createTime'] = $createTime;
                                $strs[$b]['jobNumber'] = $prefix.substr($maxNumber,5,9);
                                $strs[$b]['scId'] = $scId;
                                $strs[$b]['scPrefix'] = $prefix;
                                $strs[$b]['number'] = $maxNumber;
                                $strs[$b]['post'] = '教师';
                                $strs[$b]['homeAddress'] = serialize(array(
                                    'province' => '',
                                    'city' => '',
                                    'area' => '',
                                    'detail' => ''
                                ));
                                $strs[$b]['registerAddress'] = serialize(array(
                                    'province' => '',
                                    'city' => '',
                                    'area' => '',
                                    'detail' => ''
                                ));
                                $strs[$b]['nowAddress'] = serialize(array(
                                    'province' => '',
                                    'city' => '',
                                    'area' => '',
                                    'detail' => ''
                                ));
                                $strs[$b]['origin'] = serialize(array(
                                    'province' => '',
                                    'city' => ''
                                ));
                                $b++;
                                $maxNumber++;
                            }
                            unset($strs[0]);
                            foreach($strs as $key => $value){
                                if(!$value['name']){
                                    unset($strs[$key]);
                                }
                            }
                        }else{
                            $trueorfalse = 0;
                            foreach($userModelList as $key => $value){
                                if($value['url'] == 'workersImport'){
                                    $trueorfalse =1;
                                    break;
                                }
                            }
                            if($getSession['roleId'] == $this::$adminRoleId){
                                $trueorfalse =1;
                            }
                            if(!$trueorfalse){
                                $this->returnJson(array('statu' => 0,'message' => 'No permissions'),true);
                            }
                            for ($j=1;$j<=$highestRow;$j++){
                                $i = 0;
                                for($k='A';$k<=$highestColumn;$k++){//从A列读取数据
                                    //实测在excel中，如果某单元格的值包含了||||||导入的数据会为空
                                    $i++;
                                    if($k == 'A'){
                                        $strs[$b]['name'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'B'){
                                        $strs[$b]['sex'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'C'){
                                        $strs[$b]['post'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'D'){
                                        $strs[$b]['politics'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'E'){
                                        $strs[$b]['phone'] =(string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'F'){
                                        $strs[$b]['nation'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'G'){
                                        $strs[$b]['birth'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'H'){
                                        $strs[$b]['idCardType'] =(string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                    if($k == 'I'){
                                        $strs[$b]['idCard'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                    }
                                }
                                $password = $this->InitialPassword();
                                $strs[$b]['account'] = $prefix. $maxNumber;
                                $strs[$b]['password'] =  $this->create_password($password,self::$password_key,self::$password_key1);
                                $strs[$b]['InitialPassword'] = $password;
                                $strs[$b]['createTime'] = $createTime;
                                $strs[$b]['scId'] = $scId;
                                $strs[$b]['scPrefix'] = $prefix;
                                $strs[$b]['number'] = $maxNumber;
                                $strs[$b]['jobNumber'] = $prefix.substr($maxNumber,5,9);
                                $strs[$b]['homeAddress'] = serialize(array(
                                    'province' => '',
                                    'city' => '',
                                    'area' => '',
                                    'detail' => ''
                                ));
                                $strs[$b]['registerAddress'] = serialize(array(
                                    'province' => '',
                                    'city' => '',
                                    'area' => '',
                                    'detail' => ''
                                ));
                                $strs[$b]['nowAddress'] = serialize(array(
                                    'province' => '',
                                    'city' => '',
                                    'area' => '',
                                    'detail' => ''
                                ));
                                $strs[$b]['origin'] = serialize(array(
                                    'province' => '',
                                    'city' => ''
                                ));
                                $b++;
                                $maxNumber++;
                            }
                            unset($strs[0]);
                            foreach($strs as $key => $value){
                                if(!$value['name']){
                                    unset($strs[$key]);
                                }
                            }
                        }
                        unlink($targetFile);
                        $checkArr = array();
                        if($roleNameEn == 'xs'){
                            $checkArr = M('user')->field('name')->where(array('scId' => $scId,'roleId' => $this::$studentRoleId,'gradeId' => I('post.gradeid')))->select();
                        }
                        if($roleNameEn == 'js'){
                            $checkArr = M('user')->field('name')->where(array('scId' => $scId,'roleId' => $this::$teacherRoleId))->select();
                        }
                        if($roleNameEn == 'zg'){
                            $ter = $this::$teacherRoleId;
                            $xsr = $this::$studentRoleId;
                            $zjr = $this::$jZroleId;
                            $checkArr = M('')->query("select name from mks_user where scId=$scId and roleId!=$ter and roleId!=$xsr and roleId!=$zjr");
                        }
                        $newck = array();
                        foreach($checkArr as $key => $value){
                            $namexx = explode('(',$value['name']);
                            $namexx = $namexx[0];
                            $newck[$namexx] = 1;
                        }
                        $newck1 = array();
                        $allCount = 0;
                        foreach($strs as $key => $value){
                            if(isset($newck[$value['name']])){
                                $allCount++;
                            }
                            $newck1[$value['name']][] = 1;
                            if(count($newck1[$value['name']])>=2){
                                $allCount++;
                            }
                        }
                        if($allCount){
                            $arrayRen = array(
                                'strs' => $strs,
                                'newck' => $newck,
                                'newck1' => $newck1,
                                'roleNameEn' => $roleNameEn,
                                'gradeId' =>I('post.gradeid')
                            );
                            $token = $this->createToken();
                            $this::redis_operation($token,json_encode($arrayRen),20,1);
                            $this->returnJson(array('statu' => '202','message' => "当前角色系统已经有 $allCount 个重名的是否继续",'token' => $token),true);
                        }
                        foreach($strs as $key => $value){
                            $relName = $value['name'];
                            if(isset($newck[$value['name']]) || count($newck1[$value['name']])>=2){
                                $relName = $relName.'('.substr($value["number"],6,9).')';
                                $value['name'] = $relName;
                            }
                            if($roleNameEn == 'xs'){
                                $isResSchool = $value['isResSchool'];
                                unset($value['isResSchool']);
                                $id = M('user')->add($value);
                                $password = $this->InitialPassword();
                                $userJz[] = array(
                                    'className' => $value['className'],
                                    'grade' => $value['grade'],
                                    'name' => $relName.'家长',
                                    'number' => $value['number']-1,
                                    'scPrefix' => $value['scPrefix'],
                                    'scId' => $value['scId'],
                                    'roleId' => $this::$jZroleId,
                                    'password' => $this->create_password($password,self::$password_key,self::$password_key1),
                                    'InitialPassword' => $password,
                                    'account' => $prefix.($value['number']-1),
                                    'childId' => $id,
                                    'childName' => $value['name'],
                                    'gradeId' => $value['gradeId'],
                                    'class' => $value['class'],
                                    'createTime' => $createTime
                                );
                                $student_info[] = array(
                                    'userId' => $id,
                                    'scId' => $value['scId'],
                                    'gradeId' => $value['gradeId'],
                                    'classId' => $value['class'],
                                    'name' => $relName,
                                    'className' =>  $value['className'],
                                    'grade' =>  $value['grade'],
                                    'idCard' =>  $value['idCard'],
                                    'enrolTime' => date('Y-m-d H:i:s'),
                                    'createTime' =>  $value['createTime'],
                                    'phone' =>  $value['phone'],
                                    'sex' =>  $value['sex'],
                                    'birthday' => $value['birth'],
                                    'serialNumber' => $value['serialNumber'],
                                    'isResSchool' =>$isResSchool,
                                    'studentCard' => $value['number']
                                );
                                $cen_reg_info[] = array(
                                    'userId' => $id,
                                    'scId' => $value['scId'],
                                    'createTime' =>  $value['createTime'],
                                    'cenRegType' =>  $value['hklx']
                                );
                                $school_rollinfo[] = array(
                                    'userId' => $id,
                                    'scId' => $value['scId'],
                                    'createTime' =>  $value['createTime'],
                                    'sex' => $value['sex'],
                                    'admCategory' => $value['admCategory']
                                );
                                $parents_info[] = array(
                                    'userId' => $id,
                                    'scId' => $value['scId'],
                                    'createTime' =>  $value['createTime'],
                                );
                                $other_info[] = array(
                                    'userId' => $id,
                                    'scId' => $value['scId'],
                                    'createTime' =>  $value['createTime'],
                                );
                                $tuition_info[] = array(
                                    'userId' => $id,
                                    'scId' => $value['scId'],
                                    'createTime' =>  $value['createTime'],
                                );
                            }else{
                                $zg[] = $value;
                            }
                        }
                        if($roleNameEn == 'xs'){
                            M('user')->addAll($userJz);
                            M('student_info')->addAll($student_info);
                            M('cen_reg_info')->addAll($cen_reg_info);
                            M('school_rollinfo')->addAll($school_rollinfo);
                            M('parents_info')->addAll($parents_info);
                            M('other_info')->addAll($other_info);
                            M('tuition_info')->addAll($tuition_info);
                        }else{
                            $role = M('role')->field('roleId,roleName')->where(array('scId' => array(array('eq',0),array('eq',$scId),'or')))->select();
                            $roleRel = array();
                            foreach ($role as $key2 => $value2){
                                $roleRel[$value2['roleName']] = $value2['roleId'];
                            }
                            if($roleNameEn == 'zg'){
                                foreach ($zg as $key => $value){
                                    if(isset($roleRel[$value['post']])){
                                        $zg[$key]['roleId'] = $roleRel[$value['post']];
                                    }else{
                                        $this->returnJson(array('statu' => 3,'message' =>'请填写基本信息-权限设置中创建好的角色' ),true);
                                    }
                                }
                            }else{
                                foreach ($zg as $key => $value){
                                    if(!$value['teachingSubjectsId']){
                                        $this->returnJson(array('statu' => 3,'message' =>'请填写基本信息-科目信息中创建好的科目' ),true);
                                    }
                                }
                            }
                            M('user')->addAll($zg);
                        }
                        $this->returnJson(array('statu' => 1, 'message' => 'export success'),true);
                    }
                }
            }
            $this->returnJson(array('statu' => 101,'message' => 'upload file fail'),true);

        }
        if($type == 'getUserList'){

        }
        if($type == 'addStudent'){//创建学生
            //serialNumber
            $gradeData = M('grade')->where(array('scId' => $scId,'gradeid' => I('post.grade')))->find();
            $classData = M('class')->where(array('scId' => $scId,'classid' => I('post.className')))->find();
            $gradeId = I('post.grade');
            $classId = I('post.className');
            $className = $classData['classname'];
            $gradeName = $gradeData['name'];
            $password = $this->InitialPassword();
            $number = $maxNumber;
            $relName = I('post.name');
            if(M('user')->field('id')->where(array('scId' => $scId,'name' => I('post.name')))->find()){
                $relName = $relName.'('.substr($number,6,9).')';
            }
            $serNmu = M('')->query("select MAX(serialNumber) as num FROM mks_user WHERE scId = $scId AND gradeId=$gradeId AND class=$classId");
            $serNmu = $serNmu['num'];
            if($serNmu){
                $serNmu++;
            }else{
                $serNmu = 1;
            }
            $zx = I('post.ifExtern');
            if($zx){
                $zx = 1;
            }else{
                $zx = 0;
            }
            $return = M('user')->add(array(
                'phone' => I('post.phone'),
                'className' => I('post.className'),
                'sex' => I('post.sex'),
                'name' => $relName,
                'grade' => I('post.grade'),
                'serialNumber' => $serNmu,
                'className' => $className,
                'grade' => $gradeName,
                'gradeId' => $gradeId,
                'class' =>$classId,
                'number' => $number,
                'scPrefix' => $prefix,
                'scId' => $scId,
                'roleId' => $this::$studentRoleId,
                'password' => $this->create_password($password,self::$password_key,self::$password_key1),
                'InitialPassword' => $password,
                'account' => $prefix.$number,
                'createTime' => $createTime,
            ));
            if($return){
                $password = $this->InitialPassword();
                M('user')->add(
                    array(
                        'statu' => I('post.statu'),
                        'childId' => $return,
                        'childName' => $relName,
                        'name' => $relName.'家长',
                        'number' => $number+1,
                        'scPrefix' => $prefix,
                        'scId' => $scId,
                        'className' => $className,
                        'grade' => $gradeName,
                        'gradeId' => $gradeId,
                        'class' =>$classId,
                        'roleId' => $this::$jZroleId,
                        'password' => $this->create_password($password,self::$password_key,self::$password_key1),
                        'InitialPassword' => $password,
                        'account' => $prefix.($number+1),
                        'createTime' => $createTime
                    )
                );
                M('student_info')->add(array(
                    'userId' => $return,
                    'scId' => $scId,
                    'name' =>$relName,
                    'className' => $className,
                    'serialNumber' => $serNmu,
                    'grade' => $gradeName,
                    'gradeId' => $gradeId,
                    'enrolTime' => date('Y-m-d H:i:s'),
                    'classId' =>$classId,
                    'idCard' =>  I('post.idCard'),
                    'createTime' => $createTime,
                    'phone' =>  I('post.phone'),
                    'sex' =>  I('post.sex'),
                    'studentCard' => $number
                ));
                M('cen_reg_info')->add(array(
                    'userId' => $return,
                    'scId' => $scId,
                    'createTime' =>  $createTime,
                    'cenRegType' => I('post.hklx'),
                    'nativePlace' =>  I('post.hkAddress'),
                ));
                M('school_rollinfo')->add(array(
                    'userId' => $return,
                    'scId' => $scId,
                    'createTime' =>  $createTime,
                    'sex' =>  I('post.sex'),
                    'isTempStudy' => $zx
                ));
                M('parents_info')->add(array(
                    'userId' => $return,
                    'scId' => $scId,
                    'createTime' =>  $createTime,
                ));
                M('other_info')->add(array(
                    'userId' => $return,
                    'scId' => $scId,
                    'createTime' =>  $createTime,
                ));
                M('tuition_info')->add(array(
                    'userId' => $return,
                    'scId' => $scId,
                    'createTime' =>  $createTime,
                ));
                $this->returnJson(array('statu' => 1,'message' => 'add  succeess'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'add  fail'),true);
        }
        if($type == 'checkUserMore'){
            $name = I('post.name');
            $roleId = I('post.roleId');
            if($roleId == -2){
                $data  = M('user')->where(array('scId' => $scId,'roleId' => $this::$teacherRoleId,'name' => array('like',$name.'%')))->select();
                $count = count($data);
                if($count>0){
                    $this->returnJson(array('statu' => 0,'count'=> $count,'message' => "当前插入教师已有".$count."个重名重角色记录，是否继续上传"),true);
                }else{
                    $this->returnJson(array('statu' => 1,'message' => 'pass'),true);
                }
            }
            else if($roleId == -1){
                $data = M('user')->where(array('scId' => $scId,'roleId' => $this::$studentRoleId,'name' => array('like',$name.'%')))->select();
                $count = count($data);
                if($count>0){
                    $this->returnJson(array('statu' => 0,'count'=> $count,'message' => "当前插入学生已有".$count."个重名重角色记录，是否继续上传"),true);
                }else{
                    $this->returnJson(array('statu' => 1,'message' => 'pass'),true);
                }
            }else{
                $data  = M('user')->where(array('scId' => $scId,'roleId' => $roleId,'name' => array('like',$name.'%')))->select();
                $count = count($data);
                if($count>0){
                    $this->returnJson(array('statu' => 0,'count'=> $count,'message' => "当前插入职工已有".$count."个重名重角色记录，是否继续上传"),true);
                }else{
                    $this->returnJson(array('statu' => 1,'message' => 'pass'),true);
                }
            }
        }
        if($type == 'addTeather'){//创建老师
            $number = $maxNumber;
            $password = $this->InitialPassword();
            $relName = I('post.name');
            if(M('user')->field('id')->where(array('scId' => $scId,'name' =>I('post.name')))->find()){
                $relName = $relName.'('.substr($number,6,9).')';
            }
            $department = M('department')->field('departmentId,departmentName')->where(array('departmentId' => I('post.departmentId')))->find();
            $subjectName = M('subject')->where(array('subjectid' => I('post.teachingSubjectsId')))->find();
            $brrr = null;
            if( I('post.birth')){
                $brrr = I('post.birth');
            }
            $return = M('user')->add(array(
                'phone' => I('post.phone'),
                'sex' => I('post.sex'),
                'name' => $relName,
                'teachingSubjects' => $subjectName['subjectname'],
                'teachingSubjectsId' =>  I('post.teachingSubjectsId'),
                'politics' => I('post.politics'),
                'number' => $number,
                'post' => '教师',
                'scPrefix' => $prefix,
                'departmentId' => I('post.departmentId'),
                'department' => $department['departmentName'],
                'jobNumber' => $prefix.substr($number,5,9),
                'scId' => $scId,
                'roleId' => $this::$teacherRoleId,
                'password' => $this->create_password($password,self::$password_key,self::$password_key1),
                'account' => $prefix.$number,
                'InitialPassword' => $password,
                'statu' => 1,
                'idCard' => I('post.idCard'),
                'nation' => I('post.nation'),
                'birth' => $brrr,
                'idCardType' => I('post.idCardType'),
                'origin' => serialize(I('post.origin')),
                'homeAddress' => serialize(I('post.homeAddress')),
                'registerAddress' => serialize(I('post.registerAddress')),
                'nowAddress' => serialize(I('post.nowAddress')),
                'createTime' => $createTime
            ));
            if($return){
                $this->returnJson(array('statu' => 1,'message' => 'add  succeess'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'add  fail'),true);
        }
        if($type == 'getDepartment'){
            $data = M('department')->where(array('scId' => $scId))->select();
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $data),true);
        }
        if($type == 'addStaff'){//创建员工
            $number = $maxNumber;
            $relName = I('post.name');
            if(M('user')->field('id')->where(array('scId' => $scId,'name' =>I('post.name')))->find()){
                $relName = $relName.'('.substr($number,6,9).')';
            }
            $department = M('department')->field('departmentId,departmentName')->where(array('departmentId' => I('post.departmentId')))->find();
            if($roleId != $this::$adminRoleId){
                $password = $this->InitialPassword();
                $roleName = M('role')->field('roleName')->where(array('roleId' =>I('post.roleId')))->find();
                $return = M('user')->add(array(
                    'phone' => I('post.phone'),
                    'sex' => I('post.sex'),
                    'name' => $relName,
                    'post' => $roleName['roleName'],
                    'politics' => I('post.politics'),
                    'number' => $number,
                    'jobNumber' => $prefix.substr($number,5,9),
                    'scPrefix' => $prefix,
                    'departmentId' => I('post.departmentId'),
                    'department' => $department['departmentName'],
                    'scId' => $scId,
                    'roleId' => I('post.roleId'),
                    'password' => $this->create_password($password,self::$password_key,self::$password_key1),
                    'account' => $prefix.$number,
                    'statu' => 1,
                    'InitialPassword' => $password,
                    'createTime' => $createTime,
                    'birth' => I('post.birth'),
                    'idCard' => I('post.idCard'),
                    'idCardType' => I('post.idCardType'),
                    'nation' => I('post.nation'),
                    'origin' => serialize(I('post.origin')),
                    'homeAddress' => serialize(I('post.homeAddress')),
                    'registerAddress' => serialize(I('post.registerAddress')),
                    'nowAddress' => serialize(I('post.nowAddress')),
                ));
                if($return){
                    $this->returnJson(array('statu' => 1,'message' => 'add  succeess'),true);
                }
                $this->returnJson(array('statu' => 0,'message' => 'add  fail'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'no permissions'),true);
        }
        if($type == 'getBmList'){
            $data = M('department')->field('departmentId,departmentName')->where(array('scId' => $scId))->select();
            $this->returnJson(array('statu' => 1,'data' => $data,'message' =>'success'),true);
        }
        if($type == 'updataStudent'){
            $userId = I('post.userId');
            $updataType = I('post.updataType');
            $userData = I('post.userData');
            if($updataType == 'list'){
                M('user')-> where(array('userId' => $userData['userId'],'roleId' => $this::$studentRoleId,'scId' => $scId))->setField($userData['data']);
            }
        }
        if($type == 'getTeacherById'){
            $id = I('get.id');
            $user = M('user')->where(array('scId' => $scId,'id' => $id,'roleId' => $this::$teacherRoleId))->find();
            unset($user['account']);
            unset($user['password']);
            unset($user['scPrefix']);
            unset($user['number']);
            unset($user['state']);
            unset($user['InitialPassword']);
            $user['origin'] = unserialize( $user['origin']);
            $user['registerAddress'] = unserialize( $user['registerAddress']);
            $user['homeAddress'] = unserialize( $user['homeAddress']);
            $user['nowAddress'] = unserialize( $user['nowAddress']);
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $user),true);
        }
        if($type == 'getZgById'){
            $id = I('get.id');
            $user = M('user')->where(array('scId' => $scId,'id' => $id,'roleId' => array(array('neq',$this::$teacherRoleId),array('neq',$this::$adminRoleId),array('neq',$this::$studentRoleId),array('neq',$this::$jZroleId),'and')))->find();
            unset($user['account']);
            unset($user['password']);
            unset($user['scPrefix']);
            unset($user['number']);
            unset($user['InitialPassword']);
            $user['origin'] = unserialize( $user['origin']);
            $user['registerAddress'] = unserialize( $user['registerAddress']);
            $user['homeAddress'] = unserialize( $user['homeAddress']);
            $user['nowAddress'] = unserialize( $user['nowAddress']);
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $user),true);
        }
        if($type == 'getTeacherPersonData'){
            if($userRoleId == $this::$teacherRoleId){
                $user = M('user')->where(array('scId' => $scId,'id' => $userUserId,'roleId' => $this::$teacherRoleId))->find();
                unset($user['account']);
                unset($user['password']);
                unset($user['scPrefix']);
                unset($user['number']);
                unset($user['state']);
                unset($user['InitialPassword']);
                $user['name'] = explode('(',$user['name']);
                $user['name'] = $user['name'][0];
                $user['origin'] = unserialize( $user['origin']);
                $user['registerAddress'] = unserialize( $user['registerAddress']);
                $user['homeAddress'] = unserialize( $user['homeAddress']);
                $user['nowAddress'] = unserialize( $user['nowAddress']);
                $this->returnJson(array('statu' => 1,'isTeacher' => 1,'message' => 'success','data' => $user),true);
            }else{
                $user = M('user')->where(array('scId' => $scId,'id' => $userUserId,'roleId' => array(array('neq',$this::$teacherRoleId),array('neq',$this::$adminRoleId),array('neq',$this::$studentRoleId),array('neq',$this::$jZroleId),'and')))->find();
                unset($user['account']);
                unset($user['password']);
                unset($user['scPrefix']);
                unset($user['number']);
                unset($user['InitialPassword']);
                $user['name'] = explode('(',$user['name']);
                $user['name'] = $user['name'][0];
                $user['origin'] = unserialize( $user['origin']);
                $user['registerAddress'] = unserialize( $user['registerAddress']);
                $user['homeAddress'] = unserialize( $user['homeAddress']);
                $user['nowAddress'] = unserialize( $user['nowAddress']);
                $this->returnJson(array('statu' => 1,'isTeacher' => 0,'message' => 'success','data' => $user),true);
            }
        }
        if($type == 'updataTeacherPersonData'){
            if($userRoleId == $this::$teacherRoleId){
                $userData = I('post.userData');
                if(!$userData){
                    $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
                }
                $userData['origin'] = serialize($userData['origin']);
                $userData['registerAddress'] = serialize($userData['registerAddress']);
                $userData['homeAddress'] = serialize($userData['homeAddress']);
                $userData['nowAddress'] = serialize($userData['nowAddress']);
                $subjectName = M('subject')->where(array('subjectid' => $userData['teachingSubjectsId']))->find();
                $userData['teachingSubjects'] = $subjectName['subjectname'];
                $department = M('department')->field('departmentId,departmentName')->where(array('departmentId' => $userData['departmentId']))->find();
                $userData['department'] = $department['departmentName'];
                unset($userData['account']);
                unset($userData['password']);
                unset($userData['scPrefix']);
                unset($userData['number']);
                unset($userData['InitialPassword']);
                unset($userData['roleId']);
                unset($userData['departmentId']);
                unset($userData['department']);
                unset($userData['id']);
                unset($userData['name']);
                if(M('user')-> where(array('id' => $userUserId,'scId' => $scId))->setField($userData)===false){
                    $this->returnJson(array('statu' => 0,'message' => '更新失败'),true);
                }
                $this->returnJson(array('statu' => 1,'message' => '更新成功'),true);
            }else{
                $userData = I('post.userData');
                if(!$userData){
                    $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
                }
                $userData['origin'] = serialize($userData['origin']);
                $userData['registerAddress'] = serialize($userData['registerAddress']);
                $userData['homeAddress'] = serialize($userData['homeAddress']);
                $userData['nowAddress'] = serialize($userData['nowAddress']);
                $roleName = M('role')->field('roleName')->where(array('roleId' => $userData['roleId']))->find();
                $userData['post'] = $roleName['roleName'];
                $department = M('department')->field('departmentId,departmentName')->where(array('departmentId' => $userData['departmentId']))->find();
                $userData['department'] = $department['departmentName'];
                unset($userData['account']);
                unset($userData['password']);
                unset($userData['scPrefix']);
                unset($userData['number']);
                unset($userData['id']);
                unset($userData['name']);
                unset($userData['InitialPassword']);
                unset($userData['departmentId']);
                unset($userData['department']);
                if(M('user')-> where(array('id' => $userUserId,'scId' => $scId))->setField($userData)){
                    $this->returnJson(array('statu' => 1,'message' => 'success'),true);
                }
                $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
            }
        }
        if($type == 'updataTeacher'){
            $userData = I('post.userData');
            $userData['origin'] = serialize($userData['origin']);
            $userData['registerAddress'] = serialize($userData['registerAddress']);
            $userData['homeAddress'] = serialize($userData['homeAddress']);
            $userData['nowAddress'] = serialize($userData['nowAddress']);
            $subjectName = M('subject')->where(array('subjectid' => $userData['teachingSubjectsId']))->find();
            $userData['teachingSubjects'] = $subjectName['subjectname'];
            $department = M('department')->field('departmentId,departmentName')->where(array('departmentId' => $userData['departmentId']))->find();
            $userData['department'] = $department['departmentName'];
            unset($userData['account']);
            unset($userData['password']);
            unset($userData['scPrefix']);
            unset($userData['number']);
            unset($userData['InitialPassword']);
            unset($userData['roleId']);
            if(M('user')-> where(array('id' => $userData['id'],'scId' => $scId))->setField($userData)===false){
                $this->returnJson(array('statu' => 0,'message' => '更新失败'),true);
            }
            $this->returnJson(array('statu' => 1,'message' => '更新成功'),true);
        }
        if($type == 'updataZg'){
            $userData = I('post.userData');
            $userData['origin'] = serialize($userData['origin']);
            $userData['registerAddress'] = serialize($userData['registerAddress']);
            $userData['homeAddress'] = serialize($userData['homeAddress']);
            $userData['nowAddress'] = serialize($userData['nowAddress']);
            $roleName = M('role')->field('roleName')->where(array('roleId' => $userData['roleId']))->find();
            $userData['post'] = $roleName['roleName'];
            $department = M('department')->field('departmentId,departmentName')->where(array('departmentId' => $userData['departmentId']))->find();
            $userData['department'] = $department['departmentName'];
            unset($userData['account']);
            unset($userData['password']);
            unset($userData['scPrefix']);
            unset($userData['number']);
            unset($userData['InitialPassword']);
            if(M('user')-> where(array('id' => $userData['id'],'scId' => $scId))->setField($userData) === false){
                $this->returnJson(array('statu' => 0,'message' => '修改失败'),true);
            }
            $this->returnJson(array('statu' => 1,'message' => '修改成功'),true);
        }
    }
    private function pageGo($data){
        $valueData = I('get.value');
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
        $sort = I('get.orderBy');
        $sortData =I('get.sort');
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
                $zzz = array();
                foreach($returnList as $key => $value){
                    $zzz[] = $value;
                }
                $returnList = $zzz;
            }
            if($sortData == 'ascending'){
                krsort($returnList);
                $zzz = array();
                foreach($returnList as $key => $value){
                    $zzz[] = $value;
                }
                $returnList = $zzz;
            }
            $data = $returnList;
        }
        $page =  $page = I('get.page');
        $pageSize = 18;
        $maxPage = ceil(count($data)/$pageSize);
        if($page>$maxPage){
            $page = $maxPage;
        }
        if($page<1){
            $page = 1;
        }
        $start = ($page-1)*$pageSize;
        $end = ($page)*$pageSize;
        $return = array();
        for($i = $start ; $i < $end; $i++){
            if(isset($data[$i])){
                /*if($data[$i]['grade']){
                }else{
                }*/
                $return[] = $data[$i];
            }
        }
        $returnData = array();
        $returnData['data'] = $return;
        $returnData['page'] = $page;
        $returnData['maxpage'] = $maxPage;
        return $returnData;
    }
    private function exportUserXi($flieExtension,$targetFile,$roleNameEn,$prefix,$maxNumber,$createTime,$scId,$roleId){
        $this->pageList($this::$pageSize,M('user'),array('roleId' => $this::$teacherRoleId,'scId' => $scId),'id,roleId,name,phone,sex,teachingSubjects,jobNumber,politics',array('roleId' => $this::$teacherRoleId,'scId' => $scId));
    }
    private function  pageList($scId,$table,$roleId,$valueData = 0,$strLike ='',$sort = 0,$sortString =''){
        $i = 1;
        //获取当前页数
        $teacherRoleId = $roleId;
        if($valueData == 0 && $sort==0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId";
        }
        if($valueData!=0 && $sort!=0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId  ".$strLike.$sortString;
        }
        if($valueData!=0 && $sort==0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId   ".$strLike;
        }
        if($valueData==0 && $sort!=0){
            $allcount = "SELECT count('id') as count FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $sortString";
        }
        $allList = M('user')->query($allcount);
        $allList = $allList[0]['count'];
        $pagesize = I('get.pageSize');
        $maxpage = ceil($allList/$pagesize);
        $page = I('get.page');
        if($page <= 1){
            $page = 1;
        } else if($page>=$maxpage){
            $page = $maxpage;
        } else{
            $page = I('get.page');
        }
        if($maxpage==0){
            $maxpage =1;
        }
        $startpage = ($page-1)*$pagesize;
        if($valueData == 0 && $sort==0){
            $userSql = "SELECT * FROM $table WHERE scId=$scId and roleId = $teacherRoleId   ORDER BY createTime DESC LIMIT $startpage,$pagesize";
        }
        if($valueData!=0 && $sort!=0){
            $userSql = "SELECT * FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $strLike $sortString  LIMIT $startpage,$pagesize";
        }
        if($valueData!=0 && $sort==0){
            $userSql = "SELECT * FROM $table WHERE scId=$scId and roleId = $teacherRoleId  $strLike ORDER BY createTime DESC LIMIT $startpage,$pagesize";
        }
        if($valueData==0 && $sort!=0){
            $userSql = "SELECT * FROM $table WHERE scId=$scId and roleId = $teacherRoleId   $sortString  LIMIT $startpage,$pagesize";
        }
        /*foreach($data as $key => $value){
            $data[$key]['maxpage'] = $maxpage;
            $data[$key]['page'] = $page;
        }*/
        $data['data'] =  M('user')->query($userSql);
        foreach($data['data'] as $key => $value){
            unset($data['data'][$key]['account']);
            unset($data['data'][$key]['password']);
            unset($data['data'][$key]['scPrefix']);
            unset($data['data'][$key]['number']);
            unset($data['data'][$key]['InitialPassword']);
            $data['data'][$key]['origin'] = unserialize($data['data'][$key]['origin']);
            $data['data'][$key]['registerAddress'] = unserialize($data['data'][$key]['registerAddress']);
            $data['data'][$key]['homeAddress'] = unserialize($data['data'][$key]['homeAddress']);
            $data['data'][$key]['nowAddress'] = unserialize($data['data'][$key]['nowAddress']);
        }
        $data['maxpage'] = $maxpage;
        $data['count'] = (int)$allList;
        $data['page'] = $page;
        //$data['maxpage']['maxpage'] = $maxpage;
        // $data['page']['page'] = $page;
        return $data;
    }
    private function download($file_dir,$file_name){
        $file = fopen ( $file_dir.$file_name, "r" );
        Header ( "Content-type: application/octet-stream" );
        Header ( "Accept-Ranges: bytes" );
        Header ( "Content-Length: " . filesize ( $file_dir . $file_name ) );
        Header ( "Content-Disposition: attachment; filename=" . $file_name );
        echo fread ( $file, filesize ( $file_dir . $file_name ) );
        fclose ( $file );
    }
    private function pageListSql($scId,$table,$valueData = 0,$strLike ='',$sort = 0,$sortString =''){
        $grade = I('get.grade');
        $class = I('get.class');
        //echo $class;
        //$user = M('user')->where(array('grade' => $grade))
        $str = '';
        $len = count($class);
        $i = 1;
        foreach($class as $key => $value){
            $i++;
            if($i<=$len){
                $str = 'classId='.$value.' or '.$str;
            }else{
                $str = $str.'classId='.$value;
            }
        }
        $allClass = M('')->query("select classid,classname,major,branch from mks_class where scId=$scId AND grade=$grade");
        $bra = M('class_branch')->where(array('scId' => array(array('eq',0),array('eq',$scId),'or')))->select();
        $braRe = array();
        foreach($bra as $key => $value){
            $braRe[$value['branchid']] = $value['branch'];
        }
        $may = M('class_major')->where(array('scId' => array(array('eq',0),array('eq',$scId),'or')))->select();
        $mayRe = array();
        foreach($may as $key => $value){
            $mayRe[$value['majorid']] = $value['majorname'];
        }
        $allClassRe = array();
        foreach($allClass as $key => $value){
            $allClassRe[$value['classname']] = array(
                'stream' => $braRe[$value['branch']],
                'major' => $mayRe[$value['major']],
            );
        }
        $userAll = "SELECT userId,className,grade,name FROM mks_student_info WHERE scId=$scId and gradeId=$grade and  ($str)";
        $schoolAllUser =  M('')->query($userAll);
        $schoolAllUserCheck = array();
        foreach($schoolAllUser as $key => $value){
            $schoolAllUserCheck[$value['userId']] = $value;
        }
        $allReturn = array();
        $allReturn = M('')->query("select * from $table where scId=$scId");
        $allReturnTrue = array();
        foreach($allReturn as $key => $value){
            if(isset($schoolAllUserCheck[$value['userId']])){
                $value['className'] = $schoolAllUserCheck[$value['userId']]['className'];
                $value['grade'] = $schoolAllUserCheck[$value['userId']]['grade'];
                $value['name'] = $schoolAllUserCheck[$value['userId']]['name'];
                $allReturnTrue[] = $value;
            }
        }
        $data = $allReturnTrue;
        $valueData = I('get.value');
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
        $sortData = I('get.sort');
        $sort =I('get.sortType');
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
                $zzz = array();
                foreach($returnList as $key => $value){
                    $zzz[] = $value;
                }
                $returnList = $zzz;
            }
            if($sortData == 'ascending'){
                krsort($returnList);
                $zzz = array();
                foreach($returnList as $key => $value){
                    $zzz[] = $value;
                }
                $returnList = $zzz;
            }
            $data = $returnList;
        }
        $page = I('get.page');
        $pageSize = I('get.pageSize');
        $maxPage = ceil(count($data)/$pageSize);
        if($page>$maxPage){
            $page = $maxPage;
        }
        if($page<1){
            $page = 1;
        }
        $start = ($page-1)*$pageSize;
        $end = ($page)*$pageSize;
        $return = array();
        for($i = $start ; $i < $end; $i++){
            if(isset($data[$i])){
                /*if($data[$i]['grade']){
                }else{
                }*/
                $data[$i]['stream'] = $allClassRe[$data[$i]['className']]['stream'];
                $data[$i]['major'] =  $allClassRe[$data[$i]['className']]['major'];
                $return[] = $data[$i];
            }
        }
        $data = array();
        $data['tr'] = $this::getTr($table);
        $data['data'] =  $return;
        $data['maxpage'] = $maxPage;
        $data['page'] = $page;
        //$data['maxpage']['maxpage'] = $maxpage;
        // $data['page']['page'] = $page;
        return $data;
    }
    private function delStudent($userId){/********************删除学生时同时删除与该学生相关的考试和综合素养信息，参数userId 学生ID**********************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        M()->startTrans();
        $dao1=M('examination_arrange');
        $dao2=M('examination_student');
        $dao3=M('examination_results');
        $dao4=M('examination_number');
        $dao5=M('attainment_student');
        $dao6=M('attainment_score');
        $where1['userid']=$userId;
        $where1['scId']=$scId;
        $where2['userId']=$userId;
        $where2['scId']=$scId;
        $where3['studentId']=$userId;
        $where3['scId']=$scId;
        $f1=$dao1->where($where1)->delete();
        $f2=$dao2->where($where1)->delete();
        $f3=$dao3->where($where1)->delete();
        $f4=$dao4->where($where1)->delete();
        $f5=$dao5->where($where2)->delete();
        $f6=$dao6->where($where3)->delete();
        if($f1===false||$f2===false||$f3===false||$f4===false||$f5===false||$f6===false){
            M()->rollback();
            return false;
        }else{
            M()->commit();
            return true;
        }
    }
    public function getGrade(){
        $session = $this->get_session('loginCheck',false);
        $scId = $session['scId'];
        $userRoleId = $session['roleId'];
        $type = I('get.type');
        $userId = $session['userId'];
        if($type == 'getGradeList'){
            $getSession = $this->get_session('loginCheck',false);
            $qxModelName = $getSession['scId'].'school'.$getSession['roleId'].'navList';
            $userModelList = $this->redis_operation($qxModelName,0,0,3);
            $userModelList = json_decode($userModelList,true);
            foreach($userModelList as $key => $value){
                if($value['modelName'] == '所有学生资料查看'){
                    $data = M('')->query("SELECT name,gradeid FROM mks_grade where scId=$scId ORDER BY name");
                    foreach($data as $key => $value){
                        $data[$key]['znGradeName'] = $this::gradeToZhong($value['name']);
                    }
                    $this->returnJson($data,true);
                }
                if($value['modelName'] == '部分学生资料查看'){
                    if($return =  M('grade')->field('name,gradeid')->where(array('userId' => $userId,'scId' => $scId))->select()){
                        foreach($return as $key => $value){
                            $return[$key]['znGradeName'] = $this::gradeToZhong($value['name']);
                        }
                        $this->returnJson($return,true);
                    }if($return = M('class')->field('grade')->where(array('userid' => $userId,'scId' => $scId))->find()){
                        $returnrEL = M('grade')->field('name,gradeid')->where(array('gradeid' => $return['grade'],'scId' => $scId))->select();
                        foreach($returnrEL as $key => $value){
                            $returnrEL[$key]['znGradeName'] = $this::gradeToZhong($value['name']);
                        }
                        $this->returnJson($returnrEL,true);
                    }
                }
                if($userRoleId == $this::$adminRoleId){
                    $data = M('')->query("SELECT name,gradeid FROM mks_grade where scId=$scId ORDER BY name");
                    foreach($data as $key => $value){
                        $data[$key]['znGradeName'] = $this::gradeToZhong($value['name']);
                    }
                    $this->returnJson($data,true);
                }
            }
        }
        if($type == 'getClass'){
            $getSession = $this->get_session('loginCheck',false);
            $qxModelName = $getSession['scId'].'school'.$getSession['roleId'].'navList';
            $userModelList = $this->redis_operation($qxModelName,0,0,3);
            $userModelList = json_decode($userModelList,true);
            $grade = I('get.gradeid');
            foreach($userModelList as $key => $value){
                if($value['modelName'] == '所有学生资料查看'){
                    $class = M('class')->field('classname,classid')->where(array('grade' => $grade ,'scId' => $scId))->order('classname')->select();
                    if(!$class){
                        $class = array();
                    }
                    $this->returnJson($class,true);
                }
                if($value['modelName'] == '部分学生资料查看'){
                    if($return =  M('grade')->field('name,gradeid')->where(array('userId' => $userId,'gradeid' => $grade,'scId' => $scId))->select()){
                        $class = M('class')->field('classname,classid')->where(array('grade' => $grade ,'scId' => $scId))->order('classname')->select();
                        if(!$class){
                            $class = array();
                        }
                        $this->returnJson($class,true);
                    }if($return = M('class')->field('grade,classid')->where(array('userid' => $userId,'scId' => $scId))->find()){
                        $class = M('class')->field('classname,classid')->where(array('grade' => $grade,'classid' => $return['classid'],'scId' => $scId))->order('classname')->select();
                        $this->returnJson($class,true);
                    }
                }
                if($userRoleId == $this::$adminRoleId){
                    $class = M('class')->field('classname,classid')->where(array('grade' => $grade ,'scId' => $scId))->order('classname')->select();
                    if(!$class){
                        $class = array();
                    }
                    $this->returnJson($class,true);
                }
            }
        }
        if($type == 'getSelectType'){
            $data  = array(
                '0' => array('name' => '学生基本信息','ListType' => 'jbXi'),
                '1' => array('name' => '学生学籍信息','ListType' => 'xjXi'),
                '2' => array('name' => '学生户籍信息','ListType' => 'hjXi'),
                '3' => array('name' => '学生家长信息','ListType' => 'jzXi'),
                '4' => array('name' => '学生学费信息','ListType' => 'xfXi'),
                '5' => array('name' => '学生其他信息','ListType' => 'qtXi'),
            );
            $this->returnJson($data,true);
        }
    }
    public function getTr($table){
        if($table == 'student_xi_rel'){
            return array(
              '0' => array(
                'en' => 'name',
                'zh' => '姓名'
            ),
                '1' => array(
                'en' => 'sex',
                'zh' => '性别'
            ), '2' => array(
                'en' => 'birth',
                'zh' => '出生日期'
            ), '3' => array(
                'en' => 'className',
                'zh' => '班级'
            ),  '4' => array(
                'en' => 'phone',
                'zh' => '手机号码'
            ), '5' => array(
                'en' => 'hklx',
                'zh' => '户口类型'
            ), '6' => array(
                'en' => 'idCard',
                'zh' => '身份证号'
            ), '7' => array(
                'en' => 'admCategory',
                'zh' => '录取类别'
            ));
        }
        if($table == 'mks_student_info'){
            return $arrayTr = array(
                '0' => array(
                    'en' => 'logo',
                    'zh' => '图片'
                ),
                '1' => array(
                    'en' => 'className',
                    'zh' => '班级'
                ),
               '2' => array(
                    'en' => 'serialNumber',
                    'zh' => '班级座号'
                ),
                '3' => array(
                    'en' => 'name',
                    'zh' => '姓名'
                ),
                '4' => array(
                    'en' => 'sex',
                    'zh' => '性别'
                ), '5' => array(
                    'en' => 'height',
                    'zh' => '身高'
                ), '6' => array(
                    'en' => 'birthday',
                    'zh' => '出生日期'
                ),
                '7' => array(
                    'en' => 'phone',
                    'zh' => '手机号码'
                ),
                '8' => array(
                    'en' => 'idCardType',
                    'zh' => '身份证类型'
                ),
                '9' => array(
                    'en' => 'idCard',
                    'zh' => '身份证号'
                ),
                '10' => array(
                    'en' => 'nowHomePath',
                    'zh' => '现住地址'
                ),
                '11' => array(
                    'en' => 'nowHomePostcode',
                    'zh' => '现住地址邮编'
                ),
                '12' => array(
                    'en' => 'nation',
                    'zh' => '民族'
                ),
                '13' => array(
                    'en' => 'country',
                    'zh' => '国籍'
                ),
                '14' => array(
                    'en' => 'political',
                    'zh' => '政治面貌'
                ),'15' => array(
                    'en' => 'leagueTime',
                    'zh' => '入团时间'
                ),
                '16' => array(
                    'en' => 'enrolTime',
                    'zh' => '入学时间'
                ),
                '17' => array(
                    'en' => 'enrolWay',
                    'zh' => '入学方式'
                ),
                '18' => array(
                    'en' => 'isResSchool',
                    'zh' => '是否住校'
                ),
                '19' => array(
                    'en' => 'dorNumber',
                    'zh' => '宿舍号'
                ),
                '20' => array(
                    'en' => 'studentCard',
                    'zh' => '学生卡号'
                ),
            );
        }
        if($table == 'mks_school_rollinfo'){ //学籍信息
            return $arrayTr = array(
                '0' => array(
                    'en' => 'className',
                    'zh' => '班级'
                ),
                '1' => array(
                    'en' => 'name',
                    'zh' => '姓名'
                ),
                '2' => array(
                    'en' => 'isTempStudy',
                    'zh' => '是否借读'
                ), '3' => array(
                    'en' => 'isLeave',
                    'zh' => '是否休学'
                ), '4' => array(
                    'en' => 'isSubor',
                    'zh' => '是否靠挂'
                ),  '5' => array(
                    'en' => 'stream',
                    'zh' => '科类'
                ), '6' => array(
                    'en' => 'major',
                    'zh' => '专业'
                ), '7' => array(
                    'en' => 'studentCode',
                    'zh' => '国家学号'
                ), '8' => array(
                    'en' => 'admCategory',
                    'zh' => '录取类别'
                ), '9' => array(
                    'en' => 'eleSchool',
                    'zh' => '小学学校'
                ), '10' => array(
                    'en' => 'secSchool',
                    'zh' => '中学学校'
                ),
                '11' => array(
                    'en' => 'midExam',
                    'zh' => '中考分数'
                ),  '12' => array(
                    'en' => 'isResSchool',
                    'zh' => '是否在校'
                ),  '13' => array(
                    'en' => 'provinceNumber',
                    'zh' => '省统编学号'
                ),  '14' => array(
                    'en' => 'cityNumber',
                    'zh' => '市统编号'
                ),
            );
        }
        if($table == 'mks_cen_reg_info'){//户籍返回
            return $arrayTr = array(
                '0' => array(
                    'en' => 'name',
                    'zh' => '姓名'
                ),
                '1' => array(
                    'en' => 'className',
                    'zh' => '班级'
                ),  '2' => array(
                    'en' => 'perAddress',
                    'zh' => '户口所在地'
                ), '3' => array(
                    'en' => 'cenRegType',
                    'zh' => '户籍类型'
                ), '4' => array(
                    'en' => 'cenRegNature',
                    'zh' => '户口性质'
                ), '5' => array(
                    'en' => 'perAddressCode',
                    'zh' => '户口所在地代码'
                ), '6' => array(
                    'en' => 'originCode',
                    'zh' => '生源地代码'
                )
            );
        }
        if($table == 'mks_parents_info'){//家长信息
            return $arrayTr = array(
                '0' => array(
                    'en' => 'name',
                    'zh' => '姓名'
                ),
                '1' => array(
                    'en' => 'className',
                    'zh' => '班级'
                ),
                '2' => array(
                'en' => 'jzName1',
                'zh' => '家长1姓名'
                ),
                '3' => array(
                    'en' => 'relation1',
                    'zh' => '家长1关系'
                ), '4' => array(
                    'en' => 'jzPhone1',
                    'zh' => '家长1电话'
                ), '5' => array(
                    'en' => 'jzWorkUnit1',
                    'zh' => '家长1工作单位'
                ), '6' => array(
                    'en' => 'jzPost1',
                    'zh' => '家长1职位'
                ), '7' => array(
                    'en' => 'isGuardian1',
                    'zh' => '家长1是否是监护人'
                ), '8' => array(
                    'en' => 'jzCardType1',
                    'zh' => '家长1身份证类型'
                ),
                '9' => array(
                    'en' => 'jzIdCard1',
                    'zh' => '家长1身份证号'
                ),
                '10' => array(
                    'en' => 'jzNation1',
                    'zh' => '家长1民族'
                ),
                '11' => array(
                    'en' => 'jzNation1',
                    'zh' => '家长1户口所在地'
                ),
                '12' => array(
                    'en' => 'jzName2',
                    'zh' => '家长2姓名'
                ),  '13' => array(
                    'en' => 'relation2',
                    'zh' => '家长2关系'
                ), '14' => array(
                    'en' => 'jzPhone2',
                    'zh' => '家长2电话'
                ), '15' => array(
                    'en' => 'jzWorkUnit2',
                    'zh' => '家长2工作单位'
                ), '16' => array(
                    'en' => 'jzPost2',
                    'zh' => '家长2职位'
                ), '17' => array(
                    'en' => 'isGuardian2',
                    'zh' => '家长2是否是监护人'
                ), '18' => array(
                    'en' => 'jzCardType2',
                    'zh' => '家长2身份证类型'
                ),
                '19' => array(
                    'en' => 'jzIdCard2',
                    'zh' => '家长2身份证号'
                ),
                '20' => array(
                    'en' => 'jzNation2',
                    'zh' => '家长2民族'
                ),
                '21' => array(
                    'en' => 'jzNation2',
                    'zh' => '家长2户口所在地'
                ),
            );
        }
        if($table == 'studentExport'){//学费信息
            return $arrayTr = array(
                '0' => array(
                    'en' => 'name',
                    'zh' => '姓名'
                ),
                '1' => array(
                    'en' => 'className',
                    'zh' => '班级'
                ),  '2' => array(
                    'en' => 'serialNumber',
                    'zh' => '座位号'
                ), '3' => array(
                    'en' => 'sex',
                    'zh' => '性别'
                ), '4' => array(
                    'en' => 'phone',
                    'zh' => '电话'
                )
            );
        }
        if($table == 'mks_tuition_info'){//学费信息
            return $arrayTr = array(
                '0' => array(
                    'en' => 'name',
                    'zh' => '姓名'
                ),
                '1' => array(
                    'en' => 'className',
                    'zh' => '班级'
                ),  '2' => array(
                    'en' => 'openBank',
                    'zh' => '开户银行'
                ), '3' => array(
                    'en' => 'bankAccount',
                    'zh' => '开户银行账号'
                ), '4' => array(
                    'en' => 'accountHolder',
                    'zh' => '银行卡持有人'
                )
            );
        }
        if($table == 'mks_other_info') {//学生其他信息
            return $arrayTr = array(
                '0' => array(
                    'en' => 'className',
                    'zh' => '班级'
                ),
                '1' => array(
                    'en' => 'name',
                    'zh' => '姓名'
                ),
                '2' => array(
                    'en' => 'isSingleton',
                    'zh' => '是否是独生子女'
                ), '3' => array(
                    'en' => 'isPreschool',
                    'zh' => '是否受过学前教育'
                ), '4' => array(
                    'en' => 'isBehChild',
                    'zh' => '是否是留守儿童'
                ), '5' => array(
                    'en' => 'isTraChild',
                    'zh' => '是否进城务工人员随迁子女'
                ), '6' => array(
                    'en' => 'isOrphan',
                    'zh' => '是否孤儿'
                ), '7' => array(
                    'en' => 'isMartyrChild',
                    'zh' => '是否烈士或优抚子女'
                ), '8' => array(
                    'en' => 'disPerType',
                    'zh' => '残疾人类型'
                ), '9' => array(
                    'en' => 'trafficMode',
                    'zh' => '上下学交通方式'
                ), '10' => array(
                    'en' => 'distance',
                    'zh' => '上下学交通距离'
                ),
                '11' => array(
                    'en' => 'isTakeSchoolBus',
                    'zh' => '是否需要乘坐校车'
                ), '12' => array(
                    'en' => 'workerNumber',
                    'zh' => '社工证号'
                ),
                '13' => array(
                    'en' => 'healthStatus',
                    'zh' => '健康状况'
                ), '14' => array(
                    'en' => 'mailAddress',
                    'zh' => '通信地址'
                ),
                '15' => array(
                    'en' => 'isSubsidy',
                    'zh' => '是否享受一补'
                ), '16' => array(
                    'en' => 'isApplyFund',
                    'zh' => '是否需要资助'
                ),
                '17' => array(
                    'en' => 'bloodType',
                    'zh' => '是血型'
                ), '18' => array(
                    'en' => 'studentSource',
                    'zh' => '学生来源'
                ),
                '19' => array(
                    'en' => 'Email',
                    'zh' => '电子邮箱'
                ), '20' => array(
                    'en' => 'businessPage',
                    'zh' => '主页地址'
                ),
                '21' => array(
                    'en' => 'isGovBuyDegree',
                    'zh' => '是否政府购买学位'
                )
            );
        }
        if($table == 'zg'){
            return $arrayTr = array(
                '0' => array(
                    'en' => 'name',
                    'zh' => '姓名'
                ),
                '1' => array(
                    'en' => 'sex',
                    'zh' => '性别'
                ),  '2' => array(
                    'en' => 'post',
                    'zh' => '岗位'
                ), '3' => array(
                    'en' => 'department',
                    'zh' => '部门'
                ),  '4' => array(
                    'en' => 'politics',
                    'zh' => '政治面貌'
                ), '5' => array(
                    'en' => 'phone',
                    'zh' => '手机号码'
                ), '6' => array(
                    'en' => 'nation',
                    'zh' => '民族'
                ), '7' => array(
                    'en' => 'birth',
                    'zh' => '出生日期'
                ), '8' => array(
                    'en' => 'idCardType',
                    'zh' => '身份证类型'
                ), '9' => array(
                    'en' => 'idCard',
                    'zh' => '身份证号'
                ), '10' => array(
                    'en' => 'homeAddress',
                    'zh' => '家庭地址'
                ), '11' => array(
                    'en' => 'origin',
                    'zh' => '籍贯'
                ), '12' => array(
                    'en' => 'registerAddress',
                    'zh' => '户口所在地'
                ), '13' => array(
                    'en' => 'nowAddress',
                    'zh' => '现居住地址'
                )
            );
        }
        if($table == 'js'){
            return $arrayTr = array(
                '0' => array(
                    'en' => 'name',
                    'zh' => '姓名'
                ),
                '1' => array(
                    'en' => 'sex',
                    'zh' => '性别'
                ),  '2' => array(
                    'en' => 'teachingSubjects',
                    'zh' => '任教科目'
                ), '3' => array(
                    'en' => 'politics',
                    'zh' => '政治面貌'
                ), '4' => array(
                    'en' => 'phone',
                    'zh' => '手机号码'
                )
            );
        }
    }
}