<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/9/15
 * Time: 9:53
 */

namespace Home\Controller;

use Home\Common\Accessory;

/*
 * 档案管理
 * */
ob_end_clean();

//权限已设置
class FileManageController extends Base
{
    protected $roleId;
    protected $scId;
    protected $uId;
    protected $user;

    public function __construct()
    {
        parent::__construct();
        $this->scId = $_SESSION['loginCheck']['data']['scId'];
        $this->uId = $_SESSION['loginCheck']['data']['userId'];
        $this->roleId = $_SESSION['loginCheck']['data']['roleId'];
/*        $this->roleId = 22;
        $this->uId= 1;
        $this->scId = 2;*/

        $this->user = D('User')->where(array('id' => $this->uId, 'scId' => $this->scId))->find();
    }

    //公共调用接口
    public function common($func, $param = array())
    {
        switch ($func) {
            case 'getApprover':  //候选审批人
                $this->getApprover($param['key']);
                break;
            case 'getOwner': //得到档案所有人
                $this->getOwner();
                break;
            case 'getTag': //得到所有标签
                $this->getTag();
                break;
            case 'lastTag': //得到上一次标签
                $this->lastTag();
                break;
            case 'getAccessory': //得到附加的信息
                $this->getAccessory($param['fileId']);
                break;
            case 'fort':
                $this->fort();
                break;
            default:
                return null;
        }
    }

    //标签设置 //已测试
    public function tagSetting()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        $type = $_POST['type'];
        if (isset($type)) {
            $rs = false;
            if ($type == 'addGenre') {
                $name = $_POST['name'];
                $con = array(
                    'scId' => $this->scId,
                    'name' => $name,
                    'parentId'=>0
                );
                $rs = D('FileTag')->where($con)->find();
                if ($rs) {
                    $response['msg'] = '类型已存在';
                    $this->ajaxReturn($response);
                }
                $genre = array(
                    'name' => $name,
                    'scId' => $this->scId,
                    'creator' => $this->user['name'],
                    'creatorId' => $this->uId,
                    'lastRecordTime' => time()
                );
                $rs = D('FileTag')->data($genre)->add();
            } elseif ($type == 'save') {
                $id = $_POST['id'];
                $name = $_POST['name'];
                $con = array(
                    'scId' => $this->scId,
                    'name' => $name,
                    'parentId'=>0
                );
                $rs = D('FileTag')->where($con)->find();
                if ($rs) {
                    $response['msg'] = '类型已存在';
                    $this->ajaxReturn($response);
                }
                $rs = D('FileTag')->where(array('id'=>$id))->data(array('name' => $name))->save();
            }elseif ($type == 'addTag') {
                $con = array(
                    'scId' => $this->scId,
                    'name' => $_POST['name'],
                    'parentId' => $_POST['id']
                );
                $rs = D('FileTag')->where($con)->find();
                if ($rs) {
                    $response['msg'] = '标签存在重复值';
                    $this->ajaxReturn($response);
                }
                $tag = array(
                    'name' => $_POST['name'],
                    'scId' => $this->scId,
                    'parentId' => $_POST['id'],
                    'creator' => $this->user['name'],
                    'creatorId' => $this->uId,
                    'lastRecordTime' => time()
                );
                $rs = D('FileTag')->data($tag)->add();
            } elseif ($type == 'del') {
                $id = $_POST['id'];
                $condition=array('id'=>$id,'parentId'=>$id);
                $condition['_logic']='or';
                $rs = D('FileTag')->where($condition)->delete();
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            if ($rs) {
                $response['msg'] = '操作成功';
                $response['status'] = 1;
            } else {
                $response['msg'] = '操作失败';
            }
            $this->ajaxReturn($response);
        }
        $where = array(
            'scId' => $this->scId
        );
        $data = D('FileTag')->where($where)->field('id,name,parentId')->select();
        $tag = array();
        if ($data) {
            foreach ($data as $k => $v) {
                if ($v['parentId'] == 0) {
                    $tag[$v['id']] = array(
                        'id' => $v['id'],
                        'name' => $v['name'],
                        'tags' => array()
                    );
                } else {
                    $tag[$v['parentId']]['tags'][] = array(
                        'id' => $v['id'],
                        'name' => $v['name'],
                    );
                }
            }
            sort($tag);
            $response['status'] = 1;
            $response['data'] = $tag;
        }
        $this->ajaxReturn($response);
    }

