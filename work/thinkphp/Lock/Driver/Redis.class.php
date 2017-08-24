<?php
/**
 * tp分布式锁
 * User: Administrator
 * Date: 2016/12/21
 * Time: 11:11
 */

namespace Think\Lock\Driver;

use Think\Cache;
use Think\Lock;

defined('THINK_PATH') or exit();

class Redis extends Lock
{
    public static $key_prefix = 'hm_tp_lk_';

    public function lock($key, $time_out = 300)
    {
        $redis_key = self::$key_prefix . $key;
        if (Cache::redis()->setnx($redis_key, time())) {
            return true;
        }
        while (1) {
            usleep(5000);
            if (Cache::redis()->setnx($redis_key, time())) {
                return true;
            }
            //检测是否发生死锁, 5分钟生存期
            $this->_chkDeadLock($redis_key, $time_out);
        }

    }

    private function _chkDeadLock($redis_key, $ttl = self::LOCK_TTL)
    {
        $timeNow = time();
        $lock_time = Cache::redis()->get($redis_key);
    
        if ($lock_time !== false && $timeNow - $lock_time > $ttl
            && Cache::redis()->getSet($redis_key, $timeNow) < $timeNow
        ) {
            return Cache::redis()->del($redis_key);
        }
        return false;
    }

    public function trylock($key, $time_out = self::LOCK_TTL)
    {
        $redis_key = self::$key_prefix . $key;
        $flag = Cache::redis()->setnx($redis_key, time());
        if ($flag) {
            return true;
        }
        //检测是否死锁
        if ($this->_chkDeadLock($redis_key, $time_out))
            return $this->trylock($key);
        return false;
    }

    public function unlock($key)
    {
        Cache::redis()->del(self::$key_prefix . $key);
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
}