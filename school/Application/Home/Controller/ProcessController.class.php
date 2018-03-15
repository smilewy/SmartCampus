<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/8/3
 * Time: 11:54
 */

namespace Home\Controller;
ob_end_clean();
//权限已设置
class ProcessController extends Base
{
    protected $uid;
    protected $scId;

    public function __construct()
    {
        parent::__construct();
        $this->scId = $_SESSION['loginCheck']['data']['scId'];
        $this->uid = $_SESSION['loginCheck']['data']['userId'];
     /*   $this->uid = 1;
        $this->scId = 2;*/
    }

    //创建一些预设流程
  /*  private function def(){
        $where=array(
            'scId'=>$this->scId,
            'name'=>'通用公文流转',
            'kind'=>4
        );
        $pro=D('Process')->where($where)->find();
        if(!$pro){
            $temp=array(array(
                'time'=>0,
                'approveId'=>array(),
                'approver'=>array(),
                'limit'=>2
            ));
            $data = array(
                'kind' => 4,
                'name' => '通用公文流转',
                'step' => 1,
                'process' => json_encode($temp),
                'scId' => $this->scId,
                'createTime' => time(),
                'creator' => $this->uid,
            );
            M('Process')->data($data)->add();

        }
    }*/

    /*创建流程*/ //已测试
    public function create($kind, $step, $name, $process)
    {
        $response = array(
            'msg' => '无权限',
            'status' => 0
        );
        $auth_type = $_POST['type'];
        if ($auth_type == 'create') {
            if($name=='通用公文流转'&&$kind==4){
                $response['msg'] = '系统已设置此流程';
                $this->ajaxReturn($response);
            }

            $data = array(
                'kind' => (int)$kind,
                'name' => $name,
                'step' => (int)$step,
                'process' => $process,
                'scId' => $this->scId,
                'createTime' => time(),
                'creator' => $this->uid,
            );
            $map = array('kind' => $kind,
                'name' => $name,
                'scId' => $this->scId);
            $rs = D('Process')->where($map)->find();

            if ($rs) {
                $response['msg'] = '此流程已存在';
                $this->ajaxReturn($response);
            }
            $result = M('Process')->data($data)->add();
            if ($result) {
                $response['msg'] = '操作成功';
                $response['status'] = 1;
                $this->ajaxReturn($response);
            }
        }

        $this->ajaxReturn($response);
    }

    /*流程管理*/ //已测试
    public function manage()
    {
        //编辑流程
        $response = array(
            'msg' => '',
            'status' => 0,
            'data'=>array()
        );
      //  $this->def();
        $operate = $_POST['type'];
        if($operate){
            $pid = $_POST['processId'];
            if($pid){
                if ($operate == 'edit') {
                    $data = array(
                        'kind' => (int)$_POST['kind'],
                        'step' => (int)$_POST['step'],
                        'name' => $_POST['name'],
                        'process' => $_POST['process'],
                    );
                    $result = M('Process')->where(array('id'=>$pid))->save($data);
                    if ($result) {
                        $response['msg'] = '编辑成功';
                        $response['status'] = 1;
                    } else {
                        $response['msg'] = '编辑出错';
                    }
                } //删除流程
                elseif ($operate == 'del') {
                    $where = array(
                        'id' => array('in', $pid),
                    );
                    $result = M('Process')->where($where)->delete();
                    if ($result) {
                        $response['msg'] = '删除成功';
                        $response['status'] = 1;
                    } else {
                        $response['msg'] = '删除出错';
                    }
                } else {
                    $response['msg'] = '没有此操作';
                }
            }
            $this->ajaxReturn($response);
        }


        $page=empty($_POST['page'])?1:(int)$_POST['page'];
        $count=empty($_POST['count'])?10:(int)$_POST['count'];
        $pre_page=($page-1)*$count;
        $limit_page="$pre_page,$count";
        $data=D('Process')
            ->where("scId={$this->scId}")->field('id,kind,name,process,step,scId')
            ->limit($limit_page)
            ->select();
        $total=(int)D('Process')
            ->where("scId={$this->scId}")
            ->count();
        if($data){
          /*  $post = array('老师', '职工');
            $where = array(
                'scId' => $this->scId,
                'post' => array('in', $post)
            );
            $user = D('User')->where($where)->field('id,name,post,department')->select();
            $map=array();
            foreach ($user as $k=>$v){
                $map[$v['id']]=$v['name'];
            }*/

            foreach ($data as $k=>$v){
                $process=json_decode($v['process'],true);
                $data[$k]['process']=$process;
               /* foreach ($process as $k1=>$v1){
                    foreach ($v1['approveId'] as $k2=>$v2){
                        $data[$k]['process'][$k1]['approver'][]=$map[$v2];
                    }
                }*/
            }
            $response['total']=$total;
            $response['maxPage']=ceil($total/$count)<1?1:ceil($total/$count);
            $response['status']=1;
        }
        $response['data']=$data;
        $this->ajaxReturn($response);
    }

    //待选人员
    public function userLists()
    {
       // $this->def();
        $response = array('status' => 1);
        $map = array(
            'scId' => array(array('eq', $this->scId), array('eq', 0), 'or'),
            'roleId'=>array('not in',array($this::$jZroleId,$this::$studentRoleId,$this::$adminRoleId))
        );
        $roleId = D('role')->where($map)->field('roleId')->select();
        $roleId = array_map(function ($v) {
            return $v['roleId'];
        }, $roleId);
        $where = array(
            'scId' => $this->scId,
            'roleId' => array('in',$roleId)
        );
        $field = 'id,roleId,name,post,department';
        $res = array();
        $data = D('User')->where($where)->field($field)->select();

        if ($data) {
            foreach ($data as &$v) {
                    $post=$v['post'];
                    $department=empty($v['department'])?'其他部门':$v['department'];
                    if (!isset($res[$post][$department]))
                        $res[$post][$department] = array();
                    $res[$post][$department][] = array(
                        'id' => $v['id'],
                        'name' => $v['name']
                    );
            }
        }
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

}