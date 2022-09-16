<?php

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Worker;

require_once __DIR__.'/vendor/autoload.php';
require_once 'Local.php';
require_once 'Remote.php';

$localStock = 10; //本地库存
$localSales = 0; //本地已售
$redis      = null;
$logFile    = null;

// 创建一个Worker监听2345端口，使用http协议通讯
$http_worker = new Worker('http://0.0.0.0:2345');

$http_worker->onWorkerStart = function () {
    global $redis,$logFile;
    $productId = 1;

    $redis = new Redis();
    $redis->connect('redis', 6379);
    $redis->auth('123');
    $redis->select(1);
    //初始化远程库存数据
    $redis->hMSet(Remote::KEY_SECKILL_PRODUCT.$productId, [
        Remote::KEY_SECKILL_STOCK => 10,
        Remote::KEY_SECKILL_SALES => 0,
    ]);

    $logFile = fopen('state.log', 'a');
};

$http_worker->onWorkerStop = function () {
    global $redis,$logFile;
    $redis->close();
    fclose($logFile);
};

// 启动4个进程对外提供服务
$http_worker->count = 4;

$http_worker->onMessage = function (TcpConnection $connection, Request $request) {
    global $localStock,$localSales,$redis,$logFile;
    $msg = '';
    if ((new Local())->deductStock($localStock, $localSales)  && (new Remote())->deductStock($redis)) {
        $connection->send('秒杀成功');
        $msg = 'result:1, localSales:'.$localSales;
    } else {
        $connection->send('已售罄');
        $msg = 'result:0, localSales:'.$localSales;
    }
    fwrite($logFile, $msg.PHP_EOL);
};

// 运行worker
Worker::runAll();
