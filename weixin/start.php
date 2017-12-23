<?php
/**
 * Created by PhpStorm.
 * User: liuweigang
 * Date: 13/12/2017
 * Time: 11:19
 */

require_once(dirname(__DIR__) . "/class/class.weixin.oa.sogou.php");
$crawler = new CrawlerWeixinSogou();
$crawler->setConfig([
    'keywords' => '医药',
    'debug' => true,
    'keyword_check' => [],
    'workerConfig' => [
        'tcp://127.0.0.1:2015'
    ]
]);
$crawler->prepareCrawl();
$crawler->executeCrawl();
//$crawler->parseAccount();