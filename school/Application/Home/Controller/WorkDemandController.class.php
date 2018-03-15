<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/8/9
 * Time: 16:50
 */

namespace Home\Controller;

use Home\Common\BaseProcess;
use Home\Model\ProcessModel;
use Home\Common\Accessory;
use Home\Common\Import;
use PHPExcel;
use PHPExcel_IOFactory;

ob_end_clean();

//权限已设置
class WorkDemandController extends Base
{
    protected $doc;
    protected $car;
    protected $place;
    protected $scId;
    protected $uid;
    protected $user;
    protected $roleId;

    /******************************************公文流转功能****************************************/
    public function __construct()
    {
        parent::__construct();
        $scId = $_SESSION['loginCheck']['data']['scId'];
        $uid = $_SESSION['loginCheck']['data']['userId'];
        $this->roleId = $_SESSION['loginCheck']['data']['roleId'];
        /*
                $this->roleId = 22;
                $uid = 1;
                $scId = 2;*/
        $this->scId = $scId;
        $this->uid = $uid;
        $this->user = D('User')->where(array('id' => $this->uid, 'scId' => $this->scId))->find();
        $this->doc = new BaseProcess($scId, $uid, 4, 'Document');
        $this->car = new BaseProcess($scId, $uid, 2, 'CarApplication');
        $this->place = new BaseProcess($scId, $uid, 3, 'PlaceApplication');
    }

    private function exceedTime($kind, $which)
    {
        switch ($which) {
            case 'doc':
                $model = $this->doc;
                break;
            case 'car':
                $model = $this->car;
                break;
            case 'place':
                $model = $this->place;
                break;
            default:
                $model = null;
                break;
        }
        $type = $model->processType;
        $rs = false;
        if ($kind == 'proposer') {
            $where = array(
                'scId' => $this->scId,
                'status' => 0
            );
            if ($this->roleId != $this::$adminRoleId) {
                $where['proposerId'] = $this->uid;

            }
            $ids = $model->model->where($where)->field('id')->select();
            if (!empty($ids)) {
                $ids = array_map(function ($v) {
                    return (int)$v['id'];
                }, $ids);
                $rs = $model->aotuOverdue($ids, $type, $kind, $this->scId);//将申请的过期未审批的置为过期
            }
        } else {
            $rs = $model->aotuOverdue($this->uid, $type, $kind, $this->scId);//将申请的过期未审批的置为过期
        }
        if ($rs) {
            $map['id'] = array('in', $rs);
            $model->model->where($map)->data(array('status' => 2))->save();
        }
    }

    private function info($id, $which, $log = false)
    {
        switch ($which) {
            case 'doc':
                $type = $this->doc->processType;
                $model = D('Document h');
                break;
            case 'car':
                $type = $this->car->processType;
                $model = D('CarApplication h');
                break;
            case 'place':
                $type = $this->place->processType;
                $model = D('PlaceApplication h');
                break;
            default:
                $type = null;
                $model = null;
                break;
        }

        $where = array(
            'h.id' => $id,
            'h.scId' => $this->scId,
            'pd.processType' => $type,
            'pd.scId' => $this->scId
        );
        if ($log) {
            $where['h.proposerId'] = $this->uid;
        }

        $data = $model
            ->join('mks_process_detail pd ON h.id=pd.relationId', 'LEFT')
            ->where($where)->field('h.*,pd.approver,pd.step,pd.opinion,pd.result,pd.approveTime')
            ->select();
        return $data;
    }

    public function getName($kind)
    { //已测试
        $process = new ProcessModel();
        $name = $process->getName($kind, $this->scId);
        $this->ajaxReturn($name);
    }

    //(新建公文)得到设置的人员id
    public function userId($name)
    {

        $map = array(
            'kind' => $this->doc->processType,
            'name' => $name,
            'scId' => $this->scId
        );
        $data = D('Process')->where($map)->getField('process');
        if (!$data)
            return array();
        $data = json_decode($data, true);
        $appId = array();
        foreach ($data as $k => $v) {
            foreach ($v['approveId'] as $k1 => $v1) {
                $appId[] = $v1;
            }
        }
        return $appId;
    }

    //(新建公文)待选人员
    public function userLists($isCommon)
    {
        $response = array('status' => 0, 'data' => array());
        $field = 'id,name,post,department';
        if ($isCommon == 'Y') {
            $ids = array($this->uid);
            $accId = I('post.docId');
            if (!empty($accId)) {
                $temp = D('Document')->where(array('id' => $accId))->getField('proposerId');
                $ids[]=$temp;
            }
            $ids=array_unique($ids);
            $roleWhere=array(
                'scId'=>array(array('eq',$this->scId),array('eq',0),'or'),
                'roleId'=>array('not in',array(13,15,22)));
            $roleId=D('Role')->where($roleWhere)->field('roleId')->select();
            $roleId=array_map(function($v){
                return $v['roleId'];
            },$roleId);

            $where = array(
                'scId' => $this->scId,
                'roleId' => array('in', $roleId),
                'id' => array('not in', $ids),
                'state'=>1
            );
        } else {
            $id = $this->userId($_POST['name']);
            if (!$id) {
                $this->ajaxReturn($response);
            }
            $where = array(
                'scId' => $this->scId,
                'id' => array('in', $id),
                'state'=>1
            );
        }
        $data = D('User')->where($where)->field($field)->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }
        $res = array();
        if ($data) {
            foreach ($data as &$v) {
                $department = !empty($v['department']) ? $v['department'] : '其他职工';
                if (!isset($res[$v['post']][$department]))
                    $res[$v['post']][$department] = array();
                $res[$v['post']][$department][] = array(
                    'id' => $v['id'],
                    'name' => $v['name']
                );
            }
        }
        $response['status'] = 1;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //创建公文 //已测试
    public function createDoc()
    {
        $response = array(
            'status' => -1,
            'msg' => ''
        );
        $type = $_POST['type'];
        if (!$type || $type != 'create') {
            $response['msg'] = '没有权限创建';
            $this->ajaxReturn($response);
        } elseif ($type == 'create') {
            $data = I('post.');
            $isCommon = I('post.isCommon');
            if ($isCommon == 'N') { //非通用公文流转
                $pname = $_POST['name'];
                $process = $this->doc->getProcess($this->doc->processType, $pname, $this->scId);
                if (!$process) {
                    $response['msg'] = '公文类型不存在';
                    $this->ajaxReturn($response);
                }
            } else { //通用公文流转
                $process = array(
                    'id' => 0
                );
            }
            // 对上传附件进行封装处理
            $file = $_FILES;
            $upload = false;
            $aName = array();
            if ($file) {
                $aName = array();
                foreach ($file as &$v) {
                    foreach ($v['name'] as &$val) {
                        $aName[] = $val;
                    }
                }
                $subName = 'document';
                $upload = new Accessory($file, $this->scId, $subName);
            }
            $document = array(
                'proposer' => $this->user['name'],
                'proposerId' => $this->uid,
                'content' => $data['content'],
                'title' => $data['title'],
                'accessoryName' => implode(',', $aName),
                'name' => $data['name'],
                'processId' => $process['id'],
                'status' => 0,
                'isCommon' => $isCommon,
                'createTime' => time(),
                'scId' => $this->scId,
            );
            // 保存公文
            $res = $this->doc->create($document, $upload);

            if (!$res['status']) {
                $response['msg'] = $res['msg'];
                $this->ajaxReturn($response);
            }
            //存入流程设置
            if ($isCommon == 'N') {
                $rs = $this->doc->createDetail($res, $process, $this->doc->processType, $this->scId);
            } else {
                $detail = array(
                    'relationId' => $res['status'],
                    'processType' => $this->doc->processType,
                    'approveId' => $data['aId'],
                    'approver' => $data['aName'],
                    'limitation' => 0,
                    'parentId' => 0,
                    'step' => 1,
                    'totalStep' => 1,
                    'timeLine' => 0,
                    'result' => 5,
                    'scId' => $this->scId
                );
                $rs = D('ProcessDetail')->add($detail);
            }

            if (!$rs) {
                $response['msg'] = '保存流程失败';
                $this->ajaxReturn($response);
            }
            $response['msg'] = '创建成功';
            $response['status'] = 1;
            $this->ajaxReturn($response);
        }
    }

