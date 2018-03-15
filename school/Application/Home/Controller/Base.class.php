<?php
namespace Home\Controller;
use Think\Controller;
use Think\Db;
class Base extends Controller
{
    protected static $secret_key = '0c39b20bc693807f7180aa05f2ea1bf2';
    protected static $public_key = '696916a74513dfdb4fdbad90c6f13768';
    protected static $password_key = '1f3463992c3f7389e658e7762fcde904';
    protected static $password_key1 = 'ffaju3af2lf0giw8qacl*faf32!fdasf8*ff+58-';
    protected static $adminRoleId = 22;/*设置管理员Id*/
    protected static $uploadUrl = '/Public/upload/';
    protected static $jZroleId = 13;
    protected  static $teacherRoleId = 14;
    protected  static $studentRoleId = 15;
    protected static $xzRoleId = 16;
    protected static $sgRoleId = 1; //宿管
    protected static $jwczrRoleId = 2; //教务处主任
    protected  static $pageSize = 10;
    protected static $downUrl = 'http://171.221.202.190:11111/api/school';
    //protected  static $jzRoleId = 33;
    /*登录后权限的验证*/
    function __construct(){
        parent::__construct();
        $token_check = 0;
        $controller = CONTROLLER_NAME;
        $function = ACTION_NAME;
        $type = $_GET['type'];
        if(strtolower($function) == 'dologin'){
            return true;
        }
        if($controller == 'Questionnaire' && $function == 'getTest'){
            return true;
        }
        if($getSession = $this->get_session('loginCheck',false)){
            $qxModelName = $getSession['scId'].'school'.$getSession['roleId'];
            $userModelList = json_decode($this->redis_operation($qxModelName,0,0,3),true);
            $chechFunction = $this->qxController();
            if($getSession['roleId'] == -1){
                return true;
            }
            if($controller == 'User' && $function == 'allUse'){
                return true;
            }
            if($controller == 'Curriculum' || $controller == 'Home'){
                if(isset($userModelList[$controller])){
                    return true;
                }
            }
            if(isset($chechFunction[$controller][$function])){
                if($userModelList[$controller][$function]){
                    return true;
                }
            }
            if($userModelList[$controller][$function][$type]){
                return true;
            }
            //return true;
            $this->returnJson(array('statu' => 8,'message' => 'No permissions'),true);
        }
        $this->returnJson(array('statu' => 9,'message' => 'need login'),true);
        //exit(0);
        //return false;
    }
    public function qxController(){
        return $array = array(
            'Notification' => array( //通知公告
                'group' => 1,
                'getUser'=>1
            ),
            'Process' => array( //流程中心
                'create' => 1,
                'manage'=>1,
                'userLists'=>1
            ),
            'TeacherLeave' => array( //教师请假
                'getName' => 1,
                'create'=>1,
                'lists'=>1,
                'approve'=>1,
                'statistics'=>1,
                'detail'=>1
            ),
            'WorkDemand' => array( //办公申请
                'getName' => 1,
                'userLists'=>1,
                'createDoc'=>1,
                'logDoc'=>1,
                'approveDoc'=>1,
                'createCar'=>1,
                'logCar'=>1,
                'approveCar'=>1,
                'placeOutfit'=>1,
                'placeManage'=>1,
                'createPlace'=>1,
                'logPlace'=>1,
                'approvePlace'=>1,
                'uploadCache'=>1
            ),
            'ClassManage' => array( // 班级/年级管理
                'common' => 1,
                'seatLayout'=>1,
                'stuAttend'=>1,
                'save'=>1,
                'classAttend'=>1,
                'adjustWish'=>1,
            ),
            'DivideBranch' => array( //分科分班
                'createPlan'=>1,
                'common' => 1,
                'planLog'=>1,
                'getAllProcess'=>1,
                'updatePlan'=>1,
                'studentManage'=>1,
                'scoreBasis'=>1,
                'scoreManage'=>1,
                'wishSetting'=>1,
                'scoreSetting'=>1,
               // 'scoreCombine'=>1,
                'scoreStatistics'=>1,
                'wishAdjust'=>1,
                'wishStatistics'=>1,
                'wishNot'=>1,
                'wishConfirm'=>1,
                'classSetting'=>1,
                'assignStu'=>1,
                'quickBranch'=>1,
                'manualAdjust'=>1,
                'assignStudent'=>1,
                'equalChange'=>1,
                'printReport'=>1,
                'publish'=>1,
                'sync'=>1,
                //学生
                'fillWish'=>1,
                'branchResult'=>1,
                'personScore'=>1,
                //教师
                'adjustWish'=>1
            ),
            'FileManage' => array( //档案管理
                'common' => 1,
                'tagSetting'=>1,
                'approveSetting'=>1,
                'getAccessory'=>1,
                'fileRecord'=>1,
                'fileApprove'=>1,
                'fileStatistics'=>1
            ),
            'StudentDorm' => array( //宿舍管理
                'common'=>1,
                'attendApprove'=>1,
                'dormManage'=>1,
                'setManager'=>1,
                'stuManage'=>1,
                'dormPlan'=>1,
                'onePlan' => 1,
                'assignList'=>1,
                'assignDorm'=>1,
                'assignStu'=>1,
                'quickAssign'=>1,
                'publish'=>1,
                'manualAdjust'=>1,
                'reportForm'=>1,
                'dormResult'=>1
            ),
            'StudentEvaluate' => array( //学生评教
                'common' => 1,
                'createEvaluate'=>1,
                'modeSetting'=>1,
                'recordEvaluate'=>1,
                'teachEvaluate'=>1,
                'statisticsEvaluate'=>1,
                'studentMark'=>1,
                'record'=>1,
            ),
            'StudentIni' => array( //新生分班
                'common' => 1,
                'newManage'=>1,
                'signManage'=>1,
                'allProcess'=>1,
                'stuLists'=>1,
                'createClass'=>1,
                'assignStu'=>1,
                'scoreSetting'=>1,
                'scoreManage'=>1,
                'scoreInfo'=>1,
                //'signDivide'=>1,
                'quickBranch'=>1,
                'manualAdjust'=>1,
                'assignStudent'=>1,
                'equalChange'=>1,
                'addStu'=>1,
                'printReport'=>1
            ),
            'TeacherEvaluate'=>array(
                'getBelong'=>1,
                'statisticsEva'=>1,
                'judgeMark'=>1
            ),
            'Examination' => array(
                'exmanagement' => 1,
                'previousexam' => 1,
                'excreat' => 1
            ),
            'Assets' => array(//资产管理
                'assetsType' => 1,
                'approverSet' => 1,
                'batchImport' => 1,
                'assetStoreroom' => 1,
                'assetBrrowAndRetrun' => 1,
                'assetOut' => 1,
                'assetsApprove' => 1,
                'assetsList' => 1,
            ),
            'Classreplacement' => array(//调课代课 课时统计
                'statistics' => 1,
                'tkRecord' => 1,
                'dkRecord' => 1,
                'dKapprover' => 1,
                'dKsP' => 1,
                'tkApprover' => 1,
                'approverSet' => 1,
                'teacherTk' => 1,
                'classTk' => 1,
            ),
            'Educational' => array(//教务管理 公用
                'getSubjectList' => 1,
                'teacherSubject' => 1,
                'classAndgradeGl' => 1,
                'classHeadAndGradeHead' => 1,
                'achievementPro' => 1,
                'zdPro' => 1,
                'printCenter' => 1,
            ),
            'Eqrepair' => array(//教务管理 公用
                'basicsSet' => 1,
                'myRepair' => 1,
                'repairList' => 1,
                'repairTask' => 1,
                'repairStatistics' => 1
            ),
            'Questionnaire' => array(//调查问卷
                'fillSpeed' => 1,
                'questionnaireRecord' => 1,
                'fillTask' => 1,
                'Statistics' => 1,
                'createQuestion' => 1,
            ),
            'Schedule' => array(//课表 学生课表
                'sudent' => 1,
                'teacher' => 1,
                'admin' => 1
            ),
            'Studentleave' => array(//学生请假 新建请假
                'createLeave' => 1,
                'getLeaveList' => 1,
                'replaceLe' => 1,
                'replaceRecord' => 1,
                'leaveApproval' => 1,
                'leaveSchool' => 1,
                'leaveSelect' => 1,
                'leaveStatistics' => 1,
            ),
            'Studentsgrowthrecord' => array(//学生成长记录 公用
                'getSubjectList' => 1,
                'basicsSet' => 1,
                'myRecord' => 1,
                'studentsRecord' => 1,
                'agumentLarblSet' => 1,
                'familyRecord' => 1
            ),
            'User' => array(//用户管理
                'exportStudent' => 1,
                'getvalue' => 1,
                'getStudentList' => 1,
                'uploadUserLogo' => 1,
                'exportExcel' => 1,
                'exportTeacherOrZg' => 1,
                'getOneNav' => 1,
                'getModelListGo' => 1,
                'createRole' => 1,
                'getRoleList' => 1,
                'getRoleListUser' => 1,
                'createModel' => 1,
                'getModelList' => 1,
                'getChildModel' => 1,
                'searchNav' => 1,
                'getRoleModelList' => 1,
                'createRoleModelGo' => 1,
                'createRoleModel' => 1,
                'userPassword' => 1,
                'getGrade' => 1,
                'teacherToZg' => 1
            ),
            'Systemup' => array(//用户管理
                'faseLane' => 1,
                'passWordUpdate' => 1
            ),
        );
    }
    /*初始密码*/
    public function InitialPassword(){
        $password = '';
        for($i = 0 ; $i<6 ; $i++){
            $password = $password.rand(0,9);
        }
        return $password;
    }
    /*登录验证*/
    public function create_token($account,$password){
        $check = self::check_password($account,$password,self::$password_key,self::$password_key1);
        if($check){
            $rel_token = self::get_token($account,$password,self::$secret_key,self::$public_key);
            self::redis_operation($rel_token,1,900,2);
            return $rel_token;
        }
        return false;
    }
    public function check_token($token){
        if(1){
            return true;        }
        return false;
    }
    /*操作redis*/
    public function redis_operation($name,$value,$time,$type){
        $redis = $this->get_redis();
        if($type == 1){
            $redis->set($name,$value);
            $redis->expire($name,$time);
            return true;
        }
        if($type == 2){
            $redis->set($name,$value);
            return true;
        }
        if($type == 3){
            if($data = $redis->get($name)){
                return $data;
            }
        }
        if($type == 4){
            $redis->del($name);
        }
        $redis->close();
    }
    /*连接redis*/
    private function get_redis(){
        $redis = new \Redis();
        $redis->connect('171.221.202.190', 6379);
        $redis->auth('mysql2012');
        return $redis;
    }
    /**/
    private function get_token($account,$password,$secret,$public){
        $token = md5($account.time());
        $token1 = md5($token.$password.$public);
        return md5($token1.$secret.uniqid());
    }
    /*验证登录*/
    private function check_password($account,$password){
        $mysql_password = M('user')->field('id,roleId,password,name,scId,birth,politics,post,teachingSubjects,hklx,hkAddress,logo,idCard,sex')->where(array('account' => $account,'state' => 1))->find();
        if( $mysql_password['scId']<0){
            $this->returnJson(array('statu' => 2,'message' => '该学生已毕业'),true);
        }
        $password = self::create_password($password,self::$password_key,self::$password_key1);
        if($password == $mysql_password['password']){
            $scId = $mysql_password['scId'];
            /*添加角色，权限模块*/
            $modelName = $scId.'school'.$mysql_password['roleId'];
            $model = json_decode($this->redis_operation($modelName,0,0,3),true);
            /*找到对应的学校*/
            $school = M('school')->where(array('scId' => $scId))->find();
            if(!$school['state']){
                $this->returnJson(array('statu' => 2,'message' => '该学校已停止使用'),true);
            }
            $school_rel = array(
                'scName' => $school['scName'],
                'scId' => $school['scId'],
                'mail' => $school['mail'],
                'telephone' => $school['telephone'],
                'province' => $school['province'],
                'city' => $school['city'],
                'ministries' => $school['ministries'],
                'type' => $school['type'],
                'logo' => $school['logo'],
                'lastRecordTime' => $school['lastRecordTime'],
                'createTime' => $school['createTime'],
                'prefix' => $school['prefix'],
            );
            $user = array(
                'userId' => $mysql_password['id'],
                'account' => $mysql_password['account'],
                'roleId' => $mysql_password['roleId'],
                'name' => $mysql_password['name'],
                'scId' => $mysql_password['scId'],
                'className' => $mysql_password['className'],
                'sex' => $mysql_password['sex'],
                'idCard' => $mysql_password['idCard'],
                'major' =>$mysql_password['major'],
                'logo' => $mysql_password['logo'],
                'teachingSubjects' => $mysql_password['teachingSubjects'],
                'post' => $mysql_password['post'],
                'politics' => $mysql_password['politics'],
                'hkAddress' => $mysql_password['hkAddress'],
                'hklx' => $mysql_password['hklx'],
                'grade' => $mysql_password['grade'],
                'birth' => $mysql_password['birth'],
               // 'department' => $mysql_password['department']
            );
            $mysql_password['name'] = explode('(',$mysql_password['name']);
            $mysql_password['name'] = $mysql_password['name'][0];
            self::set_session('loginCheck',array('roleId' => $mysql_password['roleId'],'name' =>$mysql_password['name'] ,'scId' => $scId,'userId' => $mysql_password['id']),900,false);
            return array('user' => $user,'url' => $mysql_password['logo'],'school' => $school_rel,'statu' => 1,'message' => 'login success','roleId' =>$mysql_password['roleId'],'scId' => $scId );
        }else{
            $this->returnJson(array('statu' => 0,'message' => '登录失败'),true);
        }
    }
    protected function checkLogin($account,$password){
        return self::check_password($account,$password);
    }
    /*加密密码*/
    protected function create_password($password,$password_key,$password_key1){
        $passwordone = md5($password);
        $passwordtow = md5($passwordone.$password_key);
        return md5($passwordtow.$password_key1);
    }
    /* 验证session 设置session*/
    public function set_session($name,$data,$expire = 5000,$type){
        $session_data = array();
        $session_data['data'] = $data;
        if($type){
            $session_data['expire'] = time()+$expire;
        }
        $_SESSION[$name] = $session_data;
    }
    /*得到session*/
    public function get_session($name,$type){
        if(isset($_SESSION[$name])){
            if($type){
                if($_SESSION[$name]['expire']>time()){
                    return $_SESSION[$name]['data'];
                }else{
                    self::clear_session($name);
                    return false;
                }
            }else{
                return $_SESSION[$name]['data'];
            }
        }
        return false;
    }
    public  function clear_session($name){
        unset($_SESSION[$name]);
        return true;
    }
    /*检测错误*/
    private function check_data($time,$sessionname,$count){
        /*if(self::get_session($sessionname)){
            if(self::get_session($sessionname)>=$count){
                return false;
            }
            $_SESSION[$sessionname]['data']++;
            return true;
        }
        else{
            self::set_session($sessionname,1,$time);
            return true;
        }*/
    }
    /*验证自身的session，验证来源*/
    private function check_from(){
        if($_SESSION['from_app']){
            return true;
        }
        else{
            return false;
        }
    }
    private function getValue(){

    }
    /* 返回值，安卓,web*/
    public function returnJson($data,$true){
        if($true){
            ob_end_clean();
            $this->ajaxReturn($data);
            exit(0);
        }else{

        }
    }
    /*protected function urlencode_base($data){
        $returnData = array();
        foreach($data as $key => $value){
            foreach($value as $key1 => $value1){
                $returnData[$key][$key1] = urldecode($value1);
            }
        }
        return $returnData;
    }*/
    //上传文件处理
    protected function upload($fileTarName,$fileTypes){
        header('content-type:text/html;charset=utf-8');
        //$verifyToken = md5('unique_salt' . $_POST['timestamp']);
        $targetPath = dirname(dirname(dirname(dirname(__FILE__))));
        $targetPath = $targetPath.'\Public\upload\\'.'image'.'\\';
        if (!empty($_FILES)/* && $_POST['token'] == $verifyToken*/){
            $return = $this->get_session('loginCheck',false);
            $userId = $return['userId'];
            $tempFile = $_FILES['userFile']['tmp_name'];
            $targetPath = dirname(dirname(dirname(dirname(__FILE__))));
            $targetPath = $targetPath.'\Public\upload\\'.$fileTarName.'\\';
            if(!file_exists($targetPath)){
                mkdir($targetPath);
            }
            $fileNameRetrun = md5(uniqid().'df'.$userId);
            //$targetFile = $targetPath.$_FILES['userFile']['name'];
            //Validate the file type
            //$fileTypes = array('xlsx','doc','xls'); // File extensions
            $fileParts = pathinfo($_FILES['userFile']['name']);
            $flieExtension = $fileParts['extension'];
            $targetFile = $targetPath.$fileNameRetrun.'.'.$flieExtension;
            if (in_array($flieExtension,$fileTypes)){
                //print_r($_FILES);
                if(move_uploaded_file($tempFile,$targetFile)){
                    //echo $targetFile;
                    return $this::$downUrl."/Public/upload/$fileTarName/$fileNameRetrun.$flieExtension";
                }
                return false;
            }
            return false;
        }
        return false;
    }
    protected function uploads(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'jpeg','png','doc','txt','docx','wps','xls','xlsx','xlsm','ppt');// 设置附件上传类型
        $upload->rootPath  =      './Public/img/'; // 设置附件上传根目录
        // 上传单个文件
        $info   =   $upload->uploadOne($_FILES['userFile']);
        if(!$info) {// 上传错误提示错误信息
            //$this->error($upload->getError());
            return false;
        }else{// 上传成功 获取上传文件信息
            $arrayimg = $this::$downUrl.'/Public/img/'.$info['savepath'].$info['savename'];
            return $arrayimg;
        }
    }
    protected function deleteImg($url){
        return unlink("./Public/img/$url");
    }
    protected function export($data,$tr){
        $array = array();
        $i = 1;
        foreach($tr as $key => $value){
            $array[0][$value['en']] = $value['zh'];
        }
        foreach($data as $key => $value){
            foreach($tr as $key1 => $value1){
                $array[$i][$value1['en']] = $value[$value1['en']];
            }
            $i++;
        }
        $data = $array;
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
        foreach($data[0] as $key =>$value){
            $getobj1->getColumnDimension(chr(64 + $i))->setWidth(12);//设置宽度
            $i++;
        }
        foreach($data as $key => $value){
            $i = 1;
            foreach($value as $key1 => $value1){
                $getobj1->setCellValue(chr(64 + $i).$j,$value1);
                $i++;
            }
            $j++;
        }
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
    protected function sortPageValue($data){
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
        $page = $_GET['page'];
        $pageSize = $_GET['pageSize'];
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
        return array('maxPage' => $maxPage,'page' => $page,'data' => $return);
    }
}



