<?php
/**
 * Created by PhpStorm.
 * User: xiaolong
 * Date: 2017/6/22
 * Time: 14:15
 * 用户管理
 */
namespace Home\Controller;
//use Think\Controller;
//use Vendor\PHPExcel\PHPExcel;
class FlowController extends Base {
    public function getFile($dir){
        $handel = opendir($dir);
        while($file = readdir($handel)){
            if(!in_array($file,array('.','..'))){
            }
            $dir = $dir.'/'.$file;
            if(is_dir($dir)){
                $this->getFile($dir);
            }
        }
    }
    public function test(){
        $this->getFile('Public');
    }
    public function upGrade(){
        $allSchool = M('school')->where(array('state' =>1))->select();
        foreach($allSchool as $key => $value){
            $scId = $value['scId'];
            $xGradeMax = $value['xGradeMax'];
            $cGradeMax = $value['cGradeMax'];
            $gGradeMax = $value['gGradeMax'];
            $this->getUp($scId,$xGradeMax,$cGradeMax,$gGradeMax);
        }
    }
    private function getUp($scId,$xGradeMax,$cGradeMax,$gGradeMax){
        $grade = M('grade')->where(array('scId' => $scId))->order('name desc')->select();
        foreach($grade as $key => $value){
            if($value['name'] == $gGradeMax || $value['name'] == $cGradeMax || $value['name'] == $xGradeMax){
                $this->scIdSave($value['gradeid'],$scId);//张志超
                $this->huJun($value['gradeid'],$scId,$value['name']);
                $this->jiangLong($value['gradeid'],$scId);
            }else{
                $user = M('user');
                $nnn = $value['name']+1;
                M('')->startTrans();
                $a1 = M('grade')->where(array('scId' => $scId,'gradeid' => $value['gradeid']))->setField(array('znName' => $this->gradeToZhong($nnn)));
                $a2 = M('jw_schedule')->where(array('scId' => $scId,'gradeId' => $value['gradeid']))->setField(array('gradeName' => $nnn));
                $a3 = M('grade')->where(array('scId' => $scId,'gradeid' => $value['gradeid']))->setField(array('name' => $nnn));
                $a4 = M('user')->where(array('scId' => $scId,'gradeId' => $value['gradeid'],'roleId' => array(array('eq',$this::$studentRoleId),array('eq',$this::$jZroleId),'or')))->setField(array('grade' => $nnn));
                $a5 = M('student_info')->where(array('scId' => $scId,'gradeId' => $value['gradeid']))->setField(array('grade' => $nnn));
                $a6 = M('branch_class')->where(array('scId' => $scId,'gradeId' => $value['gradeid']))->setField(array('grade' => $this->gradeToZhong($nnn)));
                $a7 = M('branch_student')->where(array('scId' => $scId,'gradeId' => $value['gradeid']))->setField(array('preGrade' => $this->gradeToZhong($nnn),'proGrade' => $this->gradeToZhong($nnn)));
                $a8 = M('branch_student_new')->where(array('scId' => $scId,'gradeId' => $value['gradeid']))->setField(array('grade' => $this->gradeToZhong($nnn)));
                $a9 = M('dorm_assign')->where(array('scId' => $scId,'grade' => $value['name']))->setField(array('grade' => $nnn));
                $a11 = M('evaluate_student')->where(array('scId' => $scId,'gradeId' => $value['gradeid']))->setField(array('grade' => $nnn));
                $a12 = M('evaluate_record')->where(array('scId' => $scId,'gradeId' => $value['gradeid']))->setField(array('grade' => $nnn));
                if($a1 === false || $a2 === false || $a3 === false || $a4 === false || $a5 === false || $a6 === false || $a7 === false || $a8 === false || $a9 === false  || $a11 === false || $a12 === false){
                    M('')->rollback();
                }else{
                    M('')->commit();
                }
            }
        }
    }
    private function huJun($gradeId,$scId,$gradeName){
        M('')->startTrans();
        $a1 = M('branch_plan')->where(array('scId' => $scId,'gradeId' => $gradeId))->setField(array('scId'=> -$scId));
        $a2 = M('branch_student')->where(array('scId' => $scId,'gradeId' => $gradeId))->setField(array('scId'=> -$scId));
        $a3 =M('branch_student_new')->where(array('scId' => $scId,'gradeId' => $gradeId))->setField(array('scId'=> -$scId));
        $a4 = M('dorm_assign')->where(array('scId' => $scId,'grade' => $gradeName))->setField(array('scId'=> -$scId));
        $a6 =M('dorm_plan')->where(array('scId' => $scId,'gradeId' => $gradeId))->setField(array('scId'=> -$scId));
        $a7 =M('evaluate_record')->where(array('scId' => $scId,'gradeId' => $gradeId))->setField(array('scId'=> -$scId));
        $a8 =M('evaluate_student')->where(array('scId' => $scId,'gradeId' => $gradeId))->setField(array('scId'=> -$scId));
        $a9 =M('seat_layout')->where(array('scId' => $scId,'grade' => $gradeId))->setField(array('scId'=> -$scId));
        if($a1 === false || $a2 === false || $a3 === false || $a4 === false  || $a6 === false || $a7 === false || $a8 === false  || $a9 === false){
            M('')->rollback();
        }else{
            M('')->commit();
        }
    }
    private function jiangLong($gradeId,$scId){
        M('')->startTrans();
        $a1 = M('user')->where(array('scId' => $scId,'gradeId' => $gradeId,'roleId' => array(array('eq',$this::$studentRoleId),array('eq',$this::$jZroleId),'or')))->setField(array('scId'=> -$scId));
        $a2 = M('student_info')->where(array('scId' => $scId,'gradeId' => $gradeId))->setField(array('scId'=> -$scId));
        $a3 = M('grade')->where(array('scId' => $scId,'gradeid' => $gradeId))->setField(array('scId'=> -$scId));
        $a4 = M('class')->where(array('scId' => $scId,'grade' => $gradeId))->setField(array('scId'=> -$scId));
        $a5 = M('jw_schedule')->where(array('scId' => $scId,'gradeId' => $gradeId))->setField(array('scId'=> -$scId));
        if($a1 === false || $a2 === false || $a3 === false || $a4 === false || $a5 === false){
            M('')->rollback();
        }else{
            M('')->commit();
        }
    }
    public function school(){
        $this->display('school');
    }
    public function addSchool(){
        $url = $this::uploads();
        if($scId = M('school')->add(array(
            'scName' => $_POST['scName'],
            'telephone' => $_POST['telephone'],
            'mail' => $_POST['mail'],
            'province' => $_POST['province'],
            'city' => $_POST['city'],
            'area' => $_POST['area'],
            'ministries' => $_POST['ministries'],
            'type' => $_POST['type'],
            'logo' => $url,
            'createTime' => strtotime(date('Y-m-d H:i:s')),
            'prefix' => $_POST['prefix'],
            'enName' => $_POST['enName']
        ))){
            M('assets_type')->add(
                array(
                    'scId' => $scId,
                    'assetsTypeName' => '资产分类',
                    'createTime' => date('Y-m-d H:i:s'),
                    'assetsTypeParentId' => 0,
                    'leavel' => 1,
                    'ifApprov' => 0,
                    'ifUser' => 0
                )
            );
           M('student_growth_label')->add(array(
                'labelName' => '期末评语',
                'scId' => $scId,
                'createTime' => strtotime(date('Y-m-d H:i:s')),
                'weight' => 2
            ));
            $role = M('role')->where(array('scId' => 0))->select();
            $listAdd = array();
            $date = date('Y-m-d H:i:s');
            $model = M('role_model');
            foreach($role as $key => $value){
                $list = $model->where(array('roleId' => $value['roleId'],'scId' => 2))->select();
                foreach($list as $key1 => $value1){
                    $listAdd[] = array('roleId' => (int)$value['roleId'],'scId' => $scId,'modelId' => (int)$value1['modelId'],'createTime' => $date);
                }
                $modelNameRetrun = $scId.'school'.$value['roleId'].'returndata';
                $navList = $scId.'school'.$value['roleId'].'navList';
                $qxModelName = $scId.'school'.$value['roleId'];
                $model_return = $this->redis_operation('2school'.$value['roleId'].'returndata',0,0,3);
                $modelQx = $this->redis_operation('2school'.$value['roleId'],0,0,3);
                $qxModel = $this->redis_operation('2school'.$value['roleId'].'navList',0,0,3);
                $this->redis_operation($modelNameRetrun,$model_return,0,2);
                $this->redis_operation($qxModelName,$modelQx,0,2);
                $this->redis_operation($navList,$qxModel,0,2);
            }
            $passWord = $this->InitialPassword();
            $number = (int)(date('Y').'00001');
            if($model->addAll($listAdd)){
                M('user')->add(array(
                    'name' => $_POST['scName'],
                    'scId' => $scId,
                    'password' => $this->create_password($passWord,self::$password_key,self::$password_key1),
                    'InitialPassword' =>$passWord,
                    'account' =>  $_POST['prefix'].$number,
                    'number' => $number,
                    'roleId' => $this::$adminRoleId
                ));
                echo 'success'.'account ='.$_POST['prefix'].$number.'password='.$passWord;
            }
        }else{
            echo 'fail';
        }
    }
    public function InitialPassword(){
        $password = '';
        for($i = 0 ; $i<6 ; $i++){
            $password = $password.rand(0,9);
        }
        return $password;
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
    public function index(){
        $school = M('school')->select();
        $this->assign('school',$school);
        $this->display('index');
    }
    //张志超，毕业年级的学生
    private function scIdSave($gradeId,$scId){
        $scId1= $scId;
        $scId2=-$scId1;
        $where['gradeid'] = $gradeId;
        $where['scId'] =$scId1;
        $wherea['gradeId'] = $gradeId;
        $wherea['scId'] =$scId1;
        $dao=M();
        $data['scId']=$scId2;
        M()->startTrans();
        $fa=$dao->where($where)->field('examinationid')->table('mks_examination')->select();
        if($fa[0]['examinationid']){
            foreach($fa as $k=>$v){
                $examinationid[]=$v['examinationid'];
            }
            $where2['scId'] =$scId1;
            $where2['examinationid'] =array('in',$examinationid);
            $f2=$dao->where($where2)->table('mks_examination_arrange')->save($data);
            $f3=$dao->where($where2)->table('mks_examination_level')->save($data);
            $f4=$dao->where($where)->table('mks_examination_number')->save($data);
            $f5=$dao->where($where2)->table('mks_examination_rate')->save($data);
            $f6=$dao->where($where2)->table('mks_examination_results')->save($data);
            $f13=$dao->where($where2)->table('mks_examination_seat')->save($data);
            $f8=$dao->where($where2)->table('mks_examination_score')->save($data);
            $f9=$dao->where($where2)->table('mks_examination_student')->save($data);
            $f10=$dao->where($where2)->table('mks_examination_subject')->save($data);
            $f11=$dao->where($where2)->table('mks_examination_supervision')->save($data);
            $f12=$dao->where($where2)->table('mks_examination_teacher')->save($data);
            $f7=$dao->where($where2)->table('mks_examination_room')->save($data);
            $f1=$dao->where($where2)->table('mks_examination')->save($data);
        }

        $fb=$dao->where($where)->field('programmeId')->table('mks_attainment_programme')->select();
        if($fb['0']['programmeId']){
            foreach($fb as $k=>$v){
                $programmeId[]=$v['programmeId'];
            }
            $where3['scId'] =$scId1;
            $where3['programmeId'] =array('in',$programmeId);
            $fb1=$dao->where($where3)->table('mks_attainment_programme')->save($data);
            $fb2=$dao->where($where3)->table('mks_attainment_score')->save($data);
            $fb3=$dao->where($where3)->table('mks_attainment_student')->save($data);
            $fb4=$dao->where($where3)->table('mks_attainment_teacher')->save($data);
            $fb5=$dao->where($where3)->table('mks_attainment_class')->save($data);
        }
        $fc=$dao->where($wherea)->field('id')->table('mks_user')->select();
        if($fc['0']['id']){
            foreach($fc as $k=>$v){
                $userid[]=$v['id'];
            }
            $where4['scId'] =$scId1;
            $where4['userid'] =array('in',$userid);
            $fc1=$dao->where($where4)->table('mks_transaction_changeinto')->save($data);
            $fc2=$dao->where($where4)->table('mks_transaction_class')->save($data);
            $fc3=$dao->where($where4)->table('mks_transaction_dropout')->save($data);
            $fc4=$dao->where($where4)->table('mks_transaction_offschool')->save($data);
            $fc5=$dao->where($where4)->table('mks_transaction_read')->save($data);
            $fc6=$dao->where($where4)->table('mks_transaction_readby')->save($data);
            $fc7=$dao->where($where4)->table('mks_transaction_reinstating')->save($data);
            $fc8=$dao->where($where4)->table('mks_transaction_turnout')->save($data);
        }
        if($f1===false || $f2===false || $f3===false || $f4===false || $f5===false || $f6===false || $f7===false || $f8===false|| $f9===false|| $f10===false|| $f11===false|| $f12===false|| $f13===false
            || $fb1===false || $fb2===false || $fb3===false || $fb4===false || $fb5===false
            || $fc1===false || $fc2===false || $fc3===false || $fc4===false || $fc5===false || $fc6===false || $fc7===false || $fc8===false){
            M()->rollback();
            return false;
        }else{
            M()->commit();
            return true;
        }
    }
    //张志超
}