<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/4
 * Time: 14:24
 */

namespace Home\Model;


use Think\Model;

class ProcessModel extends Model
{
    public function add($data)
    {
        $flag = false;
        $result = $this->data($data)->add();
        if ($result) {
            $flag = true;
        }
        return $flag;
    }

    //将流程的环节以数组格式返回
    public function getProcess($kind,$pname,$scId)
    {
        //将流程的环节以数组格式返回
        $map=array(
            'kind'=>$kind,
            'name'=>$pname,
            'scId'=>$scId
        );
        $data = $this->where($map)->find();

        if ($data) {
            return $data;
        }
        return false;
    }

    //得到流程的类型
    public function getName($kind,$scId){
        $map=array(
            'kind'=>$kind,
            'scId'=>$scId
        );
        $data=$this->where($map)->field('name')->select();
        return $data;
    }



}