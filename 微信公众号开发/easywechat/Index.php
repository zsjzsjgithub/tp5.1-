<?php

namespace app\index\controller;

use comservice\GetRedis;
use datamodel\Test;

use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Image;
use Endroid\QrCode\QrCode;
use think\App;

use think\Controller;
use think\Db;
use think\facade\Config;
use think\facade\Env;
use think\facade\Log;
use think\facade\Request;
use Yansongda\Pay\Pay;

class Index extends Controller
{
    protected $config = [
        'app_id' => '2016092900620682',
        'notify_url' => 'http://6garqn.natappfree.cc/index/index/notify',
        'return_url' => 'http://6garqn.natappfree.cc/index/index/returns',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlkcNnt7gZoaYHuKBvMxXFbHMpRFZGsBNh9UbPEbU8sjZ7SYvmTqeBmho2AWDPGZCviPmYpV5YC6IXHDNSJSUlefFLAWbdwxnvkP8WvHLPw5XUPRjz2F+uGrLrKccTHFXJQ6zDM8yv9rb5HUC5NtKC2gKAdcpCA7Jf/WmvAjMHAPKYL45tP3TkYadUE06kDGgclNHp3lHf8p8CkNXZq67IXvw5Snnc7/Ug/PaHczVLD7kmDKillL+r/l0pvD6Oi392uD2lf9AGfLrAHdlLpK3jLQSId4XLGHfiI+MZNq7zAplI/D82cnL7zS8qY4e3ueqTVNz0KyX2jpAJ8aq+y6UiwIDAQAB',

        // 加密方式： **RSA2**
        'private_key' => 'MIIEowIBAAKCAQEAoWx//OanpgEfjHYDrx3k3kNHZ1748AFOkHo2aYkZUnqHPQjnw7/08qA7QJyln73fq/1H/eEbd9aQAFF5QadvDH5V0fboaIMGoppGRIZjP4H6oki+TUHoqzM7qQuq//1L9sP9pmTMN3U+hIogKKmW/ibXMyds+ev+gQ0YULE4XjIu7bUy6npan2YCyt9GprJApEpw7+b1/Fl6xwt8D11wWFcppTdXoq7DhSvZOU/kBjBC3t5DQ6ECOJVPvmaXF04ph6Q62CAU+cGJcbSV34hb/yWUs4EbwS5wEtfQZzI9J3RkHgxHP3eQTqsfn20XVVWXT0qiN/WVY0qEWDEiC/s8WwIDAQABAoIBAARrNe20Wq5+pWBw8pTemp9C9DduMB5RytbFoaut5xWM6RrQnZeML6ZcoIKaRyZiSjcpDclzWg8jvnljwY/8h1uuMac3vRDvVUUqN/Y0P49DfZrnpt3yie31zxJHkXzEcEnm/5CIfdaezQFPzqWOTuXSJl3uHFuTDDp4I0xCLarWZbVPLj9nhHeGWIfaik8L2iuKl8C2d73BF2W4/L3fsEM/bxFODps70KUeXDG76faCzCY4nVNcdyOTmAdZua0Go508f7S0PDd2VlskTf8BhxE67wFxfoDPsD+BuqPQS0nCE1B/r6xmoSyTxw2050QkdjdeQQOjhPk5rDim7+l6LnECgYEAy5sy2Qy0pBNta26d4grpqL66bjPx48PXuCMvceFz2dY8W/LuqiXJfjuOsk9L2vDHi/20aqGEhNgoIygu3C4vljwjrky6AVo0hmXmEvRVoXFCtfss29VBoRbEYcSSZTafY2IsiEmjfnetEc3IXrSuttiBpjsYgOe5kfhdmWYaduUCgYEAyvZ8IrORN/e8FFXS6/HzOqiqFRlAEMMXJLM3TNbOK6L38LHWiDPGLgmKsweudcJAGA3ckZK60CKtKL3sPTqymrulhBitlGFBaG+Z1hE5SEmtLpO6JMPxOURIx751u63a19chE7XbQ+Wyeg65apC1YXy2F/jKujD8VaVMLPoOcj8CgYBxGQChuIEPMwtwxb1FtrIcXfXJCWmwDzVgv4q3Q3jK5Eoa+VLBiMPoLsURHyMPtvfhN0hkgtvNvxRskwGUpMMiPL3FMDGSVr0eerPWi4qVZwibda5xXoBaLv9fH4YCWtkmp339Jop+0ZN9dEV89fim8JTz9Zei4xUdlEzJzQGeUQKBgAUpneC4FpKI36TYxgOwZNJyJdlhigqjG7yYPmja8eWUQKBDtcRDJOBY58lEAcEHDuBNwfWF+PCAYU0u/4pTKuXargwdIJUsoCBK7mvOhll7XkdYBJ0YytL9FKjYBGCgvHdPBo1cy9X/SrnmE/tM8QAozuzvLCDuTAzVpoliVum7AoGBAMAXufOFdX/kt/KnwwzHKm2IwAwqb3g+hR/rnouG6u7jT1TT2hgXztrjFA+FcX2BarijozlKeINVo+xQ0nOKOwjud6NGo6+5vtkwpAXUdet5Nv3Hnm803yghSTtMxPEhppS7NheJqkVj+SEpFnGF1+jOIQ34XrsJK+jRM/wHjpBq',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            'level' => 'info', // 建议生产环境等级调整为 info,开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效,默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => 'dev', // optional,设置此参数,将进入沙箱模式
    ];
    private $apps;

