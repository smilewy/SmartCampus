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
class QuestionnaireController extends Base
{
    public function fillSpeed(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type == 'getlist'){
            $questionId = $_GET['questionId'];
            $roleId = $_GET['roleId'];
            $re = M('question_list')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            $user = unserialize($re['user']);
            $return = array();
            M('user')->filed('post,department,teachingSubjects')->where(array('scId' => $scId))->select();
            foreach($user as $key => $value){
                if($value['roleId'] ==$roleId){
                    unset($value['anwser']);
                    unset($value['fillTimes']);
                    $return[] = $value;
                }
            }
            $this->returnJson(array('statu' => 1, 'message' => $return),true);
        }
    }
    public function getTest(){//公用的外面的人也可以来填
        $type = $_GET['type'];
        if($type == 'getfill'){
            $token = $_GET['token'];
            $questionId = $_GET['questionId'];
            $check = $this->redis_operation($token,0,0,3);
            if(!$check || $check!=$questionId){
                $this->returnJson(array('message' => 'fail','statu' => 0),true);
            }
            $content = M('question_list')->field('content,questionName,explain')->where(array('questionId' => $questionId))->find();
            M('question_list')->where(array('questionId' => $questionId))->setInc('browser',1);
            $paper = unserialize($content['content']);
            $an = array();
            $return = array(
                'content' => $paper,
                'explain' => $content['explain'],
                'questionName' =>  $content['questionName'],
                'token' => $token,
                'questionId' => $questionId
            );
            $this->assign('return',$return);
            $this->display();
        }
        if($type == 'create'){
            $content = I('post.QNArray');
            $answer = array();
            $kk = 0;
            foreach($content as $key => $value){
                if($value['type'] == 0){
                    $answer[$kk]['QNum'] = $value['QNum'];
                    $answer[$kk]['type'] = $value['type'];
                    foreach($value['QNSetting'] as $key1 => $value1){
                        if($value1['isChoose']){
                            $answer[$kk]['answer'] = $key1;
                            $answer[$kk]['index'] = $key1;
                        }
                    }
                }
                if($value['type'] == 1){
                    $answer[$kk]['QNum'] = $value['QNum'];
                    $answer[$kk]['type'] = $value['type'];
                    $str = null;
                    $i = 0;
                    foreach($value['QNSetting'] as $key1 => $value1){
                        if($value1['isChoose']){
                            if($i != 0){
                                $str = $str.','.$key1;
                            }else{
                                $str = $key1;
                            }
                            $i++;
                        }
                    }
                    $answer[$kk]['answer'] = $str;
                }
                if($value['type'] == 2){
                    $answer[$kk]['QNum'] = $value['QNum'];
                    $answer[$kk]['type'] = $value['type'];
                    $answer[$kk]['answer'] = $value['QNSetting']['value'];
                }
                if($value['type'] == 3){
                    $answer[$kk]['QNum'] = $value['QNum'];
                    $answer[$kk]['type'] = $value['type'];
                    $answer[$kk]['maxScore'] = $value['maxScore'];
                    $answer[$kk]['answer'] = $value['QNSetting']['value'];
                }
                $kk++;
            }
            //$answer = $_POST['anwser'];
            $questionId = I('post.questionId');
            $token = I('post.token');
            $check = $this->redis_operation($token,0,0,3);
            if(!$check || $check!=$questionId){
                $this->returnJson(array('message' => '没有验证','statu' => 2),true);
            }
            if(M('question_outschool')->add(array(
                'anwser' => serialize($answer),
                'createTime' => date('Y-m-d H:i:s'),
                'questionId' => $questionId,
                'sex' => I('post.sex'),
                'name' => I('post.name'),
                'year' => I('post.year'),
                'ip' => $_SERVER['REMOTE_ADDR']
            ))){
                M('question_list')->where(array('questionId' => $questionId))->setInc('userNum',1);
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }else{
                $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
            }
        }
    }
    protected function createToken($id){
        $token = md5('ddd'.uniqid().'ts');
        $this->redis_operation($token,$id,864000,1);
        return  $token;
    }
    public function questionnaireRecord(){//问卷记录
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type == 'getList'){
            $data = M('question_list')->field('questionId,questionName,createTime,state,userNum,browser,fillProportion,fillAvgTime')->where(array('scId' => $scId))->select();
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'delete'){
            $questionId = I('post.questionId');
            if(M('question_list')->where(array('questionId' => $questionId,'scId' => $scId))->delete()){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'copy'){
            $questionId = I('post.questionId');
            $question = M('question_list')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            unset($question['questionId']);
            $question['state'] = 0;
            $question['userNum'] = 0;
            $question['browser'] = 0;
            $question['questionName'] = $question['questionName'].'-副本';
            if(M('question_list')->add($question)){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'startOrEnd'){
            $questionId = I('post.questionId');
            $qu = M('question_list')->field('state')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            if($qu['state'] == 1){
                if(M('question_list')->where(array('questionId' => $questionId,'scId' => $scId))->setField(array('state' => 0))){
                    $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
                }
                $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
            }else{
                if(M('question_list')->where(array('questionId' => $questionId,'scId' => $scId))->setField(array('state' => 1,'releaseId' => $userId,'releaseName' => $userName,'releaseTime' => date('Y-m-d H:i:s')))){
                    $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
                }
                $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
            }
        }
        if($type == 'getUpdata'){
            $questionId = $_GET['questionId'];
            $content = M('question_list')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            $paper = unserialize($content['content']);
            //$userrel = unserialize($content['user']);
            $userrel = M('question_user_list')->field('user')->where(array('scId' => $scId,'questionId' => $questionId))->select();
            if(!$paper){
                $paper = array();
            }
            if(!$userrel){
                $userrel = array();
            }
            $userTo = array();
            $userReturn = array();
            foreach($userrel as $key => $value){
                $value = json_decode($value['user'],true);
                $userTo[$value['id']] = array(
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'roleId' => $value['roleId'],
                    'class' => $value['class'],
                    'grade' => $value['grade'],
                    'gradeId' => $value['gradeId'],
                    'className' => $value['className'],
                );
            }
            $role = M('role')->where(array('scId' => array(array('eq',0),array('eq',$scId),'or')))->select();
            $adminRole = $this::$adminRoleId;
            $user = M('')->query("select id,name,roleId,class,grade,gradeId,className,department,teachingSubjects,sex from mks_user where scId= $scId AND roleId!=$adminRole");
            $eee = array();
            $roleUser = array();
            $roleRe = array();
            foreach($role as $key => $value){
                $roleRe[$value['roleId']] = $value['roleName'];
            }
            foreach($user as $key => $value){
                if(isset($userTo[$value['id']])){
                    $value['check'] = 1;
                    $roleUser[$value['roleId']]['check'] = 1;
                }else{
                    $value['check'] = 0;
                }
                $roleUser[$value['roleId']]['name'] = $roleRe[$value['roleId']];
                if($value['roleId'] == $this::$studentRoleId || $value['roleId'] == $this::$jZroleId ){
                    if($value['class'] && $value['gradeId']  && $value['grade']  && $value['className']){
                        if(isset($userTo[$value['id']])){
                            $roleUser[$value['roleId']]['data'][$value['grade']]['check'] = 1;
                            $roleUser[$value['roleId']]['data'][$value['grade']]['data'][$value['className']]['check'] = 1;
                        }
                        $roleUser[$value['roleId']]['data'][$value['grade']]['name'] =    $this->gradeToZhong($value['grade']);
                        $roleUser[$value['roleId']]['data'][$value['grade']]['data'][$value['className']]['name'] = $value['className'];
                        $roleUser[$value['roleId']]['data'][$value['grade']]['data'][$value['className']]['data'][] = $value;
                    }
                }else{
                    $roleUser[$value['roleId']]['data'][] = $value;
                }
            }
            $return = array();
            $i = 0;
            $wyId = 1;
            $check = array();
            foreach($roleUser as $key => $value){
                if($key == $this::$studentRoleId || $key == $this::$jZroleId ) {
                    $wyId++;
                    $return[$i]['wyId'] = $wyId;
                    $return[$i]['name'] = $value['name'];
                    if(isset($value['check'])){
                        if($value['check'] ==1){
                            $return[$i]['check'] = 1;
                        }
                    }else{
                        $return[$i]['check'] = 0;
                    }
                    $j = 0;
                    foreach($value['data'] as $key1 => $value1){
                        $wyId++;
                        if(isset($value1['check'])){
                            if($value1['check'] == 1){
                                $return[$i]['data'][$j]['check'] = 1;
                            }
                        }else{
                            $return[$i]['data'][$j]['check'] = 0;
                        }
                        $return[$i]['data'][$j]['name'] = $value1['name'];
                        $return[$i]['data'][$j]['wyId'] = $wyId;
                        $z =0;
                        foreach($value1['data'] as $key2 => $value2){
                            $wyId++;
                            if(isset($value2['check'])){
                                if($value2['check'] == 1){
                                }
                            }else{
                                $value2['check'] = 0;
                            }
                            $return[$i]['data'][$j]['data'][$z]['wyId'] = $wyId;
                            $return[$i]['data'][$j]['data'][$z]['name'] = $value2['name'];
                            $return[$i]['data'][$j]['data'][$z]['check'] = $value2['check'];
                            $ttt = 0;
                            foreach($value2['data'] as $key3 => $value3){
                                $wyId++;
                                $value3['wyId'] = $wyId;
                                if(isset($value3['check'])){
                                    if($value3['check'] == 1){
                                        $userReturn[] = $value3;
                                        $check[] = $wyId;
                                    }
                                }else{
                                    $value3['check'] = 0;
                                }
                                $return[$i]['data'][$j]['data'][$z]['data'][$ttt] = $value3;
                                $ttt++;
                            }
                            $z++;
                        }
                        $j++;
                    }
                }else{
                    $wyId++;
                    if(isset($value['check'])){
                        if($value['check'] == 1){
                        }
                    }else{
                        $value['check'] = 0;
                    }
                    $return[$i]['name'] = $value['name'];
                    $return[$i]['wyId'] = $wyId;
                    $jj = 0;
                    foreach($value['data'] as $key1 => $value1){
                        $wyId++;
                        $value1['wyId'] = $wyId;
                        if($value1['check'] ==1){
                            $userReturn[] = $value1;
                            $check[] = $wyId;
                        }
                        $return[$i]['data'][$jj] = $value1;
                        $jj++;
                    }
                }
                $i++;
            }
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => array('content' => $paper,'check' => $check,'explain' => $content['explain'],'questionId' => $content['questionId'],'anonymous' => $content['anonymous'],'questionName' => $content['questionName'],'list' => $return,'userList' => $userReturn)),true);
        }
        if($type == 'preview'){
            $questionId = $_GET['questionId'];
            $content = M('question_list')->field('content,questionName,explain,anonymous')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            $paper = unserialize($content['content']);
            if(!$paper){
                $paper = array();
            }
            $return = array(
                'content' => $paper,
                'explain' => $content['explain'],
                'questionName' =>  $content['questionName'],
                'anonymous' =>$content['anonymous']
            );
            M('question_list')->where(array('scId' => $scId,'questionId' => $questionId))->setInc('browser',1);
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
        }
        if($type == 'createUpdataPreview'){
            $content = I('get.content');
            $title = I('get.questionName');
            $explain = I('get.explain');
            if(!$content){
                $content =array();
            }
            $data = array(
                'content' => $content,
                'questionName' => $title,
                'explain' => $explain
            );
            $token = 'preview'.uniqid();
            $this::redis_operation($token,serialize($data),600,1);
            $this->returnJson(array('statu' => 1, 'token' => $token),true);
        }
        if($type == 'updataPreview'){
            $token = $_GET['token'];
            $content = $this::redis_operation($token,0,0,3);
            $this->returnJson(array('statu' => 1,'data' => unserialize($content)),true);
        }
        if($type == 'share'){
            $questionId = $_GET['questionId'];
            $token = $this->createToken($questionId);
            $url = $this::$downUrl."/Questionnaire/getTest?type=getfill&questionId=$questionId&token=$token";
            $this->returnJson(array('statu' => 1,'message' => 'success','url' => $url),true);
        }
        if($type == 'export') {
            $questionId = $_GET['questionId'];
            $content = M('question_list')->field('content,questionName,explain,user')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            $title = $content['questionName'];
            $paper = unserialize($content['content']);
            $fileName = 'question' . $questionId;
            $myFile = fopen("./Public/upload/questionnaire/$fileName.doc", "w") or die("Unable to open file!");
            fwrite($myFile, $title . "\n");
            fwrite($myFile, "\n");
            fwrite($myFile, "\n");
            foreach ($paper as $key => $value) {
                if ($value['type'] == 0) {
                    fwrite($myFile, $value['QNum'] . ':' . $value['title'] . '[单选题]' . "\n");
                    foreach ($value['QNSetting'] as $key1 => $value1) {
                        fwrite($myFile,'  '.$value1['value'] . "\n");
                    }
                    fwrite($myFile, "\n");
                    fwrite($myFile, "\n");
                }
                if ($value['type'] == 1) {
                    fwrite($myFile, $value['QNum'] . ':' . $value['title'] . '[多选题]' . "\n");
                    foreach ($value['QNSetting'] as $key1 => $value1) {
                        fwrite($myFile,'  '.$value1['value'] . "\n");
                    }
                    fwrite($myFile, "\n");
                    fwrite($myFile, "\n");
                }
                if ($value['type'] == 2) {
                    fwrite($myFile, $value['QNum'] . ':' . $value['title'] . '[填空题]' . "\n");
                    fwrite($myFile, "\n");
                    fwrite($myFile, "\n");
                }
                if ($value['type'] == 3) {
                    fwrite($myFile, $value['QNum'] . ':' . $value['title'] . '[分数题]' . "\n");
                    fwrite($myFile, "\n");
                    fwrite($myFile, "\n");
                }
            }
            fclose($myFile);
            $downType = $_GET['downType'];
            $url = $this::$downUrl;
            header("Location: $url/Public/upload/questionnaire/$fileName.doc");
        }
        if($type == 'updata'){
            $questionId = I('post.questionId');
            $questionName = I('post.questionName');
            $content = I('post.content');
            $user = I('post.user');
            foreach($user as $key => $value){
                $user[$key]['ifFill'] = 0;
            }
            $createTime = date('Y-m-d H:i:s');
            M('question_user_list')->where(array('scId' => $scId,'questionId' => $questionId))->delete();
            $i = 0;
            foreach($user as $key => $value){
                $value['ifFill'] = 0;
                $createData[$i]['user'] = json_encode($value);
                $createData[$i]['questionId'] = $questionId;
                $createData[$i]['createTime'] = $createTime;
                $createData[$i]['userId'] = $value['id'];
                $createData[$i]['scId'] = $scId;
                $i++;
                if($i==1000){
                    $ddd = M('question_user_list')->addAll($createData);
                    $createData = array();
                    $i = 0;
                }
            }
            M('question_user_list')->addAll($createData) ;
            $explain = I('post.explain');
            $data = array(
                'questionName' => $questionName,
                'userNum' =>0,
                'content' => serialize($content),
                'explain' => $explain,
                //'user' => serialize($user)
            );
            M('question_list')->where(array('scId' => $scId,'questionId' => $questionId))->setField($data);
            $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
        }
        if($type == 'getCheack'){

        }
        if($type == 'fillSpeed'){
            $questionId = $_GET['questionId'];
            $roleNameId = $_GET['roleNameId'];
            $question = M('question_list')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            $user = M('question_user_list')->field('user')->where(array('scId' => $scId,'questionId' => $questionId))->select();
            //print_r($user);
            $return = array();
            if($roleNameId == 1){
                foreach($user as $key => $value){
                    $value = json_decode($value['user'],true);
                    if($value['roleId'] == $this::$teacherRoleId){
                        unset($value['anwser']);
                        $return[] = $value;
                    }
                }
            }
            if($roleNameId == 2){
                foreach($user as $key => $value){
                    $value = json_decode($value['user'],true);
                    if($value['roleId'] == $this::$studentRoleId){
                        unset($value['anwser']);
                        $return[] = $value;
                    }
                }
            }
            if($roleNameId == 3){
                foreach($user as $key => $value){
                    $value = json_decode($value['user'],true);
                    if($value['roleId'] != $this::$studentRoleId && $value['roleId'] != $this::$adminRoleId && $value['roleId'] != $this::$jZroleId && $value['roleId'] != $this::$teacherRoleId){
                        unset($value['anwser']);
                        $return[] = $value;
                    }
                }
            }
            if($roleNameId == 4){
                foreach($user as $key => $value){
                    $value = json_decode($value['user'],true);
                    if($value['roleId'] == $this::$jZroleId){
                        unset($value['anwser']);
                        $return[] = $value;
                    }
                }
            }
            foreach($return as $key => $value){
                if($value['grade']){
                    $return[$key]['gradeAndClass'] = $this->gradeToZhong($value['grade']).$value['className'].'班';
                }
            }
            $data = $this::sortPageValue($return);
            $this->returnJson(array('statu' => 1,'message' => 'success' ,'data' => $data['data'],'maxPage' => $data['maxPage'],'page' => $data['page']),true);
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
    public function fillTask(){ //填写记录
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getFillTaskList'){
            $list = M('question_list')->field('questionId,questionName,explain,releaseId,releaseName,releaseTime,anonymous')->where(array('scId' => $scId,'state' =>1))->select();
            $listRel = array();
            foreach($list as $key => $value){
                $listRel[$value['questionId']] = $value;
            }
            $user = M('question_user_list')->field('questionId,user')->where(array('scId' => $scId,'userId' => $userId))->select();
            $userList = array();
            foreach($user as $key => $value){
                if(isset($listRel[$value['questionId']])){
                    $user = json_decode($value['user'],true);
                    if($user['ifFill'] == 0){
                        $listRel[$value['questionId']]['ifFill'] = '未提交';
                    }else{
                        $listRel[$value['questionId']]['ifFill'] = '已提交';
                    }
                    $userList[] = $listRel[$value['questionId']];
                }
            }
            $this->returnJson(array('statu' => 1,'data' => $userList),true);
        }
        if($type == 'fillTaskCreateOrUpdata'){
            $content =  I('post.QNArray');
            $answer = array();
            $kk = 0;
            foreach($content as $key => $value){
                if($value['type'] == 0){
                    $answer[$kk]['QNum'] = $value['QNum'];
                    $answer[$kk]['type'] = $value['type'];
                    $answer[$kk]['answer'] = $value['index'];
                    //$answer[$kk]['QNSetting'][$value['index']] = 1;
                    /*foreach($value['QNSetting'] as $key1 => $value1){
                        if($value1['isChoose']){
                            $answer[$kk]['answer'] = $key1;
                        }
                    }*/
                }
                if($value['type'] == 1){
                    $answer[$kk]['QNum'] = $value['QNum'];
                    $answer[$kk]['type'] = $value['type'];
                    $str = null;
                    $i = 0;
                    foreach($value['QNSetting'] as $key1 => $value1){
                        if($value1['isChoose']){
                            if($i != 0){
                                $str = $str.','.$key1;
                            }else{
                                $str = $key1;
                            }
                            $i++;
                        }
                    }
                    $answer[$kk]['answer'] = $str;
                }
                if($value['type'] == 2){
                    $answer[$kk]['QNum'] = $value['QNum'];
                    $answer[$kk]['type'] = $value['type'];
                    $answer[$kk]['answer'] = $value['QNSetting']['value'];
                }
                if($value['type'] == 3){
                    $answer[$kk]['QNum'] = $value['QNum'];
                    $answer[$kk]['type'] = $value['type'];
                    $answer[$kk]['maxScore'] = $value['maxScore'];
                    $answer[$kk]['answer'] = $value['QNSetting']['value'];
                }
                $kk++;
            }
            sort($answer);
            //$answer = $_POST['anwser'];
            $questionId = I('post.questionId');
            $fillTimes = I('post.fillTimes');
            $list = M('question_list')->field('content,questionName,explain,user')->where(array('scId' => $scId,'questionId' => $questionId))->find();
           // print_r($list);
            $user = M('question_user_list')->field('user')->where(array('scId' => $scId,'questionId' => $questionId,'userId' => $userId))->find();
            $user = json_decode($user['user'],true);
            $adddd = 0;
            if($user['ifFill']){
                $user['fillTime'] = date('Y-m-d H:i;s');
                $user['anwser'] = $answer;
            }else{
                $adddd = 1;
                $user['ifFill'] = 1;
                $user['fillTime'] = date('Y-m-d H:i;s');
                $user['fillTimes'] = $fillTimes;
                $user['anwser'] = $answer;
            }
            $user = json_encode($user);
            if(M('question_user_list')->where(array('scId' => $scId,'questionId' => $questionId,'userId' => $userId))->setField(array('user' => $user))){
                if($adddd){
                    M('question_list')->where(array('scId' => $scId,'questionId' => $questionId))->setInc('userNum',1);
                }
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }else{
                $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
            }
        }
        if($type == 'fillTask'){
            $questionId = $_GET['questionId'];
            $content = M('question_list')->field('content,questionName,explain')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            $user = M('question_user_list')->field('user')->where(array('questionId' => $questionId,'userId' => $userId,'scId' => $scId))->find();
            $paper = unserialize($content['content']);
            $user = json_decode($content['user'],true);
            $ifFill = 1;
            $an = $user;
            foreach($paper as $key => $value){
                if($value['type'] == 0){
                    $paper[$key]['index'] = '';
                }
                foreach($an['anwser'] as $key1 => $value1){
                    if($value['QNum'] == $value1['QNum']){
                        if($value['type'] == 0){
                            $annn = $value1['answer'];
                            $paper[$key]['QNSetting'][$annn]['isChoose'] = 1;
                            $paper[$key]['index'] = $annn;
                        }
                        if($value['type'] == 1){
                            $annn = explode(',',$value1['answer']);
                            foreach($annn as $key2 => $value2){
                                $paper[$key]['QNSetting'][$value2]['isChoose'] = 1;
                            }
                        }
                        if($value['type'] == 2){
                            $annn = $value1['answer'];
                            $paper[$key]['QNSetting']['value'] = $annn;
                        }
                        if($value['type'] == 3){
                            $annn = $value1['answer'];
                            $paper[$key]['QNSetting']['value'] = $annn;
                        }
                    }
                }
            }
            $return = array(
                'content' => $paper,
                'explain' => $content['explain'],
                'ifFill' => $ifFill,
                'questionName' =>  $content['questionName'],
            );
            $this->returnJson(array('statu' => 1,'data' => $return),true);
        }
        if($type == 'fillTaskLook'){
            $questionId = $_GET['questionId'];
            $content = M('question_list')->field('content,questionName,explain')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            $user = M('question_user_list')->field('user')->where(array('questionId' => $questionId,'userId' => $userId,'scId' => $scId))->find();
            $paper = unserialize($content['content']);
            $user = json_decode($user['user'],true);
            $ifFill = 1;
            $an = array();
            if($user['ifFill'] == 0){
                $ifFill = 0;
                $an = array();
            }else{
                $an = $user;
            }
            foreach($paper as $key => $value){
                if($value['type'] == 0){
                    $paper[$key]['index'] = '';
                    foreach($value['QNSetting'] as $key1 => $value1){
                        if($value1['isChoose'] == 0){
                            $paper[$key]['QNSetting'][$key1]['isChoose'] = false;
                        }else{
                            $paper[$key]['QNSetting'][$key1]['isChoose'] = true;
                        }
                    }
                }
                if($value['type'] == 1){
                    $paper[$key]['index'] = '';
                }
                foreach($an['anwser'] as $key1 => $value1){
                    if($value['QNum'] == $value1['QNum']){
                        if($value['type'] == 0){
                            $annn = $value1['answer'];
                            if( is_numeric($annn)){
                                $paper[$key]['index'] = (int)$annn;
                            }
                        }
                        if($value['type'] == 1){
                            $annn = explode(',',$value1['answer']);
                            foreach($annn as $key2 => $value2){
                                if(is_numeric($value2)){
                                    $paper[$key]['QNSetting'][$value2]['isChoose'] = true;
                                }
                            }
                        }
                        if($value['type'] == 2){
                            $annn = $value1['answer'];
                            $paper[$key]['QNSetting']['value'] = $annn;
                        }
                        if($value['type'] == 3){
                            $annn = $value1['answer'];
                            $paper[$key]['QNSetting']['value'] = $annn;
                        }
                    }
                }
            }
            $return = array(
                'content' => $paper,
                'explain' => $content['explain'],
                'ifFill' => $ifFill,
                'questionName' =>  $content['questionName'],
            );
            M('question_list')->where(array('scId' => $scId,'questionId' => $questionId))->setInc('browser',1);
            $this->returnJson(array('statu' => 1, 'message' => 'fail','data' => $return),true);
        }
    }
    public function Statistics(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $userName = $jbXn['name'];
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getQuList'){
            $list = M('question_list')->field('questionId,questionName')->where(array('scId' => $scId,'state' =>1))->select();
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $list),true);
        }
        if($type == 'getXq'){
            $ifIp = $_GET['ifIp'];
            $questionId = $_GET['questionId'];
            $list = M('question_list')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            $user = array();
            $contentUser = M('question_user_list')->field('user')->where(array('scId' => $scId,'questionId' => $questionId))->select();
            foreach($contentUser as $key => $value){
                $user[]  = json_decode($value['user'],true);
            }
            //
            $content = unserialize($list['content']);
            $tititit = $list['questionName'];
            $roleId = $_GET['roleId'];
            $userZj = array();
            if($roleId == 1){
                $gradeId = $_GET['gradeId'];
                if($gradeId == 1){
                    foreach($user as $key => $value){
                        if($value['sex'] == '男'){
                            $userZj[] = $value;
                        }
                    }
                }else if($gradeId == 2){
                    foreach($user as $key => $value){
                        if($value['sex'] == '女'){
                            $userZj[] = $value;
                        }
                    }
                }else if($gradeId == 3){
                    foreach($user as $key => $value){
                        $userZj[] = $value;
                    }
                }
            }
            if($roleId == 4){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                $allTeacher  = M('jw_schedule')->where(array('scId' => $scId,'gradeId' => $gradeId))->select();
                $allTeacherRel = array();
                foreach($allTeacher as $key => $value){
                    $allTeacherRel[$value['techerId']] = 1;
                }
                foreach($user as $key => $value){
                    if($value['roleId'] == $this::$teacherRoleId){
                        if(isset($allTeacherRel[$value['id']])){
                            $userZj[] = $value;
                        }
                    }
                }
            }
            if($roleId == 5){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                $classId = $_GET['classId'];
                $allTeacher  = M('jw_schedule')->where(array('scId' => $scId,'gradeId' => $gradeId))->select();
                $allTeacherRel = array();
                foreach($allTeacher as $key => $value){
                    foreach($classId as $key2 => $value2){
                        if($value['classId'] == $value2){
                            $allTeacherRel[$value['techerId']] = 1;
                        }
                    }
                }
                foreach($user as $key => $value){
                    if($value['roleId'] == $this::$teacherRoleId){
                        if(isset($allTeacherRel[$value['id']])){
                            $userZj[] = $value;
                        }
                    }
                }
            }
            if($roleId == 6){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                foreach($user as $key => $value){
                    if($value['roleId'] == $this::$studentRoleId){
                        if($value['gradeId'] == $gradeId){
                            $userZj[] = $value;
                        }
                    }
                }
            }
            if($roleId == 7){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                $classId = $_GET['classId'];
                $classRel = array();
                foreach($classId as $key => $value){
                    $classRel[$value] = 1;
                }
                foreach($user as $key => $value){
                    if($value['roleId'] == $this::$studentRoleId){
                        if($value['gradeId'] == $gradeId){
                            if(isset($classRel[$value['class']])){
                                $userZj[] = $value;
                            }
                        }
                    }
                }
            }
            if($roleId == 8){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                foreach($user as $key => $value){
                    if($value['roleId'] == $this::$jZroleId){
                        if($value['gradeId'] == $gradeId){
                            $userZj[] = $value;
                        }
                    }
                }
            }
            if($roleId == 9){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                $classId = $_GET['classId'];
                $classRel = array();
                foreach($classId as $key => $value){
                    $classRel[$value] = 1;
                }
                foreach($user as $key => $value){
                    if($value['roleId'] ==  $this::$jZroleId){
                        if($value['gradeId'] == $gradeId){
                            if(isset($classRel[$value['class']])){
                                $userZj[] = $value;
                            }
                        }
                    }
                }
            }
            if($roleId == 10){
                $gradeId = $_GET['gradeId'];
                if(!$gradeId){
                    foreach($user as $key => $value){
                        if($value['roleId'] == $this::$studentRoleId || $value['roleId'] == $this::$jZroleId || $value['roleId'] == $value::$teacherRoleId){
                        }else{
                            $userZj[] = $value;
                        }
                    }
                }else{
                    foreach($user as $key => $value){
                        if($value['roleId'] ==  $gradeId){
                            $userZj[] = $value;
                        }
                    }
                }
            }
            if($roleId == 11){
                $gradeId = $_GET['gradeId'];
                if($gradeId == 1){
                    foreach($user as $key => $value){
                        $userZj[] = $value;
                    }
                }
                if($gradeId == 2){
                    if($ifIp){
                        $outUser = M('question_outschool')->where(array('questionId' => $questionId))->group('ip')->select();
                    }else{
                        $outUser = M('question_outschool')->where(array('questionId' => $questionId))->select();
                    }
                    foreach($outUser as $key => $value){
                        $value['anwser'] = unserialize($value['anwser']);
                        $userZj[] = $value;
                    }
                }
                if($gradeId == 3){
                    $ddd= 0;
                    foreach($user as $key => $value){
                        $userZj[$ddd] = $value;
                        $ddd++;
                    }
                    if($ifIp){
                        $outUser = M('question_outschool')->where(array('questionId' => $questionId))->group('ip')->select();
                    }else{
                        $outUser = M('question_outschool')->where(array('questionId' => $questionId))->select();
                    }
                    foreach($outUser as $key => $value){
                        $value['anwser'] = unserialize($value['anwser']);
                        $userZj[$ddd] = $value;
                        $ddd++;
                    }
                }
            }
            if(!$roleId){
                $ddd= 0;
                foreach($user as $key => $value){
                    $userZj[$ddd] = $value;
                    $ddd++;
                }
                if($ifIp){
                    $outUser = M('question_outschool')->field('id,createTime,questionId,sex,name,year,ip')->where(array('questionId' => $questionId))->group('ip')->select();
                }else{
                    $outUser = M('question_outschool')->field('id,createTime,questionId,sex,name,year,ip')->where(array('questionId' => $questionId))->select();
                }
                foreach($outUser as $key => $value){
                    $value['anwser'] = unserialize($value['anwser']);
                    $userZj[$ddd] = $value;
                    $ddd++;
                }
            }
            $user = $userZj;
            foreach($user as $key => $value){
                if(!isset($value['fillTime']) && isset($value['createTime'])){
                    $user[$key]['fillTime'] = $value['createTime'];
                    $user[$key]['department'] = '';
                    $user[$key]['gradeClass'] = '';
                    $user[$key]['ifOut'] = 1;
                }else{
                    if($value['ifFill']){
                        $user[$key]['ifOut'] = 0;
                        unset($user[$key]['anwser']);
                        if($value['grade'] && $value['className']){
                            $user[$key]['gradeClass'] = $this->gradeToZhong($value['grade']).$value['className'].'班';
                        }else{
                            $user[$key]['gradeClass'] = '';
                        }
                    }else{
                        unset($user[$key]);
                    }
                }
            }
            $ii = 0;
            $returndd = array();
            if($list['anonymous']){
                foreach($user as $key => $vlaue){
                    $value['name'] = 'xxxxx ';
                    $returndd[] = $value;
                }
            }else{
                foreach($user as $key => $vlaue){
                    $returndd[] = $vlaue;
                }
            }
            $return = $this::sortPageValue($returndd);
            $this->returnJson(array('statu' => 1,'data' => $return),true);
        }
        if($type == 'fillTaskLook'){
            $questionId = $_GET['questionId'];
            $id = $_GET['id'];
            $ifOut = $_GET['ifOut'];
            $content = M('question_list')->field('content,questionName,explain,user')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            $paper = unserialize($content['content']);
            $an = array();
            $ifFill = 1;
            if($ifOut){
                $an = M('question_outschool')->where(array('id' => $id))->find();
                $an['anwser'] = unserialize($an['anwser']);
            }else{
                $user = unserialize($content['user']);
                foreach($user as $key => $value){
                    if($value['id'] == $id){
                        $an = $value;
                    }
                }
            }
            foreach($paper as $key => $value){
                if($value['type'] == 0){
                    $paper[$key]['index'] = '';
                    foreach($value['QNSetting'] as $key1 => $value1){
                        if($value1['isChoose'] == 0){
                            $paper[$key]['QNSetting'][$key1]['isChoose'] = false;
                        }else{
                            $paper[$key]['QNSetting'][$key1]['isChoose'] = true;
                        }
                    }
                }
                if($value['type'] == 1){
                    $paper[$key]['index'] = '';
                }
                foreach($an['anwser'] as $key1 => $value1){
                    if($value['QNum'] == $value1['QNum']){
                        if($value['type'] == 0){
                            $annn = $value1['answer'];
                            if(is_numeric($value1['answer'])){
                                $paper[$key]['QNSetting'][$annn]['isChoose'] = true;
                                $paper[$key]['index'] = (int)$annn;
                            }
                        }
                        if($value['type'] == 1){
                            $annn = explode(',',$value1['answer']);
                            foreach($annn as $key2 => $value2){
                                if(is_numeric($value2)){
                                    $paper[$key]['QNSetting'][$value2]['isChoose'] = true;
                                }
                            }
                        }
                        if($value['type'] == 2){
                            $annn = $value1['answer'];
                            $paper[$key]['QNSetting']['value'] = $annn;
                        }
                        if($value['type'] == 3){
                            $annn = $value1['answer'];
                            $paper[$key]['QNSetting']['value'] = $annn;
                        }
                    }
                }
            }
            $return = array(
                'content' => $paper,
                'explain' => $content['explain'],
                'ifFill' => $ifFill,
                'questionName' =>  $content['questionName'],
            );
            M('question_list')->where(array('scId' => $scId,'questionId' => $questionId))->setInc('browser',1);
            $this->returnJson(array('statu' => 1, 'message' => 'fail','data' => $return),true);
        }
        if($type == 'getWd'){
            $questionId = $_GET['questionId'];
            $role = M('role')->where(array('scId' => array(array('eq',0),array('eq',$scId),'or')))->select();
            $content = M('question_user_list')->field('user')->where(array('scId' => $scId,'questionId' => $questionId))->select();
            $outUser = M('question_outschool')->where(array('questionId' => $questionId))->find();
            foreach($content as $key => $value){
                $user[]  = json_decode($value['user'],true);
            }
            $roleUser = array();
            $roleRe = array();
            foreach($role as $key => $value){
                $roleRe[$value['roleId']] = $value['roleName'];
            }
            foreach($user as $key => $value){
                $roleUser[$value['roleId']]['grade0rclass'] = $roleRe[$value['roleId']];
                $roleUser[$value['roleId']]['roleId'] = $value['roleId'];
                unset($value['anwser']);
                unset($value['fillTimes']);
            }
            $count = count($roleUser);
            //print_r($roleUser);
            $returnArray = array();
            if($count>0){
                $returnArray[0]['grade0rclass'] = '性别统计';
                $returnArray[0]['roleId'] = 1;
                $returnArray[0]['data'][0]['grade0rclass'] = '男';
                $returnArray[0]['data'][0]['roleId'] = 1;
                $returnArray[0]['data'][1]['grade0rclass'] = '女';
                $returnArray[0]['data'][1]['roleId'] = 2;
                $returnArray[0]['data'][2]['grade0rclass'] = '所有';
                $returnArray[0]['data'][2]['roleId'] = 3;
                $ii = 1;
                $grade = M('grade')->field('gradeid,znName')->where(array('scId' => $scId))->order('name')->select();
                $class = M('class')->field('classid,classname,grade')->where(array('scId' => $scId))->order('classname')->select();
                $gradeAndClass = array();
                $gradeRe = array();
                $iiii = 0;
                foreach($grade as $key1 => $value1){
                    $gradeRe[$iiii]['grade0rclass'] = $value1['znName'];
                    $gradeRe[$iiii]['roleId'] = $value1['gradeid'];
                    $gradeAndClass[$iiii]['grade0rclass'] = $value1['znName'];
                    $gradeAndClass[$iiii]['roleId'] = $value1['gradeid'];
                    foreach($class as $key2 => $value2){
                        if($value1['gradeid'] == $value2['grade']){
                            $gradeAndClass[$iiii]['data'][] = array(
                                'grade0rclass' => $value2['classname'],
                                'roleId' => $value2['classid']
                            );
                        }
                    }
                    $iiii++;
                }
                foreach($roleUser as $key => $value){
                    if($this::$teacherRoleId == $key){
                        $returnArray[$ii]['grade0rclass'] = '各年级教师统计';
                        $returnArray[$ii]['roleId'] = 4;
                        $returnArray[$ii]['data'] = $gradeRe;
                        $ii++;
                        $returnArray[$ii]['grade0rclass'] = '各班级教师统计';
                        $returnArray[$ii]['roleId'] = 5;
                        $returnArray[$ii]['data'] = $gradeAndClass;
                        $ii++;
                    }
                    else if($this::$studentRoleId == $key){
                        $returnArray[$ii]['grade0rclass'] = '各年级学生统计';
                        $returnArray[$ii]['roleId'] = 6;
                        $returnArray[$ii]['data'] = $gradeRe;
                        $ii++;
                        $returnArray[$ii]['grade0rclass'] = '各班级学生统计';
                        $returnArray[$ii]['roleId'] = 7;
                        $returnArray[$ii]['data'] = $gradeAndClass;
                        $ii++;
                    }
                    else if($this::$jZroleId == $key){
                        $returnArray[$ii]['grade0rclass'] = '各年级家长统计';
                        $returnArray[$ii]['roleId'] = 8;
                        $returnArray[$ii]['data'] = $gradeRe;
                        $ii++;
                        $returnArray[$ii]['grade0rclass'] = '各班级家长统计';
                        $returnArray[$ii]['roleId'] = 9;
                        $returnArray[$ii]['data'] = $gradeAndClass;
                        $ii++;
                    }
                    else{
                        $returnArray[$ii]['grade0rclass'] = '各用户类型统计';
                        $returnArray[$ii]['roleId'] = 10;
                        $data = M('role')->where(array('scId' => array(array('eq',$scId),array('eq',0),'or')))->select();
                        $dataRe = array();
                        $zz = 0;
                        foreach($data as $key1 => $value1){
                            $dataRe[$zz] = array(
                                'roleId' => $value1['roleId'],
                                'grade0rclass' => $value1['roleName']
                            );
                            $zz++;
                        }
                        $dataRe[$zz]['roleId'] = 0;
                        $dataRe[$zz]['grade0rclass'] = '所有';
                        $returnArray[$ii]['data'] = $dataRe;
                        $ii++;
                    }
                }
                if(count($outUser)>0){
                    $returnArray[$ii]['grade0rclass'] = '校内外统计';
                    $returnArray[$ii]['roleId'] = 11;
                    $returnArray[$ii]['data'] = array(
                        0 => array(
                            'roleId' => 1,
                            'grade0rclass' => '校内'
                        ),
                        1 => array(
                            'roleId' => 2,
                            'grade0rclass' => '校外'
                        ),
                        2 => array(
                            'roleId' => 3,
                            'grade0rclass' => '所有'
                        )
                    );
                    $ii++;
                }
            }
            $arrayOne = array();
            $arrayTow = array();
            $arrayThree = array();
            $wynum = 0;
            foreach($returnArray as $key => $value){
                $wynum++;
                $arrayOne[] = array(
                    'grade0rclass' => $value['grade0rclass'],
                    'roleId' => $value['roleId'],
                    'wynum' => $wynum,
                );
                $one = $value['roleId'];
                foreach($value['data'] as $key1 => $value1){
                    $chech = false;
                    if(isset($value1['data'])){
                        $chech = true;
                    }
                    $wynum++;
                    $arrayTow[$one]['value'][] = array(
                        'grade0rclass' => $value1['grade0rclass'],
                        'gradeId' => $value1['roleId'],
                        'wynum' => $wynum,
                    );
                    $arrayTow[$one]['check'] = $chech;
                    $arrayTow[$one]['title'] = $value['grade0rclass'];
                    $tow = $value1['roleId'];
                    foreach($value1['data'] as $key2 => $value2){
                        $wynum++;
                        $arrayThree[$tow]['title'] = $value1['grade0rclass'];
                        $arrayThree[$tow]['value'][$value2['roleId']] = array(
                            'title' => $value1['grade0rclass'],
                            'grade0rclass' => $value2['grade0rclass'],
                            'classId' => $value2['roleId'],
                            'wynum' => $wynum
                        );
                    }
                }
            }
            foreach($arrayThree as $key => $value){
                $arra = array();
                foreach($value['value'] as $key1 => $value1){
                    $arra[] = $value1;
                }
                $arrayThree[$key]['value'] = $arra;
            }
            $this->returnJson(array('statu' =>1,'message' => 'success','data' => array('one' => $arrayOne,'tow' => $arrayTow,'three' => $arrayThree)),true);
        }
        if($type == 'select'){
            $questionId = $_GET['questionId'];
            $serialNumData = $_GET['serialNumData'];
            $answer = $_GET['answer'];
            $list = M('question_list')->field('content,questionName')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            $userContent = M('question_user_list')->field('user')->where(array('scId' => $scId,'questionId' => $questionId))->select();
            $content = unserialize($list['content']);
            $user = array();
            foreach($userContent as $key => $value){
                $user[]  = json_decode($value['user'],true);
            }
            $userList = array();
            foreach($user as $key => $value){
                if($value['ifFill']){
                    $userList[] = $value;
                }
            }
            $user = $userList;
            $tititit = $list['questionName'];
            $roleId = $_GET['roleId'];
            $userZj = array();
            if($roleId == 1){
                $gradeId = $_GET['gradeId'];
                if($gradeId == 1){
                    foreach($user as $key => $value){
                        if($value['sex'] == '男'){
                            $userZj[] = $value;
                        }
                    }
                }else if($gradeId == 2){
                    foreach($user as $key => $value){
                        if($value['sex'] == '女'){
                            $userZj[] = $value;
                        }
                    }
                }else if($gradeId == 3){
                    foreach($user as $key => $value){
                        $userZj[] = $value;
                    }
                }
            }
            if($roleId == 4){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                $allTeacher  = M('jw_schedule')->where(array('scId' => $scId,'gradeId' => $gradeId))->select();
                $allTeacherRel = array();
                foreach($allTeacher as $key => $value){
                    $allTeacherRel[$value['techerId']] = 1;
                }
                foreach($user as $key => $value){
                    if($value['roleId'] == $this::$teacherRoleId){
                        if(isset($allTeacherRel[$value['id']])){
                            $userZj[] = $value;
                        }
                    }
                }
            }
            if($roleId == 5){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                $classId = $_GET['classId'];
                $allTeacher  = M('jw_schedule')->where(array('scId' => $scId,'gradeId' => $gradeId))->select();
                $allTeacherRel = array();
                foreach($allTeacher as $key => $value){
                    foreach($classId as $key2 => $value2){
                        if($value['classId'] == $value2){
                            $allTeacherRel[$value['techerId']] = 1;
                        }
                    }
                }
                foreach($user as $key => $value){
                    if($value['roleId'] == $this::$teacherRoleId){
                        if(isset($allTeacherRel[$value['id']])){
                            $userZj[] = $value;
                        }
                    }
                }
            }
            if($roleId == 6){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                foreach($user as $key => $value){
                    if($value['roleId'] == $this::$studentRoleId){
                        if($value['gradeId'] == $gradeId){
                            $userZj[] = $value;
                        }
                    }
                }
            }
            if($roleId == 7){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                $classId = $_GET['classId'];
                $classRel = array();
                foreach($classId as $key => $value){
                    $classRel[$value] = 1;
                }
                foreach($user as $key => $value){
                    if($value['roleId'] == $this::$studentRoleId){
                        if($value['gradeId'] == $gradeId){
                            if(isset($classRel[$value['class']])){
                                $userZj[] = $value;
                            }
                        }
                    }
                }
            }
            if($roleId == 8){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                foreach($user as $key => $value){
                    if($value['roleId'] == $this::$jZroleId){
                        if($value['gradeId'] == $gradeId){
                            $userZj[] = $value;
                        }
                    }
                }
            }
            if($roleId == 9){
                $gradeId = $_GET['gradeId'];
                $gradeRe = array();
                $classId = $_GET['classId'];
                $classRel = array();
                foreach($classId as $key => $value){
                    $classRel[$value] = 1;
                }
                foreach($user as $key => $value){
                    if($value['roleId'] ==  $this::$jZroleId){
                        if($value['gradeId'] == $gradeId){
                            if(isset($classRel[$value['class']])){
                                $userZj[] = $value;
                            }
                        }
                    }
                }
            }
            if($roleId == 10){
                $gradeId = $_GET['gradeId'];
                if(!$gradeId){
                    foreach($user as $key => $value){
                        if($value['roleId'] == $this::$studentRoleId || $value['roleId'] == $this::$jZroleId || $value['roleId'] == $this::$teacherRoleId){
                        }else{
                            $userZj[] = $value;
                        }
                    }
                }else{
                    foreach($user as $key => $value){
                        if($value['roleId'] ==  $gradeId){
                            $userZj[] = $value;
                        }
                    }
                }
            }
            if($roleId == 11){
                $gradeId = $_GET['gradeId'];
                if($gradeId == 1){
                    foreach($user as $key => $value){
                        $userZj[] = $value;
                    }
                }
                if($gradeId == 2){
                    $outUser = M('question_outschool')->where(array('questionId' => $questionId))->select();
                    foreach($outUser as $key => $value){
                        $value['anwser'] = unserialize($value['anwser']);
                        $userZj[] = $value;
                    }
                }
                if($gradeId == 3){
                    $ddd= 0;
                    foreach($user as $key => $value){
                        $userZj[$ddd] = $value;
                        $ddd++;
                    }
                    $outUser = M('question_outschool')->where(array('questionId' => $questionId))->select();
                    foreach($outUser as $key => $value){
                        $value['anwser'] = unserialize($value['anwser']);
                        $userZj[$ddd] = $value;
                        $ddd++;
                    }
                }
            }
            if(!$roleId){
                $ddd= 0;
                foreach($user as $key => $value){
                    $userZj[$ddd] = $value;
                    $ddd++;
                }
                $outUser = M('question_outschool')->where(array('questionId' => $questionId))->select();
                foreach($outUser as $key => $value){
                    $value['anwser'] = unserialize($value['anwser']);
                    $userZj[$ddd] = $value;
                    $ddd++;
                }
            }
            $user = $userZj;
            $return = array();
            if(count($serialNumData)>0){
                foreach($serialNumData as $key=> $value){
                    foreach($user as $key1 => $value1){
                        foreach($value1['anwser'] as $key2 => $value2){
                            if($value2['QNum'] == $value['QNum']){
                                if($value2['type'] == 0 || $value2['type'] == 1  ){
                                    $ex = explode(',',$value['answer']);
                                    foreach($ex as $key3 => $value3){
                                        if($value2['answer'] == $value3){
                                            $return[$value2['QNum']][$value3]++;
                                        }
                                    }
                                    $return[$value2['QNum']]['all']++;
                                }
                                if($value2['type'] == 2 ){
                                    if($value['answer'] == 1){
                                        if(strstr($value2['answer'],$value['value'])){
                                            $return[$value2['QNum']][$value2['answer']]++;
                                        }
                                    }
                                    if($value['answer'] == 0){
                                        if(strstr($value2['answer'],$value['value'])){
                                        }else{
                                            $return[$value2['QNum']][$value2['answer']]++;
                                        }
                                    }
                                    $return[$value2['QNum']]['all']++;
                                }
                                if($value2['type'] == 3 ){
                                    if($value['answer'] == 1){
                                        if($value2['answer'] > $value['value']){
                                            $return[$value2['QNum']][$value['value']]++;
                                        }
                                    }
                                    if($value['answer'] == 2){
                                        if($value2['answer'] < $value['value']){
                                            $return[$value2['QNum']][$value['value']]++;
                                        }
                                    }
                                    if($value['answer'] == 3){
                                        if($value2['answer'] == $value['value']){
                                            $return[$value2['QNum']][$value['value']]++;
                                        }
                                    }
                                    if($value['answer'] == 4){
                                        if($value2['answer'] != $value['value']){
                                            $return[$value2['QNum']][$value['value']]++;
                                        }
                                    }
                                    $return[$value2['QNum']]['all']++;
                                }
                            }
                        }
                    }
                }
                $type4avg = array();
                foreach($content as $key => $value){
                    $aaaa = 0;
                    if($value['type'] === 3){
                        foreach($return as $key1 => $value1){
                            if($key1 == $value['QNum']){
                                $maxSore = $value['maxScore'];
                                $duan = $maxSore/5;
                                for($i = 1 ; $i <= 5; $i++){
                                    $sore = $duan*$i;
                                    $statsore = $duan*($i-1);
                                    $return[$value['QNum']][$statsore.'-'.$sore]['count'] = 0;
                                }
                                $allsore = 0;
                                foreach($value1 as $key4 => $value4){
                                    if($key4 === 'all'){

                                    }else{
                                        $aaaa = $key4 + $aaaa;
                                        $soso = $this->getLeveal($key4,$duan);
                                        $return[$value['QNum']][$soso]['count'] = $value4;
                                        unset($return[$value['QNum']][$key4]);
                                    }
                                }
                                $type4avg[$value['QNum']]['avg'] =  round($aaaa/$value1['all'],2);
                                //$type4avg[$value['QNum']]['avg'] = round($allsore/$data2[$value['QNum']]['all'],2);
                            }
                        }
                    }
                }
                $returnData = array();
                foreach($return as $key => $value){
                    foreach($value as $key1 => $value1){
                        if($key1 === 'all'){
                            $returnData[$key]['all'] = $value1;
                        }
                        elseif(is_array($value1)){
                            $returnData[$key][$key1] = $value1;
                        }else{
                            $returnData[$key][$key1]['count'] = $value1;
                        }
                    }
                }
                $returnList = array();
                $i = 0;
                $contentArr = array();
                foreach($content as $key => $value){
                    $contentArr[$value['QNum']] = $value;
                }
                foreach($returnData as $key => $value){
                    $j = 0;
                    $returnList[$i]['count'] =$value['all'];
                    //$returnList[$i]['avg'] =$value['all']/$j;
                    $returnList[$i]['title'] = $contentArr[$key]['title'];
                    $returnList[$i]['QNum'] = $key;
                    $typeTrue = $contentArr[$key]['type'];
                    $returnList[$i]['type'] = $typeTrue;
                    if($typeTrue == 3){
                        $returnList[$i]['avg'] = $type4avg[$key]['avg'];
                    }
                    foreach($value as $key1 => $value1){
                        if($key1 !== 'all'){
                            $returnList[$i]['data'][$j]['percentage'] = (round($value1['count']/$value['all'],4)*100).'%';
                            $returnList[$i]['data'][$j]['count'] = $value1['count'];
                            if($typeTrue == 3 || $typeTrue == 2){
                                $returnList[$i]['data'][$j]['title'] = $key1;
                            }else{
                                $returnList[$i]['data'][$j]['title'] = $contentArr[$key]['QNSetting'][$key1]['value'];
                            }
                            $j++;
                        }
                    }
                    $i++;
                }
                foreach($returnList as $key => $value){
                    if(!isset($value['data'])){
                        $returnList[$key]['data'] = array();
                    }
                }
                $this->returnJson(array('statu' => 1,'title' => $tititit,'message'=> 'success','data' => $returnList),true);
            }else{
                $countgogo = count($user);
                foreach($user as $key => $value){
                    if($value['ifFill'] || isset($value['ip'])){
                        foreach($value['anwser'] as $key1 => $value1){
                            if($value1['type'] == 0 || $value1['type'] == 1){
                                $ex = explode(',',$value1['answer']);
                                foreach($ex as $key2 => $value2){
                                    $value1['answer'] = $value2;
                                    $return[$value1['QNum']][] = $value1;
                                }
                            }
                        }
                    }
                }
                $returnData = array();
                $all = 0;
                $answer = explode(',',$answer);
                foreach($return as $key => $value){
                    foreach($value as $key3 => $value3){
                        if(isset($returnData[$key][$value3['answer']])){
                            $returnData[$key][$value3['answer']]['count']++;
                        }else{
                            $returnData[$key][$value3['answer']]['count'] = 1;
                        }
                        $returnData[$key]['all']++;
                    }
                }
                $data2 = array();
                $type4avg = array();
                foreach($content as $key => $value){
                    if($value['type'] == 3 ){
                        $maxSore = $value['maxScore'];
                        $duan = $maxSore/5;
                        for($i = 1 ; $i <= 5; $i++){
                            $sore = $duan*$i;
                            $statsore = $duan*($i-1);
                            $data2[$value['QNum']][$statsore.'-'.$sore]['count'] = 0;
                        }
                        $data2[$value['QNum']]['all'] = 0;
                        $allsore = 0;
                        $aaaa = 0;
                        $countUser = 0;
                        foreach($user as $key1 => $value1){
                            foreach($value1['anwser'] as $key2 => $value2){
                                if($value2['QNum'] == $value['QNum']){
                                    $soso = $this->getLeveal($value2['answer'],$duan);
                                    $data2[$value['QNum']][$soso]['count']++;
                                    $data2[$value['QNum']]['all']++;
                                    $allsore = $value2['answer']+$allsore;
                                    $aaaa = $value2['answer'] + $aaaa;
                                }
                            }
                        }
                        $type4avg[$value['QNum']]['avg'] =  round($aaaa/ $data2[$value['QNum']]['all'],2);
                    }
                    if($value['type'] == 2 ){
                        foreach($user as $key1 => $value1){
                            foreach($value1['anwser'] as $key2 => $value2){
                                if($value2['QNum'] == $value['QNum']){
                                    $data2[$value['QNum']][$value2['answer']]['count']++;
                                    $data2[$value['QNum']]['all']++;
                                }
                            }
                        }
                    }
                }
                foreach($data2 as $key => $value){
                    $returnData[$key] = $value;
                }
                $contentre = array();
                foreach($content as $key => $value){
                    $contentre[$value['QNum']] = $value;
                }
                $returnList = array();
                $i = 0;
                $contentArr = array();
                foreach($content as $key => $value){
                    $contentArr[$value['QNum']] = $value;
                }
                foreach($returnData as $key => $value){
                    $j = 0;
                    $returnList[$i]['count'] =$value['all'];
                    //$returnList[$i]['avg'] =$value['all']/$j;
                    $returnList[$i]['title'] = $contentArr[$key]['title'];
                    $returnList[$i]['QNum'] = $key;
                    $typeTrue = $contentArr[$key]['type'];
                    $returnList[$i]['type'] = $typeTrue;
                    if($typeTrue == 3){
                        $returnList[$i]['avg'] = $type4avg[$key]['avg'];
                    }
                    foreach($value as $key1 => $value1){
                        if($key1 !== 'all'){
                            $returnList[$i]['data'][$j]['percentage'] = (round($value1['count']/$value['all'],4)*100).'%';
                            $returnList[$i]['data'][$j]['count'] = $value1['count'];
                            if($typeTrue == 3 || $typeTrue == 2){
                                $returnList[$i]['data'][$j]['title'] = $key1;
                            }else{
                                $returnList[$i]['data'][$j]['title'] = $contentArr[$key]['QNSetting'][$key1]['value'];
                            }
                            $j++;
                        }
                    }
                    $i++;
                }
                foreach($returnList as $key => $value){
                    if($value['type'] == 1){
                        $returnList[$key]['count'] = $countgogo;
                    }
                }
                $this->returnJson(array('statu' => 1,'message'=> 'success','title' => $tititit,'data' => $returnList),true);
            }
        }
        if($type == 'getQuestion'){
            $questionId = $_GET['questionId'];
            $list = M('question_list')->field('content')->where(array('scId' => $scId,'questionId' => $questionId))->find();
            $content = unserialize($list['content']);
            $return = array();
            foreach($content as $key => $value){
                if($value['type'] == 0 || $value['type'] == 1 || $value['type'] == 2 || $value['type'] == 3){
                    $return[] = $value;
                }
            }
            $this->returnJson(array('statu' =>1,'message' => 'success','data' => $return),true);
        }
    }
    private function getLeveal($sore,$duan){
        return (ceil($sore/$duan)-1)*$duan.'-'.ceil($sore/$duan)*$duan;
    }
    public function createQuestion(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $userName = $jbXn['name'];
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getUserList'){
            $role = M('role')->where(array('scId' => array(array('eq',0),array('eq',$scId),'or')))->select();
            $studentRole = $this::$studentRoleId;
            $jzRole = $this::$jZroleId;
            $adminRole = $this::$adminRoleId;
            $user = M('')->query("select id,name,roleId,class,grade,gradeId,className,department,teachingSubjects,sex from mks_user where scId= $scId AND roleId!=$adminRole");
            $roleUser = array();
            $roleRe = array();
            foreach($role as $key => $value){
                $roleRe[$value['roleId']] = $value['roleName'];
            }
            $useriiid = 0;
            foreach($user as $key => $value){
                $roleUser[$value['roleId']]['name'] = $roleRe[$value['roleId']];
                if($value['roleId'] == $this::$studentRoleId || $value['roleId'] == $this::$jZroleId ){
                    if($value['class'] && $value['gradeId'] && $value['grade']  && $value['className']){
                        $roleUser[$value['roleId']]['data'][$value['grade']]['name'] = $this->gradeToZhong($value['grade']);
                        $roleUser[$value['roleId']]['data'][$value['grade']]['data'][$value['className']]['name'] = $value['className'];
                        $roleUser[$value['roleId']]['data'][$value['grade']]['data'][$value['className']]['data'][] = $value;
                    }
                }else{
                    $roleUser[$value['roleId']]['data'][] = $value;
                }
            }
            //print_r($roleUser);
            $return = array();
            $i = 0;
            foreach($roleUser as $key => $value){
                if($key == $this::$studentRoleId || $key == $this::$jZroleId ) {
                    $useriiid++;
                    $return[$i]['wyId'] = $useriiid;
                    $return[$i]['name'] = $value['name'];
                    $j = 0;
                   foreach($value['data'] as $key1 => $value1){
                       $useriiid++;
                       $return[$i]['data'][$j]['wyId'] = $useriiid;
                       $return[$i]['data'][$j]['name'] = $value1['name'];
                       $z =0;
                       foreach($value1['data'] as $key2 => $value2){
                           $useriiid++;
                           $return[$i]['data'][$j]['data'][$z]['wyId'] = $useriiid;
                           $return[$i]['data'][$j]['data'][$z]['name']  = $value2['name'];
                           $zzzzzz = 0;
                           foreach($value2['data'] as $key3 => $value3){
                               $useriiid++;
                               $value3['wyId'] = $useriiid;
                               $return[$i]['data'][$j]['data'][$z]['data'][$zzzzzz] = $value3;
                               $zzzzzz++;
                           }
                           $z++;
                       }
                       $j++;
                   }
                }else{
                    $useriiid++;
                    $return[$i]['wyId'] = $useriiid;
                    $return[$i]['name'] = $value['name'];
                    $zzzz = 0;
                    foreach($value['data'] as $key1 => $value1){
                        $useriiid++;
                        $value1['wyId'] = $useriiid;
                        $return[$i]['data'][$zzzz]= $value1;
                        $zzzz++;
                    }
                }
                $i++;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
        }
        if($type == 'create'){
            $content = I('post.content');
            if(!$content){
                $content = array();
            }
            $user = I('post.user');
            if(!$user){
                $user =array();
            }
            $questionName = I('post.questionName');
            $explain = I('post.explain');
            $anonymous = I('post.anonymous');
            if($anonymous){
                $anonymous = 1;
            }else{
                $anonymous = 0;
            }
            $data = array(
                'questionName' => $questionName,
                'createTime' => date('Y-m-d H:i;s'),
                'state' => 0,
                'userNum' => 0,
                'browser' => 0,
                'fillProportion' => 0,
                'fillAvgTime' => 0,
                'content' => serialize($content),
                'scId' => $scId,
                'explain' => $explain,
                'releaseName' => $userName,
                'releaseId' => $userId,
                'anonymous' => $anonymous,
                'releaseTime' => date('Y-m-d H:i;s')
            );
            if($id = M('question_list')->add($data)){
                $createData = array();
                $i = 0;
                $createTime =date('Y-m-d H:i:s');
                foreach($user as $key => $value){
                    $value['ifFill'] = 0;
                    $createData[$i]['user'] = json_encode($value);
                    $createData[$i]['questionId'] = $id;
                    $createData[$i]['createTime'] = $createTime;
                    $createData[$i]['scId'] = $scId;
                    $createData[$i]['userId'] = $value['id'];
                    $i++;
                    if($i==1000){
                        $ddd = M('question_user_list')->addAll($createData);
                        $createData = array();
                        $i = 0;
                    }
                }
                if($dd = M('question_user_list')->addAll($createData) || $ddd){
                    $this->returnJson(array('statu' => 1, 'message' => '新建成功'),true);
                }else{
                    $this->returnJson(array('statu' => 0, 'message' => '新建失败'),true);
                }
            }
            $this->returnJson(array('statu' => 2, 'message' => '新建失败'),true);
        }
    }
}