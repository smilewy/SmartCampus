<?php
/**
 * Created by PhpStorm.
 * User: hujun
 * Date: 2017/11/21
 * Time: 18:08
 */

namespace Home\Controller;

/*
 * 首页
 * */
use Home\Common\Accessory;

ob_end_clean();

class HomeController extends Base
{
    protected $scId;
    protected $uid;
    protected $user;
    protected $roleId;


    public function __construct()
    {
        parent::__construct();
        $scId = $_SESSION['loginCheck']['data']['scId'];
        $uid = $_SESSION['loginCheck']['data']['userId'];
        $this->roleId = $_SESSION['loginCheck']['data']['roleId'];
        /*
                $this->roleId = 22;
                $uid = 1;
                $scId = 2;*/
        $this->scId = $scId;
        $this->uid = $uid;
        $this->user = D('User')->where(array('id' => $this->uid, 'scId' => $this->scId))->find();
    }
/*
    private  function messageList(){

    }*/

    //日程安排
    public function schedule()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $type = $_POST['type'];
        if (isset($type)) {
            $rs = false;
            if ($type == 'add') {
                $data = $_POST;
                $scope = $data['scope'];
                $num = count($scope);
                if ($num == 1 && $scope[0] == '自己') { //通知范围只有自己
                    $messageId = $this->uid;
                    $isSelf = 1;
                } else {
                    $isSelf = 0;
                    $messageId=array();
                    if(in_array('教师',$scope)){
                        $messageId[]=$this::$teacherRoleId;
                    }
                    if(in_array('家长',$scope)){
                        $messageId[]=$this::$jZroleId;
                    }
                    if(in_array('学生',$scope)){
                        $messageId[]=$this::$studentRoleId;
                    }
                    if(in_array('职工',$scope)){
                        $where=array(
                            'scId' => array(array('eq', $this->scId), array('eq', 0), 'or'),
                            'roleId'=>array('not in',array($this::$studentRoleId,$this::$adminRoleId,$this::$teacherRoleId,$this::$jZroleId))
                        );
                        $role=D('Role')
                            ->where($where)
                            ->field('roleId')->select();
                        foreach ($role as $k=>$v){
                            $messageId[]=$v['roleId'];
                        }
                    }

                    $messageId = implode(',', $messageId);
                }

                $schedule = array(
                    'title' => $data['title'],
                    'kind' => $data['kind'],
                    'messageId' => $messageId,
                    'content' => $data['content'],
                    'startTime' => strtotime($data['startTime']),
                    'endTime' => strtotime($data['endTime']),
                    'createTime' => time(),
                    'creator' => $this->user['name'],
                    'creatorId' => $this->uid,
                    'status' => 0,
                    'scId' => $this->scId,
                    'isSelf' => $isSelf,
                );
                $rs = D('Schedule')->data($schedule)->add();
                if ($rs)
                    $response['id'] = $rs;
            } elseif ($type == 'edit') {//编辑
                $data = $_POST;
                $schedule = array(
                    'title' => $data['title'],
                    'kind' => $data['kind'],
                    'content' => $data['content'],
                    'startTime' => strtotime($data['startTime']),
                    'endTime' => strtotime($data['endTime']),
                );
                $rs = D('Schedule')->where(array('id' => $_POST['id']))->data($schedule)->save();

            } elseif ($type == 'del') {//编辑
                $schedule=D('Schedule')->where(array('id' => $_POST['id']))->find();
                if($this->uid!==$schedule['creatorId']&&$this->uid!==$this::$adminRoleId){
                    $response['msg']='只有发布者或者管理员可以删除';
                    $response['status']=2;
                    $this->ajaxReturn($response);
                }
                $rs = D('Schedule')->where(array('id' => $_POST['id']))->delete();
            } else {
                $response['msg'] = '没有权限!';
                $this->ajaxReturn($response);
            }
            if (!$rs) {
                $response['msg'] = '操作出错!';
                $this->ajaxReturn($response);
            }
            $response['status'] = 1;
            $response['msg'] = '操作成功!';
            $this->ajaxReturn($response);
        }

