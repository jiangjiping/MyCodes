<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-2-27
 * Time: 下午2:29
 */
class Middleware
{
    public static function login($params)
    {
        $params['login'] = 1;
        return $params;
    }
    
    public static function auth($params)
    {
        $params['auth'] = 1;
        return $params;
    }
    
    public static function init($params)
    {
        $params['init'] = 1;
        return $params;
    }
}

function run()
{
    echo "everything is ok, running....<br/>";
}

$request = [
    'user_id' => 45,
    'name'    => 'hello'
];
$middleware = [
    array(Middleware::class, 'login'),
    array(Middleware::class, 'auth'),
    array(Middleware::class, 'init')
];

$default = $request;

$resp = array();
array_reduce($middleware, function ($default, $item) use (&$resp) {
    $resp = call_user_func($item, $default);
    return $resp;
}, $default);

run();
print_r($resp);