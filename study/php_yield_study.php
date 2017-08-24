<?php
//模拟协程
$task1 = array('a', 'b', 'c');
$task2 = array(10, 55, 255);
$task3 = array('#', '$', '*');


$queue = new SplQueue();
$queue->enqueue($task1);
$queue->enqueue($task2);
$queue->enqueue($task3);

//while (!$queue->isEmpty()) {
//    $current = $queue->dequeue();
//    echo array_shift($current) . "<br/>";
//    if (!empty($current)) {
//        $queue->enqueue($current);
//    }
//}


//really 协程
function gen($task)
{
    foreach ($task as $item) {
        echo $item . "<br/>";
        yield;
    }
}

class Task
{
    public $taskId;
    public $handler;
    public $beforeFirstYield = true;
    protected $socket = null;
    
    public function __construct($handler)
    {
        $this->handler = $handler;
        $this->taskId = spl_object_hash($this);
    }
    
    public function setSocket($socket)
    {
        $this->socket = $socket;
    }
    
    public function getSocket()
    {
        return $this->socket;
    }
}

class SystemCall
{
    private static $tasks = [];
    
    private static $singleton = null;
    
    private static $client_sockets = array();
    
    protected static $first = true;
    
    /**
     * @return SystemCall
     */
    public static function init()
    {
        if (is_null(self::$singleton)) {
            self::$singleton = new self();
        }
        return self::$singleton;
    }
    
    /**
     * @param Generator $gen
     * @return self
     */
    public function addTask(Generator $gen)
    {
        $task = new Task($gen);
        self::$tasks[$task->taskId] = $task;
        return $this;
    }
    
    public function run()
    {
        while (!empty(self::$tasks)) {
            $tv_sec = empty(self::$client_sockets) ? null : 2;
            if ($tv_sec != null) {
                $reads = $writes = self::$client_sockets;
                $except = null;
                if (!stream_select($reads, $writes, $except, $tv_sec)) {
                    continue;
                }
            }
            foreach (self::$tasks as $id => $task) {
                if (!$task->handler->valid()) {
                    unset(self::$tasks[$id]);
                    continue;
                }
                if ($task->beforeFirstYield) {
                    $task->beforeFirstYield = false;
                    $socket = $task->handler->current();
                    $task->setSocket($socket);
                    self::$client_sockets[(int)$socket] = $socket;
                } else {
                    $curr_socket = $task->getSocket();
                    if (!is_null($curr_socket) && in_array(intval($curr_socket), $reads)) {
                        $task->handler->send('');
                    }
                }
            }
        }
    }
}


//SystemCall::init()
//    ->addTask(gen($task1))
//    ->addTask(gen($task2))
//    ->addTask(gen($task3))
//    ->run();

//包含yield关键字就是一个协程， 协程==含yield的function

//协程函数无法直接运行 只能通过current(), send()方法调用通信

// yield == pause + recv + return


while (1) {
    $reads = [];
    $writes = null;
    $except = null;
    $tv_sec = null;
    $server_socket = stream_socket_server("tcp://0.0.0.0:15695", $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
    $reads[] = $server_socket;
    if (!stream_select($reads, $writes, $except, $tv_sec)) {
        $msg = 'nothing!!' . microtime(true) . PHP_EOL;
        file_put_contents(__DIR__ . '/log.txt', $msg);
        continue;
    }
}


// 基于协程的采集程序
$urls = [
    ['uri' => 'tcp://react.dev:80', 'req' => "GET /demo.php?id=1 HTTP/1.1\r\nHost:react.dev\r\n\r\n"],
    ['uri' => 'tcp://react.dev:80', 'req' => "GET /demo.php?id=2 HTTP/1.1\r\nHost:react.dev\r\n\r\n"],
    ['uri' => 'tcp://react.dev:80', 'req' => "GET /demo.php?id=3 HTTP/1.1\r\nHost:react.dev\r\n\r\n"]
];

foreach ($urls as $url) {
    
}

function craw_data($url, $request)
{
    $client = stream_socket_client($url, $errno, $errstr);
    if (!$client) {
        echo "[$errno] $errstr<br/>";
    }
    fwrite($client, $request);
    yield $client;
    $resp = fread($client, 8192);
    var_dump($resp);
    
}

SystemCall::init()
    ->addTask(craw_data($urls[0]['uri'], $urls[0]['req']))
    ->addTask(craw_data($urls[1]['uri'], $urls[1]['req']))
    ->addTask(craw_data($urls[2]['uri'], $urls[2]['req']))
    ->run();