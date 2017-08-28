<?php
//测试socket服务端, php当中write broken pipe并不会退出进程 估计只有c++等才会
$server_socket = stream_socket_server('tcp://127.0.0.1:9655', $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
if (!$server_socket) {
    exit($errstr);
}
$clients = array();
while (1) {
    $reads = $writes = array_merge([$server_socket], $clients);
    $except = NULL;
    if (stream_select($reads, $writes, $except, 1)) {
        foreach ($reads as $read) {
            if ($read == $server_socket) {
                $client = stream_socket_accept($read, 0);
                stream_set_blocking($client, 0);
                $clients[(int)$client] = $client;
            } else {
//                unset($clients[(int)$read]);
                fclose($read);
                //       fwrite($read, chr(0));
            }
        }
    }
    sleep(1);
}