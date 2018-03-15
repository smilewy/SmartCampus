<?php
namespace Home\Controller;
use Think\Controller;
ob_end_clean();
class TransactionController extends Base {
    private function sortArrByOneField($array,$find=false){/*************对数组进行模糊查询************/
        if($find!==false){
            foreach($array as $k=>$v){
                foreach($v as $a=>$b){
                    if(!is_array($b)){
                        if($a!='gradeid'&&$a!='id'&&$a!='userid'){
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
    private function getSerialNumber($class){
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql="SELECT MAX(serialNumber) as serialNumber FROM mks_user WHERE scId=".$scId." AND class=".$class;
        $f=$dao->query($sql);
        $st=$f['0']['serialNumber']+1;
        return $st;
    }
    private function getUser($userid,$gradeid,$classid){//获取对应学生姓名年级班级
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $sql1="SELECT `name`,sex FROM mks_user WHERE id=".$userid."  AND scId=".$scId;
        $sql2="SELECT `classname` FROM mks_class WHERE classid=".$classid." AND scId=".$scId;
        $sql3="SELECT `name` FROM mks_grade WHERE gradeid=".$gradeid." AND scId=".$scId;
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $dao=M();
        $f1=$dao->query($sql1);
        $f2=$dao->query($sql2);
        $f3=$dao->query($sql3);
        $ak=explode('(',$f1['0']['name']);
        $data['name']=$ak['0'];
        $data['sex']=$f1['0']['sex'];
        $data['class']=$f2['0']['classname'];
        $data['grade']=$arrgrade[$f3['0']['name']-1];
        return $data;
    }
    private function getGradeClassId($userid){//获取学生年级班级id
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql="select class as classid,gradeId as gradeid from mks_user where id=".$userid." and scId=".$scId;
        $f=$dao->query($sql);
        if($f===false){
            $arr=false;
        }else{
            $arr['gradeid']=$f['0']['gradeid'];
            $arr['classid']=$f['0']['classid'];
        }
        return $arr;
    }
    private function getGrade(){/***************获取年级列表****************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql="SELECT gradeid,name FROM `mks_grade` WHERE scId=".$scId." order by name asc";
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $f=$dao->query($sql);
        foreach($f as $k=>$v){
            $f[$k]['name']=$arrgrade[$v['name']-1];
        }
        return($f);
    }
    private function getClass($grade){/***************获取班级列表****************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql="SELECT classid,classname FROM `mks_class` WHERE scId=".$scId." and grade=".$grade." order by classname asc";
        $f=$dao->query($sql);
        return($f);
    }
    private function randomkeys($length){/****************根据随机数和时间戳生成文件名***************/
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $key='';
        for($i=0;$i<$length;$i++) {
            $key .= $pattern{mt_rand(0,35)};    //生成php随机数
        }
        return $key;
    }
    private function getStudents($classid,$type=null,$field,$order,$find){/****************获取学生列表******************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $roId=$this::$studentRoleId;
        $dao=M();
        if($type=='复学'){
            $str="and r.isLeave=1";
        }else{
            $str="and r.isAtSchool='是'";
        }
        if(!$field){
            $field='u.grade,u.className';
        }elseif($field=='name' || $field=='sex' || $field=='studentCode'){
            $field='r.'.$field;
        }elseif($field=='certificate' || $field=='idCard') {
            $field = 'i.' . $field;
        }elseif($field=='gradeName'||$field=='hkAddress'){
            $field = 'u.' . $field;
        }elseif($field=='className'){
            $field='gradeName';
        }
        if($order!='ascending'){
            $order='desc';
        }else{
            $order='asc';
        }
        if($find){
            $st="and (u.`grade` like '%".$find."%' or u.className like '%".$find."%' or r.name like '%".$find."%' or r.sex like '%".$find."%' or r.studentCode like '%".$find."%' or i.certificate like '%".$find."%' or i.idCard like '%".$find."%' or u.hkAddress like '%".$find."%')";
        }
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $sql="SELECT u.id as userid,u.`gradeId` as gradeid,u.`class` as classid,u.`grade` as gradeName,u.`className`,u.`name`,r.`sex`,r.studentCode,i.certificate,i.`idCard`,u.hkAddress FROM `mks_school_rollinfo` AS r,`mks_user` AS u,`mks_student_info` AS i WHERE u.`class` IN(".$classid.") ".$str." AND u.scId=".$scId." AND u.roleId=".$roId." AND u.id=r.`userId` AND u.id=i.`userId` ".$st." order by ".$field." ".$order;
        $arr=$dao->query($sql);
        foreach($arr as $k=>$v){
            $a=$v['gradeName']-1;
            $arr[$k]['gradeName']=$arrgrade[$a];
        }
        return $arr;
    }
    private function getWord($name,$sex,$grade,$class,$type,$reason){
        /*vendor("PHPWord.PHPWord");

// New Word Document
        $PHPWord = new \PHPWord();

// New portrait section
        $section = $PHPWord->createSection();
        $str1='      原班主任：                                                                   新班主任：';
        $str2='                             年      月      日                                                            年     月      日';
// Define table style arrays
        $styleTable = array('borderSize'=>6, 'borderColor'=>'000000', 'alignMent' => 'center');
        //$styleFirstRow = array('borderBottomSize'=>18, 'borderBottomColor'=>'0000FF', 'bgColor'=>'66BBFF');
        $fontStyle = array('color'=>'000000', 'size'=>22, 'bold'=>true);
        $paragraphStyle = array('align'=>'center');
        $PHPWord->addFontStyle('myOwnStyle', $fontStyle);
        $text = $section->addText('学生异动确认单', 'myOwnStyle',$paragraphStyle);
        $section->addTextBreak(3);
// Define cell style arrays
        $styleCell = array('valign'=>'center','align'=>'center');
        $styleCellBTLR = array('valign'=>'center', 'textDirection'=>\PHPWord_Style_Cell::TEXT_DIR_BTLR);

// Define font style for first row
        $fontStyle = array('bold'=>false,'size'=>12);

// Add table style
        $PHPWord->addTableStyle('myOwnTableStyle', $styleTable,$styleCellBTLR);

// Add table
        $table = $section->addTable('myOwnTableStyle');

// Add row
        $table->addRow(600);

// Add cells

        $table->addCell(2000, $styleCell)->addText('姓名', $fontStyle,$styleCell);
        $table->addCell(2000, $styleCell)->addText($name, $fontStyle,$styleCell);
        $table->addCell(2000, $styleCell)->addText('性别', $fontStyle, $styleCell);
        $table->addCell(2000, $styleCell)->addText($sex, $fontStyle, $styleCell);

// Add more rows / cells
        $table->addRow(600);
        $table->addCell(2000, $styleCell)->addText('年级', $fontStyle, $styleCell);
        $table->addCell(2000, $styleCell)->addText($grade, $fontStyle, $styleCell);
        $table->addCell(2000, $styleCell)->addText('班级', $fontStyle, $styleCell);
        $table->addCell(2000, $styleCell)->addText($class, $fontStyle, $styleCell);
        $table->addRow(600);
        $table->addCell(2000, $styleCell)->addText('异动类型', $fontStyle, $styleCell);
        $table->addCell(2000, array('cellMerge' => 'restart', 'valign' => "center"))->addText($type, $fontStyle, $styleCell);
        $table->addCell(2000, array('cellMerge' => 'continue'));
        $table->addCell(2000, array('cellMerge' => 'continue'));
        $table->addRow(2400);
        $table->addCell(2000, $styleCell)->addText('申请理由', $fontStyle, $styleCell);
        $table->addCell(2000, array('cellMerge' => 'restart', 'valign' => "center"))->addText($reason, $fontStyle, $styleCell);
        $table->addCell(2000, array('cellMerge' => 'continue'));
        $table->addCell(2000, array('cellMerge' => 'continue'));

        $section->addTextBreak(3);
        $fontStyle = array('color'=>'000000', 'bold'=>false,'size'=>12);
        $PHPWord->addFontStyle('myOwnStyle1', $fontStyle);
        $text = $section->addText($str1, 'myOwnStyle1');
        $section->addTextBreak(1);
        $text = $section->addText($str2, 'myOwnStyle1');
// Save File
//$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
//$objWriter->save('AdvancedTable.docx');
        ob_end_clean();
        $fileName = "wordExport";
        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition:attachment;filename=".$fileName.".docx");
        header('Cache-Control: max-age=0');
        $objWriter =\PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
        $objWriter->save('php://output');*/
        vendor("PHPWord.PHPWord");
        $PHPWord = new \PHPWord();
        $aa=dirname(dirname(dirname(dirname(__FILE__)))).'/Public/upload/template/template.docx';
        $document = $PHPWord->loadTemplate($aa);
        $document->setValue('a1', $name);
        $document->setValue('a2', $sex);
        $document->setValue('a3', $grade);
        $document->setValue('a4', $class);
        $document->setValue('a5', $type);
        $document->setValue('a6', $reason);
        $tmpFileName = time();
        $personBasePath=$aa;
        $document->save($personBasePath.'/'.$tmpFileName .'.docx');
        $file='./Public/upload/template/'.$tmpFileName.'.docx';
        $length = filesize($file);
        //$type = mime_content_type($file);
        $showname ="wordExport.docx";
        header("Content-Description: File Transfer");
        header('Content-type:application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Length:' . $length);
        header('Content-Disposition: attachment; filename="' . $showname . '"');
        readfile($file);
        unlink('./Public/upload/template/'.$tmpFileName.'.docx');
    }
    private function getGradeName($gradeid,$type=false){
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $dao=M();
        $sql="select name from mks_grade where gradeid=".$gradeid." and scId=".$scId;
        $f=$dao->query($sql);
        if(!$type){
            return $f['0']['name'];
        }else{
            return $arrgrade[$f['0']['name']-1];
        }
    }
    public function operation(){/***************异动操作********************/
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        if(I('get.type')=='getGrade'){/****************可选年级**********************/
            $arr=$this->getGrade();
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='getClass'){/************可选班级****************/
            $gradeid=I('post.gradeid');
            $arr=$this->getClass($gradeid);
            $this->ajaxReturn($arr);
        }elseif(I('get.type')=='getStudents'){/*****************学生列表******************/
            $classid=I('post.classid');
            $type=I('post.typename');
            $field=I('post.field');
            $order=I('post.order');
            $find=I('post.find');
            $arr=$this->getStudents($classid,$type,$field,$order,$find);
            $this->ajaxReturn($arr);
        }
        //$arr['id']=$f1;修改，返回异动的id作为异动确认单的参数
        if(I('get.type')=='zhuanban'){/**********************************转班**********************************/
            $grade=I('post.grade');
            $class=I('post.class');
            /*foreach($arrgrade as $k=>$v){
                if($v==$grade['name']){
                    $grade['name']=$k+1;
                }
            }*/
            $grade['name']=$this->getGradeName($grade['gradeid']);
            $userid=I('post.userid');
            $fk=$this->getGradeClassId($userid);
            $data['primaryGradeId']=I('post.gradeid');//原年级
            $data['primaryClassId']=I('post.classid');//原班级
            $data['transferGradeId']=$fk['gradeid'];
            $data['transferClassId']=$fk['classid'];
            $data['time']=strtotime(I('post.time'));
            $data['reason']=I('post.reason');
            $data['userid']=$userid;
            $data['scId']=$scId;
            $data['lastRecordTime']=time();
            $data['createTime']=time();
            M()->startTrans();
            $dao1=M('transaction_class');
            $f1=$dao1->data($data)->add();
            $dao2=M('user');
            $dao3=M('student_info');
            $dao4=M('cen_reg_info');//,
            $dao5=M('parents_info');//,
            $dao6=M('tuition_info');//,
            $dao7=M('other_info');//,
            $dao8=M('school_rollinfo');
            $where['id']=$userid;
            $where['scId']=$scId;
            $where1['userId']=$userid;
            $where1['scId']=$scId;
            $where2['childId']=$userid;
            $where2['scId']=$scId;
            $where2['roleId']=$this::$jZroleId;
            $data1['className']=$class['classname'];
            $data1['class']=$class['classid'];
            $data1['grade']=$grade['name'];
            $data1['gradeId']=$grade['gradeid'];
            $data1['serialNumber']=$this->getSerialNumber($class['classid']);
            $data3['className']=$class['classname'];
            $data3['classId']=$class['classid'];
            $data3['grade']=$grade['name'];
            $data3['gradeId']=$grade['gradeid'];
            $data3['serialNumber']=$data1['serialNumber'];
            $f2=$dao2->where($where)->save($data1);//////************修改*********///////////////
            $f3=$dao3->where($where1)->save($data3);
            $data1['serialNumber']=null;
            $f9=$dao2->where($where2)->save($data1);
            //$data2['grade']=$grade['name'];
            //$data2['className']=$class['classname'];
            //$f4=$dao4->where($where1)->save($data2);//,
            //$f5=$dao5->where($where1)->save($data2);//,
            //$f6=$dao6->where($where1)->save($data2);//,
            //$f7=$dao7->where($where1)->save($data2);//,
            $data2['moveForm']='转班';
            $data2['moveTime']=$data['createTime'];
            $data2['isAtSchool']='是';
            $f8=$dao8->where($where1)->save($data2);
            //if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false || $f6===false || $f7===false || $f8===false || $f9===false || $fk===false){
            if($f1===false || $f2===false || $f3===false || $f8===false || $f9===false || $fk===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
                //$arr['id']=$f1;
            }
            $this->ajaxReturn($arr);
        }
        if(I('get.type')=='zhuanru'){/**************************转入***************************/
            $name=I('post.name');//姓名
            $sex=I('post.sex');//性别
            $certificate=I('post.certificate');//身份证件类型
            $formerAddress=I('post.formerAddress');//原住址
            $idCard=I('post.idCard');//身份证号
            $studentCode=I('post.studentCode');//学籍号
            $hkAddress=I('post.hkAddress');//户籍所在地
            $nowHomePath=I('post.nowHomePath');//现住址
            $grade=I('post.grade');//拟读年级
            $class=I('post.class');//拟读班级
            $reportdate=strtotime(I('post.reportdate'));//报到日期
            if(!$reportdate){
                $reportdate=time();
            }
            /*foreach($arrgrade as $k=>$v){
                if($v==$grade['name']){
                    $grade['name']=$k+1;
                }
            }*/
            $grade['name']=$this->getGradeName($grade['gradeid']);
            $outschoolname=I('post.outschoolname');//学校名称
            $nowgrade=I('post.nowgrade');//现读年级
            $outschoolidentity=I('post.outschoolidentity');//学校标识码
            $nowclass=I('post.nowclass');//现读班级
            $outdate=strtotime(I('post.outdate'));//转出日期
            $reason=I('post.reason');//申请理由
            M()->startTrans();
            $dao1=M('transaction_changeinto');
            $dao2=M('user');
            $dao3=M('student_info');
            $dao4=M('cen_reg_info');
            $dao5=M('parents_info');
            $dao6=M('tuition_info');
            $dao7=M('other_info');
            $dao8=M('school_rollinfo');
            $prefix = M('school')->field('prefix')->where(array('scId' => $scId))->find();
            $prefix = $prefix['prefix'];
            $password = $this->InitialPassword();
            $scIda=-$scId;
            $scIdb=$scId.','.$scIda;
            $wheree['scId']=array('in',$scIdb);
            $maxNumber = M('user')->where($wheree)->max('number');
            $maxNumber++;
            $daoa=M('user');
            $wherea['name']=$name;
            $wherea['scId']=$scId;
            $where['roleId']=$this::$studentRoleId;
            $fa=$daoa->where($wherea)->count();
            $type=I('post.type');
            if($fa===false){
                $arr['return']=false;
                $this->ajaxReturn($arr);
                exit;
            }elseif($fa>0){
                if($type==0){
                    $arr['return']='error';
                    $this->ajaxReturn($arr);
                    exit;
                }elseif($type==1){
                    $name.='('.substr($maxNumber,-4).')';
                }
            }
            $strs['account'] = $prefix. $maxNumber;
            $strs['password'] =  $this->create_password($password,self::$password_key,self::$password_key1);
            $strs['roleId'] = $this::$studentRoleId;
            $strs['InitialPassword'] = $password;
            $strs['createTime'] =time();
            $strs['scId'] = $scId;
            $strs['scPrefix'] = $prefix;
            $strs['number'] = $maxNumber;
            $strs['name'] = $name;
            $strs['idCard'] = $idCard;
            $strs['sex'] = $sex;
            $strs['hkAddress'] = $hkAddress;
            $strs['className']=$class['classname'];
            $strs['class']=$class['classid'];
            $strs['grade']=$grade['name'];
            $strs['gradeId']=$grade['gradeid'];
            $strs['serialNumber']=$this->getSerialNumber($class['classid']);
            $f2=$dao2->add($strs);
            if($f2!==false){
                $prefix = M('school')->field('prefix')->where(array('scId' => $scId))->find();
                $prefix = $prefix['prefix'];
                $password = $this->InitialPassword();
                $maxNumber = M('user')->where($wheree)->max('number');
                $maxNumber++;
                $strs1['account'] = $prefix. $maxNumber;
                $strs1['password'] =  $this->create_password($password,self::$password_key,self::$password_key1);
                $strs1['roleId'] = $this::$jZroleId;
                $strs1['InitialPassword'] = $password;
                $strs1['createTime'] =time();
                $strs1['scId'] = $scId;
                $strs1['scPrefix'] = $prefix;
                $strs1['number'] = $maxNumber;
                $strs1['name'] = $name.'家长';
                $strs1['childId'] = $f2;
                $strs1['childName'] = $name;
                $strs1['className']=$class['classname'];
                $strs1['class']=$class['classid'];
                $strs1['grade']=$grade['name'];
                $strs1['gradeId']=$grade['gradeid'];
                $f3=$dao2->add($strs1);
                $strs2['createTime'] =time();
                $strs2['userId'] =$f2;
                $strs2['scId'] = $scId;
                $strs2['name'] = $name;
                $strs2['className']=$class['classname'];
                $strs2['classId']=$class['classid'];
                $strs2['grade']=$grade['name'];
                $strs2['gradeId']=$grade['gradeid'];
                $strs2['idCard'] = $idCard;
                $strs2['sex'] = $sex;
                //$strs2['isResSchool'] = '';
                $strs2['enrolTime'] = date('Y-m-d',$reportdate);
                //$strs2['enrolWay'] = '转入';
                //$strs2['homePath'] = $nowHomePath;
                $strs2['nowHomePath'] = $nowHomePath;
                $strs2['formerAddress'] = $formerAddress;
                $strs2['certificate'] = $certificate;
                $strs2['serialNumber'] = $strs['serialNumber'];
                $f4=$dao3->add($strs2);
                $strs3['perAddress'] =$hkAddress;
                $strs3['userId'] =$f2;
                $strs3['scId'] = $scId;
                $strs3['createTime'] =time();
                //$strs3['name'] = $name;
                //$strs3['className']=$class['classname'];
                //$strs3['grade']=$grade['name'];
                $f5=$dao4->add($strs3);
                $strs4['userId'] =$f2;
                $strs4['scId'] = $scId;
                $strs4['createTime'] =time();
                //$strs4['name'] = $name;
                //$strs4['className']=$class['classname'];
                //$strs4['grade']=$grade['name'];
                $f6=$dao5->add($strs4);
                $f7=$dao6->add($strs4);
                $f8=$dao7->add($strs4);
                $strs5['userId'] =$f2;
                $strs5['isAtSchool'] ='是';
                $strs5['studentCode'] =$studentCode;
                $strs5['scId'] = $scId;
                $strs5['createTime'] =time();
                //$strs5['name'] = $name;
                //$strs5['className']=$class['classname'];
                $strs5['moveForm']='转入';
                $strs5['moveTime']=time();
                $strs5['sex']=$sex;
                //$strs5['grade']=$grade['name'];
                $f9=$dao8->add($strs5);
                $strs6['userid']=$f2;
                $strs6['togodeid']=$grade['gradeid'];
                $strs6['toclassid']=$class['classid'];
                $strs6['reportdate']=$reportdate;
                $strs6['outschoolname']=$outschoolname;
                $strs6['nowgrade']=$nowgrade;
                $strs6['nowclass']=$nowclass;
                $strs6['outschoolidentity']=$outschoolidentity;
                $strs6['outdate']=$outdate;
                $strs6['reason']=$reason;
                $strs6['scId']=$scId;
                $strs6['lastRecordTime']=time();
                $strs6['createTime']=time();
                $f10=$dao1->add($strs6);
            }
            if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false || $f6===false || $f7===false || $f8===false || $f9===false || $f10===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
                //$arr['id']=$f10;//修改，返回学生家长的账号密码
                $arr['data']['student']['account']=$strs['account'];
                $arr['data']['student']['InitialPassword']=$strs['InitialPassword'];
                $arr['data']['parent']['account']=$strs1['account'];
                $arr['data']['parent']['InitialPassword']=$strs1['InitialPassword'];
            }
            $this->ajaxReturn($arr);
        }
        if(I('get.type')=='jiedu'){/*************************借读*************************************/
            $name=I('post.name');//姓名
            $sex=I('post.sex');//性别
            $certificate=I('post.certificate');//身份证件类型
            $idCard=I('post.idCard');//身份证号
            $studentCode=I('post.studentCode');//学籍号
            //$hkAddress=I('post.hkAddress');//户籍所在地

            $grade=I('post.grade');//借读年级
            $class=I('post.class');//借读班级
            $reportdate=strtotime(I('post.reportdate'));//报到日期
            if(!$reportdate){
                $reportdate=time();
            }

            /*foreach($arrgrade as $k=>$v){
                if($v==$grade['name']){
                    $grade['name']=$k+1;
                }
            }*/
            $grade['name']=$this->getGradeName($grade['gradeid']);
            $outschoolname=I('post.outschoolname');//学校名称
            $nowgrade=I('post.nowgrade');//现读年级
            $outschoolidentity=I('post.outschoolidentity');//学校标识码
            $nowclass=I('post.nowclass');//现读班级
            //$outdate=strtotime(I('post.outdate'));//转出日期
            $reason=I('post.reason');//申请理由
            M()->startTrans();
            $dao1=M('transaction_readby');
            $dao2=M('user');
            $dao3=M('student_info');
            $dao4=M('cen_reg_info');
            $dao5=M('parents_info');
            $dao6=M('tuition_info');
            $dao7=M('other_info');
            $dao8=M('school_rollinfo');
            $prefix = M('school')->field('prefix')->where(array('scId' => $scId))->find();
            $prefix = $prefix['prefix'];
            $password = $this->InitialPassword();
            $scIda=-$scId;
            $scIdb=$scId.','.$scIda;
            $wheree['scId']=array('in',$scIdb);
            $maxNumber = M('user')->where($wheree)->max('number');
            $maxNumber++;
            $daoa=M('user');
            $wherea['name']=$name;
            $wherea['scId']=$scId;
            $fa=$daoa->where($wherea)->count();
            $type=I('post.type');
            if($fa===false){
                $arr['return']=false;
                $this->ajaxReturn($arr);
                exit;
            }elseif($fa>0){
                if($type==0){
                    $arr['return']='error';
                    $this->ajaxReturn($arr);
                    exit;
                }elseif($type==1) {
                    $name .= '(' . substr($maxNumber, -4) . ')';
                }
            }
            $strs['account'] = $prefix.$maxNumber;
            $strs['password'] =  $this->create_password($password,self::$password_key,self::$password_key1);
            $strs['roleId'] = $this::$studentRoleId;
            $strs['InitialPassword'] = $password;
            $strs['createTime'] =time();
            $strs['scId'] = $scId;
            $strs['scPrefix'] = $prefix;
            $strs['number'] = $maxNumber;
            $strs['name'] = $name;
            $strs['idCard'] = $idCard;
            $strs['sex'] = $sex;
            //$strs['hkAddress'] = $hkAddress;
            $strs['className']=$class['classname'];
            $strs['class']=$class['classid'];
            $strs['grade']=$grade['name'];
            $strs['gradeId']=$grade['gradeid'];
            $strs['serialNumber']=$this->getSerialNumber($class['classid']);
            $f2=$dao2->add($strs);
            if($f2!==false){
                $prefix = M('school')->field('prefix')->where(array('scId' => $scId))->find();
                $prefix = $prefix['prefix'];
                $password = $this->InitialPassword();
                $maxNumber = M('user')->where($wheree)->max('number');
                $maxNumber++;
                $strs1['account'] = $prefix. $maxNumber;
                $strs1['password'] =  $this->create_password($password,self::$password_key,self::$password_key1);
                $strs1['roleId'] = $this::$jZroleId;
                $strs1['InitialPassword'] = $password;
                $strs1['createTime'] =time();
                $strs1['scId'] = $scId;
                $strs1['scPrefix'] = $prefix;
                $strs1['number'] = $maxNumber;
                $strs1['name'] = $name.'家长';
                $strs1['childId'] = $f2;
                $strs1['childName'] = $name;
                $strs1['className']=$class['classname'];
                $strs1['class']=$class['classid'];
                $strs1['grade']=$grade['name'];
                $strs1['gradeId']=$grade['gradeid'];
                $f3=$dao2->add($strs1);
                $strs2['createTime'] =time();
                $strs2['userId'] =$f2;
                $strs2['scId'] = $scId;
                $strs2['name'] = $name;
                $strs2['className']=$class['classname'];
                $strs2['classId']=$class['classid'];
                $strs2['grade']=$grade['name'];
                $strs2['gradeId']=$grade['gradeid'];
                $strs2['idCard'] = $idCard;
                $strs2['sex'] = $sex;
                //$strs2['isResSchool'] = '';
                $strs2['enrolTime'] =date('Y-m-d',$reportdate);
                //$strs2['enrolWay'] = '借读';
                $strs2['certificate'] = $certificate;
                $strs2['serialNumber'] = $strs['serialNumber'];
                $f4=$dao3->add($strs2);
                //$strs3['perAddress'] =$hkAddress;
                $strs3['userId'] =$f2;
                $strs3['scId'] = $scId;
                $strs3['createTime'] =time();
                //$strs3['name'] = $name;
                //$strs3['className']=$class['classname'];
                //$strs3['grade']=$grade['name'];
                $f5=$dao4->add($strs3);
                $strs4['userId'] =$f2;
                $strs4['scId'] = $scId;
                $strs4['createTime'] =time();
                //$strs4['name'] = $name;
                //$strs4['className']=$class['classname'];
                //$strs4['grade']=$grade['name'];
                $f6=$dao5->add($strs4);
                $f7=$dao6->add($strs4);
                $f8=$dao7->add($strs4);
                $strs5['userId'] =$f2;
                $strs5['isAtSchool'] ='是';
                $strs5['studentCode'] =$studentCode;
                $strs5['scId'] = $scId;
                $strs5['createTime'] =time();
                //$strs5['name'] = $name;
                //$strs5['className']=$class['classname'];
                $strs5['moveForm']='借读';
                $strs5['moveTime']=time();
                //$strs5['grade']=$grade['name'];
                $strs5['sex']=$sex;
                $strs5['isTempStudy']=1;
                $f9=$dao8->add($strs5);
                $strs6['userid']=$f2;
                $strs6['readbygradeid']=$grade['gradeid'];
                $strs6['readbyclassid']=$class['classid'];
                $strs6['reportdate']=$reportdate;
                $strs6['originalschoolname']=$outschoolname;
                $strs6['originalgrade']=$nowgrade;
                $strs6['originalclass']=$nowclass;
                $strs6['originalschoolidentity']=$outschoolidentity;
                //$strs6['outdate']=$outdate;
                $strs6['reason']=$reason;
                $strs6['scId']=$scId;
                $strs6['lastRecordTime']=time();
                $strs6['createTime']=time();
                $f10=$dao1->add($strs6);
            }
            if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false || $f6===false || $f7===false || $f8===false || $f9===false || $f10===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
                //$arr['id']=$f10;
                $arr['data']['student']['account']=$strs['account'];
                $arr['data']['student']['InitialPassword']=$strs['InitialPassword'];
                $arr['data']['parent']['account']=$strs1['account'];
                $arr['data']['parent']['InitialPassword']=$strs1['InitialPassword'];
            }
            $this->ajaxReturn($arr);
        }
        if(I('get.type')=='fuxue'){/*****************************复学***************************/
            $grade=I('post.grade');
            $class=I('post.class');
            /*foreach($arrgrade as $k=>$v){
                if($v==$grade['name']){
                    $grade['name']=$k+1;
                }
            }*/
            $grade['name']=$this->getGradeName($grade['gradeid']);
            $userid=I('post.userid');
            $data['togodeid']=$grade['gradeid'];
            $data['toclassid']=$class['classid'];
            $data['reportdate']=strtotime(I('post.time'));
            $data['reason']=I('post.reason');
            $data['userid']=$userid;
            $data['scId']=$scId;
            $data['lastRecordTime']=time();
            $data['createTime']=time();
            $f=$this->getGradeClassId($userid);
            $data['primaryClassId']=$f['classid'];
            $data['primaryGradeId']=$f['gradeid'];
            M()->startTrans();
            $dao1=M('transaction_reinstating');
            $f1=$dao1->data($data)->add();
            $dao2=M('user');
            $dao3=M('student_info');
            $dao4=M('cen_reg_info');
            $dao5=M('parents_info');
            $dao6=M('tuition_info');
            $dao7=M('other_info');
            $dao8=M('school_rollinfo');
            $where['id']=$userid;
            $where['scId']=$scId;
            $where1['userId']=$userid;
            $where1['scId']=$scId;
            $where2['childId']=$userid;
            $where2['scId']=$scId;
            $where2['roleId']=$this::$jZroleId;
            $data1['className']=$class['classname'];
            $data1['class']=$class['classid'];
            $data1['grade']=$grade['name'];
            $data1['gradeId']=$grade['gradeid'];
            $data1['serialNumber']=$this->getSerialNumber($class['classid']);
            $data1['isAtSchool']='是';
            $data3['className']=$class['classname'];
            $data3['classId']=$class['classid'];
            $data3['grade']=$grade['name'];
            $data3['gradeId']=$grade['gradeid'];
            $data3['serialNumber']=$data1['serialNumber'];
            $f2=$dao2->where($where)->save($data1);
            $f3=$dao3->where($where1)->save($data3);
            $f9=$dao2->where($where2)->save($data1);
            $data1['serialNumber']=null;
            //$data2['grade']=$grade['name'];
            //$data2['className']=$class['classname'];
            //$f4=$dao4->where($where1)->save($data2);
            //$f5=$dao5->where($where1)->save($data2);
            //$f6=$dao6->where($where1)->save($data2);
            //$f7=$dao7->where($where1)->save($data2);
            $data2['moveForm']='复学';
            $data2['isLeave']=0;
            $data2['moveTime']=$data['createTime'];
            $data2['isAtSchool']='是';
            $f8=$dao8->where($where1)->save($data2);
            $wherez['scId']=$scId;
            $wherez['userId']=$userid;
            $dataz['isAtSchool']='是';
            $daoz=M('student_info');
            $fz=$daoz->where($wherez)->save($dataz);
            //if($f1===false || $f2===false || $f3===false || $f8===false || $f9===false || $f===false || $f4===false || $f5===false || $f6===false || $f7===false){
            if($f1===false || $f2===false || $f3===false || $f8===false || $f9===false || $f===false || $fz===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
                //$arr['id']=$f1;
            }
            $this->ajaxReturn($arr);
        }
        if(I('get.type')=='zhuanchu'){/********************转出*************************/
            /*M()->startTrans();
            $grade=json_decode($_POST['grade'],true);
            $class=json_decode($_POST['class'],true);
            $dao1=M('transaction_turnout');
            $dao2=M('user');
            $dao3=M('student_info');
            $dao4=M('cen_reg_info');
            $dao5=M('parents_info');
            $dao6=M('tuition_info');
            $dao7=M('other_info');
            $dao8=M('school_rollinfo');
            $data['userid']=I('post.userid');
            $data['inschoolname']=I('post.inschoolname');
            $data['inschoolidentity']=I('post.inschoolidentity');
            $data['togode']=I('post.togode');
            $data['outdate']=strtotime(I('post.outdate'));
            $data['reason']=I('post.reason');
            $data['name']=I('post.name');
            $data['scId']=$scId;
            $data['lastRecordTime']=time();
            $data['createTime']=time();
            $data['originalgrade']=$grade['gradeid'];
            $data['originalclass']=$class['classid'];
            $where1['scId']=$scId;
            $where1['id']=$data['userid'];
            $where2['scId']=$scId;
            $where2['userId']=$data['userid'];
            $where3['scId']=$scId;
            $where3['childId']=$data['userid'];
            $f1=$dao1->add($data);
            $f2=$dao2->where($where1)->delete();
            $f3=$dao2->where($where3)->delete();
            $f4=$dao3->where($where2)->delete();
            $f5=$dao4->where($where2)->delete();
            $f6=$dao5->where($where2)->delete();
            $f7=$dao6->where($where2)->delete();
            $f8=$dao7->where($where2)->delete();
            $f9=$dao8->where($where2)->delete();
            if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false || $f6===false || $f7===false || $f8===false || $f9===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
            }
            $this->ajaxReturn($arr);
            $grade=json_decode($_POST['grade'],true);
            $class=json_decode($_POST['class'],true);*/
            M()->startTrans();
            $userid=I('post.userid');
            $dao1=M('transaction_turnout');
            $dao2=M('school_rollinfo');
            $where2['userId']=I('post.userid');
            $where2['scId']=$scId;
            $data['userid']=I('post.userid');
            $data['inschoolname']=I('post.inschoolname');
            $data['inschoolidentity']=I('post.inschoolidentity');
            $data['togode']=I('post.togode');
            $data['outdate']=strtotime(I('post.outdate'));
            $data['reason']=I('post.reason');
            $data['scId']=$scId;
            $data['lastRecordTime']=time();
            $data['createTime']=time();
            $f=$this->getGradeClassId($userid);
            $data['primaryClassId']=$f['classid'];
            $data['primaryGradeId']=$f['gradeid'];
            $f1=$dao1->data($data)->add();
            $data2['isAtSchool']='否';
            $data2['moveForm']='转出';
            $data2['moveTime']=time();
            $f2=$dao2->where($where2)->save($data2);
            $dao3=M('user');
            $where3['id']=I('post.userid');
            $where3['scId']=$scId;
            $where4['childId']=I('post.userid');
            $where4['scId']=$scId;
            $data3['state']=0;
            $data3['isAtSchool']='否';
            $f3=$dao3->where($where3)->save($data3);
            $f4=$dao3->where($where4)->save($data3);
            $wherez['scId']=$scId;
            $wherez['userId']=$userid;
            $dataz['isAtSchool']='否';
            $daoz=M('student_info');
            $fz=$daoz->where($wherez)->save($dataz);
            if($f1===false || $f2===false || $f3===false || $f4===false || $f===false || $fz===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
                //$arr['id']=$f1;
            }
            $this->ajaxReturn($arr);
        }

        if(I('get.type')=='tuixue'){/********************退学*************************/
            /*M()->startTrans();
            $grade=json_decode($_POST['grade'],true);
            $class=json_decode($_POST['class'],true);
            $dao1=M('transaction_dropout');
            $dao2=M('user');
            $dao3=M('student_info');
            $dao4=M('cen_reg_info');
            $dao5=M('parents_info');
            $dao6=M('tuition_info');
            $dao7=M('other_info');
            $dao8=M('school_rollinfo');
            $data['userid']=I('post.userid');
            $data['dropoutdate']=strtotime(I('post.dropoutdate'));
            $data['reason']=I('post.reason');
            $data['name']=I('post.name');
            $data['scId']=$scId;
            $data['lastRecordTime']=time();
            $data['createTime']=time();
            $data['originalgrade']=$grade['gradeid'];
            $data['originalclass']=$class['classid'];
            $where1['scId']=$scId;
            $where1['id']=$data['userid'];
            $where2['scId']=$scId;
            $where2['userId']=$data['userid'];
            $where3['scId']=$scId;
            $where3['childId']=$data['userid'];
            $f1=$dao1->add($data);
            $f2=$dao2->where($where1)->delete();
            $f3=$dao2->where($where3)->delete();
            $f4=$dao3->where($where2)->delete();
            $f5=$dao4->where($where2)->delete();
            $f6=$dao5->where($where2)->delete();
            $f7=$dao6->where($where2)->delete();
            $f8=$dao7->where($where2)->delete();
            $f9=$dao8->where($where2)->delete();
            if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false || $f6===false || $f7===false || $f8===false || $f9===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
            }
            $this->ajaxReturn($arr);*/
            M()->startTrans();
            $userid=I('post.userid');
            $dao1=M('transaction_dropout');
            $dao2=M('school_rollinfo');
            $where2['userId']=I('post.userid');
            $where2['scId']=$scId;
            $data['userid']=I('post.userid');
            $data['dropoutdate']=strtotime(I('post.dropoutdate'));
            $data['reason']=I('post.reason');
            $data['scId']=$scId;
            $data['lastRecordTime']=time();
            $data['createTime']=time();
            $f=$this->getGradeClassId($userid);
            $data['primaryClassId']=$f['classid'];
            $data['primaryGradeId']=$f['gradeid'];
            $f1=$dao1->data($data)->add();
            $data2['isAtSchool']='否';
            $data2['moveForm']='退学';
            $data2['moveTime']=time();
            $f2=$dao2->where($where2)->save($data2);
            $dao3=M('user');
            $where3['id']=I('post.userid');
            $where3['scId']=$scId;
            $where4['childId']=I('post.userid');
            $where4['scId']=$scId;
            $data3['state']=0;
            $data3['isAtSchool']='否';
            $f3=$dao3->where($where3)->save($data3);
            $f4=$dao3->where($where4)->save($data3);
            $wherez['scId']=$scId;
            $wherez['userId']=$userid;
            $dataz['isAtSchool']='否';
            $daoz=M('student_info');
            $fz=$daoz->where($wherez)->save($dataz);
            if($f1===false || $f2===false || $f3===false || $f4===false || $f===false || $fz===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
                //$arr['id']=$f1;
            }
            $this->ajaxReturn($arr);
        }
        if(I('get.type')=='xiuxue'){/***************************休学*******************************/
            M()->startTrans();
            $userid=I('post.userid');
            $dao1=M('transaction_offschool');
            $dao2=M('school_rollinfo');
            $where2['userId']=I('post.userid');
            $data1['userid']=I('post.userid');
            $data1['offschooldate']=strtotime(I('post.offschooldate'));
            $data1['returndate']=strtotime(I('post.returndate'));
            $data1['reason']=I('post.reason');
            $data1['scId']=$scId;
            $data1['lastRecordTime']=time();
            $data1['createTime']=time();
            $f=$this->getGradeClassId($userid);
            $data1['primaryClassId']=$f['classid'];
            $data1['primaryGradeId']=$f['gradeid'];
            $f1=$dao1->data($data1)->add();
            $data2['isAtSchool']='否';
            $data2['isLeave']=1;
            $data2['moveForm']='休学';
            $data2['moveTime']=time();
            $f2=$dao2->where($where2)->save($data2);
            $dao3=M('user');
            $where3['id']=I('post.userid');
            $where3['scId']=$scId;
            $where4['childId']=I('post.userid');
            $where4['scId']=$scId;
            $data3['state']=0;
            $data3['isAtSchool']='否';
            $f4=$dao3->where($where4)->save($data3);
            $f3=$dao3->where($where3)->save($data3);
            $wherez['scId']=$scId;
            $wherez['userId']=$userid;
            $dataz['isAtSchool']='否';
            $daoz=M('student_info');
            $fz=$daoz->where($wherez)->save($dataz);
            if($f1===false || $f2===false || $f===false || $f3===false || $f4===false || $fz===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
                //$arr['id']=$f1;
            }
            $this->ajaxReturn($arr);
        }
        if(I('get.type')=='guadu'){/***************************挂读*******************************/
            M()->startTrans();
            $userid=I('post.userid');
            $dao1=M('transaction_read');
            $dao2=M('school_rollinfo');
            $where2['userId']=I('post.userid');
            $data1['userid']=I('post.userid');
            $data1['readschoolname']=I('post.readschoolname');
            $data1['readschoolidentity']=I('post.readschoolidentity');
            $data1['readgrade']=I('post.readgrade');
            $data1['reportdate']=strtotime(I('post.reportdate'));
            $data1['reason']=I('post.reason');
            $data1['scId']=$scId;
            $data1['lastRecordTime']=time();
            $data1['createTime']=time();
            $f=$this->getGradeClassId($userid);
            $data1['primaryClassId']=$f['classid'];
            $data1['primaryGradeId']=$f['gradeid'];
            $f1=$dao1->data($data1)->add();
            $data2['isAtSchool']='否';
            $data2['isSubor']=1;
            $data2['moveForm']='挂读';
            $data2['moveTime']=time();
            $f2=$dao2->where($where2)->save($data2);
            $dao3=M('user');
            $where3['id']=I('post.userid');
            $where3['scId']=$scId;
            $data3['state']=0;
            $data3['isAtSchool']='否';
            $where4['childId']=I('post.userid');
            $where4['scId']=$scId;
            $f4=$dao3->where($where4)->save($data3);
            $f3=$dao3->where($where3)->save($data3);
            $wherez['scId']=$scId;
            $wherez['userId']=$userid;
            $dataz['isAtSchool']='否';
            $daoz=M('student_info');
            $fz=$daoz->where($wherez)->save($dataz);
            if($f1===false || $f2===false || $f===false || $f3===false ||$f4===false ||$fz===false){
                M()->rollback();
                $arr['return']=false;
            }else{
                M()->commit();
                $arr['return']=true;
                //$arr['id']=$f1;
            }
            $this->ajaxReturn($arr);
        }
        if(I('get.type')=='yidongqueren'){//打印异动确认单
            $type=I('post.typename');
            $operation=I('post.operation');
            $id=I('post.id');
            $where['scId']=$scId;
            $where['id']=$id;
            if($type!='转入'&&$type!='借读'){
                if($type=='转班'){
                    $table='transaction_class';
                }elseif($type=='退学'){
                    $table='transaction_dropout';
                }elseif($type=='休学'){
                    $table='transaction_offschool';
                }elseif($type=='挂读'){
                    $table='transaction_read';
                }elseif($type=='复学'){
                    $table='transaction_reinstating';
                }elseif($type=='转出'){
                    $table='transaction_turnout';
                }
                $dao1=M($table);
                $f1=$dao1->where($where)->field('userid,primaryGradeId,primaryClassId,reason')->select();
                $arr=$this->getUser($f1['0']['userid'],$f1['0']['primaryGradeId'],$f1['0']['primaryClassId']);
            }elseif($type=='转入'){
                $dao1=M('transaction_changeinto');
                $f1=$dao1->where($where)->field('userid,togodeid,toclassid,reason')->select();
                $arr=$this->getUser($f1['0']['userid'],$f1['0']['togodeid'],$f1['0']['toclassid']);
            }elseif($type=='借读'){
                $dao1=M('transaction_readby');
                $f1=$dao1->where($where)->field('userid,readbygradeid,readbyclassid,reason')->select();
                $arr=$this->getUser($f1['0']['userid'],$f1['0']['readbygradeid'],$f1['0']['readbyclassid']);
            }

                $str='    <div style="width: 100%" align="center">
        <p align="center" style="font-size: 22px;margin-top: 30px;margin-bottom:30px">学生异动确认单</p>
        <br>
        <table frame="border" cellpadding="15" cellspacing="0" align="center" rules="all" width="100%">
            <tr align="center" style="height: 50px">
                <td style="width: 25%">姓名</td>
                <td style="width: 25%">'.$arr['name'].'</td>
                <td style="width: 25%">性别</td>
                <td style="width: 25%">'.$arr['sex'].'</td>
            </tr>
            <tr align="center" style="height: 50px">
                <td style="width: 25%">年级</td>
                <td style="width: 25%">'.$arr['grade'].'</td>
                <td style="width: 25%">班级</td>
                <td style="width: 25%">'.$arr['class'].'</td>
            </tr>
                        <tr align="center" style="height:50px">
                <td style="width: 25%">异动类型</td>
                <td colspan="3" style="width: 75%">'.$type.'</td>
            </tr>
            <tr align="center" style="height:150px">
                <td style="width: 25%">申请理由</td>
                <td colspan="3" style="width: 75%">'.$f1['0']['reason'].'</td>
            </tr>
        </table>
        <br>
        <div><span style="float: left">原班主任：</span><span style="float: right">新班主任：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
        <br>
        <br>
        <div><span style="float: left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;年&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;月&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="float: right">年&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;月&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日</span></div>
    </div>';
                $arr['data']=$str;
                $this->ajaxReturn($arr);
                /*$arra['name']=$arr['name'];
                $arra['sex']=$arr['sex'];
                $arra['grade']=$arr['grade'];
                $arra['class']=$arr['class'];
                $arra['reason']=$f1['0']['reason'];
                $this->ajaxReturn($arra);*/
        }elseif(I('get.type')=='yidongquerenExport'){/**************导出异动申请表word********************/
            $type=I('get.typename');
            $id=I('get.id');
            $where['scId']=$scId;
            $where['id']=$id;
            if($type!='转入'&&$type!='借读'){
                if($type=='转班'){
                    $table='transaction_class';
                }elseif($type=='退学'){
                    $table='transaction_dropout';
                }elseif($type=='休学'){
                    $table='transaction_offschool';
                }elseif($type=='挂读'){
                    $table='transaction_read';
                }elseif($type=='复学'){
                    $table='transaction_reinstating';
                }elseif($type=='转出'){
                    $table='transaction_turnout';
                }
                $dao1=M($table);
                $f1=$dao1->where($where)->field('userid,primaryGradeId,primaryClassId,reason')->select();
                $arr=$this->getUser($f1['0']['userid'],$f1['0']['primaryGradeId'],$f1['0']['primaryClassId']);
            }elseif($type=='转入'){
                $dao1=M('transaction_changeinto');
                $f1=$dao1->where($where)->field('userid,togodeid,toclassid,reason')->select();
                $arr=$this->getUser($f1['0']['userid'],$f1['0']['togodeid'],$f1['0']['toclassid']);
            }elseif($type=='借读'){
                $dao1=M('transaction_readby');
                $f1=$dao1->where($where)->field('userid,readbygradeid,readbyclassid,reason')->select();
                $arr=$this->getUser($f1['0']['userid'],$f1['0']['readbygradeid'],$f1['0']['readbyclassid']);
            }

            $this->getWord($arr['name'],$arr['sex'],$arr['grade'],$arr['class'],$type,$f1['0']['reason']);
        }
    }
    public function statistics(){/**********************异动统计*************************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        if(I('get.type')=='all'){/****************异动统计**************/
            if(!I('post.starttime')){
                $starttime=0;
            }else{
                $starttime=strtotime(I('post.starttime'));
            }
            if(!I('post.endtime')){
                $endtime=time();
            }else{
                $endtime=strtotime(I('post.endtime'))+3600*24;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $dao=M();
            $gradeList=$this->getGrade();
            $sql1="SELECT c.`togodeid` as gradeId,COUNT(c.`id`) AS `count` FROM `mks_transaction_changeinto` AS c,mks_user AS u WHERE c.`userid`=u.`id` AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime." GROUP BY c.`togodeid`";
            $sql2="SELECT c.`primaryGradeId` as gradeId,COUNT(c.`id`) AS `count` FROM `mks_transaction_class` AS c,mks_user AS u WHERE c.`userid`=u.`id` AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime." GROUP BY c.`primaryGradeId`";
            $sql3="SELECT c.`primaryGradeId` as gradeId,COUNT(c.`id`) AS `count` FROM `mks_transaction_offschool` AS c,mks_user AS u WHERE c.`userid`=u.`id` AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime." GROUP BY c.`primaryGradeId`";
            $sql4="SELECT c.`primaryGradeId` as gradeId,COUNT(c.`id`) AS `count` FROM `mks_transaction_read` AS c,mks_user AS u WHERE c.`userid`=u.`id` AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime." GROUP BY c.`primaryGradeId`";
            $sql5="SELECT c.`readbygradeid` as gradeId,COUNT(c.`id`) AS `count` FROM `mks_transaction_readby` AS c,mks_user AS u WHERE c.`userid`=u.`id` AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime." GROUP BY c.`readbygradeid`";
            $sql6="SELECT c.`primaryGradeId` as gradeId,COUNT(c.`id`) AS `count` FROM `mks_transaction_reinstating` AS c,mks_user AS u WHERE c.`userid`=u.`id` AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime." GROUP BY c.`primaryGradeId`";
            $sql7="SELECT c.`primaryGradeId` as gradeId,COUNT(c.`id`) AS `count` FROM `mks_transaction_turnout` AS c,mks_user AS u WHERE c.`userid`=u.`id` AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime." GROUP BY c.`primaryGradeId`";
            $sql8="SELECT c.`primaryGradeId` as gradeId,COUNT(c.`id`) AS `count` FROM `mks_transaction_dropout` AS c,mks_user AS u WHERE c.`userid`=u.`id` AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime." GROUP BY c.`primaryGradeId`";
            $f1=$dao->query($sql1);
            $f2=$dao->query($sql2);
            $f3=$dao->query($sql3);
            $f4=$dao->query($sql4);
            $f5=$dao->query($sql5);
            $f6=$dao->query($sql6);
            //$sql7="SELECT COUNT(id) AS `count`,originalgrade AS gradeId FROM `mks_transaction_turnout` WHERE scId=".$scId." and createTime>".$starttime." and createTime<".$endtime." GROUP BY originalgrade";
            //$sql8="SELECT COUNT(id) AS `count`,originalgrade AS gradeId FROM `mks_transaction_dropout` WHERE scId=".$scId." and createTime>".$starttime." and createTime<".$endtime." GROUP BY originalgrade";
            $f7=$dao->query($sql7);
            $f8=$dao->query($sql8);
            foreach($f1 as $k=>$v){
                $arr[$v['gradeId']]['zhuanru']=$v['count'];
            }
            foreach($f2 as $k=>$v){
                $arr[$v['gradeId']]['zhuanban']=$v['count']?$v['count']:0;
            }
            foreach($f3 as $k=>$v){
                $arr[$v['gradeId']]['xiuxue']=$v['count'];
            }
            foreach($f4 as $k=>$v){
                $arr[$v['gradeId']]['guadu']=$v['count'];
            }
            foreach($f5 as $k=>$v){
                $arr[$v['gradeId']]['jiedu']=$v['count'];
            }
            foreach($f6 as $k=>$v){
                $arr[$v['gradeId']]['fuxue']=$v['count'];
            }
            foreach($f7 as $k=>$v){
                $arr[$v['gradeId']]['zhuanchu']=$v['count'];
            }
            foreach($f8 as $k=>$v) {
                $arr[$v['gradeId']]['tuixue'] = $v['count'];
            }
            $zhuanru=0;
            $zhuanban=0;
            $xiuxue=0;
            $guadu=0;
            $jiedu=0;
            $fuxue=0;
            $tuixue=0;
            $zhuanchu=0;
            $key=count($gradeList);
            foreach($gradeList as $k=>$v){
                $gradeList[$k]['zhuanru']=$arr[$v['gradeid']]['zhuanru']?$arr[$v['gradeid']]['zhuanru']:0;
                $zhuanru+=$arr[$v['gradeid']]['zhuanru'];
                $gradeList[$k]['zhuanban']=$arr[$v['gradeid']]['zhuanban']?$arr[$v['gradeid']]['zhuanban']:0;
                $zhuanban+=$arr[$v['gradeid']]['zhuanban'];
                $gradeList[$k]['xiuxue']=$arr[$v['gradeid']]['xiuxue']?$arr[$v['gradeid']]['xiuxue']:0;
                $xiuxue+=$arr[$v['gradeid']]['xiuxue'];
                $gradeList[$k]['guadu']=$arr[$v['gradeid']]['guadu']?$arr[$v['gradeid']]['guadu']:0;
                $guadu+=$arr[$v['gradeid']]['guadu'];
                $gradeList[$k]['jiedu']=$arr[$v['gradeid']]['jiedu']?$arr[$v['gradeid']]['jiedu']:0;
                $jiedu+=$arr[$v['gradeid']]['jiedu'];
                $gradeList[$k]['fuxue']=$arr[$v['gradeid']]['fuxue']?$arr[$v['gradeid']]['fuxue']:0;
                $fuxue+=$arr[$v['gradeid']]['fuxue'];
                $gradeList[$k]['tuixue']=$arr[$v['gradeid']]['tuixue']?$arr[$v['gradeid']]['tuixue']:0;
                $tuixue+=$arr[$v['gradeid']]['tuixue'];
                $gradeList[$k]['zhuanchu']=$arr[$v['gradeid']]['zhuanchu']?$arr[$v['gradeid']]['zhuanchu']:0;
                $zhuanchu+=$arr[$v['gradeid']]['zhuanchu'];
            }
            $gradeList[$key]['gradeid']='all';
            $gradeList[$key]['name']='all';
            $gradeList[$key]['zhuanru']=$zhuanru;
            $gradeList[$key]['zhuanban']=$zhuanban;
            $gradeList[$key]['xiuxue']=$xiuxue;
            $gradeList[$key]['guadu']=$guadu;
            $gradeList[$key]['jiedu']=$jiedu;
            $gradeList[$key]['fuxue']=$fuxue;
            $gradeList[$key]['tuixue']=$tuixue;
            $gradeList[$key]['zhuanchu']=$zhuanchu;
            $aa=$this->sortArrByOneField($gradeList,$find);
            $this->ajaxReturn($aa);
        }elseif(I('get.type')=='mingdan'){/*****************异动学生名单*************/
            $starttime=strtotime(I('post.starttime'));
            $endtime=strtotime(I('post.endtime'))+3600*24;
            if(!$starttime){
                $starttime=0;
            }
            if($endtime){
                $endtime=time();
            }
            $gradeid=I('post.gradeid');
            $typename=I('post.typename');
            if($gradeid!='all'){
                //$str="and u.gradeId=".$gradeid;
                $str1="and c.primaryGradeId=".$gradeid;
                $str2="and c.togodeid=".$gradeid;
                $str3="and c.readbygradeid=".$gradeid;
            }else{
                //$str="and u.gradeId<>0";
                $str1="and c.primaryGradeId<>0";
                $str2="and c.togodeid<>0";
                $str3="and c.readbygradeid<>0";
            }
            $dao=M();
            $sql1="SELECT u.`name`,u.grade,u.className,c.lastRecordTime FROM `mks_transaction_changeinto` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str2." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql2="SELECT u.`name`,u.grade,u.className,c.lastRecordTime FROM `mks_transaction_class` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str1." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql3="SELECT u.`name`,u.grade,u.className,c.lastRecordTime FROM `mks_transaction_offschool` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str1." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql4="SELECT u.`name`,u.grade,u.className,c.lastRecordTime FROM `mks_transaction_read` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str1." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql5="SELECT u.`name`,u.grade,u.className,c.lastRecordTime FROM `mks_transaction_readby` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str3." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql6="SELECT u.`name`,u.grade,u.className,c.lastRecordTime FROM `mks_transaction_reinstating` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str1." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql7="SELECT u.`name`,u.grade,u.className,c.lastRecordTime FROM `mks_transaction_turnout` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str1." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql8="SELECT u.`name`,u.grade,u.className,c.lastRecordTime FROM `mks_transaction_dropout` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str1." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            if($typename=='all'||!$typename){
                $f1=$dao->query($sql1);
                $f2=$dao->query($sql2);
                $f3=$dao->query($sql3);
                $f4=$dao->query($sql4);
                $f5=$dao->query($sql5);
                $f6=$dao->query($sql6);
                $f7=$dao->query($sql7);
                $f8=$dao->query($sql8);
            }elseif($typename=='zhuanru'){
                $f1=$dao->query($sql1);
            }elseif($typename=='zhuanban'){
                $f2=$dao->query($sql2);
            }elseif($typename=='xiuxue'){
                $f3=$dao->query($sql3);
            }elseif($typename=='guadu'){
                $f4=$dao->query($sql4);
            }elseif($typename=='jiedu'){
                $f5=$dao->query($sql5);
            }elseif($typename=='fuxue'){
                $f6=$dao->query($sql6);
            }elseif($typename=='zhuanchu'){
                $f7=$dao->query($sql7);
            }elseif($typename=='tuixue'){
                $f8=$dao->query($sql8);
            }

            //$sql7="SELECT t.name,g.name as grade,c.classname as className,t.lastRecordTime FROM `mks_transaction_turnout` as t,mks_grade as g,mks_class as c WHERE t.scId=".$scId." and t.createTime>".$starttime." ".$str2." and t.createTime<".$endtime." and t.originalgrade=g.gradeid and t.originalclass=c.classid";
            //$sql8="SELECT t.name,g.name as grade,c.classname as className,t.lastRecordTime FROM `mks_transaction_turnout` as t,mks_grade as g,mks_class as c WHERE t.scId=".$scId." and t.createTime>".$starttime." ".$str2." and t.createTime<".$endtime." and t.originalgrade=g.gradeid and t.originalclass=c.classid";
            //$sql8="SELECT t.name,g.name as grade,c.classname as className,t.lastRecordTime FROM `mks_transaction_turnout` as t,mks_grade as g,mks_class as c WHERE t.scId=".$scId." and t.createTime>".$starttime." ".$str2." and t.createTime<".$endtime." and t.originalgrade=g.gradeid and t.originalclass=c.classid";
            if($f1){
                foreach($f1 as $k=>$v){
                    $v['gradeName']=$v['grade'];
                    $v['grade']=$arrgrade[$v['grade']-1];
                    $v['typename']="转入";
                    $v['lastRecordTime']=$v['lastRecordTime'];
                    $arr[]=$v;
                }
            }
            if($f2){
                foreach($f2 as $k=>$v){
                    $v['gradeName']=$v['grade'];
                    $v['grade']=$arrgrade[$v['grade']-1];
                    $v['typename']="转班";
                    $v['lastRecordTime']=$v['lastRecordTime'];
                    $arr[]=$v;
                }
            }
            if($f3){
                foreach($f3 as $k=>$v){
                    $v['gradeName']=$v['grade'];
                    $v['grade']=$arrgrade[$v['grade']-1];
                    $v['typename']="休学";
                    $v['lastRecordTime']=$v['lastRecordTime'];
                    $arr[]=$v;
                }
            }
            if($f4){
                foreach($f4 as $k=>$v){
                    $v['gradeName']=$v['grade'];
                    $v['grade']=$arrgrade[$v['grade']-1];
                    $v['typename']="挂读";
                    $v['lastRecordTime']=$v['lastRecordTime'];
                    $arr[]=$v;
                }
            }
            if($f5){
                foreach($f5 as $k=>$v){
                    $v['gradeName']=$v['grade'];
                    $v['grade']=$arrgrade[$v['grade']-1];
                    $v['typename']="借读";
                    $v['lastRecordTime']=$v['lastRecordTime'];
                    $arr[]=$v;
                }
            }
            if($f6){
                foreach($f6 as $k=>$v){
                    $v['gradeName']=$v['grade'];
                    $v['grade']=$arrgrade[$v['grade']-1];
                    $v['typename']="复学";
                    $v['lastRecordTime']=$v['lastRecordTime'];
                    $arr[]=$v;
                }
            }
            if($f7){
                foreach($f7 as $k=>$v){
                    $v['gradeName']=$v['grade'];
                    $v['grade']=$arrgrade[$v['grade']-1];
                    $v['typename']="转出";
                    $v['lastRecordTime']=$v['lastRecordTime'];
                    $arr[]=$v;
                }
            }
            if($f8){
                foreach($f8 as $k=>$v){
                    $v['gradeName']=$v['grade'];
                    $v['grade']=$arrgrade[$v['grade']-1];
                    $v['typename']="退学";
                    $v['lastRecordTime']=$v['lastRecordTime'];
                    $arr[]=$v;
                }
            }
            foreach ($arr as $k => $v) {
                $fieldArr[$k] =$v['gradeName'];
                $fieldArr2[$k] =$v['className'];
                $fieldArr3[$k] =$v['lastRecordTime'];
            }
            array_multisort($fieldArr,SORT_ASC,$fieldArr2,SORT_ASC,$fieldArr3,SORT_ASC, $arr);
            foreach ($arr as $k => $v) {
                unset($arr[$k]['gradeName']);
            }
            foreach($arr as $k=>$v){
                $arr[$k]['lastRecordTime']=date('Y-m-d',$v['lastRecordTime']);
            }
            if(!$arr){
                $arr=array();
            }
            $this->ajaxReturn($arr);
        }
    }
    public function mingxi(){/******************异动明细*****************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $arrgrade=array('一年级','二年级','三年级','四年级','五年级','六年级','初一','初二','初三','高一','高二','高三');
        if(I('get.type')=='all'){/*************异动明细***************/
            if(!I('post.starttime')){
                $starttime=0;
            }else{
                $starttime=strtotime(I('post.starttime'));
            }
            if(!I('post.endtime')){
                $endtime=time();
            }else{
                $endtime=strtotime(I('post.endtime'))+3600*24;
            }
            $find=I('post.find');
            if($find==''){
                $find=false;
            }
            $str="and u.gradeId<>0";
            $str2="and t.originalgrade<>0";
            $dao=M();
            $sql1="SELECT u.id as userid,c.id,u.`name`,u.grade,u.className,c.lastRecordTime,u.sex FROM `mks_transaction_changeinto` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql2="SELECT u.id as userid,c.id,u.`name`,u.grade,u.className,c.lastRecordTime,u.sex FROM `mks_transaction_class` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql3="SELECT u.id as userid,c.id,u.`name`,u.grade,u.className,c.lastRecordTime,u.sex FROM `mks_transaction_offschool` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql4="SELECT u.id as userid,c.id,u.`name`,u.grade,u.className,c.lastRecordTime,u.sex FROM `mks_transaction_read` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql5="SELECT u.id as userid,c.id,u.`name`,u.grade,u.className,c.lastRecordTime,u.sex FROM `mks_transaction_readby` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql6="SELECT u.id as userid,c.id,u.`name`,u.grade,u.className,c.lastRecordTime,u.sex FROM `mks_transaction_reinstating` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql7="SELECT u.id as userid,c.id,u.`name`,u.grade,u.className,c.lastRecordTime,u.sex FROM `mks_transaction_turnout` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $sql8="SELECT u.id as userid,c.id,u.`name`,u.grade,u.className,c.lastRecordTime,u.sex FROM `mks_transaction_dropout` AS c,mks_user AS u WHERE c.`userid`=u.`id` ".$str." AND c.`scId`=".$scId." and c.createTime>".$starttime." and c.createTime<".$endtime;
            $f1=$dao->query($sql1);
            $f2=$dao->query($sql2);
            $f3=$dao->query($sql3);
            $f4=$dao->query($sql4);
            $f5=$dao->query($sql5);
            $f6=$dao->query($sql6);
            //$sql7="SELECT t.name,g.name as grade,c.classname as className,t.lastRecordTime FROM `mks_transaction_turnout` as t,mks_grade as g,mks_class as c WHERE t.scId=".$scId." and t.createTime>".$starttime." ".$str2." and t.createTime<".$endtime." and t.originalgrade=g.gradeid and t.originalclass=c.classid";
            //$sql8="SELECT t.name,g.name as grade,c.classname as className,t.lastRecordTime FROM `mks_transaction_turnout` as t,mks_grade as g,mks_class as c WHERE t.scId=".$scId." and t.createTime>".$starttime." ".$str2." and t.createTime<".$endtime." and t.originalgrade=g.gradeid and t.originalclass=c.classid";
            $f7=$dao->query($sql7);
            $f8=$dao->query($sql8);
            foreach($f1 as $k=>$v){
                $v['grade']=$arrgrade[$v['grade']-1];
                $v['typename']="转入";
                $v['lastRecordTime']=$v['lastRecordTime'];
                $arr[]=$v;
            }
            foreach($f2 as $k=>$v){
                $v['grade']=$arrgrade[$v['grade']-1];
                $v['typename']="转班";
                $v['lastRecordTime']=$v['lastRecordTime'];
                $arr[]=$v;
            }
            foreach($f3 as $k=>$v){
                $v['grade']=$arrgrade[$v['grade']-1];
                $v['typename']="休学";
                $v['lastRecordTime']=$v['lastRecordTime'];
                $arr[]=$v;
            }
            foreach($f4 as $k=>$v){
                $v['grade']=$arrgrade[$v['grade']-1];
                $v['typename']="挂读";
                $v['lastRecordTime']=$v['lastRecordTime'];
                $arr[]=$v;
            }
            foreach($f5 as $k=>$v){
                $v['grade']=$arrgrade[$v['grade']-1];
                $v['typename']="借读";
                $v['lastRecordTime']=$v['lastRecordTime'];
                $arr[]=$v;
            }
            foreach($f6 as $k=>$v){
                $v['grade']=$arrgrade[$v['grade']-1];
                $v['typename']="复学";
                $v['lastRecordTime']=$v['lastRecordTime'];
                $arr[]=$v;
            }
            foreach($f7 as $k=>$v){
                $v['grade']=$arrgrade[$v['grade']-1];
                $v['typename']="转出";
                $v['lastRecordTime']=$v['lastRecordTime'];
                $arr[]=$v;
            }
            foreach($f8 as $k=>$v){
                $v['grade']=$arrgrade[$v['grade']-1];
                $v['typename']="退学";
                $v['lastRecordTime']=$v['lastRecordTime'];
                $arr[]=$v;
            }
            foreach ($arr as $k => $v) {
                $fieldArr[$k] =$v['lastRecordTime'];
            }
            array_multisort($fieldArr,SORT_ASC, $arr);
            foreach($arr as $k=>$v){
                $arr[$k]['lastRecordTime']=date('Y-m-d',$v['lastRecordTime']);
            }
            if(!$arr){
                $arr=array();
            }
            $aa=$this->sortArrByOneField($arr,$find);
            $this->ajaxReturn($aa);
        }elseif(I('get.type')=='xiangqing'){/***************异动详情******************/
            $dao=M();
            $userid=I('post.userid');
            $id=I('post.id');
            $typename=I('post.typename');
            $sql1="SELECT u.`name`,r.`sex`,r.studentCode,i.certificate,i.`idCard`,u.hkAddress FROM `mks_school_rollinfo` AS r,`mks_student_info` AS i,mks_user as u WHERE r.userid=".$userid." and r.`userId`=u.`id` AND u.scId=".$scId." AND r.`userId`=i.`userId`";
            $f1=$dao->query($sql1);
            $arr['stInformation']=$f1;
            $sql2="SELECT account,InitialPassword,childId FROM `mks_user` WHERE scId=".$scId." AND (id=".$userid." OR childId=".$userid.")";
            $f2=$dao->query($sql2);
            foreach($f2 as $k=>$v){
                if(!$v['childId']){
                    $arr['account']['student']['number']=$v['account'];
                    $arr['account']['student']['password']=$v['InitialPassword'];
                }else{
                    $arr['account']['parent']['number']=$v['account'];
                    $arr['account']['parent']['password']=$v['InitialPassword'];
                }
            }
            $gradelist=$this->getGrade();
            foreach($gradelist as $k=>$v){
                $arr1[$v['gradeid']]=$v['name'];
            }
            $sql="SELECT classid,classname FROM mks_class WHERE scId=".$scId;
            $f=$dao->query($sql);
            foreach($f as $k=>$v){
                $arr2[$v['classid']]=$v['classname'];
            }
            $arr['transaction']['typename']=$typename;
            if($typename=='转班'){
                $sql3="SELECT primaryGradeId,primaryClassId,transferGradeId,transferClassId,`time` AS reportdate,reason FROM `mks_transaction_class` WHERE id=".$id." AND scId=".$scId;
                $f3=$dao->query($sql3);
                $arr4['primaryGrade']['name']='原年级';
                $arr4['primaryGrade']['data']=$arr1[$f3['0']['primaryGradeId']];//原年级
                $arr4['primaryClass']['name']='原班级';
                $arr4['primaryClass']['data']=$arr2[$f3['0']['primaryClassId']];//原班级
                $arr4['transferGrade']['name']='新年级';
                $arr4['transferGrade']['data']=$arr1[$f3['0']['transferGradeId']];//新年级
                $arr4['transferClass']['name']='新班级';
                $arr4['transferClass']['data']=$arr2[$f3['0']['transferClassId']];//新班级
                $arr4['reportDate']['name']='报到日期';
                if($f3['0']['reportdate']){
                    $arr4['reportDate']['data']=date('Y-m-d',$f3['0']['reportdate']);//报到日期
                }else{
                    $arr4['reportDate']['data']=null;//报到日期
                }
                $arr4['reason']['name']='申请理由';
                $arr4['reason']['data']=$f3['0']['reason'];//申请理由
                foreach($arr4 as $k=>$v){
                    $arr['transaction']['list'][]=$v;
                }
            }elseif($typename=='转入'){
                $sql3="SELECT togodeid,toclassid,reportdate,outschoolname,outschoolidentity,outdate,nowgrade,nowclass,reason FROM `mks_transaction_changeinto` WHERE id=".$id." AND scId=".$scId;
                $f3=$dao->query($sql3);
                $arr4['togodeid']['name']='拟读年级';
                $arr4['togodeid']['data']=$arr1[$f3['0']['togodeid']];//拟读年级
                $arr4['toclassid']['name']='拟读班级';
                $arr4['toclassid']['data']=$arr2[$f3['0']['toclassid']];//拟读班级
                $arr4['reportdate']['name']='报到日期';
                if($f3['0']['reportdate']){
                    $arr4['reportdate']['data']=date('Y-m-d',$f3['0']['reportdate']);//报到日期
                }else{
                    $arr4['reportdate']['data']=null;//报到日期
                }
                $arr4['outschoolname']['name']='转出学校名称';
                $arr4['outschoolname']['data']=$f3['0']['outschoolname'];//转出学校名称
                $arr4['outschoolidentity']['name']='转出学校标识';
                $arr4['outschoolidentity']['data']=$f3['0']['outschoolidentity'];//转出学校标识
                $arr4['outdate']['name']='转出日期';
                if($f3['0']['outdate']){
                    $arr4['outdate']['data']=date('Y-m-d',$f3['0']['outdate']);//转出日期
                }else{
                    $arr4['outdate']['data']=null;//转出日期
                }
                $arr4['nowgrade']['name']='现读年级';
                $arr4['nowgrade']['data']=$f3['0']['nowgrade'];//现读年级
                $arr4['nowclass']['name']='现读班级';
                $arr4['nowclass']['data']=$f3['0']['nowclass'];//现读班级
                $arr4['reason']['name']='申请理由';
                $arr4['reason']['data']=$f3['0']['reason'];//申请理由
            }elseif($typename=='转出'){
                $sql3="SELECT inschoolname,inschoolidentity,togode,outdate,reason FROM `mks_transaction_turnout` WHERE id=".$id." AND scId=".$scId;
                $f3=$dao->query($sql3);
                $arr4['inschoolname']['name']='转入学校名称';
                $arr4['inschoolname']['data']=$f3['0']['inschoolname'];//转入学校名称
                $arr4['inschoolidentity']['name']='转入学校标识';
                $arr4['inschoolidentity']['data']=$f3['0']['inschoolidentity'];//转入学校标识
                $arr4['togode']['name']='拟读年级';
                $arr4['togode']['data']=$f3['0']['togode'];//拟读年级
                $arr4['outdate']['name']='转出日期';
                if($f3['0']['outdate']){
                    $arr4['outdate']['data']=date('Y-m-d',$f3['0']['outdate']);//转出日期
                }else{
                    $arr4['outdate']['data']=null;//转出日期
                }
                $arr4['reason']['name']='申请理由';
                $arr4['reason']['data']=$f3['0']['reason'];//申请理由
            }elseif($typename=='休学'){
                $sql3="SELECT offschooldate,returndate,reason FROM `mks_transaction_offschool` WHERE id=".$id." AND scId=".$scId;
                $f3=$dao->query($sql3);
                $arr4['offschooldate']['name']='休学日期';
                if($f3['0']['offschooldate']){
                    $arr4['offschooldate']['data']=date('Y-m-d',$f3['0']['offschooldate']);//休学日期
                }else{
                    $arr4['offschooldate']['data']=null;
                }
                $arr4['returndate']['name']='复学日期';
                if($f3['0']['returndate']){
                    $arr4['returndate']['data']=date('Y-m-d',$f3['0']['returndate']);//复学日期
                }else{
                    $arr4['returndate']['data']=null;//复学日期
                }
                $arr4['reason']['name']='申请理由';
                $arr4['reason']['data']=$f3['0']['reason'];//申请理由
            }elseif($typename=='复学'){
                $sql3="SELECT togodeid,toclassid,reportdate,reason FROM `mks_transaction_reinstating` WHERE id=".$id." AND scId=".$scId;
                $f3=$dao->query($sql3);
                $arr4['togodeid']['name']='拟读年级';
                $arr4['togodeid']['data']=$arr1[$f3['0']['togodeid']];//拟读年级
                $arr4['toclassid']['name']='拟读班级';
                $arr4['toclassid']['data']=$arr2[$f3['0']['toclassid']];//拟读班级
                $arr4['reportdate']['name']='报到日期';
                if($f3['0']['reportdate']){
                    $arr4['reportdate']['data']=date('Y-m-d',$f3['0']['reportdate']);//报到日期
                }else{
                    $arr4['reportdate']['data']=null;//报到日期
                }
                $arr4['reason']['name']='申请理由';
                $arr4['reason']['data']=$f3['0']['reason'];//申请理由
            }elseif($typename=='借读'){
                $sql3="SELECT readbygradeid,readbyclassid,reportdate,outdate,originalschoolname,originalschoolidentity,originalgrade,originalclass,reason FROM `mks_transaction_readby` WHERE id=".$id." AND scId=".$scId;
                $f3=$dao->query($sql3);
                $arr4['readbygradeid']['name']='借读年级';
                $arr4['readbygradeid']['data']=$arr1[$f3['0']['readbygradeid']];//借读年级
                $arr4['readbyclassid']['name']='借读班级';
                $arr4['readbyclassid']['data']=$arr2[$f3['0']['readbyclassid']];//借读班级
                $arr4['reportdate']['name']='报到日期';
                if($f3['0']['reportdate']){
                    $arr4['reportdate']['data']=date('Y-m-d',$f3['0']['reportdate']);//报到日期
                }else{
                    $arr4['reportdate']['data']=null;//报到日期
                }
                //$arr4['outdate']['name']='转出日期';
                //$arr4['outdate']['data']=date('Y-m-d',$f3['0']['outdate']);//转出日期
                $arr4['originalschoolname']['name']='原学校名称';
                $arr4['originalschoolname']['data']=$f3['0']['originalschoolname'];//原学校名称
                $arr4['originalschoolidentity']['name']='原学校标识';
                $arr4['originalschoolidentity']['data']=$f3['0']['originalschoolidentity'];//原学校标识
                $arr4['originalgrade']['name']='原年级';
                $arr4['originalgrade']['data']=$f3['0']['originalgrade'];//原年级
                $arr4['originalclass']['name']='原班级';
                $arr4['originalclass']['data']=$f3['0']['originalclass'];//原班级
                $arr4['reason']['name']='申请理由';
                $arr4['reason']['data']=$f3['0']['reason'];//申请理由
            }elseif($typename=='挂读'){
                $sql3="SELECT readschoolname,readschoolidentity,readgrade,reportdate,reason FROM `mks_transaction_read` WHERE id=".$id." AND scId=".$scId;
                $f3=$dao->query($sql3);
                $arr4['readschoolname']['name']='挂读学校名称';
                $arr4['readschoolname']['data']=$f3['0']['readschoolname'];//挂读学校名称
                $arr4['readschoolidentity']['name']='挂读学校标识';
                $arr4['readschoolidentity']['data']=$f3['0']['readschoolidentity'];//挂读学校标识
                $arr4['readgrade']['name']='挂读年级';
                $arr4['readgrade']['data']=$f3['0']['readgrade'];//挂读年级
                $arr4['reportdate']['name']='报到日期';
                if($f3['0']['reportdate']){
                    $arr4['reportdate']['data']=date('Y-m-d',$f3['0']['reportdate']);//报到日期
                }else{
                    $arr4['reportdate']['data']=null;//报到日期
                }
                $arr4['reason']['name']='申请理由';
                $arr4['reason']['data']=$f3['0']['reason'];//申请理由
            }elseif($typename=='退学'){
                $sql3="SELECT dropoutdate,reason FROM `mks_transaction_dropout` WHERE id=".$id." AND scId=".$scId;
                $f3=$dao->query($sql3);
                $arr4['dropoutdate']['name']='退学日期';
                if($f3['0']['dropoutdate']){
                    $arr4['dropoutdate']['data']=date('Y-m-d',$f3['0']['dropoutdate']);//退学日期
                }else{
                    $arr4['dropoutdate']['data']=null;//退学日期
                }
                $arr4['reason']['name']='申请理由';
                $arr4['reason']['data']=$f3['0']['reason'];//申请理由
            }
            foreach($arr4 as $k=>$v){
                $arr['transaction']['list'][]=$v;
            }
            $this->ajaxReturn($arr);
        }
    }
    public function shenqingbiao(){/********************异动申请表**********************/
        $getSession = $this->get_session('loginCheck',false);
        $scId=$getSession['scId'];
        $roleId=$getSession['roleId'];
        $userId=$getSession['userId'];
        if (I('get.type') == 'find') {/*****************查询***************/
            $state=true;
            if($roleId==$this::$teacherRoleId){
                $dao=M();
                $sql2="SELECT count(*) as count FROM `mks_class` WHERE `userid`=".$userId." and scId=".$scId;
                $f2=$dao->query($sql2);
                $sql3="SELECT count(*) as count FROM mks_grade WHERE userId=".$userId." AND scId=".$scId;
                $f3=$dao->query($sql3);
                if($f2[0]['count']==0&&$f3[0]['count']==0){
                    $state=false;
                }
            }
            $dao = M('transaction_apply');
            $where['scId'] = $scId;
            $find=I('post.find');
            if($find){
                $where['name'] =array('like','%'.$find.'%');
            }
            $f = $dao->field('id,name,rename,suffix,address')->where($where)->select();//修改，返回文件后缀名
            //$f = $dao->field('id,name,rename')->where($where)->select();
            foreach($f as $k=>$v){
                $f[$k]['address']="http://ow365.cn/?i=14474&furl=".$this::$downUrl.$v['address'];
            }
            if(!$f){
                $f=array();
            }
            $arr['data']=$f;
            $arr['state']=$state;
            $this->ajaxReturn($arr);
        } elseif (I('get.type') == 'update') {/****************修改名称*****************/
            $id = I('post.id');
            $name = I('post.name');
            $dao = M('transaction_apply');
            $where['scId'] = $scId;
            $where['id'] = $id;
            $data['name'] = $name;
            $data['lastRecordTime'] = time();
            $f1 = $dao->field('rename')->where($where)->select();
            $arr['return'] = true;
            if ($f1['0']['rename']) {
                $f = $dao->where($where)->save($data);
            }else{
                $arr['return'] = false;
            }
            if($f === false) {
                $arr['return'] = false;
            }
            $this->ajaxReturn($arr);
        }elseif (I('get.type') == 'upload') {/********************上传*******************/
            if (!empty($_FILES)){
                //$data['name'] = I('post.name');
                $data['rename'] = I('post.rename');
                $path = $this::$uploadUrl . 'application/';
                $name = $this->randomkeys(8) . time();
                $upload = new \Think\Upload();
                $upload->maxSize = 2097152;// 设置附件上传大小
                $upload->exts = array('xlsx','xls','doc','docx','wps');// 设置附件上传类型
                $upload->rootPath = './'; // 设置附件上传根目录
                $upload->savePath = $path; // 设置附件上传（子）目录
                $upload->replace = true; // 设置附件是否替换
                //$upload->savePath  =     'cdr/'; // 设置附件上传（子）目录
                $upload->saveName = $name;
                $upload->autoSub = false;
                $info = $upload->uploadOne($_FILES['photo']);
                if (!$info) {// 上传错误提示错误信息
                    $arr['return'] = false;
                }else {
                    $string = $_FILES['photo']['name'];
                    $arraym = explode('.', $string);
                    $exs = $arraym[count($arraym) - 1];
                    unset($arraym[count($arraym) - 1]);
                    $string=implode('.',$arraym);
                    $data['address'] = $path . $name . '.' . $exs;
                    $data['scId'] = $scId;
                    $data['lastRecordTime'] = time();
                    $data['createTime'] = time();
                    $data['name'] =$string;
                    $data['suffix'] =$exs;
                    $dao = M('transaction_apply');
                    $f = $dao->add($data);
                    $arr['return'] = true;
                    if ($f === false) {
                        unlink('.' . $data['address']);
                        $arr['return'] = false;
                    }
                }
            } else {
                $arr['return'] = false;
            }
            $this->ajaxReturn($arr);
        }elseif (I('get.type') == 'export'){/********************导出******************/
            $dao = M('transaction_apply');
            $id=I('get.id');
            $where['id']=$id;
            $where['scId']=$scId;
            $f=$dao->where($where)->field('address,name')->select();
            $url=$this::$downUrl;
            $filename1 =$url.$f['0']['address'];
            $filename='.'.$f['0']['address'];
            $fileinfo = pathinfo($filename);
            ob_end_clean();
            header("Content-Description: File Transfer");
            header('Content-type: ' . $fileinfo['extension']);
            header('Content-Length:' . filesize($filename));
            header('Content-Disposition: attachment; filename='.$f['0']['name'].'.'.$fileinfo['extension']);
//            /header('Location:'.$filename1);
            readfile($filename);
        }elseif(I('get.type')=='del'){/**************删除*************/
            $id=I('post.id');
            M()->startTrans();
            $dao = M('transaction_apply');
            $where['id']=$id;
            $where['scId']=$scId;
            $f=$dao->where($where)->field('address')->select();

            if($f['0']['address']){
                $f1=$dao->where($where)->delete();
                $hr='.' . $f['0']['address'];
                if(file_exists($hr)){
                    if($f1!==false){
                        $f2=unlink('.' . $f['0']['address']);
                    }
                    if($f===false || $f1===false || !$f2){
                        M()->rollback();
                        $arr['return']=false;
                    }else{
                        M()->commit();
                        $arr['return']=true;
                    }
                }else{
                    if($f1===false){
                        M()->rollback();
                        $arr['return']=false;
                    }else{
                        M()->commit();
                        $arr['return']=true;
                    }
                }
            }else{
                $arr['return']=false;
            }
            $this->ajaxReturn($arr);
        }
    }
}