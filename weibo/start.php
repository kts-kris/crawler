<?php
/**
 * Created by PhpStorm.
 * User: liuweigang
 * Date: 13/12/2017
 * Time: 11:19
 */

require_once(dirname(__DIR__) . "/class/class.weibo.search.php");
$crawler = new CrawlerWeiboSearch();
$crawler->setConfig([
    'keywords' => ['DOTA2'],
    'debug' => true,
    'keyword_check' => ['魔兽争霸'],
]);
$crawler->prepareCrawl();
$crawler->executeCrawl();