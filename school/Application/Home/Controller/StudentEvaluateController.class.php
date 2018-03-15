<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/9/20
 * Time: 10:10
 */

namespace Home\Controller;

/*
 * 学生考评
 * */
ob_end_clean();
use PHPExcel_IOFactory;
use PHPExcel;

//权限已设置
class StudentEvaluateController extends Base
{
    protected $leave;
    protected $scId;
    protected $uId;
    protected $roleId;
    protected $user;


    public function __construct()
    {
        parent::__construct();
        $scId = $_SESSION['loginCheck']['data']['scId'];
        $uId = $_SESSION['loginCheck']['data']['userId'];
        $this->roleId = $_SESSION['loginCheck']['data']['roleId'];
        /* $this->roleId = 22;
         $scId = 2;
         $uId = 1;*/
        $this->scId = $scId;
        $this->uId = $uId;
        $this->user = D('User')->where(array('id' => $this->uId, 'scId' => $this->scId))->find();
    }

    /*********************************************管理员************************************************/

    //公用接口
    public function common($func, $param = array())
    {
        switch ($func) {
            case 'getSemester':
                $this->getSemester();
                break;
            case 'getScope':
                $this->getScope();
                break;
            case 'getClass':
                $this->getClass();
                break;
            case 'getAllEva':
                $this->getAllEva();
                break;
            case 'getCheckBox':
                $this->getCheckBox($param['evaId'], $param['option']);
                break;
            case 'getBelongEvaluate':
                $this->getBelongEvaluate();
                break;
            case 'evaList':
                $this->evaList();
                break;
            default:
                return null;
        }
    }

    //(创建教学评教)得到学期学年 //已测试
    private function getSemester()
    {
        $now = time();
        $where = array(
            'scId' => $this->scId,
            'startTime' => array('lt', $now),
            'endTime' => array('gt', $now)
        );
        $data = D('SchoolYear')->where($where)->field('yearname,term')->find();
        $this->ajaxReturn($data);
    }

    //(创建教学评教)学生评教范围 //已测试
    private function getScope()
    {
        $where = array(
            'c.scId' => $this->scId
        );
        $data = D('Class c')
            ->join('mks_grade g ON c.grade=g.gradeid', 'LEFT')
            ->where($where)
            ->field('c.classid,c.classname,c.classType,g.name,g.gradeid')
            ->order('g.name asc')
            ->select();
        $scope = array();
        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $gradeMap = array();
        foreach ($grade as $k => $v) {
            $gradeMap[$v['name']] = $v['znName'];
        }
        foreach ($data as &$v) {
            if ($v['classType'] == 1) {
                $temp = '教学班';
            } else {
                $temp = '选课班';
            }
            $grade = $gradeMap[$v['name']];
            $scope[$temp][$grade][] = array(
                'gradeId' => $v['gradeid'],
                'classId' => $v['classid'],
                'className' => $v['classname']
            );
        }
        $this->ajaxReturn($scope);
    }

    //创建教学评教 //已测试
    public function createEvaluate()
    {
        $response = array(
            'status' => 0,
            'msg' => ''
        );
        $type = $_POST['type'];
        if (isset($type)) {
            if ($type == 'create') {
                $data = $_POST;
                $where=array(
                    'scId'=>$this->scId,
                    'name' => $data['name']
                );
                $find=D('EvaluateTeach')->where($where)->find();
                if(!empty($find)){
                    $response['msg'] = '该方案名称已存在';
                    $this->ajaxReturn($response);
                }
                $findMode = D('EvaluateMode')->where(array('scId' => $this->scId))->find();
                if(empty($findMode)){
                    $response['msg'] = '评分方式未设置';
                    $this->ajaxReturn($response);
                }

                //添加教师
                $where_tea = array(
                    'scId' => $this->scId,
                    'classId' => array('in', $_POST['scope'])
                );
                $teacher = D('JwSchedule')->where($where_tea)
                    ->field('className,subject,techerId as teacherId,techerName as teacherName,gradeId,subjectId,classId,gradeName')
                    ->select();
                if (!$teacher) {
                    $response['msg'] = '所选班级暂无教师任教';
                    $this->ajaxReturn($response);
                }

                $where_stu = array(
                    's.scId' => $this->scId,
                    's.classId' => array('in', $_POST['scope']),
                    's.isAtSchool' =>'是'
                );
                $students = D('StudentInfo s')->join('mks_class c on s.classId=c.classid')->where($where_stu)
                    ->field('s.userId,s.name,s.grade,s.gradeId,s.classId,s.className,s.serialNumber,c.classType')
                    ->select();
                if (!$students) {
                    $response['msg'] = '所选班级暂无学生';
                    $this->ajaxReturn($response);
                }

                $evaluate = array(
                    'semester' => $data['semester'],
                    'name' => $data['name'],
                    'scope' => implode(',', $data['scope']), //班级id 数组
                    //'scopeInfo' => json_encode($data['scopeInfo']),
                    'startTime' => strtotime($data['startTime']),
                    'endTime' => strtotime($data['endTime']),
                    'mode' => $data['mode'],
                    'min' => $data['min'],
                    'max' => $data['max'],
                    'comment' => $data['comment'],
                    'createTime' => time(),
                    'creator' => $this->user['name'],
                    'creatorId' => $this->uId,
                    'publish' => 0,
                    'scId' => $this->scId,
                );
                // 保存方案
                $rsId = D('EvaluateTeach')->data($evaluate)->add();
                if (!$rsId) {
                    $response['msg'] = '保存方案失败';
                    $this->ajaxReturn($response);
                }

                $record = array();
                //班主任
                $where1 = array(
                    'c.scId' => $this->scId,
                    'c.classid' => array('in', $_POST['scope'])
                );
                $classUser =
                    D('Class c')
                        ->join('mks_grade g ON c.grade=g.gradeid','LEFT')
                        ->where($where1)
                        ->field('c.classid as classId,c.classname as className,c.userid as teacherId,c.grade as gradeId,g.name as gradeName')
                        ->select();
                $userId = array_map(function ($v) {
                    return $v['teacherId'];
                }, $classUser);
                if(!empty($userId)){
                    $user=D('User')->where(array('scId'=>$this->scId,'id'=>array('in',$userId)))->field('id,name')->select();
                    foreach ($user as $k=>$v){
                        $userMap[$v['id']]=$v['name'];
                    }

                    foreach ($classUser as $k=>$v){
                        if(empty($v['teacherId'])){
                            continue;
                        }
                        $record[] = array(
                            'userId' => $v['teacherId'],
                            'name' => $userMap[$v['teacherId']],
                            'subjectId' => -1,
                            'subject' => '班主任',
                            'classId' => $v['classId'],
                            'class' => $v['className'],
                            'gradeId' => $v['gradeId'],
                            'grade' => $v['gradeName'],
                            'total' => 0,
                            'evaluateId' => $rsId,
                            'scId' => $this->scId
                        );
                    }
                }

                foreach ($teacher as &$v) {
                    $record[] = array(
                        'userId' => $v['teacherId'],
                        'name' => $v['teacherName'],
                        'subjectId' => $v['subjectId'],
                        'subject' => $v['subject'],
                        'classId' => $v['classId'],
                        'class' => $v['className'],
                        'gradeId' => $v['gradeId'],
                        'grade' => $v['gradeName'],
                        'total' => 0,
                        'evaluateId' => $rsId,
                        'scId' => $this->scId
                    );
                }
                $rs = D('EvaluateRecord')->addAll($record);
                if (!$rs) {
                    $response['msg'] = '保存老师失败';
                    $this->ajaxReturn($response);
                }
                //添加学生


                $record = array();
                foreach ($students as &$v) {
                    $record[] = array(
                        'classId' => $v['classId'],
                        'class' => $v['className'],
                        'gradeId' => $v['gradeId'],
                        'grade' => $v['grade'],
                        'classType' => $v['classType'],
                        'serialNumber' => $v['serialNumber'],
                        'userId' => $v['userId'],
                        'name' => $v['name'],
                        'evaluateId' => $rsId,
                        'joined' => 0,
                        'scId' => $this->scId
                    );
                }
                $rs = D('EvaluateStudent')->addAll($record);
                if (!$rs) {
                    $response['msg'] = '保存学生失败';
                    $this->ajaxReturn($response);
                }

                $response['status'] = 1;
                $response['msg'] = '操作成功';
            } else {
                $response['msg'] = '没有权限';
            }
        }
        $this->ajaxReturn($response);

    }

