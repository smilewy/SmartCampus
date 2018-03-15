<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/10/19
 * Time: 10:21
 */

namespace Home\Controller;

/*新生管理*/
use Home\Common\Accessory;
use Home\Common\Import;
use PHPExcel_IOFactory;
use PHPExcel;

ob_end_clean();

class StudentIniController extends Base
{
    protected $roleId;
    protected $scId;
    protected $uid;
    protected $user;


    public function __construct()
    {
        parent::__construct();
        $this->scId = $_SESSION['loginCheck']['data']['scId'];
        $this->uid = $_SESSION['loginCheck']['data']['userId'];
        $this->roleId = $_SESSION['loginCheck']['data']['roleId'];

        /*      $this->roleId = 22;
                $uid = 1;
                $scId = 2;
                $this->scId = $scId;
                $this->uid = $uid;*/
        $this->user = D('User')->where(array('id' => $this->uid, 'scId' => $this->scId))->find();

    }

    //公用接口
    public function common($func, $param = array())
    {
        switch ($func) {
            case 'getGrade':
                $this->getGrade();
                break;
            case 'getOnePerson':
                $this->getOnePerson($param['userId']);
                break;
            case 'getLevel':
                $this->getLevel();
                break;
            case 'getBranch':
                $this->getBranch();
                break;
            case 'getMajor':
                $this->getMajor($param);
                break;
            case 'getClass':
                $this->getClass($param['gradeId']);
                break;
            case 'getStu':
                $this->getStu($param['gradeId'], $param['key']);
                break;
            case 'classStu':
                $this->classStu($param['gradeId'], $param['classId'], $param['key']);
                break;
            case 'gradeClass':
                $this->gradeClass($param['gradeId']);
                break;
            case 'classLevel':
                $this->classLevel($param['gradeId']);
                break;
            default:
                return null;
        }
    }

    //得到每个班的实际人数
    private function classNumber($gradeId)
    {
        $where = array(
            'gradeId' => $gradeId,
            'scId' => $this->scId
        );
        $data = D('StudentInfo')->where($where)->field('count(id) as number,classId')->group('classId')->select();
        $map = array();
        foreach ($data as $k => $v) {
            $map[$v['classId']] = $v['number'];
        }
        return $map;
    }

    //得到每个班的最大序号
    private function classSer($gradeId)
    {
        $where = array(
            'gradeId' => $gradeId,
            'scId' => $this->scId
        );
        $data = D('StudentInfo')->where($where)->field('max(serialNumber) as number,classId')->group('classId')->select();
        $map = array();
        foreach ($data as $k => $v) {
            $map[$v['classId']] = (int)$v['number'];
        }
        return $map;
    }

