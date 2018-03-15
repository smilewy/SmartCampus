<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/8/3
 * Time: 10:11
 */

namespace Home\Controller;

use Think\Model;
use Home\Common\Accessory;

ob_end_clean();

//权限已做
class NotificationController extends Base
{
    public $scId;
    public $uid;
    public $user;
    public $roleId;

    public function __construct()
    {
        parent::__construct();
        $this->scId = $_SESSION['loginCheck']['data']['scId'];
        $this->uid = $_SESSION['loginCheck']['data']['userId'];
        $this->roleId = $_SESSION['loginCheck']['data']['roleId'];

        /*$this->scId = 2;
        $this->uid = 1;*/
        $this->user = D('User')->where(array('id' => $this->uid, 'scId' => $this->scId))->find();
    }

    /*将通知发给用户*/ //已测试
    public function push($nid, $uids, $draft, $scId)
    {
        if (!$draft) {
            M('Notification')->where(array('id' => $nid))->save(array('draft' => 0, 'lastRecordTime' => time()));
            $model = M('NotificationUser');
            $data = array(
                'nid' => $nid,
                'uid' => implode(',', $uids),
                'uncheckId' => implode(',', $uids),
                'uname' => '',
                'scId' => $scId
            );
            $rs = $model->add($data);
            return $rs;
        }
        return -1;
    }

    /*查阅通知*/
    public function check($nid)
    {
        $model = M('NotificationUser');
        $model->startTrans();
        $uncheckIds = $model->where(array('nid' => $nid,'scId'=>$this->scId))->field('uncheckId')->find();

        $uncheckIds = explode(',', $uncheckIds['uncheckId']);
        $uncheckIds = array_merge(array_diff($uncheckIds, array($this->uid)));
        $data = array(
            'uncheckId' => implode(",", $uncheckIds),
        );
        $rs = $model->where(array('nid' => $nid,'scId'=>$this->scId))->data($data)->save();
        if (!$rs)
            return false;
        $model->commit();
        return true;
    }

    private function tree($pId, $index, $data)
    {
        $res = array();
        $tree = $index;
        foreach ($data as $k => $v) {
            $parId = $pId;
            $id = $tree > 0 ? $parId . ((int)$k + 1) : ((int)$k + 1);
            $res[] = array(
                'pId' => $parId,
                'id' => $id,
                'name' => $v['name'],
                'userId' => (int)$v['userId'],
                // 'open'=>true
            );
            //$in=((int)$k+1);
            if (isset($v['child'])) {
                $tree++;
                $parId = $id;
                $temp = $this->tree($parId, $tree, $v['child']);
                $res = array_merge($res, $temp);
            }
        }
        return $res;
    }