    //公文流转记录 // 已测试
    public function logDoc()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $this->exceedTime('proposer', 'doc');
        $type = $_REQUEST['type'];

        $docId = I('request.id');
        if (isset($type)) {
            if ($type == 'edit') {//已测试
                //附加是否修改
                $file = $_FILES;
                //var_dump($file);

                $accessory = new Accessory($file, $this->scId, 'document');
                $doc = $this->doc->model->where(array('id' => $docId))->find();
                $preFile = explode(',', $doc['accessory']);
                if (empty($doc['accessory']))
                    $preFile = array();
                //附件是否增减
                $temp = I('post.upload');
                $upload = empty($temp) ? array() : explode(',', $temp);
                $aName = I('post.aName');
                $nextFile = $accessory->update($preFile, $upload);
                if (isset($nextFile['status'])) {
                    $response['msg'] = $nextFile['msg'];
                    $this->ajaxReturn($response);
                }
                $newdoc = array(
                    'title' => $_POST['title'],
                    'content' => $_POST['content'],
                    'accessory' => implode(',', $nextFile),
                    'accessoryName' => $aName,
                    'lastRecordTime' => time()
                );

                $rs = $this->doc->model->where(array('id' => $docId))->data($newdoc)->save();
                if (!$rs) {
                    $response['mag'] = '编辑失败';
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '编辑成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($type == 'del') {//已测试
                $detailId = I('post.detailId');
                $con = array('id' => array('in', $docId));
                $rs = $this->doc->model->where($con)->delete();
                $result = $this->doc->detailModel->where(array(
                        'id' => array('in', $detailId),
                        'scId' => $this->scId,
                        'processType' => $this->doc->processType
                    )
                )->delete();
                if (!$rs || !$result) {
                    $response['msg'] = '删除出错';
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '删除成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($type == 'download') {
                $a = new Accessory('', $this->scId, 'document');
                $a->download(array($_REQUEST['acc']), array($_REQUEST['aNa']), true);
                die;
            } elseif ($type == 'detail') {
                $id = I('post.id');
                $data = $this->info($id, 'doc', true);
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
                            'content' => $v['content'],
                            'accessory' => empty($v['accessory']) ? array() : explode(',', $v['accessory']),
                            'accessoryName' => empty($v['accessoryName']) ? array() : explode(',', $v['accessoryName']),
                            'isCommon' => $v['isCommon'],
                            'status' => $v['status'],
                            'createTime' => date('Y-m-d H:i:s', $v['createTime']),
                            'process' => array()
                        );
                    if (!isset($rs[$v['id']]['process'][$v['step']])) {
                        $rs[$v['id']]['process'][$v['step']] = array(
                            'name' => $v['step'],
                            'child' => array()
                        );
                    }
                    $rs[$v['id']]['process'][$v['step']]['child'][] = array(
                        'approver' => $v['approver'],
                        'opinion' => $v['opinion'],
                        'result' => $v['result'],
                        'approveTime' => !empty($v['approveTime']) ? date('Y-m-d H:i:s', $v['approveTime']) : ''
                    );
                }
                foreach ($rs as $k => $v) {
                    sort($rs[$k]['process']);
                }
                sort($rs);
                $response['status'] = 1;
                $response['data'] = $rs;
                $this->ajaxReturn($response);
            }
        }

        //得到公文记录
        $where = array(
            'd.scId' => $this->scId,
            'd.proposerId' => $this->uid,
        );

        //导出
        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            unset($where['p.processType']);
            unset($where['p.step']);

            $model = D('Document d');

            $subQuery = $model->where($where)->buildSql();

            $where1 = array(
                'p.processType' => $this->doc->processType,
                'p.step' => array('EXP', "=(select max(step) from mks_process_detail where 
                processType={$this->doc->processType} and scId={$this->scId} and relationId=d.id and 
                (case when d.status= 0 then result=5 else result=d.status end))")
            );

