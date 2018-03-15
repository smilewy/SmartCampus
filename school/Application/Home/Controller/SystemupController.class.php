<?php
/**
 * Created by PhpStorm.
 * User: xiaolong
 * Date: 2017/6/22
 * Time: 14:15
 * 教务管理
 */
namespace Home\Controller;
//use Think\Controller;
//use Vendor\PHPExcel\PHPExcel;
class SystemupController extends Base
{
    public function faseLane() //快速通道
    {
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if ($type == 'create') {
            if(M('out_web')->add(array(
                'webName' =>   I('post.webName'),
                'webUrl' =>   I('post.webUrl'),
                'scId' => $scId,
                'createTime' => date('Y-m-d H:i:s')
            ))){
                $this->returnJson(array('statu' => 1,'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
        }
        if($type == 'delete'){
            if(M('out_web')->where(array('scId' => $scId,'id' =>I('post.id')))->delete()){
                $this->returnJson(array('statu' => 1,'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'fail'),true);
        }
        if($type == 'getList'){
            $data = M('out_web')->where(array('scId' => $scId))->select();
            $data = $this::sortPageValue($data);
            $this->returnJson(array('statu' => 1,'message' => 'success' ,'data' => $data),true);
        }
    }
    public function passWordUpdate(){
        $type = $_GET['type'];
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'updataPassword'){
            $password = $this->create_password( I('post.nowPassword'),self::$password_key,self::$password_key1);
            if(M('user')->where(array('scId' => $scId,'id' => $userId,'password' => $password))->find()){
                if($_POST['passwordNew'] == $_POST['passwordNewOne']){
                    $passwordNew = $this->create_password($_POST['passwordNew'],self::$password_key,self::$password_key1);
                    if(M('user')->where(array('scId' => $scId,'id' => $userId))->setField(array(
                        'password' => $passwordNew
                    ))){
                        $this->returnJson(array('statu' => 1,'message' => '密码修改成功'),true);
                    }
                    $this->returnJson(array('statu' => 3,'message' => '密码修改失败'),true);
                }
                $this->returnJson(array('statu' => 2,'message' => '两次密码输入不一致'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => '密码输入错误'),true);
        }
    }
    protected function create_password($password,$password_key,$password_key1){
        $passwordone = md5($password);
        $passwordtow = md5($passwordone.$password_key);
        return md5($passwordtow.$password_key1);
    }
}