    public function __construct()
    {
        parent::__construct();
        $config = [
            'app_id' => 'wxa19cf5b362e32a54',
            'secret' => 'a5f809fcc2a8709a7b216cfd8b773cef',
            'token' => 'hellozsj',
            'response_type' => 'array'
        ];
        $this->apps = (new Factory())::officialAccount($config);
    }

    public function workerman()
    {
        return view('index\workerman');
    }

    public function test()
    {
        $qrCode = new  QrCode('http://youqianxiang.hnzhanfeng.com/youqianxiang/index.html');
        $qrCode->setSize(300);
        $qrCode->setWriterByName('png');
        $qrCode->setMargin(10);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setForegroundColor(['r' => 108, 'g' => 182, 'b' => 229, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 213, 'g' => 241, 'b' => 251, 'a' => 0]);
        $qrCode->setLogoPath('./upload/2.jpg');
        $qrCode->setLogoSize(70);
//保存二维码
        $filepath = './upload/';
        if (!file_exists($filepath)) {
            mkdir($filepath, 0700, true);
        }
        $res = $qrCode->writeFile($filepath . time() . uniqid() . '.' . 'png');
        dump($res);
    }

    public function index()
    {
//        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V5.1<br/><span style="font-size:30px">12载初心不改(2006-2018) - 你值得信赖的PHP框架</span></p></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="eab4b9f840753f8e7"></think>';
        echo '首页';
    }

    public function qrcode()
    {

        $order = [
            'out_trade_no' => time(),
            'total_amount' => '100',
            'num' => 10,
            'subject' => '大白兔',
        ];
        $alipay = Pay::alipay($this->config)->web($order);
        return $alipay->send();// laravel 框架中请直接 `return $alipay`
    }

