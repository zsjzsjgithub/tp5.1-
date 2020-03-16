<?php
/**
 * Created by shihe
 * User: Administrator
 * Date: 2019/4/10
 * Time: 14:07
 */

namespace comservice;


class ResponseCode
{
    public static $success=0; //成功
    public static $invalidParam=4000; // 参数错误
    public static $invalidLogin=4001; //用户未登录
    public static $invalidAccess=4002; //账号被禁用
    public static $networkError=4003; //网络错误
    public static $closed=4004; //闭站 关闭所有请求
    public static $balance=4005; //余额不足
    public static $password=4006; //未填写支付密码
    public static $unactive=4007; //未激活需要
    public static $fail=5000; //操作失败



}