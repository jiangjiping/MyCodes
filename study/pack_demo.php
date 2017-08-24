<?php
/**
 * 数据包的头部为4个字节的int型，其中前10位是自定义的标识位(大小为0-1023)，后面22位为实际的数据体的长度，
 * 请将数据包头部用二进制进行传输, 用php pack函数实现
 */

function pack_header($act_mark, $len)
{
    if ($act_mark >= 1024 || $act_mark <= 0) {
        throw  new \Exception("mark bits error!");
        return;
    }
    $data = ($act_mark << 22) | $len;
    return pack("N", $data);
}


function upack_header($bin_data)
{
    $real = unpack("N", $bin_data)[1];
    $ack_mark = $real >> 22;
    echo '解包结果<br/><hr/>';
    echo "act标识: {$ack_mark}<br/>";
    $data_len = ($ack_mark << 22) ^ $real;
    echo "实际数据长度: {$data_len}";
}

$bin_data = pack_header(1023, 365756);
upack_header($bin_data);