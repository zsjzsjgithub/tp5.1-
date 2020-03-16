<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/14 0014
 * Time: 10:27
 */

namespace logicmodel\api;


use comservice\GetRedis;
use comservice\Response;
use comservice\Upload;
use datamodel\Address;
use datamodel\User;
use enum\CommonEnum;
use logicmodel\MemberLogic;
use think\Db;
use think\facade\Log;
use think\facade\Request;
use think\facade\Session;

class UserLogic
{

    private $userData;
    private $addressData;
    private $popularizeLogic;
    private $redis;
    public function __construct()
    {
        $this->userData = new User();
        $this->addressData = new Address();
        $this->popularizeLogic = new PopularizeLogic();
        $this->redis = (new GetRedis())::getRedis();
    }

    /**
     * 获取用户ID
     * @param $user
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function getUserInfo($user,$pid)
    {
        $where[] = ['openid','=',$user['id']];
        $where [] = ['is_del','=',CommonEnum::$undeleted];
        $userInfo = $this->userData->getByWhere($where);
        if ($userInfo) {
            //返回用户信息
            $user_id = $userInfo['id']; //用户ID
            $data['nick_name'] = $user['nickname'];
            $data['wechat_img'] = $user['avatar'];
            $this->userData->updateByWhere(['id'=>$user_id],$data); //更新用户基本信息
        } else {
            //新建一条记录
            $data['openid'] = $user['id'];
            $data['nick_name'] = $user['nickname'];
            $data['wechat_img'] = $user['avatar'];
            $data['pid'] = $pid;
            $data['upid'] = $pid;
            $data['create_time'] = date('Y-m-d H:i:s');
           $user_id =  $this->userData->saveEntityAndGetId($data);
            if ($user_id >0){
             $content = 'http://redwine.zsj.mobi/h5/index.html#?pid='.$user_id ;//生成专属二维码
                Log::info($content);
             $qrcode = Upload::qrcode($content);
             $this->userData->updateByWhere(['id'=>$user_id],['qrcode'=>$qrcode]);
                $this->updteDirect($user_id,$pid);//修改团队成员 直推人员
            }
        }
        if ($user_id > 0 ){
            Session::set('id',$user_id);
            return Response::success();
        }
        return Response::fail('授权失败,请稍后重试');
    }


    /**
     * 完善用户信息
     * @param $id
     * @param $phone
     * @param $code
     * @param $name
     * @param $province
     * @param $city
     * @param $area
     * @param $detail_address
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function  bindPhone($id,$phone,$code,$name,$province,$city,$area,$detail_address)
    {
        $userInfo = $this->userData->getById($id);
        if ($userInfo['phone']) return Response::fail('您已绑定手机号,无需重复绑定');
        $where [] = ['phone','=',$phone];
        $where [] = ['is_del','=',CommonEnum::$undeleted];
        $userInfo = $this->userData->getByWhere($where);
        if ($userInfo) return Response::fail('当前手机号已被绑定,请更换手机号');

        //验证验证码
        $checkCode = $this->redis->getItem($phone);
        if ($checkCode != $code) return Response::invalidParam('验证码错误,请重新输入');

        $data['phone'] = $phone;
        $data['member'] = $phone;
        $data['active_time'] = date('Y-m-d H:i:s');
        Db::startTrans();
        $result = $this->userData->updateByWhere(['id'=>$id],$data);
        if ($result <= 0){
            Db::rollback();
            return Response::fail('手机号绑定失败');
        }
        $addressData['uid'] = $id;
        $addressData['name'] = $name;
        $addressData['phone'] = $phone;
        $addressData['province'] = $province;
        $addressData['city'] = $city;
        $addressData['area'] = $area;
        $addressData['detail_address'] = $detail_address;
        $addressData['status'] = 1; //默认选中
        $addressData['create_time'] = date('Y-m-d H:i:s');
        $result = $this->addressData->saveEntityAndGetId($addressData);
        if ($result > 0) {
            $this->popularizeLogic->popularize($id);
            Db::commit();

            return Response::success('绑定成功');
        }
        Db::rollback();
        return Response::fail('绑定失败');


    }

    /**
     * 修改团队成员信息
     * @param $id
     * @param $pid
     * @throws \think\Exception
     */
    public function updteDirect($id,$pid)
    {
        $field = ['id','pid','upid'];
        $userData = (new MemberLogic())->listParent($id,$field,2,0);
        $groupArr = array_column($userData,'id');
        $where [] = ['id','in',$groupArr];
        $this->userData->updateForInc($where,'group_direct',1); //修改团队成员
        $this->userData->updateForInc(['id'=>$pid],'total_direct',1);//修改直推人数

    }

    public function sendSms($phone)
    {


        $code = rand(1111, 9999);
        $content = "尊敬的用户您好！您正在进行手机验证，验证码：{$code}，有效期5分钟，如非本人操作，请勿泄露！【红酒商城】";
        $url = "http://139.196.230.114:8088/sms.aspx";
        $curlPost = "action=send&userid=2798&account=xls&password=xls00001&mobile={$phone}&content={$content}&sendTime=&extno=";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  //允许curl提交后,网页重定向
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        $xml = simplexml_load_string($data);
        $data = json_decode(json_encode($xml), TRUE);
        if ($data['returnstatus'] == 'Success') {
            $this->redis->setItem($phone, $code);
            $this->redis->settime($phone, 60 * 5);
            return Response::success('短信验证码已发送到您手机!');
        } else {
            return Response::fail('短信发送失败');
        }
    }

}