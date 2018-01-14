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
        if(!isset(self::$redis)){
            self::$redis=new Redis();
            self::$redis->connect('127.0.0.1', 6379);
        }
    }

    protected function __destruct()
    {
        //self::$redis->close();
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
    public static function fetchQueueTask($queueName='default', $priority=0){
        if (!static::$instance) {
            static::$instance = new static();
        }
        //TODO 获取队列内容并解析
        $accountListArray = \Models\AccountInfo::model()->getAllAccounts(['availe' => 1, 'worker_id' => '']);
        $biz = (array) new \Config\Biz;
        var_dump($accountListArray);
        return false;
        //foreach($accountListArray)


        if(empty($url))return false;
        if(!empty($agent))self::setAgent($agent);
        if(!empty($cookie))self::setCookie($cookie);
        if(!empty($referer))self::setReferer($referer);

        self::$crawler->agent = self::$agent;
        self::$crawler->cookies = self::$cookie;
        self::$crawler->referer = self::$referer;

        $content = self::gatherUrl($url);
        if($content === false)return false;
        $html = self::parseHtml($content);
        $nextPage = self::parseNextPage($html);
        $nodes = self::parseNodes($html, 'a');

//        return ['html'=>$html, '']
        var_dump($nextPage);
    }

    public static function setReferer($url){
        self::$referer = $url;
    }

    public static function setAgent($agent){
        self::$agent = $agent;
    }

    public static function setCookie($cookie){
        self::$cookie = $cookie;
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
        $page_nodes = $content->find('.p-fy', 0);
        if($page_nodes){
            $totleNum_box = $page_nodes->find('.mun', 0);
            $totleStr = $totleNum_box->innertext();
            preg_match('/resultbarnum:(\d*?)-/', $totleStr, $totleNumArray);
            $totleNum = !empty($totleNumArray[1]) ? $totleNumArray[1] : 1;

            $nextPageUrl = self::parseNextPageUrl($page_nodes);

        }

        return ['totle'=>$totleNum, 'nextPageUrl'=>$nextPageUrl];
    }

    public static function parseNextPageUrl($content){
        $nextPage_box = $content->find('.np', 0);
        $url = NULL;
        if($nextPage_box){
            $url = 'http://weixin.sogou.com/weixin' . htmlspecialchars_decode($nextPage_box->getAttribute('href'));
        }
        return $url;
    }

    /**
     * 结构化内容
     * @param $content
     */
    public static function parseNodes($content, $target, $index=null){
        $nodes = $content->find($target, $index);
        return $nodes;
    }

    private static function str_get_html($str, $lowercase=true, $forceTagsClosed=true, $target_charset = 'UTF-8', $stripRN=true, $defaultBRText="\r\n", $defaultSpanText=" "){
        $dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        if (empty($str) || strlen($str) > 600000)
        {
            $dom->clear();
            return false;
        }
        $dom->load($str, $lowercase, $stripRN);
        return $dom;
    }
}