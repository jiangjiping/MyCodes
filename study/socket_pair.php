<?php
/**
 * @author randy
 * 使用socket pair实现一个master多个worker之间通信
 */
define("worker_num", 4);
$workers = array();
$i = 0;
$master_channels = array();
while (count($workers) < worker_num) {
    $channel = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    $master_channels[] = $channel[0];
    $pid = pcntl_fork();
    if ($pid === -1) {
        exit('fork error' . PHP_EOL);
    }
    if ($pid > 0) {
        fclose($channel[1]);
        $i++;
        $workers[$pid] = $pid;
        continue;
    }
    if ($pid == 0) {
        //worker logic
        fclose($channel[0]);
        $can_say = true;
        for (; ;) {
            $worker_pid = posix_getpid();
            $reads = $writes = array($channel[1]);
            $except = NULL;
            if (!stream_select($reads, $writes, $except, 1)) {
                continue;
            }
            foreach ($reads as $read) {
                $read = socket_import_stream($read);
                $buffer = socket_read($read, 1024, PHP_NORMAL_READ);
                echo "worker {$worker_pid} recv data: {$buffer}";
            }
            usleep(rand(100, 50000));
            if ($can_say && $i == 0) {
                $can_say = false;
                fwrite($channel[1], "worker {$worker_pid} say hello!\n");
            }
            
        }
    }
}

for (; ;) {
    $reads = $writes = $master_channels;
    $except = NULL;
    if (!stream_select($reads, $writes, $except, 1)) {
        continue;
    }
    foreach ($reads as $read) {
        $buffer = fread($read, 65535);
        foreach ($master_channels as $channel)
            fwrite($channel, "master forward=>{$buffer}");
    }
}