    //评分方式设置 //已测试
    public function modeSetting()
    {
        $response = array(
            'status' => 0,
            'msg' => ''
        );
        $type = $_POST['type'];
        if (isset($type)) {
            if ($type != 'save') {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            $mode = array(
                'score' => $_POST['score'],
                'field' => implode(',', $_POST['field']),
                'star' => (int)$_POST['star']
            );
            if (empty($_POST['id'])) {
                $mode['scId'] = $this->scId;
                $rs = D('EvaluateMode')->data($mode)->add();
            } else {
                $rs = D('EvaluateMode')->where(array('id' => $_POST['id']))->save($mode);
            }
            $response['status'] = 1;
            $response['msg'] = '操作成功';
            if (!$rs) {
                $response['status'] = 0;
                $response['msg'] = '操作失败';
            }
            $this->ajaxReturn($response);
        }
        $data = D('EvaluateMode')->where(array('scId' => $this->scId))->find();
        $response['id'] = $data['id'];
        if (!$data) {
            $data = D('EvaluateMode')->where(array('scId' => 0))->find();
            $response['id'] = '';
        }
        $data['field'] = explode(',', $data['field']);
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
    }

    //教学评价记录 //已测试
    public function recordEvaluate()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];
        if (isset($type)) {
            $response['msg'] = '操作成功';
            $response['status'] = 1;
            $rs = false;
            if ($type == 'publish') {
                $id = $_POST['id'];
                $rs = D('EvaluateTeach')->where(array('id' => $id))->data(array('publish' => $_POST['publish']))->save();
            } elseif ($type == 'del') {
                $id = $_POST['id'];
                $rs = D('EvaluateTeach')->where(array('id' => $id))->delete();
            } else {
                $response['msg'] = '没有权限';
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
                $this->ajaxReturn($response);
            }
            $response['status'] = 1;
            $response['msg'] = '操作成功';
            $this->ajaxReturn($response);
        }
        $where = array(
            't.scId' => $this->scId
        );
        //导出
        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            $data = D('EvaluateTeach t')->join('mks_evaluate_student es ON t.id=es.evaluateId', 'Right')
                ->where($where)
                ->group('es.classId')
                ->field('t.id,t.name,t.startTime,t.endTime,t.mode,t.createTime,t.publish,
                es.grade,es.class,es.classType,count(es.id) as total,count(if(es.joined=1,1,null)) as yet')
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
                ->setCellValue('A1', '评教名称')
                ->setCellValue('B1', '评教开始时间')
                ->setCellValue('C1', '评教结束时间')
                ->setCellValue('D1', '已评教人数')
                ->setCellValue('E1', '总评教人数')
                ->setCellValue('F1', '评分方式')
                ->setCellValue('G1', '创建时间');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            foreach ($data as $k => $v) {
                if ($v['mode'] == 1) {
                    $status = '分数';
                } elseif ($v['mode'] == 0) {
                    $status = '满意度';
                } else {
                    $status = '星级';
                }
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['title'])
                    ->setCellValue('B' . $num, date('Y-m-d H:i', $v['startTime']))
                    ->setCellValue('C' . $num, date('Y-m-d H:i', $v['endTime']))
                    ->setCellValue('D' . $num, $v['yet'])
                    ->setCellValue('E' . $num, $v['total'])
                    ->setCellValue('F' . $num, $status)
                    ->setCellValue('G' . $num, date('Y-m-d H:i:s', $v['createTime']));
            }
            $objPHPExcel->getActiveSheet()->setTitle('教学评教记录');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '教学评教记录' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;

        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        //搜索
        $key = $_POST['key'];
        if (!empty($key)) {
            $where['t.name'] = array('like', "%{$key}%");
        }

        $model = D('EvaluateTeach t');
        $subQuery = $model->where($where)->order('t.createTime desc')->limit($limit_page)->buildSql();

        $data = D('EvaluateTeach t')
            ->table($subQuery . ' t')
            ->join('mks_evaluate_student es ON t.id=es.evaluateId', 'LEFT')
            ->group('t.id,es.classId')
            ->field('t.id,t.name,t.startTime,t.endTime,t.mode,t.createTime,t.publish,
                es.grade,es.class,es.classType,count(es.id) as total,count(if(es.joined=1,1,null)) as yet')
            ->order('t.createTime desc')
            ->select();

        if (!$data) {
            $this->ajaxReturn($response);
        }
        $total = (int)D('EvaluateTeach t')
            ->where($where)->count();

        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $gradeMap = array();
        foreach ($grade as $k => $v) {
            $gradeMap[$v['name']] = $v['znName'];
        }
        $record = array();
        foreach ($data as &$v) {
            if (!isset($record[$v['id']]))
                $record[$v['id']] = array(
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'startTime' =>date('Y-m-d H:i',$v['startTime']) ,
                    'endTime' =>date('Y-m-d H:i', $v['endTime']),
                    'mode' => $v['mode'],
                    'createTime' => date('Y-m-d H:i:s',$v['createTime']),
                    'list' => array(),
                    'yet' => 0,
                    'total' => 0,
                    'publish' => $v['publish']
                );
            $record[$v['id']]['list'][$v['classType']][] = array( //
                'grade' => $gradeMap[$v['grade']],
                'class' => $v['class'],
                'total' => $v['total'],
                'yet' => $v['yet']
            );
            $record[$v['id']]['total'] += $v['total'];
            $record[$v['id']]['yet'] += $v['yet'];
        }
        $record = array_values($record);
        $response['total'] = $total;
        $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        $response['status'] = 1;
        $response['data'] = $record;
        $this->ajaxReturn($response);
    }