    //得到候选审批人
    private function getApprover($key='',$port=1)
    {
        $roleId=D('Role')->where(array('scId'=>$this->scId))->field('roleId')->select();
        $roleId=array_map(function($v){
            return (int)$v['roleId'];
        },$roleId);

        $roleId=empty($roleId)?array(1,2,14,16):array_merge($roleId,array(1,2,14,16));

        $where = array(
            'roleId' => array('in', $roleId),
            'scId' => $this->scId
        );
        if(!empty($key)){
            $where['name']=array('like',"%{$key}%");

        }
        $data = D('User')
            ->where($where)
            ->field('id,roleId,name,post,department')
            ->select();
        if($port==2){
            return $data;
        }
        $approver = array();
        foreach ($data as &$v) {
            if($v['roleId']!=14){
                $department=empty($v['department'])?'其他职工':$v['department'];
                $post='职工';
            }else{
                $department=empty($v['department'])?'其他教师':$v['department'];
                $post='教师';
            }
            $approver[$post][$department][] = array(
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'post'=>$v['post']
               );
        }

        $this->ajaxReturn($approver);
    }

    //审批设置
    public function approveSetting()
    {
        $response = array(
            'status' => 0,
            'data'=>array()
        );
        $exist = D('FileApprover')->where(array('scId' => $this->scId))->find();
        $type=$_POST['type'];
        if(isset($type)){
           if($type=='save'){
               $approvers = $_POST['approver'];
               $approver = array();
               foreach ($approvers as $k => $v) {
                   $approver['id'][] = $v['id'];
                   $approver['name'][] = $v['name'];
               }
               if ($exist) {
                   $rs = D('FileApprover')
                       ->where(array('id' => $exist['id']))
                       ->data(array(
                           'approveId' => implode(',', $approver['id']),
                           'approver' => implode(',', $approver['name'])
                       ))
                       ->save();
               } else {
                   $rs = D('FileApprover')
                       ->where(array('scId' => $this->scId))
                       ->data(array(
                           'scId' => $this->scId,
                           'approveId' => implode(',', $approver['id']),
                           'approver' => implode(',', $approver['name'])
                       ))
                       ->add();
               }
               if (!$rs) {
                   $response['msg'] = '操作失败';
                   $this->ajaxReturn($response);
               }
               $response['status'] = 1;
               $response['msg'] = '操作成功';
           }
        }
        if(!$exist){
            $this->ajaxReturn($response);
        }
        $exist['approveId']=explode(',',$exist['approveId']);
        $exist['approver']=explode(',',$exist['approver']);
        $response['status']=1;
        $response['data']=$exist;
        $this->ajaxReturn($response);
    }

    //是否已审批设置
    private function fort(){
        $re=array(
            'status'=>false
        );
        $exist = D('FileApprover')->where(array('scId' => $this->scId))->getField('approveId');
        if (!$exist) {
            $re['msg'] = '请先进行审批设置';
            $this->ajaxReturn($re);
        }
        $re['status']=true;
        $this->ajaxReturn($re);
    }

    //得到档案所有人
    private function getOwner()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $roleId=D('Role')->where(array('scId'=>$this->scId))->field('roleId')->select();
        $roleId=array_map(function($v){
            return (int)$v['roleId'];
        },$roleId);
        $roleId=empty($roleId)?array(1,2,14,16):array_merge($roleId,array(1,2,14,16));

        $where = array(
            'roleId' => array('in', $roleId),
            'scId' => $this->scId,
            'state'=>1
        );
        $data = D('User')
            ->where($where)
            ->field('id,name,post,department')
            ->select();

