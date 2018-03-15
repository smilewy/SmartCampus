<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/8/16
 * Time: 11:20
 */

namespace Home\Controller;

use Home\Model\BranchProcessModel;
use Home\Common\Accessory;
use Home\Common\Import;
use PHPExcel;
use PHPExcel_IOFactory;

/*
 * 分科分班
 * */
ob_end_clean();

//权限已设置
class DivideBranchController extends Base
{
    protected $roleId;
    protected $scId;
    protected $uid;
    protected $user;

    public function __construct()
    {
        parent::__construct();
        $this->scId     = $_SESSION['loginCheck']['data']['scId'];
        $this->uid      = $_SESSION['loginCheck']['data']['userId'];
        $this->roleId   = $_SESSION['loginCheck']['data']['roleId'];
        /*
               $this->roleId = 22;
                $uid = 95;
                $scId = 2;
                $this->scId = $scId;
                $this->uid = $uid;*/
        $this->user     = D('User')->where(array('id' => $this->uid, 'scId' => $this->scId))->find();
    }

    /*************************************管理员操作*******************************************/
    //各流程变化
    private function changeProcess(Array $process, $planId)
    {
        $steps  = implode(',', array_keys($process));

        $sql    = "UPDATE mks_branch_process SET status = CASE step ";
        foreach ($process as $step => $status) {
            $sql .= sprintf("WHEN %d THEN %d ", $step, $status);
        }
        $sql .= "END WHERE step in ({$steps}) and scId={$this->scId} and planId={$planId}";

        M('')->execute($sql);
    }

    //公共调用接口
    public function common($func, $param = array())
    {
        switch ($func) {
            case 'getAllPlan':  //分班方案
                $this->getAllPlan();
                break;
            case 'getAllStudent': //待选分班学生
                $this->getAllStudent($param['planId']);
                break;
            case 'callExam': //调用考试
                $this->callExam($param);
                break;
            case 'callBranch': //调用科类
                $this->callBranch($param);
                break;
            case 'callMajor': //调用专业
                $this->callMajor($param);
                break;
            case 'getWish': //得到科类专业
                $this->getWish($param);
                break;
            case 'getClass': //待选班级
                $this->getClass($param['planId']);
                break;
            case 'getStu': //得到待选学生
                $this->getStu($param['planId'], $param['key']);
                break;
            case 'getSubject':  //得到待选考试
                $this->getSubject($param);
                break;
            case 'getLevel':  //得到班级水平
                $this->getLevel();
                break;
            case 'getAllWish':
                $this->getAllWish($param);
                break;
            case 'getGrade':
                $this->getGrade($param['planId']);
                break;
            case 'classStu':
                $this->classStu($param['planId'], $param['classId'], $param['gradeId'], $param['key']);
                break;
            case 'classLevel':
                $this->classLevel($param['planId']);
                break;
            case 'getBelong':
                $this->getBelong();
                break;
            case 'classList':
                $this->classList();
                break;
            default:
                return null;
        }
    }

    //创建分班方案    //已测试
    public function createPlan()
    {
        $response = array(
            'status' => 0,
            'msg' => ''
        );
        $type = $_POST['type'];
        if (!$type || $type != 'create') {
            $response['msg'] = '没有权限创建';
            $this->ajaxReturn($response);
        } elseif ($type == 'create') {
            $data = $_POST;
            $plan = array(
                'creator'       => $this->user['name'],
                'creatorId'     => $this->uid,
                'name'          => $data['name'],
                'fillStart'     => strtotime($data['fillStart']),
                'fillEnd'       => strtotime($data['fillEnd']),
                'changeStart'   => strtotime($data['changeStart']),
                'changeEnd'     => strtotime($data['changeEnd']),
                'stuSearch'     => (int)$data['stuSearch'],
                'stuChange'     => (int)$data['stuChange'],
                'teaChange'     => (int)$data['teaChange'],
                'notice'        => $data['notice'],
                'notFill'       => 0,
                'fillNumber'    => 0,
                'createTime'    => time(),
                'scId'          => $this->scId,
            );
            // 保存方案
            $rs = D('BranchPlan')->data($plan)->add();
            if (!$rs) {
                $response['msg'] = '保存方案失败';
                $this->ajaxReturn($response);
            }
            $bp = new BranchProcessModel();
            //存入流程设置
            $rs = $bp->addPlanProcess($rs, $this->scId);
            if (!$rs) {
                $response['msg'] = '保存流程失败';
                $this->ajaxReturn($response);
            }
            $response['msg'] = '创建成功';
            $response['status'] = 1;
            $this->ajaxReturn($response);
        }
    }

