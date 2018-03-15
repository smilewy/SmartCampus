<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/8/14
 * Time: 14:10
 */

namespace Home\Controller;

use PHPExcel_IOFactory;
use PHPExcel;
ob_end_clean();
class ClassManageController extends Base
{
    protected $uid;
    protected $scId;
    protected $user;
    protected $roleId;

    public function __construct()
    {
        parent::__construct();
        $this->scId = $_SESSION['loginCheck']['data']['scId'];
        $this->uid = $_SESSION['loginCheck']['data']['userId'];
        $this->roleId = $_SESSION['loginCheck']['data']['roleId'];
   /*     $this->scId = 2;
        $this->uid = 1;
        $this->roleId = 22;*/
        $this->user = D('User')->where(array('id' => $this->uid, 'scId' => $this->scId))->find();
    }

    public function common($func, $param = array())
    {
        switch ($func) {
            case 'getClass':
                $this->getClass($param['kind'],$param['class']);
                break;
            case 'getGrade':
            $this->getGrade($param['source']);
                break;
            case 'gradeClass':
                $this->gradeClass($param['gradeId'],$param['source']);
                break;
            case 'getExam':
                $this->getExam($param['gradeId'],$param['classId']);
                break;
            case 'classList':
                $this->classList();
                break;
            case 'getAllPlan':
                $this->getAllPlan();
                break;
            case 'getWish':
                $this->getWish($param);
                break;
            default:
                return null;
        }
    }

    //得到所属年级和班级
    private function getId()
    {
        $userId = $this->uid;
        $info = D('Class')->where("userid={$userId} and scId={$this->scId}")->field('classid,grade')->select();
        $id = array();
        foreach ($info as &$v) {
            $id[$v['grade']][] = $v['classid'];
        }
        return $id;
    }

    //得到下拉框的值
    private function getClass($kind = '', $class = false)
    {

        $where = array(
            'g.scId' => $this->scId
        );

        if ($this->roleId != $this::$adminRoleId) {
            $id = $this->getId();
            $gradeId = array_keys($id);
            $classId = array();
            $where['g.gradeid'] = array('in', $gradeId);
            if ($class) {
                foreach ($id as $k => $t) {
                    foreach ($t as $key => $val) {
                        $classId[] = $val;
                    }
                }
                $where['c.classid'] = array('in', $classId);
            }
        }

        $grade = M('Grade g')
            ->join('mks_class c ON g.gradeid=c.grade and g.scId=c.scId', 'LEFT')
            ->where($where)
            ->field('g.gradeid,g.name,g.scId,g.znName,c.classid,c.classname,c.number,c.userid')
            ->order('g.name asc')
            ->select();

        $situation = array();
        if (!$grade || empty($grade)) {
            $this->ajaxReturn($situation);
        }
        /*$grades = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $map = array();
        foreach ($grades as $k => $v) {
            $map[$v['name']] = $v['znName'];
        }*/
        foreach ($grade as &$v) {
            if (!array_key_exists($v['gradeid'], $situation)) {
                $situation[$v['gradeid']] = array(
                    'name' => $v['znName'],
                    'sort'=>$v['name'],
                    'gradeId'=>$v['gradeid'],
                    'scId' => $v['scId']
                );
                $situation[$v['gradeid']]['class'] = array();
            }
                $situation[$v['gradeid']]['class'][]=
                    array(
                        'classid' => $v['classid'],
                        'classname' => $v['classname'],
                        'number' => $v['number'],
                        'userid' => $v['userid']
                    );


        }
        foreach ($situation as $k => $v) {
            if ($kind == 'exam') {
                $situation[$k]['exam'] = array();
            }
            if (empty($v['class'])) {
                unset($situation[$k]);
            }
        }
        if ($kind == 'exam') {
            $map = array(
                'g.scId' => $this->scId
            );
            if ($this->roleId != $this::$adminRoleId) {
                $map['e.gradeid'] = array('in', $gradeId);
            }
            $examination = M('grade g')
                ->join('mks_examination e ON g.gradeid=e.gradeid and g.scId=e.scId', 'RIGHT')
                ->where($map)
                ->field('g.gradeid,e.examinationid,e.examination,e.headmaster')
                ->select();
            foreach ($examination as &$v) {
                if (array_key_exists($v['gradeid'], $situation)) {
                    array_push($situation[$v['gradeid']]['exam'],
                        array(
                            'examinationid' => $v['examinationid'],
                            'examination' => $v['examination'],
                            'headmaster' => $v['headmaster'],
                        ));
                }
            }
        }
        usort($situation,function($a,$b){
           return $a['sort']<$b['sort']?-1:1;
        });

        $this->ajaxReturn($situation);
    }