        if (!$data) {
            $this->ajaxReturn($response);
        }
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
    }

    //得到标签
    private function getTag()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );

        $where = array(
            'scId' => $this->scId
        );
        $data = D('FileTag')->where($where)->field('id,name,parentId')->select();

        if (!$data) {
            $this->ajaxReturn($response);
        }
        $tag=array();
        if ($data) {
            foreach ($data as $k => $v) {
                if ($v['parentId'] == 0) {
                    $tag[$v['id']] = array(
                        'id' => $v['id'],
                        'name' => $v['name'],
                        'tags' => array()
                    );
                } else {
                    $tag[$v['parentId']]['tags'][] = array(
                        'id' => $v['id'],
                        'name' => $v['name'],
                        'parentId' => $v['parentId']
                    );
                }
            }
            sort($tag);
            $response['status']=1;
            $response['data']=$tag;
        }
        $this->ajaxReturn($response);
    }

    //得到附件
    public function getAccessory($fileId)
    {
        $data = D('FileAccessory')->where(array('fileId'=>$fileId))->field('id as accId,accessory,accessoryName')->select();
        $response = array();
        if ($data) {
            $response = $data;
        }
        $this->ajaxReturn($response);
    }

    //得到上一次标签
    private function lastTag()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $where = array(
            'creatorId' => $this->uId,
            'scId' => $this->scId,
        );
        $tag = D('File')->where($where)->order('createTime desc')->getField('tag');

        if (!$tag) {
            $this->ajaxReturn($response);
        }

        $tag = explode(',', $tag);
        $response['status'] = 1;
        $response['data'] = $tag;
        $this->ajaxReturn($response);
    }

    //档案记录
    public function fileRecord($option = '')
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type =$_REQUEST['type'];

        if (isset($type)) {
            if ($type == 'addFile') { //新增档案 //已测试
                $exist = D('FileApprover')->where(array('scId' => $this->scId))->getField('approveId');
                if (!$exist) {
                    $response['msg'] = '请先进行审批设置';
                    $this->ajaxReturn($response);
                }

                $date = date('Y-m-d', time());
                $time = strtotime($date);

                $index = D('File')->where(array('createTime' => array('gt', $time), 'scId' => $this->scId))->max('indexIndex');
                if (!$index) {
                    $index = 1;
                }else{
                    $index++;
                }
                $str = str_pad($index, 4, "0", STR_PAD_LEFT);
                $data = $_POST;
                //$this->ajaxReturn($data);
                $file = array(
                    'name' => $data['name'],
                    'identity' => 'DA' . date('Ymd', time()) . '-' . $str,
                    'ownerId' =>  $data['ownerId'],
                    'owner' =>  $data['owner'],
                    'tag' => $data['tag'],
                    'indexIndex' => $index,
                    'time' => strtotime($data['time']),
                    'remark' => $data['remark'],
                    'creator' => $this->user['name'],
                    'creatorId' => $this->uId,
                    'createTime' => time(),
                    'scId' => $this->scId,
                );
                $rs = D('File')->data($file)->add();
                //添加对应审批流程
                if ($rs) {
                    //审批人
                    $newId = $rs;
                    $process = array(
                        'fileId' => $rs,
                        'status' => 0,
                        'admin' => 0,
                        'yetId' => $exist
                    );
                    $rs = D('FileProcess')->data($process)->add();

                    if ($rs) {
                        //对上传附件进行处理 测试通过
                        $uploadFile = $_FILES;
                        $accessory = array();
                        $aName = array();
                        if ($uploadFile) {
                            foreach ($uploadFile as &$v) {
                                foreach ($v['name'] as &$val) {
                                    $aName[] = $val;
                                }
                            }
                            $subName = 'record';
                            $upload = new Accessory($uploadFile, $this->scId, $subName);
                            $accessory = $upload->upload();
                            //上传失败
                            if (!$accessory['status']) {
                                $response = array(
                                    'msg' => $accessory['msg'],
                                    'status' => 0
                                );
                                $this->ajaxReturn($response);
                            }
                            //上传成功处理
                            $time = time();
                            $uId = $this->uId;
                            $scId = $this->scId;
                            $path = $accessory['path'];
                            $val = '';
                            for ($i = 0; $i < count($path); $i++) {
                                $val .= '(' . "'{$path[$i]}'," . "'{$aName[$i]}'," . "{$scId}," . "{$time},"
                                    . "{$uId}," . "{$newId}" . '),';
                            }
                            $val = rtrim($val, ',');
                            if(!empty($val)){
                                $sql = "insert into mks_file_accessory (accessory,accessoryName,scId,lastRecordTime,userId,fileId)
                          values {$val}";
                                $rs = M()->execute($sql);
                            }
                        }
                    }
                }
            } elseif ($type == 'downloadAcc') { //下载附件 //已测试
                $accId = I('request.accId');
                $this->operateAccessory('download', $accId, '');
            } elseif ($type == 'preview') { //@todo 预览
                $accId =  I('request.accId');
                $response = $this->operateAccessory('preview', $accId);
                $this->ajaxReturn(array($response));
            } elseif ($type == 'uploadAcc') { //上传单个附件 //已测试
                $file = $_FILES;
                $aName = array();
                foreach ($file as &$v) {
                    foreach ($v['name'] as &$val) {
                        $aName[] = $val;
                    }
                }
                $fileId = I('post.fileId');
                $accessory = $this->operateAccessory('upload', '', $file);
                //上传失败
                if (!$accessory['status']) {
                    $response = array(
                        'msg' => $accessory['msg'],
                        'status' => 0
                    );
                    $this->ajaxReturn($response);
                }
                // 存路径和文件名
                $new = array(
                    'accessory' => $accessory['path'][0],
                    'accessoryName' => $aName[0],
                    'scId' => $this->scId,
                    'lastRecordTime' => time(),
                    'userId' => $this->uId,
                    'fileId' => $fileId
                );
                $rs = D('FileAccessory')->data($new)->add();
            } elseif ($type == 'delAcc') { //删除单个附件 //已测试
                $accId = I('post.accId');
                $rs = $this->operateAccessory('del', $accId, '');
                if ($rs) {
                    $rs = D('FileAccessory')->where("id={$accId}")->delete();
                }
            } elseif ($type == 'reName') { //附件重命名 //已测试
                $accId = I('post.accId');
                $rs = D('FileAccessory')->where(array('id'=>$accId))->data(array('accessoryName' => $_POST['aName']))->save();
            } elseif ($type == 'editFile') { //编辑档案 //已测试
                $fileId =I('post.fileId');
                $data = $_POST;
                $file = array(
                    'name' => $data['name'],
                    'ownerId' => implode(',', $data['ownerId']),
                    'owner' => implode(',', $data['owner']),
                    // 'tag' => json_encode($data['tag']),//@todo
                    'tag' => implode(',', $data['tag']),
                    'time' => strtotime($data['time']),
                    'remark' => $data['remark'],
                );
                $rs = D('File')->where("id={$fileId}")->data($file)->save();
            } elseif ($type == 'delFile') { //删除档案 //已测试
                $fileId = $_POST['fileIds'];//数组
                $where = array(
                    'id' => array('in', $fileId),
                    'scId' => $this->scId
                );
                $rs = D('File')->where($where)->delete();

                if ($rs) {
                    $rs = D('FileProcess')->where(array('fileId' => array('in', $fileId)))->delete();
                    if ($rs)
                        $accId = D('FileAccessory')->where(array('fileId' => array('in', $fileId)))->field('accessory')->select();
                    if (!empty($accId)) {
                        $path = array_map(function ($v) {
                            return $v['accessory'];
                        }, $accId);
                        $accessory = new Accessory('', $this->scId, 'record');
                        $accessory->del($path);
                        $rs = D('FileAccessory')->where(array('fileId' => array('in', $fileId)))->delete();
                    }
                }
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            if ($rs) {
                $response['status'] = 1;
                $response['msg'] = '操作成功';
            } else {
                $response['msg'] = '操作失败';
            }
            $this->ajaxReturn($response);
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        if ($option != 2) {//待处理
            $map = array(
                'f.scId' => $this->scId,
                'fp.status' => array('in', array(0, 2))
            );
            if ($this->roleId != $this::$adminRoleId) {
                $map['f.creatorId'] = $this->uId;
            }

        } else {//已通过
            $map = array(
                'f.scId' => $this->scId,
                'fp.status' => 1
            );
            if ($this->roleId != $this::$adminRoleId) {
                $map['f.creatorId'] = $this->uId;
            }
            if (!empty($_POST['startTime']) && !empty($_POST['endTime'])) {
                $map['f.time'] = array('between', array(strtotime($_POST['startTime']), strtotime($_POST['endTime'])));
            }
            if (isset($_POST['inTag'])) {
                $str = implode(',', $_POST['inTag']);
                $map['_string'] = "concat(',',f.tag,',') regexp concat(',(',replace('{$str}',',','|'),'),')";
            }
        }
        $key=$_POST['key'];
        if(!empty($key)){
            $map['f.name|f.identity|f.owner']=array('like',"%{$key}%");
        }

        $data = D('File f')
            ->join('mks_file_process fp ON f.id=fp.fileId','LEFT')
            ->where($map)
            ->field('f.id as fileId,f.name,f.identity,f.tag,f.owner,f.time,f.remark,f.creator,f.createTime,fp.yetId,
             fp.situation,fp.status,fp.admin')
            ->order('f.createTime desc')
            ->limit($limit_page)
            ->select();

        if ($data) {
            $total=(int)D('File f')
                ->join('mks_file_process fp ON f.id=fp.fileId','LEFT')
                ->where($map)->count();
            $yetId=array();

            foreach ($data as $k=>$v){
                if(!empty($v['yetId'])){
                    $temp=explode(',',$v['yetId']);
                    $yetId=array_merge($yetId,$temp);
                }
            }
            $nameMap=array();
            if(!empty($yetId)){
                $yetId=array_unique($yetId);
                $name=D('User')->where(array('id'=>array('in',$yetId)))->field('id,name')->select();
                foreach ($name as $k=>$v){
                    $nameMap[$v['id']]=$v['name'];
                }
            }

            $tag = D('FileTag')->where(array('scId' => $this->scId))->field('id,name,parentId')->select();
            $map = array();
            foreach ($tag as $k => $v) {
                $map[$v['id']] = $v['name'];
            }

            foreach ($data as &$v) {
                $temp = explode(',', $v['tag']);
                $tempTag=array();
                foreach ($temp as $k1 => $v1) {
                    if(isset($map[$v1]))
                    $tempTag[] = $map[$v1];
                }
                $v['tag'] = $tempTag;
                $v['owner'] = explode(',', $v['owner']);
                $v['time']=date('Y-m-d',$v['time']);
                //$v['situation'] = json_decode($v['situation'], true);
                $situation = json_decode($v['situation'], true);

                if ($v['admin'] > 0) {
                    $v['situation'] = array($situation[$v['admin']]);
                }else{
                    $situation=empty($situation)?array():array_values($situation);
                    $v['situation']=$situation;
                    if(!empty($v['yetId'])){
                        $temp=explode(',',$v['yetId']);
                        foreach ($temp as $k1=>$v1){
                            $v['situation'][$v1]=array(
                                'name'=>$nameMap[$v1],
                                'opinion'=>'',
                                'result'=>''
                            );
                        }
                    }
                }
                $v['situation']=array_values($v['situation']);
            }
            $response['total']=$total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['data'] = $data;
            $response['status'] = 1;
        }
        $this->ajaxReturn($response);
    }

    /*
     * @param $type 字符 具体的操作
     * @param $id  整型 附件id
     * @param $file 文件 上传的文件
     * */
    //对单个附件进行操作 //
    private function operateAccessory($type, $id = '', $file = '')
    {
        $response = array();
        if ($type == 'download') { //下载
            $accessory = new Accessory($file, $this->scId, 'record');
            $info = D('FileAccessory')->where(array('id'=>$id))->field('accessory,accessoryName')->find();
            if(!$info){
                $response['status']=0;
                $response['msg']='未找到对应文件';
                $this->ajaxReturn($response);
            }
            $accessory->download(array($info['accessory']), array($info['accessoryName']), true);
            die;
        } elseif ($type == 'del') { //删除
            $accessory = new Accessory($file, $this->scId, 'record');
            $info = D('FileAccessory')->where(array('id'=>$id))->field('accessory,accessoryName')->find();
            $response = $accessory->del($info['accessory']);
            return $response;
        } elseif ($type == 'upload') { //上传
            $accessory = new Accessory($file, $this->scId, 'record');
            $response = $accessory->upload();
            return $response;
        } elseif ($type == 'preview') {
            $info = D('FileAccessory')->where(array('id'=>$id))->field('accessory')->find();
            $postfix=strstr($info['accessory'],'.') ;
            $postfix=ltrim($postfix,'.');
            $imgArray=array('bmp','jpg','png','tiff','gif','pcx','tga','exif','fpx','svg','psd','cdr',
                'pcd','dxf','ufo','eps','ai','raw','WMF');
            $isImg=False;
            foreach ($imgArray as $k=>$v){
                if($postfix==$v){
                    $isImg=True;
                    break;
                }
            }
            if($isImg){
                $str='http://171.221.202.190:11111/api/school/Public/upload/'.$info['accessory'];
            }else{
                $str='http://ow365.cn/?i=14474&furl=';
                $str.='http://171.221.202.190:11111/api/school/Public/upload/'.$info['accessory'];
            }
            /* $str="https://view.officeapps.live.com/op/view.aspx?src=";
                 $str.=urlencode("http://171.221.202.190:11111/api/school/Public/upload/".$info['accessory']);*/
            return $str;
        }
    }

    /*@param $uId 审批人id
     *@param $option 审批
     * */
    //对档案审批 //已测试
    private function approve($fileId, $option, $opinion = '')
    {
        $rs = false;
        $uId = $this->uId;

        $where = array(
            'fileId' => $fileId,
            'status' => 0
        );
        if ($this->roleId != $this::$adminRoleId)
            $where['_string'] = "FIND_IN_SET($uId,yetId)";
        $info = D('FileProcess')->where($where)->field('situation,yetId,approvedId')->find();
        if (empty($info))
            return $rs;
        $situation = json_decode($info['situation'], true);
        $yetId = empty($info['yetId']) ? array() : explode(',', $info['yetId']);
        $approvedId = empty($info['approvedId']) ? array() : explode(',', $info['approvedId']);

        if ($this->roleId != $this::$adminRoleId) {
            $admin = 0;
            $offset = 0;
            foreach ($yetId as $k => $v) {
                if ($this->uId == $v) {
                    $offset = $k;
                    break;
                }
            }
            $temp = array_splice($yetId, $offset, 1);
            $approvedId[] = $temp[0];
            $situation[$uId]['name']=$this->user['name'];
            $situation[$uId]['opinion'] = $opinion;
            if ($option == 'pass') {
                $situation[$uId]['result'] = '通过';
                if (count($yetId) > 0) {
                    $status = 0;
                } else {
                    $status = 1;
                }
            } else {
                $status = 2;
                $situation[$uId]['result'] = '未通过';
            }
        } else {
            $situation[$uId] = array(
                'name' => '管理员',
                'opinion' => $opinion
            );
            $approvedId[] = $uId;
            $admin = $uId;
            if ($option == 'pass') {
                $status = 1;
                $situation[$uId]['result'] = '通过';
            } else {
                $status = 2;
                $situation[$uId]['result'] = '未通过';
            }
        }
        $change = array(
            'situation' => json_encode($situation),
            'yetId' => implode(',', $yetId),
            'approvedId' => implode(',', $approvedId),
            'admin' => $admin,
            'status' => $status
        );

        $rs = D('FileProcess')->where(array('fileId'=>$fileId))->data($change)->save();
        return $rs;
    }

    //档案审批
    public function fileApprove($option = '')
    {
        $response = array(
            'status' => 0,
            'msg' => ''
        );
        $type = $_REQUEST['type'];
        if (isset($type)) {
            if ($type == 'addApprover') { //添加审批人 已测试
                $approver = $_POST['approver'];
                $fileId = $_POST['fileIds'];
                $situation = array();
                $yetId = array();
                foreach ($approver as $k => $v) {
                    $situation[$k] = array(
                        'name' => $v,
                        'result' => 0,
                        'opinion' => null
                    );
                    $yetId[] = $k;
                }
                $change = array(
                    'situation' => json_encode($situation),
                    'yetId' => implode(',', $yetId)
                );
                $rs = D('FileProcess')->where(array('fileId' => array('in', $fileId)))->data($change)->save();
            } elseif ($type == 'approve') { //审批
                $operate = $_POST['operate'];
                $opinion = $_POST['opinion'];
                $fileId = $_POST['fileId'];
                $rs = $this->approve($fileId, $operate, $opinion);
            } elseif ($type == 'downloadAcc') { //下载附件 //已测试
                $accId =I('request.accId');
                $this->operateAccessory('download', $accId, '');
            } elseif ($type == 'preview') { //@todo 预览
                $accId = I('request.accId');
                $response = $this->operateAccessory('preview', $accId);
                $this->ajaxReturn(array($response));
            } elseif ($type == 'uploadAcc') { //上传单个附件 //已测试
                $file = $_FILES;
                $aName = array();
                foreach ($file as &$v) {
                    foreach ($v['name'] as &$val) {
                        $aName[] = $val;
                    }
                }
                $fileId = $_POST['fileId'];
                $accessory = $this->operateAccessory('upload', '', $file);

                //上传失败
                if (!$accessory['status']) {
                    $response = array(
                        'msg' => $accessory['msg'],
                        'status' => 0
                    );
                    $this->ajaxReturn($response);
                }
                // 存路径和文件名
                foreach ($accessory['path'] as $k=>$v){
                    $new[] = array(
                        'accessory' => $accessory['path'][$k],
                        'accessoryName' => $aName[$k],
                        'scId' => $this->scId,
                        'lastRecordTime' => time(),
                        'userId' => $this->uId,
                        'fileId' => $fileId
                    );
                }
                if(!empty($new))
                $rs = D('FileAccessory')->addALL($new);
            } elseif ($type == 'delAcc') { //删除单个附件 //已测试
                $accId =I('post.accId');
                $rs = $this->operateAccessory('del', $accId, '');
                if ($rs) {
                    $rs = D('FileAccessory')->where(array('id'=>$accId))->delete();
                }
            } elseif ($type == 'reName') { //附件重命名 //已测试
                $accId =I('post.accId');
                $rs = D('FileAccessory')->where(array('id'=>$accId))->data(array('accessoryName' => $_POST['aName']))->save();
            } elseif ($type == 'editFile') { //编辑档案 //已测试
                $fileId = $_POST['fileId'];
                $data = $_POST;
                $file = array(
                    'name' => $data['name'],
                    'ownerId' => implode(',', $data['ownerId']),
                    'owner' => implode(',', $data['owner']),
                    // 'tag' => json_encode($data['tag']),//@todo
                    'tag' => implode(',', $data['tag']),
                    'time' => strtotime($data['time']),
                    'remark' => $data['remark'],
                );
                $rs = D('File')->where("id={$fileId}")->data($file)->save();
            } else {
                $response['msg'] = '没有权限';
                $this->ajaxReturn($response);
            }
            if ($rs) {
                $response['status'] = 1;
                $response['msg'] = '操作成功';
            } else {
                $response['msg'] = '操作失败';
            }
            $this->ajaxReturn($response);
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        if ($option != 2) {//待审批
            $map = array(
                'f.scId' => $this->scId,
                'fp.status' => 0,
            );
            if ($this->roleId != $this::$adminRoleId) {
                $map['_string'] = "FIND_IN_SET({$this->uId},yetId)";
            }
        } else {//已审批
            $map = array(
                'f.scId' => $this->scId,
                'fp.status' => array('in', array(1, 2))
            );
            if ($this->roleId != $this::$adminRoleId) {
                $map['_string'] = "FIND_IN_SET($this->uId,fp.approvedId)";
            }
            if (!empty($_POST['startTime']) && !empty($_POST['endTime'])) {
                $map['f.time'] = array('between', array(strtotime($_POST['startTime']), strtotime($_POST['endTime'])));
            }
            if (isset($_POST['inTag'])) {
                $str = implode(',', $_POST['inTag']);
                $map['_string'] = "concat(',',f.tag,',') regexp concat(',(',replace('{$str}',',','|'),'),')";
            }
        }
        $key=$_POST['key'];
        if(!empty($key)){
            $map['f.name|f.identity|f.owner']=array('like',"%{$key}%");
        }

        $data = D('File f')
            ->join('mks_file_process fp ON f.id=fp.fileId','LEFT')
            ->where($map)
            ->field('f.id as fileId,f.name,f.identity,f.tag,f.owner,f.ownerId,f.time,f.remark,f.creator,f.createTime,fp.yetId,
            fp.situation,fp.status,fp.admin')
            ->order('f.createTime desc')
            ->limit($limit_page)
            ->select();


        if ($data) {
            $tag = D('FileTag')->where(array('scId' => $this->scId))->field('id,name,parentId')->select();
            $tagMap = array();
            foreach ($tag as $k => $v) {
                $tagMap[$v['id']] = $v['name'];
            }
            $total=(int)D('File f')
                ->join('mks_file_process fp ON f.id=fp.fileId','LEFT')
                ->where($map)
                ->count();
            $yetId=array();
            foreach ($data as $k=>$v){
                if(!empty($v['yetId'])){
                    $temp=explode(',',$v['yetId']);
                    $yetId=array_merge($yetId,$temp);
                }
            }
            $nameMap=array();
            if(!empty($yetId)){
                $yetId=array_unique($yetId);
                $name=D('User')->where(array('id'=>array('in',$yetId)))->field('id,name')->select();
                foreach ($name as $k=>$v){
                    $nameMap[$v['id']]=$v['name'];
                }
            }

            foreach ($data as &$v) {
                $temp = empty($v['tag'])?array():explode(',', $v['tag']);
                $temp1=array();
                foreach ($temp as $k1 => $v1) {
                    if(isset($tagMap[$v1]))
                    $temp1[] = array('id'=>$v1,'tag'=>$tagMap[$v1]);
                }
                $v['tag'] = $temp1;
                $v['ownerId']=empty($v['ownerId'])?array():explode(',', $v['ownerId']);
                $v['owner']=empty($v['owner'])?array():explode(',', $v['owner']);
                $v['createTime']=date('Y-m-d H:i:s',$v['createTime']);
                $v['time']=date('Y-m-d',$v['time']);
                $situation = json_decode($v['situation'], true);
                if ($v['admin'] > 0) {
                    $v['situation'] = array($situation[$v['admin']]);
                }else{
                    $situation=empty($situation)?array():array_values($situation);
                    $v['situation']=$situation;
                    if(!empty($v['yetId'])){
                        $temp=explode(',',$v['yetId']);
                        foreach ($temp as $k1=>$v1){
                            $v['situation'][$v1]=array(
                                'name'=>$nameMap[$v1],
                                'opinion'=>'',
                                'result'=>''
                            );
                        }
                    }
                }
                $v['situation']=array_values($v['situation']);
            }
            $response['total']=$total;
            $response['maxPage'] = ceil($total / $count) < 1 ? 1 : ceil($total / $count);
            $response['data'] = $data;
            $response['status'] = 1;
        }
        $this->ajaxReturn($response);

    }

    //档案统计
    public function fileStatistics()
    {
        $response = array(
            'status' => 0,
            'data'=>array(),
            'filed'=>array()
        );
        $tag = $_POST['tag'];

        $vdoing_line= $tag[0];
        $vdoing_row = $tag[1];
        $fId=D('File f')->join('mks_file_process fp on f.id=fp.fileId','Left')
            ->where(array('f.scId'=>$this->scId,'fp.status'=>1))
            ->field('f.id')->select();
        $fId=array_map(function($v){
            return $v['id'];
        },$fId);
        $map = array(
            'scId' => $this->scId,
            'id'=>array('in',$fId)
        );
        //标签映射关系
        $mapTag=array();
        $tags=D('FileTag')->where(array('scId'=>$this->scId))->field('id,name')->select();
        foreach ($tags as $k=>$v){
            $mapTag[$v['id']]=$v['name'];
        }

        $count = array();
        if (!empty($_POST['startTime']) && !empty($_POST['endTime']))
            $map['time'] = array('between', array(strtotime($_POST['startTime']),strtotime($_POST['endTime'])));
        $filed=array();
        if(!empty($vdoing_row)){
            foreach ($vdoing_line as $k => $vl) {
                $filed[]=array(
                    'id'=>$vl.$k,
                    'name'=>$mapTag[$vl]
                );
                foreach ($vdoing_row as &$vr) {
                    $str = $vr . ',' . $vl;
                    $map['_string'] = "concat(',',tag,',') regexp concat(',(',replace('{$str}',',','|'),'),')";
                    $rs = D('File')->where($map)->count();
                    if ($rs===false)
                        $this->ajaxReturn($response);
                    if(!isset($count[$vr]))
                        $count[$vr] = array(
                            'name'=>$mapTag[$vr],
                        );
                    $count[$vr][$vl.$k]=$rs;
                }
            }
        }else{
            foreach ($vdoing_line as $k => $vl) {
                $filed[]=array(
                    'id'=>$vl.$k,
                    'name'=>$mapTag[$vl]
                );
                $str =   $vl;
                $map['_string'] = "concat(',',tag,',') regexp concat(',(',replace('{$str}',',','|'),'),')";
                $rs = D('File')->where($map)->count();
                if (!$rs)
                       $this->ajaxReturn($response);
                $temp_name='';
                if(!isset($count[$temp_name]))
                    $count[$temp_name] = array(
                        'name'=>$temp_name,
                    );
                $count[$temp_name][$vl.$k]=$rs;
            }
        }

        sort($count);
        $response['status'] = 1;
        $response['data'] = $count;
        $response['filed']=$filed;
        $this->ajaxReturn($response);
    }

}