    //得到年级名单
    private function getGrade()
    {
        $grade = D('Grade')->where(array('scId' => $this->scId))
            ->field('gradeid as id,name,znName')
            ->order('name asc')
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

    //新生的数据操作
    private function stuHandle($type, $planId, $info)
    {
        $data = $info;
        $rs = false;
        if ($type == 'add') {
            //添加新生 //已测
            /*$prefix = M('school')->field('prefix')->where(array('scId' => $this->scId))->find();
            $prefix = $prefix['prefix'];
            $number = M('user')->where(array('scId' => $this->scId))->max('number');
            $number++;*/
            $stu = array();
            $regInfo = array();
            $rollInfo = array();
            $parentInfo = array();
            $otherInfo = array();
            $tuiInfo = array();

            foreach ($info as $k => $v) {
                //user表
                //添加学生

                $password = $this->InitialPassword();
                $rs = M('user')->add(array( //添加学生
                    'grade' => $v['grade'],
                    'gradeId' => $v['gradeId'],
                    'sex' => $v['sex'], //性别
                    'name' => $v['name'], //姓名
                    'politics' => $v['politics'],//政治面貌
                    'number' => $v['number'],
                    'scPrefix' => $v['prefix'],
                    'scId' => $this->scId,
                    'roleId' => $this::$studentRoleId,
                    'password' => $this->create_password($password, self::$password_key, self::$password_key1),
                    'InitialPassword' => $password,
                    'account' => $v['prefix'] . $v['number'],
                    'createTime' => time(),
                    'birth' => $v['birthday'],
                ));

                if (!$rs) {
                    return false;
                }

                //添加家长
                $stu[$k] = array(
                    'userId' => $rs,
                    'grade' => $v['grade'],
                    'gradeId' => $v['gradeId'],
                    'sex' => $v['sex'], //性别
                    'name' => $v['name'], //姓名
                    'politics' => $v['politics'],//政治面貌
                );
                $v['number']++;
                $prs = M('user')->add(array( //添加家长
                    'grade' => $v['grade'],
                    'name' => $v['name'] . '家长',
                    'number' => $v['number'],
                    'scPrefix' => $v['prefix'],
                    'scId' => $this->scId,
                    'roleId' => $this::$jZroleId,
                    'password' => $this->create_password($password, self::$password_key, self::$password_key1),
                    'InitialPassword' => $password,
                    'account' => $v['prefix'] . ($v['number']),
                    'childId' => $rs,
                    'childName' => $v['name'],
                    'gradeId' => $v['gradeId'],
                    'createTime' => time(),
                ));
                if (!$prs) {
                    return false;
                }

                //stu_info 数据
                $srs = M('student_info')->add(array(
                    'userId' => $rs,
                    'name' => $v['name'],
                    'scId' => $this->scId,
                    'birthday' => $v['birthday'], //出生日期
                    'phone' => $v['phone'], //电话
                    'homePath' => $v['homePath'], //家庭住址
                    'nation' => $v['nation'], //民族
                    'nowHomePath' => $v['nowHomePath'], //现住地址
                    'nowHomePostcode' => $v['nowHomePostcode'], //邮政编码
                    'createTime' => time(),
                    'sex' => $v['sex'],
                    'grade' => $v['grade'],
                    'gradeId' => $v['gradeId'],
                    'isNew' => 1,
                ));
                if (!$srs) {
                    return false;
                }

                //
                $stu[$k]['birthday'] = strtotime($v['birthday']);
                $stu[$k]['stuId'] = $srs;
                $stu[$k]['phone'] = $v['phone'];
                $stu[$k]['homePath'] = $v['homePath'];
                $stu[$k]['nation'] = $v['nation'];
                $stu[$k]['nowHomePath'] = $v['nowHomePath'];
                $stu[$k]['nowHomePostcode'] = $v['nowHomePostcode'];

                $regInfo[] = array(
                    'userId' => $rs,
                    'scId' => $this->scId,
                    'perAddress' => $v['perAddress'], // 户口所在地
                    'createTime' => time(),
                );
                $stu[$k]['perAddress'] = $v['perAddress'];

                $rollInfo[] = array(
                    'userId' => $rs,
                    'scId' => $this->scId,
                    'exaCategory' => !empty($v['isSigRaw']) ? '签约生' : $v['exaCategory'], //考生类型
                    'regNumber' => $v['regNumber'], //准考证号
                    'voluntPath' => $v['voluntPath'], //填报志愿所在地
                    'secSchool' => $v['secSchool'], //中学学校
                    'midExam' => $v['midExam'], //中考分数
                    'isTarget' => $v['isTarget'], //指标生出档
                    'isTempStudy' => empty($v['isTempStudy']) ? 0 : $v['isTempStudy'],//是否借读
                    'isSigRaw' => empty($v['isSigRaw']) ? 0 : $v['isSigRaw'], //是否签约生
                    'promise' => $v['promise'], //签约承诺
                    'createTime' => time(),
                );
                $stu[$k]['exaCategory'] = !empty($v['isSigRaw']) ? '签约生' : $v['exaCategory'];
                $stu[$k]['regNumber'] = $v['regNumber'];
                $stu[$k]['voluntPath'] = $v['voluntPath'];
                $stu[$k]['secSchool'] = $v['secSchool'];
                $stu[$k]['midExam'] = $v['midExam'];
                $stu[$k]['isTarget'] = $v['isTarget'];
                $stu[$k]['isTempStudy'] = empty($v['isTempStudy']) ? 0 : $v['isTempStudy'];//是否借读
                $stu[$k]['isSigRaw'] = empty($v['isSigRaw']) ? 0 : $v['isSigRaw'];
                $stu[$k]['promise'] = (int)$v['promise'];

                $parentInfo[] = array(
                    'userId' => $rs,
                    'scId' => $this->scId,
                    'createTime' => time(),
                );
                $otherInfo[] = array(
                    'userId' => $rs,
                    'scId' => $this->scId,
                    'createTime' => time(),
                );
                $tuiInfo[] = array(
                    'userId' => $rs,
                    'scId' => $this->scId,
                    'createTime' => time(),
                );
                $stu[$k]['planId'] = $planId;
                $stu[$k]['scId'] = $this->scId;

            }

            M('cen_reg_info')->addAll($regInfo);
            M('school_rollinfo')->addAll($rollInfo);
            M('parents_info')->addAll($parentInfo);
            M('other_info')->addAll($otherInfo);
            M('tuition_info')->addAll($tuiInfo);
            $rs = $this->syncStu($stu, 'add');

        } elseif ($type == 'del') { //删除新生 //已测
            $ids = $data['ids']; //userId

            /*$pid = M('user')->where(array('childId' => array('in', $ids), 'scId' => $this->scId))->select();
            $pid = array_map(function ($v) {
                return $v['id'];
            }, $pid);*/

            M('user')->where(array('id' => array('in', $ids), 'scId' => $this->scId))->delete();
            M('user')->where(array('childId' => array('in', $ids), 'scId' => $this->scId))->delete();
            M('cen_reg_info')->where(array('userId' => array('in', $ids), 'scId' => $this->scId))->delete();
            M('student_info')->where(array('userId' => array('in', $ids), 'scId' => $this->scId))->delete();
            M('school_rollinfo')->where(array('userId' => array('in', $ids), 'scId' => $this->scId))->delete();
            M('parents_info')->where(array('userId' => array('in', $ids), 'scId' => $this->scId))->delete();
            M('tuition_info')->where(array('userId' => array('in', $ids), 'scId' => $this->scId))->delete();
            M('other_info')->where(array('userId' => array('in', $ids), 'scId' => $this->scId))->delete();

            $stu['planId'] = $planId;
            $stu['scId'] = $this->scId;
            $stu['userId'] = $ids;
            $rs = $this->syncStu($stu, 'del');
        } elseif ($type == 'edit') { //编辑新生 //已测
            $userId = $data['userId'];
            M('user')->where(array('id' => $userId, 'scId' => $this->scId))
                ->save(array(
                    'sex' => $data['sex'],
                    'name' => $data['name'],
                    'politics' => $data['politics'],  //政治面貌
                ));
            $stu['userId'] = $userId;
            $stu['sex'] = $data['sex'];
            $stu['name'] = $data['name'];
            $stu['politics'] = $data['politics'];

            M('user')->where(array('childId' => $userId, 'scId' => $this->scId))
                ->save(array( //家长
                    'name' => $data['name'] . '家长',
                    'childName' => $data['name'],
                ));

            M('student_info')->where(array('userId' => $userId, 'scId' => $this->scId))
                ->save(array(
                    'name' => $data['name'],
                    'birthday' => strtotime($data['birthday']),
                    'phone' => $data['phone'],
                    'homePath' => $data['homePath'],
                    'nation' => $data['nation'],
                    'nowHomePath' => $data['nowHomePath'],
                    'nowHomePostcode' => $data['nowHomePostcode'],
                    'sex' => $data['sex'],
                ));
            $stu['birthday'] = strtotime($data['birthday']);
            $stu['phone'] = $data['phone'];
            $stu['homePath'] = $data['homePath'];
            $stu['nation'] = $data['nation'];
            $stu['nowHomePath'] = $data['nowHomePath'];
            $stu['nowHomePostcode'] = $data['nowHomePostcode'];
            $stu['sex'] = $data['sex'];

            M('cen_reg_info')->where(array('userId' => $userId, 'scId' => $this->scId))
                ->save(array(
                    'perAddress' => $data['perAddress'],
                    // 'name'=>$data['name']
                ));
            $stu['perAddress'] = $data['perAddress'];

            M('school_rollinfo')->where(array('userId' => $userId, 'scId' => $this->scId))
                ->save(array(
                    //  'name'=>$data['name'],
                    'exaCategory' => $data['exaCategory'],
                    'regNumber' => $data['regNumber'],
                    'voluntPath' => $data['voluntPath'],
                    'secSchool' => $data['secSchool'],
                    'midExam' => $data['midExam'],
                    'isTarget' => $data['isTarget'],
                    'promise' => $data['promise'],
                    'sex' => $data['sex']
                ));
            // $stu['exaCategory'] = $data['exaCategory'];
            $stu['regNumber'] = $data['regNumber'];
            $stu['voluntPath'] = $data['voluntPath'];
            $stu['secSchool'] = $data['secSchool'];
            /*M('parents_info')->where(array('userId' => $userId, 'scId' => $this->scId))
                ->save(array(
                    'name'=>$data['name']
                ));
            M('tuition_info')->where(array('userId' => $userId, 'scId' => $this->scId))
                ->save(array(
                    'name'=>$data['name']
                ));
            M('other_info')->where(array('userId' => $userId, 'scId' => $this->scId))
                ->save(array(
                    'name'=>$data['name']
                ));*/
            $stu['exaCategory'] = !empty($data['isSigRaw']) ? '签约生' : $data['exaCategory'];
            $stu['midExam'] = $data['midExam'];
            $stu['isTarget'] = $data['isTarget'];
            $stu['isTempStudy'] = empty($data['isTempStudy']) ? 0 : $data['isTempStudy'];//是否借读
            $stu['isSigRaw'] = empty($data['isSigRaw']) ? 0 : $data['isSigRaw'];
            $stu['promise'] = (int)$data['promise'];
            $stu['planId'] = $planId;
            $stu['scId'] = $this->scId;
            $rs = $this->syncStu($stu, 'update');
        } elseif ($type = 'setClass') { //分班信息更新后才进行此更新
            $map = $data;
            $user_val = '';
            $userIds = array();
            foreach ($map as $k => $v) {
                $userIds[] = $v['userId'];

            }
            $parentWhere = array(
                'scId' => $this->scId,
                'childId' => array('in', $userIds)
            );
            $parent = D('User')->where($parentWhere)->field('id,childId')->select();
            $parentMap = array();
            foreach ($parent as $k => $v) {
                $parentMap[$v['childId']] = $v['id'];
            }
            // $classUserMap=array();
            foreach ($map as $k => $v) {
                $user_val .= '(' . "{$v['userId']}," . "{$v['classId']},"
                    . "'{$v['class']}'," . "{$v['serialNumber']}),";  //学生变动
                if (isset($parentMap[$v['userId']])) {
                    $user_val .= '(' . "{$parentMap[$v['userId']]}," . "{$v['classId']},"
                        . "'{$v['class']}'," . "{$v['serialNumber']}),";  //家长变动
                }
                /*$classUserMap[$v['userId']]=array(
                    'classId'=>$v['classId'],
                    'className'=>$v['className'],
                    'serialNumber'=>$v['serialNumber']
                );*/
            }
            $user_val = rtrim($user_val, ',');
            $user_sql = "insert into mks_user (id,class,className,serialNumber)
                          values {$user_val} on duplicate key update class=values(class),
                          className=values(className),serialNumber=values(serialNumber)";

            $rs = M()->execute($user_sql);
            if ($rs) {
                $stu_val = '';
                $isNew = 0;
                foreach ($map as $k => $v) {
                    $stu_val .= '(' . "{$v['stuId']}," . "{$v['classId']},"
                        . "'{$v['class']}'," . "{$v['serialNumber']},"
                        . "{$isNew}),";
                }
                $stu_val = rtrim($stu_val, ',');
                $stu_sql = "insert into mks_student_info (id,classId,className,serialNumber,isNew)
                          values {$stu_val} on duplicate key update classId=values(classId),
                          className=values(className),serialNumber=values(serialNumber),
                          isNew=values(isNew)";
                $rs = M()->execute($stu_sql);

                /*//parents_info,cen_reg_info,school_rollinfo,tuition_info,other_info更新
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
                if ($rs) {
                    if (!empty($userIds)) { //将新生名单中已同步的学生置为1
                        $where = array(
                            'planId' => $planId,
                            'userId' => array('in', $userIds),
                            'scId' => $this->scId
                        );
                        D('BranchStudentNew')->where($where)->save(array('isSync' => 1));
                    }
                }
            }
        }
        return $rs;
    }

    private function getOnePerson($userId)
    {
        $where = array(
            'u.id' => $userId,
            'u.scId' => $this->scId,
        );
        $stuInfo = D('User u')
            ->join('mks_student_info si ON si.userId=u.id', 'LEFT')
            ->join('mks_school_rollinfo sr ON u.id=sr.userId', 'LEFT')
            ->join('mks_cen_reg_info cri ON u.id=cri.userId', 'LEFT')
            ->where($where)
            ->field('u.id as userId,u.name,u.sex,u.politics,si.birthday,si.phone,si.homePath,
            si.nation,si.nowHomePath,si.nowHomePostcode,sr.voluntPath,sr.exaCategory,
            sr.secSchool,sr.midExam,sr.isTarget,sr.regNumber,sr.promise,cri.perAddress')
            ->find();

        $level = D('ClassLevel')->where(array('scId' => $this->scId))->select();
        foreach ($level as $k => $v) {
            $levelMap[$v['levelid']] = $v['levelname'];
        }
        $stuInfo['promiseName'] = $levelMap[$stuInfo['promise']];
        // $stuInfo['birthday'] = date('Y-m-d', $stuInfo['birthday']);
        $homePath = $stuInfo['homePath'];
        $homePath = empty($homePath) ? array() : explode(' ', $homePath);
        $stuInfo['homePath1'] = array($homePath[0], $homePath[1], $homePath[2]);
        $stuInfo['homePath2'] = $homePath[3];
        $nowPath = $stuInfo['nowHomePath'];
        $nowPath = empty($homePath) ? array() : explode(' ', $nowPath);
        $stuInfo['nowHomePath1'] = array($nowPath[0], $nowPath[1], $nowPath[2]);
        $stuInfo['nowHomePath2'] = $nowPath[3];
        $stuInfo['perAddress'] = explode(' ', $stuInfo['perAddress']);
        $stuInfo['voluntPath'] = explode(' ', $stuInfo['voluntPath']);
        $this->ajaxReturn($stuInfo);
    }

    private function checkName($name, $add = true, $userId = '')
    {
        if ($add) {
            $prefix = M('School')->field('prefix')->where(array('scId' => $this->scId))->find();
            $response = array(
                'prefix' => $prefix['prefix'],
            );
        }

        if (empty($userId)) {
            $number = M('User')->where(array('scId' => array(array('eq', $this->scId), array('eq', -$this->scId), 'or')))->max('number');
            $number++;
        } else {
            $number = M('User')->where(array('id' => $userId))->getField('number');
        }

        $where = array('scId' => $this->scId);
        if (!empty($userId)) {
            $where['id'] = array('neq', $userId);
        }
        $checkArr = M('User')->field('name')->where($where)->select();
        $checkArr = array_map(function ($v) {
            return $v['name'];
        }, $checkArr);
        if (in_array($name, $checkArr)) {
            $name = $name . '(' . substr($number, 6, 9) . ')';
        }
        $response['number'] = $number;
        $response['name'] = $name;
        return $response;
    }

    private function checkNumber($data, $planId)
    {
        $where = array(
            'regNumber' => $data['regNumber'],
            'scId' => $this->scId
        );
        $rs = M('SchoolRollinfo')->where($where)->find();
        if ($rs) {
            $info[] = $data;
            $cacheName = 'new' . time();
            S($cacheName, $info, 3600);
            $response['planId'] = $planId;
            $response['cacheName'] = $cacheName;
            $response['status'] = 0;
            $response['msg'] = '准考证号重复，将用新记录覆盖旧记录，是否继续添加';
            $this->ajaxReturn($response);
            exit;
        }
        return;
    }

    public function uploadCache($cacheName, $planId)
    {
        $response = array(
            'status' => 0
        );
        $data = S($cacheName);
        if (!$data) {
            $response['msg'] = '数据丢失,请重新添加或者导入';
            $this->ajaxReturn($response);
        }

        S($cacheName, null);


        foreach ($data as $k => $v) {
            //将以前旧数据删除 引入新数据
            $where = array(
                'regNumber' => $v['regNumber'],
                'scId' => $this->scId
            );
            $userId = D('SchoolRollinfo')->where($where)->getField('userId');
            if ($userId) {
                $uIds['ids'][] = (int)$userId;
            }
        }

        if (!empty($uIds['ids'])) {
            $uIds['ids'] = array_unique($uIds['ids']);
            $this->stuHandle('del', $planId, $uIds);
        }

        $rs = $this->stuHandle('add', $planId, $data);

        if (!$rs) {
            $response['msg'] = '操作失败';
            $this->ajaxReturn($response);
        }

        $response['status'] = 1;
        $response['data'] = $data;
        $response['msg'] = '操作成功';
        $this->ajaxReturn($response);
    }

    /*********************************新生管理*******************************************/
    public function newManage($gradeId = '')
    {

        $response = array(
            'status' => 0,
            'data' => array()
        );

        $plan = $this->checkProcess($gradeId);
        $planId = $plan['planId'];
        $type = $_REQUEST['type'];
        $grade = D('Grade')->where(array('gradeid' => $gradeId))->find();
        if (!empty($type)) {
            if ($type == 'createGrade') {
                $code = strtoupper(I('post.code'));
                if (M('grade')->where(array('scId' => $this->scId, 'code' => $code))->find()) {
                    $this->returnJson(array('status' => 2, 'message' => '年级代码重复不能创建年级'), true);
                }
                $znName = I('post.znName');
                $highestgrade = I('post.highestgrade');
                $autoupdate = I('post.autoupdate');
                $xiao = explode('X', $code);
                $chu = explode('C', $code);
                $gao = explode('G', $code);
                $name = 0;
                $year = date('Y');
                $uploadData = null;
                $check = 0;
                if ((int)date('m') >= 8) {
                    if (count($xiao) == 2) {
                        $name = $year - $xiao[1] + 1;
                        $uploadData = $xiao[1];
                        $check = 'X';
                    }
                    if (count($chu) == 2) {
                        $name = $year - $chu[1] + 7;
                        $uploadData = $chu[1];
                        $check = 'C';
                    }
                    if (count($gao) == 2) {
                        $name = $year - $gao[1] + 10;
                        $uploadData = $gao[1];
                        $check = 'G';
                    }
                    if ($uploadData > $year) {
                        $this->returnJson(array('status' => 2, 'message' => '下半学期只能录取小于等于当前年的'), true);
                    }
                } else {
                    if (count($xiao) == 2) {
                        $name = $year - $xiao[1];
                        $uploadData = $xiao[1];
                        $check = 'X';
                    }
                    if (count($chu) == 2) {
                        $name = $year - $chu[1] + 6;
                        $uploadData = $chu[1];
                        $check = 'C';
                    }
                    if (count($gao) == 2) {
                        $name = $year - $gao[1] + 9;
                        $uploadData = $gao[1];
                        $check = 'G';
                    }
                    if ($uploadData >= $year) {
                        $this->returnJson(array('status' => 2, 'message' => '上半学期只能录小于当前年的'), true);
                    }
                }
                if ($check == 'G') {
                    if ($name > 12 || $name < 10) {
                        $this->returnJson(array('status' => 2, 'message' => '请输入正确的年级代码'), true);
                    }
                }
                if ($check == 'C') {
                    if ($name > 9 || $name < 7) {
                        $this->returnJson(array('status' => 2, 'message' => '请输入正确的年级代码'), true);
                    }
                }
                if ($check == 'X') {
                    if ($name > 6 || $name < 1) {
                        $this->returnJson(array('status' => 2, 'message' => '请输入正确的年级代码'), true);
                    }
                }
                if (!$check) {
                    $this->returnJson(array('status' => 2, 'message' => '输入数据出错，请按照提示输入'), true);
                }
                if (M('grade')->where(array('scId' => $this->scId, 'code' => $code))->find()) {
                    if (M('grade')->where(array('scId' => $this->scId, 'code' => $code))->setField(
                        array(
                            'code' => $code,
                            'name' => $name,
                            'znName' => $znName,
                            'autoupdate' => $autoupdate,
                            'highestgrade' => $highestgrade,
                        )
                    )
                    ) {
                        $this->returnJson(array('status' => 1, 'message' => 'success'), true);
                    }
                    $this->returnJson(array('status' => 0, 'message' => 'fail'), true);
                } else {
                    if (M('grade')->add(
                        array(
                            'code' => $code,
                            'name' => $name,
                            'znName' => $znName,
                            'scId' => $this->scId,
                            'autoupdate' => $autoupdate,
                            'highestgrade' => $highestgrade,
                            'createTime' => strtotime(date('Y-m-d H:i:s'))
                        )
                    )
                    ) {
                        $this->returnJson(array('status' => 1, 'message' => 'success'), true);
                    }
                    $this->returnJson(array('status' => 0, 'message' => 'fail'), true);
                }
            }/*{ //新增年级
                $code = $_POST['code'];
                $znName = $_POST['znName'];
                $highestgrade = $_POST['highestgrade'];
                $autoupdate = $_POST['autoupdate'];
                $xiao = explode('X', $znName);
                $chu = explode('C', $znName);
                $gao = explode('G', $znName);
                $name = 0;
                if (count($xiao) == 2) {
                    $year = date('Y');
                    $name = $year - $xiao[1] + 1;
                }
                if (count($chu) == 2) {
                    $year = date('Y');
                    $name = $year - $chu[1] + 7;
                }
                if (count($gao) == 2) {
                    $year = date('Y');
                    $name = $year - $gao[1] + 10;
                }
                if (M('grade')->where(array('scId' => $this->scId, 'code' => $code))->find()) {
                    $this->ajaxReturn(array('status' => 0, 'message' => '年级已存在'));
                } else {

                    if (M('grade')->add(
                        array(
                            'code' => $code,
                            'name' => $name,
                            'znName' => $znName,
                            'scId' => $this->scId,
                            'autoupdate' => $autoupdate,
                            'highestgrade' => $highestgrade,
                            'createTime' => strtotime('Y-m-d H:i:s')
                        )
                    )
                    ) {
                        $this->ajaxReturn(array('status' => 1, 'message' => '添加成功'));
                    }
                    $this->ajaxReturn(array('status' => 0, 'message' => '添加失败'));
                }
            }*/ elseif ($type == 'add' && !empty($planId)) { //添加新生
                $post = I('post.');
                $post['homePath'] = implode(' ', $post['homePath1']) . ' ' . $post['homePath2'];
                $post['nowHomePath'] = implode(' ', $post['nowHomePath1']) . ' ' . $post['nowHomePath2'];
                $post['perAddress'] = implode(' ', $post['perAddress']);
                $post['voluntPath'] = implode(' ', $post['voluntPath']);
                $post['grade'] = $grade['name'];
                $result = $this->checkName($post['name']);
                $post['name'] = $result['name'];
                $post['prefix'] = $result['prefix'];
                $post['number'] = $result['number'];

                $this->checkNumber($post, $planId);//验证准考证号
                $addList[] = $post;
                $rs = $this->stuHandle($type, $planId, $addList);
                if (!$rs) {
                    $response['msg'] = '添加失败';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                $response['msg'] = '添加成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'del' && !empty($planId)) { //删除新生
                $post = I('post.');
                $post['grade'] = $grade['name'];
                $rs = $this->stuHandle($type, $planId, $post);
                if (!$rs) {
                    $response['msg'] = '删除失败';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                $response['msg'] = '删除成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'edit' && !empty($planId)) {//班级新生
                $post = I('post.');
                $result = $this->checkName($post['name'], false, $post['userId']);
                $post['name'] = $result['name'];
                $post['homePath'] = implode(' ', $post['homePath1']) . ' ' . $post['homePath2'];
                $post['nowHomePath'] = implode(' ', $post['nowHomePath1']) . ' ' . $post['nowHomePath2'];
                $post['perAddress'] = implode(' ', $post['perAddress']);
                $post['voluntPath'] = implode(' ', $post['voluntPath']);
                $post['grade'] = $grade['name'];
                $rs = $this->stuHandle($type, $planId, $post);
                if (!$rs) {
                    $response['msg'] = '编辑失败';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                $response['msg'] = '编辑成功';
                $this->ajaxReturn($response);
                die;
            } elseif ($type == 'download' && !empty($planId)) { //已测试
                $file = new Accessory('', $this->scId, 'download');
                $filename = array('download/newStudent.xlsx');
                $aName = array('新生模板.xlsx');
                $file->download($filename, $aName, true);
                die;
            } elseif ($type == 'preview' && !empty($planId)) {
                $file = $_FILES;
                if (empty($file)) {
                    $response['msg'] = '未检索到文件！';
                    $this->ajaxReturn($response);
                }
                $acc = new Accessory($file, $this->scId, 'excel');

                $rs = $acc->uploadExcel();
                $filename = $rs['path'][0];
                $import = new Import();
                $rs = $import->read($filename);
                $acc->del($filename);
                $data = array_splice($rs, 1); //去掉表头
                //去掉示例
                if ($data[0][0] == '张三' && $data[0][2] == '123456') {
                    unset($data[0]);
                }
                $newData = array();
                foreach ($data as $k => $v) {
                    /*$homePath = empty($v[7]) ? array() : explode('-', $v[7]);
                    $homePath1 = array($homePath[0], $homePath[1], $homePath[2]);
                    $homePath2 = $homePath[3];
                    $nowHomePath = empty($v[8]) ? array() : explode('-', $v[10]);
                    $nowHomePath1 = array($nowHomePath[0], $nowHomePath[1], $nowHomePath[2]);
                    $nowHomePath2 = $nowHomePath[3];*/
                    $newData[] = array(
                        'name' => $v[0],
                        'regNumber' => $v[2],
                        'sex' => $v[1],
                        'birthday' => str_replace('.', '-', $v[3]),
                        'phone' => $v[4],
                        'nation' => $v[5],
                        'politics' => $v[6],
                        'exaCategory' => $v[7],
                        'secSchool' => $v[8],
                        'nowHomePostcode' => $v[9],
                        'midExam' => $v[10],
                        'isTarget' => $v[11] == '是' ? 1 : 0,
                    );
                }
                $response['status'] = 1;
                $response['data'] = $newData;
                $this->ajaxReturn($response);

            } elseif ($type == 'import' && !empty($planId)) {
                $data = I('post.data');

                $regNumber = array();
                $info = array();
                $newName = array();
                $prefix = M('school')->field('prefix')->where(array('scId' => $this->scId))->find();
                $prefix = $prefix['prefix'];
                $number = M('user')->where(array('scId' => array(array('eq', $this->scId), array('eq', -$this->scId), 'or')))->max('number');
                $number++;
                $checkArr = M('user')->field('name')->where(array('scId' => $this->scId))->select();
                $checkArr = array_map(function ($v) {
                    return $v['name'];
                }, $checkArr);

                $nation = "汉族、蒙古族、回族、藏族、维吾尔族、苗族、彝族、壮族、布依族、朝鲜族、满族、侗族、瑶族、白族、土家族、哈尼族、
                    哈萨克族、傣族、黎族、僳僳族、佤族、畲族、高山族、拉祜族、水族、东乡族、纳西族、景颇族、柯尔克孜族、土族、达斡尔族、
                    仫佬族、羌族、布朗族、撒拉族、毛南族、仡佬族、锡伯族、阿昌族、普米族、塔吉克族、怒族、乌孜别克族、俄罗斯族、鄂温克族、
                    德昂族、保安族、裕固族、京族、塔塔尔族、独龙族、鄂伦春族、赫哲族、门巴族、珞巴族、基诺族";
                $nation = explode('、', $nation);
                $sex = array('男', '女');
                $birthday = '/^([1-2]\\d{3})[\-](0?[1-9]|10|11|12)[\-]([1-2]?[0-9]|0[1-9]|30|31)$/';
                $phone = '/^(?=\d{11}$)^1(?:3\d|4[57]|5[^4\D]|66|7[^249\D]|8\d|9[89])\d{8}$/';
                $politics = array('共青团员', '群众');
                $exaCategory = array('应届生', '复读生', '其他');
                $postcode = '/^[1-9][0-9]{5}$/';
                //$target = array(1, 0);

                foreach ($data as $k => $v) {
                    //去掉示例
                    if ($v['name'] == '张三' && $v['regNumber'] == '123456')
                        continue;
                    if (empty($v['name']))
                        continue;
                    //格式验证
                    if (!in_array($v['sex'], $sex) || empty($v['birthday']) ? false : !preg_match($birthday, $v['birthday'])
                    || !preg_match($phone, $v['phone']) || empty($v['nation']) ? false : !in_array($v['nation'], $nation)
                    || empty($v['politics']) ? false : !in_array($v['politics'], $politics)
                    || empty($v['exaCategory']) ? false : !in_array($v['exaCategory'], $exaCategory)
                    || empty($v['nowHomePostcode']) ? false : !preg_match($postcode, $v['nowHomePostcode'])
                    || empty($v['midExam']) ? false : !is_numeric($v['midExam'])
                        /* || empty($v['isTarget']) ? false : !in_array($v['isTarget'], $target)*/
                    ) {
                        $response['status'] = 3;
                        $response['msg'] = $v['name'] . '填写的数据格式不正确';
                        $this->ajaxReturn($response);
                    }
                    $result['name'] = $v['name'];
                    $result['number'] = $number;
                    $result['prefix'] = $prefix;
                    if (in_array($v['name'], $checkArr)) {
                        $result['name'] = $v['name'] . '(' . substr($number, 6, 9) . ')';
                    }
                    if (in_array($v['name'], $newName)) {
                        $result['name'] = $v['name'] . '(' . substr($number, 6, 9) . ')';
                    }
                    /*else{
                        $result=$this->checkName($v[0]);
                        $numberMap[$v[0]]['prefix']=$result['prefix'];
                        $numberMap[$v[0]]['number']=$result['number'];
                    }*/

                    $info [] = array(
                        'name' => $result['name'],
                        'prefix' => $result['prefix'],
                        'number' => $result['number'],
                        'sex' => $v['sex'],
                        'regNumber' => $v['regNumber'],
                        'birthday' => $v['birthday'],
                        'voluntPath' => implode(' ', $v['voluntPath']),
                        'phone' => $v['phone'],
                        'perAddress' => implode(' ', $v['perAddress']),
                        'homePath' => implode(' ', $v['homePath1']) . ' ' . $v['homePath2'],
                        'nation' => $v['nation'],
                        'politics' => $v['politics'],
                        'exaCategory' => $v['exaCategory'],
                        'nowHomePath' => implode(' ', $v['nowHomePath1']) . ' ' . $v['nowHomePath2'],
                        'secSchool' => $v['secSchool'],
                        'nowHomePostcode' => $v['nowHomePostcode'],
                        'midExam' => $v['midExam'],
                        'isTarget' => (int)$v['isTarget'] /*!= 'false' ? 1 : 0*/,
                        'grade' => $grade['name'],
                        'gradeId' => $gradeId
                    );
                    $newName[] = $v['name'];
                    $regNumber[] = $v['regNumber'];
                    $number += 2;
                }

                //  $imData = $info;
                if (count($regNumber) != count(array_unique($regNumber))) {
                    $response['msg'] = '上传数据中准考证号存在重复值';
                    $response['status'] = -1;
                    $this->ajaxReturn($response);
                }

                //准考证号
                $oldNumber = D('SchoolRollinfo')->where(array('scId' => $this->scId))->field('regNumber')->select();

                $oldNumber = array_map(function ($v) {
                    return $v['regNumber'];
                }, $oldNumber);

                $diff = array_intersect($regNumber, $oldNumber);
                if (!empty($diff)) {
                    $cacheName = 'new' . time();
                    S($cacheName, $info, 3600);
                    $response['plan'] = $planId;
                    $response['cacheName'] = $cacheName;
                    $response['msg']='准考证号 ';
                    foreach ($diff as $k=>$v)
                    {
                        $response['msg'].=$v.' ';
                    }
                    $response['msg'] .= '在系统中已存在，将用新记录覆盖旧记录，是否继续添加';
                    //$response['msg'] = '准考证号重复，将用新记录覆盖旧记录，是否继续添加';
                    $this->ajaxReturn($response);
                }

                $rs = $this->stuHandle('add', $planId, $info);
                if (!$rs) {
                    $response['status'] = 2;
                    $response['msg'] = '导入过程中失败';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                //$response['data'] = $imData;
                $response['msg'] = '导入成功';
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '没有权限';
            }
            $this->ajaxReturn($response);
        }

        $where = array(
            'u.gradeId' => $gradeId,
            'u.scId' => $this->scId,
            'sr.isSigRaw' => 0,
            'si.isNew' => 1
        );

        $key = I('post.key');
        if (!empty($key)) {
            // $where['u.name'] = array('like', "%{$key}%");
            $where['_string'] = "u.name like '%{$key}%' or sr.regNumber like '%{$key}%'";
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $stuInfo = D('User u')
            ->join('mks_student_info si ON si.userId=u.id', 'LEFT')
            ->join('mks_school_rollinfo sr ON u.id=sr.userId', 'LEFT')
            ->join('mks_cen_reg_info cri ON u.id=cri.userId', 'LEFT')
            ->where($where)
            ->order('u.createTime desc')
            ->field('u.id as userId,u.name,u.sex,u.politics,si.birthday,si.phone,si.homePath,
            si.nation,si.nowHomePath,si.nowHomePostcode,sr.voluntPath,sr.exaCategory,sr.promise,
            sr.secSchool,sr.midExam,sr.isTarget,sr.regNumber,cri.perAddress')
            ->limit($limit_page)
            ->select();


        $total = D('User u')
            ->join('mks_student_info si ON si.userId=u.id', 'LEFT')
            ->join('mks_school_rollinfo sr ON u.id=sr.userId', 'LEFT')
            ->join('mks_cen_reg_info cri ON u.id=cri.userId', 'LEFT')
            ->where($where)
            ->count();
        $total = (int)$total;
        if ($stuInfo) {

            foreach ($stuInfo as $k => $v) {
                $stuInfo[$k]['birthday'] = $v['birthday']/*!empty($v['birthday']) ? date('Y-m-d', $v['birthday']) : ''*/;
                //地址处理
                $homePath = $stuInfo[$k]['homePath'];
                $homePath = empty($homePath) ? array() : explode(' ', $homePath);
                $stuInfo[$k]['homePath1'] = array($homePath[0], $homePath[1], $homePath[2]);
                $stuInfo[$k]['homePath2'] = isset($homePath[3]) ? $homePath[3] : '';
                $nowPath = $stuInfo[$k]['nowHomePath'];
                $nowPath = empty($homePath) ? array() : explode(' ', $nowPath);
                $stuInfo[$k]['nowHomePath1'] = array($nowPath[0], $nowPath[1], $nowPath[2]);
                $stuInfo[$k]['nowHomePath2'] = isset($nowPath[3]) ? $nowPath[3] : '';
                $stuInfo[$k]['perAddress'] = empty($stuInfo[$k]['perAddress']) ? array() : explode(' ', $stuInfo[$k]['perAddress']);
                $stuInfo[$k]['voluntPath'] = empty($stuInfo[$k]['voluntPath']) ? array() : explode(' ', $stuInfo[$k]['voluntPath']);

            }
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['status'] = 1;
            $response['data'] = $stuInfo;
        }
        $this->ajaxReturn($response);
    }

    /*********************************签约生管理*****************************************/
    public function signManage($gradeId)
    {

        $response = array(
            'status' => 0,
            'data' => array()
        );
        $plan = $this->checkProcess($gradeId);
        $planId = $plan['planId'];
        $type = $_REQUEST['type'];
        $grade = D('Grade')->where(array('gradeid' => $gradeId))->find();
        if (isset($type)) {
            if ($type == 'add') { //添加签约生
                $post = I('post.');
                $post['grade'] = $grade['name'];
                $post['homePath'] = implode(' ', $post['homePath1']) . ' ' . $post['homePath2'];
                $post['nowHomePath'] = implode(' ', $post['nowHomePath1']) . ' ' . $post['nowHomePath2'];
                $post['perAddress'] = implode(' ', $post['perAddress']);
                $post['isSigRaw'] = 1;
                $result = $this->checkName($post['name']);
                $post['name'] = $result['name'];
                $post['prefix'] = $result['prefix'];
                $post['number'] = $result['number'];
                $this->checkNumber($post, $planId);//验证准考证号
                $addList[] = $post;
                $rs = $this->stuHandle($type, $planId, $addList);
                if (!$rs) {
                    $response['msg'] = '添加失败';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                $response['msg'] = '添加成功';
                $this->ajaxReturn($response);

            } elseif ($type == 'del') { //删除新生
                $post = I('post.');
                $post['grade'] = $grade['name'];
                $rs = $this->stuHandle($type, $planId, $post);
                if (!$rs) {
                    $response['msg'] = '删除失败';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                $response['msg'] = '删除成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'edit') {//班级新生
                $post = I('post.');
                $result = $this->checkName($post['name'], false, $post['userId']);
                $post['name'] = $result['name'];
                $post['homePath'] = implode(' ', $post['homePath1']) . ' ' . $post['homePath2'];
                $post['nowHomePath'] = implode(' ', $post['nowHomePath1']) . ' ' . $post['nowHomePath2'];
                $post['perAddress'] = implode(' ', $post['perAddress']);
                $post['grade'] = $grade['name'];
                $post['isSigRaw'] = 1;
                $rs = $this->stuHandle($type, $planId, $post);
                if (!$rs) {
                    $response['msg'] = '编辑失败';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                $response['msg'] = '编辑成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'download') { //已测试
                $file = new Accessory('', $this->scId, 'download');
                $filename = array('download/newPromise.xlsx');
                $aName = array('签约生模板.xlsx');
                $file->download($filename, $aName, true);
                die;
            } elseif ($type == 'preview') {
                $file = $_FILES;
                if (empty($file)) {
                    $response['msg'] = '未检索到文件！';
                    $this->ajaxReturn($response);
                }
                $acc = new Accessory($file, $this->scId, 'excel');
                $rs = $acc->uploadExcel();
                $filename = $rs['path'][0];
                $import = new Import();
                $rs = $import->read($filename);
                $acc->del($filename);
                $data = array_splice($rs, 1);
                //去掉示例
                if ($data[0][0] == '张三' && $data[0][2] == '123456') {
                    unset($data[0]);
                }
                $newData = array();
                foreach ($data as $k => $v) {
                    /*$homePath = empty($v[7]) ? array() : explode('-', $v[7]);
                    $homePath1 = array($homePath[0], $homePath[1], $homePath[2]);
                    $homePath2 = $homePath[3];
                    $nowHomePath = empty($v[8]) ? array() : explode('-', $v[10]);
                    $nowHomePath1 = array($nowHomePath[0], $nowHomePath[1], $nowHomePath[2]);
                    $nowHomePath2 = $nowHomePath[3];*/
                    $newData[] = array(
                        'name' => $v[0],
                        'regNumber' => $v[2],
                        'sex' => $v[1],
                        'birthday' => str_replace('.', '-', $v[3]),
                        'phone' => $v[5],
                        'nation' => $v[5],
                        'promise' => $v[6],
                        'secSchool' => $v[4],
                        'homePath' => '',
                        'perAddress' => '',
                        'nowHomePath' => '',
                        'NowHomePostcode' => $v[7],
                    );
                }
                $response['status'] = 1;
                $response['data'] = $newData;
                $this->ajaxReturn($response);
            } elseif ($type == 'import') {
                $data = I('post.data');
                $regNumber = array();
                $info = array();
                $newName = array();
                $prefix = M('school')->field('prefix')->where(array('scId' => $this->scId))->find();
                $prefix = $prefix['prefix'];
                $number = M('user')->where(array('scId' => array(array('eq', $this->scId), array('eq', -$this->scId), 'or')))->max('number');
                $number++;
                $checkArr = M('user')->field('name')->where(array('scId' => $this->scId))->select();
                $checkArr = array_map(function ($v) {
                    return $v['name'];
                }, $checkArr);
                $level = D('ClassLevel')->where(array('scId' => $this->scId))->select();
                foreach ($level as $k => $v) {
                    $levelMap[$v['levelname']] = $v['levelid'];
                }

                $sex = array('男', '女');
                $birthday = '/^([1-2]\d{3})[-](0?[1-9]|10|11|12)[-]([1-2]?[0-9]|0[1-9]|30|31)$/';
                // $phone='/^1(3[0-9]|4[579]|5[0-35-9]|7[0-9]|8[0-9])\\d{8}$/';
                $phone = '/^(?=\d{11}$)^1(?:3\d|4[57]|5[^4\D]|66|7[^249\D]|8\d|9[89])\d{8}$/';
                $postcode = '/^[1-9][0-9]{5}$/';

                foreach ($data as $k => $v) {
                    //去掉示例
                    if ($v['name'] == '张三' && $v['regNumber'] == '123456')
                        continue;
                    //格式验证
                    if (!in_array($v['sex'], $sex) || empty($v['birthday']) ? false : !preg_match($birthday, $v['birthday'])
                    || !preg_match($phone, $v['phone']) || empty($v['NowHomePostcode']) ? false : !preg_match($postcode, $v['NowHomePostcode'])
                    ) {
                        $response['status'] = 3;
                        $response['msg'] = $v['name'] . '填写的数据格式不正确';
                        $this->ajaxReturn($response);
                    }

                    $result['name'] = $v['name'];
                    $result['number'] = $number;
                    $result['prefix'] = $prefix;
                    if (in_array($v['name'], $checkArr)) {
                        $result['name'] = $v['name'] . '(' . substr($number, 6, 9) . ')';
                    }
                    if (in_array($v['name'], $newName)) {
                        $result['name'] = $v['name'] . '(' . substr($number, 6, 9) . ')';
                    }
                    $info [] = array(
                        'name' => $result['name'],
                        'prefix' => $result['prefix'],
                        'number' => $result['number'],
                        'sex' => $v['sex'],
                        'regNumber' => $v['regNumber'],
                        'birthday' => $v['birthday']/*str_replace('.', '-', $v[3])*/,
                        'secSchool' => $v['secSchool'],
                        'phone' => $v['phone'],
                        'promise' => isset($levelMap[$v['promise']]) ? $levelMap[$v['promise']] : 0,
                        //'homePath' => implode(' ',explode('-',$v[7])),
                        'homePath' => implode(' ', $v['homePath1']) . ' ' . $v['homePath2'],
                        //'perAddress' => implode(' ',explode('-',$v[8])),
                        'perAddress' => implode(' ', $v['perAddress']),
                        'nowHomePostcode' => $v['nowHomePostcode'],
                        //'nowHomePath' => implode(' ',explode('-',$v[10])),
                        'nowHomePath' => '',
                        'isSigRaw' => 1,
                        'grade' => $grade['name'],
                        'gradeId' => $gradeId
                    );
                    $newName[] = $v['name'];
                    $regNumber[] = $v['regNumber'];
                    $number += 2;
                }

               // $imData = $info;
                if (count($regNumber) != count(array_unique($regNumber))) {
                    $response['msg'] = '上传数据中准考证号存在重复值';
                    $response['status'] = -1;
                    $this->ajaxReturn($response);
                }

                //准考证号
                $oldNumber = D('SchoolRollinfo')->where(array('scId' => $this->scId))->field('regNumber')->select();
                $oldNumber = array_map(function ($v) {
                    return $v['regNumber'];
                }, $oldNumber);
                $diff = array_intersect($regNumber, $oldNumber);
                if (!empty($diff)) {
                    $cacheName = 'new' . time();
                    S($cacheName, $info, 3600);
                    $response['plan'] = $planId;
                    $response['cacheName'] = $cacheName;
                    $response['msg']='准考证号 ';
                    foreach ($diff as $k=>$v)
                    {
                        $response['msg'].=$v.' ';
                    }
                    $response['msg'] .= '在系统中已存在，将用新记录覆盖旧记录，是否继续添加';
                    $this->ajaxReturn($response);
                }

                $rs = $this->stuHandle('add', $planId, $info);
                if (!$rs) {
                    $response['status'] = 2;
                    $response['msg'] = '导入过程中失败';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                //$response['data'] = $imData;
                $response['msg'] = '导入成功';
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '没有权限';
            }
        }

        $where = array(
            'si.gradeId' => $gradeId,
            'si.scId' => $this->scId,
            'sr.isSigRaw' => 1,
            'si.isNew' => 1
        );
        $key = $_POST['key'];
        if (empty($key)) {
            $where['u.name|sr.regNumber'] = array('like', "%{$key}%");
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $stuInfo = D('User u')
            ->join('mks_student_info si ON si.userId=u.id', 'LEFT')
            ->join('mks_school_rollinfo sr ON u.id=sr.userId', 'LEFT')
            ->join('mks_cen_reg_info cri ON u.id=cri.userId', 'LEFT')
            ->where($where)
            ->order('u.createTime desc')
            ->field('u.id as userId,u.name,u.sex,si.birthday,si.phone,si.homePath,
            si.nowHomePath,si.nowHomePostcode,sr.secSchool,sr.promise,
            sr.regNumber,cri.perAddress')
            ->limit($limit_page)
            ->select();
        $total = D('User u')
            ->join('mks_student_info si ON si.userId=u.id', 'LEFT')
            ->join('mks_school_rollinfo sr ON u.id=sr.userId', 'LEFT')
            ->join('mks_cen_reg_info cri ON u.id=cri.userId', 'LEFT')
            ->where($where)
            ->count();
        $total = (int)$total;
        if ($stuInfo) {
            $level = D('ClassLevel')->where(array('scId' => $this->scId))->select();
            foreach ($level as $k => $v) {
                $levelMap[$v['levelid']] = $v['levelname'];
            }
            foreach ($stuInfo as $k => $v) {
                $stuInfo[$k]['birthday'] =$v['birthday'] /*!empty($v['birthday']) ? date('Y-m-d', $v['birthday']) : ''*/;
                //地址处理
                $homePath = $stuInfo[$k]['homePath'];
                $homePath = empty($homePath) ? array() : explode(' ', $homePath);
                $stuInfo[$k]['homePath1'] = array($homePath[0], $homePath[1], $homePath[2]);
                $stuInfo[$k]['homePath2'] = isset($homePath[3]) ? $homePath[3] : '';
                $nowPath = $stuInfo[$k]['nowHomePath'];
                $nowPath = empty($homePath) ? array() : explode(' ', $nowPath);
                $stuInfo[$k]['nowHomePath1'] = array($nowPath[0], $nowPath[1], $nowPath[2]);
                $stuInfo[$k]['nowHomePath2'] = isset($nowPath[3]) ? $nowPath[3] : '';
                $stuInfo[$k]['perAddress'] = empty($stuInfo[$k]['perAddress']) ? array() : explode(' ', $stuInfo[$k]['perAddress']);
                $stuInfo[$k]['promise'] = isset($levelMap[$v['promise']]) ? $levelMap[$v['promise']] : '';
            }
            $response['total'] = $total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['status'] = 1;
            $response['data'] = $stuInfo;
        }
        $this->ajaxReturn($response);
    }
    /*********************************新生分班*******************************************/
    //同步分班的新生名单
    private function syncStu($stu, $handle)
    {
        if ($handle == 'add') {
            $data = array_values($stu);
            $rs = D('BranchStudentNew')->addAll($data);
            $total = (int)D('BranchStudentNew')
                ->where(array('planId' => $stu[0]['planId'], 'scId' => $this->scId))
                ->count();
            D('BranchSubjectNew')->where(array('planId' => $stu[0]['planId'], 'scId' => $this->scId))->save(array('totalNumber' => $total));
        } elseif ($handle == 'del') {
            $where = array(
                'planId' => $stu['planId'],
                'scId' => $stu['scId'],
                'userId' => array('in', $stu['userId'])
            );
            $rs = D('BranchStudentNew')->where($where)->delete();
            $total = (int)D('BranchStudentNew')
                ->where(array('planId' => $stu['planId'], 'scId' => $this->scId))
                ->count();
            D('BranchSubjectNew')->where(array('planId' => $stu['planId'], 'scId' => $this->scId))
                ->save(array('totalNumber' => $total, 'recordNumber' => null));
        } elseif ($handle == 'update') {
            $where = array(
                'planId' => $stu['planId'],
                'scId' => $stu['scId'],
                'userId' => $stu['userId']
            );
            $rs = D('BranchStudentNew')->where($where)->save($stu);
        } else {
            $rs = false;
        }
        return $rs;
    }

    //得到相应的流程
    private function checkProcess($gradeId)
    {
        if (empty($gradeId))
            return null;
        /*是否有对应的方案*/
        $where = array(
            'gradeId' => $gradeId,
            'scId' => $this->scId,
            'isNew' => 1
        );
        $rs = D('BranchPlan')->where($where)->find();
        if (!$rs) {
            /*添加对应方案*/
            $plan = array(
                'creator' => $this->user['name'],
                'creatorId' => $this->uid,
                'name' => '新生分班方案',
                'createTime' => time(),
                'scId' => $this->scId,
                'isNew' => 1,
                'gradeId' => $gradeId
            );
            $planRs = D('BranchPlan')->data($plan)->add();
            if (!$planRs) {
                return false;
            }
            $pro = array(
                array('name' => '新生分班名单', 'status' => 1, 'step' => 1, 'url' => '', 'planId' => $planRs, 'scId' => $this->scId),
                array('name' => '创建班级', 'status' => 2, 'step' => 2, 'url' => '', 'planId' => $planRs, 'scId' => $this->scId),
                array('name' => '指定学生到班', 'status' => -1, 'step' => 3, 'url' => '', 'planId' => $planRs, 'scId' => $this->scId),
                array('name' => '分班成绩合成设置', 'status' => 3, 'step' => 4, 'url' => '', 'planId' => $planRs, 'scId' => $this->scId),
                array('name' => '分班合成成绩情况', 'status' => 3, 'step' => 5, 'url' => '', 'planId' => $planRs, 'scId' => $this->scId),
                array('name' => '快速分班', 'status' => -1, 'step' => 6, 'url' => '', 'planId' => $planRs, 'scId' => $this->scId),
                array('name' => '手动调整', 'status' => -1, 'step' => 7, 'url' => '', 'planId' => $planRs, 'scId' => $this->scId),
                array('name' => '学生补录', 'status' => -1, 'step' => 8, 'url' => '', 'planId' => $planRs, 'scId' => $this->scId),
                array('name' => '打印报表', 'status' => -1, 'step' => 9, 'url' => '', 'planId' => $planRs, 'scId' => $this->scId),
            );
            $rs = D('BranchProcess')->addAll($pro);
            if (!$rs) {
                return false;
            }
            $process['planId'] = $planRs;
            $process['process'] = $pro;
        } else { //已创建 得到对应流程
            $pro = D('BranchProcess')->where(array('planId' => $rs['id'], 'scId' => $this->scId))->select();
            $process['planId'] = $rs['id'];
            $process['process'] = $pro;
        }
        return $process;
    }

    //各流程变化
    private function changeProcess(Array $process, $planId)
    {
        $steps = implode(',', array_keys($process));

        $sql = "UPDATE mks_branch_process SET status = CASE step ";
        foreach ($process as $step => $status) {
            $sql .= sprintf("WHEN %d THEN %d ", $step, $status);
        }
        $sql .= "END WHERE step in ({$steps}) and scId={$this->scId} and planId={$planId}";

        M('')->execute($sql);
    }

    //得到所有流程
    public function allProcess($gradeId)
    {
        $response['status'] = 0;
        $response['data'] = array();
        $process = $this->checkProcess($gradeId);
        if (!$process) {
            $response['msg'] = '流程出错';
            $this->ajaxReturn($response);
        }
        foreach ($process['process'] as $k => $v) {
            $response['data'][$v['step']] = $v['status'];
        }
        $response['status'] = 1;
        $this->ajaxReturn($response);
    }

    //新生分班名单
    public function stuLists($gradeId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'gradeId' => $gradeId,
            'isNew' => 1,
            'scId' => $this->scId
        );
        $plan = D('BranchPlan')->where($where)->find();

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        $stu = D('BranchStudentNew')
            ->where(array('planId' => $plan['id'], 'scId' => $this->scId, 'isSync' => 0))
            ->field('id,userId,stuId,name,sex,birthday,voluntPath,perAddress,nation,politics,
            exaCategory,secSchool')
            ->limit($limit_page)
            ->select();
        $total = D('BranchStudentNew')
            ->where(array('planId' => $plan['id'], 'scId' => $this->scId, 'isSync' => 0))
            ->count();
        $total = (int)$total;
        $attend = (int)D('BranchStudentNew')
            ->where(array('planId' => $plan['id'], 'scId' => $this->scId, 'assign' => 0, 'isSync' => 0))
            ->count();
        if ($stu) {
            $stu1 = array();
            foreach ($stu as $k => $v) {
                if ($v['exaCategory'] == '签约生') {
                    $stu2[] = $v;
                } else {
                    $stu1[] = $v;
                }
            }

            if (!empty($stu2))
                $stu1 = array_merge($stu1, $stu2);

            foreach ($stu1 as $k => $v) {
                $stu1[$k]['birthday'] = empty($stu[$k]['birthday']) ? '' : date('Y-m-d', $v['birthday']);
            }
            $response['total'] = $total;
            $response['attend'] = $attend;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['status'] = 1;
            $response['data'] = $stu1;
        }
        $this->ajaxReturn($response);
    }

    //得到班级水平
    private function getLevel()
    {
        $response = array(
            'data' => array(),
            'status' => 0
        );
        $level = D('ClassLevel')->where(array('scId' => $this->scId))->select();
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

    //得到科类
    private function getBranch()
    {
        $response = array(
            'data' => array(),
            'status' => 0
        );
        $rs = D('ClassBranch')->where(array('scId' => array('in', array(0, $this->scId))))->field('branchid as branchId,branch')->select();
        if (!$rs)
            $this->ajaxReturn($response);
        $response['status'] = 1;
        $response['data'] = $rs;
        $this->ajaxReturn($response);
    }

    //得到专业
    private function getMajor($param)
    {
        $response = array(
            'data' => array(),
            'status' => 0
        );
        $rs = D('ClassMajor')->where(array('branch' => $param['branchId'], 'scId' => array('in', array(0, $this->scId))))
            ->field('majorid as majorId,majorname as major')->select();

        if (!$rs)
            $this->ajaxReturn($response);
        $response['status'] = 1;
        $response['data'] = $rs;
        $this->ajaxReturn($response);
    }

    //创建班级
    public function createClass($gradeId)
    {
        $response['status'] = 0;
        $response['data'] = array();
        $type = $_POST['type'];
        $plan = $this->checkProcess($gradeId);
        if (isset($type)) {
            if ($type == 'add') {
                $name = trim($_POST['className']);
                $reg = '/\d+/';//匹配数字的正则表达式
                preg_match_all($reg, $name, $result);
                $className = intval($result[0][0]);
                $where = array(
                    'scId' => $this->scId,
                    'classname' => $className,
                    'grade' => $_POST['gradeId'],
                );
                $bar = D('Class')->where($where)->find();
                if ($bar) {
                    $response['msg'] = '班级已存在';
                    $this->ajaxReturn($response);
                }
                $class = array(
                    'classname' => $className,
                    'grade' => $_POST['gradeId'],
                    'major' => $_POST['majorId'],
                    'levelid' => $_POST['levelId'],
                    'branch' => $_POST['branchId'],
                    'number' => $_POST['number'],
                    'scId' => $this->scId,
                    'createTime' => time(),
                    'isUse' => 0
                );
                $rs = D('Class')->data($class)->add();
                if (!$rs) {
                    $response['msg'] = '添加失败';
                    $this->ajaxReturn($response);
                }
                $process = array(
                    2 => 1, 3 => 3, 6 => 2
                );
                $this->changeProcess($process, $plan['planId']);
                $response['status'] = 1;
                $response['msg'] = '添加成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'del') {
                $ids = $_POST['ids'];
                $number = $this->classNumber($gradeId);
                foreach ($ids as $k => $v) {
                    $temp = (int)$number[$v];
                    if ($temp > 0) {
                        $response['msg'] = '删除失败,已有班级不能删除';
                        $this->ajaxReturn($response);
                    }
                }
                $rs = D('Class')->where(array('classid' => array('in', $ids)))->delete();
                if (!$rs) {
                    $response['msg'] = '删除失败,已有班级不能删除';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                $response['msg'] = '删除成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'edit') {
                $classId = $_POST['classId'];
                $number = $this->classNumber($gradeId);
                if ($number[$classId] > 0) {
                    $postNumber = $_POST['number'];
                    if ($postNumber < $number[$classId]) {
                        $response['msg'] = '修改失败,容纳人数小于当前人数';
                        $this->ajaxReturn($response);
                    }
                    $class = array(
                        'number' => $postNumber,
                        'lastRecordTime' => time()
                    );
                } else {
                    $class = array(
                        'classname' => $_POST['className'],
                        'grade' => $_POST['gradeId'],
                        'major' => $_POST['majorId'],
                        'levelid' => $_POST['levelId'],
                        'branch' => $_POST['branchId'],
                        'number' => $_POST['number'],
                        'lastRecordTime' => time()
                    );
                }
                $rs = D('Class')->where(array('classid' => $classId))->data($class)->save();
                if (!$rs) {
                    $response['msg'] = '修改失败';
                    $this->ajaxReturn($response);
                }
                $process = array(
                    2 => 1, 3 => 3, 6 => 2
                );
                $this->changeProcess($process, $plan['planId']);
                $response['status'] = 1;
                $response['msg'] = '修改成功';
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
        }
        $where = array(
            'c.grade' => $gradeId,
            'c.scId' => $this->scId,
            'c.classType' => 1
        );
        $class = D('Class c')
            ->join('mks_class_major cm ON cm.majorid=c.major', 'LEFT')
            ->join('mks_class_branch cb ON cb.branchid=c.branch', 'LEFT')
            ->join('mks_class_level cl ON cl.levelid=c.levelid', 'LEFT')
            ->where($where)
            ->field('c.classid as classId,c.classname as className,c.number,c.major as majorId,c.branch as branchId,c.levelid as levelId,
            cb.branch,cm.majorname as major,cl.levelname as level')
            ->select();

        $total = (int)D('BranchStudentNew')
            ->where(array('planId' => $plan['planId'], 'scId' => $this->scId, 'isSync' => 0))
            ->count();
        $attend = (int)D('BranchStudentNew')
            ->where(array('planId' => $plan['planId'], 'scId' => $this->scId, 'assign' => 0, 'isSync' => 0))
            ->count();
        if ($class) {
            $realNumber = $this->classNumber($gradeId);
            foreach ($class as &$v) {
                $v['realNumber'] = (int)$realNumber[$v['classId']];
            }
            $response['total'] = $total;
            $response['attend'] = $attend;
            $response['data'] = $class;
            $response['status'] = 1;
        }
        $this->ajaxReturn($response);
    }

    //(指定学生到班)待选班级
    private function getClass($gradeId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $grade = D('Grade')->where(array('gradeid' => $gradeId))->find();
        $plan = $this->checkProcess($gradeId);
        $class = D('Class c')
            ->join('mks_class_major cm ON cm.majorid=c.major', 'LEFT')
            ->join('mks_class_branch cb ON cb.branchid=c.branch', 'LEFT')
            ->join('mks_class_level cl ON cl.levelid=c.levelid', 'LEFT')
            ->where(array('c.grade' => $gradeId))
            ->field('c.classid as classId,c.classname as className,c.number,
            cb.branchid as branchId,cb.branch,cm.majorid as majorId,cm.majorname as major,
            cl.levelid as levelId,cl.levelname as level')
            ->select();
        if (!$class) {
            $this->ajaxReturn($response);
        }

        $stu = D('BranchStudentNew')
            ->where(array('planId' => $plan['planId'], 'scId' => $this->scId, 'assign' => 1, 'isSync' => 0))
            ->field('id,userId,stuId,name,sex,classId')
            ->select();
        $stuMap = array();
        foreach ($stu as $k => $v) {
            $v['disabled'] = true;
            $stuMap[$v['classId']][] = $v;
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

            if (!isset($rs[$v['branchId']]['child'][$grade['gradeid']])) {
                $rs[$v['branchId']]['child'][$grade['gradeid']]
                    = array(
                    'gradeId' => $grade['gradeid'],
                    'name' => $grade['znName'],
                    'parentId' => $v['branchId'],
                    'child' => array()
                );
            }

            if (!isset($rs[$v['branchId']]['child']
                [$grade['gradeid']]['child'][$v['classId']])
            ) {
                $rs[$v['branchId']]['child'][$grade['gradeid']]['child']
                [$v['classId']]
                    = array(
                    'classId' => $v['classId'],
                    'name' => $v['className'] . '(' . $v['level'] . ')',
                    'total' => $v['number'],
                    'parentId' => $grade['gradeid'],
                    'number' => count($stuMap[$v['classId']]),
                    'child' => array()
                );
            }
            $rs[$v['branchId']]['child'][$grade['gradeid']]['child']
            [$v['classId']]['child'] = $stuMap[$v['classId']];
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
    private function getStu($gradeId, $key)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $plan = $this->checkProcess($gradeId);
        $where = array('planId' => $plan['planId'], 'scId' => $this->scId, 'assign' => 0, 'isSync' => 0);
        if (!empty($key)) {
            $where['name'] = array('like', "%{$key}%");
        }
        $stu = D('BranchStudentNew')
            ->where($where)
            ->field('id,userId,stuId,name,sex,classId,class,score,isSigRaw,promise')
            ->select();

        if (!$stu) {
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['data'] = $stu;
        $this->ajaxReturn($response);
    }

    //指定学生到班
    public function assignStu($gradeId)
    {
        $response['status'] = 0;
        $plan = $this->checkProcess($gradeId);
        $type = $_POST['type'];
        if (isset($type)) {
            if ($type == 'save') {
                $allot = $_POST['allot'];
                D('BranchStudentNew')
                    ->where(array('gradeId' => $gradeId, 'planId' => $plan['planId'], 'scId' => $this->scId,
                        'classId' => $allot['classId']))
                    ->data(array(
                        'classId' => '',
                        'class' => '',
                        'serialNumber' => '',
                        'assign' => 0
                    ))
                    ->save();
                $val = '';
                $total = D('BranchStudentNew')
                    ->where(array('gradeId' => $gradeId, 'planId' => $plan['planId'], 'scId' => $this->scId))
                    ->max('serialNumber');
                $i = (int)$total;
                $assign = 1;
                $stuIds = $_POST['stuIds'];
                $rs = true;
                if (!empty($stuIds)) {
                    foreach ($stuIds as &$v) {
                        $i++;
                        $val .= '(' . "{$v}," . "{$allot['classId']}," . "'{$allot['class']}',"
                            . "'{$i}'," . "{$assign}),";
                    }

                    $val = rtrim($val, ',');
                    $sql = "insert into mks_branch_student_new (id,classId,class,serialNumber,assign)
                          values {$val} on duplicate key update classId=values(classId),
                          class=values(class),serialNumber=values(serialNumber)
                          ,assign=values(assign)";
                    $rs = M()->execute($sql);
                }
            } elseif ($type == 'clean') {
                $rs = D('BranchStudentNew')
                    ->where(array('gradeId' => $gradeId, 'scId' => $this->scId, 'planId' => $plan['planId'], 'classId' => $_POST['classId']))
                    ->data(array(
                        'classId' => '',
                        'class' => '',
                        'serialNumber' => '',
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

    //合成成绩  //已测试
    private function scoreCombine($planId)
    {//合成成绩
        //得到比重规则
        /*        $ratios = D('BranchWish')->where("planId={$planId}")
                    ->field('id,ratio')->select();//subId=>ratio,examRatio*/
        $allRatio = D('BranchSubjectNew s')
            ->join('mks_branch_exam_new e ON s.branchExamId=e.id', 'LEFT')
            ->where(array('s.planId' => $planId, 's.scId' => $this->scId))
            ->field('s.id as subId,s.ratio as subRatio,s.maxPoint,e.id as examId,e.ratio as examRatio')
            ->select();

        if (!$allRatio)
            return false;
        $ratio = array();
        foreach ($allRatio as $k => $v) {
            $ratio[$v['subId']] = array(
                'ratio' => $v['subRatio'],
                'examRatio' => round($v['examRatio'] / $v['maxPoint'], 1)
            );
        }

        //得到成绩
        $students = D('BranchStudentNew')
            ->where(array('planId' => $planId, 'isSync' => 0, 'scId' => $this->scId))
            ->field('id,userId,subScore')
            ->select();

        //得到合成成绩
        foreach ($students as $k => $v) {
            $subScore = json_decode($v['subScore'], true);
            $score[$v['id']] = 0;
            foreach ($subScore as $k1 => $v1) {
                $score[$v['id']] += $ratio[$k1]['ratio'] * (float)$v1 * $ratio[$k1]['examRatio'];
            }
        }
        arsort($score);
        $rank = array_keys($score);


        foreach ($students as $k => $v) {
            $perRank = array_search($v['id'], $rank) + 1;
            $students[$k]['score'] = $score[$v['id']];
            $students[$k]['rank'] = $perRank;
        }

        $cnt = count($students);
        $val = '';
        for ($i = 0; $i < $cnt; $i++) {
            $val .= '(' . $students[$i]['id'] . ',' . "'{$students[$i]['score']}'" . ','
                . "'{$students[$i]['rank']}'" . '),';
        }
        $val = rtrim($val, ',');
        $sql = "insert into mks_branch_student_new (id,score,rank) 
            values {$val} on duplicate key update score=values(score),rank=values(rank)";

        $rs = M()->execute($sql);
        return $rs;
    }

    //分班成绩合成设置
    public function scoreSetting($gradeId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $plan = $this->checkProcess($gradeId);
        $type = $_POST['type'];
        if (!empty($type)) {
            if ($type == 'addExam') {//添加考试
                $rs = D('BranchExamNew')->where(
                    array('examination' => $_POST['exam'],
                        'planId' => $plan['planId'],
                        'scId' => $this->scId))->find();
                if ($rs) {
                    $response['msg'] = '考试已存在';
                    $response['examId'] = $rs;
                    $this->ajaxReturn($response);
                }
                $exam = array(
                    'examination' => $_POST['exam'],
                    'ratio' => $_POST['ratio'],
                    'planId' => $plan['planId'],
                    'scId' => $this->scId);
                $rs = D('BranchExamNew')->add($exam);
            } elseif ($type == 'editExam') {
                $exam = array(
                    'examination' => $_POST['exam'],
                    'ratio' => $_POST['ratio']
                );
                $rs = D('BranchExamNew')->where(array('id' => $_POST['examId']))->save($exam);
            } /*elseif ($type == 'addSubject') {//添加依据科目
                $rs = D('BranchSubjectNew')->where(array('subject' => $_POST['subject'],
                    'branchExamId' => $_POST['examId']))->find();
                if ($rs) {
                    $response['msg'] = '科目已存在';
                    $this->ajaxReturn($response);
                }
                $total = D('BranchStudentNew')
                    ->where(array('planId' => $plan['planId'], 'scId' => $this->scId))
                    ->count();
                $total = (int)$total;
                $data = array(
                    'subject' => $_POST['subject'],
                    'branchExamId' => $_POST['examId'],
                    'recordNumber' => 0,
                    'totalNumber' => $total,
                    'ratio' => $_POST['ratio'],
                    'maxPoint' => $_POST['maxPoint'],
                    'planId' => $plan['planId'],
                    'scId' => $this->scId
                );
                $rs = D('BranchSubjectNew')->add($data);
            }*/ elseif ($type == 'delExam') {//删除考试依据
                $rs = D('BranchExamNew')->where(array('id' => $_POST['examId']))->delete();
                if ($rs) {
                    D('BranchSubjectNew')->where(array('branchExamId' => $_POST['examId']))->delete();
                }
            } /*elseif ($type == 'delSubject') {//删除科目依据
                $rs = D('BranchSubjectNew')->where(array('id'=>$_POST['subId']))->delete();
            }*/ elseif ($type == 'save') {
                $total = D('BranchStudentNew')
                    ->where(array('planId' => $plan['planId'], 'scId' => $this->scId, 'isSync' => 0))
                    ->count();
                $total = (int)$total;

                $data = D('BranchSubjectNew')->where(array('planId' => $plan['planId'], 'scId' => $this->scId))
                    ->field('id,branchExamId')->select();
                $map = array();
                foreach ($data as $k => $v) {
                    $map[$v['branchExamId']][] = $v['id'];
                }
                $subjects = I('post.subjects');
                $diff = array();
                $val = '';
                foreach ($subjects as $k => $v) {
                    $temp = array();
                    foreach ($v['sub'] as $k1 => $v1) {
                        $val .= '(' . "'{$v1['subId']}'" . ',' . "'{$v1['subject']}'" . ','
                            . "{$v['examId']}" . ',' . "0" . ',' . "{$total}" . ','
                            . "'{$v1['subRatio']}'" . ',' . "'{$v1['maxPoint']}'" . ','
                            . "{$plan['planId']}" . ',' . "{$this->scId}" .
                            '),';
                        if (!empty($v1['subId'])) {
                            $temp[] = $v1['subId'];
                        }
                    }
                    $diff = array_merge($diff, array_diff($map[$v['examId']], $temp));
                }

                if (!empty($diff)) {
                    D('BranchSubjectNew')->where(array('id' => array('in', $diff)))->delete();
                }
                $rs = true;
                if ($val != '') {
                    $val = rtrim($val, ',');
                    $sql = "insert into mks_branch_subject_new (id,subject,branchExamId,recordNumber,
                          totalNumber,ratio,maxPoint,planId,scId) values {$val} on duplicate key update 
                          subject=values(subject),branchExamId=values(branchExamId),recordNumber=values(recordNumber),
                          totalNumber=values(totalNumber),ratio=values(ratio),maxPoint=values(maxPoint),
                          planId=values(planId),scId=values(scId)";
                    $rs = M('')->execute($sql);
                }
            } else {
                $response['msg'] = '没权限进行操作';
                $this->ajaxReturn($response);
            }
            if ($rs) {
                $this->scoreCombine($plan['planId']); //更改学生合成分数
                $response['status'] = 1;
                $response['msg'] = '操作成功';
                $this->ajaxReturn($response);
            } else {
                $response['msg'] = '操作失败';
                $this->ajaxReturn($response);
            }
        }

        $where = array(
            'e.planId' => $plan['planId'],
            'e.scId' => $this->scId);
        //得到分班成绩依据
        $data = M('BranchExamNew e')
            ->join('mks_branch_subject_new s ON e.id=s.branchExamId', 'LEFT')
            ->where($where)
            ->field('e.id as examId,e.examination,e.planId,e.ratio as examRatio,s.id as subId,s.subject,s.ratio as subRatio,s.maxPoint')
            ->select();

        if (!$data) {
            $this->ajaxReturn($response);
        }
        $re = array();
        foreach ($data as $k => $v) {
            if (!isset($re[$v['examId']]))
                $re[$v['examId']] = array(
                    'examId' => $v['examId'],
                    'examination' => $v['examination'],
                    'planId' => $v['planId'],
                    'examRatio' => $v['examRatio'],
                    'sub' => array()
                );
            if (!empty($v['subId']))
                $re[$v['examId']]['sub'][] = array(
                    'subId' => $v['subId'],
                    'subject' => $v['subject'],
                    'subRatio' => $v['subRatio'],
                    'maxPoint' => $v['maxPoint']
                );
        }
        sort($re);
        $response['status'] = 1;
        $response['data'] = $re;
        $this->ajaxReturn($response);
    }

    //(分班成绩合成设置)导入成绩
    public function scoreManage($gradeId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $plan = $this->checkProcess($gradeId);
        $type = $_REQUEST['type'];
        if (!empty($type)) {
            $subId = I('post.subId');
            $students = D('BranchStudentNew')
                ->where(array('planId' => $plan['planId'], 'isSync' => 0, 'scId' => $this->scId))
                ->field('id,userId,name,grade,regNumber,subScore')
                ->select();
            if ($type == 'record') {
                $key = I('post.key');
                $where_r = array(
                    'planId' => $plan['planId'],
                    'isSync' => 0,
                    'scId' => $this->scId
                );
                if (isset($key)) {
                    $where_r['name|regNumber'] = array('like', "%{$key}%");
                }
                /*$page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
                $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
                $pre_page = ($page - 1) * $count;
                $limit_page = "$pre_page,$count";*/
                $students = D('BranchStudentNew')
                    ->where($where_r)
                    ->field('id,userId,name,grade,regNumber,subScore')
                    /* ->limit($limit_page)*/
                    ->select();
                if (!$students) {
                    $this->ajaxReturn($response);
                }
                /*    $total = (int)D('BranchStudentNew')
                        ->where($where_r)
                        ->count();*/
                foreach ($students as $k => $v) {
                    $subScore = json_decode($v['subScore'], true);
                    $score = array_key_exists($subId, $subScore) ? $subScore[$subId] : null;
                    $students[$k]['subScore'] = $score;
                }
                //$response['total'] = $total;
                //$response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
                $response['status'] = 1;
                $response['data'] = $students;
                $this->ajaxReturn($response);
            } elseif ($type == 'save') {
                $subId = I('post.subId');
                $score = I('post.subScore');//userId=>score
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
                $val = rtrim($val, ',');
                $sql = "insert into mks_branch_student_new (id,subScore) 
            values {$val} on duplicate key update subScore=values(subScore)";
                $res = M()->execute($sql);
                if (!$res) {
                    $response['msg'] = '保存失败';
                    $this->ajaxReturn($response);
                }
                D('BranchSubjectNew')->where(array('id' => $subId))->data($data)->save();
                $response['status'] = 1;
                $response['msg'] = '保存成功';
                $this->scoreCombine($plan['planId']); //更改学生合成成绩
                $this->ajaxReturn($response);
            } elseif ($type == 'import') {
                $file = $_FILES;
                if (empty($file)) {
                    $response['msg'] = '未检索到文件！';
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
                    $students[$k]['subScore'] = $map[$v['grade'] . $v['name'] . $v['regNumber']];
                }
                $response['status'] = 1;
                $response['data'] = $students;
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
                    ->setCellValue('A1', '年级')
                    ->setCellValue('B1', '姓名')
                    ->setCellValue('C1', '考号')
                    ->setCellValue('D1', '分数');

                foreach ($students as $k => $v) {
                    $num = $k + 2;
                    $objPHPExcel->setActiveSheetIndex(0)
                        //Excel的第A列，uid是你查出数组的键值，下面以此类推
                        ->setCellValue('A' . $num, $v['grade'])
                        ->setCellValue('B' . $num, $v['name'])
                        ->setCellValue('C' . $num, $v['regNumber'])
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
        $where = array(
            'planId' => $plan['planId'],
            'branchExamId' => $exId
        );
        $field = 'id,subject,totalNumber,recordNumber,maxPoint';
        $subject = D('BranchSubjectNew')->where($where)
            ->field($field)
            ->select();
        if (!$subject) {
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['data'] = $subject;
        $this->ajaxReturn($response);
    }

    //分班合成成绩情况
    public function scoreInfo($gradeId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $plan = $this->checkProcess($gradeId);
        $exams = D('BranchExamNew e')
            ->join('mks_branch_subject_new s ON s.branchExamId=e.id', 'LEFT')
            ->where(array('e.planId' => $plan['planId'], 'e.scId' => $this->scId))
            ->field('s.id as subId,s.subject,e.id as examId,e.examination')
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
        $where_sn = array('planId' => $plan['planId'], 'isSync' => 0, 'scId' => $this->scId);
        $key = $_POST['key'];
        if (!empty($key)) {
            $where_sn['name|sex'] = array('like', "%{$key}%");
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
            $order = 'score desc';
        }

        $students = D('BranchStudentNew')
            ->where($where_sn)
            ->order($order)
            ->field('id,userId,name,sex,score,subScore')
            ->limit($limit_page)
            ->select();
        $total = (int)D('BranchStudentNew')
            ->where($where_sn)
            ->count();
        if (!$students) {
            $this->ajaxReturn($response);
        }

        foreach ($students as $k => $v) {
            $subScore = json_decode($v['subScore'], true);
            $temp = array();
            foreach ($subScore as $k1 => $v1) {
                $temp[$k1] = array(
                    'score' => $v1,
                );
            }
            unset($students[$k]['subScore']);
            $students[$k] = $students[$k] + $temp;
        }
        $download = $_REQUEST['download'];
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
                ->setCellValue('A1', '姓名')
                ->setCellValue('B1', '性别')
                ->setCellValue('C1', '分数');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:A2');
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B1:B2');
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('C1:C2');
            $str = 'D';
            $map = array();
            $sub = 0;
            foreach ($exam as $k => $v) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($str . '1', $v['name']);
                $temp = ord($str);
                $count = count($v['subs']);
                $i = 0;
                foreach ($v['subs'] as $k1 => $v1) {
                    $sub++;
                    $i++;
                    $str_sub = chr($temp);
                    $map[$str_sub] = $v1['subId'];
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue($str_sub . '2', $v1['name']);
                    if ($i < $count)
                        $temp++;
                }
                $temp2 = chr($temp);
                $objPHPExcel->setActiveSheetIndex(0)->mergeCells($str . '1:' . $temp2 . '1');
                $str = chr(ord($temp2) + 1);
            }

            foreach ($students as $k => $v) {
                $num = $k + 3;
                $objPHPExcel->setActiveSheetIndex(0)
                    //Excel的第A列，uid是你查出数组的键值，下面以此类推
                    ->setCellValue('A' . $num, $v['name'])
                    ->setCellValue('B' . $num, $v['sex'])
                    ->setCellValue('C' . $num, $v['score']);
                $foo = ord('C');
                for ($i = 1; $i <= $sub; $i++) {
                    $foo += 1;
                    $str = chr($foo);
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue($str . $num, $v[$map[$str]]['score']);
                }
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
        $rs['student'] = $students;
        $response['total'] = $total;
        $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        $response['data'] = $rs;
        $response['status'] = 1;
        $this->ajaxReturn($response);

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

    //签约生分班
    private function signDivide($gradeId, $planId)
    {
        //得到所有班级
        $classes = D('Class c')
            ->join('mks_class_level l ON c.levelid=l.levelid', 'LEFT')
            ->where(array('c.grade' => $gradeId, 'c.scId' => $this->scId, 'c.classType' => 1))
            ->field('c.classid as classId,c.className as className,c.levelid as levelId,
            c.number,l.levelname as level')
            ->select();

        $classNumber = $this->classNumber($gradeId);
        foreach ($classes as $k => $v) {
            $classes[$k]['number'] -= (int)$classNumber[$v['classId']];  //每个班剩余多少人
        }
        //找出签约生
        $where = array(
            'planId' => $planId,
            'scId' => $this->scId,
            'isSigRaw' => 1,
            'assign' => 0,
            'isSync' => 0
        );
        $stu = D('BranchStudentNew')->where($where)
            ->field('id,score,rank,isSigRaw,promise')
            ->select();

        if (!$stu) {
            return;
        }
        D('BranchStudentNew')->where($where)
            ->save(array('isPromise' => 0));

        usort($stu, function ($a, $b) {
            $al = (int)$a['rank'];
            $bl = (int)$b['rank'];
            if ($al == $bl)
                return 0;
            return ($al > $bl) ? -1 : 1; //升序
        });

        $stuTree = array();
        foreach ($stu as $k => $v) {
            $stuTree[$v['promise']][] = $v;
        }

        foreach ($classes as $k => $v) {
            $cnt = (int)$v['number'];
            $num = count($stuTree[$v['levelId']]);
            if ($cnt > 0 && $num > 0) {
                $classes[$k]['stu'] = array_splice($stuTree[$v['levelId']], 0, $cnt);
            }
        }

        $val = '';
        $isPromise = 1;
        foreach ($classes as $k => $v) {
            if (isset($v['stu'])) {
                foreach ($v['stu'] as $k1 => $v1) {
                    $val .= '(' . "{$v1['id']}," . "'{$v['classId']}'," .
                        "'{$v['className']}'," . "{$isPromise}),";
                }
            }
        }
        $rs = true;
        $val = rtrim($val, ',');
        if (!empty($val)) {
            $sql = "insert into mks_branch_student_new (id,classId,class,isPromise)
                          values {$val} on duplicate key update classId=values(classId),
                          class=values(class),isPromise=values(isPromise)";
            $rs = M()->execute($sql);
        }

        if (!$rs) {
            $response['msg'] = '签约生分班出错';
            $response['status'] = 0;
            $this->ajaxReturn($response);
        }
        return;
    }

    //生成分班     //已测试
    private function quickDivide($planId, $gradeId, $classLimit, $fill, $distribute, $sex, $priority, $snake, $numberLimit)
    {

        //得到所有班级
        $classes = D('Class c')
            ->join('mks_class_level l ON c.levelid=l.levelid', 'LEFT')
            ->where(array('c.grade' => $gradeId, 'c.scId' => $this->scId, 'c.classType' => 1))
            ->field('c.classid as classId,c.className as className,c.levelid as levelId,
            c.number,l.levelname as level')
            ->select();

        //得到所有新生

        $students = D('BranchStudentNew')->where(array(
            'scId' => $this->scId,
            'planId' => $planId,
            'gradeId' => $gradeId,
            'assign' => 0,
            'isPromise' => 0,
            'isSync' => 0))
            ->field('id,userId,stuId,name,sex,score,rank,assign')
            ->select();
        shuffle($students);//将学生顺序打乱

        //指定到班的每个班的学生人数 以及签约生
        $class_a = D('BranchStudentNew')->where(array(
            'scId' => $this->scId,
            'planId' => $planId,
            'gradeId' => $gradeId,
            'assign' => 1,
            'isPromise' => 1,
            'isSync' => 0))
            ->group('classId')->field('classId,count(id) as number')->select();
        $class_n = array();
        foreach ($class_a as $k => $v) {
            $class_n[$v['classId']] = (int)$v['number'];
        }
        $mapN = $this->classNumber($gradeId);
        if ($class_a) { //减少人数
            foreach ($classes as $k => $v) {
                $classes[$k]['number'] -= $class_n[$v['classId']];
                $classes[$k]['number'] -= (int)$mapN[$v['classId']];  //减去现有的学生人数
            }
        }

        foreach ($classes as $k => $v) {
            if ($classes[$k]['number'] < 1) {
                unset($classes[$k]);
            }  //减去现有的学生人数
        }

        /*$classLimit 班级限制 level 级别优先 parallel 所有班平行
        $fill 分配形式 sequence 按照班级序号 parallel 班级平行
        $distribute 分配方式 score 按照分数分配 random 随机分配
        $sex 性别 ave平均分配 random 随机*/

        //班级处理
        $newClass = array();

        if ($classLimit == 'level' && !empty($priority)) { //级别优先 将班级放入各自级别 //已测试
            foreach ($classes as $k => $v) {
                foreach ($priority as $key => $levels) {
                    if (!in_array($v['level'], $levels))
                        continue;
                    else {
                        if (!isset($newClass[$key]))
                            $newClass[$key] = array();
                        $newClass[$key][] = $v;
                    }
                }
            }
            //按照优先级排序 升序
            ksort($newClass);
        } else {
            foreach ($classes as $k => $v) { //平行 将所有班级放入一个级别
                $newClass[0][] = $v;
            }
        }
        $classNumber = $this->classNumber($gradeId);

        foreach ($newClass as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $newClass[$k][$k1]['number'] -= $classNumber[$v1['classId']];
            }
        }


        $proClass = array();
        $male = array();
        $female = array();
        $grade_stu = array();

        if ($distribute == 'score') {  //按分数
            if ($sex == 'ave') { //性别平均
                foreach ($students as $k => $v) {
                    if ($v['sex'] == '男') {
                        $male[] = $v;
                    } else {
                        $female[] = $v;
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
                $grade_stu = array_merge($male, $female); //将性别分开后排序(两边大中间小)然后合并

            } else {
                $grade_stu = $students;
                usort($grade_stu, function ($a, $b) {
                    $al = (int)$a['rank'];
                    $bl = (int)$b['rank'];
                    if ($al == $bl)
                        return 0;
                    return ($al < $bl) ? -1 : 1; //升序
                });
            }
        } else {
            if ($sex == 'ave') {
                foreach ($students as $k => $v) {
                    if ($v['sex'] == '男') {
                        $male[] = $v;
                    } else {
                        $female[] = $v;
                    }
                }
                $grade_stu = array_merge($male, $female);
            }
        }


//人数分配形式 一个班级一个班级的填充 按序号 number为总人数 平行 number=1

        foreach ($newClass as $l => $classes) {

            if ($fill == 'sequence') {//顺序填充 //正确
                foreach ($classes as $k => $class) {
                    if (!isset($proClass[$class['classId']]['stu'])) {
                        $proClass[$class['classId']]['className'] = $class['className'];
                        $proClass[$class['classId']]['classId'] = $class['classId'];
                        $proClass[$class['classId']]['stu'] = array();
                    }
                    $fillNumber = $class['number'];
                    if ($fillNumber < 0 || $fillNumber == 0) {
                        continue;
                    }
                    $count = count($grade_stu);
                    if ($count < 1)
                        continue;
                    if ($fillNumber > $count)
                        $fillNumber = $count;
                    if ($sex == 'ave') {
                        $fillNumber1 = ceil($fillNumber / 2);
                        $fillNumber2 = $fillNumber - $fillNumber1;
                        $spliceStu1 = array_splice($grade_stu, 0, $fillNumber1);//取男生

                        $spliceStu2 = array_splice($grade_stu, -$fillNumber2);//取女生
                        $proClass[$class['classId']]['stu']
                            = array_merge($proClass[$class['classId']]['stu'], $spliceStu1, $spliceStu2);
                    } else {
                        $spliceStu = array_splice($grade_stu, 0, $fillNumber);
                        $proClass[$class['classId']]['stu']
                            = array_merge($proClass[$class['classId']]['stu'], $spliceStu);
                    }
                }
            } else {//平行填充 //正确
                do {
                    $flag = false;
                    $label = 0;
                    $foo = 1;//记录蛇形
                    foreach ($newClass[$l] as $k => $class) {

                        $number = $newClass[$l][$k]['number'];
                        if ($number < 0 || $number == 0) {
                            continue;
                        }

                        if (!isset($proClass[$class['classId']]['stu'])) {
                            $proClass[$class['classId']]['className'] = $class['className'];
                            $proClass[$class['classId']]['classId'] = $class['classId'];
                            $proClass[$class['classId']]['stu'] = array();
                        }

                        $fillNumber = 1;
                        /* $number = $newClass[$l][$k]['number'];
                         var_dump($number);
                         if($number<0||$number==0){
                             continue;
                         }*/

                        $count = count($grade_stu);
                        if ($count < 1)
                            continue;
                        $spliceStu = array_splice($grade_stu, 0, $fillNumber);
                        //var_dump($spliceStu);

                        $proClass[$class['classId']]['stu']
                            = array_merge($proClass[$class['classId']]['stu'], $spliceStu);
                        // var_dump($proClass);
                        // die;
                        $newClass[$l][$k]['number'] -= 1;
                        $number = $newClass[$l][$k]['number'];
                        if ($number < 0 || $number == 0) {
                            continue;
                        }
                        $count = count($grade_stu);
                        if ($count < 1)
                            continue;
                        $spliceStu = array_splice($grade_stu, -1, $fillNumber);
                        $proClass[$class['classId']]['stu']
                            = array_merge($proClass[$class['classId']]['stu'], $spliceStu);

                        $newClass[$l][$k]['number'] -= 1;
                        $number = $newClass[$l][$k]['number'];
                        $count = count($grade_stu);
                        //var_dump($count);
                        if ($number > 0 && $count > 0)
                            $label += $number;
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
                    foreach ($proClass as $class => $stu) {
                        usort($proClass[$class]['stu'], function ($a, $b) {
                            $al = (int)$a['rank'];
                            $bl = (int)$b['rank'];
                            if ($al == $bl)
                                return 0;
                            return ($al < $bl) ? -1 : 1; //升序
                        });
                    }
                } elseif ($numberLimit == 'name') {
                    foreach ($proClass as $class => $stu) {
                        foreach ($stu['stu'] as $k => $v) {
                            $proClass[$class]['stu'][$k]['char'] = $this->getFirstCharter($v['name']);

                        }
                        usort($proClass[$class]['stu'], function ($a, $b) {
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
    private function classLevel($gradeId)
    {
        $response = array(
            'data' => array(),
            'status' => 0
        );
        $where = array(
            'scId' => $this->scId,
            'grade' => $gradeId,
            'classType' => 1,
        );
        $levelids = D('Class')->where($where)->field('distinct(levelid)')->select();
        $levelId = array_map(function ($v) {
            return $v['levelid'];
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


    //快速分班  //已测试
    public function quickBranch($gradeId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $plan = $this->checkProcess($gradeId);
        $type = $_REQUEST['type'];
        if (!empty($type)) {
            if ($type == 'save') {
                $stuLists = I('post.stuLists');
                $val = '';
                $map = array();
                $classIds = array();
                foreach ($stuLists as &$v) {
                    $val .= '(' . "{$v['id']}," . "'{$v['classId']}'," . "'{$v['className']}',"
                        . "'{$v['serialNumber']}'),";
                    $map[] = array(
                        'userId' => $v['userId'],
                        'stuId' => $v['stuId'],
                        'classId' => $v['classId'],
                        'class' => $v['className'],
                        'serialNumber' => $v['serialNumber']
                    );
                    if (!in_array($v['classId'], $classIds))
                        $classIds[] = $v['classId'];
                }

                $val = rtrim($val, ',');
                $sql = "insert into mks_branch_student_new (id,classId,class,serialNumber)
                          values {$val} on duplicate key update classId=values(classId),
                          class=values(class),serialNumber=values(serialNumber)";
                //班级改为使用中
                if (!empty($classIds)) {
                    $where_c = array('classid' => array('in', $classIds));
                    D('Class')->where($where_c)
                        ->data(array('isUse' => 1))->save();
                }
                // 同步user/student_info 表
                $res = $this->stuHandle('setClass', $plan['planId'], $map);
                if (!$res) {
                    $response['msg'] = '同步信息表出错！';
                    $this->ajaxReturn($response);
                }
                $res = M()->execute($sql);
                if ($res) {
                    $process = array(
                        6 => 1, 7 => 3, 8 => 3, 9 => 3
                    );
                    $this->changeProcess($process, $plan['planId']);
                    $response['msg'] = '保存成功';
                    $response['status'] = 1;
                    $this->ajaxReturn($response);
                }
            } elseif ($type == 'divide') {
                $way = I('post.way');
                $number = I('post.number');
                $sex = I('post.sex');
                $equal = I('post.equal');
                $priority = I('post.priority');
                //签约生分班
                $this->signDivide($gradeId, $plan['planId']);
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
                $data = $this->quickDivide($plan['planId'], $gradeId, $classLimit, $fill, $distribute, $sex, $priority, $snake, $number);
                //加入指定到班的学生

                $stu_a = D('BranchStudentNew')->where(array(
                    'scId' => $this->scId,
                    'planId' => $plan['planId'],
                    'gradeId' => $gradeId,
                    'isSync' => 0,
                    '_string' => '(assign=1 or (assign=0 and isPromise=1))'
                ))->field('id,userId,stuId,name,sex,classId,class as className,score,rank,assign')
                    ->select();
                if ($stu_a) {
                    foreach ($stu_a as $k => $v) {
                        $data[$v['classId']]['stu'][] = $v;
                    }
                }
                $mapS = $this->classSer($gradeId);

                $result = array();
                foreach ($data as $k => $v) {
                    foreach ($v['stu'] as $k1 => $v1) {
                        $result[] = array(
                            'id' => $v1['id'],
                            'userId' => $v1['userId'],
                            'stuId' => $v1['stuId'],
                            'classId' => $v['classId'],
                            'className' => $v['className'],
                            'sex' => $v1['sex'],
                            'name' => $v1['name'],
                            'serialNumber' => $k1 + 1 + (int)$mapS[$v['classId']]
                        );
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
                    ->setCellValue('C1', '分班班级')
                    ->setCellValue('D1', '分班座号');
                $students = D('BranchStudentNew')
                    ->where(array('gradeId' => $gradeId, 'planId' => $plan['planId'], 'isSync' => 0, 'scId' => $this->scId))
                    ->field('name,sex,class,serialNumber')->select();
                foreach ($students as $k => $v) {
                    $num = $k + 2;
                    $objPHPExcel->setActiveSheetIndex(0)
                        //Excel的第A列，uid是你查出数组的键值，下面以此类推
                        ->setCellValue('A' . $num, $v['name'])
                        ->setCellValue('B' . $num, $v['sex'])
                        ->setCellValue('C' . $num, $v['class'])
                        ->setCellValue('D' . $num, $v['serialNumber']);
                }
                $objPHPExcel->getActiveSheet()->setTitle('新生分班表');
                $objPHPExcel->setActiveSheetIndex(0);
                //header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . '新生分班表' . '.xls"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
                exit;
            } else {
                $response['msg'] = '没权限进行操作';
                $this->ajaxReturn($response);
            }
            $this->ajaxReturn($response);
        }
    }

    //手动调整
    public function manualAdjust($gradeId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $plan = $this->checkProcess($gradeId);
        $grade = D('Grade')->where(array('gradeid' => $gradeId))->find();
        $class = D('Class c')
            ->join('mks_class_level cl ON cl.levelid=c.levelid', 'LEFT')
            ->where(array('c.grade' => $gradeId, 'c.scId' => $this->scId))
            ->field('c.classid as classId,c.classname as class,c.number,cl.levelname as level')
            ->select();
        $number = $this->classNumber($gradeId);
        //  var_dump($number);
        //分班后的学生
        $stu_where = array(
            'planId' => $plan['planId'],
            'gradeId' => $gradeId,
            'scId' => $this->scId,
            'isSync' => 0
        );
        $stu = D('BranchStudentNew')->where($stu_where)
            ->group('classId')
            ->field('classId,class,count(id) as realNumber')
            ->select();

        $map = array();
        foreach ($stu as $k => $v) {
            $map[$v['classId']] = $v['realNumber'];
        }

        if (!$class)
            $this->ajaxReturn($response);

        foreach ($class as $k => $v) {
            $class[$k]['realNumber'] = empty($map[$v['classId']]) ? 0 : $map[$v['classId']];
            $class[$k]['realNumber'] += (int)$number[$v['classId']];
            $class[$k]['grade'] = $grade['znName'];
        }
        $response['status'] = 1;
        $response['data'] = $class;
        $this->ajaxReturn($response);
    }

    //(手动调整)参与分班学生名单
    private function classStu($gradeId, $classId, $key)
    {

        $response = array(
            'status' => 0,
            'data' => array()
        );
        $plan = $this->checkProcess($gradeId);
        $where = array(
            'scId' => $this->scId,
            'classId' => array('neq', $classId),
            'planId' => $plan['planId'],
            'gradeId' => $gradeId,
            'isSync' => 1
        );
        if (!empty($key)) {
            $where['name'] = array('like', "%{$key}%");
        }
        $stu = D('BranchStudentNew')->where($where)
            ->field('id,userId,stuId,name,sex,classId,class,serialNumber,score,rank,assign')->select();
        if (!$stu) {
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['data'] = $stu;
        $this->ajaxReturn($response);
    }

    //(手动调整)指定学生到班
    public function assignStudent($gradeId)
    {
        $response = array(
            'status' => 0,
            'msg' => ''
        );
        $ids = I('post.ids');
        $proClass = I('post.proClass');
        $plan = $this->checkProcess($gradeId);
        $val = '';
        $i = (int)D('BranchStudentNew')
            ->where(array('classId' => $proClass['classId'], 'planId' => $plan['planId'], 'scId' => $this->scId, 'isSync' => 1))
            ->max('serialNumber');
        $assign = 1;
        $map = array();

        foreach ($ids as &$v) {
            $i++;
            $val .= '(' . "{$v['id']}," . "'{$proClass['classId']}'," . "'{$proClass['class']}',"
                . "'{$i}'," . "{$assign}),";
            $map[] = array(
                'userId' => $v['userId'],
                'stuId' => $v['stuId'],
                'classId' => $proClass['classId'],
                'class' => $proClass['class'],
                'serialNumber' => $i
            );
        }
        $val = rtrim($val, ',');
        $sql = "insert into mks_branch_student_new (id,classId,class,serialNumber,assign)
                          values {$val} on duplicate key update classId=values(classId),
                          class=values(class),serialNumber=values(serialNumber)
                          ,assign=values(assign)";
        // 同步user/student_info 表

        $res = $this->stuHandle('setClass', $plan['planId'], $map);
        if (!$res) {
            $response['msg'] = '同步信息表出错！';
            $this->ajaxReturn($response);
        }
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
    public function equalChange($gradeId)
    {
        $response = array(
            'status' => 0,
            'msg' => ''
        );
        $ids = I('post.ids');
        $preClass = I('post.preClass');
        $proClass = I('post.proClass');
        $plan = $this->checkProcess($gradeId);
        $i = count($ids);
        $stuNumber = $i;
        $notIds = array();
        foreach ($ids as $k => $v) {
            $notIds[] = $v['id'];
        }
        $fill = array();
        $foo = 0;
        $res = false;
        $map = array();
        $where = array(
            'classId' => $proClass['classId'],
            'scId' => $this->scId,
            'planId' => $plan['planId'],
            'isSync' => 1
        );
        $sn = (int)D('BranchStudentNew')->where($where)->max('serialNumber');
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
            $changeStu = D('BranchStudentNew')->where($where)->order($order)->find();
            $notIds[] = $changeStu['id'];
            if (!$changeStu)
                break;
            $index = $ids[$foo]['id'];
            $fill[] = array(
                'id' => $index,
                'userId' => $ids[$foo]['userId'],
                'stuId' => $ids[$foo]['stuId'],
                'classId' => $proClass['classId'],
                'class' => $proClass['class'],
                'serialNumber' => $changeStu['serialNumber'],
            );

            //序号为空
            $serialNumber = $ids[$foo]['serialNumber'];
            if (empty($serialNumber)) {
                $serialNumber = $sn;
                $sn++;
            }

            $fill[] = array(
                'id' => $changeStu['id'],
                'userId' => $changeStu['userId'],
                'stuId' => $changeStu['stuId'],
                'classId' => $preClass[$index]['classId'],
                'class' => $preClass[$index]['class'],
                'serialNumber' => $serialNumber,
            );
            $foo++;
        } while ($i > 0);
        if (count($fill) != $stuNumber * 2) {
            $response['msg'] = '交换信息出错';
            $this->ajaxReturn($response);
        }
        $val = '';
        $assign = 1;
        foreach ($fill as &$v) {
            $val .= '(' . "{$v['id']}," . "'{$v['classId']}'," . "'{$v['class']}',"
                . "'{$v['serialNumber']}'" . "{$assign}),";
            $map[] = array(
                'userId' => $v['userId'],
                'stuId' => $v['stuId'],
                'classId' => $v['classId'],
                'class' => $v['class'],
                'serialNumber' => $v['serialNumber']
            );

        }
        $val = rtrim($val, ',');
        $sql = "insert into mks_branch_student_new (id,classId,class,serialNumber,assign)
                          values {$val} on duplicate key update classId=values(classId),
                          class=values(class),serialNumber=values(serialNumber),
                          assign=values(assign)";
        // 同步user/student_info 表

        $res = $this->stuHandle('setClass', $plan['planId'], $map);
        if (!$res) {
            $response['msg'] = '同步信息表出错！';
            $this->ajaxReturn($response);
        }
        $res = M()->execute($sql);
        if (!$res) {
            $response['msg'] = '操作失败';
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['msg'] = '操作成功';
        $this->ajaxReturn($response);
    }

    //(学生补录)得到某个年级的班级
    private function gradeClass($gradeId)
    {
        $response = array(
            'status' => 0,
            'data' => array(),
        );
        $grade = D('Grade')->where(array('gradeid' => $gradeId))->find();
        $class = D('Class')->where(array('grade' => $gradeId, 'scId' => $this->scId))
            ->field('classid as classId,classname as className')
            ->select();
        if (!$class) {
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['data'] = $class;
        $response['grade'] = $grade['znName'];
        $this->ajaxReturn($response);
    }

    //学生补录
    public function addStu($gradeId)
    {
        $response['status'] = 0;
        $data = I('post.');
        $grade = D('Grade')->where(array('gradeid' => $gradeId))->find();
        $data['grade'] = $grade['name'];
        //$plan=$this->checkProcess($gradeId);
        //添加新生 //已测
        /*  $prefix = M('school')->field('prefix')->where(array('scId' => $this->scId))->find();
          $prefix = $prefix['prefix'];
          $number = M('user')->where(array('scId' => $this->scId))->max('number');
          $number++;*/
        $password = $this->InitialPassword();
        $result = $this->checkName($data['name']);
        $data['name'] = $result['name'];
        $data['prefix'] = $result['prefix'];
        $data['number'] = $result['number'];
        $sn = (int)D('StudentInfo')->where(array('scId' => $this->scId, 'classId' => $data['classId']))->max('serialNumber');
        $rs = M('user')->add(array( //添加学生
            'grade' => $data['grade'],
            'gradeId' => $data['gradeId'],
            'sex' => $data['sex'], //性别
            'name' => $data['name'], //姓名
            'politics' => $data['politics'],//政治面貌
            'number' => $data['number'],
            'scPrefix' => $data['prefix'],
            'scId' => $this->scId,
            'roleId' => $this::$studentRoleId,
            'password' => $this->create_password($password, self::$password_key, self::$password_key1),
            'InitialPassword' => $password,
            'account' => $data['prefix'] . $data['number'],
            'createTime' => time(),
            //班级
            'class' => $data['classId'],
            'className' => $data['className'],
            'serialNumber' => $sn,

        ));
        if (!$rs) {
            $response['msg'] = '添加出错';
            $this->ajaxReturn($response);
        }
        $number = (int)$data['number'] + 1;
        $prs = M('user')->add(array( //添加家长
            'grade' => $data['grade'],
            'name' => $data['name'] . '家长',
            'number' => $number,
            'scPrefix' => $data['prefix'],
            'scId' => $this->scId,
            'roleId' => $this::$jZroleId,
            'password' => $this->create_password($password, self::$password_key, self::$password_key1),
            'InitialPassword' => $password,
            'account' => $data['prefix'] . ($number),
            'childId' => $rs,
            'childName' => $data['name'],
            'gradeId' => $data['gradeId'],
            'createTime' => time(),
            //班级
            'class' => $data['classId'],
            'className' => $data['className']
        ));
        M('student_info')->add(array(
            'userId' => $rs,
            'name' => $data['name'],
            'scId' => $this->scId,
            'birthday' => strtotime($data['birthday']), //出生日期
            'phone' => $data['phone'], //电话
            'homePath' => $data['homePath'], //家庭住址
            'nation' => $data['nation'], //民族
            'nowHomePath' => $data['nowHomePath'], //现住地址
            'nowHomePostcode' => $data['nowHomePostcode'], //邮政编码
            'createTime' => time(),
            'sex' => $data['sex'],
            'grade' => $data['grade'],
            'gradeId' => $data['gradeId'],
            'isNew' => 1,
            //班级
            'serialNumber' => $sn,
            'classId' => $data['classId'],
            'className' => $data['className']
        ));
        M('cen_reg_info')->add(array(
            'userId' => $rs,
            //  'name' => $data['name'],
            'scId' => $this->scId,
            'perAddress' => $data['perAddress'], // 户口所在地
            'createTime' => time(),
        ));
        M('school_rollinfo')->add(array(
            'userId' => $rs,
            //'name' => $data['name'],
            'scId' => $this->scId,
            // 'exaCategory' => !empty($data['isSigRaw']) ? '签约生' : $data['exaCategory'], //考生类型
            //'regNumber' => $data['regNumber'], //准考证号
            'voluntPath' => $data['voluntPath'], //填报志愿所在地
            //'secSchool' => $data['secSchool'], //中学学校
            'midExam' => $data['midExam'], //中考分数
            'isTarget' => $data['isTarget'], //指标生出档
            'isTempStudy' => empty($data['isTempStudy']) ? 0 : $data['isTempStudy'],//是否借读
            'isSigRaw' => empty($data['isSigRaw']) ? 0 : $data['isSigRaw'], //是否签约生
            'promise' => $data['promise'], //签约承诺
            'createTime' => time(),
        ));

        M('parents_info')->add(array(
            'userId' => $rs,
            //'name' => $data['name'],
            'scId' => $this->scId,
            'createTime' => time(),
        ));

        M('other_info')->add(array(
            'userId' => $rs,
            //   'name' => $data['name'],
            'scId' => $this->scId,
            'createTime' => time(),
        ));
        M('tuition_info')->add(array(
            'userId' => $rs,
            // 'name' => $data['name'],
            'scId' => $this->scId,
            'createTime' => time(),
        ));
        if (!$rs) {
            $response['msg'] = '添加失败';
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['msg'] = '添加成功';
        $this->ajaxReturn($response);
    }

    //打印报表
    public function printReport($gradeId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $grade = D('Grade')->where(array('gradeid' => $gradeId))->find();
        $plan = $this->checkProcess($gradeId);
        $where = array(
            'planId' => $plan['planId'],
            'scId' => $this->scId,
            //  'isSync'=>1
        );
        $students = D('BranchStudentNew')->where($where)
            ->field('name,sex,classId,serialNumber')->select();

        if (!$students) {
            $this->ajaxReturn($response);
        }
        $class = D('Class c')
            ->join('mks_class_major cm ON cm.majorid=c.major', 'LEFT')
            ->join('mks_class_branch cb ON cb.branchid=c.branch', 'LEFT')
            ->join('mks_class_level cl ON cl.levelid=c.levelid', 'LEFT')
            ->where(array('c.grade' => $gradeId))
            ->field('c.classid as classId,c.classname as className,c.number,c.userid as userId,
            cb.branchid as branchId,cb.branch,cm.majorid as majorId,cm.majorname as major,
            cl.levelid as levelId,cl.levelname as level')
            ->select();

        $classMap = array();
        foreach ($class as $k => $v) {
            $classMap[$v['classId']] = $v;
        }

        $data = array();
        $not = array(
            'grade' => $grade['znName'],
            'className' => '未参与分班',
            'level' => '',
            'user' => '',
            'number' => 0,
            'total' => 0,
            'stu' => array(),
        );

        //班主任
        $userId = array_map(function ($v) {
            return $v['userId'];
        }, $class);

        $user = D('User')->where(array('id' => array('in', $userId)))->field('id,name')->select();
        $userMap = array();
        foreach ($user as $k => $v) {
            $userMap[$v['id']] = $v['name'];
        }
        foreach ($students as $k => $v) {
            if ($v['classId'] > 0) {
                if (!isset($data[$v['classId']])) {
                    $data[$v['classId']] = array(
                        'grade' => $grade['znName'],
                        'className' => $classMap[$v['classId']]['className'],
                        'level' => $classMap[$v['classId']]['level'],
                        'number' => $classMap[$v['classId']]['number'],
                        'user' => $userMap[$classMap[$v['classId']]['userId']],
                        'total' => 0,
                        'stu' => array(),
                    );
                }
                $data[$v['classId']]['stu'][] = $v;
                $data[$v['classId']]['total'] += 1;
            } else {
                $not['stu'][] = $v;
                $not['total'] += 1;
            }
        }
        sort($data);
        $response['not'] = $not;
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
    }

}
