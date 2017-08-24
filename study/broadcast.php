<?php
/**
 * php使用UDP广播发数据demo
 */
//client 本地往广播地址发数据
$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
$str = 'hello sihe member';
$flag = socket_sendto($sock, $str, strlen($str), 0, "192.168.3.255", 60000);
var_dump($flag);
echo PHP_EOL;
socket_close($sock);
exit;

//server部署在192.168.3网段
$server = stream_socket_server("udp://0.0.0.0:60000", $errno, $errstr, STREAM_SERVER_BIND);
if (!$server) {
    exit($errno . $errstr . PHP_EOL);
}
while (1) {
    $read = [$server];
    $except = $write = NULL;
    $ret = stream_select($read, $write, $except, 10);
    if ($ret) {
        foreach ($read as $fd) {
            $buffer = stream_socket_recvfrom($fd, 65565, 0, $remote_addr);
            echo $buffer . "\n";
        }
    }
    usleep(50000);
}