    // (班级评教名单)得到班级 //已测试
    private function getClass()
    {
        if (!in_array($this->roleId, array($this::$teacherRoleId, $this::$adminRoleId, $this::$xzRoleId))) {
            $this->ajaxReturn(array());
        }
        $classId = D('Class')->where(array('scId' => $this->scId))->field('classid,userid')->select();
        $cId = array();
        $cMap = array();
        foreach ($classId as $k => $v) {
            if (!empty($v['userid'])) {
                $cId[] = $v['userid'];
                $cMap[$v['userid']][] = $v['classid'];
            }
        }

        $gradeId = D('Grade')->where(array('scId' => $this->scId))->field('gradeid,userId')->select();
        $gId = array();
        $gMap = array();
        foreach ($gradeId as $k => $v) {
            if (!empty($v['userId'])) {
                $gId[] = $v['userId'];
                $gMap[$v['userId']][] = $v['gradeid'];
            }
        }

        $where = array(
            't.scId' => $this->scId,
            's.scId' => $this->scId
        );
        if ($this->record == $this::$teacherRoleId) {
            if (in_array($this->uId, $gId)) {
                $where['s.gradeId'] = array('in', $gMap[$this->uId]);
            } elseif (in_array($this->uId, $cId)) {
                $where['s.classId'] = array('in', $cMap[$this->uId]);
            } else {
                $this->ajaxReturn(array());
            }
        }


        $info = D('EvaluateTeach t')->join('mks_evaluate_student s ON t.id=s.evaluateId', 'Right')->
        where($where)->group('t.id,s.classId')->
        field('t.id,t.name,s.grade,s.gradeId,s.class,s.classId')->select();

        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $gradeMap = array();
        foreach ($grade as $k => $v) {
            $gradeMap[$v['name']] = $v['znName'];
        }

        $data = array();

        foreach ($info as &$v) {
            if (!isset($data[$v['id']])) {
                $data[$v['id']] = array(
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'child' => array()
                );
            }
            if (!isset($data[$v['id']]['child'][$v['gradeId']])) {
                $data[$v['id']]['child'][$v['gradeId']] = array(
                    'gradeId' => $v['gradeId'],
                    'grade' =>$gradeMap[$v['grade']],
                    'child' => array()
                );
            }
            $data[$v['id']]['child'][$v['gradeId']]['child'][] = array(
                'class' => $v['class'],
                'classId' => $v['classId']
            );
        }
        foreach ($data as $k => $v) {
            sort($data[$k]['child']);
        }
        sort($data);
        $this->ajaxReturn($data);
    }

    //班级评教名单 //已测试
    public function teachEvaluate()
    {
        $response = array(
            'status' => 0
        );
        $classId = $_REQUEST['classId'];
        $evaluateId = $_REQUEST['evaluateId'];
        $where = array(
            'classId' => $classId,
            'evaluateId' => $evaluateId,
            'scId' => $this->scId
        );

        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            $data = D('EvaluateStudent')
                ->where($where)
                ->field('serialNumber,name,joined')
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
                ->setCellValue('A1', '班级序号')
                ->setCellValue('B1', '姓名')
                ->setCellValue('C1', '评教状态');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            foreach ($data as $k => $v) {
                if ($v['joined'] == 1) {
                    $status = '已评教';
                } else {
                    $status = '未评教';
                }
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['serialNumber'])
                    ->setCellValue('B' . $num, $v['name'])
                    ->setCellValue('C' . $num, $status);
            }
            $objPHPExcel->getActiveSheet()->setTitle('班级评教名单');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '班级评教名单' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        //搜索
        $key = $_POST['key'];
        if (!empty($key)) {
            $where['name|serialNumber'] = array('like', "%{$key}%");
        }

        $data = D('EvaluateStudent')
            ->where($where)
            ->field('serialNumber,name,joined')
            ->limit($limit_page)
            ->select();

