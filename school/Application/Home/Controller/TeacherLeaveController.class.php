<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/8/8
 * Time: 14:19
 */

namespace Home\Controller;

use Home\Model\ProcessModel;
use Home\Common\BaseProcess;
use PHPExcel;
use PHPExcel_IOFactory;

ob_end_clean();
//权限已设置
class TeacherLeaveController extends Base
{
    protected $leave;
    protected $scId;
    protected $uid;
    protected $roleId;
    protected $user;


    public function __construct()
    {
        parent::__construct();
        $scId = $_SESSION['loginCheck']['data']['scId'];
        $uid = $_SESSION['loginCheck']['data']['userId'];
        $this->roleId = $_SESSION['loginCheck']['data']['roleId'];
/*
        $scId = 2;
        $uid = 1;*/
        $this->scId = $scId;
        $this->uid = $uid;
        $this->leave = new BaseProcess($scId, $uid, 1, 'TeacherLeave');
        $this->user = D('User')->where(array('id' => $this->uid, 'scId' => $this->scId))->find();
    }

    public function getName()
    {
        $process = new ProcessModel();
        $name = $process->getName(1, $this->scId);
        $this->ajaxReturn($name);
    }

    private function exceedTime($kind)
    {
        $model=$this->leave;
        $type = $model->processType;
        $rs = false;
        if($kind=='proposer'){
            $where=array(
                'scId' => $this->scId,
                'status' => 0
            );
            if ($this->roleId != $this::$adminRoleId) {
                $where['proposerId']=$this->uid;

            }
            $ids = $model->model->where($where)->field('id')->select();
            if (!empty($ids)) {
                $ids = array_map(function ($v) {
                    return (int)$v['id'];
                }, $ids);
                $rs = $model->aotuOverdue($ids, $type, $kind, $this->scId);//将申请的过期未审批的置为过期
            }
        }else{
            $rs = $model->aotuOverdue($this->uid, $type, $kind, $this->scId);//将申请的过期未审批的置为过期
        }

        if ($rs) {
            $map['id'] = array('in', $rs);
            $model->model->where($map)->data(array('status' => 2))->save();
        }
    }

    private function time($stime,$etime){
        $time=$etime-$stime;
        if($time<=28800){
            return round($time/3600,1);
        }elseif(28800<$time&&$time<=86400){
            return 8;
        }else{
            $time=(intval($time/ 86400))*8;
            $s0time=strtotime(date('Y-m-d',$stime));
            $e0time=strtotime(date('Y-m-d',$etime));
            return $time+abs($this->time($etime,$e0time)-$this->time($stime,$s0time));
        }
    }

    private function info($id,$log=false){
        $response=array(
            'status'=>0,
            'data'=>array()
        );
        $where = array(
            't.id' => $id,
            't.scId' => $this->scId,
            'pd.processType' => $this->leave->processType,
            'pd.scId' => $this->scId
        );
        if($log){
            $where['t.proposerId']=$this->uid;
        }

        $data = D('TeacherLeave t')
            ->join('mks_process_detail pd ON t.id=pd.relationId', 'LEFT')
            ->where($where)->field('t.*,pd.approver,pd.step,pd.opinion,pd.result,pd.approveTime')
            ->select();

        if (!$data) {
            $this->ajaxReturn($response);
        }
        $rs = array();
        foreach ($data as $k => $v) {
            if (!isset($rs[$v['id']]))
                $rs[$v['id']] = array(
                    'id' => $v['id'],
                    'title' => $v['title'],
                    'name' => $v['name'],
                    'proposer' => $v['proposer'],
                    'startTime' => date('Y-m-d H:i', $v['startTime']),
                    'endTime' => date('Y-m-d H:i', $v['endTime']),
                    'duration' => $v['duration'],
                    'reason' => $v['reason'],
                    'status'=>$v['status'],
                    'createTime' => date('Y-m-d H:i:s', $v['createTime']),
                    'process' => array()
                );
            if(!isset($rs[$v['id']]['process'][$v['step']])){
                $rs[$v['id']]['process'][$v['step']]=array(
                    'name'=>$v['step'],
                    'child'=>array()
                );
            }
            $rs[$v['id']]['process'][$v['step']]['child'][] = array(
                'approve' => $v['approver'],
                'opinion' => $v['opinion'],
                'result' => $v['result'],
                'approveTime' => !empty($v['approveTime'])?date('Y-m-d H:i:s', $v['approveTime']):''
            );
        }
        foreach ($rs as $k=>$v){
            sort($rs[$k]['process']);
        }
        sort($rs);
        $response['status'] = 1;
        $response['data'] = $rs;
        $this->ajaxReturn($response);
    }

