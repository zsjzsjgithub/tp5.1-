<?php
namespace comservice;

use think\Exception;

class Response
{
    /**
     * 返回成功
     * @param  $data
     * @param string $msg
     * @return array
     */
    public static function success( $msg='success',array $data=null)
    {
        if($data===null)
        {
           return ["errcode"=>ResponseCode::$success,"msg"=>$msg];
        }

        return  ["errcode"=>ResponseCode::$success,"msg"=>$msg,"res"=>$data];
    }

    /**
     * 返回失败，一般核心业务逻辑出现错误，比如入库失败

     * @param string $msg
     * @return array
     */
    public static function fail( $msg='操作失败，请稍后重试')
    {
        return ["errcode"=>ResponseCode::$fail,"msg"=>$msg];

    }

    /**
     * 参数有误

     * @param string $msg
     * @return array
     */
    public static function invalidParam($msg='参数错误')
    {
        $res=["errcode"=>ResponseCode::$invalidParam,"msg"=>$msg];
        return $res;
    }
    /**
     * 没有登录，返回错误

     * @param string $msg
     * @return array
     */
    public static function invalidLogin($msg='请登录后重试')
    {
        $res=["errcode"=>ResponseCode::$invalidLogin,"msg"=>$msg];
        return $res;
    }
    /**
     * 没有登录，返回错误

     * @param string $msg
     * @return array
     */
    public static function closed($msg='系统维护中')
    {
        $res=["errcode"=>ResponseCode::$closed,"msg"=>$msg];
        return $res;
    }

    /**
     * 权限不够，禁止访问
     * @param string $msg
     * @return array
     */
    public static function invalidAccess($msg='权限不够，禁止访问')
    {
       return ["errcode"=>ResponseCode::$invalidAccess,"msg"=>$msg];

    }
    public static function networkError($msg='网络错误，请稍后重试')
    {
        return ["errcode"=>ResponseCode::$networkError,"msg"=>$msg];
    }
    public static function balance($msg='余额不足,请先充值')
    {
        return ["errcode"=>ResponseCode::$balance,"msg"=>$msg];
    }
    public static function password($msg='请先完善支付密码')
    {
        return ["errcode"=>ResponseCode::$password,"msg"=>$msg];
    }
    public static function active($msg='您的账号需要后台管理员激活')
    {
        return ["errcode"=>ResponseCode::$unactive,"msg"=>$msg];
    }
    /**
     * 自定义返回信息,code为1——100之间的数字
     * @param string $data
     * @param string $code
     * @param string $msg
     * @return array
     */
    public static function custom(int $code,string $msg,array $data=null)
    {
        if($data===null)
        {
            return ["errcode"=>$code,"msg"=>$msg];

        }

        return ["errcode"=>$code,"msg"=>$msg,'res'=>$data];
    }

    /**
     * 系统异常，返回信息
     * @param int $code
     * @param Exception $e
     * @return array
     */
    public static function systemException(int $code,\Exception $e)
    {
        return ["errcode"=>$code,"msg"=>$e->getMessage()];

    }

    /**
     * 抛出ThinkPhp的Exception,返回信息
     * @param Exception $e
     * @return array
     */
    public static function tpException(Exception $e)
    {
        if($e->getCode()) {
            return ["errcode" => $e->getCode(), "msg" => $e->getMessage()];
        }
        return ["errcode" => ResponseCode::$fail, "msg" => $e->getMessage()];

    }

    /**
     * assert的异常，返回信息
     * @param \InvalidArgumentException $e
     * @return array
     */
    public static function assertException(\InvalidArgumentException $e)
    {
        return ["errcode"=>ResponseCode::$invalidParam,"msg"=>$e->getMessage()];
    }
}