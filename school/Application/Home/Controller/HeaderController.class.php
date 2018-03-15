<?php
namespace Home\Controller;
use Think\Controller;
ob_end_clean();
class HeaderController extends Base {
    private function toWeek($key){
        $array = array(
            0 => '星期一',
            1 => '星期二',
            2 => '星期三',
            3 => '星期四',
            4 => '星期五',
            5 => '星期六',
            6 => '星期天',
        );
        return $array[$key];
    }
    private function gradeToZhong($gradeName){
        $return = array(
            '1' => '一年级',
            '2' => '二年级',
            '3' => '三年级',
            '4' => '四年级',
            '5' => '五年级',
            '6' => '六年级',
            '7' => '初一',
            '8' => '初二',
            '9' => '初三',
            '10' => '高一',
            '11' => '高二',
            '12' => '高三',
        );
        return $return[$gradeName];
    }
    private function lessonSubstitute(){//调课代课
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];//6
        $userId=$getSession['userId'];//1971
        if(!M('adjustment_class_approve_user')->where(array('scId' => $scId,'userId' => $userId))->find()){
            $this->returnJson(array('statu' => 2,'没有权限'),true);
        }
        $startTime = $_GET['startTime'];
        $endTime = $_GET['endTime'];
        $data = M('adjustment_apply_record')->where(array('scId' => $scId, 'appoveStatu' => 0,'type' => array(array('eq', 0), array('eq', 1), array('eq', 3), 'or')))->select();
        foreach ($data as $key => $value) {
            $new = unserialize($value['new']);
            $old = unserialize($value['old']);
            $new = $new['time'] . $this->toWeek($new['y']) . '第' . ($new['x'] + 1) . '节' . $this->gradeToZhong($new['gradeName']) . $new['className'] . '班' . $old['subjectName'] . $old['teacherName'];
            $old = $old['time'] . $this->toWeek($old['y']) . '第' . ($old['x'] + 1) . '节' . $this->gradeToZhong($old['gradeName']) . $old['className'] . '班' . $old['subjectName'] . $old['teacherName'];
            $data[$key]['new'] = $new;
            $data[$key]['old'] = $old;
        }
        $valueData = $_GET['valueData'];
        if($valueData){
            $returnList = array();
            if($valueData){
                foreach($data as $key => $value){
                    $i = 0;
                    foreach($value as $key1 => $value1){
                        if(count(explode($valueData,$value1))>1){
                            $i++;
                        }
                    }
                    if($i>=1){
                        unset($value['scId']);
                        unset($value['createTime']);
                        unset($value['assetsNumber']);
                        unset($value['state']);
                        $returnList[] = $value;
                    }
                }
                $data = $returnList;
            }
        }
        $returnList = array();
        $sort = $_GET['sort'];
        $sortData = $_GET['sortData'];
        if($sort){
            $returnList = array();
            $data1 = $data;
            foreach ($data as $key => $value) {
                $max = array();
                $kk = 0;
                foreach ($data1 as $key1 => $value1) {
                    if ($value1[$sort] >= $max[$sort]) {
                        $max = $value1;
                        $kk = $key1;
                    }
                }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if ($sortData == 'inc') {
                krsort($returnList);
            }
            $data = $returnList;
        }
        foreach($data as $k=>$v){
            $datan[$k]['type']='调课代课';
            $datan[$k]['title']=$v['new'];
            $datan[$k]['createTime']=$v['createTime'];
            $datan[$k]['proposer']=$v['applicantName'];
        }
        return $datan;
    }
    public function upcoming(){//代办事项
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $userId=$getSession['userId'];
        $dao=M();
        $sql1="SELECT d.name as `type`,d.`title`,d.createTime,d.proposer,s.relationId,s.processType FROM `mks_document` AS d,`mks_process_detail` AS s WHERE s.`relationId`=d.`id` AND s.`processType`=4 AND s.`result`=5 AND s.`approveId`=".$userId." AND d.`scId`=".$scId;
        $sql2="SELECT l.name AS `type`,l.`title`,l.createTime,l.proposer,d.relationId,d.processType FROM mks_process_detail AS d,mks_teacher_leave AS l WHERE d.`relationId`=l.`id` AND d.`processType`=1 AND d.`result`=0 AND d.`approveId`=".$userId." AND d.`scId`=".$scId;
        $sql3="SELECT l.name AS `type`,l.`title`,l.createTime,l.proposer,d.relationId,d.processType FROM mks_process_detail AS d,mks_car_application AS l WHERE d.`relationId`=l.`id` AND d.`processType`=2 AND d.`result`=0 AND d.`approveId`=".$userId." AND d.`scId`=".$scId;
        $sql4="SELECT l.name AS `type`,l.`title`,l.createTime,l.proposer,d.relationId,d.processType FROM mks_process_detail AS d,mks_place_application AS l WHERE d.`relationId`=l.`id` AND d.`processType`=3 AND d.`result`=0 AND d.`approveId`=".$userId." AND d.`scId`=".$scId;
        $f1=$dao->query($sql1);
        $f2=$dao->query($sql2);
        $f3=$dao->query($sql3);
        $f4=$dao->query($sql4);
        foreach($f1 as $k=>$v){
            $v['type']='公文流转';
            $data[]=$v;
        }
        foreach($f2 as $k=>$v){
            $v['type']='教师请假';
            $data[]=$v;
        }
        foreach($f3 as $k=>$v){
            $v['type']='用车申请';
            $data[]=$v;
        }
        foreach($f4 as $k=>$v){
            $v['type']='场地申请';
            $data[]=$v;
        }
        $arr=$this->lessonSubstitute();
        foreach($arr as $k=>$v){
            $data[]=$v;
        }
        $this->ajaxReturn($data);
    }
}