        $map = "scId={$this->scId} and ((isSelf=1 and messageId={$this->uid}) 
        or (isSelf=0 and FIND_IN_SET ($this->roleId,messageId)))";
        $schedule = D('Schedule')->where($map)->field('id,title,kind,content,startTime,endTime')->select();

        if (!$schedule) {
            $this->ajaxReturn($response);
        }
        $res = array();
        foreach ($schedule as $k => $v) {
            $res[] = array(
                'id' => $v['id'],
                'title' => $v['title'],
                'kind' => $v['kind'],
                'description' => $v['content'],
                'start' => date('Y-m-d H:i:s', $v['startTime']),
                'end' => date('Y-m-d H:i:s', $v['endTime'])
            );
        }
        $response['status'] = 1;
        $response['data'] = $res;
        $this->ajaxReturn($response);
    }

    public function contacts()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        if(in_array($this->roleId,array($this::$jZroleId,$this::$studentRoleId))){
            $this->ajaxReturn($response);
        }
        $map = array(
            'scId' => array(array('eq', $this->scId), array('eq', 0), 'or'),
            'roleId'=>array('not in',array($this::$jZroleId,$this::$studentRoleId,$this::$adminRoleId))
        );
        $roleId = D('role')->where($map)->field('roleId')->select();
        $roleId = array_map(function ($v) {
            return $v['roleId'];
        }, $roleId);
        $where = array(
            'scId' => $this->scId,
            'roleId' => array('in', $roleId),
            'state' => 1
        );
        $user = D('User')->where($where)->field('id,name,phone')->select();
        if (!$user) {
            $this->ajaxReturn($response);
        }
        foreach ($user as $k => $v) {
            $user[$k]['char'] = $this->getFirstCharter($v['name']);
        }
        usort($user, function ($a, $b) {
            $al = $a['char'];
            $bl = $b['char'];
            if ($al == $bl)
                return 0;
            return ($al < $bl) ? -1 : 1; //升序
        });
        $response['status'] = 1;
        $response['data'] = $user;
        $this->ajaxReturn($response);
    }

    //获得首字母
    private function getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631) return 'P';
        if ($asc >= -14630 && $asc <= -14150) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';
        return null;
    }

    //获得通知列表
    public function lists()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $download = $_REQUEST['download'];
        if ($download == 'ensure') {  //下载附件
            $nid = $_REQUEST['nid'];
            $tempfilename = M('Notification')->where(array('id' => $nid))->field('accessory,accessoryName')->find();
            if ($tempfilename) {
                $a = new Accessory('', $this->scId, 'notification');
                $filename =empty($tempfilename['accessory'])?array(): explode(',', $tempfilename['accessory']);
                $aName = empty($tempfilename['accessoryName'])?array():explode(',', $tempfilename['accessoryName']);
                $a->download($filename, $aName);
                die;
            } else {
                $response['msg'] = '无文件';
                $this->ajaxReturn($response);
            }
        }

        $page = empty($_POST['page']) ? 1 : (int)$_POST['page'];
        $count = empty($_POST['count']) ? 10 : (int)$_POST['count'];
        $pre_page = ($page - 1) * $count;
        $limit_page = "$pre_page,$count";
        $order = 'createTime DESC';
        $where = array();

        $uid = $this->uid;
        $map['_string'] = "FIND_IN_SET($uid,uid)";
        $nid = M('NotificationUser')->where($map)->field('nid,uncheckId')->select();
        if (!$nid) {
            $this->ajaxReturn($response);
        }
        $mapId = array();
        foreach ($nid as $k => $v) {
            $mapId[$v['nid']] = $v['uncheckId'];
            $nids[] = $v['nid'];
        }

        foreach ($nid as $k => $v) {
            $nids[] = $v['nid'];
        }
        $where['id'] = array('in', $nids);
        $notice = D('Notification')->where($where)->order($order)
            ->limit($limit_page)
            ->select();
        if (!$notice) {
            $response['data']=array();
            $this->ajaxReturn($response);
        }
        $char=false;
        foreach ($notice as $k => $v) {
            $notice[$k]['accessory'] = empty($v['accessory'])?array():explode(',', $v['accessory']);
            $notice[$k]['accessoryName'] = empty($v['accessoryName'])?array():explode(',', $v['accessoryName']);
            $notice[$k]['createTime'] = date('Y-m-d H:i:s', $v['createTime']);
            $uncheckId = empty($mapId[$v['id']])?array():explode(',', $mapId[$v['id']]);
            if (in_array($this->uid, $uncheckId)) {
                $notice[$k]['status'] = '未查阅';
                $char=true;
            } else {
                $notice[$k]['status'] = '已查阅';
            }
            foreach ($notice[$k]['accessory'] as $k1=>$v1){
                $notice[$k]['accessory'][$k1]=array(
                    'name'=>$v1,
                    'aName'=>$notice[$k]['accessoryName'][$k1]
                );
            }
        }

        $response['status'] = 1;
        $response['char']=$char;
        $response['data'] = $notice;
        $this->ajaxReturn($response);
    }

    //代办事项
    public function task()
    {
        $response = array(
            'status' => 0,
            'data' => array()
        );
        //校园办公的代办事项
        $task = array(
            1 => '教师请假',
            2 => '用车申请',
            3 => '场地申请',
            4 => '公文管理',
            5 => '订餐申请',
        );

        $map = array(
            'result' => 5,
            'scId' => $this->scId,
            'approveId' => $this->uid,
            //'processType' => $this->doc->processType
        );
        $temp = D('ProcessDetail')
            ->where($map)
            ->field("id,relationId,processType,approveId,parentId")
            ->select();

        if (!$temp) {
            $this->ajaxReturn($response);
        }
        $timeMap = array();
        foreach ($temp as $k => $v) {
            $timeMap[$v['relationId'] . $v['processType']][$v['approveId']]
                = $v['timeLine'] > 0 ? date('Y-m-d H:i:s', $v['timeLine']) : '-';
        }

        $sort = array();
        foreach ($temp as $k => $v) {
            $sort[$v['processType']]['id'][] = $v['relationId'];
        }
        $data = array();

        foreach ($sort as $k => $v) {
            $condition = array(
                'scId' => $this->scId,
                'id' => array('in', $v['id']),
                'status' => 0
            );
            if ($k == 1) { //教师
                $temp = D('TeacherLeave')->where($condition)->select();
                foreach ($temp as $k1 => $v1) {
                    $temp[$k1]['createTime'] = date('Y-m-d H:s:i', $v1['createTime']);
                    $temp[$k1]['startTime'] = date('Y-m-d H:s:i', $v1['startTime']);
                    $temp[$k1]['endTime'] = date('Y-m-d H:s:i', $v1['endTime']);
                    $temp[$k1]['exceed'] =
                        !empty($timeMap[$v1['id'] . $k][$this->uid]) ? $timeMap[$v1['id']][$this->uid] : '-';
                }
                $data[] = array(
                    'name' => '教师请假',
                    'thing' => $temp);
            } elseif ($k == 2) {  //用车
                $temp = D('CarApplication')->where($condition)->select();
                foreach ($temp as $k1 => $v1) {
                    $temp[$k1]['createTime'] = date('Y-m-d H:s:i', $v1['createTime']);
                    $temp[$k1]['startTime'] = date('Y-m-d H:s:i', $v1['startTime']);
                    $temp[$k1]['endTime'] = date('Y-m-d H:s:i', $v1['endTime']);
                    $temp[$k1]['exceed'] =
                        !empty($timeMap[$v1['id'] . $k][$this->uid]) ? $timeMap[$v1['id']][$this->uid] : '-';
                }
                $data[] = array(
                    'name' => '用车申请',
                    'thing' => $temp);
            } elseif ($k == 3) {  //场地
                $temp = D('PlaceApplication')->where($condition)->select();
                foreach ($temp as $k1 => $v1) {
                    $temp[$k1]['exceed'] =
                        !empty($timeMap[$v['id'] . $k][$this->uid]) ? $timeMap[$v1['id']][$this->uid] : '-';
                    $temp[$k1]['occupyTime'] = empty($v1['occupyTime']) ? array() : explode(',', $v1['occupyTime']);
                }
                $data[] = array(
                    'name' => '场地申请',
                    'thing' => $temp);
            } elseif ($k == 4) {  //公文
                $temp = D('Document')->where($condition)->select();
                foreach ($temp as $k1 => $v1) {
                    $temp[$k1]['accessory'] = empty($v1['accessory']) ? array() : explode(',', $v1['accessory']);
                    $temp[$k1]['accessoryName'] = empty($v1['accessoryName']) ? array() : explode(',', $v1['accessoryName']);
                    $temp[$k1]['exceed'] =
                        !empty($timeMap[$v['id'] . $k][$this->uid]) ? $timeMap[$v1['id']][$this->uid] : '-';
                    $temp[$k1]['createTime'] = date('Y-m-d H:i:s', $v1['createTime']);
                }
                $data[] = array(
                    'name' => '公文流转',
                    'thing' => $temp);
            }
        }
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);

    }

    public function getChannel()
    {//快速通道
        $response = array(
            'status' => 0,
            'data' => array()
        );
        $scId = $this->scId;
        $data = M('out_web')->where(array('scId' => $scId))
            ->limit('0,10')->order('createTime desc')->select();
        if (!$data)
            $this->ajaxReturn($response);
        $response['status'] = 1;
        $response['data'] = $data;
        $this->ajaxReturn($response);
    }

    /*
        function getIP()
        {
            static $realip;
            if (isset($_SERVER)) {
                if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                    $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                    $realip = $_SERVER["HTTP_CLIENT_IP"];
                } else {
                    $realip = $_SERVER["REMOTE_ADDR"];
                }
            } else {
                if (getenv("HTTP_X_FORWARDED_FOR")) {
                    $realip = getenv("HTTP_X_FORWARDED_FOR");
                } else if (getenv("HTTP_CLIENT_IP")) {
                    $realip = getenv("HTTP_CLIENT_IP");
                } else {
                    $realip = getenv("REMOTE_ADDR");
                }
            }
            return $realip;
        }

        public function getCity()
        {

           // $ip='101.204.247.75';
            //$ip=$this->getIP();
            $ip='101.204.247.75';
            $url="http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
            $ip=json_decode(file_get_contents($url));
            if((string)$ip->code=='1'){
                return false;
            }
            $data = (array)$ip->data;
            $address=strstr($data['city'],'市',true);
            return $address;
        }

        public function weather($city='')
        {
            $response=array(
                'status'=>0,
                'data'=>array()
            );
            if (!$city) {
                $city = $this->getCity();
            }
            $host = "http://jisutqybmf.market.alicloudapi.com";
            $path = "/weather/query";
            $method = "GET";
            $appcode = "1215c3a301254ee79ca773ce9054f2ca";//阿里云appcode
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $appcode);
            $querys = "city=$city&citycode=citycode&cityid=cityid&ip=ip&location=location";
            $bodys = "";
            $url = $host . $path . "?" . $querys;

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            if (1 == strpos("$" . $host, "https://")) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }
            $result = curl_exec($curl);
            $data = json_decode($result, true);
            if($data['msg']!='ok'){
                $this->ajaxReturn($response);
            }
            $result=$data['result'];
            $res=array(
                "city"=>$result['city'],
                "cityid"=>$result['cityid'],
                "citycode"=>$result['citycode'],
                "date"=>$result['date'],
                "week"=>$result['week'],
                "weather"=>$result['weather'],
                "temp"=>$result['temp'],
                "temphigh"=>$result['temphigh'],
                "templow"=>$result['templow'],
                "img"=>$result['img'],
                "humidity"=>$result['humidity'],
                "pressure"=>$result['pressure'],
                "windspeed"=>$result['windspeed'],
                "winddirect"=>$result['winddirect'],
                "windpower"=>$result['windpower'],
                "updatetime"=>$result['updatetime'],
            );
            $response['status']=1;
            $response['data']=$res;
            $this->ajaxReturn($response);
        }*/


}