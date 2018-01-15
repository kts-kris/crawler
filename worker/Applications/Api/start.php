<?php
/**
 * Created by PhpStorm.
 * User: liuweigang
 * Date: 15/01/2018
 * Time: 13:48
 */


use \Workerman\Worker;
use \Workerman\WebServer;

// 这里监听8080端口，如果要监听80端口，需要root权限，并且端口没有被其它程序占用
$webserver = new WebServer('http://0.0.0.0:8080');
$webserver->name = 'Api';
$webserver->addRoot('www.qusu.com', __DIR__.'/Article');
\Workerman\Protocols\Http::header('Access-Control-Allow-Origin: *');
\Workerman\Protocols\Http::header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
\Workerman\Protocols\Http::header('Access-Control-Allow-Methods: GET, POST, PUT,OPTIONS');
// 设置开启多少进程
$webserver->count = 4;

Worker::runAll();