    //分科分班记录    //已测试
    public function planLog()
    {
        $response = array(
            'status' => 0,
            'msg'    => '',
            'data'   => ''
        );
        $type = $_POST['type'];
        if (!empty($type)) {
            $rs = false;
            if ($type == 'del') {
                $planId = $_POST['planId'];
                $rs = D('BranchPlan')->where(array('id' => $planId, 'scId' => $this->scId))->delete();
            }
            if ($rs) {
                $response['msg'] = '删除成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            }
            $response['msg'] = '无权限';
            $this->ajaxReturn($response);
        }


        $page       = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count      = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page   = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        $key        = $_POST['key'];
        $logWhere   = array(
            'scId'  => $this->scId,
            'isNew' => 0);
        if ($this->roleId != $this::$adminRoleId) {
            $logWhere['creatorId'] = $this->uid;
        }
        if (isset($key)) {
            $logWhere['name'] = array('like', "%{$key}%");
        }
        $order = 'desc';
        if (isset($_POST['order'])) {
            if ($_POST['order'] == 'ascending') {
                $order = 'asc';
            }
        }
        $order = 'createTime ' . $order;


        $data = D('BranchPlan')->where($logWhere)
            ->field('id,name,fillStart,fillEnd,changeStart,changeEnd,createTime')
            ->order($order)
            ->limit($limit_page)->select();
        if ($data) {
            $planIds=array_map(function($v){
                return $v['id'];
            },$data);

            $student=D('BranchStudent')->where(array('scId'=>$this->scId,
                'planId'=>array('in',$planIds)))->field('wishId,planId')->select();
            $numberMap=array();
            foreach ($student as $k=>$v){
                $numberMap[$v['planId']]['total']+=1;
                if(empty($v['wishId']))
                $numberMap[$v['planId']]['not']+=1;
            }

            /*foreach ($data as &$v) {
                if ((int)$v['fillNumber'] == 0) {
                    $v['rate'] = 0;
                } else {
                    $temp = (int)$v['fillNumber'] - (int)$v['notFill'];
                    $fillNumber = (int)$v['fillNumber'];
                    $v['rate'] = round($temp / $fillNumber, 3) * 100;
                }
            }*/
            foreach ($data as $k=>$v){
                $data[$k]['fillNumber']=isset($numberMap[$v['id']]['total'])?$numberMap[$v['id']]['total']:0;
                $data[$k]['notFill']=isset($numberMap[$v['id']]['not'])?$numberMap[$v['id']]['not']:0;
                if($data[$k]['fillNumber']>0){
                    $fill=$data[$k]['fillNumber'];
                    $not=$data[$k]['notFill'];
                    $data[$k]['rate']=round(($fill-$not) / $fill, 3) * 100;
                }else{
                    $data[$k]['rate']=0;
                }
            }
            $response['data'] = $data;
            $response['status'] = 1;
            $response['total'] = (int)D('BranchPlan')->where($logWhere)->count();
            $response['maxPage'] = ceil($response['total'] / $count) < 1 ? 1 : ceil($response['total'] / $count);
            $this->ajaxReturn($response);
        }
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

    //分科分班管理 //已测试
    public function getAllProcess($planId)
    {
        $pd = new BranchProcessModel();
        $data = $pd->getAllProcess($planId);
        if (empty($data))
            $data = array();
        $this->ajaxReturn($data);
    }

    //修改分班方案    //已测试
    public function updatePlan($planId)
    {
        $response = array(
            'status' => 0,
            'msg' => '',
            'data' => ''
        );
        $type = $_POST['type'];
        if (!empty($type) && $type != 'update') {
            $response['msg'] = '没有权限修改';
            $this->ajaxReturn($response);
        } elseif ($type == 'update') {
            $data = $_POST;
            $update = array(
                'name' => $data['name'],
                'fillStart' => strtotime($data['fillStart']),
                'fillEnd' => strtotime($data['fillEnd']),
                'changeStart' => strtotime($data['changeStart']),
                'changeEnd' => strtotime($data['changeEnd']),
                'stuSearch' => (int)$data['stuSearch'],
                'stuChange' => (int)$data['stuChange'],
                'teaChange' => (int)$data['teaChange'],
                'notice' => $data['notice'],
                'lastRecordTime' => time()
            );
            $rs = D('BranchPlan')->where(
                array('id' => $planId))->data($update)->save();
            if (!$rs) {
                $response['msg'] = '修改出错';
                $this->ajaxReturn($response);
            }
            $response['msg'] = '修改成功';
            $response['status'] = 1;
            $this->ajaxReturn($response);
        }
        $plan = D('BranchPlan')->where(
            array('id' => $planId))
            ->field('id,name,fillStart,fillEnd,changeStart,changeEnd,stuSearch,stuChange,teaChange,notice')
            ->find();
        if ($plan) {
            $response['data'] = $plan;
            $response['status'] = 1;
        }
        $this->ajaxReturn($response);
    }

    //(分班学生管理)得到所有待选分班学生
    private function getAllStudent($planId)
    {
        $where = array(
            'scId' => $this->scId,
            'isNew' => 0,
            'classId' => array('gt', 0),
            'isAtSchool' => '是'
        );

        $allStudent = D('StudentInfo')->where($where)
            ->field('userId,id as stuId,name,sex,grade,gradeId,
            classId,serialNumber,className')
            ->select();

        $data = D('BranchStudent')->where(array('planId' => $planId,'scId'=>$this->scId))
            ->field('userId')
            ->select();

        $userIds = array_map(function ($v) {
            return $v['userId'];
        }, $data);

        $grade = D('Grade')->where(array('scId' => $this->scId))
            ->field('gradeid,znName')->select();
        foreach ($grade as $k => $v) {
            $map[$v['gradeid']] = $v['znName'];
        }
        if (!$allStudent)
            return null;
        $student = array();
        foreach ($allStudent as $k => $v) {
            $allStudent[$k]['grade1'] = $v['grade'];
            $allStudent[$k]['grade'] = $map[$v['gradeId']];

        }

        foreach ($allStudent as $k => $v) {
            if (!array_key_exists($v['gradeId'], $student)) {
                $student[$v['gradeId']] = array(
                    'id' => $v['gradeId'],
                    'sort' => $v['grade1'],
                    /*'name' => $v['grade'],*/
                    'checked' => false,
                    'name' => $map[$v['gradeId']],
                    'class' => array()
                );
            }
            if (!array_key_exists($v['classId'], $student[$v['gradeId']]['class'])) {
                $student[$v['gradeId']]['class'][$v['classId']] = array(
                    'id' => $v['classId'],
                    'checked' => false,
                    'name' => $v['className'],
                    'class' => array()
                );
            }
            $foo['checked'] = in_array($v['userId'], $userIds) ? true : false;
            if ($foo['checked']) {
                $student[$v['gradeId']]['checked'] = true;
                $student[$v['gradeId']]['class'][$v['classId']]['checked'] = true;
            }
            $temp = array_merge($v, $foo);
            $student[$v['gradeId']]['class'][$v['classId']]['class'][] = $temp;
        }
        foreach ($student as $k => $v) {
            sort($student[$k]['class']);
        }
        usort($student, function ($a, $b) {
            return $a['sort'] < $b['sort'] ? -1 : 1;
        });
        $this->ajaxReturn($student);
        //return $student;
    }

    //分班学生管理 //已测试
    public function studentManage($planId)
    {
        $model = D('BranchStudent');
        $response = array(
            'status' => 0,
            'msg' => '',
            'data' => ''
        );
        $type = $_POST['type'];
        if ($type == 'save') {
            $students = I('post.students');
            //$students =$_POST['students'];
            $stuLists = json_decode(urldecode($students), true);
            //$stuLists = json_decode($students, true);
            if (empty($students)) {
                $model->startTrans();
                $rs = $model->where(array('planId' => $planId))->delete();
                if (!$rs) {
                    $model->rollback();
                    $response['msg'] = '操作失败';
                    $this->ajaxReturn($response);
                }
                $model->commit();
                $response['status'] = 1;
                $response['msg'] = '操作成功';
                $this->ajaxReturn($response);
            }
            if (is_array($stuLists)) {
                $rs = $model->where(array('planId' => $planId))->find();
                if ($rs) {
                    $model->startTrans();
                    $res = $model->where(array('planId' => $planId))->delete();
                    if (!$res)
                        $model->rollback();
                    $model->commit();
                }
                if (!empty($students)) {
                    $val = '';
                    foreach ($stuLists as &$v) {
                        $val .= '(' . "{$v['userId']}," . "{$v['stuId']}," . "'{$v['name']}',"
                            . "'{$v['sex']}'," . "{$v['gradeId']}," . "'{$v['grade']}'," . "'{$v['className']}',"
                            . "'{$v['serialNumber']}'," . "{$planId}," . "{$this->scId}" . '),';
                    }
                    $val = rtrim($val, ',');
                    $sql = "insert into mks_branch_student (userId,stuId,name,sex,gradeId,preGrade,preClass,preSerialNumber,planId,scId)
                          values {$val}";
                    $res = M()->execute($sql);
                }
                D('BranchSubject')->where(array('planId' => $planId, 'scId' => $this->scId))
                    ->save(array(
                        'totalNumber' => count($stuLists),
                        'recordNumber' => 0
                    ));

                if ($res) {
                    $response['status'] = 1;
                    $response['msg'] = '保存成功';
                    $status = D('BranchProcess')
                        ->where(array('planId' => $planId, 'scId' => $this->scId, 'step' => 4))
                        ->getField('status');
                    if ($status != 1) {
                        $process = array(2 => 1, 3 => 1);
                    } else {
                        $process = array(2 => 1, 3 => 1, 5 => 2);
                    }
                    $this->changeProcess($process, $planId);
                    $number = count($stuLists);
                    //修改志愿填写人数
                    D('BranchPlan')->where(array('id' => $planId))->data(array('fillNumber' => $number, 'notFill' => $number))->save();
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '保存失败';
                $this->ajaxReturn($response);
            }
        } elseif ($type == 'clean') {
            /**/
        }


        $data = $model->where(array('planId' => $planId,'scId'=>$this->scId))
            ->field('userId,stuId,name,sex,preGrade as grade,gradeId,
            preSerialNumber as serialNumber,
            preClass as className')
            ->select();
        if ($data) {
            foreach ($data as &$v) {
                $v['checked'] = true;
            }
            $response['status'] = 1;
            $response['data'] = $data;
            //$total = (int)$model->where("planId={$planId}")->count();
            // $response['total'] = $total;
            //$response['maxPage'] = ceil($total / $count) <1 ? 1 : ceil($total / $count);
            $this->ajaxReturn($response);
        }
        $this->ajaxReturn($response);
    }

    //调用考试 已测试 对数据库操作有点多 待优化 @todo
    private function callExam($param)
    {

        $response['status'] = 0;
        $response['data'] = array();
        $planId = $param['planId'];
        $type = $param['type'];
        $exam = $param['exam'];
        $grades = D('BranchStudent')->where(array('planId' => $planId, 'scId' => $this->scId))
            ->field('DISTINCT(preGrade),gradeId')
            ->select();
        $grade = array();
        foreach ($grades as &$v) {
            $grade[$v['gradeId']] = $v['preGrade'];
        }
        //得到年级id
        $gradeId = array_map(function ($v) {
            return $v['gradeId'];
        }, $grades);

        //调用考试成绩
        if ($type == 'sure') {
            $response['msg'] = 'fail';
            $total = D('BranchStudent')->where(array('planId' => $planId, 'scId' => $this->scId))->count();

            foreach ($exam as &$v) {
                $new_exam = array(
                    'examination' => $v['name'],
                    'examinationId' => $v['id'],
                    'planId' => $planId,
                    'scId' => $this->scId
                );
                $branExamId = D('BranchExam')->data($new_exam)->add();//将考试加入
                //$branExamId=18;
                if (!$branExamId)
                    $this->ajaxReturn($response);

                $where_subs = array(
                    'es.scId' => $this->scId,
                    'es.examinationid' => $v['id']
                );

                $subs = D('ExaminationSubject es')
                    ->join('mks_subject s ON es.subject=s.subjectid', 'LEFT')
                    ->where($where_subs)
                    ->field('es.examinationid as examId,es.proportion,s.subjectid as subId,s.subjectname as subName')
                    ->select();                             //找出考试的拥有的科目

                $subject = array();
                $old_sub = array();
                foreach ($subs as &$val) {
                    $subject[$val['subId']] = array(
                        'subject' => $val['subName'],
                        'branchExamId' => $branExamId,
                        'planId' => $planId,
                        'scId' => $this->scId,
                        'totalNumber' => (int)$total,
                        'recordNumber' => 0,
                        'lastRecordTime' => time(),
                        'maxPoint' => $val['proportion']
                    );
                    $old_sub[$val['subName']] = $val['subId'];
                }

                $subjectLists = array_values($subject);
                $rs = D('BranchSubject')->addAll($subjectLists);//将科目加入

                if (!$rs)
                    $this->ajaxReturn($response);
                $new_sub = D('BranchSubject')
                    ->where(array('branchExamId' => $branExamId, 'scId' => $this->scId, 'planId' => $planId))
                    ->field('id,subject')->select();        //得到科目id
                $map = array();
                foreach ($new_sub as &$k) {
                    $map[$old_sub[$k['subject']]] = $k['id'];  //旧id---新id 映射关系
                }
                $where2 = array(
                    'bs.planId' => $planId,
                    'bs.scId' => $this->scId,
                    'er.examinationid' => $v['id']
                );
                $subScore = D('BranchStudent bs')
                    ->join('mks_examination_results er ON bs.userId=er.userid', 'LEFT')
                    ->where($where2)
                    ->field('bs.id,bs.subScore,er.subjectid as subId,er.resultsAll as score')
                    ->select();                         //得到现有分班学生在考试中各科目的成绩

                $new = array();
                $fillNumber = array();
                foreach ($subScore as &$item) {
                    if (!isset($new[$item['id']]['subScore']))
                        $new[$item['id']]['subScore'] = json_decode($item['subScore'], true);
                    if (!isset($fillNumber[$map[$item['subId']]])) //填写人数
                        $fillNumber[$map[$item['subId']]] = 0;
                    $new[$item['id']]['subScore'][$map[$item['subId']]] = (int)$item['score'];
                    $fillNumber[$map[$item['subId']]] += 1;
                }
                if (!empty($fillNumber)) {
                    $sub_val = '';
                    foreach ($fillNumber as $k1 => $v1) {
                        $sub_val .= '(' . "{$k1}" . ',' . "{$v1}" . '),';
                    }
                    $sub_val = rtrim($sub_val, ',');
                    $stu_val = '';
                    foreach ($new as $k2 => $v2) {
                        $newScore = json_encode($v2['subScore']);
                        $stu_val .= '(' . "{$k2}" . ',' . "'{$newScore}'" . '),';
                    }

                    $stu_val = rtrim($stu_val, ',');
                    $sub_sql = "insert into mks_branch_subject (id,recordNumber) 
            values {$sub_val} on duplicate key update recordNumber=values(recordNumber)";
                    $stu_sql = "insert into mks_branch_student (id,subScore) 
            values {$stu_val} on duplicate key update subScore=values(subScore)";
                    $rs = M()->execute($sub_sql);
                    if (!$rs) {
                        $response['msg'] = 'fail';
                        break;
                    }
                    $rs = M()->execute($stu_sql);
                }
                if (!$rs) {
                    $response['msg'] = 'fail';
                    break;
                }
                $response['msg'] = 'success';
            }
            if ($response['msg'] == 'success') {
                $response['status'] = 1;
            }
            $this->ajaxReturn($response);
        }

        $examTemp = D('BranchExam')
            ->where(array('planId' => $planId, 'scId' => $this->scId, 'examinationId' => array('gt', 0)))
            ->select();

        $where_exam = array('gradeid' => array('in', $gradeId), 'scId' => $this->scId);
        if ($examTemp) {
            $examId = array_map(function ($v) {
                return $v['examinationId'];
            }, $examTemp);
            $where_exam['examinationid'] = array('not in', $examId);
        }
        //得到考试
        $where_exam['release'] = 1;
        $exams = D('Examination')
            ->where($where_exam)
            ->field('gradeid,examinationid,examination')
            ->select();

        $exam = array();
        if (!$exams)
            $this->ajaxReturn($response);
        foreach ($exams as &$v) {
            $exam[] = array(
                'examId' => $v['examinationid'],
                'name' => $v['examination'],
                'grade' => $grade[$v['gradeid']]
            );
        }
        $response['data'] = $exam;
        $response['status'] = 1;
        $this->ajaxReturn($response);
    }

    //分班成绩依据 //已测试
    public function scoreBasis($planId)
    {
        $response = array(
            'status' => 0,
            'msg' => '',
            'data' => ''
        );
        $type = $_POST['type'];
        if (isset($type)) {
            if ($type == 'addExam') {//添加考试依据
                $rs = D('BranchExam')->where(array('examination' => $_POST['exam'], 'planId' => $planId, 'scId' => $this->scId))->find();
                if ($rs) {
                    $response['msg'] = '考试已存在';
                    $response['examId'] = $rs;
                    $this->ajaxReturn($response);
                }
                $rs = D('BranchExam')->add(array('examination' => $_POST['exam'], 'planId' => $planId, 'scId' => $this->scId));
            } elseif ($type == 'editExam') {
                $examId = $_POST['examId'];
                $exam = array(
                    'examination' => $_POST['exam'],
                );
                $rs = D('BranchExam')->where(array('id' => $examId))->save($exam);
            } elseif ($type == 'addSubject') {//添加科目依据
                $rs = D('BranchSubject')->where(array('subject' => $_POST['subject'],
                    'branchExamId' => $_POST['examId']))->find();
                if ($rs) {
                    $response['msg'] = '科目已存在';
                    $this->ajaxReturn($response);
                }
                $students = D('BranchStudent')
                    ->where(array('planId' => $planId,'scId'=>$this->scId))
                    ->field('userId,name,sex,preGrade,preClass')
                    ->order('userId asc')
                    ->select();
                /*foreach ($students as &$v) {
                    $v['score'] = null;
                }
                $score = json_encode($students);*/
                $total = count($students);
                $data = array(
                    'subject' => $_POST['subject'],
                    'branchExamId' => $_POST['examId'],
                    'recordNumber' => 0,
                    'totalNumber' => $total,
                    //'score' => $score,
                    'planId' => $planId,
                    'scId' => $this->scId
                );
                $rs = D('BranchSubject')->add($data);
            } elseif ($type == 'delExam') {//删除考试依据
                $rs = D('BranchExam')->where(array('id' => $_POST['examId']))->delete();
                if ($rs) {
                    D('BranchSubject')->where(array('branchExamId' => $_POST['examId']))->delete();
                }
            } elseif ($type == 'delSubject') {//删除科目依据
                $rs = D('BranchSubject')->where(array('id' => $_POST['subId']))->delete();
            } else {
                $response['msg'] = '没权限进行操作';
                $this->ajaxReturn($response);
            }
            if ($rs) {
                $response['status'] = 1;
                $response['msg'] = '操作成功';
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '操作失败';
                $this->ajaxReturn($response);
            }
        }

        $where = array(
            'e.planId' => $planId,
            'e.scId' => $this->scId);
        //得到分班成绩依据
        $data = M('BranchExam e')
            ->join('mks_branch_subject s ON e.id=s.branchExamId', 'LEFT')
            ->where($where)
            ->field('e.id as examId,e.examination,e.planId,s.id as subId,s.subject')
            ->select();

        if (!$data) {
            $this->ajaxReturn($response);
        }
        $res = array();
        foreach ($data as $k => $v) {
            if (!isset($res[$v['examId']])) {
                $res[$v['examId']] = array(
                    'examId' => $v['examId'],
                    'examination' => $v['examination'],
                    'planId' => $v['planId'],
                    'sub' => array()
                );
            }
            if (isset($v['subId'])) {
                $res[$v['examId']]['sub'][] = array(
                    'subId' => $v['subId'],
                    'subject' => $v['subject']
                );
            }

        }
        sort($res);
        $response['status'] = 1;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //成绩管理 //已测试
    public function scoreManage($planId)
    {
        $response = array(
            'status' => 0,
            'msg' => '',
            'data' => ''
        );
        $type = $_REQUEST['type'];
        if (isset($type)) {
            $subId = $_POST['subId'];
            $students = D('BranchStudent')
                ->where(array('planId' => $planId,'scId'=>$this->scId))
                ->field('id,userId,name,sex,preClass,preGrade,subScore')
                ->select();

            if ($type == 'record') {
                $maxPoint = (int)D('BranchSubject')->where(array('id' => $subId))->getField('maxPoint');
                foreach ($students as $k => $v) {
                    $subScore = json_decode($v['subScore'], true);
                    $score = array_key_exists($subId, $subScore) ? $subScore[$subId] : null;
                    $students[$k]['subScore'] = $score;
                }
                $response['status'] = 1;
                $response['maxPoint'] = $maxPoint;
                $response['data'] = $students;
                $this->ajaxReturn($response);
            } elseif ($type == 'save') {
                $subId = I('post.subId');
                D('BranchSubject')->where(array('id' => $subId))->save(array('maxPoint' => (int)$_POST['maxPoint']));
                // $score = json_decode($_POST['subScore'], true);//userId=>score
                $score = I('post.subScore');
                foreach ($students as $k => $v) {
                    $students[$k]['subScore'] = json_decode($students[$k]['subScore'], true);
                    $students[$k]['subScore'][$subId] = $score[$v['userId']];
                }

                $i = 0;
                $val = '';

                foreach ($students as $k => $v) {
                    if (!is_null($v['subScore'][$subId]))  //录入成绩人数
                        $i++;
                    $newScore = json_encode($v['subScore']);
                    $val .= '(' . "{$v['id']}" . ',' . "'{$newScore}'" . '),';
                }

                $data = array(
                    'recordNumber' => $i,
                    'lastRecordTime' => time()
                );
                $rs = D('BranchSubject')->where(array('id' => $subId))->data($data)->save();
                $val = rtrim($val, ',');
                $sql = "insert into mks_branch_student (id,subScore) 
            values {$val} on duplicate key update subScore=values(subScore)";
                $res = M()->execute($sql);
                if (!$rs || !$res) {
                    $response['msg'] = '保存失败';
                    $this->ajaxReturn($response);
                }
                $this->scoreCombine($planId);
                $response['status'] = 1;
                $response['msg'] = '保存成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'import') {
                $file = $_FILES;
                if(empty($file)){
                    $response['msg']='未检索到文件！';
                    $this->ajaxReturn($response);
                }
                $acc = new Accessory($file, $this->scId, 'excel');
                $rs = $acc->uploadExcel();
                $filename = $rs['path'][0];
                $import = new Import();
                $rs = $import->read($filename);
                $acc->del($filename);
                $data = array_splice($rs, 1);
                $map = array();
                foreach ($data as $k => $v) {
                    $map[$v[0] . $v[1] . $v[2]] = $v[3];
                }
                foreach ($students as $k => $v) {
                    $temp = $v['preGrade'] . $v['preClass'] . $v['name'];
                    $students[$k]['subScore'] = $map[$temp];
                }
                $response['status'] = 1;
                $response['data'] = $students;
                $this->ajaxReturn($response);
            } elseif ($type == 'download') {
                /*$file = new Accessory('', $this->scId, 'download');
                $filename = array('download/divideScore.xlsx');
                $aName = array('成绩录入表.xlsx');
                $file->download($filename, $aName, true);*/
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
                    ->setCellValue('A1', '年级')
                    ->setCellValue('B1', '班级')
                    ->setCellValue('C1', '姓名')
                    ->setCellValue('D1', '分数');

                foreach ($students as $k => $v) {
                    $num = $k + 2;
                    $objPHPExcel->setActiveSheetIndex(0)
                        //Excel的第A列，uid是你查出数组的键值，下面以此类推
                        ->setCellValue('A' . $num, $v['preGrade'])
                        ->setCellValue('B' . $num, $v['preClass'])
                        ->setCellValue('C' . $num, $v['name'])
                        ->setCellValue('D' . $num, '');
                }
                $objPHPExcel->getActiveSheet()->setTitle('录入成绩表');
                $objPHPExcel->setActiveSheetIndex(0);
                //header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . '录入成绩表模板' . '.xls"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
                exit;
            } else {
                $response['msg'] = '没权限进行操作';
                $this->ajaxReturn($response);
            }
        }

        $exId = $_POST['examId'];
        $field = 'id,subject,totalNumber,recordNumber,maxPoint';
        $subject = D('BranchSubject')->where(array('planId' => $planId, 'branchExamId' => $exId))
            ->field($field)
            ->select();
        if (!$subject) {
            $this->ajaxReturn($response);
        }
        foreach ($subject as $k => $v) {
            $subject[$k]['ratio'] = sprintf("%.2f", (int)$subject[$k]['recordNumber'] / (int)$subject[$k]['totalNumber']);
            $subject[$k]['notNumber'] = (int)$subject[$k]['totalNumber'] - (int)$subject[$k]['recordNumber'];
        }
        $response['status'] = 1;
        $response['data'] = $subject;
        $this->ajaxReturn($response);
    }

    //调用科类  //已测试
    private function callBranch($param)
    {

        $response['data'] = array();
        $where = array(
            'scId' => array('in', array($this->scId, -$param['planId'], 0))
            // 'scId' => $this->scId
        );

        $rs = D('ClassBranch')->where($where)->field('branchid as id,branch')->select();

        if (!$rs)
            $this->ajaxReturn($response);
        $response['data'] = $rs;
        $this->ajaxReturn($response);
    }

    //调用专业  //已测试
    private function callMajor($param)
    {
        $response['status'] = 0;
        $response['data'] = array();
        $where = array(
            'branch' => $param['branchId'],
            'scId' => array('in', array($this->scId, 0))
        );

        $rs = D('ClassMajor')->where($where)
            ->field('majorid as majorId,majorname as major')->select();
        foreach ($rs as $k => $v) {
            $res[$v['majorId']] = $v;
        }

        if (!$rs)
            $this->ajaxReturn($response);
        $where2 = array(
            'scId' => $this->scId,
            'planId' => $param['planId'],
            'branchId' => $param['branchId']
        );
        $temp = D('BranchWish')->where($where2)->field('major,majorId')->select();
        if ($temp) {
            foreach ($temp as $k => $v) {
                unset($res[$v['majorId']]);
            }
        }
        $response['status'] = 1;
        $response['data'] = array_values($res);
        $this->ajaxReturn($response);
    }

    //填报志愿设置 //已测试
    public function wishSetting($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];
        if (!empty($type)) {
            if ($type == 'addBranch') { //添加科类
                $branch = array(
                    'branch' => $_POST['branch'],
                    'scId' => -$planId, //为同步做区分
                    'createTime' => time()
                );
                $where = array(
                    'branch' => $_POST['branch'],
                    'scId' => array('in', array($this->scId, $this->scId + 30000 + $planId))
                );
                $rs = D('ClassBranch')->where($where)->find();
                if ($rs) {
                    $response['msg'] = '科类已存在，请直接调用';
                    $this->ajaxReturn($response);
                }
                $rs = D('ClassBranch')
                    ->data($branch)
                    ->add();
                if (!$rs) {
                    $response['msg'] = '添加失败';
                    $this->ajaxReturn($response);
                }
                $response['BranchId'] = $rs;
            } elseif ($type == 'addMajor') { //添加专业
                /*$rs = D('BranchWish')->where(array(
                    'major' => $_POST['major'],
                    'branch' => $_POST['branch'],
                    'planId' => $planId,
                    'scId' => $this->scId
                ))->find();
                if (!is_null($rs)) {
                    $response['msg'] = '专业已存在此科类内';
                    $this->ajaxReturn($response);
                }*/
                $major = array(
                    'majorname' => $_POST['major'],
                    'branch' => $_POST['branchId'],
                    'scId' => $this->scId,
                    'createTime' => time()
                );
                $rs = D('ClassMajor')
                    ->where(array(
                        'majorname' => $_POST['major'],
                        'branch' => $_POST['branchId'],
                        'scId' => $this->scId))
                    ->find();
                if (!is_null($rs)) {
                    $response['msg'] = '专业已存在，请直接调用';
                    $this->ajaxReturn($response);
                }
                $rs = D('ClassMajor')
                    ->data($major)
                    ->add();
                if (!$rs) {
                    $response['msg'] = '添加专业出错';
                    $this->ajaxReturn($response);
                }
                /*                $rs = D('BranchWish')->data(array(
                                    'branch' => $_POST['branch'],
                                    'major' => $_POST['major'],
                                    'majorId' => $rsId,
                                    'branchId' => $_POST['branchId'],
                                    'planId' => $planId,
                                    'scId' => $this->scId))->add();*/
                if ($rs) {
                    $response['wishId'] = $rs;
                    // $response['majorId'] = $rsId;
                    $response['name'] = $_REQUEST['major'];
                }
            } elseif ($type == 'delBranch') { //删除科类
                $where = array(
                    'branchId' => $_POST['branchId'],
                    'planId' => $planId,
                    'scId' => $this->scId
                );
                $rs = D('BranchWish')->where($where)->find();
                if (!$rs) {
                    $response['msg'] = '此科类未设置专业,尚未保存';
                    $this->ajaxReturn($response);
                }
                $rs = D('BranchWish')->where($where)->delete();
            } elseif ($type == 'delMajor') { //删除专业
                $wishId = $_POST['wishId'];
                $rs = D('BranchWish')->where(array('id' => $wishId))->delete();
                D('BranchStudent')->where(array('scId' => $this->scId, 'planId' => $planId, 'wishId' => $wishId))->save(array(
                    'wishId' => '',
                    'branch' => '',
                    'major' => ''
                ));
            } elseif ($type == 'majorFromCall') {
                $rs = D('BranchWish')->where(array(
                    'major' => $_POST['major'],
                    'majorId' => $_POST['majorId'],
                    'branch' => $_POST['branch'],
                    'branchId' => $_POST['branchId'],
                    'planId' => $planId,
                    'scId' => $this->scId
                ))->find();
                if (!is_null($rs)) {
                    $response['msg'] = '专业已存在此科类内';
                    $this->ajaxReturn($response);
                }
                $rs = D('BranchWish')->data(array(
                    'branch' => $_POST['branch'],
                    'major' => $_POST['major'],
                    'majorId' => $_POST['majorId'],
                    'branchId' => $_POST['branchId'],
                    'planId' => $planId,
                    'scId' => $this->scId))->add();
                if (!$rs) {
                    $response = '调用添加失败';
                    $this->ajaxReturn($response);
                }
                $response['wishId'] = $rs;
                $response['majorId'] = $_POST['majorId'];
                $response['name'] = $_POST['major'];
            } else {
                $response['msg'] = '没权限进行操作';
                $this->ajaxReturn($response);
            }
            if ($rs) {
                $response['status'] = 1;
                $response['msg'] = '操作成功';
                $status = D('BranchProcess')
                    ->where(array('planId' => $planId, 'scId' => $this->scId, 'step' => 3))
                    ->getField('status');

                if ($status != 1) {
                    $process = array(4 => 1);
                } else {
                    $process = array(4 => 1, 5 => 2);
                }
                $this->changeProcess($process, $planId);
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '操作失败';
                $this->ajaxReturn($response);
            }
        }

        $wish = D('BranchWish')
            ->where(array('planId' => $planId, 'scId' => $this->scId))
            ->field('id as wishId,branch,branchId,major,majorId')->select();
        if ($wish) {
            $data = array();
            foreach ($wish as &$v) {
                if (!array_key_exists($v['branch'], $data))
                    $data[$v['branch']] = array(
                        'branch' => $v['branch'],
                        'branchId' => $v['branchId'],
                        'major' => array()
                    );
                $data[$v['branch']]['major'][] = array(
                    'wishId' => $v['wishId'],
                    'major' => $v['major'],
                    'majorId' => $v['majorId']
                );
            }
            sort($data);
            $response['data'] = $data;
            $response['status'] = 1;
        }
        $this->ajaxReturn($response);
    }

    //成绩统计设置
    //得到科类/专业 //已测试
    private function getWish($param)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $planId = $param['planId'];
        $wish = array();
        $data = D('BranchWish')->where(array('planId' => $planId))
            ->field('id as wishId,branch,branchId,major,majorId')
            ->select();

        if (!$data)
            $this->ajaxReturn($response);
        foreach ($data as &$v) {
            if (!array_key_exists($v['branchId'], $wish))
                $wish[$v['branchId']] = array(
                    'name' => $v['branch'],
                    'branchId' => $v['branchId'],
                    'majors' => array(),
                );
            $wish[$v['branchId']]['majors'] [] = array(
                'name' => $v['major'],
                'parentId' => $v['branchId'],
                'majorId' => $v['majorId'],
                'wishId' => $v['wishId'],
            );
        }
        sort($wish);
        $response['status'] = 1;
        $response['data'] = $wish;
        $this->ajaxReturn($response);
    }

    //得到待选考试及科目  //已测试
    private function getSubject($param)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $planId = $param['planId'];
        $wishId = $param['wishId'];
        if (!empty($wishId)) {
            $where1 = array(
                'planId' => $planId,
                'id' => $wishId
            );
            $data = D('BranchWish')->where($where1)
                ->field('id as wishId,ratio')
                ->select();
            foreach ($data as $k => $v) {
                $temp = json_decode($v['ratio'], true);
                foreach ($temp as $k1 => $v1) {
                    $subIds[] = $k1;
                }
            }
        }

        $subject = array();
        $data = D('BranchExam e')
            ->join('mks_branch_subject s ON e.id=s.branchExamId', 'LEFT')
            ->where(array('s.planId' => $planId))
            ->field('e.id as examId,e.examination,s.id as subId,s.subject')
            ->select();
        if (!$data)
            $this->ajaxReturn($response);
        foreach ($data as &$v) {
            if (!array_key_exists($v['examId'], $subject))
                $subject[$v['examId']] = array(
                    'examId' => $v['examId'],
                    'name' => $v['examination'],
                    'subs' => array(),
                    'examRatio' => '',
                    'checked' => in_array($v['subId'], $subIds) ? true : false,
                );
            $subject[$v['examId']]['subs'][] = array(
                'subId' => $v['subId'],
                'parentId' => $v['examId'],
                'name' => $v['subject'],
                'ratio' => '',
                'checked' => in_array($v['subId'], $subIds) ? true : false
            );
        }
        $response['status'] = 1;
        sort($subject);
        $response['data'] = $subject;
        $this->ajaxReturn($response);
    }

    //已测试
    //成绩统计设置 //已测试
    public function scoreSetting($planId)
    {
        $response = array(
            'status' => 0,
            'msg' => '',
        );
        $type = $_POST['type'];

        if (!empty($type)) {
            if ($type == 'save') {//添加分数比重
                $ratio = $_POST['ratio'];
                foreach ($ratio as $k => $v) {
                    $ratio[$k]['ratio'] = $v['ratio'] / 100;
                    $ratio[$k]['examRatio'] = $v['examRatio'];
                }
                $ratio = json_encode($ratio);
                //$subRatio=json_encode($_POST['subjectRatio']);
                $ratio = array(
                    'ratio' => $ratio,
                );
                $map = array(
                    'id' => array('in', $_POST['wishIds'])
                );
                $rs = D('BranchWish')->where($map)->data($ratio)->save();
                if ($rs) {
                    $response['status'] = 1;
                    $response['msg'] = '保存成功';
                    $process = array(
                        5 => 1, 6 => 3, 8 => 3, 9 => 3, 10 => 3, 11 => 3, 12 => 2
                    );
                    $this->changeProcess($process, $planId);
                    $this->scoreCombine($planId);
                    $this->ajaxReturn($response);
                }
            } else {
                $response['msg'] = '操作失败';
                $this->ajaxReturn($response);
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
                $this->ajaxReturn($response);
            }
        }
        /*        $response['wish'] = $this->getWish($planId) ? $this->getWish($planId) : array();
                $response['subject'] = $this->getSubject($planId) ? $this->getSubject($planId) : array();*/
        $wishId = $_POST['wishId'];
        $where1 = array(
            'planId' => $planId,
            'id' => $wishId
        );
        $data = D('BranchWish')->where($where1)
            ->field('id as wishId,ratio')
            ->select();

        if (!$data) {
            $this->ajaxReturn($response);
        }
        $map = array();
        $subIds = array();
        foreach ($data as $k => $v) {
            $temp = json_decode($v['ratio'], true);
            foreach ($temp as $k1 => $v1) {
                $temp[$k1]['majorId'] = $k1;
                $subIds[] = $k1;
            }
            $map[$v['wishId']] = $temp;
        }
        $where = array(
            's.planId' => $planId,
            //'s.id' => array('in', $subIds)
        );
        $exam = D('BranchExam e')
            ->join('mks_branch_subject s ON e.id=s.branchExamId', 'LEFT')
            ->where($where)
            ->field('e.id as examId,e.examination,s.id as subId,s.subject')
            ->select();

        $rs = array();

        foreach ($exam as &$v) {
            if (in_array($v['subId'], $subIds)) {
                if (!array_key_exists($v['examId'], $rs))
                    $rs[$v['examId']] = array(
                        'examId' => $v['examId'],
                        'examRatio' => $map[$wishId][$v['subId']]['examRatio'],
                        'name' => $v['examination'],
                        'subs' => array(),
                        'checked' => true
                    );
                $rs[$v['examId']]['subs'][] = array(
                    'subId' => $v['subId'],
                    'parentId' => $v['examId'],
                    'ratio' => $map[$wishId][$v['subId']]['ratio'] * 100,
                    'name' => $v['subject'],
                    'checked' => true
                );
            }/*else{
                if (!array_key_exists($v['examId'], $rs))
                    $rs[$v['examId']] = array(
                        'examId' => $v['examId'],
                        'examRatio' =>0,
                        'name' => $v['examination'],
                        'subs' => array(),
                        'checked'=>false
                    );
                $rs[$v['examId']]['subs'][] = array(
                    'subId' => $v['subId'],
                    'parentId' => $v['examId'],
                    'ratio' => 0,
                    'name' => $v['subject'],
                    'checked'=>false
                );
            }*/
        }
        sort($rs);
        $response['status'] = 1;
        $response['data'] = $rs;
        $this->ajaxReturn($response);
    }

    //合成成绩  //已测试
    public function scoreCombine($planId)
    {//合成成绩
        //得到比重规则
        $ratios = D('BranchWish')->where(array('planId' => $planId))
            ->field('id,ratio')->select();//subId=>ratio,examRatio
        if (!$ratios)
            return false;
        $ratio = array();
        foreach ($ratios as &$temp) {
            $ratio[$temp['id']] = json_decode($temp['ratio'], true);//wishId=>subId=>ratio,examRatio
        }

        //得到成绩
        $students = D('BranchStudent')
            ->where(array('planId' => $planId,'scId'=>$this->scId))
            ->field('id,userId,subScore')
            ->select();

        //得到总分
        $data = D('BranchSubject')->where(array('scId' => $this->scId, 'planId' => $planId))->field('id,maxPoint')->select();
        foreach ($data as $k => $v) {
            $map[$v['id']] = $v['maxPoint'];
        }
        //得到合成成绩

        foreach ($ratio as $w => $r) {//一个专业的比重
            $rankScore = array();
            foreach ($students as $k => $v) {//v每个学生
                $subScore = json_decode($v['subScore'], true);
                $score = json_decode($v['comScore'], true);

                foreach ($r as $sId => $rs) {//rs包含sub,exam的比重

                    if (!array_key_exists($w, $score))
                        $score[$w] = array(
                            'score' => 0,
                        );
                    if (isset($map[$sId])) {
                        $score[$w]['score'] += round($rs['examRatio'] / $map[$sId], 1) * (int)$subScore[$sId] * $rs['ratio'];
                    }
                }
                $rankScore[$v['id']] = $score[$w]['score'];
                $students[$k]['comScore'] = json_encode($score);
            }

            //得到排名
            arsort($rankScore);
            $rankScore = array_keys($rankScore);
            foreach ($students as $k => $v) {
                $rank = json_decode($v['comRank'], true);
                $perRank = array_search($v['id'], $rankScore) + 1;

                $rank[$w] = $perRank;
                $students[$k]['comRank'] = json_encode($rank);
            }
        }

        $cnt = count($students);
        $val = '';
        for ($i = 0; $i < $cnt; $i++) {
            $val .= '(' . $students[$i]['id'] . ',' . "'{$students[$i]['comScore']}'" . ','
                . "'{$students[$i]['comRank']}'" . '),';
        }
        $val = rtrim($val, ',');
        $sql = "insert into mks_branch_student (id,comScore,comRank) 
            values {$val} on duplicate key update comScore=values(comScore),comRank=values(comRank)";

        $rs = M()->execute($sql);
        return $rs;
    }

    //合成成绩统计    //已测试
    public function scoreStatistics($planId, $sort)
    {//$sort 1成绩汇总 2 成绩明细
        $response = array(
            'status' => 0,
            'data' => ''
        );
        $rs = array();

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        if ($sort == 1) {
            $field = 'userId,preGrade,preClass,name,comScore,comRank';
            $wishes = D('BranchWish e')
                ->where(array('planId' => $planId, 'scId' => $this->scId))
                ->field('id,branch,major')
                ->select();
            $wish = array();
            foreach ($wishes as $k => $v) {
                $wish[] = array(
                    'wishId' => $v['id'],
                    'wishName' => $v['branch'] . '/' . $v['major']
                );
            }
            $rs['wish'] = $wish;
            $where = array(
                'planId' => $planId,
                'scId' => $this->scId
            );
            $key = $_POST['key']; //搜索
            if (!empty($key)) {
                $where['preGrade|preClass|name'] = array('like', "%{$key}%");
            }
            $order = $_POST['order']; //排序
            $according = $_POST['according'];
            if (!empty($order) && !empty($according)) {
                $order = $according . ' ' . stristr($order, 'ending', true);
            } else {
                $order = 'preGrade desc';
            }

            $data = D('BranchStudent')->where($where)->order($order)
                ->field($field)->limit($limit_page)->select();
            $total = (int)D('BranchStudent')->where($where)->count();
            if (!$data) {
                $this->ajaxReturn($data);
            }

            foreach ($data as $k => $v) {
                $comScore = empty($v['comScore']) ? array() : json_decode($v['comScore'], true);
                $comRank = empty($v['comScore']) ? array() : json_decode($v['comRank'], true);
                $temp = array();
                foreach ($wish as $k1 => $v1) {
                    $temp[$v1['wishId']] = array(
                        'score' => $comScore[$v1['wishId']]['score'],
                        'rank' => $comRank[$v1['wishId']]
                    );
                }
                unset($data[$k]['comScore']);
                unset($data[$k]['comRank']);
                $data[$k] = $data[$k] + $temp;
            }
            $rs['student'] = $data;

            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['status'] = 1;
            $response['data'] = $rs;
            $this->ajaxReturn($response);
        } elseif ($sort == 2) {
            $field = 'userId,preGrade,preClass,name,subScore,comScore,score';
            $exams = D('BranchExam e')
                ->join('mks_branch_subject s ON e.id=s.branchExamId', 'LEFT')
                ->where(array('s.planId' => $planId))
                ->field('e.id as examId,e.examination,s.id as subId,s.subject')
                ->select();

            $exam = array();
            foreach ($exams as &$v) {
                if (!array_key_exists($v['examId'], $exam))
                    $exam[$v['examId']] = array(
                        'examId' => $v['examId'],
                        'name' => $v['examination'],
                        'subs' => array()
                    );
                $exam[$v['examId']]['subs'][] = array(
                    'subId' => $v['subId'],
                    'name' => $v['subject']
                );
            }
            sort($exam);

            $rs['exam'] = $exam;

            $wishId = $_POST['wishId'];
            $ratio = D('BranchWish')->where(array('id' => $wishId))->field('ratio')->find();
            $ratio = json_decode($ratio['ratio'], true);

            $where = array(
                // 'wishId' => $wishId,
                'planId' => $planId,
                'scId'=> $this->scId,
            );
            $key = $_POST['key']; //搜索
            if (!empty($key)) {
                $where['preGrade|preClass|name'] = array('like', "%{$key}%");
            }
            $order = $_POST['order']; //排序
            $according = $_POST['according'];
            if (!empty($order) && !empty($according)) {
                $order = $according . ' ' . stristr($order, 'ending', true);
            } else {
                $order = 'preGrade desc';
            }

            $data = D('BranchStudent')->where($where)->order($order)
                ->field($field)->limit($limit_page)->select();

            $total = (int)D('BranchStudent')->where($where)->count();
            if (!$data) {
                $this->ajaxReturn($response);
            }

            foreach ($data as $k => $v) {
                $comScore = json_decode($v['comScore'], true);
                $subScore = json_decode($v['subScore'], true);
                $temp = array();
                foreach ($subScore as $k1 => $v1) {
                    $temp[$k1] = array(
                        'score' => $v1 * $ratio[$k1]['ratio'],
                    );
                }
                $data[$k]['score'] = $comScore[$wishId]['score'];
                unset($data[$k]['subScore']);
                $data[$k] = $data[$k] + $temp;
            }
            $rs['student'] = $data;
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['status'] = 1;
            $response['data'] = $rs;
            $this->ajaxReturn($response);
        }

    }

    //得到一个方案对应的科类和专业
    private function getAllWish($planId)
    {
        $data = D('BranchWish')
            ->where(array('planId' => $planId))
            ->field('id,branch,branchId,major,majorId')
            ->select();
        $wish = array();
        if (!$data)
            return $wish;
        foreach ($data as &$v) {
            $wish[$v['branch']][] = array(
                'id' => $v['id'],
                'branchId' => $v['branchId'],
                'major' => $v['major'],
                'majorId' => $v['majorId']
            );
        }
        return $wish;
    }

    //学生志愿调整    //已测试
    public function wishAdjust($planId)
    {
        $response = array(
            'status' => 0,
            'msg' => '',
        );
        $type = $_REQUEST['type'];
        if (!empty($type)) {
            if ($type == 'save') {
                if ($this->roleId != $this::$adminRoleId) {
                    $teaChange = D('BranchPlan')->where(array('id' => $planId, 'scId' => $this->scId))
                        ->getField('teaChange');
                    if ($teaChange != 1) {
                        $response['msg'] = '没有权限进行修改';
                        $this->ajaxReturn($response);
                    }
                }
                $stus = D('BranchStudent')->where(array('planId' => $planId,'scId'=>$this->scId))->field('id,comScore,comRank')->select();
                $rank = array();
                $score = array();
                foreach ($stus as $k => $v) {
                    $score[$v['id']] = json_decode($v['comScore'], true);
                    $rank[$v['id']] = json_decode($v['comRank'], true);
                }

                $val = '';
                $changeId = $this->uid;
                $wish = I('post.wishes');
                //$wish = json_decode($wish, true);
                $cnt = count($wish);
                for ($i = 0; $i < $cnt; $i++) {
                    $val .= '(' . $wish[$i]['sId'] . ',' . $wish[$i]['wishId'] .
                        ',' . "'{$wish[$i]['branch']}'" . ',' . "'{$wish[$i]['major']}'" .
                        ',' . $score[$wish[$i]['sId']][$wish[$i]['wishId']]['score']
                        . ',' . $rank[$wish[$i]['sId']][$wish[$i]['wishId']] . ',' . $changeId .
                        '),';
                }
                $val = rtrim($val, ',');
                $sql = "insert into mks_branch_student (id,wishId,branch,major,score,rank) 
            values {$val} on duplicate key update wishId=values(wishId),branch=values(branch),major=values(major)
            ,score=values(score),rank=values(rank),changeId=values(changeId)";
                $rs = M()->execute($sql);
                if (!$rs) {
                    $response['msg'] = '保存失败';
                    $this->ajaxReturn($response);
                }
                //修改志愿填写人数
                $notFill = D('BranchPlan')->where(array('id' => $planId))->getField('notFill');
                $notFill = (int)$notFill - $cnt;
                D('BranchPlan')->where(array('id' => $planId))->data(array('notFill' => $notFill))->save();
                $response['msg'] = '保存成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($type == 'edit') {
                if ($this->roleId != $this::$adminRoleId) {
                    $teaChange = D('BranchPlan')->where(array('id' => $planId, 'scId' => $this->scId))
                        ->getField('teaChange');
                    if ($teaChange != 1) {
                        $response['msg'] = '没有权限进行修改';
                        $this->ajaxReturn($response);
                    }
                }
                $id = $_POST['sId'];
                $wish = $_POST['wish'];
                $find=D('BranchWish')->where(array('id'=>$wish['wishId']))->find();
                if(empty($find['ratio'])){
                    $response['msg'] = '该科类无分班的成绩依据，请前往设置';
                    $this->ajaxReturn($response);
                }
                $temp = D('BranchStudent')->where(array('id' => $id))->field('wishId,comScore,comRank')->find();
                $score = json_decode($temp['comScore'], true);
                $score = $score[$wish['wishId']]['score'];
                $rank = json_decode($temp['comRank'], true);
                $rank = $rank[$wish['wishId']];
                $rs = D('BranchStudent')->where(array('id' => $id))
                    ->data(array(
                        'wishId' => $wish['wishId'],
                        'branch' => $wish['branch'],
                        'major' => $wish['major'],
                        'score' => $score,
                        'rank' => $rank,
                        'changeId' => $this->uid
                    ))->save();
                if (!$rs) {
                    $response['msg'] = '编辑错误';
                    $this->ajaxReturn($response);
                }
                if (empty($temp['wishId'])) {
                    //修改志愿填写人数
                    $notFill = D('BranchPlan')->where(array('id' => $planId))->getField('notFill');
                    $notFill = (int)$notFill - 1;
                    D('BranchPlan')->where(array('id' => $planId))->data(array('notFill' => $notFill))->save();
                }
                $response['status'] = 1;
                $response['msg'] = '编辑成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'download') { //下载模板
                $data = D('BranchStudent')
                    ->where(array('planId' => $planId, 'scId' => $this->scId))
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
                    ->setCellValue('A1', '年级')
                    ->setCellValue('B1', '班级')
                    ->setCellValue('C1', '姓名')
                    ->setCellValue('D1', '科类')
                    ->setCellValue('E1', '专业');
                foreach ($data as $k => $v) {
                    $num = $k + 2;
                    $objPHPExcel->setActiveSheetIndex(0)
                        //Excel的第A列，uid是你查出数组的键值，下面以此类推
                        ->setCellValue('A' . $num, $v['preGrade'])
                        ->setCellValue('B' . $num, $v['preClass'])
                        ->setCellValue('C' . $num, $v['name'])
                        ->setCellValue('D' . $num, $v['branch'])
                        ->setCellValue('E' . $num, $v['major']);
                }
                $objPHPExcel->getActiveSheet()->setTitle('学生志愿调整表');
                $objPHPExcel->setActiveSheetIndex(0);
                //  header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . '学生志愿调整表' . '.xls"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
                exit;
            } elseif ($type == 'import') {//批量导入
                if ($this->roleId != $this::$adminRoleId) {
                    $teaChange = D('BranchPlan')->where(array('id' => $planId, 'scId' => $this->scId))
                        ->getField('teaChange');
                    if ($teaChange != 1) {
                        $response['msg'] = '没有权限进行修改';
                        $this->ajaxReturn($response);
                    }
                }

                $file = $_FILES;
                if(empty($file)){
                    $response['msg']='未检索到文件！';
                    $this->ajaxReturn($response);
                }
                $acc = new Accessory($file, $this->scId, 'excel');
                $rs = $acc->uploadExcel();
                $filename = $rs['path'][0];
                $import = new Import();
                $rs = $import->read($filename);
                $acc->del($filename);

                $stus = D('BranchStudent')->where(array('planId' => $planId, 'scId' => $this->scId))
                    ->select();
                $map_stu = array();
                foreach ($stus as $k => $v) {
                    $map_stu[$v['preGrade'] . $v['preClass'] . $v['name']] = $v['id'];
                }

                $wishes = D('BranchWish')->where(array('planId' => $planId, 'scId' => $this->scId))
                    ->select();
                $map_wish = array();
                foreach ($wishes as $k => $v) {
                    $map_wish[$v['branch'] . $v['major']] = $v['id'];
                }

                $data = array_splice($rs, 1);
                $students = array();
                foreach ($data as $k => $v) {
                    $wishId = $map_wish[$v[3] . $v[4]];
                    $id = $map_stu[$v[0] . $v[1] . $v[2]];
                    if (!empty($id) && !empty($wishId)) {
                        $students[] = array(
                            'id' => $id,
                            'wishId' => $wishId,
                            'preGrade' => $v[0],
                            'preClass' => $v[1],
                            'name' => $v[2],
                            'branch' => $v[3],
                            'major' => $v[4]
                        );
                    }
                }

                $rank = array();
                $score = array();
                foreach ($stus as $k => $v) {
                    $score[$v['id']] = json_decode($v['comScore'], true);
                    $rank[$v['id']] = json_decode($v['comRank'], true);
                }

                $val = '';
                $changeId = $this->uid;
                $wish = $students;
                //$wish = json_decode($wish, true);
                $cnt = count($wish);
                for ($i = 0; $i < $cnt; $i++) {
                    if(!isset($score[$wish[$i]['id']][$wish[$i]['wishId']]['score'])){
                        $response['msg']='成绩统计设置出错';
                        $this->ajaxReturn($response);
                    }
                    $val .= '(' . $wish[$i]['id'] . ',' . $wish[$i]['wishId'] .
                        ',' . "'{$wish[$i]['branch']}'" . ',' . "'{$wish[$i]['major']}'" .
                        ',' . $score[$wish[$i]['id']][$wish[$i]['wishId']]['score']
                        . ',' . $rank[$wish[$i]['id']][$wish[$i]['wishId']] . ',' . $changeId .
                        '),';
                }

                $val = rtrim($val, ',');
                $sql = "insert into mks_branch_student (id,wishId,branch,major,score,rank,changeId) 
            values {$val} on duplicate key update wishId=values(wishId),branch=values(branch),major=values(major)
            ,score=values(score),rank=values(rank),changeId=values(changeId)";
                $rs = M()->execute($sql);
                if (!$rs) {
                    $response['msg'] = '更新志愿失败,请查看志愿是否填写正确';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                $response['data'] = $students;
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '没权限进行操作';
                $this->ajaxReturn($response);
            }
        }

        $where = array(
            'planId' => $planId,
            'scId' => $this->scId
        );

        $key = $_POST['key'];
        if (!empty($key)) {
            $where['preGrade|preClass|name|branch|major'] = array('like', "%{$key}%");
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $order = $_POST['order']; //排序
        $according = $_POST['according'];
        if (!empty($order) && !empty($according)) {
            $order = $according . ' ' . stristr($order, 'ending', true);
        } else {
            $order = 'preGrade desc';
        }

        $students = D('BranchStudent')
            ->where($where)
            ->order($order)
            ->field('id as sId,name,sex,wishId,branch,major,preGrade,preClass')
            ->limit($limit_page)
            ->select();
        $total = (int)D('BranchStudent')
            ->where($where)->count();
        if (!$students)
            $this->ajaxReturn($students);
        $response['total'] = (int)$total;
        $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        $response['data'] = $students;
        $response['status'] = 1;
        $this->ajaxReturn($response);
    }

    //学生志愿统计    //已测试
    public function wishStatistics($planId, $sort)
    { //1志愿填报情况 2志愿分布情况
        $response = array(
            'status' => 0,
            'data' => ''
        );
        if ($sort == 1) {
            $students = D('BranchStudent')->where(array(
                'planId' => $planId,
                'scId' => $this->scId,
            ))->select();

            $wish = D('BranchWish')->where(array(
                'planId' => $planId,
                'scId' => $this->scId,
            ))->select();

            if (!$students)
                $this->ajaxReturn($response);
            //学生填报志愿进度图 wishStu
            //各科类学生志愿分布图 wishStuBra
            //各专业学生志愿分布图  wishStuMaj
            //各班志愿填报进步统计图   wishClass
            //各班志愿分布图-科类    wishClassBra
            //各班志愿分布图-专业    wishClassMaj
            $wishStu = array(
                'not' => 0,
                'al' => 0
            );
            $wishStuBra = array();
            $wishStuMaj = array();
            $wishClass = array();
            $bra = array();
            $maj = array();
            $class = array();
            foreach ($wish as $K => $v) {
                if (!isset($bra[$v['branch']]))
                    $bra[$v['branch']] = array(
                        'name' => $v['branch'],
                        'class' => array()
                    );
                if (!isset($maj[$v['major']]))
                    $maj[$v['major']] = array(
                        'name' => $v['major'],
                        'class' => array()
                    );
                if (!isset($wishStuBra[$v['branch']]))
                    $wishStuBra[$v['branch']] = 0;
                if (!isset($wishStuMaj[$v['major']]))
                    $wishStuMaj[$v['major']] = 0;

            }
            foreach ($students as $k => $v) {
                $className = $v['preGrade'] . $v['preClass'] . '班';
                if (!in_array($className, $class))
                    $class[] = $className;
                if (!isset($wishClass[$className]))
                    $wishClass[$className] = array(
                        'name' => $className,
                        'not' => 0,
                        'alr' => 0,
                    );

                $wishStu['al'] += 1;
                if (!empty($v['wishId'])) {
                    if (!isset($bra[$v['branch']]['class'][$className])) {
                        $bra[$v['branch']]['class'][$className] = 0;
                    }
                    if (!isset($maj[$v['major']]['class'][$className])) {
                        $maj[$v['major']]['class'][$className] = 0;
                    }
                    $wishStuBra[$v['branch']] += 1;
                    $wishStuMaj[$v['major']] += 1;
                    $wishClass[$className]['alr'] += 1;
                    $bra[$v['branch']]['class'][$className] += 1;
                    $maj[$v['major']]['class'][$className] += 1;
                } else {
                    $wishStu['not'] += 1;
                    $wishClass[$className]['not'] += 1;
                }


            }
            foreach ($wishClass as $k => $v) {
                $sum = $v['not'] + $v['alr'];
                $wishClass[$k]['alr'] = round($v['alr'] / $sum, 2) * 100;
                $wishClass[$k]['not'] = round($v['not'] / $sum, 2) * 100;
            }
            $wishStu['al'] = (int)$wishStu['al'] - (int)$wishStu['not'];
            $response['wishStu'] = $wishStu;
            $response['wishStuBra'] = $wishStuBra;
            $response['wishStuMaj'] = $wishStuMaj;
            sort($wishClass);
            $response['wishClass'] = $wishClass;

            sort($bra);
            $response['wishClassBra'] = $bra;

            sort($maj);
            $response['wishClassMaj'] = $maj;
            $response['class'] = $class;
            $response['status'] = 1;
            $this->ajaxReturn($response);
        } elseif ($sort == 2) {
            $genre = $_POST['genre'];
            $where = array(
                'planId' => $planId,
                'wishId' => array('gt', 0),
                'scId' => $this->scId
            );
            $students = D('BranchStudent')->where($where)
                ->field('wishId,branch,major,preGrade,preClass,rank')->select();
            if (!$students)
                $this->ajaxReturn($response);
            $segment = $_POST['segment'];
            $seg = array();
            foreach ($segment as $k => $v) {
                $seg[] = array('name' => '前' . $v . '名',
                    'id' => $v);
            }

            $situation = array();
            $cnt = count($segment);
            if ($genre == 'class') {//各班填报志愿成分布情况统计
                foreach ($students as &$v) {
                    if (!isset($situation[$v['preGrade'] . $v['preClass']])) {
                        $situation[$v['preGrade'] . $v['preClass']] = array(
                            'grade' => $v['preGrade'],
                            'class' => $v['preClass'],
                            'branch' => $v['branch'],
                            'major' => $v['major']
                        );
                    }
                    for ($i = 0; $i < $cnt; $i++) {
                        $index = $segment[$i];
                        if (!isset($situation[$v['preGrade'] . $v['preClass']][$index]))
                            $situation[$v['preGrade'] . $v['preClass']][$index] = 0;
                        if ((int)$v['rank'] <= (int)$segment[$i]) {
                            $situation[$v['preGrade'] . $v['preClass']][$index] += 1;
                        }
                    }
                }
            } elseif ($genre == 'branch') {//各科类填报志愿成绩分布情况统计
                foreach ($students as &$v) {
                    if (!isset($situation[$v['wishId']])) {
                        $situation[$v['wishId']] = array(
                            'branch' => $v['branch'],
                            'major' => $v['major']
                        );
                    }
                    for ($i = 0; $i < $cnt; $i++) {
                        $index = $segment[$i];
                        if (!isset($situation[$v['wishId']][$index]))
                            $situation[$v['wishId']][$index] = 0;
                        if ($v['rank'] <= $segment[$i]) {
                            $situation[$v['wishId']][$index] += 1;
                        }
                    }
                }
            }
            sort($situation);
            $response['seg'] = $seg;
            $response['data'] = $situation;
            $response['status'] = 1;
            $this->ajaxReturn($response);
        }
        $this->ajaxReturn($response);
    }

    //未报志愿学生名单  //已测试
    public function wishNot($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array(),
        );
        $where = array(
            'planId' => $planId,
            'scId' => $this->scId,
            'wishId' => array('EXP', 'is NULL')
        );

        $download = $_REQUEST['download'];
        if ($download == 'ensure') {
            $data = D('BranchStudent')
                ->where($where)
                ->field('id,userId,preClass,preGrade,name,sex')
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
                ->setCellValue('A1', '年级')
                ->setCellValue('B1', '班级')
                ->setCellValue('C1', '姓名')
                ->setCellValue('D1', '性别');
            foreach ($data as $k => $v) {

                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['preGrade'])
                    ->setCellValue('B' . $num, $v['preClass'])
                    ->setCellValue('C' . $num, $v['name'])
                    ->setCellValue('D' . $num, $v['sex']);
            }
            $objPHPExcel->getActiveSheet()->setTitle('未填报志愿学生名单');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '未填报志愿学生名单' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        $key = $_POST['key'];
        if (!empty($key)) {
            $where['preGrade|preClass|name|sex'] = array('like', "%{$key}%");
        }
        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        $order = $_POST['order']; //排序
        $according = $_POST['according'];
        if (!empty($order) && !empty($according)) {
            $order = $according . ' ' . stristr($order, 'ending', true);
        } else {
            $order = 'preGrade desc';
        }


        $students = D('BranchStudent')
            ->where($where)
            ->field('id,userId,preClass,preGrade,name,sex')
            ->order($order)
            ->limit($limit_page)
            ->select();

        if (!$students)
            $this->ajaxReturn($response);
        $total = (int)D('BranchStudent')
            ->where($where)
            ->count();
        $response['total'] = $total;
        $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        $response['status'] = 1;
        $response['data'] = $students;
        $this->ajaxReturn($response);
    }

    //志愿确认名单    //已测试
    public function wishConfirm($planId)
    {
        $response = array(
            'status' => 0,
            'data' => ''
        );
        $where = array(
            's.planId' => $planId,
            's.scId' => $this->scId
        );
        $students = D('BranchStudent s')
            ->where($where)
            ->field('id,userId,wishId,preSerialNumber,preClass,preGrade,name,confirm,
            branch,major')
            ->order('s.preClass asc')
            ->select();
        if (!$students)
            $this->ajaxReturn($students);
        $rs = array();
        foreach ($students as $k => $v) {
            if (!isset($rs[$v['preGrade'] . $v['preClass']])) {
                $rs[$v['preGrade'] . $v['preClass']] = array(
                    'name' => $v['preGrade'] . $v['preClass'] . '班',
                    'not' => 0,
                    'stu' => array()
                );
            }
            $rs[$v['preGrade'] . $v['preClass']]['stu'][] =
                array(
                    'serialNumber' => $v['preSerialNumber'],
                    'name' => $v['name'],
                    'wish' => $v['branch'] . '/' . $v['major'],
                    'confirm' => $v['confirm'] ? '已确认' : '未确认'
                );
            if (empty($v['wishId']))
                $rs[$v['preGrade'] . $v['preClass']]['not'] += 1;

        }
        sort($rs);
        $response['status'] = 1;
        $response['data'] = $rs;
        $this->ajaxReturn($response);
    }

    //得到各年级各科类各专业的人数
    private function getAllStu($planId, $gradeId)
    {
        $where = array(
            'planId' => $planId,
            'gradeId' => $gradeId,
            'scId'=>$this->scId,
            'wishId' => array('EXP', 'is not NULL')
        );
        $students = D('BranchStudent')->where($where)->select();
        $data = array();

        foreach ($students as &$v) {
            if (!isset($data[$v['branch']]['major'][$v['major']]))
                $data[$v['branch']]['major'][$v['major']] = 0;
            if (!isset($data[$v['branch']]['total']))
                $data[$v['branch']]['total'] = 0;
            $data[$v['branch']]['major'][$v['major']] += 1;
            $data[$v['branch']]['total'] += 1;
        }

        $rs = D('BranchStudent')->where(array(
            'planId' => $planId,
            'gradeId' => $gradeId,
            'scId' => $this->scId,
            'wishId' => array('EXP', 'is NULL')
        ))->count();

        if (is_bool($rs) || !$students)
            return array();
        $response['stu'] = $data;
        $response['not'] = $rs;

        return $response;
    }

    //(拟分班设置)得到年级
    private function getGrade($planId)
    {
        $grade = D('BranchStudent')->where(array('planId' => $planId,'scId'=>$this->scId))
            ->group('gradeId')->field('gradeId')->select();
        $gradeId = array_map(function ($v) {
            return $v['gradeId'];
        }, $grade);

        $grade = D('Grade')->where(
            array('scId' => $this->scId,
                'gradeid' => array('in', $gradeId)
            ))
            ->field('gradeid as id,name,znName')
            ->select();
        $response = array(
            'status' => 0,
            'data' => array()
        );
        if ($grade) {
            $response['status'] = 1;
            $response['data'] = $grade;
        }
        $this->ajaxReturn($response);
    }

    //(拟分班设置)得到班级水平
    private function getLevel()
    {
        $level = D('ClassLevel')->where(array('scId' => $this->scId))->select();
        $response = array();
        if ($level) {
            foreach ($level as $k => $v) {
                $response[] = array(
                    'levelId' => $v['levelid'],
                    'level' => $v['levelname']
                );
            }
        }
        $this->ajaxReturn($response);
    }

    //(拟分班设置)得到科类专业getWish

    //拟分班设置 //已测试
    public function classSetting($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        $data = D('BranchWish')->where(array('planId' => $planId, 'scId' => $this->scId))
            ->field('id as wishId,branch,branchId,major,majorId')
            ->select();

        $wishMap = array();
        foreach ($data as &$v) {
            if (!array_key_exists($v['branchId'], $wishMap))
                $wishMap[$v['branchId']] = array(
                    'name' => $v['branch'],
                    'branchId' => $v['branchId'],
                    'majors' => array()
                );
            $wishMap[$v['branchId']]['majors'] [$v['wishId']] = array(
                'name' => $v['major'],
                'parentId' => $v['branchId'],
                'majorId' => $v['majorId'],
                'number' => 0,
                'wishId' => $v['wishId']
            );
        }

        $type = $_POST['type'];
        if (!empty($type)) {
            if ($type == 'save') {//已测试
                $class = I('post.class');
                $gradeId = $class[0]['gradeId'];
                $whereCount = array(
                    'scId' => $this->scId,
                    'planId' => $planId,
                    'gradeId' => $gradeId
                );
                $totalNumber = D('BranchStudent')->where($whereCount)->field('wishId,count(id) as totalNumber')->group('wishId')->select();
                $totalNumberMap = array();
                foreach ($totalNumber as $k => $v) {
                    $totalNumberMap[$v['wishId']] = (int)$v['totalNumber'];
                }
                $number = array();
                $val = '';
                $proId = array();
                $reg = '/\d+/';//匹配数字的正则表达式
                foreach ($class as $k => $v) {
                    $majorId = implode(',', $v['majorId']);
                    $bar = $v['majorNumber'];
                    $name = trim($v['name']);
                    //$className=(int)substr($name,0,1);
                    preg_match_all($reg, $name, $result);
                    $className = intval($result[0][0]);
                    $temp = array();
                    foreach ($bar as $k1 => $v1) {
                        $number[$v1['wishId']] += (int)$v1['number'];
                        $majorMap[$v1['wishId']] = $v1['major'];
                        $temp[$v1['wishId']] = array(
                            'major' => $v1['major'],
                            'number' => $v1['number']
                        );
                    }
                    $temp = json_encode($temp);
                    $temp = addcslashes($temp, "\\");
                    $val .= '(' . "'{$v['id']}'," . "$className," . "'{$v['grade']}'," . "{$v['gradeId']},"
                        . "'$majorId'," . "{$v['branchId']}," . "'{$v['branch']}',"
                        . "'{$v['level']}'," . "{$v['levelId']}," . "'{$v['number']}',"
                        . "'{$temp}'," . "{$this->scId}," . "{$planId}),";
                    if (!empty($v['id'])) {
                        $proId[] = $v['id'];
                    }
                }
                foreach ($number as $k => $v) {
                    if ($v < $totalNumberMap[$k]) {
                        $response['msg'] = $majorMap[$k] . '专业的容纳人数必须大于等于填报该专业的志愿学生人数';
                        $this->ajaxReturn($response);
                    }
                }


                $preId = D('BranchClass')->where(array('planId' => $planId))->field('id')->select();
                $preId = array_map(function ($v) {
                    return $v['id'];
                }, $preId);

                $val = rtrim($val, ',');
                $sql = "insert into mks_branch_class (id,className,grade,gradeId,majorId,branchId,branch,
                      level,levelId,number,majorNumber,scId,planId) values {$val} on duplicate key update 
                      className=values(className),grade=values(grade),gradeId=values(gradeId),majorId=values(majorId),
                          branchId=values(branchId),branch=values(branch),level=values(level),
                          levelId=values(levelId),number=values(number),majorNumber=values(majorNumber),
                          scId=values(scId),planId=values(planId)";

                $rs = D('')->execute($sql);
                if ($rs) {
                    $process = array(
                        12 => 1, 13 => 3, 14 => 2
                    );
                    $this->changeProcess($process, $planId);
                }
                $delId = array_diff($preId, $proId);
                if (!empty($delId)) {
                    $where = array(
                        'id' => array('in', $delId)
                    );
                    $rs = D('BranchClass')->where($where)->delete();
                    $where = array(
                        'scId' => $this->scId,
                        'planId' => $planId,
                        'bcId' => array('in', $delId),
                        'assign' => 1
                    );
                    D('BranchStudent')->where($where)->save(array('bcId' => '', 'assign' => 0));//将指定到删除班级的学生还原
                }
            } elseif ($type == 'set') { //已测试
                $grade = $_POST['grade'];
                $gradeId = $_POST['gradeId'];
                $temp = $this->getAllStu($planId, $gradeId);
                $stu = $temp['stu'];

                $number = (int)$_POST['number'];

                $braClass = array();
                $j = 1;//班级名
                $level = D('ClassLevel')->where(array('scId' => $this->scId))->
                field('levelid as levelId,levelname as level')->find(); //班级默认水平

                $wishes = D('BranchWish')->where(array('planId' => $planId))->field('id,branch,major')->select();
                $wish = array();

                $scId = array(-$planId, $this->scId, 0);
                $branchTemp = D('ClassBranch')->where(array('scId' => array('in', $scId)))->select();
                foreach ($branchTemp as $k => $v) {
                    $branch[$v['branch']] = $v['branchid'];
                }

                foreach ($wishes as &$v) {
                    $wish[$v['branch'] . $v['major']] = (int)$v['id'];
                }

                foreach ($stu as $key => $val) {
                    $k = ceil((int)$val['total'] / $number);//班级总数
                    $everyNumber = ceil((int)$val['total'] / $k);
                    $majorNumber = array();
                    foreach ($val['major'] as $x => $y) {
                        $majorNumber[$wish[$key . $x]] = array(
                            'wishId' => $wish[$x],
                            'major' => $x,
                            'number' => ceil((int)$y / $k)
                        );
                    }

                    $temp = $majorNumber;


                    $far = $wishMap;

                    foreach ($temp as $k1 => $v1) {
                        //  var_dump($k1);
                        foreach ($far as $b => $m) {
                            if (key_exists($k1, $m['majors'])) {
                                $far[$b]['majors'][$k1]['number'] = $v1['number'];
                            }
                        }
                    }

                    foreach ($far as $k2 => $v2) {
                        sort($far[$k2]['majors']);
                    }
                    //var_dump($far);
                    sort($far);

                    //每个班每个专业分多少人
                    $wishId = array_keys($majorNumber);

                    sort($majorId);

                    for ($i = 0; $i < $k; $i++) { //$i班级数
                        $braClass[] = array(
                            'className' => $i + $j,
                            'grade' => $grade,
                            'gradeId' => $gradeId,
                            'majorId' => $wishId,
                            'branchId' => $branch[$key],
                            'branch' => $key,
                            'level' => $level['level'],
                            'levelId' => $level['levelId'],
                            'number' => $everyNumber,
                            'majorNumber' => $far
                        );
                        $index = $i + $j;
                    }
                    $j = $index;
                    $j++;//班级名
                }

                $response['data'] = $braClass;
                $response['status'] = 1;
                $this->ajaxReturn($response);
                // $this->ajaxReturn($braClass);
            } else {
                $response['msg'] = '没权限进行操作';
                $this->ajaxReturn($response);
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
                $this->ajaxReturn($response);
            }
            $response['msg'] = '保存成功';
            $response['status'] = 1;
            $this->ajaxReturn($response);
        }

        //sort($wish);
        $gradeId = I('post.gradeId');
        $stu = $this->getAllStu($planId, $gradeId);

        if (empty($stu))
            $this->ajaxReturn($response);
        $info = array();
        $i = 0;
        foreach ($stu['stu'] as $k => $v) {
            $info[$i] = array(
                'name' => $k,
                //'major'=>$v['major'],
                'total' => $v['total'].'人'
            );
            foreach ($v['major'] as $k1 => $v1) {
                $info[$i]['major'][] = array(
                    'name' => $k1,
                    'number' => $v1.'人'
                );
            }
            $i++;
        }
        $response['not'] = $stu['not'].'人';//没填志愿的人数
        $response['stu'] = $info;//一些展示信息

        $class = D('BranchClass')->where(array('planId' => $planId, 'gradeId' => $gradeId))->select();
        if (!$class)
            $this->ajaxReturn($response);
        foreach ($class as $k => $v) {
            $class[$k]['majorId'] = explode(',', $v['majorId']);

            $temp = json_decode($v['majorNumber'], true);
            $far = $wishMap;
            foreach ($temp as $k1 => $v1) {
                foreach ($far as $b => $m) {
                    if (key_exists($k1, $m['majors'])) {
                        $far[$b]['majors'][$k1]['number'] = $v1['number'];
                    }
                }
            }
            foreach ($far as $k1 => $v1) {
                sort($far[$k1]['majors']);
            }
            sort($far);

            $class[$k]['majorNumber'] = $far;

        }
        $response['data'] = $class;
        $response['status'] = 1;
        $this->ajaxReturn($response);
    }

    //(指定学生到班)待选班级
    private function getClass($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array(),
        );
        $class = D('BranchClass')
            ->where(array('planId' => $planId, 'scId' => $this->scId))
            ->select();
        if (!$class)
            $this->ajaxReturn($response);
        $stu = D('BranchStudent')
            ->where(array('planId' => $planId, 'scId' => $this->scId, 'assign' => 1))
            ->field('id,userId,stuId,name,sex,wishId,branch,major,bcId,assign')
            ->select();
        $stuMap = array();
        foreach ($stu as $k => $v) {
            $stuMap[$v['bcId']][] = $v;
        }

        $rs = array();
        foreach ($class as $k => $v) {

            if (!isset($rs[$v['branchId']])) {
                $rs[$v['branchId']] = array(
                    'branchId' => $v['branchId'],
                    'name' => $v['branch'],
                    'child' => array()
                );
            }

            if (!isset($rs[$v['branchId']]['child'][$v['gradeId']])) {
                $rs[$v['branchId']]['child'][$v['gradeId']]
                    = array(
                    'gradeId' => $v['gradeId'],
                    'name' => $v['grade'],
                    'parentId' => $v['branchId'],
                    'child' => array()
                );
            }
            if (!isset($rs[$v['branchId']]['child']
                [$v['gradeId']]['child'][$v['id']])
            ) {
                $rs[$v['branchId']]['child'][$v['gradeId']]['child']
                [$v['id']]
                    = array(
                    'classId' => $v['id'],
                    'name' => $v['className'],
                    'branch' => $v['branch'],
                    'grade' => $v['grade'],
                    'parentId' => $v['gradeId'],
                    'number' => $v['number'],
                    'child' => array()
                );
            }
            $rs[$v['branchId']]['child'][$v['gradeId']]['child']
            [$v['id']]['child'] = $stuMap[$v['id']];
        }
        foreach ($rs as $k => $v) {
            foreach ($v['child'] as $k1 => $v1) {
                sort($rs[$k]['child'][$k1]['child']);
            }
            sort($rs[$k]['child']);
        }
        sort($rs);
        $response['status'] = 1;
        $response['data'] = $rs;
        $this->ajaxReturn($response);
    }

    //(指定学生到班)待选学生
    private function getStu($planId, $key)
    {
        $response = array(
            'status' => 0,
            'data' => array(),
        );
        $where = array(
            'wishId' => array('EXP', 'IS NOT NULL'),
            'planId' => $planId,
            'scId' => $this->scId,
            //'assign' => 0
        );
        if (!empty($key)) {
            $where['name|preGrade|preClass|major'] = array('like', "%{$key}%");
        }

        $stu = D('BranchStudent')
            ->where($where)
            ->field('id,userId,stuId,name,sex,wishId,branch,major,preGrade,preClass,bcId,proClass,proGrade,score,rank')
            ->select();
        if (!$stu) {
            $this->ajaxReturn($response);
        }
        foreach ($stu as &$v){
            if(!empty($v['bcId'])){
                $v['assignClass']=$v['proGrade'].'-'.$v['proClass'].'班';
            }else{
                $v['assignClass']='';
            }

        }
        $response['status'] = 1;
        $response['data'] = $stu;
        $this->ajaxReturn($response);
    }

    //指定学生到班//
    public function assignStu($planId)
    {
        $response = array(
            'status' => 0
        );
        $type = $_POST['type'];
        if (isset($type)) {
            $bcId = I('post.classId');
            if ($type == 'save') {
                D('BranchStudent')//将班级中的指定学生清空
                ->where(array('bcId' => $bcId,'scId'=>$this->scId, 'planId' => $planId))
                    ->data(array(
                        'bcId' => '',
                        'proClass' => '',
                        'proGrade' => '',
                        'proSerialNumber' => '',
                        'assign' => 0
                    ))
                    ->save();
                $val = '';
                /* $total = D('BranchStudent')
                     ->where(array('bcId' => $bcId, 'planId' => $planId))
                     ->max('proSerialNumber');
                 $i = (int)$total;*/
                $assign = 1;
                $allot = I('post.allot');
                $stuIds = I('post.stuIds');
                $rs = true;
                if (!empty($stuIds)) {
                    foreach ($stuIds as &$v) {
                        //   $i++;
                        $val .= '(' . "{$v}," . "'{$allot['classId']}'," . "'{$allot['className']}',"
                            . "'{$allot['grade']}'," /*. "'{$i}'," */ . "{$assign}),";
                    }

                    $val = rtrim($val, ',');
                    $sql = "insert into mks_branch_student (id,bcId,proClass,proGrade,
                            /*proSerialNumber,*/assign)
                          values {$val} on duplicate key update bcId=values(bcId),
                          proClass=values(proClass),proGrade=values(proGrade),
                          /*proSerialNumber=values(proSerialNumber)
                          ,*/assign=values(assign)";
                    $rs = M()->execute($sql);
                }
            } elseif ($type == 'clean') {
                $rs = D('BranchStudent')
                    ->where(array('bcId' => $bcId, 'scId'=>$this->scId,'planId' => $planId))
                    ->data(array(
                        'bcId' => '',
                        'proClass' => '',
                        'proGrade' => '',
                        'proSerialNumber' => '',
                        'assign' => 0
                    ))
                    ->save();
            } else {
                $rs = false;
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
                $this->ajaxReturn($response);
            }
            $response['status'] = 1;
            $response['msg'] = '操作成功';
            $this->ajaxReturn($response);
        }
    }

    //获得首字母
    private function getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631) return 'P';
        if ($asc >= -14630 && $asc <= -14150) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';
        return null;
    }