    //得到学生
    private function getStudent($classId)
    {
        $where=array(
            'classId'=>$classId,
            'scId'=>$this->scId,
            'isAtSchool'=>'是'
        );
        $students = D('StudentInfo')->where($where)->field('id,name')->select();
        return $students;
    }

    /*****************************************座位调整*************************************************/
    public function seatLayout($view = '')
    {
        $group = $_POST['group'];
        $response = array(
            'status' => 0,
            'msg' => '',
        );
        $classId = $_POST['classId'];
        $type = $_POST['type'];
        $where1=array(
            'scId'=>$this->scId,
            'classId'=>$classId
        );
        $rs = D('SeatLayout')->where($where1)->field('id,layout,arrange')->find();

        if (isset($type)) {
            if ($type == 'layoutSave') {
                $layout = json_encode($_POST['layout']);
                if (!$rs) {
                    $data = array(
                        'layout' => $layout,
                        'classId' => $classId,
                        'class' => $_POST['class'],
                        'grade' => $_POST['grade'],
                        'userId' => $_POST['userId'],
                        'scId' => $this->scId,
                        'createTime' => time(),
                        'creator' => $this->user['name'],
                        'creatorId' => $this->uid
                    );
                    $res = D('SeatLayout')->data($data)->add();
                } else {
                    $where=array(
                        'id'=>$rs['id']
                    );
                    $data = array(
                        'layout' => $layout,
                        'arrange' => '',
                        'lastRecordTime' => time()
                    );
                    $res = D('SeatLayout')->where($where)->data($data)->save();
                }
                if(!$res){
                    $response['msg']='操作失败';
                    $this->ajaxReturn($response);
                }
            }
                elseif ($type == 'arrangeSave') {
                $where2['id']=$_POST['id'];
                $data = array(
                    'arrange' =>json_encode($_POST['arrange']),
                    'lastRecordTime' => time()
                );
                $res = D('SeatLayout')->where($where2)->data($data)->save();
                if(!$res){
                    $response['msg']='操作失败';
                    $this->ajaxReturn($response);
                }
            } elseif ($type == 'produceArr') {//生成座位表
                $examId = isset($_POST['examId']) ? $_POST['examId'] : null;
                $order = $_POST['order'];//排序
                $acc = $_POST['according'];//排序依据 score height number
                $sex = $_POST['sex'];//性别规则 random together part
                $layout=$rs['layout'];

                if(empty($layout)){
                    $response['msg']='没有座位布局';
                    $this->ajaxReturn($response);
                }
                $layout=json_decode($layout,true);
                $res = $this->seatArrange($order, $acc, $sex, $classId, $examId,$layout);
                if(!$res){
                    $response['msg']='生成座位表出错';
                }else{
                    $response['status']=1;
                    $response['data']=$res;
                }
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            $response['msg'] = '操作成功';
            $response['status'] = 1;
            $this->ajaxReturn($response);
        }


        if ($view == 'arrange') {
            if ($rs) {
                $response['id'] = $rs['id'];
                $layout = json_decode($rs['layout'], true);
                $arr = json_decode($rs['arrange'], true);
                $arrange = array();
                foreach ($layout as $g => $l) {
                    foreach ($l as $k => $v) {
                        foreach ($v as $k1=>$v1){
                            $arrange[$g][$k][] = array(
                                'seat' => $v1,
                                'id' => $arr[$v1]['id'],
                                'name' => $arr[$v1]['name']
                            );

                        }
                    }
                }
                $response['arrange'] = $arrange;
            }
            $response['status'] = 1;
        } else {
            $students = $this->getStudent($classId);
            $total = count($students);
            if (!$rs) {
                if ($students) {
                    $response['status'] = 1;
                    $response['total'] = $total;
                }
            } else {
                $response['id'] = $rs['id'];
                $layout = json_decode($rs['layout'], true);
                $response['layout'] = $layout;
                $response['total']=$total;
                $response['status'] = 1;
            }
        }
        $this->ajaxReturn($response);
    }

    //生成座位安排
    private function seatArrange($order, $acc, $sex, $classId, $examId,$layout=array())
    {
        $arrange = array();
        $where_s=array(
            'scId'=>$this->scId,
            'classId'=>$classId
        );
        $students = D('StudentInfo')
            ->where($where_s)
            ->field('userId,name,serialNumber,sex,height')
            ->select();

        if(!$students)
            return false;
        $temp_stu = array();
        if($acc=='Score'){  //成绩
            $userId=array_map(function($v){
                return $v['userId'];
            },$students);
            $where=array(
                'scId'=>$this->scId,
                'userid'=>array('in',$userId),
                'examinationid'=>$examId
            );
            $score=D('ExaminationResults')->where($where)
                ->group('userid')->field('userid as userId,sum(results) as score')->select();
            if(!$score)
                return false;
            $temp=array();
            foreach ($score as &$v){
                $temp[$v['userId']]=$v['score'];
            }

            foreach ($students as &$v){
                $temp_stu[]=array(
                    'userId'=>$v['userId'],
                    'name'=>$v['name'],
                    'sex'=>$v['sex'],
                    'order'=>$temp[$v['userId']]
                );
            }
        }elseif($acc=='height'){//身高
            foreach ($students as &$v){
                $temp_stu[]=array(
                    'userId'=>$v['userId'],
                    'name'=>$v['name'],
                    'sex'=>$v['sex'],
                    'order'=>(int)$v['height']
                );
            }
        }else{  //序号
            foreach ($students as &$v){
                $temp_stu[]=array(
                    'userId'=>$v['userId'],
                    'name'=>$v['name'],
                    'sex'=>$v['sex'],
                    'order'=>(int)$v['serialNumber']
                );
            }
        }

        if ($order == 'asc') {
            usort($temp_stu, function ($a, $b) {
                $al = (int)$a['order'];
                $bl = (int)$b['order'];
                if ($al == $bl)
                    return 0;
                return ($al < $bl) ? -1 : 1; //升序
            });
        } else {
            usort($temp_stu, function ($a, $b) {
                $al = (int)$a['order'];
                $bl = (int)$b['order'];
                if ($al == $bl)
                    return 0;
                return ($al > $bl) ? -1 : 1; //降序
            });
        }

        $new_temp=array();
        $male=array();
        $female=array();
        if($sex=='random'){
            $new_temp=$temp_stu;
        }else{
            foreach ($temp_stu as &$v){
                if($v['sex']=='男'){
                    $male[]=$v;
                }else{
                    $female[]=$v;
                }
            }
            $female=array_reverse($female);
            $temp_stu=array_merge($male,$female);
            if($sex=='part'){//不同桌
                do{
                    foreach ($layout as $key=>$val){
                        $line=count($val);
                        if($line>count($temp_stu)){
                            $temp=array_splice($temp_stu,0);
                        }else{
                            $tempKey=(int)substr($key,-1);
                            if(($tempKey%2)==0){
                                $temp=array_splice($temp_stu,0,$line);
                            }else{
                                $temp=array_splice($temp_stu,-$line);
                            }
                        }
                        $new_temp=array_merge($new_temp,$temp);
                    }
                }while(count($temp_stu)>1);
            }elseif ($sex=='together'){//同桌
                do{
                    foreach ($layout as $key=>$val){
                        $line=count($val);
                        for($i=1;$i<=$line;$i++){
                            if($i%2==0){
                                $temp=array_splice($temp_stu,0,1);
                            }else{
                                $temp=array_splice($temp_stu,-1);
                            }
                            $new_temp=array_merge($new_temp,$temp);
                            if(count($temp_stu)<1)
                                break;
                        }
                    }
                }while(count($temp_stu)>1);
            }
        }
        return $new_temp;
    }

    /*****************************************参考*************************************************/
    private function getGrade($source){
        $where['scId']=$this->scId;
        $dao=M('grade');
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $f=array();
        if($this->roleId==$this::$teacherRoleId){
            if($source=='class'){
                $sql2="SELECT g.`gradeid`,g.`name` FROM `mks_class` AS c,mks_grade AS g 
                        WHERE c.`userid`=".$this->uid." and c.scId=".$this->scId." AND c.`grade`=g.`gradeid`"." order by g.name";
            }elseif($source=='grade'){
                $sql2="SELECT g.`gradeid`,g.`name` FROM mks_grade AS g 
                          WHERE g.`userid`=".$this->uid." and g.scId=".$this->scId." order by g.name";
            }
            $f2=$dao->query($sql2,array($this->uid,$this->scId));
            foreach($f2 as $k=>$v){
                $f[$v['gradeid']]=$v;
            }
            $f=array_values($f);
        }else{
            $f=$dao->where($where)->field('gradeid,name')->order('name asc')->select();
        }
        foreach($f as $k=>$v){
            $key=$v['name']-1;
            $f[$k]['name']=$arrgrade[$key];
        }
        $this->ajaxReturn($f);
    }

    private function gradeClass($gradeId,$source){
        $where['scId']=$this->scId;
        $where['grade']=$gradeId;
        $dao=M('class');
        if($source=='class'){
            if($this->roleId==$this::$teacherRoleId){
                $where['userid']=$this->uid;
            }
        }
        $f=$dao->where($where)->field('classid,classname')->order('classname asc')->select();
        if(empty($f)){
            $f=array();
        }
        $this->ajaxReturn($f);
    }

    private function getExam($gradeId,$classId){
        $gradeid=$gradeId;
        $classid=$classId;
        $dao1=M('examination');
        $where1['scId']=$this->scId;
        $where1['gradeid']=$gradeid;
        if($this->roleId==$this::$teacherRoleId){
            $where1['headmaster']=1;
        }
        $f1=$dao1->where($where1)->field('examinationid,examination,class,starttime,endtime')->select();
        if(!$f1['0']['examinationid']){
            $arra['return']=false;
            $arra['data']=array();
            $arra['msg']='未创建相应考试!';
            $this->ajaxReturn($arra);
            exit;
        }
        $arr2=array();
        $time1=time();
        foreach($f1 as $k=>$v){//修改，
            $time2=strtotime($v['endtime']);
            $time3=strtotime($v['starttime']);
            if(count(array_intersect($classid,explode(',',$v['class'])))&&$time3>$time1){
                unset($v['class']);
                unset($v['starttime']);
                $arr2[]=$v;
            }
        }
        if($f1['0']['examinationid']&&empty($arr2)){
            $arra['return']=false;
            $arra['data']=array();
            $arra['msg']='相关考试已过期!';
            $this->ajaxReturn($arra);
            exit;
        }
        $this->ajaxReturn($arr2);
    }

    //学生参考确认
    public function stuAttend(){
        $dao=M();
        $classid=I('post.classid');
        $classid=implode(',',$classid);
        $examinationid=I('post.examinationid');
        $screen=I('post.screen');
        $field=I('post.field');
        $order=I("post.order");
        if($order=='descending'){
            $order='desc';
        }else{
            $order='asc';
        }
        $limit=I("post.limit");
        $page=I('post.page');
        if(!$page){
            $page=1;
        }
        if(!$limit){
            $limit=10;
        }
        $limitstart=($page-1)*$limit;
        if(!$field){
            $field='u.id';
        }else{
            if($field=='participate' || $field=='reported'){
                $field='s.'.$field;
            }else{
                $field='u.'.$field;
            }
        }
        if($screen){
            $st="and (u.className like '%".$screen."%' or u.serialNumber like '%".$screen."%' or u.sex like '%".
                $screen."%' or u.name like '%".$screen."%' or s.`participate` like '%".$screen."%' or s.`reported` like '%".
                $screen."%')";
        }
        $roleId=parent::$studentRoleId;
        $sql="select s.id,u.`className`,u.serialNumber,u.`sex`,u.`name`,s.`participate`,s.reported 
              from `mks_examination_student` as s,mks_user as u where s.scId=".$this->scId." 
              and u.`class` in(".$classid.") and u.`roleId`=".$roleId." 
              and u.state=1 and s.userid=u.`id` and s.examinationid=".$examinationid." $st 
              order by ".$field." ".$order." limit ".$limitstart.",".$limit;
        $sql2="select count(s.id) from `mks_examination_student` as s,mks_user as u 
where s.scId=".$this->scId." and u.`class` in(".$classid.") and u.`roleId`=".$roleId." 
and u.state=1 and s.userid=u.`id` and s.examinationid=".$examinationid." $st";
        //exit;
        $data=$dao->query($sql);
        $data2=$dao->query($sql2);
        $pageall=ceil($data2['0']['count(s.id)']/$limit);
        foreach($data as $k=>$v){
            $data[$k]['id']=$v['id'];
            $data[$k]['className']=$v['className'];
            $data[$k]['serialNumber']=$v['serialNumber'];
            $data[$k]['sex']=$v['sex'];
            $data[$k]['name']=$v['name'];
            $data[$k]['participate']=$v['participate']=='是'?true:false;
            $data[$k]['reported']=$v['reported']=='是'?true:false;
            $data1['data'][$k]=$data[$k];
        }
        if($data){
            $data1['page']['page']=$page;
            $data1['page']['pageall']=$pageall;
            $data1['page']['count']=$data2['0']['count(s.id)'];
        }elseif(empty($data)){
            $data1['data']=null;
        }
        $this->ajaxReturn($data1);
    }

    public function save($source){
        $data=I('post.data');
        $dao=M('examination_student');
        $arr['return']=true;
        $headmaster='否';
        if($source=='class'){
            if($this->roleId==$this::$teacherRoleId){
                $sql2="SELECT COUNT(classid) AS num FROM mks_class WHERE userid=".$this->uid;
                $f2=$dao->query($sql2);
                if($f2['0']['num']){
                    $headmaster='是';
                }
            }
            $val='';
            foreach($data as $k=>$v){
                $id=$v['id'];
                $participate=$v['participate']=='true'?'是':'否';
                $reported=$v['reported']=='true'?'是':'否';
                $val .= '(' . $id . ',' . "'{$participate}'" . ','
                    . "'{$reported}'" .','."'{$headmaster}'" . '),';
            }
            $val= rtrim($val, ',');
                $sql = "insert into mks_examination_student (id,participate,reported,headmaster)
            values {$val} on duplicate key update participate=values(participate),
            reported=values(reported),headmaster=values(headmaster)";

                $f=M('')->execute($sql);
                if($f===false){
                    $arr['return']=false;
                }
        }elseif ($source=='grade'){
            $val='';
            foreach($data as $k=>$v){
                $id=$v['id'];
                $participate=$v['participate']=='true'?'是':'否';
                $headmaster="是";
                $val .= '(' . $id . ',' . "'{$participate}'" . ','
                    ."'{$headmaster}'" . '),';
            }
            $val= rtrim($val, ',');
            $sql = "insert into mks_examination_student (id,participate,headmaster)
            values {$val} on duplicate key update participate=values(participate),
            headmaster=values(headmaster)";
            $f=M('')->execute($sql);
            if($f===false){
                $arr['return']=false;
            }
        }
        $this->ajaxReturn($arr);
    }

    //各班参考确认
    public function classAttend(){
        $dao=M();
        $classid=I('post.classid');
        $classid=implode(',',$classid);
        $examinationid=I('post.examinationid');
        $roleId=parent::$studentRoleId;
        $key=I('post.key');
        $where='';
        if(!empty($key)) {
            $where=" and (b.branch like '%{$key}%' or c.classname like '%{$key}%')";
        }
        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $sql="SELECT b.branch,c.classname,COUNT(IF(s.participate='是',TRUE,NULL )) AS participate,c.classid 
        FROM `mks_examination_student` AS s,mks_user AS u,mks_class AS c,mks_class_branch AS b 
        WHERE c.branch=b.branchid AND u.class=c.classid AND s.scId=".$this->scId." AND u.`class` IN(".$classid.") 
            AND u.`roleId`=".$roleId." AND u.state=1 AND s.userid=u.`id` AND s.examinationid=".$examinationid.$where
            ." GROUP BY u.class"." limit ".$limit_page;
        $count_sql="SELECT b.branch,c.classname,COUNT(IF(s.participate='是',TRUE,NULL )) AS participate,c.classid 
        FROM `mks_examination_student` AS s,mks_user AS u,mks_class AS c,mks_class_branch AS b 
        WHERE c.branch=b.branchid AND u.class=c.classid AND s.scId=".$this->scId." AND u.`class` IN(".$classid.") 
            AND u.`roleId`=".$roleId." AND u.state=1 AND s.userid=u.`id` AND s.examinationid=".$examinationid.$where
            ." GROUP BY u.class";
        $all=count($dao->query($count_sql));
        $sql2="SELECT c.classid,u.name FROM mks_class AS c,mks_user AS u 
            WHERE c.classid IN(".$classid.") AND c.userid=u.id AND c.scId=".$this->scId;
        $sql3="SELECT class,COUNT(id) as num FROM mks_user WHERE class IN(".$classid.") 
                    AND scId=".$this->scId." AND roleId=".$roleId." AND state=1 GROUP BY class";
        $f=$dao->query($sql);
        if(!$f){
            $this->ajaxReturn(array('status'=>0,
                'data'=>array()));
        }
        $f2=$dao->query($sql2);
        $f3=$dao->query($sql3);
        foreach($f2 as $k=>$v){
            $arr2[$v['classid']]=$v['name'];
        }
        foreach($f3 as $k=>$v){
            $arr3[$v['class']]=$v['num'];
        }
        foreach($f as $k=>$v){
            $f[$k]['total']=$arr3[$v['classid']];
            $f[$k]['unparticipate']=$arr3[$v['classid']]-$v['participate'];
            $f[$k]['headmaster']=$arr2[$v['classid']];
        }
        foreach ($f as $k=>$v){
         $response['data'][]=$v;
        }
        $response['status']=1;
        $response['total']=$all;
        $response['maxPage'] = ceil($all / $count) < 1 ? 1 : ceil($all / $count);
        $this->ajaxReturn($response);
    }


    /*****************************************志愿调整*************************************************/
    //得到所属的班级列表
    private function classList()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'scId' => $this->scId
        );
        if ($this->roleId != $this::$adminRoleId) {
            $where['userid'] = $this->uid;
        }
        $class = D('Class')
            ->where($where)
            ->select();
        if (!$class) {
            $this->ajaxReturn($response);
        }
        $grade = D('Grade')->where(array('scId' => $this->scId))->select();
        $map = array();
        foreach ($grade as $k => $v) {
            $map[$v['gradeid']] = $v['znName'];
        }
        $data = array();
        foreach ($class as $k => $v) {
            if (!isset($v['gradeid']))
                $data[$v['gradeid']] = array(
                    'gradeName' => $map[$v['grade']],
                    'gradeId' => $v['grade'],
                    'classes' => array()
                );
            $data[$v['gradeid']]['classes'][] = array(
                'className' => $v['classname'],
                'classId' => $v['classid']
            );
        }
        sort($data);
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
    }

    //得到所有方案 //已测试
    private function getAllPlan()
    {
        $data = D('BranchPlan')->where(
            array('scId' => $this->scId, 'isNew' => 0))
            ->field('id,name,changeStart,changeEnd')->select();
        if (empty($data))
            $data = array();
        $this->ajaxReturn($data);
    }
    //调整学生志愿
    public function adjustWish($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];
        if (isset($type)) {
            if ($type == 'adjust') {
                $process = D('BranchProcess')->where(array('planId' => $planId, $this->scId, 'step' => 18))->find();
                if ($process['status'] == 1) {
                    $response['msg'] = '此方案已同步数据,不可再调整志愿！';
                    $this->ajaxReturn($response);
                }
                $time = time();
                $plan = D('BranchPlan')->where(array('id'=>$planId))->find();
                if ($time < $plan['changeStart'] || $time > $plan['changeEnd']) {
                    $response['msg'] = '当前不在修改志愿时间之内';
                    $this->ajaxReturn($response);
                }
                if ($plan['teaChange'] != 1) {
                    $response['msg'] = '本方案教师不可修改志愿';
                    $this->ajaxReturn($response);
                }

                $bar = D('BranchStudent')
                    ->where(array('id' => $_POST['id']))
                    ->find();
                $wishId=(int)$bar['wishId'];
                if(empty($wishId)){
                    $number = (int)$plan['notFill'] - 1;
                }else{
                    $number = 0;
                }
                $score=json_decode($bar['comScore'],true);
                $score=$score[$_POST['wishId']]['score'];
                $rank=json_decode($bar['comRank'],true);
                $rank=$rank[$_POST['wishId']];

                $data = array(
                    'wishId' => $_POST['wishId'],
                    'branch' => $_POST['branch'],
                    'major' => $_POST['major'],
                    'score'=>$score,
                    'rank'=>$rank,
                    'changeId' => $this->uid,
                );
                $rs = D('BranchStudent')
                    ->where(array('id' => $_POST['id']))
                    ->data($data)->save();
                if (!$rs) {
                    $response['msg'] = '修改失败';
                    $this->ajaxReturn($response);
                }

                if ($number) { //修改填写志愿人数
                    D('BranchPlan')->where(array('id'=>$planId))->data(array('notFill' => $number))->save();
                }
                $response['msg'] = '修改成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
        }

        $gradeId = $_REQUEST['gradeId'];
        $preClass = $_REQUEST['className'];
        $where = array(
            'scId' => $this->scId,
            'planId' => $planId,
            'gradeId' => $gradeId,
            'preClass' => $preClass
        );

        $export=$_REQUEST['export'];
        if($export=='ensure'){
            $stus = D('BranchStudent')->where($where)
                ->field('id,name,sex,preGrade,preClass,branch,major')
                ->select();
            error_reporting(E_ALL);
            date_default_timezone_set('Asia/Hong_Kong');
            Vendor('PHPExcel.PHPExcel');
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("智慧校园")
                ->setLastModifiedBy("智慧校园")
                ->setTitle("数据EXCEL导出")
                ->setSubject("数据EXCEL导出")
                ->setDescription("备份数据")
                ->setKeywords("excel")
                ->setCategory("result file");
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '姓名')
                ->setCellValue('B1', '性别')
                ->setCellValue('C1', '年级')
                ->setCellValue('D1', '班级')
                ->setCellValue('E1', '科类')
                ->setCellValue('F1', '专业');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $map=array();
            $sub=0;
            foreach ($stus as $k=>$v){
                $num=$k+2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A'.$num, $v['name'])
                    ->setCellValue('B'.$num, $v['sex'])
                    ->setCellValue('C'.$num, $v['preGrade'])
                    ->setCellValue('D'.$num, $v['preClass'])
                    ->setCellValue('E'.$num, $v['branch'])
                    ->setCellValue('F'.$num, $v['major']);
                $foo=ord('C');
                for($i=1;$i<=$sub;$i++){
                    $foo+=1;
                    $str=chr($foo);
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue($str.$num, $v[$map[$str]]['score']);
                }
            }
            $objPHPExcel->getActiveSheet()->setTitle('教师志愿调整表');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.'教师志愿调整表'.'.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        $key = $_POST['key'];
        if (!empty($key)) {
            $where['name|branch|major']=array('like',"%{$key}%");
        }
        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $stus = D('BranchStudent')->where($where)
            ->field('id,name,sex,preGrade,preClass,branch,major')
            ->limit($limit_page)->select();
        if (!$stus) {
            $this->ajaxReturn($response);
        }
        $total= (int)D('BranchStudent')->where($where)->count();
        $response['total'] = $total;
        $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        $response['status'] = 1;
        $response['data'] = $stus;
        $this->ajaxReturn($response);
    }

    //得到科类/专业 //已测试
    private function getWish($param)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $planId = $param['planId'];
        $wish = array();
        $data = D('BranchWish')->where(array('planId'=>$planId))
            ->field('id as wishId,branch,branchId,major,majorId')
            ->select();

        if (!$data)
            $this->ajaxReturn($response);
        foreach ($data as &$v) {
            if (!array_key_exists($v['branchId'], $wish))
                $wish[$v['branchId']] = array(
                    'name' => $v['branch'],
                    'branchId' => $v['branchId'],
                    'majors' => array()
                );
            $wish[$v['branchId']]['majors'] [] = array(
                'name' => $v['major'],
                'parentId' => $v['branchId'],
                'majorId' => $v['majorId'],
                'wishId' => $v['wishId']
            );
        }
        sort($wish);
        $response['status'] = 1;
        $response['data'] = $wish;
        $this->ajaxReturn($response);
    }
}
