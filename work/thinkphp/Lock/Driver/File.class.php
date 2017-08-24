<?php
namespace Think\Lock\Driver;

use Think\Lock;

defined('THINK_PATH') or exit();

/**
 * tp文件锁
 * User: randy
 * Date: 2016/12/20
 * Time: 18:44
 */
class File extends Lock
{
    public static $fds = [];

    /**
     * 锁的key和mutex_file文件的键值对
     * @var array
     */
    public static $keyFileMap = [];

    public static $mutex_file = '';

    private function _getMutexFile($key)
    {
        if (isset(self::$keyFileMap[$key])) {
            return self::$keyFileMap[$key];
        }
        $lock_path = DATA_PATH . '/lock';
        is_dir($lock_path) or mkdir($lock_path, 0777, true);
        self::$keyFileMap[$key] = $lock_path . '/lock_' . md5($key) . '.mutex';
        is_file(self::$keyFileMap[$key]) or touch(self::$keyFileMap[$key]);
        return self::$keyFileMap[$key];
    }

    public function lock($key)
    {
        $mutex_file = $this->_getMutexFile($key);
        $fd = fopen($mutex_file, 'r');
        self::$fds[md5($mutex_file)] = $fd;
        return flock($fd, LOCK_EX);
    }

    public function trylock($key)
    {
        $mutex_file = $this->_getMutexFile($key);
        $fd = fopen($mutex_file, 'r');
        self::$fds[md5($mutex_file)] = $fd;
        if (!flock($fd, LOCK_EX | LOCK_NB)) {
            fclose($fd);
            return false;
        }
        return true;
    }

    public function unlock($key)
    {
        $mutex_file = $this->_getMutexFile($key);
        $fd_key = md5($mutex_file);
        flock(self::$fds[$fd_key], LOCK_UN);
        fclose(self::$fds[$fd_key]);
        //is_file($mutex_file) and unlink($mutex_file);
        unset(self::$fds[$fd_key]);
        unset(self::$keyFileMap[$key]);
    }

    public function __destruct()
    {

    }
}