    /*新建请假*/ //已测试
    public function create()
    {
        $response = array(
            'msg' => '',
            'status' => 0
        );
        $auth_type = $_POST['type'];
        if ($auth_type == 'create') {
            $data = I('post.');
            //得到相应的流程
            $pname = $data['name'];
            $process = $this->leave->getProcess($this->leave->processType, $pname, $this->scId);
            if (!$process) {
                $response['msg'] = '请假类型不存在或者流程不存在';
                $this->ajaxReturn($response);
            }
            $stime=strtotime($data['startTime']);
            $etime=strtotime($data['endTime']);
            $time=$this->time($stime,$etime);
            $application = array(
                'title' => $data['title'],
                'name' => $data['name'],
                'startTime' => strtotime($data['startTime']),
                'endTime' => strtotime($data['endTime']),
                'duration' => $time,
                'reason' => $data['reason'],
                'createTime' => time(),
                'proposerId' => $this->uid,
                'processId' => $process['id'],
                'proposer' => $this->user['name'],
                'scId' => $this->scId,
                'status' => 0
            );
            $result = $this->leave->create($application, false);
            if (!$result['status']) {
                $response['msg'] = $result['msg'];
                $this->ajaxReturn($response);
            }
            //存入流程设置
            $rs = $this->leave->createDetail($result, $process, $this->leave->processType, $this->scId);
            if (!$rs) {
                $response['msg'] = '保存流程失败';
                $this->ajaxReturn($response);
            }
            $response['msg'] = '申请成功';
            $response['status'] = 1;
            $this->ajaxReturn($response);
        }
        $response['msg'] = '无权限';
        $this->ajaxReturn($response);
    }

    /*请假记录*/ //已测试
    public function lists()
    {
        $response = array(
            'data' => array(),
            'status' => -1
        );
        $this->exceedTime('proposer');
        $type = $_POST['type'];
        if (isset($type)) {
            if ($type == 'cancel') { //撤销
                $id = I('post.id');
                $data = array(
                    'status' => 3,
                    'cancelTime' => time()
                );
                $rs = D('TeacherLeave')->where(array('id' => $id))->save($data);
                if (!$rs) {
                    $response['msg'] = '撤销失败';
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '撤销成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($type == 'detail') { //详情
                $id = $_POST['id'];
                $this->info($id,true);
            } else {

            }
        }

        $where = array(
            'scId' => $this->scId,
            'proposerId'=>$this->uid
        );
        //导出
        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            error_reporting(E_ALL);
            date_default_timezone_set('Asia/Hong_Kong');
            Vendor('PHPExcel.PHPExcel');
            $data = D('TeacherLeave')
                ->where($where)
                ->select();
            foreach ($data as &$v) {
                if ($v['ifApprove'] == 0) {
                    $v['status'] = 9;//还没有人审批过 0正在审批中
                }}
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("智慧校园")
                ->setLastModifiedBy("智慧校园")
                ->setTitle("数据EXCEL导出")
                ->setSubject("数据EXCEL导出")
                ->setDescription("备份数据")
                ->setKeywords("excel")
                ->setCategory("result file");
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '标题')
                ->setCellValue('B1', '类型')
                ->setCellValue('C1', '创建日期')
                ->setCellValue('D1', '审批状态');
            foreach ($data as $k => $v) {
                if ($v['status'] == -1) {
                    $status = '未通过';
                } elseif ($v['status'] == 0) {
                    $status = '正在审批';
                } elseif ($v['status'] == 1) {
                    $status = '通过';
                } elseif ($v['status'] == 2) {
                    $status = '审批过期';
                } elseif ($v['status'] == 9){
                    $status = '待审批';
                }else {
                    $status = '撤回';
                }
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['title'])
                    ->setCellValue('B' . $num, $v['name'])
                    ->setCellValue('C' . $num, date('Y-m-d H:i:s', $v['createTime']))
                    ->setCellValue('D' . $num, $status);
            }
            $objPHPExcel->getActiveSheet()->setTitle('请假记录表');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '请假记录表' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        if (!empty($_POST['time'])) {
            $temp=86399+(int)strtotime($_POST['time']);
            $where['startTime'] =array('elt',$temp);
            $where['endTime'] =array('egt',strtotime($_POST['time']));
        }

