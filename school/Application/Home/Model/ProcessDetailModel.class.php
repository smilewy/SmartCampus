<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/7/6
 * Time: 14:41
 */

namespace Home\Model;


use Think\Model;

class ProcessDetailModel extends Model
{
    public function createDetail($res, $process, $type, $scId)
    {
        // 保存具体流程

        if (empty($process)) {
            return false;
        }
        $process = json_decode($process['process'], true);

        /*$appId=array();
        foreach ($process as $k=>$v){
            foreach ($v['approveId'] as $k1=>$v1){
                $appId[]=$v1;
            }
        }*/
        $cnt = count($process); //环节总数
        $step = 1;//第一环节
        foreach ($process as $k => $v) {
            foreach ($v['approveId'] as $k1 => $v1) {  //当前环节的审批人
                $status = 0;
                if ($step == 1) {
                    $status = 5;  //第一环节的审批人 处于审批
                }

                // 过期时间线处理
                $limit = $v['limit'];
                $timeLine = null;
                if ($step == 1) {  //第一环节审批人设置时间线
                    if ($limit > 1) {
                        $timeLine = 0;
                    } else {
                        $timeLine = time() + $v['time'];
                    }
                }

                $detail[] = array(
                    'relationId' => $res['status'],
                    'processType' => $type,
                    'approveId' => $v1,
                    'approver' => $v['approver'][$k1],
                    'limitation' => $v['time'],
                    'step' => $step,
                    'totalStep' => $cnt,
                    'timeLine' => $timeLine,
                    'result' => $status,
                    'scId' => $scId
                );
            }
            $step++; //环节+1
        }

        $rs = $this->addAll($detail);
        if ($rs) {
            return $rs;
        } else {
            return -1;
        }
    }

    //自动过期
    public function aotuOverdue($id, $type, $kind, $scId)
    {
        $flag = false;
        //判断是查看记录 还是审批
        if ($kind == 'proposer') {
            $where = array(
                'relationId' => array('in', $id)
            );
        } elseif ($kind == 'approver') {
            $where = array(
                'approveId' => $id
            );
        }

        $where['processType'] = $type;
        $where['result'] = 5;
        $where['scId'] = $scId;
        $where['timeLine'] = array('gt', 0);

        $currentTime = time();
        $update = array(
            'result' => 2
        );
        $data = $this->where($where)->select();

        if ($data) {
            foreach ($data as &$v) {
                //过期处理
                $timeLine = $v['timeLine'];
                if ($timeLine < $currentTime) {
                    $result = $this->where("id={$v['id']}")->data($update)->save();
                    if ($result) {
                        $flag[] = $v['relationId'];
                    }
                }
            }
        }
        return $flag;
    }


    //通过或拒绝申请或取消申请
    public function operate($id, $type, $sort, $scId, $uid, $opinion)
    {

        $flag = false;

        $where = array(
            'relationId' => $id,
            'processType' => $type,
            'scId' => $scId,
            'approveId' => $uid,
            'result' => 5,
        );
        $detail = $this->where($where)->find();

        if (!$detail) {
            return $flag;
        }

        //审批通过 //已测试
        if ($sort == 1) {
            $status = 0;  // 申请是否进行修改 0 原申请状态无变化 1 状态变化成功 -1状态变为拒绝 3状态变为撤销
            $update = array(
                'result' => 1,
                'opinion' => $opinion,
                'approveTime' => time()
            );
            $rs = $this->where(array('id' => $detail['id']))->data($update)->save();

            if ($rs) {
                $map = array(
                    'relationId' => $id,
                    'processType' => $type,
                    'scId' => $scId,
                    'step' => $detail['step'],
                    'result' => 5
                );
                $temp = $this->where($map)->find();  //当前环节是否还有人未审批

                if ($detail['step'] != $detail['totalStep']) { //不是最后一个环节
                    if (!$temp) { //不存在   对下一环节进行操作
                        $where = array(
                            'relationId' => $id,
                            'processType' => $type,
                            'scId' => $scId,
                            'step' => (int)$detail['step'] + 1,
                            'result' => 0
                        );
                        $find=$this->where($where)->find();
                        $limitation=$find['limitation'];
                        $time=time();
                        $change = array(
                            'timeLine' =>$limitation>0?$limitation+$time:'',
                            'result' => 5,
                        );
                        $rs = $this->where($where)->save($change);

                       // $this->setDec()
                    }
                } else {//最后一个环节
                    if (!$temp) { //不存在
                        $status = 1;  //审批完成
                    }
                }
            }
        } elseif ($sort == 2) {//审批拒绝 //已测试
            $status = -1;
            $update = array(
                'result' => $status,
                'opinion' => $opinion,
                'approveTime' => time()
            );
            $rs = $this->where(array('id' => $detail['id']))->data($update)->save();
        } else {//取消申请
            $status = 0;
            $rs = false;
            /*$status = 3;
            $update = array(
                'result' => $status,
            );
            $rs = $this->where(array('id'=>$detail['id']))->data($update)->save();*/
        }

        if ($rs) {
            if ($status != 0) {
                $flag = array(
                    'id' => $id,
                    'status' => $status
                );
            } else {
                $flag = $rs;
            }

        }
        return $flag;
    }

//设置下一审批人
    public
    function setNext()
    {

    }

}