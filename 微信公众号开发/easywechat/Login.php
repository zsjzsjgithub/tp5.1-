<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/14 0014
 * Time: 9:49
 */

namespace app\api\controller;


use comservice\Response;
use comservice\Upload;
use EasyWeChat\Factory;
use logicmodel\api\UserLogic;
use think\Controller;
use think\facade\Session;
use validate\BindPhone;
use validate\CheckPhone;

class Login extends Controller
{
    private $userLogic;

    public function __construct()
    {
        parent::__construct();

        $this->userLogic = new UserLogic();
    }
    public function index()
    {
        Session::delete('id');
    }

    /**
     * 登录
     * @param int $pid
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function login($pid=1)
    {

        $config = [
            'app_id' => 'wx38a4f78b301f4430',
            'secret' => 'cb2aa22f728454a5f850c01c42f7d309',
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' =>   '/public/index.php/api/login/back/pid/'.$pid,
            ],
        ];
        $app = Factory::officialAccount($config);
      return   $app->oauth->redirect();
    }


    /**
     * 登录回调
     * @param $pid int 推荐人ID
     * @return array|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function back($pid=1){

        $config = [
            'app_id' => 'wx38a4f78b301f4430',
            'secret' => 'cb2aa22f728454a5f850c01c42f7d309',
        ];
        $app = Factory::officialAccount($config);
        $user = $app->oauth->user();
        if (empty($user)) return Response::fail('用户信息错误');
        $user = $user->toArray();
      $this->userLogic->getUserInfo($user,$pid);
      return $this->redirect('/h5/index.html');

    }


    /**
     * 用户绑定手机号
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function bindPhone()
    {
        $data = input();
        $data['id'] = Session::get('id'); //会员ID
        $result = (new BindPhone())->goCheck($data);
        if ($result !== true) return json($result);
        return json($this->userLogic->bindPhone($data['id'],$data['phone'],$data['code'],$data['name'],$data['province'],$data['city'],$data['area'],$data['detail_address']));
    }


    /**
     * 发送短信验证码
     * @return \think\response\Json
     */
    public function sendSms()
    {
        $data = input();
        $result = (new CheckPhone())->goCheck($data);
        if ($result !== true) return json($result);
        return json($this->userLogic->sendSms($data['phone']));
    }



}