    /**
     * 同步
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function returns()
    {
        $data = Pay::alipay($this->config)->verify(); // 是的,验签就这么简单！
        //echo $data->trade_no;
        dump($data);
        // 订单号：

        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
    }

    /**
     * 异步回调
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function notify()
    {
        $alipay = Pay::alipay($this->config);
        try {
            $data = $alipay->verify(); // 是的,验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断,在支付宝的业务通知中,只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时,支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额(即商户订单创建时的金额)；
            // 3、校验通知中的seller_id(或者seller_email) 是否为out_trade_no这笔单据的对应的操作方(有的时候,一个商户可能有多个seller_id/seller_email)；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
            if ($data->trade_status === 'TRADE_SUCCESS') {
                app()->log->info('异步验签成功');
            }
            Log::info('Alipay notify{data}', ['data' => $data]);
            Test::create(['name' => '张三', 'pid' => 1, 'path' => '1,2,3']);
        } catch (\Exception $e) {
            $e->getMessage();
        }

        return $alipay->success()->send();// laravel 框架中请直接 `return $alipay->success()`
    }

    /**
     * 退款
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function refund()
    {
        $order = [
            'out_trade_no' => 1559197346,
            'total_amount' => '100',
            'num' => 10,
            'subject' => '大白兔',
        ];
        $data = Pay::alipay($this->config)->refund($order);
        var_dump($data);
    }

    /**
     * 公众号授权登录
     */
    public function login()
    {

        $config = [
            'app_id' => 'wxa19cf5b362e32a54',
            'secret' => 'a5f809fcc2a8709a7b216cfd8b773cef',
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => '/index.php/index/index/back',
            ],
        ];
        $app = Factory::officialAccount($config);
        return $app->oauth->redirect();
    }

    /**
     * 登录回调
     */
    public function back()
    {
        $config = [
            'app_id' => 'wxa19cf5b362e32a54',
            'secret' => 'a5f809fcc2a8709a7b216cfd8b773cef'
        ];
        $app = Factory::officialAccount($config);
        $userinfo = $app->oauth->user();
        $userinfo = $userinfo->toArray();
        $this->save($userinfo);
        $this->redirect('/index.php/index/index/tests');
    }

    public function save($userinfo)
    {

        $openid = $userinfo['original']['openid'];
        $where = ['openid' => $openid];
        $userData = Test::where($where)->find();
        if ($userData) {
            //  $user_id=$userData['id'];
            $data['nick_name'] = $userinfo['nickname'];
            $data['avatar'] = $userinfo['avatar'];
            $res = Test::where($where)->update($data);
        } else {
            $data['openid'] = $userinfo['original']['openid'];
            $data['nick_name'] = $userinfo['nickname'];
            $data['avatar'] = $userinfo['avatar'];
            $data['sex'] = $userinfo['original']['sex'];
            $res = Test::create($data);
        }
    }

    public function tests()
    {
        $config = [
            'app_id' => 'wxa19cf5b362e32a54',
            'secret' => 'a5f809fcc2a8709a7b216cfd8b773cef',
        ];
        $app = Factory::officialAccount($config);
        $buttons = [
            [
                "type" => "click",
                "name" => "今日歌曲",
                "key" => "V1001_TODAY_MUSIC"
            ],
            [
                "name" => "菜单",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "搜索",
                        "url" => "http://www.soso.com/"
                    ],
                    [
                        "type" => "click",
                        "name" => "赞一下我们",
                        "key" => "V1001_GOOD"
                    ],
                ],
            ],
        ];
        $app->menu->create($buttons);
        var_dump($app->menu->list());
    }

    public function box()
    {

//        $array=['a','c','b'];
//        sort($array);
//        var_dump($array);
        //1）将token、timestamp、nonce三个参数进行字典序排序
        // 2）将三个参数字符串拼接成一个字符串进行sha1加密
        // 3）开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
//    $array=[
//        'hello',input('timestamp'),input('nonce')
//    ];
//     sort($array);
//    $str=sha1(implode('',$array));
//    if ($str===input('signature')){
//   echo input('echostr');
//    }
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $echostr = $_GET['echostr'];
        $array = ['tokenhello', $timestamp, $nonce];
        sort($array);
        $string = implode('', $array);
        $sing_string = sha1($string);
        if ($sing_string == $signature) {
            //告诉微信验证成功
            echo $echostr;
            exit;
        }

    }

    public function boxs()
    {
        $this->apps->server->push(function ($message) {

            switch ($message['MsgType']) {

                case 'event':
                    switch ($message['Event']){
                        case 'subscribe':
                            return '欢迎订阅sjmember的账号a';
                            break;
                    }
                    break;
                case 'text':
                    return '收到文字消息';
                    break;
                case 'image':
                    return '图片';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                // ... 其它消息
                default:
                    return '收到其它消息';
                    break;
            }

            // ...
        });
        $response = $this->apps->server->serve();
        return $response->send();
    }
public function boxss(){
        //echo Request::url();
    $redis=GetRedis::getRedis();
    $redis->setItem('name','张三');
    echo $redis->getItem('name');
}
public function phpinfo(){
        echo phpinfo();
}
public function create(){
    $copy_time = '15:36';
    //把设置的时间转化为时间戳
    $copy_time = strtotime(date("Y-m-d {$copy_time}"));
    $copy_times = $copy_time + 1 * 60;
    if (time() >= $copy_time && time() <= $copy_times) {
        $res = Db::table('user')->insert(['name' => '你好', 'age' => 25, 'create_time' => date('Y-m-d H:i:s')]);
        if ($res) {
            return '添加成功';
        } else {
            return '添加失败';
        }
    }else{
        Db::table('user')->insert(['name' => '未到时间', 'age' => 25, 'create_time' => date('Y-m-d H:i:s')]);
    }
}
}
