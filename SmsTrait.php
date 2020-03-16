<?php

namespace Rzy\Smscode;

use Carbon\Carbon;
use Rzy\Smscode\Interfaces\SmsSend;
use Rzy\Smscode\Models\SmsCode;

trait SmsTrait
{
    public function sendSms($phones, $content)
    {
        $smsGuard = config('sms.default');
        $smsGuardClass = "Rzy\\Smscode\\Guards\\" . ucfirst($smsGuard) . 'Guard';
        $guardInstance = new $smsGuardClass;
        if ($guardInstance instanceof SmsSend) {
            return $guardInstance->sendSms($phones, $content);
        }
        throw new Exception("sms guard must implements SmsSend interface", 1);
    }

    protected function checkSmsCode($phone, $code, $expire = 60 * 5)
    {
        $sms_code = SmsCode::where('phone', $phone)->where('code', $code)->where('status', SmsCode::STATUS_UNUSED)->orderBy('id', 'desc')->first();
        if (!$sms_code || time() - $sms_code->created_at->timestamp > $expire) {
            return ['message' => '验证码错误'];
        }
        $sms_code->status = SmsCode::STATUS_USED;
        $sms_code->save();

        return true;
    }

    protected function sendCodeAndSave($phone)
    {
        if (!preg_match('~^1[0-9]{10}$~', $phone)) {
            return [
                'success' => false,
                'message' => '请输入正确的手机号码',
            ];
        }
        $dayCount = SmsCode::whereDate('created_at', date("Y-m-d"))->where('ip', request()->ip())->count();
        if ($dayCount > config('sms.ip_day_limit')) {
            return [
                'success' => false,
                'message' => '发送短信次数超过限制',
            ];
        }
        $phoneCount = SmsCode::where('phone', request('phone'))->where('created_at', '>', Carbon::now()->subHours(1))->count();
        if ($phoneCount > config('sms.phone_hour_limit')) {
            return [
                'success' => false,
                'message' => '发送短信次数超过限制',
            ];
        }
        $code = mt_rand(1000, 9999);
        $content = '你的验证码为' . $code . ', 有效时间为5分钟';
        $sms_code = new SmsCode;
        $sms_code->ip = request()->ip();
        $sms_code->phone = $phone;
        $sms_code->code = $code;
        $sms_code->save();
        $res = $this->sendSms($phone, $content);
        $sms_code->result = $res->result;
        $sms_code->save();
        if ($res->success == SendReturn::SUCCESS_CODE) {
            return [
                'success' => true,
                'message' => '发送成功',
            ];
        }

        return [
            'success' => false,
            'message' => '发送失败, 请重试',
        ];
    }
}
