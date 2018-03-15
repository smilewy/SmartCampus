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
class AssetsController extends Base
{
    private function findChild(&$arr,$id){
        $childs=array();
        foreach ($arr as $k => $v){
            if($v['assetsTypeParentId']== $id){
                $childs[]=$v;
            }
        }
        return $childs;
    }
    private function build_tree($rows,$root_id){
        $childs=$this->findChild($rows,$root_id);
        if(empty($childs)){
            return null;
        }
        foreach ($childs as $k => $v){
            $rescurTree=$this->build_tree($rows,$v['assetsTypeId']);
            if( null != $rescurTree){
                $childs[$k]['childs']=$rescurTree;
            }
        }
        return $childs;
    }
    public function assetsType(){//资产分类
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getAssetsType'){
            $data = M('assets_type')->where(array('scId' => $scId))->select();
            foreach($data as $key => $value){
                if($value['ifUser'] == 1){
                    $data[$key]['ifUser'] = true;
                }else{
                    $data[$key]['ifUser'] = false;
                }
            }
            $data=$this->build_tree($data,0);
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'delete'){
            $data = M('assets_type')->where(array('scId' => $scId))->select();
            $child = $this->findChild($data,I('post.assetsTypeId'));
            $ch = $this->getCh(I('post.assetsTypeId'),$data,$scId);
            if(!count($child)){
                $ch = true;
            }
            if($ch){
                $this->deleteTree(I('post.assetsTypeId'),$data,$scId);
                if(M('assets_type')->where(array('scId' => $scId,'leavel' => array('neq',1),'assetsTypeId' =>I('post.assetsTypeId')))->delete()){
                    $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
                }
                $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
            }
            $this->returnJson(array('statu' => 2, 'message' => '子类有内容并且在使用'),true);
        }
        if($type == 'create'){
            $assetsTypeParentId = I('post.assetsTypeId');
            $leavel = I('post.leavel');
            $assetsTypeName = I('post.assetsTypeName');
            if($id =M('assets_type')->add(array(
                'createTime' => date('Y-m-d H:i:s'),
                'assetsTypeName' => $assetsTypeName,
                'scId' => $scId,
                'assetsTypeParentId' => $assetsTypeParentId,
                'leavel' => $leavel++
            ))){
                $array = array(
                    'createTime' => date('Y-m-d H:i:s'),
                    'assetsTypeName' => $assetsTypeName,
                    'scId' => $scId,
                    'assetsTypeParentId' => $assetsTypeParentId,
                    'leavel' => $leavel,
                    'assetsTypeId' => $id
                );
                $this->returnJson(array('statu' => 1, 'message' => 'success','data' =>$array ),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'success'),true);
        }
        if($type == 'updata'){
            $assetsTypeId = I('post.assetsTypeId');
            $assetsTypeName = I('post.assetsTypeName');
            if(M('assets_type')->where(array('scId' => $scId,'assetsTypeId' => $assetsTypeId))->setField(array(
                'assetsTypeName' => $assetsTypeName,
            ))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'userType'){
            $data = M('assets_type')->where(array('scId' => $scId))->select();
            $assetsTypeId = I('post.assetsTypeId');
            if($assetsTypeId == 'all'){
                M('assets_type')->where(array('scId' => $scId))->setField(array('ifUser' =>1));
            }
            if($assetsTypeId == 'noall'){
                M('assets_type')->where(array('scId' => $scId))->setField(array('ifUser' =>0));
            }
            $array = M('assets_type')->where(array('scId' => $scId,'assetsTypeId' => $assetsTypeId))->find();
            $userType = 0;
            if($array['ifUser'] == 0){
                $userType = 1;
            }
            $this->userTree($assetsTypeId,$data,$scId,$userType);
            if($userType){
                M('assets_type')->where(array('scId' => $scId,'assetsTypeId' =>$assetsTypeId))->setField(array('ifUser' =>1));
            }else{
                M('assets_type')->where(array('scId' => $scId,'assetsTypeId' =>$assetsTypeId))->setField(array('ifUser' =>0));
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
        }
    }
    private function userTree($assetsTypeId,$data,$scId,$userType){
        foreach($data as $key => $value){
            if($assetsTypeId == $value['assetsTypeParentId']){
                if($userType){
                    M('assets_type')->where(array('scId' => $scId,'assetsTypeId' => $value['assetsTypeId']))->setField(array('ifUser' =>1));
                }else{
                    M('assets_type')->where(array('scId' => $scId,'assetsTypeId' => $value['assetsTypeId']))->setField(array('ifUser' =>0));
                }
                $this->userTree($value['assetsTypeId'],$data,$scId,$userType);
            }
        }
        return true;
    }
    private function deleteTree($assetsTypeId,$data,$scId){
        foreach($data as $key => $value){
            if($assetsTypeId == $value['assetsTypeParentId']){
                M('assets_type')->where(array('scId' => $scId,'leavel' => array('neq',1),'assetsTypeId' => $value['assetsTypeId']))->delete();
                $this->deleteTree($value['assetsTypeId'],$data,$scId);
            }
        }
        return true;
    }
    public function approverSet(){//审批设置
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'getAssetsType'){
            $data = M('assets_type')->where(array('scId' => $scId,'ifUser' => 1))->select();
            $data=$this->build_tree($data,0);
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getApproverUser'){
            $studentRole = $this::$studentRoleId;
            $jzRole = $this::$jZroleId;
            $adminRole = $this::$adminRoleId;
            $user = M('')->query("select id,name,roleId,departmentId,department from mks_user where scId= $scId AND roleId!=$studentRole AND roleId!=$jzRole AND roleId!=$adminRole");
            $roleUser = array();
            foreach($user as $key => $value){
                if(!$value['department']){
                    $roleUser[$value['departmentId']]['name'] = '其他职工';
                }else{
                    $roleUser[$value['departmentId']]['name'] = $value['department'];
                }
                $roleUser[$value['departmentId']]['data'][] = $value;
            }
            $return = array();
            foreach($roleUser as $key => $value){
                $return[] = $value;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $return),true);
        }
        if($type == 'createApproverUser'){
            $id  = I('post.id');
            $assetsTypeId = I('post.assetsTypeId');
            $ifApprov = I('post.ifApprov');
            $count = count($id);
            $i = 1;
            $idid = '';
            foreach($id as $key => $value){
                if($count == $i){
                    $idid = $idid.$value;
                }else{
                    $idid = $value.','.$idid;
                }
                $i++;
            }
            $id = $idid;
            if(M('assets_type')->where(array('scId' => $scId,'assetsTypeId' =>$assetsTypeId))->setField(array('ifApprov' =>$ifApprov,'approver' => $id))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'getApproverUserList'){
            $assetsTypeId = I('get.assetsTypeId');
            $type = M('assets_type')->where(array('scId' => $scId,'assetsTypeId' =>$assetsTypeId))->find();
            $userIdList = $type['approver'];
            $user = M('')->query("select id,name from mks_user WHERE  scId= $scId AND id IN($userIdList)");
            if(!$user){
                $user = array();
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $user),true);
        }
    }
    public function batchImport(){//批量导入
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'import'){
            define('MAX_SIZE_FILE_UPLOAD', '10000000' );
            //header('content-type:text/html;charset=utf-8');
            if (!empty($_FILES)/* && $_POST['token'] == $verifyToken*/) {
                $tempFile = $_FILES['userFile']['tmp_name'];
                $fileTypes = array('xlsx', 'doc', 'xls'); // File extensions
                $fileParts = pathinfo($_FILES['userFile']['name']);
                $flieExtension = $fileParts['extension'];
                $targetFile = md5(uniqid('gsfdg')) . '.' . $flieExtension;
                if (in_array($flieExtension, $fileTypes)) {
                    if (move_uploaded_file($tempFile, $targetFile)) {
                        vendor('PHPExcel.PHPExcel');
                        $objReader = 0;
                        if($flieExtension == 'xls'){
                            $objReader = new \PHPExcel_Reader_Excel5();
                        }
                        if($flieExtension == 'xlsx'){
                            $objReader = new \PHPExcel_Reader_Excel2007();
                        }
                        $objPHPExcel = $objReader->load($targetFile); //Excel 路径
                        $sheet = $objPHPExcel->getSheet(0);
                        $highestRow = $sheet->getHighestRow(); // 取得总行数
                        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
                        $strs=array();//USER表信息；
                        $b = 0;
                        unset($targetFile);
                        $max = M('assets_list')->where(array('scId' => $scId))->max('assetsId');
                        for ($j=1;$j<=$highestRow;$j++){
                            $i = 0;
                            for($k='A';$k<=$highestColumn;$k++){//从A列读取数据
                                //实测在excel中，如果某单元格的值包含了||||||导入的数据会为空
                                /*$allNum = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                for($kk = 0; $kk<$allNum; $kk++){

                                }*/
                                $i++;
                                if($k == 'A'){
                                    $strs[$b]['assetsName'] =(string) $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                }
                                if($k == 'B'){
                                    $strs[$b]['assetsTypeId'] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                }
                                if($k == 'C'){
                                    $strs[$b]['spec'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                }
                                if($k == 'D'){
                                    $strs[$b]['brandModel'] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                }
                                if($k == 'E'){
                                    $strs[$b]['supplier'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                }
                                if($k == 'F'){
                                    $strs[$b]['unit'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                }
                                if($k == 'G'){
                                    $strs[$b]['onePrice'] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                }
                                if($k == 'H'){
                                    $strs[$b]['num'] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                }
                                if($k == 'I'){
                                    $strs[$b]['storageLocation'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                }
                                if($k == 'J'){
                                    $strs[$b]['remarks'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                }
                                if($k == 'K'){
                                    $strs[$b]['inStorageTime'] = (string)$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                                }
                            }
                            $strs[$b]['createTime'] = date('Y-m-d H:i:s');
                            $strs[$b]['scId'] = $scId;
                            $b++;
                        }
                        unset($strs[0]);
                        rsort($strs);
                        $this->returnJson(array('message' => 'success','statu' => 1,'data' => $strs),true);
                    }
                    $this->returnJson(array('message' => 'upload fail','statu' => 0),true);
                }
                $this->returnJson(array('message' => '上传格式不对','statu' => 0),true);
            }
            $this->returnJson(array('message' => 'have on fail','statu' => 0),true);
        }
        if($type == 'submit'){
            $max = M('assets_list')->where(array('scId' => $scId))->max('assetsId');
            $DDDDD = M('assets_type')->field('assetsTypeId')->where(array('scId' => $scId))->select();
            $dddddNew = array();
            foreach ($DDDDD as $key => $value){
                $dddddNew[$value['assetsTypeId']] = 1;
            }
            $data = I('post.data');
            $newList = array();
            $data11 =  date('Y-m-d H:i:s');
            foreach($data as $key => $value){
                if(isset($dddddNew[$value['assetsTypeId']])){
                    if(is_numeric($value['num']) && $value['num']>0){
                        if(is_numeric($value['onePrice']) && $value['onePrice'] > 0){
                        }else{
                            $this->returnJson(array('message' => '价格必须要数字','statu' => 0),true);
                        }
                        $numnum = $value['num'];
                        for($i = 0 ; $i < $numnum  ; $i++){
                            $num = 10000;
                            $max++;
                            $num = $num+$max;
                            $value['num'] = 1;
                            $value['assetsNumber'] = date('Y').date('m').date('d').$num;
                            $newList[] = $value;
                        }
                    }else{
                        $this->returnJson(array('message' => '资产数量只能为正整数','statu' => 0),true);
                    }
                }else{
                    $this->returnJson(array('message' => '资产分类代码与系统不一致','statu' => 0),true);
                }
            }
            foreach($newList as $key => $value){
                M('assets_list')->add($value);
            }
            $this->returnJson(array('message' => '上传成功','statu' => 1),true);
        }
        if($type == 'getAssetsList'){
            $data = M('assets_list')->where(array('scId' => $scId,'state' =>1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value['assetsTypeName'];
            }
            foreach($data as $key => $value){
                $data[$key]['assetsTypeName'] = $typeRe[$value['assetsTypeId']];
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getAssetsListValue'){
            $data = M('assets_list')->where(array('scId' => $scId,'state' =>1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value['assetsTypeName'];
            }
            foreach($data as $key => $value){
                $data[$key]['assetsTypeName'] = $typeRe[$value['assetsTypeId']];
            }
            $valueData = I('get.valueData');
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
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getAssetsListSort'){
            $data = M('assets_list')->where(array('scId' => $scId,'state' =>1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value['assetsTypeName'];
            }
            foreach($data as $key => $value){
                $data[$key]['assetsTypeName'] = $typeRe[$value['assetsTypeId']];
            }
            $returnList = array();
            $sort = I('get.sort');
            $sortData = I('get.sortData');
            $returnList = array();
            $data1 = $data;
            foreach($data as $key => $value){
                $max = array();
                $kk = 0;
               foreach($data1 as $key1 => $value1){
                  if($value1[$sort]>= $max[$sort]){
                      $max = $value1;
                      $kk = $key1;
                  }
               }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if($sortData == 'inc'){
               krsort($returnList);
            }
            $data = $returnList;
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getAssetsListExport'){
            $data = M('assets_list')->where(array('scId' => $scId,'state' =>1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value['assetsTypeName'];
            }
            foreach($data as $key => $value){
                $data[$key]['assetsTypeName'] = $typeRe[$value['assetsTypeId']];
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'downloadModel'){
            $url = $this::$downUrl;
            header("Location: $url/Public/upload/downloadexcel/assets.xlsx");
        }
        if($type == 'export'){
            $data = M('assets_list')->where(array('scId' => $scId))->select();
            $tr = array(
                '0' => array(
                    'en' => 'assetsName',
                    'zh' => '资产名称'
                ),
                '1' => array(
                    'en' => 'assetsTypeId',
                    'zh' => '分类代码'
                ),  '2' => array(
                    'en' => 'spec',
                    'zh' => '规格'
                ), '3' => array(
                    'en' => 'brandModel',
                    'zh' => '品牌型号'
                ), '4' => array(
                    'en' => 'supplier',
                    'zh' => '供应商'
                ), '5' => array(
                    'en' => 'unit',
                    'zh' => '单位'
                ), '6' => array(
                    'en' => 'onePrice',
                    'zh' => '单价'
                ), '7' => array(
                    'en' => 'num',
                    'zh' => '数量'
                ),
                '8' => array(
                    'en' => 'storageLocation',
                    'zh' => '存放地址'
                ),
                '9' => array(
                    'en' => 'approver',
                    'zh' => '责任人'
                ),
                '10' => array(
                    'en' => 'userAddress',
                    'zh' => '使用地址'
                ),
                '11' => array(
                    'en' => 'assetsNumber',
                    'zh' => '资产编号'
                ),

            );
            $this::export($data,$tr);
        }
    }
    public function assetStoreroom(){//资产入库
        $type =  I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        if($type == 'create'){
            $max = M('assets_list')->max('assetsId');
            $max = $max+2;
            for($i = 0 ; $i< $_POST['num'];$i++){
                $str = 10000+$max;
                $data = array(
                    'assetsName' => I('post.assetsName'),
                    'assetsTypeId' => I('post.assetsTypeId'),
                    'supplier' => I('post.supplier'),
                    'num' => 1,
                    'unit' =>I('post.unit'),
                    'brandModel' => I('post.brandModel'),
                    'spec' => I('post.spec'),
                    'onePrice' => I('post.onePrice'),
                    'createTime' =>date('Y-m-d H:i:s'),
                    'storageLocation' => I('post.storageLocation'),
                    'remarks' => I('post.Remarks'),
                    'scId' => $scId,
                    'inStorageTime' => I('post.inStorageTime'),
                    'assetsNumber' => date('Y').date('m').date('d').$str,
                );
                $max++;
                M('assets_list')->add($data);
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
        }
        if($type == 'getAssetsType'){
            $data = M('assets_type')->where(array('scId' => $scId,'ifUser' =>1))->select();
            $data=$this->build_tree($data,0);
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'export'){
            $data = M('assets_list')->where(array('scId' => $scId))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value['assetsTypeName'];
            }
            foreach($data as $key => $value){
                $data[$key]['assetsTypeName'] = $typeRe[$value['assetsTypeId']];
            }
            $tr = array(
                '0' => array(
                    'en' => 'assetsName',
                    'zh' => '资产名称'
                ),
                '1' => array(
                    'en' => 'assetsNumber',
                    'zh' => '资产编号'
                ),
                '2' => array(
                    'en' => 'assetsTypeName',
                    'zh' => '资产分类'
                ),
                '3' => array(
                    'en' => 'assetsTypeId',
                    'zh' => '分类代码'
                ),
                '4' => array(
                    'en' => 'inStorageTime',
                    'zh' => '入库日期'
                ),
                '5' => array(
                    'en' => 'createTime',
                    'zh' => '创建时间'
                ),
                '6' => array(
                    'en' => 'supplier',
                    'zh' => '供应商'
                ),
                '7' => array(
                    'en' => 'spec',
                    'zh' => '规格'
                ),
                '8' => array(
                    'en' => 'unit',
                    'zh' => '单位'
                ),
                '9' => array(
                    'en' => 'brandModel',
                    'zh' => '品牌型号'
                ),
                '10' => array(
                    'en' => 'onePrice',
                    'zh' => '单价'
                ),
                '11' => array(
                    'en' => 'num',
                    'zh' => '数量'
                ),
                '12' => array(
                    'en' => 'storageLocation',
                    'zh' => '存放地址'
                ),
                '14' => array(
                    'en' => 'remarks',
                    'zh' => '备注'
                ),
            );
            $this->export($data,$tr);
        }
        if($type == 'getAssetsList'){
            $data = M('assets_list')->where(array('scId' => $scId,'state' =>1))->order('createTime desc')->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value['assetsTypeName'];
            }
            foreach($data as $key => $value){
                $data[$key]['assetsTypeName'] = $typeRe[$value['assetsTypeId']];
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getAssetsListValue'){
            $data = M('assets_list')->where(array('scId' => $scId,'state' =>1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value['assetsTypeName'];
            }
            foreach($data as $key => $value){
                $data[$key]['assetsTypeName'] = $typeRe[$value['assetsTypeId']];
            }
            $valueData = I('get.valueData');
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
                        unset($value['state']);
                        $returnList[] = $value;
                    }
                }
                $data = $returnList;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getAssetsListSort'){
            $data = M('assets_list')->where(array('scId' => $scId,'state' =>1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value['assetsTypeName'];
            }
            foreach($data as $key => $value){
                $data[$key]['assetsTypeName'] = $typeRe[$value['assetsTypeId']];
            }
            $returnList = array();
            $sort = I('get.sort');
            $sortData = I('get.sortData');
            $returnList = array();
            $data1 = $data;
            foreach($data as $key => $value){
                $max = array();
                $kk = 0;
                foreach($data1 as $key1 => $value1){
                    if($value1[$sort]>= $max[$sort]){
                        $max = $value1;
                        $kk = $key1;
                    }
                }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if($sortData == 'inc'){
                krsort($returnList);
            }
            $data = $returnList;
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'delete'){
            $assetsId = I('post.assetsId');
            $i = 0;
            foreach($assetsId as $key =>$value){
                if(M('assets_list')->where(array('scId' => $scId,'assetsId' => $value))->setField(array('state' => 0))){
                    $i++;
                }
            }
            if($i == count($assetsId)){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'updata'){
            $data =I('post.data');
            $assetsId = $data['assetsId'];
            unset($data['assetsId']);
            unset($data['assetsTypeName']);
            if($data){
                if(M('assets_list')->where(array('scId' => $scId,'assetsId' =>$assetsId))->setField($data) === false){
                    $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
                }
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
    }
    private function getApper($assetsTypeId,$data,$scId){
        global $arr;
            foreach($data as $key => $value){
                if($assetsTypeId == $value['assetsTypeId']){
                    if($value['approver']){
                        $arr[] = $value;
                    }
                    $this->getApper($value['assetsTypeParentId'],$data,$scId);
                }
            }
        return $arr;
    }
    private function getCh($assetsTypeId,$data,$scId){
        global $arr;
        foreach($data as $key => $value){
            if($assetsTypeId == $value['assetsTypeParentId']){
                if($value['ifUser']){
                    return false;
                }
                $arr[] = $value;
                $this->getCh($value['assetsTypeId'],$data,$scId);
            }
        }
        return $arr;
    }
    public function assetBrrowAndRetrun(){//资产借还
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userNameName = $jbXn['name'];
        if($type == 'getAssetsType'){
            $data = M('assets_type')->where(array('scId' => $scId,'ifUser' => 1))->select();
            $data=$this->build_tree($data,0);
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getListSort'){ //排序
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'ifRecive' => 0,'state' => 1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    $dateRe[] = $value;
                }
            }
            $reciveList = M('assets_revice')->where(array('userId' => $userId,'reviceStatu' => 1,'resetStatu' => 0,'scId' => $scId))->order('createTime')->select();
            foreach($dateRe as $key => $value){
                foreach($reciveList as $key1 => $value1){
                    if($value['assetsId'] == $value1['assetsId']){
                        $dateRe[$key]['ifRecive'] = 2;
                    }
                }
            }
            $sort = I('get.sort');
            $sortData = I('get.sortData');
            $returnList = array();
            $data = $dateRe;
            $data1 = $data;
            foreach($data as $key => $value){
                $max = array();
                $kk = 0;
                foreach($data1 as $key1 => $value1){
                    if($value1[$sort]>= $max[$sort]){
                        $max = $value1;
                        $kk = $key1;
                    }
                }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if($sortData == 'inc'){
                krsort($returnList);
            }
            $data = $returnList;
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getUser'){
            $user = M('user')->field('id,name,roleId')->where(array('scId' => $scId))->select();
            $studentRole = $this::$studentRoleId;
            $jzRole = $this::$jZroleId;
            $adminRole = $this::$adminRoleId;
            $user = M('')->query("select id,name,roleId from mks_user where scId= $scId AND roleId!=$studentRole AND roleId!=$jzRole AND roleId!=$adminRole");
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $user),true);
        }
        if($type == 'getListValue'){//搜索
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'ifRecive' => 0,'state' => 1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    $dateRe[] = $value;
                }
            }
            $reciveList = M('assets_revice')->where(array('userId' => $userId,'reviceStatu' => 1,'resetStatu' => 0,'scId' => $scId))->order('createTime')->select();
            foreach($dateRe as $key => $value){
                foreach($reciveList as $key1 => $value1){
                    if($value['assetsId'] == $value1['assetsId']){
                        $dateRe[$key]['ifRecive'] = 2;
                    }
                }
            }
            $data = $dateRe;
            $valueData = I('get.valueData');
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
                        unset($value['state']);
                        $returnList[] = $value;
                    }
                }
                $data = $returnList;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getList'){
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'state' => 1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    $dateRe[] = $value;
                }
            }
            $reciveList = M('assets_revice')->where(array('userId' => $userId,'reviceStatu' => 1,'resetStatu' => 0,'scId' => $scId))->order('createTime')->select();
            foreach($dateRe as $key => $value){
                foreach($reciveList as $key1 => $value1){
                    if($value['assetsId'] == $value1['assetsId']){
                        $dateRe[$key]['ifRecive'] = 2;
                    }
                }
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $dateRe),true);
        }
        if($type == 'addExport'){
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'outStatu' =>0,'state' => 1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    if($value['ifRecive']){
                        $value['ifRecive'] = '已领用';
                    }else{
                        $value['ifRecive'] = '未领用';
                    }
                    $value['assetsTypeName'] =  $typeRe[$value['assetsTypeId']]['assetsTypeName'];
                    $dateRe[] = $value;
                }else{

                }
            }
            $tr = array(
                '0' => array(
                    'en' => 'assetsName',
                    'zh' => '资产名称'
                ),
                '1' => array(
                    'en' => 'assetsNumber',
                    'zh' => '资产编号'
                ),
                '2' => array(
                    'en' => 'gbCode',
                    'zh' => '国际代码'
                ),
                '3' => array(
                    'en' => 'onePrice',
                    'zh' => '单价'
                ),
                '4' => array(
                    'en' => 'storageLocation',
                    'zh' => '存放地址'
                ),
                '5' => array(
                    'en' => 'spec',
                    'zh' => '规格'
                ),
                '6' => array(
                    'en' => 'brandModel',
                    'zh' => '品牌型号'
                ),
                '7' => array(
                    'en' => 'supplier',
                    'zh' => '供应商'
                ),
                '8' => array(
                    'en' => 'unit',
                    'zh' => '单位'
                ),
                '9' => array(
                    'en' => 'remarks',
                    'zh' => '备注'
                ),
                '10' => array(
                    'en' => 'ifRecive',
                    'zh' => '领取状态'
                )
            );
            $this->export($dateRe, $tr);
        }
        if($type == 'returnRecord'){
            if($this::$adminRoleId){
                $outData = M('assets_revice')->where("scId = $scId and (backStatu = 1 or backStatu=2  or backStatu=3)")->select();
            }else{
                $outData = M('assets_revice')->where("scId = $scId userId = $userId (and backStatu = 1 or backStatu=2  or backStatu=3)")->select();
            }
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $outData),true);
        }
        if($type == 'returnRecordValue'){
            if($this::$adminRoleId){
                $outData = M('assets_revice')->where("scId = $scId and (backStatu = 1 or backStatu=2)")->select();
            }else{
                $outData = M('assets_revice')->where("scId = $scId userId = $userId and (backStatu = 1 or backStatu=2)")->select();
            }
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
            }
            $data = $outData;
            $valueData = I('get.valueData');
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
                        unset($value['state']);
                        $returnList[] = $value;
                    }
                }
                $data = $returnList;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'returnRecordSort'){
            if($this::$adminRoleId){
                $outData = M('assets_revice')->where("scId = $scId and (backStatu = 1 or backStatu=2)")->select();
            }else{
                $outData = M('assets_revice')->where("scId = $scId userId = $userId and (backStatu = 1 or backStatu=2)")->select();
            }
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
            }
            $sort = I('get.sort');
            $sortData = I('get.sortData');
            $returnList = array();
            $data = $outData;
            $data1 = $data;
            foreach($data as $key => $value){
                $max = array();
                $kk = 0;
                foreach($data1 as $key1 => $value1){
                    if($value1[$sort]>= $max[$sort]){
                        $max = $value1;
                        $kk = $key1;
                    }
                }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if($sortData == 'inc'){
                krsort($returnList);
            }
            $data = $returnList;
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'exportReurnCecord'){
            $outData = M('assets_revice')->where("scId = $scId and (backStatu = 1 or backStatu=2)")->select();
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
            }
            $tr = array(
                '0' => array(
                    'en' => 'assetsName',
                    'zh' => '资产名称'
                ),
                '1' => array(
                    'en' => 'assetsNumber',
                    'zh' => '资产编号'
                ),
                '2' => array(
                    'en' => 'assetsTypeId',
                    'zh' => '分类代码'
                ),
                '3' => array(
                    'en' => 'onePrice',
                    'zh' => '单价'
                ),
                '4' => array(
                    'en' => 'backTime',
                    'zh' => '归还日期'
                ),
                '5' => array(
                    'en' => 'createTime',
                    'zh' => '创建日期'
                ),
                '6' => array(
                    'en' => 'backUserId',
                    'zh' => '归还人'
                ),
                '7' => array(
                    'en' => 'createUserName',
                    'zh' => '创建人'
                ),
                '8' => array(
                    'en' => 'explain',
                    'zh' => '说明'
                ),
            );
            $this->export($outData, $tr);
        }
        if($type == 'brrowRecord'){
            if($this::$adminRoleId){
                $outData = M('assets_revice')->where(array('scId' => $scId))->select();
            }else{
                $outData = M('assets_revice')->where(array('scId' => $scId,'userId' => $userId))->select();
            }
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
            }
            foreach($outData as $key => $value){
                if($value['resetStatu'] == 1){
                    $outData[$key]['reviceStatu'] = 3;
                }
                if($value['backStatu'] == 1){
                    $outData[$key]['reviceStatu'] = 5;
                }
                if($value['backStatu'] == 2){
                    $outData[$key]['reviceStatu'] = 4;
                }
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $outData),true);
        }
        if($type == 'brrowRecordValue'){
            if($this::$adminRoleId){
                $outData = M('assets_revice')->where(array('scId' => $scId))->select();
            }else{
                $outData = M('assets_revice')->where(array('scId' => $scId,'userId' => $userId))->select();
            }
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
            }
            foreach($outData as $key => $value){
                if($value['resetStatu'] == 1){
                    $outData[$key]['reviceStatu'] = 3;
                }
                if($value['backStatu'] == 1){
                    $outData[$key]['reviceStatu'] = 5;
                }
                if($value['backStatu'] == 2){
                    $outData[$key]['reviceStatu'] = 4;
                }
            }
            $data = $outData;
            $valueData = I('get.valueData');
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
                        unset($value['state']);
                        $returnList[] = $value;
                    }
                }
                $data = $returnList;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'brrowRecordSort'){
            if($this::$adminRoleId){
                $outData = M('assets_revice')->where(array('scId' => $scId))->select();
            }else{
                $outData = M('assets_revice')->where(array('scId' => $scId,'userId' => $userId))->select();
            }
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
            }
            foreach($outData as $key => $value){
                if($value['resetStatu'] == 1){
                    $outData[$key]['reviceStatu'] = 3;
                }
                if($value['backStatu'] == 1){
                    $outData[$key]['reviceStatu'] = 5;
                }
                if($value['backStatu'] == 2){
                    $outData[$key]['reviceStatu'] = 4;
                }
            }
            $sort = I('get.sort');
            $sortData = I('get.sortData');
            $returnList = array();
            $data = $outData;
            $data1 = $data;
            foreach($data as $key => $value){
                $max = array();
                $kk = 0;
                foreach($data1 as $key1 => $value1){
                    if($value1[$sort]>= $max[$sort]){
                        $max = $value1;
                        $kk = $key1;
                    }
                }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if($sortData == 'inc'){
                krsort($returnList);
            }
            $data = $returnList;
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'updataBrrow'){
            $reciveId= I('post.reciveId');
            $reciveTime = I('post.reciveTime');
            $useAddress = I('post.useAddress');
            $explain = I('post.explain');
            if(M('assets_revice')->where(array('reciveId' => $reciveId,'scId' => $scId))->setField(array('reciveTime' => $reciveTime,'useAddress' => $useAddress,'explain' => $explain))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'updataBack'){
            $reciveId= I('post.reciveId');
            $backTime = I('post.backTime');
            $backExplain = I('post.backExplain');
            if(M('assets_revice')->where(array('reciveId' => $reciveId,'scId' => $scId))->setField(array('backTime' => $backTime,'backExplain' => $backExplain))){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'return'){
            $reciveId = I('get.reciveId');
            foreach($reciveId as $key => $value){
                $userUser = M('assets_revice')->where(array('scId' => $scId,'reciveId' => $value))->find();
                if($userUser['backStatu']== 2 || $userUser['backStatu']== 1){
                    $this->returnJson(array('statu' => 0, 'message' => '已归还和申请中的不能归还'),true);
                }
                if($userUser['reviceStatu'] == 2){

                }else{
                    $this->returnJson(array('statu' => 0, 'message' => '未领取成功的不能归还'),true);
                }
                if(M('assets_revice')->where(array('scId' => $scId,'reciveId' => $value))->setField(
                    array('backStatu' => 1,'backUserId' => $userUser['createUserId'],'backUserName' => $userUser['createUserName'])
                )){
                    $assets = M('assets_list')->where(array('scId' => $scId,'assetsId' => $userUser['assetsId']))->find();
                    M('assets_approve')->add(array(
                        'approveName' =>$userUser['userName'].'还'.$assets['assetsName'],
                        'eventId' => $value,
                        'scId' => $scId,
                        'businessTime' =>$userUser['reciveTime'],
                        'createTime' =>date('Y-m-d H:i:s'),
                        'eventType' => 2,
                    ));
                    //$assetsId = M('assets_revice')->field('assetsId')->where(array('scId' => $scId,'reciveId' => $value))->find();
                    //$assetsId = $assetsId['assetsId'];
                    //M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('ifRecive' => 0));
                }
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
        }
        if($type =='revoke'){
            $reciveId =I('get.reciveId');
            foreach($reciveId as $key => $value){
                $assetsId = M('assets_revice')->field('assetsId,backStatu,approveStatu')->where(array('scId' => $scId,'reciveId' => $value))->find();
                if($assetsId['backStatu'] || $assetsId['approveStatu']){
                    $this->returnJson(array('statu' => 0, 'message' => '已经归还，和审批过后的不能撤销'),true);
                }
                if(M('assets_revice')->where(array('scId' => $scId,'reciveId' => $value))->setField(array('resetStatu' => 1))){
                    $assetsId = M('assets_revice')->field('assetsId')->where(array('scId' => $scId,'reciveId' => $value))->find();
                    $assetsId = $assetsId['assetsId'];
                    M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('ifRecive' => 0));
                    M('assets_approve')->where(array('scId' => $scId,'eventId' => $value,'eventType' => 1))->delete();
                }
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
        }
        if($type =='export'){
            $outData = M('assets_revice')->where(array('scId' => $scId))->select();
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
                $outData[$key]['state'] = '';
                if($value['resetStatu']){
                    $outData[$key]['state'] = '已撤销';
                }else{
                    $outData[$key]['state'] = '未撤销';
                }
                if($value['backStatu']){
                    $outData[$key]['state'] = $outData[$key]['state'].',已归还';
                }else{
                    $outData[$key]['state'] = $outData[$key]['state'].',未归还';
                }
            }
            $tr = array(
                '0' => array(
                    'en' => 'assetsName',
                    'zh' => '资产名称'
                ),
                '1' => array(
                    'en' => 'assetsNumber',
                    'zh' => '资产编号'
                ),
                '2' => array(
                    'en' => 'assetsTypeId',
                    'zh' => '分类代码'
                ),
                '3' => array(
                    'en' => 'onePrice',
                    'zh' => '单价'
                ),
                '4' => array(
                    'en' => 'reciveTime',
                    'zh' => '领用日期'
                ),
                '5' => array(
                    'en' => 'createTime',
                    'zh' => '创建日期'
                ),
                '6' => array(
                    'en' => 'userName',
                    'zh' => '领用人'
                ),
                '7' => array(
                    'en' => 'createUserName',
                    'zh' => '创建人'
                ),
                '8' => array(
                    'en' => 'unit',
                    'zh' => '单位'
                ),
                '9' => array(
                    'en' => 'useAddress',
                    'zh' => '使用地址'
                ),
                '10' => array(
                    'en' => 'explain',
                    'zh' => '说明'
                ),
                '11' => array(
                'en' => 'state',
                'zh' => '状态'
            )
            );
            $this->export($outData, $tr);
        }
        if($type == 'brrowGo'){//创建借审批
            $assetsId = I('get.assetsId');
            $data = array(
                'assetsId' => $assetsId,
                'createTime' => date('Y-m-d H:i:s'),
                'scId' => $scId,
                'userId' => $userId,
                'userName' => $userNameName,
                'scId' => $scId,
                'approveStatu' => 0,
                'reviceStatu' => 0
            );
            if($ddd = M('assets_revice')->add($data)){
                M('assets_approve')->add(array(
                    'approveName' =>$userNameName.'领用申请',
                    'eventId' => $ddd,
                    'scId' => $scId,
                    'businessTime' => date('Y-m-d H:i:s'),
                    'createTime' =>date('Y-m-d H:i:s'),
                    'eventType' => 1,
                ));
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'createBrrow'){
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            $allAsset = M('assets_list')->where(array('scId' => $scId))->select();
            $allAssetArray = array();
            foreach($allAsset as $key => $value){
                $allAssetArray[$value['assetsId']] = $value;
            }
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            //借操作
            $assetsId = I('post.assetsId');
            $userName = M('user')->field('name')->where(array('scId' => $scId,'id' =>I('post.userId')))->find();
            $userName = $userName['name'];
            foreach($assetsId as $key => $value){
                $assetsData = $allAssetArray[$value];
                $assetsTypeData = $typeRe[$assetsData['assetsTypeId']];
                if($assetsTypeData['ifApprov'] == 1){
                    $data = array(
                        'assetsId' => $value,
                        'reciveTime' => I('post.reciveTime'),
                        'explain' =>I('post.explain'),
                        'useAddress' => I('post.useAddress'),
                        'scId' => $scId,
                        'createUserId' =>  I('post.userId'),
                        'createUserName' => $userName,
                        'userId' => $userId,
                        'userName' => $userNameName,
                        'approveStatu' => 0,
                        'reviceStatu' => 1,
                        'createTime' => date('Y-m-d H:i:s'),
                    );
                    if($ddd = M('assets_revice')->add($data)){
                        M('assets_approve')->add(array(
                            'approveName' =>$userNameName.'领用申请',
                            'eventId' => $ddd,
                            'scId' => $scId,
                            'businessTime' => date('Y-m-d H:i:s'),
                            'createTime' =>date('Y-m-d H:i:s'),
                            'eventType' => 1,
                        ));
                    }
                }else{
                    $data = array(
                        'assetsId' => $value,
                        'reciveTime' => I('post.reciveTime'),
                        'explain' => I('post.explain'),
                        'useAddress' => I('post.useAddress'),
                        'scId' => $scId,
                        'createUserId' =>  I('post.userId'),
                        'createUserName' => $userName,
                        'userId' => $userId,
                        'userName' => $userNameName,
                        'approveStatu' => 1,
                        'reviceStatu' => 2,
                        'createTime' => date('Y-m-d H:i:s'),
                    );
                    if(M('assets_revice')->add($data)){
                        M('assets_list')->where(array('assetsId' => $value,'scId' => $scId))->setField(array('ifRecive' => 1));
                    }
                }
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
        }
    }
    private function getAll($assetsTypeId,$data,$scId){
        global $arr;
        foreach($data as $key => $value){
            if($assetsTypeId == $value['assetsTypeParentId']){
                $arr[$value['assetsTypeId']] = $value;
                $this->getAll($value['assetsTypeId'],$data,$scId);
            }
        }
        return $arr;
    }
    public function assetOut(){//资产出库
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type == 'getUserList'){
            $role = M('role')->where(array('scId' => array(array('eq',0),array('eq',$scId),'or')))->select();
            $user = M('user')->field('id,name,roleId')->where(array('scId' => $scId))->select();
            $studentRole = $this::$studentRoleId;
            $jzRole = $this::$jZroleId;
            $adminRole = $this::$adminRoleId;
            $user = M('')->query("select id,name,roleId from mks_user where scId= $scId AND roleId!=$studentRole AND roleId!=$jzRole AND roleId!=$adminRole");
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $user),true);
        }
        if($type == 'getAssetsListSort'){ //排序
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'outStatu' =>0,'state' => 1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    $value['assetsTypeName'] =  $typeRe[$value['assetsTypeId']]['assetsTypeName'];
                    $dateRe[] = $value;
                }else{

                }
            }
            $sort = I('get.sort');
            $sortData = I('get.sortData');
            $returnList = array();
            $data = $dateRe;
            $data1 = $data;
            foreach($data as $key => $value){
                $max = array();
                $kk = 0;
                foreach($data1 as $key1 => $value1){
                    if($value1[$sort]>= $max[$sort]){
                        $max = $value1;
                        $kk = $key1;
                    }
                }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if($sortData == 'inc'){
                krsort($returnList);
            }
            $data = $returnList;
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $dateRe),true);
        }
        if($type == 'getAssetsListValue'){//搜索
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'outStatu' =>0,'state' => 1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    $value['assetsTypeName'] =  $typeRe[$value['assetsTypeId']]['assetsTypeName'];
                    $dateRe[] = $value;
                }else{

                }
            }
            $data = $dateRe;
            $valueData = I('get.valueData');
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
                        unset($value['state']);
                        $returnList[] = $value;
                    }
                }
                $data = $returnList;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getAssetsList'){
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'outStatu' =>0,'state' => 1))->order('ifRecive desc')->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    $value['assetsTypeName'] =  $typeRe[$value['assetsTypeId']]['assetsTypeName'];
                    $dateRe[] = $value;
                }else{

                }
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $dateRe),true);
        }
        if($type == 'getAssetsType'){
            $data = M('assets_type')->where(array('scId' => $scId,'ifUser' =>1))->select();
            $data=$this->build_tree($data,0);
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'exportAdd'){
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'outStatu' =>0))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    if($value['ifRecive']){
                        $value['ifRecive'] = '已领用';
                    }else{
                        $value['ifRecive'] = '未领用';
                    }
                    $value['assetsTypeName'] =  $typeRe[$value['assetsTypeId']]['assetsTypeName'];
                    $dateRe[] = $value;
                }else{

                }
            }
            $tr = array(
                '0' => array(
                    'en' => 'assetsName',
                    'zh' => '资产名称'
                ),
                '1' => array(
                    'en' => 'assetsNumber',
                    'zh' => '资产编号'
                ),
                '2' => array(
                    'en' => 'assetsTypeId',
                    'zh' => '分类代码'
                ),
                '3' => array(
                    'en' => 'onePrice',
                    'zh' => '单价'
                ),
                '4' => array(
                    'en' => 'storageLocation',
                    'zh' => '存放地址'
                ),
                '5' => array(
                    'en' => 'spec',
                    'zh' => '规格'
                ),
                '6' => array(
                    'en' => 'brandModel',
                    'zh' => '品牌型号'
                ),
                '7' => array(
                    'en' => 'supplier',
                    'zh' => '供应商'
                ),
                '8' => array(
                    'en' => 'unit',
                    'zh' => '单位'
                ),
                '9' => array(
                    'en' => 'remarks',
                    'zh' => '备注'
                ),
                '10' => array(
                    'en' => 'ifRecive',
                    'zh' => '领取状态'
                )
            );
            $this->export($dateRe, $tr);
        }
        if($type =='createOut'){
            $assetsId = I('post.assetsId');
            $useruser = M('user')->field('name')->where(array('scId' => $scId,'id' => I('post.approverId')))->find();
            $ii = 0;
            foreach($assetsId as $key => $value){
                $data = array(
                    'assetsId' => $value,
                    'approver' => $useruser['name'],
                    'approverId' => I('post.approverId'),
                    'explain' => I('post.explain'),
                    'useAddress' => I('post.useAddress'),
                    'approveTime' => date('Y-m-d H:i:s'),
                    'scId' => $scId,
                    'outStatu' => 1,
                    'approveStatu' =>1,
                    'createUserId' => $userId,
                    'createUserName' =>$userName
                );
                if(M('assets_out')->add($data)){
                    $ii++;
                    M('assets_list')->where(array('scId' => $scId,'assetsId' => $value))->setField(array('outStatu' => 1));
                }
            }
            if($ii){
                $this->returnJson(array('statu' => 1,'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0,'message' => 'success'),true);
        }
        if($type =='createOutList'){
            $assetsId = I('post.assetsId');
            $useruser = M('user')->field('name')->where(array('scId' => $scId,'id' =>I('post.approverId')))->find();
            foreach($assetsId as $key => $value){
                $data = array(
                    'assetsId' => $value,
                    'approver' => $useruser['name'],
                    'approverId' => I('post.approverId'),
                    'explain' => I('post.explain'),
                    'useAddress' =>I('post.useAddress'),
                    'approveTime' => date('Y-m-d H:i:s'),
                    'scId' => $scId,
                    'outStatu' => 1,
                    'approveStatu' =>1,
                    'createUserId' => $userId,
                    'createUserName' =>$userName
                );
                M('assets_out')->add($data);
                M('assets_list')->where(array('scId' => $scId,'assetsId' => $value))->setField(array('outStatu' => 1));
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
        }
        if($type == 'getOutRecord'){
            $outData = M('assets_out')->where(array('scId' => $scId))->select();
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $outData),true);
        }
        if($type == 'getOutRecordSort'){
            $outData = M('assets_out')->where(array('scId' => $scId))->select();
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
            }
            $sort = I('get.sort');
            $sortData = I('get.sortData');
            $returnList = array();
            $data = $outData;
            $data1 = $data;
            foreach($data as $key => $value){
                $max = array();
                $kk = 0;
                foreach($data1 as $key1 => $value1){
                    if($value1[$sort]>= $max[$sort]){
                        $max = $value1;
                        $kk = $key1;
                    }
                }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if($sortData == 'inc'){
                krsort($returnList);
            }
            $data = $returnList;
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getOutRecordValue'){
            $outData = M('assets_out')->where(array('scId' => $scId))->select();
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
            }
            $data = $outData;
            $valueData = I('get.valueData');
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
                        unset($value['state']);
                        $returnList[] = $value;
                    }
                }
                $data = $returnList;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type =='revoke'){
            $assetOutId = I('post.assetOutId');
            foreach($assetOutId as $key => $value){
                if(M('assets_out')->where(array('scId' => $scId,'assetOutId' => $value))->setField(array('ifRevoKe' => 1))){
                    $assetsId = M('assets_out')->field('assetsId')->where(array('scId' => $scId,'assetOutId' => $value))->find();
                    $assetsId = $assetsId['assetsId'];
                    M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('outStatu' => 0));
                }
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
        }
        if($type =='destroy'){
            $assetOutId = I('post.assetOutId');
            foreach($assetOutId as $key => $value){
                if(M('assets_out')->where(array('scId' => $scId,'assetOutId' => $value))->setField(array('ifDestroy' => 1))){
                    $assetsId = M('assets_out')->field('assetsId')->where(array('scId' => $scId,'assetOutId' => $value))->find();
                    $assetsId = $assetsId['assetsId'];
                    M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('state' => 0));
                }
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
        }
        if($type == 'updataOut'){
            $assetOutId = I('post.assetOutId');
            $data = array(
                'outTime' => I('post.outTime'),
                'useAddress' => I('post.useAddress'),
                'explain' => I('post.explain')
            );
            if(M('assets_out')->where(array('scId' => $scId,'assetOutId' => $assetOutId))->setField($data)){
                $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
            }else{
                $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
            }
        }
        if ($type == 'export') {
            $outData = M('assets_out')->where(array('scId' => $scId))->select();
            $listData = M('assets_list')->where(array('scId' => $scId))->select();
            $listDataRe = array();
            foreach($listData as $key => $value){
                $listDataRe[$value['assetsId']] = $value;
            }
            foreach($outData as $key => $value){
                if($outData[$key]['ifRevoKe'] == 0){
                    $outData[$key]['stateState'] ='未撤销';
                }else{
                    $outData[$key]['stateState'] ='已撤销';
                }
                if($outData[$key]['ifDestroy'] == 0){
                    $outData[$key]['stateState'] = $outData[$key]['stateState'].',未销毁';
                }else{
                    $outData[$key]['stateState'] = $outData[$key]['stateState'].',已销毁';
                }
                $outData[$key]['assetsName'] = $listDataRe[$value['assetsId']]['assetsName'];
                $outData[$key]['assetsNumber'] = $listDataRe[$value['assetsId']]['assetsNumber'];
                $outData[$key]['assetsTypeId'] = $listDataRe[$value['assetsId']]['assetsTypeId'];
                $outData[$key]['onePrice'] = $listDataRe[$value['assetsId']]['onePrice'];
            }
            $tr = array(
                '0' => array(
                    'en' => 'assetsName',
                    'zh' => '资产名称'
                ),
                '1' => array(
                    'en' => 'assetsNumber',
                    'zh' => '资产编号'
                ),
                '2' => array(
                    'en' => 'assetsTypeId',
                    'zh' => '分类代码'
                ),
                '3' => array(
                    'en' => 'outTime',
                    'zh' => '出库时期'
                ),
                '4' => array(
                    'en' => 'approveTime',
                    'zh' => '创建时间'
                ),
                '5' => array(
                    'en' => 'spec',
                    'zh' => '单价'
                ),
                '6' => array(
                    'en' => 'useAddress',
                    'zh' => '使用地址'
                ),
                '7' => array(
                    'en' => 'approver',
                    'zh' => '负责人'
                ),
                '8' => array(
                    'en' => 'createUserName',
                    'zh' => '创建人'
                ),
                '9' => array(
                    'en' => 'explain',
                    'zh' => '说明'
                ),
                '10' => array(
                    'en' => 'stateState',
                    'zh' => '状态'
                )
            );
            $this->export($outData, $tr);
        }
    }
    private function check($assetsTypeId,$assetsTypeData,$userId){
        global $aa;
        foreach($assetsTypeData as $key => $value){
            if($value['assetsTypeId'] == $assetsTypeId){
                $assetsTypeParentId = $value['assetsTypeParentId'];
                $allUser = explode(',',$value['approver']);
                foreach($allUser as $key1 => $value1){
                    if($value1 == $userId){
                        $aa = 1;
                    }
                }
                $this->check($assetsTypeParentId,$assetsTypeData,$userId);
            }
        }
        return $aa;
    }
    private function needApporve($assetsTypeId,$data,$scId){
        global $arr;
        foreach($data as $key => $value){
            if($assetsTypeId == $value['assetsTypeParentId']){
                $arr[$value['assetsTypeId']] = $value;
                $this->needApporve($value['assetsTypeId'],$data,$scId);
            }
        }
        return $arr;
    }
    private function state($id){
        $array = array(
            0 => '未审批',
            1 => '已审批'
        );
        return $array[$id];
    }
    public function assetsApprove(){//资产审批
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type =='getApporveList'){
            $data = M('assets_approve')->where(array('scId' => $scId,'state' => 0))->select();
            $rev = M('assets_revice')->field('reciveId,assetsId,userId,userName,useAddress,explain')->where(array('scId' => $scId))->select();
            $revRe = array();
            foreach($rev as $key => $value){
                $revRe[$value['reciveId']] = $value;
            }
            $list = M('assets_list')->field('assetsId,assetsName,assetsNumber,assetsTypeId,num,onePrice')->where(array('scId' => $scId))->select();
            $listRe = array();
            foreach($list as $key => $value){
                $listRe[$value['assetsId']] = $value;
            }
            foreach($data as $key => $value){
                $data[$key]['assetsId'] = $revRe[$value['eventId']]['assetsId'];
                $data[$key]['userId'] = $revRe[$value['eventId']]['userId'];
                $data[$key]['userName'] = $revRe[$value['eventId']]['userName'];
                $data[$key]['useAddress'] = $revRe[$value['eventId']]['useAddress'];
                $data[$key]['explain'] = $revRe[$value['eventId']]['explain'];
                $data[$key]['assetsName'] = $listRe[ $data[$key]['assetsId']]['assetsName'];
                $data[$key]['assetsNumber'] = $listRe[ $data[$key]['assetsId']]['assetsNumber'];
                $data[$key]['assetsTypeId'] = $listRe[ $data[$key]['assetsId']]['assetsTypeId'];
                $data[$key]['num'] = $listRe[ $data[$key]['assetsId']]['num'];
                $data[$key]['onePrice'] = $listRe[ $data[$key]['assetsId']]['onePrice'];
                $data[$key]['allPrice'] =  $data[$key]['num']* $data[$key]['onePrice'];
            }
            $assetsTypeData = M('assets_type')->where(array('scId' => $scId))->select();
            if($userRoleId != $this::$adminRoleId){
                $rere = array();
                foreach($data as $key => $value){
                    $check = $this->check($value['assetsTypeId'],$assetsTypeData,$userId);
                    if($check){
                        $rere[] = $value;
                    }
                }
                $data = $rere;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type =='getApporveListSort'){
            $data = M('assets_approve')->where(array('scId' => $scId,'state' => 0))->select();
            $rev = M('assets_revice')->field('reciveId,assetsId,userId,userName,useAddress,explain')->where(array('scId' => $scId))->select();
            $revRe = array();
            foreach($rev as $key => $value){
                $revRe[$value['reciveId']] = $value;
            }
            $list = M('assets_list')->field('assetsId,assetsName,assetsNumber,assetsTypeId,num,onePrice')->where(array('scId' => $scId))->select();
            $listRe = array();
            foreach($list as $key => $value){
                $listRe[$value['assetsId']] = $value;
            }
            foreach($data as $key => $value){
                $data[$key]['assetsId'] = $revRe[$value['eventId']]['assetsId'];
                $data[$key]['userId'] = $revRe[$value['eventId']]['userId'];
                $data[$key]['userName'] = $revRe[$value['eventId']]['userName'];
                $data[$key]['useAddress'] = $revRe[$value['eventId']]['useAddress'];
                $data[$key]['explain'] = $revRe[$value['eventId']]['explain'];
                $data[$key]['assetsName'] = $listRe[ $data[$key]['assetsId']]['assetsName'];
                $data[$key]['assetsNumber'] = $listRe[ $data[$key]['assetsId']]['assetsNumber'];
                $data[$key]['assetsTypeId'] = $listRe[ $data[$key]['assetsId']]['assetsTypeId'];
                $data[$key]['num'] = $listRe[ $data[$key]['assetsId']]['num'];
                $data[$key]['onePrice'] = $listRe[ $data[$key]['assetsId']]['onePrice'];
                $data[$key]['allPrice'] =  $data[$key]['num']* $data[$key]['onePrice'];
            }
            $assetsTypeData = M('assets_type')->where(array('scId' => $scId))->select();
            if($userRoleId != $this::$adminRoleId){
                $rere = array();
                foreach($data as $key => $value){
                    $check = $this->check($value['assetsTypeId'],$assetsTypeData,$userId);
                    if($check){
                        $rere[] = $value;
                    }
                }
                $data = $rere;
            }
            $sort =  I('get.sort');
            $sortData = I('get.sortData');
            $returnList = array();
            $data1 = $data;
            foreach($data as $key => $value){
                $max = array();
                $kk = 0;
                foreach($data1 as $key1 => $value1){
                    if($value1[$sort]>= $max[$sort]){
                        $max = $value1;
                        $kk = $key1;
                    }
                }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if($sortData == 'inc'){
                krsort($returnList);
            }
            $data = $returnList;
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type =='getApporveListValue'){
            $data = M('assets_approve')->where(array('scId' => $scId,'state' => 0))->select();
            $rev = M('assets_revice')->field('reciveId,assetsId,userId,userName,useAddress,explain')->where(array('scId' => $scId))->select();
            $revRe = array();
            foreach($rev as $key => $value){
                $revRe[$value['reciveId']] = $value;
            }
            $list = M('assets_list')->field('assetsId,assetsName,assetsNumber,assetsTypeId,num,onePrice')->where(array('scId' => $scId))->select();
            $listRe = array();
            foreach($list as $key => $value){
                $listRe[$value['assetsId']] = $value;
            }
            foreach($data as $key => $value){
                $data[$key]['assetsId'] = $revRe[$value['eventId']]['assetsId'];
                $data[$key]['userId'] = $revRe[$value['eventId']]['userId'];
                $data[$key]['userName'] = $revRe[$value['eventId']]['userName'];
                $data[$key]['useAddress'] = $revRe[$value['eventId']]['useAddress'];
                $data[$key]['explain'] = $revRe[$value['eventId']]['explain'];
                $data[$key]['assetsName'] = $listRe[ $data[$key]['assetsId']]['assetsName'];
                $data[$key]['assetsNumber'] = $listRe[ $data[$key]['assetsId']]['assetsNumber'];
                $data[$key]['assetsTypeId'] = $listRe[ $data[$key]['assetsId']]['assetsTypeId'];
                $data[$key]['num'] = $listRe[ $data[$key]['assetsId']]['num'];
                $data[$key]['onePrice'] = $listRe[ $data[$key]['assetsId']]['onePrice'];
                $data[$key]['allPrice'] =  $data[$key]['num']* $data[$key]['onePrice'];
            }
            $assetsTypeData = M('assets_type')->where(array('scId' => $scId))->select();
            if($userRoleId != $this::$adminRoleId){
                $rere = array();
                foreach($data as $key => $value){
                    $check = $this->check($value['assetsTypeId'],$assetsTypeData,$userId);
                    if($check){
                        $rere[] = $value;
                    }
                }
                $data = $rere;
            }
            $valueData = I('get.valueData');
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
                        unset($value['state']);
                        $returnList[] = $value;
                    }
                }
                $data = $returnList;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type =='exportWaitApprove'){
            $data = M('assets_approve')->where(array('scId' => $scId,'state' => 0))->select();
            $rev = M('assets_revice')->field('reciveId,assetsId,userId,userName,useAddress,explain')->where(array('scId' => $scId))->select();
            $revRe = array();
            foreach($rev as $key => $value){
                $revRe[$value['reciveId']] = $value;
            }
            $list = M('assets_list')->field('assetsId,assetsName,assetsNumber,assetsTypeId,num,onePrice')->where(array('scId' => $scId))->select();
            $listRe = array();
            foreach($list as $key => $value){
                $listRe[$value['assetsId']] = $value;
            }
            foreach($data as $key => $value){
                $data[$key]['assetsId'] = $revRe[$value['eventId']]['assetsId'];
                $data[$key]['userId'] = $revRe[$value['eventId']]['userId'];
                $data[$key]['userName'] = $revRe[$value['eventId']]['userName'];
                $data[$key]['useAddress'] = $revRe[$value['eventId']]['useAddress'];
                $data[$key]['explain'] = $revRe[$value['eventId']]['explain'];
                $data[$key]['assetsName'] = $listRe[ $data[$key]['assetsId']]['assetsName'];
                $data[$key]['assetsNumber'] = $listRe[ $data[$key]['assetsId']]['assetsNumber'];
                $data[$key]['assetsTypeId'] = $listRe[ $data[$key]['assetsId']]['assetsTypeId'];
                $data[$key]['num'] = $listRe[ $data[$key]['assetsId']]['num'];
                $data[$key]['onePrice'] = $listRe[ $data[$key]['assetsId']]['onePrice'];
                $data[$key]['allPrice'] =  $data[$key]['num']* $data[$key]['onePrice'];
            }
            $tr = array(
                '0' => array(
                    'en' => 'approveName',
                    'zh' => '业务名称'
                ),
                '1' => array(
                    'en' => 'assetsName',
                    'zh' => '资产名称'
                ),
                '2' => array(
                    'en' => 'assetsNumber',
                    'zh' => '资产编号'
                ),
                '3' => array(
                    'en' => 'assetsTypeId',
                    'zh' => '分类代码'
                ),
                '4' => array(
                    'en' => 'allPrice',
                    'zh' => '总价'
                ),
                '5' => array(
                    'en' => 'businessTime',
                    'zh' => '业务日期'
                ),
                '6' => array(
                    'en' => 'createTime',
                    'zh' => '申请日期'
                ),
                '7' => array(
                    'en' => 'useAddress',
                    'zh' => '使用地址'
                ),
                '8' => array(
                    'en' => 'userName',
                    'zh' => '负责人'
                ),
                '9' => array(
                    'en' => 'explain',
                    'zh' => '说明'
                )
            );
            $this::export($data,$tr);
        }
        if($type == 'approveHandle'){
            $adpot = I('get.adpot');
            if($adpot == 'true'){
                $adpot = 1;
            }else{
                $adpot = 0;
            }
            $approveOpinion = I('get.approveOpinion');
            $approveId = I('get.approveId');
            $trueOrfalse = 0;
            foreach($approveId as $key => $value){
                $approve = M('assets_approve')->where(array('scId' => $scId,'approveId' => $value))->find();
                $eventId =  $approve['eventId'];
                $eventType = $approve['eventType'];
                $assetsId = M('assets_revice')->where(array('scId' => $scId,'reciveId' => $eventId))->find();
                $assetsId = $assetsId['assetsId'];
                if($adpot){
                    if(M('assets_approve')->where(array('scId' => $scId,'approveId' => $value))->setField(array('appResult' => 1,'approver' => $userName,'approverId' => $userId,'state' => 1,'approveTime' => date('Y-m-d H:i:s'),'approveOpinion' => $approveOpinion))){
                        if($eventType == 1){
                            //M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('ifRecive' => 1));
                            M('assets_revice')->where(array('reciveId' => $eventId,'scId' => $scId))->setField(array('approveStatu' => 1,'reviceStatu' => 2));
                            M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('ifRecive' => 1));
                        }else{
                            M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('ifRecive' => 0));
                            M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('outStatu' => 0));
                            M('assets_revice')->where(array('reciveId' => $eventId,'scId' => $scId))->setField(array('backStatu' => 2,'approveStatu'=>1));
                        }
                        $trueOrfalse = 1;
                    }
                }else{
                    if(M('assets_approve')->where(array('scId' => $scId,'approveId' => $value))->setField(array('appResult' => 2,'state' => 1,'approver' => $userName,'approverId' => $userId,'approveTime' => date('Y-m-d H:i:s'),'approveOpinion' => $approveOpinion))){
                        if($eventType == 1){
                            M('assets_revice')->where(array('reciveId' => $eventId,'scId' => $scId))->setField(array('approveStatu' => 2,'reviceStatu' => 6));
                        }else{
                            //M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('ifRecive' => 0));
                            M('assets_revice')->where(array('reciveId' => $eventId,'scId' => $scId))->setField(array('backStatu' => 0,'approveStatu'=>2));
                        }
                        $trueOrfalse = 1;
                    }
                }
            }
            if($trueOrfalse){
                $this->returnJson(array('statu' => 1, 'message' => '审批成功'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => '审批失败'),true);
            /*$approve = M('assets_approve')->where(array('scId' => $scId,'approveId' => $approveId))->find();
            $eventId =  $approve['eventId'];
            $eventType = $approve['eventType'];
            if($adpot == 1){
                if(M('assets_approve')->where(array('scId' => $scId,'approveId' => $approveId))->setField(array('appResult' => 1,'approver' => $userName,'approverId' => $userId,'state' => 1,'approveTime' => date('Y-m-d H:i:s'),'approveOpinion' => $approveOpinion))){
                    if($eventType == 1){
                        M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('ifRecive' => 1));
                        M('assets_revice')->where(array('reciveId' => $eventId,'scId' => $scId))->setField(array('approveStatu' => 1));
                        $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
                    }else{
                        M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('ifRecive' => 0));
                        M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('outStatu' => 0));
                        M('assets_revice')->where(array('reciveId' => $eventId,'scId' => $scId))->setField(array('backStatu' => 2,'approveStatu'=>1));
                        $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
                    }
                }
                $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
            }else{
                if(M('assets_approve')->where(array('scId' => $scId,'approveId' => $approveId))->setField(array('appResult' => 2,'state' => 1,'approver' => $userName,'approverId' => $userId,'approveTime' => date('Y-m-d H:i:s'),'approveOpinion' => $approveOpinion))){
                    if($eventType == 1){
                        M('assets_revice')->where(array('reciveId' => $eventId,'scId' => $scId))->setField(array('approveStatu' => 2));
                        $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
                    }else{
                        M('assets_list')->where(array('scId' => $scId,'assetsId' => $assetsId))->setField(array('ifRecive' => 0));
                        M('assets_revice')->where(array('reciveId' => $eventId,'scId' => $scId))->setField(array('backStatu' => 3,'approveStatu'=>2));
                        $this->returnJson(array('statu' => 1, 'message' => 'success'),true);
                    }
                }
                $this->returnJson(array('statu' => 0, 'message' => 'fail'),true);
            }*/
        }
        if($type == 'getHaveApprove'){
            $data = M('assets_approve')->where(array('scId' => $scId, 'state' => 1))->select();
            $rev = M('assets_revice')->field('reciveId,assetsId,userId,userName,useAddress,explain')->where(array('scId' => $scId))->select();
            $revRe = array();
            foreach ($rev as $key => $value) {
                $revRe[$value['reciveId']] = $value;
            }
            $list = M('assets_list')->field('assetsId,assetsName,assetsNumber,assetsTypeId,num,onePrice')->where(array('scId' => $scId))->select();
            $listRe = array();
            foreach ($list as $key => $value) {
                $listRe[$value['assetsId']] = $value;
            }
            foreach ($data as $key => $value) {
                $data[$key]['assetsId'] = $revRe[$value['eventId']]['assetsId'];
                $data[$key]['userId'] = $revRe[$value['eventId']]['userId'];
                $data[$key]['userName'] = $revRe[$value['eventId']]['userName'];
                $data[$key]['useAddress'] = $revRe[$value['eventId']]['useAddress'];
                $data[$key]['explain'] = $revRe[$value['eventId']]['explain'];
                $data[$key]['assetsName'] = $listRe[$data[$key]['assetsId']]['assetsName'];
                $data[$key]['assetsNumber'] = $listRe[$data[$key]['assetsId']]['assetsNumber'];
                $data[$key]['assetsTypeId'] = $listRe[$data[$key]['assetsId']]['assetsTypeId'];
                $data[$key]['num'] = $listRe[$data[$key]['assetsId']]['num'];
                $data[$key]['onePrice'] = $listRe[$data[$key]['assetsId']]['onePrice'];
                $data[$key]['allPrice'] = $data[$key]['num'] * $data[$key]['onePrice'];
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getHaveApproveSort'){
            $data = M('assets_approve')->where(array('scId' => $scId, 'state' => 1))->select();
            $rev = M('assets_revice')->field('reciveId,assetsId,userId,userName,useAddress,explain')->where(array('scId' => $scId))->select();
            $revRe = array();
            foreach ($rev as $key => $value) {
                $revRe[$value['reciveId']] = $value;
            }
            $list = M('assets_list')->field('assetsId,assetsName,assetsNumber,assetsTypeId,num,onePrice')->where(array('scId' => $scId))->select();
            $listRe = array();
            foreach ($list as $key => $value) {
                $listRe[$value['assetsId']] = $value;
            }
            foreach ($data as $key => $value) {
                $data[$key]['assetsId'] = $revRe[$value['eventId']]['assetsId'];
                $data[$key]['userId'] = $revRe[$value['eventId']]['userId'];
                $data[$key]['userName'] = $revRe[$value['eventId']]['userName'];
                $data[$key]['useAddress'] = $revRe[$value['eventId']]['useAddress'];
                $data[$key]['explain'] = $revRe[$value['eventId']]['explain'];
                $data[$key]['assetsName'] = $listRe[$data[$key]['assetsId']]['assetsName'];
                $data[$key]['assetsNumber'] = $listRe[$data[$key]['assetsId']]['assetsNumber'];
                $data[$key]['assetsTypeId'] = $listRe[$data[$key]['assetsId']]['assetsTypeId'];
                $data[$key]['num'] = $listRe[$data[$key]['assetsId']]['num'];
                $data[$key]['onePrice'] = $listRe[$data[$key]['assetsId']]['onePrice'];
                $data[$key]['allPrice'] = $data[$key]['num'] * $data[$key]['onePrice'];
            }
            $sort =I('get.sort');
            $sortData = I('get.sortData');
            $returnList = array();
            $data1 = $data;
            foreach($data as $key => $value){
                $max = array();
                $kk = 0;
                foreach($data1 as $key1 => $value1){
                    if($value1[$sort]>= $max[$sort]){
                        $max = $value1;
                        $kk = $key1;
                    }
                }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if($sortData == 'inc'){
                krsort($returnList);
            }
            $data = $returnList;
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getHaveApproveValue'){
            $data = M('assets_approve')->where(array('scId' => $scId, 'state' => 1))->select();
            $rev = M('assets_revice')->field('reciveId,assetsId,userId,userName,useAddress,explain')->where(array('scId' => $scId))->select();
            $revRe = array();
            foreach ($rev as $key => $value) {
                $revRe[$value['reciveId']] = $value;
            }
            $list = M('assets_list')->field('assetsId,assetsName,assetsNumber,assetsTypeId,num,onePrice')->where(array('scId' => $scId))->select();
            $listRe = array();
            foreach ($list as $key => $value) {
                $listRe[$value['assetsId']] = $value;
            }
            foreach ($data as $key => $value) {
                $data[$key]['assetsId'] = $revRe[$value['eventId']]['assetsId'];
                $data[$key]['userId'] = $revRe[$value['eventId']]['userId'];
                $data[$key]['userName'] = $revRe[$value['eventId']]['userName'];
                $data[$key]['useAddress'] = $revRe[$value['eventId']]['useAddress'];
                $data[$key]['explain'] = $revRe[$value['eventId']]['explain'];
                $data[$key]['assetsName'] = $listRe[$data[$key]['assetsId']]['assetsName'];
                $data[$key]['assetsNumber'] = $listRe[$data[$key]['assetsId']]['assetsNumber'];
                $data[$key]['assetsTypeId'] = $listRe[$data[$key]['assetsId']]['assetsTypeId'];
                $data[$key]['num'] = $listRe[$data[$key]['assetsId']]['num'];
                $data[$key]['onePrice'] = $listRe[$data[$key]['assetsId']]['onePrice'];
                $data[$key]['allPrice'] = $data[$key]['num'] * $data[$key]['onePrice'];
            }
            $valueData = I('get.valueData');
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
                        unset($value['state']);
                        $returnList[] = $value;
                    }
                }
                $data = $returnList;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getHaveApproveExport') {
            $data = M('assets_approve')->where(array('scId' => $scId, 'state' => 1))->select();
            $rev = M('assets_revice')->field('reciveId,assetsId,userId,userName,useAddress,explain')->where(array('scId' => $scId))->select();
            $revRe = array();
            foreach ($rev as $key => $value) {
                $revRe[$value['reciveId']] = $value;
            }
            $list = M('assets_list')->field('assetsId,assetsName,assetsNumber,assetsTypeId,num,onePrice')->where(array('scId' => $scId))->select();
            $listRe = array();
            foreach ($list as $key => $value) {
                $listRe[$value['assetsId']] = $value;
            }
            foreach ($data as $key => $value) {
                $data[$key]['assetsId'] = $revRe[$value['eventId']]['assetsId'];
                $data[$key]['userId'] = $revRe[$value['eventId']]['userId'];
                $data[$key]['userName'] = $revRe[$value['eventId']]['userName'];
                $data[$key]['useAddress'] = $revRe[$value['eventId']]['useAddress'];
                $data[$key]['explain'] = $revRe[$value['eventId']]['explain'];
                $data[$key]['assetsName'] = $listRe[$data[$key]['assetsId']]['assetsName'];
                $data[$key]['assetsNumber'] = $listRe[$data[$key]['assetsId']]['assetsNumber'];
                $data[$key]['assetsTypeId'] = $listRe[$data[$key]['assetsId']]['assetsTypeId'];
                $data[$key]['num'] = $listRe[$data[$key]['assetsId']]['num'];
                $data[$key]['onePrice'] = $listRe[$data[$key]['assetsId']]['onePrice'];
                $data[$key]['allPrice'] = $data[$key]['num'] * $data[$key]['onePrice'];
            }
            $tr = array(
                '0' => array(
                    'en' => 'approveName',
                    'zh' => '业务名称'
                ),
                '1' => array(
                    'en' => 'assetsName',
                    'zh' => '资产名称'
                ),
                '2' => array(
                    'en' => 'assetsNumber',
                    'zh' => '资产编号'
                ),
                '3' => array(
                    'en' => 'assetsTypeId',
                    'zh' => '分类代码'
                ),
                '4' => array(
                    'en' => 'allPrice',
                    'zh' => '总价'
                ),
                '5' => array(
                    'en' => 'approveTime',
                    'zh' => '审批日期'
                ),
                '6' => array(
                    'en' => 'businessTime',
                    'zh' => '业务日期'
                ),
                '7' => array(
                    'en' => 'createTime',
                    'zh' => '申请日期'
                ),
                '8' => array(
                    'en' => 'useAddress',
                    'zh' => '使用地址'
                ),
                '9' => array(
                    'en' => 'userName',
                    'zh' => '负责人'
                ),
                '10' => array(
                    'en' => 'approver',
                    'zh' => '审批人'
                ),
                '11' => array(
                    'en' => 'explain',
                    'zh' => '说明'
                ),
                '12' => array(
                    'en' => 'approveOpinion',
                    'zh' => '审批意见'
                )
            );
            $this::export($data,$tr);
        }
    }
    public function assetsList(){
        $type = I('get.type');
        $jbXn = $this->get_session('loginCheck', false);
        $scId = $jbXn['scId'];
        $userId = $jbXn['userId'];
        $userRoleId = $jbXn['roleId'];
        $userName = $jbXn['name'];
        if($type == 'getList'){
            M('assets_list')->where(array('scId' => $scId))->select();
        }
        if ($type == 'getType') {
            $data = M('assets_type')->where(array('scId' => $scId))->select();
            $data=$this->build_tree($data,0);
            $this->returnJson(array('statu' => 1, 'message' => 'success', 'data' => $data), true);
        }
        if ($type == 'getAssetsList') {
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'outStatu' =>0,'state' => 1))->order('assetsId desc')->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    $value['assetsTypeName'] =  $typeRe[$value['assetsTypeId']]['assetsTypeName'];
                    $dateRe[] = $value;
                }else{

                }
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $dateRe),true);
        }
        if($type == 'delete'){
            $assetsId = I('post.assetsId');
            foreach($assetsId as $key => $value){
                M('assets_list')->where(array('scId' => $scId,'assetsId' =>$value))->setField(array('state' => 0));
            }
            $this->returnJson(array('statu' => 1, 'message' => '删除成功'),true);
        }
        if($type == 'getAssetsListValue'){
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'outStatu' =>0,'state' => 1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    $value['assetsTypeName'] =  $typeRe[$value['assetsTypeId']]['assetsTypeName'];
                    $dateRe[] = $value;
                }else{

                }
            }
            $data = $dateRe;
            $valueData = I('get.valueData');
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
                        $returnList[] = $value;
                    }
                }
                $data = $returnList;
            }
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'getAssetsListSort'){
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'outStatu' =>0,'state' => 1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    $value['assetsTypeName'] =  $typeRe[$value['assetsTypeId']]['assetsTypeName'];
                    $dateRe[] = $value;
                }else{

                }
            }
            $data = $dateRe;
            $returnList = array();
            $sort = I('get.sort');
            $sortData = I('get.sortData');
            $returnList = array();
            $data1 = $data;
            foreach($data as $key => $value){
                $max = array();
                $kk = 0;
                foreach($data1 as $key1 => $value1){
                    if($value1[$sort]>= $max[$sort]){
                        $max = $value1;
                        $kk = $key1;
                    }
                }
                $returnList[] = $max;
                unset($data1[$kk]);
            }
            if($sortData == 'inc'){
                krsort($returnList);
            }
            $data = $returnList;
            $this->returnJson(array('statu' => 1, 'message' => 'success','data' => $data),true);
        }
        if($type == 'update'){
            $data =  I('post.data');
            $assetsId = $data['assetsId'];
            unset($data['assetsId']);
            if($userRoleId == $this::$teacherRoleId){
                $assetTypes = M('assets_type')->field('assetsTypeId,approver')->where(array('scId' => $scId))->select();
                $list = array();
                $check = false;
                foreach($assetTypes as $key => $value){
                    $list = explode(',',$value['approver']);
                    foreach($list as $key1 => $value1){
                        if($value1 == $this::$studentRoleId){
                            $check = true;
                            break;
                        }
                    }
                    if($check){
                        break;
                    }
                }
            }
            if(!$check){
                $this->returnJson(array('statu' => 2, 'message' => '没有修改权限'),true);
            }
            if($data){
                if(M('assets_list')->where(array('assetsId' => $assetsId,'scId' => $scId))->setField($data) === false){
                    $this->returnJson(array('statu' => 0, 'message' => '修改失败'),true);
                }
                $this->returnJson(array('statu' => 1, 'message' => '修改成功'),true);
            }
            $this->returnJson(array('statu' => 0, 'message' => '修改失败'),true);
        }
        if($type == 'export'){
            $assetsTypeId = I('get.assetsTypeId');
            $data = M('assets_list')->where(array('scId' => $scId,'outStatu' =>0,'state' => 1))->select();
            $type = M('assets_type')->where(array('scId' => $scId))->select();
            $typeRe = array();
            foreach($type as $key => $value){
                $typeRe[$value['assetsTypeId']] = $value;
            }
            $typeReturn = $this->getAll($assetsTypeId,$type,$scId);
            $typeReturn[$assetsTypeId] = $typeRe[$assetsTypeId];
            $dateRe = array();
            foreach($data as $key => $value){
                if(isset($typeReturn[$value['assetsTypeId']])){
                    $value['assetsTypeName'] =  $typeRe[$value['assetsTypeId']]['assetsTypeName'];
                    $dateRe[] = $value;
                }else{

                }
            }
            $tr = array(
                '0' => array(
                    'en' => 'assetsName',
                    'zh' => '资产名称'
                ),
                '1' => array(
                    'en' => 'assetsNumber',
                    'zh' => '资产编号'
                ),
                '2' => array(
                    'en' => 'assetsTypeId',
                    'zh' => '分类代码'
                ),
                '3' => array(
                    'en' => 'onePrice',
                    'zh' => '单价'
                ),
                '4' => array(
                    'en' => 'storageLocation',
                    'zh' => '存放地址'
                ),
                '5' => array(
                    'en' => 'spec',
                    'zh' => '规格'
                ),
                '6' => array(
                    'en' => 'brandModel',
                    'zh' => '品牌型号'
                ),
                '7' => array(
                    'en' => 'supplier',
                    'zh' => '供应商'
                ),
                '8' => array(
                    'en' => 'unit',
                    'zh' => '单位'
                ),
                '9' => array(
                    'en' => 'remarks',
                    'zh' => '备注'
                ),
                '10' => array(
                    'en' => 'ifRecive',
                    'zh' => '领取状态'
                )
            );
            $this->export($dateRe, $tr);
        }
    }
    private function leaveState($id){
        $array = array(
            0 => '未离校',
            1 => '已离校'
        );
        return $array[$id];
    }
    private function toLeave($id){
        $array = array(
            1 => '事假',
            2 => '病假',
            3 => '其他',
        );
        return $array[$id];
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
}