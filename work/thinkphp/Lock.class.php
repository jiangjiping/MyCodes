<?php
/**
 * 对非阻塞锁测试时,不能用同一个浏览器的两个tab窗口去请求，
 * 因为浏览器本身的机制对相同URL请求，要等第一个tab请求完成才会去
 * 请求第二个tab的url地址
 * User: randy
 * Date: 2016/12/20
 * Time: 18:31
 */

namespace Think;


abstract class Lock
{
    private static $lock = [];

    /**
     * 锁的生存时间, 防止死锁
     */
    const LOCK_TTL = 30;

    /**
     * @return self;
     */
    public static function redis()
    {
        return self::init('redis');
    }

    /**
     * @return self;
     */
    public static function file()
    {
        return self::init('file');
    }

    private static function init($driver_name)
    {
        $class = 'Think\\Lock\\Driver\\' . ucfirst($driver_name);
        if (isset(self::$lock[$driver_name]) && !empty(self::$lock[$driver_name])) {
            return self::$lock[$driver_name];
        }
        self::$lock[$driver_name] = new $class();
        return self::$lock[$driver_name];
    }

    /**
     * @param $key
     * @return boolean
     */
    abstract function lock($key);

    /**
     * @param $key
     * @return boolean
     */
    abstract function trylock($key);

    /**
     * @param $key
     * @return boolean
     */
    abstract function unlock($key);

}