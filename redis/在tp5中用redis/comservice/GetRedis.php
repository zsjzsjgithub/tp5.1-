<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/1
 * Time: 16:28
 */
namespace comservice;
use \think\facade\Config;
class GetRedis
{
    public static function getRedis($dbIndex=0)
    {
        $redis = RedisCache::getInstance(Config::get('redis.server'),Config::get('redis.pwd'));
        $redis->selectDb($dbIndex);
        return $redis;
    }
}