    /*新建通知的选择人数*/
    public function getUser($which)
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        if ($which == 1) {
            $map = array(
                'scId' => array(array('eq', $this->scId), array('eq', 0), 'or'),
                'roleId'=>array('not in',array($this::$adminRoleId))
            );
            $roleId = D('role')->where($map)->field('roleId')->select();
            $roleId = array_map(function ($v) {
                return $v['roleId'];
            }, $roleId);
            $where = array(
                'r.roleId' => array('in',$roleId),
                'r.scId' => array(array('eq', $this->scId), array('eq', 0), 'or'),
                //'u.class' => array('EXP', 'is not null'),
                'u.scId'=>$this->scId,
                'u.state'=>1,
                'u.isAtSchool'=>'是'
            );
            $user = D('User u')
                ->join('mks_role r ON u.roleId=r.roleId', 'LEFT')
                ->where($where)
                ->field('u.id,u.name,u.grade,u.gradeId,u.class,u.className,u.department,r.roleName,r.roleNameEn,r.roleId')
                ->select();
            $jzxsRoleId=array($this::$jZroleId,$this::$studentRoleId);
            foreach ($user as $k=>$v){
                if(empty($v['class'])&&in_array($v['roleId'],$jzxsRoleId)){
                    unset($user[$k]);
                }
            }

            $grade = D('Grade')->where(array('scId' => $this->scId))->
            field('gradeid,znName')->select();
            $gradeMap = array();
            foreach ($grade as $k => $v) {
                $gradeMap[$v['gradeid']] = $v['znName'];
            }
            if (!$user) {
                $this->ajaxReturn($response);
            }

            $users = array();
            foreach ($user as $k => $v) {
                if (!isset($users[$v['roleId']]))
                    $users[$v['roleId']] = array(
                        'identify' => 'role',
                        'name' => $v['roleName'],
                        'child' => array()
                    );
                if (in_array($v['roleId'],$jzxsRoleId)) {
                    if (!isset($users[$v['roleId']]['child'][$v['gradeId']]))
                        $users[$v['roleId']]['child'][$v['gradeId']] = array(
                            'identify' => 'grade',
                            'sort'=>$v['grade'],
                            'name' => $gradeMap[$v['gradeId']],
                            'child' => array()
                        );
                    if (!isset($users[$v['roleId']]['child'][$v['gradeId']]
                        ['child'][$v['class']]
                    )
                    )
                        $users[$v['roleId']]['child'][$v['gradeId']]
                        ['child'][$v['class']] = array(
                            'identify' => 'class',
                            'name' => $v['className'],
                            'child' => array()
                        );
                    $users[$v['roleId']]['child'][$v['gradeId']]
                    ['child'][$v['class']]['child'][] = array(
                        'userId' => $v['id'],
                        'identify' => 'user',
                        'name' => $v['name']
                    );
                } else {
                    $department = empty($v['department']) ? '其他职工' : $v['department'];
                    if (!isset($users[$v['roleId']]['child'][$department]))
                        $users[$v['roleId']]['child'][$department] = array(
                            'identify' => 'grade',
                            'name' => $department,
                            'child' => array()
                        );
                    $users[$v['roleId']]['child'][$department]
                    ['child'][] = array(
                        'userId' => $v['id'],
                        'identify' => 'user',
                        'name' => $v['name']
                    );
                }

            }
            foreach ($users as $k => $v) {
                foreach ($v['child'] as $k1 => $v1) {
                    sort($users[$k]['child'][$k1]['child']);
                }
                usort($users[$k]['child'],function($a,$b){
                    return $a['sort']<$b['sort']?-1:1;
                });
                //sort($users[$k]['child']);
            }
            sort($users);
            $pId = 0;
            $tree = 0;
            /*foreach ($users as $k=>$v){
                $rs[]=array(
                    'pId'=>$pId,
                    'id'=>$index,
                    'name'=>$v['name']
                );

                foreach ($v['child'] as $k1=>$v1){
                    $rs[]=array(
                        'pId'=>$index,
                        'id'=>$index.((int)$k1+1),
                        'name'=>$v1['name']
                    );
                    foreach ($v1['child'] as $k2=>$v2){

                        $rs[]=array(
                            'pId'=>$index.((int)$k1+1),
                            'id'=>$index.((int)$k1+1).((int)$k2+1),
                            'name'=>$v2['name'],
                            'userId'=>$v2['id']
                        );

                    }
                }
                $index++;
            }*/

            $rs = $this->tree($pId, $tree, $users);
            $response['status'] = 1;
            $response['data'] = $rs;
        } else {

        }
        $this->ajaxReturn($response);
    }
    /*新建通知*/ //已测试
    public function create()
    {
        //通知存入
        $response = array(
            'msg' => '',
            'status' => 0
        );
        $type = $_POST['type'];
        if (!$type) {
            $response['msg'] = '无权限';
            $this->ajaxReturn($response);
        }
        if ($type == 'create') {
            $data = $_POST;
            if ($data) {
                $find=D('Notification')->where(array(
                    'scId'=>$this->scId,
                    'title' => $data['title'],
                    'kind' => $data['kind'],
                    'content' => $data['content']
                    ))->find();
                if($find){
                    $response['msg']='此通知已经存在';
                    $this->ajaxReturn($response);
                }
                $notification = array(
                    'title' => $data['title'],
                    'kind' => $data['kind'],
                    'content' => $data['content'],
                    'creatorId' => $this->uid,
                    'creator' => $this->user['name'],
                    'department' => $this->user['department'],
                    'createTime' => time(),
                    'draft' => 0,
                    'scId' => $this->scId,
                );
                //对上传附件进行处理 测试通过
                $file = $_FILES;
                $accessory = array();
                if ($file) {
                    $aName = array();
                    foreach ($file as &$v) {
                        foreach ($v['name'] as &$val) {
                            $aName[] = $val;
                        }
                    }

                    $notification['accessoryName'] = implode(',', $aName);

                    $subName = 'notification';
                    $upload = new Accessory($file, $this->scId, $subName);
                    $accessory = $upload->upload();
                    //上传失败
                    if (!$accessory['status']) {
                        $response = array(
                            'msg' => $accessory['msg'],
                            'status' => 0
                        );
                        $this->ajaxReturn($response);
                    }
                }
                //上传成功处理
                $notification['accessory'] = implode(',', $accessory['path']);

                if ($data['draft'] == 1) {
                    //是否为草稿
                    $notification['draft'] = 1;
                }

                $rs = M('Notification')->data($notification)->add();
                if (!$rs) {
                    $response['msg'] = '保存数据失败';
                    $this->ajaxReturn($response);
                }
                //将通知发给用户
                /*$adminUserId=D('User')->where(array('scId'=>$this->scId,'roleId'=>$this::$adminRoleId))->field('id')->select();
                $aUserId=array_map(function($v){
                    return $v['id'];
                },$adminUserId);
                $data['uids']=array_merge($data['uids'], $aUserId);*/
                $rs = self::push($rs, $data['uids'], $data['draft'], $this->scId);
                if (!$rs) {
                    $response['msg'] = '发送通知给用户失败';
                    $this->ajaxReturn($response);
                }
                $response['msg'] = '操作成功';
                $response['status'] = 1;
            }
        }

        $this->ajaxReturn($response);
    }

    /*查看通知*/ //已测试
    public function lists()
    {
        $response = array(
            'status' => 0,
            'data' => ''
        );

        $type = $_REQUEST['type'];
        $ids = $_POST['ids'];
        if(isset($type)){
            if ($type == 'del') {
                if ($this->roleId != $this::$adminRoleId) {
                    $response['msg'] = '没有权限删除';
                    $this->ajaxReturn($response);
                }
                $res = self::del($ids);
                if (!$res) {
                    $response['msg'] = '删除失败';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                $response['msg'] = '删除成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'check') {
                $nid = $_POST['nid'];
                $rs = $this->check($nid);
                if (!$rs)
                    $this->ajaxReturn($response);
                $response['status'] = 1;
                $response['msg'] = '成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'download') {
                $nid = $_REQUEST['nid'];
                $tempfilename = M('Notification')->where(array('id' => $nid))->field('accessory,accessoryName')->find();
                if ($tempfilename) {
                    $a = new Accessory('', $this->scId, 'notification');
                    $filename =empty($tempfilename['accessory'])?array():explode(',', $tempfilename['accessory']);
                    $aName =empty($tempfilename['accessoryName'])?array():explode(',', $tempfilename['accessoryName']);
                    $a->download($filename, $aName);
                    die;
                } else {
                    $response['msg'] = '无文件';
                    $this->ajaxReturn($response);
                }
            }
        }


        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        $orderField = $_POST['order'];
        $order = '';
        if (empty($orderField)) {
            $order = 'createTime DESC';
        } else {
            foreach ($orderField as $k => $v) {
                if (!empty($v)) {
                    $order .= $k . ' ' . $v . ',';
                }
            }
            if (!empty($order))
                $order = rtrim($order, ',');
            else
                $order = 'createTime DESC';
        }
        $where1 = array(
            'scId'=>$this->scId
        );

        $uid = $this->uid;
        $where1['_string'] = "FIND_IN_SET($uid,uid)";
        $nid = M('NotificationUser')->where($where1)->field('nid,uncheckId')->select();

        if (!$nid) {
            $this->ajaxReturn($response);
        }
        $map = array();
        foreach ($nid as $k => $v) {
            $map[$v['nid']] = $v['uncheckId'];
            $nids[] = $v['nid'];
        }
        $where['id'] = array('in', $nids);

        $key = $_POST['key'];
        if (!empty($key))
            $where['title|creator|department'] = array('like', "%{$key}%");
        $notice = D('Notification')->where($where)->order($order)->limit($limit_page)->select();
        $total = (int)D('Notification')->where($where)->count();
        if (!$notice) {
            $this->ajaxReturn($response);
        }

        foreach ($notice as $k => $v) {
            $notice[$k]['accessory'] = empty($v['accessory'])?array():explode(',', $v['accessory']);
            $notice[$k]['accessoryName'] = empty($v['accessoryName'])?array():explode(',', $v['accessoryName']);
            $uncheckId = empty($map[$v['id']])?array():explode(',', $map[$v['id']]);
            if (in_array($this->uid, $uncheckId)) {
                $notice[$k]['status'] = '未查阅';
            } else {
                $notice[$k]['status'] = '已查阅';
            }
            foreach ($notice[$k]['accessory'] as $k1=>$v1){
                $notice[$k]['accessory'][$k1]=array(
                    'name'=>$v1,
                    'aName'=>$notice[$k]['accessoryName'][$k1]
                );
            }
        }


        $response['total'] = $total;
        $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
        $response['status'] = 1;
        $response['data'] = $notice;
        $this->ajaxReturn($response);
    }

    /*发布记录*/
    public function log()
    {
        $response = array(
            'status' => -1,
            'data' => array()
        );
        $ids = $_POST['ids'];
        $type = $_POST['type'];
        if (isset($type)) {
            if ($type == 'del') {
                $result = self::del($ids);
                if (!$result) {
                    $response['msg'] = '删除失败';
                    $this->ajaxReturn($response);
                }
                $response['status'] = 1;
                $response['msg'] = '删除成功';
                $this->ajaxReturn($response);
            } elseif ($type == 'list') {
                $id = $_POST['id'];
                $tea = array();
                $stu = array();
                $where = array(
                    'nid' => $id,
                    'scId' => $this->scId
                );
                /*$page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
                $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
                $pre_page = ($page - 1) * $count;
                $limit_page = "$pre_page,$count";*/
                $data = D('NotificationUser')->where($where)->field('uncheckId')->find();

                $par = array();
                $uncheck = $data['uncheckId'];
                if (!$data) {
                    $response['status'] = 1;
                    $response['stu'] = $stu;
                    $this->ajaxReturn($response);
                }

                $grade = D('Grade')->where(array('scId' => $this->scId))->field('id,znName')->select();
                $gradeMap = array();
                foreach ($grade as $k => $v) {
                    $gradeMap[$v['id']] = $v['znName'];
                }
              /*  $adminUserId=D('User')->where(array('scId'=>$this->scId,'roleId'=>$this::$adminRoleId))->field('id')->select();
                $aUserId=array_map(function($v){
                    return $v['id'];
                },$adminUserId);*/

                $uncheck=empty($uncheck)?array():explode(',',$uncheck);
              //  $unId=array_diff($uncheck,$aUserId);
                $unId=$uncheck;

                $roleId = $_POST['roleId'];
                $where = array('id' => array('in', $unId), 'roleId' => $roleId);

                $data = D('User')->where($where)
                    ->field('roleId,name,className,gradeId')->select();

                foreach ($data as $k => $v) {
                    $stu[] = array(
                        'name' => $v['name'],
                        'className' => $v['className'],
                        'grade' => $gradeMap[$v['gradeId']]
                    );
                }
                $response['status'] = 1;
                $response['data'] = $stu;
                $this->ajaxReturn($response);
            }
        }

        $uid = $this->uid;
        $scId = $this->scId;
        $model = M();
        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";

        $order = 'n.createTime desc';
        $limit = $_POST['order'];
        if (!empty($limit))
            $order = 'n.' . $limit;


        $where = array(
            'n.creatorId' => $uid,
            '_string' => 'n.id=nu.nid',
            'n.scId' => $scId
        );
        $data = $model->table('mks_notification n,mks_notification_user nu')->
        where($where)->field("n.id,n.title,n.kind,n.createTime,n.draft,
        nu.uncheckId,nu.uid")->order($order)->limit($limit_page)->select();
        $total = (int)$model->table('mks_notification n,mks_notification_user nu')->
        where($where)->count();

        if (!$data) {
            $this->ajaxReturn($response);
        }

        // 未查阅名单
       /* $adminUserId=D('User')->where(array('scId'=>$this->scId,'roleId'=>$this::$adminRoleId))->field('id')->select();
        $aUserId=array_map(function($v){
            return $v['id'];
        },$adminUserId);*/

        foreach ($data as &$v) {
            $unchecks = empty($v['uncheckId'])?array():explode(',', $v['uncheckId']);
            $uncheck = array_map(function ($v) {
                return (int)$v;
            }, $unchecks);
            //$uncheck=array_diff($uncheck,$aUserId);
            $allchecks = explode(',', $v['uid']);
            $allcheck = array_map(function ($v) {
                return (int)$v;
            }, $allchecks);
           // $allcheck=array_diff($allcheck,$aUserId);
            $v['ratio'] = (count($allcheck) - count($uncheck)) . '/' . count($allcheck);
            if (!$uncheck) {
                continue;
            }

        }
        $response['total'] = $total;
        $response['maxPage'] = ceil($total / $count) > 0 ? 1 : ceil($total / $count);
        $response['data'] = $data;
        $response['status'] = 1;
        $this->ajaxReturn($response);
    }


    /*删除通知*/ //已测试
    private function del($ids)
    {

        $map = array(
            'id' => array('in', $ids)
        );

        $paths = M('Notification')->where($map)->field('accessory')->select();
        $re = M('NotificationUser')->where(array('nid' => array('in', $ids)))->delete();
        $rs = M('Notification')->where($map)->delete();
        if (!$rs) {
            return false;
        }
        $paths = array_map(function ($v) {
            return explode(',', $v['accessory']);
        }, $paths);
        $i = 0;
        $path = array();
        foreach ($paths as $k => $t) {
            foreach ($t as &$v) {
                if (empty($v))
                    continue;
                $path[$i] = $v;
                $i++;
            }
        }
        $accessory = new Accessory('', $this->scId, 'notification');
        $accessory->del($path);
        return true;
    }

    /*常用联系人*/
    public function group()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];
        if (isset($type)) {
            if ($type == 'addGroup') { //添加分组
                $group = array(
                    'group' => $_POST['group'],
                    'creatorId' => $this->uid,
                    'lastRecordTime' => time(),
                    'scId' => $this->scId
                );
                $rs = D('NotificationGroup')->data($group)->add();
            } elseif ($type == 'delGroup') {
                $id = $_POST['id'];
                $rs = D('NotificationGroup')->where(array('id' => $id))->delete();
            } elseif ($type == 'editGroup') {
                $id = $_POST['id'];
                $rs = D('NotificationGroup')->where(array('id' => $id))->save(array('group' => $_POST['group']));
            } elseif ($type == 'user') {
                $id = $_POST['id'];
                $data = array(
                    'user' => json_encode($_POST['user'])
                );
                $rs = D('NotificationGroup')->where(array('id' => $id))->save($data);
            } elseif ($type == 'delUser') {
                $groupId = $_POST['groupId'];
                $userId = $_POST['userId'];
                $rs = D('NotificationGroup')->where(array('id' => $groupId))->find();
                $group = json_decode($rs['user'], true);
                foreach ($group as $k => $v) {
                    if ($v['id'] == $userId) {
                        unset($group[$k]);
                    }
                }
                $group = array('user' => json_encode($group));
                $rs = D('NotificationGroup')->where(array('id' => $groupId))->save($group);
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
        $where = array(
            'scId' => $this->scId,
            'creatorId' => $this->uid,
        );
        $data = D('NotificationGroup')->where($where)->field('id,group,user')->select();
        if (!$data) {
            $this->ajaxReturn($response);
        }
        $rs = array();
        foreach ($data as $k => $v) {
            $rs[] = array(
                'id' => $v['id'],
                'pId' => 0,
                'name' => $v['group'],
                'open' => true
            );
            $temp = empty($v['user']) ? array() : json_decode($v['user'], true);
            foreach ($temp as $k1 => $v1) {
                $rs[] = array(
                    'id' => $v['id'] . ((int)$k + 1),
                    'pId' => $v['id'],
                    'name' => $v1['name'],
                    'userId' => (int)$v1['id'],
                    'open' => true
                );
            }
        }
        $response['status'] = 1;
        $response['data'] = $rs;
        $this->ajaxReturn($response);
    }

}