    //生成分班     //已测试 //
    public function quickDivide($planId, $classLimit, $fill, $distribute, $sex, $priority, $snake, $numberLimit)
    {

        //得到所有班级
        $classes = D('BranchClass')->where(array('planId' => $planId))->select();

        //得到所有填了志愿的学生
        $where = array(
            'scId' => $this->scId,
            'planId' => $planId,
            'assign' => 0,
            'wishId' => array('EXP', 'is not NULL')
        );
        $students = D('BranchStudent')->where($where)
            ->field('id,userId,stuId,name,sex,wishId,branch,major,gradeId,preGrade,preClass
            ,preSerialNumber,score,rank,assign')
            ->select();

        //指定到班的学生
        $stu_a = D('BranchStudent')
            ->where(array('planId' => $planId,
                'scId'=>$this->scId,
                'assign' => 1))
            ->field('id,wishId,bcId')->select();

        $assignMap = array();
        foreach ($stu_a as $k => $v) {
            if (!isset($assignMap[$v['bcId']][$v['wishId']])) {
                $assignMap[$v['bcId']][$v['wishId']] = 0;
            }
            $assignMap[$v['bcId']][$v['wishId']] += 1;
        }

        shuffle($students);//将学生顺序打乱
        $stuTree = array();//将学生分为男女两大类
        foreach ($students as &$v) {
            if (!isset($stuTree[$v['gradeId']][$v['wishId']]))
                $stuTree[$v['gradeId']][$v['wishId']] = array();
            $stuTree[$v['gradeId']][$v['wishId']][] = $v;
        }

        /*$classLimit 班级限制 level 级别优先 parallel 所有班平行
        $fill 分配形式 sequence 按照班级序号 parallel 班级平行
        $distribute 分配方式 score 按照分数分配 random 随机分配
        $sex 性别 ave平均分配 random 随机*/

        //班级处理 //已测试
        $tempClass = array();
        foreach ($classes as $key => $val) {
            $temp = $val;
            $temp['majorNumber'] = json_decode($temp['majorNumber'], true);
            foreach ($temp['majorNumber'] as $k => $v) { //去掉本专业指定到班的学生人数
                $temp['majorNumber'][$k]['number'] -= (int)$assignMap[$val['id']][$k];
            }
            $tempClass[$val['gradeId']][] = $temp;
        }

        $newClass = array();

        if ($classLimit == 'level' && !empty($priority)) { //级别优先 将班级放入各自级别
            foreach ($tempClass as $k => $v) {
                foreach ($v as $k1 => $v1) {
                    foreach ($priority as $key => $levels) {
                        if (!in_array($v1['level'], $levels))
                            continue;
                        else {
                            if (!isset($newClass[$k][$key]))
                                $newClass[$k][$key] = array();
                            $newClass[$k][$key][] = $v1;
                        }
                    }
                }
            }
            foreach ($newClass as $k => $v) {//按照优先级排序 key升序
                ksort($newClass[$k]);
            }
        } else {
            foreach ($tempClass as $k => $v) { //平行 将所有班级放入一个级别
                $newClass[$k][0] = $v;
            }
        }


        $proClass = array();

//人数分配形式 一个班级一个班级的填充 按序号 number为总人数 平行 number=1

        foreach ($newClass as $grade => $level) {

            //取出相应的学生 $stuTree[$grade][$wishId]
            $grade_stu = $stuTree[$grade];

            if ($distribute == 'score') {
                foreach ($grade_stu as $w => $v) {
                    if ($sex == 'ave') {
                        $male = array();
                        $female = array();
                        foreach ($v as $key => $oneStu) {
                            if ($oneStu['sex'] == '男') {
                                $male[] = $oneStu;
                            } else {
                                $female[] = $oneStu;
                            }
                        }
                        usort($male, function ($a, $b) {
                            $al = (int)$a['rank'];
                            $bl = (int)$b['rank'];
                            if ($al == $bl)
                                return 0;
                            return ($al < $bl) ? -1 : 1; //升序
                        });
                        usort($female, function ($a, $b) {
                            $al = (int)$a['rank'];
                            $bl = (int)$b['rank'];
                            if ($al == $bl)
                                return 0;
                            return ($al > $bl) ? -1 : 1; //降序
                        });
                        $grade_stu[$w] = array_merge($male, $female);
                    } else {
                        usort($grade_stu[$w], function ($a, $b) {
                            $al = (int)$a['rank'];
                            $bl = (int)$b['rank'];
                            if ($al == $bl)
                                return 0;
                            return ($al < $bl) ? -1 : 1; //升序
                        });
                    }
                }
            } else {
                foreach ($grade_stu as $w => $v) {
                    if ($sex == 'ave') {
                        $male = array();
                        $female = array();
                        foreach ($v as $key => $oneStu) {
                            if ($oneStu['sex'] == '男') {
                                $male[] = $oneStu;
                            } else {
                                $female[] = $oneStu;
                            }
                        }
                        $grade_stu[$w] = array_merge($male, $female);
                    }
                }
            }


            foreach ($newClass[$grade] as $l => $classes) {
                if ($fill == 'sequence') {//顺序填充 //正确
                    foreach ($classes as $k => $class) {
                        if (!isset($proClass[$grade]['gradeId'])) {
                            $proClass[$grade] = array(
                                'gradeId' => $class['gradeId'],
                                'grade' => $class['grade']
                            );
                        }
                        if (!isset($proClass[$grade]['class'][$class['id']])) {
                            $proClass[$grade]['class'][$class['id']]['className'] = $class['className'];
                            $proClass[$grade]['class'][$class['id']]['classId'] = $class['id'];
                            $proClass[$grade]['class'][$class['id']]['stu'] = array();
                        }
                        foreach ($class['majorNumber'] as $wid => $number) {
                            $fillNumber = $number['number'];
                            $count = count($grade_stu[$wid]);
                            if ($count == 0)
                                continue;
                            if ($fillNumber > $count)
                                $fillNumber = $count;
                            if ($sex == 'ave') {
                                $fillNumber1 = ceil($fillNumber / 2);
                                $fillNumber2 = $fillNumber - $fillNumber1;
                                $spliceStu1 = array_splice($grade_stu[$wid], 0, $fillNumber1);
                                $spliceStu2 = array_splice($grade_stu[$wid], -$fillNumber2);
                                $proClass[$grade]['class'][$class['id']]['stu']
                                    = array_merge($proClass[$grade]['class'][$class['id']]['stu'], $spliceStu1, $spliceStu2);
                            } else {
                                $spliceStu = array_splice($grade_stu[$wid], 0, $fillNumber);
                                $proClass[$grade]['class'][$class['id']]['stu']
                                    = array_merge($proClass[$grade]['class'][$class['id']]['stu'], $spliceStu);
                            }

                        }

                    }
                } else {//平行填充 //正确
                    do {
                        $flag = false;
                        $label = 0;
                        $foo = 1;//记录蛇形
                        foreach ($newClass[$grade][$l] as $k => $class) {
                            if (!isset($proClass[$grade]['gradeId'])) {
                                $proClass[$grade] = array(
                                    'gradeId' => $class['gradeId'],
                                    'grade' => $class['grade']
                                );
                            }
                            // var_dump($class['name']);
                            if (!isset($proClass[$grade]['class'][$class['id']]['stu'])) {
                                $proClass[$grade]['class'][$class['id']]['className'] = $class['className'];
                                $proClass[$grade]['class'][$class['id']]['classId'] = $class['id'];
                                $proClass[$grade]['class'][$class['id']]['stu'] = array();
                            }
                            // var_dump($proClass);
                            $fillNumber = 1;
                            foreach ($class['majorNumber'] as $wid => $number) {
                                $number = $newClass[$grade][$l][$k]['majorNumber'][$wid]['number'];
                                $count = count($grade_stu[$wid]);
                                if ($number == 0 || $count < 1 || $number < 0)
                                    continue;
                                $spliceStu = array_splice($grade_stu[$wid], 0, $fillNumber);
                                $proClass[$grade]['class'][$class['id']]['stu']
                                    = array_merge($proClass[$grade]['class'][$class['id']]['stu'], $spliceStu);
                                $newClass[$grade][$l][$k]['majorNumber'][$wid]['number'] -= 1;
                                $number = $newClass[$grade][$l][$k]['majorNumber'][$wid]['number'];
                                $count = count($grade_stu[$wid]);
                                if ($number == 0 || $count < 1 || $number < 0)
                                    continue;
                                $spliceStu = array_splice($grade_stu[$wid], -1, $fillNumber);
                                $proClass[$grade]['class'][$class['id']]['stu']
                                    = array_merge($proClass[$grade]['class'][$class['id']]['stu'], $spliceStu);
                                $newClass[$grade][$l][$k]['majorNumber'][$wid]['number'] -= 1;
                                $number = $newClass[$grade][$l][$k]['majorNumber'][$wid]['number'];
                                $count = count($grade_stu[$wid]);
                                //var_dump($count);
                                if ($number > 0 && $count > 0)
                                    $label += $number;
                            }
                        }
                        if ($snake)
                            $classes = array_reverse($classes);
                        if ($label > 0)
                            $flag = true;
                    } while ($flag);
                }

                //序号
                if ($numberLimit != 'random') {
                    if ($numberLimit == 'score') {
                        foreach ($proClass as $g => $c) {
                            foreach ($c['class'] as $class => $stu) {
                                usort($proClass[$g]['class'][$class]['stu'], function ($a, $b) {
                                    $al = (int)$a['rank'];
                                    $bl = (int)$b['rank'];
                                    if ($al == $bl)
                                        return 0;
                                    return ($al < $bl) ? -1 : 1; //升序
                                });
                            }
                        }
                    } elseif ($numberLimit == 'name') {
                        foreach ($proClass as $g => $c) {
                            foreach ($c['class'] as $class => $stu) {
                                foreach ($stu['stu'] as $k => $v) {
                                    $proClass[$g]['class'][$class]['stu'][$k]['char'] = $this->getFirstCharter($v['name']);
                                }
                                usort($proClass[$g]['class'][$class]['stu'], function ($a, $b) {
                                    $al = $a['char'];
                                    $bl = $b['char'];
                                    if ($al == $bl)
                                        return 0;
                                    return ($al < $bl) ? -1 : 1; //升序
                                });
                            }
                        }
                    }
                }
            }

        }

        return $proClass;
    }

    //得到分班的限制条件 //已测试
    private function getCondition($way, $number, $sex, $equal, $priority)
    {
        $response = array();
        //序号按成绩 否则random
        $response['number'] = $number;
        $response['equal'] = $equal == 'add' ? 'add' : 'random';
        $response['sex'] = $sex == 'ave' ? 'ave' : 'random';
        $response['fill'] = 'sequence';
        $response['snake'] = false;
        if ($way == 'random') {
            $response['distribute'] = 'random';
        } elseif ($way == 'score') {
            $response['distribute'] = 'score';
        } elseif ($way == 'snake') {
            $response['distribute'] = 'score';
            $response['fill'] = 'parallel';
            $response['snake'] = true;
        }
        //priority [[水平1s],[水平2s]] 优先级降序
        if (count($priority) == 1) {
            $response['classLimit'] = 'parallel';
        } else {
            $response['classLimit'] = 'level';
        }
        return $response;
    }

    //(快速分班)班级水平
    private function classLevel($planId)
    {
        $response = array(
            'data' => array(),
            'status' => 0
        );
        $where = array(
            'scId' => $this->scId,
            'planId' => $planId,
        );
        $levelids = D('BranchClass')->where($where)->field('distinct(levelId)')->select();
        $levelId = array_map(function ($v) {
            return $v['levelId'];
        }, $levelids);
        $level = D('ClassLevel')->where(array('levelid' => array('in', $levelId)))->select();
        if ($level) {
            $response['status'] = 1;
            foreach ($level as $k => $v) {
                $response['data'][] = array(
                    'levelId' => $v['levelid'],
                    'level' => $v['levelname']
                );
            }
        }
        $this->ajaxReturn($response);

    }


    //快速分班  //已测试  //
    public function quickBranch($planId)
    {
        $response = array(
            'status' => 0,
            'msg' => '',
            'data' => ''
        );
        $type = $_REQUEST['type'];
        if (!empty($type)) {
            if ($type == 'save') {
                $stuLists = I('post.stuLists');
                $val = '';
                foreach ($stuLists as &$v) {

                    $val .= '(' . "{$v['id']}," . "'{$v['proGrade']}'," . "'{$v['proClass']}',"
                        . "'{$v['proSerialNumber']}'," . "{$v['bcId']}," . "{$v['assign']}),";
                }
                $val = rtrim($val, ',');
                $sql = "insert into mks_branch_student (id,proGrade,proClass,proSerialNumber,bcId,assign)
                          values {$val} on duplicate key update proGrade=values(proGrade),
                          proClass=values(proClass),proSerialNumber=values(proSerialNumber)
                          ,bcId=values(bcId),assign=values(assign)";

                $res = M()->execute($sql);
                if ($res) {
                    $process = array(
                        14 => 1, 15 => 3, 16 => 3, 17 => 2
                    );
                    $this->changeProcess($process, $planId);
                    $response['msg'] = '保存成功';
                    $response['status'] = 1;
                    $this->ajaxReturn($response);
                }
            } elseif ($type == 'divide') {
                $way = $_POST['way'];
                $number = $_POST['number'];
                $sex = $_POST['sex'];
                $equal = $_POST['equal'];
                $priority = $_POST['priority'];
                //处理优先级
                if (!empty($priority)) {
                    $temp = array();
                    foreach ($priority as $k => $v) {
                        $temp[$v['right']][] = $v['level'];
                    }
                    ksort($temp);
                    $priority = array();
                    foreach ($temp as $k => $v) {
                        $priority[] = $v;
                    }
                }
                $condition = $this->getCondition($way, $number, $sex, $equal, $priority);
                $classLimit = $condition['classLimit'];//班级限制 level 级别优先 parallel 所有班平行
                $fill = $condition['fill'];//分配形式 sequence 按照班级序号 parallel 班级平行
                $distribute = $condition['distribute'];//分配方式 score 按照分数分配 random 随机分配
                $sex = $condition['sex'];//性别 ave平均分配 random 随机
                $snake = $condition['snake'];
                $number = $condition['number'];
                $data = $this->quickDivide($planId, $classLimit, $fill, $distribute, $sex, $priority, $snake, $number);


                //指定到班的学生

                $stu_a = D('BranchStudent')->where(array('planId' => $planId,'scId'=>$this->scId, 'assign' => 1))
                    ->field('id,userId,stuId,name,sex,bcId,gradeId,preGrade
                    ,preClass,preSerialNumber,proGrade,proClass,proSerialNumber,assign')
                    ->select();


                if ($stu_a) {
                    foreach ($stu_a as $k => $v) {
                        $data[$v['gradeId']]['class'][$v['bcId']]['stu'][] = $v;
                    }
                }
                $classNumber = D('StudentInfo')->where(array('scId' => $this->scId, 'gradeId' => array_keys($data)))
                    ->field('grade,className,max(serialNumber) as number')->group('gradeId,classId')->select();
                foreach ($classNumber as $k => $v) {
                    $cnumMap[$v['className']] = (int)$v['number'];
                }

                $result = array();
                foreach ($data as $g => $c) {
                    foreach ($c['class'] as $class => $s) {
                        foreach ($s['stu'] as $k => $v) {
                            $index = isset($cnumMap[$s['className']]) ? $cnumMap[$s['className']] + $k : $k;
                            if ($v['assign']) { //指定到班的学生
                                $result[] = array(
                                    'id' => $v['id'],
                                    'userId' => $v['userId'],
                                    'stuId' => $v['stuId'],
                                    'name' => $v['name'],
                                    'sex' => $v['sex'],
                                    'preGrade' => $v['preGrade'],
                                    'preClass' => $v['preClass'],
                                    'preSerialNumber' => $v['preSerialNumber'],
                                    'proGrade' => $v['proGrade'],
                                    'proClass' => $v['proClass'],
                                    'proSerialNumber' => $k + 1,
                                    'assign' => $v['assign'],
                                    'bcId' => $v['bcId']
                                );
                            } else {  //正常分班的学生
                                $result[] = array(
                                    'id' => $v['id'],
                                    'userId' => $v['userId'],
                                    'stuId' => $v['stuId'],
                                    'name' => $v['name'],
                                    'sex' => $v['sex'],
                                    'preGrade' => $v['preGrade'],
                                    'preClass' => $v['preClass'],
                                    'preSerialNumber' => $v['preSerialNumber'],
                                    'proGrade' => $c['grade'],
                                    'proClass' => $s['className'],
                                    'proSerialNumber' => $index + 1,//+$serMap[],
                                    'assign' => $v['assign'],
                                    'bcId' => $s['classId']
                                );
                            }
                        }
                    }
                }

                $response['data'] = $result;
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($type == 'download') {
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
                    ->setCellValue('C1', '当前年级')
                    ->setCellValue('D1', '当前班级')
                    ->setCellValue('E1', '当前班级序号')
                    ->setCellValue('F1', '拟分年级')
                    ->setCellValue('G1', '拟分班级')
                    ->setCellValue('H1', '拟分班级序号')
                    ->setCellValue('I1', '是否指定到班');
                $students = D('BranchStudent')
                    ->where(
                        array(
                            'scId' => $this->scId,
                            'planId' => $planId,
                            'wishId' => array('EXP', 'IS NOT NULL'))
                    )
                    ->select();
                foreach ($students as $k => $v) {
                    if ($v['assign'] == 1) {
                        $status = '是';
                    } else {
                        $status = '否';
                    }
                    $num = $k + 2;
                    $objPHPExcel->setActiveSheetIndex(0)
                        //Excel的第A列，uid是你查出数组的键值，下面以此类推
                        ->setCellValue('A' . $num, $v['name'])
                        ->setCellValue('B' . $num, $v['sex'])
                        ->setCellValue('C' . $num, $v['preClass'])
                        ->setCellValue('D' . $num, $v['preGrade'])
                        ->setCellValue('E' . $num, $v['preSerialNumber'])
                        ->setCellValue('F' . $num, $v['proClass'])
                        ->setCellValue('G' . $num, $v['proGrade'])
                        ->setCellValue('H' . $num, $v['proSerialNumber'])
                        ->setCellValue('I' . $num, $status);
                }
                $objPHPExcel->getActiveSheet()->setTitle('分班分科表');
                $objPHPExcel->setActiveSheetIndex(0);
                //header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . '分班分科表' . '.xls"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
                exit;
            } else {
                $response['msg'] = '没权限进行操作';
                $this->ajaxReturn($response);
            }
        }
        $where = array(
            'scId' => $this->scId,
            'planId' => $planId,
            'wishId' => array('EXP', 'is not NULL')
        );
        $students = D('BranchStudent')->where($where)
            ->field('id,name,sex,wishId,branch,major,gradeId,preGrade,preClass
            ,preSerialNumber,proGrade,proClass,proSerialNumber,assign')
            ->select();
        if ($students) {
            $response['status'] = 1;
            $response['data'] = $students;
        }
        $this->ajaxReturn($response);

    }

    //手动调整
    public function manualAdjust($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array(),
        );

        $class = D('BranchClass')
            ->where(array('planId' => $planId))->select();

        //分班后的学生
        $stu_where = array(
            'planId' => $planId,
            'bcId' => array('EXP', 'is not NULL'),
            'scId' => $this->scId
        );
        $stu = D('BranchStudent')->where($stu_where)
            ->group('bcId')
            ->field('bcId,count(id) as total')
            ->select();
        $map = array();
        foreach ($stu as $k => $v) {
            $map[$v['bcId']] = $v['total'];
        }
        if (!$class)
            $this->ajaxReturn($response);
        $newClass = array();
        foreach ($class as $k => $v) {
            if (!isset($newClass[$v['branch']]))
                $newClass[$v['branch']] = array(
                    'name' => $v['branch'],
                    'class' => array()
                );
            $newClass[$v['branch']]['class'][] = array(
                'classId' => $v['id'],
                'class' => $v['className'],
                'gradeId' => $v['gradeId'],
                'grade' => $v['grade'],
                'level' => $v['level'],
                'number' => $v['number'],
                'realNumber' => empty($map[$v['id']]) ? 0 : $map[$v['id']]
            );

        }
        sort($newClass);
        $response['status'] = 1;
        $response['data'] = $newClass;
        $this->ajaxReturn($response);
    }

    //(手动调整)参与分班学生名单
    private function classStu($planId, $classId, $gradeId, $key)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'scId' => $this->scId,
            'bcId' => array('neq', $classId),
            'gradeId' => $gradeId,
            'planId' => $planId,
        );
        if (!empty($key)) {
            $where['name|preClass|preGrade|sex'] = array('like', "%{$key}%");
        }
        $stu = D('BranchStudent')->where($where)
            ->field('id,userId,stuId,name,sex,wishId,branch,major,bcId,gradeId,proGrade,proClass
            ,score,assign,rank,assign')->select();

        if (!$stu) {
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['data'] = $stu;
        $this->ajaxReturn($response);
    }

    //(手动调整)指定学生到班
    public function assignStudent($planId)
    {
        $response = array(
            'status' => 0,
            'msg' => ''
        );
        $val = '';
        $ids = I('post.ids');
        $proClass = I('post.proClass');
        $i = (int)D('BranchStudent')
            ->where(array('bcId' => $proClass['classId'], 'planId' => $planId, 'scId' => $this->scId))
            ->max('proSerialNumber');
        $assign = 1;
        foreach ($ids as &$v) {
            $i++;
            $val .= '(' . "{$v['id']}," . "'{$proClass['classId']}'," . "'{$proClass['class']}',"
                . "'{$proClass['grade']}'," . "'{$i}'," . "{$assign}),";
        }
        $val = rtrim($val, ',');
        $sql = "insert into mks_branch_student (id,bcId,proClass,proGrade,proSerialNumber,assign)
                          values {$val} on duplicate key update bcId=values(bcId),
                          proClass=values(proClass),proGrade=values(proGrade),proSerialNumber=values(proSerialNumber)
                          ,assign=values(assign)";

        $res = M()->execute($sql);
        if (!$res) {
            $response['msg'] = '操作失败';
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['msg'] = '操作成功';
        $this->ajaxReturn($response);
    }

    //(手动调整)相邻分数学生互换
    public function equalChange($planId)
    {
        $response = array(
            'status' => 0,
            'msg' => ''
        );
        $ids = I('post.ids');
        $preClass = I('post.preClass');
        $proClass = I('post.proClass');
        $i = count($ids);
        $stuNumber = $i;
        $notIds = array();
        foreach ($ids as $k => $v) {
            $notIds[] = $v['id'];
        }

        $fill = array();
        $foo = 0;
        $where = array(
            'bcId' => $proClass['classId'],
            'scId' => $this->scId,
            'planId' => $planId
        );
        $sn = (int)D('BranchStudent')->where($where)->max('serialNumber');
        do {
            $i--;
            if (count($notIds) > 0) {
                $where['id'] = array('not in', $notIds);
            }
            if (empty($ids[$foo]['rank'])) {
                $response['msg'] = '学生没有排名，不可进行交换';
                $this->ajaxReturn($response);
            }
            $order = "(rank-{$ids[$foo]['rank']})*(rank-{$ids[$foo]['rank']}) asc";
            $changeStu = D('BranchStudent')->where($where)->order($order)->find();
            $notIds[] = $changeStu['id'];
            if (!$changeStu)
                break;
            $index = $ids[$foo]['id'];
            $fill[] = array(
                'id' => $index,
                'classId' => $proClass['classId'],
                'class' => $proClass['class'],
                'grade' => $proClass['grade'],
                'serialNumber' => $changeStu['proSerialNumber'],
            );

            //序号为空
            $serialNumber = $ids[$foo]['serialNumber'];
            if (empty($serialNumber)) {
                $serialNumber = $sn;
                $sn++;
            }

            $fill[] = array(
                'id' => $changeStu['id'],
                'classId' => $preClass[$index]['classId'],
                'class' => $preClass[$index]['class'],
                'grade' => $preClass[$index]['grade'],
                'serialNumber' => $serialNumber,
            );
            $foo++;
        } while ($i > 0);
        // $this->ajaxReturn(array('a'=>$fill));
        // $this->ajaxReturn($fill);

        if (count($fill) != $stuNumber * 2) {
            $response['msg'] = '无相邻分数的学生可替换';
            $this->ajaxReturn($response);
        }
        $val = '';
        $assign = 1;

        foreach ($fill as &$v) {
            $val .= '(' . "{$v['id']}," . "'{$v['classId']}'," . "'{$v['class']}',"
                . "'{$v['grade']}'," . "'{$v['serialNumber']}'," . "{$assign}),";

        }
        $val = rtrim($val, ',');
        $sql = "insert into mks_branch_student(id,bcId,proClass,proGrade,proSerialNumber,assign)
                          values {$val} on duplicate key update bcId=values(bcId),
                          proClass=values(proClass),proGrade=values(proGrade),
                          proSerialNumber=values(proSerialNumber),assign=values(assign)";

        $res = M()->execute($sql);
        if (!$res) {
            $response['msg'] = '操作失败';
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['msg'] = '操作成功';
        $this->ajaxReturn($response);
    }

    //打印报表
    public function printReport($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'planId' => $planId,
            'scId' => $this->scId,
        );
        $students = D('BranchStudent')->where($where)
            ->field('name,sex,branch,major,preGrade,preClass,bcId,proSerialNumber')->select();
        if (!$students) {
            $this->ajaxReturn($response);
        }
        $class = D('BranchClass')->where($where)
            ->field('id,className,branch,majorNumber,grade,level,number')->select();
        $classMap = array();
        foreach ($class as $k => $v) {
            $classMap[$v['id']] = $v;
            $classMap[$v['id']]['bm'] = $v['branch'] . '-';
            $temp = json_decode($v['majorNumber'], true);
            foreach ($temp as $k1 => $v1) {
                $classMap[$v['id']]['bm'] .= $v1['major'] . '/';
            }

            $classMap[$v['id']]['bm'] = rtrim($classMap[$v['id']]['bm'], '/');
        }

        $data = array();
        $not = array(
            'grade' => '',
            'className' => '未参与分班',
            'level' => '',
            'number' => 0,
            'total' => 0,
            'stu' => array(),
        );
        foreach ($students as $k => $v) {
            if ($v['bcId'] > 0) {
                if (!isset($data[$v['bcId']])) {
                    $data[$v['bcId']] = array(
                        'grade' => $classMap[$v['bcId']]['grade'],
                        'className' => $classMap[$v['bcId']]['className'] . '班',
                        'level' => $classMap[$v['bcId']]['level'],
                        'number' => $classMap[$v['bcId']]['number'],
                        'total' => 0,
                        'stu' => array(),
                        'bm' => $classMap[$v['bcId']]['bm']
                    );
                }

                $data[$v['bcId']]['stu'][] = $v;
                $data[$v['bcId']]['total'] += 1;
            } else {
                $not['stu'][] = $v;
                $not['total'] += 1;
            }
        }
        $data = array_values($data);
        $response['not'] = $not;
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
    }

    //发布分班结果
    public function publish($planId)
    {
        $publish = array(
            'stuLook' => $_POST['stuLook'],
            'publish' => 1,
            'sTime' => strtotime($_POST['sTime']),
            'eTime' => strtotime($_POST['eTime'])
        );
        $rs = D('BranchPlan')->where(array('id' => $planId))->data($publish)->save();
        if (!$rs) {
            $response['msg'] = '操作失败';
            $response['status'] = 0;
            $this->ajaxReturn($response);
        }
        $process = array(
            17 => 1, 18 => 2
        );
        $this->changeProcess($process, $planId);
        $response['msg'] = '操作成功';
        $response['status'] = 1;
        $this->ajaxReturn($response);
    }

    //同步分科分班数据
    public function sync($planId)
    {
        $response['status'] = 0;
        /*科目更新*/
        $branchIds = D('BranchWish')->where(array('planId' => $planId, 'scId' => $this->scId))
            ->field('distinct(branchId)')->select();
        $branchId = array_map(function ($v) {
            return $v['branchId'];
        }, $branchIds);
        $map_branch = array(
            'branchid' => array('in', $branchId),
            'scId' => -$planId
        );
        //
        $bar = D('ClassBranch')->where($map_branch)->find();
        if ($bar) {
            $rs = D('ClassBranch')->where($map_branch)->save(array('scId' => $this->scId));
            if (!$rs) {
                $response['msg'] = '科类同步更新出错';
                $this->ajaxReturn($response);
            }
        }

        /*将班级更新*/
        //旧班级 array1
        $oldClass = D('Class')->where(array('scId' => $this->scId))->select();
        $array1 = array();
        $array_id = array();
        foreach ($oldClass as $k => $v) {
            $temp = $v['classname'] . $v['grade'];
            $array1[$temp] = array(
                'className' => $v['classname'],
                'gradeId' => $v['grade'],
                //'majorId' => $v['major'],
                'levelId' => $v['levelid'],
                'branchId' => $v['branch'],
                'number' => $v['number'],
                'scId' => $v['scId']
            );
            $array_id[$temp] = $v['classid']; //存班级的id
        }


        //新班级 array2
        $newClass = D('BranchClass')->where(array('planId' => $planId))->select();
        $array2 = array();
        foreach ($newClass as $k => $v) {
            $temp = $v['className'] . $v['gradeId'];
            $array2[$temp] = array(
                'className' => $v['className'],
                'gradeId' => $v['gradeId'],
                //'majorId' => $v['majorId'],
                'levelId' => $v['levelId'],
                'branchId' => $v['branchId'],
                'number' => $v['number'],
                'scId' => $v['scId']
            );
        }

        //对比 $array3 需要增加的班级 array4需要更新的班级
        $array3 = array();
        $array4 = array();

        foreach ($array2 as $k => $v) {
            if (!isset($array1[$k])) {
                $array3[] = array(
                    'id' => '',
                    'classname' => $v['className'],
                    'grade' => $v['gradeId'],
                    // 'major' => $v['majorId'],
                    'levelid' => $v['levelId'],
                    'branch' => $v['branchId'],
                    'number' => $v['number'],
                    'scId' => $v['scId']
                );
            } else {
                if ($v != $array1[$k]) {
                    $array4[] = array(
                        'id' => $array_id[$k],
                        'classname' => $v['className'],
                        'grade' => $v['gradeId'],
                        //'major' => $v['majorId'],
                        'levelid' => $v['levelId'],
                        'branch' => $v['branchId'],
                        'number' => $v['number'],
                        'scId' => $v['scId']
                    );
                }
            }
        }

        /*班级更新*/
        $val = '';

        if ($array3) { //新增
            foreach ($array3 as &$v) {
                $val .= '(' . "'{$v['id']}'," . "{$v['classname']}," . "'{$v['grade']}'," /*. "'{$v['major']}'," */ . "'{$v['levelid']}',"
                    . "'{$v['branch']}'," . "{$v['number']}," . "{$v['scId']}),";
            }
        }

        // $updateClassId=array();
        if ($array4) { //更新
            foreach ($array4 as &$v) {
                $val .= '(' . "'{$v['id']}'," . "{$v['classname']}," . "'{$v['grade']}'," /*. "'{$v['major']}'," */ . "'{$v['levelid']}',"
                    . "'{$v['branch']}'," . "{$v['number']}," . "{$v['scId']}),";
                //$updateClassId[$v['id']]=$v['classname'];
            }
            /*if(!empty($updateClassId)){
                $updateWhere=array(
                    'scId'=>$this->scId,
                    'classId'=>array('in',$updateClassId)
                );
                D('User')->where($updateWhere)->field('id as userId,classId')
            }*/
        }

        if ($val != '') {
            $val = rtrim($val, ',');
            $sql = "insert into mks_class (classid,classname,grade/*,major*/,levelid,branch,number,scId)
                          values {$val} on duplicate key update classname=values(classname),
                          grade=values(grade),major=values(major),levelid=values(levelid),
                          branch=values(branch),number=values(number),scId=values(scId)";
            $rs = M('')->execute($sql);
            if (!$rs) {
                $response['msg'] = '班级同步更新失败';
                $this->ajaxReturn($response);
            }

            //更改学生的班级信息
            /*     if(!empty($updateClassId)){
                     $tempIds=array_keys($updateClassId);
                     $updateWhere=array(
                         'scId'=>$this->scId,
                         'class'=>array('in',$tempIds)
                     );
                     $userIds=D('User')->where($updateWhere)->field('id,class')->select();
                     $userIdMap=array();
                     foreach ($userIds as $k=>$v){
                         $userIdMap[$v['class']][]=$v['id'];
                     }
                     foreach ($updateClassId as $k=>$v){
                         $userId=$userIdMap[$k];
                         if(!empty($userId)){
                             $data=array(
                                 'className'=>$v
                             );
                             D('User')->where(array('id'=>array('in',$userId)))->save($data);
                             D('User')->where(array('childId'=>array('in',$userId)))->save($data);
                             D('StudentInfo')->where(array('userId'=>array('in',$userId)))->save($data);
                             D('JwSchedule')->where(array('scId'=>$this->scId,'classId'=>$k))->save($data);
                                // D('CenRegInfo')->where(array('userId'=>array('in',$userId)))->save($data);
                                // D('ParentsInfo')->where(array('userId'=>array('in',$userId)))->save($data);
                                // D('SchoolRollinfo')->where(array('userId'=>array('in',$userId)))->save($data);
                                // D('TuitionIfo')->where(array('id'=>array('in',$userId)))->save($data);
                               //  D('OtherInfo')->where(array('id'=>array('in',$userId)))->save($data);
                         }
                     }
                 }*/
        }

        /*学生信息更新*/
        //array5 班级和年级id

        $proClass = D('Class c')
            ->join('mks_grade g on c.grade=g.gradeid')
            ->where(array('c.scId' => $this->scId))
            ->field('c.classid as classId,c.classname as className,
            g.gradeid as gradeId,g.name as gradeName')
            ->select();

        $array5 = array();
        foreach ($proClass as $k => $v) {
            $temp = $v['gradeId'] . $v['className'];
            $array5[$temp] = array(
                'classId' => $v['classId'],
                'gradeId' => $v['gradeId']
            );
        }

        $stuWhere = array(
            'scId' => $this->scId,
            'planId' => $planId,
            'wishId' => array('exp', 'is not null'),
            'proClass' => array('exp', 'is not null')
        );

        $stu = D('BranchStudent')->where($stuWhere)
            ->field('userId,stuId,gradeId,proGrade,proClass,proSerialNumber,wishId')
            ->select();

        $wishData = D('BranchWish')->where(array('planId' => $planId, 'scId' => $this->scId))->field('id,branchId,majorId')->select();
        $wishMap = array();
        foreach ($wishData as $k => $v) {
            $wishMap[$v['id']] = array(
                'branchId' => $v['branchId'],
                'majorId' => $v['majorId']
            );
        }

        foreach ($stu as $k => $v) {
            $temp = $v['gradeId'] . $v['proClass'];
            $stu[$k]['classId'] = $array5[$temp]['classId'];
            $stu[$k]['gradeId'] = $array5[$temp]['gradeId'];
            $stu[$k]['branchId'] = $wishMap[$v['wishId']]['branchId'];
            $stu[$k]['majorId'] = $wishMap[$v['wishId']]['majorId'];
            $classUserMap[$v['userId']] = array(
                'classId' => $array5[$temp]['classId'],
                'className' => $v['proClass'],
                'serialNumber' => $v['proSerialNumber']
            );
        }

        //stu_info 更新
        $stu_val = '';
        foreach ($stu as &$v) {
            $stu_val .= '(' . "{$v['stuId']}," . "'{$v['classId']}'," . "'{$v['proClass']}'," . "'{$v['proSerialNumber']}'),";
        }
        $stu_val = rtrim($stu_val, ',');
        $stu_sql = "insert into mks_student_info (id,classId,className,serialNumber)
                          values {$stu_val} on duplicate key update classId=values(classId),
                          className=values(className),serialNumber=values(serialNumber)";

        $rs = M()->execute($stu_sql);
        if (!$rs) {
            $response['msg'] = '学生信息同步更新失败';
            $this->ajaxReturn($response);
        }
        //

        //school_rollinfo 更新
        $info_sql = "update mks_school_rollinfo set stream = case userId ";
        foreach ($stu as $k => $v) {
            $info_sql .= sprintf("when %d then %d ", $v['userId'], $v['branchId']);
        }
        $info_sql .= "end,major = case userId ";
        $foo = array();
        foreach ($stu as $k => $v) {
            $foo[] = $v['userId'];
            $info_sql .= sprintf("when %d then %d ", $v['userId'], $v['majorId']);
        }
        $foo = implode(',', $foo);
        $info_sql .= "end where userId in ({$foo}) and scId={$this->scId}";

        $rs = M()->execute($info_sql);
        if (!$rs) {
            $response['msg'] = '学生学籍信息同步更新失败';
            $this->ajaxReturn($response);
        }
        //user更新
        $user_val = '';
        foreach ($stu as &$v) {
            $user_val .= '(' . "{$v['userId']}," . "'{$v['classId']}'," . "'{$v['proClass']}'," . "'{$v['proSerialNumber']}'),";
        }
        $user_val = rtrim($user_val, ',');
        $user_sql = "insert into mks_user (id,class,className,serialNumber)
                          values {$user_val} on duplicate key update class=values(class),
                          className=values(className),serialNumber=values(serialNumber)";
        $rs = M()->execute($user_sql);
        if (!$rs) {
            $response['msg'] = '用户信息同步更新失败';
            $this->ajaxReturn($response);
        }
        /*  //parents_info,cen_reg_info,school_rollinfo,tuition_info,other_info更新
          if(!empty($classUserMap)){
              $ids = implode(',', array_keys($classUserMap));
              $p_sql = "UPDATE mks_parents_info SET className = CASE userId ";
              $c_sql = "UPDATE mks_cen_reg_info SET className = CASE userId ";
              $sr_sql = "UPDATE mks_school_rollinfo SET className = CASE userId ";
              $t_sql = "UPDATE mks_tuition_info SET className = CASE userId ";
              $o_sql = "UPDATE mks_other_info SET className = CASE userId ";

              foreach ($classUserMap as $id => $v) {
                  $p_sql .= sprintf("WHEN %d THEN %d ", $id, $v['className']);
                  $c_sql .= sprintf("WHEN %d THEN %d ", $id, $v['className']);
                  $sr_sql .= sprintf("WHEN %d THEN %d ", $id, $v['className']);
                  $t_sql .= sprintf("WHEN %d THEN %d ", $id, $v['className']);
                  $o_sql .= sprintf("WHEN %d THEN %d ", $id, $v['className']);
              }

              $p_sql .= "END WHERE userId IN ($ids) and scId={$this->scId}";
              $c_sql .= "END WHERE userId IN ($ids) and scId={$this->scId}";
              $sr_sql .= "END WHERE userId IN ($ids) and scId={$this->scId}";
              $t_sql .= "END WHERE userId IN ($ids) and scId={$this->scId}";
              $o_sql .= "END WHERE userId IN ($ids) and scId={$this->scId}";

              M()->execute($p_sql);
              M()->execute($c_sql);
              M()->execute($sr_sql);
              M()->execute($t_sql);
              M()->execute($o_sql);
          }*/
        $process = array(
            18 => 1
        );
        $this->changeProcess($process, $planId);
        $response['status'] = 1;
        $response['msg'] = '信息同步更新成功';
        $this->ajaxReturn($response);
    }


    /*************************************学生操作*******************************************/

    //得到学生参与的分班方案
    private function getBelong()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $userId = $this->user['id'];
        $map = array(
            'userId' => $userId,
            'scId' => $this->scId
        );
        $plans = D('BranchStudent')
            ->where($map)
            ->group('planId')
            ->field('planId')
            ->select();
        $planId = array_map(function ($v) {
            return $v['planId'];
        }, $plans);

        $where = array(
            'id' => array('in', $planId),
            'scId' => $this->scId,
            'isNew' => 0
        );
        $plan = D('BranchPlan')
            ->where($where)->field('id,name,fillStart,fillEnd')->select();
        if (!$plan) {
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['data'] = $plan;
        $this->ajaxReturn($response);
    }

    //填报分班志愿
    public function fillWish($planId)
    {
        $response = array(
            'status' => 0,
        );
        if ($this->roleId == $this::$jZroleId) {
            $userId = $this->user['childId'];
        } else {
            $userId = $this->user['id'];
        }

        $type = $_POST['type'];
        if (isset($type)) {
            if ($type == 'submit') {
                $time = time();
                $plan = D('BranchPlan')->where(array('id' => $planId))->find();
                $change = D('BranchStudent')
                    ->where(array('userId' => $userId, 'planId' => $planId, 'scId' => $this->scId))
                    ->find();
                if ($plan['publish'] == 1) {
                    $response['msg'] = '分班结果已发布,不可更改志愿!';
                    $this->ajaxReturn($response);
                }

                if (!$change['changeId']) { //第一次填写
                    if ($time < $plan['fillStart'] || $time > $plan['fillEnd']) {
                        $response['msg'] = '当前不在填写志愿时间之内';
                        $this->ajaxReturn($response);
                    }
                    $number = (int)$plan['notFill'] - 1;
                } else { //非第一次填写
                    if ($plan['stuChange'] != 1) {
                        $response['msg'] = '学生不可修改志愿';
                        $this->ajaxReturn($response);
                    }
                    if ($time < $plan['changeStart'] || $time > $plan['changeEnd']) {
                        $response['msg'] = '当前不在修改志愿时间之内';
                        $this->ajaxReturn($response);
                    }
                    $number = 0;
                }

                $score = json_decode($change['comScore'], true);
                $score = $score[$_POST['wishId']]['score'];
                $rank = json_decode($change['comRank'], true);
                $rank = $rank[$_POST['wishId']];

                $data = array(
                    'wishId' => $_POST['wishId'],
                    'branch' => $_POST['branch'],
                    'major' => $_POST['major'],
                    'changeId' => $this->uid,
                    'score' => $score,
                    'rank' => $rank,
                    'confirm' => 1
                );
                $rs = D('BranchStudent')
                    ->where(array('id' => $_POST['id']))
                    ->data($data)->save();
                if (!$rs) {
                    $response['msg'] = '提交失败';
                    $this->ajaxReturn($response);
                }
                if ($number) { //修改填写志愿人数
                    D('BranchPlan')->where(array('id' => $planId))->data(array('notFill' => $number))->save();
                }
                $response['msg'] = '填写成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
        }
        $wish = D('BranchStudent')
            ->where(array('userId' => $this->uid, 'planId' => $planId, 'scId' => $this->scId))
            ->field('id,userId,stuId,wishId,branch,major,changeId')
            ->find();

        $response['status'] = 1;
        $response['data'] = $wish;
        $this->ajaxReturn($response);

    }

    //查看分班结果
    public function branchResult($planId)
    {
        $response = array(
            'status' => 0,
        );
        if ($this->roleId == $this::$jZroleId) {
            $userId = $this->user['childId'];
        } else {
            $userId = $this->user['id'];
        }

        $plan = D('BranchPlan')->where(array('id' => $planId))->find();
        if ($plan['publish'] != 1 || $plan['stuLook'] != 1) {
            $response['msg'] = '结果未发布或者没有权限查看';
            $this->ajaxReturn($response);
        }
        $time = time();
        if ($time < $plan['sTime'] || $time > $plan['eTime']) {
            $response['msg'] = '不在查看结果的时间内';
            $this->ajaxReturn($response);
        }
        $result = D('BranchStudent')
            ->where(array('scId' => $this->scId, 'planId' => $planId, 'userId' => $userId))
            ->field('id,preGrade,preClass,preSerialNumber,proGrade,proClass,proSerialNumber')
            ->find();
        if ($result) {
            $response['status'] = 1;
            $response['data'] = $result;
        }
        $this->ajaxReturn($response);
    }

    //分班成绩查询
    public function personScore($planId, $whether = 1)
    {
        $response = array(
            'status' => 0,
        );
        if ($this->roleId == $this::$jZroleId) {
            $userId = $this->user['childId'];
        } else {
            $userId = $this->user['id'];
        }
        $stuStudent = (int)D('BranchPlan')->where(array('id' => $planId))->getField('stuSearch');
        if ($stuStudent != 1) {
            $response['msg'] = '本方案学生不可查看成绩';
            $response['status'] = 1;
        }
        $info = D('BranchStudent')
            ->where(array('userId' => $userId, 'planId' => $planId,'scId'=>$this->scId))
            ->field('id,comScore,comRank,subScore')
            ->find();
        if ($info) {
            $score = array();
            if ($whether == 1) { //成绩汇总
                $foo = D('BranchWish')->where(array('planId' => $planId))->field('id,branch,major')->select();
                $wish = array();
                foreach ($foo as &$v) {
                    $wish[$v['id']] = array(
                        'branch' => $v['branch'],
                        'major' => $v['major']
                    );
                }
                $comScore = json_decode($info['comScore'], true);
                $comRank = json_decode($info['comRank'], true);
                foreach ($comScore as $k => $v) {
                    $score[$k] = array(
                        'branch' => $wish[$k]['branch'],
                        'major' => $wish[$k]['major'],
                        'score' => $v['score'],
                        'rank' => $comRank[$k]
                    );
                }
            } else { //成绩明细
                //$wish=$_POST['wishId'];
                $foo = D('BranchSubject s')
                    ->join('mks_branch_exam e ON s.branchExamId=e.id', 'LEFT')
                    ->where(array('s.planId' => $planId))
                    ->field('s.id as subId,s.subject,e.id as examId,e.examination')
                    ->select();
                $subject = array();
                foreach ($foo as &$v) {
                    $subject[$v['subId']] = array(
                        'subject' => $v['subject'],
                        'exam' => $v['examination']
                    );
                }

                $sc = D('BranchStudent')->where(array('planId' => $planId,'scId'=>$this->scId))->field('subScore')->select();
                $scores = array_map(function ($v) {
                    return json_decode($v['subScore'], true);
                }, $sc);
                $rank = array();
                foreach ($scores as $k => $v) {
                    foreach ($v as $k1 => $v1) {
                        $rank[$k1][] = $v1;
                    }
                }
                foreach ($rank as $key => $v) {
                    rsort($rank[$key]);
                }

                $subScore = json_decode($info['subScore'], true);
                foreach ($subScore as $k => $v) {
                    $score[$k] = array(
                        'exam' => $subject[$k]['exam'],
                        'subject' => $subject[$k]['subject'],
                        'score' => $v,
                        'rank' => array_search($v, $rank[$k]),
                    );
                }
            }
            sort($score);
            $response['status'] = 1;
            $response['data'] = $score;
        }

        $this->ajaxReturn($response);
    }

    /*************************************教师操作*******************************************/
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
            if (!isset($data[$v['grade']]))
                $data[$v['grade']] = array(
                    'gradeName' => $map[$v['grade']],
                    'sort'      => $v['sort'],
                    'gradeId'   => $v['grade'],
                    'classes'   => array()
                );
            $data[$v['grade']]['classes'][] = array(
                'className' => $v['classname'],
                'classId'   => $v['classid']
            );
        }
        usort($data, function ($a, $b) {
            return $a['sort'] < $b['sort'] ? -1 : 1;
        });

        $data=array_values($data);
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
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
                $plan = D('BranchPlan')->where(array('id' => $planId))->find();
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
                $wishId = (int)$bar['wishId'];

                if (empty($wishId)) {
                    $number = (int)$plan['notFill'] - 1;
                } else {
                    $number = 0;
                }
                $score = json_decode($bar['comScore'], true);
                $score = $score[$_POST['wishId']]['score'];
                $rank = json_decode($bar['comRank'], true);
                $rank = $rank[$_POST['wishId']];


                $data = array(
                    'wishId' => $_POST['wishId'],
                    'branch' => $_POST['branch'],
                    'major' => $_POST['major'],
                    'score' => $score,
                    'rank' => $rank,
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
                    D('BranchPlan')->where(array('id' => $planId))->data(array('notFill' => $number))->save();
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

        $export = $_REQUEST['export'];
        if ($export == 'ensure') {
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

            $map = array();
            $sub = 0;
            foreach ($stus as $k => $v) {
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['name'])
                    ->setCellValue('B' . $num, $v['sex'])
                    ->setCellValue('C' . $num, $v['preGrade'])
                    ->setCellValue('D' . $num, $v['preClass'])
                    ->setCellValue('E' . $num, $v['branch'])
                    ->setCellValue('F' . $num, $v['major']);
                $foo = ord('C');
                for ($i = 1;
                     $i <= $sub;
                     $i++) {
                    $foo += 1;
                    $str = chr($foo);
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue($str . $num, $v[$map[$str]]['score']);
                }
            }
            $objPHPExcel->getActiveSheet()->setTitle('教师志愿调整表');
            $objPHPExcel->setActiveSheetIndex(0);
//  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '教师志愿调整表' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        $key = $_POST['key'];
        if (!empty($key)) {
            $where['name|branch|major'] = array('like', "%{$key}%");
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
        $total = (int)D('BranchStudent')->where($where)->count();
        $response['total'] = $total;
        $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        $response['status'] = 1;
        $response['data'] = $stus;
        $this->ajaxReturn($response);
    }

}