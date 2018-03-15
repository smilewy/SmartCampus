<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/9/1
 * Time: 14:23
 */

namespace Home\Controller;

ob_end_clean();

//教师考评功能
class TeacherEvaluateController extends Base
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
        /*$this->roleId = 22;
        $scId = 6;
        $uid = 1;*/
        $this->scId = $scId;
        $this->uid = $uid;
        $this->user = D('User')->where(array('id' => $this->uid, 'scId' => $this->scId))->find();
    }

    /*************************************管理员操作*******************************************/
    //各流程变化 //已测试
    private function changeProcess(Array $process, $evaluateId)
    {
        $steps = implode(',', array_keys($process));

        $sql = "UPDATE mks_evaluate_process SET status = CASE step ";
        foreach ($process as $step => $status) {
            $sql .= sprintf("WHEN %d THEN %d ", $step, $status);
        }
        $sql .= "END WHERE step in ({$steps}) and scId={$this->scId} and evaluateId={$evaluateId}";

        M('')->execute($sql);
    }

    //创建考评方案    //已测试
    public function createEvaluate()
    {
        $response = array(
            'status' => 0,
            'msg' => ''
        );
        $type = $_POST['type'];
        if (!isset($type)) {
            $response['msg'] = '没有权限创建';
            $this->ajaxReturn($response);
        } else {
            if ($type == 'create') {
                $data = $_POST;
                $where=array(
                    'name' => $data['name'],
                    'scId' => $this->scId,
                    'creator' => $this->user['name'],
                    'creatorId' => $this->uid,
                );
                $find=D('EvaluateTeacher')->where($where)->find();
                if($find){
                    $response['msg']='此方案已存在';
                    $response['status']=2;
                    $this->ajaxReturn($response);

                }
                $evaluate = array(
                    'name' => $data['name'],
                    'startTime' => strtotime($data['startTime']),
                    'endTime' => strtotime($data['endTime']),
                    'creator' => $this->user['name'],
                    'creatorId' => $this->uid,
                    'publish' => 0,
                    'createTime' => time(),
                    'scId' => $this->scId,
                );
                // 保存方案
                $rsId = D('EvaluateTeacher')->data($evaluate)->add();
                if (!$rsId) {
                    $response['msg'] = '保存方案失败';
                    $this->ajaxReturn($response);
                }
                //存入流程设置
                $process = array(
                    array('name' => '修改考评方案', 'status' => -1, 'step' => 1, 'url' => '', 'evaluateId' => $rsId, 'scId' => $this->scId),
                    array('name' => '评委分组', 'status' => -1, 'step' => 2, 'url' => '', 'evaluateId' => $rsId, 'scId' => $this->scId),
                    array('name' => '设置考评分组', 'status' => -1, 'step' => 3, 'url' => '', 'evaluateId' => $rsId, 'scId' => $this->scId),
                    array('name' => '分配考评人员', 'status' => -1, 'step' => 4, 'url' => '', 'evaluateId' => $rsId, 'scId' => $this->scId),
                    array('name' => '评委打分', 'status' => 0, 'step' => 5, 'url' => '', 'evaluateId' => $rsId, 'scId' => $this->scId),
                    array('name' => '统计分析', 'status' => -1, 'step' => 6, 'url' => '', 'evaluateId' => $rsId, 'scId' => $this->scId),
                    array('name' => '比对名单统计', 'status' => 3, 'step' => 7, 'url' => '', 'evaluateId' => $rsId, 'scId' => $this->scId),
                    array('name' => '考评进度跟踪', 'status' => 3, 'step' => 8, 'url' => '', 'evaluateId' => $rsId, 'scId' => $this->scId)
                );
                $rs = D('EvaluateProcess')->addAll($process);

                if (!$rs) {
                    $response['msg'] = '保存流程失败';
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '创建成功';
                $response['status'] = 1;
                $process = array(
                    1 => 1, 2 => 2
                );
                $this->changeProcess($process, $rsId);
            } else {
                $response['msg'] = '没有权限创建';
            }
            $this->ajaxReturn($response);
        }
    }

    //得到所有考评    //已测试
    public function getAllEvaluate()
    {
        $response = array(
            'status' => 0,
            'data' => array());
        $where = array(
            'scId' => $this->scId
        );
        if ($this->roleId != $this::$adminRoleId) {
            $map = array(
                '_string' => "FIND_IN_SET ($this->uid,judgeId)",
                'scId' => $this->scId,
            );
            $ids = D('EvaluateJudge')->where($map)->field('evaluateId')->select();
            if (!$ids) {
                $this->ajaxReturn($response);
            }
            $id = array_map(function ($v) {
                return $v['evaluateId'];
            }, $ids);
            $where['id'] = array('in', $id);
        }
        $evaluate = D('EvaluateTeacher')
            ->where($where)
            ->field('id,name,startTime,endTime')
            ->select();
        if (!$evaluate) {
            $this->ajaxReturn($response);
        }
        foreach ($evaluate as &$v) {
            $v['startTime'] = date('Y-m-d H:i:s', $v['startTime']);
            $v['endTime'] = date('Y-m-d H:i:s', $v['endTime']);
        }
        $response['status'] = 1;
        $response['data'] = $evaluate;
        $this->ajaxReturn($response);
    }

    //考评管理  //已测试
    public function manageEvaluate($id)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where=array(
            'evaluateId'=>$id,
            'scId'=>$this->scId
        );
        $process = D('EvaluateProcess')
            ->where($where)
            ->field('id,name,status,url')
            ->select();
        if ($process) {
            $response['data'] = $process;
            $response['status'] = 1;
        }
        $this->ajaxReturn($response);
    }

    //修改考评方案    //已测试
    public function changeEvaluate($id)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];
        if (!isset($type)) {
            $evaluate = D('EvaluateTeacher')
                ->where(array('id'=>$id))
                ->field('id,name,startTime,endTime')
                ->find();
            if ($response) {
                $evaluate['startTime'] = date('Y-m-d H:i:s', $evaluate['startTime']);
                $evaluate['endTime'] = date('Y-m-d H:i:s', $evaluate['endTime']);
                $response['status'] = 1;
                $response['data'] = $evaluate;
            }
        } elseif ($type == 'save') {
            $data = $_POST;
            $evaluate = array(
                'name' => $data['name'],
                'startTime' => strtotime($data['startTime']),
                'endTime' => strtotime($data['endTime']),
            );
            $rs = D('EvaluateTeacher')->where(array('id'=>$id))->data($evaluate)->save();
            if ($rs) {
                $response['status'] = 1;
                $response['msg'] = '修改成功';
            }
        }
        $this->ajaxReturn($response);
    }

    //候选评委名单    //已测试
    public function judgeLists($id,$judgeId = '')
    {
        $response = array('status' => 0, 'data' => array());
        $map=array(
            'scId'=>$this->scId,
            'evaluateId'=>$id,
        );
        if(!empty($judgeId)){
            $map['id']=array('neq',$judgeId);
        }
        $ids=D('EvaluateJudge')->where($map)->field('judgeId')->select();
        $inId=array();
        foreach ($ids as $k=>$v){
            if(empty($v['judgeId'])){
                continue;
            }
            $inId=array_merge($inId,explode(',',$v['judgeId']));
        }
        //$post = array('教师', '职工');
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
            'state'=>1
        );
        if(!empty($inId)){
            $where['id']=array('not in',$inId);
        }

        $field = 'id,name,post,department';
        $res = array();
        $selected = array();
        $data = D('User')->where($where)->field($field)->select();
        if(!$data){
            $response['msg']='已无可选候选人';
            $this->ajaxReturn($response);
        }
        if ($data) {
            foreach ($data as $k => $v) {
                $department=empty($v['department'])?'无部门':$v['department'];
                if (!isset($res[$v['post']]))
                    $res[$v['post']] = array(
                        'name' => $v['post'],
                        'child' => array()
                    );
                if (!isset($res[$v['post']]['child'][$department]))
                    $res[$v['post']]['child'][$department] = array(
                        'name' => $department,
                        'child' => array()
                    );
                $res[$v['post']]['child'][$department]['child'][] = array(
                    'id' => $v['id'],
                    'name' => $v['name']
                );
            }
            foreach ($res as $k => $v) {
                sort($res[$k]['child']);
            }
            sort($res);
            $response['status'] = 1;
            $selected = D('EvaluateJudge')->where(array('id' => $judgeId))->getField('judgeId');
            $selected = empty($selected) ? array() : explode(',', $selected);
        }
        $response['selected'] = $selected;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //判断评委是否已在分组中
    public function judgeExist($id, $userId, $judgeId = '', $sort)
    {
        $response = array(
            'status' => 1,
            'msg' => '不存在'
        );
        $where = array(
            'evaluateId' => $id,
            'scId' => $this->scId
        );
        if ($sort == 'save') {
            $where['id'] = array('neq', $judgeId);
        }
        $where['_string'] = "FIND_IN_SET({$userId},judgeId)";
        $rs = D('EvaluateJudge')->where($where)->select();

        if ($rs) {
            if (!empty($rs)) {
                $response['status'] = 1;
                $response['msg'] = '存在';
            }
        }
        $this->ajaxReturn($response);
    }

    //评委分组  //已测试
    public function judgeEvaluate($id)
    {
        $response = array('status' => 0, 'data' => array());
        $type = $_POST['type'];
        if (!empty($type)) {
            $judge = array(
                'name' => $_POST['name'],
                'max' => (int)$_POST['max'],
                'min' => (int)$_POST['min'],
                'scId' => $this->scId,
                'judge' => empty($_POST['judge'])?'':json_encode($_POST['judge']),
                'evaluateId' => $id
            );
            $judges = array_map(function ($v) {
                return $v['id'];
            }, $_POST['judge']);
            $judge['judgeId'] = implode(',', $judges);
            if ($type == 'save') {
                $judeId =I('post.judgeId');
                $rs = D('EvaluateJudge')->where(array('id'=>$judeId))->data($judge)->save();
            } elseif ($type == 'add') {
                $rs = D('EvaluateJudge')->data($judge)->add();
                if ($rs) {
                    $process = array(
                        2 => 1, 3 => 2
                    );
                    $this->changeProcess($process, $id);
                }
            } elseif ($type == 'del') {
                $judeId =I('post.judgeId');
                $rs = D('EvaluateJudge')->where(array('id'=>$judeId))->delete();
            } else {
                $response['msg'] = '无权限';
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
            } else {
                $response['status'] = 1;
                $response['msg'] = '操作成功';
            }
            $this->ajaxReturn($response);
        }

        $judge = D('EvaluateJudge')->where(array('evaluateId'=>$id))->field('id,name,max,min,judge')->select();
        if ($judge) {
            foreach ($judge as $k => $v) {
                $judge[$k]['judge'] = empty($v['judge'])?array(): json_decode($v['judge'], true);
            }
            $response['data'] = $judge;
        }
        $response['status'] = 1;
        $this->ajaxReturn($response);
    }

    //得到评委分组   //已测试
    public function getJudgeGroup($id, $sort = 1)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'evaluateId' => $id,
            'scId' => $this->scId
        );
        $data = D('EvaluateJudge')->where($where)->field('id,name')->select();
        if ($sort == 2) {
            return $data;
        }
        if ($data) {
            $response['status'] = 1;
            $response['data'] = $data;
        }
        $this->ajaxReturn($data);
    }

    //得到考评分组   //已测试
    public function getGroup($id, $sort = 1)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'evaluateId' => $id,
            'scId' => $this->scId
        );
        $data = D('EvaluateGroup')->where($where)->field('id,name')->select();
        if ($sort == 2) {
            return $data;
        }
        if ($data) {
            $response['status'] = 1;
            $response['data'] = $data;
        }
        $this->ajaxReturn($data);
    }

    //设置考评分组    //已测试
    public function groupOfEvaluate($id)
    {
        $response = array('status' => 0, 'data' => array());
        $type = $_POST['type'];
        if (!empty($type)) {
            $rs = false;
            if ($type == 'save') {
                $groups = I('post.group');
                $val = '';
                $proId = array();
                $preId = D('EvaluateGroup')->where(array('evaluateId'=>$id))->field('id')->select();
                $preId = array_map(function ($v) {
                    return $v['id'];
                }, $preId);

                foreach ($groups as $k => $v) {
                    $proId[] = $v['id'];
                    $weigh = $v['judgeWeight'];
                    $ratio = array();
                    foreach ($weigh as $k1 => $v1) {
                        $ratio[$v1['id']] = ((int)$v1['value']) / 100;
                    }
                    $ratio = json_encode($ratio);
                    $val .= '(' . "'{$v['id']}'" . ',' . "'{$v['name']}'" . ',' . "'{$ratio}'" . ',' . "{$id}" . ','
                        . "{$this->scId}" . '),';
                }

                $diffId = array_diff($preId, $proId);
                if (!empty($diffId)) {
                    D('EvaluateGroup')->where(array('id' => array('in', $diffId)))->delete();
                }

                $val = rtrim($val, ',');
                $sql = "insert into mks_evaluate_group (id,name,judgeWeight,evaluateId,scId)
            values {$val} on duplicate key update name=values(name),judgeWeight=values(judgeWeight),evaluateId=values(evaluateId),
            scId=values(scId)";
                $rs = D('EvaluateGroup')->execute($sql);
                if ($rs) {

                    $process = array(
                        3 => 1, 4 => 2
                    );
                    $this->changeProcess($process, $id);
                }
            } elseif ($type == 'del') {
                /*   $groupId = $_POST['groupId'];
                   $rs = D('EvaluateGroup')->where("id={$groupId}")->delete();*/
            } else {
                $response['msg'] = '无权限';
            }
            if (!$rs) {
                $response['msg'] = '数据未进行更新,操作失败';
            } else {
                $response['status'] = 1;
                $response['msg'] = '操作成功';
            }
            $this->ajaxReturn($response);
        }

        $group = D('EvaluateGroup')->where(array('evaluateId'=>$id))->field('id,name,judgeWeight')->select();

        if ($group) {
            $temp = $this->getJudgeGroup($id, 2);
            $weigh = array();
            foreach ($temp as $k => $v) {
                $weigh[$v['id']] = array('name' => $v['name'], 'id' => $v['id']);
            }
            foreach ($group as $k => $v) {
                $judgeWeight = json_decode($v['judgeWeight'], true);
                $group[$k]['judgeWeight'] = array();
                foreach ($weigh as $key => $val) {
                    $foo = array(
                        'id' => $weigh[$key]['id'],
                        'name' => $weigh[$key]['name'],
                    );
                    if (key_exists($key, $judgeWeight)) {
                        $foo['value'] = $judgeWeight[$key];
                    } else {
                        $foo['value'] = null;
                    }
                    array_push($group[$k]['judgeWeight'], $foo);
                }
            }
            $response['data'] = $group;
            $response['status'] = 1;
        }
        $this->ajaxReturn($response);
    }

    //(分配考评人员)待选被评人员   //已测试
    public function personnelLists($id, $groupId = '')
    {
        $response = array('status' => 1);

        $map=array(
            'scId'=>$this->scId,
            'evaluateId'=>$id,
        );
        $ids=D('EvaluateJudge')->where($map)->field('judgeId')->select();
        $inId=array();
        foreach ($ids as $k=>$v){
            if(empty($v['judgeId'])){
                continue;
            }
            $inId=array_merge($inId,explode(',',$v['judgeId']));
        }

        if(!empty($groupId)){
            $map['id']=array('neq',$groupId);
        }

        $ids=D('EvaluateGroup')->where($map)->field('personnelId')->select();
        foreach ($ids as $k=>$v){
            if(empty($v['personnelId'])){
                continue;
            }
            $inId=array_merge($inId,explode(',',$v['judgeId']));
        }
        $roleWhere=array(
            'scId'=>array(array('eq',$this->scId),array('eq',0),'or'),
            'roleId'=>array('not in',array(13,15,22)));
        $roleId=D('Role')->where($roleWhere)->field('roleId')->select();
        $roleId=array_map(function($v){
            return $v['roleId'];
        },$roleId);
        //$post = array('教师', '职工');
        $where = array(
            'scId' => $this->scId,
           // 'post' => array('in', $post),
            'roleId'=>array('in',$roleId),
            'state' => 1
        );
        if(!empty($inId)){
            $where['id']=array('not in',$inId);
        }
        $field = 'id,name,post,department';
        $res = array();
        $selected = array();
        $data = D('User')->where($where)->field($field)->select();
        if(!$data){
            $response['msg']='已无可选候选人';
            $this->ajaxReturn($response);
        }

        if ($data) {
            foreach ($data as $k => $v) {
                $department=empty($v['department'])?'无部门':$v['department'];
                if (!isset($res[$v['post']]))
                    $res[$v['post']] = array(
                        'name' => $v['post'],
                        'child' => array()
                    );
                if (!isset($res[$v['post']]['child'][$department]))
                    $res[$v['post']]['child'][$department] = array(
                        'name' => $department,
                        'child' => array()
                    );
                $res[$v['post']]['child'][$department]['child'][] = array(
                    'id' => $v['id'],
                    'name' => $v['name']
                );
            }
            foreach ($res as $k => $v) {
                sort($res[$k]['child']);
            }
            sort($res);
            $response['status'] = 1;
            $selected = D('EvaluateGroup')->where(array('id' => $groupId))->getField('personnelId');
            $selected = empty($selected) ? array() : explode(',', $selected);

        }
        $response['selected'] = $selected;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //(分配考评人员)已添加被评人员名单    //已测试
    public function addPersonnel($groupId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $data = D('EvaluateGroup')
            ->where(array('id'=>$groupId))->field('personnel')
            ->find();
        if(!$data){
            $this->ajaxReturn($response);
        }
        if ($data) {
            $res = json_decode($data['personnel'], true);
            //$response['status'] = 1;
            $response['data'] = $res;
        }
        $response['status']=1;
        $this->ajaxReturn($response);
    }

    //(分配考评人员)待选评委列表  //已测试
    public function judgesLists($id, $groupId)
    {
        $response = array('status' => 1, 'data' => array());
        $res = array();
        $selected = array();
        $data = D('EvaluateJudge')
            ->where(array('evaluateId' => $id, 'scId' => $this->scId))
            ->field('name,judge')
            ->select();
        if ($data) {
            foreach ($data as &$v) {
                $res[] = array(
                    'name' => $v['name'],
                    'judge' => json_decode($v['judge'], true)
                );
            }
            $selected = D('EvaluateGroup')->where(array('id' => $groupId))->getField('judgeId');
            $selected = empty($selected) ? array() : explode(',', $selected);
            $response['status'] = 1;
        }
        $response['selected'] = $selected;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //(分配考评人员)已添加评委名单
    public function addJudge($groupId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        $data = D('EvaluateGroup')
            ->where(array('id'=>$groupId))->field('judge')
            ->find();
        if(!$data){
            $this->ajaxReturn($response);
        }
        if ($data) {
            $res = empty($data['judge']) ? array() : json_decode($data['judge'], true);
            // $response['status'] = 1;
            $response['data'] = $res;
        }
        $response['status']=1;
        $this->ajaxReturn($response);
    }

    //(分配考评人员)保存
    public function save($id)
    {
        $response = array(
            'status' => 0,
        );
        $type = $_POST['type'];
        if (!empty($type)) {
            if ($type == 'save') {
                $groupId = $_POST['groupId'];
                $data['personnel'] = json_encode($_POST['personnel']);
                $personnelId = array_map(function ($v) {
                    return $v['id'];
                }, $_POST['personnel']);
                $data['personnelId'] = implode(',', $personnelId);
                $data['judge'] = json_encode($_POST['judge']);
                $judgeId = array_map(function ($v) {
                    return $v['id'];
                }, $_POST['judge']);
                $data['judgeId'] = implode(',', $judgeId);
                $rs = D('EvaluateGroup')->where(array('id'=>$groupId))->data($data)->save();
                if ($rs) {
                    $process = array(
                        4 => 1,6=>3
                    );
                    $this->changeProcess($process, $id);
                }
                if (!$rs) {
                    $response['msg'] = '操作失败';
                } else {
                    $response['msg'] = '操作成功';
                    $response['status'] = 1;
                }
            } else {
                $response['msg'] = '无权限';
            }
            $this->ajaxReturn($response);
        }
    }

    //比对名单统计
    public function comparisonLists($id)
    {
        //被考评的所有人
        $data = D('EvaluateGroup')
            ->where(array('evaluateId' => $id, 'scId' => $this->scId))->field('judge,personnel')->select();
        $per = array();
        $judge = array();
        foreach ($data as &$v) {
            $per = array_merge($per, json_decode($v['personnel'], true));
            $judge = array_merge($judge, json_decode($v['judge'], true));
        }
        $perIds = array_map(function ($v) {
            return $v['id'];
        }, $per);
        $field = 'id,name';
        //$post = array('老师', '职工');
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
            'id' => array('not in', $perIds),
            'state' => 1
        );
        $notPer = D('User')->where($where)->field($field)->select();
        $response['status'] = 1;
        $response['notPer'] = $notPer;
        $response['per'] = $per;
        $response['judge'] = $judge;
        $this->ajaxReturn($response);
    }

    //考评进度跟踪
    public function trackProgress($id)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $data = D('EvaluateGroup')
            ->where(array('evaluateId' => $id, 'scId' => $this->scId))
            ->field('judgedId,judge')->select();
        if ($data) {
            $yetId = array();
            $not = array();
            $yet = array();
            foreach ($data as &$v) {
                $judge = json_decode($v['judge'], true);
                $temp = json_decode($v['judgedId'], true);
                foreach ($temp as $key => $val) {
                    foreach ($val as &$i) {
                        $yetId[] = $i;
                    }
                }
                foreach ($judge as $k1 => $v1) {
                    if (in_array($v1['id'], $yetId))
                        $yet[] = $v1;
                    else
                        $not[] = $v1;
                }
            }
            $res = array(
                array('name' => '已考评评委', 'lists' => $yet, 'number' => count($yet)),
                array('name' => '未考评评委', 'lists' => $not, 'number' => count($not))
            );
            $response['status'] = 1;
            $response['data'] = $res;
        }
        $this->ajaxReturn($response);
    }

    //统计分析
    public function statisticsEva($id, $groupId)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $map = array(
            'id' => $groupId,
            'scId' => $this->scId
        );

        $personnel = D('EvaluateGroup')->where($map)
            ->field('id,name,judge,judgeId,judgeWeight,personnel,judgeScore,totalScore,rank')->find();
        if (!$personnel) {
            $this->ajaxReturn($response);
        }
        $weight = json_decode($personnel['judgeWeight'], true);
        $where = array(
            //'_string'=>"FIND_IN_SET({$this->uid},judgeId)",
            'evaluateId' => $id,
            'scId' => $this->scId
        );
        $info = D('evaluateJudge')->where($where)->field('id,name,max,min,judgeId')->select();
        $judge = array();
        $judgeGroup = array();
        $j_id=array();
        foreach ($info as $k => $v) {
            $judge[$v['id']] = array(
                'max' => (int)$v['max'],
                'min' => (int)$v['min'],
                'weight' => $weight[$v['id']]
            );
            $judgeGroup[] = array(
                'name'=>$v['name'],
                'id'=>'j'.$v['id']
            );
            $j_id[]=$v['id'];
        }
        $judgeGroup[]=array(
            'name'=>'总分',
            'id'=>'total'
        );
        $person = json_decode($personnel['personnel'], true);
        $judgeScore=json_decode($personnel['judgeScore'], true);
        //$totalScore=json_decode($personnel['totalScore'], true);
        $rank = json_decode($personnel['rank'], true);
        $showPer = array();

        if ($this->roleId != $this::$adminRoleId) {
            foreach ($person as $key => $val) {
                if ($this->uid == $val['id'])
                    $showPer =array($person[$key]) ;
            }
        } else {
            $showPer =$person ;
        }

        foreach ($showPer as $index => $per) {

            $perId=$per['id'];
            $temp_score=array();
            $showPer[$index]['score']=array(
                0=>'',
                1=>'',
                2=>'',
                3=>'',
            );
            foreach ($judgeGroup as $k=>$v){
                $showPer[$index]['score'][$v['id']]='';
            }

            $showPer[$index]['score']['total']='';
            foreach ($rank as $k=>$v){
                if(!in_array($k,$j_id)){
                    continue;
                }
                $rank_judge=$v[$perId];
                //去掉最高分和最低分
                if($judge[$k]['min']>0)
                array_splice($rank_judge, 0, $judge[$k]['min']);//最低
                if($judge[$k]['max']>0)
                array_splice($rank_judge, -$judge[$k]['max']); //最高
                if(empty($rank_judge)){
                    continue;
                }
                foreach ($rank_judge as $k1=>$v1){
                    $per_score=$judgeScore[$v1][$perId];
                    foreach ($per_score as $key=>$val){
                        $temp_score[$k][$key]+=(int)$val;
                        $temp_score[$k]['total']+=(int)$val;
                    }
                    $temp_score[$k]['number']++;
                }
            }

            foreach ($temp_score as $k=>$v){
                $total=$v['total'];
                $number=$v['number'];
                unset($temp_score[$k]['total']);
                unset($temp_score[$k]['number']);
                foreach ($temp_score[$k] as $key=>$val){
                    $showPer[$index]['score'][$key]+=round(($val/$number)*$judge[$k]['weight']);
                }
                $showPer[$index]['score']['j'.$k]+=round(($total/$number)*$judge[$k]['weight']);
                $showPer[$index]['score']['total']+=round(($total/$number)*$judge[$k]['weight']);
            }
           // $showPer[$index]['score'] = $score[$per['id']];
    }

        $page = empty($_POST['page']) ? 0 : (int)$_POST['page']-1;
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $total=count($showPer);
        $response['status'] = 1;
        $response['total']=$total;
        $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        $response['data'] =array_splice($showPer,$page,$count) ;
        $response['group'] = $judgeGroup;
        $this->ajaxReturn($response);
    }

    //考评记录
    public function evaluateLog()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];
        if (isset($type)) {
            $rs = false;
            $id = I('post.logId');
            if ($type == 'publish') {
                $status = $_POST['status'];
                $rs = D('EvaluateTeacher')->where(array('id' => $id))->data(array('publish' => $status))->save();
            } elseif ($type == 'del') {
                $map = array(
                    'evaluateId' => $id,
                    'scId' => $this->scId
                );
                $rs = D('EvaluateTeacher')->where(array('id' => $id))->delete();
                D('EvaluateProcess')->where($map)->delete();
                D('EvaluateJudge')->where($map)->delete();
                D('EvaluateGroup')->where($map)->delete();
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            if (!$rs) {
                $response['msg'] = '操作失败';
            } else {
                $response['msg'] = '操作成功';
                $response['status'] = 1;
            }
            $this->ajaxReturn($response);
        }
        $where = array(
            'scId' => $this->scId
        );
        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $data = D('EvaluateTeacher')
            ->where($where)
            ->field('id,name,startTime,endTime,publish,createTime')->limit($limit_page)
                ->select();

        if ($data) {
            $map = array();
            $bar = D('EvaluateGroup')->where($where)->field('judgeId,judgedId,evaluateId')->select();
            foreach ($bar as $k => $v) {
                if (!isset($map[$v['evaluateId']])) {
                    $map[$v['evaluateId']] = array(
                        'all' => array(),
                        'al' => array()
                    );
                }
                $judgeId = empty($v['judgeId']) ? array() : explode(',', $v['judgeId']);
                $judgedId = empty($v['judgedId']) ? array() : json_decode($v['judgedId'], true);
                $map[$v['evaluateId']]['all'] = array_merge($map[$v['evaluateId']]['all'], $judgeId);
                foreach ($judgedId as $k1 => $v1) {
                    $map[$v['evaluateId']]['al'] = array_merge($map[$v['evaluateId']]['al'], $v1);
                }
            }

            foreach ($data as &$v) {
                if (!isset($map[$v['id']])) {
                    $v['rate'] = 0;
                } else {
                    $all = count($map[$v['id']]['all']);
                    $al = count($map[$v['id']]['al']);
                    $v['rate'] = sprintf("%.2f", $al / $all) * 100;
                }

                $v['startTime'] = date('Y-m-d H:i:s', $v['startTime']);
                $v['endTime'] = date('Y-m-d H:i:s', $v['endTime']);
                $v['createTime'] = date('Y-m-d H:i:s', $v['createTime']);
            }
            $total=(int)D('EvaluateTeacher')
                ->where($where)
                ->count();
            $response['total']=$total;
            $response['maxPage']=ceil($total / $count) <1 ? 1 : ceil($total / $count);
            $response['data'] = $data;
            $response['status'] = 1;
        }
        $this->ajaxReturn($response);
    }

    /*************************************评委操作*******************************************/
    public function getBelong($sort = '')
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $uId = $this->uid;
        if ($sort == 2) {
            $map['g.scId'] = $this->scId;
            if ($this->roleId != $this::$adminRoleId) {
                $map['_string'] = "FIND_IN_SET({$uId},g.personnelId)";
                $map['t.publish']=1;
            }
        } else {
            $map = array(
                '_string' => "FIND_IN_SET({$uId},g.judgeId)",
                'g.scId' => $this->scId,
                //'t.publish'=>1
            );
        }

        $eva = D('evaluateGroup g')
            ->join('mks_evaluate_teacher t ON g.evaluateId=t.id', 'LEFT')
            ->where($map)
            ->field('g.id as groupId,g.name as groupName,t.id as id,t.name as plan,t.startTime,t.endTime')->select();

        if (!$eva) {
            $this->ajaxReturn($response);
        }
        $res = array();
        foreach ($eva as $k => $v) {
            if (!isset($res[$v['id']])) {
                $res[$v['id']] = array(
                    'id' => $v['id'],
                    'name' => $v['plan'],
                    'group' => array()
                );
            }
            $res[$v['id']]['group'][] = array(
                'id' => $v['groupId'],
                'name' => $v['groupName'],
                'startTime' => date('Y-m-d H:i:s', $v['startTime']),
                'endTime' => date('Y-m-d H:i:s', $v['endTime'])
            );
        }
        sort($res);
        $response['status'] = 1;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    //评委打分     //已测试
    public function judgeMark($id, $groupId)
    {

        $response = array(
            'status' => 0,
            'data' => array()
        );
        $uId = $this->uid;

        $plan = D('evaluateTeacher')->where(array('id' => $id))->find();
        if ($plan['startTime'] > time() || $plan['endTime'] < time()) {
            $response['msg'] = '不在考评时间之内';
            $this->ajaxReturn($response);
        }
        //找到评委组
        $type = $_POST['type'];
        if (isset($type)) {         //pass
            if ($type == 'submit') {
                //得到已经评过的分数
                $gId = $groupId;
                $personnel = D('evaluateGroup')->where(array('id'=>$gId))
                    ->field('id,name,personnel,personnelId,judgeScore,totalScore,rank,judgedId')->find();
                $map = array(
                    '_string' => "FIND_IN_SET({$uId},judgeId)",
                    'evaluateId' => $id,
                    'scId' => $this->scId
                );
                $judge = D('evaluateJudge')->where($map)->field('id,name,max,min,judgeId')->find();
                $judgeScore = json_decode($personnel['judgeScore'], true);
                $totalScore = json_decode($personnel['totalScore'], true);
                $rank = json_decode($personnel['rank'], true);
                $judgedId = json_decode($personnel['judgedId'], true);
                //得到当前评委所在的评委组的权重
                $judgeId = $judge['id'];
                $new_score = $_POST['score'];
                foreach ($new_score as $pId => $s) {
                    $total = array_pop($s);
                    $judgeScore[$uId][$pId] = $s;
                    /*
                         $temp[$k] = round(($temp[$k] * $foo + $v) / ($foo + 1), 0) * $weight;
                     }*/
                    $totalScore[$judgeId][$pId][$uId] = $total;
                }

                $judgedId[$judgeId][] = (int)$uId;
                foreach ($totalScore[$judgeId] as $pid => $jud) {
                    $temp_rank = array();
                    foreach ($jud as $key => $val) {
                        $temp_rank[$key] = $val;
                    }
                    asort($temp_rank);
                    $rank[$judgeId][$pid] = array_keys($temp_rank);
                }

                $data = array(
                    'judgedId' => json_encode($judgedId),
                    'judgeScore' => json_encode($judgeScore),
                    'rank' => json_encode($rank),
                    'totalScore' => json_encode($totalScore),
                );
                $rs = D('EvaluateGroup')->where(array('id'=>$gId))->data($data)->save();
                if ($rs) {
                    $response['msg'] = '提交成功';
                    $response['status'] = 1;
                } else {
                    $response['msg'] = '提交失败';
                }
            } else {
                $response['msg'] = '没权限';
            }
            $this->ajaxReturn($response);
        }

        $personnel = D('evaluateGroup')->where(array('id' => $groupId))
            ->field('id,name,personnel,personnelId,judgeScore,judgedId')->find();
        if (!$personnel) {
            $response['msg'] = '你不属于此分组的评委';
            $this->ajaxReturn($response);
        }

        $judgeScore = empty($personnel['judgeScore']) ? array() : json_decode($personnel['judgeScore'], true);
// $totalScore = empty($personnel['totalScore'])?array():json_decode($personnel['totalScore'], true);
        $person = empty($personnel['personnel']) ? array() : json_decode($personnel['personnel'], true);
        if (!array_key_exists($uId, $judgeScore)) {
            foreach ($person as $key => $val) {
                $person[$key]['score'] = array('', '', '', '', '');
            }
            $submit = 0;
        } else {
            foreach ($person as $key => $v1) {
                $temp_id = $person[$key]['id'];
                $person[$key]['score'] = $judgeScore[$uId][$temp_id];
                $total = 0;
                foreach ($judgeScore[$uId][$temp_id] as $k => $v) {
                    $total += (int)$v;
                }
                $person[$key]['score'][] = $total;
            }
            $submit = 1;
        }
        $data = $person;
        $response['submit'] = $submit;
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
    }

}