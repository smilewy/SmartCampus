<?php
namespace Home\Controller;
use Think\Controller;
ob_end_clean();
class ExaminationController extends Base {
    private function sortArrByOneField($array, $field, $desc = false){
        $fieldArr = array();
        foreach ($array as $k => $v) {
            $fieldArr[$k] = $v[$field];
        }
        $sort = $desc == false ? SORT_ASC : SORT_DESC;
        array_multisort($fieldArr, $sort, $array);
        return $array;
    }
    private function sortArrByOneFieldAll($array, $field, $desc = false,$find=false,$page,$limit,$desc2=false,$field2=false){
        if($field){
            $fieldArr = array();
            foreach($array as $k => $v){
                $fieldArr[$k] = $v[$field];
            }
            foreach($array as $k => $v){
                $fieldArr2[$k] = $v[$field2];
            }
            $sort = $desc == false ? SORT_ASC : SORT_DESC;
            $sort2 = $desc2 == false ? SORT_ASC : SORT_DESC;
            if(!$field2){
                array_multisort($fieldArr, $sort, $array);
            }else{
                array_multisort($fieldArr, $sort,$fieldArr2, $sort2, $array);
            }
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
    private function kaoshichongming($gradeid=null,$examination,$examinationid=0){
        $dao=M();
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(!$gradeid){
            $sqla="SELECT gradeid FROM `mks_examination` WHERE examinationid='".$examinationid."' AND scId=".$scId;
            $fa=$dao->query($sqla);
            $gradeid=$fa['0']['gradeid'];
        }
        $sql="SELECT count(*) as num FROM `mks_examination` WHERE gradeid=".$gradeid." AND examination='".$examination."' AND scId=".$scId." AND examinationid<>".$examinationid;
        $f=$dao->query($sql);
        if($f['0']['num']){
            return false;
        }else{
            return true;
        }
    }
    private function getExamName($examinationid){
        $getSession = $this->get_session('loginCheck',false);
        $dao=M('examination');
        $where['examinationid']=$examinationid;
        $where['scId']=$getSession['scId'];
        $f=$dao->where($where)->field('examination')->select();
        return $f['0']['examination'];
    }
    private function getArrange($examinationid,$f3=null){
        $getSession = $this->get_session('loginCheck',false);
        $dao=M();
        $scId=$getSession['scId'];
        $weekday = array('星期日','星期一','星期二','星期三','星期四','星期五','星期六');
        $sql1="SELECT b.`branch`,s.`date`,s.`id` AS ensubjectid,t.`subjectname`,s.`starttime`,s.`endtime`,b.branchid FROM `mks_examination_subject` AS s,mks_class_branch AS b,mks_subject AS t WHERE s.`examinationid`=".$examinationid." and s.scId=".$scId." AND s.`subject`=t.`subjectid` AND s.`branchid`=b.`branchid` order by s.`date`,s.`starttime`";
        $f1=$dao->query($sql1);
        foreach($f1 as $k=>$v){
            $data1[$v['branchid']]['branch']=$v['branch'];
            $data1[$v['branchid']]['datelist'][$v['date']]['date']=$v['date']."(".$weekday[date('w',strtotime($v['date']))].")";
            $data1[$v['branchid']]['datelist'][$v['date']]['subjectlist'][$v['ensubjectid']]['subjectname']=$v['subjectname'].'('.$v['starttime']."-".$v['endtime'].')';
            //$data1[$v['branchid']]['datelist'][$v['date']]['subjectlist'][$v['ensubjectid']]['starttime']=$v['starttime'];
            //$data1[$v['branchid']]['datelist'][$v['date']]['subjectlist'][$v['ensubjectid']]['endtime']=$v['endtime'];
            $data1[$v['branchid']]['datelist'][$v['date']]['subjectlist'][$v['ensubjectid']]['ensubjectid']=$v['ensubjectid'];
        }
        foreach($data1 as $k=>$v){
            foreach($v['datelist'] as $a=>$b){
                $data1[$k]['datelist'][$a]['subjectlist']=array_values($b['subjectlist']);
            }
            $data1[$k]['datelist']=array_values($data1[$k]['datelist']);
        }
        $data1=array_values($data1);
        $sql2="SELECT r.`id` AS roomid,r.room,b.`branch` FROM `mks_examination_room` AS r,mks_class_branch AS b WHERE r.`branch`=b.`branchid` AND r.`examinationid`=".$examinationid." and r.scId=".$scId." order by r.branch";
        $f2=$dao->query($sql2);
        foreach($f2 as $k=>$v){
            $data2[$v['branch']]['branch']=$v['branch'];
            $data2[$v['branch']]['roomlist'][$v['roomid']]['roomid']=$v['roomid'];
            $data2[$v['branch']]['roomlist'][$v['roomid']]['room']=$v['room'];
        }
        foreach($data2 as $k=>$v){
            $arr1['roomid']="巡考";
            $arr1['room']="巡考";
            $arr2['roomid']="总巡考";
            $arr2['room']="总巡考";
            $data2[$k]['roomlist']=array_values($data2[$k]['roomlist']);
            $data2[$k]['roomlist'][]=$arr1;
            $data2[$k]['roomlist'][]=$arr2;
        }
        $sql4="SELECT b.`branch`,s.id AS ensubjectid FROM `mks_examination_subject` AS s,mks_class_branch AS b WHERE s.`examinationid`=".$examinationid." and s.scId=".$scId." AND s.`branchid`=b.`branchid`";
        $f4=$dao->query($sql4);
        foreach($f4 as $k=>$v){
            $data5[$v['branch']][]=$v['ensubjectid'];
        }
        foreach($data2 as $k=>$v){
            foreach($v['roomlist'] as $c=>$d){
                foreach($data5[$k] as $a=>$b){
                    $data2[$k]['roomlist'][$c][$b]=array();
                }
            }
        }
        $data2=array_values($data2);
        if(!$f3){
            $sql3="SELECT u.`name`,s.`ensubjectid`,s.`room` as roomid,u.id as userid FROM `mks_examination_supervision` AS s,mks_user AS u WHERE s.`userid`=u.`id` AND s.`examinationid`=".$examinationid." and s.scId=".$scId;
            $f3=$dao->query($sql3);
        }
        foreach($f3 as $k=>$v){
            $data3[$v['roomid']][$v['ensubjectid']][]=$v;
        }
        foreach($data3 as $k=>$v){
            foreach($v as $a=>$b){
                $j=count($b);
                if($j<2){
                    $daaa['name']=null;
                    $daaa['ensubjectid']=$a;
                    $daaa['roomid']=$k;
                    $daaa['userid']=null;
                    if($j==1){
                        $data3[$k][$a][]=$daaa;
                    }elseif($j==0){
                        $data3[$k][$a][]=$daaa;
                        $data3[$k][$a][]=$daaa;
                    }
                }
            }
        }

        foreach($data2 as $k=>$v){
            foreach($v['roomlist'] as $a=>$b){
                foreach($b as $u=>$i){
                    if($u!='room'&&$u!='roomid'){
                        $daaa['name']=null;
                        $daaa['ensubjectid']=$u;
                        $daaa['roomid']=$b['roomid'];
                        $daaa['userid']=null;
                        if(isset($data3[$b['roomid']][$u])){
                            foreach($data3[$b['roomid']] as $e=>$f){
                                if(isset($data2[$k]['roomlist'][$a][$e])){
                                    $data2[$k]['roomlist'][$a][$e]=$f;
                                    //unset($data3[$b['roomid']][$e]);
                                }
                            }
                        }else{
                            $data2[$k]['roomlist'][$a][$u][]=$daaa;
                            $data2[$k]['roomlist'][$a][$u][]=$daaa;
                        }
                    }
                }
            }
        }
        $arraa['title']=$data1;
        $arraa['data']=$data2;
        $arraa['return']=true;
        return $arraa;
    }
    /******************************************创建考试*******************************************************/
    public function excreat(){
        /**********************************可选考试科目及年级及科类列表*************************************/
        $getSession = $this->get_session('loginCheck',false);
        if(I('get.type')=='examcreat'){
            if(I('get.typename')=='grade'){/***************年级**************/
                $where['scId']=$getSession['scId'];
                $userId=$getSession['userId'];
                $roleId=$getSession['roleId'];
                if($roleId==$this::$teacherRoleId){
                    $where['userId']=$userId;
                }
                $dao=M();
                $f1=$dao->table('mks_grade')->field('gradeid,name')->where($where)->order('name asc')->select();
                if(!$f1){
                    $f1=array();
                }
                $this->ajaxReturn($f1);
            }elseif(I('get.typename')=='template'){/**************模板****************/
                $dao=M();
                $scId=$getSession['scId'];
                $gradeid=I('post.gradeid');
                $sql="SELECT e.`examination`,e.examinationid FROM `mks_examination` AS e WHERE e.release=1 and e.gradeid=".$gradeid." AND e.`scId`=".$scId;
                $data1=$dao->query($sql);
                $this->ajaxReturn($data1);
            }elseif(I('get.typename')=='subject'){/**************科目****************/
                $dao=M();
                $scId=$getSession['scId'];
                $where['scId']=$scId;
                $examinationid=I('post.examinationid');
                $gradeid=I('post.gradeid');
                $f1=$dao->table('mks_subject')->field('subjectid,subjectname,fullcredit')->where($where)->select();
                $sql2="SELECT b.`branch`,b.`branchid` FROM mks_class AS c,mks_class_branch AS b WHERE c.`grade`=".$gradeid." AND c.scId=".$scId." and c.`branch`=b.`branchid` GROUP BY branchid";
                $f2=$dao->query($sql2);
                foreach($f1 as $k=>$v){
                    $f1[$k]['state']=false;
                    $f1[$k]['proportion']=$v['fullcredit'];
                }
                foreach($f2 as $k=>$v){
                    $arr2['branch']=$v['branch'];
                    $arr2['branchid']=$v['branchid'];
                    $arr2['subjectlist']=$f1;
                    $data[]=$arr2;
                }
                if($examinationid){
                    $sql3="SELECT branchid,subject FROM `mks_examination_subject` WHERE examinationid=".$examinationid." and scId=".$scId;
                    $f3=$dao->query($sql3);

                    foreach($f3 as $k=>$v){
                        $arr3[$v['branchid']][]=$v['subject'];
                    }
                    foreach($data as $k=>$v){
                        foreach($v['subjectlist'] as $a=>$b){
                            foreach($arr3[$v['branchid']] as $c=>$d){
                                    if($b['subjectid']==$d){
                                        $data[$k]['subjectlist'][$a]['state']=true;
                                    }
                            }
                        }
                    }
                }
                if(!$data){
                    $data=array();
                }
                $this->ajaxReturn($data);
                /*$sql="SELECT e.`examination`,e.examinationid FROM `mks_examination` AS e WHERE e.gradeid=".$gradeid." AND e.`scId`=".$scId;
                $data1=$dao->query($sql);
                if(!$data1){
                    $data1=array();
                }
                $this->ajaxReturn($data1);*/
            }elseif(I('get.typename')=='suexselect'){/***********************选择科目默认总分***************************/
                $subjectid=I('post.subjectid');
                $dao=M('subject');
                $where['subjectid'] = array('in',$subjectid);
                $where['scId'] = $getSession['scId'];
                $data=$dao->where($where)->select();
                if($data){
                    foreach($data as $k=>$v){
                        $data1[$k]['subjectid']=$v['subjectid'];
                        $data1[$k]['subjectname']=$v['subjectname'];
                        $data1[$k]['fullcredit']=$v['fullcredit'];
                    }
                }else{
                    $data1=$data;
                }
                $this->ajaxReturn($data1);
            }elseif(I('get.typename')=='clexselect'){/***********************对应年级可选班级列表***************************/
                $dao=M();
                $branchid=implode(',',I('post.branchid'));
                $grade=I('post.gradeid');
                $scId=$getSession['scId'];
                //$where['scId']=I('get.scId');
                $sql="SELECT c.`classid`,c.`classname`,b.`branch` FROM `mks_class` AS c,`mks_class_branch` AS b WHERE c.branch in(".$branchid.") and c.`branch`=b.`branchid` AND c.grade=".$grade." and c.scId=".$scId." order by c.classname";
                //$sql="SELECT c.`classid`,c.`classname`,b.`branch` FROM `mks_class` AS c,`mks_class_branch` AS b WHERE c.`branch`=b.`branchid` AND c.grade=".$grade." and c.scId=".$scId;
                $f3=$dao->query($sql);
                if($f3){
                    foreach($f3 as $k=>$v){
                        $data2[$v['branch']][$k]['classid']=$v['classid'];
                        $data2[$v['branch']][$k]['classname']=$v['classname'];
                    }
                    foreach($data2 as $k=>$v){
                        $data3['branch']=$k;
                        $data3['classlist']=array_values($v);
                        $data4[]=$data3;
                    }
                }else{
                    $data4=$f3;
                }
                $this->ajaxReturn($data4);
            }elseif(I('get.typename')=='exinsert'){
                /*****************************保存创建考试****************************************/
                $dao=M();
                $scId=$getSession['scId'];
                M()->startTrans();
                $data['examinationid']=0;
                $data['gradeid']=I('post.gradeid');
                $data['examination']=I('post.examination');
                $data['starttime']=I('post.starttime');
                $data['endtime']=I('post.endtime');
                $data['release']=0;
                $data['class']=I('post.class');
                $data['scId']=$scId;
                $data['createTime']=time();
                $data['lastRecordTime']=time();
                $data['headmaster']=I('post.headmaster');
                $sqlz="SELECT c.classid,c.branch FROM mks_class AS c,mks_user AS u WHERE c.`classid` IN(".$data['class'].") AND c.`grade`=".$data['gradeid']." AND c.`classid`=u.`class` AND c.`scId`=".$scId." GROUP BY c.`classid`";
                $fz=$dao->query($sqlz);
                $aatra=$this->kaoshichongming($data['gradeid'],$data['examination']);
                if(!$aatra){
                    $arr['return']=false;
                    $arr['msg']='该年级存在相同的考试名!';
                    $this->ajaxReturn($arr);
                }
                if(!$fz['0']['classid']){
                    $arr['return']=false;
                    $arr['msg']='所选班级下没有学生!';
                    $this->ajaxReturn($arr);
                }
                foreach($fz as $k=>$v){
                    $class[]=$v['classid'];
                    $branch[]=$v['branch'];
                }
                $data['class']=implode(',',$class);
                $f1=$dao->table('mks_examination')->data($data)->add();
                $class=explode(',',$data['class']);
                $data1=I('post.data');//////////////////////////////
                $i=0;
                foreach($data1 as $k=>$v){
                    if(!in_array($v['branchid'],$branch)){
                        unset($data1[$k]);
                        continue;
                    }
                    foreach($v['subjectlist'] as $a=>$b){
                        $data2[$i]['examinationid']=$f1;
                        $data2[$i]['lastRecordTime']=time();
                        $data2[$i]['createTime']=time();
                        $data2[$i]['results']=$b['proportion'];/***********************修改，总成绩存的是计入总分分数********************/
                        $data2[$i]['proportion']=$b['fullcredit'];/***************************修改，计入总分分数存的是总成绩（总成绩和计入总分分数交换位置）*****************************/
                        $data2[$i]['subject']=$b['subjectid'];
                        $data2[$i]['branchid']=$v['branchid'];
                        $data2[$i]['scId']=$scId;
                        $i++;
                    }
                }
                $f2=$dao->table('mks_examination_subject')->addAll($data2);

                $where['class'] = array('in',$class);
                $where['state']=1;
                $where['scId']=$scId;
                $where['roleId']=parent::$studentRoleId;
                //$f3=$dao->table('mks_user')->where($where)->select();
                $sql2="SELECT u.`id`,c.`branch`,u.gradeId,u.class FROM mks_user AS u,mks_class AS c,mks_school_rollinfo as r WHERE r.userId=u.id AND r.isAtSchool='是' AND u.gradeId=".$data['gradeid']." AND u.class IN(".$data['class'].") AND u.scId=".$data['scId']." AND u.roleId=".$where['roleId']." AND c.classid=u.class";
                $f3=$dao->query($sql2);
                if(!$f3['0']['id']){
                    $arr['return']=false;
                    $arr['msg']='所选班级下没有学生!';
                    $this->ajaxReturn($arr);
                }
                foreach($f3 as $k=>$v){
                    $data3[$k]['id']=0;
                    $data3[$k]['userid']=$v['id'];
                    $data3[$k]['gradeId']=$v['gradeId'];
                    $data3[$k]['classId']=$v['class'];
                    $data3[$k]['branch']=$v['branch'];
                    $data3[$k]['examinationid']=$f1;
                    $data3[$k]['lastRecordTime']=time();
                    $data3[$k]['createTime']=time();
                    $data3[$k]['scId']=$scId;
                }
                $f4=$dao->table('mks_examination_student')->addAll($data3);
                $sql5="SELECT c.`classname`,g.`name`,c.`branch` FROM `mks_grade` AS g,mks_class AS c WHERE c.`grade`=g.`gradeid` AND c.`classid` in(".$data['class'].")";
                $f5=$dao->query($sql5);
                $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
                foreach($f5 as $k=>$v){
                    $key=$v['name']-1;
                    $arr7[$k]['room']=$arrgrade[$key]."(".$v['classname'].")";
                    $arr7[$k]['branch']=$v['branch'];
                    $arr7[$k]['examinationid']=$f1;
                    $arr7[$k]['lastRecordTime']=time();
                    $arr7[$k]['createTime']=time();
                    $arr7[$k]['scId']=$scId;
                }
                $f6=$dao->table('mks_examination_room')->addAll($arr7);
                if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false || $f6===false || $fz===false){
                    M()->rollback();
                    $arr['return']=false;
                    $arr['msg']='创建失败!';
                }else{
                    M()->commit();
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }
        }
    }
    /**********************************************考试管理******************************************************/
    public function exmanagement(){
        $getSession = $this->get_session('loginCheck',false);
        if(I('get.type')=='interface'){/******************考试管理界面*********************/
            if(I('get.typename')=='exselect'){/********************************考试列表********************************/
                $dao=M();
                $scId=$getSession['scId'];
                $userId=$getSession['userId'];
                $roleId=$getSession['roleId'];
                if($roleId==$this::$teacherRoleId){
                    $st=" and g.userId=".$userId." order by e.examinationid";
                }else{
                    $st=" order by e.examinationid";
                }
                $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
                $sql="SELECT g.name,g.gradeid,e.examinationid,e.examination,e.release FROM mks_grade AS g,mks_examination AS e WHERE e.gradeid=g.gradeid and e.scId=".$scId.$st;
                $data=$dao->query($sql);
                $where['scId']=$scId;
                $f1=$dao->query("SELECT COUNT(*) as num,examinationid FROM `mks_examination_number` WHERE scid=".$scId." GROUP BY examinationid");
                $f2=$dao->query("SELECT COUNT(*) as num,examinationid FROM `mks_examination_results` WHERE scid=".$scId." GROUP BY examinationid");
                foreach($f1 as $k=>$v){
                    $data1[$v['examinationid']]=$v['num'];
                }
                foreach($f2 as $k=>$v){
                    $data2[$v['examinationid']]=$v['num'];
                }
                if($data){
                    foreach($data as $k=>$v){
                        $arr[$v['name']]['name']=$arrgrade[$v['name']-1];//修改，将年级名进行转换为汉字
                        $arr[$v['name']]['gradeid']=$v['gradeid'];
                        $arr1['examinationid']=$v['examinationid'];
                        $arr1['examination']=$v['examination'];
                        if($data1[$v['examinationid']]){
                            $arr1['number']=true;
                        }else{
                            $arr1['number']=false;
                        }
                        if($data2[$v['examinationid']]){
                            $arr1['import']=true;
                        }else{
                            $arr1['import']=false;
                        }
                        if($data1[$v['examinationid']]){
                            $arr1['number']=true;
                        }else{
                            $arr1['number']=false;
                        }
                        if($v['release']){
                            $arr1['release']=true;
                        }else{
                            $arr1['release']=false;
                        }
                        $arr[$v['name']]['ex'][]=$arr1;
                    }

                    $i=0;
                    foreach($arr as $k=>$v){
                        $arrn[$i]=$v;
                        $i++;
                    }
                }else{
                    $data=null;
                    $arrn=$data;
                }
                if(!$arrn){
                    $arrn=array();
                }
                $this->ajaxReturn($arrn);
            }
        }elseif(I('get.type')=='exsubject'){/***************各科目考试时间*************************/
            if(I('get.typename')=='exsfind'){/***************考试科目查询*****************/
                $dao=M();
                $dao1=M('subject');
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                $field=I('post.field');
                $order=I("post.order");
                if($order=='descending'){
                    $order='desc';
                }else{
                    $order='asc';
                }
                $sqla="SELECT starttime,endtime FROM `mks_examination` WHERE examinationid=".$examinationid." and scId=".$scId;
                $fa=$dao->query($sqla);
                /*$limit=I("post.limit");
                $page=I('post.page');
                if(!$page){
                    $page=1;
                }
                if(!$limit){
                    $limit=2;
                }
                $limitstart=($page-1)*$limit;*/
                if(!$field){
                    $field='id';
                }
                if($field=='branch'){
                    $field='b.'.$field;
                }else{
                    $field='e.'.$field;
                }
                //$sql="SELECT e.id,e.subject,e.date,e.starttime,e.endtime,b.`branch` FROM `mks_examination_subject` AS e,`mks_class_branch` AS b WHERE e.`branchid`=b.`branchid` AND e.examinationid=".$examinationid." order by b.branch";
                $sql="SELECT e.id,e.subject,e.date,e.starttime,e.endtime,b.`branch` FROM `mks_examination_subject` AS e,`mks_class_branch` AS b WHERE e.`branchid`=b.`branchid` AND e.scId=".$scId." AND e.examinationid=".$examinationid." order by ".$field." ".$order;
                $data=$dao->query($sql);
                //$sql2="SELECT COUNT(e.`id`) FROM `mks_examination_subject` AS e,`mks_class_branch` AS b WHERE e.`branchid`=b.`branchid` AND e.examinationid=".$examinationid;
                //$data2=$dao->query($sql2);
                //$limitall=$data2['0']['COUNT(e.`id`)'];
                //$pageall=ceil($limitall/$limit);
                if($data){
                    foreach($data as $k=>$v){
                        $data[$k]['branch']=$v['branch'];
                        if(is_numeric($v['subject'])){
                            $where['subjectid']=$v['subject'];
                            $subject=$dao1->where($where)->select();
                            $data[$k]['subject']=$subject['0']['subjectname'];
                            $data[$k]['subjectid']=$v['subject'];
                        }
                        $data[$k]['subject']=$data[$k]['subject'];
                    }
                }

                if(empty($data)){
                    $arr=null;
                }
                if($data===false){
                    $arr=false;
                }else{
                    foreach($data as $k=>$v){
                        $date1=$v['date'].' '.$v['starttime'];
                        $date2=$v['date'].' '.$v['endtime'];
                        $data[$k]['duration']=(strtotime($date2)-strtotime($date1))/60;
                    }
                    //$arr=$data;
                    $arr['data']=$data;//修改，将值放入data中，和考试的开始时间结束时间分开，使用时将前面的$arr删除
                }
                $arr['starttime']=strtotime($fa['0']['starttime'])*1000;//修改，返回考试的开始时间的时间戳
                $arr['endtime']=strtotime($fa['0']['endtime'])*1000;//修改，返回考试的结束时间的时间戳

                /*$arr['page']['pageall']=$pageall;
                $arr['page']['page']=$page;
                $arr['page']['count']=$limitall;*/
                $this->ajaxReturn($arr);
            }elseif(I('get.typename')=='exsupdate'){/********************************修改各科目考试时间*****************************/
                $dao=M('examination_subject');
                $scId=$getSession['scId'];
                //$data=json_decode(urldecode($_GET['data']),true);
                //foreach($data as $k=>$v){
                /*$m['return']=true;
                $data1['id']=$v['id'];
                $data2['date']=$v['date'];
                $data2['starttime']=$v['starttime'];
                $data2['endtime']=$v['endtime'];
                $data2['lastRecordTime']=time();*/
                $sqla="select date,starttime,endtime,subject,id,branchid from mks_examination_subject WHERE examinationid=".I('post.examinationid')." and scId=".$scId;
                $fa=$dao->query($sqla);
                foreach($fa as $k=>$v){
                    $dataa[$v['branchid']][$v['id']]['starttime']=strtotime($v['date'].' '.$v['starttime']);
                    $dataa[$v['branchid']][$v['id']]['endtime']=strtotime($v['date'].' '.$v['endtime']);
                    $datab[$v['branchid']][$v['subject']]['starttime']=strtotime($v['date'].' '.$v['starttime']);
                    $datab[$v['branchid']][$v['subject']]['endtime']=strtotime($v['date'].' '.$v['endtime']);
                    $branch[$v['branchid']][]=$v['id'];
                    $brancha[$v['branchid']][]=$v['subject'];
                }
                $strtime=strtotime(I('post.date').' '.I('post.starttime'));
                $endtime=strtotime(I('post.date').' '.I('post.endtime'));
                if(I('post.id')){
                    $data1['id']=I('post.id');
                    foreach($branch as $k=>$v){
                        if(in_array($data1['id'],$v)){
                            unset($dataa[$k][$data1['id']]);
                            foreach($dataa[$k] as $a=>$b){
                                if(($strtime>=$b['starttime']&&$strtime<=$b['endtime']) || ($endtime>=$b['starttime']&&$endtime<=$b['endtime']) || ($strtime<=$b['starttime']&&$endtime>=$b['endtime'])){
                                    $m['return']=false;
                                    $m['message']='同一科类的科目考试时间不能重叠';
                                    $this->ajaxReturn($m);
                                }
                            }
                        }
                    }
                }elseif(I('post.subjectid') && I('post.examinationid')&&!I('post.id')){
                    $data1['subject'] =I('post.subjectid');
                    $data1['examinationid'] =I('post.examinationid');
                    foreach($datab as $k=>$v){
                        if(in_array($data1['subject'],$brancha[$k])){
                            foreach($v as $a=>$b){
                                if($data1['subject']!=$a){
                                    if(($strtime>=$b['starttime']&&$strtime<=$b['endtime']) || ($endtime>=$b['starttime']&&$endtime<=$b['endtime']) || ($strtime<=$b['starttime']&&$endtime>=$b['endtime'])){
                                        $m['return']=false;
                                        $m['message']='同一科类的科目考试时间不能重叠';
                                        $this->ajaxReturn($m);
                                    }
                                }
                            }
                        }
                    }
                }
                $data1['scId']=$scId;
                $data2['date']=I('post.date');
                $data2['starttime']=I('post.starttime');
                $data2['endtime']=I('post.endtime');
                $data2['lastRecordTime']=time();
                $f=$dao->where($data1)->save($data2);
                if($f===false){
                    $m['return']=false;
                    $m['message']='保存失败';
                }else{
                    $m['return']=true;
                }
                //}
                $this->ajaxReturn($m);
            }elseif(I('get.typename')=='exsexport') {
                /***************考试科目考试时间导出*****************/
                $dao = M();
                $dao1 = M('subject');
                $examinationid = I('get.examinationid');
                $scId=$getSession['scId'];
                $sql = "SELECT e.subject,e.date,e.starttime,e.endtime,b.`branch` FROM `mks_examination_subject` AS e,`mks_class_branch` AS b WHERE e.`branchid`=b.`branchid` AND e.examinationid=" . $examinationid . " and e.scId=".$scId." order by b.branch";
                $data = $dao->query($sql);
                if ($data) {
                    foreach ($data as $k => $v) {
                        $data[$k]['branch'] = $v['branch'];
                        if (is_numeric($v['subject'])) {
                            $where['subjectid'] = $v['subject'];
                            $subject = $dao1->where($where)->select();
                            $data[$k]['subject'] = $subject['0']['subjectname'];
                        }
                        $data[$k]['subject'] = $data[$k]['subject'];
                    }
                }
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
                $objPHPExcel->getActiveSheet()->setTitle('CDN');
                $objPHPExcel->getActiveSheet()->SetCellValue('A1', '科类');
                $objPHPExcel->getActiveSheet()->SetCellValue('B1', '科目');
                $objPHPExcel->getActiveSheet()->SetCellValue('C1', '考试日期');
                $objPHPExcel->getActiveSheet()->SetCellValue('D1', '开考时间');
                $objPHPExcel->getActiveSheet()->SetCellValue('E1', '结束时间');
                $objPHPExcel->getActiveSheet()->SetCellValue('F1', '考试时长（分钟）');
                $i = 2;
                foreach ($data as $k => $v) {
                    $num = $i++;
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $num, $v['branch']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $num, $v['subject']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $num, $v['date']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $num, $v['starttime']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $num, $v['endtime']);
                    $data1 = $v['date'] . " " . $v['starttime'];
                    $data2 = $v['date'] . " " . $v['endtime'];
                    $time1 = strtotime($data1);
                    $time2 = strtotime($data2);
                    $time = ($time2 - $time1) / 60;
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $num, $time);
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

                //print_r($data);
            }
        }elseif(I('get.type')=='confirm'){/***************学生参考确认*****************/
            if(I('get.typename')=='attendselect'){/************************学生参考确认情况查询********************************/
                $dao=M();
                $examinationid=I('post.examinationid');
                //$dao1=M('subject');
                $scId=$getSession['scId'];
                $screen=I('post.screen');
                if($screen){
                    $st="where a.branch like '%".$screen."%' or a.className like '%".$screen."%' or a.all like '%".$screen."%' or a.reported like '%".$screen."%' or a.unreported like '%".$screen."%' or a.participate like '%".$screen."%' or a.unparticipate like '%".$screen."%'";
                }

                $field=I('post.field');
                $order=I("post.order");
                if($order=='descending'){
                    $order='desc';
                }else{
                    $order='asc';
                }
                $limit=I("post.limit");
                $page=I('post.page');
                if(!$page){
                    $page=1;
                }
                if(!$field){
                    $field='branch,className';
                }
                if(!$limit){
                    $limit=2;
                }
                $limitstart=($page-1)*$limit;
                //$sql="select u.className,u.class,u.`name`,e.`reported`,e.`participate` FROM `mks_examination_student` AS e,`mks_user` AS u WHERE e.`userid`=u.`id` AND u.scId='".$scId."' AND e.`examinationid`='".$examinationid."' AND u.`roleId`=15 AND u.statu='是'";
                $sql="select * from (SELECT b.branch,u.className,COUNT(u.`name`) AS `all`,u.class,COUNT(e.`reported`='是' OR NULL) AS reported,COUNT(e.`reported`='否' OR NULL) AS unreported,COUNT(e.`participate`='是' OR NULL) AS participate,COUNT(e.`participate`='否' OR NULL) AS unparticipate,COUNT(e.`headmaster`='是' OR NULL) as confirm FROM `mks_examination_student` AS e,`mks_user` AS u,mks_class_branch as b WHERE e.`userid`=u.`id` and e.scId=".$scId." and b.branchid=e.branch AND e.`examinationid`='".$examinationid."' GROUP BY u.`class`) as a $st order by a.".$field." ".$order." limit ".$limitstart.",".$limit;//查询相关数据并分页排序
                $sql1="select count(*) from (SELECT b.branch FROM `mks_examination_student` AS e,`mks_user` AS u,mks_class_branch as b WHERE e.`userid`=u.`id` and e.scId=".$scId." and b.branchid=e.branch AND e.`examinationid`='".$examinationid."' GROUP BY u.`class`) as a $st";//总班级数
                //SELECT * FROM (SELECT u.className,u.class,COUNT(e.`reported`='是' OR NULL) AS reported,COUNT(e.`reported`='否' OR NULL) AS unreported,COUNT(e.`participate`='是' OR NULL) AS participate,COUNT(e.`participate`='否' OR NULL) AS unparticipate FROM `mks_examination_student` AS e,`mks_user` AS u WHERE e.`userid`=u.`id` AND e.`examinationid`='123' GROUP BY u.`class`) AS a WHERE a.reported=1//做模糊查询时用的sql

                $f=$dao->query($sql);
                $f1=$dao->query($sql1);
                /*$data=array();
                foreach($f as $k=>$v){
                    if(!$data[$v['className']]['all']){
                        $data[$v['className']]['all']=0;
                    }
                    if(!$data[$v['className']]['participate']){
                        $data[$v['className']]['participate']=0;
                    }
                    if(!$data[$v['className']]['unparticipate']){
                        $data[$v['className']]['unparticipate']=0;
                    }
                    if(!$data[$v['className']]['reported']){
                        $data[$v['className']]['reported']=0;
                    }
                    if(!$data[$v['className']]['unreported']){
                        $data[$v['className']]['unreported']=0;
                    }
                    if($v['participate']=='是'){
                        $data[$v['className']]['participate']++;
                    }else{
                        $data[$v['className']]['unparticipate']++;
                    }
                    if($v['reported']=='是'){
                        $data[$v['className']]['reported']++;
                    }else{
                        $data[$v['className']]['unreported']++;
                    }
                    $data[$v['className']]['all']++;
                    $data[$v['className']]['class']=$v['class'];
                }*/
                //$al=explode(',',$f1['0']['class']);
                $i=0;
                if(empty($f)){
                    $data3['data']=null;
                }
                $sqll="select class from mks_examination where scId=".$scId." and examinationid=".$examinationid;
                $ff=$dao->query($sqll);
                $sqlq="select u.name,c.classid from mks_class as c,mks_user as u where c.classid IN(".$ff['0']['class'].") and c.userid=u.id";
                $fq=$dao->query($sqlq);
                foreach($fq as $k=>$v){
                    $aaa[$v['classid']]=$v['name'];
                }
                foreach($f as $k=>$v){
                    $data1[$i]=$v;
                    unset($data1[$i]['class']);
                    //$data1[$i]['className']=$k;
                    $data1[$i]['teacher']=$aaa[$v['class']];
                    $data1[$i]['confirm'];//修改，班主任是否确认，只要班主任修改了本班某个人的参考状态，就算班主任确认了，使用时删掉unset和下面的注释
                    if($v['confirm']){
                        $data1[$i]['confirm']='是';
                    }else{
                        $data1[$i]['confirm']='否';
                    }
                    $data3['data'][$i]=$data1[$i];
                    $i++;
                }
                if(!$data3['data']){
                    $data3['data']=array();
                }
                $data3['page']['page']=$page;
                $data3['page']['pageall']=ceil($f1['0']['count(*)']/$limit);
                $data3['page']['count']=$f1['0']['count(*)'];
                /*if($order=='ascending' || !$order){
                    $order=false;
                }else{
                    $order=true;
                }
                $data2=$this->sortArrByOneField($data1,$field,$order);
                $li1=$limitstart;
                $li2=$limitstart+$limit;
                for($i=$li1;$i<$li2;$i++){
                    if($data2[$i]){
                        $data3[]=$data2[$i];
                    }
                }
                foreach($data3 as $k=>$v){
                    $data3[$k]['teacher']=urlencode($v['teacher']);
                }
                $pageall=ceil(count($data2)/$limit);
                $data3['page']['page']=$page;
                $data3['page']['$pageall']=$pageall;*/
                $this->ajaxReturn($data3);
            }elseif(I('get.typename')=='exclass'){/*******************************学生参考确认班级查询（修改时查询）**********************************************/
                $dao1=M('examination');
                $examinationid['examinationid']=I('post.examinationid');
                $examinationid['scId']=$getSession['scId'];
                $data=$dao1->where($examinationid)->select();
                $class=explode(',',$data['0']['class']);
                $where['classid'] = array('in',$class);
                $where['scId'] =$getSession['scId'];
                $dao2=M('class');
                $data2=$dao2->where($where)->field('classid,classname')->select();
                foreach($data2 as $k=>$v){
                    $data2[$k]['classname']=$v['classname'];
                }
                $this->ajaxReturn($data2);
            }elseif(I('get.typename')=='attend'){/*******************************学生参考确认查询（修改时查询）**********************************************/
                $dao=M();
                $scId=$getSession['scId'];//$getSession['scId'];
                $classid=I('post.classid');
                $examinationid=I('post.examinationid');
                $screen=I('post.screen');
                $field=I('post.field');
                $order=I("post.order");
                if($order=='descending'){
                    $order='desc';
                }else{
                    $order='asc';
                }
                $limit=I("post.limit");
                $page=I('post.page');
                if(!$page){
                    $page=1;
                }
                if(!$limit){
                    $limit=2;
                }
                $limitstart=($page-1)*$limit;
                if(!$field){
                    $field='u.id';
                }else{
                    if($field=='participate' || $field=='reported'){
                        $field='s.'.$field;
                    }else{
                        $field='u.'.$field;
                    }
                }
                if($screen){
                    $st="and (u.className like '%".$screen."%' or u.serialNumber like '%".$screen."%' or u.sex like '%".$screen."%' or u.name like '%".$screen."%' or s.`participate` like '%".$screen."%' or s.`reported` like '%".$screen."%')";
                }
                $roleId=parent::$studentRoleId;
                $sql="select s.id,u.`className`,u.serialNumber,u.`sex`,u.`name`,s.`participate`,s.`reported` from `mks_examination_student` as s,mks_user as u where s.scId=".$scId." and u.`class` in(".$classid.") and u.`roleId`=".$roleId." and s.userid=u.`id` and s.examinationid=".$examinationid." $st order by ".$field." ".$order." limit ".$limitstart.",".$limit;
                $sql2="select count(s.id) from `mks_examination_student` as s,mks_user as u where s.scId=".$scId." and u.`class` in(".$classid.") and u.`roleId`=".$roleId." and s.userid=u.`id` and s.examinationid=".$examinationid." $st";
                //exit;
                $data=$dao->query($sql);
                $data2=$dao->query($sql2);
                if(!$data){

                }
                $pageall=ceil($data2['0']['count(s.id)']/$limit);
                foreach($data as $k=>$v){
                    $data[$k]['id']=$v['id'];
                    $data[$k]['className']=$v['className'];
                    $data[$k]['serialNumber']=$v['serialNumber'];
                    $data[$k]['sex']=$v['sex'];
                    $data[$k]['name']=$v['name'];
                    $data[$k]['participate']=$v['participate'];
                    $data[$k]['reported']=$v['reported'];
                    $data1['data'][$k]=$data[$k];
                }
                if($data){
                    $data1['page']['page']=$page;
                    $data1['page']['pageall']=$pageall;
                    $data1['page']['count']=$data2['0']['count(s.id)'];
                }elseif(empty($data)){
                    $data1['data']=null;
                }

                $this->ajaxReturn($data1);
            }elseif(I('get.typename')=='attendupdate'){/********************学生参考确认（修改)****************************************/
                $dao=M('examination_student');
                $data=I('post.data');
                $scId=$getSession['scId'];
                //$where['examinationid']=I('get.examinationid');
                foreach($data as $k=>$v){
                    $where['id']=$v['id'];
                    $where['scId']=$scId;
                    $data1['reported']=$v['reported'];
                    $data1['participate']=$v['participate'];
                    $arr[$v['reported']][$v['participate']]['id'][]=$v['id'];
                    //$f=$dao->where($where)->save($data1);
                }
                foreach($arr as $k=>$v){
                    $sql1="UPDATE mks_examination_student SET reported='".$k."'";
                    foreach($v as $a=>$b){
                        $id=implode(',',$b['id']);
                        $sql2=",participate='".$a."' where id in(".$id.") and scId=".$scId;
                        $sql=$sql1.$sql2;
                        $f=$dao->query($sql);
                        $sql='';
                    }
                }
                if($f===false){
                    $arrcd['return']=false;
                }else{
                    $arrcd['return']=true;
                }
                $this->ajaxReturn($arrcd);
            }elseif(I('get.typename')=='participateupdate') {
                /********************学生参考确认（全部参考，全部不参考，全部上报，全部不上报)****************************************/
                $field = I('post.field');
                $where['examinationid'] = I('post.examinationid');
                $where['scId']=$getSession['scId'];
                $dao = M('examination_student');
                if ($field == 'reported') {
                    $data['reported'] = '是';
                } elseif ($field == 'unreported') {
                    $data['reported'] = '否';
                } elseif ($field == 'participate') {
                    $data['participate'] = '是';
                } elseif ($field == 'unparticipate') {
                    $data['participate'] = '否';
                }
                $f = $dao->where($where)->save($data);
                if ($f === false) {
                    $arr['return'] = false;
                } else {
                    $arr['return'] = true;
                }
                $this->ajaxReturn($arr);
            }
        }elseif(I('get.type')=='examroom'){/*******************************考场分配*******************************/
            if(I('get.typename')=='roomselect'){/*******************************考场列表*******************************/
                $where['examinationid']=I('post.examinationid');

                $scId['scId']=$getSession['scId'];
                $where['scId']=$scId['scId'];
                $dao=M('examination_room');

                $field=I('post.field');
                $order=I("post.order");
                if($order=='descending'){
                    $order='desc';
                }else{
                    $order='asc';
                }
                $limit=I("post.limit");
                $page=I('post.page');
                if(!$page){
                    $page=1;
                }
                if(!$limit){
                    $limit=1;
                }
                $limitstart=($page-1)*$limit;
                if(!$field){
                    $field='id';
                }
                $order="".$field." ".$order."";
                $f=$dao->where($where)->field('id,branch,room,seats,columns,status,sort')->order($order)->page($page,$limit)->select();
                $f2=$dao->where($where)->count();
                $pageall=ceil($f2/$limit);
                $daon=M('class_branch');
                $sqlh="SELECT b.`branch`,b.`branchid` FROM `mks_examination_subject` AS s,mks_class_branch AS b WHERE s.`examinationid`=".$where['examinationid']." and s.scid=".$scId['scId']." AND s.`branchid`=b.`branchid` GROUP BY s.`branchid`";
                $fn=$daon->query($sqlh);
                //$arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','七年级','八年级','九年级','十年级','十一年级','十二年级');
                foreach($fn as $k=>$v){
                    $fn[$k]['branch']=$v['branch'];
                }
                $dao2=M('examination_student');
                //$where['participate']=1;
                $sql="SELECT COUNT(s.branch) as num,b.branch FROM `mks_examination_student` AS s,`mks_class_branch` AS b WHERE s.examinationid='".$where['examinationid']."' AND s.participate='是' AND s.`branch`=b.`branchid` GROUP BY s.branch";
                $arra=$dao2->query($sql);
                $sql2="SELECT COUNT(s.room) as num,b.branch,SUM(s.`seats`) as numb FROM `mks_examination_room` AS s,`mks_class_branch` AS b WHERE s.examinationid='".$where['examinationid']."' AND s.`branch`=b.`branchid` GROUP BY s.branch";
                $arrb=$dao2->query($sql2);
                $all=0;
                foreach($arra as $k=>$v){
                    $all+=$v['num'];
                    $arrmc['date'][$k]['branch']=$v['branch'];
                    $arrmc['date'][$k]['num']=$v['num'];
                }
                $key=count($arra);
                $arra[$key]['branch']='all';
                $arrmc['all']=$all;
                $allc=0;
                $alld=0;
                foreach($arrb as $k=>$v){
                    $allc+=$v['num'];
                    $alld+=$v['numb'];
                    $arrb[$k]['branch']=$v['branch'];
                }
                foreach($arrb as $k=>$v){
                    $arrcb['data'][$k]['branch']=$v['branch'];
                    $arrcb['data'][$k]['num']=$v['num'];
                    $arrdv['data'][$k]['branch']=$v['branch'];
                    $arrdv['data'][$k]['num']=$v['numb'];
                }
                $key=count($arrc);
                $arrc[$key]['branch']='all';
                $arrcb['all']=$allc;

                $key=count($arrd);
                $arrd[$key]['branch']='all';
                $arrdv['all']=$alld;

                //$dao1=M('');
                //$sql1="SELECT class FROM `mks_examination` WHERE `examinationid`=".$where['examinationid'];
                //$f1=$dao1->query($sql1);
                /*if(!$f){
                    $sql2="SELECT c.`classname`,g.`name`,c.`branch` FROM `mks_grade` AS g,mks_class AS c WHERE c.`grade`=g.`gradeid` AND c.`classid` in(".$f1['0']['class'].")";
                    $f2=$dao1->query($sql2);
                        foreach($f2 as $k=>$v){
                            $key=$v['name']-1;
                            $arr2['roomlist'][$k]['room']=urlencode($arrgrade[$key]."(".$v['classname'].")");
                            //$arr2['roomlist'][$k]['seats']='';
                            $arr2['roomlist'][$k]['columns']='';
                            $arr2['roomlist'][$k]['status']=0;
                            $arr2['roomlist'][$k]['seat']='';
                            $arr2['roomlist'][$k]['branch']=urlencode($v['branch']);
                        }
                        $arr2['branchlist']=$fn;
                        $arr2['attend']=$arra;
                    //$arr2['situation']['students']=$f3;
                    echo urldecode(json_encode($arr2));
                }else{*/
                foreach($f as $k=>$v){
                    foreach($fn as $y=>$z){
                        if($v['branch']==$z['branchid']){
                            $v['branch']=urldecode($z['branch']);
                        }
                    }
                    foreach($v as $m=>$n){
                        $arrm['roomlist'][$k][$m]=$n;
                    }
                }
                if(!$arrm['roomlist']){
                    $arrm['roomlist']=array();
                }
                $arrm['branchlist']=$fn;
                $arrm['attend']=$arrmc;
                $arrm['room']=$arrcb;
                $arrm['seats']=$arrdv;
                $arrm['page']['page']=$page;
                $arrm['page']['pageall']=$pageall;
                $arrm['page']['count']=$f2;
                //$arrm['situation']['students']=$f3;
                $this->ajaxReturn($arrm);
                //}
            }elseif(I('get.typename')=='exroom'){/************************************可用的考场模板****************************************/
                $dao=M('examination_room');
                $gradeid=I('post.gradeid');
                $sql="SELECT e.examinationid,e.`examination` FROM `mks_examination_room` AS r,`mks_examination` AS e WHERE e.`examinationid`=r.`examinationid` AND e.`gradeid`=".$gradeid." GROUP BY e.examinationid";
                $data=$dao->query($sql);
                if(empty($data)){
                    $data=null;
                }
                $this->ajaxReturn($data);
            }elseif(I('get.typename')=='roomupin'){/********************************修改添加考场信息*************************************/
                $dao=M('examination_room');
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                //$data=json_decode(urldecode($_GET['data']),true);
                $f3=true;
                //foreach($data as $k=>$v){
                //$data[$k]['examinationid']=$examinationid;
                if(I('post.id')){
                    $id['id']=I('post.id');
                    $id['scId']=$scId;
                    $datam['branch']=I('post.branchid');
                    $datam['room']=I('post.room');
                    $datam['seats']=I('post.seats');
                    $datam['status']=0;
                    $datam['columns']=I('post.columns');
                    $datam['lastRecordTime']=time();
                    $f2=$dao->where($id)->save($datam);
                    if($f2===false){
                        $f3=false;
                    }
                }else{
                    $datam['branch']=I('post.branchid');
                    $datam['scId']=$scId;
                    $datam['room']=I('post.room');
                    $datam['seats']=I('post.seats');
                    $datam['columns']=I('post.columns');
                    $datam['lastRecordTime']=time();
                    $datam['createTime']=time();
                    $datam['examinationid']=$examinationid;
                    $f1=$dao->data($datam)->add();
                }
                //}
                if($f1===false){
                    $f3=false;
                }
                $arr['return']=$f3;
                $this->ajaxReturn($arr);
            }elseif(I('get.typename')=='roomseatfind'){/***********************当前考场座位布局**********************/
                $id['roomid']=I('post.id');
                $id['scId']=$getSession['scId'];
                $dao=M('examination_seat');
                $f=$dao->where($id)->field('seatRow,seatCol,seatNumber')->select();
                $this->ajaxReturn($f);
            }elseif(I('get.typename')=='roomseatup'){/****************************保存当前考场设置的座位布局****************/
                M()->startTrans();
                $id=I('post.id');
                $sort=I('post.sort');
                $roomid=I('post.roomid');
                $where['roomid']=array('in',$id);
                $scId=$getSession['scId'];
                $where1['id']=array('in',$id);
                $where1['scId']=$scId;
                $where['examinationid']=I('post.examinationid');
                $where['scId']=$scId;
                $where2['id']=$roomid;
                //$seat2=json_decode($_POST['seat']);
                $seat=I('post.seat');
                foreach($id as $a=>$b){
                    foreach($seat as $k=>$v){
                        $seat[$k]['examinationid']=I('post.examinationid');
                        $seat[$k]['roomid']=$b;
                        $seat[$k]['scId']=$scId;
                        $seatall[]=$seat[$k];
                    }
                }
                $dao1=M('examination_room');
                $f5=$dao1->field('seats,columns')->where($where2)->select();
                $dao=M('examination_seat');
                $f1=$dao->where($where)->delete();
                $f2=$dao->addAll($seatall);

                $seat1['lastRecordTime']=time();
                $seat1['status']=1;
                $seat1['seats']=$f5['0']['seats'];
                $seat1['columns']=$f5['0']['columns'];
                $seat1['sort']=$sort;
                $f=$dao1->where($where1)->data($seat1)->save();
                if($f1===false || $f===false || $f2===false || $f5===false){
                    $arrj['return']=false;
                    M()->rollback();
                }else{
                    M()->commit();
                    $arrj['return']=true;
                }
                $this->ajaxReturn($arrj);
            }elseif(I('get.typename')=='seatroom'){/************************座位布局中可选的考场***************/
                $dao=M('examination_room');
                $scId=$getSession['scId'];
                $where['examinationid']=I('post.examinationid');
                $where['scId']=$scId;
                $f=$dao->where($where)->field('room,id')->select();
                foreach($f as $k=>$v){
                    $f[$k]['room']=$v['room'];
                }
                $this->ajaxReturn($f);
            }elseif(I('get.typename')=='roomseatupall'){/***********************************同步并修改考场座位布局//已整合到保存当前考场设置的座位布局中***********************************/
                M()->startTrans();
                $dao=M('examination_room');
                $scId=$getSession['scId'];
                $id['id']=I('post.id');
                $id['scId']=$scId;
                $idall=I('post.idall');
                $f1=$dao->field('seats,columns')->where($id)->select();
                $f1['0']['status']=1;
                $f1['0']['lastRecordTime']=time();
                $ida=explode(',',$idall);
                $dao3=M('examination_seat');
                $where3['roomid']=$id['id'];
                $where3['scId']=$scId;
                $f3=$dao3->where($where3)->field('seatRow,seatCol,seatNumber,scId,examinationid')->select();
                foreach($ida as $k=>$v){
                    foreach($f3 as $m=>$n){
                        $n['roomid']=$v;
                        $arr[]=$n;
                    }
                }
                $where6['roomid']=array('in',$ida);
                $where6['scId']=$scId;
                $f5=$dao3->where($where6)->delete();
                $f4=$dao3->addAll($arr);
                $where5['id']=array('in',$ida);
                $where5['scId']=$scId;
                $f2=$dao->where($where5)->save($f1['0']);
                if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false){
                    $arrj['return']=false;
                    M()->rollback();
                }else{
                    M()->commit();
                    $arrj['return']=true;
                }
                echo json_encode($arrj);
            }elseif(I('get.typename')=='roomdel'){/***********************************删除考场***********************************/
                M()->startTrans();
                $scId=$getSession['scId'];
                $dao1=M('examination_room');
                $dao2=M('examination_seat');
                //$id=json_decode($_GET['id'],true);
                $id=explode(',',I('post.id'));
                $where1['id']=array('in',$id);
                $where1['scId']=$scId;
                $where2['roomid']=array('in',$id);
                $where2['scId']=$scId;
                $f1=$dao1->where($where1)->delete();
                $f2=$dao2->where($where2)->delete();
                if($f1===false || $f2===false){
                    M()->rollback();
                    $arr['return']=false;
                }else{
                    M()->commit();
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }
        }elseif(I('get.type')=='exnumber'){/**************考号设置************/
            if(I('get.typename')=='exnumberfind'){/*****************考号查询*********************/
                $gradeid=I('post.gradeid');
                $program=I('post.program');
                $examinationid=I('post.examinationid');
                $screen=I('post.screen');
                $scId=$getSession['scId'];
                $find=I('post.find');
                $where['gradeId']=I('post.gradeid');
                $where1['examinationid']=I('post.examinationid');
                $where1['scId']=$scId;
                $where['scId']=$scId;
                $where['roleId']=parent::$studentRoleId;
                $where['state']=1;
                $limit=I('post.limit');
                if(!$limit){
                    $limit=2;
                }
                $page=I('post.page');
                if(!$page){
                    $page=1;
                }
                if($screen){
                    if($find=="考号调用"){
                        $st=" and u.name like '%".$screen."%'";
                    }else{
                        $st=" and u.name like '%".$screen."%'";
                    }
                }
                $limitstart=($page-1)*$limit;
                $field=I('post.field');
                if(!$field){
                    if($find=="考号调用"){
                        $field='className,u.serialNumber,u.id';
                    }else{
                        $field='u.className,u.serialNumber,u.id';
                    }
                }
                $order=I("post.order");
                if($order=='descending'){
                    $order='desc';
                }else{
                    $order='asc';
                }
                $dao1=M('examination_number');
                $dao2=M('user');
                $dao3=M('examination_student');
                //$sql1="SELECT u.`name`,u.`className`,u.`serialNumber`,u.`id`,n.`number` FROM mks_user AS u,mks_examination_number AS n WHERE u.id=n.`userid` AND u.`roleId`=15 AND n.`program`=".$program." and n.gradeid=".$gradeid;
                //$f1=$dao1->query($sql1);
                if($find=="考号调用"){
                    //$sql2="SELECT u.name,u.className,u.serialNumber,u.id FROM mks_user as u,mks_examination_student as s,mks_school_rollinfo as r where u.id=r.userId and r.isAtSchool='是' AND u.id=s.userid and s.scId=".$scId." and s.participate='是' and s.examinationid=".$examinationid.$st." order by u.".$field." ".$order." limit ".$limitstart.",".$limit;
                    //$sql5="SELECT count(*) as num FROM mks_user as u,mks_examination_student as s,mks_school_rollinfo as r where u.id=r.userId and r.isAtSchool='是' AND u.id=s.userid and s.scId=".$scId." and s.participate='是' and s.examinationid=".$examinationid.$st;
                    $sql2="SELECT u.name,u.className,u.serialNumber,u.id FROM mks_user as u,mks_examination_student as s where u.id=s.userid and s.scId=".$scId." and s.participate='是' and s.examinationid=".$examinationid.$st." order by u.".$field." ".$order." limit ".$limitstart.",".$limit;
                    $sql5="SELECT count(*) as num FROM mks_user as u,mks_examination_student as s where u.id=s.userid and s.scId=".$scId." and s.participate='是' and s.examinationid=".$examinationid.$st;
                }else{
                    //$sql2="SELECT u.name,u.className,u.serialNumber,u.id FROM mks_user as u,mks_school_rollinfo as r where u.id=r.userId and r.isAtSchool='是' and u.gradeId=".$gradeid." and u.scId=".$scId." and u.`roleId`=".$where['roleId'].$st." and u.state=1 order by ".$field." ".$order." limit ".$limitstart.",".$limit;
                    //$sql5="SELECT count(*) as num FROM mks_user as u,mks_school_rollinfo as r where u.id=r.userId and r.isAtSchool='是' and u.gradeId=".$gradeid." and u.scId=".$scId." and u.`roleId`=".$where['roleId'].$st." and u.state=1";
                    $sql2="SELECT u.name,u.className,u.serialNumber,u.id FROM mks_user as u,mks_class as c where u.class=c.classid AND u.gradeId=".$gradeid." and u.scId=".$scId." and u.`roleId`=".$where['roleId'].$st." order by ".$field." ".$order." limit ".$limitstart.",".$limit;
                    $sql5="SELECT count(*) as num FROM mks_user as u,mks_class as c where u.class=c.classid AND u.gradeId=".$gradeid." and u.scId=".$scId." and u.`roleId`=".$where['roleId'].$st;

                }
                $f2=$dao2->query($sql2);
                $f6=$dao2->query($sql5);
                if(empty($f2)){
                    $data['data']=array();
                }
                foreach($f2 as $k=>$v){
                    if($program=="本次考试"){
                        $sql1="select id,number from mks_examination_number where userid=".$v['id']." and scId=".$scId." and program='".$program."' and examinationid=".$examinationid;
                    }else{
                        $sql1="select id,number from mks_examination_number where userid=".$v['id']." and scId=".$scId." and program='".$program."'";
                    }
                    $f1=$dao1->query($sql1);
                    $f2[$k]['name']=$v['name'];
                    $f2[$k]['className']=$v['className'];
                    $f2[$k]['number']=$f1['0']['number'];
                    $f2[$k]['id']=$f1['0']['id'];
                    $f2[$k]['userid']=$v['id'];
                    $data['data'][$k]=$f2[$k];
                }
                if($find=="考号调用"){
                    $where1['participate']='是';
                    $f3=$dao3->where($where1)->count();
                    //$sqlaz="SELECT COUNT(n.`userid`) AS num FROM `mks_examination_number` AS n,mks_user AS u WHERE n.`examinationid`=".$examinationid." AND n.`scId`=".$scId." AND n.`userid`=u.`id` AND u.`state`=1";
                    $sqlaz="SELECT COUNT(n.`userid`) AS num FROM `mks_examination_student` AS n,mks_user AS u WHERE n.`examinationid`=".$examinationid." AND n.`scId`=".$scId." AND n.`userid`=u.`id`";
                    $faz=$dao3->query($sqlaz);
                    $f3=$faz['0']['num'];
                    if($program=='本次考试'){
                        $aaa=' AND s.`examinationid`=n.`examinationid`';
                    }
                    /************修改，有效考号去掉了相同的考号和被删除的学生的考号**************/
                    $sql3="select count(id) as num from (SELECT n.id FROM `mks_examination_student` AS s,`mks_examination_number` AS n,mks_user as u WHERE s.userid=u.id AND s.`participate`='是' AND n.`program`='".$program."'".$aaa." AND s.`examinationid`=".$examinationid." AND s.`userid`=n.`userid` and s.scId=".$scId." AND n.`number`<>'' group by n.`number`) as a";
                    $f5=$dao1->query($sql3);
                    $f4=$f5['0']['num'];
                }else{
                    /************修改，有效考号去掉了相同的考号和被删除的学生的考号**************/
                    $sql3="SELECT COUNT(id) AS num FROM (SELECT n.id FROM `mks_examination_number` AS n,mks_user AS u,mks_class as c WHERE c.classid=u.class AND n.gradeid=".$gradeid." and n.userid=u.id AND n.`program`='".$program."' AND n.scId=".$scId." AND n.`number`<>'' GROUP BY n.`number`) AS a";//有效考号数
                    //$sql3="SELECT COUNT(id) AS num FROM (SELECT n.id FROM `mks_examination_number` AS n,mks_user AS u,`mks_school_rollinfo` AS r WHERE u.state=1 AND u.id=r.userId AND r.`isAtSchool`='是' AND n.userid=u.id AND n.`program`='".$program."' AND n.scId=".$scId." AND n.`number`<>'' GROUP BY n.`number`) AS a";
                    $f5=$dao1->query($sql3);
                    $f4=$f5['0']['num'];
                    //$sqlz="SELECT COUNT(*) as num FROM `mks_user` AS u,`mks_school_rollinfo` AS r WHERE u.scId=".$scId." AND u.roleId=".$where['roleId']." AND u.state=1 AND r.isAtSchool='是' AND u.id=r.userId";
                    $sqlz="SELECT COUNT(*) as num FROM `mks_user` AS u,mks_class as c WHERE u.class=c.classid AND u.scId=".$scId." AND u.roleId=".$where['roleId']." AND u.gradeId=".$gradeid;
                    $fz=$dao2->query($sqlz);
                    $f3=$fz['0']['num'];
                    unset($where1['examinationid']);
                    $where1['program']=$program;
                    $where1['number']=array('neq','');
                    //$f4=$dao1->where($where1)->count();
                }
                $data['page']['page']=$page;
                $data['page']['pageall']=ceil($f6['0']['num']/$limit);
                $data['page']['count']=$f6['0']['num'];
                $data['num']['student']=$f3;
                $data['num']['number']=$f4;
                if($find!="考号调用"){
                    $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
                    $sql="select name from mks_grade where gradeid=".$gradeid." and scId=".$scId;
                    $f=$dao1->query($sql);
                    $data['grade']=$arrgrade[$f['0']['name']-1];
                }
                $this->ajaxReturn($data);
            }elseif(I('get.typename')=='exnumberinsert') {/***************************考号管理生成考号*********************************/
                $prefix = I('post.prefix');
                $rule = I('post.rule');
                $scId=$getSession['scId'];
                $where['scId'] =$scId;
                $where['gradeId'] = I('post.gradeid');
                $where['roleId'] = parent::$studentRoleId;
                $where['state'] = 1;
                $where1['gradeid'] = I('post.gradeid');
                $where1['program'] = I('post.program');
                $program = I('post.program');
                M()->startTrans();
                $dao1 = M('user');
                //$f1 = $dao1->where($where)->field('id,className,serialNumber,number')->select();
                $sql1="SELECT u.`id`,u.`className`,u.`serialNumber`,u.`number` FROM `mks_user` AS u,mks_class AS r WHERE u.`scId` = ".$where['scId']." AND u.`gradeId` = ".$where['gradeId']." AND u.`roleId` = ".$where['roleId']." AND u.class=r.classid order by u.`className`,u.`serialNumber`,u.id";
                $f1=$dao1->query($sql1);
                $dao2 = M('examination_number');
                $f2 = $dao2->where($where1)->delete();
                $pattern = '123456789';
                $key='';
                for($i=0;$i<3;$i++) {
                    $key .= $pattern{mt_rand(0,9)};    //生成php随机数
                }
                if($f2 !== false) {
                    if ($rule == 1) {
                        $i=1;
                        $zz="00000";
                        foreach ($f1 as $k => $v) {
                            $lenth=5-strlen($i);
                            $nu=substr($zz,0,$lenth).$i;
                            $data[$k]['userid'] = $v['id'];
                            $data[$k]['gradeid'] = I('post.gradeid');
                            $data[$k]['program'] = $program;
                            $data[$k]['number'] = $prefix.$nu;
                            $data[$k]['scId'] =$scId;
                            $data[$k]['lastRecordTime'] = time();
                            $data[$k]['createTime'] = time();
                            $i++;
                        }
                    }elseif ($rule == 2) {
                        foreach ($f1 as $k => $v) {
                            $data[$k]['userid'] = $v['id'];
                            $data[$k]['gradeid'] = I('post.gradeid');
                            $data[$k]['program'] = $program;
                            if(!$v['serialNumber']){
                                $v['serialNumber']=$key;
                                $key++;
                            }
                            $data[$k]['number'] = $prefix . $v['className'].$v['serialNumber'];
                            $data[$k]['scId'] =$scId;
                            $data[$k]['lastRecordTime'] = time();
                            $data[$k]['createTime'] = time();
                        }
                    }elseif ($rule == 3) {
                        foreach ($f1 as $k => $v) {
                            if(!$v['serialNumber']){
                                $v['serialNumber']=$key;
                                $key++;
                            }
                            $data[$k]['userid'] = $v['id'];
                            $data[$k]['gradeid'] = I('post.gradeid');
                            $data[$k]['program'] = $program;
                            $data[$k]['number'] = $v['className'] . $v['serialNumber'];
                            $data[$k]['scId'] =$scId;
                            $data[$k]['lastRecordTime'] = time();
                            $data[$k]['createTime'] = time();
                        }
                    }
                    $f3 = $dao2->addAll($data);
                    if($f3===false || $f1===false || $f2===false){
                        M()->rollback();
                        $arrm['return']=false;
                    }else{
                        M()->commit();
                        $arrm['return']=true;
                    }
                    $this->ajaxReturn($arrm);
                }
            }elseif(I('get.typename')=='exnumberupin'){/************************考号管理修改添加考号******************************/
                $where1['id'] = I('post.id');
                $where1['scId'] = $getSession['scId'];
                $data1['number']=I('post.number');
                $data1['lastRecordTime']=time();
                $data2['program'] = I('post.program');
                $data2['gradeid'] = I('post.gradeid');
                $data2['scId'] = $getSession['scId'];
                $data2['number'] = I('post.number');
                $data2['userid'] = I('post.userid');
                $data2['lastRecordTime'] = time();
                $data2['createTime'] = time();
                $dao=M("examination_number");
                if($where1['id']){
                    $f1=$dao->where($where1)->save($data1);
                }else{
                    $f1=$dao->data($data2)->add();
                }
                if($f1===false){
                    $arr['return']=false;
                }else{
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }elseif(I('get.typename')=='exnumberdel'){/******************清空考号******************/
                $where1['gradeid']=I('post.gradeid');
                $where1['program']=I('post.program');
                $dao=M("examination_number");
                $f=$dao->where($where1)->delete();
                if($f===false){
                    $arr['return']=false;
                }else{
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }elseif(I('get.typename')=='exnumberexport'){/****************导出考号模板********************/
                $url=$this::$downUrl;
                $rule=I('get.rule');
                if($rule=='name'){ //修改，加上了班级+班级座号的匹配规则的模板下载，使用时将注释符号去掉
                    header('Location: '.$url.'/Public/upload/exam/numberTemplate.xls');
                }elseif($rule=='serialNumber'){
                    header('Location: '.$url.'/Public/upload/exam/numberTemplateSerialNumber.xls');
                }

            }elseif(I('get.typename')=='export'){/****************导出考号********************/
                //if(){}
                $scId=$getSession['scId'];
                $dao=M();
                $program=I('get.program');
                $examinationid=I('get.examinationid');
                $gradeid=I('get.gradeid');
                if($program=='本次考试'){
                    $program='本次考试考号';
                    $sql1="SELECT u.name,u.className,u.serialNumber,s.number FROM mks_user AS u,mks_examination_number AS s WHERE u.id=s.userid AND s.scId=".$scId." AND s.examinationid=".$examinationid." order by u.className,u.serialNumber";
                }else{
                    $sql1="SELECT u.name,u.className,u.serialNumber,s.number FROM mks_user AS u,mks_examination_number AS s WHERE u.id=s.userid AND s.scId=".$scId." AND s.gradeid=".$gradeid." and program='".$program."' order by u.className,u.serialNumber";
                }

                $data=$dao->query($sql1);

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
                $objPHPExcel->getActiveSheet()->SetCellValue('A1', '班级');
                $objPHPExcel->getActiveSheet()->SetCellValue('B1', '班级座号');
                $objPHPExcel->getActiveSheet()->SetCellValue('C1', '学生姓名');
                $objPHPExcel->getActiveSheet()->SetCellValue('D1', $program);
                $i = 2;
                foreach ($data as $k => $v) {
                    $num = $i++;
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $num, $v['className']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $num, $v['serialNumber']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $num, $v['name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $num, $v['number']);
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




            }elseif(I('get.typename')=='exnumbereimport'){/****************导入学生考号********************/
                if (!empty($_FILES)) {
                    $scId=$getSession['scId'];
                    $upload = new \Think\Upload();
                    $upload->maxSize   =     3145728 ;// 设置附件上传大小
                    $upload->exts      =     array('xlsx', 'xls');// 设置附件上传类型
                    $upload->rootPath  =     './'; // 设置附件上传根目录
                    $upload->savePath  =     'Public/upload/exam/'; // 设置附件上传（子）目录
                    $upload->replace  =     true; // 设置附件是否替换
                    //$upload->savePath  =     'cdr/'; // 设置附件上传（子）目录
                    $upload->saveName = 'numberInformation';
                    $upload->autoSub = false;
                    $info   =   $upload->uploadOne($_FILES['photo']);
                    if(!$info) {// 上传错误提示错误信息
                        $arr['return']=false;
                        return $arr;
                    }
                    $string =$_FILES['photo']['name'];
                    $arraym = explode('.',$string);
                    $exs=$arraym[count($arraym)-1];
                    vendor("PHPExcel.PHPExcel");
                    //$objPHPExcel = new \PHPExcel();
                    if($exs=='xls'){
                        $reader = \PHPExcel_IOFactory::createReader('Excel5'); //设置以Excel5格式(Excel97-2003工作簿)
                        $PHPExcel = $reader->load("./Public/upload/exam/numberInformation.xls"); // 载入excel文件
                    }else{
                        $reader = \PHPExcel_IOFactory::createReader('Excel2007'); //设置以Excel5格式(Excel97-2003工作簿)
                        $PHPExcel = $reader->load("./Public/upload/exam/numberInformation.xlsx"); // 载入excel文件
                    }
                    $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
                    $highestRow = $sheet->getHighestRow(); // 取得总行数
                    $highestColumm = $sheet->getHighestColumn(); // 取得总列数
                    $row = 1;
                    for ($column = 'A'; $column <= $highestColumm; $column++) {//列数是以A列开始
                        $value = (string)$sheet->getCell($column . $row)->getValue();
                        if ($value == '') {
                            continue;
                        }
                        $value=strtr($value,array(' '=>''));
                        $dataset[$column] = $value;//表头字段
                        //echo $column.$row.":".$sheet->getCell($column.$row)->getValue()."<br />";
                    }
                    $dataset1 = array();
                    for ($row = 2; $row <= $highestRow; $row++) {//行数是以第1行开始
                        $dataset1=array();
                        for ($column = 'A'; $column <= $highestColumm; $column++) {//列数是以A列开始
                            $value =(string)$sheet->getCell($column . $row)->getValue();
                            if ($value === '') {
                                continue;
                            }
                            $dataset1[$column] = $value;
                            //echo $column.$row.":".$sheet->getCell($column.$row)->getValue()."<br />";
                        }
                        if (empty($dataset1)) {
                            continue;
                        }
                        $dataset2[] = $dataset1;
                    }
                    $array = array();
                    foreach ($dataset2 as $k => $v) {
                        foreach ($v as $m => $n) {
                            foreach ($dataset as $o => $p) {
                                if($m == $o &&$p!='序号'){
                                    $array[$p] = $n;
                                    break;
                                }
                            }
                        }
                        $array1[]=$array;
                    }

                    if($exs=='xls') {
                        unlink('./Public/upload/exam/numberInformation.xls');
                    }else{
                        unlink('./Public/upload/exam/numberInformation.xlsx');
                    }
                    $rule=I('post.rule');//修改，加上根据班级+班级序号添加考号的功能
                    $validate=I('post.validate');//validate 用来判断当前是否跳过座号重复和考号重复的验证，最开始的时候validate为0，跳过座号重复：validate为1，跳过考号重复：validate为2
                    if($rule=='serialNumber'){
                        $arr['numberList']=array();
                        $arr['seatList']=array();
                        if($validate==0){//验证考号是否有重复
                            foreach($array1 as $k=>$v){//班级座号是否重复
                                if($aarr[$v['班级']][$v['班级座号']]){//验证每个班的座号是否有重复
                                    $abc[$v['班级']][$v['班级座号']]=$v['班级座号'];
                                }
                                $aarr[$v['班级']][$v['班级座号']]=$v['班级座号'];
                            }
                            if(!empty($abc)){
                                $arr['return']='error';
                                $ac='';
                                foreach($abc as $k=>$v){
                                    $ac[]=$k."班座号:".implode(',',$v);
                                }
                                $arr['seatList']=$ac;
                            }
                            $abc=array();
                            $aarr=array();
                            foreach($array1 as $k=>$v){//验证考号是否重复
                                if($aarr[$v['考号']]){
                                    $abc[$v['考号']]=$v['考号'];
                                    unset($array1[$acd[$v['考号']]]);//修改，只保留最后一次相同的考号
                                }
                                $acd[$v['考号']]=$k;
                                $aarr[$v['考号']]=$v['考号'];
                            }
                            if(!empty($abc)){
                                $arr['return']='error';
                                $arr['numberList']=array_values($abc);
                            }
                            $this->ajaxReturn($arr);
                            exit;
                        }
                        $aarr=array();
                        foreach($array1 as $k=>$v){//验证考号是否重复
                            if($aarr[$v['考号']]){
                                unset($array1[$acd[$v['考号']]]);//修改，只保留最后一次相同的考号
                            }
                            $aarr[$v['考号']]=$v['考号'];
                        }
                        $dao1 = M('user');
                        $where['scId'] =$scId;
                        $where['gradeId'] = I('post.gradeid');
                        $where['roleId'] = parent::$studentRoleId;
                        $sql1="SELECT u.`id`,u.`className`,u.`serialNumber`,u.`name` FROM `mks_user` AS u,mks_class AS r WHERE u.`scId` = ".$where['scId']." AND u.`gradeId` = ".$where['gradeId']." AND u.`roleId` = ".$where['roleId']." AND u.`state` = 1 AND u.class=r.classid order by u.`className`,u.`serialNumber`,u.id";
                        $f1=$dao1->query($sql1);
                        foreach($array1 as $k=>$v){
                            $data[$v['班级']][$v['班级座号']]=$v['考号'];
                        }
                        foreach($f1 as $k=>$v){
                            $fn[$k]['number']=$data[$v['className']][$v['serialNumber']];
                            $fn[$k]['userid']=$v['id'];
                            $fn[$k]['program']=I('post.program');
                            $fn[$k]['gradeid']=I('post.gradeid');
                            $fn[$k]['scId']=$scId;
                            $fn[$k]['lastRecordTime']=time();
                            $fn[$k]['createTime']=time();
                            if($v['serialNumber']==''){
                                unset($data[$v['className']][$v['serialNumber']]);
                            }
                        }
                        $where1['scId'] =$scId;
                        $where1['gradeid'] = I('post.gradeid');
                        $where1['program']=I('post.program');
                        $dao2=M('examination_number');
                        $f2=$dao2->where($where1)->delete();
                        if($f2!==false){
                            $f3=$dao2->addAll($fn);
                        }
                        if($f3===false){
                            $arr['return']=false;
                        }else{
                            $arr['return']=true;
                        }
                        $this->ajaxReturn($arr);
                        exit;
                    }/*/

                    //$validate=I('post.validate');//validate 用来判断当前是否跳过重名和考号重复的验证，最开始的时候validate为0，跳过重名：validate为2，跳过考号重复：validate为1
                    //可能会忽略掉重名问题
                    /**********************修改，当班内有学生重名的时候返回状态nameError以及重名学生列表
                    if($validate==0||$validate==1){
                        foreach($array1 as $k=>$v){
                            if($aarr[$v['班级']][$v['姓名']]){
                                $abc[]=$v['姓名'];
                            }
                            $aarr[$v['班级']][$v['姓名']]=$v['姓名'];
                        }
                        if(!empty($abc)){
                            $arr['return']='nameError';
                            $arr['List']=$abc;
                            $this->ajaxReturn($arr);
                            exit;
                        }
                    }/***************************/
                    /*********////修改，当考号重复的时候返回状态numberError以及重复考号列表
                    if($validate==0){
                        $arr['numberList']=array();
                        $arr['seatList']=array();
                        foreach($array1 as $k=>$v){
                            if($aarr[$v['考号']]){
                                $abc[$v['考号']]=$v['考号'];//修改，只保留最后一个相等的考号
                                unset($array1[$acd[$v['考号']]]);
                            }
                            $acd[$v['考号']]=$k;
                            $aarr[$v['考号']]=$v['考号'];
                        }
                        if(!empty($abc)){
                            $arr['return']='error';
                            $arr['numberList']=array_values($abc);
                            $this->ajaxReturn($arr);
                            exit;
                        }
                    }/******************************/
                    $aarr=array();
                    foreach($array1 as $k=>$v){
                        if($aarr[$v['考号']]){
                            unset($array1[$acd[$v['考号']]]);
                        }
                        $acd[$v['考号']]=$k;
                        $aarr[$v['考号']]=$v['考号'];
                    }
                    $dao1 = M('user');
                    $where['scId'] = $scId;
                    $where['gradeId'] = I('post.gradeid');

                    $where['roleId'] = parent::$studentRoleId;
                    $sql1="SELECT u.`id`,u.`className`,u.`serialNumber`,u.`name` FROM `mks_user` AS u,mks_class AS r WHERE u.`scId` = ".$where['scId']." AND u.`gradeId` = ".$where['gradeId']." AND u.`roleId` = ".$where['roleId']." AND u.`state` = 1 AND u.class=r.classid order by u.`className`,u.`serialNumber`,u.id";
                    $f1=$dao1->query($sql1);
                    foreach($array1 as $k=>$v){
                        $data[$v['班级']][$v['姓名']]=$v['考号'];
                    }
                    foreach($f1 as $k=>$v){
                        $fn[$k]['number']=$data[$v['className']][$v['name']];
                        $fn[$k]['userid']=$v['id'];
                        $fn[$k]['program']=I('post.program');
                        $fn[$k]['gradeid']=I('post.gradeid');
                        $fn[$k]['scId']=$scId;
                        $fn[$k]['lastRecordTime']=time();
                        $fn[$k]['createTime']=time();
                    }
                    $where1['scId'] = $scId;
                    $where1['gradeid'] = I('post.gradeid');
                    $where1['program']=I('post.program');
                    $dao2=M('examination_number');
                    $f2=$dao2->where($where1)->delete();
                    if($f2!==false){
                        $f3=$dao2->addAll($fn);
                    }
                    if($f3===false){
                        $arr['return']=false;
                    }else{
                        $arr['return']=true;
                    }
                    $this->ajaxReturn($arr);
                }else{
                    $arr['return']=false;
                    $this->ajaxReturn($arr);
                }
            }elseif(I('get.typename')=='examnumberin'){/*********************调用考号生成本次考试考号*********************/
                $scId=$getSession['scId'];
                $where1['gradeid']=I('post.gradeid');
                $where1['program']=I('post.program');
                $where1['scId']=$scId;
                $where2['examinationid']=I('post.examinationid');
                $where2['scId']=$scId;
                $where3['examinationid']=I('post.examinationid');
                $where3['scId']=$scId;
                $where2['participate']="是";
                $dao1=M('');
                M()->startTrans();
                $f1=$dao1->table('mks_examination_number')->field('userid,number')->where($where1)->select();
                $f2=$dao1->table('mks_examination_student')->field('userid')->where($where2)->select();
                $f3=$dao1->table('mks_examination_number')->where($where3)->delete();
                foreach($f1 as $k=>$v){
                    $arr[$v['userid']]=$v['number'];
                }
                foreach($f2 as $k=>$v){
                    $f2[$k]['number']=$arr[$v['userid']];
                    $f2[$k]['gradeid']=I('post.gradeid');
                    $f2[$k]['program']="本次考试";
                    $f2[$k]['scId']=$getSession['scId'];
                    $f2[$k]['examinationid']=I('post.examinationid');
                    $f2[$k]['lastRecordTime']=time();
                    $f2[$k]['createTime']=time();
                }
                $f4=$dao1->table('mks_examination_number')->addAll($f2);
                if($f1===false || $f2===false || $f3===false || $f4===false){
                    M()->rollback();
                    $a['return']=false;
                }else{
                    M()->commit();
                    $a['return']=true;
                }
                $this->ajaxReturn($a);
            }
        }elseif(I('get.type')=='invigilatortask'){
            /*******************************监考任务****************************/
            if (I('get.typename') == 'examteacher') {
                /********************************监考任务查询*************************************/
                //修改，让最后安排的展示在最前面
                $scId=$getSession['scId'];
                $data['examinationid'] = I('post.examinationid');
                //$data['status'] = 1;
                $data['scId'] = $scId;
                $dao = M('examination_room');
                $f = $dao->where($data)->field('branch,count(*) as num')->group('branch')->select();
                $field=I('post.field');
                $limit=I("post.limit");
                $screen=I('post.screen');
                if($screen){
                    $st="and (invigilator like '%".$screen."%' or visits like '%".$screen."%' or totalinspection like '%".$screen."%' or arrange like '%".$screen."%')";
                }
                if(!$limit){
                    $limit=10;
                }
                $page=I('post.page');
                if(!$page){
                    $page=1;
                }
                $limitstart=($page-1)*$limit;
                if(!$field){
                    $field='id';
                }
                $order=I('post.order');
                if($order=='ascending'){
                    $order='asc';
                }else{
                    $order='desc';
                }
                if ($f) {
                    $arrall['room'] = true;
                } else {
                    $arrall['room'] = false;
                }
                $dao1=M('user');
                $roleId1=parent::$studentRoleId;
                $roleId2=parent::$jZroleId;
                $where1['roleId']=array('neq',$roleId1);
                $where1['roleId']=array('neq',$roleId2);

                $where1['scId']=$scId;
                $f1=$dao1->field('id,name,grade,class,teachingSubjects,roleId')->where($where1)->select();
                $dao2=M('examination_teacher');
                $where2['examinationid']=I('post.examinationid');
                $where2['scId']=$scId;
                //$f2=$dao2->where($where2)->field('id,userid,invigilator,visits,totalinspection,arrange')->order(array($field=>$order))->limit($limitstart,$limit)->select();
                $sql2="select id,userid,invigilator,visits,totalinspection,arrange from mks_examination_teacher where examinationid=".I('post.examinationid')." and scId=".$scId." ".$st." order by ".$field." ".$order." limit ".$limitstart.",".$limit;
                $f2=$dao->query($sql2);
                $sqln="select count(id) as count from mks_examination_teacher where examinationid=".I('post.examinationid')." and scId=".$scId." ".$st." order by ".$field." ".$order;
                $fn=$dao->query($sqln);
                $count=$fn['0']['count'];
                //$count=$dao2->where($where2)->field('id,userid,invigilator,visits,totalinspection,arrange')->count();
                $dao3=M('');
                $sql3="SELECT c.`classid`,b.`branch`,c.userid FROM mks_class AS c,mks_class_branch AS b WHERE c.`branch`=b.`branchid` and c.scId=".$scId;
                $f3=$dao3->query($sql3);
                foreach($f3 as $k=>$v){
                    $data3[$v['classid']]=$v['branch'];
                }
                foreach($f3 as $k=>$v){
                    $data2[$v['userid']]=true;
                }
                foreach($f1 as $k=>$v){
                    $arr1[$k]['branch']=$data3[$v['class']];
                    $arr1[$k]['name']=$v['name'];
                    $arr1[$k]['grade']=$v['grade'];
                    $arr1[$k]['teachingSubjects']=$v['teachingSubjects'];
                    $arr1[$k]['userid']=$v['id'];
                    if($v['roleId']!=parent::$teacherRoleId){
                        $arrj[$v['id']]=true;
                    }else{
                        $arrj[$v['id']]=false;
                    }
                }
                //$arrall['teacher']=$arr1;
                foreach($arr1 as $k=>$v){
                    $data1[$v['userid']]['branch']=$v['branch'];
                    $data1[$v['userid']]['name']=$v['name'];
                    $data1[$v['userid']]['grade']=$v['grade'];
                    $data1[$v['userid']]['teachingSubjects']=$v['teachingSubjects'];
                }
                foreach($f2 as $k=>$v){
                    //$f1=$dao1->field('id,name,grade,class,teachingSubjects,roleId')->where($where1)->select();
                    $f2[$k]['branch']=$data1[$v['userid']]['branch'];
                    $f2[$k]['name']=$data1[$v['userid']]['name'];
                    $f2[$k]['teachingSubjects']=$data1[$v['userid']]['teachingSubjects'];
                    $f2[$k]['headmaster']=$data2[$v['userid']];
                    $f2[$k]['staff']=$arrj[$v['userid']];
                    unset($f2[$k]['userid']);
                }
                $pageall=ceil($count/$limit);
                $arrall['exam']=$f2;
                $dao4=M('examination_subject');
                $sql4="SELECT branchid FROM `mks_examination_subject` WHERE examinationid=".$data['examinationid']." and scId=".$scId." GROUP BY date,starttime,branchid";
                $f4=$dao4->query($sql4);
                $sqlk="SELECT branchid FROM `mks_examination_subject` WHERE examinationid=".$data['examinationid']." and scId=".$scId." GROUP BY date,starttime";
                $fk=$dao4->query($sqlk);
                $n4=count($fk);
                $arrz=array();
                foreach($f4 as $k=>$v){
                    $arrz[$v['branchid']]++;
                }
                foreach($f as $k=>$v){
                    $arrkl[]=$v['num']*$arrz[$v['branch']];
                }
                $arr4['invigilatorall']=array_sum($arrkl)*2;/*****************修改，将应安排总监考数改为时间段*考场*2*****************/
                $sql5="SELECT SUM(invigilator) AS invigilator,SUM(visits) AS visits,SUM(totalinspection) AS totalinspection,SUM(arrange) AS arrangeall,AVG(arrange) AS arrange FROM `mks_examination_teacher` WHERE examinationid=".I('post.examinationid')." GROUP BY examinationid";
                $f5=$dao2->query($sql5);
                $arr4['invigilator']=$f5['0']['invigilator'];
                $arr4['visits']=$f5['0']['visits'];
                $arr4['totalinspection']=$f5['0']['totalinspection'];
                $arr4['arrangeall']=$f5['0']['arrangeall'];
                $arr4['arrange']=round($f5['0']['arrange'],2);
                $arrall['all']=$arr4;
                $arrall['slot']=$n4;//修改，返回时间段数
                $arrall['page']['page']=$page;
                $arrall['page']['pageall']=$pageall;
                $arrall['page']['count']=$count;
                $this->ajaxReturn($arrall);
            }elseif(I('get.typename') == 'teacher'){/****************可选监考教师列表*****************/
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                $dao1=M('user');
                $sql3="SELECT c.`classid`,b.`branch`,c.userid FROM mks_class AS c,mks_class_branch AS b WHERE c.`branch`=b.`branchid` and c.scId=".$scId;
                $f3=$dao1->query($sql3);
                foreach($f3 as $k=>$v){
                    $data3[$v['classid']]=$v['branch'];
                }
                $roleId1=parent::$studentRoleId;
                $roleId2=parent::$jZroleId;
                $roleId3=$this::$adminRoleId;
                $where1['roleId']=array('neq',$roleId1);
                $where1['roleId']=array('neq',$roleId2);
                $where1['scId']=$scId;
                $sql1="select `id`,`name`,`grade`,`class`,`teachingSubjects`,`roleId` FROM `mks_user` WHERE `roleId` <> ".$roleId1." and `roleId` <> ".$roleId2." and roleId<>".$roleId3." AND `scId` =".$scId;
                $f1=$dao1->query($sql1);
                $sql2="select userid from mks_examination_teacher where examinationid=".$examinationid;
                $f2=$dao1->query($sql2);
                foreach($f2 as $k=>$v){
                    $data2[$v['userid']]='userid';
                }
                foreach($f1 as $k=>$v){
                    if($data2[$v['id']]!='userid'){
                        $arr1[$k]['branch']=$data3[$v['class']];
                        $arr1[$k]['name']=$v['name'];
                        $arr1[$k]['grade']=$v['grade'];
                        $arr1[$k]['teachingSubjects']=$v['teachingSubjects'];
                        $arr1[$k]['userid']=$v['id'];
                    }
                }
                $arr1=array_values($arr1);
                $this->ajaxReturn($arr1);
            }elseif(I('get.typename') == 'exteacherinsert'){/**************监考任务添加***************/
                $data['examinationid']=I('post.examinationid');
                $userid=I('post.userid');
                $scId=$getSession['scId'];
                $dao=M('examination_teacher');
                foreach($userid as $k=>$v){
                    $data['userid']=$v;
                    $data['lastRecordTime']=time();
                    $data['createTime']=time();
                    $data['scId']=$scId;
                    $dataa[]=$data;
                }
                $f=$dao->data()->addAll($dataa);
                if($f===false){
                    $arr['return']=false;
                }else{
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }elseif(I('get.typename') == 'exteacherup'){/*****************监考任务修改*******************/
                $where['id']=I('post.id');
                $where['scId']=$getSession['scId'];
                $data['invigilator']=I('post.invigilator');
                $data['visits']=I('post.visits');
                $data['totalinspection']=I('post.totalinspection');
                $data['arrange']=I('post.totalinspection')+I('post.visits')+I('post.invigilator');
                $data['lastRecordTime']=time();
                $dao=M('examination_teacher');
                $f=$dao->where($where)->save($data);
                if($f===false){
                    $arr['return']=false;
                }else{
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }elseif(I('get.typename') == 'exteacherdel'){/*****************监考任务删除*****************/
                $id=I('post.id');
                $where['scId']=$getSession['scId'];
                $dao=M('examination_teacher');
                foreach($id as $k=>$v){
                    $az[]=$v;

                }
                $where['id']=array('in',$id);
                $f=$dao->where($where)->delete();
                if($f===false){
                    $arr['return']=false;
                }else{
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }
        }elseif(I('get.type')=='arrange'){/*************************考生安排查询************************/
            if(I('get.typename')=='arrangefind'){
                $where1['examinationid']=I('post.examinationid');
                $scId=$getSession['scId'];
                $where1['scId']=$scId;
                //$where2['gradeId']=I('post.gradeid');
                $where2['scId']=$scId;
                $dao1=M('examination_arrange');
                $f1=$dao1->where($where1)->select();
                $sqln="SELECT r.`userId`,r.isTempStudy as isResSchool FROM mks_examination_student as s,`mks_school_rollinfo` AS r WHERE s.examinationid=".$where1['examinationid']." and s.userid=r.userId and s.scId=".$scId;
                $fn=$dao1->query($sqln);
                foreach($fn as $k=>$v){
                    $arrn[$v['userId']]=$v['isResSchool'];
                }
                if($f1===null){
                    $data2['data']=array();
                    $data2['page']['page']=1;
                    $data2['page']['pageAll']=0;
                    $data2['page']['count']=0;
                    $data2['examlist']=array();
                    $this->ajaxReturn($data2);
                }else{
                    $dao2=M('');
                    $field=I('post.field');
                    $screen=I('post.screen');
                    $sqle="SELECT r.examinationid as id,e.`examination` FROM `mks_examination_results` AS r,mks_examination AS e WHERE e.scid=".$scId." AND e.`examinationid`=r.`examinationid` GROUP BY r.examinationid";
                    $examlist=$dao2->query($sqle);
                    if(!$screen){
                        $screen=false;
                    }
                    if(!$field){
                        $field="className";
                    }
                    $order=I("post.order");
                    if($order=='descending'){
                        $order=true;
                    }else{
                        $order=false;
                    }
                    $limit=I("post.limit");
                    if(!$limit){
                        $limit=2;
                    }
                    $page=I('post.page');
                    if(!$page){
                        $page=1;
                    }
                    $limitstart=($page-1)*$limit;

                    $examinationid=I('post.examinationid');
                    //$sql2="SELECT u.className,u.serialNumber,u.name,a.roomid,a.seatnumber,b.branch,n.number,u.id FROM `mks_examination_arrange` AS a,mks_user AS u,`mks_class_branch` AS b,`mks_examination_number` AS n WHERE a.userid=u.id AND a.branchid=b.branchid AND a.examinationid=n.examinationid AND a.userid=n.userid AND a.examinationid=".$examinationid." and u.scId=".$scId." $st order by ".$field." ".$order." limit ".$limitstart.",".$limit;
                    //$sql3="SELECT count(*) as num FROM `mks_examination_arrange` AS a,mks_user AS u,`mks_class_branch` AS b,`mks_examination_number` AS n WHERE a.userid=u.id AND a.branchid=b.branchid AND a.examinationid=n.examinationid AND a.userid=n.userid AND a.examinationid=".$examinationid." and u.scId=".$scId." $st";
                    $sql2="SELECT u.id,u.className,u.serialNumber,u.name,a.roomid,a.seatnumber,b.branch,u.id FROM `mks_examination_arrange` AS a,mks_user AS u,`mks_class_branch` AS b WHERE a.userid=u.id AND a.branchid=b.branchid AND a.examinationid=".$examinationid." and u.scId=".$scId. $st;
                    $sql3="SELECT count(*) as num FROM `mks_examination_arrange` AS a,mks_user AS u,`mks_class_branch` AS b WHERE a.userid=u.id AND a.branchid=b.branchid AND a.examinationid=".$examinationid." and u.scId=".$scId." $st";
                    $sqle="SELECT userid,number FROM `mks_examination_number` WHERE scId=".$scId." AND examinationid=".$examinationid;
                    $f2=$dao2->query($sql2);
                    $fe=$dao2->query($sqle);
                    foreach($fe as $k=>$v){
                        $are[$v['userid']]=$v['number'];
                    }
                    foreach($f2 as $k=>$v){
                        $f2[$k]['number']=$are[$v['id']];
                    }
                    $count1=$dao2->query($sql3);
                    $count=$count1['0']['num'];
                    $dao3=M('examination_room');
                    $f3=$dao3->field('id,room')->where($where1)->select();
                    foreach($f3 as $k=>$v){
                        $data1[$v['id']]=$v['room'];
                    }
                    if(empty($f2)){
                        $data2['data']=null;
                    }
                    foreach($f2 as $k=>$v){
                        $data2['data'][$k]['className']=$v['className'];
                        $data2['data'][$k]['serialNumber']=$v['serialNumber'];
                        $data2['data'][$k]['name']=$v['name'];
                        $data2['data'][$k]['room']=$data1[$v['roomid']];
                        $data2['data'][$k]['seatnumber']=$v['seatnumber'];
                        $data2['data'][$k]['branch']=$v['branch'];
                        $data2['data'][$k]['number']=$v['number'];
                        if(!$arrn[$v['id']]){
                            $data2['data'][$k]['isResSchool']='否';
                        }else{
                            $data2['data'][$k]['isResSchool']='是';
                        }
                    }

                    $datac=$this->sortArrByOneFieldAll($data2['data'],$field,$order,$screen,$page,$limit);
                    $data2['data']=$datac['data'];
                    $data2['page']=$datac['pageclass'];
                    $data2['examlist']=$examlist;
                    $this->ajaxReturn($data2);
                }
            }elseif(I('get.typename')=='arrangeinsert'){/********************考生安排生成排位*******************/
                //修改，被删除的学生不参与生成排位，在排位时对考场进行排序，方便后面使用第X考场
                $mode=I('post.mode');
                $scId=$getSession['scId'];
                $gradeid=I('post.gradeid');
                $examinationid=I('post.examinationid');
                $where1['examinationid']=$examinationid;
                $daoa=M();//修改，判断考场安排的座位数是否小于参考人数，如果小于返回error
                $sqla="SELECT SUM(seats) as num,branch FROM `mks_examination_room` WHERE examinationid=".$examinationid." AND scId=".$scId." GROUP BY branch";
                $sqlb="SELECT COUNT(id) as num,branch FROM `mks_examination_student` WHERE examinationid=".$examinationid." AND scId=".$scId." and participate='是' GROUP BY branch";
                $fa=$daoa->query($sqla);
                $fb=$daoa->query($sqlb);
                /*$sqlz="SELECT COUNT(*) as num FROM `mks_examination_room` WHERE examinationid=".$examinationid." AND `status`=0 AND scId=".$scId;
                $fz=$daoa->query($sqlz);
                if($fz['0']['num']!=0){
                    $arr['return']=false;
                    $arr['msg']='未设置考场座位布局！';
                    $this->ajaxReturn($arr);
                    exit;
                }*/
                foreach($fa as $k=>$v){
                    $ta[$v['branch']]=$v['num'];
                }
                foreach($fb as $k=>$v){
                    if($v['num']>$ta[$v['branch']]){
                        $arr['return']=false;
                        $arr['msg']='座位数少于参考人数！';
                        $this->ajaxReturn($arr);
                        exit;
                    }
                }
                $isResSchool=I('post.isResSchool');
                $where1['scId']=$scId;
                $dao1=M('examination_arrange');
                $f1=$dao1->where($where1)->delete();
                if($f1!==false){
                    if($mode=='随机排序'){
                        $dao=M();
                        $sql="SELECT s.`userid`,s.`branch` FROM `mks_examination_student` as s,mks_user as u WHERE s.userid=u.id AND s.`participate`='是' AND s.`examinationid`=".I('post.examinationid')." and s.scId=".$scId;
                        $f=$dao->query($sql);
                        foreach($f as $k=>$v){
                            $arr['userid']=$v['userid'];
                            $aaz[]=$v['userid'];
                            $arr['branchid']=$v['branch'];
                            $arr['examinationid']=I('post.examinationid');
                            $arr['scId']=$scId;
                            $arr['roomid']=null;
                            $arr['seatnumber']=null;
                            $arr['lastRecordTime']=time();
                            $arr['createTime']=time();
                            $arr1[$v['branch']][]=$arr;
                        }
                        foreach($arr1 as $k=>$v){
                            shuffle($arr1[$k]);
                        }
                    }elseif($mode=='按座位号顺序'){
                        $dao=M();
                        $sql1="SELECT s.`userid`,s.`branch`,u.class,u.serialNumber FROM `mks_examination_student` AS s,mks_user AS u WHERE s.`participate`='是' AND u.`id`=s.`userid` AND s.`examinationid`=".I('post.examinationid')." and u.scId=".$scId." order by s.branch,u.className,u.serialNumber asc";
                        $f1=$dao->query($sql1);
                        foreach($f1 as $k=>$v){
                            $arr['userid']=$v['userid'];
                            $aaz[]=$v['userid'];
                            $arr['branchid']=$v['branch'];
                            $arr['examinationid']=I('post.examinationid');
                            $arr['roomid']=null;
                            $arr['seatnumber']=null;
                            $arr['scId']=$scId;
                            $arr['lastRecordTime']=time();
                            $arr['createTime']=time();
                            $arr1[$v['branch']][]=$arr;
                        }
                    }elseif($mode=='按总分降序排序'){
                        $dao=M();
                        $id=I('post.id');
                        $sql1="SELECT SUM(results) as num,userid FROM `mks_examination_results` WHERE examinationid=".$id." GROUP BY userid";
                        $f1=$dao->query($sql1);
                        foreach($f1 as $k=>$v){
                            $arr11[$v['num']]=$v['userid'];
                        }
                        $sql2="SELECT s.`userid`,s.`branch` FROM `mks_examination_student` as s,mks_user as u WHERE s.userid=u.id AND s.`participate`='是' AND s.`examinationid`=".I('post.examinationid')." and s.scId=".$scId;
                        $f2=$dao->query($sql2);
                        ksort($arr11);
                        foreach($arr11 as $k=>$v){
                            foreach($f2 as $a=>$b){
                                if($v==$b['userid']){
                                    unset($f2[$a]);
                                    array_unshift($f2,$b);
                                }
                            }
                        }
                        $arrf=array_values($f2);
                        foreach($arrf as $k=>$v){
                            $arr['userid']=$v['userid'];
                            $aaz[]=$v['userid'];
                            $arr['branchid']=$v['branch'];
                            $arr['examinationid']=I('post.examinationid');
                            $arr['roomid']=null;
                            $arr['seatnumber']=null;
                            $arr['scId']=$scId;
                            $arr['lastRecordTime']=time();
                            $arr['createTime']=time();
                            $arr1[$v['branch']][]=$arr;
                        }
                    }
                    $aaid=implode(',',$aaz);
                    if($isResSchool){
                        $sqln="SELECT `userId`,isTempStudy as isResSchool FROM `mks_school_rollinfo` WHERE userId in(".$aaid.")";
                        $fn=$dao->query($sqln);
                        foreach($fn as $k=>$v){
                            $arrn[$v['userId']]=$v['isResSchool'];
                        }
                        foreach($arr1 as $k=>$v){
                            foreach($v as $a=>$b){
                                if($arrn[$b['userid']]==1){
                                    unset($arr1[$k][$a]);
                                    array_push($arr1[$k],$b);
                                }
                            }
                            $arr1[$k]=array_values($arr1[$k]);
                        }
                    }
                    $dao2=M('examination_room');
                    //$where1['status']=1;//修改，只对座位布局已完成的考场分配考生
                    $f2=$dao2->field('id,branch,seats')->where($where1)->order('branch')->select();
                    foreach($f2 as $k=>$v){
                        $arr3['id']=$v['id'];
                        $arr3['seats']=$v['seats'];
                        $arr2[$v['branch']][]=$arr3;
                        $arrt[$v['id']]=$k+1;
                    }
                    /******************给考场进行排序，生成序号后面排第X考场用******************/
                    $ids = implode(',', array_keys($arrt));
                    $sql = "UPDATE mks_examination_room SET name = CASE id ";
                    foreach($arrt as $k=>$v){
                        $sql.="WHEN ".$k." THEN ".$v." ";
                    }
                    $sql.=' END,lastRecordTime ='.time()." WHERE id IN (".$ids.") and scId=".$scId;
                    $fz=$dao->query($sql);
                    if($fz!==false){
                        foreach($arr2 as $k=>$v){
                            $arrt['id']=$v['id'];
                            $arrt['name']=$k+1;
                            $ara[]=$arrt;
                            $numstart=0;
                            foreach($v as $m=>$n){
                                $numend=$numstart+$n['seats'];
                                $z=1;
                                for($i=$numstart;$i<$numend;$i++){
                                    if($arr1[$k][$i]){
                                        $arr1[$k][$i]['roomid']=$n['id'];
                                        $arr1[$k][$i]['seatnumber']=$z;
                                        $z++;
                                    }
                                }
                                $numstart=$numstart+$n['seats'];
                            }
                        }
                        //exit;
                        $arro['return']=true;
                        $dao3=M("examination_arrange");
                        foreach($arr1 as $k=>$v){
                            $f=$dao3->addAll($v);
                            if($f===false){
                                $arro['return']=false;
                                $arro['msg']='操作失败！';
                            }
                        }
                    }else{
                        $arro['return']=false;
                        $arro['msg']='操作失败！';
                    }
                }else{
                    $arro['return']=false;
                    $arro['msg']='操作失败！';
                }
                $this->ajaxReturn($arro);
            }
        }elseif(I('get.type')=='invigilatorarrange'){/*******************监考安排**********************/
            if(I('get.typename')=='generate'){/***********生成监考安排*************/
                $mode=I('post.mode');
                $dao=M();
                $dao1=M('examination_subject');
                $scId=$getSession['scId'];
                $dao2=M('examination_room');
                $dao3=M('examination_teacher');
                /*$sqlz="SELECT COUNT(*) as num FROM `mks_examination_room` WHERE examinationid=".I('post.examinationid')." AND `status`=0 AND scId=".$scId;
                $fz=$dao->query($sqlz);
                if($fz['0']['num']!=0){
                    $arr['return']=false;
                    $arr['msg']='未设置完成所有考场座位布局！';
                    $this->ajaxReturn($arr);
                    exit;
                }*/
                $sqla='select u.`name`,t.`userid` from `mks_examination_teacher` as t,mks_user as u where u.id=t.`userid` and t.scId='.$scId.' and examinationid='.I('post.examinationid');
                $fa=$dao3->query($sqla);
                if(empty($fa['0'])){
                    $are=$this->getArrange($examinationid);
                }else{
                    foreach($fa as $k=>$v){
                        $arrfa[$v['userid']]=$v['name'];
                    }
                    $sql1="SELECT e.id,e.branchid,e.date,e.starttime,e.endtime,s.subjectname FROM `mks_examination_subject` AS e,mks_subject AS s WHERE e.subject=s.subjectid and e.scId=".$scId." AND e.examinationid=".I('post.examinationid');
                    $f1=$dao->query($sql1);//考试科目，时间，日期，科类
                    $where['examinationid']=I('post.examinationid');
                    $where['scId']=$scId;
                    $where2['examinationid']=I('post.examinationid');
                    //$where2['status']=1;
                    $f2=$dao2->where($where2)->field('branch,room,id')->order('name asc')->select();//考场科类，考场
                    $count=count($f2);
                    foreach($f2 as $k=>$v){
                        $arr1[$v['branch']][]=$v['id'];
                    }
                    foreach($f1 as $k=>$v){
                        $time=$v['date'].'-'.$v['starttime']."-".$v['endtime'];
                        foreach($arr1[$v['branchid']] as $m=>$n){
                            $arr2[$time][$v['id']][]=$n;
                        }
                    }
                    foreach($f1 as $k=>$v){
                        $arr5[$v['id']]=$v['subjectname'];
                    }
                    //print_r($arr5);
                    //exit;
                    $sql3="SELECT t.userid,t.invigilator,t.visits,t.totalinspection,u.`teachingSubjects` FROM `mks_examination_teacher` AS t,mks_user AS u WHERE t.`userid`=u.`id` and t.scId=".$scId." AND t.examinationid=".I('post.examinationid');
                    $f3=$dao3->query($sql3);
                    $key5=0;
                    $keyz=0;
                    foreach($f3 as $k=>$v){
                        if($v['invigilator']){
                            //$arrinvigilator[$v['userid']]=$v['invigilator'];
                            if($v['invigilator']){
                                $key5++;
                                $keyz=$keyz+$v['invigilator'];
                            }
                            $arz['invigilator']=$v['invigilator'];
                            $arz['userid']=$v['userid'];
                            $arz['teachingSubjects']=$v['teachingSubjects'];
                            $arrinvigilator[]=$arz;//监考
                        }

                        if($v['visits']){
                            $arrvisits[$v['userid']]=$v['visits'];//巡考
                        }
                        if($v['totalinspection']){
                            $arrtotalinspection[$v['userid']]=$v['totalinspection'];//总巡考
                        }
                    }
                    $keyy=count($arr2);
                    $dao9=M('examination_subject');
                    $sql9="SELECT * FROM `mks_examination_subject` WHERE examinationid=".$where['examinationid']." and scId=".$scId." GROUP BY date,starttime";
                    $f9=$dao9->query($sql9);
                    $n9=count($f9);
                    $datab['examinationid'] = I('post.examinationid');
                    //$datab['status'] = 1;
                    $dao10 = M('examination_room');
                    $f = $dao10->where($datab)->count();
                    $key1=count($f3);
                    $key2=$n9*$f;
                    $key2=count($f2);
                    $key3=$key5/$key2;
                    $keyh=$keyz/($keyy*$key2);
                    if($key3>=2 &&$keyh>=2){
                        $key3=2;
                    }else{
                        $key3=1;
                    }
                    if($mode=='不限制'){
                        foreach($arr2 as $k=>$v){//考试日期
                            shuffle($arrinvigilator);/*****************修改，将监考老师的数组随机排序，以免一直是一个老师在监考******************/
                            foreach($arrvisits as $a=>$b){
                                if($arrvisits[$a]){
                                    $arr8['ensubjectid']='';
                                    $arr8['roomid']='巡考';
                                    $arr8['userid']=$a;
                                    $arr8['name']=$arrfa[$a];
                                    $arrza[]=$a;
                                    $arrvisits[$a]=$arrvisits[$a]-1;
                                    break;
                                    //$e++;
                                }else{
                                    $arr8=array();
                                    continue;
                                }
                            }
                            foreach($arrtotalinspection as $a=>$b){
                                if($arrtotalinspection[$a]){
                                    $arr9['ensubjectid']='';
                                    $arr9['roomid']='总巡考';
                                    $arr9['userid']=$a;
                                    $arr9['name']=$arrfa[$a];
                                    $arrza[]=$a;
                                    $arrtotalinspection[$a]=$arrtotalinspection[$a]-1;
                                    break;
                                    //$e++;
                                }else{
                                    $arr9=array();
                                    continue;
                                }
                            }
                            $arrn=$arrinvigilator;
                            $i=0;
                            foreach($v as $c=>$d){//科目
                                if(!empty($arr8)){
                                    $arr8['ensubjectid']=$c;
                                    $arr4[]=$arr8;
                                }
                                if(!empty($arr9)){
                                    $arr9['ensubjectid']=$c;
                                    $arr4[]=$arr9;
                                }
                                foreach($d as $a=>$b){//考场
                                    $z=0;//控制监考人数
                                    for($l=$i;$l<$key1;$l++){//老师
                                        if($arrinvigilator[$l]['invigilator']&&!in_array($arrinvigilator[$l]['userid'],$arrza)){/*************************修改，去掉已经安排了巡考或总巡考的*********************************/
                                            if($z==$key3){
                                                break;
                                            }else{
                                                $arr3['ensubjectid']=$c;
                                                $arr3['roomid']=$b;
                                                $arr3['userid']=$arrinvigilator[$l]['userid'];
                                                $arr3['name']=$arrfa[$arrinvigilator[$l]['userid']];
                                                $arr4[]=$arr3;
                                                $arrinvigilator[$l]['invigilator']=$arrinvigilator[$l]['invigilator']-1;
                                                $z++;
                                            }
                                        }
                                        $i=$l+1;
                                    }
                                }
                            }
                        }
                    }elseif($mode=='本学科教师不要监考本学科考试'){
                        foreach($arr2 as $k=>$v){//考试日期
                            shuffle($arrinvigilator);
                            foreach($arrvisits as $a=>$b){
                                if($arrvisits[$a]){
                                    $arr8['ensubjectid']='';
                                    $arr8['roomid']='巡考';
                                    $arr8['userid']=$a;
                                    $arr8['name']=$arrfa[$a];
                                    $arrza[]=$a;
                                    $arrvisits[$a]=$arrvisits[$a]-1;
                                    break;
                                    //$e++;
                                }else{
                                    $arr8=array();
                                    continue;
                                }
                            }
                            foreach($arrtotalinspection as $a=>$b){
                                if($arrtotalinspection[$a]){
                                    $arr9['ensubjectid']='';
                                    $arr9['roomid']='总巡考';
                                    $arr9['userid']=$a;
                                    $arrza[]=$a;
                                    $arr9['name']=$arrfa[$a];
                                    $arrtotalinspection[$a]=$arrtotalinspection[$a]-1;
                                    break;
                                    //$e++;
                                }else{
                                    $arr9=array();
                                    continue;
                                }
                            }
                            $arrn=$arrinvigilator;
                            $i=0;
                            foreach($v as $c=>$d){//科目
                                if(!empty($arr8)){
                                    $arr8['ensubjectid']=$c;
                                    $arr4[]=$arr8;
                                }
                                if(!empty($arr9)){
                                    $arr9['ensubjectid']=$c;
                                    $arr4[]=$arr9;
                                }
                                $arrk=$arrn;
                                foreach($arrk as $f=>$g){
                                    if($arr5[$c]==$g['teachingSubjects']){
                                        unset($arrk[$f]);
                                    }
                                }
                                foreach($d as $a=>$b){//考场
                                    $z=0;//控制监考人数
                                    for($l=$i;$l<$key1;$l++){//老师
                                        if($arrk[$l]['invigilator']&&!in_array($arrinvigilator[$l]['userid'],$arrza)){
                                            if($z==$key3){
                                                break;
                                            }else{
                                                $arr3['ensubjectid']=$c;
                                                $arr3['roomid']=$b;
                                                $arr3['userid']=$arrk[$l]['userid'];
                                                $arr3['name']=$arrfa[$arrk[$l]['userid']];
                                                $arr4[]=$arr3;
                                                unset($arrn[$l]);
                                                $arrinvigilator[$l]['invigilator']=$arrinvigilator[$l]['invigilator']-1;
                                                $z++;
                                            }
                                        }
                                        $i=$l+1;
                                    }
                                }
                            }
                        }
                    }elseif($mode=='本学科教师要监考本学科考试'){
                        foreach($arr2 as $k=>$v){//考试日期
                            shuffle($arrinvigilator);
                            foreach($arrvisits as $a=>$b){
                                if($arrvisits[$a]){
                                    $arr8['ensubjectid']='';
                                    $arr8['roomid']='巡考';
                                    $arr8['userid']=$a;
                                    $arr8['name']=$arrfa[$a];
                                    $arrza[]=$a;
                                    $arrvisits[$a]=$arrvisits[$a]-1;
                                    break;
                                    //$e++;
                                }else{
                                    continue;
                                }
                            }

                            foreach($arrtotalinspection as $a=>$b){
                                if($arrtotalinspection[$a]){
                                    $arr9['ensubjectid']='';
                                    $arr9['roomid']='总巡考';
                                    $arr9['userid']=$a;
                                    $arr9['name']=$arrfa[$a];
                                    $arrza[]=$a;
                                    $arrtotalinspection[$a]=$arrtotalinspection[$a]-1;
                                    break;
                                    //$e++;
                                }else{
                                    continue;
                                }
                            }
                            $arrn=$arrinvigilator;
                            $i=0;
                            foreach($v as $c=>$d){//科目
                                if(!empty($arr8)){
                                    $arr8['ensubjectid']=$c;
                                    $arr4[]=$arr8;
                                    $arr8=array();
                                }
                                if(!empty($arr9)){
                                    $arr9['ensubjectid']=$c;
                                    $arr4[]=$arr9;
                                    $arr9=array();
                                }
                                $arrk=$arrn;
                                foreach($arrk as $f=>$g){
                                    if($arr5[$c]!=$g['teachingSubjects']){
                                        unset($arrk[$f]);
                                    }
                                }
                                foreach($d as $a=>$b){//考场
                                    $z=0;//控制监考人数
                                    for($l=$i;$l<$key1;$l++){//老师
                                        if($arrk[$l]['invigilator']&&!in_array($arrinvigilator[$l]['userid'],$arrza)){
                                            if($z==$key3){
                                                break;
                                            }else{
                                                $arr3['ensubjectid']=$c;
                                                $arr3['roomid']=$b;
                                                $arr3['userid']=$arrk[$l]['userid'];
                                                $arr3['name']=$arrfa[$arrk[$l]['userid']];
                                                $arr4[]=$arr3;
                                                unset($arrn[$l]);
                                                $arrinvigilator[$l]['invigilator']=$arrinvigilator[$l]['invigilator']-1;
                                                $z++;
                                            }
                                        }
                                        $i=$l+1;
                                    }
                                }
                            }
                        }
                    }
                    $are=$this->getArrange($where['examinationid'],$arr4);
                }
                $this->ajaxReturn($are);
            }elseif(I('get.typename')=="find"){/************************监考安排展示****************************/
                $examinationid=I('post.examinationid');
                $are=$this->getArrange($examinationid);
                $this->ajaxReturn($are);
            }elseif(I('get.typename')=="save"){/***************监考安排保存***********/
                $data=I('post.data');
                if($data){
                    M()->startTrans();
                    $dao=M('examination_supervision');
                    $scId=$getSession['scId'];
                    $examinationid=I('post.examinationid');
                    $sql="SELECT `date`,starttime,id FROM `mks_examination_subject` WHERE examinationid=".$examinationid." AND scId=".$scId." GROUP BY `date`,starttime,id";
                    $fk=$dao->query($sql);
                    foreach($fk as $k=>$v){
                        $arrk[$v['date'].$v['starttime']][]=$v['id'];
                    }
                    foreach($data as $k=>$v){
                        if($v['roomid']!='巡考' && $v['roomid']!='总巡考'){
                            $azaz[$v['ensubjectid']][]=$v['userid'];
                        }else{
                            $azaza[$v['ensubjectid']][]=$v['userid'];
                        }
                    }
                    foreach($azaz as $k=>$v){
                        foreach($v as $a=>$h){
                            if(!$h){
                                unset($v[$a]);
                            }
                        }
                        $azaz[$k]=array_values($v);
                    }
                    foreach($azaza as $k=>$v){
                        foreach($v as $a=>$h){
                            if(!$h){
                                unset($v[$a]);
                            }
                        }
                        $azaza[$k]=array_values(array_unique($v));
                    }
                    foreach($arrk as $k=>$v){
                        foreach($v as $a=>$b){
                            $azaz[$b]=array_values($azaz[$b]);
                            $azaza[$b]=array_values($azaza[$b]);

                            foreach($azaz[$b] as $l=>$n){
                                $ahah[$k][]=$n;
                            }
                            foreach($azaza[$b] as $l=>$n){
                                $ahaha[$k][]=$n;
                            }
                        }
                    }
                    foreach($ahah as $k=>$v){
                        if(count($v)!=count(array_unique($v))){
                            $ac['return']=false;
                            $ac['msg']='不同科类中同一时间段内监考老师重复安排!';
                            $this->ajaxReturn($ac);
                        }else{
                            foreach($ahaha[$k] as $a=>$c){
                                if(in_array($c,$v)){
                                    $ac['return']=false;
                                    $ac['msg']='不同科类中同一时间段内监考老师重复安排!';
                                    $this->ajaxReturn($ac);
                                }
                            }
                        }
                    }

                    $arr['return']=true;

                    //{"userid":"122","roomid":"140","ensubjectid":"512","id":"428"}
                    $where1['examinationid']=$examinationid;
                    $where1['scId']=$scId;

                    $data2=array();
                    foreach($data as $k=>$v){
                        if($v['userid']){
                            $data1['room']=$v['roomid'];
                            $data1['scId']=$scId;
                            $data1['examinationid']=$examinationid;
                            $data1['ensubjectid']=$v['ensubjectid'];
                            $data1['userid']=$v['userid'];
                            $data1['lastRecordTime']=time();
                            $data1['createTime']=time();
                            $data2[]=$data1;
                        }
                    }
                    $f1=$dao->where($where1)->delete();
                    $f=$dao->addAll($data2);
                    if($f1===false || $f===false){
                        M()->rollback();
                        $arr['return']=false;
                        $arr['msg']='保存失败!';
                    }else{
                        M()->commit();
                        $arr['return']=true;
                    }
                }else{
                    $arr['return']=false;
                    $arr['msg']='保存失败!';
                }

                $this->ajaxReturn($arr);
            }elseif(I('get.typename')=='export'){/*****************监考安排导出********************/
                $examinationid=I('get.examinationid');
                $branch=I('post.branch');
                $are=$this->getArrange($examinationid);
                foreach($are['title'] as $k=>$v){
                    if($v['branch']==$branch){
                        $title=$v['datelist'];
                    }else{
                        continue;
                    }
                }
                vendor("PHPExcel.PHPExcel");
                $objPHPExcel = new \PHPExcel();
                // Set properties
                //$objPHPExcel->getProperties()->setCreator("cdn");
                //$objPHPExcel->getProperties()->setLastModifiedBy("cdn");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");

// Add some data
                $a=0;
                $b=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
                foreach($are['title'] as $k=>$v){
                    if($a!=0){
                        $objPHPExcel->createSheet();
                    }
                    $objPHPExcel->setActiveSheetIndex($a);
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objPHPExcel->getActiveSheet()->setTitle($v['branch']);
                    $a++;
                    $objPHPExcel->getActiveSheet()->SetCellValue('A1', '考场');
                    $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
                    $n=1;
                    foreach($v['datelist'] as $c=>$d){
                        $num=count($d['subjectlist']);
                        $objPHPExcel->getActiveSheet()->SetCellValue($b[$n].'1', $d['date']);
                        $num1=$n+$num-1;
                        $objPHPExcel->getActiveSheet()->mergeCells($b[$n].'1:'.$b[$num1].'1');
                        $z=$n;
                        foreach($d['subjectlist'] as $g=>$h){
                            $objPHPExcel->getActiveSheet()->SetCellValue($b[$z].'2', $h['subjectname']);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($b[$z])->setWidth(20);
                            $y=3;
                            foreach($are['data'] as $j=>$l){
                                if($l['branch']==$v['branch']){
                                    foreach($l['roomlist'] as $m=>$o){
                                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$y, $o['room']);
                                        $st='';
                                        if($o[$h['ensubjectid']]){
                                            if(!$o[$h['ensubjectid']]['1']){
                                                $st=$o[$h['ensubjectid']]['0']['name'].'-----';
                                            }else{
                                                $st=$o[$h['ensubjectid']]['0']['name'].$o[$h['ensubjectid']]['1']['name'];
                                            }
                                        }else{
                                            $st='----------';
                                        }
                                        $objPHPExcel->getActiveSheet()->SetCellValue($b[$z].$y,$st);
                                        $y++;
                                    }
                                }
                            }
                            $z++;
                        }
                        $n+=$num;
                    }
                    /*$n=1;
                    foreach($arr6['scorenamelist'] as $k=>$v){
                        $st='name'.$n;
                        if(){}
                    }
                    $objPHPExcel->getActiveSheet()->SetCellValue('C1', $arr6['scorenamelist']['name1']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('D1', $arr6['scorenamelist']['name2']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('E1', $arr6['scorenamelist']['name3']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('F1', $arr6['scorenamelist']['name4']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('G1', $arr6['scorenamelist']['name5']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('H1', $arr6['scorenamelist']['name6']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('I1', $arr6['scorenamelist']['name7']);
                    $i = 2;
                    foreach ($arr6['data'] as $k => $v){
                        $num = $i++;
                        $objPHPExcel->getActiveSheet()->setCellValue('A' . $num, $v['branch']);
                        $objPHPExcel->getActiveSheet()->setCellValue('B' . $num, $v['subject']);
                        $objPHPExcel->getActiveSheet()->setCellValue('C' . $num, $v['score1']);
                        $objPHPExcel->getActiveSheet()->setCellValue('D' . $num, $v['score2']);
                        $objPHPExcel->getActiveSheet()->setCellValue('E' . $num, $v['score3']);
                        $objPHPExcel->getActiveSheet()->setCellValue('F' . $num, $v['score4']);
                        $objPHPExcel->getActiveSheet()->setCellValue('G' . $num, $v['score5']);
                        $objPHPExcel->getActiveSheet()->setCellValue('H' . $num, $v['score6']);
                        $objPHPExcel->getActiveSheet()->setCellValue('I' . $num, $v['score7']);
                    }*/
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
        } elseif(I('get.type')=='resultin'){/**********************导入成绩********************/
            if(I('get.typename')=='resultfind'){/*************************各科目成绩导入情况查询*******************************/
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                $dao1=M('examination_subject');
                $where1['examinationid']=$examinationid;
                $where2['examinationid']=$examinationid;
                $where2['participate']='是';
                $where1['scId']=$scId;
                $where2['scId']=$scId;
                $dao2=M('examination_student');
                $dao3=M('examination_results');
                $sql6="SELECT branch,COUNT(branch) AS num FROM `mks_examination_student` WHERE examinationid=".$examinationid." and participate='是' and scId=".$scId." GROUP BY branch";
                $f6=$dao2->query($sql6);
                foreach($f6 as $k=>$v){
                    $m6[$v['branch']]=$v['num'];
                }
                $f1=$dao1->field('branchid,subject')->where($where1)->select();
                foreach($f1 as $k=>$v){
                    $arr1[$v['branchid']][$v['subject']]=0;
                }
                $sql2="SELECT branch,COUNT(userid) as num FROM mks_examination_student WHERE participate='是' AND examinationid=".$examinationid." and scId=".$scId." GROUP BY branch";
                $f2=$dao2->query($sql2);
                foreach($f2 as $k=>$v){
                    $arr2[$v['branch']]=$v['num'];
                }
                $sql3="SELECT COUNT(userid) AS num,subjectid,branchid FROM `mks_examination_results` WHERE examinationid=".$examinationid." and scId=".$scId." group by branchid,subjectid";
                $f3=$dao3->query($sql3);
                foreach($f3 as $k=>$v){
                    $arr3[$v['branchid']][$v['subjectid']]=$v["num"];
                }
                foreach($arr1 as $k=>$v){
                    foreach($v as $m=>$n){
                        $arr1[$k][$m]=$arr3[$k][$m];
                    }
                }
                $where['scId']=$scId;
                $wherez['scId']=array('in','0,'.$scId);
                $dao4=M('class_branch');
                $dao5=M('subject');
                $f4=$dao4->where($wherez)->field("branchid,branch")->select();
                foreach($f4 as $k=>$v){
                    $arr4[$v['branchid']]=$v['branch'];
                }

                $f5=$dao5->where($where)->field("subjectid,subjectname")->select();
                foreach($f5 as $k=>$v){
                    $arr5[$v['subjectid']]=$v['subjectname'];
                }
                foreach($arr1 as $k=>$v){
                    $i=0;
                    foreach($v as $m=>$n){
                        if(!$n){
                            $n=0;
                        }
                        //$arr6['branchlist'][]=$arr4[$k];
                        $arr6[$arr4[$k]][$i]['input']=$n;
                        $arr6[$arr4[$k]][$i]['uninput']=$m6[$k]-$n;
                        $arr6[$arr4[$k]][$i]['all']=$m6[$k];
                        $arr6[$arr4[$k]][$i]['ratio']=round($n/$m6[$k],4)*100;
                        $arr6[$arr4[$k]][$i]['branchid']=$k;
                        $arr6[$arr4[$k]][$i]['subjectid']=$m;
                        $arr6[$arr4[$k]][$i]['subject']=$arr5[$m];
                        $i++;
                    }
                }
                foreach($arr6 as $k=>$v){
                    $arr7['branchname']=$k;
                    $arr7['data']=$v;
                    $arr8[]=$arr7;
                }
                //foreach(){}
                //$array=array_flip($arr6['branchlist']);
                //$arr6['branchlist']=array_keys($array);
                $this->ajaxReturn($arr8);
            }elseif(I('get.typename')=='resultexport'){/****************导出成绩模板********************/
                //$http = new \Org\Net\Http;
                //ob_end_clean();
                //$filename = "./Public/upload/exam/numberTemplate.xls";
                $url=$this::$downUrl;
                header('Location: '.$url.'/Public/upload/exam/resultTemplate.xls');
                //$showname=" 导入学生考号模板.xls";
                //$http->download($filename, $showname);

            }elseif(I('get.typename')=='resultinfind'){/********************成绩查询****************/
                $where1['examinationid']=I('post.examinationid');
                $scId=$getSession['scId'];
                $where2['gradeId']=I('post.gradeid');
                $where1['scId']=$scId;
                $where2['scId']=$scId;
                $dao1=M('examination_arrange');
                $screen=I('post.screen');
                if(!$screen){
                    $screen=false;
                }
                $dao2 = M('');
                /*
                $sqla="SELECT results,proportion FROM `mks_examination_subject` WHERE examinationid=".$examinationid." AND scId="$scId" AND `subject`=".$subjectid." AND branchid=".$branchid;
                $fa=$dao->query($sqla);
                $numb=$fa['0']['results']/$fa['0']['proportion'];
                 */
                $field = I('post.field');
                $order = I("post.order");
                if (!$field) {
                    $field= "className";
                    $field2="serialNumber";
                }elseif($field=='班级+班级序号'){
                    $field="className";
                    $field="serialNumber";
                }elseif($field=="考试+考试座号"||$field=="考场+考场座号"){
                    $field = "room";
                    $field2 = "seatnumber";
                }else{
                    $field2=false;
                }
                $order=I('post.order');
                if($order=='descending'){
                    $order=true;
                }else{
                    $order=false;
                }
                $limit = I("post.limit");
                if(!$limit){
                    $limit=2;
                }
                $page = I('post.page');
                if(!$page){
                    $page=1;
                }
                $limitstart = ($page - 1) * $limit;
                $dao6=M('examination_student');
                $where3['examinationid']=I('post.examinationid');
                $where3['participate']='是';
                $where3['branch']=I('post.branchid');
                //$count = $dao6->where($where3)->count();
                $examinationid = I('post.examinationid');
                //$sql2="SELECT u.className,u.serialNumber,u.name,u.id,n.number FROM mks_user AS u,mks_examination_number AS n,mks_examination_student as s WHERE s.examinationid=n.examinationid and s.userid=n.userid and s.participate='是' and n.`userid`=u.`id` AND n.`examinationid`=".$examinationid." and s.branch=".I('post.branchid')." and n.scId=".$scId." ".$st." order by " . $field . " " . $order . " limit " . $limitstart . "," . $limit;
                $sql2="SELECT u.className,u.serialNumber,u.name,u.id FROM mks_user AS u,mks_examination_student as s
                WHERE s.participate='是' and s.`userid`=u.`id` AND s.`examinationid`=".$examinationid." and s.branch=".I('post.branchid')." and s.scId=".$scId;
                $sqln="SELECT userid,roomid,seatnumber FROM `mks_examination_arrange` WHERE examinationid=".$examinationid." AND branchid=".I('post.branchid')." AND scId=".$scId;
                $sqlv="SELECT userid,number FROM `mks_examination_number` WHERE scId=".$scId." AND examinationid=".$examinationid." AND program='本次考试'";
                $f2 = $dao2->query($sql2);
                $fv=$dao2->query($sqlv);
                foreach($fv as $k=>$v){
                    $arrv[$v['userid']]=$v['number'];
                }
                $fn=$dao2->query($sqln);
                foreach($fn as $k=>$v){
                    $aa1[$v['userid']]['roomid']=$v['roomid'];
                    $aa1[$v['userid']]['seatnumber']=$v['seatnumber'];
                }
                foreach($f2 as $k=>$v){
                    $f2[$k]['roomid']=$aa1[$v['id']]['roomid'];
                    $f2[$k]['seatnumber']=$aa1[$v['id']]['seatnumber'];
                    $f2[$k]['number']=$arrv[$v['id']];
                }
                //print_r($f2);exit;
                //$sql2 ="SELECT u.className,u.serialNumber,u.name,u.id,a.roomid,a.seatnumber,n.number FROM `mks_examination_arrange` AS a,mks_user AS u,`mks_examination_number` AS n WHERE a.userid=u.id AND a.branchid=".I('post.branchid')." and a.scId=".$scId." AND a.examinationid=n.examinationid AND a.userid=n.userid AND a.examinationid=" . $examinationid . " $st order by " . $field . " " . $order . " limit " . $limitstart . "," . $limit;
                //$sql3="SELECT count(n.userid) as num FROM mks_user AS u,mks_examination_number AS n,mks_examination_student as s WHERE s.examinationid=n.examinationid and s.userid=n.userid and n.`userid`=u.`id` AND n.`examinationid`=".$examinationid." and s.branch=".I('post.branchid')." and n.scId=".$scId." ".$st." order by " . $field . " " . $order;
                //$f2 = $dao2->query($sql2);
                //print_r($f2);
                //exit;
                //$count1=$dao2->query($sql3);
                //$count=$count1['0']['num'];
                $dao3 = M('examination_room');
                $f3 = $dao3->field('id,room')->where($where1)->select();
                foreach ($f3 as $k => $v) {
                    $data1[$v['id']] = $v['room'];
                }
                $dao5=M("examination_results");
                $where1['subjectid']=I('post.subjectid');
                $where1['branchid']=I('post.branchid');
                $where7['examinationid']=I('post.examinationid');
                $where7['scId']=$scId;
                $where7['subject']=I('post.subjectid');
                $where7['branchid']=I('post.branchid');
                $dao7=M('examination_subject');
                $f7=$dao7->field('proportion,results')->where($where7)->select();
                //echo json_encode($f7);
                if(empty($f2)){
                    $data2['data']=null;
                }
                $f5=$dao5->where($where1)->field('id,results,resultsAll,userid')->select();
                foreach($f5 as $k=>$v){
                    if(!$v['resultsAll']){
                        $datac[$v['userid']]['results']= $v['results']/($f7['0']['results']/$f7['0']['proportion']);/**************************修改，因为存的是按计入总分分数算出来的分数，现在全卷成绩就是该分数除以比例**********************/
                    }else{
                        $datac[$v['userid']]['results'] = $v['resultsAll'];
                    }
                    $datac[$v['userid']]['id']=$v['id'];
                }

                foreach ($f2 as $k => $v) {
                    $data2['data'][$k]['className'] = $v['className'];
                    $data2['data'][$k]['serialNumber'] = $v['serialNumber'];
                    $data2['data'][$k]['name'] = $v['name'];
                    $data2['data'][$k]['room'] = $data1[$v['roomid']];
                    $data2['data'][$k]['seatnumber'] = $v['seatnumber'];
                    $data2['data'][$k]['number'] = $v['number'];
                    $data2['data'][$k]['userid'] = $v['id'];
                    //$where1['userid']=$v['id'];
                    /*$f5=$dao5->where($where1)->field('id,results,resultsAll')->select();
                    if(!$f5['0']['resultsAll']){
                        $data2['data'][$k]['results'] = $f5['0']['results']/($f7['0']['results']/$f7['0']['proportion']);/**************************修改，因为存的是按计入总分分数算出来的分数，现在全卷成绩就是该分数除以比例**********************/
                    /*}else{
                        $data2['data'][$k]['results'] = $f5['0']['resultsAll'];
                    }*/
                    $data2['data'][$k]['results'] = $datac[$v['id']]['results'];
                    $data2['data'][$k]['id'] = $datac[$v['id']]['id'];
                }
                $ann=$this->sortArrByOneFieldAll($data2['data'],$field,$order,$screen,$page,$limit,$order,$field2);
                $data2['data']=$ann['data'];
                //$array, $field, $desc = false,$find=false,$page,$limit
                //proportion存的是满分 results存的是计入总分分数
                $data2['value']['results']=$f7['0']['proportion'];/***修改，因为存数据的时候满分和计入总分分数是交换了的，所以在这里进行交换*/
                $data2['value']['proportion']=$f7['0']['results'];
                $data2['page']['pageall'] = $ann['pageclass']['pageAll'];
                $data2['page']['page'] = $ann['pageclass']['page'];
                $data2['page']['count'] = $ann['pageclass']['count'];
                $this->ajaxReturn($data2);
            }elseif(I('get.typename')=='resultupin'){/*******************************修改添加成绩******************************************/
                $data=I('post.data');//修改，将单条提交的数据改为批量提交
                $scId=$getSession['scId'];
                M()->startTrans();
                $dao=M('examination_results');
                $branchid=I('post.branchid');
                $subjectid=I('post.subjectid');
                $examinationid=I('post.examinationid');
                $sqla="SELECT results,proportion FROM `mks_examination_subject` WHERE examinationid=".$examinationid." AND scId=".$scId." AND `subject`=".$subjectid." AND branchid=".$branchid;
                $fa=$dao->query($sqla);
                $numb=round($fa['0']['results']/$fa['0']['proportion'],2);//计算录入总分分数占全卷满分的比例
                foreach($data as $k=>$v){
                    $id=$v['id'];
                    $where['scId']=$scId;
                    $userid=$v['userid'];
                    if(!$result){
                        $result=0;
                    }
                    if($v['results']>$fa['0']['proportion']){
                        $v['results']=$fa['0']['proportion'];
                    }
                    $resultsa=$v['results'];
                    $results=round($v['results']*$fa['0']['results']/$fa['0']['proportion'],1);
                    if($id){
                        $dataResults[$id]=$results;
                        $dataResultsa[$id]=$resultsa;
                        $lastRecordTime=time();
                        $dataId[]=$id;
                    }else{
                        $dataa['results']=$results;
                        $dataa['resultsAll']=$resultsa;
                        $dataa['userid']=$userid;
                        $dataa['examinationid']=$examinationid;
                        $dataa['subjectid']=$subjectid;
                        $dataa['branchid']=$branchid;
                        $dataa['lastRecordTime']=time();
                        $dataa['createTime']=time();
                        $dataa['scId']=$scId;
                        $data1[]=$dataa;
                    }
                }
                $arr['return']=true;
                if($dataId){
                    $id=implode(',',$dataId);
                    $sql = "UPDATE mks_examination_results SET results = CASE id ";
                    foreach($dataResults as $a=>$b){
                        $sql.="WHEN ".$a." THEN ".$b." ";
                    }
                    $sql.=' END,resultsAll = CASE id ';
                    foreach($dataResultsa as $a=>$b){
                        $sql.="WHEN ".$a." THEN ".$b." ";
                    }
                    $sql.=' END,lastRecordTime ='.$lastRecordTime." WHERE id IN (".$id.") and scId=".$scId;
                    $f1=$dao->query($sql);
                }
                if($data1){
                    $f=$dao->addAll($data1);
                }

                if($f===false || $f1===false){
                    M()->rollback();
                    $arr['return']=false;
                }else{
                    M()->commit();
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
                /*$where['id']=I('post.id');
                $scId=$getSession['scId'];
                $where['scId']=$scId;
                $userid=I('post.userid');
                $examinationid=I('post.examinationid');
                $subjectid=I('post.subjectid');
                $branchid=I('post.branchid');
                $results=I('post.results');
                $dao=M('examination_results');
                if($where['id']){
                    $data['results']=$results;
                    $data['lastRecordTime']=time();
                    $f=$dao->where($where)->data($data)->save();
                }else{
                    $data['results']=$results;
                    $data['userid']=$userid;
                    $data['examinationid']=$examinationid;
                    $data['subjectid']=$subjectid;
                    $data['branchid']=$branchid;
                    $data['lastRecordTime']=time();
                    $data['createTime']=time();
                    $data['scId']=$scId;
                    $f=$dao->add($data);
                }
                if($f===false){
                    $data1['return']=false;
                }else{
                    $data1['return']=true;
                }
                $this->ajaxReturn($data1);*/
            }elseif(I('get.typename')=='delall'){/*******************清空**********************/
                $scId=$getSession['scId'];
                $examinationid=I('post.examinationid');
                $subjectid=I('post.subjectid');
                $branchid=I('post.branchid');
                $where['examinationid']=$examinationid;
                $where['subjectid']=$subjectid;
                $where['branchid']=$branchid;
                $where['scId']=$scId;
                $dao=M('examination_results');
                $f=$dao->where($where)->delete();
                if($f===false){
                    $data1['return']=false;
                }else{
                    $data1['return']=true;
                }
                $this->ajaxReturn($data1);
            }elseif(I('get.typename')=='import'){/*******************导入成绩********************/
                if (!empty($_FILES)) {
                    $scId=$getSession['scId'];
                    $upload = new \Think\Upload();
                    $upload->maxSize   =     3145728 ;// 设置附件上传大小
                    $upload->exts      =     array('xlsx', 'xls');// 设置附件上传类型
                    $upload->rootPath  =     './'; // 设置附件上传根目录
                    $upload->savePath  =     'Public/upload/exam/'; // 设置附件上传（子）目录
                    $upload->replace  =     true; // 设置附件是否替换
                    //$upload->savePath  =     'cdr/'; // 设置附件上传（子）目录
                    $upload->saveName = 'numberInformation';
                    $upload->autoSub = false;
                    $info   =   $upload->uploadOne($_FILES['photo']);
                    if(!$info) {// 上传错误提示错误信息
                            $arr['return']=false;
                            return $arr;
                    }

                    vendor("PHPExcel.PHPExcel");
                    //$objPHPExcel = new \PHPExcel();
                    $string =$_FILES['photo']['name'];
                    $arraym = explode('.',$string);
                    $exs=$arraym[count($arraym)-1];
                    vendor("PHPExcel.PHPExcel");
                    //$objPHPExcel = new \PHPExcel();
                    if($exs=='xls') {
                        $reader = \PHPExcel_IOFactory::createReader('Excel5'); //设置以Excel5格式(Excel97-2003工作簿)
                        $PHPExcel = $reader->load("./Public/upload/exam/numberInformation.xls"); // 载入excel文件
                    }else{
                        $reader = \PHPExcel_IOFactory::createReader('Excel2007'); //设置以Excel5格式(Excel97-2003工作簿)
                        $PHPExcel = $reader->load("./Public/upload/exam/numberInformation.xlsx"); // 载入excel文件
                    }
                    $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
                    $highestRow = $sheet->getHighestRow(); // 取得总行数
                    $highestColumm = $sheet->getHighestColumn(); // 取得总列数
                    $row = 1;
                    for ($column = 'A'; $column <= $highestColumm; $column++) {//列数是以A列开始
                        $value = (string)$sheet->getCell($column . $row)->getValue();
                        if ($value == ''){
                            continue;
                        }
                        $value=strtr($value,array(' '=>''));
                        $dataset[$column] = $value;//表头字段
                        //echo $column.$row.":".$sheet->getCell($column.$row)->getValue()."<br />";
                    }
                    $dataset1 = array();
                    for ($row = 2; $row <= $highestRow; $row++) {//行数是以第1行开始
                        $dataset1=array();
                        for ($column = 'A'; $column <= $highestColumm; $column++) {//列数是以A列开始
                            $value = (string)$sheet->getCell($column . $row)->getValue();
                            if ($value === '') {
                                continue;
                            }
                            $dataset1[$column] = $value;
                            //echo $column.$row.":".$sheet->getCell($column.$row)->getValue()."<br />";
                        }
                        if (empty($dataset1)) {
                            continue;
                        }
                        $dataset2[] = $dataset1;
                    }
                    $array = array();
                    foreach ($dataset2 as $k => $v) {
                        foreach ($v as $m => $n) {
                            foreach ($dataset as $o => $p) {
                                if ($m == $o &&$p!='序号') {
                                    $array[$p] = $n;
                                    break;
                                }
                            }
                        }
                        $array1[]=$array;
                    }
                    /**********************修改，当班内有学生重名的时候返回状态nameError以及重名学生列表
                    $validate=I('post.validate');//validate用来判断是否跳过重名验证初始为0，跳过为1
                    if($validate==0){
                    foreach($array1 as $k=>$v){
                    if($aarr[$v['班级']][$v['姓名']]){
                    $abc[]=$v['姓名'];
                    }
                    $aarr[$v['班级']][$v['姓名']]=$v['姓名'];
                    }
                    if(!empty($abc)){
                    $arr['return']='nameError';
                    $arr['List']=$abc;
                    $this->ajaxReturn($arr);
                    exit;
                    }
                    }***************************/
                    /**********************修改，当考号重复的时候返回状态numberError以及重复考号列表*/
                    $validate=I('post.validate');//validate用来判断是否跳过考号验证初始为0，跳过为1
                    if($validate==0){
                        foreach($array1 as $k=>$v){
                            if($aarr[$v['考号']]){
                                $abc[]=$v['考号'];
                            }
                            $aarr[$v['考号']]=$v['考号'];
                        }
                        if(!empty($abc)){
                            $arr['return']='numberError';
                            $arr['List']=$abc;
                            $this->ajaxReturn($arr);
                            exit;
                        }
                    }/***************************/
                    if($exs=='xls') {
                        unlink('./Public/upload/exam/numberInformation.xls');
                    }else{
                        unlink('./Public/upload/exam/numberInformation.xlsx');
                    }

                    $subjectid=I('post.subjectid');
                    $examinationid=I('post.examinationid');
                    $branchid=I('post.branchid');
                    $where7['examinationid']=$examinationid;
                    $where7['scId']=$scId;
                    $where7['subject']=$subjectid;
                    $where7['branchid']=$branchid;
                    $dao7=M('examination_subject');
                    $f7=$dao7->field('proportion,results')->where($where7)->select();
                    $dao1 = M('');
                    /***************修改，改为通过考号来对应学生添加成绩**************/
                    $sql1="SELECT u.`id`,n.`number`,u.`name` FROM `mks_examination_student` AS s,mks_user AS u,mks_examination_number as n WHERE u.scId=".$scId." and s.`userid`=u.`id` AND s.`participate`='是' AND s.`examinationid`=".$examinationid." and n.examinationid=s.examinationid AND n.`userid`=s.`userid` and s.branch=".$branchid;
                    //$sql1="SELECT u.`id`,u.`className`,u.`name` FROM `mks_examination_student` AS s,mks_user AS u WHERE u.scId=".$scId." and s.`userid`=u.`id` AND s.`participate`='是' AND s.`examinationid`=".$examinationid." and s.branch=".$branchid;
                    $f1=$dao1->query($sql1);
                    /*foreach($array1 as $k=>$v){
                        $data[$v['班级']][$v['姓名']]=$v['成绩'];
                    }*/
                    /***************************修改，改为通过考号来对应学生添加成绩*/

                    foreach($array1 as $k=>$v){
                        $data[$v['考号']]=$v['成绩'];
                        $dataz[]=$v['考号'];
                    }
                    foreach($f1 as $k=>$v){
                        if(in_array($v['number'],$dataz)&&!$data[$v['number']]){
                            $data[$v['number']]=0;
                        }elseif(!in_array($v['number'],$dataz)){
                            continue;
                        }
                        if($data[$v['number']]>$f7['0']['proportion']){
                            $data[$v['number']]=$f7['0']['proportion'];
                        }
                        $fn[$k]['results']=round($data[$v['number']]*$f7['0']['results']/$f7['0']['proportion'],1);//修改，保存的成绩为（计入总分成绩/满分）按比例存
                        $fn[$k]['resultsAll']=$data[$v['number']];//修改，保存的成绩为（计入总分成绩/满分）按比例存
                        $fn[$k]['userid']=$v['id'];
                        $fn[$k]['subjectid']=$subjectid;
                        $fn[$k]['examinationid']=$examinationid;
                        $fn[$k]['branchid']=$branchid;
                        $fn[$k]['lastRecordTime']=time();
                        $fn[$k]['createTime']=time();
                        $fn[$k]['scId']=$scId;
                    }
                    $fn=array_values($fn);
                    /**********************/
                    /*foreach($f1 as $k=>$v){
                        if(!$data[$v['className']][$v['name']]){//修改，将导入的成绩为空的改为0；
                            $fn[$k]['results']=0;
                        }else{
                            $fn[$k]['results']=$data[$v['className']][$v['name']]*round($f7['0']['results']/$f7['0']['proportion']);/******************修改，保存的成绩为（计入总分成绩/满分）按比例存****************/
                        /*}
                        $fn[$k]['userid']=$v['id'];
                        $fn[$k]['subjectid']=$subjectid;
                        $fn[$k]['examinationid']=$examinationid;
                        $fn[$k]['branchid']=$branchid;
                        $fn[$k]['lastRecordTime']=time();
                        $fn[$k]['createTime']=time();
                        $fn[$k]['scId']=$scId;
                    }*/
                    $where1['subjectid'] =$subjectid;
                    $where1['examinationid'] =$examinationid;
                    $where1['branchid']=$branchid;
                    $where1['scId']=$scId;
                    $dao2=M('examination_results');
                    $f2=$dao2->where($where1)->delete();
                    if($f2!==false){
                        $f3=$dao2->addAll($fn);
                        if($f3===false){
                            $arr['return']=false;
                        }else{
                            $arr['return']=true;
                        }
                    }else{
                        $arr['return']=false;
                    }

                    $this->ajaxReturn($arr);
                }else{
                    $arr['return']=false;
                    $this->ajaxReturn($arr);
                }
            }
        }elseif(I('get.type')=='joinalone'){/*********************************学生单独参考**************************************/
            if(I('get.typename')=='select'){/**************************单独参考学生查询************************/
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                $screen=I('post.screen');
                if($screen){
                    $st="and (u.grade like '%".$screen."%' or u.className like '%".$screen."%' or u.serialNumber like '%".$screen."%' or u.name like '%".$screen."%')";
                }
                $gradeid=I('post.gradeid');
                $dao1=M('user');
                $where1['gradeId']=$gradeid;
                $where1['roleId']=parent::$studentRoleId;
                $where1['scId']=$scId;
                $field=I('post.field');
                if(!$field){
                    $field='u.className,u.serialNumber';
                }else{
                    $field='u.'.$field;
                }
                $order=I('post.order');
                if($order=='descending'){
                    $order='desc';
                }else{
                    $order='asc';
                }
                $sqla="SELECT branch FROM mks_examination_student WHERE examinationid=".$examinationid." AND scId=".$scId." GROUP BY branch";
                $fa=$dao1->query($sqla);
                foreach($fa as $k=>$v){
                    $branchall[]=$v['branch'];
                }
                $branchll=implode(',',$branchall);
                $sqlb="SELECT classid FROM mks_class WHERE grade=".$gradeid." AND branch IN(".$branchll.") AND scId=".$scId;
                $fb=$dao1->query($sqlb);
                foreach($fb as $k=>$v){
                    $cla[]=$v['classid'];
                }
                $clas=implode(',',$cla);
                $str=$field." ".$order;
                $sql1="select u.id,g.name as grade,c.classname as className,u.serialNumber,u.name from mks_user as u,mks_school_rollinfo as r,mks_class as c,mks_grade as g where c.classid=u.class and g.gradeid=u.gradeId and u.id=r.userId and u.class IN(".$clas.") and r.`isAtSchool`='是' and u.gradeId=".$gradeid." and u.roleId=".$where1['roleId']." and u.scId=".$scId." $st order by ".$field." ".$order;
                //$f1=$dao1->where($where1)->field('id,grade,className,serialNumber,name')->order($str)->select();
                $f1=$dao1->query($sql1);
                foreach($f1 as $k=>$v){
                    $arr1[$v['id']]=$v;
                }
                $dao2=M('examination_student');
                $where2['examinationid']=$examinationid;
                $where2['scId']=$scId;
                $f2=$dao2->field('userid,participate')->where($where2)->select();
                foreach($f2 as $k=>$v){
                    $arr2[$v['userid']]=$v['participate'];
                }
                foreach($arr1 as $k=>$v){
                    if($arr2[$k]!='是'){
                        $arr3[]=$v;
                    }
                }
                if(!$arr3){
                    $arr3=array();
                }
                $this->ajaxReturn($arr3);
            }elseif(I('get.typename')=='insert'){/***********************参加考试***************************/
                $examinationid=I('post.examinationid');
                $userid=I('post.id');
                $program=I('post.program');
                $gradeid=I('post.gradeid');
                $scId=$getSession['scId'];
                $dao1=M();
                $sql1="SELECT u.`id` as userid,c.`branch`,u.gradeId,u.class as classId FROM mks_user AS u,mks_class AS c WHERE u.scId=".$scId." and u.id=".$userid." AND u.`class`=c.`classid`";
                $f1=$dao1->query($sql1);
                $dao2=M('examination_number');
                $where2['program']=$program;
                $where2['userid']=$userid;
                $where2['scId']=$scId;
                $arr['return']=false;
                $f2=$dao2->where($where2)->field('userid,number')->select();//修改，使用时删除或者注释掉
                if($f1!==false && $f2!==false){
                    $dao3=M('examination_student');
                    $where3['examinationid']=$examinationid;
                    $where3['userid']=$userid;
                    $where3['scId']=$scId;
                    $f3=$dao3->where($where3)->delete();
                    $fk=$dao2->where($where3)->delete();
                    if($f3!==false &&$fk!==false){
                        $f1['0']['examinationid']=$examinationid;
                        $f1['0']['lastRecordTime']=time();
                        $f1['0']['createTime']=time();
                        //$f1['0']['reported']='是';
                        $f1['0']['participate']='是';
                        $f1['0']['scId']=$scId;
                        $f4=$dao3->addAll($f1);
                        if($f4!==false){
                            $f2['0']['gradeid']=$gradeid;
                            $f2['0']['program']='本次考试';
                            $f2['0']['examinationid']=$examinationid;
                            $f2['0']['lastRecordTime']=time();
                            $f2['0']['createTime']=time();
                            $f2['0']['scId']=$scId;
                            /*
                            $f2['0']['userid']=$userid;
                            $f2['0']['number']=I('post.number')//考号改为传过来的考号，不通过按类型查询
                            */
                            $dao5=M('examination_number');
                            $f5=$dao5->addAll($f2);
                        }
                    }
                }
                $daokz=M('examination_score');
                $whereka['examinationid']=$examinationid;
                $fkz=$daokz->where($whereka)->delete();
                if($f5){
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }elseif(I('get.typename')=='findNumber'){/******************查询对应类型的考号******************/
                $where['userid']=I('post.id');
                $where['program']=I('post.program');
                $where['scId']=$getSession['scId'];
                $dao=M('examination_number');
                $f=$dao->where($where)->field('number')->select();
                if(!$f){
                    $f['number']=null;
                }
                $this->ajaxreturn($f);
            }
        }elseif(I('get.type')=='release'){/***************************发布成绩*********************************/
            if(I('get.typename')=='find'){/**********************成绩导入情况查询*******************/
                $examinationid=I('post.examinationid');
                $dao1=M('examination_subject');
                $scId=$getSession['scId'];
                $where1['examinationid']=$examinationid;
                $where1['scId']=$scId;
                $where2['examinationid']=$examinationid;
                $where2['scId']=$scId;
                $where2['participate']='是';
                $dao2=M('examination_student');
                $dao3=M('examination_results');
                $sql6="SELECT branch,COUNT(branch) AS num FROM `mks_examination_student` WHERE examinationid=".$examinationid." and scId=".$scId." GROUP BY branch";
                $f6=$dao2->query($sql6);
                foreach($f6 as $k=>$v){
                    $m6[$v['branch']]=$v['num'];
                }
                $f1=$dao1->field('branchid,subject')->where($where1)->select();
                foreach($f1 as $k=>$v){
                    $arr1[$v['branchid']][$v['subject']]=0;
                }
                $sql2="SELECT branch,COUNT(userid) as num FROM mks_examination_student WHERE participate='是' and scId=".$scId." AND examinationid=".$examinationid." GROUP BY branch";
                $f2=$dao2->query($sql2);
                foreach($f2 as $k=>$v){
                    $arr2[$v['branch']]=$v['num'];
                }
                $sql3="SELECT COUNT(userid) AS num,subjectid,branchid FROM `mks_examination_results` WHERE examinationid=".$examinationid." and scId=".$scId." group by branchid,subjectid";
                $f3=$dao3->query($sql3);
                foreach($f3 as $k=>$v){
                    $arr3[$v['branchid']][$v['subjectid']]=$v["num"];
                }
                foreach($arr1 as $k=>$v){
                    foreach($v as $m=>$n){
                        $arr1[$k][$m]=$arr3[$k][$m];
                    }
                }
                $where['scId']=$scId;
                $wherez['scId']=array('in','0,'.$scId);
                $dao4=M('class_branch');
                $dao5=M('subject');
                $f4=$dao4->where($wherez)->field("branchid,branch")->select();
                foreach($f4 as $k=>$v){
                    $arr4[$v['branchid']]=$v['branch'];
                }
                $f5=$dao5->where($where)->field("subjectid,subjectname")->select();
                foreach($f5 as $k=>$v){
                    $arr5[$v['subjectid']]=$v['subjectname'];
                }

                foreach($arr1 as $k=>$v){
                    foreach($v as $m=>$n){
                        if(!$n){
                            $n=0;
                        }
                        $arrp['branch']=$arr4[$k];
                        $arrp['sunbject']=$arr5[$m];
                        $arrp['input']=$n;
                        $arrp['uninput']=$m6[$k]-$n;
                        $arrp['all']=$m6[$k];
                        $arrp['ratio']=(round($n/$m6[$k],2)*100).'%';
                        //$arrp['subjectid']=$m;
                        $arr6['data'][]=$arrp;
                    }
                }
                $dao9=M('examination');
                $where9['examinationid']=$examinationid;
                $where9['scId']=$scId;
                $f9=$dao9->where($where9)->field('release,ranking')->select();
                $arr6['state']=$f9['0'];
                $this->ajaxReturn($arr6);
            }elseif(I('get.typename')=='do'){/**********************发布成绩************************/
                $examinationid=I('post.examinationid');
                $release=I('post.release');
                $ranking=I('post.ranking');
                $where['examinationid']=$examinationid;
                $where['scId']=$getSession['scId'];
                $dao=M('examination');
                $data['release']=$release;
                $data['ranking']=$ranking;
                $data['lastRecordTime']=time();
                $f=$dao->where($where)->data($data)->save();
                if($f===false){
                    $arr['return']=false;
                }else{
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }
        }elseif(I('get.type')=="score"){/******************考试划线*************************/
            if(I('get.typename')=="scorefind"){/*****************考试划线查询********************/
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                $field=I('post.field');
                $order=I('post.order');
                if(!$field){
                    $field='branchid,subject';
                }elseif($field=='branch'){
                    $field='branchid';
                }elseif($field=='subject'){
                    $field='ensubjectid';
                }
                if($order=='descending'){
                    $order='desc';
                }else{
                    $order='asc';
                }
                $wherez['scId']=array('in','0,'.$scId);
                $where1['scId']=$scId;
                $str=$field." ".$order;
                $dao1=M('examination_subject');
                $where['examinationid']=$examinationid;
                $where['scId']=$scId;
                $dao2=M('examination_score');
                $f2=$dao2->where($where)->count();
                if($f2==0){
                    $f1=$dao1->field('subject,branchid')->where($where)->select();
                    foreach($f1 as $k=>$v){
                        $arr1[$v['branchid']][]=$v['subject'];
                    }
                    foreach($arr1 as $k=>$v){
                        $arr1[$k][]='总分';
                    }
                    foreach($arr1 as $k=>$v){
                        foreach($v as $m=>$n){
                            $arr2['branchid']=$k;
                            $arr2['ensubjectid']=$n;
                            $arr2['score1']=null;
                            $arr2['name1']='线一';
                            $arr2['score2']=null;
                            $arr2['name2']='线二';
                            $arr2['score3']=null;
                            $arr2['name3']='线三';
                            $arr2['score4']=null;
                            $arr2['name4']='线四';
                            $arr2['score5']=null;
                            $arr2['name5']='线五';
                            $arr2['score6']=null;
                            $arr2['name6']='线六';
                            $arr2['lastRecordTime']=time();
                            $arr2['createTime']=time();
                            $arr2['examinationid']=$examinationid;
                            $arr2['scId']=$scId;
                            $arr3[]=$arr2;
                        }
                    }
                    $f3=$dao2->addAll($arr3);
                }
                $f4=$dao2->where($where)->field('id,branchid as branch,ensubjectid as subject,score1,score2,score3,score4,score5,score6')->order($str)->select();
                $f7=$dao2->where($where)->field('name1,name2,name3,name4,name5,name6')->limit(1)->select();
                $dao3=M('subject');
                $dao4=M('class_branch');
                $f5=$dao3->where($where1)->field('subjectid,subjectname')->select();
                $f6=$dao4->where($wherez)->field('branchid,branch')->select();
                foreach($f5 as $k=>$v){
                    $arr4[$v['subjectid']]=$v['subjectname'];
                }
                foreach($f6 as $k=>$v){
                    $arr5[$v['branchid']]=$v['branch'];
                }
                foreach($f4 as $k=>$v){
                    $arr7['branchid']=$v['branch'];
                    $arr7['branchname']=$arr5[$v['branch']];
                    $arr8[$v['branch']]=$arr7;
                    $f4[$k]['branch']=$arr5[$v['branch']];
                    if(is_numeric($v['subject'])){
                        $f4[$k]['subject']=$arr4[$v['subject']];
                    }
                }
                $i=1;

                foreach($f7['0'] as $k=>$v){
                    $arraa[$k]=$v;
                    $arraa['score']='score'.$i;
                    $arrab[]=$arraa;
                    $i++;
                    $arraa=array();
                }
                $arr6['data']=$f4;
                $arr6['scorenamelist']=$arrab;
                $arr6['branchlist']=array_values($arr8);
                $this->ajaxReturn($arr6);
            }elseif(I('get.typename')=='scorename') {/******************划线名称设置**********************/
                $data1 = I('post.');
                $examinationid = $data1['examinationid'];
                $scId = $getSession['scId'];
                $where['examinationid'] = $examinationid;
                $where['scId'] = $scId;
                $dao = M('examination_score');
                $data['name1'] = $data1['name1'];
                $data['name2'] = $data1['name2'];
                $data['name3'] = $data1['name3'];
                $data['name4'] = $data1['name4'];
                $data['name5'] = $data1['name5'];
                $data['name6'] = $data1['name6'];
                $i = 1;
                foreach ($data as $k => $v) {
                    if ($v == '') {
                        $strn = 'score'.$i;
                        $data[$strn] = null;
                    }
                    $i++;
                }
                $f1 = $dao->where($where)->data($data)->save();
                if ($f1 === false) {
                    $arr['return'] = false;
                } else {
                    $arr['return'] = true;
                }
                $this->ajaxReturn($arr);
            }elseif(I('get.typename')=='find'){
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                $dao=M();
                $sql="SELECT name1,name2,name3,name4,name5,name6 FROM `mks_examination_score` WHERE examinationid=".$examinationid." and scId=".$scId." GROUP BY examinationid";
                $f=$dao->query($sql);
                $this->ajaxReturn($f);
            }elseif(I('get.typename')=='scoreupdate'){/***************修改划线人数***************/
                $data1=I('post.');
                $scId=$getSession['scId'];//$getSession['scId'];
                $examinationid=$data1['examinationid'];
                $id=$data1['id'];
                $branchid=$data1['branchid'];
                $data['score1']=$data1['score1'];
                $data['score2']=$data1['score2'];
                $data['score3']=$data1['score3'];
                $data['score4']=$data1['score4'];
                $data['score5']=$data1['score5'];
                $data['score6']=$data1['score6'];
                $data2['name1']=$data1['name1'];
                $data2['name2']=$data1['name2'];
                $data2['name3']=$data1['name3'];
                $data2['name4']=$data1['name4'];
                $data2['name5']=$data1['name5'];
                $data2['name6']=$data1['name6'];
                $data2['lastRecordTime']=time();
                $data2['examinationid']=$examinationid;
                $data2['branchid']=$branchid;
                $data2['createTime']=time();
                $data2['scId']=$scId;
                if($branchid && $examinationid){
                    $dao1=M();
                    $dao=M('examination_score');
                    $sql1="SELECT subjectid,results FROM `mks_examination_results` WHERE examinationid=".$examinationid." AND branchid=".$branchid." ORDER BY results DESC";
                    $f1=$dao1->query($sql1);
                    $sql2="SELECT SUM(results) AS results FROM `mks_examination_results` WHERE examinationid=".$examinationid." AND branchid=".$branchid." group by userid ORDER BY results DESC";
                    $f2=$dao1->query($sql2);
                    foreach($f1 as $k=>$v){
                        $arr1[$v['subjectid']][]=$v['results'];
                    }
                    $where1['examinationid']=$examinationid;
                    $where1['branchid']=$branchid;
                    $where1['scId']=$scId;
                    $fz=$dao->where($where1)->delete();
                    if($fz!==false){
                        foreach($arr1 as $k=>$v){
                            $z=0;
                            $ensubjectid=$k;
                            foreach($data as $a=>$b){
                                if($b){
                                    $z+=$b;
                                    $s=$z-1;
                                    if($s>count($arr1[$k])-1){
                                        $data2[$a]=$v[count($arr1[$k])-1];
                                    }else{
                                        $data2[$a]=$v[$s];
                                    }
                                }
                            }
                            $data2['ensubjectid']=$ensubjectid;
                            $datt[]=$data2;
                        }
                        $where1['ensubjectid']='总分';
                        $z=0;
                        foreach($data as $a=>$b){
                            if($b){
                                $z+=$b;
                                $s=$z-1;
                                if($s>count($f2)-1){
                                    $data2[$a]=$f2[count($f2)-1]['results'];
                                }else{
                                    $data2[$a]=$f2[$s]['results'];
                                }
                            }
                        }
                        $data2['ensubjectid']='总分';
                        $datt[]=$data2;
                        $f1=$dao->addAll($datt);
                    }else{
                        $arr['return']=false;
                    }
                }elseif($id){
                    $where1['id']=$id;
                    $where1['scId']=$scId;
                    $dao=M('examination_score');
                    $data['lastRecordTime']=time();
                    $f1=$dao->where($where1)->data($data)->save();
                }
                if($f1===false){
                    $arr['return']=false;
                }else{
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }elseif(I('get.typename')=='scoredel'){/**********************清空划线**************************/
                $data1=I('post.');
                $scId=$getSession['scId'];
                $examinationid=$data1['examinationid'];
                $where['examinationid']=$examinationid;
                $where['scId']=$scId;
                $dao=M('examination_score');
                $f1=$dao->where($where)->delete();
                if($f1===false){
                    $arr['return']=false;
                }else{
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }elseif(I('get.typename')=='scoreexport'){/*********************导出************************/
                $examinationid=I('get.examinationid');
                $scId=$getSession['scId'];
                $dao2=M('examination_score');
                $where1['scId']=$scId;
                $wherez['scId']=array('in','0,'.$scId);
                $where['examinationid']=$examinationid;
                $where['scId']=$getSession['scId'];
                $f4=$dao2->where($where)->field('branchid as branch,ensubjectid as subject,score1,score2,score3,score4,score5,score6')->select();
                $f7=$dao2->where($where)->field('name1,name2,name3,name4,name5,name6')->limit(1)->select();
                $dao3=M('subject');
                $dao4=M('class_branch');
                $f5=$dao3->where($where1)->field('subjectid,subjectname')->select();
                $f6=$dao4->where($wherez)->field('branchid,branch')->select();
                foreach($f5 as $k=>$v){
                    $arr4[$v['subjectid']]=$v['subjectname'];
                }
                foreach($f6 as $k=>$v){
                    $arr5[$v['branchid']]=$v['branch'];
                }
                foreach($f4 as $k=>$v){
                    $f4[$k]['branch']=$arr5[$v['branch']];
                    if(is_numeric($v['subject'])){
                        $f4[$k]['subject']=$arr4[$v['subject']];
                    }
                }
                $arr6['data']=$f4;
                $arr6['scorenamelist']=$f7['0'];
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
                $objPHPExcel->getActiveSheet()->SetCellValue('A1', '科类');
                $objPHPExcel->getActiveSheet()->SetCellValue('B1', '科目');
                /*$n=1;
                foreach($arr6['scorenamelist'] as $k=>$v){
                    $st='name'.$n;
                    if(){}
                }*/
                $objPHPExcel->getActiveSheet()->SetCellValue('C1', $arr6['scorenamelist']['name1']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D1', $arr6['scorenamelist']['name2']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E1', $arr6['scorenamelist']['name3']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F1', $arr6['scorenamelist']['name4']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G1', $arr6['scorenamelist']['name5']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H1', $arr6['scorenamelist']['name6']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I1', $arr6['scorenamelist']['name7']);
                $i = 2;
                foreach ($arr6['data'] as $k => $v) {
                    $num = $i++;
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $num, $v['branch']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $num, $v['subject']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $num, $v['score1']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $num, $v['score2']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $num, $v['score3']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $num, $v['score4']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $num, $v['score5']);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $num, $v['score6']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $num, $v['score7']);
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
            }elseif(I('get.typename')=='ratiofind'){/****************分数率设置查询*******************/
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                $field=I('post.field');
                $order=I('post.order');
                if(!$field){
                    $field='branchid,subject';
                }elseif($field=='branch'){
                    $field='branchid';
                }elseif($field=='subject'){
                    $field='ensubjectid';
                }
                if($order=='descending'){
                    $order='desc';
                }else{
                    $order='asc';
                }
                $where1['scId']=$scId;
                $where['scId']=$scId;
                $str=$field." ".$order;
                $dao1=M('examination_subject');
                $where['examinationid']=$examinationid;
                $dao2=M('examination_rate');
                $f2=$dao2->where($where)->count();
                if($f2==0){
                    $f1=$dao1->field('subject,branchid,results')->where($where)->select();
                    foreach($f1 as $k=>$v){
                        $arr1[$v['branchid']][]=$v['subject'];
                        $arr9[$v['branchid']][$v['subject']]=$v['results'];
                        if(isset($arr10[$v['branchid']])){
                            $arr10[$v['branchid']]+=$v['results'];
                        }else{
                            $arr10[$v['branchid']]=$v['results'];
                        }
                    }

                    foreach($arr1 as $k=>$v){
                        $arr1[$k][]='总分';
                    }

                    foreach($arr1 as $k=>$v){
                        foreach($v as $m=>$n){
                            $arr2['branchid']=$k;
                            $arr2['ensubjectid']=$n;
                            $arr2['lastRecordTime']=time();
                            $arr2['createTime']=time();
                            $arr2['examinationid']=$examinationid;
                            $arr2['scId']=$scId;
                            if(is_numeric($n)){
                                $arr2['excellent']=$arr9[$k][$n]*8/10;
                                $arr2['pass']=$arr9[$k][$n]*6/10;
                                $arr2['lowscore']=$arr9[$k][$n]*3/10;
                                $arr2['fullscore']=$arr9[$k][$n];
                            }else{
                                $arr2['excellent']=$arr10[$k]*8/10;
                                $arr2['pass']=$arr10[$k]*6/10;
                                $arr2['lowscore']=$arr10[$k]*3/10;
                                $arr2['fullscore']=$arr10[$k];
                            }
                            $arr3[]=$arr2;
                        }
                    }
                    $f3=$dao2->addAll($arr3);
                }
                $f4=$dao2->where($where)->field('id,branchid as branch,ensubjectid as subject,fullscore,excellent,pass,lowscore')->order($str)->select();
                $dao3=M('subject');
                $wherez['scId']=array('in','0,'.$scId);
                $dao4=M('class_branch');
                $f5=$dao3->where($where1)->field('subjectid,subjectname')->select();
                $f6=$dao4->where($wherez)->field('branchid,branch')->select();
                $f7=$f1=$dao1->field('subject,branchid,results')->where($where)->select();
                foreach($f5 as $k=>$v){
                    $arr4[$v['subjectid']]=$v['subjectname'];
                }
                foreach($f6 as $k=>$v){
                    $arr5[$v['branchid']]=$v['branch'];
                }
                foreach($f4 as $k=>$v){
                    $f4[$k]['branch']=$arr5[$v['branch']];
                    if(is_numeric($v['subject'])){
                        $f4[$k]['subject']=$arr4[$v['subject']];
                    }
                }
                $arr6=$f4;
                $this->ajaxReturn($arr6);
            }elseif(I('get.typename')=='ratioup'){/***********************快速设置*************************/
                $data1=I('post.');
                $scId=$getSession['scId'];
                $examinationid=$data1['examinationid'];
                $id=$data1['id'];
                $dao=M('examination_rate');
                $where['examinationid']=$examinationid;
                $where['scId']=$scId;
                $f=$dao->where($where)->field('id,fullscore,subjectid')->select();
                if($examinationid&&!$id){
                    $f4=$dao->where($where)->delete();
                    if($f4!==false){
                        $dao1=M('examination_subject');
                        $f2=$dao1->field('subject,branchid,results')->where($where)->select();
                        foreach($f2 as $k=>$v){
                            $arr1[$v['branchid']][]=$v['subject'];
                            $arr9[$v['branchid']][$v['subject']]=$v['results'];
                            if(isset($arr10[$v['branchid']])){
                                $arr10[$v['branchid']]+=$v['results'];
                            }else{
                                $arr10[$v['branchid']]=$v['results'];
                            }
                        }
                        foreach($arr1 as $k=>$v){
                            $arr1[$k][]='总分';
                        }
                        foreach($arr1 as $k=>$v){
                            foreach($v as $m=>$n){
                                $arr2['branchid']=$k;
                                $arr2['ensubjectid']=$n;
                                $arr2['lastRecordTime']=time();
                                $arr2['createTime']=time();
                                $arr2['examinationid']=$examinationid;
                                $arr2['scId']=$scId;
                                if(is_numeric($n)){
                                    $arr2['excellent']=$arr9[$k][$n]*$data1['excellent']/100;
                                    $arr2['pass']=$arr9[$k][$n]*$data1['pass']/100;
                                    $arr2['lowscore']=$arr9[$k][$n]*$data1['lowscore']/100;
                                    $arr2['fullscore']=$arr9[$k][$n];
                                }else{
                                    $arr2['excellent']=$arr10[$k]*$data1['excellent']/100;
                                    $arr2['pass']=$arr10[$k]*$data1['pass']/100;
                                    $arr2['lowscore']=$arr10[$k]*$data1['lowscore']/100;
                                    $arr2['fullscore']=$arr10[$k];
                                }
                                $arr3[]=$arr2;
                            }
                        }
                        $f1=$dao->addAll($arr3);
                    }
                }elseif($id){
                    $where1['id']=$id;
                    $data['excellent']=$data1['excellent'];
                    $data['pass']=$data1['pass'];
                    $data['lowscore']=$data1['lowscore'];
                    $data['lastRecordTime']=time();
                    $f1=$dao->where($where1)->data($data)->save();
                }
                if($f1===false){
                    $arr['return']=false;
                }else{
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }elseif(I('get.typename')=='ratiodel'){/*******************清空*********************/
                $data1=I('post.');
                $scId=$getSession['scId'];
                $examinationid=$data1['examinationid'];
                $where['examinationid']=$examinationid;
                $where['scId']=$scId;
                $dao=M('examination_rate');
                $f1=$dao->where($where)->delete();
                if($f1===false){
                    $arr['return']=false;
                }else{
                    $arr['return']=true;
                }
                $this->ajaxReturn($arr);
            }elseif(I('get.typename')=='ratioexport'){/********************导出****************/
                $examinationid=I('get.examinationid');
                $dao2=M('examination_rate');
                $where1['scId']=$getSession['scId'];
                $where['scId']=$getSession['scId'];
                $where['examinationid']=$examinationid;
                $f4=$dao2->where($where)->field('branchid as branch,ensubjectid as subject,fullscore,excellent,pass,lowscore')->select();
                $dao3=M('subject');
                $dao4=M('class_branch');
                $wherez['scId']=array('in','0,'.$scId);
                $f5=$dao3->where($where1)->field('subjectid,subjectname')->select();
                $f6=$dao4->where($wherez)->field('branchid,branch')->select();
                foreach($f5 as $k=>$v){
                    $arr4[$v['subjectid']]=$v['subjectname'];
                }
                foreach($f6 as $k=>$v){
                    $arr5[$v['branchid']]=$v['branch'];
                }
                foreach($f4 as $k=>$v){
                    $f4[$k]['branch']=$arr5[$v['branch']];
                    if(is_numeric($v['subject'])){
                        $f4[$k]['subject']=$arr4[$v['subject']];
                    }
                }
                $arr6=$f4;
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
                $objPHPExcel->getActiveSheet()->SetCellValue('A1', '科类');
                $objPHPExcel->getActiveSheet()->SetCellValue('B1', '科目');
                $objPHPExcel->getActiveSheet()->SetCellValue('C1', '满分');
                $objPHPExcel->getActiveSheet()->SetCellValue('D1', '优秀');
                $objPHPExcel->getActiveSheet()->SetCellValue('E1', '及格');
                $objPHPExcel->getActiveSheet()->SetCellValue('F1', '低分');
                $i = 2;
                foreach ($arr6 as $k => $v) {
                    $num = $i++;
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $num, $v['branch']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $num, $v['subject']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $num, $v['fullscore']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $num, $v['excellent']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $num, $v['pass']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $num, $v['lowscore']);
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
            }elseif(I('get.typename')=='levelfind'){/*********************等级设置查询********************/
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                $field=I('post.field');
                $order=I('post.order');
                if(!$field){
                    $field='branchid';
                }elseif($field=='branch'){
                    $field='branchid';
                }elseif($field=='subjectid'){
                    $field='ensubject';
                }
                if($order=='descending'){
                    $order='desc';
                }else{
                    $order='asc';
                }
                $where1['scId']=$scId;
                $where['scId']=$scId;
                $str=$field." ".$order;
                $where['examinationid']=$examinationid;
                $dao2=M('examination_level');
                $f4=$dao2->where($where)->field('id,branchid as branch,ensubjectid as subject,score1,score2,score3,score4,score5,score6,score7,score8,score9,score10,score11,score12,enable')->order($str)->select();
                $f7=$dao2->where($where)->field('name1,name2,name3,name4,name5,name6,name7,name8,name9,name10,name11,name12')->limit(1)->select();
                $dao3=M('subject');
                $dao4=M('class_branch');
                $wherez['scId']=array('in','0,'.$scId);
                $f5=$dao3->where($where1)->field('subjectid,subjectname')->select();
                $f6=$dao4->where($wherez)->field('branchid,branch')->select();
                foreach($f5 as $k=>$v){
                    $arr4[$v['subjectid']]=$v['subjectname'];
                }
                foreach($f6 as $k=>$v){
                    $arr5[$v['branchid']]=$v['branch'];
                }
                foreach($f4 as $k=>$v){
                    $f4[$k]['branch']=$arr5[$v['branch']];
                    if(is_numeric($v['subject'])){
                        $f4[$k]['subject']=$arr4[$v['subject']];
                    }
                }
                foreach($f4 as $k=>$v){
                    foreach($arraa as $a=>$b){
                        $score='score'.$b;
                        unset($f4[$k][$score]);
                    }
                }
                for($i=7;$i<=12;$i++){
                    $aa='name'.$i;
                    unset($f7['0'][$aa]);
                    foreach($f4 as $k=>$v){
                        $bb='score'.$i;
                        unset($f4[$k][$bb]);
                    }
                }
                $i=1;
                foreach($f7['0'] as $k=>$v){
                    $arraa[$k]=$v;
                    $arraa['score']='score'.$i;
                    $arrab[]=$arraa;
                    $i++;
                    $arraa=array();
                }
                $arr6['data']=$f4;
                $arr6['namelist']=$arrab;
                $this->ajaxReturn($arr6);
            }elseif(I('get.typename')=='levelinsert'){/******************快速设置***************/
                $examinationid=I('post.examinationid');
                $where1['scId']=$getSession['scId'];
                $where['scId']=$getSession['scId'];
                $dat1=I('post.');
                $dao1=M('examination_subject');
                $where['examinationid']=$examinationid;
                $dao2=M('examination_level');
                $f1=$dao1->field('subject,branchid,results')->where($where)->select();
                $f2=$dao2->where($where)->delete();
                if($f2!==false){
                    foreach($f1 as $k=>$v){
                        $arr1[$v['branchid']][]=$v['subject'];
                        $arr9[$v['branchid']][$v['subject']]=$v['results'];
                        if(isset($arr10[$v['branchid']])){
                            $arr10[$v['branchid']]+=$v['results'];
                        }else{
                            $arr10[$v['branchid']]=$v['results'];
                        }
                    }
                    foreach($arr1 as $k=>$v){
                        $arr1[$k][]='总分';
                    }
                    if(!$dat1['ratio1']){
                        $dat1['ratio1']=90;
                    }
                    if(!$dat1['ratio2']){
                        $dat1['ratio2']=80;
                    }
                    if(!$dat1['ratio3']){
                        $dat1['ratio3']=70;
                    }
                    if(!$dat1['ratio4']){
                        $dat1['ratio4']=60;
                    }
                    if(!$dat1['ratio5']){
                        $dat1['ratio5']=55;
                    }
                    if(!$dat1['ratio6']){
                        $dat1['ratio6']=50;
                    }
                    if(!$dat1['ratio7']){
                        $dat1['ratio7']=45;
                    }
                    if(!$dat1['ratio8']){
                        $dat1['ratio8']=40;
                    }
                    if(!$dat1['ratio9']){
                        $dat1['ratio9']=35;
                    }
                    if(!$dat1['ratio10']){
                        $dat1['ratio10']=20;
                    }
                    if(!$dat1['ratio11']){
                        $dat1['ratio11']=15;
                    }
                    if(!$dat1['ratio12']){
                        $dat1['ratio12']=10;
                    }
                    foreach($arr1 as $k=>$v) {
                        foreach ($v as $m => $n) {
                            $arr2['branchid'] = $k;
                            $arr2['ensubjectid'] = $n;
                            $arr2['lastRecordTime'] = time();
                            $arr2['createTime'] = time();
                            $arr2['examinationid'] = $examinationid;
                            if (is_numeric($n)) {
                                $result=$arr9[$k][$n];
                            }else{
                                $result=$arr10[$k];
                            }
                            if($dat1['name1']){
                                $arr2['score1'] = $result * $dat1['ratio1'] / 100;
                                $arr2['name1'] = $dat1['name1'];
                            }
                            if($dat1['name2']){
                                $arr2['score2'] = $result * $dat1['ratio2'] / 100;
                                $arr2['name2'] = $dat1['name2'];
                            }
                            if($dat1['name3']){
                                $arr2['score3'] = $result * $dat1['ratio3'] / 100;
                                $arr2['name3'] = $dat1['name3'];
                            }
                            if($dat1['name4']){
                                $arr2['score4'] = $result * $dat1['ratio4'] / 100;
                                $arr2['name4'] = $dat1['name4'];
                            }
                            if($dat1['name5']){
                                $arr2['score5'] = $result * $dat1['ratio5'] / 100;
                                $arr2['name5'] = $dat1['name5'];
                            }
                            if($dat1['name6']){
                                $arr2['score6'] = $result * $dat1['ratio6'] / 100;
                                $arr2['name6'] = $dat1['name6'];
                            }
                            if($dat1['name7']){
                                $arr2['score7'] = $result * $dat1['ratio7'] / 100;
                                $arr2['name7'] = $dat1['name7'];
                            }
                            if($dat1['name8']){
                                $arr2['score8'] = $result * $dat1['ratio8'] / 100;
                                $arr2['name8'] = $dat1['name8'];
                            }
                            if($dat1['name9']){
                                $arr2['score9'] = $result * $dat1['ratio9'] / 100;
                                $arr2['name9'] = $dat1['name9'];
                            }
                            if($dat1['name10']){
                                $arr2['score10'] = $result * $dat1['ratio10'] / 100;
                                $arr2['name10'] = $dat1['name10'];
                            }
                            if($dat1['name11']){
                                $arr2['score11'] = $result * $dat1['ratio11'] / 100;
                                $arr2['name11'] = $dat1['name11'];
                            }
                            if($dat1['name12']){
                                $arr2['score12'] = $result * $dat1['ratio12'] / 100;
                                $arr2['name12'] = $dat1['name12'];
                            }
                            $arr2['scId']=$where1['scId'];
                            $arr3[] = $arr2;
                        }
                    }
                    $f11=$dao2->addAll($arr3);
                    if($f11===false){
                        $arrj['return']=false;
                    }else{
                        $arrj['return']=true;
                    }
                }else{
                    $arrj['return']=false;
                }
                $this->ajaxReturn($arrj);
            }elseif(I('get.typename')=='leveldel'){/******************清空***************/
                $examinationid=I('post.examinationid');
                $where['scId']=$getSession['scId'];
                $where['examinationid']=$examinationid;
                $dao2=M('examination_level');
                $f2=$dao2->where($where)->delete();
                if($f2===false){
                    $arrj['return']=false;
                }else{
                    $arrj['return']=true;
                }
                $this->ajaxReturn($arrj);
            }elseif(I('get.typename')=='levelupdate'){/******************修改***************/
                $da1=I('post.');
                $where['id']=$da1['id'];
                $where['scId']=$getSession['scId'];
                $data['enable']=$da1['enable'];
                $data['score1']=$da1['score1'];
                $data['score2']=$da1['score2'];
                $data['score3']=$da1['score3'];
                $data['score4']=$da1['score4'];
                $data['score5']=$da1['score5'];
                $data['score6']=$da1['score6'];
                $data['score7']=$da1['score7'];
                $data['score8']=$da1['score8'];
                $data['score9']=$da1['score9'];
                $data['score10']=$da1['score10'];
                $data['score11']=$da1['score11'];
                $data['score12']=$da1['score12'];
                $dao=M('examination_level');
                $f=$dao->where($where)->data($data)->save();
                if($f===false){
                    $arrj['return']=false;
                }else{
                    $arrj['return']=true;
                }
                $this->ajaxReturn($arrj);
            }elseif(I('get.typename')=='levelexport'){/*********************导出************************/
                $examinationid=I('get.examinationid');
                $dao2=M('examination_level');
                $where1['scId']=$getSession['scId'];
                $where['scId']=$getSession['scId'];
                $where['examinationid']=$examinationid;
                $f4=$dao2->where($where)->field('branchid as branch,ensubjectid as subject,score1,score2,score3,score4,score5,score6,score7,score8,score9,score10,score11,score12')->select();
                $f7=$dao2->where($where)->field('name1,name2,name3,name4,name5,name6,name7,name8,name9,name10,name11,name12')->limit(1)->select();
                $dao3=M('subject');
                $dao4=M('class_branch');
                $wherez['scId']=array('in','0,'.$scId);
                $f5=$dao3->where($where1)->field('subjectid,subjectname')->select();
                $f6=$dao4->where($wherez)->field('branchid,branch')->select();
                foreach($f5 as $k=>$v){
                    $arr4[$v['subjectid']]=$v['subjectname'];
                }
                foreach($f6 as $k=>$v){
                    $arr5[$v['branchid']]=$v['branch'];
                }
                foreach($f4 as $k=>$v){
                    $f4[$k]['branch']=$arr5[$v['branch']];
                    if(is_numeric($v['subject'])){
                        $f4[$k]['subject']=$arr4[$v['subject']];
                    }
                }
                $arr6['data']=$f4;
                $arr6['scorenamelist']=$f7['0'];
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
                $objPHPExcel->getActiveSheet()->SetCellValue('A1', '科类');
                $objPHPExcel->getActiveSheet()->SetCellValue('B1', '科目');
                /*$n=1;
                foreach($arr6['scorenamelist'] as $k=>$v){
                    $st='name'.$n;
                    if(){}
                }*/
                $objPHPExcel->getActiveSheet()->SetCellValue('C1', $arr6['scorenamelist']['name1']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D1', $arr6['scorenamelist']['name2']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E1', $arr6['scorenamelist']['name3']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F1', $arr6['scorenamelist']['name4']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G1', $arr6['scorenamelist']['name5']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H1', $arr6['scorenamelist']['name6']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I1', $arr6['scorenamelist']['name7']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J1', $arr6['scorenamelist']['name8']);
                $objPHPExcel->getActiveSheet()->SetCellValue('K1', $arr6['scorenamelist']['name9']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L1', $arr6['scorenamelist']['name10']);
                $objPHPExcel->getActiveSheet()->SetCellValue('M1', $arr6['scorenamelist']['name11']);
                $objPHPExcel->getActiveSheet()->SetCellValue('N1', $arr6['scorenamelist']['name12']);
                $i = 2;
                foreach ($arr6['data'] as $k => $v) {
                    $num = $i++;
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $num, $v['branch']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $num, $v['subject']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $num, $v['score1']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $num, $v['score2']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $num, $v['score3']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $num, $v['score4']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $num, $v['score5']);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $num, $v['score6']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $num, $v['score7']);
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $num, $v['score8']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $num, $v['score9']);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $num, $v['score10']);
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $num, $v['score11']);
                    $objPHPExcel->getActiveSheet()->setCellValue('N' . $num, $v['score12']);
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
        }elseif(I('get.type')=='report'){/****************考务报表打印****************/
            if(I('get.typename')=='arrange'){/***********************考场安排情况*********************/
                $dao=M('examination_room');
                $where['examinationid']=I('post.examinationid');
                //$where['status']=1;//修改，只显示座位布局完成的考场
                $scId=$getSession['scId'];
                $where['scId']=$scId;
                $f['data']=$dao->where($where)->field('id,name as room,seats,columns')->select();
                foreach($f['data'] as $k=>$v){
                    $f['data'][$k]['room']='第'.$v['room'].'考场';
                }
                $sqla="select count(*) as num,roomid from `mks_examination_arrange` where examinationid=".I('post.examinationid')." and scId=".$scId." group by roomid";
                $fa=$dao->query($sqla);
                foreach ($fa as $k=>$v){
                    $aza[$v['roomid']]=$v['num'];
                }
                foreach($f['data'] as $k=>$v){
                    $f['data'][$k]['seats']=$aza[$v['id']];
                }
                $f['examname']=$this->getExamName(I('post.examinationid'));

                $this->ajaxReturn($f);
            }elseif(I('get.typename')=='roomseat'){/***********************考场布局表*****************************/
                $dao1=M('examination_room');
                $dao2=M('examination_seat');
                $dao3=M('examination_arrange');
                $dao4=M('user');
                $dao5=M('examination_number');
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                $gradeid=I('post.gradeid');
                $where['examinationid']=$examinationid;
                $where['scId']=$scId;
                if(I('post.value')=='seat'){
                    $f2=$dao2->where($where)->field('seatRow,seatCol,seatNumber,roomid')->order('seatNumber asc')->select();
                }else{
                    $f2=$dao2->where($where)->field('seatRow,seatCol,seatNumber,roomid')->select();
                }

                $f3=$dao3->where($where)->field('userid,roomid,seatnumber')->select();
                $f5=$dao5->where($where)->field('userid,number')->select();
                $where['status']=1;//修改，只显示座位布局完成的考场
                $f1=$dao1->where($where)->field('id,name as room,room as roomName')->select();
                $where1['gradeId']=$gradeid;
                $where1['scId']=$scId;
                $f4=$dao4->where($where1)->field('id,name')->select();
                foreach($f4 as $k=>$v){
                    $arr4[$v['id']]=$v['name'];
                }
                foreach($f5 as $k=>$v){
                    $arr5[$v['userid']]=$v['number'];
                }
                foreach($f3 as $k=>$v){
                    $arr3[$v['roomid']][$v['seatnumber']]['name']=$arr4[$v['userid']];
                    $arr3[$v['roomid']][$v['seatnumber']]['number']=$arr5[$v['userid']];
                }
                if(I('post.value')=='roomseat'){//考场布局表(考场粘贴)
                    foreach($f2 as $k=>$v){
                        $arr['seatRow']=$v['seatRow'];
                        $arr['seatCol']=$v['seatCol'];
                        $arr['seatNumber']=$v['seatNumber'];
                        $arr['name']=$arr3[$v['roomid']][$v['seatNumber']]['name'];
                        $arr['number']=$arr3[$v['roomid']][$v['seatNumber']]['number'];
                        $arr1[$v['roomid']][]=$arr;
                    }
                }elseif(I('post.value')=='seat'){//座签表(座位粘贴)
                    foreach($f2 as $k=>$v){
                        //$arr['seatRow']=$v['seatRow'];
                        //$arr['seatCol']=$v['seatCol'];
                        $arr['seatNumber']=$v['seatNumber'];
                        $arr['name']=$arr3[$v['roomid']][$v['seatNumber']]['name'];
                        $arr['number']=$arr3[$v['roomid']][$v['seatNumber']]['number'];
                        $arr1[$v['roomid']][]=$arr;
                    }
                }
                foreach($f1 as $k=>$v){
                    $arr2[$v['id']]['room']='第'.$v['room'].'考场';
                    foreach($arr1[$v['id']] as $a=>$b){
                        $arr1[$v['id']][$a]['roomName']=$v['roomName'];
                    }
                    $arr2[$v['id']]['seat']=$arr1[$v['id']];
                }
                if(!$arr2){
                    $arr2=array();
                }
                $data['examname']=$this->getExamName($examinationid);
                $data['data']=array_values($arr2);
                $this->ajaxReturn($data);
            }elseif(I('get.typename')=='classroom'){/*****************分班考场安排表*******************/
                $scId=$getSession['scId'];
                $where1['examinationid']=I('post.examinationid');
                $where1['scId']=$scId;
                $where2['gradeId']=I('post.gradeid');
                $where2['scId']=$scId;
                $dao1=M('examination_arrange');
                $f1=$dao1->where($where1)->select();
                if(I('post.value')=='class'){
                    $field='u.className,u.serialNumber asc';
                }elseif(I('post.value')=='room'){
                    $field='a.roomid,a.seatnumber asc';
                }
                if($f1===null){
                    $f1=array();
                    $this->ajaxReturn($f1);
                }else {
                    $dao2 = M('');
                    $examinationid = I('post.examinationid');
                    $sql2 = "SELECT u.class,u.className,u.serialNumber,u.name,a.roomid,a.seatnumber,b.branch,n.number FROM `mks_examination_arrange` AS a,mks_user AS u,`mks_class_branch` AS b,`mks_examination_number` AS n WHERE a.userid=u.id AND a.branchid=b.branchid AND a.examinationid=n.examinationid AND a.userid=n.userid and u.scId=".$scId." AND a.examinationid=" . $examinationid . " order by ".$field;
                    $f2 = $dao2->query($sql2);
                    $dao3 = M('examination_room');
                    $f3 = $dao3->field('id,room as roomName,name as room')->where($where1)->select();

                    foreach ($f3 as $k => $v) {
                        $data1[$v['id']]['room'] ='第'.$v['room'].'考场';
                        $data1[$v['id']]['roomName'] =$v['roomName'];
                    }
                    if(I('post.value')=='class'){//分班考场安排表(发给班主任)
                        foreach ($f2 as $k => $v) {
                            $data2[$v['class']]['className'] = $v['className'].'班';
                            $data2[$v['class']]['data'][$k]['serialNumber'] = $v['serialNumber'];
                            $data2[$v['class']]['data'][$k]['name'] = $v['name'];
                            $data2[$v['class']]['data'][$k]['room'] = $data1[$v['roomid']]['roomName'];
                            //$data2[$v['class']]['data'][$k]['seatnumber'] = $v['seatnumber'];
                            $data2[$v['class']]['data'][$k]['number'] = $v['number'];
                            //$data2[$v['class']]['data'][$k]['key'] = $k+1;
                            $data2[$v['class']]['data']=array_values($data2[$v['class']]['data']);
                        }
                        foreach($data2 as $k=>$v){
                            foreach($v['data'] as $a=>$b){
                                $data2[$k]['data'][$a]['key'] = $a+1;
                            }
                        }
                    }elseif(I('post.value')=='room'){//考试安排表(发给监考员)
                        $i=0;
                        foreach ($f2 as $k => $v) {
                            $data2[$v['class']]['room'] = $data1[$v['roomid']]['room'];
                            $data2[$v['class']]['value'][$k]['serialNumber'] = $v['serialNumber'];
                            $data2[$v['class']]['value'][$k]['name'] = $v['name'];
                            $data2[$v['class']]['value'][$k]['className'] = $v['className'];
                            $data2[$v['class']]['value'][$k]['seatnumber'] = $v['seatnumber'];
                            $data2[$v['class']]['value'][$k]['number'] = $v['number'];
                            $data2[$v['class']]['value'][$k]['roomName'] = $data1[$v['roomid']]['roomName'];
                            $data2[$v['class']]['value']=array_values($data2[$v['class']]['value']);
                        }
                    }
                    $data['examname']=$this->getExamName($examinationid);
                    if(!$data2){
                        $data2=array();
                    }
                    $data['data']=array_values($data2);
                    $this->ajaxReturn($data);
                }
            }elseif(I('get.typename')=='roomcard'){/**********考场台卡***********/
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                $where['examinationid']=$examinationid;
                $where['scId']=$scId;
                //$where['status']=1;
                $dao=M('examination_room');
                $f=$dao->where($where)->field('name,room,seats')->select();
                foreach($f as $k=>$v){
                    $f[$k]['name']='第'.$v['name'].'考场';
                }
                $data['examname']=$this->getExamName($examinationid);
                if(!$f){
                    $f=array();
                }
                $data['room']=$f;
                $this->ajaxReturn($data);
            }elseif(I('get.typename')=='invigilatorsub'){/*************考签到****************/
                $dao1=M();
                $scId=$getSession['scId'];
                $examinationid=I('post.examinationid');
                $sqla="SELECT u.id,u.`name`,j.`subjectname`,b.`branch`,t.starttime,t.endtime,r.room,a.seatnumber AS serialNumber,t.id,r.id AS roomid
                      FROM `mks_examination_student` AS s,`mks_examination_subject` AS t,mks_user AS u,mks_subject AS j,`mks_class_branch` AS b,`mks_examination_arrange` AS a,`mks_examination_room` AS r
                      WHERE s.`branch`=b.`branchid` AND s.participate='是' AND j.`subjectid`=t.`subject` AND u.`id`=s.`userid` AND a.examinationid=s.examinationid AND r.examinationid=s.examinationid AND a.roomid=r.id AND s.userid=a.userid AND s.examinationid=".$examinationid." AND s.scId=".$scId." AND s.`examinationid`=t.`examinationid` AND s.`branch`=t.`branchid`
                      ORDER BY b.branch,j.subjectname,r.id,a.seatnumber";
                $fa=$dao1->query($sqla);
                $sqlb="SELECT s.ensubjectid,s.room,u.name FROM `mks_examination_supervision` AS s,mks_user AS u WHERE s.examinationid=".$examinationid." AND s.scId=".$scId." AND s.userid=u.id";
                $fb=$dao1->query($sqlb);
                foreach($fb as $k=>$v){
                    $aarz[$v['ensubjectid']][$v['room']][]=$v['name'];
                }
                foreach($fa as $k=>$v){
                    $time1=explode(':',$v['starttime']);
                    $time2=explode(':',$v['endtime']);
                    $time=$v['starttime']."-".$v['endtime'];
                    $arr[$v['branch'].'-'.$v['subjectname'].' '.$time][$v['room']][implode(',',$aarz[$v['id']][$v['roomid']])][]=$v;
                }
                foreach($arr as $k=>$v){
                    foreach($v as $a=>$b){
                        foreach($b as $c=>$d){
                            foreach($d as $e=>$f){
                                $arr1['name']=$f['name'];
                                $arr1['serialNumber']=$f['serialNumber'];
                                $arra['bsname']=$k;
                                $arra['room']=$a;
                                $arra['teacher']=$c;
                                $arra['list'][]=$arr1;
                            }
                        }
                        $arrb[]=$arra;
                        $arra=array();
                    }
                }
                if(!$arrb){
                    $arrb=array();
                }
                $f1['data']=$arrb;
                $f1['examname']=$this->getExamName($examinationid);
                $this->ajaxReturn($f1);
                /*exit;
                $sql1="SELECT u.id,u.`name`,u.`serialNumber`,j.`subjectname`,b.`branch`,t.starttime,t.endtime FROM `mks_examination_student` AS s,`mks_examination_subject` AS t,mks_user AS u,mks_subject AS j,`mks_class_branch` AS b WHERE s.`branch`=b.`branchid` and s.participate='是' AND j.`subjectid`=t.`subject` AND u.`id`=s.`userid` AND s.examinationid=".$examinationid." AND s.scId=".$scId." AND s.`examinationid`=t.`examinationid` AND s.`branch`=t.`branchid` order by u.class,u.serialNumber";
                $f=$dao1->query($sql1);
                $sql2="SELECT a.userid,a.seatnumber,r.room FROM `mks_examination_arrange` AS a,mks_examination_room as r WHERE r.id=a.roomid and a.scId=".$scId." AND a.examinationid=".$examinationid;
                $f2=$dao1->query($sql2);
                foreach($f2 as $k=>$v){
                    $arrj[$v['userid']]['seatnumber']=$v['seatnumber'];
                    $arrj[$v['userid']]['roomName']=$v['room'];
                }
                foreach($f as $k=>$v){
                    $arr1['name']=$v['name'];
                    $arr1['serialNumber']=$arrj[$v['id']]['seatnumber'];
                    $arr1['roomName']=$arrj[$v['id']]['roomName'];
                    $time1=explode(':',$v['starttime']);
                    $time2=explode(':',$v['endtime']);
                    $time=$v['starttime']."-".$v['endtime'];
                    $arr[$v['branch'].'-'.$v['subjectname'].' '.$time][]=$arr1;
                }
                foreach($arr as $k=>$v){
                    foreach($v as $a=>$b){
                        $arra['bsname']=$k;
                        $arra['list'][]=$b;
                    }
                    $arrb[]=$arra;
                    $arra=array();
                }
                if(!$arrb){
                    $arrb=array();
                }
                $f1['data']=$arrb;
                $f1['examname']=$this->getExamName($examinationid);
                $this->ajaxReturn($f1);*/
            }elseif(I('get.typename')=='invigilatorsign'){/**************监考签到******************/
            /*    $dao1=M();
                $scId=$getSession['scId'];
                $subjectid=I('post.subjectid');
                $examinationid=I('post.examinationid');
                $sql1="SELECT s.`room`,u.`name` FROM mks_examination_subject AS e,`mks_examination_supervision` AS s,mks_user AS u WHERE u.`id`=s.`userid` AND e.`id`=s.`ensubjectid` AND s.scId=".$scId." AND e.`subject`=".$subjectid." AND s.examinationid=".$examinationid;
                $f1['data']=$dao1->query($sql1);
                $sql2="SELECT id,name as room FROM `mks_examination_room` WHERE examinationid=".$examinationid." AND scId=".$scId;
                $f2=$dao1->query($sql2);
                foreach($f2 as $k=>$v){
                    $data[$v['id']]='第'.$v['room'].'考场';
                }
                foreach($f1['data'] as $k=>$v){
                    if(is_numeric($v['room'])){
                        $f1['data'][$k]['room']=$data[$v['room']];
                    }
                }
                $f1['examname']=$this->getExamName($examinationid);
                $this->ajaxReturn($f1);*/
            }elseif(I('get.typename')=='kaowu'){/*************考务会签到****************/
                $dao1=M();
                $scId=$getSession['scId'];
                $examinationid=I('post.examinationid');
                $sql1="SELECT u.`name`,u.`phone` FROM `mks_examination_supervision` AS s,`mks_user` AS u WHERE s.`examinationid`=".$examinationid." and s.scId=".$scId." AND s.`userid`=u.`id` GROUP BY s.`userid`";
                $arr=$dao1->query($sql1);
                if(!$arr){
                    $arr=array();
                }
                $f1['data']=$arr;
                $f1['examname']=$this->getExamName($examinationid);
                $this->ajaxReturn($f1);
            }elseif(I('get.typename')=='jiankaotongji'){/******************************监考任务安排统计表******************/
                $dao1=M();
                $examinationid=I('post.examinationid');
                $scId=$getSession['scId'];
                $sql1="SELECT u.name,s.ensubjectid,s.`room`,s.`userid`,t.`date`,t.`starttime`,t.`endtime` FROM `mks_examination_supervision` AS s,`mks_examination_subject` AS t,mks_user as u WHERE u.id=s.userid AND s.examinationid=".$examinationid." and s.scId=".$scId." AND s.`ensubjectid`=t.`id` GROUP BY t.`date`,t.`starttime`,t.`endtime`,s.userid order by s.id";
                $f1=$dao1->query($sql1);
                $invigilator=0;
                $visits=0;
                $totalinspection=0;
                foreach($f1 as $k=>$v){
                    $data[$v['userid']]['name']=$v['name'];
                    if($data[$v['userid']]['time']){
                        $data[$v['userid']]['time']+=strtotime($v['date']." ".$v['endtime'])-strtotime($v['date']." ".$v['starttime']);
                    }else{
                        $data[$v['userid']]['time']=strtotime($v['date']." ".$v['endtime'])-strtotime($v['date']." ".$v['starttime']);
                    }
                    if($v['room']=='巡考'){
                        if($data[$v['userid']]['visits']){
                            $data[$v['userid']]['visits']++;
                        }else{
                            $data[$v['userid']]['visits']=1;
                        }
                    }elseif($v['room']=='总巡考'){
                        if($data[$v['userid']]['totalinspection']){
                            $data[$v['userid']]['totalinspection']++;
                        }else{
                            $data[$v['userid']]['totalinspection']=1;
                        }
                    }elseif(is_numeric($v['room'])){
                        if($data[$v['userid']]['invigilator']){
                            $data[$v['userid']]['invigilator']++;
                        }else{
                            $data[$v['userid']]['invigilator']=1;
                        }
                    }
                }
                foreach($data as $k=>$v){
                    $arrn['invigilator']=$v['invigilator'];
                    $arrn['visits']=$v['visits'];
                    $arrn['totalinspection']=$v['totalinspection'];
                    $arrn['all']=$arrn['invigilator']+$arrn['visits']+$arrn['totalinspection'];
                    $arrn['time']=$v['time']/3600;
                    $arrn['timeaverage']=$arrn['time']/$arrn['all'];
                    $arrn['name']=$v['name'];
                    $data2['data'][]=$arrn;
                }
                if(!$data2['data']){
                    $data2['data']=array();
                }
                $data2['examname']=$this->getExamName($examinationid);
                $this->ajaxReturn($data2);
            }elseif(I('get.typename')=='xueshengmingdan'){/*************上报学生名单和不上报学生名单*****************/
                $dao1=M();
                $examinationid=I('post.examinationid');
                $reported=I('post.reported');
                $scId=$getSession['scId'];
                $sql1="SELECT n.`number`,u.`name`,u.`sex`,u.hklx,b.branch,u.id FROM `mks_examination_student` AS s,`mks_examination_number` AS n,mks_user AS u,mks_class_branch AS b WHERE s.branch=b.branchid and s.`userid`=n.`userid` AND s.`userid`=u.`id` AND s.`examinationid`=".$examinationid." AND n.`examinationid`=".$examinationid." AND s.`reported`='".$reported."' and s.scId=".$scId;
                $f1=$dao1->query($sql1);
                if(!$f1['0']['name']){
                    $data2['examname']=$this->getExamName($examinationid);
                    $data2['data']=array();
                    $this->ajaxReturn($data2);
                    exit;
                }
                $dao2=M('school');
                $where['scId']=$scId;
                $f2=$dao2->where($where)->field('scName')->select();
                $sql3="SELECT SUM(results) AS results,userid FROM `mks_examination_results` WHERE examinationid=".$examinationid." AND scId=".$scId." GROUP BY userid";
                $f3=$dao1->query($sql3);
                foreach($f3 as $k=>$v){
                    $data1[$v['userid']]=$v['results'];
                }
                foreach($f1 as $k=>$v){
                    $f1[$k]['scool']=$f2['0']['scName'];
                    $f1[$k]['results']=$data1[$v['id']];
                    unset($f1[$k]['id']);
                }
                $data2['data']=$f1;
                $data2['examname']=$this->getExamName($examinationid);
                $this->ajaxReturn($data2);
            }elseif(I('get.typename')=='bucankaoxuesheng'){/*************不参考学生名单*****************/
                $dao1=M();
                $scId=$getSession['scId'];
                $examinationid=I('post.examinationid');
                $sql1="SELECT u.`className`,u.`name` FROM `mks_examination_student` AS s,mks_user AS u WHERE s.`participate`='否' AND s.`userid`=u.`id` AND s.`examinationid`=".$examinationid." AND s.`scId`=".$scId;
                $f1['data']=$dao1->query($sql1);
                $f1['examname']=$this->getExamName($examinationid);
                $this->ajaxReturn($f1);
            }elseif(I('get.typename')=='jiankaozuoweika'){/*************监考座位卡*****************/
                $dao1=M();
                $scId=$getSession['scId'];
                $examinationid=I('post.examinationid');
                $sql1="SELECT u.`name` FROM `mks_examination_supervision` AS s,mks_user AS u WHERE s.`userid`=u.`id` AND s.`examinationid`=".$examinationid." AND s.`scId`=".$scId." GROUP BY s.`userid`";
                $f1['data']=$dao1->query($sql1);
                $f1['examname']=$this->getExamName($examinationid);
                $this->ajaxReturn($f1);
            }elseif(I('get.typename')=='zongmingce'){/*************总名册*****************/
                $dao=M();
                $examinationid=I('post.examinationid');
                $gradeid=I('post.gradeid');
                $scId=$getSession['scId'];
                $sql1="SELECT n.`number`,u.`grade`,u.`className`,u.`name`,r.`name` as room,a.`seatnumber`,u.`idCard`,b.`branch`,u.class,u.id FROM `mks_examination_student` AS s,mks_user AS u,mks_examination_number AS n,mks_class_branch AS b,`mks_examination_arrange` AS a,mks_examination_room AS r WHERE s.`examinationid`=n.`examinationid` AND s.`examinationid`=a.`examinationid` AND s.`userid`=a.`userid` AND a.`roomid`=r.`id` AND s.`participate`='是' AND s.`userid`=u.`id` AND s.`userid`=n.`userid` AND s.`branch`=b.`branchid` AND s.`examinationid`=".$examinationid." and s.scId=".$scId;
                $sql2="SELECT s.`subjectId`,s.`classId`,s.`techerName`,t.`branchid` FROM `mks_jw_schedule` AS s,`mks_examination_subject` AS t WHERE s.scId=".$scId." AND s.gradeId=1 AND t.`examinationid`=".$examinationid." AND t.`subject`=s.`subjectId`";
                $sql3="SELECT u.`className`,u.`class`,t.`subject`,j.`subjectname` FROM `mks_examination_subject` AS t,`mks_examination_student` AS s,mks_user AS u,mks_subject AS j WHERE j.`subjectid`=t.`subject` AND t.`examinationid`=s.`examinationid` AND s.`examinationid`=".$examinationid." AND s.`userid`=u.`id` AND s.`branch`=t.`branchid` GROUP BY u.`class`,t.`subject`";
                $sql4="select scName from mks_school where scId=".$scId;
                $sql5="SELECT r.userId,r.regNumber FROM `mks_school_rollinfo` AS r,mks_user AS u WHERE r.scId=".$scId." AND u.gradeId=".$gradeid." AND u.`grade`=r.`grade`";
                $f1=$dao->query($sql1);
                foreach($f1 as $k=>$v){
                    $f1[$k]['room']='第'.$v['room'].'考场';
                }
                $f2=$dao->query($sql2);
                $f3=$dao->query($sql3);
                $f4=$dao->query($sql4);
                $f5=$dao->query($sql5);
                foreach($f5 as $k=>$v){
                    $arr6[$v['userId']]=$v['regNumber'];
                }
                foreach($f3 as $k=>$v){
                    $arr5[$v['class']][$v['subject']]['subject']=$v['subjectname'];
                }
                //print_r($f1);
                //exit;
                //print_r($f1);
                //echo "<br>";
                //print_r($f2);
                foreach($f2 as $k=>$v){
                    $arr2[$v['classId']][$v['subjectId']]=$v['techerName'];
                }
                foreach($arr5 as $k=>$v){
                    foreach($v as $a=>$b){
                        $arr5[$k][$a]['teacher']=$arr2[$k][$a];
                    }
                }
                foreach($f1 as $k=>$v){
                    $f1[$k]['scName']=$f4['0']['scName'];
                    $arr4[$v['class']]['subjectlist']=array_values($arr5[$v['class']]);
                    unset($f1[$k]['class']);
                    $arr4[$v['class']]['students'][]=$f1[$k];
                }

                foreach($arr4 as $k=>$v){
                    foreach($v['students'] as $a=>$b){
                        $arr4[$k]['students'][$a]['regNumber']=$arr6[$b['id']];
                        unset($arr4[$k]['students'][$a]['id']);
                    }
                }
                $data=array_values($arr4);
                $fn['data']=$data;
                $fn['examname']=$this->getExamName($examinationid);
                $this->ajaxReturn($fn);
            }
        }
    }
    /**********************历次考试*********************/
    public function previousexam(){
        $getSession = $this->get_session('loginCheck',false);
        if(I('get.type')=='exam'){
            if(I('get.typename')=='findgrade'){/***********************年级列表****************/
                $where['scId']=$getSession['scId'];
                $userId=$getSession['userId'];
                $roleId=$getSession['roleId'];
                if($roleId==$this::$teacherRoleId){
                    $where['userId']=$userId;
                }
                $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
                $dao=M('grade');
                $f=$dao->where($where)->field('gradeid,name')->order('name asc')->select();
                foreach($f as $k=>$v){
                    $key=$v['name']-1;
                    $f[$k]['name']=$arrgrade[$key];
                }
                if(!$f){
                    $f=array();
                }
                $this->ajaxReturn($f);
            }elseif(I('get.typename')=='findexam') {/*******************考试查询*****************/
                $where['gradeid'] = I('post.gradeid');
                $where['scId'] =$getSession['scId'];
                $dao = M('examination');
                $f = $dao->where($where)->field('examinationid,examination,starttime,endtime,release')->order('examinationid asc')->select();
                foreach ($f as $k => $v) {
                    $data[$k]['examinationid'] = $v['examinationid'];
                    $data[$k]['examination'] = $v['examination'];
                    $data[$k]['date'] = $v['starttime'] . "-----" . $v['endtime'];
                    $data[$k]['release'] = $v['release'];
                }
                if(!$data){
                    $data=array();
                }
                $this->ajaxReturn($data);
            }elseif(I('get.typename')=='examup'){/**************修改考试名****************/
                $where['examinationid'] = I('post.examinationid');
                $data['examination'] = I('post.examination');
                $where['scId'] =$getSession['scId'];
                $ttaa=$this->kaoshichongming(null,$data['examination'],$where['examinationid']);
                if(!$ttaa){
                    $arrj['return']=false;
                    $arrj['msg']='该年级存在相同的考试名!';
                    $this->ajaxReturn($arrj);
                }
                $data['lastRecordTime'] = time();
                $dao = M('examination');
                $f= $dao->where($where)->data($data)->save();
                if($f===false){
                    $arrj['return']=false;
                    $arrj['msg']='修改失败!';
                }else{
                    $arrj['return']=true;
                }
                $this->ajaxReturn($arrj);
            }elseif(I('get.typename')=='examdel'){/*******************删除考试*********************/
                $where['examinationid'] = array('in',I('post.examinationid'));
                $where['scId'] =$getSession['scId'];
                $dao=M();
                M()->startTrans();
                $f2=$dao->where($where)->table('mks_examination_arrange')->delete();
                $f3=$dao->where($where)->table('mks_examination_level')->delete();
                $f4=$dao->where($where)->table('mks_examination_number')->delete();
                $f5=$dao->where($where)->table('mks_examination_rate')->delete();
                $f6=$dao->where($where)->table('mks_examination_results')->delete();
                $f13=$dao->where($where)->table('mks_examination_seat')->delete();

                $f8=$dao->where($where)->table('mks_examination_score')->delete();
                $f9=$dao->where($where)->table('mks_examination_student')->delete();
                $f10=$dao->where($where)->table('mks_examination_subject')->delete();
                $f11=$dao->where($where)->table('mks_examination_supervision')->delete();
                $f12=$dao->where($where)->table('mks_examination_teacher')->delete();
                $f7=$dao->where($where)->table('mks_examination_room')->delete();
                $f1=$dao->where($where)->table('mks_examination')->delete();
                //$f12=$dao->table()->delete();
                if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false || $f6===false || $f7===false || $f8===false || $f9===false || $f10===false || $f11===false || $f12===false || $f13===false){
                    M()->rollback();
                    $a['return']=false;
                }else{
                    M()->commit();
                    $a['return']=true;
                }
                $this->ajaxReturn($a);
            }
        }
    }

    public function cankao(){/*******************学生参考确认********************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $roleId=$getSession['roleId'];
        //$userid=124;//$getSession['userId'];
        $userid=$getSession['userId'];
        if(I('get.type')=='grade'){/*************************班级管理年级列表************************/
            $where['scId']=$scId;
            $dao=M('grade');
            $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
            $f=array();
            if($roleId==$this::$teacherRoleId){
                $sql2="SELECT g.`gradeid`,g.`name` FROM `mks_class` AS c,mks_grade AS g WHERE c.`userid`=".$userid." and c.scId=".$scId." AND c.`grade`=g.`gradeid`";
                $f2=$dao->query($sql2);
                foreach($f2 as $k=>$v){
                    $f[$v['gradeid']]=$v;
                }
                $f=array_values($f);
            }else{
                $f=$dao->where($where)->field('gradeid,name')->select();
            }
            foreach($f as $k=>$v){
                $key=$v['name']-1;
                $f[$k]['name']=$arrgrade[$key];
            }
            $this->ajaxReturn($f);
        }elseif(I('get.type')=='class'){/******************班级管理班级列表***********************/
            $where['scId']=$scId;
            $where['grade']=I('post.gradeid');
            $dao=M('class');
            if($roleId==$this::$teacherRoleId){
                $where['userid']=$userid;
            }
            $f=$dao->where($where)->field('classid,classname')->select();
            if(!$f){
                $f=array();
            }
            $this->ajaxReturn($f);
        }
        if(I('get.type')=='gradeGd'){/*************************年级管理年级列表************************/
            $where['scId']=$scId;
            $dao=M('grade');
            $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
            $f=array();
            if($roleId==$this::$teacherRoleId){
                $sql2="SELECT g.`gradeid`,g.`name` FROM mks_grade AS g WHERE g.`userid`=".$userid." and g.scId=".$scId;
                $f2=$dao->query($sql2);
                foreach($f2 as $k=>$v){
                    $f[$v['gradeid']]=$v;
                }
                $f=array_values($f);
            }else{
                $f=$dao->where($where)->field('gradeid,name')->select();
            }
            foreach($f as $k=>$v){
                $key=$v['name']-1;
                $f[$k]['name']=$arrgrade[$key];
            }
            $this->ajaxReturn($f);
        }elseif(I('get.type')=='classGd'){/******************年级管理班级列表***********************/
            $where['scId']=$scId;
            $where['grade']=I('post.gradeid');
            $dao=M('class');
            $f=$dao->where($where)->field('classid,classname')->select();
            if(empty($f)){
                $f=array();
            }
            $this->ajaxReturn($f);
        }elseif(I('get.type')=='exam'){/***************考试列表*****************/
            $gradeid=I('post.gradeid');
            $classid=I('post.classid');
            $dao1=M('examination');
            $where1['scId']=$scId;
            $where1['gradeid']=$gradeid;
            if($roleId==$this::$teacherRoleId){
                $where1['headmaster']=1;
            }
            $f1=$dao1->where($where1)->field('examinationid,examination,class,starttime')->select();//修改，
            $arr2=array();
            $time1=time();
            foreach($f1 as $k=>$v){//修改，
                $time2=strtotime($v['starttime']);
                if(count(array_intersect($classid,explode(',',$v['class'])))&&$time1<$time2){
                    unset($v['class']);
                    unset($v['starttime']);
                    $arr2[]=$v;
                }
            }
            $this->ajaxReturn($arr2);
        }elseif(I('get.type')=='student'){//增加字段reported 是否上报
            $dao=M();
            $classid=I('post.classid');
            $classid=implode(',',$classid);
            $examinationid=I('post.examinationid');
            $screen=I('post.screen');
            $field=I('post.field');
            $order=I("post.order");
            if($order=='descending'){
                $order='desc';
            }else{
                $order='asc';
            }
            $limit=I("post.limit");
            $page=I('post.page');
            if(!$page){
                $page=1;
            }
            if(!$limit){
                $limit=10;
            }
            $limitstart=($page-1)*$limit;
            if(!$field){
                $field='u.id';
            }else{
                if($field=='participate' || $field=='reported'){
                    $field='s.'.$field;
                }else{
                    $field='u.'.$field;
                }
            }
            if($screen){
                $st="and (u.className like '%".$screen."%' or u.serialNumber like '%".$screen."%' or u.sex like '%".$screen."%' or u.name like '%".$screen."%' or s.`participate` like '%".$screen."%' or s.`reported` like '%".$screen."%')";
            }
            $roleId=parent::$studentRoleId;
            $sql="select s.id,u.`className`,u.serialNumber,u.`sex`,u.`name`,s.`participate`,s.reported from `mks_examination_student` as s,mks_user as u where s.scId=".$scId." and u.`class` in(".$classid.") and u.`roleId`=".$roleId." and s.userid=u.`id` and s.examinationid=".$examinationid." $st order by ".$field." ".$order." limit ".$limitstart.",".$limit;
            $sql2="select count(s.id) from `mks_examination_student` as s,mks_user as u where s.scId=".$scId." and u.`class` in(".$classid.") and u.`roleId`=".$roleId." and s.userid=u.`id` and s.examinationid=".$examinationid." $st";
            //exit;
            $data=$dao->query($sql);
            $data2=$dao->query($sql2);
            $pageall=ceil($data2['0']['count(s.id)']/$limit);
            foreach($data as $k=>$v){
                $data[$k]['id']=$v['id'];
                $data[$k]['className']=$v['className'];
                $data[$k]['serialNumber']=$v['serialNumber'];
                $data[$k]['sex']=$v['sex'];
                $data[$k]['name']=$v['name'];
                $data[$k]['participate']=$v['participate'];
                $data[$k]['reported']=$v['reported'];
                $data1['data'][$k]=$data[$k];
            }
            if($data){
                $data1['page']['page']=$page;
                $data1['page']['pageall']=$pageall;
                $data1['page']['count']=$data2['0']['count(s.id)'];
            }elseif(empty($data)){
                $data1['data']=null;
            }
            $this->ajaxReturn($data1);
        }elseif(I('get.type')=='save'){/*******************班级管理修改参考*********************/ //增加参数reported 是否上报
            $data=I('post.data');
            $dao=M('examination_student');
            $arr['return']=true;
            if($roleId==$this::$teacherRoleId){
                $sql2="SELECT COUNT(classid) AS num FROM mks_class WHERE userid=".$userid;
                $f2=$dao->query($sql2);
                if($f2['0']['num']){
                    $data1['headmaster']='是';
                }
            }
            foreach($data as $k=>$v){
                $where['id']=$v['id'];
                $data1['participate']=$v['participate'];
                $data1['reported']=$v['reported'];
                $f=$dao->where($where)->data($data1)->save();
                if($f===false){
                    $arr['return']=false;
                    break;
                }
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='saveGd'){/*******************年级管理修改参考*********************/ //增加参数reported 是否上报
            $data=I('post.data');
            $dao=M('examination_student');
            $arr['return']=true;
            foreach($data as $k=>$v){
                $where['id']=$v['id'];
                $data1['participate']=$v['participate'];
                $data1['headmaster']="是";
                $f=$dao->where($where)->data($data1)->save();
                if($f===false){
                    $arr['return']=false;
                    break;
                }
            }
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='gebancankao'){/********************年级管理各班参考确认*********************/
            $dao=M();
            $classid=I('post.classid');
            $classid=implode(',',$classid);
            $examinationid=I('post.examinationid');
            $roleId=parent::$studentRoleId;
            $sql="SELECT b.branch,c.classname,COUNT(IF(s.participate='是',TRUE,NULL )) AS participate,c.classid FROM `mks_examination_student` AS s,mks_user AS u,mks_class AS c,mks_class_branch AS b WHERE c.branch=b.branchid AND u.class=c.classid AND s.scId=".$scId." AND u.`class` IN(".$classid.") AND u.`roleId`=".$roleId." AND s.userid=u.`id` AND s.examinationid=".$examinationid." GROUP BY u.class";
            $sql2="SELECT c.classid,u.name FROM mks_class AS c,mks_user AS u WHERE c.classid IN(".$classid.") AND c.userid=u.id AND c.scId=".$scId;
            $sql3="SELECT class,COUNT(id) as num FROM mks_user WHERE class IN(".$classid.") AND scId=".$scId." AND roleId=".$roleId." AND state=1 GROUP BY class";
            $f=$dao->query($sql);
            $f2=$dao->query($sql2);
            $f3=$dao->query($sql3);
            foreach($f2 as $k=>$v){
                $arr2[$v['classid']]=$v['name'];
            }
            foreach($f3 as $k=>$v){
                $arr3[$v['class']]=$v['num'];
            }
            foreach($f as $k=>$v){
                $f[$k]['total']=$arr3[$v['classid']];
                $f[$k]['unparticipate']=$arr3[$v['classid']]-$v['participate'];
                $f[$k]['headmaster']=$arr2[$v['classid']];
            }
            $this->ajaxReturn($f);
        }
    }
}