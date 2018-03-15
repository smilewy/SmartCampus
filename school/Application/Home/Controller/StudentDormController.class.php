<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/9/26
 * Time: 9:04
 */

namespace Home\Controller;

/*
 * 学生宿舍
 * */
use Home\Common\Accessory;
use Home\Common\Import;
use PHPExcel;
use PHPExcel_IOFactory;

ob_end_clean();

class StudentDormController extends Base
{
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
        /*       $this->roleId = 22;
                $scId = 2;
                $uId = 1;*/
        $this->scId = $scId;
        $this->uId = $uId;
        $this->user = D('User')->where(array('id' => $this->uId, 'scId' => $this->scId))->find();
    }

    //公共调用接口
    public function common($func, $param = array())
    {
        switch ($func) {
            case 'getManager':
                $this->getManager();
                break;
            case 'getDorm':
                $this->getDorm();
                break;
            case 'getStu':
                $this->getStu();
                break;
            case 'getProcess':
                $this->getProcess($param['planId']);
                break;
            case 'getGrade':
                $this->getGrade();
                break;
            case 'getAssign':
                $this->getAssign($param['planId']);
                break;
            case 'getPlanDorm':
                $this->getPlanDorm($param);
                break;
            case 'getPlanStu':
                $this->getPlanStu($param['planId']);
                break;
            case 'getExam':
                $this->getExam();
                break;
            case 'getSelDorm':
                $this->getSelDorm($param['planId']);
                break;
            case 'getInfo':
                $this->getInfo();
                break;
            case 'dormList':
                $this->dormList($param);
                break;
            case 'requestInfo':
                $this->requestInfo($param['planId']);
                break;
            case 'getSelStu':
                $this->getSelStu($param['planId'], $param['dormId']);
                break;
            default:
                return null;
        }
    }

    //就读方式审批 //已测试
    public function attendApprove($option = 1)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        //宿管是不是宿舍主任
        if ($this->roleId == $this::$sgRoleId) {
            $teaId = D('Dorm')->where(array('scId' => $this->scId, 'dormDean' => 1))->field('teaId')->select();
            $teaId = array_map(function ($v) {
                return $v['teaId'];
            }, $teaId);
            if (!in_array($this->uId, $teaId)) {
                $response['msg'] = '不是宿舍主任';
                $this->ajaxReturn($response);
            }
        }
        $type = $_POST['type'];
        $ids = I('post.ids');
        if (isset($type)) {
            $rs = false;
            if ($type == 'inSchool') { //调整为住校
                $rs = D('StudentInfo')->where(array('id' => array('in', $ids)))->save(array('isResCache' => 1));
            } elseif ($type == 'notInSchool') {//调整为走读
                $rs = D('StudentInfo')->where(array('id' => array('in', $ids)))->save(array('isResCache' => 1));
            } elseif ($type == 'pass') {
                $ids = implode(',', $ids);
                $sql = "UPDATE mks_student_info SET isResCache=0,isResSchool= CASE";
                $sql .= " when isResSchool='是' then '否' when isResSchool='否' then '是'";
                $sql .= " END WHERE id in ({$ids}) and scId={$this->scId}";

                $rs = M('')->execute($sql);
            } elseif ($type == 'reject') {
                $rs = D('StudentInfo')->where(array('id' => array('in', $ids)))->save(array('isResCache' => 0));
            } else {
                $response['msg'] = '没有权限';
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
            } else {
                $response['status'] = 1;
                $response['msg'] = '操作成功';
            }
            $this->ajaxReturn($response);
        }
        $where = array(
            'si.scId' => $this->scId,
            'si.isAtSchool' => '是'
        );
        if ($option == 1) { //待审批
            $where['si.isResCache'] = 1;
        } elseif ($option == 2) { //住校
            $where['si.isResSchool'] = '是';
        } elseif ($option == 3) { //走读
            $where['si.isResSchool'] = '否';
        }

        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $map = array();
        foreach ($grade as $k => $v) {
            $map[$v['name']] = $v['znName'];
        }

        $download = $_REQUEST['download'];
        if ($download == 'ensure') {
            $data = D('StudentInfo si')
                ->join('mks_school_rollinfo sr ON si.userId=sr.userId', 'LEFT')
                ->join('mks_cen_reg_info cri ON si.userId=cri.userId', 'LEFT')
                ->where($where)
                ->field('si.id,si.name,si.sex,si.grade,si.className,si.phone,si.idCard,
            si.isResSchool,si.remark,sr.regNumber,cri.perAddress,sr.midExam,sr.admCategory')
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
                ->setCellValue('A1', '考号')
                ->setCellValue('B1', '姓名')
                ->setCellValue('C1', '性别')
                ->setCellValue('D1', '总分')
                ->setCellValue('E1', '年级')
                ->setCellValue('F1', '班级')
                ->setCellValue('G1', '手机号')
                ->setCellValue('H1', '身份证号')
                ->setCellValue('I1', '户口所在地')
                ->setCellValue('J1', '录取类型')
                ->setCellValue('K1', '备注');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            foreach ($data as $k => $v) {
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['regNumber'])
                    ->setCellValue('B' . $num, $v['name'])
                    ->setCellValue('C' . $num, $v['sex'])
                    ->setCellValue('D' . $num, $v['midExam'])
                    ->setCellValue('E' . $num, $map[$v['grade']])
                    ->setCellValue('F' . $num, $v['className'])
                    ->setCellValue('G' . $num, $v['phone'])
                    ->setCellValue('H' . $num, $v['idCard'])
                    ->setCellValue('I' . $num, $v['perAddress'])
                    ->setCellValue('J' . $num, $v['admCategory'])
                    ->setCellValue('K' . $num, $v['remark']);
            }

            $objPHPExcel->getActiveSheet()->setTitle('就读方式审批表');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '就读方式审批表' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        $key = $_POST['key'];
        if (!empty($key)) {
            $where['si.name|sr.regNumber|sr.admCategory'] = array('like', "%{$key}%");
        }
        $order = $_POST['order']; //排序
        $according = $_POST['according'];
        if (!empty($order) && !empty($according)) {
            $order = $according . ' ' . stristr($order, 'ending', true);
        } else {
            $order = 'si.grade asc,si.className asc';
        }
        $data = D('StudentInfo si')
            ->join('mks_user u ON si.userId=u.id', 'LEFT')
         /*   ->join('mks_school_rollinfo sr ON si.userId=sr.userId', 'LEFT')
            ->join('mks_cen_reg_info cri ON si.userId=cri.userId', 'LEFT')*/
            ->where($where)
            ->field('u.number,si.id,si.name,si.sex,si.grade,si.className,si.phone,si.idCard,
            si.isResSchool,si.remark'/*,sr.regNumber,cri.perAddress,sr.midExam,sr.admCategory'*/)
            ->order($order)
            ->limit($limit_page)
            ->select();

        if ($data) {
            foreach ($data as &$v) {
                $v['grade'] = $map[$v['grade']];
                if ($v['isResSchool'] == '是') {
                    $v['turn'] = '转为走读';
                } else {
                    $v['turn'] = '转为住校';
                }
            }
            $total = (int)D('StudentInfo si')
                ->join('mks_school_rollinfo sr ON si.userId=sr.userId', 'LEFT')
                ->join('mks_cen_reg_info cri ON si.userId=cri.userId', 'LEFT')
                ->where($where)
                ->count();
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['status'] = 1;
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);

    }

    //宿舍信息管理
    public function dormManage()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_REQUEST['type'];
        if (isset($type)) {
            $rs = false;
            if ($type == 'add') { //添加
                $data = $_POST;
                $findWhere=array(
                    'scId'=>$this->scId,
                    'number'=>$data['number'],
                    'floor'=>$data['floor'],
                    'dormNumber'=>$data['dormNumber']
                );
                $find=D('Dorm')->where($findWhere)->find();
                if($find){
                    $response['msg']='此宿舍已存在';
                    $this->ajaxReturn($response);
                }
                $dorm = array(
                    'number' => $data['number'],
                    'name' => $data['name'],
                    'floor' => $data['floor'],
                    'dormNumber' => $data['dormNumber'],
                    'dormName' => $data['dormName'],
                    'dormType' => $data['dormType'],
                    'capacity' => $data['capacity'],
                    'scId' => $this->scId,
                    'lastRecordTime' => time(),
                    'createTime' => time(),
                );
                $rs = D('Dorm')->data($dorm)->add();
            } elseif ($type == 'edit') { //编辑
                $data = $_POST;
                $dorm = array(
                    'number' => $data['number'],
                    'name' => $data['name'],
                    'floor' => $data['floor'],
                    'dormNumber' => $data['dormNumber'],
                    'dormType' => $data['dormType'],
                    'dormName' => $data['dormName'],
                    'capacity' => $data['capacity'],
                    'scId' => $this->scId,
                    'lastRecordTime' => time(),
                    'createTime' => time(),
                );
                $findWhere=array(
                    'scId'=>$this->scId,
                    'number'=>$data['number'],
                    'floor'=>$data['floor'],
                    'dormNumber'=>$data['dormNumber']
                );
                $find=D('Dorm')->where($findWhere)->find();
                if($find){
                    $response['msg']='此宿舍已存在';
                    $this->ajaxReturn($response);
                }
                $rs = D('Dorm')->where(array('id' => $data['id']))->data($dorm)->save();
            } elseif ($type == 'del') { //删除
                $map = array(
                    'id' => array('in', $_POST['ids'])
                );
                $stuId = D('Dorm')->where($map)->field('stuId')->select();
                if (!empty($stuId)) {//更新学生基本信息
                    $stuId = array_map(function ($v) {
                        return explode(',', $v['stuId']);
                    }, $stuId);
                    $change = array(
                        'dorStore' => '',
                        'dorStorey' => '',
                        'dorNumber' => ''
                    );
                    $rs = D('StudentInfo')->where(array('userId' => array('in', $stuId)))
                        ->data($change)->save();
                    if (!$rs) {
                        $response['msg'] = '清空学生信息失败';
                    }
                }
                $rs = D('Dorm')->where(array('id' => array('in', $_POST['ids'])))->delete();
            } elseif ($type == 'import') { //批量导入
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
                $dormList = array();

                foreach ($data as &$v) {
                    if ($v[0] == 1 && $v[1] == 'xx苑')
                        continue;
                    if ($v[5] == '女生') {
                        $dormType = 1;
                    } elseif ($v[5] == '男生') {
                        $dormType = 2;
                    } elseif ($v[5] == '混合') {
                        $dormType = 3;
                    } else {
                        $dormType = 4;
                    }
                    $capacity = (int)$v[6];
                    if ($capacity <= 0) {
                        $response['msg'] = '容纳人数填写错误';
                        $this->ajaxReturn($response);
                    }
                    $dormList[] = array(
                        'number' => $v[0],
                        'name' => $v[1],
                        'floor' => $v[2],
                        'dormNumber' => $v[3],
                        'dormName' => $v[4],
                        'dormType' => $dormType,
                        'capacity' => $v[6],
                        'scId' => $this->scId,
                        'lastRecordTime' => time(),
                        'createTime' => time(),
                    );
                }
                $rs = D('Dorm')->addAll($dormList);
            } elseif ($type == 'download') { //已测试
                $file = new Accessory('', $this->scId, 'download');
                $filename = array('download/newDorm.xlsx');
                $aName = array('宿舍模板.xlsx');
                $file->download($filename, $aName, true);
                die;
            } else {
                $response['msg'] = '没有权限';
            }
            if ($rs) {
                $response['status'] = 1;
                $response['msg'] = '操作成功';
            }
            $this->ajaxReturn($response);
        }

        //导出
        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            $data = D('Dorm')
                ->where(array('scId' => $this->scId))
                ->field('id,number,name,floor,dormNumber,dormName,dormType,capacity')
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
                ->setCellValue('A1', '栋号')
                ->setCellValue('B1', '宿舍楼名称')
                ->setCellValue('C1', '楼层')
                ->setCellValue('D1', '宿舍号')
                ->setCellValue('E1', '宿舍名称')
                ->setCellValue('F1', '宿舍类型')
                ->setCellValue('G1', '容纳人数');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            foreach ($data as $k => $v) {
                if ($v['dormType'] == 1) {
                    $dormType = '女生宿舍';
                } elseif ($v['dormType'] == 2) {
                    $dormType = '男生宿舍';
                } elseif ($v['dormType'] == 3) {
                    $dormType = '混合宿舍';
                } else {
                    $dormType = '其他';
                }

                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['number'])
                    ->setCellValue('B' . $num, $v['name'])
                    ->setCellValue('C' . $num, $v['floor'])
                    ->setCellValue('D' . $num, $v['dormNumber'])
                    ->setCellValue('E' . $num, $v['dormName'])
                    ->setCellValue('F' . $num, $dormType)
                    ->setCellValue('G' . $num, $v['capacity']);
            }

            $objPHPExcel->getActiveSheet()->setTitle('宿舍信息管理表');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '宿舍信息管理表' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }
        $where = array(
            'scId' => $this->scId,
            'dormDean' =>0
        );
        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        $key = $_POST['key'];
        if (!empty($key)) {
            $where['number|name|floor|dormNumber|dormName|dormType|capacity'] = array('like', "%{$key}%");
        }
        $order = $_POST['order']; //排序
        $according = $_POST['according'];
        if (!empty($order) && !empty($according)) {
            $order = $according . ' ' . stristr($order, 'ending', true);
        } else {
            $order = 'number desc';
        }
        $data = D('Dorm')
            ->where($where)
            ->order($order)
            ->field('id,number,name,floor,dormNumber,dormName,dormType,capacity')
            ->limit($limit_page)
            ->select();

        if ($data) {
            $total = (int)D('Dorm')
                ->where($where)
                ->count();
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['status'] = 1;
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);
    }

    //(设置生活老师)得到生活老师 //已测试
    private function getManager()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $roleId = array(
            $this::$studentRoleId, $this::$jZroleId,
            $this::$adminRoleId
        );
        $where = array(
            'roleId' => array('not in', $roleId),
            'scId' => $this->scId
        );
        $data = D('User')->where($where)->field('id,name,post,department')->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }
        $res = array();
        foreach ($data as $v) {
            $post = empty($v['post']) ? '其他职位' : $v['post'];
            if (!isset($res[$post])) {
                $res[$post] = array(
                    'name' => $post,
                    'child' => array()
                );
            }
            $department = empty($v['department']) ? '其他部门' : $v['department'];
            if (!isset($res[$post]['child'][$department]))
                $res[$post]['child'][$department] = array(
                    'name' => $department,
                    'child' => array()

                );
            $res[$post]['child'][$department]['child'][] = array(
                'teaId' => $v['id'],
                'name' => $v['name']
            );
        }
        foreach ($res as $k => $v) {
            sort($res[$k]['child']);
        }
        sort($res);
        $response['status'] = 1;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //得到待选宿舍 //已测试
    private function getDorm()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $dorm = D('Dorm')->where(array('scId' => $this->scId, 'dormDean' => 0))
            ->field('id,number,name,floor,dormNumber,dormName,teaName,dormType,capacity')
            ->select();
        if (!$dorm) {
            $this->ajaxReturn($response);
        }
        $res = array();
        foreach ($dorm as $k => $v) {
            $temp = $v['number'] . '(' . $v['name'] . ')';
            if (!isset($res[$temp]))
                $res[$temp] = array(
                    'name' => $temp,
                    'child' => array()
                );
            if (!isset($res[$temp]['child'][$v['floor']])) {
                $res[$temp]['child'][$v['floor']] = array(
                    'name' => $v['floor'],
                    'child' => array());
            };
            $res[$temp]['child'][$v['floor']]['child'][] = array(
                'dormId' => $v['id'],
                'dormNumber' => $v['dormNumber'],
                'name' => $v['dormName'],
                'teaName' => $v['teaName'],
                'dormType' => $v['dormType'],
                'floor' => $v['floor'],
                'capacity' => $v['capacity'],
                'number' => $v['number'],
                'buildName' => $v['name']
            );
        }
        foreach ($res as $k => $v) {
            sort($res[$k]['child']);
        }
        sort($res);
        $response['status'] = 1;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //设置生活老师
    public function setManager()
    {
        $response = array(
            'status' => 0,
            'data'=>array()
        );

        //宿管是不是宿舍主任
        if ($this->roleId == $this::$sgRoleId) {
            $teaId = D('Dorm')->where(array('scId' => $this->scId, 'dormDean' => 1))->field('teaId')->select();
            $teaId = array_map(function ($v) {
                return $v['teaId'];
            }, $teaId);
            if (!in_array($this->uId, $teaId)) {
                $response['msg'] = '不是宿舍主任';
                $this->ajaxReturn($response);
            }
        }

        //操作
        $type = $_POST['type'];
        if (isset($type)) {
            $rs = false;
            if ($type == 'save') {
                $setting = I('post.setting');
                $val = '';
                $tempId = D('Dorm')->where(array('scId' => $this->scId, 'dormDean' => 0))->field('id')->select();
                $tempId = array_map(function ($v) {
                    return $v['id'];
                }, $tempId);
                $pro = array();
                foreach ($setting as $k => $v) {
                    if (!empty($v['dormId'])) {
                        foreach ($v['dormId'] as $k1 => $v1) {
                            $val .= '(' . "{$v1}," . "'{$v['teaId']}'," . "'{$v['teaName']}'," .
                                "{$this->scId}," . "'{$v['dormDean']}'),";
                            $pro[] = $v1;
                        }
                    } else {
                        if ($v['dormDean'] == 1) {
                            $id = D('Dorm')->where(array('scId' => $this->scId, 'dormDean' => 1))->getField('id');
                            if (!$id)
                                $id = '';
                            $val .= '(' . "'{$id}'," . "'{$v['teaId']}'," . "'{$v['teaName']}'," .
                                "{$this->scId}," . "'{$v['dormDean']}'),";
                        }
                    }
                }
                $diffId = array_diff($tempId, $pro);
                foreach ($diffId as $k => $v) {
                    $dormDean = 0;
                    $val .= '(' . "{$v}," . "''," . "''," .
                        "{$this->scId}," . "'{$dormDean}'),";
                }

                $val = rtrim($val, ',');
                $sql = "insert into mks_dorm (id,teaId,teaName,scId,dormDean)
                          values {$val} on duplicate key update teaId=values(teaId),
                          teaName=values(teaName),scId=values(scId),dormDean=values(dormDean)";

                $rs = M()->execute($sql);
                if (!$rs) {
                    $response['msg'] = '操作失败';
                    $this->ajaxReturn($response);
                }
            } elseif ($type == 'del') {
                $id = $_POST['id'];
                $rs = D('Dorm')->where(array('id' => array('in', $id)))->data(array('teaId' => '', 'teaName' => ''))->save();
                if (!$rs) {
                    $response['msg'] = '操作失败';
                    $this->ajaxReturn($response);
                }
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            $response['status'] = 1;
            $response['msg'] = '操作成功';
            $this->ajaxReturn($response);
        }

        //展示
        $where = array(
            'teaId' => array('gt', 0),
            'scId' => $this->scId
        );
        $data = D('Dorm')->where($where)->field('id,name,number,floor,teaId,teaName,dormNumber,dormDean')->select();

        if ($data) {
            $response['status'] = 1;
            $result = array(
                'zr' => array(),
                'ls' => array()
            );
            foreach ($data as $k => $v) {
                if ($v['dormDean'] == 1)
                    $result['zr'] = array(
                        'id' => $v['id'],
                        'teaId' => $v['teaId'],
                        'teaName' => $v['teaName']
                    );
                else {
                    if (!isset($result['ls'][$v['teaId']]))
                        $result['ls'][$v['teaId']] = array(
                            'teaId' => $v['teaId'],
                            'teaName' => $v['teaName'],
                            'dorm' => array()
                        );
                    $result['ls'][$v['teaId']]['dorm'][] = array(
                        'dormId' => $v['id'],
                        'name' =>$v['number'].'-'.$v['floor'].'-'. $v['dormNumber']
                    );
                }
            }
            sort($result['ls']);
            $response['data'] = $result;
        }
        $this->ajaxReturn($response);
    }

    //(住宿人员管理) 下拉框
    private function getInfo()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        $map = array(
            'scId' => $this->scId,
            'dormDean' => 0
        );

        $dorm = D('Dorm')->where($map)
            ->field('id,number,name,floor,dormType')
            ->select();
        if (!$dorm) {
            $this->ajaxReturn($response);
        }
        $res = array();
        $map = array(
            1 => '女生宿舍', 2 => '男生宿舍', 3 => '混合宿舍', 4 => '其它',
        );
        foreach ($dorm as $k => $v) {
            if (!isset($res[$v['number']])) {
                $res[$v['number']] = array(
                    'name' => $v['number'],
                    'child' => array()
                );
            }
            if (!isset($res[$v['number']]['child'][$v['floor']])) {
                $res[$v['number']]['child'][$v['floor']] = array(
                    'name' => $v['floor'],
                    'child' => array()
                );
            }
            $res[$v['number']]['child'][$v['floor']]['child'][] = array(
                'name' => $v['dormType'], 'znName' => $map[$v['dormType']]
            );
        }

        foreach ($res as $k => $v) {
            foreach ($res[$k]['child'] as $k1 => $v1) {
                $res[$k]['child'][$k1]['child'] = array_unique($res[$k]['child'][$k1]['child']);
            }
            sort($res[$k]['child']);
        }
        sort($res);
        $response['status'] = 1;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //(住宿人员管理) 宿舍
    private function dormList($param)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        $map = array(
            'number' => $param['number'],
            'floor' => $param['floor'],
            'dormType' => $param['dormType'],
            'scId' => $this->scId,
            'dormDean' => 0
        );

        $dorm = D('Dorm')->where($map)
            ->field('id as dormId,floor,number,dormNumber,dormName,dormType,teaName')
            ->select();
        if (!$dorm) {
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['data'] = $dorm;
        $this->ajaxReturn($response);
    }

    //(住宿人员管理)待选学生
    private function getStu()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'scId' => $this->scId,
            'isResSchool' => '是',
            'isAtSchool' => '是'
        );
        $data = D('StudentInfo')->where($where)
            ->field('id,userId,grade,className,name,sex,dorNumber')->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }
        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $map = array();
        foreach ($grade as $k => $v) {
            $map[$v['name']] = $v['znName'];
        }
        $res = array();
        foreach ($data as $k => $v) {
            $grade = $map[$v['grade']];
            if (!isset($res[$v['grade']])) {
                $res[$v['grade']] = array(
                    'name' => $grade,
                    'child' => array()
                );
            }
            if (!isset($res[$v['grade']]['child'][$v['className']])) {
                $res[$v['grade']]['child'][$v['className']] = array(
                    'name' => $v['className'],
                    'child' => array()
                );
            }
            $res[$v['grade']]['child'][$v['className']]['child'][] = array(
                'stuId' => $v['id'],
                'userId' => $v['userId'],
                'name' => $v['name'],
                'sex' => $v['sex'],
                'dorNumber' => $v['dorNumber']
            );
        }
        foreach ($res as $k => $v) {
            sort($res[$k]['child']);
        }
        sort($res);
        $response['status'] = 1;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //住宿人员管理
    public function stuManage($option = 1)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];
        if (isset($type)) {
            $rs = false;
            if ($type == 'save') {
                $id = I('post.dormId');
                $stuId = I('post.stuId');
                $stuId = array_unique($stuId);
                $stuName = I('post.stuName');
                $stuName = array_unique($stuName);
                $strId = implode(',', $stuId);
                $strName = implode(',', $stuName);
                //更新宿舍学生信息
                $map = array(
                    'scId' => $this->scId,
                    'dormDean' => 0,
                    '_string' => "concat(',',stuId,',') regexp concat(',(',replace('{$strId}',',','|'),'),')"
                );
                $modifyData = D('Dorm')->where($map)->field('id,stuId,stuName')->select();
                $val = '';
                $time = time();
                if ($modifyData) {
                    foreach ($modifyData as &$v) {
                        if ($v['id'] == $id) {
                            continue;
                        }
                        $temp_stuId = empty($v['stuId']) ? array() : explode(',', $v['stuId']);
                        $temp_stuName = empty($v['stuName']) ? array() : explode(',', $v['stuName']);
                        $diff_stuId = implode(',', array_diff($temp_stuId, $stuId));
                        $diff_stuName = implode(',', array_diff($temp_stuName, $stuName));
                        $val .= '(' . "{$v['id']}," . "'{$diff_stuId}'," . "'{$diff_stuName}'," . "{$time}),";
                    }
                }
                $val .= '(' . "{$id}," . "'{$strId}'," . "'{$strName}'," . "{$time}),";
                $val = rtrim($val, ',');
                $sql = "insert into mks_dorm (id,stuId,stuName,lastRecordTime)
                          values {$val} on duplicate key update stuId=values(stuId),
                          stuName=values(stuName),lastRecordTime=values(lastRecordTime)";

                $rs = M()->execute($sql);
                if (!$rs) {
                    $response['msg'] = '更新宿舍学生信息失败';
                    $this->ajaxReturn($response);
                }
                //更新学生基本信息
                $changeWhere = array(
                    'dorStore' => $_POST['number'],
                    'dorStorey' => $_POST['floor'],
                    'dorNumber' => $_POST['dormNumber'],
                    'scId' => $this->scId
                );
                $full = array(
                    'dorStore' => '',
                    'dorStorey' => '',
                    'dorNumber' => ''
                );
                D('StudentInfo')->where($changeWhere)
                    ->data($full)->save();
                $change = array(
                    'dorStore' => $_POST['number'],
                    'dorStorey' => $_POST['floor'],
                    'dorNumber' => $_POST['dormNumber']
                );
                $rs = D('StudentInfo')->where(array('id' => array('in', $stuId)))
                    ->data($change)->save();

            } elseif ($type == 'clean') {
                $id = $_POST['dormId'];
                $stuId = D('Dorm')->where(array('id' => $id))->getFiled('stuId');
                if (!empty($stuId)) {//更新学生基本信息
                    $stuId = explode(',', $stuId);
                    $change = array(
                        'dorStore' => '',
                        'dorStorey' => '',
                        'dorNumber' => ''
                    );
                    $rs = D('StudentInfo')->where(array('id' => array('in', $stuId)))
                        ->data($change)->save();
                    if (!$rs) {
                        $response['msg'] = '清空学生信息失败';
                    }
                }
                //更新宿舍信息
                $rs = D('Dorm')->where("id={$id}")->data(array('stuId' => '', 'stuName' => ''))->save();
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
                $this->ajaxReturn($response);
            }
            $response['msg'] = '操作成功';
            $response['status'] = 1;
            $this->ajaxReturn($response);
        }
        if ($option == 1) { //住宿人员管理
            $dormId = $_POST['dormId'];
            $where = array(
                'id' => $dormId,
                'dormDean' => 0,
                'scId' => $this->scId
            );
            $foo = D('Dorm')->where($where)->field('id as dormId,dormNumber,teaName,stuId,stuName')
                ->select();
            if (!$foo) {
                $this->ajaxReturn($response);
            }
            $data = array();
            foreach ($foo as $k => $v) {
                $stuId = empty($v['stuId']) ? array() : explode(',', $v['stuId']);
                $stuName = empty($v['stuName']) ? array() : explode(',', $v['stuName']);
                $data[] = array(
                    'dormId' => $v['dormId'],
                    'dormNumber' => $v['dormNumber'],
                    'teaName' => $v['teaName'],
                    'stu' => array());
                foreach ($stuId as $k1 => $v1) {
                    $data[$k]['stu'][] = array(
                        'stuId' => $stuId[$k1],
                        'name' => $stuName[$k1]
                    );
                }
            }
        } else {//住宿人员明细
            $where = array('scId' => $this->scId, 'dormDean' => 0);
            $download = $_REQUEST['export'];
            if ($download == 'ensure') {
                $data = D('Dorm')
                    ->where($where)
                    ->field('id as dormId,number,name,floor,dormNumber,dormName,dormType,capacity,stuName')
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
                    ->setCellValue('A1', '栋号')
                    ->setCellValue('B1', '宿舍楼名称')
                    ->setCellValue('C1', '楼层')
                    ->setCellValue('D1', '宿舍号')
                    ->setCellValue('E1', '宿舍名称')
                    ->setCellValue('F1', '宿舍类型')
                    ->setCellValue('G1', '容纳人数')
                    ->setCellValue('H1', '实住人数')
                    ->setCellValue('I1', '实住人员名单');
                $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

                foreach ($data as $k => $v) {
                    if ($v['dormType'] == 1) {
                        $dormType = '女生宿舍';
                    } elseif ($v['dormType'] == 2) {
                        $dormType = '男生宿舍';
                    } elseif ($v['dormType'] == 3) {
                        $dormType = '混合宿舍';
                    } else {
                        $dormType = '其他';
                    }
                    $temp = empty($v['stuName']) ? array() : explode(',', $v['stuName']);

                    $real = count($temp);
                    $num = $k + 2;
                    $objPHPExcel->setActiveSheetIndex(0)
                        //Excel的第A列，uid是你查出数组的键值，下面以此类推
                        ->setCellValue('A' . $num, $v['number'])
                        ->setCellValue('B' . $num, $v['name'])
                        ->setCellValue('C' . $num, $v['floor'])
                        ->setCellValue('D' . $num, $v['dormNumber'])
                        ->setCellValue('E' . $num, $v['dormName'])
                        ->setCellValue('F' . $num, $dormType)
                        ->setCellValue('G' . $num, $v['capacity'])
                        ->setCellValue('H' . $num, $real)
                        ->setCellValue('I' . $num, $v['stuName']);
                }

                $objPHPExcel->getActiveSheet()->setTitle('住宿人员明细表');
                $objPHPExcel->setActiveSheetIndex(0);
                //  header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . '住宿人员明细表' . '.xls"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
                exit;
            }

            $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
            $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
            $pre_page = ($page - 1) * $count;
            $limit_page = "$pre_page,$count";
            $key = $_POST['key'];
            if (!empty($key)) {
                $where['number|floor|dormNumber|dormName'] = array('like', "%{$key}%");
            }
            $order = $_POST['order']; //排序
            $according = $_POST['according'];
            if (!empty($order) && !empty($according)) {
                $order = $according . ' ' . stristr($order, 'ending', true);
            } else {
                $order = 'number desc';
            }
            $data = D('Dorm')->where($where)
                ->field('id as dormId,number,name,floor,dormNumber,dormName,dormType,capacity,stuName')
                ->order($order)
                ->limit($limit_page)
                ->select();
            if (!$data) {
                $this->ajaxReturn($response);
            }
            foreach ($data as &$v) {
                $temp = empty($v['stuName']) ? array() : explode(',', $v['stuName']);
                $v['stuName'] = $temp;
                $v['real'] = count($temp);
            }
            $total = (int)D('Dorm')->where($where)
                ->count();
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        }
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
    }


    /*********************************************宿舍分配************************************************/
    //各流程变化
    private function changeProcess(Array $process, $planId)
    {
        $steps = implode(',', array_keys($process));

        $sql = "UPDATE mks_dorm_process SET status = CASE step ";
        foreach ($process as $step => $status) {
            $sql .= sprintf("WHEN %d THEN %d ", $step, $status);
        }
        $sql .= "END WHERE step in ({$steps}) and scId={$this->scId} and planId={$planId}";

        M('')->execute($sql);
    }

    //得到流程
    private function getProcess($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'planId' => $planId,
            'scId' => $this->scId
        );
        $data = D('DormProcess')->where($where)->field('id,name,status')->select();
        if ($data) {
            $response['status'] = 1;
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);
    }

    //得到年级 //已测试
    private function getGrade()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $data = D('Grade')->where(array('scId' => $this->scId))->field('gradeid as id,name,znName')->select();

        if ($data) {
            // $data = array_merge($data,array(array('id'=>0,'name'=>0,'znName'=>'全部年级')));
            $response['status'] = 1;
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);
    }

    //宿舍分配方案 //已测试
    public function dormPlan()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];

        if (isset($type)) {
            $rs = false;
            if ($type == 'add') {
                $data = $_POST;
                $find = D('DormPlan')->where(array('scId' => $this->scId,
                    'creator' => $this->user['name'],
                    'creatorId' => $this->uId,
                    'name' => $data['name']))->find();
                if ($find) {
                    $response['msg'] = '此方案名已存在';
                    $response['status'] = 0;
                    $this->ajaxReturn($response);
                }

                $stu = $this->getList($data['gradeId']);
                if (empty($stu)) {
                    $response['msg'] = '所选年级暂无住校学生';
                    $response['status'] = 0;
                    $this->ajaxReturn($response);
                }

                $plan = array(
                    'name' => $data['name'],
                    'gradeId' => $data['gradeId'],
                    'notice' => $data['notice'],
                    'currentStatus' => '新创建',
                    'createTime' => time(),
                    'creator' => $this->user['name'],
                    'creatorId' => $this->uId,
                    'scId' => $this->scId
                );
                $planId = D('DormPlan')->data($plan)->add();
                if (!$planId) {
                    $response['msg'] = '添加失败';
                    $this->ajaxReturn($response);
                }
                $process = array(
                    array('name' => '设置分配人员名单', 'status' => 2, 'step' => 1, 'url' => '',
                        'planId' => $planId, 'scId' => $this->scId),
                    array('name' => '设置分配宿舍信息', 'status' => -1, 'step' => 2, 'url' => '',
                        'planId' => $planId, 'scId' => $this->scId),
                    array('name' => '指定学生到宿舍', 'status' => -1, 'step' => 3, 'url' => '',
                        'planId' => $planId, 'scId' => $this->scId),
                    array('name' => '快速分配宿舍', 'status' => -1, 'step' => 4, 'url' => '',
                        'planId' => $planId, 'scId' => $this->scId),
                    array('name' => '发布宿舍信息', 'status' => -1, 'step' => 5, 'url' => '',
                        'planId' => $planId, 'scId' => $this->scId),
                    array('name' => '手动调整', 'status' => -1, 'step' => 6, 'url' => '',
                        'planId' => $planId, 'scId' => $this->scId),
                    array('name' => '打印报表', 'status' => -1, 'step' => 7, 'url' => '',
                        'planId' => $planId, 'scId' => $this->scId));
                $rs = D('DormProcess')->addAll($process);
                if (!$rs) {
                    $response['msg'] = '添加流程失败';
                    $this->ajaxReturn($response);
                }

                $list = array();
                foreach ($stu as $k => $v) {
                    $list[] = array(
                        'stuId' => $v['stuId'],
                        'userId' => $v['userId'],
                        'stuName' => $v['name'],
                        'planId' => $planId,
                        'scId' => $this->scId,
                        'isAssign' => 0,
                        'sex' => $v['sex'],
                        'grade' => $v['grade'],
                        'class' => $v['className'],
                        'phone' => $v['phone'],
                        'idCard' => $v['idCard'],
                        'remark' => $v['remark'],
                        'regNumber' => $v['regNumber'],
                        'perAddress' => $v['perAddress'],
                        'midExam' => $v['midExam'],
                        'admCategory' => $v['admCategory']
                    );
                }
                $rs = D('DormAssign')->addAll($list);
                if (!$rs) {
                    $response['msg'] = '添加分配人员失败';
                    $this->ajaxReturn($response);
                }
                $rs = D('DormMap')->data(array('planId' => $planId, 'scId' => $this->scId))->add();
            } elseif ($type == 'del') {
                $planId = $_POST['planId'];
                $rs = D('DormPlan')->where(array('id' => $planId))->delete();
            } elseif ($type == 'clean') { //清空人员
                $planId = $_POST['planId'];
                $rs = D('DormMap')->where(array('planId' => $planId))->save(array(
                    'assignId' => ''
                ));
            } elseif ($type == 'edit') { //编辑
                $data = $_POST;
                $plan = array(
                    'name' => $data['name'],
                    // 'grade' => $data['grade'],
                    'notice' => $data['notice'],
                    'lastRecordTime' => time());
                $rs = D('DormPlan')->where(array('id' => I('post.id')))->data($plan)->save();
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
                $this->ajaxReturn($response);
            }
            $response['status'] = 1;
            $response['msg'] = '操作成功';
            $this->ajaxReturn($response);
        }
        $where = array('p.scId' => $this->scId);
        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        $key = $_POST['key'];
        if (!empty($key)) {
            $where['p.name|p.currentStatus'] = array('like', "%{$key}%");
        }

        $data = D('DormPlan p')
            ->join('mks_dorm_map m ON p.id=m.planId', 'LEFT')
            ->where($where)
            ->field('p.id,p.name,p.gradeId,p.createTime,p.notice,p.currentStatus,m.assignId,m.dormId')
            ->limit($limit_page)
            ->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }
        foreach ($data as &$v) {
            $v['stuNumber'] = empty($v['assignId']) ? 0 : count(explode(',', $v['assignId']));
            $v['dormNumber'] = empty($v['dormId']) ? 0 : count(explode(',', $v['dormId']));
            unset($v['assignId']);
            unset($v['dormId']);
        }
        $total = (int)D('DormPlan p')
            ->join('mks_dorm_map m ON p.id=m.planId', 'LEFT')
            ->where(array('p.scId' => $this->scId))
            ->count();
        $response['total'] = (int)$total;
        $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        $response['data'] = $data;
        $response['status'] = 1;
        $this->ajaxReturn($response);
    }

    //得到一个具体的方案
    public function onePlan($id)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $grade = D('Grade')->where(array('scId' => $this->scId))->field('gradeid,znName')->select();
        $map[] = array();
        foreach ($grade as $k => $v) {
            $map[$v['gradeid']] = $v['znName'];
        }
        $data = D('DormPlan p')
            ->where(array('p.id' => $id))
            ->field('p.id,p.name,p.gradeId,p.notice')
            ->find();
        if (!$data) {
            $this->ajaxReturn($response);
        }
        $data['grade'] = $map[$data['gradeId']];
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
    }

    private function getList($gradeId = 0)
    {
        $where = array(
            'si.scId' => $this->scId,
        );
        if ($gradeId > 0) {
            $where = array(
                'si.gradeId' => $gradeId
            );
        }
        $where['si.isResSchool'] = '是';
        $where['si.isAtSchool'] = '是';
        $data = D('StudentInfo si')
            ->join('mks_school_rollinfo sr ON si.userId=sr.userId', 'LEFT')
            ->join('mks_cen_reg_info cri ON si.userId=cri.userId', 'LEFT')
            ->where($where)
            ->field('si.id as stuId,si.userId,si.name,si.sex,si.grade,si.className,si.phone,si.idCard,
            si.isResSchool,si.remark,sr.regNumber,cri.perAddress,sr.midExam,sr.admCategory')
            ->select();

        $response = array();
        if ($data) {
            $response = $data;
        }
        return $response;
        //  $this->ajaxReturn($response);
    }

    //(设置分配人员名单)候选名单 //已测试
    private function getAssign($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'planId' => $planId,
            'scId' => $this->scId
        );

        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();

        $map = array();
        foreach ($grade as $k => $v) {
            $map[$v['name']] = $v['znName'];
        }

        $gradeId = D('DormPlan')->where(array('scId' => $this->scId, 'id' => $planId))->getField('gradeId');

        $inSchoolStu = $this->getList($gradeId);

        $inId = array_map(function ($v) {  //住校的id
            return $v['userId'];
        }, $inSchoolStu);
        if (empty($inId)) {
            $inId = array();
        }
        $data = D('DormAssign')->where($where)
            ->field('id,userId,stuName as name,sex,grade,class,phone,idCard,remark,regNumber,perAddress,midExam,admCategory')
            ->select();
        $dormId = array_map(function ($v) {  //在宿舍的id
            return $v['userId'];
        }, $data);
        if (empty($dormId)) {
            $dormId = array();
        }
        $tempId1 = array_diff($inId, $dormId);

        if (!empty($tempId1)) { //存在  有新添的学生
            $list = array();
            foreach ($inSchoolStu as $k => $v) {
                if (in_array($v['userId'], $tempId1)) {
                    $list[] = array(
                        'stuId' => $v['stuId'],
                        'userId' => $v['userId'],
                        'stuName' => $v['name'],
                        'planId' => $planId,
                        'scId' => $this->scId,
                        'isAssign' => 0,
                        'sex' => $v['sex'],
                        'grade' => $v['grade'],
                        'class' => $v['className'],
                        'phone' => $v['phone'],
                        'idCard' => $v['idCard'],
                        'remark' => $v['remark'],
                        'regNumber' => $v['regNumber'],
                        'perAddress' => $v['perAddress'],
                        'midExam' => $v['midExam'],
                        'admCategory' => $v['admCategory']
                    );
                }
            }
            D('DormAssign')->addAll($list);
        }
        $tempId2 = array_diff($dormId, $inId);


        if (!empty($tempId2)) { //学生异动 删除名单的
            $assignId1 = D('DormAssign')->where(array('planId' => $planId, 'userId' => array('in', $tempId2), 'scId' => $this->scId))
                ->field('id')->select();
            $assignId1 = array_map(function ($v) {
                return $v['id'];
            }, $assignId1);
            D('DormAssign')->where(array('planId' => $planId, 'userId' => array('in', $tempId2), 'scId' => $this->scId))->delete();
            $dormMap = D('DormMap')->where(array('scId' => $this->scId, 'planId' => $planId))->find();
            if ($dormMap) {
                $assignId = empty($dormMap['assignId']) ? array() : explode(',', $dormMap['assignId']);
                if (!empty($assignId)) {
                    $assignId = array_diff($assignId, $assignId1);
                    $data = implode(',', $assignId);
                    D('DormMap')->where(array('scId' => $this->scId, 'planId' => $planId))->save(array('assignId' => $data));
                }
            }
        }

        $data = D('DormAssign')->where($where)
            ->field('id,userId,stuName as name,sex,grade,class,phone,idCard,remark,regNumber,perAddress,midExam,admCategory')
            ->select();

        if (!$data) {
            $this->ajaxReturn($response);
        }
        $res = array();
        foreach ($data as $k => $v) {
            if (!isset($res[$v['grade']]))
                $res[$v['grade']] = array(
                    'name' => $map[$v['grade']],
                    'child' => array()
                );
            if (!isset($res[$v['grade']]['child'][$v['class']]))
                $res[$v['grade']]['child'][$v['class']] = array(
                    'name' => $v['class'],
                    'child' => array()
                );
            $res[$v['grade']]['child'][$v['class']]['child'][] = array(
                'id' => $v['id'],
                'name' => $v['name'],
                'sex' => $v['sex'],
                'grade' => $map[$v['grade']],
                'class' => $v['class'],
                'phone' => $v['phone'],
                'idCard' => $v['idCard'],
                'remark' => $v['remark'],
                'regNumber' => $v['regNumber'],
                'perAddress' => $v['perAddress'],
                'midExam' => $v['midExam'],
                'admCategory' => $v['admCategory'],
            );
        }
        foreach ($res as $k => $v) {
            sort($res[$k]['child']);
        }
        sort($res);
        $response['status'] = 1;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //设置分配人员名单 //已测试
    public function assignList($planId)
    {
        $response['status'] = 0;
        $response['data'] = array();
        $type = $_POST['type'];
        if (isset($type)) {
            $rs = false;
            if ($type == 'operate') {
                $assignId = $_POST['assignId'];
                $assignId = empty($assignId) ? '' : implode(',', $assignId);
                $res = D('DormMap')->where(array('planId' => $planId, 'scId' => $this->scId))->find();
                if ($res) {
                    $rs = D('DormMap')->where(array('planId' => $planId, 'scId' => $this->scId))
                        ->data(array('assignId' => $assignId))->save();
                } else {
                    $rs = D('DormMap')
                        ->data(array('planId' => $planId, 'scId' => $this->scId, 'assignId' => $assignId))
                        ->add();
                }
                $process = array(
                    1 => 1, 2 => 2
                );
                $this->changeProcess($process, $planId);
                D('DormPlan')->where(array('id' => $planId))->data(array('currentStatus' => '设置分配人员名单'))->save();
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
            } else {
                $response['status'] = 1;
                $response['msg'] = '操作成功';
            }
            $this->ajaxReturn($response);
        }

        $data = array();
        $assignId = D('DormMap')->where(array('planId' => $planId, 'scId' => $this->scId))->getField('assignId');

        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $map = array();
        foreach ($grade as $k => $v) {
            $map[$v['name']] = $v['znName'];
        }
        if (!empty($assignId)) {
            $assignId = explode(',', $assignId);
            $data = D('DormAssign')->where(array('planId' => $planId, 'scId' => $this->scId, 'id' => array('in', $assignId)))
                ->field('id,stuName as name,sex,grade,class,phone,idCard,remark,regNumber,perAddress,midExam,admCategory')->select();
            foreach ($data as &$v) {
                $v['grade'] = $map[$v['grade']];
            }
        }
        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
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
                ->setCellValue('A1', '考号')
                ->setCellValue('B1', '姓名')
                ->setCellValue('C1', '性别')
                ->setCellValue('D1', '总分')
                ->setCellValue('E1', '年级')
                ->setCellValue('F1', '班级')
                ->setCellValue('G1', '手机号')
                ->setCellValue('H1', '身份证号');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            foreach ($data as $k => $v) {
                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['regNumber'])
                    ->setCellValue('B' . $num, $v['name'])
                    ->setCellValue('C' . $num, $v['sex'])
                    ->setCellValue('D' . $num, $v['midExam'])
                    ->setCellValue('E' . $num, $v['grade'])
                    ->setCellValue('F' . $num, $v['className'])
                    ->setCellValue('G' . $num, $v['phone'])
                    ->setCellValue('H' . $num, $v['idCard']);
            }

            $objPHPExcel->getActiveSheet()->setTitle('宿舍分配人员表');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '宿舍分配人员表' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        if ($data) {
            $response['status'] = 1;
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);
    }

    //设置宿舍分配信息 //已测试
    public function assignDorm($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];
        if (isset($type)) {
            $rs = false;
            if ($type == 'operate') {
                $dormId = I('post.dormId');
                $where = array(
                    'planId' => $planId,
                    'scId' => $this->scId,
                    'dormId' => array('not in', $dormId),
                    'isAssign' => 1);
                $dormId = empty($dormId) ? '' : implode(',', $dormId);
                $rs = D('DormMap')->where(array('planId' => $planId, 'scId' => $this->scId))
                    ->data(array('dormId' => $dormId))->save();

                D('DormAssign')->where($where)->save(array('dormId' => '', 'isAssign' => 0)); //将指定到被删除宿舍的学生还原
                $process = array(
                    2 => 1, 3 => 3, 4 => 2
                );
                $this->changeProcess($process, $planId);
                D('DormPlan')->where(array('id' => $planId))->data(array('currentStatus' => '设置分配宿舍信息'))->save();
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
            } else {
                $response['status'] = 1;
                $response['msg'] = '操作成功';
            }
            $this->ajaxReturn($response);
        }

        $dormId = D('DormMap')->where(array('planId' => $planId, 'scId' => $this->scId))->getField('dormId');
        if (!empty($dormId)) {
            $dormId = explode(',', $dormId);
            $where = array(
                'scId' => $this->scId,
                'id' => array('in', $dormId),
            );
            $data = D('Dorm')
                ->where($where)
                ->field('id as dormId,number,name as buildName,floor,dormNumber,dormName as name,dormType,capacity,stuId')
                ->select();
            if (!$data) {
                $this->ajaxReturn($response);
            }
            foreach ($data as &$v) {
                $temp = empty($v['stuId']) ? array() : explode(',', $v['stuId']);
                $v['stuId'] = count($temp);
            }

            $download = $_REQUEST['export'];
            if ($download == 'ensure') {
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
                    ->setCellValue('A1', '宿舍楼名称')
                    ->setCellValue('B1', '栋号')
                    ->setCellValue('C1', '楼层')
                    ->setCellValue('D1', '宿舍号')
                    ->setCellValue('E1', '宿舍类型')
                    ->setCellValue('F1', '容纳人数');
                $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

                foreach ($data as $k => $v) {
                    $num = $k + 2;
                    if ($v['dormType'] == 1) {
                        $dormType = '女生宿舍';
                    } elseif ($v['dormType'] == 2) {
                        $dormType = '男生宿舍';
                    } elseif ($v['dormType'] == 3) {
                        $dormType = '混合宿舍';
                    } else {
                        $dormType = '其他';
                    }
                    $objPHPExcel->setActiveSheetIndex(0)
                        //Excel的第A列，uid是你查出数组的键值，下面以此类推
                        ->setCellValue('A' . $num, $v['buildName'])
                        ->setCellValue('B' . $num, $v['number'])
                        ->setCellValue('C' . $num, $v['floor'])
                        ->setCellValue('D' . $num, $v['dormNumber'])
                        ->setCellValue('E' . $num, $dormType)
                        ->setCellValue('F' . $num, $v['capacity']);
                }

                $objPHPExcel->getActiveSheet()->setTitle('宿舍分配宿舍表');
                $objPHPExcel->setActiveSheetIndex(0);
                //  header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . '宿舍分配宿舍表' . '.xls"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
                exit;
            }
            $response['status'] = 1;
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);
    }

    //(指定学生到宿舍)下拉框 //已测试
    private function requestInfo($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $dormId = D('DormMap')
            ->where(array('planId' => $planId, 'scId' => $this->scId))
            ->getField('dormId');
        if (!$dormId || empty($dormId)) {
            $this->ajaxReturn($response);
        }
        $dormId = explode(',', $dormId);
        $map = array(
            'scId' => $this->scId,
            'id' => array('in', $dormId)
        );

        $dorm = D('Dorm')->where($map)
            ->field('id,number,name,floor,dormType')
            ->select();
        if (!$dorm) {
            $this->ajaxReturn($response);
        }
        $res = array();
        $map = array(
            1 => '女生宿舍', 2 => '男生宿舍', 3 => '混合宿舍', 4 => '其它',
        );
        foreach ($dorm as $k => $v) {
            if (!isset($res[$v['number']])) {
                $res[$v['number']] = array(
                    'name' => $v['number'],
                    'child' => array()
                );
            }
            if (!isset($res[$v['number']]['child'][$v['floor']])) {
                $res[$v['number']]['child'][$v['floor']] = array(
                    'name' => $v['floor'],
                    'child' => array()
                );
            }
            $res[$v['number']]['child'][$v['floor']]['child'][] = array(
                'name' => $v['dormType'], 'znName' => $map[$v['dormType']]
            );
        }

        foreach ($res as $k => $v) {
            sort($res[$k]['child']);
        }
        sort($res);
        $response['status'] = 1;
        $response['data'] = $res;
        $this->ajaxReturn($response);

    }

    //(指定学生到宿舍)宿舍列表 //已测试
    private function getPlanDorm($param)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        $dormId = D('DormMap')
            ->where(array('planId' => $param['planId'], 'scId' => $this->scId))
            ->getField('dormId');
        if (!$dormId || empty($dormId)) {
            $this->ajaxReturn($response);
        }
        $dormId = explode(',', $dormId);

        $map = array(
            'number' => $param['number'],
            'floor' => $param['floor'],
            'dormType' => $param['dormType'],
            'scId' => $this->scId,
            'dormDean' => 0,
            'id' => array('in', $dormId)
        );

        $data = D('DormAssign')->where(array('scId' => $this->scId, 'isAssign' => 1, 'planId' => $param['planId']))->group('dormId')->field('dormId,count(id) as total')->select();
        foreach ($data as $k => $v) {
            $nMap[$v['dormId']] = $v['total'];
        }

        $dorm = D('Dorm')->where($map)
            ->field('id as dormId,floor,number,dormNumber,dormName,dormType,teaName,capacity')
            ->select();
        foreach ($dorm as $k => $v) {
            $dorm[$k]['total'] = (int)$nMap[$v['dormId']];
        }
        if (!$dorm) {
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['data'] = $dorm;
        $this->ajaxReturn($response);

    }

    //(指定学生到宿舍)候选名单 //已测试
    private function getPlanStu($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        $assignId = D('DormMap')
            ->where(array('planId' => $planId, 'scId' => $this->scId))
            ->getField('assignId');
        if (!$assignId || empty($assignId)) {
            $this->ajaxReturn($response);
        }
        $assignId = explode(',', $assignId);
        $where = array(
            'planId' => $planId,
            'id' => array('in', $assignId),
            'scId' => $this->scId,
            'isAssign' => 0
        );
        $data = D('DormAssign')->where($where)
            ->field('id,stuId,grade,class,stuName,sex')
            ->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }

        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $map = array();
        foreach ($grade as $k => $v) {
            $map[$v['name']] = $v['znName'];
        }

        $res = array();
        foreach ($data as $k => $v) {
            if (!isset($res[$v['grade']]))
                $res[$v['grade']] = array(
                    'name' => $map[$v['grade']],
                    'child' => array()
                );
            if (!isset($res[$v['grade']]['child'][$v['class']]))
                $res[$v['grade']]['child'][$v['class']] = array(
                    'name' => $v['class'],
                    'child' => array()
                );
            $res[$v['grade']]['child'][$v['class']]['child'][] = array(
                'id' => $v['id'],
                'stuId' => $v['stuId'],
                'name' => $v['stuName'],
                'sex' => $v['sex'],
            );
        }

        foreach ($res as $k => $v) {
            sort($res[$k]['child']);
        }
        sort($res);
        $response['status'] = 1;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //指定学生到宿舍 //已测试
    public function assignStu($planId, $dormId)
    {
        $response['status'] = 0;
        $response['data'] = array();
        $type = $_POST['type'];
        if (isset($type)) {
            $rs = false;
            if ($type == 'save') {
                $stuId = $_POST['stuId'];
                D('DormAssign')
                    ->where(array('scId' => $this->scId, 'planId' => $planId, 'isAssign' => 1, 'dormId' => $dormId))
                    ->save(array('dormId' => '', 'isAssign' => 0));
                $rs = true;
                if (!empty($stuId)) {
                    $change = array(
                        'dormId' => $dormId,
                        'isAssign' => 1
                    );
                    $rs = D('DormAssign')
                        ->where(array('scId' => $this->scId, 'planId' => $planId, 'stuId' => array('in', $stuId)))
                        ->save($change);
                }
            } elseif ($type == 'clean') {
                //$dormId = $_POST['dormId'];
                $rs = D('DormAssign')
                    ->where(array('scId' => $this->scId, 'planId' => $planId, 'dormId' => $dormId))
                    ->data(array('dormId' => '', 'isAssign' => 0))
                    ->save();
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
            } else {
                $response['status'] = 1;
                $response['msg'] = '操作成功';
            }
            $this->ajaxReturn($response);
        }

        //$dormId = $_POST['dormId'];
        $data = D('DormAssign')
            ->where(array('dormId' => $dormId, 'planId' => $planId, 'scId' => $this->scId, 'isAssign' => 1))
            ->field('id,stuId,stuName as name,sex')->select();
        $response['data'] = array();
        if ($data) {
            $response['status'] = 1;
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);
    }

    //(快速分配宿舍)生成分配 //已测试
    /*云青青兮欲雨,水澹澹兮生烟*/
    private function distribute($planId, $scoreOrder, $examId = '', $classOrder, $rule = '')
    {
        $data = D('DormMap')->where(array('scId' => $this->scId, 'planId' => $planId))->find();
        if (empty($data['assignId']) || empty($data['dormId']))
            return false;
        $assignId = explode(',', $data['assignId']); //学生id

        $dormId = explode(',', $data['dormId']); //宿舍id
        //分配的所有人员 $stu
        $stu = D('DormAssign')->where(array('id' => array('in', $assignId), 'isAssign' => 0, 'scId' => $this->scId))
            ->field('id,userId,sex,class,grade,midExam as score')->select();

        $totalStu = count($stu);
        //分配的所有宿舍 $dormList
        $tempList = D('Dorm')->where(array('id' => array('in', $dormId), 'dormDean' => 0))->field('id,capacity,dormType')->select();
        $tempDormId = D('DormAssign')
            ->where(array('dormId' => array('in', $dormId), 'planId' => $planId, 'isAssign' => 1, 'scId' => $this->scId))
            ->field('dormId')->select();
        $tempDormId = array_map(function ($v) {
            return $v['dormId'];
        }, $tempDormId);

        $dormList = array();
        foreach ($tempList as $k => $v) { //将宿舍转为键值对
            $dormList[$v['id']] = array(
                'capacity' => (int)$v['capacity'],
                'stuId' => array(),
                'dormType' => (int)$v['dormType']
            );
        }
        $tempId = array_keys($dormList);
        foreach ($tempDormId as $k => $v) { //将指定了宿舍容量排除  //已测试
            if (in_array($v, $tempId)) {
                $dormList[$v]['capacity'] -= 1;
            }
        }


        if ($scoreOrder != 'random') { //成绩规则随机
            if (!empty($examId)) {  //调用考试
                if ($examId > 0) {//不是中考分数
                    $userId = array_map(function ($v) {
                        return (int)$v['userId'];
                    }, $stu);
                    //  $order='score '.$scoreOrder;
                    $temp_exam = D('ExaminationResults')//考试成绩
                    ->where(array('userid' => array('in', $userId), 'scId' => $this->scId, 'examinationid' => $examId))
                        ->group('userid')
                        // ->order($order)
                        ->field('userid as userId,sum(results) as score')->select();
                    foreach ($temp_exam as $k => $v) {
                        $exam[$v['userId']] = $v['score'];
                    }
                    foreach ($stu as $k1 => $v1) {  //将成绩赋值
                        $stu[$k1]['score'] = empty($exam[$v1['userId']]) ? 0 : $exam[$v1['userId']];
                    }
                }
                if ($scoreOrder == 'asc') {
                    usort($stu, function ($a, $b) {
                        $al = (int)$a['score'];
                        $bl = (int)$b['score'];
                        if ($al == $bl)
                            return 0;
                        return ($al < $bl) ? -1 : 1; //升序
                    });
                } elseif ($scoreOrder == 'desc') {
                    usort($stu, function ($a, $b) {
                        $al = (int)$a['score'];
                        $bl = (int)$b['score'];
                        if ($al == $bl)
                            return 0;
                        return ($al > $bl) ? -1 : 1; //降序
                    });
                }
            }
        }

        //班级规则
        $stuList = array(); //将男女分开后的人员  //已测试
        if ($classOrder != 'random') { //将人员放在不同班级并排序
            foreach ($stu as $k => $v) {  //将性别分开
                $stuList[$v['sex']][$v['grade'] . $v['class']][] = array(
                    'id' => $v['id'],
                    'class' => $v['class'],
                    'score' => $v['score']
                );
            }
            if ($classOrder == 'desc') { //班级降序
                krsort($stuList['男']);
                krsort($stuList['女']);
            } else { //班级升序
                ksort($stuList['男']);
                ksort($stuList['女']);
            }
        } else { //将人员放在同一个班级
            foreach ($stu as $k => $v) {  //将性别分开
                $stuList[$v['sex']][0][] = array(
                    'id' => $v['id'],
                    'class' => $v['class'],
                    'score' => $v['score']
                );
            }
        }

        $sexMap = array(
            '男' => 2,
            '女' => 1
        );


        //将宿舍分配给人员 $dormList stuList
        if (!empty($rule)) { //班级规则分配
            if ($rule != 'remnant') {
                foreach ($stuList as $sex => $class) {
                    foreach ($class as $k => $v) {
                        $total = count($stuList[$sex][$k]); //性别分类下的每个班级的人数
                        foreach ($dormList as $k1 => $v1) {
                            //宿舍类型和学生性别判断
                            if (in_array($v1['dormType'], array(1, 2))) {
                                if ($sexMap[$sex] != $v1['dormType'])
                                    continue;
                            }
                            if ($total <= 0)
                                break;
                            //有剩余人数
                            $capacity = (int)$v1['capacity']; //宿舍容纳人数

                            if (count($v1['stuId']) == 0) { //宿舍为空宿舍 不做判断
                                $dormList[$k1]['classId'] = $k;
                            } else { //宿舍不为空 判断性别 判断班级
                                if (count($v1['stuId']) >= $capacity) { //宿舍满员 跳出
                                    continue;
                                }
                                if ($rule == 'notCross') { //班级不交叉 性别不同 班级不同
                                    if(!in_array($v1['dormType'], array(1, 2))){ //宿舍不是男女宿舍
                                        //同一个班级
                                        if($k==$dormList[$k1]['classId']){
                                            $capacity = $capacity - count($v1['stuId']);//宿舍剩余人数
                                        }
                                    } else{
                                        continue;
                                    }
                                } elseif ($rule == 'continue') { //班级连续 判断性别
                                    $capacity = $capacity - count($v1['stuId']);//宿舍剩余人数
                                } else {
                                    return false;
                                }
                            }
                            $total -= $capacity; //分配人数
                            if ($total >= 0) { //学生人数>宿舍人数
                                $temp = array_splice($stuList[$sex][$k], 0, $capacity);
                            } else {
                                $temp = array_splice($stuList[$sex][$k], 0);
                            }
                            foreach ($temp as $k2 => $v2) { //将学生分配给宿舍
                                $dormList[$k1]['stuId'][] = $v2['id'];
                            }
                          //  $dormList[$k1]['sex'] = $sex;
                            if ($total <= 0) { //学生分完
                                break;
                            }
                        }
                    }
                }
            } else { //班级剩余成员统一另外分
                foreach ($stuList as $sex => $class) {
                    $remnant = array();
                    foreach ($class as $k => $v) {
                        $total = count($stuList[$sex][$k]); //性别分类下的每个班级的人数
                        foreach ($dormList as $k1 => $v1) {
                            //宿舍类型和学生性别判断
                            if (in_array($v1['dormType'], array(1, 2))) {
                                if ($sexMap[$sex] != $v1['dormType'])
                                    continue;
                            }
                            if ($total <= 0)
                                break;
                            //有剩余人数
                            $capacity = (int)$v1['capacity']; //宿舍人数
                            if ($total < $capacity) { //人数为剩余
                                foreach ($stuList[$sex][$k] as $k3 => $v3) {
                                    if (!isset($remnant[$sex]))
                                        $remnant[$sex] = array();
                                    $remnant[$sex][] = $v3['id'];
                                }
                                break;
                            }
                            if (count($v1['stuId']) == 0) { //宿舍为空宿舍 不做判断

                            } else { //宿舍不为空 不做判断
                                continue;
                            }

                            $total -= $capacity; //分配人数
                            $temp = array_splice($stuList[$sex][$k], 0, $capacity);

                            foreach ($temp as $k2 => $v2) { //将学生分配给宿舍
                                $dormList[$k1]['stuId'][] = $v2['id'];
                            }
                            $dormList[$k1]['sex'] = $sex;
                            if ($total <= 0) { //学生分完
                                break;
                            }
                        }
                    }

                    $total_remnant = count($remnant[$sex]);

                    if ($total_remnant > 0) {
                        foreach ($dormList as $k1 => $v1) {
                            //宿舍类型和学生性别判断
                            if (in_array($v1['dormType'], array(1, 2))) {
                                if ($sexMap[$sex] != $v1['dormType'])
                                    continue;
                            }
                            if ($total_remnant <= 0)
                                break;
                            //有剩余人数
                            $capacity = (int)$v1['capacity']-count($v1['stuId']); //宿舍人数

                            if (count($v1['stuId']) == 0) { //宿舍为空宿舍 不做判断

                            } else { //宿舍不为空 不做判断
                                if(in_array($v1['dormType'], array(1, 2))){ //是否为男女宿舍
                                    continue;
                                }
                            }
                            $total_remnant -= $capacity; //分配人数
                            if ($total_remnant >= 0) { //学生人数>宿舍人数
                                $temp = array_splice($remnant[$sex], 0, $capacity);
                            } else {
                                $temp = array_splice($remnant[$sex], 0);
                            }

                            foreach ($temp as $k2 => $v2) { //将学生分配给宿舍
                                $dormList[$k1]['stuId'][] = $v2;
                            }
                            $dormList[$k1]['sex'] = $sex;
                            if ($total_remnant <= 0) { //学生分完
                                break;
                            }
                        }
                    }
                }
            }
        } else { //班级规则随机  //已测试
            foreach ($stuList as $sex => $class) {
                foreach ($class as $k => $v) {
                    $total = count($stuList[$sex][$k]); //性别分类下的人数
                    foreach ($dormList as $k1 => $v1) {
                        //宿舍类型和学生性别判断
                        if (in_array($v1['dormType'], array(1, 2))) {
                            if ($sexMap[$sex] != $v1['dormType'])
                                continue;
                        }
                        //有剩余人数
                        if ($total <= 0)
                            break;
                        $capacity = (int)$v1['capacity']; //宿舍人数
                        if (count($v1['stuId']) == 0) { //宿舍为空宿舍
                            $total -= $capacity; //分配人数
                            if ($total >= 0) { //学生人数>宿舍人数
                                $temp = array_splice($stuList[$sex][$k], 0, $capacity);
                            } else {
                                $temp = array_splice($stuList[$sex][$k], 0);
                            }
                            foreach ($temp as $k2 => $v2) { //将学生分配给宿舍
                                $dormList[$k1]['stuId'][] = $v2['id'];
                            }
                            if ($total <= 0) { //学生分完
                                break;
                            }
                        } else { //宿舍不为空 判断性别 性别不同
                            continue;
                        }
                    }
                }
            }
        }
        return $dormList;
    }

    //(快速分配宿舍)考试列表 //已测试
    private function getExam()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'scId' => $this->scId
        );
        $data = D('Examination')
            ->where($where)
            ->field('examinationid as examId,examination')->select();

        if ($data) {
            $response['status'] = 1;
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);
    }

    //快速分配宿舍 //已测试
    public function quickAssign($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];
        if (isset($type)) {
            if ($type == 'quickAssign') {
                $scoreOrder = $_POST['scoreOrder'];
                $examId = $_POST['examId'];
                $classOrder = $_POST['classOrder'];
                $rule = $_POST['rule'];
                $res = $this->distribute($planId, $scoreOrder, $examId, $classOrder, $rule);

                if (!$res) {
                    $response['msg'] = '分配出错';
                    $this->ajaxReturn($response);
                }
                $dormId = array();
                $assignId = array();
                foreach ($res as $k => $v) {
                    $dormId[] = $k;
                    foreach ($v['stuId'] as $k1 => $v1) {
                        $assignId[$v1] = $k;
                    }
                }

                $temp_dorm = D('Dorm')->where(array('id' => array('in', $dormId), 'dormDean' => 0))
                    ->field('id,teaName,number,name,floor,dormNumber')->select();
                $dorm = array();
                foreach ($temp_dorm as $k => $v) {
                    $dorm[$v['id']] = $v;
                }

                $temp_id = D('DormMap')
                    ->where(array('planId' => $planId, 'scId' => $this->scId))
                    ->getField('assignId');
                if (!$temp_id || empty($temp_id)) {
                    $this->ajaxReturn($response);
                }

                $temp_id = explode(',', $temp_id);

                $assign = D('DormAssign')->where(array('id' => array('in', $temp_id)))
                    ->field('id,dormId,stuName,isAssign,sex,grade,class,phone,idCard,perAddress')->select();

                $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
                $grade_map = array();
                foreach ($grade as $k => $v) {
                    $grade_map[$v['name']] = $v['znName'];
                }

                foreach ($assign as &$v) {
                    if ($v['isAssign'] == 1)
                        $tId = $v['dormId'];
                    else {
                        $tId = $assignId[$v['id']];
                        $v['dormId'] = $tId;
                    }
                    $v['teaName'] = $dorm[$tId]['teaName'];
                    $v['number'] = $dorm[$tId]['number'];
                    $v['name'] = $dorm[$tId]['name'];
                    $v['floor'] = $dorm[$tId]['floor'];
                    $v['dormNumber'] = $dorm[$tId]['dormNumber'];
                }
                foreach ($assign as &$v) {
                    $v['grade'] = $grade_map[$v['grade']];
                }
                $this->ajaxReturn($assign);
            } elseif ($type == 'save') {
                $assign = I('post.assign');
                $val = '';
                foreach ($assign as $k => $v) {
                    $val .= '(' . "{$v['id']}," . "{$v['dormId']}," . "{$v['isAssign']}),";
                }
                $val = rtrim($val, ',');

                $sql = "insert into mks_dorm_assign (id,dormId,isAssign)
                          values {$val} on duplicate key update dormId=values(dormId),
                          isAssign=values(isAssign)";
                //$this->ajaxReturn(array($sql));

                $rs = M()->execute($sql);

                $response['msg'] = '操作失败';

                if ($rs === 0) {
                    $response['msg'] = '数据没有变动';
                }
                if ($rs) {
                    $process = array(
                        4 => 1, 5 => 2, 6 => 3, 7 => 3
                    );
                    $this->changeProcess($process, $planId);
                    D('DormPlan')->where(array('id' => $planId))->data(array('currentStatus' => '快速分配宿舍'))->save();
                    $response['status'] = 1;
                    $response['msg'] = '操作成功';
                }
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
        }

        $dormMap = D('DormMap')
            ->where(array('planId' => $planId, 'scId' => $this->scId))
            ->find();
        $assignId=$dormMap['assignId'];

        if (!$assignId || empty($assignId)) {
            $this->ajaxReturn($response);
        }
        $assignId = explode(',', $assignId);

        $map = array(
            'da.id' => array('in', $assignId),
            'da.scId' => $this->scId
        );

        $data = D('DormAssign da')
            ->join('mks_dorm d ON da.dormId=d.id', 'LEFT')
            ->where($map)
            ->field('da.id,da.dormId,da.stuName,da.isAssign,da.sex,da.grade,da.class,da.phone,da.idCard,da.perAddress,da.regNumber,
            d.teaName,d.number,d.name,d.floor,d.dormNumber')
            ->select();

        $download = $_REQUEST['export'];
        if ($download == 'ensure') {
            $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
            $map = array();
            foreach ($grade as $k => $v) {
                $map[$v['name']] = $v['znName'];
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
                ->setCellValue('A1', '考号')
                ->setCellValue('B1', '姓名')
                ->setCellValue('C1', '性别')
                ->setCellValue('D1', '年级')
                ->setCellValue('E1', '班级')
                ->setCellValue('F1', '手机号')
                ->setCellValue('G1', '户口所在地')
                ->setCellValue('H1', '生活老师')
                ->setCellValue('I1', '宿舍楼名称')
                ->setCellValue('J1', '栋号')
                ->setCellValue('K1', '楼层')
                ->setCellValue('L1', '宿舍号');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            foreach ($data as $k => $v) {

                $num = $k + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['regNumber'])
                    ->setCellValue('B' . $num, $v['stuName'])
                    ->setCellValue('C' . $num, $v['sex'])
                    ->setCellValue('D' . $num, $map[$v['grade']])
                    ->setCellValue('E' . $num, $v['class'])
                    ->setCellValue('F' . $num, $v['phone'])
                    ->setCellValue('G' . $num, $v['perAddress'])
                    ->setCellValue('H' . $num, $v['teaName'])
                    ->setCellValue('I' . $num, $v['name'])
                    ->setCellValue('J' . $num, $v['number'])
                    ->setCellValue('K' . $num, $v['floor'])
                    ->setCellValue('L' . $num, $v['dormNumber']);
            }

            $objPHPExcel->getActiveSheet()->setTitle('住宿快速分配表');
            $objPHPExcel->setActiveSheetIndex(0);
            //  header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . '住宿快速分配表' . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }

        if ($data) {
            $number=array(
                'male'=>0,
                'female'=>0,
                'other'=>0
            );
            if(!empty($dormMap['dormId'])){
                $dormIds=explode(',',$dormMap['dormId']);
                $tempData=D('Dorm')->where(array('id'=>array('in',$dormIds)))->field('dormType,capacity')->select();
                foreach ($tempData as $k=>$v){
                    if($v['dormType']==1){
                        $number['female']+=(int)$v['capacity'];
                    }elseif ($v['male']==2){
                        $number['other']+=(int)$v['capacity'];
                    }else{
                        $number['other']+=(int)$v['capacity'];
                    }
                }
            }
            $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
            $grade_map = array();
            $totalNumber=array(
                'male'=>0,
                'female'=>0
            );
            foreach ($grade as $k => $v) {
                $grade_map[$v['name']] = $v['znName'];
            }
            foreach ($data as &$v) {
                if($v['sex']=='男'){
                    $totalNumber['male']+=1;
                }else{
                    $totalNumber['female']+=1;
                }
                $v['grade'] = $grade_map[$v['grade']];
            }
            $diffNumber=($totalNumber['male']-$number['male'])+($totalNumber['female']-$number['female'])-$number['other'];
            $response['status'] = 1;
            $response['number'] = $diffNumber;
            $response['data'] = $data;
        }
        $this->ajaxReturn($response);
    }

    //发布宿舍信息 //已测试
    public function publish($planId)
    {
        $response = array(
            'status' => 0
        );
        $data = D('DormMap')->where(array('planId' => $planId, 'scId' => $this->scId))->find();
        if (empty($data['assignId']) || empty($data['dormId']))
            return false;
        $assignId = explode(',', $data['assignId']); //学生id
        $dormId = explode(',', $data['dormId']); //宿舍id
        $temp_dorm = D('Dorm')->where(array('id' => array('in', $dormId), 'dormDean' => 0))
            ->field('id,teaName,number,name,floor,dormNumber,stuId,stuName')->select();
        $dorm = array();
        $oldId = array();
        foreach ($temp_dorm as $k => $v) {
            $dorm[$v['id']] = $v;
            $temp = empty($v['stuId']) ? array() : explode(',', $v['stuId']);
            $oldId = array_merge($oldId, array_values($temp));
        }
        $temp_assign = D('DormAssign')->where(array('id' => array('in', $assignId), 'planId' => $planId, 'scId' => $this->scId))
            ->field('id,stuId,dormId,stuName')->select();
        $assign = array();
        $newId = array();
        $map = array();
        foreach ($temp_assign as $k => $v) {
            if (!empty($v['dormId'])) {
                $assign[$v['dormId']]['stuId'][] = $v['stuId'];
                $assign[$v['dormId']]['stuName'][] = $v['stuName'];
            }
            $newId[] = $v['stuId'];
            $map[$v['stuId']] = $v['dormId'];
        }
        //更新宿舍信息
        $dormVal = '';
        foreach ($assign as $k => $v) {
            $stuId = implode(',', $v['stuId']);
            $stuName = implode(',', $v['stuName']);
            $dormVal .= '(' . "{$k}," . "'{$stuId}'," . "'{$stuName}'),";
        }
        $dormVal = rtrim($dormVal, ',');
        $sql = "insert into mks_dorm (id,stuId,stuName)
                          values {$dormVal} on duplicate key update stuId=values(stuId),
                          stuName=values(stuName)";
        $rs = M()->execute($sql);
        //更新学生信息
        if ($rs) {
            $stuVal = '';
            $change = array_diff($oldId, $newId);
            foreach ($change as $k => $v) { //原来的清空
                $stuVal .= '(' . "{$v}," . "''," . "''," . "''),";
            }
            foreach ($newId as $k => $v) {
                $dorStore = $dorm[$map[$v]]['number'];
                $dorStorey = $dorm[$map[$v]]['floor'];
                $dorNumber = $dorm[$map[$v]]['dormNumber'];
                $stuVal .= '(' . "{$v}," . "'{$dorStore}'," . "'{$dorStorey}'," . "'{$dorNumber}'),";
            }
            $stuVal = rtrim($stuVal, ',');
            $sql = "insert into mks_student_info (id,dorStore,dorStorey,dorNumber)
                          values {$stuVal} on duplicate key update dorStore=values(dorStore),
                          dorStorey=values(dorStorey),dorNumber=values(dorNumber)";

            $rs = M()->execute($sql);
            if ($rs)
                D('DormPlan')->where(array('id' => $planId))->data(array('currentStatus' => '发布宿舍信息'))->save();
        }

        if (!$rs) {
            $response['msg'] = '失败';
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['msg'] = '成功';
        $this->ajaxReturn($response);
    }

    //(手动调整)宿舍信息列表
    private function getSelDorm($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $dormId = D('DormMap')->where(array('planId' => $planId, 'scId' => $this->scId))
            ->getField('dormId');

        $res = array();
        if (!empty($dormId)) {
            $dormId = explode(',', $dormId);
            $rs = D('Dorm')
                ->where(array('id' => array('in', $dormId), 'dormDean' => 0))
                ->field('id,number,dormType')->select();
            $map = array(
                1 => '女生宿舍', 2 => '男生宿舍', 3 => '混合宿舍', 4 => '其它',
            );
            if ($rs) {
                foreach ($rs as $k => $v) {
                    if (!isset($res[$v['dormType']])) {
                        $res[$v['dormType']] = array(
                            'name' => $map[$v['dormType']],
                            'value' => $v['dormType'],
                            'child' => array()
                        );
                    }
                    if (!isset($res[$v['dormType']]['child'][$v['number']]))
                        $res[$v['dormType']]['child'][$v['number']] = array(
                            'name' => $v['number']
                        );
                }
            }
            foreach ($res as $k => $v) {
                sort($res[$k]['child']);
            }
            sort($res);
            $response['status'] = 1;
            $response['data'] = $res;
        }
        $this->ajaxReturn($response);
    }

    //(手动调整)待选学生列表
    private function getSelStu($planId, $dormId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        //待调整学生名单
        $assignId = D('DormMap')
            ->where(array('planId' => $planId, 'scId' => $this->scId))
            ->getField('assignId');
        $assignId = explode(',', $assignId);
        $dormType = D('Dorm')->where(array('id' => $dormId))->getField('dormType');

        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $gradeMap = array();
        foreach ($grade as $k => $v) {
            $gradeMap[$v['name']] = $v['znName'];
        }

        $where = array(
            'a.dormId' => array(array('not in', $dormId), array('EXP', 'is null'), 'or'),
            'a.id' => array('in', $assignId),
            'a.scId' => $this->scId
        );
        if (in_array($dormType, array(1, 2))) {
            $sexMap = array(
                1 => '女',
                2 => '男'
            );
            $where['a.sex'] = $sexMap[$dormType];
        }

        $bar = D('DormAssign a')
            ->join('mks_dorm d ON a.dormId=d.id', 'LEFT')
            ->where($where)
            ->field('a.id as assignId,a.stuName,a.isAssign,a.sex,a.grade,a.class,a.remark,
                d.number,d.name,d.dormNumber')
            ->select();

        if (!$bar) {
            $this->ajaxReturn($response);
        }
        $wStu = array();
        if ($bar) {
            foreach ($bar as &$v) {
                $v['grade'] = $gradeMap[$v['grade']];
            }
            $wStu = $bar;
        }
        $response['status'] = 1;
        $response['data'] = $wStu;
        $this->ajaxReturn($response);

    }

    //手动调整
    public function manualAdjust($planId)
    {
        $response = array(
            'status' => 0,
            'data' => array(),
            //'wStu'=>array()
        );
        $type = $_POST['type'];
        if ($type == 'add') {
            $assignId = $_POST['assignId']; //数组
            $dormId = (int)$_POST['dormId'];
            $rs = D('DormAssign')->where(array('planId' => $planId, 'id' => array('in', $assignId), 'scId' => $this->scId))
                ->data(array('dormId' => $dormId))->save();
            if (!$rs) {
                $response['msg'] = '调整失败';
                $this->ajaxReturn($response);
            }
            $response['status'] = 1;
            $response['msg'] = '调整成功';
            $this->ajaxReturn($response);
        }

        $dormType = $_POST['dormType'];
        $number = $_POST['number'];
        //$where['planId']=$planId;
        if (!empty($dormType)) {
            $where['d.dormType'] = $dormType;
        }
        if (!empty($number)) {
            $where['d.number'] = $number;
        }

        $dormId = D('DormMap')->where("planId={$planId} and scId={$this->scId}")
            ->getField('dormId');
        $dormId = explode(',', $dormId);
        $where['d.id'] = array('in', $dormId);
        $where['a.planId'] = $planId;
        $result = D('Dorm d')
            ->join('mks_dorm_assign a ON d.id=a.dormId', 'RIGHT')
            ->where($where)
            ->field('d.id as dormId,d.number,d.name,d.floor,d.dormNumber,d.dormType,d.capacity,
            a.id as assignId,a.stuName,a.isAssign,a.sex,a.grade,a.class,a.remark')
            ->select();
        if (!$result) {
            $this->ajaxReturn($response);
        }
        $rs = array();

        $typeMap = array(
            1 => '女生宿舍', 2 => '男生宿舍', 3 => '混合宿舍', 4 => '其它',
        );
        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $gradeMap = array();
        foreach ($grade as $k => $v) {
            $gradeMap[$v['name']] = $v['znName'];
        }
        foreach ($result as $k => $v) {
            if (!isset($rs[$v['dormId']])) {
                $rs[$v['dormId']] = array(
                    'dormId' => $v['dormId'],
                    'number' => $v['number'],
                    'name' => $v['name'],
                    'floor' => $v['floor'],
                    'dormNumber' => $v['dormNumber'],
                    'dormType' => $typeMap[$v['dormType']],
                    'capacity' => $v['capacity'],
                    'real' => 0,
                    'stu' => array(),
                );
            }
            $rs[$v['dormId']]['stu'][] = array(
                'assignId' => $v['assignId'],
                'stuName' => $v['stuName'],
                'isAssign' => $v['isAssign'],
                'sex' => $v['sex'],
                'grade' => $gradeMap[$v['grade']],
                'class' => $v['class'],
                'remark' => $v['remark']
            );
            $rs[$v['dormId']]['real']++;
        }
        sort($rs);

        $response['data'] = $rs;

        $response['status'] = 1;
        //   $response['wStu'] = $wStu;
        $this->ajaxReturn($response);
    }

    //打印报表
    public function reportForm($option, $planId)
    {
        $grade = D('Grade')->where(array('scId' => $this->scId))->field('name,znName')->select();
        $map = array();
        foreach ($grade as $k => $v) {
            $map[$v['name']] = $v['znName'];
        }
        if ($option == 1) {//粘贴总名单
            $result = D('DormAssign a')
                ->join('mks_dorm d ON d.id=a.dormId', 'LEFT')
                ->where(array('a.planId' => $planId, 'a.scId' => $this->scId, 'a.dormId' => array('exp', 'is not null')))
                ->field('d.number,d.name,d.floor,d.dormNumber,d.dormType,d.dormName,
            a.id as assignId,a.stuName,a.isAssign,a.sex,a.grade,a.class')
                ->select();
            foreach ($result as &$v) {
                $v['grade'] = $map[$v['grade']];
            }
            $this->ajaxReturn($result);
        } elseif ($option == 2) {//宿舍分配总名单
            $result = D('DormAssign a')
                ->join('mks_dorm d ON d.id=a.dormId', 'LEFT')
                ->where(array('a.planId' => $planId, 'a.scId' => $this->scId, 'a.dormId' => array('exp', 'is not null')))
                ->field('d.number,d.name,d.floor,d.dormNumber,d.dormType,d.teaName,
            a.id as assignId,a.stuName,a.isAssign,a.sex,a.grade,a.class,a.phone,a.regNumber,
            a.perAddress,a.admCategory')
                ->order('a.grade asc,a.class asc')
                ->select();
            foreach ($result as &$v) {
                $v['grade'] = $map[$v['grade']];
            }
            $this->ajaxReturn($result);
        } elseif ($option == 3) {//各宿舍学生名单
            $dormId = D('DormMap')->where(array('planId' => $planId, 'scId' => $this->scId))
                ->getField('dormId');
            $where['d.id'] = array('in', $dormId);
            $where['a.planId'] = $planId;
            $result = D('Dorm d')
                ->join('mks_dorm_assign a ON d.id=a.dormId', 'RIGHT')
                ->where($where)
                ->field('d.id,d.number,d.name,d.floor,d.dormNumber,d.dormType,d.capacity,d.teaId, 
                d.teaName,a.stuName,a.sex,a.grade,a.class,a.remark')
                ->select();
            $rs = array();
            if ($result) {
                foreach ($result as $k => $v) {
                    if (!isset($rs[$v['teaId']])) {
                        $rs[$v['teaId']] = array(
                            'name' => $v['teaName'],
                            'dorm' => array()
                        );
                    }
                    if (!isset($rs[$v['teaId']]['dorm'][$v['id']])) {
                        $rs[$v['teaId']]['dorm'][$v['id']] = array(
                            'number' => $v['number'],
                            'name' => $v['name'],
                            'floor' => $v['floor'],
                            'dormNumber' => $v['dormNumber'],
                            'dormType' => $v['dormType'],
                            'capacity' => $v['capacity'],
                            'stu' => array()
                        );
                    }
                    $rs[$v['teaId']]['dorm'][$v['id']]['stu'][] = array(
                        'stuName' => $v['stuName'],
                        'sex' => $v['sex'],
                        'grade' => $map[$v['grade']],
                        'class' => $v['class'],
                        'remark' => $v['remark'],
                    );
                }
                foreach ($rs as $k => $v) {
                    sort($rs[$k]['dorm']);
                }
                sort($rs);
            }
            $this->ajaxReturn($rs);
        }

    }

    /*********************************************宿舍分配************************************************/
    //宿舍分配结果
    public function dormResult()
    {
        if ($this->roleId == $this::$jZroleId) {
            $userId = $this->user['childId'];
            $stu = D('User')->where(array('id' => $userId, 'scId' => $this->scId))->find();
            $name = $stu['name'];
            $number = $stu['number'];
            $class = $stu['className'];
            $gradeId = $stu['gradeId'];
        } else {
            $userId = $this->user['id'];
            $name = $this->user['name'];
            $number = $this->user['number'];
            $class = $this->user['className'];
            $gradeId = $this->user['gradeId'];
        }
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $stuId = D('StudentInfo')->where(array('scId' => $this->scId, 'userId' => $userId))->getField('id');
        $where = array(
            'scId' => $this->scId,
            'dormDean' => 0,
            '_string' => "FIND_IN_SET({$stuId},stuId)"
        );
        $data = D('Dorm')->where($where)->field('number as buildNumber,name as buildName,floor,dormNumber,dormName,dormType,teaName')->find();

        if (!$data) {
            $this->ajaxReturn($response);
        }
        if ($data['dormType'] == 1) {
            $data['dormType'] = '女生宿舍';
        } elseif ($data['dormType'] == 2) {
            $data['dormType'] = '男生宿舍';
        } elseif ($data['dormType'] == 3) {
            $data['dormType'] = '混合宿舍';
        } else {
            $data['dormType'] = '其他';
        }
        $grade = D('Grade')->where(array('scId' => $this->scId))->field('gradeid,znName')->select();
        $gradeMap = array();
        foreach ($grade as $k => $v) {
            $gradeMap[$v['gradeid']] = $v['znName'];
        }
        $data['grade'] = $gradeMap[$gradeId];
        $data['class'] = $class;
        $data['number'] = $number;
        $data['name'] = $name;
        $response['status'] = 1;
        $response['data'] = array($data);
        $this->ajaxReturn($response);
    }

}