        if ($data) {
            $total = (int)D('EvaluateStudent')
                ->where($where)
                ->count();
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['status'] = 1;
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);
    }

    //(评教数据分析)下拉框
    private function getAllEva()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'scId' => $this->scId,
        );
        $data = D('EvaluateTeach')->where($where)->field('id,name')->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
    }

    //(评教数据分析)下拉框 //已测试
    private function getCheckBox($evaId, $option)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $grade = D('Grade')->where(array('scId' => $this->scId))->field('gradeid,znName')->select();
        $gradeMap = array();
        foreach ($grade as $k => $v) {
            $gradeMap[$v['gradeid']] = $v['znName'];
        }

        if ($option == 'GradeClass') {
            $data = D('EvaluateRecord')->where(array(
                'evaluateId' => $evaId,
                'scId' => $this->scId
            ))->select();
            $res = array();
            if (!$data)
                $this->ajaxReturn($response);
            foreach ($data as $k => $v) {
                if (!isset($res[$v['gradeId']])) {
                    $res[$v['gradeId']] = array(
                        'name' => $gradeMap[$v['gradeId']],
                        'id' => $v['gradeId'],
                        'child' => array()
                    );
                }
                if (!isset($res[$v['gradeId']]['child'][$v['classId']]))
                    $res[$v['gradeId']]['child'][$v['classId']] = array(
                        'name' => $v['class'],
                        'id' => $v['classId']
                    );
            }
            foreach ($res as $k => $v) {
                sort($res[$k]['child']);
            }
            sort($res);
            $response['status'] = 1;
            $response['data'] = $res;
            $this->ajaxReturn($response);
        } elseif ($option == 'GradeSub') {
            $data = D('EvaluateRecord')->where(array(
                'evaluateId' => $evaId,
                'subjectId'=>array('neq',-1),
                'scId' => $this->scId
            ))->select();
            $res = array();
            if (!$data)
                $this->ajaxReturn($response);
            foreach ($data as $k => $v) {
                if (!isset($res[$v['gradeId']])) {
                    $res[$v['gradeId']] = array(
                        'name' => $gradeMap[$v['gradeId']],
                        'id' => $v['gradeId'],
                        'child' => array()
                    );
                }
                if (!isset($res[$v['gradeId']]['child'][$v['subjectId']]))
                    $res[$v['gradeId']]['child'][$v['subjectId']] = array(
                        'name' => $v['subject'],
                        'id' => $v['subjectId']
                    );
            }
            foreach ($res as $k => $v) {
                sort($res[$k]['child']);
            }
            sort($res);
            $response['status'] = 1;
            $response['data'] = $res;
            $this->ajaxReturn($response);
        } elseif ($option == 'allSubject') {
            $data = D('EvaluateRecord')->where(array(
                'evaluateId' => $evaId,
                'subjectId'=>array('neq',-1),
                'scId' => $this->scId
            ))->select();
            $res = array();
            if (!$data)
                $this->ajaxReturn($response);
            foreach ($data as $k => $v) {
                if (!isset($res[$v['subjectId']])) {
                    $res[$v['subjectId']] = array(
                        'name' => $v['subject'],
                        'id' => $v['subjectId']
                    );
                }
            }
            sort($res);
            $response['status'] = 1;
            $response['data'] = $res;
            $this->ajaxReturn($response);
        } else {
        }
    }

    //评教数据分析 //已测试
    public function statisticsEvaluate($evaId, $option){
        $response = array(
            'status' => 0,
            'data' => array(),
            'field' => array()
        );
        $eva = D('EvaluateTeach')
            ->where(array('id' => $evaId))->field('id,name,mode,min,max')->find();
        $mode = D('EvaluateMode')->where(array('scId' => $this->scId))->find();
        $case = array('teacher', 'class', 'subjectClass');
        $field = array();
        if (in_array($option, $case)) {
            $name = '班内排名';
        } elseif($option=='subjectGrade') {
            $name = '年级排名';
        }else{
            $name = '年级内排名';
        }
        if ($eva['mode'] == 1) { //分数
            $field = array(
                array(
                    'id' => 'rank',
                    'name' => $name
                ), array(
                    'id' => 'ave',
                    'name' => '平均分')
            );
        } elseif ($eva['mode'] == 2) { //字段
            $mode = explode(',', $mode['field']);
            foreach ($mode as $k => $v) {
                $field[] = array(
                    'id' => 'f' . (int)($k + 1),
                    'name' => $v
                );
                $field[] = array(
                    'id' => 'f' . 'f' . (int)($k + 1),
                    'name' => $v . '率'
                );
            }
        } else {
            $field[] = array(
                'id' => 'ave',
                'name' => '平均分'
            );
            $field[] = array(
                'id' => 'rank',
                'name' => $name
            );
            $mode = (int)$mode['star'];
            for ($i = 0; $i < $mode; $i++) {
                $k = $i + 1;
                $field[] = array(
                    'id' => 's' . (int)$k,
                    'name' => '星级' . (int)$k
                );
                $field[] = array(
                    'id' => 's' . 's' . (int)$k,
                    'name' => '星级' . (int)$k . '率'
                );
            }
        }

        switch ($option) {
            case 'teacher': //各班主任统计
       /*         $teaId = D('Class')->where(array('scId' => $this->scId))->field('userid')->select();
                $teaId = array_map(function ($v) {
                    return $v['userid'];
                }, $teaId);*/

                $where = array(
                   // 'r.userId' => array('in', $teaId),
                   // 'r.subjectId'=>-1,
                    'r.gradeId' => $_REQUEST['gradeId'],
                    'r.evaluateId' => $evaId, 'r.scId' => $this->scId);
                $data = D('EvaluateRecord r')->join('mks_evaluate_map m ON r.id=m.recordId', 'Right')
                    ->where($where)
                    ->field('r.class,r.classId,r.name,r.userId,r.subjectId,m.*')
                    ->select();

                $res = array();
                $index = array();
                $rate = array();
                foreach ($data as $k => $v) {
                    if (!isset($res[$v['userId'].$v['subjectId']])) {
                        $res[$v['userId'].$v['subjectId']] = array(
                            'class' => $v['class'],
                            'classId' => $v['classId'],
                            'name' => $v['name'],
                            'subjectId'=>$v['subjectId'],
                            'total' => 0
                        );
                    }
                    $res[$v['userId'].$v['subjectId']]['total']++; //参评人数
                    if ($eva['mode'] == 1) { //分数
                        $res[$v['userId'].$v['subjectId']]['totalScore'] += (int)$v['value'];
                        $index[$v['userId'].$v['subjectId']][] = (int)$v['value']; //用于去除最高分和最低分
                    } else { //字段和星级
                        $key = $v['property'];
                        $index[$v['userId'].$v['subjectId']][] = $key;
                        $res[$v['userId'].$v['subjectId']][$key] += (int)$v['value'];
                        $temp = (int)substr($key, -1); //倍数
                        $res[$v['userId'].$v['subjectId']]['totalScore'] += (int)$v['value'] * $temp;
                        $anotherKey = substr($v['property'], 0, 1) . $v['property']; //求率
                        $rate[$v['userId'].$v['subjectId']][] = $anotherKey;
                    }
                }
                //去掉最高分最低分
                $max = (int)$eva['max'];
                $min = (int)$eva['min'];
                foreach ($index as $k => $v) {
                    $temp = $v;
                    rsort($temp); //降序
                    $res[$k]['real'] = $res[$k]['total']; //参与计算人数
                    $res[$k]['real'] -= $max;
                    for ($i = 0; $i < $max; $i++) {
                        if ($eva['mode'] == 1) {
                            $res[$k]['totalScore'] -= (int)array_splice($temp, 0, 1);
                        } else {
                            $bar = array_splice($temp, 0, 1);
                            $score = (int)substr($bar[0], -1);
                            $res[$k]['totalScore'] -= $score;
                        }
                    }
                    $res[$k]['real'] -= $min;
                    for ($i = 0; $i < $min; $i++) {
                        if ($eva['mode'] == 1) {
                            $bar = array_splice($temp, -1);
                            $res[$k]['totalScore'] -= (int)$bar[0];

                        } else {
                            $bar = array_splice($temp, -1);
                            $score = (int)substr($bar[0], -1);
                            $res[$k]['totalScore'] -= $score;
                        }
                    }
                }

              /*  //排名
                uasort($res, function ($a, $b) {
                    $al = (int)$a['totalScore'];
                    $bl = (int)$b['totalScore'];
                    if ($al == $bl)
                        return 0;
                    return ($al > $bl) ? -1 : 1; //降序
                });
                */

                //分班
                $map = array();
                foreach ($res as $k => $v) {
                    $map[$v['classId']][] = $v['totalScore'];
                }
                foreach ($map as $k=>$v){
                    //去掉重复的总分(重复的并列)
                    $map[$k]=array_unique($map[$k]);
                    //对分数进行排名
                    rsort($map[$k]);
                }

                $result = array();
                foreach ($res as $k => $v) {
                    if ($v['subjectId']==-1) { //找出班主任
                        $result[$k] = $v;
                        if ($v['real'] < 1) {
                            $result[$k]['ave'] = 0;
                        } else {
                            $result[$k]['ave'] = round($v['totalScore'] / $v['real']);
                        }
                        $result[$k]['rank'] = array_search($v['totalScore'], $map[$v['classId']]) + 1;
                        if ($mode != 1) {
                            $temp = array_unique($rate[$k]);
                            foreach ($temp as $k1 => $v1) {
                                $k1 = substr($v1, 1);
                                $result[$k][$v1]
                                    = (sprintf("%.2f", $result[$k][$k1] / $result[$k]['total']) * 100) . '%';
                            }
                        }
                    }
                }
                uasort($result, function ($a, $b) {
                    $al = (int)$a['rank'];
                    $bl = (int)$b['rank'];
                    if ($al == $bl)
                        return 0;
                    return ($al < $bl) ? -1 : 1; //升序
                });
                $result = array_values($result);
                break;
            case 'class'://班级各科统计
                $where = array('r.gradeId' => $_REQUEST['gradeId'],
                    'r.classId' => $_REQUEST['classId'],
                    'r.subjectId'=>array('neq',-1),
                    'r.evaluateId' => $evaId, 'r.scId' => $this->scId);
                $data = D('EvaluateRecord r')->join('mks_evaluate_map m ON r.id=m.recordId', 'Right')
                    ->where($where)
                    ->field('r.subject,r.classId,r.class,r.name,r.userId,m.*')
                    ->select();
                $res = array();
                $index = array();
                $rate = array();
                foreach ($data as $k => $v) {
                    if (!isset($res[$v['userId'].$v['subjectId']])) {
                        $res[$v['userId'].$v['subjectId']] = array(
                            'subject' => $v['subject'],
                            'classId' => $v['classId'],
                            'name' => $v['name'],
                            'total' => 0
                        );
                    }
                    $res[$v['userId'].$v['subjectId']]['total']++; //参评人数
                    if ($eva['mode'] == 1) { //分数
                        $res[$v['userId'].$v['subjectId']]['totalScore'] += (int)$v['value'];
                        $index[$v['userId'].$v['subjectId']][] = (int)$v['value']; //用于去除最高分和最低分

                    } else { //字段和星级
                        $key = $v['property'];
                        $index[$v['userId'].$v['subjectId']][] = $key;
                        $res[$v['userId'].$v['subjectId']][$key] += (int)$v['value'];
                        $temp = (int)substr($key, -1); //倍数
                        $res[$v['userId'].$v['subjectId']]['totalScore'] += (int)$v['value'] * $temp;

                        $anotherKey = substr($v['property'], 0, 1) . $v['property']; //求率
                        $rate[$v['userId'].$v['subjectId']][] = $anotherKey;
                    }
                }
                //去掉最高分最低分
                $max = (int)$eva['max'];
                $min = (int)$eva['min'];
                foreach ($index as $k => $v) {
                    $temp = $v;
                    rsort($temp); //降序
                    $res[$k]['real'] = $res[$k]['total']; //参与计算人数
                    $res[$k]['real'] -= $max;
                    for ($i = 0; $i < $max; $i++) {
                        if ($eva['mode'] == 1) {
                            $res[$k]['totalScore'] -= (int)array_splice($temp, 0, 1);
                        } else {
                            $bar = array_splice($temp, 0, 1);
                            $score = (int)substr($bar[0], -1);
                            $res[$k]['totalScore'] -= $score;
                        }
                    }
                    $res[$k]['real'] -= $min;
                    for ($i = 0; $i < $min; $i++) {
                        if ($eva['mode'] == 1) {
                            $bar = array_splice($temp, -1);
                            $res[$k]['totalScore'] -= (int)$bar[0];

                        } else {
                            $bar = array_splice($temp, -1);
                            $score = (int)substr($bar[0], -1);
                            $res[$k]['totalScore'] -= $score;
                        }
                    }
                }

             /*   //排名
                uasort($res, function ($a, $b) {
                    $al = (int)$a['totalScore'];
                    $bl = (int)$b['totalScore'];
                    if ($al == $bl)
                        return 0;
                    return ($al > $bl) ? -1 : 1; //降序
                });*/

                //分班
                $map = array();
                foreach ($res as $k => $v) {
                    $map[$v['classId']][] = $v['totalScore'];
                }
                foreach ($map as $k=>$v){
                    //去掉重复的总分(重复的并列)
                    $map[$k]=array_unique($map[$k]);
                    //对分数进行排名
                    rsort($map[$k]);
                }
                $result = array();
                foreach ($res as $k => $v) {
                    $result[$k] = $v;
                    if ($v['real'] < 1) {
                        $result[$k]['ave'] = 0;
                    } else {
                        $result[$k]['ave'] = round($v['totalScore'] / $v['real']);
                    }
                    $result[$k]['rank'] = array_search($v['totalScore'], $map[$v['classId']]) + 1;
                    if ($mode != 1) {
                        $temp = array_unique($rate[$k]);
                        foreach ($temp as $k1 => $v1) {
                            $k1 = substr($v1, 1);
                            $result[$k][$v1]
                                = (sprintf("%.2f", $result[$k][$k1] / $result[$k]['total']) * 100) . '%';
                        }
                    }
                }

                uasort($result, function ($a, $b) {
                    $al = (int)$a['rank'];
                    $bl = (int)$b['rank'];
                    if ($al == $bl)
                        return 0;
                    return ($al < $bl) ? -1 : 1; //升序
                });

                $result = array_values($result);
                break;
            case 'subjectClass'://科目各班统计
                $where = array('r.gradeId' => $_REQUEST['gradeId'],
                    //'r.subjectId' => $_REQUEST['subjectId'],
                    'r.evaluateId' => $evaId, 'r.scId' => $this->scId);
                $data = D('EvaluateRecord r')->join('mks_evaluate_map m ON r.id=m.recordId', 'Right')
                    ->where($where)
                    ->field('r.subject,r.subjectId,r.classId,r.class,r.name,r.userId,m.*')
                    ->select();
                $res = array();
                $index = array();
                $rate = array();
                foreach ($data as $k => $v) {
                    if (!isset($res[$v['userId'].$v['subjectId']])) {
                        $res[$v['userId'].$v['subjectId']] = array(
                            'class' => $v['class'],
                            'classId' => $v['classId'],
                            'name' => $v['name'],
                            'subjectId'=>$v['subjectId'],
                            'total' => 0
                        );
                    }
                    $res[$v['userId'].$v['subjectId']]['total']++; //参评人数
                    if ($eva['mode'] == 1) { //分数
                        $res[$v['userId'].$v['subjectId']]['totalScore'] += (int)$v['value'];
                        $index[$v['userId'].$v['subjectId']][] = (int)$v['value']; //用于去除最高分和最低分

                    } else { //字段和星级
                        $key = $v['property'];
                        $index[$v['userId'].$v['subjectId']][] = $key;
                        $res[$v['userId'].$v['subjectId']][$key] += (int)$v['value'];
                        $temp = (int)substr($key, -1); //倍数
                        $res[$v['userId'].$v['subjectId']]['totalScore'] += (int)$v['value'] * $temp;

                        $anotherKey = substr($v['property'], 0, 1) . $v['property']; //求率
                        $rate[$v['userId'].$v['subjectId']][] = $anotherKey;
                    }
                }
                //去掉最高分最低分
                $max = (int)$eva['max'];
                $min = (int)$eva['min'];
                foreach ($index as $k => $v) {
                    $temp = $v;
                    rsort($temp); //降序
                    $res[$k]['real'] = $res[$k]['total']; //参与计算人数
                    $res[$k]['real'] -= $max;
                    for ($i = 0; $i < $max; $i++) {
                        if ($eva['mode'] == 1) {
                            $res[$k]['totalScore'] -= (int)array_splice($temp, 0, 1);
                        } else {
                            $bar = array_splice($temp, 0, 1);
                            $score = (int)substr($bar[0], -1);
                            $res[$k]['totalScore'] -= $score;
                        }
                    }
                    $res[$k]['real'] -= $min;
                    for ($i = 0; $i < $min; $i++) {
                        if ($eva['mode'] == 1) {
                            $bar = array_splice($temp, -1);
                            $res[$k]['totalScore'] -= (int)$bar[0];

                        } else {
                            $bar = array_splice($temp, -1);
                            $score = (int)substr($bar[0], -1);
                            $res[$k]['totalScore'] -= $score;
                        }
                    }
                }

               /* //排名
                uasort($res, function ($a, $b) {
                    $al = (int)$a['totalScore'];
                    $bl = (int)$b['totalScore'];
                    if ($al == $bl)
                        return 0;
                    return ($al > $bl) ? -1 : 1; //降序
                });*/

                //分班
                $map = array();
                foreach ($res as $k => $v) {
                    $map[$v['classId']][] = $v['totalScore'];
                }
                foreach ($map as $k=>$v){
                    //去掉重复的总分(重复的并列)
                    $map[$k]=array_unique($map[$k]);
                    //对分数进行排名
                    rsort($map[$k]);
                }
                $result = array();
                $subId=I('request.subjectId');

                foreach ($res as $k => $v) {
                    if($v['subjectId']==$subId){
                        $result[$k] = $v;
                        if ($v['real'] < 1) {
                            $result[$k]['ave'] = 0;
                        } else {
                            $result[$k]['ave'] = round($v['totalScore'] / $v['real']);
                        }
                        $result[$k]['rank'] = array_search($v['totalScore'], $map[$v['classId']]) + 1;
                        if ($mode != 1) {
                            $temp = array_unique($rate[$k]);
                            foreach ($temp as $k1 => $v1) {
                                $k1 = substr($v1, 1);
                                $result[$k][$v1]
                                    = (sprintf("%.2f", $result[$k][$k1] / $result[$k]['total']) * 100) . '%';
                            }
                        }
                    }
                }
                uasort($result, function ($a, $b) {
                    $al = (int)$a['rank'];
                    $bl = (int)$b['rank'];
                    if ($al == $bl)
                        return 0;
                    return ($al < $bl) ? -1 : 1; //升序
                });
                $result = array_values($result);
                break;
            case 'subjectGrade'://科目年级统计
                $where = array(
                    'r.subjectId' => $_REQUEST['subjectId'], 'r.evaluateId' => $evaId, 'r.scId' => $this->scId);
                $data = D('EvaluateRecord r')->join('mks_evaluate_map m ON r.id=m.recordId', 'Right')
                    ->where($where)
                    ->field('r.subject,r.subjectId,r.classId,r.gradeId,r.grade,r.class,r.name,r.userId,m.*')
                    ->select();
                $res = array();
                $index = array();
                $rate = array();
                $grade = D('Grade')->where(array('scId' => $this->scId))->field('gradeid,znName')->select();
                $gradeMap = array();
                foreach ($grade as $k => $v) {
                    $gradeMap[$v['gradeid']] = $v['znName'];
                }
                foreach ($data as $k => $v) {
                    if (!$res[$v['gradeId']]) {
                        $res[$v['gradeId']] = array(
                            'classId' => $v['classId'],
                            'grade' => $gradeMap[$v['gradeId']],
                            'gradeId'=>$v['gradeId'],
                            'total' => 0
                        );
                    }
                    $res[$v['gradeId']]['total']++; //参评人数
                    if ($eva['mode'] == 1) { //分数
                        $res[$v['gradeId']]['totalScore'] += (int)$v['value'];
                        $index[$v['gradeId']][] = (int)$v['value']; //用于去除最高分和最低分

                    } else { //字段和星级
                        $key = $v['property'];
                        $index[$v['gradeId']][] = $key;
                        $res[$v['gradeId']][$key] += (int)$v['value'];
                        $temp = (int)substr($key, -1); //倍数
                        $res[$v['gradeId']]['totalScore'] += (int)$v['value'] * $temp;

                        $anotherKey = substr($v['property'], 0, 1) . $v['property']; //求率
                        $rate[$v['gradeId']][] = $anotherKey;
                    }
                }
                //去掉最高分最低分
                $max = (int)$eva['max'];
                $min = (int)$eva['min'];
                foreach ($index as $k => $v) {
                    $temp = $v;
                    rsort($temp); //降序
                    $res[$k]['real'] = $res[$k]['total']; //参与计算人数
                    $res[$k]['real'] -= $max;
                    for ($i = 0; $i < $max; $i++) {
                        if ($eva['mode'] == 1) {
                            $res[$k]['totalScore'] -= (int)array_splice($temp, 0, 1);
                        } else {
                            $bar = array_splice($temp, 0, 1);
                            $score = (int)substr($bar[0], -1);
                            $res[$k]['totalScore'] -= $score;
                        }
                    }
                    $res[$k]['real'] -= $min;
                    for ($i = 0; $i < $min; $i++) {
                        if ($eva['mode'] == 1) {
                            $bar = array_splice($temp, -1);
                            $res[$k]['totalScore'] -= (int)$bar[0];

                        } else {
                            $bar = array_splice($temp, -1);
                            $score = (int)substr($bar[0], -1);
                            $res[$k]['totalScore'] -= $score;
                        }
                    }
                }

                //排名
                uasort($res, function ($a, $b) {
                    $al = (int)$a['totalScore'];
                    $bl = (int)$b['totalScore'];
                    if ($al == $bl)
                        return 0;
                    return ($al > $bl) ? -1 : 1; //降序
                });

              /*  //分班
                $map = array();

                foreach ($res as $k => $v) {
                    $map[$v['gradeId']][] = $k;
                }*/

                $result = array();
                $rank=0;
                foreach ($res as $k => $v) {
                    $rank++;
                    $result[$k] = $v;
                    if ($v['real'] < 1) {
                        $result[$k]['ave'] = 0;
                    } else {
                        $result[$k]['ave'] = round($v['totalScore'] / $v['real']);
                    }
                  /*  var_dump($k);
                   var_dump($map[$v['gradeId']]);
                    var_dump(array_search($k, $map[$v['gradeId']]));
                    die;*/
                    //$result[$k]['rank'] = array_search($k, $map[$v['gradeId']]) + 1;
                    $result[$k]['rank'] =$rank;
                    if ($mode != 1) {
                        $temp = array_unique($rate[$k]);
                        foreach ($temp as $k1 => $v1) {
                            $k1 = substr($v1, 1);
                            $result[$k][$v1]
                                = (sprintf("%.2f", $result[$k][$k1] / $result[$k]['total']) * 100) . '%';
                        }
                    }
                }
                $result = array_values($result);
                break;
            case 'grade'://年级各科统计
                $where = array(
                    'r.gradeId' => $_REQUEST['gradeId'], 'r.subjectId'=>array('neq',-1),
                    'r.evaluateId' => $evaId, 'r.scId' => $this->scId);
                $data = D('EvaluateRecord r')->join('mks_evaluate_map m ON r.id=m.recordId', 'Right')
                    ->where($where)
                    ->field('r.subject,r.subjectId,r.classId,r.gradeId,r.grade,r.class,r.name,r.userId,m.*')
                    ->select();
                $res = array();
                $index = array();
                $rate = array();
                foreach ($data as $k => $v) {
                    if (!$res[$v['subjectId']]) {
                        $res[$v['subjectId']] = array(
                            'subjectId' => $v['subjectId'],
                            'gradeId'=>$v['gradeId'],
                            'subject' => $v['subject'],
                            'total' => 0
                        );
                    }
                    $res[$v['subjectId']]['total']++; //参评人数
                    if ($eva['mode'] == 1) { //分数
                        $res[$v['subjectId']]['totalScore'] += (int)$v['value'];
                        $index[$v['subjectId']][] = (int)$v['value']; //用于去除最高分和最低分

                    } else { //字段和星级
                        $key = $v['property'];
                        $index[$v['subjectId']][] = $key;
                        $res[$v['subjectId']][$key] += (int)$v['value'];
                        $temp = (int)substr($key, -1); //倍数
                        $res[$v['subjectId']]['totalScore'] += (int)$v['value'] * $temp;

                        $anotherKey = substr($v['property'], 0, 1) . $v['property']; //求率
                        $rate[$v['subjectId']][] = $anotherKey;
                    }
                }
                //去掉最高分最低分
                $max = (int)$eva['max'];
                $min = (int)$eva['min'];
                foreach ($index as $k => $v) {
                    $temp = $v;
                    rsort($temp); //降序
                    $res[$k]['real'] = $res[$k]['total']; //参与计算人数
                    $res[$k]['real'] -= $max;
                    for ($i = 0; $i < $max; $i++) {
                        if ($eva['mode'] == 1) {
                            $res[$k]['totalScore'] -= (int)array_splice($temp, 0, 1);
                        } else {
                            $bar = array_splice($temp, 0, 1);
                            $score = (int)substr($bar[0], -1);
                            $res[$k]['totalScore'] -= $score;
                        }
                    }
                    $res[$k]['real'] -= $min;
                    for ($i = 0; $i < $min; $i++) {
                        if ($eva['mode'] == 1) {
                            $bar = array_splice($temp, -1);
                            $res[$k]['totalScore'] -= (int)$bar[0];

                        } else {
                            $bar = array_splice($temp, -1);
                            $score = (int)substr($bar[0], -1);
                            $res[$k]['totalScore'] -= $score;
                        }
                    }
                }

             /*   //排名
                uasort($res, function ($a, $b) {
                    $al = (int)$a['totalScore'];
                    $bl = (int)$b['totalScore'];
                    if ($al == $bl)
                        return 0;
                    return ($al > $bl) ? -1 : 1; //降序
                });*/

                //分班
                $map = array();
                foreach ($res as $k => $v) {
                    $map[$v['gradeId']][] = $v['totalScore'];
                }
                foreach ($map as $k=>$v){
                    //去掉重复的总分(重复的并列)
                    $map[$k]=array_unique($map[$k]);
                    //对分数进行排名
                    rsort($map[$k]);
                }

                $result = array();
                foreach ($res as $k => $v) {
                    $result[$k] = $v;
                    if ($v['real'] < 1) {
                        $result[$k]['ave'] = 0;
                    } else {
                        $result[$k]['ave'] = round($v['totalScore'] / $v['real']);
                    }
                    $result[$k]['rank'] = array_search($v['totalScore'], $map[$v['gradeId']]) + 1;
                    if ($mode != 1) {
                        $temp = array_unique($rate[$k]);
                        foreach ($temp as $k1 => $v1) {
                            $k1 = substr($v1, 1);
                            $result[$k][$v1]
                                = (sprintf("%.2f", $result[$k][$k1] / $result[$k]['total']) * 100) . '%';
                        }
                    }
                }

                uasort($result, function ($a, $b) {
                    $al = (int)$a['rank'];
                    $bl = (int)$b['rank'];
                    if ($al == $bl)
                        return 0;
                    return ($al < $bl) ? -1 : 1; //升序
                });

                $result = array_values($result);
                break;
            default :
                break;
        }

        if (empty($result)) {
            $this->ajaxReturn($response);
        }

        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            switch ($option) {
                case 'teacher':
                    $preField = array(
                        'class' => '班级',
                        'name' => '班主任',
                        'total' => '参评人数'
                    );
                    break;
                case 'class':
                    $preField = array(
                        'class' => '班级',
                        'name' => '班主任',
                        'total' => '参评人数'
                    );
                    break;
                case 'subjectClass':
                    $preField = array(
                        'class' => '班级',
                        'name' => '任课老师',
                        'total' => '参评人数'
                    );
                    break;
                case 'subjectGrade':
                    $preField = array(
                        'grade' => '年级',
                        'total' => '参评人数',
                    );
                    break;
                case 'grade':
                    $preField = array(
                        'subject' => '科目',
                        'total' => '参评人数'
                    );
                    break;
                default :
                    $preField = array();
                    break;
            }
            foreach ($field as $k => $v) {
                $preField[$v['id']] = $v['name'];
            }

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
            $index = 65;
            $map = array();
            foreach ($preField as $k => $v) {
                $str = chr($index);
                $map[$str] = $k;
                $index++;
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($str . '1', $v);
            }
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            foreach ($result as $k => $v) {
                $num = $k + 2;
                foreach ($map as $k1 => $v1) {
                    $temp = $v[$v1];
                    if (empty($v[$v1]))
                        $temp = 0;
                    $objPHPExcel->setActiveSheetIndex(0)
                        //Excel的第A列，uid是你查出数组的键值，下面以此类推
                        ->setCellValue($k1 . $num, $temp);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle('评教数据分析表');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '评教数据分析表' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        $response['status'] = 1;
        $response['field'] = $field;
        $response['data'] = $result;
        $this->ajaxReturn($response);

    }

/*********************************************学生************************************************/
    //得到所属评教方案 //已测试
    private function getBelongEvaluate()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $class = $this->user['class'];

        $where = array(
            'scId' => $this->scId,
            '_string' => "FIND_IN_SET($class,scope)"
        );
        $eva = D('EvaluateTeach')
            ->where($where)->field('id,name,comment,startTime,endTime,mode')->select();
        $mode = D('EvaluateMode')->where(array('scId' => $this->scId))->find();

        if (!$eva)
            $this->ajaxReturn($response);
        foreach ($eva as $k => $v) {
            $eva[$k]['score'] = $mode['score'];
            $eva[$k]['field'] = explode(',', $mode['field']);
            $eva[$k]['star'] = $mode['star'];
        }
        $response['status'] = 1;
        $response['data'] = $eva;
        $this->ajaxReturn($response);
    }

    //学生评分 //已测试
    public function studentMark($evaId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];
        $classId = $this->user['class'];
        $mode = D('EvaluateTeach')->where(array('id' => $evaId))->getField('mode');

        if (isset($type)) {
            if ($type != 'submit') {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            $record = $_POST['record'];
            //更新学生评价
            $where = array(
                'evaluateId' => $evaId,
                'userId' => $this->uId,
                'scId' => $this->scId
            );
            $rs = D('EvaluateStudent')
                ->where($where)->data(array('joined' => 1))
                ->save();
            if ($rs === false) {
                $response['msg'] = '更新学生评教信息失败';
                $this->ajaxReturn($response);
            }
            //生成评价信息
            $data = array();
            foreach ($record as $k => $v) {
                $data[] = array(
                    'stuId' => $this->uId,
                    'recordId' => $v['recordId'],
                    'property' => $v['property'],
                    'value' => (int)$v['value'],
                    'remark' => $v['remark'],
                    'evaluateId' => $evaId,
                    'scId' => $this->scId,
                    'mode' => $mode
                );
            }
            $rs = D('EvaluateMap')->addAll($data);

            if (!$rs) {
                $response['msg'] = '生成评价信息失败';
                $this->ajaxReturn($response);
            }
            $response['status'] = 1;
            $response['msg'] = '评教成功';
            $this->ajaxReturn($response);
        }

        $stuRecord = D('EvaluateMap')
            ->where(array('evaluateId' => $evaId, 'stuId' => $this->uId, 'scId' => $this->scId))
            ->select();


        $teaRecord = D('EvaluateRecord')
            ->where(array('evaluateId' => $evaId, 'classId' => $classId, 'scId' => $this->scId))
            ->field('id,name,subject,type')
            ->select();

        $record = array();
        if (!$stuRecord) { //未评教
            $joined = 0;
            foreach ($teaRecord as &$v) {
                $record[] = array(
                    'recordId' => $v['id'],
                    'name' => $v['name'],
                    'subject' => $v['subject'],
                    'remark' => '',
                    'property' => '',
                    'value' => '',
                    'mode' => $mode
                );
            }
        } else { //已评教
            $joined = 1;
            $map = array();
            foreach ($stuRecord as $k => $v) {
                $map[$v['recordId']] = array(
                    'property' => $v['property'],
                    'value' => $v['value'],
                    'remark' => $v['remark'],
                );
            }

            foreach ($teaRecord as &$v) {
                $record[] = array(
                    'recordId' => $v['id'],
                    'name' => $v['name'],
                    'subject' => $v['subject'],
                    'remark' => $map[$v['id']]['remark'],
                    'value' => $map[$v['id']]['value'],
                    'property' => $map[$v['id']]['property'],
                    'mode' => $mode,
                );
            }
        }
        $response['status'] = 1;
        $response['data'] = $record;
        $response['joined'] = $joined;
        $this->ajaxReturn($response);
    }

    /*********************************************老师************************************************/
    //(评教成绩查询)得到评教名称
    private function evaList()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $map = array(
            'userId' => $this->uId,
            'scId' => $this->scId,
        );
        $teaRecord = D('EvaluateRecord')//评价的老师
        ->where($map)
            ->group('evaluateId')
            ->field('evaluateId')
            ->select();

        if (!$teaRecord) {
            $this->ajaxReturn($response);
        }
        $evaId = array_map(function ($v) {
            return $v['evaluateId'];
        }, $teaRecord);

        $where = array(
            'id' => array('in', $evaId),
            'scId' => $this->scId
        );
        $data = D('EvaluateTeach')//评价的老师
        ->where($where)
            ->field('id,name')
            ->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
    }

    //评教成绩查询
    public function record($evaId)
    {
        $response = array(
            'status' => 0,
            'data' => array(),
            'field' => array()
        );
        $eva = D('EvaluateTeach')
            ->where(array('id' => $evaId))->field('id,name,mode,min,max,publish')->find();

        if ($eva['publish'] != 1) {
            $response['msg'] = '成绩未发布';
            $this->ajaxReturn($response);
        }
        $mode = D('EvaluateMode')->where(array('scId' => $this->scId))->find();

        $field = array();
        $name = '班内排名';

        if ($eva['mode'] == 1) { //分数
            $field = array(
                array(
                    'id' => 'rank',
                    'name' => $name
                ), array(
                    'id' => 'ave',
                    'name' => '平均分')
            );
        } elseif ($eva['mode'] == 2) { //字段
            $mode = explode(',', $mode['field']);
            foreach ($mode as $k => $v) {
                $field[] = array(
                    'id' => 'f' . (int)($k + 1),
                    'name' => $v
                );
                $field[] = array(
                    'id' => 'f' . 'f' . (int)($k + 1),
                    'name' => $v . '率'
                );
            }
        } else {
            $field[] = array(
                'id' => 'ave',
                'name' => '平均分'
            );
            $field[] = array(
                'id' => 'rank',
                'name' => $name
            );
            $mode = (int)$mode['star'];
            for ($i = 0; $i < $mode; $i++) {
                $k = $i + 1;
                $field[] = array(
                    'id' => 's' . (int)$k,
                    'name' => '星级' . (int)$k
                );
                $field[] = array(
                    'id' => 's' . 's' . (int)$k,
                    'name' => '星级' . (int)$k . '率'
                );
            }
        }

        $where = array(
            'r.evaluateId' => $evaId, 'r.scId' => $this->scId);
        $data = D('EvaluateRecord r')->join('mks_evaluate_map m ON r.id=m.recordId', 'Right')
            ->where($where)
            ->field('r.class,r.classId,r.grade,r.subject,r.name,r.userId,m.*')
            ->select();
        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $gradeMap = array();
        foreach ($grade as $k => $v) {
            $gradeMap[$v['name']] = $v['znName'];
        }
        $res = array();
        $index = array();
        $rate = array();
        foreach ($data as $k => $v) {
            if (!$res[$v['userId']]) {
                $res[$v['userId']] = array(
                    'class' => $v['class'],
                    'classId' => $v['classId'],
                    'grade' => $gradeMap[$v['grade']],
                    'subject' => $v['subject'],
                    'name' => $v['name'],
                    'total' => 0
                );
            }
            $res[$v['userId']]['total']++; //参评人数
            if ($eva['mode'] == 1) { //分数
                $res[$v['userId']]['totalScore'] += (int)$v['value'];
                $index[$v['userId']][] = (int)$v['value']; //用于去除最高分和最低分

            } else { //字段和星级
                $key = $v['property'];
                $index[$v['userId']][] = $key;
                $res[$v['userId']][$key] += (int)$v['value'];
                $temp = (int)substr($key, -1); //倍数
                $res[$v['userId']]['totalScore'] += (int)$v['value'] * $temp;

                $anotherKey = substr($v['property'], 0, 1) . $v['property']; //求率
                $rate[$v['userId']][] = $anotherKey;
            }
        }
        //去掉最高分最低分
        $max = (int)$eva['max'];
        $min = (int)$eva['min'];
        foreach ($index as $k => $v) {
            $temp = $v;
            rsort($temp); //降序
            $res[$k]['real'] = $res[$k]['total']; //参与计算人数
            $res[$k]['real'] -= $max;
            for ($i = 0; $i < $max; $i++) {
                if ($eva['mode'] == 1) {
                    $res[$k]['totalScore'] -= (int)array_splice($temp, 0, 1);
                } else {
                    $bar = array_splice($temp, 0, 1);
                    $score = (int)substr($bar[0], -1);
                    $res[$k]['totalScore'] -= $score;
                }
            }
            $res[$k]['real'] -= $min;
            for ($i = 0; $i < $min; $i++) {
                if ($eva['mode'] == 1) {
                    $bar = array_splice($temp, -1);
                    $res[$k]['totalScore'] -= (int)$bar[0];

                } else {
                    $bar = array_splice($temp, -1);
                    $score = (int)substr($bar[0], -1);
                    $res[$k]['totalScore'] -= $score;
                }
            }
        }

        //排名
        uasort($res, function ($a, $b) {
            $al = (int)$a['totalScore'];
            $bl = (int)$b['totalScore'];
            if ($al == $bl)
                return 0;
            return ($al > $bl) ? -1 : 1; //降序
        });

        //分班
        $map = array();
        foreach ($res as $k => $v) {
            $map[$v['classId']][] = $k;
        }
        $result = array();
        foreach ($res as $k => $v) {
            if ($k == $this->uId) {
                $result[$k] = $v;
                if ($v['real'] < 1) {
                    $result[$k]['ave'] = 0;
                } else {
                    $result[$k]['ave'] = round($v['totalScore'] / $v['real']);
                }
                $result[$k]['rank'] = array_search($k, $map[$v['classId']]) + 1;
                if ($mode != 1) {
                    $temp = array_unique($rate[$k]);
                    foreach ($temp as $k1 => $v1) {
                        $k1 = substr($v1, 1);
                        $result[$k][$v1]
                            = (sprintf("%.2f", $result[$k][$k1] / $result[$k]['total']) * 100) . '%';
                    }
                }
            }
        }

        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            $preField = array(
                'grade' => '年级',
                'class' => '班级',
                'name' => '教师',
                'subject' => '科目',
                'total' => '参评人数'
            );

            foreach ($field as $k => $v) {
                $preField[$v['id']] = $v['name'];
            }

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
            $index = 65;
            $map = array();
            foreach ($preField as $k => $v) {
                $str = chr($index);
                $map[$str] = $k;
                $index++;
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($str . '1', $v);
            }
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            foreach ($result as $k => $v) {
                $num = $k + 1;
                foreach ($map as $k1 => $v1) {
                    $temp = $v[$v1];
                    if (empty($v[$v1]))
                        $temp = 0;
                    $objPHPExcel->setActiveSheetIndex(0)
                        //Excel的第A列，uid是你查出数组的键值，下面以此类推
                        ->setCellValue($k1 . $num, $temp);
                }
            }

            $objPHPExcel->getActiveSheet()->setTitle('评教数据分析表');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '评教数据分析表' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        if (!$result) {
            $this->ajaxReturn($response);
        }
        $result = array_values($result);
        $response['status'] = 1;
        $response['field'] = $field;
        $response['data'] = $result;
        $this->ajaxReturn($response);

    }
}