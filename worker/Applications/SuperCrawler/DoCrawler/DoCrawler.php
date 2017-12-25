<?php

use Components\Crawler\Snoopy;
use Components\Crawler\simple_html_dom;
class DoCrawler{
    private static $referer;
    private static $agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
    private static $cookie;
    private static $crawler;
    private static $redis;
    protected static $instance;

    protected function __construct(){
        if(!isset(self::$crawler))self::$crawler=new Snoopy();
        if(!isset(self::$redis))self::$redis=new Redis();
    }


    public static function instance() {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 从队列中获取任务
     * @param string $queueName
     * @param int $priority
     */
    public static function fetchQueueTask($queueName='defalut', $priority=0){
        if (!static::$instance) {
            static::$instance = new static();
        }
        //TODO 获取队列内容并解析
        self::$redis->pconnect('127.0.0.1', 6379);
        self::$redis->subscribe([$queueName], function($instance, $channelName, $message){
            print $channelName.'=====>'.$message."\n";
        });
        self::$redis->close();
    }

    public static function setReferer($url){
        slef::$referer = $url;
    }

    public static function setAgent($agent){
        slef::$agent = $agent;
    }

    public static function setCookie($cookie){
        slef::$cookie = $cookie;
    }

    /**
     * 抓取指定的Url
     * @param $url
     */
    public static function gatherUrl($url){
        self::$crawler->fetch($url);

        $result = self::$crawler->results;
        if($result === NULL)return false;
        return $result;
    }

    /**
     * 解析网页内容
     * @param $html
     */
    public static function parseHtml($html){
        $content = self::str_get_html($html);
        return $content;
    }

    /**
     * 结构化翻页
     * @param $content
     */
    public static function parseNextPage($content){

    }

    /**
     * 结构化内容
     * @param $content
     */
    public static function parseNodes($content, $target, $index=null){
        $nodes = $content->find($target, $index);
        return $nodes;
    }

    private static function str_get_html($str, $lowercase=true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT){
        $dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        if (empty($str) || strlen($str) > MAX_FILE_SIZE)
        {
            $dom->clear();
            return false;
        }
        $dom->load($str, $lowercase, $stripRN);
        return $dom;
    }
}