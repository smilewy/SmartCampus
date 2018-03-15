<?php
/**
 * Created by PhpStorm.
 * User: xiaolong
 * Date: 2017/6/22
 * Time: 14:15
 * 排课管理
 */
namespace Home\Controller;
//use Think\Controller;
//use Vendor\PHPExcel\PHPExcel;
class ClassreplacementController extends Base{
    public function statistics(){ //课时统计
        $type =  I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $usernamename = $jbXn['name'];
        if($type == 'getList'){
            $typeType =  I('get.typeType');
            if($typeType == 1){
                $startTime = I('get.startTime');
                $endTime = I('get.endTime');
                $week = date('W',strtotime($startTime));
                $ifWeek = 0;
                if($week%2 == 0){
                    $ifWeek = 2;
                }else{
                    $ifWeek = 1;
                }
                $all = M('pk_teacher_result')->where(array('scId' => $scId))->select();
                $allList = array();
                foreach($all as $key => $value){
                    $allList[$value['pkListId']][] = $value;
                }
                $pk = M('pk_list')->where(array('scId' => $scId,'ifStartUp' =>1))->select();
                $pkList = array();
                $allReall = array();
                $nowData = date('Y-m-d');
                foreach($pk as $key => $value){
                    if($value['startTime']<=$nowData && $value['endTime']>= $nowData){
                        if($value['ifWeek'] == $ifWeek || $value['ifWeek'] == 0){
                            if(isset($allList[$value['id']])){
                                $allReall[] = $allList[$value['id']];
                            }else{
                            }
                        }
                    }
                }
                $returnList = array();
                $ii = 0;
                foreach($allReall as $key => $value){
                    foreach($value as $key1 => $value1){
                        $data = unserialize($value1['classSet']);
                        $count = 0;
                        foreach($data as $key2 => $value2){
                            foreach($value2 as $key3 => $value3){
                                if(is_array($value3)){
                                    $returnList[$value1['teacherId']]['subjectName'] = $value3['subjectName'];
                                    if(isset($value3['gradeName'])){
                                        $returnList[$value1['teacherId']]['gradeName'][$value3['gradeName']] = $this->gradeToZhong($value3['gradeName']);
                                    }
                                    $returnList[$value1['teacherId']]['teacherId'] = $value1['teacherId'];
                                    $returnList[$value1['teacherId']]['teacherName'] = $value1['teacherName'];
                                    $returnList[$value1['teacherId']]['className'][$value3['className']] = $value3['className'];
                                    $count++;
                                }
                            }
                        }
                        $returnList[$value1['teacherId']]['count'] = $count;
                        $ii++;
                    }
                }
                $tz = M('adjustment_teacher_table')->where(array('scId' => $scId,'startTime'=> array('EGT',$startTime),'endTime' => array('EGT',$endTime)))->select();
                $tzRe = array();
                $iii = 0;
                foreach($tz as $key => $value){
                    $count =0;
                    $data = unserialize($value['classSet']);
                    if($data['statu'] == 1){
                        $count--;
                    }else{
                        $count++;
                    }
                    $tzRe[$value['teacherId']][] = $count;
                    $iii++;
                }
                $tz = array();
                foreach($tzRe as $key => $value){
                    $count = 0;
                    foreach($value as $key1 => $value1){
                        $count = $count+$value1;
                    }
                    $tz[$key]['count'] = $count;
                }
                /*foreach($tz as $key => $value){
                    $returnList[$key]['count'] = $returnList[$key]['count']+$value['count'];
                }*/
                rsort($returnList);
                $data = $returnList;
                $valueData = I('get.valueData');
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
                $sort = I('get.sort');
                $sortData = I('get.sortData');
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
                foreach($data as $key => $value){
                    $ggg = '';
                    $ccc = '';
                    foreach($value['gradeName'] as $key1 => $value1){
                        $ggg = $value1.'班'.$ggg;
                    }
                    $data[$key]['gradeName'] = $ggg;
                    foreach($value['className'] as $key1 => $value1){
                        $ccc = $ccc.$value1;
                    }
                    $data[$key]['className'] = $ccc;
                }
                $this->returnJson(array('statu' =>1 ,'data' => $data),true);
            }else{
                $startTime = I('get.startTime');
                $endTime = I('get.endTime');
                $tz = M('adjustment_teacher_table')->where(array('scId' => $scId,'ifStart' => 1,'startTime'=> array('EGT',$startTime),'endTime' => array('EGT',$endTime)))->select();
                $tzRe = array();
                $iii = 0;
                foreach($tz as $key => $value){
                    $count =0;
                    $data = unserialize($value['classSet']);
                    if($data['statu'] == 1){
                        $count++;
                    }else{
                        $count++;
                    }
                    $tzRe[$value['teacherId']]['count'][$value['type']][] = $count;
                    $tzRe[$value['teacherId']]['teacherName'] = $value['teacherName'];
                    if(isset($data['gradeName'])){
                        $tzRe[$value['teacherId']]['gradeName'][$data['gradeName']] = $this->gradeToZhong($data['gradeName']);
                    }
                    if(isset($data['subjectName'])){
                        $tzRe[$value['teacherId']]['subjectName'] = $data['subjectName'];
                    }
                    if(isset($data['subjectName'])){
                        $tzRe[$value['teacherId']]['className'][$data['className']] = $data['className'];
                    }
                    $iii++;
                }
                $arrarr = array();
                $iiii = 0;
                foreach($tzRe as $key => $value){
                    $arrarr[$iiii]['teacherName'] = $value['teacherName'];
                    $ggg = '';
                    foreach($value['gradeName'] as $key1 => $value1){
                        $ggg = $ggg.$value1;
                    }
                    $ccc = '';
                    foreach($value['className'] as $key1 => $value1){
                        $ccc = $ccc.$value1;
                    }
                    $arrarr[$iiii]['gradeName'] = $ggg;
                    $arrarr[$iiii]['subjectName'] = $value['subjectName'];
                    $arrarr[$iiii]['className'] = $ccc;
                    foreach($value['count'] as $key1 => $value1){
                        $countc = 0;
                        foreach($value1 as $key2 => $value2){
                            $countc = $countc+$value2;
                        }
                        if($key1 == 0 || $key1 == 1){
                            $arrarr[$iiii]['tk'] = $countc;
                        }else{
                            $arrarr[$iiii]['dk'] = $countc;
                        }
                    }
                    $iiii++;
                }
                foreach($arrarr as $key => $value){
                    if(!isset($value['tk'])){
                        $arrarr[$key]['tk'] = 0;
                    }
                    if(!isset($value['dk'])){
                        $arrarr[$key]['dk'] = 0;
                    }
                }
                $data = $arrarr;
                $valueData = I('get.valueData');
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
                $sort = I('get.sort');
                $sortData = I('get.sortData');
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
                $this->returnJson(array('statu' => 1, 'data' => $data),true);
            }

        }
        if($type == 'export') {
            $typeType = I('get.typeType');
            if ($typeType == 1) {
                $startTime = I('get.startTime');
                $endTime = I('get.endTime');
                $week = date('W', strtotime($startTime));
                $ifWeek = 0;
                if ($week % 2 == 0) {
                    $ifWeek = 2;
                } else {
                    $ifWeek = 1;
                }
                $all = M('pk_teacher_result')->where(array('scId' => $scId))->select();
                $allList = array();
                foreach ($all as $key => $value) {
                    $allList[$value['pkListId']][] = $value;
                }
                $pk = M('pk_list')->where(array('scId' => $scId, 'ifStartUp' => 1))->select();
                $pkList = array();
                $allReall = array();
                $nowData = date('Y-m-d');
                foreach($pk as $key => $value){
                    if($value['startTime']<=$nowData && $value['endTime']>= $nowData){
                        if ($value['ifWeek'] == $ifWeek || $value['ifWeek'] == 0){
                            if (isset($allList[$value['id']])) {
                                $allReall[] = $allList[$value['id']];
                            } else {
                            }
                        }
                    }
                }
                $returnList = array();
                $ii = 0;
                foreach ($allReall as $key => $value) {
                    foreach ($value as $key1 => $value1) {
                        $data = unserialize($value1['classSet']);
                        $count = 0;
                        foreach ($data as $key2 => $value2) {
                            foreach ($value2 as $key3 => $value3) {
                                if (is_array($value3)) {
                                    $returnList[$value1['teacherId']]['subjectName'] = $value3['subjectName'];
                                    $returnList[$value1['teacherId']]['gradeName'] = $this->gradeToZhong($value3['gradeName']);
                                    $returnList[$value1['teacherId']]['teacherId'] = $value1['teacherId'];
                                    $returnList[$value1['teacherId']]['teacherName'] = $value1['teacherName'];
                                    $count++;
                                }
                            }
                        }
                        $returnList[$value1['teacherId']]['count'] = $count;
                        $ii++;
                    }
                }
                $tz = M('adjustment_teacher_table')->where(array('scId' => $scId, 'startTime' => array('EGT', $startTime), 'endTime' => array('EGT', $endTime)))->select();
                $tzRe = array();
                $iii = 0;
                foreach ($tz as $key => $value) {
                    $count = 0;
                    $data = unserialize($value['classSet']);
                    if ($data['statu'] == 1) {
                        $count--;
                    } else {
                        $count++;
                    }
                    $tzRe[$value['teacherId']][] = $count;
                    $iii++;
                }
                $tz = array();
                foreach ($tzRe as $key => $value) {
                    $count = 0;
                    foreach ($value as $key1 => $value1) {
                        $count = $count + $value1;
                    }
                    $tz[$key]['count'] = $count;
                }
                /*foreach($tz as $key => $value){
                    $returnList[$key]['count'] = $returnList[$key]['count']+$value['count'];
                }*/
                rsort($returnList);
                $data = $returnList;
                $valueData = I('get.valueData');
                if ($valueData) {
                    $returnList = array();
                    if ($valueData) {
                        foreach ($data as $key => $value) {
                            $i = 0;
                            foreach ($value as $key1 => $value1) {
                                if (count(explode($valueData, $value1)) > 1) {
                                    $i++;
                                }
                            }
                            if ($i >= 1) {
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
                $sort = I('get.sort');
                $sortData = I('get.sortData');
                if ($sort) {
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
                $tr = array(
                    0 => array(
                        'en' => 'subjectName',
                        'zh' => '科目'
                    ),
                    1 => array(
                        'en' => 'gradeName',
                        'zh' => '年级'
                    ),
                    2 => array(
                        'en' => 'teacherName',
                        'zh' => '教师'
                    ),
                    3 => array(
                        'en' => 'count',
                        'zh' => '节数'
                    ),
                );
                $this->export($data,$tr);
            } else {
                $startTime = I('get.startTime');
                $endTime = I('get.endTime');
                $tz = M('adjustment_teacher_table')->where(array('scId' => $scId, 'ifStart' => 1, 'startTime' => array('EGT', $startTime), 'endTime' => array('EGT', $endTime)))->select();
                $tzRe = array();
                $iii = 0;
                foreach ($tz as $key => $value) {
                    $count = 0;
                    $data = unserialize($value['classSet']);
                    if ($data['statu'] == 1) {
                        $count++;
                    } else {
                        $count++;
                    }
                    $tzRe[$value['teacherId']]['count'][$value['type']][] = $count;
                    $tzRe[$value['teacherId']]['teacherName'] = $value['teacherName'];
                    $tzRe[$value['teacherId']]['gradeName'] = $this->gradeToZhong($data['gradeName']);
                    $tzRe[$value['teacherId']]['subjectName'] = $data['subjectName'];
                    $iii++;
                }
                $arrarr = array();
                $iiii = 0;
                foreach ($tzRe as $key => $value) {
                    $arrarr[$iiii]['teacherName'] = $value['teacherName'];
                    $arrarr[$iiii]['gradeName'] = $value['gradeName'];
                    $arrarr[$iiii]['subjectName'] = $value['subjectName'];
                    foreach ($value['count'] as $key1 => $value1) {
                        $countc = 0;
                        foreach ($value1 as $key2 => $value2) {
                            $countc = $countc + $value2;
                        }
                        if ($key1 == 0 || $key1 == 1) {
                            $arrarr[$iiii]['tk'] = $countc;
                        } else {
                            $arrarr[$iiii]['dk'] = $countc;
                        }
                    }
                    $iiii++;
                }
                foreach ($arrarr as $key => $value) {
                    if (!isset($value['tk'])) {
                        $arrarr[$key]['tk'] = 0;
                    }
                    if (!isset($value['dk'])) {
                        $arrarr[$key]['dk'] = 0;
                    }
                }
                $data = $arrarr;
                $valueData = I('post.valueData');
                if ($valueData) {
                    $returnList = array();
                    if ($valueData) {
                        foreach ($data as $key => $value) {
                            $i = 0;
                            foreach ($value as $key1 => $value1) {
                                if (count(explode($valueData, $value1)) > 1) {
                                    $i++;
                                }
                            }
                            if ($i >= 1) {
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
                $sort = I('get.sort');
                $sortData = I('get.sortData');
                if ($sort) {
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
                $tr = array(
                    0 => array(
                        'en' => 'subjectName',
                        'zh' => '科目'
                    ),
                    1 => array(
                        'en' => 'gradeName',
                        'zh' => '年级'
                    ),
                    2 => array(
                        'en' => 'teacherName',
                        'zh' => '教师'
                    ),
                    3 => array(
                        'en' => 'dk',
                        'zh' => '代课节数'
                    ),
                    4 => array(
                        'en' => 'tk',
                        'zh' => '调课节数'
                    ),
                );
                $this->export($data,$tr);
            }
        }
    }
    public function tkRecord(){//调课申请记录
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $usernamename = $jbXn['name'];
        if($type == 'getList'){
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            if(!$startTime){
                $startTime = '2010-01-01 01:02:22';
            }
            if(!$endTime){
                $endTime = date('Y-m-d H:i;s');
            }
            $data = M('adjustment_apply_record')->where(array('scId' => $scId,'applicantId' => $userId, 'type' => array(array('eq', 0), array('eq', 1), array('eq', 3),'or'), 'createTime' => array(array('EGT', $startTime), array('LT', $endTime), 'and')))->select();
            if($this::$adminRoleId == $userRoleId){
                $data = M('adjustment_apply_record')->where(array('scId' => $scId, 'type' => array(array('eq', 0), array('eq', 1),array('eq', 3), 'or'), 'createTime' => array(array('EGT', $startTime), array('LT', $endTime), 'and')))->select();
            }
            foreach ($data as $key => $value) {
                $new = unserialize($value['new']);
                $old = unserialize($value['old']);
                $new = $new['time'] . $this->toWeek($new['y']) . '第' . ($new['x'] + 1) . '节' . $this->gradeToZhong($new['gradeName']) . $new['className'] . '班' . $old['subjectName'] . $old['teacherName'];
                $old = $old['time'] . $this->toWeek($old['y']) . '第' . ($old['x'] + 1) . '节' . $this->gradeToZhong($old['gradeName']) . $old['className'] . '班' . $old['subjectName'] . $old['teacherName'];
                $data[$key]['new'] = $new;
                $data[$key]['old'] = $old;
            }
            $valueData = I('get.valueData');
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
            $sort = I('get.sort');
            $sortData = I('get.sortData');
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
            $this->returnJson(array('statu' => 1,'data' => $data),true);
        }
        if($type == 'delete'){
            $tkId = I('post.tkId');
            if(M('adjustment_apply_record')->where(array('tkId' => $tkId,'scId' => $scId,'appoveStatu' => 1))->delete()){
                $this->returnJson(array('statu' => 1,'message' => '删除成功'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => '删除失败'),true);
        }
        if($type == 'revoke'){
            $tkId = I('post.tkId');
            if(M('adjustment_apply_record')->where(array('tkId' => $tkId,'scId' => $scId,'appoveStatu' => 0))->delete()){
                M('adjustment_teacher_table')->where(array('tkId' => $tkId,'scId' => $scId))->delete();
                M('adjustment_class_table')->where(array('tkId' => $tkId,'scId' => $scId))->delete();
                $this->returnJson(array('statu' => 1,'message' => '撤销成功'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => '撤销失败'),true);
        }
        if($type == 'export'){
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            $data = M('adjustment_apply_record')->where(array('scId' => $scId, 'type' => array(array('eq', 0), array('eq', 1), array('eq', 3), 'or'), 'createTime' => array(array('EGT', $startTime), array('LT', $endTime), 'and')))->select();
            foreach ($data as $key => $value) {
                $new = unserialize($value['new']);
                $old = unserialize($value['old']);
                $new = $new['time'] . $this->toWeek($new['y']) . '第' . ($new['x'] + 1) . '节' . $this->gradeToZhong($new['gradeName']) . $new['className'] . '班' . $old['subjectName'] . $old['teacherName'];
                $old = $old['time'] . $this->toWeek($old['y']) . '第' . ($old['x'] + 1) . '节' . $this->gradeToZhong($old['gradeName']) . $old['className'] . '班' . $old['subjectName'] . $old['teacherName'];
                $data[$key]['new'] = $new;
                $data[$key]['old'] = $old;
                if($value['type'] == 0){
                    $data[$key]['type'] = '非指定调课';
                }
                if($value['type'] == 1){
                    $data[$key]['type'] = '指定调课';
                }
                if($value['result'] == 0){
                    $data[$key]['result'] = '同意';
                }
                if($value['result'] == 1){
                    $data[$key]['result'] = '不同意';
                }
                if($value['result'] == null){
                    $data[$key]['result'] = '待审批';
                }

            }
            $valueData = I('get.valueData');
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
            $sort = I('get.sort');
            $sortData = I('get.sortData');
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
            $tr = array(
                0 => array(
                    'en' => 'type',
                    'zh' => '类型'
                ),
                1 => array(
                    'en' => 'result',
                    'zh' => '审批结果'
                ),
                2 => array(
                    'en' => 'advice',
                    'zh' => '审批意见'
                ),
                3 => array(
                    'en' => 'applicantName',
                    'zh' => '申请人'
                ),
                4 => array(
                    'en' => 'appoveName',
                    'zh' => '审批人'
                ),
                5 => array(
                    'en' => 'appoveTime',
                    'zh' => '审批时间'
                ),
                6 => array(
                    'en' => 'createTime',
                    'zh' => '申请时间'
                ),
            );
            $this->export($data,$tr);
        }
    }
    public function dkRecord(){//代课申请记录
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $usernamename = $jbXn['name'];
        if($type == 'getList'){
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            if(!$startTime){
                $startTime = '2010-01-01 01:02:22';
            }
            if(!$endTime){
                $endTime = date('Y-m-d H:i;s');
            }
            $data = M('adjustment_apply_record')->where(array('scId' => $scId,'applicantId' => $userId,'type' => array('eq',2),'createTime' => array(array('EGT',$startTime),array('LT',$endTime),'and')))->select();
            if($this::$adminRoleId == $userRoleId){
                $data = M('adjustment_apply_record')->where(array('scId' => $scId,'type' => array('eq',2),'createTime' => array(array('EGT',$startTime),array('LT',$endTime),'and')))->select();
            }
            foreach($data as $key => $value) {
                $old = unserialize($value['old']);
                $data[$key]['haveTime'] = $old['startTime'] .'--'.$old['endTime'];
                $data[$key]['jie'] = $old['time'] .' '. $this->toWeek($old['y']) . ' 第' . ($old['x'] + 1) . '节';
                $old = $old['time'] .' '.$this->toWeek($old['y']) . ' 第' . ($old['x'] + 1) . '节 ' . $this->gradeToZhong($old['gradeName']) . $old['className'] . '班 ' . $old['subjectName'] .' '. $old['teacherName'];
                $data[$key]['old'] = $old;
            }
            $valueData = I('get.valueData');
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
            $sort = I('get.sort');
            $sortData =I('get.sortData');
            if($sort){
                $returnList = array();
                $data1 = $data;
                foreach ($data as $key => $value) {
                    $max = array();
                    $kk = 0;
                    foreach ($data1 as $key1 => $value1) {
                        if ($value1[$sort] >= $max[$sort]){
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
            $this->returnJson(array('statu' => 1, 'data' => $data),true);
        }
        if($type == 'export'){
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            $data = M('adjustment_apply_record')->where(array('scId' => $scId,'applicantId' => $userId,'type' => array('eq',2),'createTime' => array(array('EGT',$startTime),array('LT',$endTime),'and')))->select();
            foreach($data as $key => $value) {
                $old = unserialize($value['old']);
                $data[$key]['haveTime'] = $old['startTime'] .'--'.$old['endTime'];
                $data[$key]['jie'] = $old['time'] .' '. $this->toWeek($old['y']) . ' 第' . ($old['x'] + 1) . '节';
                $old = $old['time'] .' '.$this->toWeek($old['y']) . ' 第' . ($old['x'] + 1) . '节 ' . $this->gradeToZhong($old['gradeName']) . $old['className'] . '班 ' . $old['subjectName'] .' '. $old['teacherName'];
                $data[$key]['old'] = $old;
                if($value['result'] == 0){
                    $data[$key]['result'] = '同意';
                }
                if($value['result'] == 1){
                    $data[$key]['result'] = '不同意';
                }
                if($value['result'] == null){
                    $data[$key]['result'] = '待审批';
                }
            }
            $valueData =  I('get.valueData');
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
            $sort = I('get.sort');
            $sortData =I('get.sortData');
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
            $tr = array(
                0 => array(
                    'en' => 'old',
                    'zh' => '代课节数'
                ),
                1 => array(
                    'en' => 'haveTime',
                    'zh' => '有效期'
                ),
                2 => array(
                    'en' => 'applicantName',
                    'zh' => '代课老师'
                ),
                3 => array(
                    'en' => 'result',
                    'zh' => '审批结果'
                ),
                4 => array(
                    'en' => 'advice',
                    'zh' => '审批意见'
                ),
                5 => array(
                    'en' => 'appoveName',
                    'zh' => '审批人'
                ),
                6 => array(
                    'en' => 'createTime',
                    'zh' => '申请时间'
                ),
            );
            $this->export($data,$tr);
        }
        if($type == 'delete'){
            $tkId = I('post.tkId');
            if(M('adjustment_apply_record')->where(array('tkId' => $tkId,'scId' => $scId,'appoveStatu' => 1,'type' => 2))->delete()){
                $this->returnJson(array('statu' => 1,'message' => '删除成功'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => '删除失败'),true);
        }
        if($type == 'revoke'){
            $tkId = I('post.tkId');
            if(M('adjustment_apply_record')->where(array('tkId' => $tkId,'scId' => $scId,'appoveStatu' => 0,'type' => 2))->delete()){
                M('adjustment_teacher_table')->where(array('tkId' => $tkId,'scId' => $scId))->delete();
                M('adjustment_class_table')->where(array('tkId' => $tkId,'scId' => $scId))->delete();
                $this->returnJson(array('statu' => 1,'message' => '撤销成功'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => '撤销失败'),true);
        }
    }
    public function dKapprover(){//代课申请
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $usernamename = $jbXn['name'];
        if($type == 'getTeacher'){
            $data = M('')->query("SELECT * FROM mks_jw_schedule WHERE scId=$scId GROUP BY techerId,subjectId");
            $xxx = array();
            $sss = array();
            foreach($data as $key => $value){
                if($value['techerId'] == $userId){
                    $xxx[$value['subjectId']] = 1;
                    $sss[$value['gradeId']] = 1;
                }
            }
            $dataList = array();
            foreach($data as $key => $value){
                if(isset($xxx[$value['subjectId']]) && isset($sss[$value['gradeId']])){
                    $dataList[$value['subjectId']]['data'][] = $value;
                    $dataList[$value['subjectId']]['techerName'] = $value['subject'];
                }
            }
            rsort($dataList);
            $this->returnJson(array('statu' => 1,'message' => 'success','data' => $dataList),true);
        }
        if($type == 'submit'){
            $data = I('get.data');
            if($data['teacherId'] == $userId){
                $this->returnJson(array('statu' => 3,'message' =>'不能给自己代课'),true);
            }
            $subject = M('jw_schedule')->where(array('scId' => $scId,'techerId' => $userId))->select();
            $check = 0;
            foreach($subject as $key => $value){
                if($value['subjectId'] == $data['subjectId']){
                    $check = 1;
                    break;
                }
            }
            if(!$check){
                $this->returnJson(array('statu' => 4,'message' =>'不能跨科目代课'),true);
            }
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            $sxStartTime = I('get.sxStartTime');
            $sxEndTime = I('get.sxEndTime');
            if(!$_GET['sxStartTime']){
                $sxStartTime = $startTime;
                $sxEndTime = $endTime;
            }
            $pkpkId = 0;
            //
            $week = date('W',strtotime($startTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $classData = array();
            $teacherTable  = array();
            $relTable = array();
            $rrrweek = 0;
            $hhh = M('adjustment_teacher_table')->where(array('teacherId' =>$userId,'scId' => $scId,'startTime'=> array('EGT',$startTime),'endTime' => array('EGT',$sxEndTime)))->select();
            $teaData = M('pk_teacher_result')->where(array('scId' => $scId,'teacherId' => $userId))->select();
            $nowDate = date('Y-m-d');
            foreach($teaData as $key => $value){
                $pkListId =$value['pkListId'];
                $pkList = M('pk_list')->where(array('scId' => $scId,'id' => $pkListId,'ifStartUp' => 1))->find();
                $checkchchch = 0;
                if($pkList['startTime']<=$nowDate && $pkList['endTime']>=$nowDate){
                    $checkchchch = 1;
                }
                //
                if($pkList['ifWeek'] == 0 ||$pkList['ifWeek'] == $ifWeek &&$checkchchch){
                    $rrrweek = $pkList['ifWeek'];
                    $relTable = unserialize($value['classSet']);
                    $pkpkId = $pkListId;
                    break;
                }
            }
            foreach($hhh as $key => $value){
                if(is_array($value)){
                    $relTable[$value['x']][$value['y']] = $value;
                    unset( $relTable[$value['x']][$value['y']]['x']);
                    unset( $relTable[$value['x']][$value['y']]['y']);
                }
            }
            $teaData = unserialize($teaData['classSet']);
            $teacherTable[0] = $teaData;
            foreach($hhh as $key1 => $value1){
                if ($value1['statu'] == 1) {
                    $value1 = 1;
                    $teacherTable[0][$value1['x']][$value1['y']] = $value1;
                } else {
                    $teacherTable[0][$value1['x']][$value1['y']] = $value1;
                    unset($teacherTable[0][$value1['x']][$value1['y']]['x']);
                    unset($teacherTable[0][$value1['x']][$value1['y']]['y']);
                }
            }
            $trueOrFalse = 1;
            if($relTable[$data['x']][$data['y']] == 0 ||is_array($relTable[$data['x']][$data['y']])){
                $trueOrFalse = 0;
                $this->returnJson(array('statu' => 5,'message' =>'您在这节课已有排课'),true);
            }
            if($trueOrFalse){
                $data['endTime'] = $sxEndTime;
                if($tkId = M('adjustment_apply_record')->add(array(
                    'applicantId' => $userId,
                    'scId' => $scId,
                    'createTime' => date('Y-m-d H:i:s'),
                    'appoveStatu' => 0,
                    'old' => serialize($data),
                    'type'=> 2,
                    'applicantName' => $usernamename
                ))){
                    if(M('adjustment_teacher_table')->add(
                        array(
                            'teacherId' =>$userId,
                            'teacherName' => $usernamename,
                            'classSet' => serialize(array(
                                'subjectId' => $data['subjectId'],
                                'subjectName' => $data['subjectName'],
                                'statu' =>5,
                                'gradeId' => $data['gradeId'],
                                'classId' => $data['classId'],
                                'className' => $data['className'],
                                'gradeName' => $data['gradeName'],
                                'x' => $data['x'],
                                'y' => $data['y']
                            )),
                            'createTime' => date('Y-m-d H:i:s'),
                            'scId' => $scId,
                            'startTime' =>$startTime,
                            'endTime' => $sxEndTime,
                            'ifWeek' => $data['ifWeek'],
                            'pkListId' => $data['pkListId'],
                            'ifStart' => 0,
                            'tkId' => $tkId,
                            'type'=> 2,
                        )
                    )){
                        if(M('adjustment_teacher_table')->add(
                            array(
                                'teacherId' =>$data['teacherId'],
                                'teacherName' => $data['teacherName'],
                                'classSet' => serialize(array(
                                    'statu' =>1,
                                    'x' => $data['x'],
                                    'y' => $data['y'],
                                )),
                                'createTime' => date('Y-m-d H:i:s'),
                                'scId' => $scId,
                                'startTime' =>$startTime,
                                'endTime' => $sxEndTime,
                                'ifWeek' => $data['ifWeek'],
                                'pkListId' => $data['pkListId'],
                                'ifStart' => 0,
                                'tkId' => $tkId,
                                'type'=> 2,
                            )
                        )){
                            $this->returnJson(array('statu' => 1,'message' => '代课申请成功'),true);
                        }
                        $this->returnJson(array('statu' => 0,'message' => '代课申请失败'),true);
                    }
                    $this->returnJson(array('statu' => 0,'message' => '代课申请失败'),true);
                }else{
                    $this->returnJson(array('statu' => 0,'message' => '代课申请失败'),true);
                }
            }else{
                $this->returnJson(array('statu' => 0,'message' => '代课申请有冲突'),true);
            }
        }
        if($type == 'getTeacherTable'){
            $id = I('get.techerId');
            if($id){
                $userId = $id;
            }
            $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $userId))->find();
            $userName = $userName['name'];
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            $week = date('W',strtotime($startTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $check = 0;
            $pkpkId = 0;
            $rrrweek = '';
            $hhh = array();
            if($data = M('adjustment_teacher_table')->where(array('scId' => $scId,'ifStart' => 1,'teacherId' => $userId,'startTime'=> array('EGT',$startTime),'endTime' => array('ELT',$endTime)))->select()){
                $rrrweek = $data['ifWeek'];
                foreach($data as $key => $value){
                    $hhh[] = unserialize($value['classSet']);
                }
            }
            $table = M('pk_teacher_result')->where(array('teacherId' => $userId, 'scId' => $scId))->select();
            $relTable = array();
            $nowDate11 = date('Y-m-d');
            foreach($table as $key => $value){
                $pkListId =$value['pkListId'];
                $pkList = M('pk_list')->where(array('scId' => $scId,'id' => $pkListId,'ifStartUp' => 1))->find();
                $checkchchch = 0;
                if($pkList['startTime']<=$nowDate11 && $pkList['endTime']>=$nowDate11){
                    $checkchchch = 1;
                }
                if($pkList['ifWeek'] == 0 ||$pkList['ifWeek'] == $ifWeek   &&$checkchchch){
                    $rrrweek = $pkList['ifWeek'];
                    $startTimePk = $pkList['startTime'];
                    $endTimePk= $pkList['endTime'];
                    $relTable = unserialize($value['classSet']);
                    $pkpkId = $pkListId;
                    break;
                }
            }
            foreach($hhh as $key => $value){
                if($value == 1){

                }else{
                    $relTable[$value['x']][$value['y']] = $value;
                    unset( $relTable[$value['x']][$value['y']]['x']);
                    unset( $relTable[$value['x']][$value['y']]['y']);
                }
            }
            $return = array();
            $ii = 1;
            $subjectIdgo = array();
            $timeCheck = 0;
            $nowTime = date('Y-m-d');
            foreach($relTable as $key => $value){
                $return[$key][0]['subjectName'] = '第'.$ii.'节';
                $jj = 1;
                $zzz = 0;
                foreach($value as $key1 => $value1){
                    $time = date('Y-m-d',strtotime($startTime)+24*3600*$zzz);
                    if(($time<$nowTime) || ($time>$endTimePk)){
                        $timeCheck = 0;
                    }else{
                        $timeCheck = 1;
                    }
                    $zzz++;
                    if(is_array($value1)){
                        $subjectIdgo[$value1['subjectId']] = 1;
                        $value1['x'] = $key;
                        $value1['y'] = $key1;
                        $value1['teacherName'] = $userName;
                        $value1['teacherId'] = $userId;
                        $value1['pkListId'] = $pkpkId;
                        $value1['startTime'] = $startTime;
                        $value1['endTime'] = $endTime;
                        $value1['ifWeek'] = $rrrweek;
                        $value1['time'] =$time;
                        $value1['checkd'] = $timeCheck;
                        $value1['show'] = $this->toWeek($key1).' '. '第'.$ii.'节 '.$value1['subjectName'];
                        $return[$key][$jj] = $value1;
                    }else{
                        $sst = 0;
                        if($value1 == 0){
                            $sst = '不上课';
                        }else{
                            $sst = '';
                        }
                        $return[$key][$jj] = array(
                            'x' => $key,
                            'y' => $key1,
                            'subjectName' => $sst,
                            'show' =>'',
                            'statu' => (int)$value1,
                            'ifWeek' => '',
                            'teacherName' => '',
                            'teacherId' => $userId,
                            'subjectId' => '',
                            'gradeId' => '',
                            'gradeName' => '',
                            'classId' => '',
                            'className' => '',
                            'pkListId' => $pkpkId,
                            'startTime' =>$startTime,
                            'endTime' => $endTime,
                            'time' => $time,
                            'checkd' => $timeCheck
                        );
                    }
                    $jj++;
                }
                $ii++;
            }
            $this->returnJson(array('statu' =>1,'startTimePk' => $startTimePk,'endTimePk' => $endTimePk,'data' => $return),true);
        }
    }
    public function dKsP(){//代课审批
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $usernamename = $jbXn['name'];
        if($type == 'getNosp'){
            if(!M('adjustment_class_approve_user')->where(array('scId' => $scId,'userId' => $userId))->find()){
                $this->returnJson(array('statu' => 2,'没有权限'),true);
            }
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            if(!$startTime){
                $startTime = '2010-01-01 01:02:22';
            }
            if(!$endTime){
                $endTime = date('Y-m-d H:i;s');
            }
            $data = M('adjustment_apply_record')->where(array('scId' => $scId,'appoveStatu' => 0,'type' => array('eq',2),'createTime' => array(array('EGT',$startTime),array('LT',$endTime),'and')))->select();
            foreach($data as $key => $value) {
                $old = unserialize($value['old']);
                $data[$key]['haveTime'] = $old['startTime'] .'--'.$old['endTime'];
                $data[$key]['jie'] = $old['time'] .' '. $this->toWeek($old['y']) . ' 第' . ($old['x'] + 1) . '节'.$old['teacherName'];
                $old = $old['time'] .' '.$this->toWeek($old['y']) . ' 第' . ($old['x'] + 1) . '节 ' . $this->gradeToZhong($old['gradeName']) . $old['className'] . '班 ' . $old['subjectName'] .' '. $old['teacherName'];
                $data[$key]['old'] = $old;
            }
            $valueData =  I('get.valueData');
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
            $sort = I('get.sort');
            $sortData =I('get.sortData');
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
            $this->returnJson(array('statu' =>1 ,'data' => $data),true);
        }
        if($type == 'getHavesp'){
            if(!M('adjustment_class_approve_user')->where(array('scId' => $scId,'userId' => $userId))->find()){
                $this->returnJson(array('statu' => 2,'没有权限'),true);
            }
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            $data = M('adjustment_apply_record')->where(array('scId' => $scId,'appoveStatu' => 1,'type' => array('eq',2),'createTime' => array(array('EGT',$startTime),array('LT',$endTime),'and')))->select();
            foreach($data as $key => $value) {
                $old = unserialize($value['old']);
                $data[$key]['haveTime'] = $old['startTime'] .'--'.$old['endTime'];
                $data[$key]['jie'] = $old['time'] .' '. $this->toWeek($old['y']) . ' 第' . ($old['x'] + 1) . '节 '.$old['teacherName'];
                $old = $old['time'] .' '.$this->toWeek($old['y']) . ' 第' . ($old['x'] + 1) . '节 ' . $this->gradeToZhong($old['gradeName']) . $old['className'] . '班 ' . $old['subjectName'] .' '. $old['teacherName'];
                $data[$key]['old'] = $old;
            }
            $valueData =  I('get.valueData');
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
            $sort = I('get.sort');
            $sortData =I('get.sortData');
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
            $this->returnJson(array('statu' =>1 ,'data' => $data),true);
        }
        if($type == 'getAll'){
            if(!M('adjustment_class_approve_user')->where(array('scId' => $scId,'userId' => $userId))->find()){
                $this->returnJson(array('statu' => 2,'没有权限'),true);
            }
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            $data = M('adjustment_apply_record')->where(array('scId' => $scId,'type' => array('eq',2),'createTime' => array(array('EGT',$startTime),array('LT',$endTime),'and')))->select();
            foreach($data as $key => $value) {
                $old = unserialize($value['old']);
                $data[$key]['haveTime'] = $old['startTime'] .'--'.$old['endTime'];
                $data[$key]['jie'] = $old['time'] .' '. $this->toWeek($old['y']) . ' 第' . ($old['x'] + 1) . '节';
                $old = $old['time'] .' '.$this->toWeek($old['y']) . ' 第' . ($old['x'] + 1) . '节 ' . $this->gradeToZhong($old['gradeName']) . $old['className'] . '班 ' . $old['subjectName'] .' '. $old['teacherName'];
                $data[$key]['old'] = $old;
            }
            $valueData =  I('get.valueData');
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
            $sort = I('get.sort');
            $sortData =I('get.sortData');
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
            $this->returnJson(array('statu' =>1 ,'data' => $data),true);
        }
        if($type == 'approval'){
            if(!M('adjustment_class_approve_user')->where(array('scId' => $scId,'userId' => $userId))->find()){
                $this->returnJson(array('statu' => 2,'没有权限'),true);
            }
            $advice = I('get.advice');
            $result = I('get.result');
            $tkId = I('get.tkId');
            if(M('adjustment_apply_record')->where(array('scId' => $scId,'tkId' => $tkId,'type' => 2))->setField(array(
                'appoveTime' => date('Y-m-d H:i:s'),
                'result' => $result,
                'advice' => $advice,
                'appoveUserId' => $userId,
                'appoveStatu' => 1,
                'appoveName' => $usernamename
            ))){
                if($result == 1){
                    M('adjustment_teacher_table')->where(array('scId' => $scId,'tkId' => $tkId))->setField(array('ifStart' => 1));
                    //M('adjustment_class_table')->where(array('scId' => $scId,'tkId' => $tkId))->setField(array('ifStart' => 1));
                }
                $this->returnJson(array('statu' =>1,'message' => 'success'),true);
            }
            $this->returnJson(array('statu' =>0,'message' => 'fail'),true);
        }
    }
    public function tkApprover()
        {//调课审批
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $usernamename = $jbXn['name'];
        if($type == 'haveApprover'){
            /*
             * 审批人
             * */
            if(!M('adjustment_class_approve_user')->where(array('scId' => $scId,'userId' => $userId))->find()){
                $this->returnJson(array('statu' => 2,'没有权限'),true);
            }
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            if(!$startTime){
                $startTime = '2010-01-01 01:02:22';
            }
            if(!$endTime){
                $endTime = date('Y-m-d H:i;s');
            }
            $data = M('adjustment_apply_record')->where(array('scId' => $scId, 'appoveStatu' => 1, 'type' => array(array('eq', 0), array('eq', 1), array('eq', 3), 'or'), 'createTime' => array(array('EGT', $startTime), array('LT', $endTime), 'and')))->select();
            foreach ($data as $key => $value) {
                $new = unserialize($value['new']);
                $old = unserialize($value['old']);
                $new = $new['time'] . $this->toWeek($new['y']) . '第' . ($new['x'] + 1) . '节' . $this->gradeToZhong($new['gradeName']) . $new['className'] . '班' . $new['subjectName'] . $new['teacherName'];
                $old = $old['time'] . $this->toWeek($old['y']) . '第' . ($old['x'] + 1) . '节' . $this->gradeToZhong($old['gradeName']) . $old['className'] . '班' . $old['subjectName'] . $old['teacherName'];
                $data[$key]['new'] = $new;
                $data[$key]['old'] = $old;
            }
            $valueData =  I('get.valueData');
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
            $sort = I('get.sort');
            $sortData =I('get.sortData');
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
            $this->returnJson(array('statu' => 1, 'data' => $data),true);
        }
        if ($type == 'noApprover') {
            if(!M('adjustment_class_approve_user')->where(array('scId' => $scId,'userId' => $userId))->find()){
                $this->returnJson(array('statu' => 2,'没有权限'),true);
            }
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            if(!$startTime){
                $startTime = '2010-01-01 01:02:22';
            }
            if(!$endTime){
                $endTime = date('Y-m-d H:i;s');
            }
            $data = M('adjustment_apply_record')->where(array('scId' => $scId, 'appoveStatu' => 0,'type' => array(array('eq', 0), array('eq', 1), array('eq', 3), 'or'), 'createTime' => array(array('EGT', $startTime), array('LT', $endTime), 'and')))->select();
            $dataRe = array();
            foreach ($data as $key => $value) {
                if($value['new'] && $value['old']){
                    $new = unserialize($value['new']);
                    $old = unserialize($value['old']);
                    $new = $new['time'] . $this->toWeek($new['y']) . '第' . ($new['x'] + 1) . '节' . $this->gradeToZhong($new['gradeName']) . $new['className'] . '班' . $new['subjectName'] .'-'. $new['teacherName'];
                    $old = $old['time'] . $this->toWeek($old['y']) . '第' . ($old['x'] + 1) . '节' . $this->gradeToZhong($old['gradeName']) . $old['className'] . '班' . $old['subjectName'] .'-'. $old['teacherName'];
                    $data[$key]['new'] = $new;
                    $data[$key]['old'] = $old;
                    $dataRe[] = $data[$key];
                }
            }
            $data = $dataRe;
            $valueData =  I('get.valueData');
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
            $sort = I('get.sort');
            $sortData =I('get.sortData');
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
            $this->returnJson(array('statu' => 1, 'data' => $data),true);
        }
        if ($type == 'approval'){
            if(!M('adjustment_class_approve_user')->where(array('scId' => $scId,'userId' => $userId))->find()){
                $this->returnJson(array('statu' => 2,'没有权限'),true);
            }
            $advice = I('get.advice');
            $result = I('get.result');
            $tkId = I('get.tkId');
            if (M('adjustment_apply_record')->where(array('scId' => $scId, 'tkId' => $tkId))->setField(array(
                'appoveTime' => date('Y-m-d H:i:s'),
                'result' => $result,
                'advice' => $advice,
                'appoveUserId' => $userId,
                'appoveStatu' => 1,
                'appoveName' => $usernamename
            ))) {
                if ($result == 1) {
                    M('adjustment_teacher_table')->where(array('scId' => $scId, 'tkId' => $tkId))->setField(array('ifStart' => 1));
                    M('adjustment_class_table')->where(array('scId' => $scId, 'tkId' => $tkId))->setField(array('ifStart' => 1));
                }
                $this->returnJson(array('statu' => 1, 'message' => 'success'), true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'), true);
        }
        if ($type == 'allApprover') {
            if(!M('adjustment_class_approve_user')->where(array('scId' => $scId,'userId' => $userId))->find()){
                $this->returnJson(array('statu' => 2,'没有权限'),true);
            }

            $startTime = I('get.startTime');
            $endTime = I('get.endTime');
            if(!I('get.startTime')){
                $startTime = '2014-02-01';
            }
            if(!I('get.endTime')){
                $endTime = '2056-02-01';
            }
            $data = M('adjustment_apply_record')->where(array('scId' => $scId, 'createTime' => array(array('EGT', $startTime), array('LT', $endTime), 'and')))->select();
            foreach ($data as $key => $value) {
                $new = unserialize($value['new']);
                $old = unserialize($value['old']);
                $new = $new['time'] . $this->toWeek($new['y']) . '第' . ($new['x'] + 1) . '节' . $this->gradeToZhong($new['gradeName']) . $new['className'] . '班' . $old['subjectName'] . $old['teacherName'];
                $old = $old['time'] . $this->toWeek($old['y']) . '第' . ($old['x'] + 1) . '节' . $this->gradeToZhong($old['gradeName']) . $old['className'] . '班' . $old['subjectName'] . $old['teacherName'];
                $data[$key]['new'] = $new;
                $data[$key]['old'] = $old;
            }
            $valueData =  I('get.valueData');
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
            $sort = I('get.sort');
            $sortData =I('get.sortData');
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
            $this->returnJson(array('statu' => 1, 'data' => $data),true);
        }
    }
    public function approverSet(){//审批设置
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getApproverUser'){
            $studentRole = $this::$studentRoleId;
            $jzRole = $this::$jZroleId;
            $adminRole = $this::$adminRoleId;
            $user = M('')->query("select id,name,roleId,departmentId,department,post from mks_user where scId= $scId AND roleId!=$studentRole AND roleId!=$jzRole AND roleId!=$adminRole");
            $roleUser = array();
            foreach($user as $key => $value){
                if(!$value['department']){
                    $roleUser[$value['departmentId']]['name'] = '其他职工';
                }else{
                    $roleUser[$value['departmentId']]['name'] = $value['department'];
                }
                $value['name'] = $value['name'].'('.$value['post'].')';
                $roleUser[$value['departmentId']]['data'][] = $value;
            }
            $return = array();
            foreach($roleUser as $key => $value){
                $return[] = $value;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
        }
        if($type == 'createApproverUser'){
            /*$user = array(
                0 =>array('id' => 18,'name' => '小江' ),
                1 =>array('id' => 92,'name' => '李四ha 0116' ),
                2 =>array('id' => 95,'name' => '李四四' ),
            );*/
            $user = I('post.user');
            M('adjustment_class_approve_user')->where(array('scId' => $scId))->delete();
            $ii = 0;
            foreach($user as $key => $value){
                $value['scId'] = $scId;
                $value['userId'] = $value['id'];
                unset($value['id']);
                unset($value['roleId']);
                $value['createTime'] = date('Y-m-d H:i:s');
                unset($value['departmentId']);
                unset($value['department']);
                unset($value['post']);
                if(M('adjustment_class_approve_user')->add($value)){
                    $ii = 1;
                }
            }
            if($ii){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'getApproverUserList'){
            $user = M('adjustment_class_approve_user')->field('userId,name')->where(array('scId' => $scId))->select();
            foreach($user as $key => $value){
                $user[$key]['id'] = $value['userId'];
                unset($user[$key]['userId']);
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $user),true);
        }
    }
    public function teacherTk(){ //教师调课
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'teacherZd'){//教师指定调课
            $id = I('get.techerId');
            $zdOrNo = I('get.zdOrNo');
            if($id){
                $userId = $id;
            }
            $userName = M('user')->field('name')->where(array('scId' => $scId,'id' => $userId))->find();
            $userName = $userName['name'];
            $gradeId = I('get.gradeId');
            $classId = I('get.classId');
            $startTime = I('get.startTime');
            $endTime = I('get.endTime');

            $week = date('W',strtotime($startTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $check = 0;
            $pkpkId = 0;
            $rrrweek = '';
            $hhh = array();
            if($data = M('adjustment_teacher_table')->where(array('scId' => $scId,'teacherId' => $userId,'startTime'=> array('EGT',$startTime),'endTime' => array('ELT',$endTime)))->order('id desc')->select()){
                $rrrweek = $data['ifWeek'];
                foreach($data as $key => $value){
                    if($value['ifStart'] == 1 && $value['ifNo'] == 0){
                        $hhh[] = unserialize($value['classSet']);
                    }
                    if($zdOrNo){
                        if($value['ifStart'] == 0 && $value['ifNo'] == 1){
                            $hhh[] = unserialize($value['classSet']);
                        }
                    }
                }
            }
            $table = M('pk_teacher_result')->where(array('teacherId' => $userId, 'scId' => $scId))->select();
            $relTable = array();
            $endTimePk = 0;
            $startTimePk = 0;
            $nowDate11 = date('Y-m-d');
            foreach($table as $key => $value){
                $pkListId =$value['pkListId'];
                $pkList = M('pk_list')->where(array('scId' => $scId,'id' => $pkListId,'ifStartUp' => 1))->find();
                $gradeddd = explode(',',$pkList['pkRange']);
                $checkchchch = 0;
                foreach($gradeddd as $key2 => $value2){
                    if($value2 == $gradeId){
                        if($pkList['startTime']<=$nowDate11 && $pkList['endTime']>=$nowDate11){
                            $checkchchch = 1;
                        }
                    }
                }
                if(($pkList['ifWeek'] == 0 ||$pkList['ifWeek'] == $ifWeek) && $checkchchch ){
                    $rrrweek = $pkList['ifWeek'];
                    $relTable = unserialize($value['classSet']);
                    $startTimePk = $pkList['startTime'];
                    $endTimePk= $pkList['endTime'];
                    $pkpkId = $pkListId;
                    break;
                }
            }
            foreach($hhh as $key => $value){
                if(is_array($value)){
                    $relTable[$value['x']][$value['y']] = $value;
                    unset( $relTable[$value['x']][$value['y']]['x']);
                    unset( $relTable[$value['x']][$value['y']]['y']);
                }
            }
            $return = array();
            $ii = 1;
            $subjectIdgo = array();
            $timeCheck = 0;
            $nowTime = date('Y-m-d');
            $hb =  $this->hbToData($this->hb($pkListId));//合班
            $dataLL = array();
            foreach($hb as $key => $value){
                if($value['teacherId'] == $userId){
                    foreach($value['data'] as $key1 => $value1){
                        $dataLL[$key][] = $value1;
                    }
                }
            }
            $array = array();
            $strs = '';
            foreach($dataLL as $key => $value){
                foreach($value as $key1 => $value1){
                    if($value1['gradeId'] == $gradeId && $value1['classId'] == $classId){
                        foreach($value as $key2 => $value2){
                            $array[] = $value2['classId'];
                            $strs.=$value2['className'].'班';
                        }
                    }
                }
            }
            if(count($array)){

            }else{
                $array[] = $classId;
            }
            foreach($relTable as $key => $value){
                $return[$key][0]['subjectName'] = '第'.$ii.'节';
                $jj = 1;
                $zzz = 0;
                foreach($value as $key1 => $value1){
                    $time = date('Y-m-d',strtotime($startTime)+24*3600*$zzz);
                    if(($time<$nowTime) || ($time>$endTimePk)){
                        $timeCheck = 0;
                    }else{
                        $timeCheck = 1;
                    }
                    $zzz++;
                    if(is_array($value1)){
                        if($value1['gradeId'] == $gradeId && in_array($value1['classId'],$array)){
                            $subjectIdgo[$value1['subjectId']] = 1;
                            $value1['x'] = $key;
                            $value1['y'] = $key1;
                            $value1['teacherName'] = $userName;
                            $value1['teacherId'] = $userId;
                            $value1['pkListId'] = $pkpkId;
                            $value1['startTime'] = $startTime;
                            $value1['endTime'] = $endTime;
                            $value1['ifWeek'] = $rrrweek;
                            $value1['time'] =$time;
                            $value1['show'] = $this->toWeek($key1).' '. '第'.$ii.'节 '.$value1['subjectName'];
                            $value1['checkd'] = $timeCheck;
                            if(count($array)>1){
                                $value1['statu'] = 5;
                                $value1['subjectName'] = $value1['subjectName']."($strs 合班)";
                                $value1['checkd'] = 0;
                            }
                            $return[$key][$jj] = $value1;
                        }else{
                            $return[$key][$jj] = array(
                                'x' => $key,
                                'y' => $key1,
                                'subjectName' => '',
                                'show' =>'',
                                'ifWeek' => '',
                                'statu' => (int)$value1,
                                'teacherName' => '',
                                'subjectId' => '',
                                'gradeId' => '',
                                'gradeName' => '',
                                'classId' => '',
                                'className' => '',
                                'pkListId' => $pkpkId,
                                'teacherId' => $userId,
                                'startTime' => $startTime,
                                'endTime' => $endTime,
                                'time' => $time,
                                'checkd' => $timeCheck
                            );
                        }
                    }else{
                        $sst = 0;
                        if($value1 == 0){
                            $sst = '不上课';
                        }else{
                            $sst = '';
                        }
                        $return[$key][$jj] = array(
                            'x' => $key,
                            'y' => $key1,
                            'subjectName' => $sst,
                            'show' =>'',
                            'statu' => (int)$value1,
                            'ifWeek' => '',
                            'teacherName' => '',
                            'teacherId' => $userId,
                            'subjectId' => '',
                            'gradeId' => '',
                            'gradeName' => '',
                            'classId' => '',
                            'className' => '',
                            'pkListId' => $pkpkId,
                            'startTime' =>$startTime,
                            'endTime' => $endTime,
                            'time' => $time,
                            'checkd' => $timeCheck
                        );
                    }
                    $jj++;
                }
                $ii++;
            }
            $subjectIdList = array();
            foreach($subjectIdgo as $key => $value){
                $subjectIdList[] = $key;
            }
            if(count($return)<=0){
                $return = array();
            }
            if(count($subjectIdList)){
                $subjectIdList = array();
            }
            $this->returnJson(array('data' => $return,'startTimePk' =>$startTimePk ,'endTimePk' => $endTimePk,'subjectId' => $subjectIdList,'statu' => 1,'message' => 'success'),true);
        }
        if($type == 'startTk'){
            $data = I('post.data');
            $tz = array();
            $old = array();
            $new = array();
            foreach($data as $key => $value){
                //if($value)
                if($value['teacherId'] == $userId){
                    $old = $value;
                }else{
                    $new = $value;
                }
                $tz[] = $value;
            }
            if($tz[0]['gradeId'] == $tz[1]['gradeId'] && $tz[0]['classId'] == $tz[1]['classId']){
                $classData = array();
                $teacherTable  = array();
                $iiii = 0;
                foreach($tz as $key => $value){
                    $dddd = M('adjustment_teacher_table')->where(array('teacherId' => $value['teacherId'],'ifStart' => 1,'scId' => $scId,'startTime'=> array('EGT',$value['startTime']),'endTime' => array('EGT',$value['endTime'])))->select();
                    $teaData = M('pk_teacher_result')->where(array('scId' => $scId,'pkListId' => $value['pkListId'],'teacherId' => $value['teacherId']))->find();
                    $teaData = unserialize($teaData['classSet']);
                    $hhh = array();
                    foreach($dddd as $key1 => $value1){
                        $hhh[] = unserialize($value1['classSet']);
                    }
                    $teacherTable[$iiii] = $teaData;
                    foreach($hhh as $key1 => $value1){
                        if(is_array($value1)) {
                            $teacherTable[$iiii][$value1['x']][$value1['y']] = $value1;
                            unset($teacherTable[$iiii][$value1['x']][$value1['y']]['x']);
                            unset($teacherTable[$iiii][$value1['x']][$value1['y']]['y']);
                        }
                    }
                    $iiii++;
                }
                $trueOrFalse = 1;
                if($teacherTable[0][$tz[1]['x']][$tz[1]['y']] == 0 ||is_array($teacherTable[0][$tz[1]['x']][$tz[1]['y']])){
                    $trueOrFalse = 0;
                }
                if($teacherTable[1][$tz[0]['x']][$tz[0]['y']] == 0 ||is_array($teacherTable[1][$tz[0]['x']][$tz[0]['y']])){
                    $trueOrFalse = 0;
                }
                if($teacherTable[0][$tz[1]['x']][$tz[1]['y']] == 0 ||is_array($teacherTable[0][$tz[1]['x']][$tz[1]['y']])){
                    $trueOrFalse = 0;
                }
                if($teacherTable[1][$tz[0]['x']][$tz[0]['y']] == 0 ||is_array($teacherTable[1][$tz[0]['x']][$tz[0]['y']])){
                    $trueOrFalse = 0;
                }
                if($trueOrFalse == 1){
                    $classData[0]= array(
                        'teacherId' => $tz[1]['teacherId'],
                        'teacherName' => $tz[1]['teacherName'],
                        'subjectId' => $tz[1]['subjectId'],
                        'subjectName' => $tz[1]['subjectName'],
                        'statu' => 5,
                        'x' => $tz[0]['x'],
                        'y' => $tz[0]['y']
                    );
                    $classData[1]= array(
                        'teacherId' => $tz[0]['teacherId'],
                        'teacherName' => $tz[0]['teacherName'],
                        'subjectId' => $tz[0]['subjectId'],
                        'subjectName' => $tz[0]['subjectName'],
                        'statu' => 5,
                        'x' => $tz[1]['x'],
                        'y' => $tz[1]['y']
                    );
                    $teacherTableGo = array();
                    $teacherTableGo[0]['new'] = $teacherTable[0][$tz[0]['x']][$tz[0]['y']];
                    $teacherTableGo[0]['new']['x'] = $tz[1]['x'];
                    $teacherTableGo[0]['new']['y'] = $tz[1]['y'];
                    $teacherTableGo[1]['new'] = $teacherTable[1][$tz[1]['x']][$tz[1]['y']];
                    $teacherTableGo[1]['new']['x'] = $tz[0]['x'];
                    $teacherTableGo[1]['new']['y'] = $tz[0]['y'];
                    $teacherTableGo[1]['old'] = array('statu' => 1,'x' => $tz[1]['x'],'y' => $tz[1]['y']);
                    $teacherTableGo[0]['old'] = array('statu' => 1,'x' => $tz[0]['x'],'y' => $tz[0]['y']);
                    $ccc = 0;
                    $m = M('');
                    $m->startTrans();
                    $type = 1;
                    if(isset($old['tkId'])){
                        M('adjustment_teacher_table')->where(array('scId' => $scId,'tkId' => $old['tkId']))->delete();
                        M('adjustment_apply_record')->where(array('scId' => $scId,'tkId' => $old['tkId']))->delete();
                        $type = 0;
                    }
                    $tkId = M('adjustment_apply_record')->add(array(
                        'applicantId' => $userId,
                        'scId' => $scId,
                        'createTime' => date('Y-m-d H:i:s'),
                        'appoveStatu' => 0,
                        'old' => serialize($old),
                        'new' => serialize($new),
                        'type' => $type,
                        'applicantName' => $old['teacherName']
                    ));
                    $iii = 0;
                    foreach($tz as $key => $value){
                        if($iii == 0){
                            if(M('adjustment_class_table')->add(array(
                                'className' => $tz[$iii]['className'],
                                'gradeName' => $tz[$iii]['gradeName'],
                                'schedule' => serialize($classData[$iii]),
                                'createTime' => date('Y-m-d H:i:s'),
                                'scId' => $scId,
                                'gradeId' => $tz[$iii]['gradeId'],
                                'classId' => $tz[$iii]['classId'],
                                'startTime' => $tz[1]['startTime'],
                                'endTime' => $tz[1]['endTime'],
                                'ifWeek' => $tz[1]['ifWeek'],
                                'pkListId' => $tz[1]['pkListId'],
                                'ifStart' => 0,
                                'tkId' => $tkId
                            ))){
                                $ccc++;
                            }
                            if(M('adjustment_teacher_table')->add(array(
                                'teacherId' => $tz[$iii]['teacherId'],
                                'teacherName' => $tz[$iii]['teacherName'],
                                'classSet' => serialize($teacherTableGo[$iii]['new']),
                                'createTime' => date('Y-m-d H:i:s'),
                                'scId' => $scId,
                                'startTime' => $tz[1]['startTime'],
                                'endTime' => $tz[1]['endTime'],
                                'ifWeek' => $tz[1]['ifWeek'],
                                'pkListId' => $tz[1]['pkListId'],
                                'ifStart' => 0,
                                'tkId' => $tkId,
                                'type' => $type
                            ))){
                                $ccc++;
                            }
                        }else{
                            if(M('adjustment_class_table')->add(array(
                                'className' => $tz[$iii]['className'],
                                'gradeName' => $tz[$iii]['gradeName'],
                                'schedule' => serialize($classData[$iii]),
                                'createTime' => date('Y-m-d H:i:s'),
                                'scId' => $scId,
                                'gradeId' => $tz[$iii]['gradeId'],
                                'classId' => $tz[$iii]['classId'],
                                'startTime' => $tz[0]['startTime'],
                                'endTime' => $tz[0]['endTime'],
                                'ifWeek' => $tz[0]['ifWeek'],
                                'pkListId' => $tz[0]['pkListId'],
                                'ifStart' => 0,
                                'tkId' => $tkId
                            ))){
                                $ccc++;
                            }
                            if(M('adjustment_teacher_table')->add(array(
                                'teacherId' => $tz[$iii]['teacherId'],
                                'teacherName' => $tz[$iii]['teacherName'],
                                'classSet' => serialize($teacherTableGo[$iii]['new']),
                                'createTime' => date('Y-m-d H:i:s'),
                                'scId' => $scId,
                                'startTime' => $tz[0]['startTime'],
                                'endTime' => $tz[0]['endTime'],
                                'ifWeek' => $tz[0]['ifWeek'],
                                'pkListId' => $tz[0]['pkListId'],
                                'ifStart' => 0,
                                'tkId' => $tkId,
                                'type' => $type
                            ))){
                                $ccc++;
                            }
                        }
                        if(M('adjustment_teacher_table')->add(array(
                            'teacherId' => $tz[$iii]['teacherId'],
                            'teacherName' => $tz[$iii]['teacherName'],
                            'classSet' => serialize($teacherTableGo[$iii]['old']),
                            'createTime' => date('Y-m-d H:i:s'),
                            'scId' => $scId,
                            'startTime' => $tz[$iii]['startTime'],
                            'endTime' => $tz[$iii]['endTime'],
                            'ifWeek' => $tz[$iii]['ifWeek'],
                            'pkListId' => $tz[$iii]['pkListId'],
                            'ifStart' => 0,
                            'tkId' => $tkId,
                            'type' => $type
                        ))){
                            $ccc++;
                        }
                        $iii++;
                    }
                    if($ccc == 6){
                        $m->commit();
                        $this->returnJson(array('statu' => 1,'message' => '调课申请成功'),true);
                    }else{
                        $m->rollback();
                        $this->returnJson(array('statu' => 0,'message' => '调课申请失败'),true);
                    }
                }else{
                    $this->returnJson(array('statu' => 2 ,'message' => '调课申请有冲突'),true);
                }
            }
            if($tz[0]['subjectId'] == $tz[1]['subjectId'] && $tz[0]['gradeId'] == $tz[1]['gradeId']){
                $classData = array();
                $teacherTable  = array();
                $iiii = 0;
                foreach($tz as $key => $value){
                    $dddd = M('adjustment_teacher_table')->where(array('teacherId' => $value['teacherId'],'ifStart' => 1,'scId' => $scId,'pkListId' => $value['pkListId'],'startTime'=> array('EGT',$value['startTime']),'endTime' => array('EGT',$value['endTime'])))->select();
                    $teaData = M('pk_teacher_result')->where(array('scId' => $scId,'pkListId' => $value['pkListId'],'teacherId' => $value['teacherId']))->find();
                    $teaData = unserialize($teaData['classSet']);
                    $teacherTable[$iiii] = $teaData;
                    $hhh = array();
                    foreach($dddd as $key1 => $value1){
                        $hhh[] = unserialize($value1['classSet']);
                    }
                    foreach($hhh as $key1 => $value1) {
                        if ($value1== 1) {
                        } else {
                            $teacherTable[$iiii][$value1['x']][$value1['y']] = $value1;
                            unset($teacherTable[$iiii][$value1['x']][$value1['y']]['x']);
                            unset($teacherTable[$iiii][$value1['x']][$value1['y']]['y']);
                        }
                    }
                    $iiii++;
                }
                $trueOrFalse = 1;
                /*if($teacherTable[0][$tz[1]['x']][$tz[1]['y']] == 0 ||is_array($teacherTable[0][$tz[1]['x']][$tz[1]['y']])){
                    $trueOrFalse = 0;
                }
                if($teacherTable[1][$tz[0]['x']][$tz[0]['y']] == 0 ||is_array($teacherTable[1][$tz[0]['x']][$tz[0]['y']])){
                    $trueOrFalse = 0;
                }
                if($teacherTable[0][$tz[1]['x']][$tz[1]['y']] == 0 ||is_array($teacherTable[0][$tz[1]['x']][$tz[1]['y']])){
                    $trueOrFalse = 0;
                }
                if($teacherTable[1][$tz[0]['x']][$tz[0]['y']] == 0 ||is_array($teacherTable[1][$tz[0]['x']][$tz[0]['y']])){
                    $trueOrFalse = 0;
                }*/
                if($trueOrFalse == 1){
                    $classData[0]= array(
                        'teacherId' => $tz[1]['teacherId'],
                        'teacherName' => $tz[1]['teacherName'],
                        'subjectId' => $tz[0]['subjectId'],
                        'subjectName' => $tz[0]['subjectName'],
                        'statu' => 5,
                        'x' => $tz[0]['x'],
                        'y' => $tz[0]['y']
                    );
                    $classData[1]= array(
                        'teacherId' => $tz[0]['teacherId'],
                        'teacherName' => $tz[0]['teacherName'],
                        'subjectId' => $tz[1]['subjectId'],
                        'subjectName' => $tz[1]['subjectName'],
                        'statu' => 5,
                        'x' => $tz[1]['x'],
                        'y' => $tz[1]['y']
                    );
                    $teacherTableGo = array();
                    $teacherTableGo[0]['new'] = $teacherTable[0][$tz[0]['x']][$tz[0]['y']];
                    $teacherTableGo[0]['new']['gradeId'] =  $tz[1]['gradeId'];
                    $teacherTableGo[0]['new']['gradeName'] =  $tz[1]['gradeName'];
                    $teacherTableGo[0]['new']['classId'] =  $tz[1]['classId'];
                    $teacherTableGo[0]['new']['className'] =  $tz[1]['className'];
                    $teacherTableGo[0]['new']['x'] = $tz[1]['x'];
                    $teacherTableGo[0]['new']['y'] = $tz[1]['y'];
                    $teacherTableGo[1]['new'] = $teacherTable[1][$tz[1]['x']][$tz[1]['y']];
                    $teacherTableGo[1]['new']['gradeId'] =  $tz[0]['gradeId'];
                    $teacherTableGo[1]['new']['gradeName'] =  $tz[0]['gradeName'];
                    $teacherTableGo[1]['new']['classId'] =  $tz[0]['classId'];
                    $teacherTableGo[1]['new']['className'] =  $tz[0]['className'];
                    $teacherTableGo[1]['new']['x'] = $tz[0]['x'];
                    $teacherTableGo[1]['new']['y'] = $tz[0]['y'];
                    $teacherTableGo[1]['old'] = array('statu' => 1,'x' => $tz[1]['x'],'y' => $tz[1]['y']);
                    $teacherTableGo[0]['old'] = array('statu' => 1,'x' => $tz[0]['x'],'y' => $tz[0]['y']);
                    $iii = 0;
                    $ccc = 0;
                    $m = M('');
                    $m->startTrans();
                    $type = 1;
                    if(isset($new['tkId'])){
                        M('adjustment_teacher_table')->where(array('scId' => $scId,'tkId' => $new['tkId']))->delete();
                        M('adjustment_apply_record')->where(array('scId' => $scId,'tkId' => $new['tkId']))->delete();
                        $type = 0;
                    }
                    $tkId = M('adjustment_apply_record')->add(array(
                        'applicantId' => $userId,
                        'scId' => $scId,
                        'createTime' => date('Y-m-d H:i:s'),
                        'appoveStatu' => 0,
                        'old' => serialize($old),
                        'new' => serialize($new),
                        'type' => $type,
                        'applicantName' => $old['teacherName']
                    ));
                    $iii = 0;
                    foreach($tz as $key => $value){
                        if($iii == 0){
                            if(M('adjustment_teacher_table')->add(array(
                                'teacherId' => $tz[$iii]['teacherId'],
                                'teacherName' => $tz[$iii]['teacherName'],
                                'classSet' => serialize($teacherTableGo[$iii]['new']),
                                'createTime' => date('Y-m-d H:i:s'),
                                'scId' => $scId,
                                'startTime' => $tz[1]['startTime'],
                                'endTime' => $tz[1]['endTime'],
                                'ifWeek' => $tz[1]['ifWeek'],
                                'pkListId' => $tz[1]['pkListId'],
                                'ifStart' => 0,
                                'tkId' => $tkId,
                                'type' => $type
                            ))){
                                $ccc++;
                            }
                        }else{
                            if(M('adjustment_teacher_table')->add(array(
                                'teacherId' => $tz[$iii]['teacherId'],
                                'teacherName' => $tz[$iii]['teacherName'],
                                'classSet' => serialize($teacherTableGo[$iii]['new']),
                                'createTime' => date('Y-m-d H:i:s'),
                                'scId' => $scId,
                                'startTime' => $tz[0]['startTime'],
                                'endTime' => $tz[0]['endTime'],
                                'ifWeek' => $tz[0]['ifWeek'],
                                'pkListId' => $tz[0]['pkListId'],
                                'ifStart' => 0,
                                'tkId' => $tkId,
                                'type' => $type
                            ))){
                                $ccc++;
                            }
                        }
                        if(M('adjustment_class_table')->add(array(
                            'className' => $tz[$iii]['className'],
                            'gradeName' => $tz[$iii]['gradeName'],
                            'schedule' => serialize($classData[$iii]),
                            'createTime' => date('Y-m-d H:i:s'),
                            'scId' => $scId,
                            'gradeId' => $tz[$iii]['gradeId'],
                            'classId' => $tz[$iii]['classId'],
                            'startTime' => $tz[$iii]['startTime'],
                            'endTime' => $tz[$iii]['endTime'],
                            'ifWeek' => $tz[$iii]['ifWeek'],
                            'pkListId' => $tz[$iii]['pkListId'],
                            'ifStart' => 0,
                            'tkId' => $tkId
                        ))){
                            $ccc++;
                        }
                        if(M('adjustment_teacher_table')->add(array(
                            'teacherId' => $tz[$iii]['teacherId'],
                            'teacherName' => $tz[$iii]['teacherName'],
                            'classSet' => serialize($teacherTableGo[$iii]['old']),
                            'createTime' => date('Y-m-d H:i:s'),
                            'scId' => $scId,
                            'startTime' => $tz[$iii]['startTime'],
                            'endTime' => $tz[$iii]['endTime'],
                            'ifWeek' => $tz[$iii]['ifWeek'],
                            'pkListId' => $tz[$iii]['pkListId'],
                            'ifStart' => 0,
                            'tkId' => $tkId,
                            'type' => $type
                        ))){
                            $ccc++;
                        }
                        $iii++;
                    }
                    if($ccc == 6){
                        $m->commit();
                        $this->returnJson(array('statu' => 1,'message' => '调课申请成功'),true);
                    }else{
                        $m->rollback();
                        $this->returnJson(array('statu' => 0,'message' => '调课申请失败'),true);
                    }
                }else{
                    $this->returnJson(array('statu' => 2 ,'message' => '调课申请有冲突'),true);
                }
            }
            $this->returnJson(array('statu' => 2 ,'message' => '调课申请有冲突'),true);
        }
        if($type == 'getTeacher'){
            $gradeId = I('get.gradeId');
            $classIdOne = I('get.classIdOne');
            $classIdTow = I('get.classIdTow');
            $data = array();
            $data = M('jw_schedule')->field('techerId,techerName,subjectId,subject')->where(array('scId' => $scId,'gradeId' => $gradeId))->group('techerId')->select();
            $userdddd = array();
            foreach($data as $key => $value){
                if($value['techerId'] == $userId){
                    $userdddd[$value['subjectId']] = 1;
                }
            }
            $dataList = array();
            foreach($data as $key => $value){
                if($classIdOne == $classIdTow){
                    $dataList[$value['subjectId']]['data'][] = $value;
                    $dataList[$value['subjectId']]['techerName'] = $value['subject'];
                }else{
                    if(isset($userdddd[$value['subjectId']])){
                        $dataList[$value['subjectId']]['data'][] = $value;
                        $dataList[$value['subjectId']]['techerName'] = $value['subject'];
                    }
                }
            }
            rsort($dataList);
            $this->returnJson(array('data' => $dataList,'message' => 'success','statu' => 1),true);
        }
        if($type == 'getGrade'){
            $id = I('get.techerId');
            if($id){
                $table = M('jw_schedule')->field('gradeId,gradeName')->where(array('techerId' => $id, 'scId' => $scId))->group('gradeId')->order('gradeName')->select();
                if(!$table){
                    $table = array();
                }else{
                    foreach($table as $key => $value){
                        $table[$key]['gradeName'] = $this->gradeToZhong($table[$key]['gradeName']);
                    }
                }
                $this->returnJson(array('statu' => 1,'data' => $table,'message' => 'success'),true);
            }else{
                $id = $userId;
                $table = M('jw_schedule')->field('gradeId,gradeName')->where(array('techerId' => $id, 'scId' => $scId))->group('gradeId')->order('gradeName')->select();
                if(!$table){
                    $table = array();
                }else{
                    foreach($table as $key => $value){
                        $table[$key]['gradeName'] = $this->gradeToZhong($table[$key]['gradeName']);
                    }
                }
                $this->returnJson(array('statu' => 1,'data' => $table,'message' => 'success'),true);
            }
        }
        if($type == 'getClass'){
            $id = I('get.techerId');
            $gradeId = I('get.gradeId');
            if($id){
                //$table = M('jw_schedule')->field('classId,className')->where(array('techerId' => $id,'gradeId' => $gradeId, 'scId' => $scId))->group('classId')->order('className')->select();
                $table = M('class')->field('classid,classname')->where(array('scId' => $scId,'grade' => $gradeId))->order('classname')->select();
                foreach($table as $key => $value){
                    $table[$key]['classId'] = $value['classid'];
                    $table[$key]['className'] = $value['classname'];
                }
                $this->returnJson(array('statu' => 1,'data' => $table,'message' => 'success'),true);
            }else{
                $id = $userId;
                //$table = M('jw_schedule')->field('classId,className')->where(array('techerId' => $id,'gradeId' => $gradeId, 'scId' => $scId))->group('classId')->order('className')->select();
                $table = M('class')->field('classid,classname')->where(array('scId' => $scId,'grade' => $gradeId))->order('classname')->select();
                foreach($table as $key => $value){
                    $table[$key]['classId'] = $value['classid'];
                    $table[$key]['className'] = $value['classname'];
                }
                $this->returnJson(array('statu' => 1,'data' => $table,'message' => 'success'),true);
            }
        }
        if($type == 'noZd'){
            $data = I('post.data');
            if($tkId = M('adjustment_apply_record')->add(array(
                'applicantId' => $userId,
                'scId' => $scId,
                'createTime' => date('Y-m-d H:i:s'),
                'appoveStatu' => 0,
                'old' => serialize($data),
                'applicantName' => $data['teacherName'],
                'type' => 0,
            ))){
                if(M('adjustment_teacher_table')->add(
                    array(
                        'teacherId' =>$data['teacherId'],
                        'teacherName' => $data['teacherName'],
                        'classSet' => serialize(array(
                            'subjectId' => $data['subjectId'],
                            'subjectName' => $data['subjectName'],
                            'statu' =>6,
                            'tkId' => $tkId,
                            'gradeId' => $data['gradeId'],
                            'classId' => $data['classId'],
                            'className' => $data['className'],
                            'gradeName' => $data['gradeName'],
                            'x' => $data['x'],
                            'y' => $data['y']
                        )),
                        'createTime' => date('Y-m-d H:i:s'),
                        'scId' => $scId,
                        'startTime' =>$data['startTime'],
                        'endTime' => $data['endTime'],
                        'ifWeek' => $data['ifWeek'],
                        'pkListId' => $data['pkListId'],
                        'ifStart' => 0,
                        'tkId' => $tkId,
                        'type' =>1,
                        'ifNo' =>1
                    )
                )){
                    $this->returnJson(array('statu' => 1,'message' => '调课申请成功'),true);
                }
                $this->returnJson(array('statu' => 0,'message' => '调课申请失败'),true);
            }else{
                $this->returnJson(array('statu' => 0,'message' => '调课申请失败'),true);
            }
        }
    }
        public function classTk(){//班级调课
        $type =  I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type == 'classCR'){
            $data = I('post.data');
            $tz = $data;
            $iiii = 0;
            $tz = array();
            $old = array();
            $new = array();
            foreach($data as $key => $value){
                //if($value)
                if($key == 0){
                    $old = $value;
                }else{
                    $new = $value;
                }
                $tz[] = $value;
            }
            if(1){
                foreach($tz as $key => $value){
                    $dddd = M('adjustment_teacher_table')->where(array('teacherId' => $value['teacherId'],'ifStart' => 1,'scId' => $scId,'startTime'=> array('EGT',$value['startTime']),'endTime' => array('EGT',$value['endTime'])))->select();
                    $teaData = M('pk_teacher_result')->where(array('scId' => $scId,'pkListId' => $value['pkListId'],'teacherId' => $value['teacherId']))->find();
                    $teaData = unserialize($teaData['classSet']);
                    $hhh = array();
                    foreach($dddd as $key1 => $value1){
                        $hhh[] = unserialize($value1['classSet']);
                    }
                    $teacherTable[$iiii] = $teaData;
                    foreach($hhh as $key1 => $value1){
                        if($value1 == 1) {

                        }else{
                            $teacherTable[$iiii][$value1['x']][$value1['y']] = $value1;
                            unset($teacherTable[$iiii][$value1['x']][$value1['y']]['x']);
                            unset($teacherTable[$iiii][$value1['x']][$value1['y']]['y']);
                        }
                    }
                    $iiii++;
                }
                $trueOrFalse = 1;
                if($teacherTable[0][$tz[1]['x']][$tz[1]['y']] == 0 ||is_array($teacherTable[0][$tz[1]['x']][$tz[1]['y']])){
                    $trueOrFalse = 0;
                }
                if($teacherTable[1][$tz[0]['x']][$tz[0]['y']] == 0 ||is_array($teacherTable[1][$tz[0]['x']][$tz[0]['y']])){
                    $trueOrFalse = 0;
                }
                if($teacherTable[0][$tz[1]['x']][$tz[1]['y']] == 0 ||is_array($teacherTable[0][$tz[1]['x']][$tz[1]['y']])){
                    $trueOrFalse = 0;
                }
                if($teacherTable[1][$tz[0]['x']][$tz[0]['y']] == 0 ||is_array($teacherTable[1][$tz[0]['x']][$tz[0]['y']])){
                    $trueOrFalse = 0;
                }
                if($trueOrFalse){
                    $classData[0]= array(
                        'teacherId' => $tz[1]['teacherId'],
                        'teacherName' => $tz[1]['teacherName'],
                        'subjectId' => $tz[1]['subjectId'],
                        'subjectName' => $tz[1]['subjectName'],
                        'statu' => 5,
                        'x' => $tz[0]['x'],
                        'y' => $tz[0]['y']
                    );
                    $classData[1]= array(
                        'teacherId' => $tz[0]['teacherId'],
                        'teacherName' => $tz[0]['teacherName'],
                        'subjectId' => $tz[0]['subjectId'],
                        'subjectName' => $tz[0]['subjectName'],
                        'statu' => 5,
                        'x' => $tz[1]['x'],
                        'y' => $tz[1]['y']
                    );
                    $teacherTableGo = array();
                    $teacherTableGo[0]['new'] = $teacherTable[0][$tz[0]['x']][$tz[0]['y']];
                    $teacherTableGo[0]['new']['x'] = $tz[1]['x'];
                    $teacherTableGo[0]['new']['y'] = $tz[1]['y'];
                    $teacherTableGo[1]['new'] = $teacherTable[1][$tz[1]['x']][$tz[1]['y']];
                    $teacherTableGo[1]['new']['x'] = $tz[0]['x'];
                    $teacherTableGo[1]['new']['y'] = $tz[0]['y'];
                    $teacherTableGo[1]['old'] = array('statu' => 1,'x' => $tz[1]['x'],'y' => $tz[1]['y']);
                    $teacherTableGo[0]['old'] = array('statu' => 1,'x' => $tz[0]['x'],'y' => $tz[0]['y']);
                    $ccc = 0;
                    $m = M('');
                    $m->startTrans();
                    $type = 3;
                    $tkId = M('adjustment_apply_record')->add(array(
                        'applicantId' => $userId,
                        'scId' => $scId,
                        'createTime' => date('Y-m-d H:i:s'),
                        'appoveStatu' => 0,
                        'old' => serialize($old),
                        'new' => serialize($new),
                        'type' => $type,
                        'applicantName' =>$userName
                    ));
                    $iii = 0;
                    foreach($tz as $key => $value){
                        if($iii == 0){
                            if(M('adjustment_class_table')->add(array(
                                'className' => $tz[$iii]['className'],
                                'gradeName' => $tz[$iii]['gradeName'],
                                'schedule' => serialize($classData[$iii]),
                                'createTime' => date('Y-m-d H:i:s'),
                                'scId' => $scId,
                                'gradeId' => $tz[$iii]['gradeId'],
                                'classId' => $tz[$iii]['classId'],
                                'startTime' => $tz[1]['startTime'],
                                'endTime' => $tz[1]['endTime'],
                                'ifWeek' => $tz[1]['ifWeek'],
                                'pkListId' => $tz[1]['pkListId'],
                                'ifStart' => 0,
                                'tkId' => $tkId
                            ))){
                                $ccc++;
                            }
                            if(M('adjustment_teacher_table')->add(array(
                                'teacherId' => $tz[$iii]['teacherId'],
                                'teacherName' => $tz[$iii]['teacherName'],
                                'classSet' => serialize($teacherTableGo[$iii]['new']),
                                'createTime' => date('Y-m-d H:i:s'),
                                'scId' => $scId,
                                'startTime' => $tz[1]['startTime'],
                                'endTime' => $tz[1]['endTime'],
                                'ifWeek' => $tz[1]['ifWeek'],
                                'pkListId' => $tz[1]['pkListId'],
                                'ifStart' => 0,
                                'tkId' => $tkId
                            ))){
                                $ccc++;
                            }
                        }else{
                            if(M('adjustment_class_table')->add(array(
                                'className' => $tz[$iii]['className'],
                                'gradeName' => $tz[$iii]['gradeName'],
                                'schedule' => serialize($classData[$iii]),
                                'createTime' => date('Y-m-d H:i:s'),
                                'scId' => $scId,
                                'gradeId' => $tz[$iii]['gradeId'],
                                'classId' => $tz[$iii]['classId'],
                                'startTime' => $tz[0]['startTime'],
                                'endTime' => $tz[0]['endTime'],
                                'ifWeek' => $tz[0]['ifWeek'],
                                'pkListId' => $tz[0]['pkListId'],
                                'ifStart' => 0,
                                'tkId' => $tkId
                            ))){
                                $ccc++;
                            }
                            if(M('adjustment_teacher_table')->add(array(
                                'teacherId' => $tz[$iii]['teacherId'],
                                'teacherName' => $tz[$iii]['teacherName'],
                                'classSet' => serialize($teacherTableGo[$iii]['new']),
                                'createTime' => date('Y-m-d H:i:s'),
                                'scId' => $scId,
                                'startTime' => $tz[0]['startTime'],
                                'endTime' => $tz[0]['endTime'],
                                'ifWeek' => $tz[0]['ifWeek'],
                                'pkListId' => $tz[0]['pkListId'],
                                'ifStart' => 0,
                                'tkId' => $tkId
                            ))){
                                $ccc++;
                            }
                        }
                        if(M('adjustment_teacher_table')->add(array(
                            'teacherId' => $tz[$iii]['teacherId'],
                            'teacherName' => $tz[$iii]['teacherName'],
                            'classSet' => serialize($teacherTableGo[$iii]['old']),
                            'createTime' => date('Y-m-d H:i:s'),
                            'scId' => $scId,
                            'startTime' => $tz[$iii]['startTime'],
                            'endTime' => $tz[$iii]['endTime'],
                            'ifWeek' => $tz[$iii]['ifWeek'],
                            'pkListId' => $tz[$iii]['pkListId'],
                            'ifStart' => 0,
                            'tkId' => $tkId
                        ))){
                            $ccc++;
                        }
                        $iii++;
                    }
                    if($ccc == 6){
                        $m->commit();
                        $this->returnJson(array('statu' => 1,'message' => '调课申请成功'),true);
                    }else{
                        $m->rollback();
                        $this->returnJson(array('statu' => 0,'message' => '调课申请失败'),true);
                    }
                }else{
                    $this->returnJson(array('statu' => 2 ,'message' => '调课申请有冲突'),true);
                }
            }
        }
        if($type == 'getGradeAndClass'){
            if($userRoleId == $this::$adminRoleId){
                $grade = M('grade')->where(array('scId' => $scId))->order('name')->select();
                $class = M('class')->where(array('scId' => $scId))->order('classname')->select();
                $gradeRe = array();
                foreach($grade as $key => $value){
                    $grade[$key]['classname'] =  $grade[$key]['znName'];
                    foreach($class as $key1 => $value1){
                        if($value['gradeid'] == $value1['grade']){
                            $value1['gradeName'] = $grade[$key]['znName'];
                            $grade[$key]['classname'] =  $grade[$key]['znName'];
                            $grade[$key]['data'][] = $value1;
                        }
                    }
                }
                $this->returnJson(array('statu' =>1,'data' => $grade,'message' => 'success'),true);
            }
            $grade = M('grade')->where(array('scId' => $scId))->order('name')->select();
            foreach($grade as $key => $value){
                $gradeRe = array();
                if($value['userId'] == $userId){
                    $gradeRe[0] = $value;
                    $class = M('class')->where(array('scId' => $scId))->order('classname')->select();
                    $classRe =array();
                    foreach($class as $key1 => $value1){
                        if($value1['grade'] == $gradeRe[0]['gradeid']){
                            $value1['gradeName'] = $gradeRe[0]['znName'];
                            $classRe[] = $value1;
                        }
                    }
                    $gradeRe[0]['classname'] = $gradeRe[0]['znName'];
                    $gradeRe[0]['data'] = $classRe;
                    $this->returnJson(array('statu' =>1,'data' => $gradeRe,'message' => 'success'),true);
                }
            }
            $class = M('class')->where(array('scId' => $scId))->order('classname')->select();
            foreach($class as $key => $value){
                if($value['userid'] == $userId){
                    foreach($grade as $key1 => $value1){
                        if($value1['gradeid'] == $value['grade']){
                            $gradeRe[0] = $value1;
                            $gradeRe[0]['classname'] = $gradeRe[0]['znName'];
                            $value['gradeName'] =  $gradeRe[0]['znName'];
                            $gradeRe[0]['data'][0] = $value;
                            $this->returnJson(array('statu' =>1,'data' => $gradeRe,'message' => 'success'),true);
                        }
                    }
                }
            }
        }
        if($type == 'getClassTable'){
            $gradeId = I('get.grade');
            $classId = I('get.classid');
            $startTime =  I('get.startTime');
            $endTime = I('get.endTime');
            $week = date('W',strtotime($startTime));
            $ifWeek = 0;
            if($week%2 == 0){
                $ifWeek = 2;
            }else{
                $ifWeek = 1;
            }
            $rrrweek = 0;
            $check = 0;
            $pkpkId = 0;
            $hhh = array();
            if($data = M('adjustment_class_table')->where(array('scId' => $scId,'ifStart' => 1,'classId' => $classId,'gradeId' => $gradeId ,'startTime'=> array('EGT',$startTime),'endTime' => array('EGT',$endTime)))->order('id')->select()){
                $rrrweek = $data['ifWeek'];
                foreach($data as $key => $value){
                    $hhh[] = unserialize($value['schedule']);
                }
            }
            $table = M('pk_class_result')->where(array('gradeId' => $gradeId,'classId' => $classId, 'scId' => $scId))->select();
            $className = $table[0]['className'];
            $gradeName = $table[0]['gradeName'];
            $relTable = array();
            $nowDate11 = date('Y-m-d');
            foreach($table as $key => $value){
                $pkListId =$value['pkListId'];
                $pkList = M('pk_list')->where(array('scId' => $scId,'id' => $pkListId,'ifStartUp' => 1))->find();
                $gradeddd = explode(',',$pkList['pkRange']);
                $checkchchch = 0;
                foreach($gradeddd as $key2 => $value2){
                    if($value2 == $gradeId){
                        if($pkList['startTime']<=$nowDate11 && $pkList['endTime']>=$nowDate11){
                            $checkchchch = 1;
                        }
                    }
                }
                if(($pkList['ifWeek'] == 0 ||$pkList['ifWeek'] == $ifWeek) && $checkchchch ){
                    $rrrweek = $pkList['ifWeek'];
                    $startTimePk = $pkList['startTime'];
                    $endTimePk= $pkList['endTime'];
                    $relTable = unserialize($value['schedule']);
                    $pkpkId = $pkListId;
                    break;
                }
            }
            foreach($hhh as $key => $value){
                if(is_array($value)){
                    $relTable[$value['x']][$value['y']] = $value;
                    unset( $relTable[$value['x']][$value['y']]['x']);
                    unset( $relTable[$value['x']][$value['y']]['y']);
                }
            }
            $timeCheck = 0;
            $nowTime = date('Y-m-d');
            $ii = 1;
            foreach($relTable as $key => $value){
                $return[$key][0]['subjectName'] = '第'.$ii.'节';
                $jj = 1;
                $zzz = 0;
                foreach($value as $key1 => $value1){
                    $time = date('Y-m-d',strtotime($startTime)+24*3600*$zzz);
                    if(($time<$nowTime) || ($time>$endTimePk)){
                        $timeCheck = 0;
                    }else{
                        $timeCheck = 1;
                    }
                    $zzz++;
                    if(is_array($value1)){
                        $subjectIdgo[$value1['subjectId']] = 1;
                        $value1['x'] = $key;
                        $value1['y'] = $key1;
                        $value1['pkListId'] = $pkpkId;
                        $value1['startTime'] = $startTime;
                        $value1['endTime'] = $endTime;
                        $value1['ifWeek'] = $rrrweek;
                        $value1['gradeId'] = $gradeId;
                        $value1['classId'] = $classId;
                        $value1['checkd'] = $timeCheck;
                        $value1['time'] = $time;
                        $value1['className'] = $className;
                        $value1['gradeName'] = $gradeName;
                        $value1['show'] = $this->toWeek($key1).' '. '第'.$ii.'节 '.$value1['subjectName'];
                        $return[$key][$jj] = $value1;
                    }else{
                        $sst = 0;
                        if($value1 == 0){
                            $sst = '不上课';
                        }else{
                            $sst = '';
                        }
                        $return[$key][$jj] = array(
                            'x' => $key,
                            'y' => $key1,
                            'subjectName' => $sst,
                            'show' =>'',
                            'statu' => (int)$value1,
                            'ifWeek' => '',
                            'teacherName' => '',
                            'teacherId' => $userId,
                            'subjectId' => '',
                            'time' => $time,
                            'gradeId' => '',
                            'gradeName' => '',
                            'classId' => '',
                            'className' => '',
                            'pkListId' => $pkpkId,
                            'startTime' =>$startTime,
                            'endTime' => $endTime,
                            'checkd' => $timeCheck
                        );
                    }
                    $jj++;
                }
                $ii++;
            }
            if(count($return)<=0){
                $return = array();
            }
            $this->returnJson(array('data' => $return,'startTimePk' =>$startTimePk ,'endTimePk' => $endTimePk,'statu' => 1),true);
        }
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
    public function dK(){
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'classDkApprovalRecord'){

        }
        if($type == 'classDkApproval'){

        }
        if($type == 'classDkApply'){

        }
    }
    private function hb($pkListId){
        $hbClass = M('pk_hb_set')->where(array('pkListId' => $pkListId))->select();
        $return = array();
        $i = 0;
        foreach($hbClass as $key => $value){
            $return[$i]['subjectId'] = $value['subjectId'];
            $return[$i]['teacherId'] = $value['teacherId'];
            $return[$i]['teacherName'] = $value['teacherName'];
            $return[$i]['subjectName'] = $value['subjectName'];
            $return[$i]['data'] = $value['classSet'];
            $i++;
        }
        return $return;
    }
    private function hbToData($data){
        foreach($data as $key => $value){
            $data[$key]['data'] = unserialize($value['data']);
        }
        return $data;
    }
}