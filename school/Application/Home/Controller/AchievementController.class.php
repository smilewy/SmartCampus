<?php
namespace Home\Controller;
use Think\Controller;
ob_end_clean();
class AchievementController extends Base {
    private function sortArrByOneField($array, $field, $desc = false,$find=false,$page,$limit){
        if($field){
            $fieldArr = array();
            foreach ($array as $k => $v) {
                $fieldArr[$k] = $v[$field];
            }
            $sort = $desc == false ? SORT_ASC : SORT_DESC;
            array_multisort($fieldArr, $sort, $array);
        }
        if(!$page){
            $page=1;
        }
        $start=($page-1)*$limit;
        if($find!==false){
            foreach($array as $k=>$v){
                foreach($v as $a=>$b){
                    if(!is_array($b)){
                        $aa=explode($find,$b);
                        if(count($aa)>1){
                            $arr[]=$v;
                            break;
                        }
                    }
                }
            }
        }else{
            $arr=$array;
        }
        $pageAll=ceil(count($arr)/$limit);
        $arra['data']=array_slice($arr,$start,$limit);
        $arra['pageclass']['page']=$page;
        if(!$pageAll){
            $pageAll=0;
        }
        $arra['pageclass']['pageAll']=$pageAll;
        $arra['pageclass']['count']=count($arr);
        return $arra;
    }
    private function comparison($userid,$branchid,$classid,$examinationid){//成绩对比
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql1="SELECT t.`subjectid`,t.`subjectname` FROM mks_examination_subject AS s,mks_subject AS t WHERE s.`subject`=t.`subjectid` AND s.`examinationid`=".$examinationid." and s.scId=".$scId." and s.branchid=".$branchid." order by t.subjectid desc";//考试科目
        $f1=$dao->query($sql1);
        $sql2="SELECT userid,subjectid,results FROM `mks_examination_results` WHERE examinationid=".$examinationid." AND branchid=".$branchid." ORDER BY subjectid desc,results*1 desc";//考试成绩用于排年级名次及成绩返回
        $f2=$dao->query($sql2);
        $sql4="SELECT userid,SUM(results) AS `sum` FROM `mks_examination_results` WHERE examinationid=".$examinationid." AND branchid=".$branchid." AND scId=".$scId." GROUP BY userid ORDER BY `sum` DESC";//用于排总成绩的年级名次及成绩
        $f4=$dao->query($sql4);
        $sql6="SELECT SUM(r.results) AS `sum`,r.`userid` FROM `mks_examination_results` AS r,mks_user AS u,mks_examination_student as mb WHERE r.`userid`=u.`id` and mb.userid=u.id AND r.scId=".$scId." AND r.`examinationid`=".$examinationid." and mb.examinationid=r.examinationid AND mb.`classId`=".$classid." GROUP BY r.`userid` ORDER BY `sum` DESC";//用于排总成绩的班级名次
        $f6=$dao->query($sql6);
        foreach($f6 as $k=>$v){
            $arr6[$v['userid']]=$k+1;
        }
        foreach($f2 as $k=>$v){
            unset($v['userid']);
            unset($v['subjectid']);
            $arr3[$f2[$k]['userid']][$f2[$k]['subjectid']]=$v['results'];
        }
        foreach($f4 as $k=>$v){
            $data4[$v['userid']]=$k+1;
        }
        $array['subjectlist']=$f1;
        $array['classscore']=$arr3[$userid];
        $array['gradeRanking']['total']=$data4[$userid];
        $array['classRanking']['total']=$arr6[$userid];
        return $array;
    }
    private function acfind($examinationid,$branchid,$classid,$subjectid,$afterNum,$beforNum,$isRemoveMarkZero,$isRemoveResultZero){//成绩查询
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql1="SELECT t.`subjectid`,t.`subjectname` FROM mks_examination_subject AS s,mks_subject AS t WHERE s.`subject`=t.`subjectid` AND s.`examinationid`=".$examinationid." and s.scId=".$scId." and s.branchid=".$branchid." and t.subjectid in(".$subjectid.") order by t.subjectid desc";//对应的科目信息
        $f1=$dao->query($sql1);
        //$sql2="SELECT s.userid,u.`name`,u.`serialNumber`,u.`className`,u.class as classid FROM `mks_examination_student` AS s,mks_user AS u,mks_class as c WHERE s.userId=c.classname and s.`userid`=u.`id` and s.scId=".$scId." AND s.`examinationid`=".$examinationid." AND s.`participate`='是' AND s.`classId` IN(".$classid.") order by u.`className`,u.`serialNumber`,u.id";//学生班级信息
        $sql2="SELECT s.userid,u.`name`,u.`serialNumber`,c.`classname` AS className,s.classId AS classid FROM `mks_examination_student` AS s,mks_user AS u,mks_class AS c WHERE s.classId=c.classid AND s.`userid`=u.`id` AND s.scId=".$scId." AND s.`examinationid`=".$examinationid." AND s.`participate`='是' AND s.`classId` IN(".$classid.") ORDER BY c.`classname`,u.`serialNumber`,u.id";
        $f2=$dao->query($sql2);
        //echo $sql2;
        //exit;
        $sql3="SELECT r.userid,r.subjectid,r.results FROM `mks_examination_results` as r,mks_user as u WHERE r.userid=u.id AND r.examinationid=".$examinationid." AND r.branchid=".$branchid." AND r.scId=".$scId." and r.subjectid in(".$subjectid.") ORDER BY r.subjectid desc,r.results*1 desc";//学生各科目成绩
        $f3=$dao->query($sql3);
        //$sql4="SELECT r.userid,SUM(r.results) AS `sum` FROM `mks_examination_results` as r,mks_user as u WHERE r.examinationid=".$examinationid." and u.id=r.userid AND r.branchid=".$branchid." AND r.scId=".$scId." GROUP BY r.userid ORDER BY `sum` DESC";//学生总分用于排年级名次
        //$f4=$dao->query($sql4);
        /********************修改，去掉了不必要的查询，修改优化了排名，排名可以并列********************/
        $sql5="SELECT SUM(r.results) AS `sum`,r.`userid`,s.`classId` as class FROM `mks_examination_results` AS r,`mks_examination_student` AS s,mks_user AS u WHERE r.`userid`=u.`id` AND r.scId=".$scId." AND r.`examinationid`=".$examinationid." and s.`userid`=u.`id` AND s.`classId` IN(".$classid.") AND r.`branchid`=".$branchid." and r.examinationid=s.examinationid and r.subjectid in(".$subjectid.") GROUP BY r.`userid` ORDER BY `sum` DESC";//学生总成绩用于排班级名次
        $f5=$dao->query($sql5);
        foreach($f5 as $k=>$v){
            $mmm['userid']=$v['userid'];
            $mmm['sum']=round($v['sum'],2);
            $datan[$v['class']][]=$mmm;
            $data5[$v['class']][]=$mmm;
        }
        $a1=null;
        $ak=0;
        foreach($data5 as $k=>$v){
            foreach($v as $a=>$b){
                $aa=$a+1;
                if($b['sum']!=$a1){
                    $ak=$aa;
                }
                $data6[$b['userid']]=$ak;
                $a1=$b['sum'];
            }
        }
        $a1=null;
        $ak=0;
        foreach($f5 as $k=>$v){
            if($v['sum']!=$a1){
                $ak=$k+1;
            }
            $data4[$v['userid']]=$ak;
            $a1=$v['sum'];
            $data3[$v['userid']]=$v['sum'];
        }
        foreach($f3 as $k=>$v){
            unset($v['userid']);
            unset($v['subjectid']);
            $arr3[$f3[$k]['userid']][$f3[$k]['subjectid']]=$v['results'];
        }
        foreach($f3 as $k=>$v){
            $arrc['userid']=$v['userid'];
            $arrc['results']=$v['results'];
            $data[$v['subjectid']][]=$arrc;
        }
        foreach($data as $k=>$v){
            $a1=null;
            $ak=0;
            foreach($v as $a=>$b){
                $aa=$a+1;
                if($a1!=$b['results']){
                    $ak=$aa;
                }
                $data1[$k][$b['userid']]=$ak;
                $a1=$b['results'];
            }
        }
        $sss=0;
        $aak=explode(',',$subjectid);

        foreach($aak as $k=>$v){
            foreach($arr3 as $a=>$b){
                if(empty($b[$v])){
                    $arr3[$a][$v]=null;
                }
            }
        }
        foreach($f2 as $k=>$v){
            $i=1;
            if(!$arr3[$v['userid']]){
                unset($f2[$k]);
                continue;
            }
            foreach($arr3[$v['userid']] as $a=>$b){
                $f2[$k][$a]['results']=$b;
                $f2[$k][$a]['ranking']=$data1[$a][$v['userid']];
                $i++;
            }
            $f2[$k]['totalScore']=$data3[$v['userid']];
            $f2[$k]['gradeRanking']=$data4[$v['userid']];
            $f2[$k]['classRanking']=$data6[$v['userid']];
            if($sss<$data4[$v['userid']]){
                $sss=$data4[$v['userid']];
            }
        }
        $arr['subject']=$f1;
        if($beforNum){
            $beforNum=$sss-$beforNum;
        }

        foreach($f2 as $k=>$v){
            if($afterNum){
                if($v['gradeRanking']>$afterNum){
                    unset($f2[$k]);
                }
            }
            if($beforNum){
                if($v['gradeRanking']<=$beforNum){
                    unset($f2[$k]);
                }
            }
            if($isRemoveMarkZero){
                if($v['totalScore']==0){
                    unset($f2[$k]);
                }
            }
            if($isRemoveResultZero){
                foreach($v as $a=>$b){
                    if(is_numeric($a)){
                        if(!$b['results']){
                            unset($f2[$k]);
                            break;
                        }
                    }
                }
            }
            unset($f2[$k]['classid']);
        }
        $arr['student']=$f2;
        return $arr;
    }
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
        foreach ($data as $k => $v) {
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
    private function findgrade(){/***********************年级列表****************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $where['scId']=$scId;
        $dao=M('grade');
        $roleId=$getSession['roleId'];
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $f=array();
        if($roleId==$this::$teacherRoleId){
            $userid=$getSession['userId'];
            $sql1="SELECT g.`gradeid`,g.`name` FROM `mks_jw_schedule` AS j,mks_grade AS g WHERE j.techerId=".$userid." AND j.`gradeId`=g.`gradeid` and j.scId=".$scId." order by g.name asc";
            $f1=$dao->query($sql1);
            $sql2="SELECT g.`gradeid`,g.`name` FROM `mks_class` AS c,mks_grade AS g WHERE c.`userid`=".$userid." and c.scId=".$scId." AND c.`grade`=g.`gradeid` order by g.name asc";
            $f2=$dao->query($sql2);
            $sql3="SELECT gradeid,`name` FROM mks_grade WHERE userId=".$userid." AND scId=".$scId." order by name asc";
            $f3=$dao->query($sql3);
            foreach($f1 as $k=>$v){
                $f[$v['gradeid']]=$v;
            }
            foreach($f2 as $k=>$v){
                $f[$v['gradeid']]=$v;
            }
            foreach($f3 as $k=>$v){
                $f[$v['gradeid']]=$v;
            }
            $f=array_values($f);
        }else{
            $f=$dao->where($where)->field('gradeid,name')->order('name asc')->select();
        }
        foreach($f as $k=>$v){
            $key=$v['name']-1;
            $f[$k]['name']=$arrgrade[$key];
        }
        $this->ajaxReturn($f);
    }

    private function findexam($gradeid){/*******************考试查询*****************///后面要加上只有发布了成绩的考试才查出来
        $getSession = $this->get_session('loginCheck',false);
        $where['gradeid'] = $gradeid;
        $where['scId'] =$getSession['scId'];
        $where['release'] =1;//修改，使用时去掉注释，只返回已发布成绩的考试
        $dao = M('examination');
        $f = $dao->where($where)->field('examinationid,examination,starttime,endtime,release')->select();
        $data=array();
        foreach ($f as $k => $v) {
            $data[$k]['examinationid'] = $v['examinationid'];
            $data[$k]['examination'] = $v['examination'];
        }
        $this->ajaxReturn($data);
    }
    private function findsubject($examinationid,$branchid){//查询考试及科类对应的考试科目
        $getSession =$this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql1="SELECT t.`subjectid`,t.`subjectname` FROM mks_examination_subject AS s,mks_subject AS t WHERE s.`subject`=t.`subjectid` AND s.`examinationid`=".$examinationid." and s.scId=".$scId." and s.branchid=".$branchid." order by t.subjectid";
        $f1=$dao->query($sql1);
        return $f1;
    }
    private function findclass($examinationid,$type=null){/*******************对应考试的科类班级查询*****************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $sql1="SELECT class FROM `mks_examination` WHERE examinationid=".$examinationid." and scId=".$scId;
        $dao=M();
        $f1=$dao->query($sql1);
        $sql2="SELECT b.`branch`,b.branchid,c.`classid`,c.`classname` FROM mks_class AS c,mks_class_branch AS b WHERE c.`branch`=b.`branchid` and c.scId=".$scId." AND c.`classid` IN(".$f1['0']['class'].") order by c.classname";
        $f2=$dao->query($sql2);
        foreach($f2 as $k=>$v){
            $arr1['clsssid']=$v['classid'];
            $arr1['clsssname']=$v['classname'];
            $arr2[$v['branch']]['branch']=$v['branch'];
            $arr2[$v['branch']]['branchid']=$v['branchid'];
            $arr2[$v['branch']]['classlist'][]=$arr1;
        }
        $arr2=array_values($arr2);
        if($type){
            return $arr2;
        }else{
            $this->ajaxReturn($arr2);
        }
    }
    private function findClassAll($gradeid){/*******************对应年级的科类班级查询*****************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql2="SELECT b.`branch`,b.branchid,c.`classid`,c.`classname` FROM mks_class AS c,mks_class_branch AS b WHERE c.scId=".$scId." and c.`branch`=b.`branchid` AND c.grade=".$gradeid." order by c.classname";
        $f2=$dao->query($sql2);
        foreach($f2 as $k=>$v){
            $arr1['clsssid']=$v['classid'];
            $arr1['clsssname']=$v['classname'];
            $arr2[$v['branch']]['branch']=$v['branch'];
            $arr2[$v['branch']]['branchid']=$v['branchid'];
            $arr2[$v['branch']]['classlist'][]=$arr1;
        }
        $arr2=array_values($arr2);
        $this->ajaxReturn($arr2);
    }
    private function findbranch($examinationid){/*******************科类查询*****************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $sql1="SELECT class FROM `mks_examination` WHERE examinationid=".$examinationid." and scId=".$scId;
        $dao=M();
        $f1=$dao->query($sql1);
        $sql2="SELECT b.`branch`,b.branchid FROM mks_class AS c,mks_class_branch AS b WHERE c.`branch`=b.`branchid` AND c.`classid` IN(".$f1['0']['class'].")";
        $f2=$dao->query($sql2);
        foreach($f2 as $k=>$v){
            $arr[$v['branchid']]['branch']=$v['branch'];
            $arr[$v['branchid']]['branchid']=$v['branchid'];
        }
        $arr=array_values($arr);
        $this->ajaxReturn($arr);
    }

    private function getSubjectStatistics($examinationid,$statistics){//学科统计
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql2="SELECT class FROM `mks_examination` WHERE examinationid=".$examinationid." and scId=".$scId;
        $f2=$dao->query($sql2);
        $class=$f2['0']['class'];
        if($statistics){
            $order='b.`branch`,s.`subjectname`,c.`classname`';
            //$sql1="SELECT b.`branch`,b.branchid,s.`subjectname`,u.`className`,u.`class` AS classid,r.`subjectid`,COUNT(r.userid) AS `join`,AVG(r.`results`) AS `avg`,MAX(r.`results`) AS `max`,MIN(r.`results`) AS `min`,SUM(CASE WHEN r.`results`>=e.`excellent` THEN 1 ELSE 0 END ) AS excellent,SUM(CASE WHEN (r.`results`<=e.`excellent` AND r.`results`>=e.`pass`) THEN 1 ELSE 0 END ) AS pass,SUM(CASE WHEN r.`results`<=e.`lowscore` THEN 1 ELSE 0 END ) AS lowscore FROM `mks_examination_results` AS r,mks_user AS u,mks_class_branch AS b,mks_subject AS s,`mks_examination_rate` AS e WHERE r.`subjectid`=s.`subjectid` AND r.`branchid`=b.`branchid` AND r.`examinationid`=".$examinationid." AND r.`examinationid`=e.`examinationid` and r.scId=".$scId." AND r.`subjectid`=e.`ensubjectid` AND r.`branchid`=e.`branchid` AND r.`userid`=u.`id` AND u.`class` IN(".$class.") GROUP BY b.`branchid`,r.`subjectid`";//年级统计
            //$sql1="SELECT b.`branch`,b.branchid,s.`subjectname`,c.`classname` AS className,mb.`classId` AS classid,r.`subjectid`,COUNT(r.userid) AS `join`,AVG(r.`results`) AS `avg`,MAX(r.`results`) AS `max`,MIN(r.`results`) AS `min`,SUM(CASE WHEN r.`results`>=e.`excellent` THEN 1 ELSE 0 END ) AS excellent,SUM(CASE WHEN (r.`results`<=e.`excellent` AND r.`results`>=e.`pass`) THEN 1 ELSE 0 END ) AS pass,SUM(CASE WHEN r.`results`<=e.`lowscore` THEN 1 ELSE 0 END ) AS lowscore FROM `mks_examination_results` AS r,mks_user AS u,mks_class_branch AS b,mks_subject AS s,`mks_examination_rate` AS e,`mks_examination_student` AS mb,mks_class AS c WHERE mb.userid=u.id AND mb.classId=c.classid AND r.`subjectid`=s.`subjectid` AND r.`branchid`=b.`branchid` AND r.`examinationid`=".$examinationid." and mb.examinationid=r.examinationid AND r.`examinationid`=e.`examinationid` AND r.scId=".$scId." AND r.`subjectid`=e.`ensubjectid` AND r.`branchid`=e.`branchid` AND r.`userid`=u.`id` AND mb.`classId` IN(".$class.") GROUP BY r.`branchid`,r.`subjectid`";//年级统计
            $sql1="SELECT b.`branch`,b.branchid,s.`subjectname`,c.`classname` AS className,mb.`classId` AS classid,r.`subjectid`,COUNT(r.userid) AS `join`,AVG(r.`results`) AS `avg`,MAX(r.`results`) AS `max`,MIN(r.`results`) AS `min` FROM `mks_examination_results` AS r,mks_user AS u,mks_class_branch AS b,mks_subject AS s,`mks_examination_student` AS mb,mks_class AS c WHERE mb.userid=u.id AND mb.classId=c.classid AND r.`subjectid`=s.`subjectid` AND r.`branchid`=b.`branchid` AND r.`examinationid`=".$examinationid." and mb.examinationid=r.examinationid AND r.scId=".$scId." AND r.`userid`=u.`id` AND mb.`classId` IN(".$class.") GROUP BY r.`branchid`,r.`subjectid`";//年级统计
            $f1=$dao->query($sql1);
            $slq2="SELECT r.`results`,r.`branchid`,r.`subjectid`,t.`classId` FROM `mks_examination_results` AS r,`mks_examination_student` AS t WHERE r.`userid`=t.`userid` AND r.`examinationid`=".$examinationid." AND t.examinationid=r.examinationid AND r.scId=".$scId." AND t.`classId` IN(".$class.")";
            $faf2=$dao->query($slq2);
            $slq3="SELECT r.`branchid`,r.`ensubjectid`,r.excellent,r.`pass`,r.`lowscore` FROM `mks_examination_rate` AS r WHERE r.`examinationid`=".$examinationid." AND r.`scId`=".$scId." AND r.`ensubjectid`<>'总分'";
            $faf3=$dao->query($slq3);
            foreach($faf3 as $k=>$v){
                $rra2[$v['branchid']][$v['ensubjectid']]=$v;
            }
            $rra3=array();
            foreach($faf2 as $k=>$v){
                if($v['results']>=$rra2[$v['branchid']][$v['subjectid']]['excellent']){
                    if(!$rra3[$v['branchid']][$v['subjectid']]['excellent']){
                        $rra3[$v['branchid']][$v['subjectid']]['excellent']=0;
                    }
                    $rra3[$v['branchid']][$v['subjectid']]['excellent']++;
                }elseif($v['results']<$rra2[$v['branchid']][$v['subjectid']]['excellent']&&$v['results']>=$rra2[$v['branchid']][$v['subjectid']]['pass']){
                    if(!$rra3[$v['branchid']][$v['subjectid']]['pass']){
                        $rra3[$v['branchid']][$v['subjectid']]['pass']=0;
                    }
                    $rra3[$v['branchid']][$v['subjectid']]['pass']++;
                }elseif($v['results']<$rra2[$v['branchid']][$v['subjectid']]['pass']&&$v['results']>=$rra2[$v['branchid']][$v['subjectid']]['lowscore']){
                    if(!$rra3[$v['branchid']][$v['subjectid']]['lowscore']){
                        $rra3[$v['branchid']][$v['subjectid']]['lowscore']=0;
                    }
                    $rra3[$v['branchid']][$v['subjectid']]['lowscore']++;
                }
            }
            foreach($f1 as $k=>$v){
                $f1[$k]['excellent']=$rra3[$v['branchid']][$v['subjectid']]['excellent'];
                $f1[$k]['pass']=$rra3[$v['branchid']][$v['subjectid']]['pass'];
                $f1[$k]['lowscore']=$rra3[$v['branchid']][$v['subjectid']]['lowscore'];
            }
        }else{
            $order='s.`subjectname`,b.`branch`,c.`classname`';
            //$sql1="SELECT s.`subjectname`,c.`classname` AS className,mb.`classId` AS classid,r.`subjectid`,COUNT(r.userid) AS `join`,AVG(r.`results`) AS `avg`,MAX(r.`results`) AS `max`,MIN(r.`results`) AS `min`,SUM(CASE WHEN r.`results`>=e.`excellent` THEN 1 ELSE 0 END ) AS excellent,SUM(CASE WHEN (r.`results`<=e.`excellent` AND r.`results`>=e.`pass`) THEN 1 ELSE 0 END ) AS pass,SUM(CASE WHEN r.`results`<=e.`lowscore` THEN 1 ELSE 0 END ) AS lowscore FROM `mks_examination_results` AS r,mks_user AS u,mks_class_branch AS b,mks_subject AS s,`mks_examination_rate` AS e,`mks_examination_student` AS mb,mks_class AS c WHERE mb.userid=u.id AND mb.classId=c.classid AND r.`subjectid`=s.`subjectid` AND r.`branchid`=b.`branchid` AND r.`examinationid`=".$examinationid." and mb.examinationid=r.examinationid AND r.`examinationid`=e.`examinationid` AND r.`subjectid`=e.`ensubjectid` and r.scId=".$scId." AND r.`branchid`=e.`branchid` AND r.`userid`=u.`id` AND mb.`classId` IN(".$class.") GROUP BY r.`subjectid`";//年级统计
            $sql1="SELECT s.`subjectname`,c.`classname` AS className,mb.`classId` AS classid,r.`subjectid`,COUNT(r.userid) AS `join`,AVG(r.`results`) AS `avg`,MAX(r.`results`) AS `max`,MIN(r.`results`) AS `min` FROM `mks_examination_results` AS r,mks_user AS u,mks_class_branch AS b,mks_subject AS s,`mks_examination_student` AS mb,mks_class AS c WHERE mb.userid=u.id AND mb.classId=c.classid AND r.`subjectid`=s.`subjectid` AND r.`branchid`=b.`branchid` AND r.`examinationid`=".$examinationid." and mb.examinationid=r.examinationid AND r.scId=".$scId." AND r.`userid`=u.`id` AND mb.`classId` IN(".$class.") GROUP BY r.`subjectid`";//年级统计
            $f1=$dao->query($sql1);
            $slq2="SELECT r.`results`,r.`branchid`,r.`subjectid`,t.`classId` FROM `mks_examination_results` AS r,`mks_examination_student` AS t WHERE r.`userid`=t.`userid` AND r.`examinationid`=".$examinationid." AND t.examinationid=r.examinationid AND r.scId=".$scId." AND t.`classId` IN(".$class.")";
            $faf2=$dao->query($slq2);
            $slq3="SELECT r.`branchid`,r.`ensubjectid`,r.excellent,r.`pass`,r.`lowscore` FROM `mks_examination_rate` AS r WHERE r.`examinationid`=".$examinationid." AND r.`scId`=".$scId." AND r.`ensubjectid`<>'总分'";
            $faf3=$dao->query($slq3);
            foreach($faf3 as $k=>$v){
                $rra2[$v['branchid']][$v['ensubjectid']]=$v;
            }
            $rra3=array();
            foreach($faf2 as $k=>$v){
                if($v['results']>=$rra2[$v['branchid']][$v['subjectid']]['excellent']){
                    if(!$rra3[$v['subjectid']]['excellent']){
                        $rra3[$v['subjectid']]['excellent']=0;
                    }
                    $rra3[$v['subjectid']]['excellent']++;
                }elseif($v['results']<$rra2[$v['branchid']][$v['subjectid']]['excellent']&&$v['results']>=$rra2[$v['branchid']][$v['subjectid']]['pass']){
                    if(!$rra3[$v['subjectid']]['pass']){
                        $rra3[$v['subjectid']]['pass']=0;
                    }
                    $rra3[$v['subjectid']]['pass']++;
                }elseif($v['results']<$rra2[$v['branchid']][$v['subjectid']]['pass']&&$v['results']>=$rra2[$v['branchid']][$v['subjectid']]['lowscore']){
                    if(!$rra3[$v['subjectid']]['lowscore']){
                        $rra3[$v['subjectid']]['lowscore']=0;
                    }
                    $rra3[$v['subjectid']]['lowscore']++;
                }
            }
            foreach($f1 as $k=>$v){
                $f1[$k]['excellent']=$rra3[$v['subjectid']]['excellent'];
                $f1[$k]['pass']=$rra3[$v['subjectid']]['pass'];
                $f1[$k]['lowscore']=$rra3[$v['subjectid']]['lowscore'];
            }
        }
        //$sql3="SELECT b.`branch`,b.branchid,s.`subjectname`,c.`classname` AS className,mb.`classId` AS classid,r.`subjectid`,COUNT(r.userid) AS `join`,AVG(r.`results`) AS `avg`,MAX(r.`results`) AS `max`,MIN(r.`results`) AS `min`,SUM(CASE WHEN r.`results`>=e.`excellent` THEN 1 ELSE 0 END ) AS excellent,SUM(CASE WHEN (r.`results`< e.`excellent` AND r.`results`>=e.`pass`) THEN 1 ELSE 0 END ) AS pass,SUM(CASE WHEN r.`results`<=e.`lowscore` THEN 1 ELSE 0 END ) AS lowscore FROM `mks_examination_results` AS r,mks_user AS u,mks_class_branch AS b,mks_subject AS s,`mks_examination_rate` AS e,`mks_examination_student` AS mb,mks_class AS c WHERE mb.userid=u.id AND mb.classId=c.classid AND r.`subjectid`=s.`subjectid` AND r.`branchid`=b.`branchid` AND r.`examinationid`=".$examinationid." and mb.examinationid=r.examinationid AND r.`examinationid`=e.`examinationid` and r.scId=".$scId." AND r.`subjectid`=e.`ensubjectid` AND r.`branchid`=e.`branchid` AND r.`userid`=u.`id` AND mb.`classId` IN(".$class.") GROUP BY mb.`classId`,r.`subjectid` ORDER BY ".$order;//班统计
        //$f3=$dao->query($sql3);
        $slq="SELECT b.`branch`,b.branchid,s.`subjectname`,c.`classname` AS className,mb.`classId` AS classid,
              r.`subjectid`,COUNT(r.userid) AS `join`,AVG(r.`results`) AS `avg`,MAX(r.`results`) AS `max`,MIN(r.`results`) AS `min`
              FROM `mks_examination_results` AS r,mks_user AS u,mks_class_branch AS b,mks_subject AS s,`mks_examination_student` AS mb,mks_class AS c
              WHERE mb.userid=u.id AND mb.classId=c.classid AND r.`subjectid`=s.`subjectid` AND r.`branchid`=b.`branchid` AND r.`examinationid`=".$examinationid." AND mb.examinationid=r.examinationid AND r.scId=".$scId." AND r.`userid`=u.`id` AND mb.`classId` IN(".$class.") GROUP BY mb.`classId`,r.`subjectid` order by ".$order;
        $f3=$dao->query($slq);
        $slq2="SELECT r.`results`,r.`branchid`,r.`subjectid`,t.`classId` FROM `mks_examination_results` AS r,`mks_examination_student` AS t WHERE r.`userid`=t.`userid` AND r.`examinationid`=".$examinationid." AND t.examinationid=r.examinationid AND r.scId=".$scId." AND t.`classId` IN(".$class.")";
        $faf2=$dao->query($slq2);
        $slq3="SELECT r.`branchid`,r.`ensubjectid`,r.excellent,r.`pass`,r.`lowscore` FROM `mks_examination_rate` AS r WHERE r.`examinationid`=".$examinationid." AND r.`scId`=".$scId." AND r.`ensubjectid`<>'总分'";
        $faf3=$dao->query($slq3);
        foreach($faf3 as $k=>$v){
            $rra2[$v['branchid']][$v['ensubjectid']]=$v;
        }
        $rra3=array();
        foreach($faf2 as $k=>$v){
                if($v['results']>=$rra2[$v['branchid']][$v['subjectid']]['excellent']){
                    if(!$rra3[$v['classId']][$v['branchid']][$v['subjectid']]['excellent']){
                        $rra3[$v['classId']][$v['branchid']][$v['subjectid']]['excellent']=0;
                    }
                    $rra3[$v['classId']][$v['branchid']][$v['subjectid']]['excellent']++;
                }elseif($v['results']<$rra2[$v['branchid']][$v['subjectid']]['excellent']&&$v['results']>=$rra2[$v['branchid']][$v['subjectid']]['pass']){
                    if(!$rra3[$v['classId']][$v['branchid']][$v['subjectid']]['pass']){
                        $rra3[$v['classId']][$v['branchid']][$v['subjectid']]['pass']=0;
                    }
                    $rra3[$v['classId']][$v['branchid']][$v['subjectid']]['pass']++;
                }elseif($v['results']<$rra2[$v['branchid']][$v['subjectid']]['pass']&&$v['results']>=$rra2[$v['branchid']][$v['subjectid']]['lowscore']){
                    if(!$rra3[$v['classId']][$v['branchid']][$v['subjectid']]['lowscore']){
                        $rra3[$v['classId']][$v['branchid']][$v['subjectid']]['lowscore']=0;
                    }
                    $rra3[$v['classId']][$v['branchid']][$v['subjectid']]['lowscore']++;
                }
        }
        foreach($f3 as $k=>$v){
            $f3[$k]['excellent']=$rra3[$v['classid']][$v['branchid']][$v['subjectid']]['excellent'];
            $f3[$k]['pass']=$rra3[$v['classid']][$v['branchid']][$v['subjectid']]['pass'];
            $f3[$k]['lowscore']=$rra3[$v['classid']][$v['branchid']][$v['subjectid']]['lowscore'];
        }
        $sql4="SELECT j.`techerName`,j.`classId`,j.`subjectId` FROM `mks_examination` AS e,`mks_jw_schedule` AS j WHERE e.`gradeid`=j.`gradeId` and e.scId=".$scId." AND e.`examinationid`=".$examinationid;
        $f4=$dao->query($sql4);
        foreach($f4 as $k=>$v){
            $arr4[$v['subjectId']][$v['classId']]=$v['techerName'];
        }
        if($statistics){
            foreach($f1 as $k=>$v){
                $excellentPercent=($v['excellent']/$v['join'])*100;
                $passPercent=($v['pass']/$v['join'])*100;
                $lowscorePercent=($v['lowscore']/$v['join'])*100;
                $data6[$v['branchid']][$v['subjectid']]['branch']=$v['branch'];
                $data6[$v['branchid']][$v['subjectid']]['subjectname']=$v['subjectname'];
                $data6[$v['branchid']][$v['subjectid']]['className']='全年级';
                $data6[$v['branchid']][$v['subjectid']]['join']=$v['join'];
                $data6[$v['branchid']][$v['subjectid']]['avg']=round($v['avg'],2);
                $data6[$v['branchid']][$v['subjectid']]['ranking']=null;
                $data6[$v['branchid']][$v['subjectid']]['max']=$v['max'];
                $data6[$v['branchid']][$v['subjectid']]['min']=$v['min'];
                $data6[$v['branchid']][$v['subjectid']]['excellent']=$v['excellent'];
                $data6[$v['branchid']][$v['subjectid']]['excellentPercent']=round($excellentPercent,2);
                $data6[$v['branchid']][$v['subjectid']]['pass']=$v['pass'];
                $data6[$v['branchid']][$v['subjectid']]['passPercent']=round($passPercent,2);
                $data6[$v['branchid']][$v['subjectid']]['lowscore']=$v['lowscore'];
                $data6[$v['branchid']][$v['subjectid']]['lowscorePercent']=round($lowscorePercent,2);
                $data6[$v['branchid']][$v['subjectid']]['teacher']=null;
            }
            foreach($f3 as $k=>$v){
                $arr1[$v['branchid']][$v['subjectid']][$v['classid']]=$v['avg'];
            }

            foreach($arr1 as $k=>$v){
                foreach($v as $c=>$d){
                    arsort($arr1[$k][$c]);
                    foreach($arr1[$k][$c] as $a=>$b){

                        $arrc['classid']=$a;
                        $arrc['avg']=$b;
                        $arr2[$c][$k][]=$arrc;
                    }
                }
            }
            foreach($arr2 as $k=>$v){//科目
                $i=0;
                $aab='';
                $aad='';
                foreach($v as $a=>$b){//科类
                    foreach($b as $c=>$d){
                        if($d['avg']!=$aab){
                            $aac=$c+1+$i;
                        }
                        $arr3[$k][$d['classid']]=$aac;
                        $aab=$d['avg'];
                    }
                    $i=$c+1;
                }
            }
            //print_r($arr3);
            //exit;
        }else{
            foreach($f1 as $k=>$v){
                $excellentPercent=($v['excellent']/$v['join'])*100;
                $passPercent=($v['pass']/$v['join'])*100;
                $lowscorePercent=($v['lowscore']/$v['join'])*100;
                $data6[$v['subjectid']]['branch']=null;
                $data6[$v['subjectid']]['subjectname']=$v['subjectname'];
                $data6[$v['subjectid']]['className']='全年级';
                $data6[$v['subjectid']]['join']=$v['join'];
                $data6[$v['subjectid']]['avg']=round($v['avg'],2);
                $data6[$v['subjectid']]['ranking']=null;
                $data6[$v['subjectid']]['max']=$v['max'];
                $data6[$v['subjectid']]['min']=$v['min'];
                $data6[$v['subjectid']]['excellent']=$v['excellent'];
                $data6[$v['subjectid']]['excellentPercent']=round($excellentPercent,2);
                $data6[$v['subjectid']]['pass']=$v['pass'];
                $data6[$v['subjectid']]['passPercent']=round($passPercent,2);
                $data6[$v['subjectid']]['lowscore']=$v['lowscore'];
                $data6[$v['subjectid']]['lowscorePercent']=round($lowscorePercent,2);
                $data6[$v['subjectid']]['teacher']=null;
            }
            foreach($f3 as $k=>$v){
                $arr1[$v['subjectid']][$v['classid']]=$v['avg'];
            }
            foreach($arr1 as $k=>$v){
                arsort($arr1[$k]);
                foreach($arr1[$k] as $a=>$b){
                    $arrc['classid']=$a;
                    $arrc['avg']=$b;
                    $arr2[$k][]=$arrc;
                }
            }
            foreach($arr2 as $k=>$v){
                $i=0;
                $aab='';
                foreach($v as $a=>$b){
                    if($b['avg']!=$aab){
                        $aac=$a+1;
                    }
                    $arr3[$k][$b['classid']]=$aac;
                    $aab=$b['avg'];
                }
            }
        }
        foreach($f3 as $k=>$v){
            $excellentPercent=($v['excellent']/$v['join'])*100;
            $passPercent=($v['pass']/$v['join'])*100;
            $lowscorePercent=($v['lowscore']/$v['join'])*100;
            $data1['branch']=$v['branch'];
            $data1['subjectname']=$v['subjectname'];
            $data1['className']=$v['className'];
            $data1['join']=$v['join'];
            $data1['avg']=round($v['avg'],2);
            $data1['ranking']=$arr3[$v['subjectid']][$v['classid']];
            $data1['max']=$v['max'];
            $data1['min']=$v['min'];
            $data1['excellent']=$v['excellent'];
            $data1['excellentPercent']=round($excellentPercent,2);
            $data1['pass']=$v['pass'];
            $data1['passPercent']=round($passPercent,2);
            $data1['lowscore']=$v['lowscore'];
            $data1['lowscorePercent']=round($lowscorePercent,2);
            $data1['teacher']=$arr4[$v['subjectid']][$v['classid']];
            $data2[]=$data1;
            $k2=$k+1;
            if($f3[$k]['subjectid']!=$f3[$k2]['subjectid']){
                if($statistics){
                    $data2[]=$data6[$v['branchid']][$v['subjectid']];
                }else{
                    $data2[]=$data6[$v['subjectid']];
                }
            }
        }
        return $data2;
    }
    private function getSubsection($examinationid,$section,$branchid){//分段统计
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $roleId=$this::$teacherRoleId;
        $dao=M();
        $sql1="SELECT j.classid,j.className,COUNT(j.userid) AS `join`,AVG(j.results) AS `avg`,j.classid as ranking,MAX(j.results) AS `max`,MIN(j.results) AS `min`";
        foreach($section as $k=>$v){
            if($section[$k]['from']==''){
                $section[$k]['from']=0;
            }
            if($section[$k]['to']==''){
                $section[$k]['to']=0;
            }
            $sql1.=",SUM(CASE WHEN (j.`results`>=".$section[$k]['from']." AND j.`results`<=".$section[$k]['to'].") THEN 1 ELSE 0 END) AS `".$section[$k]['from']."-".$section[$k]['to']."`";
        }
        $sql1.=",j.teacher FROM (SELECT r.`userid`,SUM(r.`results`) AS results,mb.`classId` AS classid,c.`classname` as className,c.`userid` AS teacher
        FROM `mks_examination_results` AS r,mks_user AS u,mks_class AS c,`mks_examination_student` AS mb
        WHERE mb.userid=u.id and mb.classId=c.classid and mb.examinationid=r.examinationid and r.examinationid=".$examinationid." and r.scId=".$scId." AND r.`userid`=u.`id` AND r.`branchid`=".$branchid." GROUP BY mb.`classId`,r.`userid`)
        AS j GROUP BY j.classid order by j.className";
        //$sql1="SELECT j.classid,j.className,COUNT(j.userid) AS `join`,AVG(j.results) AS `avg`,j.classid as ranking,MAX(j.results) AS `max`,MIN(j.results) AS `min`,SUM(CASE WHEN (j.`results`>=".$section['0']['from']." AND j.`results`<=".$section['0']['to'].") THEN 1 ELSE 0 END) AS section1,SUM(CASE WHEN (j.`results`>=".$section['1']['from']." AND j.`results`<=".$section['1']['to'].") THEN 1 ELSE 0 END) AS section2,SUM(CASE WHEN (j.`results`>=".$section['2']['from']." AND j.`results`<=".$section['2']['to'].") THEN 1 ELSE 0 END) AS section3,SUM(CASE WHEN (j.`results`>=".$section['3']['from']." AND j.`results`<=".$section['3']['to'].") THEN 1 ELSE 0 END) AS section4,SUM(CASE WHEN (j.`results`>=".$section['4']['from']." AND j.`results`<=".$section['4']['to'].") THEN 1 ELSE 0 END) AS section5,SUM(CASE WHEN (j.`results`>=".$section['5']['from']." AND j.`results`<=".$section['5']['to'].") THEN 1 ELSE 0 END) AS section6,SUM(CASE WHEN (j.`results`>=".$section['6']['from']." AND j.`results`<=".$section['6']['to'].") THEN 1 ELSE 0 END) AS section7,j.teacher FROM (SELECT r.`userid`,SUM(r.`results`) AS results,u.`class` AS classid,u.`className`,c.`userid` AS teacher FROM `mks_examination_results` AS r,mks_user AS u,mks_class AS c WHERE r.examinationid=".$examinationid." and r.scId=".$scId." and c.`classid`=u.`class` AND r.`userid`=u.`id` AND r.`branchid`=".$branchid." GROUP BY u.`class`,r.`userid`) AS j GROUP BY j.classid order by j.className";
        $f1=$dao->query($sql1);
        $sql2="SELECT id,name FROM mks_user WHERE roleId=".$roleId." AND scId=".$scId;
        $f2=$dao->query($sql2);
        foreach($f2 as $k=>$v){
            $teacher[$v['id']]=$v['name'];
        }
        foreach($f1 as $k=>$v){
            $f1[$k]['avg']=round($v['avg'],2);
            $data12[$v['classid']]=round($v['avg'],2);
        }
        $i=1;
        $ak=0;
        $ad=0;
        foreach($data12 as $k=>$v){
            if($ad!=$v){
                $ak=$i;
            }
            $data1[$k]=$ak;
            $ad=$v;
            $i++;
        }
        foreach($f1 as $k=>$v){
            unset($f1[$k]['classid']);
            $f1[$k]['ranking']=$data1[$v['classid']];
            $f1[$k]['teacher']=$teacher[$v['teacher']];
        }
        return $f1;
    }
    private function getAvg($examinationid,$branchid){//均分总表
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $roleId=$this::$teacherRoleId;
        $subject=$this->findsubject($examinationid,$branchid);
        $sql1="SELECT sum(r.results) as resultsall,count(r.userid) as stuall,c.`classname` as className,r.subjectid,mb.classId as class,AVG(r.`results`) AS `avg`,s.`subjectname`,c.`userid` AS teacher
              FROM `mks_examination_results` AS r,mks_user AS u,mks_class AS c,mks_subject AS s,`mks_examination_student` AS mb
              WHERE mb.userid=u.id and mb.classId=c.classid and r.`subjectid`=s.`subjectid` AND mb.classId=c.`classid` AND mb.examinationid=r.examinationid and r.`examinationid`=".$examinationid." AND r.`branchid`=".$branchid." AND r.`scId`=".$scId." AND r.`userid`=u.`id` GROUP BY r.`subjectid`,mb.`classId` ORDER BY r.`subjectid`,`avg` DESC";

        $f1=$dao->query($sql1);
        foreach($f1 as $k=>$v){
            $arrc['class']=$v['class'];
            $arrc['avg']=$v['avg'];
            $data1[$v['subjectid']][]=$arrc;
        }

        foreach($data1 as $k=>$v){
            $ak=0;
            $ad='';
            foreach($v as $a=>$b){
                if($ad!=$b['avg']){
                    $ak=$a+1;
                }
                $arr1[$b['class']][$k]=$ak;
                $ad=$b['avg'];
            }
        }
        $sql2="SELECT id,name FROM mks_user WHERE roleId=".$roleId." AND scId=".$scId;
        $f2=$dao->query($sql2);
        foreach($f2 as $k=>$v){
            $data2[$v['id']]=$v['name'];
        }
        $sql3="SELECT j.class,SUM(j.`avg`) AS `sum`,j.stuall,j.resultsall FROM(
            SELECT count(r.userid) as stuall,mb.classId as class,AVG(r.`results`) AS `avg`,sum(r.results) as resultsall
            FROM `mks_examination_results` AS r,mks_user AS u,mks_class AS c,`mks_examination_student` AS mb
            WHERE mb.userid=u.id and mb.classId=c.classid and mb.classId=c.`classid` AND mb.examinationid=r.examinationid and r.`examinationid`=".$examinationid." AND r.`branchid`=".$branchid." AND r.`scId`=".$scId." AND r.`userid`=u.`id` GROUP BY r.`subjectid`,mb.`classId` ORDER BY r.`subjectid`,`avg` DESC
            ) AS j GROUP BY j.class ORDER BY `sum` DESC";
        $f3=$dao->query($sql3);
        $resultsall=0;
        $stuall=0;
        foreach($f3 as $k=>$v){
            $data3[$v['class']]['totalRanking']=$k+1;
            $data3[$v['class']]['totalResults']=round($v['sum'],2);
            $resultsall+=$v['resultsall'];
            $stuall+=$v['stuall'];
        }
        $totalResults=round($resultsall/$stuall,2);
        $arrd=array();
        foreach($f1 as $k=>$v){
            $arra[$v['className']]['className']=$v['className'];
            $arra[$v['className']]['teacher']=$data2[$v['teacher']];
            $arra[$v['className']]['totalResults']=$data3[$v['class']]['totalResults'];
            $arra[$v['className']]['totalRanking']=$data3[$v['class']]['totalRanking'];
            $arra[$v['className']][$v['subjectid']]['resutls']=round($v['avg'],2);
            $arra[$v['className']][$v['subjectid']]['ranking']=$arr1[$v['class']][$v['subjectid']];
            if(!$arrd[$v['subjectid']]['resultsall']){
                $arrd[$v['subjectid']]['resultsall']=$v['resultsall'];
                $arrd[$v['subjectid']]['stuall']=$v['stuall'];
            }else{
                $arrd[$v['subjectid']]['resultsall']+=$v['resultsall'];
                $arrd[$v['subjectid']]['stuall']+=$v['stuall'];
            }
        }
        $sqlg="SELECT u.`name` FROM `mks_examination` AS e,mks_user AS u,mks_grade AS g WHERE e.`examinationid`=".$examinationid." AND e.`scId`=".$scId." AND e.`gradeid`=g.`gradeid` AND g.`userId`=u.`id`";
        $fg=$dao->query($sqlg);
        if(!empty($f1)){
            $arra['全年级']['className']='全年级';
            $arra['全年级']['teacher']=$fg['0']['name'];
            $arra['全年级']['totalResults']=0;
            $arra['全年级']['totalRanking']=null;
            foreach($arrd as $k=>$v){
                $arra['全年级'][$k]['resutls']=round($v['resultsall']/$v['stuall'],2);
                $arra['全年级'][$k]['totalRanking']=null;
                $arra['全年级']['totalResults']+=$arra['全年级'][$k]['resutls'];
            }
        }
        foreach($arra as $k=>$v){
            $arrb[$k]["className"]=$v['className'];
            $arrb[$k]["totalResults"]=$v['totalResults'];
            $arrb[$k]["totalRanking"]=$v['totalRanking'];
            foreach($subject as $a=>$b){
                if(empty($arra[$k][$b['subjectid']])){
                    $arrb[$k][$b['subjectid']]['resutls']=null;
                    $arrb[$k][$b['subjectid']]['ranking']=null;
                }else{
                    $arrb[$k][$b['subjectid']]['resutls']=$v[$b['subjectid']]['resutls'];
                    $arrb[$k][$b['subjectid']]['ranking']=$v[$b['subjectid']]['ranking'];
                }
            }
            $arrb[$k]["teacher"]=$v['teacher'];
        }

        ksort($arrb);
        $arrb=array_values($arrb);
        $data['subjectlist']=$subject;
        $data['data']=$arrb;
        return $data;
    }
    private function getTeacher($class,$subjectid){//获取对应班级对应科目的老师
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql="SELECT techerName FROM `mks_jw_schedule` WHERE subjectid=".$subjectid." AND classId=".$class." and scId=".$scId;
        $arr=$dao->query($sql);
        return $arr['0']['techerName'];
    }
    private function getHeadmaster($userid){//获取对应的老师是否为班主任
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql="SELECT name FROM `mks_user` WHERE id=".$userid." and scId=".$scId;
        $arr=$dao->query($sql);
        return $arr['0']['name'];
    }
    private function getRankingSubject($subjectid,$branchid,$examinationid,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6){//对应科目的排名统计
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql1="SELECT subjectname,class,className,sum(rank) as `sum`,count(userid) as `join`,avg(rank) as `avg`,
            SUM(CASE WHEN rank<=".$rank1." THEN 1 ELSE 0 END ) AS rank1,
            SUM(CASE WHEN rank<=".$rank2." THEN 1 ELSE 0 END ) AS rank2,
            SUM(CASE WHEN rank<=".$rank3." THEN 1 ELSE 0 END ) AS rank3,
            SUM(CASE WHEN rank<=".$rank4." THEN 1 ELSE 0 END ) AS rank4,
            SUM(CASE WHEN rank<=".$rank5." THEN 1 ELSE 0 END ) AS rank5,
            SUM(CASE WHEN rank<=".$rank6." THEN 1 ELSE 0 END ) AS rank6 FROM (
            SELECT userid,subjectname,results,class,className,
            @curRank := IF(@preRank = results, @curRank, @incRank) AS rank,
            @incRank := @incRank + 1,
            @preRank := results
            FROM (SELECT @curRank := 0, @preRank := NULL, @incRank := 1) t,(
            SELECT r.userid,su.subjectname,r.results,mb.classId as class,c.classname as className
            from `mks_examination_results` r,mks_subject as su,mks_user u,`mks_examination_student` AS mb,mks_class as c
            WHERE u.id=mb.userid and mb.classId=c.classid and mb.examinationid=r.examinationid and r.examinationid=".$examinationid." and r.scId=".$scId." AND r.subjectid=".$subjectid." and r.subjectid=su.subjectid AND r.branchid=".$branchid." AND r.userid=u.id
            ORDER BY results*1 DESC) h ) z GROUP BY class order by className";
        $f1=$dao->query($sql1);
        $rank1=0;
        $rank2=0;
        $rank3=0;
        $rank4=0;
        $rank5=0;
        $rank6=0;
        $sumall=0;
        $joinall=0;
        foreach($f1 as $k=>$v){
            $rank1+=$v['rank1'];
            $rank2+=$v['rank2'];
            $rank3+=$v['rank3'];
            $rank4+=$v['rank4'];
            $rank5+=$v['rank5'];
            $rank6+=$v['rank6'];
            $sumall+=$v['sum'];
            $joinall+=$v['join'];
            unset($f1[$k]['sum']);
            unset($f1[$k]['class']);
            $f1[$k]['avg']=round($v['avg'],2);
            $f1[$k]['teacherName']=$this->getTeacher($v['class'],$subjectid);
        }
        $arr['subjectname']=$f1['0']['subjectname'];
        $arr['className']='全年级';
        $arr['join']=$joinall;
        $arr['avg']=round($sumall/$joinall,2);
        $arr['rank1']=$rank1;
        $arr['rank2']=$rank2;
        $arr['rank3']=$rank3;
        $arr['rank4']=$rank4;
        $arr['rank5']=$rank5;
        $arr['rank6']=$rank6;
        $arr['teacherName']=null;
        $f1[]=$arr;
        return $f1;
    }
    private function getSum($branchid,$examinationid,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6){//总成绩的排名统计
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql1="SELECT subjectname,className,SUM(rank) AS `sum`,COUNT(userid) AS `join`,AVG(rank) AS `avg`,
            SUM(CASE WHEN rank<=".$rank1." THEN 1 ELSE 0 END ) AS rank1,
            SUM(CASE WHEN rank<=".$rank2." THEN 1 ELSE 0 END ) AS rank2,
            SUM(CASE WHEN rank<=".$rank3." THEN 1 ELSE 0 END ) AS rank3,
            SUM(CASE WHEN rank<=".$rank4." THEN 1 ELSE 0 END ) AS rank4,
            SUM(CASE WHEN rank<=".$rank5." THEN 1 ELSE 0 END ) AS rank5,
            SUM(CASE WHEN rank<=".$rank6." THEN 1 ELSE 0 END ) AS rank6,teacherName FROM (
               SELECT subjectname,userid,results,class,className,teacherName,
            @curRank := IF(@preRank = results, @curRank, @incRank) AS rank,
            @incRank := @incRank + 1,
            @preRank := results
            FROM (SELECT @curRank := 0, @preRank := NULL, @incRank := 1) t,(SELECT r.subjectid as subjectname,r.userid,SUM(r.results) AS results,mb.classId as class,a.classname as className,a.userid AS teacherName
            FROM `mks_examination_results` r,mks_class AS a,mks_user u,`mks_examination_student` AS mb
            WHERE u.id=mb.userid and mb.examinationid=r.examinationid and r.examinationid=".$examinationid." and r.scId=".$scId." AND r.branchid=".$branchid." AND r.userid=u.id AND a.classid=mb.classId GROUP BY r.userid
            ORDER BY results*1 DESC)z ORDER BY results*1 DESC) h GROUP BY class ORDER BY className";
        $f1=$dao->query($sql1);
        $rank1=0;
        $rank2=0;
        $rank3=0;
        $rank4=0;
        $rank5=0;
        $rank6=0;
        $sumall=0;
        $joinall=0;
        foreach($f1 as $k=>$v){
            $rank1+=$v['rank1'];
            $rank2+=$v['rank2'];
            $rank3+=$v['rank3'];
            $rank4+=$v['rank4'];
            $rank5+=$v['rank5'];
            $rank6+=$v['rank6'];
            $sumall+=$v['sum'];
            $joinall+=$v['join'];
            unset($f1[$k]['sum']);
            unset($f1[$k]['class']);
            $f1[$k]['subjectname']='总分';
            $f1[$k]['teacherName']=$this->getHeadmaster($v['teacherName']);
            $f1[$k]['avg']=round($v['avg'],2);
        }
        $arr['subjectname']=$f1['0']['subjectname'];
        $arr['className']='全年级';
        $arr['join']=$joinall;
        $arr['avg']=round($sumall/$joinall,2);
        $arr['rank1']=$rank1;
        $arr['rank2']=$rank2;
        $arr['rank3']=$rank3;
        $arr['rank4']=$rank4;
        $arr['rank5']=$rank5;
        $arr['rank6']=$rank6;
        $arr['teacherName']=null;
        $f1[]=$arr;
        return $f1;
    }
    private function getContrastResultAll($examinationid1,$examinationid2,$branchid,$class){//成绩对比
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao = M();
        $subject1 = $this->findsubject($examinationid1, $branchid);
        $subject2 = $this->findsubject($examinationid2, $branchid);
        foreach ($subject1 as $k => $v) {
            $subject[$v['subjectid']] = $v;
        }
        if(!$subject){
            $subject=array();
        }
        foreach ($subject2 as $k => $v) {
            $subjectb[$v['subjectid']] = $v;
        }
        $subjecto='';
        foreach($subject as $k=>$v){
            if(!$subjectb[$k]){
                unset($subject[$k]);
            }else{
                $subjecto=$subjecto.$k.',';
            }
        }
        $subjecto=rtrim($subjecto,',');
        $subject = array_values($subject);
        if(empty($subject)){
            $arr=false;
            return($arr);
            //$this->ajaxReturn($arr);
        }else{
            $arr1=$this->getContrastResult($examinationid1,$class,$subjecto);
            $arr2=$this->getContrastResult($examinationid2,$class,$subjecto);
        }
        $sql1="SELECT c.`classname` as className,mb.`classId` as class,u.`serialNumber`,u.`name`,r.userid FROM `mks_examination_results` AS r,mks_user AS u,`mks_examination_student` AS mb,mks_class as c WHERE mb.userid=u.id and mb.classId=c.classid and mb.examinationid=r.examinationid AND r.`examinationid`=".$examinationid1." and r.scId=".$scId." AND r.`userid`=u.`id` AND mb.`classId` IN(".$class.") GROUP BY r.`userid` ORDER BY c.classname,u.serialNumber";
        $f1=$dao->query($sql1);
        $aaa['ranking']=null;
        $aaa['results']=null;
        foreach($f1 as $k=>$v){
            $data1[$v['userid']]['className']=$v['className'];
            $data1[$v['userid']]['serialNumber']=$v['serialNumber'];
            $data1[$v['userid']]['name']=$v['name'];
            if($arr1['all'][$v['userid']]){
                $data1[$v['userid']]['exam'][$examinationid1]['总分']=$arr1['all'][$v['userid']];
            }else{
                $data1[$v['userid']]['exam'][$examinationid1]['总分']=$aaa;
            }
            if($arr2['all'][$v['userid']]){
                $data1[$v['userid']]['exam'][$examinationid2]['总分']=$arr2['all'][$v['userid']];
                if($arr1['all'][$v['userid']]){
                    $data1[$v['userid']]['exam'][$examinationid2]['总分']['progress']=$arr2['all'][$v['userid']]['ranking']-$arr1['all'][$v['userid']]['ranking'];
                }else{
                    $data1[$v['userid']]['exam'][$examinationid2]['总分']['progress']==null;
                }
            }else{
                $data1[$v['userid']]['exam'][$examinationid2]['总分']=$aaa;
                $data1[$v['userid']]['exam'][$examinationid2]['总分']['progress']=null;
            }
            foreach($subject as $a=>$b){
                if($arr1['sub'][$b['subjectid']][$v['userid']]){
                    $data1[$v['userid']]['exam'][$examinationid1][$b['subjectid']]=$arr1['sub'][$b['subjectid']][$v['userid']];
                }else{
                    $data1[$v['userid']]['exam'][$examinationid1][$b['subjectid']]=$aaa;
                }
                if($arr2['sub'][$b['subjectid']][$v['userid']]){
                    $data1[$v['userid']]['exam'][$examinationid2][$b['subjectid']]=$arr2['sub'][$b['subjectid']][$v['userid']];
                }else{
                    $data1[$v['userid']]['exam'][$examinationid2][$b['subjectid']]=$aaa;
                }
            }
        }
        foreach($data1 as $k=>$v){
            $data1[$k]['exam']=array_values($data1[$k]['exam']);
        }
        $data1=array_values($data1);
        $arrr['subjectlist']=$subject;
        $arrr['data']=$data1;
        return $arrr;
    }
    private function getContrastResult($examinationid1,$class,$subjecto){//对应考试的成绩统计
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql1 = "SELECT mb.`classId` as class,r.`results`,r.`subjectid`,r.userid
            FROM `mks_examination_results` AS r,mks_user AS u,`mks_examination_student` AS mb
            WHERE mb.userid=u.id and mb.examinationid=r.`examinationid` and r.`examinationid`=" . $examinationid1 . " and r.scId=".$scId." AND r.`userid`=u.`id` AND mb.`classId` IN(" . $class . ") and r.subjectid in(".$subjecto.") ORDER BY r.`results` DESC";
        $f1 = $dao->query($sql1);
        foreach ($f1 as $k => $v) {
            $arr['userid'] = $v['userid'];
            $arr['results'] = $v['results'];
            $arr1[$v['subjectid']][] = $arr;
        }
        $ru = null;
        $key = 1;
        foreach ($arr1 as $k => $v) {
            foreach ($v as $a => $b) {
                if ($ru == $b['results']) {
                    $key = $key;
                } else {
                    $key = $a + 1;
                }
                $arra[$k][$b['userid']]['ranking'] = $key;
                $arra[$k][$b['userid']]['results'] = $b['results'];
                $ru = $b['results'];
            }
        }
        $sql2 = "SELECT r.userid,mb.`classId` as class,SUM(r.`results`) AS `sum`
            FROM `mks_examination_results` AS r,mks_user AS u,`mks_examination_student` AS mb
            WHERE mb.userId=u.id and r.`examinationid`=" . $examinationid1 . " and mb.examinationid=r.`examinationid` and r.scId=".$scId." AND r.`userid`=u.`id` AND mb.`classId` IN(" . $class . ") GROUP BY r.`userid` ORDER BY `sum` DESC";
        $f2 = $dao->query($sql2);
        foreach ($f2 as $k => $v) {
            $arr['userid'] = $v['userid'];
            $arr['results'] = $v['sum'];
            $arr2[] = $arr;
            $arr3[$v['userid']]=$v;
        }
        $ru = null;
        $key = 1;
        foreach($arr2 as $k => $v){
            if($ru == $v['results']){
                $key = $key;
            }else{
                $key = $k+1;
            }
            $arrb[$v['userid']]['ranking']= $key;
            $arrb[$v['userid']]['results']= $v['results'];
            $ru = $v['results'];
        }
        $arrp['sub']=$arra;
        $arrp['all']=$arrb;
        return $arrp;
    }
    private function getContrastRank($branchid,$examinationid1,$examinationid2,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6){//名次对比
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $arr1=$this->getSum($branchid,$examinationid1,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6);
        $arr2=$this->getSum($branchid,$examinationid2,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6);
        foreach($arr1 as $k=>$v){
            unset($v['teacherName']);
            $arr[]=$v;
        }
        foreach($arr2 as $k=>$v){
            unset($v['teacherName']);
            $arra[$v['subjectname']][$v['className']]=$v;
        }
        $subject1 = $this->findsubject($examinationid1, $branchid);
        $subject2 = $this->findsubject($examinationid2, $branchid);
        foreach ($subject1 as $k => $v) {
            $subject[$v['subjectid']] = $v;
        }
        foreach ($subject2 as $k => $v) {
            $subjectb[$v['subjectid']] = $v;
        }
        foreach($subject as $k=>$v){
            if(!$subjectb[$k]){
                unset($subject[$k]);
            }
        }
        foreach($subject as $k=>$v){
            $arr1=$this->getRankingSubject($v['subjectid'],$branchid,$examinationid1,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6);
            foreach($arr1 as $k=>$v){
                unset($v['teacherName']);
                $arr[]=$v;
            }
        }
        foreach($subject as $k=>$v){
            $arr1=$this->getRankingSubject($v['subjectid'],$branchid,$examinationid2,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6);
            foreach($arr1 as $a=>$b){
                unset($b['teacherName']);
                $arra[$b['subjectname']][$b['className']]=$b;
            }
        }
        foreach($arr as $k=>$v){
            $arr[$k]['avga']=$arra[$v['subjectname']][$v['className']]['avg'];
            $arr[$k]['shortavg']=round($arra[$v['subjectname']][$v['className']]['avg']-$v['avg'],2);
            $arr[$k]['ranka']=$arra[$v['subjectname']][$v['className']]['rank1'];
            $arr[$k]['shortrank1']=$arra[$v['subjectname']][$v['className']]['rank1']-$v['rank1'];
            $arr[$k]['rankb']=$arra[$v['subjectname']][$v['className']]['rank2'];
            $arr[$k]['shortrank2']=$arra[$v['subjectname']][$v['className']]['rank2']-$v['rank2'];
            $arr[$k]['rankc']=$arra[$v['subjectname']][$v['className']]['rank3'];
            $arr[$k]['shortrank3']=$arra[$v['subjectname']][$v['className']]['rank3']-$v['rank3'];
            $arr[$k]['rankd']=$arra[$v['subjectname']][$v['className']]['rank4'];
            $arr[$k]['shortrank4']=$arra[$v['subjectname']][$v['className']]['rank4']-$v['rank4'];
            $arr[$k]['ranke']=$arra[$v['subjectname']][$v['className']]['rank5'];
            $arr[$k]['shortrank5']=$arra[$v['subjectname']][$v['className']]['rank5']-$v['rank5'];
            $arr[$k]['rankf']=$arra[$v['subjectname']][$v['className']]['rank6'];
            $arr[$k]['shortrank6']=$arra[$v['subjectname']][$v['className']]['rank6']-$v['rank6'];
        }
        return $arr;
    }
    private function getContrastSubjectAvg($examinationid1,$branchid,$subjectid){/***************对应考试及科类的单科均分信息*********************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sq11="SELECT subjectname,className,result,`join`,`sum`,
            @curRank := IF(@preRank = result, @curRank, @incRank) AS rank,
            @incRank := @incRank + 1,
            @preRank := result
            FROM (SELECT @curRank := 0, @preRank := NULL, @incRank := 1) t,
            (SELECT su.subjectname,AVG(r.results) AS result,mb.classId as class,c.classname as className,COUNT(r.userid) AS `join`,SUM(r.`results`) AS `sum`
            FROM `mks_examination_results` r,mks_subject AS su,mks_user u,`mks_examination_student` AS mb,mks_class AS c
            WHERE mb.userid=u.id and mb.classId=c.classid and mb.examinationid=r.examinationid and r.examinationid=".$examinationid1." and r.scId=".$scId." AND r.subjectid=".$subjectid." AND r.subjectid=su.subjectid AND r.branchid=".$branchid." AND r.userid=u.id GROUP BY mb.classId
            ORDER BY result DESC) AS z ORDER BY result DESC";
        $f1=$dao->query($sq11);
        $sum=0;
        $join=0;
        foreach($f1 as $k=>$v){
            unset($f1[$k]['@incRank := @incRank + 1']);
            unset($f1[$k]['@preRank := result']);
            unset($f1[$k]['sum']);
            $sum+=$v['sum'];
            $join+=$v['join'];
        }
        $arr['subjectname']=$f1['0']['subjectname'];
        $arr['className']='全年级';
        $arr['result']=round($sum/$join,2);
        $arr['join']=$join;
        $arr['rank']=null;
        $f1[]=$arr;
        foreach($f1 as $k=>$v){
            $arr1[$v['className']]=$v;
        }
        asort($arr1);
        return $arr1;
    }
    private function getContrastsum($examinationid1,$branchid){/***************对应考试的班级总分均分信息*********************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sq11="SELECT class as subjectname,className,result,`join`,`sum`,
            @curRank := IF(@preRank = result, @curRank, @incRank) AS rank,
            @incRank := @incRank + 1,
            @preRank := result
            FROM (SELECT @curRank := 0, @preRank := NULL, @incRank := 1) t,
            (SELECT class,className,COUNT(userid) AS `join`,AVG(`sum`) AS result,SUM(`sum`) AS `sum` FROM(
            SELECT mb.classId as class,c.classname as className,r.userid,SUM(r.`results`) AS `sum`
            FROM `mks_examination_results` r,mks_subject AS su,mks_user u,`mks_examination_student` AS mb,mks_class AS c
            WHERE mb.userid=u.id and mb.classId=c.classid and mb.examinationid=r.examinationid and r.examinationid=".$examinationid1." and r.scId=".$scId." AND r.subjectid=su.subjectid AND r.branchid=".$branchid." AND r.userid=u.id GROUP BY mb.classId,r.userid
            ORDER BY `sum` DESC) j GROUP BY class ORDER BY result DESC) AS z ORDER BY result DESC";
        $f1=$dao->query($sq11);
        $sum=0;
        $join=0;
        foreach($f1 as $k=>$v){
            unset($f1[$k]['@incRank := @incRank + 1']);
            unset($f1[$k]['@preRank := result']);
            unset($f1[$k]['sum']);
            $f1[$k]['subjectname']='总分';
            $sum+=$v['sum'];
            $join+=$v['join'];
        }
        $arr['subjectname']=$f1['0']['subjectname'];
        $arr['className']='全年级';
        $arr['result']=round($sum/$join,2);
        $arr['join']=$join;
        $arr['rank']=null;
        $f1[]=$arr;
        foreach($f1 as $k=>$v){
            $arr1[$v['className']]=$v;
        }
        asort($arr1);
        return $arr1;
    }
    private function getContrastAvg($examinationid1,$examinationid2,$branchid){/**********************均分对比***********************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $subject1 = $this->findsubject($examinationid1, $branchid);
        $subject2 = $this->findsubject($examinationid2, $branchid);

        foreach ($subject1 as $k => $v) {
            $subject[$v['subjectid']] = $v;
        }
        foreach ($subject2 as $k => $v) {
            $subjectb[$v['subjectid']] = $v;
        }
        foreach($subject as $k=>$v){
            if(!$subjectb[$k]){
                unset($subject[$k]);
            }
        }
        $arr1=$this->getContrastsum($examinationid1,$branchid);
        foreach($arr1 as $k=>$v){
            $arr[]=$v;
        }
        foreach($subject as $k=>$v){
            $arr1=$this->getContrastSubjectAvg($examinationid1,$branchid,$v['subjectid']);
            foreach($arr1 as $a=>$b){
                $arr[]=$b;
            }
        }
        $arr2=$this->getContrastsum($examinationid2,$branchid);
        foreach($arr2 as $k=>$v){
            $arra[$v['subjectname']][$v['className']]=$v;
        }
        foreach($subject as $k=>$v){
            $arr2=$this->getContrastSubjectAvg($examinationid2,$branchid,$v['subjectid']);
            foreach($arr2 as $a=>$b){
                $arra[$b['subjectname']][$b['className']]=$b;
            }
        }
        foreach($arr as $k=>$v){
            $arrc[$v['subjectname']][$v['className']]['subjectname']=$v['subjectname'];
            $arrc[$v['subjectname']][$v['className']]['className']=$v['className'];
            $arrc[$v['subjectname']][$v['className']]['join']=$v['join'];
            $arrc[$v['subjectname']][$v['className']]['result']=round($v['result'],2);
            $arrc[$v['subjectname']][$v['className']]['rank']=$v['rank'];
            $arrc[$v['subjectname']][$v['className']]['result1']=round($arra[$v['subjectname']][$v['className']]['result'],2);
            $arrc[$v['subjectname']][$v['className']]['coefficient']='';
            $arrc[$v['subjectname']][$v['className']]['targetresult']='';
            $arrc[$v['subjectname']][$v['className']]['retreat']='';
            $arrc[$v['subjectname']][$v['className']]['targetrank1']=$arra[$v['subjectname']][$v['className']]['rank'];
            $arrc[$v['subjectname']][$v['className']]['retreatrank']=$arra[$v['subjectname']][$v['className']]['rank']-$v['rank'];
        }
        foreach($arrc as $k=>$v){
            foreach($v as $a=>$b){
                $arrc[$k][$a]['coefficient']=round($b['result1']/$arrc[$k]['全年级']['result1'],2);
                $arrc[$k][$a]['targetresult']=round($arrc[$k]['全年级']['result']*$arrc[$k][$a]['coefficient'],2);
                $arrc[$k][$a]['retreat']=round($arrc[$k][$a]['result']-$arrc[$k][$a]['targetresult'],2);
                $arrn[]=$arrc[$k][$a];
            }
        }
        return $arrn;
    }
    private function getTotalOnline($examinationid){/********************总分上线**************************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql1="SELECT COUNT(id) AS `count` FROM `mks_examination_score` WHERE examinationid=".$examinationid." AND (score1!='' OR score2!='' OR score3!='' OR score4!='' OR score5!='' OR score6!='')";//查询是否划线
        $f1=$dao->query($sql1);
        if($f1['0']){
            $sql1="SELECT r.branchid,b.branch FROM `mks_examination_results` AS r,mks_class_branch as b WHERE r.examinationid=".$examinationid." and r.scId=".$scId." and r.branchid=b.branchid GROUP BY r.branchid";//查询考试科类
            $f1=$dao->query($sql1);
            $sql="SELECT id,`name` FROM `mks_user` WHERE roleId=".$this::$teacherRoleId." AND scId=".$scId;//查询班主任
            $f=$dao->query($sql);
            foreach($f as $k=>$v){
                $da[$v['id']]=$v['name'];
            }
            $sql5="SELECT u.`name` FROM mks_examination AS e,mks_grade AS g,mks_user AS u WHERE e.`examinationid`=".$examinationid." AND e.`gradeid`=g.`gradeid` AND g.`userId`=u.`id` and e.scId=".$scId;//年级主任
            $f5=$dao->query($sql5);
            foreach($f1 as $k=>$v){
                $sql2="SELECT score1,score2,score3,score4,score5,score6,name1,name2,name3,name4,name5,name6 FROM `mks_examination_score` AS s WHERE s.branchid=".$v['branchid']." and s.scId=".$scId." AND s.examinationid=".$examinationid." AND s.ensubjectid='总分'";//查询分数线
                $f2=$dao->query($sql2)['0'];
                for($i=1;$i<=6;$i++){
                    $name='name'.$i;
                    $score='score'.$i;
                    if($f2[$score]&&$f2[$name]){
                        $arr['lineName']=$f2[$name];
                        $arr['lineIndex']=$score;
                        $arr3['lineIndex']=$score;
                        $arr3['score']=$f2[$score];
                        $arr1[]=$arr;
                        $arr2[]=$arr3;
                    }
                }
                $data['lineList']=$arr1;
                $arr1=array();
                $str='';
                foreach($arr2 as $a=>$b){
                    if($arr2[$a-1]['score']){
                        $str.="SUM(CASE WHEN (`results`< ".$arr2[$a-1]['score']." AND `results`>=".$b['score'].") THEN 1 ELSE 0 END ) as ".$b['lineIndex'].",";
                    }else{
                        $str.="SUM(CASE WHEN (`results`>=".$b['score'].") THEN 1 ELSE 0 END ) as ".$b['lineIndex'].",";
                    }
                }
                $arr2=array();
                //$str=rtrim($str,',');
                $sql4="SELECT `branchid` as branch,`className`,COUNT(`userid`) AS `join`,".$str."teacher FROM (SELECT SUM(r.results) AS `results`,r.userid,r.`branchid`,c.`classname` as className,mb.classId as class,c.userid AS teacher FROM `mks_examination_results` AS r,mks_user AS u,mks_class as c,`mks_examination_student` AS mb WHERE mb.userid=u.id and mb.`classId`=c.`classid` and mb.`examinationid`=r.`examinationid` and r.`examinationid`=".$examinationid." and r.scId=".$scId." AND r.`branchid`=".$v['branchid']." AND r.`userid`=u.`id` GROUP BY r.userid) AS h GROUP BY class";
                $f4=$dao->query($sql4);
                $join=0;
                $aa=array();
                foreach($f4 as $a=>$b) {
                    $data1[$a]['branch']=$v['branch'];
                    $data1[$a]['className']=$b['className'];
                    $data1[$a]['join']=$b['join'];
                    $join+=$b['join'];
                    foreach($b as $c=>$d){
                        if(substr($c,0,5)=='score'){
                            if($aa[$c]){
                                $aa[$c]+=$b[$c];
                            }else{
                                $aa[$c]=$b[$c];
                            }
                            $data1[$a][$c]=$b[$c];
                            $data1[$a][$c."proportion"]=round(($f4[$a][$c]/$f4[$a]['join'])*100,2);
                        }
                    }
                    $data1[$a]['teacher']=$da[$b['teacher']];
                    $data['data'][]=$data1[$a];
                }
                $data2['branch']=$v['branch'];
                $data2['className']='全年级';
                $data2['join']=$join;
                foreach($aa as $a=>$b){
                    $data2[$a]=$b;
                    $data2[$a."proportion"]=round($b/$join*100,2);
                }
                $data2['teacher']=$f5['0']['name'];
                $data['data'][]=$data2;
            }
            return $data;
        }else{
            $arrrr=false;
            return $arrrr;
        }
    }
    private function getSingleOnline(){

    }
    private function getPersonal($examinationid,$userid,$branchid,$classid){/************************学生个人成绩*****************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql1="SELECT t.`subjectid`,t.`subjectname` FROM mks_examination_subject AS s,mks_subject AS t WHERE s.`subject`=t.`subjectid` AND s.`examinationid`=".$examinationid." and s.scId=".$scId." and s.branchid=".$branchid." order by t.subjectid desc";//考试科目
        $f1=$dao->query($sql1);
        $sql2="SELECT userid,subjectid,results FROM `mks_examination_results` WHERE examinationid=".$examinationid." AND branchid=".$branchid." ORDER BY subjectid desc,results*1 desc";//考试成绩用于排年级名次及成绩返回
        $f2=$dao->query($sql2);
        $sql4="SELECT userid,SUM(results) AS `sum` FROM `mks_examination_results` WHERE examinationid=".$examinationid." AND branchid=".$branchid." AND scId=".$scId." GROUP BY userid ORDER BY `sum` DESC";//用于排总成绩的年级名次及成绩
        $f4=$dao->query($sql4);
        $sql5="SELECT AVG(r.`results`) AS `avg`,r.`subjectid` FROM `mks_examination_results` AS r,mks_user AS u,mks_examination_student as mb WHERE mb.userid=u.id and mb.`classId`=".$classid." AND u.id=r.`userid` AND mb.examinationid=r.examinationid and r.`examinationid`=".$examinationid." AND r.`scId`=".$scId." GROUP BY r.`subjectid`";//班级平均成绩
        $f5=$dao->query($sql5);
        $sql6="SELECT SUM(r.results) AS `sum`,r.`userid` FROM `mks_examination_results` AS r,mks_user AS u,mks_examination_student as mb WHERE mb.userid=u.id AND r.`userid`=u.`id` AND r.scId=".$scId." AND r.`examinationid`=".$examinationid." AND mb.examinationid=r.examinationid AND mb.`classId`=".$classid." GROUP BY r.`userid` ORDER BY `sum` DESC";//用于排总成绩的班级名次
        $f6=$dao->query($sql6);
        $sql7="SELECT r.`userid`,r.`subjectid` FROM `mks_examination_results` AS r,mks_user AS u,mks_examination_student as mb WHERE mb.userid=u.id AND r.`userid`=u.`id` AND r.scId=".$scId." AND r.`examinationid`=".$examinationid." AND mb.examinationid=r.examinationid AND mb.`classId`=".$classid." ORDER BY r.`subjectid` DESC,r.`results` DESC";//用于排总成绩的班级名次
        $f7=$dao->query($sql7);
        foreach($f6 as $k=>$v){
            $arr6[$v['userid']]=$k+1;
        }
        foreach($f7 as $k=>$v){
            $arr7[$v['subjectid']][]=$v['userid'];
        }
        foreach($arr7 as $k=>$v){
            foreach($v as $a=>$b){
                $aa=$a+1;
                if($b==$userid){
                    $data7[$k]=$aa;
                }else{
                    continue;
                }
            }
        }
        foreach($f2 as $k=>$v){
            unset($v['userid']);
            unset($v['subjectid']);
            $arr3[$f2[$k]['userid']][$f2[$k]['subjectid']]=$v['results'];
        }
        foreach($f2 as $k=>$v){
            $data[$v['subjectid']][]=$v['userid'];
        }
        foreach($data as $k=>$v){
            foreach($v as $a=>$b){
                $aa=$a+1;
                if($b==$userid){
                    $data1[$k]=$aa;
                }else{
                    continue;
                }
            }
        }
        foreach($f4 as $k=>$v){
            $data4[$v['userid']]=$k+1;
            $data3[$v['userid']]=$v['sum'];
        }
        $total=0;
        foreach($f5 as $k=>$v){
            $arr5[$v['subjectid']]=round($v['avg'],2);
            $total+=round($v['avg'],2);
        }
        $array['subjectlist']=$f1;
        $array['classscore']=$arr3[$userid];
        $array['classscore']['total']=$data3[$userid];
        $array['gradeRanking']=$data1;
        $array['gradeRanking']['total']=$data4[$userid];
        $array['classRanking']=$data7;
        $array['classRanking']['total']=$arr6[$userid];
        $array['classavg']=$arr5;
        $array['classavg']['total']=$total;
        return $array;
    }
    private function getUserid(){
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M('user');
        $where['scId']=$scId;
        $where['id']=$getSession['userId'];
        $roleId=$getSession['roleId'];
        if($roleId==$this::$jZroleId){
            $f=$dao->where($where)->field('childId')->select();
            $userid=$f['0']['childId'];
        }else{
            $userid=$where['id'];
        }
        return $userid;
    }
    private function getSingleLine($examinationid){
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql1="SELECT COUNT(id) AS `count` FROM `mks_examination_score` WHERE examinationid=".$examinationid." AND (score1!='' OR score2!='' OR score3!='' OR score4!='' OR score5!='' OR score6!='')";//查询是否划线
        $f1=$dao->query($sql1);
        if($f1['0']['count']){
            $sql1="SELECT r.branchid,b.branch FROM `mks_examination_results` AS r,mks_class_branch as b WHERE r.examinationid=".$examinationid." and r.scId=".$scId." and r.branchid=b.branchid GROUP BY r.branchid";//查询考试科类
            //$f1=$dao->query($sql1);
            $sql="SELECT id,`name` FROM `mks_user` WHERE roleId=".$this::$teacherRoleId." AND scId=".$scId;//查询班主任
            $f=$dao->query($sql);
            foreach($f as $k=>$v){
                $da[$v['id']]=$v['name'];
            }
            $arrn=$this->findclass($examinationid,true);
            foreach($arrn as $k=>$v){
                foreach($v['classlist'] as $a=>$b){
                    $a1[$v['branch']][$b['clsssname']]=array();
                    $a1[$v['branch']][$b['clsssname']]['总分']=array();
                }
            }
            foreach($arrn as $k=>$v){//循环科类
                $sql2="SELECT score1,score2,score3,score4,score5,score6,name1,name2,name3,name4,name5,name6 FROM `mks_examination_score` AS s WHERE s.branchid=".$v['branchid']." and s.scId=".$scId." AND s.examinationid=".$examinationid." AND s.ensubjectid='总分'";//查询分数线
                $f2=$dao->query($sql2)['0'];
                for($i=1;$i<=6;$i++){
                    $name='name'.$i;
                    $score='score'.$i;
                    if($f2[$score]&&$f2[$name]){
                        $arr['lineName']=$f2[$name];
                        $arr['lineIndex']=$score;
                        $arr3['lineIndex']=$score;
                        $arr3['score']=$f2[$score];
                        $arr1[]=$arr;
                        $arr2[]=$arr3;
                        $aza[]=$score;
                    }
                }
                $azb=implode(',',$aza);
                $subject=$this->findsubject($examinationid,$v['branchid']);
                foreach($subject as $a=>$b){
                    $subjectarra[]=$b['subjectid'];
                }
                $subjectlist=implode(',',$subjectarra);
                $slq1="SELECT ".$azb.",ensubjectid FROM `mks_examination_score` WHERE `examinationid`=".$examinationid." and scId=".$scId." AND branchid=".$v['branchid']." AND ensubjectid in(".$subjectlist.")";
                $faf1=$dao->query($slq1);
                foreach($faf1 as $a=>$b){
                    foreach($aza as $c=>$d){
                        $azc[$b['ensubjectid']][$d]=$b[$d];
                    }
                }
                $slq2="SELECT c.`classname` AS className,c.`classid` AS class,r.`userid`,r.`results`,r.`subjectid` FROM `mks_examination_results` AS r,`mks_examination_student` AS s,mks_class AS c WHERE r.`examinationid`=".$examinationid." AND r.`branchid`=".$v['branchid']." AND r.`scId`=".$scId." AND s.`examinationid`=r.`examinationid` AND r.`userid`=s.userId AND c.`classid`=s.classId  AND r.subjectid in(".$subjectlist.")";
                $faf2=$dao->query($slq2);
                /*foreach($faf1 as $c=>$d){
                    $lin1[$d['ensubjectid']]=$d['lin1'];
                    $lin2[$d['ensubjectid']]=$d['lin2'];
                }*/

                $rra2=array();
                foreach($faf2 as $c=>$d){
                    $rra2[$d['subjectid']][]=$d;
                }
                $aaaap['lineList']=$arr1;
                $arr1=array();
                $str='';
                $sqln="SELECT c.`classname` as className,COUNT(r.userid) AS `join`,s.`subjectname` FROM `mks_examination_results` AS r,mks_user AS u,mks_subject AS s,mks_class AS c,`mks_examination_student` AS mb WHERE mb.userid=u.id and mb.classId=c.classid and mb.examinationid=r.examinationid and u.`id`=r.`userid` AND r.`examinationid`=".$examinationid." AND r.`subjectid`=s.`subjectid` AND r.`branchid`=".$v['branchid']." AND r.`scId`=".$scId." GROUP BY mb.`classId`,r.`subjectid`";
                $fn=$dao->query($sqln);
                foreach($fn as $c=>$d){
                    $a1[$v['branch']][$d['className']]['参考人数'][$d['subjectname']]=$d['join'];
                }
                $sql4="SELECT SUM(r.results) AS `results`,r.userid,r.`branchid`,c.`classname` as className,mb.classId as class,c.userid AS teacher FROM `mks_examination_results` AS r,mks_user AS u,mks_class AS c,`mks_examination_student` AS mb WHERE mb.`classId`=c.`classid` AND mb.userid=u.id AND mb.examinationid=r.examinationid AND r.`examinationid`=".$examinationid." AND r.scId=".$scId." AND r.`branchid`=".$v['branchid']." AND r.`userid`=u.`id` GROUP BY r.userid";
                $azaza=$dao->query($sql4);
                foreach($arr2 as $a=>$b){
                    foreach($azaza as $c=>$d){
                        if($a==0){
                            if($d['results']>=$b['score']){
                                $a1[$v['branch']][$d['className']]['总分'][$b['lineIndex']][]=$d['userid'];
                            }
                        }else{
                            if($d['results']>=$b['score']&&$d['results']<$arr2[$a-1]['score']){
                                $a1[$v['branch']][$d['className']]['总分'][$b['lineIndex']][]=$d['userid'];
                            }
                            //$sql4.=",SUM(CASE WHEN (results>=".$b['score']." and results<".$arr2[$a-1]['score'].") THEN 1 ELSE 0 END) as ".$b['lineIndex'];
                        }
                    }
                }
                //$sql4.=" from (SELECT SUM(r.results) AS `results`,r.userid,r.`branchid`,c.`classname` as className,mb.classId as class,c.userid AS teacher FROM `mks_examination_results` AS r,mks_user AS u,mks_class AS c,`mks_examination_student` AS mb WHERE mb.`classId`=c.`classid` AND mb.userid=u.id AND mb.examinationid=r.examinationid AND r.`examinationid`=".$examinationid." AND r.scId=".$scId." AND r.`branchid`=".$v['branchid']." AND r.`userid`=u.`id` GROUP BY r.userid) as h";
                //$a2=$dao->query($sql4);
                foreach($arr2 as $a=>$b){//循环分数线
                    if($a==0){
                        /*$sql4="SELECT `branchid` AS branch,`className`,teacher,userid,class FROM (SELECT SUM(r.results) AS `results`,r.userid,r.`branchid`,c.`classname` as className,mb.classId as class,c.userid AS teacher FROM `mks_examination_results` AS r,mks_user AS u,mks_class AS c,`mks_examination_student` AS mb WHERE mb.`classId`=c.`classid` AND mb.userid=u.id AND mb.examinationid=r.examinationid AND r.`examinationid`=".$examinationid." AND r.scId=".$scId." AND r.`branchid`=".$v['branchid']." AND r.`userid`=u.`id` GROUP BY r.userid) AS h where results>=".$b['score'];
                        $a2=$dao->query($sql4);

                        foreach($a2 as $c=>$d){
                            $a1[$v['branch']][$d['className']]['总分'][$b['lineIndex']][]=$d['userid'];
                        }*/
                        //$slq1="SELECT ".$b['lineIndex'].",ensubjectid FROM `mks_examination_score` WHERE `examinationid`=".$examinationid." and scId=".$scId." AND branchid=".$v['branchid']." AND ensubjectid in(".$subjectlist.")";
                        //$faf1=$dao->query($slq1);
                        foreach($subject as $c=>$d){
                            foreach($a1[$v['branch']] as $i=>$j){
                                $a1[$v['branch']][$i][$d['subjectname']][$b['lineIndex']]=array();
                            }
                            /*$sql5="SELECT r.`userid`,c.`classname` as className,mb.classId as class FROM `mks_examination_score` AS s,`mks_examination_results` AS r,mks_user AS u,mks_class AS c,`mks_examination_student` AS mb WHERE mb.userid=u.id and mb.classId=c.classid and mb.examinationid=r.examinationid and u.`id`=r.`userid` AND s.`examinationid`=".$examinationid." AND r.`branchid`=".$v['branchid']." AND r.`subjectid`=".$d['subjectid']." AND r.`subjectid`=s.`ensubjectid` AND s.`branchid`=r.`branchid` AND r.`examinationid`=s.`examinationid` AND r.`results`>=s.`".$b['lineIndex']."` AND r.`userid`=u.`id`";
                            $a3=$dao->query($sql5);
                            $av[]=$sql5;*/
                            foreach($rra2[$d['subjectid']] as $e=>$h){
                                if($h['results']>=$azc[$d['subjectid']][$b['lineIndex']]){
                                    $a1[$v['branch']][$h['className']][$d['subjectname']][$b['lineIndex']][]=$h['userid'];
                                }
                                //$a1[$v['branch']][$h['className']][$d['subjectname']][$b['lineIndex']][]=$h['userid'];
                            }
                            //$a1[$v['branch']][$d['subjectname']][$b['lineIndex']]
                        }
                    }else{
                        /*$sql4="SELECT `branchid` AS branch,`className`,teacher,userid,class FROM (SELECT SUM(r.results) AS `results`,r.userid,r.`branchid`,c.`classname` as className,mb.classId as class,c.userid AS teacher FROM `mks_examination_results` AS r,mks_user AS u,mks_class AS c,`mks_examination_student` AS mb WHERE  mb.userid=u.id and mb.classId=c.classid and mb.examinationid=r.examinationid AND r.`examinationid`=".$examinationid." AND r.scId=".$scId." AND r.`branchid`=".$v['branchid']." AND r.`userid`=u.`id` GROUP BY r.userid) AS h where results>=".$b['score']." and results<".$arr2[$a-1]['score'];
                        $a2=$dao->query($sql4);
                        foreach($a2 as $c=>$d){
                            $a1[$v['branch']][$d['className']]['总分'][$b['lineIndex']][]=$d['userid'];
                        }*/
                        //$a1[$v['branch']]['总分'][$b['lineIndex']]=$a2;
                        //$slq1="SELECT ".$b['lineIndex']." as lin1,".$arr2[$a-1]['lineIndex']." as lin2,ensubjectid FROM `mks_examination_score` WHERE `examinationid`=".$examinationid." and scId=".$scId." AND branchid=".$v['branchid']." AND ensubjectid in(".$subjectlist.")";
                        //$faf1=$dao->query($slq1);
                        //print_r($faf1);
                        //exit;

                        foreach($subject as $c=>$d){
                            foreach($a1[$v['branch']] as $i=>$j){
                                $a1[$v['branch']][$i][$d['subjectname']][$b['lineIndex']]=array();
                            }
                            /*$sql5="SELECT r.`userid`,c.`classname` as className,mb.classId as class FROM `mks_examination_score` AS s,`mks_examination_results` AS r,mks_user AS u,mks_class AS c,`mks_examination_student` AS mb WHERE s.`examinationid`=".$examinationid." AND r.`branchid`=".$v['branchid']." AND mb.userid=u.id and mb.classId=c.classid and mb.examinationid=r.examinationid and r.`subjectid`=".$d['subjectid']." AND r.`subjectid`=s.`ensubjectid` AND s.`branchid`=r.`branchid` AND r.`examinationid`=s.`examinationid` AND r.`results`>=s.`".$b['lineIndex']."` and r.`results`<s.`".$arr2[$a-1]['lineIndex']."` AND r.`userid`=u.`id`";
                            $av[]=$sql5;
                            $a3=$dao->query($sql5);*/
                            foreach($rra2[$d['subjectid']] as $e=>$h){
                                if($h['results']>=$azc[$d['subjectid']][$b['lineIndex']]&&$h['results']<$azc[$d['subjectid']][$arr2[$a-1]['lineIndex']]){
                                    $a1[$v['branch']][$h['className']][$d['subjectname']][$b['lineIndex']][]=$h['userid'];
                                }
                                //$a1[$v['branch']][$h['className']][$d['subjectname']][$b['lineIndex']][]=$h['userid'];
                            }
                        }
                    }
                }
                $arr2=array();
            }
            $aaa=array();
            foreach($a1 as $k=>$v){//科类
                foreach($v as $a=>$b){//班级
                    foreach($b as $c=>$d){
                        if($c!='总分' && $c!='参考人数'){
                            $aar['branch']=$k;
                            $aar['subject']=$c;
                            $aar['class']=$a;
                            if(!$b['参考人数'][$c]){
                                $b['参考人数'][$c]=0;
                            }
                            $aar['join']=$b['参考人数'][$c];
                            foreach($d as $e=>$f){
                                $aar[$e.'total']=count($b['总分'][$e]);
                                $aar[$e.'single']=count($f);
                                $aaa[$k][$c][$e.'total']+=$aar[$e.'total'];
                                $aaa[$k][$c][$e.'single']+=$aar[$e.'single'];
                                $o=0;
                                foreach($f as $g=>$h){
                                    foreach($b['总分'][$e] as $i=>$j){
                                        if($h==$j){
                                            $o++;
                                        }
                                    }
                                }
                                $aar[$e.'double']=$o;
                                $aaa[$k][$c][$e.'double']+=$o;
                                if(!$b['参考人数'][$c]){
                                    $aar[$e.'rate']=0;
                                }else{
                                    $aar[$e.'rate']=round((count($b[$c][$e])/$b['参考人数'][$c])*100,2);
                                }
                            }
                            $aaar[]=$aar;
                        }
                    }
                }
            }
            foreach($aaar as $k=>$v){
                $aaak[$v['branch']][$v['subject']][]=$v;
                $aaa[$v['branch']][$v['subject']]['join']+=$v['join'];
            }
            foreach($aaa as $k=>$v){
                foreach($v as $c=>$d){
                    foreach($d as $a=>$b){
                        $jj=explode('total',$a);
                        if(count($jj)==2){
                            $aaa[$k][$c][$jj['0'].'rate']=round(($d[$jj['0'].'single']/$d['join'])*100,2);
                            $aaa[$k][$c]['branch']=$k;
                            $aaa[$k][$c]['subject']=$c;
                            $aaa[$k][$c]['class']='全年级';
                        }
                    }
                }
            }
            foreach($aaak as $k=>$v){
                foreach($v as $a=>$b){
                    foreach($b['0'] as $c=>$d){
                        $zzz[$c]=$aaa[$k][$a][$c];
                    }
                    $aaak[$k][$a][]=$zzz;
                }
            }
            foreach($aaak as $k=>$v){
                foreach($v as $a=>$b){
                    foreach($b as $c=>$d){
                        $aah[]=$d;
                    }
                }
            }
            $aaaap['data']=$aah;
            $aaaap['score']=true;
            return $aaaap;
        }else{
            $aaaap['score']=false;
            $aaaap['lineList']=null;
            return $aaaap;
        }
    }
    private function rangeInspect($branchid,$examinationid1,$examinationid2){
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql1="SELECT subject FROM `mks_examination_subject` WHERE examinationid=".$examinationid1." AND scId=".$scId." AND branchid=".$branchid;
        $sql2="SELECT subject FROM `mks_examination_subject` WHERE examinationid=".$examinationid2." AND scId=".$scId." AND branchid=".$branchid;
        $f1=$dao->query($sql1);
        $f2=$dao->query($sql2);
        if(empty($f1)||empty($f2)){
            $arr['return']=false;
            $this->ajaxReturn($arr);
            exit;
        }
        foreach($f1 as $k=>$v){
            $arra[]=$v['subject'];
        }
        foreach($f2 as $k=>$v){
            $arrb[]=$v['subject'];
        }
        $ak=array_intersect($arra,$arrb);
        if(empty($ak)){
            $arr['return']=false;
            $this->ajaxReturn($arr);
            exit;
        }
        $sql3="select class from mks_examination where examinationid=".$examinationid1." AND scId=".$scId;
        $sql4="select class from mks_examination where examinationid=".$examinationid2." AND scId=".$scId;
        $f3=$dao->query($sql3);
        $f4=$dao->query($sql4);
        $sql5="SELECT classid FROM `mks_class` WHERE classid IN(".$f3['0']['class'].") AND scId=".$scId." AND branch=".$branchid;
        $sql6="SELECT classid FROM `mks_class` WHERE classid IN(".$f4['0']['class'].") AND scId=".$scId." AND branch=".$branchid;
        $f5=$dao->query($sql5);
        $f6=$dao->query($sql6);
        foreach($f5 as $k=>$v){
            $arrc[]=$v['classid'];
        }
        foreach($f6 as $k=>$v){
            $arrd[]=$v['classid'];
        }
        $ah=array_intersect($arrc,$arrd);
        if(empty($ah)){
            $arr['return']=false;
            $this->ajaxReturn($arr);
            exit;
        }
    }
    public function achievementFind(){/**********成绩查询*****/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(I('get.type')=='student'){/*****************************学生信息************************/
            $dao=M();
            $scId=$getSession['scId'];
            $userid=I("post.userid");
            $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
            $sql1="select u.`grade`,u.`className`,u.`name`,e.`examination`,e.`examinationid`,u.class as classid,c.branch as branchid from mks_user as u,mks_examination as e,`mks_examination_results` as r,mks_class as c where u.id=".$userid." and u.class=c.classid and e.release=1 and r.`userid`=u.`id` and r.`examinationid`=e.`examinationid` and u.scId=".$scId." group by r.`examinationid`";
            $f1=$dao->query($sql1);
            foreach($f1 as $k=>$v){
                $arr['grade']=$arrgrade[$v['grade']+1];
                $arr['className']=$v['className'];
                $arr['name']=$v['name'];
                $arr1['examination']=$v['examination'];
                $arr1['examinationid']=$v['examinationid'];
                $arr['examinationlist'][]=$arr1;
                $arr['branchid']=$v['branchid'];
                $arr['classid']=$v['classid'];
                $arr['userid']=$userid;
            }
            $this->ajaxReturn($arr);
        }
        if(I('get.type')=='findgrade'){/***********************年级列表****************/
            $this->findgrade();
        }elseif(I('get.type')=='findexam') {/*******************考试查询*****************/
            $gradeid=I('post.gradeid');
            $this->findexam($gradeid);
        }elseif(I('get.type')=='findclass') {/*******************科类班级查询*****************/
            $examinationid=I('post.examinationid');
            $this->findclass($examinationid);
        }elseif(I('get.type')=='subjectfind'){/****************考试科目查询****************/
            $examinationid=I('post.examinationid');
            $branchid=I('post.branchid');
            $arr=$this->findsubject($examinationid,$branchid);
            if(!$arr){
                $arr=array();
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='acfind'){/****************成绩查询*****************/
            $examinationid=I('post.examinationid');
            $branchid=I('post.branchid');
            $classid1=I('post.classid');
            $classid=implode(',',$classid1);

            //$classid=I('post.classid');
            $subjectid1=I('post.subjectid');
            $subjectid=implode(',',$subjectid1);
            $afterNum=I('post.afterNum');//前**名
            $beforNum=I('post.beforNum');//后**名
            $isRemoveMarkZero=I('post.isRemoveMarkZero');//除去总分0分
            $isRemoveResultZero=I('post.isRemoveResultZero');//除去单科缺考
            $arr=$this->acfind($examinationid,$branchid,$classid,$subjectid,$afterNum,$beforNum,$isRemoveMarkZero,$isRemoveResultZero);
            if(!$arr['subject']){
                $arr['subject']=array();
            }
            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order=true;
            }else{
                $order=false;
            }
            $page=I('post.page');
            $find=I('post.find');

            if($find==''){
                $find=false;
            }
            $limit=I('post.limit');
            if(!$limit){
                $limit=2;
            }
            $arr1=$this->sortArrByOneField($arr['student'],$field,$order,$find,$page,$limit);
            $arr['student']=$arr1['data'];
            if(empty($arr['student'])){
                $arr['student']=array();
            }
            if(empty($arr['pageclass'])){
                $arr['pageclass']=array();
            }
            $arr['pageclass']=$arr1['pageclass'];
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='personalfind'){/************************学生个人成绩*****************/
            $examinationid=I('post.examinationid');
            $userid=I('post.userid');
            $branchid=I('post.branchid');
            $classid=I('post.classid');
            $dao=M('examination');
            $sql1="SELECT branch,classId FROM `mks_examination_student` WHERE examinationid=".$examinationid." AND userid=".$userid." and scId=".$scId;
            $f1=$dao->query($sql1);
            $branchid=$f1['0']['branch'];
            $classid=$f1['0']['classId'];
            $array=$this->getPersonal($examinationid,$userid,$branchid,$classid);
            $sqla="SELECT COUNT(*) as num FROM `mks_examination_student` WHERE branch=".$branchid." AND scId=".$scId." AND examinationid=".$examinationid;
            $fa=$dao->query($sqla);
            $sqlb="SELECT COUNT(*) as num FROM `mks_examination_student` WHERE classId=".$classid." AND scId=".$scId." AND examinationid=".$examinationid;
            $fb=$dao->query($sqlb);
            $array['gradeAll']=$fa[0]['num'];
            $array['classAll']=$fb[0]['num'];
            $this->ajaxReturn($array);
        }elseif(I('get.type')=='comparison'){/****************成绩对比******************/
            $examinationid=I('post.examinationid');
            $userid=I('post.userid');
            $branchid=I('post.branchid');
            $classid=I('post.classid');
            $dao=M('examination');
            //$classid=implode(',',$classid1);
            $arrpa=0;
            $arrpb=0;
            foreach($examinationid as $k=>$v){
                $sql1="SELECT s.branch,s.classId,e.examination FROM `mks_examination_student` as s,mks_examination as e WHERE e.examinationid=s.examinationid and s.examinationid=".$v." AND s.userid=".$userid." and s.scId=".$scId;
                $f1=$dao->query($sql1);
                $branchid=$f1['0']['branch'];
                $classid=$f1['0']['classId'];
                $sqla="SELECT COUNT(*) as num FROM `mks_examination_student` WHERE branch=".$branchid." AND scId=".$scId." AND examinationid=".$v;
                $fa=$dao->query($sqla);
                $sqlb="SELECT COUNT(*) as num FROM `mks_examination_student` WHERE classId=".$classid." AND scId=".$scId." AND examinationid=".$v;
                $fb=$dao->query($sqlb);
                $arr=$this->comparison($userid,$branchid,$classid,$v);
                $arrz['examination']=$f1['0']['examination'];
                $arrn['score']=$arr['classscore'];
                $arrn['gradeRanking']=$arr['gradeRanking']['total'];
                $arrn['classRanking']=$arr['classRanking']['total'];
                $arrz['value']=$arrn;
                $arr1[]=$arrz;
                foreach($arr['subjectlist'] as $a=>$b){
                    $arr2[]=$b;
                }
                if($arrpa<$fa[0]['num']){
                    $arrpa=$fa[0]['num'];
                }
                if($arrpb<$fb[0]['num']){
                    $arrpb=$fb[0]['num'];
                }
            }
            $arr3=array();
            $i=0;
            foreach($arr2 as $k=>$v){
                if(!in_array($v,$arr3)){
                    $arr3[$i]=$v;
                    $i++;
                }
            }
            $arrp['subjectlist']=$arr3;
            $arrp['data']=$arr1;
            $arrp['gradeAll']=$arrpa;
            $arrp['classAll']=$arrpb;
            $this->ajaxReturn($arrp);
        }elseif(I('get.type')=='scoreexport'){/****************成绩导出******************/
            $examinationid=I('get.examinationid');
            $branchid=I('get.branchid');
            $classid1=json_decode(urldecode($_GET['classid']),true);
            $classid=I('get.classid');//implode(',',$classid1);
            $subjectid1=json_decode(urldecode($_GET['subjectid']),true);
            $subjectid=I('get.subjectid');//implode(',',$subjectid1);
            $ranking=I('get.ranking');
            $afterNum=I('get.afterNum');//前**名
            $beforNum=I('get.beforNum');//后**名
            $isRemoveMarkZero=I('get.isRemoveMarkZero');//除去总分0分
            $isRemoveResultZero=I('get.isRemoveResultZero');//除去单科缺考
            $arr=$this->acfind($examinationid,$branchid,$classid,$subjectid,$afterNum,$beforNum,$isRemoveMarkZero,$isRemoveResultZero);
            $arr1=array('姓名','班级','座号');
            foreach($arr['subject'] as $k=>$v){
                $key=$k+3;
                $arr1[$key]=$v['subjectname'];
                $arr2[]=$v['subjectid'];
            }
            $sum=count($arr2);
            array_push($arr1,"总分","班名次","级名次");
            foreach($arr['student'] as $k=>$v){
                $arr3[]=$v['name'];
                $arr3[]=$v['className'];
                $arr3[]=$v['serialNumber'];
                if($ranking){
                    foreach($arr2 as $a=>$b){
                        $arr3[]=$v[$b]['results']."|".$v[$b]['ranking'];
                    }
                }else{
                    foreach($arr2 as $a=>$b){
                        $arr3[]=$v[$b]['results'];
                    }
                }
                $arr3[]=$v['totalScore'];
                $arr3[]=$v['classRanking'];
                $arr3[]=$v['gradeRanking'];
                $arr4[]=$arr3;
                $arr3=array();
            }
            $this->getExport($arr1,$arr4);
        }
        if(I('get.type')=='userExam'){/***********************学生考试*********************/
            $dao=M();
            $scId=$getSession['scId'];
            $id=I('post.userid');
            $sql1="select u.`grade`,u.`className`,u.`name`,e.`examination`,e.`examinationid`,u.class as classid,c.branch as branchid
            from mks_user as u,mks_examination as e,`mks_examination_results` as r,mks_class as c
            where u.id=".$id." and u.class=c.classid and e.release=1 and r.`userid`=u.`id` and r.`examinationid`=e.`examinationid` and u.scId=".$scId." group by r.`examinationid`";
            $f1=$dao->query($sql1);
            foreach($f1 as $k=>$v){
                $arr1['examination']=$v['examination'];
                $arr1['examinationid']=$v['examinationid'];
                $arr[]=$arr1;
            }
            if(!$f1){
                $arr=array();
            }
            $this->ajaxReturn($arr);
        }
    }
    public function statistics(){/***************成绩统计*****************/
        if(I('get.type')=='findgrade'){/***********************年级列表****************/
            $this->findgrade();
        }elseif(I('get.type')=='findexam') {/*******************考试查询*****************/
            $gradeid=I('post.gradeid');
            $this->findexam($gradeid);
        }elseif(I('get.type')=='findbranch') {/*******************科类查询*****************/
            $examinationid=I('post.examinationid');
            $this->findbranch($examinationid);
        }
        if(I('get.type')=='subject'){/*********************学科统计*******************/
            $examinationid=I('post.examinationid');
            $statistics=I('post.statistics');
            $arr=$this->getSubjectStatistics($examinationid,$statistics);
            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order=true;
            }else{
                $order=false;
            }
            $page=I('post.page');
            if(!$page){
                $page=1;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $limit=I('post.limit');
            if(!$limit){
                $limit=2;
            }
            $arr1=$this->sortArrByOneField($arr,$field,$order,$find,$page,$limit);
            if(!$arr1['data']){
                $arr1['data']=array();
            }
            $this->ajaxReturn($arr1);
        }
        elseif(I('get.type')=='subjectexport'){/*********************学科统计导出*******************/
            $examinationid=I('get.examinationid');
            $statistics=I('get.statistics');
            $arr=$this->getSubjectStatistics($examinationid,$statistics);
            $header=array('科类','科目','班级','参考人数','均分','排名','最高分','最低分','优秀数','优秀率%','及格数','及格率%','低分数','低分率%','教师');
            $this->getExport($header,$arr);

        }
        if(I('get.type')=='ranking'){/**************************名次统计*******************/
            //$sql1="SELECT u.`className`,r.`userid`,SUM(r.`results`) AS sum1 FROM `mks_examination_results` AS r,mks_user AS u WHERE r.`examinationid`=192 AND r.`branchid`=2 AND r.`userid`=u.`id` GROUP BY r.`userid` ORDER BY sum1 DESC";
            $examinationid=I('post.examinationid');
            $branchid=I('post.branchid');
            $rank1=I('post.rank1');
            $rank2=I('post.rank2');
            $rank3=I('post.rank3');
            $rank4=I('post.rank4');
            $rank5=I('post.rank5');
            $rank6=I('post.rank6');
            if(!$rank1){
                $rank1=0;
            }
            if(!$rank2){
                $rank2=0;
            }
            if(!$rank3){
                $rank3=0;
            }
            if(!$rank4){
                $rank4=0;
            }
            if(!$rank5){
                $rank5=0;
            }
            if(!$rank6){
                $rank6=0;
            }
            $arr1=$this->getSum($branchid,$examinationid,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6);
            foreach($arr1 as $k=>$v){
                $arr[]=$v;
            }
            $subject=$this->findsubject($examinationid,$branchid);
            foreach($subject as $k=>$v){
                $arr1=$this->getRankingSubject($v['subjectid'],$branchid,$examinationid,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6);
                foreach($arr1 as $k=>$v){
                    $arr[]=$v;
                }
            }
            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order=true;
            }else{
                $order=false;
            }
            $page=I('post.page');
            if(!$page){
                $page=1;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $limit=I('post.limit');
            if(!$limit){
                $limit=2;
            }
            $arr1=$this->sortArrByOneField($arr,$field,$order,$find,$page,$limit);
            if(!$arr1['data']){
                $arr1['data']=array();
            }
            $this->ajaxReturn($arr1);
        }elseif(I('get.type')=='rankingexport'){/**************************名次统计导出*******************/
            $examinationid=I('get.examinationid');
            $branchid=I('get.branchid');
            $rank1=I('get.rank1');
            $rank2=I('get.rank2');
            $rank3=I('get.rank3');
            $rank4=I('get.rank4');
            $rank5=I('get.rank5');
            $rank6=I('get.rank6');
            if(!$rank1){
                $rank1=0;
            }
            if(!$rank2){
                $rank2=0;
            }
            if(!$rank3){
                $rank3=0;
            }
            if(!$rank4){
                $rank4=0;
            }
            if(!$rank5){
                $rank5=0;
            }
            if(!$rank6){
                $rank6=0;
            }
            $arr1=$this->getSum($branchid,$examinationid,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6);
            foreach($arr1 as $k=>$v){
                $arr[]=$v;
            }
            $subject=$this->findsubject($examinationid,$branchid);
            foreach($subject as $k=>$v){
                $arr1=$this->getRankingSubject($v['subjectid'],$branchid,$examinationid,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6);
                foreach($arr1 as $k=>$v){
                    $arr[]=$v;
                }
            }
            $header=array('科目','班级','参考人数','平均名次','前'.$rank1.'名','前'.$rank2.'名','前'.$rank3.'名','前'.$rank4.'名','前'.$rank5.'名','前'.$rank6.'名','教师');
            $this->getExport($header,$arr);
        }
        if(I('get.type')=='subsection'){/*******************分段统计****************/
            $examinationid=I('post.examinationid');
            $section=I('post.section');
            $branchid=I('post.branchid');
            $data=$this->getSubsection($examinationid,$section,$branchid);
            foreach($section as $k=>$v){
                $aa['name']=$section[$k]['from']."-".$section[$k]['to'];
                $title[]=$aa;
            }
            if(!$title){
                $title=array();
            }
            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order=true;
            }else{
                $order=false;
            }
            $page=I('post.page');
            if(!$page){
                $page=1;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $limit=I('post.limit');
            if(!$limit){
                $limit=2;
            }
            $arr2['title']=$title;
            $arr1=$this->sortArrByOneField($data,$field,$order,$find,$page,$limit);
            if(!$arr1['data']){
                $arr1['data']=array();
            }
            $arr2['data']=$arr1['data'];
            $arr2['pageclass']=$arr1['pageclass'];
            $this->ajaxReturn($arr2);
        }elseif(I('get.type')=='subsectionexport'){/*******************分段统计导出****************/
            $examinationid=I('get.examinationid');
            $section=explode(',',I('get.section'));//json_decode(urldecode($_GET['section']),true);
            $sectiona=I('get.section');//json_decode(urldecode($_GET['section']),true);
            $branchid=I('get.branchid');
            $data=$this->getSubsection($examinationid,$sectiona,$branchid);

            $header=array('班级','参考人数','平均分','名次','最高分','最低分');
            foreach($sectiona as $k=>$v){
                $header[]=$v['from']."-".$v['to'];
            }
            $herder[]='班主任';
            //$header=array('班级','参考人数','平均分','名次','最高分','最低分',$section['0']['from']."-".$section['0']['to'],$section['1']['from']."-".$section['1']['to'],$section['2']['from']."-".$section['2']['to'],$section['3']['from']."-".$section['3']['to'],$section['4']['from']."-".$section['4']['to'],$section['5']['from']."-".$section['5']['to'],$section['6']['from']."-".$section['6']['to'],'班主任');
            $this->getExport($header,$data);
        }
        if(I('get.type')=='avg'){/********************均分总表********************/
            $examinationid=I('post.examinationid');
            $branchid=I('post.branchid');
            $arr=$this->getAvg($examinationid,$branchid);
            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order=true;
            }else{
                $order=false;
            }
            $page=I('post.page');
            if(!$page){
                $page=1;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $limit=I('post.limit');
            if(!$limit){
                $limit=2;
            }
            $arr1=$this->sortArrByOneField($arr['data'],$field,$order,$find,$page,$limit);
            if(!$arr1['data']){
                $arr1['data']=array();
            }
            $arr['data']=$arr1['data'];
            $arr['pageclass']=$arr1['pageclass'];

            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='avgexport'){/********************均分总表导出********************/
            $examinationid=I('get.examinationid');
            $branchid=I('get.branchid');
            $arr=$this->getAvg($examinationid,$branchid);
            if(I('get.ranking')){
                foreach($arr['data'] as $k=>$v){
                    unset($arr['data'][$k]['totalRanking']);
                    if($arr['data'][$k]['className']=='全年级'){
                        $arr['data'][$k]['totalResults']=$v['totalResults'];
                        foreach($v as $a=>$b){
                            if($a!='className'&&$a!='totalResults'&&$a!='totalRanking'&&$a!='teacher'){
                                $arr['data'][$k][$a]=$b['resutls'];
                            }
                        }
                    }else{
                        $arr['data'][$k]['totalResults']=$v['totalResults'].'|'.$v['totalRanking'];
                        foreach($v as $a=>$b){
                            if($a!='className'&&$a!='totalResults'&&$a!='totalRanking'&&$a!='teacher'){
                                $arr['data'][$k][$a]=$b['resutls']."|".$b['ranking'];
                            }
                        }
                    }
                }
            }else{
                foreach($arr['data'] as $k=>$v){
                    $arr['data'][$k]['totalResults']=$v['totalResults'];
                    unset($arr['data'][$k]['totalRanking']);
                    foreach($v as $a=>$b){
                        if($a!='className'&&$a!='totalResults'&&$a!='totalRanking'&&$a!='teacher'){
                            $arr['data'][$k][$a]=$b['resutls'];
                        }
                    }
                }
            }
            $header=array('班级','总分');
            foreach($arr['subjectlist'] as $k=>$v){
                $header[]=$v['subjectname'];
            }
            $header[]='班主任';
            $this->getExport($header,$arr['data']);
        }
    }
    public function contrast(){/***************两次对比***************/
        $getSession = $this->get_session('loginCheck',false);
        if(I('get.type')=='findgrade'){/***********************年级列表****************/
            $this->findgrade();
        }
        elseif(I('get.type')=='findexam') {/*******************考试查询*****************/
            $gradeid=I('post.gradeid');
            $this->findexam($gradeid);
        }
        elseif(I('get.type')=='findclass') {/*******************科类班级查询*****************/
            $gradeid = I('post.gradeid');
            $this->findClassAll($gradeid);
        }
        elseif(I('get.type')=='exam'){/******************对应班级的考试列表********************/
            $gradeid=I('post.gradeid');
            $classid=I('post.classid');
            $dao1=M('examination');
            $where1['scId']=$getSession['scId'];
            $where1['gradeid']=$gradeid;
            $where1['release']=1;//修改，使用时去掉注释，只返回已发布成绩的考试
            $f1=$dao1->where($where1)->field('examinationid,examination,class')->select();
            $arr2=array();
            foreach($f1 as $k=>$v){
                if(count(array_intersect($classid,explode(',',$v['class'])))){
                    unset($v['class']);
                    $arr2[]=$v;
                }
            }
            $this->ajaxReturn($arr2);
        }
        elseif(I('get.type')=='branch'){
            $scId=$getSession['scId'];
            $examinationid1=I('post.examinationid1');
            $examinationid2=I('post.examinationid2');
            if(I('post.gradeid')){
                $gradeid = I('post.gradeid');
                $this->findClassAll($gradeid);
            }else{
                $dao1=M('examination');
                $where1['examinationid']=$examinationid1;
                $where1['scId']=$scId;
                $f1=$dao1->where($where1)->field('class')->select();
                $where1['examinationid']=$examinationid2;
                $f2=$dao1->where($where1)->field('class')->select();
                $class1=explode(',',$f1['0']['class']);
                $class2=explode(',',$f2['0']['class']);
                $classlist=array_intersect($class1,$class2);
                $class=implode(',',$classlist);
                $sql2="SELECT b.branch,b.`branchid` FROM mks_class AS c,mks_class_branch AS b WHERE c.`classid` IN(".$class.") AND c.`branch`=b.`branchid` AND c.scId=".$scId;
                $f3=$dao1->query($sql2);
                foreach($f3 as $k=>$v){
                    $aaz[$v['branchid']]=$v['branch'];
                }
                foreach($aaz as $k=>$v){
                    $aay['branchid']=$k;
                    $aay['branch']=$v;
                    $arra[]=$aay;
                }
                if(!$arra){
                    $arra=array();
                }
                $this->ajaxReturn($arra);
            }
        }
        if(I('get.type')=='result') {/*******************成绩对比*******************/
            $examinationid1 = I('post.examinationid1');
            $examinationid2 = I('post.examinationid2');
            $class1=$class = I('post.class');
            $class=implode(',',$class1);
            $branchid = I('post.branchid');
            $this->rangeInspect($branchid,$examinationid1,$examinationid2);
            $arr=$this->getContrastResultAll($examinationid1,$examinationid2,$branchid,$class);
            foreach($arr['data'] as $k=>$v){
                foreach($arr['data'][$k]['exam']['0'] as $a=>$b){
                    if($a=='总分'){
                        $a='zf';
                    }
                    $arr['data'][$k]['exa'][$a]['ranking']=$b['ranking'];
                    $arr['data'][$k]['exa'][$a]['results']=$b['results'];
                }
                foreach($arr['data'][$k]['exam']['1'] as $a=>$b){
                    if($a=='总分'){
                        $a='zf';
                        $arr['data'][$k]['exa'][$a]['progress']=$b['progress'];
                    }
                    $arr['data'][$k]['exa'][$a]['rankingContrast']=$b['ranking'];
                    $arr['data'][$k]['exa'][$a]['resultsContrast']=$b['results'];
                }
                unset($arr['data'][$k]['exam']);
            }
            //$aaz['subjectid']='zf';
            //$aaz['subjectname']='总分';
            //$arr['subjectlist'][]=$aaz;
            if(!$arr['subjectlist']){
                $arr['subjectlist']=array();
            }
            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order=true;
            }else{
                $order=false;
            }
            $page=I('post.page');
            if(!$page){
                $page=1;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $limit=I('post.limit');
            if(!$limit){
                $limit=2;
            }
            $arr1=$this->sortArrByOneField($arr['data'],$field,$order,$find,$page,$limit);
            if(!$arr1['data']){
                $arr1['data']=array();
            }
            $arr['data']=$arr1['data'];
            $arr['pageclass']=$arr1['pageclass'];
            $arr['return']=true;
            $this->ajaxReturn($arr);
        }
        elseif(I('get.type')=='resultexport'){/*******************成绩对比导出*******************/
            $examinationid1 = I('get.examinationid1');
            $examinationid2 = I('get.examinationid2');
            //$class1=json_decode(urldecode($_GET['class']),true);
            $class=I('get.class');//implode(',',$class1);
            $a=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
            $branchid = I('get.branchid');
            $arr=$this->getContrastResultAll($examinationid1,$examinationid2,$branchid,$class);
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
            $objPHPExcel->getActiveSheet()->SetCellValue('A1', '班级');
            $objPHPExcel->getActiveSheet()->SetCellValue('B1', '座号');
            $objPHPExcel->getActiveSheet()->SetCellValue('C1', '姓名');
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
            $objPHPExcel->getActiveSheet()->mergeCells('B1:B2');
            $objPHPExcel->getActiveSheet()->mergeCells('C1:C2');
            $objPHPExcel->getActiveSheet()->SetCellValue('D1', '总分');
            $objPHPExcel->getActiveSheet()->mergeCells('D1:H1');
            $objPHPExcel->getActiveSheet()->SetCellValue('D2', '本次成绩');
            $objPHPExcel->getActiveSheet()->SetCellValue('E2', '名次');
            $objPHPExcel->getActiveSheet()->SetCellValue('F2', '上次成绩');
            $objPHPExcel->getActiveSheet()->SetCellValue('G2', '名次');
            $objPHPExcel->getActiveSheet()->SetCellValue('H2', '进步');
            $i1=8;
            foreach($arr['subjectlist'] as $k=>$v){
                $objPHPExcel->getActiveSheet()->SetCellValue($a[$i1].'1', $v['subjectname']);
                $i2=$i1+3;
                $objPHPExcel->getActiveSheet()->mergeCells($a[$i1].'1:'.$a[$i2].'1');
                $objPHPExcel->getActiveSheet()->SetCellValue($a[$i1].'2', '本次成绩');
                $objPHPExcel->getActiveSheet()->SetCellValue($a[$i1+1].'2', '名次');
                $objPHPExcel->getActiveSheet()->SetCellValue($a[$i1+2].'2', '上次成绩');
                $objPHPExcel->getActiveSheet()->SetCellValue($a[$i1+3].'2', '名次');
                $i1=$i1+4;
            }
            $i = 3;
            foreach ($arr['data'] as $k => $v) {
                $i1=8;
                $num = $i++;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $num, $v['className']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $num, $v['serialNumber']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $num, $v['name']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $num, $v['exam']['0']['总分']['results']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $num, $v['exam']['0']['总分']['ranking']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $num, $v['exam']['1']['总分']['results']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $num, $v['exam']['1']['总分']['ranking']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $num, $v['exam']['1']['总分']['progress']);
                foreach($arr['subjectlist'] as $h=>$b){
                    $objPHPExcel->getActiveSheet()->SetCellValue($a[$i1].$num, $v['exam']['0'][$b['subjectid']]['results']);
                    $objPHPExcel->getActiveSheet()->SetCellValue($a[$i1+1].$num, $v['exam']['0'][$b['subjectid']]['ranking']);
                    $objPHPExcel->getActiveSheet()->SetCellValue($a[$i1+2].$num, $v['exam']['1'][$b['subjectid']]['results']);
                    $objPHPExcel->getActiveSheet()->SetCellValue($a[$i1+3].$num, $v['exam']['1'][$b['subjectid']]['ranking']);
                    $i1=$i1+4;
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
        if(I('get.type')=='rank'){/*******************名次对比*******************/
            $examinationid1=I('post.examinationid1');
            $examinationid2=I('post.examinationid2');
            $branchid=I('post.branchid');
            $this->rangeInspect($branchid,$examinationid1,$examinationid2);
            $ar1[]=$rank1=I('post.rank1');
            $ar1[]=$rank2=I('post.rank2');
            $ar1[]=$rank3=I('post.rank3');
            $ar1[]=$rank4=I('post.rank4');
            $ar1[]=$rank5=I('post.rank5');
            $ar1[]=$rank6=I('post.rank6');

            foreach($ar1 as $k=>$v){
                $titlez['name']=$v;
                $i=$k+1;
                $titlez['value']='value'.$i;
                $title[]=$titlez;
            }
            if(!$rank1){
                $rank1=0;
            }
            if(!$rank2){
                $rank2=0;
            }
            if(!$rank3){
                $rank3=0;
            }
            if(!$rank4){
                $rank4=0;
            }
            if(!$rank5){
                $rank5=0;
            }
            if(!$rank6){
                $rank6=0;
            }
            $az=array('a','b','c','d','e','f');
            $arr=$this->getContrastRank($branchid,$examinationid1,$examinationid2,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6);

            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order=true;
            }else{
                $order=false;
            }
            $page=I('post.page');
            if(!$page){
                $page=1;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $limit=I('post.limit');
            if(!$limit){
                $limit=2;
            }
            //print_r($arr);
            //exit;
            foreach($arr as $k=>$v){
                $i=$k+1;
                $arr2[$k]['subjectname']=$v['subjectname'];
                $arr2[$k]['className']=$v['className'];
                $arr2[$k]['join']=$v['join'];
                $arr2[$k]['avg']=$v['avg'];
                $arr2[$k]['avga']=$v['avga'];
                $arr2[$k]['shortavg']=$v['shortavg'];
                for($i=1;$i<=6;$i++){
                    $z=$i-1;
                    $arr2[$k]['value'.$i]['rank1']=$v['rank'.$i];
                    $arr2[$k]['value'.$i]['rank2']=$v['rank'.$az[$z]];
                    $arr2[$k]['value'.$i]['shortrank']=$v['shortrank'.$i];
                }
            }
            if(!$title){
                $title=array();
            }
            $arr3['title']=$title;
            $arr1=$this->sortArrByOneField($arr2,$field,$order,$find,$page,$limit);
            if(!$arr1['data']){
                $arr1['data']=array();
            }
            $arr3['data']=$arr1['data'];
            $arr3['pageclass']=$arr1['pageclass'];
            $arr3['return']=true;
            $this->ajaxReturn($arr3);
        }
        elseif(I('get.type')=='rankexport'){/*******************名次对比导出*******************/
            $examinationid1=I('get.examinationid1');
            $examinationid2=I('get.examinationid2');
            $branchid=I('get.branchid');
            $rank1=I('get.rank1');
            $rank2=I('get.rank2');
            $rank3=I('get.rank3');
            $rank4=I('get.rank4');
            $rank5=I('get.rank5');
            $rank6=I('get.rank6');
            $header=array('科目','班级','对比人数','本次平均名次','对比平均名次','差值','本次前'.$rank1.'名','对比前'.$rank1.'名','差值','本次前'.$rank2.'名','对比前'.$rank2.'名','差值','本次前'.$rank3.'名','对比前'.$rank3.'名','差值','本次前'.$rank4.'名','对比前'.$rank4.'名','差值','本次前'.$rank5.'名','对比前'.$rank5.'名','差值','本次前'.$rank6.'名','对比前'.$rank6.'名','差值');
            if(!$rank1){
                $rank1=0;
            }
            if(!$rank2){
                $rank2=0;
            }
            if(!$rank3){
                $rank3=0;
            }
            if(!$rank4){
                $rank4=0;
            }
            if(!$rank5){
                $rank5=0;
            }
            if(!$rank6){
                $rank6=0;
            }
            $arr=$this->getContrastRank($branchid,$examinationid1,$examinationid2,$rank1,$rank2,$rank3,$rank4,$rank5,$rank6);
            foreach($arr as $k=>$v){
                $arr1[$k]['subjectname']=$v['subjectname'];
                $arr1[$k]['className']=$v['className'];
                $arr1[$k]['join']=$v['join'];
                $arr1[$k]['avg']=$v['avg'];
                $arr1[$k]['avga']=$v['avga'];
                $arr1[$k]['shortavg']=$v['shortavg'];
                $arr1[$k]['rank1']=$v['rank1'];
                $arr1[$k]['ranka']=$v['ranka'];
                $arr1[$k]['shortrank1']=$v['shortrank1'];
                $arr1[$k]['rank2']=$v['rank2'];
                $arr1[$k]['rankb']=$v['rankb'];
                $arr1[$k]['shortrank2']=$v['shortrank2'];
                $arr1[$k]['rank3']=$v['rank3'];
                $arr1[$k]['rankc']=$v['rankc'];
                $arr1[$k]['shortrank3']=$v['shortrank3'];
                $arr1[$k]['rank4']=$v['rank4'];
                $arr1[$k]['rankd']=$v['rankd'];
                $arr1[$k]['shortrank4']=$v['shortrank4'];
                $arr1[$k]['rank5']=$v['rank5'];
                $arr1[$k]['ranke']=$v['ranke'];
                $arr1[$k]['shortrank5']=$v['shortrank5'];
                $arr1[$k]['rank6']=$v['rank6'];
                $arr1[$k]['rankf']=$v['rankf'];
                $arr1[$k]['shortrank6']=$v['shortrank6'];
            }
            $this->getExport($header,$arr1);
        }
        if(I('get.type')=='avg'){/**********************均分对比***********************/
            $examinationid1=I('post.examinationid1');
            $examinationid2=I('post.examinationid2');
            $branchid=I('post.branchid');
            $arr=$this->getContrastAvg($examinationid1,$examinationid2,$branchid);
            $this->rangeInspect($branchid,$examinationid1,$examinationid2);
            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order=true;
            }else{
                $order=false;
            }
            $page=I('post.page');
            if(!$page){
                $page=1;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $limit=I('post.limit');
            if(!$limit){
                $limit=2;
            }
            if(!$arr1['data']){
                $arr1['data']=array();
            }
            $arr1=$this->sortArrByOneField($arr,$field,$order,$find,$page,$limit);
            $arr1['return']=true;
            $this->ajaxReturn($arr1);
        }elseif(I('get.type')=='avgexport'){/**********************均分对比导出***********************/
            $examinationid1=I('get.examinationid1');
            $examinationid2=I('get.examinationid2');
            $branchid=I('get.branchid');
            $arr=$this->getContrastAvg($examinationid1,$examinationid2,$branchid);
            $header=array('科目','班级','参考人数','本次平均分','本次名次','对比平均分','目标系数','目标均分','均分进退','对比名次','名次进退');
            $this->getExport($header,$arr);
        }
    }

    public function score(){/*******************模拟上线*******************/
        if(I('get.type')=='findgrade'){/***********************年级列表****************/
            $this->findgrade();
        }elseif(I('get.type')=='findexam') {/*******************考试查询*****************/
            $gradeid=I('post.gradeid');
            $this->findexam($gradeid);
        }
        if(I('get.type')=='totalscore'){/********************总分上线**************************/
            $examinationid=I('post.examinationid');
            $arr=$this->getTotalOnline($examinationid);
            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order=true;
            }else{
                $order=false;
            }
            $page=I('post.page');
            if(!$page){
                $page=1;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $limit=I('post.limit');
            if(!$limit){
                $limit=2;
            }
            foreach($arr['lineList'] as $k=>$v){
                $arrn['lineName']=$v['lineName'].'率';
                $arrn['lineIndex']=$v['lineIndex'].'proportion';
                $aarr[]=$v;
                $aarr[]=$arrn;
            }
            if(!$aarr){
                $aarr=array();
                $arr['score']=false;
            }else{
                $arr['score']=true;
            }
            $arr['lineList']=$aarr;
            $arr1=$this->sortArrByOneField($arr['data'],$field,$order,$find,$page,$limit);
            if(!$arr1['data']){
                $arr1['data']=array();
            }
            $arr['data']=$arr1['data'];
            $arr['pageclass']=$arr1['pageclass'];
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='totalscoreexport'){/********************总分上线导出*******************/
            $examinationid=I('get.examinationid');
            $arr=$this->getTotalOnline($examinationid);
            $header=array('科类','班级','考生数');
            foreach($arr['lineList'] as $k=>$v){
                $header[]=$v['lineName'];
                $header[]=$v['lineName'].'率';
            }
            $header[]='班主任/年级主任';
            $this->getExport($header,$arr['data']);
        }
        if(I('get.type')=='singlescore'){/****************************单科上线***************************/
            $examinationid=I('post.examinationid');
            $arr=$this->getSingleLine($examinationid);
            if(!$arr['lineList']){
                $arr['lineList']=array();
            }
            if(!$arr['score']){
                $arr['score']=false;
            }
            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order=true;
            }else{
                $order=false;
            }
            $page=I('post.page');
            if(!$page){
                $page=1;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $limit=I('post.limit');
            if(!$limit){
                $limit=2;
            }
            foreach($arr['lineList'] as $k=>$v){
                $arrn['lineName']='总分'.$v['lineName'];
                $arrn['lineIndex']=$v['lineIndex'].'total';
                $aarr[]=$arrn;
                $arrn['lineName']='单科'.$v['lineName'];
                $arrn['lineIndex']=$v['lineIndex'].'single';
                $aarr[]=$arrn;
                $arrn['lineName']=$v['lineName'].'双上线';
                $arrn['lineIndex']=$v['lineIndex'].'double';
                $aarr[]=$arrn;
                $arrn['lineName']=$v['lineName'].'率';
                $arrn['lineIndex']=$v['lineIndex'].'rate';
                $aarr[]=$arrn;
            }
            if(!$aarr){
                $aarr=array();
            }
            $arr['lineList']=$aarr;
            $arr1=$this->sortArrByOneField($arr['data'],$field,$order,$find,$page,$limit);
            if(!$arr1['data']){
                $arr1['data']=array();
            }
            $arr['data']=$arr1['data'];
            $arr['pageclass']=$arr1['pageclass'];
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='singlescoreexport'){/****************************单科上线导出***************************/
            $examinationid=I('get.examinationid');
            $arr=$this->getSingleLine($examinationid);
            $header=array('科类','科目','班级','考生数');
            foreach($arr['lineList'] as $k=>$v){
                $header[]='总分'.$v['lineName'];
                $header[]='单科'.$v['lineName'];
                $header[]=$v['lineName'].'双上线';
                $header[]=$v['lineName'].'率';
            }
            $this->getExport($header,$arr['data']);
        }
        if(I('get.type')=='onlineContrast'){/***************上线对比****************/
            $examinationid1=I('post.examinationid1');
            $examinationid2=I('post.examinationid2');
            $arr1=$this->getSingleLine($examinationid1);
            $arr2=$this->getSingleLine($examinationid2);
            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order=true;
            }else{
                $order=false;
            }
            $page=I('post.page');
            if(!$page){
                $page=1;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $limit=I('post.limit');
            if(!$limit){
                $limit=2;
            }
            foreach($arr2['data'] as $k=>$v){
                $arrd[$v['branch']][$v['subject']][$v['class']]=$v;
            }
            $arra=$this->getTotalOnline($examinationid1);
            $arrb=$this->getTotalOnline($examinationid2);
            foreach($arrb['data'] as $k=>$v){
                $arrc[$v['branch']][$v['className']]=$v;
            }
            foreach($arr2['lineList'] as $k=>$v){
                $arr3[$v['lineName']]=$v;
            }
            foreach($arr1['lineList'] as $k=>$v){
                if($arr3[$v['lineName']]){
                    $arr4['lineName']=$v['lineName'];
                    $arr4['lineIndex1']=$v['lineIndex'];
                    $arr4['lineIndex2']=$arr3[$v['lineName']]['lineIndex'];
                    $arr5[]=$arr4;
                }
            }
            if(!$arr5){
                $aaak['score']=false;
                $this->ajaxReturn($aaak);
                exit;
            }else{
                $score=true;
            }
            foreach($arr5 as $k=>$v){
                $line1['lineName']=$v['lineName'].'本次';
                $line1['lineIndex']=$v['lineIndex1'].'now';
                $lineList[]=$line1;
                $line1['lineName']=$v['lineName'].'上次';
                $line1['lineIndex']=$v['lineIndex1'].'contrast';
                $lineList[]=$line1;
                $line1['lineName']=$v['lineName'].'进退';
                $line1['lineIndex']=$v['lineIndex1'].'difference';
                $lineList[]=$line1;
                $line1['lineName']=$v['lineName'].'率本次';
                $line1['lineIndex']=$v['lineIndex1'].'now_proportion';
                $lineList[]=$line1;
                $line1['lineName']=$v['lineName'].'率上次';
                $line1['lineIndex']=$v['lineIndex1'].'contrast_proportion';
                $lineList[]=$line1;
                $line1['lineName']=$v['lineName'].'率进退';
                $line1['lineIndex']=$v['lineIndex1'].'proportion_difference';
                $lineList[]=$line1;
            }
            foreach($arra['data'] as $a=>$b){
                $arr6['branch']=$b['branch'];
                $arr6['subject']='总分';
                $arr6['className']=$b['className'];
                $arr6['join']=$b['join'];
                foreach($arr5 as $k=>$v){
                    $arr6[$v['lineIndex1'].'now']=$b[$v['lineIndex1']];
                    $arr6[$v['lineIndex1'].'contrast']=$arrc[$b['branch']][$b['className']][$v['lineIndex1']];
                    $arr6[$v['lineIndex1'].'difference']=$b[$v['lineIndex1']]-$arrc[$b['branch']][$b['className']][$v['lineIndex1']];
                    $arr6[$v['lineIndex1'].'now_proportion']=$b[$v['lineIndex1'].'proportion'];
                    $arr6[$v['lineIndex1'].'contrast_proportion']=$arrc[$b['branch']][$b['className']][$v['lineIndex1'].'proportion'];
                    $arr6[$v['lineIndex1'].'proportion_difference']=$b[$v['lineIndex1'].'proportion']-$arrc[$b['branch']][$b['className']][$v['lineIndex1'].'proportion'];//修改，进退=本次-上次?
                }
                $arr7[]=$arr6;
            }
            foreach($arr1['data'] as $a=>$b){
                $arr6['branch']=$b['branch'];
                $arr6['subject']=$b['subject'];
                $arr6['className']=$b['class'];
                $arr6['join']=$b['join'];
                foreach($arr5 as $k=>$v){
                    $arr6[$v['lineIndex1'].'now']=$b[$v['lineIndex2'].'single'];
                    $arr6[$v['lineIndex1'].'contrast']=$arrd[$b['branch']][$b['subject']][$b['class']][$v['lineIndex2'].'single'];
                    $arr6[$v['lineIndex1'].'difference']=$b[$v['lineIndex1'].'single']-$arrd[$b['branch']][$b['subject']][$b['class']][$v['lineIndex2'].'single'];
                    $arr6[$v['lineIndex1'].'now_proportion']=$b[$v['lineIndex2'].'rate'];
                    $arr6[$v['lineIndex1'].'contrast_proportion']=$arrd[$b['branch']][$b['subject']][$b['class']][$v['lineIndex2'].'rate'];
                    $arr6[$v['lineIndex1'].'proportion_difference']=$b[$v['lineIndex1'].'rate']-$arrd[$b['branch']][$b['subject']][$b['class']][$v['lineIndex2'].'rate'];//修改，?进退=本次-上次
                }
                $arr7[]=$arr6;
            }
            $arr8=$this->sortArrByOneField($arr7,$field,$order,$find,$page,$limit);
            $arr8['lineList']=$lineList;
            $arr8['score']=$score;
            $this->ajaxReturn($arr8);
        }elseif(I('get.type')=='onlineContrastExport'){
            $examinationid1=I('get.examinationid1');
            $examinationid2=I('get.examinationid2');
            $arr1=$this->getSingleLine($examinationid1);
            $arr2=$this->getSingleLine($examinationid2);
            foreach($arr2['data'] as $k=>$v){
                $arrd[$v['branch']][$v['subject']][$v['class']]=$v;
            }
            $arra=$this->getTotalOnline($examinationid1);
            $arrb=$this->getTotalOnline($examinationid2);
            foreach($arrb['data'] as $k=>$v){
                $arrc[$v['branch']][$v['className']]=$v;
            }
            foreach($arr2['lineList'] as $k=>$v){
                $arr3[$v['lineName']]=$v;
            }
            foreach($arr1['lineList'] as $k=>$v){
                if($arr3[$v['lineName']]){
                    $arr4['lineName']=$v['lineName'];
                    $arr4['lineIndex1']=$v['lineIndex'];
                    $arr4['lineIndex2']=$arr3[$v['lineName']]['lineIndex'];
                    $arr5[]=$arr4;
                }
            }
            foreach($arr5 as $k=>$v){
                $line1['lineName']=$v['lineName'].'本次';
                $line1['lineIndex']=$v['lineIndex1'].'now';
                $lineList[]=$line1;
                $line1['lineName']=$v['lineName'].'上次';
                $line1['lineIndex']=$v['lineIndex1'].'contrast';
                $lineList[]=$line1;
                $line1['lineName']=$v['lineName'].'进退';
                $line1['lineIndex']=$v['lineIndex1'].'difference';
                $lineList[]=$line1;
                $line1['lineName']=$v['lineName'].'率本次';
                $line1['lineIndex']=$v['lineIndex1'].'now_proportion';
                $lineList[]=$line1;
                $line1['lineName']=$v['lineName'].'率上次';
                $line1['lineIndex']=$v['lineIndex1'].'contrast_proportion';
                $lineList[]=$line1;
                $line1['lineName']=$v['lineName'].'率进退';
                $line1['lineIndex']=$v['lineIndex1'].'proportion_difference';
                $lineList[]=$line1;
            }
            foreach($arra['data'] as $a=>$b){
                $arr6['branch']=$b['branch'];
                $arr6['subject']='总分';
                $arr6['className']=$b['className'];
                $arr6['join']=$b['join'];
                foreach($arr5 as $k=>$v){
                    $arr6[$v['lineIndex1'].'now']=$b[$v['lineIndex1']];
                    $arr6[$v['lineIndex1'].'contrast']=$arrc[$b['branch']][$b['className']][$v['lineIndex1']];
                    $arr6[$v['lineIndex1'].'difference']=$arrc[$b['branch']][$b['className']][$v['lineIndex1']]-$b[$v['lineIndex1']];
                    $arr6[$v['lineIndex1'].'now_proportion']=$b[$v['lineIndex1']];
                    $arr6[$v['lineIndex1'].'contrast_proportion']=$arrc[$b['branch']][$b['className']][$v['lineIndex1']];
                    $arr6[$v['lineIndex1'].'proportion_difference']=$arrc[$b['branch']][$b['className']][$v['lineIndex1']]-$b[$v['lineIndex1']];
                }
                $arr7[]=$arr6;
            }
            foreach($arr1['data'] as $a=>$b){
                $arr6['branch']=$b['branch'];
                $arr6['subject']=$b['subject'];
                $arr6['className']=$b['class'];
                $arr6['join']=$b['join'];
                foreach($arr5 as $k=>$v){
                    $arr6[$v['lineIndex1'].'now']=$b[$v['lineIndex2'].'single'];
                    $arr6[$v['lineIndex1'].'contrast']=$arrd[$b['branch']][$b['subject']][$b['class']][$v['lineIndex2'].'single'];
                    $arr6[$v['lineIndex1'].'difference']=$arrd[$b['branch']][$b['subject']][$b['class']][$v['lineIndex2'].'single']-$b[$v['lineIndex1'].'single'];
                    $arr6[$v['lineIndex1'].'now_proportion']=$b[$v['lineIndex2'].'rate'];
                    $arr6[$v['lineIndex1'].'contrast_proportion']=$arrd[$b['branch']][$b['subject']][$b['class']][$v['lineIndex2'].'rate'];
                    $arr6[$v['lineIndex1'].'proportion_difference']=$arrd[$b['branch']][$b['subject']][$b['class']][$v['lineIndex2'].'rate']-$b[$v['lineIndex1'].'rate'];
                }
                $arr7[]=$arr6;
            }
            $header=array('科类','科目','班级','考生数');
            foreach($lineList as $k=>$v){
                $header[]=$v['lineName'];
            }
            $this->getExport($header,$arr7);
        }
    }
    public function personal(){/**********************学生个人成绩*************************/
        $getSession = $this->get_session('loginCheck',false);
        $id=$this->getUserid();
        $scId=$getSession['scId'];
        if(I('get.type')=='user'){/***********************学生信息*********************/
            $dao=M();
            $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
            $sql1="select u.`grade`,u.`className`,u.`name`,e.`examination`,e.`examinationid`,u.class as classid,c.branch as branchid
            from mks_user as u,mks_examination as e,`mks_examination_results` as r,mks_class as c
            where u.id=".$id." and u.class=c.classid and e.release=1 and r.`userid`=u.`id` and r.`examinationid`=e.`examinationid` and u.scId=".$scId." group by r.`examinationid`";
            $f1=$dao->query($sql1);
            foreach($f1 as $k=>$v){
                $arr['grade']=$arrgrade[$v['grade']-1];
                $arr['className']=$v['className'];
                $arr['name']=$v['name'];
                $arr1['examination']=$v['examination'];
                $arr1['examinationid']=$v['examinationid'];
                $arr['examinationlist'][]=$arr1;
                $arr['branchid']=$v['branchid'];
                $arr['classid']=$v['classid'];
            }
            if(!$f1){
                $arr=array();
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='scorequery'){/******************成绩查询*********************/
            $userid=$id;
            $examinationid=I('post.examinationid');
            $branchid=I('post.branchid');
            $classid=I('post.classid');
            $dao=M('examination');
            $sql1="SELECT branch,classId FROM `mks_examination_student` WHERE examinationid=".$examinationid." AND userid=".$userid." and scId=".$scId;
            $f1=$dao->query($sql1);
            $branchid=$f1['0']['branch'];
            $classid=$f1['0']['classId'];
            $array=$this->getPersonal($examinationid,$userid,$branchid,$classid);
            $where['scId']=$scId;
            $where['examinationid']=$examinationid;
            $f=$dao->where($where)->field('ranking')->select();
            if($f['0']['ranking']==0){
                $array['gradeRanking']=null;
                $array['classRanking']=null;
            }
            $sqla="SELECT COUNT(*) as num FROM `mks_examination_student` WHERE branch=".$branchid." AND scId=".$scId." AND examinationid=".$examinationid;
            $fa=$dao->query($sqla);
            $sqlb="SELECT COUNT(*) as num FROM `mks_examination_student` WHERE classId=".$classid." AND scId=".$scId." AND examinationid=".$examinationid;
            $fb=$dao->query($sqlb);
            $array['gradeAll']=$fa[0]['num'];
            $array['classAll']=$fb[0]['num'];
            $this->ajaxReturn($array);
        }elseif(I('get.type')=='contrast'){/***********************成绩对比************************/
            $examinationid=I('post.examinationid');
            $userid=$id;
            $branchid=I('post.branchid');
            $classid=I('post.classid');
            $dao=M('examination');
            //$classid=implode(',',$classid1);
            $arrpa=0;
            $arrpb=0;
            foreach($examinationid as $k=>$v){
                $sql1="SELECT s.branch,s.classId,e.examination FROM `mks_examination_student` as s,mks_examination as e WHERE e.examinationid=s.examinationid and s.examinationid=".$v." AND s.userid=".$userid." and s.scId=".$scId;
                $f1=$dao->query($sql1);
                $branchid=$f1['0']['branch'];
                $classid=$f1['0']['classId'];
                $sqla="SELECT COUNT(*) as num FROM `mks_examination_student` WHERE branch=".$branchid." AND scId=".$scId." AND examinationid=".$v;
                $fa=$dao->query($sqla);
                $sqlb="SELECT COUNT(*) as num FROM `mks_examination_student` WHERE classId=".$classid." AND scId=".$scId." AND examinationid=".$v;
                $fb=$dao->query($sqlb);
                $arr=$this->comparison($userid,$branchid,$classid,$v);
                $arrz['examination']=$f1['0']['examination'];
                $arrn['score']=$arr['classscore'];
                $arrn['gradeRanking']=$arr['gradeRanking']['total'];
                $arrn['classRanking']=$arr['classRanking']['total'];
                $arrz['value']=$arrn;
                $arr1[]=$arrz;
                foreach($arr['subjectlist'] as $a=>$b){
                    $arr2[]=$b;
                }
                if($arrpa<$fa[0]['num']){
                    $arrpa=$fa[0]['num'];
                }
                if($arrpb<$fb[0]['num']){
                    $arrpb=$fb[0]['num'];
                }

            }
            $arr3=array();
            $i=0;
            foreach($arr2 as $k=>$v){
                if(!in_array($v,$arr3)){
                    $arr3[$i]=$v;
                    $i++;
                }
            }
            $arrp['subjectlist']=$arr3;
            $arrp['data']=$arr1;
            $arrp['gradeAll']=$arrpa;
            $arrp['classAll']=$arrpb;
            $this->ajaxReturn($arrp);
        }
    }
}