        $order = $_POST['order'];
        if (!isset($order))
            $order = 'createTime desc';

        $key = $_POST['key'];
        if (!empty($key))
            $where['name|title'] = array('like', "%{$key}%");


        $data = D('TeacherLeave')
            ->where($where)
            ->order($order)
            ->limit($limit_page)
            ->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }
        foreach ($data as &$v) {
            if ($v['ifApprove'] == 0) {
                $v['status'] = 9;//还没有人审批过 0正在审批中
            }
            $v['createTime'] = date('Y-m-d H:i:s', $v['createTime']);
            $v['startTime'] = date('Y-m-d H:i', $v['startTime']);
            $v['endTime'] = date('Y-m-d H:i', $v['endTime']);
        }
        $total = (int)D('TeacherLeave')
            ->where($where)
            //->field('id,title,name,createTime,status')
            ->count();
        $response['total'] = $total;
        $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        $response['data'] = $data;
        $response['status'] = 1;
        $this->ajaxReturn($response);
    }

    /*请假审批*/ //已测试
    public function approve($whether)
    {
        $response = array(
            'status' => -1,
            'data' => array()
        );
        $uid = $this->uid;
//过期
        $this->exceedTime('approver');
        // 通过或拒绝审批

        $operate = $_POST['type'];
        if (isset($operate)) {
            $opinion = I('post.opinion');
            $id = I('post.id');
            if ($operate == 'pass') {
                $operate = 1;
            } elseif ($operate == 'reject') {
                $operate = 2;
            }elseif ($operate=='detail'){
                $id =I('post.id');
                $this->info($id);
            }
            $result = $this->leave->operate($id, $this->leave->processType, $operate, $this->scId, $this->uid, $opinion);
            if ($result) {
                $response['msg'] = '操作成功';
                $this->leave->model->where(array('id'=>$id,'ifApprove'=>0))->data(array('ifApprove'=>1))->save();
                if ($result['status'] != 0)
                    $this->leave->model->where(array('id'=>$result['id']))->
                    data(array('status' => $result['status'], 'lastRecordTime' => time()))->save();
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '操作失败';
                $response['status'] = -1;
                $this->ajaxReturn($response);
            }
        }

        if ($whether == 1) {
            $map = array(
                'result' => array('in',array(2,5)),
                'scId' => $this->scId,
                'approveId' => $uid,
                'processType' => $this->leave->processType
            );
        } elseif ($whether == 2) {
            $map = array(
                'approveId' => $uid,
                'result' => array('in', array(1, -1)),
                'scId' => $this->scId,
                'processType' => $this->leave->processType
            );
        }

        $data = $this->leave->detailModel->where($map)->field('relationId,timeLine,approveId')->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }

        $whe=(int)$whether;
        $timeMap=array();
        foreach ($data as $k=>$v){
            $timeMap[$v['relationId']][$v['approveId']]=$v['timeLine']>0?date('Y-m-d H:i:s',$v['timeLine']):'-';
        }

        $relationIds = array_map(function ($v) {
            return $v['relationId'];
        }, $data);
        $condition = array(
            'scId' => $this->scId,
            'id' => array('in', $relationIds),
            'status'=>$whe>1?array('neq',3):array('in',array(0,2))
        );

        //导出

        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            $data = $this->leave->model->where($condition)->select();

            foreach ($data as $k=>$v){
                if ($v['ifApprove'] == 0) {
                    $data[$k]['status'] = 9;//还没有人审批过 0正在审批中
                }}
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
                ->setCellValue('A1', '标题')
                ->setCellValue('B1', '类型')
                ->setCellValue('C1', '开始时间')
                ->setCellValue('D1', '请假时长(小时)')
                ->setCellValue('E1', '请假原因')
                ->setCellValue('F1', '申请人')
                ->setCellValue('G1', '创建日期');
            foreach ($data as $k => $v) {
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['title'])
                    ->setCellValue('B' . $num, $v['name'])
                    ->setCellValue('C' . $num, date('Y-m-d H:i', $v['startTime']))
                    ->setCellValue('D' . $num, $v['duration'])
                    ->setCellValue('E' . $num, $v['reason'])
                    ->setCellValue('F' . $num, $v['proposer'])
                    ->setCellValue('G' . $num, date('Y-m-d H:i:s', $v['createTime']));
            }
            $objPHPExcel->getActiveSheet()->setTitle('请假审批表');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '请假审批表' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        //搜索
        $key = $_POST['key'];
        if ($key) {
            $condition['title|reason|name'] = array('like',"%{$key}%");
        }

        if (!empty($_POST['startTime']) && !empty($_POST['endTime'])) {
            $condition['createTime'] =
                array('between', array(strtotime($_POST['startTime']), strtotime($_POST['endTime'])));
        }

        $order = $_POST['order'];
        if (!isset($order))
            $order = 'createTime desc';

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $data = $this->leave->model->where($condition)->order($order)->limit($limit_page)->select();

        foreach ($data as $k=>$v){
            if ($v['ifApprove'] == 0) {
                $data[$k]['status'] = 9;//还没有人审批过 0正在审批中
            }
            $data[$k]['createTime']=date('Y-m-d H:i:s',$v['createTime']);
            $data[$k]['startTime']=date('Y-m-d H:i',$v['startTime']);
            $data[$k]['endTime']=date('Y-m-d H:i',$v['endTime']);
            $data[$k]['exceed']=
                !empty($timeMap[$v['id']][$this->uid])?$timeMap[$v['id']][$this->uid]:'-';
        }

        if ($data) {
            $total = (int)$this->leave->model->where($condition)->count();
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['data'] = $data;
            $response['status'] = 1;
        }
        $this->ajaxReturn($response);
    }

    /*请假统计*/
    public function statistics()
    {

        $map = array(
            'scId' => $this->scId
        );
        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $key = I('post.key');
        $stime = strtotime($_POST['startTime']);
        $etime = strtotime($_POST['endTime']) + 86399;
        if (!empty($stime) && !empty($etime)) {
            $map['createTime'] = array('between', array($stime, $etime));
        }

        $order = 'createTime desc';
        $limit = $_POST['order'];
        if (!empty($limit))
            $order = $limit;

        if (!empty($key)) {
            $map['_string'] = "(proposer like '%{$key}%')";
        }
        $map['status']=1;
        $data['data'] = $this->leave->model->where($map)->field('id,proposerId,proposer,count(id) as times,sum(duration) as totalDays')
            ->group('proposerId')->order($order)->limit($limit_page)->select();
        $total = $this->leave->model->where($map)->field('count(*)')->group('proposerId')->select();

        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            error_reporting(E_ALL);
            date_default_timezone_set('Asia/Hong_Kong');
            Vendor('PHPExcel.PHPExcel');
            $data = $this->leave->model->where($map)->field('id,proposerId,proposer,count(id) as times,sum(duration) as totalDays')
                ->group('proposerId')->order($order)->select();
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
                ->setCellValue('B1', '总计次数')
                ->setCellValue('C1', '总计天数');
            foreach ($data as $k => $v) {
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['proposer'])
                    ->setCellValue('B' . $num, $v['times'])
                    ->setCellValue('C' . $num, $v['totalDays']);
            }
            $objPHPExcel->getActiveSheet()->setTitle('请假统计表');
            $objPHPExcel->setActiveSheetIndex(0);
            // header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '请假统计表' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }


        $total = count($total);
        $data['total'] = $total;
        $data['maxPage'] = ceil($total / $count) > 0 ? 1 : ceil($total / $count);
        $data['status'] = 1;
        $this->ajaxReturn($data);
    }

    /*请假明细*/
    public function detail()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $map = array(
            'scId' => $this->scId,
        );

        $type=$_POST['type'];
        if(isset($type)){
            if($type=='detail'){
                $id = I('post.id');;
                $this->info($id);
            }
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $order = $_POST['order'];
        if (!isset($order))
            $order = 'createTime desc';

        if (!empty($_POST['startTime']) && !empty($_POST['endTime'])) {
            $map['createTime'] =
                array('between', array(strtotime($_POST['startTime']), strtotime($_POST['endTime'])));
        }

        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            error_reporting(E_ALL);
            date_default_timezone_set('Asia/Hong_Kong');
            Vendor('PHPExcel.PHPExcel');
            $data = D('TeacherLeave')
                ->where($map)
                ->select();
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("智慧校园")
                ->setLastModifiedBy("智慧校园")
                ->setTitle("数据EXCEL导出")
                ->setSubject("数据EXCEL导出")
                ->setDescription("备份数据")
                ->setKeywords("excel")
                ->setCategory("result file");
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '申请人')
                ->setCellValue('B1', '标题')
                ->setCellValue('C1', '类型')
                ->setCellValue('D1', '开始时间')
                ->setCellValue('E1', '结束时间')
                ->setCellValue('F1', '请假时长(小时)')
                ->setCellValue('G1', '请假原因')
                ->setCellValue('H1', '审批状态');
            foreach ($data as $k => $v) {
                if ($v['status'] == -1) {
                    $status = '未通过';
                } elseif ($v['status'] == 0) {
                    $status = '待审批';
                } elseif ($v['status'] == 1) {
                    $status = '通过';
                } elseif ($v['status'] == 2) {
                    $status = '审批过期';
                }  elseif ($v['status'] == 3) {
                    $status = '已撤销';
                }else {
                    $status = '取消申请';
                }
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['proposer'])
                    ->setCellValue('B' . $num, $v['title'])
                    ->setCellValue('C' . $num, $v['name'])
                    ->setCellValue('D' . $num, date('Y-m-d H:i', $v['startTime']))
                    ->setCellValue('E' . $num, date('Y-m-d H:i', $v['endTime']))
                    ->setCellValue('F' . $num, $v['duration'])
                    ->setCellValue('G' . $num, $v['reason'])
                    ->setCellValue('H' . $num, $status);
            }
            $objPHPExcel->getActiveSheet()->setTitle('请假明细表');
            $objPHPExcel->setActiveSheetIndex(0);
            //header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '请假明细表' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        $data = D('TeacherLeave')
            ->where($map)
            ->order($order)
            ->limit($limit_page)
            ->select();

        if (!$data) {
            $this->ajaxReturn($response);
        }

        foreach ($data as &$v) {
            if ($v['ifApprove'] == 0) {
                $v['status'] = 9;//还没有人审批过 0正在审批中
            }
            $v['startTime']=date('Y-m-d H:i',$v['startTime']);
            $v['endTime']=date('Y-m-d H:i',$v['endTime']);
            $v['createTime']=date('Y-m-d H:i:s',$v['createTime']);
        }
        $response['data'] = $data;
        $total = (int)$this->leave->model->where($map)->count();
        $response['total'] = $total;
        $response['status'] = 1;
        $response['maxPage'] = ceil($total / $count) > 0 ? 1 : ceil($total / $count);
        $this->ajaxReturn($response);

    }

}