            $docLog = $model
                ->table($subQuery . ' d')
                ->join('mks_process_detail p ON d.id=p.relationId', 'RIGHT')
                ->where($where1)
                ->field('d.*,p.id as detailId,p.approveId,p.approver,p.opinion,p.result,p.step,p.approveTime')
                ->order('d.createTime desc')
                ->select();
            foreach ($docLog as $k => $v) {
                $docLog[$k]['createTime'] = date('Y-m-d H:i:s', $v['createTime']);
                if ($v['result'] == 5 && $v['step'] == 1) {
                    $docLog[$k]['status'] = 9;//还没有人审批过 0正在审批中
                }
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
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '标题')
                ->setCellValue('B1', '类型')
                ->setCellValue('C1', '内容')
                ->setCellValue('D1', '创建日期')
                ->setCellValue('E1', '审批状态')
                ->setCellValue('F1', '审批结果')
                ->setCellValue('G1', '审批人');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            foreach ($docLog as $k => $v) {
                if ($v['status'] == -1) {
                    $status = '未通过';
                } elseif ($v['status'] == 0) {
                    $status = '待审批';
                } elseif ($v['status'] == 1) {
                    $status = '通过';
                } elseif ($v['status'] == 2) {
                    $status = '审批过期';
                } elseif ($v['status'] == 9) {
                    $status = '正在审批';
                }

                if ($v['result'] == -1) {
                    $result = '未通过';
                } elseif ($v['result'] == 0) {
                    $result = '未审批';
                } elseif ($v['result'] == 1) {
                    $result = '通过';
                } elseif ($v['result'] == 2) {
                    $result = '审批过期';
                } elseif ($v['result'] == 4) {
                    $result = '转发';
                } elseif ($v['result'] == 5) {
                    $result = '待审批';
                }

                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['title'])
                    ->setCellValue('B' . $num, $v['name'])
                    ->setCellValue('C' . $num, $v['content'])
                    ->setCellValue('D' . $num, date('Y-m-d H:i:s', $v['createTime']))
                    ->setCellValue('E' . $num, $status)
                    ->setCellValue('F' . $num, $result)
                    ->setCellValue('G' . $num, $v['approver']);
            }
            $objPHPExcel->getActiveSheet()->setTitle('公文流转记录');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '公文流转记录' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;

        }
        $key = I('post.key');
        if (!empty($key)) {
            $where['d.title|d.name'] = array('like', "%{$key}%");
        }

        if (!empty($_POST['startTime']) && !empty($_POST['endTime'])) {
            $where['d.createTime'] =
                array('between', array(strtotime($_POST['startTime']), strtotime($_POST['endTime'])));
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $order = I('post.order');
        if (empty($order))
            $order = 'd.createTime desc';

        $model = D('Document d');

        $subQuery = $model->where($where)->order($order)->limit($limit_page)->buildSql();

        $where1 = array(
            'p.processType' => $this->doc->processType,
            'p.step' => array('EXP', "=(select max(step) from mks_process_detail where 
                processType={$this->doc->processType} and scId={$this->scId} and relationId=d.id and 
                (case when d.status= 0 then (result=5 or result=4 )else result=d.status end))")
        );

        $docLog = $model
            ->table($subQuery . ' d')
            ->join('mks_process_detail p ON d.id=p.relationId', 'RIGHT')
            ->where($where1)
            ->field('d.*,p.id as detailId,p.approveId,p.approver,p.opinion,p.result,p.step,p.approveTime')
            ->order('d.createTime desc')
            ->select();

        if ($docLog) {
            $total = (int)D('Document d')
                ->where($where)
                ->count();

            foreach ($docLog as $k => $v) {
                $docLog[$k]['createTime'] = date('Y-m-d H:i:s', $v['createTime']);
                if ($v['result'] == 5 && $v['step'] == 1) {
                    $docLog[$k]['status'] = 9;//还没有人审批过 0正在审批中
                }
            }
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['status'] = 1;
            $response['data'] = $docLog;
        }
        $this->ajaxReturn($response);
    }

    //公文流转审批 // 已测试
    public function approveDoc($whether = 1)
    {

        $response = array(
            'status' => 0,
            'data' => array()
        );

        $uid = $this->uid;
        $this->exceedTime('approver', 'doc');

        // 通过或拒绝审批
        $operate = $_POST['type'];
        if (isset($operate)) {
            $docId = I('post.docId');
            $opinion = I('post.opinion');
            if ($operate == 'pass') {
                $isCommon = I('post.isCommon');
                if ($isCommon == 'Y') {
                    $next = I('post.next');
                    if (!empty($next)) {
                        $map = array(
                            'relationId' => $docId,
                            'processType' => $this->doc->processType,
                            'scId' => $this->scId,
                            'approveId' => $this->uid
                        );
                        $temp = D('ProcessDetail')->where($map)->find();
                        $change = array(
                            //'timeLine'=>$temp['limitation']+time(),
                            'opinion' => $_POST['opinion'],
                            'approveTime' => time(),
                            'result' => 1,
                        );
                        $rs = D('ProcessDetail')->where($map)->save($change);
                        $data = array(
                            'relationId' => $docId,
                            'processType' => $this->doc->processType,
                            'approveId' => $next['id'],
                            'approver' => $next['name'],
                            'limitation' => 0,
                            //'nextId' => '',
                            'parentId' => $this->uid,
                            'step' => (int)$temp['step'] + 1,
                            'totalStep'=>(int)$temp['step'] + 1,
                            'scId' => $this->scId,
                            'result' => 5,
                        );
                        $rs = D('ProcessDetail')->add($data);
                        if (!$rs) {
                            $response['msg'] = '操作失败';
                            $this->ajaxReturn($response);
                        }
                        $response['status'] = 1;
                        $response['msg'] = '操作成功';
                        $this->ajaxReturn($response);
                        exit;
                    } else {//提交并结束
                        $operate = 1;
                        //$this->doc->model->where("id={$result['id']}")->data(array('status' => $result['status']))->save();
                    }
                } else {
                    $operate = 1;
                }
            } elseif ($docId && $operate == 'reject') {
                $operate = 2;
            } elseif ($docId && $operate == 'transmit') {
                $next = I('post.next');
                if (empty($next)) {
                    $response['msg'] = '未设置下一审批人';
                    $this->ajaxReturn($response);
                } else {
                    $map = array(
                        'relationId' => $docId,
                        'processType' => $this->doc->processType,
                        'scId' => $this->scId,
                        'approveId' => $this->uid
                    );
                    $temp = D('ProcessDetail')->where($map)->find();
                    $change = array(
                        'opinion' => I('post.opinion'),
                        'approveTime' => time(),
                        'result' => 4,
                    );
                    $rs = D('ProcessDetail')->where($map)->save($change);
                    $data = array(
                        'relationId' => $docId,
                        'processType' => $this->doc->processType,
                        'approveId' => $next['id'],
                        'approver' => $next['name'],
                        'limitation' => 0,
                        //'nextId' => '',
                        'parentId' => $this->uid,
                        'step' => (int)$temp['step'] + 1,
                        'scId' => $this->scId,
                        'result' => 5,
                    );
                    $rs = D('ProcessDetail')->add($data);
                    if (!$rs) {
                        $response['msg'] = '操作失败';
                        $this->ajaxReturn($response);
                    }
                    $response['status'] = 1;
                    $response['msg'] = '操作成功';
                    $this->ajaxReturn($response);
                }
            } elseif ($operate == 'del') {
                $ids = I('post.ids');
                $con = array('id' => array('in', $ids));
                $rs = $this->doc->model->where($con)->delete();
                if (!$rs) {
                    $response['msg'] = '删除申请出错';
                    $this->ajaxReturn($response);
                }
                $result = $this->doc->detailModel->where(array(
                    'relationId' => array('in', $ids),
                    'processType' => $this->doc->processType,
                    'scId' => $this->scId))->delete();
                if (!$result) {
                    $response['msg'] = '删除流程出错';
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '删除成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($operate == 'detail') {
                $id = I('post.id');
                $data = $this->info($id, 'doc');

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
                            'content' => $v['content'],
                            'accessory' => empty($v['accessory']) ? array() : explode(',', $v['accessory']),
                            'accessoryName' => empty($v['accessoryName']) ? array() : explode(',', $v['accessoryName']),
                            'isCommon' => $v['isCommon'],
                            'status' => $v['status'],
                            'createTime' => date('Y-m-d H:i:s', $v['createTime']),
                            'process' => array()
                        );
                    if (!isset($rs[$v['id']]['process'][$v['step']])) {
                        $rs[$v['id']]['process'][$v['step']] = array(
                            'name' => $v['step'],
                            'child' => array()
                        );
                    }
                    $rs[$v['id']]['process'][$v['step']]['child'][] = array(
                        'approver' => $v['approver'],
                        'opinion' => $v['opinion'],
                        'result' => $v['result'],
                        'approveTime' => !empty($v['approveTime']) ? date('Y-m-d H:i:s', $v['approveTime']) : ''
                    );
                }
                foreach ($rs as $k => $v) {
                    sort($rs[$k]['process']);
                }
                sort($rs);
                $response['status'] = 1;
                $response['data'] = $rs;
                $this->ajaxReturn($response);
            }
            $result = $this->doc->operate($docId, $this->doc->processType, $operate, $this->scId, $this->uid, $opinion);
            if ($result) {
                $response['msg'] = '操作成功';
                if ($result['status'] != 0)
                    $this->doc->model->where(array('id' => $result['id']))->data(array('status' => $result['status']))->save();
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
                'result' => array('in', array(2, 5)),
                'scId' => $this->scId,
                'approveId' => $uid,
                'processType' => $this->doc->processType
            );
        } elseif ($whether == 2) {
            $map = array(
                'approveId' => $uid,
                'scId' => $this->scId,
                'processType' => $this->doc->processType,
                'result' => array('in', array(1, -1, 4))
            );
        }

        $temp = D('ProcessDetail')
            ->where($map)
            ->field("id,relationId,approveId,parentId")
            ->select();

        if (!$temp) {
            $this->ajaxReturn($response);
        }

        $relationIds = array_map(function ($v) {
            return $v['relationId'];
        }, $temp);
        $relationIds = array_unique($relationIds);

        $condition = array(
            'd.scId' => $this->scId,
            'd.id' => array('in', $relationIds),
        );

        //导出
        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            $model = D('Document d');

            $subQuery = $model->where($condition)->buildSql();

            $where1 = array(
                'p.processType' => $this->doc->processType,
                'p.scId' => $this->scId,
            );

            $docData = $model
                ->table($subQuery . ' d')
                ->join('mks_process_detail p ON d.id=p.relationId', 'LEFT')
                ->where($where1)
                ->field('d.*,p.id as detailId,p.approveId,p.parentId,p.approver,p.opinion,p.result,p.step,p.approveTime')
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
            $temp = $whether > 1 ? '审批人' : '上一审批人';
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '标题')
                ->setCellValue('B1', '类型')
                ->setCellValue('C1', '申请人')
                ->setCellValue('D1', $temp)
                ->setCellValue('E1', '审批结果')
                ->setCellValue('F1', '审批意见')
                ->setCellValue('G1', '创建时间')
                ->setCellValue('H1', '审批状态');

            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            foreach ($docData as $k => $v) {
                if ($v['status'] == -1) {
                    $status = '未通过';
                } elseif ($v['status'] == 0) {
                    $status = '待审批';
                } elseif ($v['status'] == 1) {
                    $status = '通过';
                } elseif ($v['status'] == 2) {
                    $status = '审批过期';
                } else {
                    $status = '取消申请';
                }

                if ($v['result'] == -1) {
                    $result = '未通过';
                } elseif ($v['result'] == 0) {
                    $result = '未审批';
                } elseif ($v['result'] == 1) {
                    $result = '通过';
                } elseif ($v['result'] == 2) {
                    $result = '审批过期';
                } elseif ($v['result'] == 4) {
                    $result = '转发';
                } elseif ($v['result'] == 5) {
                    $result = '待审批';
                }

                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['title'])
                    ->setCellValue('B' . $num, $v['name'])
                    ->setCellValue('C' . $num, $v['proposer'])
                    ->setCellValue('D' . $num, $v['approver'])
                    ->setCellValue('E' . $num, $result)
                    ->setCellValue('F' . $num, $v['opinion'])
                    ->setCellValue('G' . $num, date('Y-m-d H:i:s', $v['createTime']))
                    ->setCellValue('H' . $num, $status);
            }
            $objPHPExcel->getActiveSheet()->setTitle('公文流转审批');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '公文流转审批' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;

        }

        if (!empty($_POST['startTime']) && !empty($_POST['endTime'])) {
            $condition['d.createTime'] =
                array('between', array(strtotime($_POST['startTime']), strtotime($_POST['endTime'])));
        }

        $key = $_POST['key'];
        if (!empty($key)) {
            $condition['d.title|d.name|d.proposer'] = array('like', "%{$key}%");
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $order = $_POST['order'];
        if (empty($order))
            $order = 'd.createTime desc';

        $model = D('Document d');

        $subQuery = $model->where($condition)->order($order)->limit($limit_page)->buildSql();

        $where1 = array(
            'p.processType' => $this->doc->processType,
            'p.scId' => $this->scId,
        );

        $data = $model
            ->table($subQuery . ' d')
            ->join('mks_process_detail p ON d.id=p.relationId', 'LEFT')
            ->where($where1)
            ->field('d.*,p.id as detailId,p.approveId,p.parentId,p.approver,p.opinion,p.result,p.step,p.approveTime')
            ->select();

        /*        $sql="select d.*,p.id as detailId,p.approveId,p.approver,p.opinion,p.result,p.step,p.approveTime
                        from (select d.* from mks_document d  order by ".$order." limit ".$limit_page.") d
                          left join mks_process_detail p ON d.id=p.relationId";*/

        /*$data=M('')->query($sql);*/


        if ($data) {
            $total = count($relationIds);
            $rs = array();
            $processMap = array();
            $nextStep = array();
            foreach ($data as $k => $v) {
                if (!isset($rs[$v['id']]))
                    $rs[$v['id']] = array(
                        'id' => $v['id'],
                        'title' => $v['title'],
                        'name' => $v['name'],
                        'proposer' => $v['proposer'],
                        'content' => $v['content'],
                        'accessory' => empty($v['accessory']) ? array() : explode(',', $v['accessory']),
                        'accessoryName' => empty($v['accessoryName']) ? array() : explode(',', $v['accessoryName']),
                        'isCommon' => $v['isCommon'],
                        'status' => $v['status'],
                        'createTime' => date('Y-m-d H:i:s', $v['createTime']),
                    );
                $processMap[$v['id']][$v['step']] = array(
                    'approver' => $v['approver'],
                    'opinion' => $v['opinion'],
                    'result' => $v['result'],
                    'approveTime' => !empty($v['approveTime']) ? date('Y-m-d H:i:s', $v['approveTime']) : ''
                );
                if ($v['approveId'] == $this->uid) {
                    if ($v['step'] > 1) {
                        $step[$v['id']] = $v['step'] - 1;
                    } else { //没有上一个环节
                        $step[$v['id']] = 0;
                    }
                    $nextStep[$v['id']] = $v['step'];
                }
            }

            foreach ($rs as $k => $v) {
                if ($whether == 1) {
                    $s = $step[$v['id']];
                } else {
                    $s = $nextStep[$v['id']];
                }
                // $rs[$k]['nextApprove'] = $processMap[$id][$ns]['approver'];
                $rs[$k]['approver'] = $processMap[$v['id']][$s]['approver'];
                $rs[$k]['opinion'] = $processMap[$v['id']][$s]['opinion'];
                $rs[$k]['result'] = $processMap[$v['id']][$s]['result'];
                $rs[$k]['approveTime'] = $processMap[$v['id']][$s]['approveTime'];
            }
            sort($rs);

            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['data'] = $rs;
            $response['status'] = 1;
        }

        $this->ajaxReturn($response);
    }

    /******************************************用车申请功能****************************************/
    //新建用车申请 //已测试
    public function createCar()
    {
        $response = array(
            'status' => -1,
            'msg' => ''
        );
        $type = $_POST['type'];
        if (isset($type)) {
            if ($type != 'create') {
                $response['msg'] = '没有权限提交申请';
                $this->ajaxReturn($response);
            }
            $data = I('post.');
            $pname = $_POST['name'];
            $process = $this->car->getProcess($this->car->processType, $pname, $this->scId);

            if (!$process) {
                $response['msg'] = '用车申请类型不存在';
                $this->ajaxReturn($response);
            }

            $application = array(
                'title' => $data['title'],
                'name' => $data['name'],
                'carType' => $data['carType'],
                'carOwner' => $data['carOwner'],
                'users' => $data['users'],
                'destination' => $data['destination'],
                'proposer' => $this->user['name'],
                'proposerId' => $this->uid,
                'contactMan' => $data['contactMan'],
                'telephone' => $data['telephone'],
                'startTime' => strtotime($data['startTime']),
                'endTime' => strtotime($data['endTime']),
                'duration' => $data['duration'],
                'reason' => $data['reason'],
                'processId' => $process['id'],
                'status' => 0,
                'createTime' => time(),
                'scId' => $this->scId,
            );

            // 保存申请
            $res = $this->car->create($application, false);

            if (!$res['status']) {
                $response['msg'] = $res['msg'];
                $this->ajaxReturn($response);
            }
            //存入流程设置
            $rs = $this->car->createDetail($res, $process, $this->car->processType, $this->scId);
            if (!$rs) {
                $response['msg'] = '保存流程失败';
                $this->ajaxReturn($response);
            }
            $response['msg'] = '提交成功';
            $response['status'] = 1;
            $this->ajaxReturn($response);
        }
    }

    //用车申请记录 // 已测试
    public function logCar()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        $type = $_POST['type'];

        $this->exceedTime('proposer', 'car');

        $carId = I('post.id');
        if (isset($type)) {
            if ($type == 'del') {//已测试
                $con = array('id' => array('in', $carId));
                $rs = $this->car->model->where($con)->delete();
                $result = $this->car->detailModel->where(array(
                    'relationId' => array('in', $carId),
                    'processType' => $this->car->processType,
                    'scId' => $this->scId))->delete();
                if (!$rs || !$result) {
                    $response['msg'] = '删除出错';
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '删除成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($type == 'cancel') {
                $id = I('post.id');
                $data = array(
                    'status' => 3,
                    'cancelTime' => time()
                );
                $rs = $this->car->model->where(array('id' => $id))->save($data);
                if (!$rs) {
                    $response['msg'] = '撤销失败';
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '撤销成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($type == 'detail') { //详情
                $id = I('post.id');
                $data = $this->info($id, 'car', true);
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
                            'carType' => $v['carType'],
                            'carOwner' => $v['carOwner'],
                            'users' => $v['users'],
                            'destination' => $v['destination'],
                            'proposer' => $v['name'],
                            'proposerId' => $v['proposerId'],
                            'contactMan' => $v['contactMan'],
                            'telephone' => $v['telephone'],
                            'startTime' => date('Y-m-d H:i', $v['startTime']),
                            'endTime' => date('Y-m-d H:i', $v['endTime']),
                            'duration' => $v['duration'],
                            'reason' => $v['reason'],
                            'processId' => $v['processId'],
                            'status' => $v['status'],
                            'createTime' => date('Y-m-d H:i:s', $v['createTime']),
                            'scId' => $v['scId'],
                        );
                    if (!isset($rs[$v['id']]['process'][$v['step']])) {
                        $rs[$v['id']]['process'][$v['step']] = array(
                            'name' => $v['step'],
                            'child' => array()
                        );
                    }
                    $rs[$v['id']]['process'][$v['step']]['child'][] = array(
                        'approver' => $v['approver'],
                        'opinion' => $v['opinion'],
                        'result' => $v['result'],
                        'approveTime' => !empty($v['approveTime']) ? date('Y-m-d H:i:s', $v['approveTime']) : ''
                    );
                }
                foreach ($rs as $k => $v) {
                    sort($rs[$k]['process']);
                }
                sort($rs);
                $response['status'] = 1;
                $response['data'] = $rs;
                $this->ajaxReturn($response);
            } else {

            }
        }


        //得到申请记录
        $map = array(
            'scId' => $this->scId,
            'proposerId' => $this->uid,
        );

        //导出
        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            $carLog = D('CarApplication')
                ->where($map)
                ->order('createTime desc')
                ->select();
            foreach ($carLog as &$v) {
                if ($v['ifApprove'] == 0) {
                    $v['status'] = 9;//还没有人审批过 0正在审批中
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
                ->setCellValue('C1', '车辆类型')
                ->setCellValue('D1', '用车人数')
                ->setCellValue('E1', '开始日期')
                ->setCellValue('F1', '结束日期')
                ->setCellValue('G1', '目的地')
                ->setCellValue('H1', '用车联系人')
                ->setCellValue('I1', '用车联系电话')
                ->setCellValue('J1', '审批状态')
                ->setCellValue('K1', '创建日期');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);


            foreach ($carLog as $k => $v) {
                if ($v['status'] == -1) {
                    $status = '未通过';
                } elseif ($v['status'] == 0) {
                    $status = '正在审批';
                } elseif ($v['status'] == 1) {
                    $status = '通过';
                } elseif ($v['status'] == 2) {
                    $status = '审批过期';
                } elseif ($v['status'] ==9){
                    $status = '待审批';
                }else {
                    $status = '撤回';
                }
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['title'])
                    ->setCellValue('B' . $num, $v['name'])
                    ->setCellValue('C' . $num, $v['carType'])
                    ->setCellValue('D' . $num, $v['users'])
                    ->setCellValue('E' . $num, date('Y-m-d H:i:s', $v['startTime']))
                    ->setCellValue('F' . $num, date('Y-m-d H:i:s', $v['endTime']))
                    ->setCellValue('G' . $num, $v['destination'])
                    ->setCellValue('H' . $num, $v['contactMan'])
                    ->setCellValue('I' . $num, $v['telephone'])
                    ->setCellValue('J' . $num, $status)
                    ->setCellValue('K' . $num, date('Y-m-d H:i:s', $v['createTime']));
            }
            $objPHPExcel->getActiveSheet()->setTitle('用车申请记录');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '用车申请记录' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;

        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        if (!empty($_POST['startTime']) && !empty($_POST['endTime'])) {
            $map['createTime'] =
                array('between', array(strtotime($_POST['startTime']), strtotime($_POST['endTime'])));
        }

        $order = $_POST['order'];
        if (empty($order))
            $order = 'createTime desc';

        $key = $_POST['key'];
        if (!empty($key)) {
            $map['title|name|carType'] = array('like', "%{$key}%");
        }

        $carLog = D('CarApplication')
            ->where($map)
            ->order($order)
            ->limit($limit_page)
            ->select();

        if ($carLog) {
            foreach ($carLog as &$v){
                if ($v['ifApprove'] == 0) {
                    $v['status'] = 9;//还没有人审批过 0正在审批中
                }
                $v['createTime']=date('Y-m-d H:i:s',$v['createTime']);
                $v['startTime']=date('Y-m-d H:i',$v['startTime']);
                $v['endTime']=date('Y-m-d H:i',$v['endTime']);
            }
            $total = (int)D('CarApplication')->where($map)->count();
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['status'] = 1;
            $response['data'] = $carLog;
        }
        $this->ajaxReturn($response);
    }

    //用车申请审批
    public function approveCar($whether = 1)
    {
        $response = array(
            'status' => -1,
            'data' => array()
        );

        $uid = $this->uid;
        $this->exceedTime('approver', 'car');

        // 通过或拒绝审批
        $carId = I('post.carId');
        $operate = $_POST['type'];
        $opinion = I('post.opinion');
        if ($operate) {
            if ($carId && $operate == 'pass') {
                $operate = 1;
            } elseif ($carId && $operate == 'reject') {
                $operate = 2;
            } elseif ($operate == 'del') {
                $ids = I('post.ids');
                $con = array('id' => array('in', $ids));
                $rs = $this->car->model->where($con)->delete();
                if (!$rs) {
                    $response['msg'] = '删除申请出错';
                    $this->ajaxReturn($response);
                }
                $result = $this->car->detailModel->where(array(
                    'relationId' => array('in', $ids),
                    'processType' => $this->car->processType,
                    'scId' => $this->scId))->delete();
                if (!$result) {
                    $response['msg'] = '删除流程出错';
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '删除成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($operate == 'detail') { //详情
                $id = I('post.id');
                $data = $this->info($id, 'car');
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
                            'carType' => $v['carType'],
                            'carOwner' => $v['carOwner'],
                            'users' => $v['users'],
                            'destination' => $v['destination'],
                            'proposer' => $v['name'],
                            'proposerId' => $v['proposerId'],
                            'contactMan' => $v['contactMan'],
                            'telephone' => $v['telephone'],
                            'startTime' => date('Y-m-d H:i', $v['startTime']),
                            'endTime' => date('Y-m-d H:i', $v['endTime']),
                            'duration' => $v['duration'],
                            'reason' => $v['reason'],
                            'processId' => $v['processId'],
                            'status' => $v['status'],
                            'createTime' => date('Y-m-d H:i:s', $v['createTime']),
                            'scId' => $v['scId'],
                        );
                    if (!isset($rs[$v['id']]['process'][$v['step']])) {
                        $rs[$v['id']]['process'][$v['step']] = array(
                            'name' => $v['step'],
                            'child' => array()
                        );
                    }
                    $rs[$v['id']]['process'][$v['step']]['child'][] = array(
                        'approver' => $v['approver'],
                        'opinion' => $v['opinion'],
                        'result' => $v['result'],
                        'approveTime' => !empty($v['approveTime']) ? date('Y-m-d H:i:s', $v['approveTime']) : ''
                    );
                }
                foreach ($rs as $k => $v) {
                    sort($rs[$k]['process']);
                }
                sort($rs);
                $response['status'] = 1;
                $response['data'] = $rs;
                $this->ajaxReturn($response);
            }
            $result = $this->car->operate($carId, $this->car->processType, $operate, $this->scId, $this->uid, $opinion);

            if ($result) {
                $this->car->model->where(array('id'=>$carId,'ifApprove'=>0))->data(array('ifApprove'=>1))->save();
                $response['msg'] = '操作成功';
                if ($result['status'] != 0)
                    $this->car->model->where(array('id' => $carId))->data(array('status' => $result['status']))->save();
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
                'result' => array('in', array(2, 5)),
                'scId' => $this->scId,
                'approveId' => $uid,
                'processType' => $this->car->processType
            );
        } elseif ($whether == 2) {
            $map = array(
                'result' => array('in', array(1, -1)),
                'scId' => $this->scId,
                'approveId' => $uid,
                'processType' => $this->car->processType
            );
        }

        $data = $this->car->detailModel->where($map)->field('relationId,timeLine,approveId')->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }
        $timeMap = array();
        foreach ($data as $k => $v) {
            $timeMap[$v['relationId']][$v['approveId']] = $v['timeLine'] > 0 ? date('Y-m-d H:i:s', $v['timeLine']) : '-';
        }

        $relationIds = array_map(function ($v) {
            return $v['relationId'];
        }, $data);
        $relationIds = array_unique($relationIds);
        $condition = array(
            'scId' => $this->scId,
            'id' => array('in', $relationIds),
            'status' => $whether > 1 ? array('neq', 3) : array('in', array(0, 2))
        );

        //导出
        $download = $_REQUEST['export'];

        if ($download == 'ensure') {
            $carData = $this->car->model->where($condition)->order('createTime desc')->select();
            foreach ($carData as $k => $v) {
                if ($v['ifApprove'] == 0) {
                    $carData[$k]['status'] = 9;//还没有人审批过 0正在审批中
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
                ->setCellValue('C1', '车辆类型')
                ->setCellValue('D1', '用车人数')
                ->setCellValue('E1', '开始日期')
                ->setCellValue('F1', '结束日期')
                ->setCellValue('G1', '目的地')
                ->setCellValue('H1', '用车联系人')
                ->setCellValue('I1', '用车联系电话')
                ->setCellValue('J1', '审批状态')
                ->setCellValue('K1', '创建日期');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            foreach ($carData as $k => $v) {
                if ($v['status'] == -1) {
                    $status = '未通过';
                } elseif ($v['status'] == 0) {
                    $status = '待审批';
                } elseif ($v['status'] == 1) {
                    $status = '通过';
                } elseif ($v['status'] == 2) {
                    $status = '审批过期';
                } else {
                    $status = '取消申请';
                }
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['title'])
                    ->setCellValue('B' . $num, $v['name'])
                    ->setCellValue('C' . $num, $v['carType'])
                    ->setCellValue('D' . $num, $v['users'])
                    ->setCellValue('E' . $num, date('Y-m-d H:i:s', $v['startTime']))
                    ->setCellValue('F' . $num, date('Y-m-d H:i:s', $v['endTime']))
                    ->setCellValue('G' . $num, $v['destination'])
                    ->setCellValue('H' . $num, $v['contactMan'])
                    ->setCellValue('I' . $num, $v['telephone'])
                    ->setCellValue('J' . $num, $status)
                    ->setCellValue('K' . $num, date('Y-m-d H:i:s', $v['createTime']));
            }
            $objPHPExcel->getActiveSheet()->setTitle('用车申请审批记录');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '用车申请记录' . '.xls"');
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
            $condition['title|name'] = array('like', "%{$key}%");
        }
        if (!empty($_POST['startTime']) && !empty($_POST['endTime'])) {
            $condition['createTime'] =
                array('between', array(strtotime($_POST['startTime']), strtotime($_POST['endTime'])));
        }
        $order = $_POST['order'];
        if (empty($order))
            $order = 'createTime desc';

        $data = $this->car->model->where($condition)->order($order)->limit($limit_page)->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }

        foreach ($data as $k => $v) {
            if ($v['ifApprove'] == 0) {
                $data[$k]['status'] = 9;//还没有人审批过 0正在审批中
            }
            $data[$k]['createTime'] = date('Y-m-d H:i:s', $v['createTime']);
            $data[$k]['startTime'] = date('Y-m-d H:i', $v['startTime']);
            $data[$k]['endTime'] = date('Y-m-d H:i', $v['endTime']);
            $data[$k]['exceed'] =
                !empty($timeMap[$v['id']][$this->uid]) ? $timeMap[$v['id']][$this->uid] : '-';
        }
        $total = (int) $this->car->model->where($condition)->count();
        $response['total'] = $total;
        $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        $response['data'] = $data;
        $response['status'] = 1;
        $this->ajaxReturn($response);
    }


    /*****************************************场地申请功能*****************************************/
    //场地配置设置    //已测试
    public function placeOutfit()
    {
        $response = array(
            'status' => -1,
            'data' => ''
        );
        $type = $_POST['type'];
        if (isset($type)) {
            if ($type == 'create') {
                $outfit = array(
                    'name' => I('post.name'),
                    //'option' => implode(',', $_POST['option']),
                    'scId' => $this->scId
                );
                $rs = M('PlaceOutfit')->data($outfit)->add();
                if (!$rs) {
                    $response['msg'] = '添加配置错误';
                    $response['status'] = -1;
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '添加成功';
                $response['status']=1;
                $this->ajaxReturn($response);
            } elseif ($type == 'option') {
                $outfit = array(
                    'option' => implode(',', I('post.option')),
                );
                $rs = M('PlaceOutfit')->where(array('id' => I('post.id')))->data($outfit)->save();
                if (!$rs) {
                    $response['msg'] = '添加选项错误';
                    $response['status'] = -1;
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '添加成功';
                $response['status']=1;
                $this->ajaxReturn($response);
            } elseif ($type == 'del') {
                $ids = I('post.ids');
                if (!empty($ids)) {
                    $map = array(
                        'id' => array('in', $ids)
                    );
                    $rs = M('PlaceOutfit')->where($map)->delete();
                    if (!$rs) {
                        $response['msg'] = '删除配置错误';
                        $response['status'] = -1;
                        $this->ajaxReturn($response);
                    }
                    $response['msg'] = '删除成功';
                    $response['status']=1;
                    $this->ajaxReturn($response);
                }
            } else {
                $response['status'] = -1;
                $response['msg'] = '没有权限操作';
                $this->ajaxReturn($response);
            }
        }
        $data = M('PlaceOutfit')->where(array('scId' => $this->scId))->select();
        if ($data) {
            $response['status'] = 1;
            foreach ($data as $k => $v) {
                $data[$k]['option'] = explode(',', $v['option']);
            }
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);
    }

    //场地管理
    public function placeManage()
    {
        $response = array(
            'msg' => '',
            'status' => -1,
            'data' => array()
        );
        $type = $_REQUEST['type'];

        if (isset($type)) {
            if ($type == 'create') {
                $data = $_POST;
                $outfit = array(
                    'buildingName' => $data['buildingName'],
                    'buildingNumber' => $data['buildingNumber'],
                    'floor' => $data['floor'],
                    'room' => $data['room'],
                    'name' => $data['name'],
                    'status' => (int)$data['status'],
                    'scId' => $this->scId
                );
                $rs = M('Place')->data($outfit)->add();
                if (!$rs) {
                    $response['msg'] = '添加场地错误';
                    $response['status'] = -1;
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '添加成功';
                $outfit['id'] = $rs;
                //$response['id']=$rs;
                $response['data'] = $outfit;
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($type == 'del') {
                $id = I('post.id');
                if (!empty($id)) {
                    $map = array(
                        'id' => $id
                    );
                    $rs = M('Place')->where($map)->delete();
                    if (!$rs) {
                        $response['msg'] = '删除场地错误';
                        $response['status'] = -1;
                        $this->ajaxReturn($response);
                    }
                    $response['msg'] = '删除成功';
                    $response['status'] = 1;
                    $this->ajaxReturn($response);
                }
            } elseif ($type == 'edit') {
                $data = I('post.');
                $outfit = array(
                    'buildingName' => $data['buildingName'],
                    'buildingNumber' => $data['buildingNumber'],
                    'floor' => $data['floor'],
                    'room' => $data['room'],
                    'name' => $data['name'],
                    'status' => (int)$data['status'],
                );
                $rs = M('Place')->where(array('id' => I('post.id')))->data($outfit)->save();
                if (!$rs) {
                    $response['msg'] = '编辑错误场地错误';
                    $response['status'] = -1;
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '编辑成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($type == 'download') { //已测试
                $file = new Accessory('', $this->scId, 'download');
                $filename = array('download/placeManage.xlsx');
                $aName = array('场地管理模板.xlsx');
                $file->download($filename, $aName, true);
                die;
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
                $info = array();
                foreach ($data as $k => $v) {
                    $info[$v[4]] = array(
                        'buildingNumber' => $v[0],
                        'buildingName' => $v[1],
                        'floor' => $v[2],
                        'room' => $v[3],
                        'name' => $v[4],
                        'status' => ($v[5] == '是') ? 1 : 0,
                        'scId' => $this->scId
                    );
                }
                $newName = array_keys($info);
                $oldName = D('Place')->where(array('scId' => $this->scId))->field('id,name')->select();
                $oldName = array_map(function ($v) {
                    return $v['name'];
                }, $oldName);
                if (empty($oldName))
                    $oldName = array();
                $diff = array_intersect($newName, $oldName);
                if (!empty($diff)) {
                    $cacheName = 'place' . time();
                    S($cacheName, $info, 3600);
                    $response['name'] = $cacheName;
                    $response['msg'] = '场地名称重复，将用新记录覆盖旧记录';
                    $this->ajaxReturn($response);
                }
                $info = array_values($info);
                $rs = D('Place')->addAll($info);
                if (!$rs) {
                    $response['msg'] = '导入错误';
                    $response['status'] = -1;
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '导入成功';
                /*$outfit['id']=$rs;
                //$response['id']=$rs;
                $response['data']=$outfit;*/
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } else {
                $response['status'] = -1;
                $response['msg'] = '没有权限操作';
                $this->ajaxReturn($response);
            }
        }

        $where = array(
            'scId' => $this->scId
        );
        $key = $_POST['key'];
        if (isset($key)) {
            $where['buildingName|buildingNumber|floor|room|name']
                = array('like', "%{$key}%");
        }


        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $data = M('Place')->where($where)->limit($limit_page)->select();
        $total = (int)M('Place')->where($where)->count();
        if ($data) {
            $response['status'] = 1;
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);
    }

    //时间占用判断
    private function occupy($pid, $time)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'placeId' => $pid,
            // 'occupyDate'=>$time,
            'scId' => $this->scId,
            'status' => array('in', array(0, 1))
        );
        $data = D('PlaceApplication')->where($where)->field('occupyTime')->select();
        if (!$data) {
            return $response;
        } else {
            $occ = array(); //占用时间
            foreach ($data as $k => $v) {
                $temp = empty($v['occupyTime']) ? array() : explode(',', $v['occupyTime']);
                $occ = array_merge($occ, $temp);
            }
            $app = $time; //申请时间
            $in = array_intersect($app, $occ);
            if (!empty($in)) {
                $response['status'] = 1;
                $response['data'] = $in;
            }
            return $response;
        }
    }

    //场地申请 //已测试
    public function createPlace()
    {
        $response = array(
            'msg' => '',
            'status' => 0,
            'data' => ''
        );
        $type = $_POST['type'];
        if ($type == 'create') {
            $data = I('post.');
            $pname = $_POST['name'];
            $process = $this->doc->getProcess($this->place->processType, $pname, $this->scId);
            if (!$process) {
                $response['msg'] = '未查找到相关类型流程';
                $this->ajaxReturn($response);
            }
            $occupyTime = I('post.occupyTime');
            $res = $this->occupy(I('post.id'), $occupyTime);
            if ($res['status'] == 1) {
                $response['msg'] = '所选时间已被占用';
                $response['time'] = $response['data'];
                $this->ajaxReturn($response);
            }

            $application = array(
                'placeId' => $_POST['id'],
                'title' => $_POST['title'],
                'address' => $_POST['address'],
                'name' => $_POST['name'],
                'occupyTime' => implode(',', $_POST['occupyTime']),
                'principal' => $_POST['principal'],
                'proposerId' => $this->uid,
                'proposer' => $this->user['name'],
                'telephone' => $_POST['telephone'],
                'outfit' => implode(',', $_POST['outfit']),
                'explain' => $_POST['explain'],
                'createTime' => time(),
                'processId' => $process['id'],
                'status' => 0,
                'scId' => $this->scId
            );
            $application['occupyDate'] = array();
            if (!empty($occupyTime)) {
                $temp = array();
                foreach ($occupyTime as &$v) {
                    $date = stristr($v, ' ', true);
                    if (!in_array($date, $temp))
                        $temp[] = stristr($v, ' ', true);
                }
                $application['occupyDate'] = implode(',', $temp);
            }

            // 保存公文
            $res = $this->place->create($application, false);

            if (!$res['status']) {
                $response['msg'] = $res['msg'];
                $this->ajaxReturn($response);
            }

            //存入流程设置
            $rs = $this->place->createDetail($res, $process, $this->place->processType, $this->scId);
            if (!$rs) {
                $response['msg'] = '保存流程失败';
                $this->ajaxReturn($response);
            }
            $response['msg'] = '创建成功';
            $response['status'] = 1;
        } elseif ($type == 'look') {
            $id = I('post.id');
            $date = strtotime($_POST['date']);
            $date = date('Y-m-d', $date);
            $time_pre = strtotime($date);
            $map = array(
                'p.scId' => $this->scId,
                'p.placeId' => $id,
                'p.status' => array('in', array(0, 1))
            );
            $model = M('PlaceApplication p');
            $place = $model->where($map)
                ->field("id,occupyTime")->field('occupyTime')->select();

            if (!$place) {
                $this->ajaxReturn($response);
            }
            $temp = array();
            foreach ($place as $k => $v) {
                $occupyTime = empty($v['occupyTime']) ? array() : explode(',', $v['occupyTime']);
                foreach ($occupyTime as &$val) {
                    $bar = explode(' ', $val);
                    $key = $bar[0];
                    $value = $bar[1];
                    $time_next = strtotime($key);
                    if ($time_next == $time_pre) {
                        $temp[] = $value;
                    }
                }
            }
            sort($temp);
            $response['status'] = 1;
            $response['data'] = $temp;
            $this->ajaxReturn($response);

        }
        $date = strtotime($_POST['date']);
        $date = date('Y-m-d', $date);
        $time_pre = strtotime($date);

        $map = array(
            'p.status' => 1,
            'p.scId' => $this->scId,
            //'_string'=>"FIND_IN_SET('{$date}',a.occupyDate)"
        );
        $model = M('Place p');
        $place = $model->join("mks_place_application a ON p.id=a.placeId"/* AND
        (FIND_IN_SET('{$date}',a.occupyDate))"*/, 'LEFT')->where($map)
            ->field("p.*,group_concat(a.occupyTime) as occupyTime")->group('p.id')->select();

        foreach ($place as $k => $v) {
            if (!empty($v['occupyTime'])) {
                $temp = array();
                $temp2 = array();
                $h = empty($v['occupyTime']) ? array() : explode(',', $v['occupyTime']);
                foreach ($h as &$val) {
                    $bar = explode(' ', $val);
                    $key = $bar[0];
                    $value = $bar[1];
                    $time_next = strtotime($key);
                    if ($time_next == $time_pre) {
                        $temp[] = $value;
                    }
                    $temp2[] = $val;
                }
                $place[$k]['occupyTime'] = $temp;
                // $place[$k]['occupyTime'] = $temp2;
            }
        }

        $response['status'] = 1;
        $response['data'] = $place;
        $this->ajaxReturn($response);
    }

    //场地申请记录
    public function logPlace()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        $type = $_POST['type'];

        $this->exceedTime('proposer', 'place');

        if (isset($type)) {
            if ($type == 'del') {//已测试
                $placeIds = I('post.ids');
                $condition = array('id' => array('in', $placeIds));
                $rs = $this->place->model->where($condition)->delete();
                $result = $this->place->detailModel->where(array(
                    'relationId' => array('in', $placeIds),
                    'scId' => $this->scId,
                    'processType' => $this->place->processType,
                ))->delete();
                if (!$rs || !$result) {
                    $response['msg'] = '删除出错';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                $response['msg'] = '删除成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'cancel') {
                $id = $_POST['id'];
                $data = array(
                    'status' => 3,
                    'cancelTime' => time()
                );
                $rs = D('PlaceApplication')->where(array('id' => $id))->save($data);
                if (!$rs) {
                    $response['msg'] = '撤销失败';
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '撤销成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($type == 'detail') {
                $id = $_POST['id'];
                $data = $this->info($id, 'place', true);

                if (!$data) {
                    $this->ajaxReturn($response);
                }

                foreach ($data as $k => $v) {
                    if (!isset($rs[$v['id']]))
                        $rs[$v['id']] = array(
                            'id' => $v['id'],
                            'placeId' => $v['placeId'],
                            'title' => $v['title'],
                            'address' => $v['address'],
                            'name' => $v['name'],
                            'occupyTime' => empty($v['occupyTime']) ? array() : explode(',', $v['occupyTime']),
                            'principal' => $v['principal'],
                            'proposer' => $v['proposer'],
                            'telephone' => $v['telephone'],
                            'destination' => $v['destination'],
                            'outfit' => $v['outfit'],
                            'createTime' => date('Y-m-d H:i:s', $v['createTime']),
                            'status' => $v['status'],
                            'scId' => $v['scId'],
                            'explain' => $v['explain'],
                            'process' => array()
                        );
                    if (!isset($rs[$v['id']]['process'][$v['step']])) {
                        $rs[$v['id']]['process'][$v['step']] = array(
                            'name' => $v['step'],
                            'child' => array()
                        );
                    }
                    $rs[$v['id']]['process'][$v['step']]['child'][] = array(
                        'approver' => $v['approver'],
                        'opinion' => $v['opinion'],
                        'result' => $v['result'],
                        'approveTime' => !empty($v['approveTime']) ? date('Y-m-d H:i:s', $v['approveTime']) : ''
                    );
                }
                foreach ($rs as $k => $v) {
                    sort($rs[$k]['process']);
                }
                sort($rs);
                $response['status'] = 1;
                $response['data'] = $rs;
                $this->ajaxReturn($response);
            } else {

            }
        }

        //得到公文记录
        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $where = array(
            'scId' => $this->scId,
            'proposerId' => $this->uid
        );

        //导出
        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            $placeLog = D('PlaceApplication')
                ->where($where)
                ->order('createTime desc')
                ->select();
            foreach ($placeLog as &$v) {
                if ($v['ifApprove'] == 0) {
                    $v['status'] = 9;//还没有人审批过 0正在审批中
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
                ->setCellValue('B1', '详细地址')
                ->setCellValue('C1', '类型')
                ->setCellValue('D1', '使用时间')
                ->setCellValue('E1', '场地负责人')
                ->setCellValue('F1', '联系电话')
                ->setCellValue('G1', '创建日期')
                ->setCellValue('H1', '审批状态');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            foreach ($placeLog as $k => $v) {
                if ($v['status'] == -1) {
                    $status = '未通过';
                } elseif ($v['status'] == 0) {
                    $status = '正在审批';
                } elseif ($v['status'] == 1) {
                    $status = '通过';
                } elseif ($v['status'] == 2) {
                    $status = '审批过期';
                } elseif ($v['status'] == 9) {
                    $status = '待审批';
                }else {
                    $status = '撤回';
                }
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['title'])
                    ->setCellValue('B' . $num, $v['address'])
                    ->setCellValue('C' . $num, $v['name'])
                    ->setCellValue('D' . $num, $v['occupyTime'])
                    ->setCellValue('E' . $num, $v['principal'])
                    ->setCellValue('F' . $num, $v['telephone'])
                    ->setCellValue('G' . $num, date('Y-m-d H:i:s', $v['createTime']))
                    ->setCellValue('H' . $num, $status);
            }
            $objPHPExcel->getActiveSheet()->setTitle('场地申请记录');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '场地申请记录' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;

        }

        if (!empty($_POST['startTime']) && !empty($_POST['endTime'])) {
            $where['createTime'] =
                array('between', array(strtotime($_POST['startTime']), strtotime($_POST['endTime'])));
        }

        $order = $_POST['order'];
        if (!isset($order))
            $order = 'createTime desc';

        $key = $_POST['key'];
        if (!empty($key)) {
            $where['title|name'] = array('like', "%{$key}%");
        }

        $placeLog = D('PlaceApplication')
            ->where($where)
            ->order($order)
            ->limit($limit_page)
            ->select();

        if ($placeLog) {
            foreach ($placeLog as &$v) {
                if ($v['ifApprove'] == 0) {
                    $v['status'] = 9;//还没有人审批过 0正在审批中
                }
                $v['occupyTime'] = empty($v['occupyTime']) ? array() : explode(',', $v['occupyTime']);
            }
            $total = (int) D('PlaceApplication')->where($where)->count();
            $response['status'] = 1;
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['data'] = $placeLog;
        }
        $this->ajaxReturn($response);
    }

    //场地申请审批
    public function approvePlace($whether = 1)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        $this->exceedTime('approver', 'place');

        // 通过或拒绝审批
        $placeId = I('post.placeId');
        $operate = $_POST['type'];
        $opinion = I('post.opinion');
        if ($operate) {
            if ($placeId && $operate == 'pass') {
                $operate = 1;
            } elseif ($placeId && $operate == 'reject') {
                $operate = 2;
            } elseif ($operate == 'del') {
                $ids = I('post.ids');
                $con = array('id' => array('in', $ids));
                $rs = $this->place->model->where($con)->delete();
                if (!$rs) {
                    $response['msg'] = '删除申请出错';
                    $this->ajaxReturn($response);
                }
                $result = $this->place->detailModel->where(array(
                    'relationId' => array('in', $ids),
                    'processType' => $this->place->processType,
                    'scId' => $this->scId))->delete();
                if (!$result) {
                    $response['msg'] = '删除流程出错';
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '删除成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            } elseif ($operate == 'detail') {
                $id = I('post.id');
                $data = $this->info($id, 'place');

                if (!$data) {
                    $this->ajaxReturn($response);
                }

                foreach ($data as $k => $v) {
                    if (!isset($rs[$v['id']]))
                        $rs[$v['id']] = array(
                            'id' => $v['id'],
                            'placeId' => $v['placeId'],
                            'title' => $v['title'],
                            'address' => $v['address'],
                            'name' => $v['name'],
                            'occupyTime' => empty($v['occupyTime']) ? array() : explode(',', $v['occupyTime']),
                            'principal' => $v['principal'],
                            'proposer' => $v['proposer'],
                            'telephone' => $v['telephone'],
                            'destination' => $v['destination'],
                            'outfit' => $v['outfit'],
                            'createTime' => date('Y-m-d H:i:s', $v['createTime']),
                            'status' => $v['status'],
                            'scId' => $v['scId'],
                            'explain' => date('Y-m-d H:i:s', $v['explain']),
                            'process' => array()
                        );
                    if (!isset($rs[$v['id']]['process'][$v['step']])) {
                        $rs[$v['id']]['process'][$v['step']] = array(
                            'name' => $v['step'],
                            'child' => array()
                        );
                    }
                    $rs[$v['id']]['process'][$v['step']]['child'][] = array(
                        'approver' => $v['approver'],
                        'opinion' => $v['opinion'],
                        'result' => $v['result'],
                        'approveTime' => !empty($v['approveTime']) ? date('Y-m-d H:i:s', $v['approveTime']) : ''
                    );
                }
                foreach ($rs as $k => $v) {
                    sort($rs[$k]['process']);
                }
                sort($rs);
                $response['status'] = 1;
                $response['data'] = $rs;
                $this->ajaxReturn($response);
            }
            $result = $this->place->operate($placeId, $this->place->processType,
                $operate, $this->scId, $this->uid, $opinion, $this->user['name']);
            if ($result) {
                $response['msg'] = '操作成功';
                $this->place->model->where(array('id'=>$placeId,'ifApprove'=>0))->data(array('ifApprove'=>1))->save();
                if ($result['status'] != 0)
                    $this->place->model->where("id={$result['id']}")->data(array('status' => $result['status']))->save();
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
                'result' => array('in', array(2, 5)),
                'scId' => $this->scId,
                'approveId' => $this->uid,
                'processType' => $this->place->processType
            );
        } elseif ($whether == 2) {
            $map = array(
                'approveId' => $this->uid,
                'result' => array('in', array(1, -1)),
                'scId' => $this->scId,
                'processType' => $this->place->processType
            );
        }


        $data = $this->place->detailModel->where($map)
            ->field('relationId,timeLine,approveId')->select();

        if (!$data) {
            $this->ajaxReturn($response);
        }

        $timeMap = array();
        foreach ($data as $k => $v) {
            $timeMap[$v['relationId']][$v['approveId']] = $v['timeLine'] > 0 ? date('Y-m-d H:i:s', $v['timeLine']) : '-';
        }

        $relationIds = array_map(function ($v) {
            return $v['relationId'];
        }, $data);
        $relationIds = array_unique($relationIds);
        $condition = array(
            'scId' => $this->scId,
            'id' => array('in', $relationIds),
            'status' => $whether > 1 ? array('neq', 3) : array('in', array(0, 2))
        );


        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        //搜索

        if (!empty($_POST['startTime']) && !empty($_POST['endTime'])) {
            $condition['createTime'] =
                array('between', array(strtotime($_POST['startTime']), strtotime($_POST['endTime'])));
        }
        $order = $_POST['order'];
        if (!isset($order))
            $order = 'createTime desc';

        $key = $_POST['key'];
        if (!empty($key)) {
            $condition['name|title'] = array('like', "%{$key}%");
        }
        $data = $this->place->model->where($condition)->order($order)->limit($limit_page)->select();

        if ($data) {
            foreach ($data as $k => $v) {
                $data[$k]['exceed'] =
                    !empty($timeMap[$v['id']][$this->uid]) ? $timeMap[$v['id']][$this->uid] : '-';
                $data[$k]['occupyTime'] = empty($v['occupyTime']) ? array() : explode(',', $v['occupyTime']);
            }
            $total = (int)$this->place->model->where($condition)->count();
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['data'] = $data;
            $response['status'] = 1;
        }
        $this->ajaxReturn($response);
    }

    //文件缓存数据上传
    public function uploadCache($name)
    {
        $response = array(
            'status' => 0
        );
        $data = S($name);
        if (!$data) {
            $response['msg'] = '上传数据丢失,请重新上传';
            $this->ajaxReturn($response);
        }

        S($name, null);
        $old = D('Place')->where(array('scId' => $this->scId))->field('id,name')->select();
        foreach ($old as $k => $v) {
            $oldName[] = $v['name'];
            $map[$v['name']] = $v['id'];
        }
        $newName = array_keys($data);
        $data = array_values($data);
        $ins = array_intersect($newName, $oldName);
        foreach ($ins as $k => $v) { //删除重复数据
            D('Place')->where(array('id' => $map[$v]))->delete();
        }
        $rs = D('Place')->addAll($data);
        if (!$rs) {
            $response['msg'] = '上传失败';
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['msg'] = '上传成功';
        $this->ajaxReturn($response);
    }
}