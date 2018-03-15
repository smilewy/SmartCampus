<?php
namespace Home\Controller;
use Think\Controller;
ob_end_clean();
class AccomplishmentController extends Base {

    private function getExport($header,$data){//导出
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        // Set properties
        //$objPHPExcel->getProperties()->setCreator("cdn");
        //$objPHPExcel->getProperties()->setLastModifiedBy("cdn");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");

// Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        //$objPHPExcel->getActiveSheet()->setTitle('CDN');
        $a=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        $l=0;
        foreach($header as $k=>$v){
            $st=$a[$l];
            $objPHPExcel->getActiveSheet()->SetCellValue($st.'1',$v);
            $l++;
        }
        $i = 2;
        foreach ($data as $k => $v){
            $num = $i++;
            $l=0;
            foreach($v as $c=>$b){
                $st=$a[$l];
                $objPHPExcel->getActiveSheet()->setCellValue($st . $num, $b);
                $l++;
            }
        }
// Save Excel 2007 file
//$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        ob_end_clean();
        header("Content-Disposition:attachment;filename=tableExport.xls");
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    private function sortArrByOneField($array,$find=false){/*************对数组进行模糊查询************/
        if($find!==false){
            foreach($array as $k=>$v){
                foreach($v as $a=>$b){
                    if(!is_array($b)){
                        if($a!='directionId'&&$a!='programmeId'&&$a!='gradeId'&&$a!='classId'){
                            $aa=explode($find,$b);
                            if(count($aa)>1){
                                $arr[]=$v;
                                break;
                            }
                        }
                    }
                }
            }
        }else{
            $arr=$array;
        }
        return $arr;
    }
    private function make_tree1($list,$pk='projectId',$pid='pid',$child='childs',$root=0){/*****************将数组排成树形图格式*****************/
        $tree=array();
        foreach($list as $key=> $val){
            if($val[$pid]==$root){
                //获取当前$pid所有子类
                unset($list[$key]);
                if(! empty($list)){
                    $child=$this->make_tree1($list,$pk,$pid,$child,$val[$pk]); //来来来 找北京的子栏目 递归 空
                    if(!empty($child)){
                        $val['childs']=$child;
                    }
                }
                $tree[]=$val;
            }
        }
        return $tree;
    }
    private function make_tree12($list,$root=0,$pk='projectId',$pid='pid',$child='childs'){/*****************将数组排成树形图格式*****************/
        $tree=array();
        foreach($list as $key=> $val){
            if($val[$pid]==$root){
                //获取当前$pid所有子类
                unset($list[$key]);
                if(! empty($list)){
                    $child=$this->make_tree12($list,$val[$pk],$pk,$pid,$child); //来来来 找北京的子栏目 递归 空
                    if(!empty($child)){
                        foreach($child as $k=>$v){
                            $tree[]=$v[$pk];
                        }
                    }
                }
                $tree[]=$val[$pk];
            }
        }
        return $tree;
    }
    private function getProject($directionId){/******************获取考核项目和条例******************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $where['directionId']=$directionId;
        $where['scId']=$scId;
        $dao=M('attainment_project');
        $f=$dao->field('projectId,projectNmae,pid,level,isParent,scoreAll')->where($where)->select();
        foreach($f as $k=>$v){
            if($v['isParent']==0){
                $f[$k]['projectNmaeRules']=$v['projectNmae'];
                $f[$k]['projectNmae']=null;
            }else{
                $f[$k]['projectNmaeRules']=null;
            }
        }
        $arr=$this->make_tree1($f);
        return $arr;
    }
    private function getProjectId($directionId,$projectId){/******************获取考核项目和条例******************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $where['directionId']=$directionId;
        $where['scId']=$scId;
        $dao=M('attainment_project');
        $f=$dao->field('projectId,pid')->where($where)->select();
        $arr=$this->make_tree12($f,$projectId);
        return $arr;
    }
    protected function getDirectionList($find){/*****************获取考核方向********************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $where['scId']=$scId;
        $dao1=M('attainment_direction');
        $dao2=M('attainment_project');
        $f1=$dao1->where($where)->field('directionId,directionName')->select();
        $sql2="SELECT directionId,SUM(scoreAll) AS scoreAll FROM `mks_attainment_project` WHERE scId=".$scId." GROUP BY directionId";
        $f2=$dao2->query($sql2);
        foreach($f2 as $k=>$v){
            $arr[$v['directionId']]=$v['scoreAll'];
        }
        foreach($f1 as $k=>$v){
            $f1[$k]['scoreAll']=$arr[$v['directionId']];
        }
        if($find || $find===0){
            $f1=$this->sortArrByOneField($f1,$find);
        }
        return $f1;
    }
    private function getGrade(){/***************获取年级列表****************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $userId=$getSession['userId'];
        $roleId=$getSession['roleId'];
        $dao=M();
        if($roleId==$this::$teacherRoleId){
            $st=" and userId=".$userId." order by name";
        }else{
            $st=" order by name";
        }
        $sql="SELECT gradeid,name FROM `mks_grade` WHERE scId=".$scId.$st;
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $f=$dao->query($sql);
        foreach($f as $k=>$v){
            $f[$k]['name']=$arrgrade[$v['name']-1];
        }
        return($f);
    }
    private function getProgramme(){/*****************获取考核方案****************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $roleId=$getSession['roleId'];
        $userId=$getSession['userId'];
        $dao=M('attainment_programme');
        if($roleId==$this::$teacherRoleId){
            $dao=M();
            //$userid=$getSession['id'];
            $sql1="SELECT `gradeId` FROM `mks_jw_schedule` WHERE techerId=".$userId." and scId=".$scId;
            $f1=$dao->query($sql1);
            $sql2="SELECT `grade` as gradeId FROM `mks_class` WHERE `userid`=".$userId." and scId=".$scId;
            $f2=$dao->query($sql2);
            $sql3="SELECT gradeid as gradeId FROM mks_grade WHERE userId=".$userId." AND scId=".$scId;
            $f3=$dao->query($sql3);
            /*foreach($f1 as $k=>$v){
                $arr[$v['gradeId']]=$v['gradeId'];
            }
            foreach($f2 as $k=>$v){
                $arr[$v['gradeId']]=$v['gradeId'];
            }*/
            foreach($f3 as $k=>$v){
                $arr[$v['gradeId']]=$v['gradeId'];
            }

            $hh=implode(',',$arr);
            //$where['gradeid']=array('in',implode(',',$arr));
            $sqlb="SELECT programmeId FROM `mks_attainment_teacher` WHERE userId=".$userId." AND scId=".$scId;
            $fb=$dao->query($sqlb);
            foreach($fb as $k=>$v){
                $aaam[]=$v['programmeId'];
            }
            $ss=implode(',',$aaam);
            if(!empty($fb) && !$arr){
                //$where['programmeId']=array('in',$ss);
                $sql="select * from mks_attainment_programme where scId=".$scId." and programmeId In(".$ss.")";
            }elseif(empty($fb) && $arr){
                $sql="select * from mks_attainment_programme where scId=".$scId." and gradeId In(".$hh.")";
            }elseif(!empty($fb) && $arr){
                $sql="select * from mks_attainment_programme where scId=".$scId." and (gradeId In(".$hh.") or programmeId In(".$ss."))";
            }else{
                $f=array();
                return $f;
            }
            $f=$dao->query($sql);
            return $f;
        }
        $where['scId']=$scId;

        $f=$dao->where($where)->field('programmeId,programmeName')->select();
        return $f;
    }
    private function getRole($scId,$userId){
        $dao=M();
        $sql1="select count(*) as `count` from mks_class where userid=".$userId." and scId=".$scId;
        $sql2="select count(*) as `count` from mks_grade where userId=".$userId." and scId=".$scId;
        $sql3="select count(*) as `count` from mks_jw_schedule where techerId=".$userId." and scId=".$scId;
        $f1=$dao->query($sql1);
        $f2=$dao->query($sql2);
        $f3=$dao->query($sql3);
        if($f1['0']['count']||$f2['0']['count']||$f3['0']['count']){
            return true;
        }else{
            return false;
        }
    }
    public function direction(){/*********************素养考核方向***********************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(I('get.type')=='insertDirection') {/*******************添加考核方向***********************/
            $dao = M('attainment_direction');
            $data['directionName'] = I('post.directionName');
            $data['scId'] = $scId;
            $data['lastRecordTime'] = time();
            $data['createTime'] = time();
            $f1 = $dao->add($data);
            if($f1 === false) {
                $arr['return'] = false;
            }else {
                $arr['return'] = true;
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='saveDirection') {/*******************修改考核方向名称***********************/
            $dao = M('attainment_direction');
            $value=I('post.data');
            $sql="INSERT INTO `mks_attainment_direction`(directionId,directionName,scId,lastRecordTime) VALUES ";
            foreach($value as $k=>$v){
                $str="(".$v['directionId'].",'".$v['directionName']."',".$scId.",".time().")";
                $val[]=$str;
            }
            $value=implode(',',$val);
            $sql.=$value." ON DUPLICATE KEY UPDATE directionName=IF(scId = ".$scId.",VALUES(directionName),directionName),
            lastRecordTime=IF(scId = ".$scId.",VALUES(lastRecordTime),lastRecordTime)";
            $f1=$dao->query($sql);
            if($f1 === false) {
                $arr['return'] = false;
            } else {
                $arr['return'] = true;
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='findDirectionList'){/********************查询考核方向*************************/
            $find=I('post.find');
            $arr=$this->getDirectionList($find);
            $dao=M();
            $userId=$getSession['userId'];
            $roleId=$getSession['roleId'];
            if($roleId==$this::$teacherRoleId){/************修改，加上年级主任的权限，当教师不为年级主任时返回return=false，当通过验证时返回return=true,使用时去掉注释返回$data************/
                $sqla="SELECT gradeid FROM `mks_grade` WHERE userid=".$userId." and scId=".$scId;
                $fa=$dao->query($sqla);
                if(empty($fa)){
                    $arr=array();
                    $data['data']=$arr;
                    $data['return']=false;
                    $this->ajaxReturn($data);
                    exit;
                }
            }
            $sql="SELECT directionId FROM `mks_attainment_programme` WHERE scId=".$scId;
            $f=$dao->query($sql);
            foreach($f as $k=>$v){
                $arra[]=$v['directionId'];
            }
            foreach($arr as $k=>$v){
                if(in_array($v['directionId'],$arra)){
                    $arr[$k]['state']=false;
                }else{
                    $arr[$k]['state']=true;
                }
            }
            $data['data']=$arr;
            $data['return']=true;
            $this->ajaxReturn($data);
        }elseif(I('get.type')=='insertPproject'){/*****************************添加考核项目(条例)*************************/
            $dao=M('attainment_project');
            $data['directionId']=I('post.directionId');
            $data['projectNmae']=I('post.projectNmae');
            $pid=I('post.pid');
            $level=I('post.level');
            $data['isParent']=I('post.isParent');
            $data['lastRecordTime']=time();
            $data['createTime']=time();
            $data['scId']=$scId;
            if($data['isParent']==0){
                $data['pid']=$pid;
                $data['level']=$level+1;
                $data['scoreAll']=I('post.scoreAll');
            }elseif($data['isParent']==1&&$pid&&$level){
                $data['pid']=$pid;
                $data['level']=$level+1;
                $data['scoreAll']=null;
            }elseif($data['isParent']==1&&!$pid&&!$level){
                $data['pid']=0;
                $data['level']=1;
                $data['scoreAll']=null;
            }
            $f=$dao->add($data);
            if($f===false){
                $arr['return']=false;
            }else{
                $arr['return']=true;
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='findProject'){/****************查询考核项目(条例)*********************/
            $directionId=I('post.directionId');
            $arr1=$this->getDirectionList(false);
            $dao=M();
            $sql="SELECT directionId FROM `mks_attainment_programme` WHERE scId=".$scId;
            $f=$dao->query($sql);
            foreach($f as $k=>$v){
                $arra[]=$v['directionId'];
            }
            foreach($arr1 as $k=>$v){
                if(in_array($v['directionId'],$arra)){
                    $arr1[$k]['state']=false;
                }else{
                    $arr1[$k]['state']=true;
                }
            }
            foreach($arr1 as $k=>$v){
                $arr2[$v['directionId']]=$v;
            }
            $arr['directionId']=$arr2[$directionId]['directionId'];
            $arr['directionName']=$arr2[$directionId]['directionName'];
            $arr['scoreAll']=$arr2[$directionId]['scoreAll'];
            $arr['state']=$arr2[$directionId]['state'];
            $arr['data']=$this->getProject($directionId);
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='saveProject'){/****************修改考核项目(条例)*********************/
            $projectId=I('post.projectId');
            $scoreAll=I('post.scoreAll');
            $dao=M('attainment_project');
            $where['projectId']=$projectId;
            $where['scId']=$scId;
            $data['projectNmae']=I('post.projectNmae');
            if($scoreAll){
                $data['scoreAll']=$scoreAll;
            }else{
                $data['scoreAll']=null;
            }
            $f=$dao->where($where)->data($data)->save();
            if($f===false){
                $arr['return']=false;
            }else{
                $arr['return']=true;
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='delDirection'){/**********************删除考核方向*********************************/
            $where['scId']=$scId;
            $where1['scId']=$scId;
            $where['directionId']=I('post.directionId');
            M()->startTrans();
            $dao1=M('attainment_direction');
            $dao2=M('attainment_project');
            $dao3=M('attainment_programme');
            $dao4=M('attainment_score');
            $dao5=M('attainment_student');
            $dao6=M('attainment_teacher');
            $dao7=M('attainment_class');
            $f1=$dao1->where($where)->delete();
            $f2=$dao2->where($where)->delete();
            $f4=$dao3->where($where)->field('programmeId')->select();
            if(!empty($f4)){
                $f3=$dao3->where($where)->delete();
                foreach($f4 as $k=>$v){
                    $arra[]=$v['programmeId'];
                }
                $arrb=implode(',',$arra);
                $where1['programmeId']=array('in',$arrb);
                $f5=$dao4->where($where1)->delete();
                $f6=$dao5->where($where1)->delete();
                $f7=$dao6->where($where1)->delete();
                $f8=$dao7->where($where1)->delete();
            }
            if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false || $f6===false || $f7===false || $f8===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='projectDel'){/***************************删除考核项目(条例)*******************************/
            $directionId=I('post.directionId');
            $projectId=I('post.projectId');
            $arr=$this->getProjectId($directionId,$projectId);
            $aa=implode(',',$arr);
            if($aa){
                $aa.=",".$projectId;
            }else{
                $aa=$projectId;
            }
            M()->startTrans();
            $dao1=M('attainment_project');
            $dao2=M('attainment_programme');
            $dao3=M('attainment_score');
            $dao4=M('attainment_student');
            $dao5=M('attainment_teacher');
            $dao6=M('attainment_class');
            $sql1="select count(projectId) as `count` from mks_attainment_project where projectId in(".$aa.") and isParent=0 and scId=".$scId;
            $f1=$dao1->query($sql1);
            if($f1['0']['count']){
                $sql2="select programmeId from mks_attainment_programme where directionId=".$directionId." and scId=".$scId;
                $f2=$dao2->query($sql2);
                if(!empty($f2)){
                    foreach($f2 as $k=>$v){
                        $arr1[]=$v['programmeId'];
                    }
                    $p=implode(',',$arr1);
                    $where1['scId']=$scId;
                    $where1['programmeId']=array('in',$p);
                    $f3=$dao3->where($where1)->delete();
                    $data1['state']=0;
                    $f5=$dao5->where($where1)->data($data1)->save();
                    $f6=$dao6->where($where1)->data($data1)->save();
                    $data1['score']=null;
                    $f4=$dao4->where($where1)->data($data1)->save();
                }
            }
            $where2['projectId']=array('in',$aa);
            $f7=$dao1->where($where2)->delete();
            if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false || $f6===false || $f7===false){
                M()->rollback();
                $arrn['return']=false;
            }else{
                M()->commit();
                $arrn['return']=true;
            }
            $this->ajaxReturn($arrn);
        }
    }
    public function programme(){/*****************方案管理************************/
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(I('get.type')=='programmeAdd'){/*******************创建考核方案************************/
            /*$data['programmeName']=I('post.programmeName');
            $data['gradeid']=I('post.gradeid');
            $data['startTime']=strtotime(I('post.startTime'));
            $data['endTime']=strtotime(I('post.endTime'));
            $data['directionId']=I('post.directionId');;
            $data['headmaster']=I('post.headmaster');
            $data['teacher']=I('post.teacher');
            $data['student']=I('post.student');
            $data['scId']=$scId;
            $data['lastRecordTime']=time();
            $data['createTime']=time();
            M()->startTrans();
            $dao=M('attainment_programme');
            $dao2=M();
            $f=$dao->add($data);
            $sql2="SELECT u.id as userId,u.name,u.gradeId,u.class as classId,u.className,u.grade as gradeName FROM `mks_user` AS u,`mks_school_rollinfo` AS r WHERE u.gradeId=".$data['gradeid']." and u.class<>'' AND u.id=r.userid AND  u.state=1 AND r.isAtSchool='是' AND u.scId=".$scId;
            $f2=$dao2->query($sql2);
            foreach($f2 as $k=>$v){
                $f2[$k]['scId']=$scId;
                $f2[$k]['lastRecordTime']=time();
                $f2[$k]['createTime']=time();
                $f2[$k]['programmeId']=$f;
            }
            $dao3=M('attainment_student');
            $f3=$dao3->addAll($f2);

            if($data['headmaster']){
                $sql5="SELECT c.`userid`,u.class FROM mks_class AS c,`mks_user` AS u,`mks_school_rollinfo` AS r WHERE c.`classid`=u.`class` AND u.state=1 AND r.isAtSchool='是' AND u.`id`=r.`userId` and c.scId=".$scId." AND c.`grade`=".$data['gradeid']." GROUP BY c.`userid`";
                $f5=$dao2->query($sql5);
                foreach($f5 as $k=>$v){
                    $arr5[$v['userid']][$v['class']]=$v['class'];
                }
            }
            if($data['teacher']){
                $sql6="SELECT classId,techerId FROM `mks_jw_schedule` WHERE `gradeId`=".$data['gradeid']." and scId=".$scId;
                $f6=$dao2->query($sql6);
                foreach($f6 as $k=>$v){
                    $arr5[$v['techerId']][$v['classId']]=$v['classId'];
                }
            }
            foreach($arr5 as $k=>$v){
                $arr6['userId']=$k;
                $arr6['class']=implode(',',array_values($v));
                $arr6['programmeId']=$f;
                $arr6['scId']=$scId;
                $arr6['lastRecordTime']=time();
                $arr6['createTime']=time();
                $arr7[]=$arr6;
            }
            $dao5=M('attainment_teacher');
            $f5=$dao5->addAll($arr7);
            if($f===false || $f2===false || $f3===false || $f5===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
            }
            $this->ajaxReturn($arr);*/
            $data['programmeName']=I('post.programmeName');
            $data['gradeid']=I('post.gradeid');
            $data['startTime']=strtotime(I('post.startTime'));
            $data['endTime']=strtotime(I('post.endTime'))+3600*24-1;
            $data['directionId']=I('post.directionId');;
            $data['headmaster']=I('post.headmaster');
            $data['teacher']=I('post.teacher');
            $data['student']=I('post.student');
            $data['scId']=$scId;
            $data['lastRecordTime']=time();
            $data['createTime']=time();
            M()->startTrans();
            $dao=M('attainment_programme');
            $dao2=M();
            $f=$dao->add($data);
            $sql1="SELECT classid AS classId,userid FROM `mks_class` WHERE grade=".$data['gradeid']." AND scId=".$scId;
            $f1=$dao2->query($sql1);
            foreach($f1 as $k=>$v){
                $gar[]=$v['classId'];
                $headm[$v['classId']][$v['userid']]=1;
            }
            $aaa=implode(',',$gar);
            $roleId=$this::$studentRoleId;
            $sql2="SELECT u.id as userId,u.name,u.gradeId,u.class as classId,u.className,u.grade as gradeName FROM `mks_user` AS u,`mks_school_rollinfo` AS r WHERE u.gradeId=".$data['gradeid']." and u.class<>'' and u.class in(".$aaa.") AND u.id=r.userid AND  u.state=1 AND u.scId=".$scId." AND u.roleId=".$roleId." and r.isAtSchool='是' and u.roleId=".$this::$studentRoleId." order by u.className";
            $f2=$dao2->query($sql2);
            if(!$f2['0']['userId']){
                $arr['return']=false;
                $arr['msg']='该年级下无班级学生或完整的任教信息!';
                $this->ajaxReturn($arr);
            }
            foreach($f2 as $k=>$v){
                $f2[$k]['scId']=$scId;
                $f2[$k]['lastRecordTime']=time();
                $f2[$k]['createTime']=time();
                $f2[$k]['programmeId']=$f;
                $ack[]=$v['userId'];
            }
            $dao3=M('attainment_student');
            $f3=$dao3->addAll($f2);
            if($data['headmaster']){
                $sql5="SELECT c.`userid`,c.classid AS class FROM mks_class AS c,`mks_user` AS u WHERE c.classid IN($aaa) AND u.state=1 AND u.`id`=c.`userId` AND c.scId=".$scId." AND c.`grade`=".$data['gradeid'];
                //$sql5z="SELECT counmt(*) as sum FROM mks_class as c WHERE c.classid in(".$aaa.") and c.scId=".$scId;
                $f5=$dao2->query($sql5);
                foreach($f5 as $k=>$v){
                    if($v['userid']){
                        $arr5[$v['userid']][$v['class']]=$v['class'];
                        $arrc[$v['class']][]=$v['userid'];
                    }
                }
                if(count($gar)>($k+1)){
                    $arr['return']=false;
                    $arr['msg']='该年级下无班级学生或完整的任教信息!';
                    $this->ajaxReturn($arr);
                }
            }
            if($data['teacher']){
                $sql6="SELECT s.classId,s.techerId FROM `mks_jw_schedule` as s,mks_user as u WHERE s.classId in(".$aaa.") and s.`gradeId`=".$data['gradeid']." and s.scId=".$scId." and s.techerId=u.id";
                $f6=$dao2->query($sql6);
                foreach($f6 as $k=>$v){
                    $arr5[$v['techerId']][$v['classId']]=$v['classId'];
                    $arrc[$v['classId']][]=$v['techerId'];
                    $arrabc[$v['classId']]=$v['classId'];
                }
                if(count($gar)>count($arrabc)){
                    $arr['return']=false;
                    $arr['msg']='该年级下无班级学生或完整的任教信息!';
                    $this->ajaxReturn($arr);
                }
            }
            foreach($arrc as $k=>$v){
                $arrc[$k]=array_unique($arrc[$k]);
            }
            foreach($arrc as $k=>$v){
                foreach($v as $a=>$b){
                    $arrd['classId']=$k;
                    $arrd['userId']=$b;
                    $arrd['programmeId']=$f;
                    $arrd['scId']=$scId;
                    $arrd['lastRecordTime']=time();
                    $arrd['createTime']=time();
                    if($headm[$k][$b]){
                        $arrd['headmaster']=1;
                    }else{
                        $arrd['headmaster']=0;
                    }


                    $arre[]=$arrd;
                }
            }
            $dao5=M('attainment_class');
            if(!empty($arre)){
                $f7=$dao5->addALL($arre);
            }
            foreach($arr5 as $k=>$v){
                $arr6['userId']=$k;
                $arr6['class']=implode(',',array_values($v));
                $arr6['programmeId']=$f;
                $arr6['scId']=$scId;
                $arr6['lastRecordTime']=time();
                $arr6['createTime']=time();
                $ack[]=$k;
                $arr7[]=$arr6;
            }
            /*$datan['messageId']=implode(',',$ack);
            $datan['isPerson']=1;
            $datan['title']='学生素养评分通知';
            $datan['type']='通知公告';
            $datan['content']='请填写方案:'.$data['programmeName'].'的评分';
            $daon=M('message');
            $f9=$daon->add($datan);*/
            $dao5=M('attainment_teacher');
            if(!empty($arr7)){
                $f5=$dao5->addAll($arr7);
            }
            //if($f===false || $f1===false || $f2===false || $f3===false || $f5===false || $f6===false || $f7===false || $f9===false){
            if($f===false || $f1===false || $f2===false || $f3===false || $f5===false || $f6===false || $f7===false){
                M()->rollback();
                $arr['return']=false;
                $arr['msg']='创建失败!';
            }else{
                M()->commit();
                $arr['return']=true;
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='findGrade'){/********************年级列表***********************/
            $arr=$this->getGrade();
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='findDirection'){/********************考核方向列表***********************/
            $arr=$this->getDirectionList(false);
            foreach($arr as $k=>$v){
                unset($arr[$k]['scoreAll']);
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='programmeUpdate'){/***************************修改考核方案**************************/
            $where['programmeId']=I('post.programmeId');
            $where['scId']=$scId;
            $data['programmeName']=I('post.programmeName');
            //$data['gradeid']=I('post.gradeid');
            $data['startTime']=strtotime(I('post.startTime'));
            $data['endTime']=strtotime(I('post.endTime'))+3600*24-1;
            //$data['directionId']=I('post.directionId');;
            //$data['headmaster']=I('post.headmaster');
            //$data['teacher']=I('post.teacher');
            //$data['student']=I('post.student');
            $data['lastRecordTime']=time();
            $dao=M('attainment_programme');
            $f=$dao->where($where)->save($data);
            if($f===false){
                $arr['return']=false;
            }else{
                $arr['return']=true;
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='programeDel'){/************************删除考核方案***********************/
            $where['programmeId']=I('post.programmeId');
            $where['scId']=$scId;
            M()->startTrans();
            $dao1=M('attainment_programme');
            $f1=$dao1->where($where)->delete();
            $dao2=M('attainment_score');
            $f2=$dao2->where($where)->delete();
            $dao3=M('attainment_student');
            $f3=$dao3->where($where)->delete();
            $dao4=M('attainment_teacher');
            $f4=$dao4->where($where)->delete();
            $dao5=M('attainment_class');
            $f5=$dao5->where($where)->delete();
            if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='programmeFind'){/*********************考核方案查询***********************/
            $dao1=M();
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $where['scId']=$scId;
            $userId=$getSession['userId'];
            $roleId=$getSession['roleId'];
            if($roleId==$this::$teacherRoleId){/************修改，加上年级主任的权限，当教师不为年级主任时返回return=false，当通过验证时返回return=true，使用时去掉注释，返回$data************/
                $sqla="SELECT gradeid FROM `mks_grade` WHERE userid=".$userId." and scId=".$where['scId'];
                $fa=$dao1->query($sqla);
                if(!empty($fa)){
                    foreach($fa as $k=>$v){
                        $aa[]=$v['gradeid'];
                    }
                    $gradeIdAll=implode(',',$aa);
                    $st=" and p.gradeid IN(".$gradeIdAll.")";
                    //$st=""
                }else{
                    $arr=array();
                    $data['data']=$arr;
                    $data['return']=false;
                    $this->ajaxReturn($data);
                    exit;
                }
            }
            $sql1="SELECT p.`programmeId`,p.`programmeName`,d.`directionName`,p.`startTime`,p.`endTime`,p.`createTime`,p.gradeid,p.directionId,p.headmaster,p.teacher,p.student FROM `mks_attainment_programme` AS p,`mks_attainment_direction` AS d WHERE p.`directionId`=d.`directionId` AND p.`scId`=".$scId.$st;
            $f1=$dao1->query($sql1);
            $sql2="SELECT COUNT(id) as `count`,programmeId FROM `mks_attainment_student` WHERE scId=".$scId." GROUP BY programmeId";
            $f2=$dao1->query($sql2);
            $sql3="SELECT COUNT(id) as `count`,programmeId FROM `mks_attainment_student` WHERE scId=".$scId." and state=1 GROUP BY programmeId";
            $f3=$dao1->query($sql3);
            foreach($f2 as $k=>$v){
                $arr2[$v['programmeId']]=$v['count'];
            }
            foreach($f3 as $k=>$v){
                $arr3[$v['programmeId']]=$v['count'];
            }
            foreach($f1 as $k=>$v){
                $f1[$k]['startTime']=date('Y-m-d',$v['startTime']);
                $f1[$k]['endTime']=date('Y-m-d',$v['endTime']);
                $f1[$k]['createTime']=date('Y-m-d',$v['createTime']);
                if(!$arr3[$v['programmeId']]){
                    $arr3[$v['programmeId']]=0;
                }
                $f1[$k]['schedule']=$arr3[$v['programmeId']].'/'.$arr2[$v['programmeId']];
                $f1[$k]['percentage']=round(($arr3[$v['programmeId']]/$arr2[$v['programmeId']])*100,2);
            }
            $arr=$this->sortArrByOneField($f1,$find);
            if(empty($arr)){
                $arr=array();
            }
            $data['data']=$arr;
            $data['return']=true;
            $this->ajaxReturn($data);
        }elseif(I('get.type')=='xiangqing'){/********************考核进度详情************************/
            $programmeId=I('post.programmeId');
            $sql1="SELECT a.`userId`,a.`classId`,a.`state`,u.`name`,c.`classname` AS className,g.`name` AS gradeName FROM `mks_attainment_class` AS a,mks_user AS u,mks_class AS c,mks_grade AS g,`mks_attainment_programme` AS p WHERE a.programmeId=".$programmeId." AND a.scId=".$scId." AND a.`userId`=u.`id` AND p.`programmeId`=a.`programmeId` AND a.`classId`=c.`classid` AND p.`gradeid`=g.`gradeid`";
            $dao=M();
            $headmaster['title'][0]['titleName']='年级';
            $headmaster['title'][0]['props']='gradeName';
            $headmaster['title'][1]['titleName']='班级';
            $headmaster['title'][1]['props']='className';
            $headmaster['title'][2]['titleName']='姓名';
            $headmaster['title'][2]['props']='name';
            $headmaster['title'][3]['titleName']='是否发布';
            $headmaster['title'][3]['props']='state';

            $teacher['title'][0]['titleName']='年级';
            $teacher['title'][0]['props']='gradeName';
            $teacher['title'][1]['titleName']='班级';
            $teacher['title'][1]['props']='className';
            $teacher['title'][2]['titleName']='科目';
            $teacher['title'][2]['props']='subject';
            $teacher['title'][3]['titleName']='姓名';
            $teacher['title'][3]['props']='name';
            $teacher['title'][4]['titleName']='是否发布';
            $teacher['title'][4]['props']='state';
            $student['title'][0]['titleName']='年级';
            $student['title'][0]['props']='gradeName';
            $student['title'][1]['titleName']='班级';
            $student['title'][1]['props']='className';
            $student['title'][2]['titleName']='姓名';
            $student['title'][2]['props']='name';
            $student['title'][3]['titleName']='是否发布';
            $student['title'][3]['props']='state';
            $f1=$dao->query($sql1);
            foreach($f1 as $k=>$v){
                $aaac[$v['userId']][$v['classId']]=$v;
            }
            $sql2="SELECT classId FROM `mks_attainment_class` WHERE programmeId=".$programmeId." AND scId=".$scId." GROUP BY classId";
            $f2=$dao->query($sql2);
            foreach($f2 as $k=>$v){
                $arrc[]=$v['classId'];
            }
            $class=implode(',',$arrc);
            $sql3="SELECT userid,classid FROM mks_class WHERE classid IN(".$class.") and scId=".$scId;
            $f3=$dao->query($sql3);
            $sql4="SELECT j.techerId as userId,s.subjectname FROM `mks_jw_schedule` AS j,mks_subject AS s WHERE j.classId IN(".$class.") AND j.scId=".$scId." AND s.subjectid=j.subjectId GROUP BY j.techerId,j.subjectId";
            $f4=$dao->query($sql4);
            foreach($f4 as $k=>$v){
                $arrs[$v['userId']][]=$v['subjectname'];
            }
            foreach($arrs as $k=>$v){
                $subject[$k]=implode(',',$v);
            }
            foreach($f3 as $k=>$v){
                $hesr[$v['classid']][]=$v['userid'];
                $head[$v['userid']]=$v['classid'];
            }
            foreach($f1 as $k=>$v){
                if(in_array($v['userId'],$hesr[$v['classId']])){
                    if($aaac[$v['userId']][$v['classId']]['state']){
                        $headr['state']='是';
                    }else{
                        $headr['state']='否';
                    }
                    $headr['name']=$aaac[$v['userId']][$v['classId']]['name'];
                    $headr['className']=$aaac[$v['userId']][$v['classId']]['className'];
                    $headr['gradeName']=$arrgrade[$aaac[$v['userId']][$v['classId']]['gradeName']-1];
                    $headmaster['data'][]=$headr;
                    unset($f1[$k]);
                    //unset($aaac[$v['userId']][$head[$v['userId']]]);
                }
            }
            foreach($f1 as $k=>$v){
                $aaaaa[$v['userId']]['className'][]=$v['className'];
                $aaaaa[$v['userId']]['gradeName']=$arrgrade[$v['gradeName']-1];
                $aaaaa[$v['userId']]['name']=$v['name'];
                if($v['state']){
                    $aaaaa[$v['userId']]['state']='是';
                }else{
                    $aaaaa[$v['userId']]['state']='否';
                }
            }
            foreach($aaaaa as $k=>$v){
                $aaaaa[$k]['className']=array_unique($v['className']);
            }
            foreach($aaaaa as $k=>$v){
                $aaaaa[$k]['className']=implode(',',$v['className']);
                $aaaaa[$k]['subject']=$subject[$k];
            }
            $teacher['data']=array_values($aaaaa);
            $sql5="SELECT s.userId,u.`name`,g.`name` AS gradeName,c.`classname` AS className FROM `mks_attainment_student` AS s,mks_class AS c,mks_grade AS g,mks_user as u WHERE u.id=s.userId AND s.programmeId=".$programmeId." AND s.scId=".$scId." AND s.`classId`=c.`classid` AND s.`gradeId`=g.`gradeid` order by c.classname";
            $f5=$dao->query($sql5);
            $sql6="select studentId as userId from mks_attainment_score where `programmeId`=".$programmeId." and state=1 and `studentId`=`ratersId` and scId=".$scId;
            $f6=$dao->query($sql6);
            foreach($f6 as $k=>$v){
                $arrjp[]=$v['userId'];
            }

            foreach($f5 as $k=>$v){
                $stu['name']=$v['name'];
                $stu['gradeName']=$arrgrade[$v['gradeName']-1];
                $stu['className']=$v['className'];
                if(in_array($v['userId'],$arrjp)){
                    $stu['state']='是';
                }else{
                    $stu['state']='否';
                }
                $student['data'][]=$stu;
            }
            if(!$f5){
                $student['data']=array();
            }
            if(!$f1){
                $headmaster['data']=array();
            }
            if(!$aaaaa){
                $teacher['data']=array();
            }
            $dataa['headmaster']=$headmaster;
            $dataa['teacher']=$teacher;
            $dataa['student']=$student;
            $sqlzz="SELECT headmaster,teacher,student FROM `mks_attainment_programme` WHERE programmeId=".$programmeId." AND scId=".$scId;
            $fzz=$dao->query($sqlzz);
            if(!$fzz[0]['headmaster']){
                $dataa['headmaster']['data']=array();
            }
            if(!$fzz[0]['teacher']){
                $dataa['teacher']['data']=array();
            }
            if(!$fzz[0]['student']){
                $dataa['student']['data']=array();
            }
            $this->ajaxReturn($dataa);
        }
    }
    public function detailed(){/*************************考核明细*****************************/
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $roleId=$getSession['roleId'];
        $userId=$getSession['userId'];
        if(I('get.type')=='findProgramme'){/***************考核方案列表*****************/
            $arr=$this->getProgramme();
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='findStudentList'){/************考核学生列表************/
            $dao=M('attainment_student');
            $where['scId']=$scId;
            $programmeId=I('post.programmeId');
            $where['programmeId']=$programmeId;
            $find=I('post.find');
            if($roleId==$this::$teacherRoleId){
                $sqla="SELECT g.userid FROM `mks_attainment_programme` AS p,mks_grade AS g WHERE p.`gradeid`=g.`gradeid` AND p.`programmeId`=".$programmeId." AND p.`scId`=".$scId;
                $daoa=M();
                $fa=$daoa->query($sqla);
                if($userId!=$fa['0']['userid']){
                    $sqlb="select class from `mks_attainment_teacher` where userId=".$userId." and programmeId=".$programmeId." and scId=".$scId;
                    $fb=$daoa->query($sqlb);
                    if(!empty($fb)){
                        $where['classId']=array('in',$fb['0']['class']);
                        //$ac=" and classId In(".$fb['0']['class'].")";
                        $ac=" and s.classId In(".$fb['0']['class'].")";
                    }else{
                        $aaa=array();
                        $this->ajaxReturn($aaa);
                        exit;
                    }
                }
            }
            /*if($find!=''){
                $st=" and (className like '%".$find."%' or name like '%".$find."%' or gradeName like '%".$find."%' or name like '%".$find."%' or name like '%".$find."%')";
            }*/
            if($find==null){
                $find=false;
            }
            //$sql="select userId,name,gradeId,gradeName,classId,className from mks_attainment_student where scId=".$scId.$st." and programmeId=".$programmeId.$ac;
            $sql="SELECT s.userId,u.name,s.gradeId,s.classId FROM `mks_attainment_student` AS s,mks_user AS u WHERE s.programmeId=".$programmeId." AND u.id=s.userId and s.scId=".$scId.$ac;
            $f=$dao->query($sql);
            $sql2="SELECT g.name as gradeName,p.gradeid as gradeId FROM `mks_attainment_programme` AS p,mks_grade AS g WHERE p.programmeId=".$programmeId." AND p.gradeid=g.gradeid AND p.scId=".$scId;
            $f2=$dao->query($sql2);
            $sql3="SELECT c.`classname` AS className,a.`classId` FROM `mks_attainment_class` AS a,mks_class AS c WHERE a.`classId`=c.`classid` AND a.`programmeId`=".$programmeId." AND a.`scId`=".$scId;
            $f3=$dao->query($sql3);
            foreach($f2 as $k=>$v){
                $grad[$v['gradeId']]=$v['gradeName'];
            }
            foreach($f3 as $k=>$v){
                $clas[$v['classId']]=$v['className'];
            }
            /*if(empty($f)){
                $f=$dao->field('userId,name,gradeId,gradeName,classId,className')->where($where)->select();
            }*/
            foreach($f as $k=>$v){
                $f[$k]['gradeName']=$arrgrade[$grad[$v['gradeId']]-1];
                $f[$k]['className']=$clas[$v['classId']];
            }
            $f=$this->sortArrByOneField($f,$find);
            foreach($f as $k=>$v){
                $classId=$v['classId'];
                $arr['name']=$v['gradeName'];
                $arr['gradeId']=$v['gradeId'];
                $arr2[$classId]['name']=$v['className'];
                $arr2[$classId]['classId']=$v['classId'];
                unset($v['gradeName']);
                unset($v['gradeId']);
                unset($v['className']);
                unset($v['classId']);
                $arr2[$classId]['childs'][]=$v;
            }
            if(!$arr2){
                $arr2=array();
            }
            $arr['childs']=array_values($arr2);
            $arrc[]=$arr;
            $this->ajaxReturn($arrc);
        }elseif(I('get.type')=='mingxi'){/*********************考核明细*******************/
            $programmeId=I('post.programmeId');
            $userId=I('post.userId');
            $daoa=M('attainment_student');
            $wherea['userId']=$userId;
            $wherea['programmeId']=$programmeId;
            $wherea['scId']=$scId;
            $sqla="SELECT s.score,u.name FROM `mks_attainment_student` AS s,mks_user AS u WHERE s.userId=u.`id` AND s.programmeId=".$programmeId." AND s.scId=".$scId." AND s.userId=".$userId;
            $fa=$daoa->query($sqla);
            $dao1=M();
            $sql="SELECT p.`programmeName`,d.directionId,d.directionName,SUM(t.`scoreAll`) AS scoreAll,p.`headmaster`,p.`teacher`,p.`student` FROM `mks_attainment_programme` AS p,`mks_attainment_direction` AS d,`mks_attainment_project` AS t WHERE t.`directionId`=p.`directionId` AND t.`isParent`=0 AND programmeId=".$programmeId." AND p.scId=".$scId." AND p.directionId=d.directionId";
            $f1=$dao1->query($sql);
            $arr5=array();
            $sql4="SELECT studentId AS userId,role,score FROM `mks_attainment_score` WHERE studentId=".$userId." and programmeId=".$programmeId." and scId=".$scId;
            $f4=$dao1->query($sql4);
            foreach($f4 as $k=>$v){
                $arr3=json_decode($v['score'],true);
                foreach($arr3 as $a=>$b){
                    $arr5[$v['role']][$a][]=$b;
                }
            }
            foreach($arr5 as $k=>$v){
                foreach($v as $a=>$b){
                    $arr6[$a][$k]=array_sum($b)/count($b);
                }
            }
            foreach($f1 as $k=>$v){
                $arr['programmeName']=$v['programmeName'];
                $arr['directionName']=$v['directionName'];
                $arr['name']=$fa['0']['name'];
                $arr['scoreAll']=$v['scoreAll'];
                $arr['score']=$fa['0']['score'];
                if($v['headmaster']){
                    $appraiser[]='班主任';
                    $appraiser1['1']='headmaster';
                    $appraiser3['1']=$v['headmaster'];
                    $appraiser4['name']='（班主任评'.$v['headmaster'].'%)';
                    $appraiser4['props']='headmaster';
                    $arr1[]=$appraiser4;
                }
                if($v['teacher']){
                    $appraiser[]='任课教师';
                    $appraiser1['2']='teacher';
                    $appraiser3['2']=$v['teacher'];
                    $appraiser4['name']='（任课教师评'.$v['teacher'].'%)';
                    $appraiser4['props']='teacher';
                    $arr1[]=$appraiser4;
                }

                if($v['student']){
                    $appraiser[]='学生本人';
                    $appraiser1['3']='student';
                    $appraiser3['3']=$v['student'];
                    $appraiser4['name']='（学生本人评'.$v['student'].'%)';
                    $appraiser4['props']='student';
                    $arr1[]=$appraiser4;
                }
                $arr['appraiser']=implode('、',$appraiser);
                $arr['title']=$arr1;
            }
            foreach($arr6 as $k=>$v){
                foreach($appraiser3 as $a=>$b){
                    $arr6[$k]['all']+=$v[$a]*$b/100;
                }
            }
            /*foreach($arr5 as $k=>$v){

            }*/
            $where['directionId']=$f1['0']['directionId'];
            $where['scId']=$scId;
            $dao=M('attainment_project');
            $f=$dao->field('projectId,projectNmae,pid,level,isParent,scoreAll')->where($where)->select();
            foreach($f as $k=>$v){
                foreach($appraiser1 as $a=>$b){
                    $f[$k][$b]=$arr6[$v['projectId']][$a];
                }
                $f[$k]['all']=$arr6[$v['projectId']]['all'];
            }
            foreach($f as $k=>$v){
                if($v['isParent']==0){
                    $f[$k]['projectNmaeRules']=$v['projectNmae'];
                    $f[$k]['projectNmae']=null;
                }else{
                    $f[$k]['projectNmaeRules']=null;
                }
            }
            $arr['list']=$this->make_tree1($f);
            //$arr['list']=$this->getProject($f1['0']['directionId']);
            $this->ajaxReturn($arr);
        }
    }
    public function junfen(){/*****************均分统计***************/
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(I('get.type')=='findProgramme'){/********************考核方案列表******************/
            $arr=$this->getProgramme();
            $this->ajaxReturn($arr);
        }
        if(I('get.type')=='junfenFind'){/****************均分列表******************/
            $dao=M();
            $programmeId=I('post.programmeId');
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $sql="SELECT AVG(score) AS score,count(userId) as `count`,gradeName,className FROM `mks_attainment_student` WHERE programmeId=".$programmeId." AND state=1 and scId=".$scId." GROUP BY gradeId,classId";
            $f1=$dao->query($sql);
            foreach($f1 as $k=>$v){
                $f1[$k]['gradeName']=$arrgrade[$v['gradeName']-1];
            }
            $arr=$this->sortArrByOneField($f1,$find);
            $this->ajaxReturn($arr);
        }
        if(I('get.type')=='export'){
            $dao=M();
            $programmeId=I('get.programmeId');
            $sql="SELECT gradeName,className,count(userId) as `count`,AVG(score) AS score FROM `mks_attainment_student` WHERE programmeId=".$programmeId." AND state=1 and scId=".$scId." GROUP BY gradeId,classId";
            $f1=$dao->query($sql);
            foreach($f1 as $k=>$v){
                $f1[$k]['gradeName']=$arrgrade[$v['gradeName']-1];
            }
            $header=array('年级','班级','被评人数','平均分数');
            $this->getExport($header,$f1);
        }
    }
    public function pingfen(){/*********************学生素养评分*********************/
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(I('get.type')=='pingfentongji'){/*************方案评分信息***************/
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $userId=$getSession['userId'];
            $roId=$getSession['roleId'];
            $studentRoleId=$this::$studentRoleId;
            $teacherRoleId=$this::$teacherRoleId;
            $time=time();
            $dao=M();
            if($roId==$studentRoleId){
                $sql1="SELECT p.`programmeId`,p.`programmeName`,d.directionName,p.`createTime` FROM `mks_attainment_student` AS s,`mks_attainment_programme` AS p,`mks_attainment_direction` AS d WHERE p.student>0 AND p.startTime<=".$time." and p.endTime>=".$time." and p.scId=".$scId." and p.`directionId`=d.directionId AND s.`programmeId`=p.`programmeId` and p.student<>0 AND s.`userId`=".$userId;
                $f1=$dao->query($sql1);
                $sql2="SELECT programmeId,state FROM `mks_attainment_score` WHERE ratersId=".$userId." AND scId=".$scId." ORDER BY programmeId";
                $f2=$dao->query($sql2);
                foreach($f2 as $k=>$v){
                    $arr2[$v['programmeId']]['programmeId']=1;
                    $arr2[$v['programmeId']]['state']=$v['state'];
                }
                foreach($f1 as $k=>$v){
                    if(!$arr2[$v['programmeId']]){
                        $arr2[$v['programmeId']]['programmeId']=0;
                        $arr2[$v['programmeId']]['state']=2;
                    }
                    $f1[$k]['createTime']=date('Y-m-d',$v['createTime']);
                    $f1[$k]['schedule']=$arr2[$v['programmeId']]['programmeId'].'/1';
                    $f1[$k]['state']=$arr2[$v['programmeId']]['state'];
                    $f1[$k]['percentage']=round(($arr2[$v['programmeId']]['programmeId']/1)*100,2);
                }
            }else{
                $sql1="SELECT p.`programmeId`,p.`programmeName`,d.directionName,p.`createTime`,s.class,s.state FROM `mks_attainment_teacher` AS s,`mks_attainment_programme` AS p,`mks_attainment_direction` AS d WHERE p.startTime<=".$time." and p.endTime>=".$time." and p.scId=".$scId." and p.`directionId`=d.directionId AND s.`programmeId`=p.`programmeId` AND s.`userId`=".$userId;
                $f1=$dao->query($sql1);
                foreach($f1 as $k=>$v){
                    $sql2="SELECT COUNT(DISTINCT e.`studentId`) AS `count` FROM `mks_attainment_student` AS s,`mks_attainment_score` AS e WHERE s.programmeId=".$v['programmeId']." and s.programmeId=e.programmeId and s.scId=".$scId." and s.classId IN(".$v['class'].") AND e.`studentId`=s.`userId` AND e.`ratersId`=".$userId." GROUP BY e.`programmeId`";
                    $f2=$dao->query($sql2);
                    $sql3="SELECT COUNT(DISTINCT `userId`) AS `count` FROM `mks_attainment_student` WHERE scId=".$scId." AND programmeId=".$v['programmeId']." AND classId IN(".$v['class'].")";
                    $f3=$dao->query($sql3);
                    unset($f1[$k]['class']);
                    $f1[$k]['createTime']=date('Y-m-d',$v['createTime']);
                    if($v['state']==0){
                        if($f3['0']['count']>$f2['0']['count']){
                            $f1[$k]['state']=2;
                            if(!$f2['0']['count']){
                                $f2['0']['count']=0;
                            }
                            //$f1[$k]['schedule']=$f2['0']['count'].'/'.$f3['0']['count'];
                        }elseif($f3['0']['count']==$f2['0']['count']){
                            $f1[$k]['state']=0;
                        }
                        //$f1[$k]['schedule']=$f2['0']['count'].'/'.$f3['0']['count'];
                    }
                    $f1[$k]['schedule']=$f2['0']['count'].'/'.$f3['0']['count'];
                    $f1[$k]['percentage']=round(($f2['0']['count']/$f3['0']['count'])*100,2);
                }
            }
            $arr=$this->sortArrByOneField($f1,$find);
            if(!$arr){
                $arr=array();
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='pingfenxinxi'){
            $userId=$getSession['userId'];
            $roId=$getSession['roleId'];
            $studentRoleId=$this::$studentRoleId;
            $teacherRoleId=$this::$teacherRoleId;
            $programmeId=I('post.programmeId');
            $dao=M();
            if($roId==$studentRoleId){
                $sql2="SELECT g.`name` as gradename,c.`classname`,u.`name` FROM `mks_attainment_student` AS s,mks_user AS u,mks_class AS c,mks_grade AS g WHERE s.`programmeId`=".$programmeId." AND s.userId=".$userId." and s.`scId`=".$scId." AND s.`classId`=c.`classid` AND s.`gradeId`=g.`gradeid` AND s.`userId`=u.`id` order by c.classname asc";
                $f2=$dao->query($sql2);
                $sql3="SELECT count(*) as num FROM `mks_attainment_score` WHERE ratersId=".$userId." AND scId=".$scId." AND programmeId=".$programmeId;
                $f3=$dao->query($sql3);
                if($f3['0']['num']){
                    $f2['0']['score']=true;
                }else{
                    $f2['0']['score']=false;
                }
            }else{
                $sql1="SELECT classId FROM `mks_attainment_class` WHERE programmeId=".$programmeId." AND scId=".$scId." AND userId=".$userId;
                $f1=$dao->query($sql1);
                foreach($f1 as $k=>$v){
                    $classa[]=$v['classId'];
                }
                $class=implode(',',$classa);
                $sql2="SELECT g.`name` as gradename,c.`classname`,u.`name`,u.id FROM `mks_attainment_student` AS s,mks_user AS u,mks_class AS c,mks_grade AS g WHERE s.`programmeId`=".$programmeId." AND s.classId in(".$class.") and s.`scId`=".$scId." AND s.`classId`=c.`classid` AND s.`gradeId`=g.`gradeid` AND s.`userId`=u.`id` order by c.classname asc";
                $f2=$dao->query($sql2);
                $sql3="SELECT studentId FROM `mks_attainment_score` WHERE ratersId=".$userId." AND scId=".$scId." AND programmeId=".$programmeId;
                $f3=$dao->query($sql3);
                foreach($f3 as $k=>$v){
                    $az[$v['studentId']]=true;
                }
                foreach($f2 as $k=>$v){
                    if($az[$v['id']]){
                        $f2[$k]['score']=true;
                    }else{
                        $f2[$k]['score']=false;
                    }
                    unset($f2[$k]['id']);
                }
            }
            if(!$f2){
                $f2=array();
            }
            $this->ajaxReturn($f2);
        }elseif(I('get.type')=='findStudentList'){/******************学生列表*****************/
            $userId=$getSession['userId'];
            $roId=$getSession['roleId'];
            $studentRoleId=$this::$studentRoleId;
            $teacherRoleId=$this::$teacherRoleId;
            $dao=M('attainment_student');
            $dao1=M('attainment_teacher');
            $where['scId']=$scId;
            $programmeId=I('post.programmeId');
            $where['programmeId']=$programmeId;
            $where['userId']=$userId;
            $find=I('post.find');
            if($find==null){
                $find=false;
            }
            if($roId==$studentRoleId){
                $sql="select userId,name,gradeId,gradeName,classId,className from mks_attainment_student where userId=".$userId." and scId=".$scId." and programmeId=".$programmeId;
            //}elseif($this->getRole($scId,$userId)){
                $sql3="SELECT c.`classname` AS className,a.`classId` FROM `mks_attainment_student` AS a,mks_class AS c WHERE a.`classId`=c.`classid` AND a.userId=".$userId." AND a.`programmeId`=".$programmeId." AND a.`scId`=".$scId;
                $f3=$dao->query($sql3);
            }else{
                $f1=$dao1->where($where)->field('class')->select();
                $sql="select userId,name,gradeId,gradeName,classId,className from mks_attainment_student where classId in(".$f1['0']['class'].") and scId=".$scId." and programmeId=".$programmeId;
                $sql3="SELECT c.`classname` AS className,a.`classId` FROM `mks_attainment_class` AS a,mks_class AS c WHERE a.`classId`=c.`classid` AND a.`programmeId`=".$programmeId." AND a.`scId`=".$scId;
                $f3=$dao->query($sql3);
            }
            $f=$dao->query($sql);
            $sql2="SELECT g.name as gradeName,p.gradeid as gradeId FROM `mks_attainment_programme` AS p,mks_grade AS g WHERE p.programmeId=".$programmeId." AND p.gradeid=g.gradeid AND p.scId=".$scId;
            $f2=$dao->query($sql2);
            foreach($f2 as $k=>$v){
                $grad[$v['gradeId']]=$v['gradeName'];
            }
            foreach($f3 as $k=>$v){
                $clas[$v['classId']]=$v['className'];
            }
            foreach($f as $k=>$v){
                $f[$k]['gradeName']=$arrgrade[$grad[$v['gradeId']]-1];
                $f[$k]['className']=$clas[$v['classId']];
            }
            $f=$this->sortArrByOneField($f,$find);
            /*if(empty($f)){
                if($roId!=$studentRoleId){
                    unset($where['userId']);
                    $where['classId']=array('in',$f1['0']['class']);
                }
                $f=$dao->field('userId,name,gradeId,gradeName,classId,className')->where($where)->select();
            }*/
            if($f){
                foreach($f as $k=>$v){
                    $classId=$v['classId'];
                    $arr['name']=$v['gradeName'];
                    $arr['gradeId']=$v['gradeId'];
                    $arr2[$classId]['name']=$v['className'];
                    $arr2[$classId]['classId']=$v['classId'];
                    unset($v['gradeName']);
                    unset($v['gradeId']);
                    unset($v['className']);
                    unset($v['classId']);
                    $arr2[$classId]['childs'][]=$v;
                }
                $arr['childs']=array_values($arr2);
                $arrc['student'][]=$arr;
            }else{
                $arrc['student']=array();
            }
            $sqla="SELECT startTime,endTime FROM `mks_attainment_programme` WHERE programmeId=".$programmeId." and scId=".$scId;
            $fa=$dao->query($sqla);
            $arrc['time']['startTime']=date('Y-m-d',$fa['0']['startTime']);
            $arrc['time']['endTime']=date('Y-m-d',$fa['0']['endTime']);
            $this->ajaxReturn($arrc);
        }elseif(I('get.type')=='findprogramme'){/*****************方案列表*****************/
            $userId=$getSession['userId'];
            $roId=$getSession['roleId'];
            $studentRoleId=$this::$studentRoleId;
            $teacherRoleId=$this::$teacherRoleId;
            $dao=M();
            if($roId==$studentRoleId){
                $sql1="SELECT p.`programmeId`,p.`programmeName` FROM `mks_attainment_student` AS s,`mks_attainment_programme` AS p,mks_attainment_score as c WHERE c.`programmeId`=p.`programmeId` AND c.studentId=c.ratersId and c.studentId=s.`userId` AND s.`programmeId`=p.`programmeId` AND s.`scId`=".$scId." AND s.`userId`=".$userId;
                $f1=$dao->query($sql1);
            }elseif($this->getRole($scId,$userId)){
                $sql1="SELECT p.`programmeId`,p.`programmeName` FROM `mks_attainment_teacher` AS s,`mks_attainment_programme` AS p WHERE s.`programmeId`=p.`programmeId` AND s.`scId`=".$scId." AND s.`userId`=".$userId;
                $f1=$dao->query($sql1);
            }
            if(!$f1){
                $f1=array();
            }
            $this->ajaxReturn($f1);
        }elseif(I('get.type')=='pingfenfind'){/**************评分查询****************/
            $ratersId=$getSession['userId'];
            $roId=$getSession['roleId'];
            $studentRoleId=$this::$studentRoleId;
            $teacherRoleId=$this::$teacherRoleId;
            $programmeId=I('post.programmeId');
            $classId=I('post.classId');

            $userId=I("post.userId");
            $sqlaz="select s.classId,u.name from mks_attainment_student as s,mks_user as u where s.userId=".$userId." and s.programmeId=".$programmeId." and s.userId=u.id and s.scId=".$scId;
            $dao=M();
            $faz=$dao->query($sqlaz);
            $classId=$faz['0']['classId'];
            $daoa=M('attainment_score');
            $wherea['ratersId']=$ratersId;
            $wherea['programmeId']=$programmeId;
            $wherea['scId']=$scId;
            $sqla="select u.`name`,c.`score` from `mks_attainment_score` as c,`mks_attainment_student` as s,mks_user as u where u.id=".$userId." and c.`ratersId`=".$ratersId." and c.`studentId`=".$userId." and c.programmeId=".$programmeId." and s.programmeId=c.programmeId and c.`studentId`=s.`userId` and c.`scId`=".$scId." limit 0,1";
            $fa=$daoa->query($sqla);
            $fa['0']['score']=array_sum(json_decode($fa['0']['score'],true));
            $dao1=M();
            $sql="SELECT p.`programmeName`,d.directionId,d.directionName,SUM(t.`scoreAll`) AS scoreAll,p.`headmaster`,p.`teacher`,p.`student` FROM `mks_attainment_programme` AS p,`mks_attainment_direction` AS d,`mks_attainment_project` AS t WHERE t.`directionId`=p.`directionId` AND t.`isParent`=0 AND programmeId=".$programmeId." AND p.scId=".$scId." AND p.directionId=d.directionId";
            $f1=$dao1->query($sql);
            $arr5=array();
            $sql4="SELECT studentId AS userId,role,score FROM `mks_attainment_score` WHERE studentId=".$userId." and programmeId=".$programmeId." and scId=".$scId;
            $f4=$dao1->query($sql4);
            foreach($f4 as $k=>$v){
                $arr3=json_decode($v['score'],true);
                foreach($arr3 as $a=>$b){
                    $arr5[$v['role']][$a][]=$b;
                }
            }
            foreach($arr5 as $k=>$v){
                foreach($v as $a=>$b){
                    $arr6[$a][$k]=array_sum($b)/count($b);
                }
            }
            if($roId==$this::$studentRoleId){
                $appraiser[]='学生本人';
            }else{
                $sqla="SELECT headmaster FROM `mks_attainment_class` WHERE classId=".$classId." and userId=".$ratersId." AND scId=".$scId." AND programmeId=".$programmeId;
                $fjk=$dao->query($sqla);
                if($fjk['0']['headmaster']=='0'){
                    $appraiser[]='任课教师';
                    $role=2;
                }else{
                    $appraiser[]='班主任';
                    $role=1;
                }
            }
            foreach($f1 as $k=>$v){
                $arr['programmeName']=$v['programmeName'];
                $arr['directionName']=$v['directionName'];
                $arr['name']=$faz['0']['name'];
                $arr['scoreAll']=$v['scoreAll'];
                $arr['score']=$fa['0']['score'];
                /*if($v['headmaster']){
                    $appraiser[]='班主任';
                }
                if($v['teacher']){
                    $appraiser[]='任课教师';
                }
                if($v['student']){
                    $appraiser[]='学生本人';
                }*/
                $arr['appraiser']=implode('、',$appraiser);
            }
            $sql1="SELECT directionId FROM mks_attainment_programme WHERE scId=".$scId." AND programmeId=".$programmeId;
            $f1=$dao->query($sql1);
            if($roId==$studentRoleId){
                $sql="SELECT score,scoreId FROM `mks_attainment_score` WHERE role=3 AND ratersId=".$ratersId." AND studentId=".$userId." AND scId=".$scId." AND programmeId=".$programmeId;
                $f=$dao->query($sql);
            //}elseif($this->getRole($scId,$ratersId)){
            }else{
                /*$sql3="SELECT COUNT(classid) AS `count` FROM mks_class WHERE classid=".$classId." AND userid=".$ratersId." AND scId=".$scId;
                $f3=$dao->query($sql3);
                if($f3['0']['count']){
                    $role=1;
                }else{
                    $role=2;
                }*/
                $sql="SELECT score,scoreId FROM `mks_attainment_score` WHERE role=".$role." AND ratersId=".$ratersId." AND studentId=".$userId." AND scId=".$scId." AND programmeId=".$programmeId;
                $f=$dao->query($sql);
            }
            $scoreId=$f['0']['scoreId'];
            $f=json_decode($f['0']['score'],true);
            $where['directionId']=$f1['0']['directionId'];
            $where['scId']=$scId;
            $dao2=M('attainment_project');
            $f2=$dao2->field('projectId,projectNmae,pid,level,isParent,scoreAll')->where($where)->select();
            foreach($f2 as $k=>$v){
                if($v['isParent']==0){
                    $f2[$k]['score']=$f[$v['projectId']];
                }
            }
            $arr['scoreId']=$scoreId;
            foreach($f2 as $k=>$v){
                if($v['isParent']==0){
                    $f2[$k]['projectNmaeRules']=$v['projectNmae'];
                    $f2[$k]['projectNmae']=null;
                }else{
                    $f2[$k]['projectNmaeRules']=null;
                }
            }
            $arr['list']=$this->make_tree1($f2);
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='pingfen'){/**********************评分********************/
            $ratersId=$getSession['userId'];
            $roId=$getSession['roleId'];
            $studentRoleId=$this::$studentRoleId;
            $teacherRoleId=$this::$teacherRoleId;
            $programmeId=I('post.programmeId');
            $classId=I('post.classId');
            $userId=I("post.userId");
            $scoreId=I('post.scoreId');
            $sqla="select classId from mks_attainment_student where userId=".$userId." and programmeId=".$programmeId." and scId=".$scId;
            $dao=M();
            $fa=$dao->query($sqla);
            $classId=$fa['0']['classId'];
            $score=json_encode(I('post.score'));
            $dao=M();
            $dao1=M('attainment_score');
            if(!$scoreId){
                if($roId==$studentRoleId){
                    $role=3;
                }else{
                    $sql3="SELECT COUNT(classid) AS `count` FROM mks_class WHERE classid=".$classId." AND userid=".$ratersId." AND scId=".$scId;
                    $sql3="SELECT COUNT(classid) AS `count` FROM mks_attainment_class WHERE classId=".$classId." AND userId=".$ratersId." AND scId=".$scId." and headmaster=1";
                    $f3=$dao->query($sql3);
                    if($f3['0']['count']){
                        $role=1;
                    }else{
                        $role=2;
                    }
                }
                $data['role']=$role;
                $data['studentId']=$userId;
                $data['ratersId']=$ratersId;
                $data['score']=$score;
                $data['programmeId']=$programmeId;
                $data['scId']=$scId;
                $data['lastRecordTime']=time();
                $data['createTime']=time();
                $fn=$dao1->add($data);
            }else{
                $where['scId']=$scId;
                $where['scoreId']=$scoreId;
                $data['score']=$score;
                $data['lastRecordTime']=time();
                $fn=$dao1->where($where)->save($data);
            }
            if($fn===false){
                $arrn['return']=false;
            }else{
                $arrn['return']=true;
            }
            $this->ajaxReturn($arrn);
        }elseif(I('get.type')=='del'){/********************删除评分,功能取消*********************/
            /*$ratersId=$getSession['userId'];
            $roId=$getSession['roleId'];
            $studentRoleId=$this::$studentRoleId;
            $teacherRoleId=$this::$teacherRoleId;
            $programmeId=I('post.programmeId');
            M()->startTrans();
            $dao1=M('attainment_score');
            $where1['ratersId']=$ratersId;
            $where1['scId']=$scId;
            $where1['programmeId']=$programmeId;
            $f1=$dao1->where($where1)->delete();
            $dao2=M('attainment_student');
            $dao3=M('attainment_teacher');
            $dao4=M('attainment_class');
            if($roId==$studentRoleId){
                $where2['userId']=$ratersId;
                $where2['programmeId']=$programmeId;
                $where2['scId']=$scId;
                $data1['state']=0;
                $data1['score']=null;
                $f2=$dao2->where($where2)->data($data1)->save();
                if($f1===false || $f2===false){
                    M()->rollback();
                    $arr['return']=false;
                }else{
                    M()->commit();
                    $arr['return']=true;
                }
            }elseif($roId==$teacherRoleId){
                $where2['userId']=$ratersId;
                $where2['programmeId']=$programmeId;
                $where2['scId']=$scId;
                $data1['state']=0;
                $f2=$dao3->where($where2)->data($data1)->save();
                $sql3="SELECT class FROM `mks_attainment_teacher` WHERE userId=".$ratersId." AND programmeId=".$programmeId." AND scId=".$scId;
                $f3=$dao3->query($sql3);
                $where3['classId']=array('in',$f3['0']['class']);
                $where3['programmeId']=$programmeId;
                $where3['scId']=$scId;
                $f5=$dao4->where($where2)->data($data1)->save();
                $data1['score']=null;
                $f4=$dao2->where($where3)->data($data1)->save();
                if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false){
                    M()->rollback();
                    $arr['return']=false;
                }else{
                    M()->commit();
                    $arr['return']=true;
                }
            }
            $this->ajaxReturn($arr);*/
        }elseif(I('get.type')=='shangbao'){/***************上报******************/
            $ratersId=$getSession['userId'];
            $roId=$getSession['roleId'];
            $studentRoleId=$this::$studentRoleId;
            $teacherRoleId=$this::$teacherRoleId;
            $programmeId=I('post.programmeId');
            //$userId=I('post.userId');
            M()->startTrans();
            $dao1=M('attainment_teacher');
            $dao2=M('attainment_class');
            $dao3=M('attainment_student');
            $dao4=M('attainment_score');
            $dao5=M('attainment_programme');
            $where1['scId']=$scId;
            $where1['programmeId']=$programmeId;
            $where1['userId']=$ratersId;
            $where2['scId']=$scId;
            $where2['programmeId']=$programmeId;
            $where2['ratersId']=$ratersId;
            $where3['scId']=$scId;
            $where3['programmeId']=$programmeId;
            $data1['state']=1;
            if($roId==$studentRoleId){
                $f1=$dao4->where($where2)->data($data1)->save();
                $f2=$dao3->where($where1)->field('classId')->select();
                $sql3="SELECT SUM(IF(state=0,1,0)) AS `count` FROM `mks_attainment_class` WHERE classId=".$f2['0']['classId']." AND programmeId=".$programmeId." AND scId=".$scId;
                $f3=$dao2->query($sql3);
                if($f3['0']['count']==0){
                    $sql4="SELECT studentId AS userId,role,score FROM `mks_attainment_score` WHERE studentId=".$ratersId." and programmeId=".$programmeId." and scId=".$scId;
                    $f4=$dao4->query($sql4);
                    foreach($f4 as $k=>$v){
                        $arr5[$v['role']][]=array_sum(json_decode($v['score'],true));
                    }
                    $f7=$dao5->where()->where($where3)->field('headmaster,teacher,student')->select();
                    $where4['programmeId']=$programmeId;
                    $where4['userId']=$ratersId;
                    $where4['scId']=$scId;
                    $data2['state']=1;
                    $data2['score']=($arr5['1']['0']*$f7['0']['headmaster']/100)+((array_sum($arr5['2'])/count($arr5['2']))*$f7['0']['teacher']/100)+($arr5['3']['0']*$f7['0']['student']/100);
                    $f8=$dao3->where($where4)->data($data2)->save();
                }
            }else{
                $f1=$dao1->where($where1)->data($data1)->save();
                $f2=$dao2->where($where1)->data($data1)->save();
                $f3=$dao1->where($where1)->field('class')->select();
                $sql4="SELECT SUM(IF(state=0,1,0)) AS `count`,classId FROM `mks_attainment_class` WHERE programmeId=".$programmeId." and classId IN(".$f3['0']['class'].") AND scId=".$scId." GROUP BY classId";
                $f4=$dao2->query($sql4);
                foreach($f4 as $k=>$v){
                    if($v['count']==0){
                        $arr[]=$v['classId'];
                    }
                }
                $classlist=implode(',',$arr);
                if($classlist){
                    $sql5="SELECT c.studentId as userId,c.score FROM `mks_attainment_score` as c,mks_attainment_student as s WHERE s.userId=c.studentId and s.state=0 and s.classId in(".$classlist.") AND c.studentId=c.ratersId and c.state=1 and c.programmeId=".$programmeId." and c.scId=".$scId;
                    $f5=$dao4->query($sql5);
                    $sqlz="SELECT student FROM `mks_attainment_programme` WHERE programmeId=".$programmeId." AND scId=".$scId;
                    $fz=$dao4->query($sqlz);
                    if($fz['0']['student']){
                        $statu=true;
                    }else{
                        $statu=false;
                    }
                    if((!empty($f5)&&$fz['0']['student'])||!$fz['0']['student']){
                        if($statu){
                            foreach($f5 as $k=>$v){
                                $arr5[$v['userId']]['student']=array_sum(json_decode($v['score'],true));
                            }
                        }
                        $sql6="SELECT c.studentId AS userId,c.score,c.`ratersId`,c.`role` FROM `mks_attainment_score` AS c,mks_attainment_student AS s WHERE s.userId=c.studentId AND s.state=0 AND s.classId IN(".$classlist.") AND c.studentId<>c.ratersId AND c.programmeId=".$programmeId." AND c.scId=".$scId;
                        $f6=$dao4->query($sql6);
                        foreach($f6 as $k=>$v){
                            $arr6[$v['userId']][$v['role']][]=array_sum(json_decode($v['score'],true));
                        }
                        if($statu){
                            foreach($arr5 as $k=>$v){
                                $arr5[$k]['headmaster']=$arr6[$k]['1']['0'];
                                $arr5[$k]['teacher']=array_sum($arr6[$k]['2'])/count($arr6[$k]['2']);
                            }
                        }else{
                            foreach($arr6 as $k=>$v){
                                $arr5[$k]['headmaster']=$arr6[$k]['1']['0'];
                                $arr5[$k]['teacher']=array_sum($arr6[$k]['2'])/count($arr6[$k]['2']);
                            }
                        }
                        $f7=$dao5->where()->where($where3)->field('headmaster,teacher,student')->select();
                        $sqlt = "UPDATE mks_attainment_student SET score = CASE userId ";
                        foreach($arr5 as $k=>$v){
                            $where4['programmeId']=$programmeId;
                            $where4['userId']=$k;
                            $where4['scId']=$scId;
                            $data2['state']=1;
                            if($statu){
                                $data2['score']=($v['headmaster']*$f7['0']['headmaster']/100)+($v['teacher']*$f7['0']['teacher']/100)+($v['student']*$f7['0']['student']/100);
                            }else{
                                $data2['score']=($v['headmaster']*$f7['0']['headmaster']/100)+($v['teacher']*$f7['0']['teacher']/100);
                            }
                            $userId[]=$k;
                            $sqlt.="WHEN ".$k." THEN ".$data2['score']." ";
                            //$f8=$dao3->where($where4)->data($data2)->save();
                            //if($f8===false){
                              //  break;
                            //}
                        }
                        $ids=implode(',',$userId);
                        $sqlt.=' END,lastRecordTime ='.time().",state=1 WHERE userId IN (".$ids.") and programmeId=".$programmeId." and scId=".$scId;
                        $f8=$dao3->query($sqlt);
                    }
                }
            }
            if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false || $f6===false || $f7===false || $f8===false){
                M()->rollback();
                $arrn['return']=false;
            }else{
                M()->commit();
                $arrn['return']=true;
            }
            $this->ajaxReturn($arrn);
        }
    }
    public function chengji(){/*******综合素养成绩*********/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(I('get.type')=='findprogramme') {/***********方案列表***********/
            $userId =$getSession['userId'];
            $dao = M();
            $sql1 = "SELECT p.`programmeId`,p.`programmeName` FROM `mks_attainment_student` AS s,`mks_attainment_programme` AS p WHERE s.`programmeId`=p.`programmeId` AND s.`scId`=" . $scId . " AND s.`userId`=" . $userId;
            $f1 = $dao->query($sql1);
            $this->ajaxReturn($f1);
        }elseif(I('get.type')=='chengji'){/*****************成绩***************/
            $programmeId=I('post.programmeId');
            $userId=$getSession['userId'];
            $daoa=M('attainment_student');
            $wherea['userId']=$userId;
            $wherea['programmeId']=$programmeId;
            $wherea['scId']=$scId;
            $sqla="SELECT s.score,u.name FROM `mks_attainment_student` AS s,mks_user AS u WHERE s.userId=u.`id` AND s.programmeId=".$programmeId." AND s.scId=".$scId." AND s.userId=".$userId;
            $fa=$daoa->query($sqla);
            $dao1=M();
            $sql="SELECT p.`programmeName`,d.directionId,d.directionName,SUM(t.`scoreAll`) AS scoreAll,p.`headmaster`,p.`teacher`,p.`student` FROM `mks_attainment_programme` AS p,`mks_attainment_direction` AS d,`mks_attainment_project` AS t WHERE t.`directionId`=p.`directionId` AND t.`isParent`=0 AND programmeId=".$programmeId." AND p.scId=".$scId." AND p.directionId=d.directionId";
            $f1=$dao1->query($sql);
            $arr5=array();
            $sql4="SELECT studentId AS userId,role,score FROM `mks_attainment_score` WHERE studentId=".$userId." and programmeId=".$programmeId." and scId=".$scId;
            $f4=$dao1->query($sql4);
            foreach($f4 as $k=>$v){
                $arr3=json_decode($v['score'],true);
                foreach($arr3 as $a=>$b){
                    $arr5[$v['role']][$a][]=$b;
                }
            }
            foreach($arr5 as $k=>$v){
                foreach($v as $a=>$b){
                    $arr6[$a][$k]=array_sum($b)/count($b);
                }
            }
            foreach($f1 as $k=>$v){
                $arr['programmeName']=$v['programmeName'];
                $arr['directionName']=$v['directionName'];
                $arr['name']=$fa['0']['name'];
                $arr['scoreAll']=$v['scoreAll'];
                $arr['score']=$fa['0']['score'];
                if($v['headmaster']){
                    $appraiser[]='班主任';
                    $appraiser1['1']='headmaster';
                    $appraiser3['1']=$v['headmaster'];
                    $appraiser4['name']='（班主任评'.$v['headmaster'].'%)';
                    $appraiser4['props']='headmaster';
                    $arr1[]=$appraiser4;
                }
                if($v['teacher']){
                    $appraiser[]='任课教师';
                    $appraiser1['2']='teacher';
                    $appraiser3['2']=$v['teacher'];
                    $appraiser4['name']='（任课教师评'.$v['teacher'].'%)';
                    $appraiser4['props']='teacher';
                    $arr1[]=$appraiser4;
                }

                if($v['student']){
                    $appraiser[]='学生本人';
                    $appraiser1['3']='student';
                    $appraiser3['3']=$v['student'];
                    $appraiser4['name']='（学生本人评'.$v['student'].'%)';
                    $appraiser4['props']='student';
                    $arr1[]=$appraiser4;
                }
                $arr['appraiser']=implode('、',$appraiser);
                $arr['title']=$arr1;
            }
            foreach($arr6 as $k=>$v){
                foreach($appraiser3 as $a=>$b){
                    $arr6[$k]['all']+=$v[$a]*$b/100;
                }
            }
            /*foreach($arr5 as $k=>$v){

            }*/
            $where['directionId']=$f1['0']['directionId'];
            $where['scId']=$scId;
            $dao=M('attainment_project');
            $f=$dao->field('projectId,projectNmae,pid,level,isParent,scoreAll')->where($where)->select();
            foreach($f as $k=>$v){
                foreach($appraiser1 as $a=>$b){
                    $f[$k][$b]=$arr6[$v['projectId']][$a];
                }
                $f[$k]['all']=$arr6[$v['projectId']]['all'];
            }
            foreach($f as $k=>$v){
                if($v['isParent']==0){
                    $f[$k]['projectNmaeRules']=$v['projectNmae'];
                    $f[$k]['projectNmae']=null;
                }else{
                    $f[$k]['projectNmaeRules']=null;
                }
            }
            $arr['list']=$this->make_tree1($f);
            //$arr['list']=$this->getProject($f1['0']['directionId']);
            $this->ajaxReturn($arr);
        }
    }
}