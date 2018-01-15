<?php
use Components\Crawler\Snoopy;
use Components\Crawler\simple_html_dom;
class ArticleCrawler{
    private static $referer;
    private static $agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
    private static $cookie;
    private static $crawler;
    private static $redis;
    protected static $instance;

    protected function __construct(){
        if(!isset(self::$crawler))self::$crawler=new Snoopy();
        if(!isset(self::$redis)){
            //self::$redis=new Redis();
            //self::$redis->connect('127.0.0.1', 6379);
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
    public static function fetchQueueTask($worker_id, $queueName='default', $priority=0){
        if (!static::$instance) {
            static::$instance = new static();
        }
        sleep(rand(1,10));
        $runTimeStr = date('YmdH', time());
        //TODO 获取队列内容并解析
        $accountListArray = \Models\OfficalAccount::model()->getOfficalAccount(['avail' => 1, 'worker_id' => 0, 'update_time <>' => $runTimeStr], 1);
//        $biz = (array) new \Config\Biz;
//        var_dump($accountListArray);
        //return false;
//        $accountListArray = [['wx_message_list_url'  =>  'https://mp.weixin.qq.com/profile?src=3&timestamp=1515936879&ver=1&signature=sO4m4OCcy3nJVhPiPlrT-UrkEnTgW8R14M9yeK7OUUCvmPZChs0MWFFDZ21D1zCDaNLYB0hSYuG9vN*izEoYRA==', 'id'=>1, 'wx_id' => 'yaozh008']];
//        $accountInfoArray = [];
        foreach($accountListArray as $key => $accountArray){
            $url = $accountArray['wx_message_list_url'];
//            print $url . "\n";
            if(empty($url))return false;
            if(!empty($agent))self::setAgent($agent);
            if(!empty($cookie))self::setCookie($cookie);
            if(!empty($referer))self::setReferer($referer);

            self::$crawler->agent = self::$agent;
            self::$crawler->cookies = self::$cookie;
            self::$crawler->referer = self::$referer;


            \Models\Article::model()->updateWorderId($accountArray['wx_id'], $worker_id);
            $content = self::gatherUrl($url);
            file_put_contents('/tmp/'.$accountArray['wx_id'].'_msgList.html', $content);
            if($content === false){
                \Models\Article::model()->updateWorderId($accountArray['wx_id'], 0);
                return false;
            }
//            print $accountArray['wx_id'] . ':' . strlen($content) . "\n";

            preg_match('/msgList = ([\w\W]*?)};/', $content, $msgList);
            //var_dump($msgList);
            $msgListArray = [];
            if(!empty($msgList[1])){
                $msgListStr = $msgList[1] . "}";
                $msgListArray = json_decode($msgListStr, true);
//                var_dump($msgListArray);
            }

            if(!isset($msgListArray['list'])){
                print '疑似访问被封' . "\n";
                \Models\Article::model()->updateWorderId($accountArray['wx_id'], 0);
                return false;
            }

//            $html = self::parseHtml($content);
//
//            //抓取被封检测
//            $authBox = $html->find('[name=authform]', 0);
//            if($authBox){
//                print '疑似访问被封' . "\n";
//                \Models\OfficalAccount::model()->updateWorderId($accountArray['wx_id'], 0);
//                return false;
//            }
//
//            $nodes = $html->find('.news-list2 li');

            foreach($msgListArray['list'] as $node){
//                var_dump($node, $msgListArray);
                try{
                    $msgInfoArray = [
                        'wx_id'                     =>  $accountArray['wx_id'],
                        'article_id'                =>  $node['comm_msg_info']['id'],
                        'title'                     =>  $node['app_msg_ext_info']['title'],
                        'author'                    =>  $node['app_msg_ext_info']['author'],
                        'content'                   =>  $node['app_msg_ext_info']['content'],
                        'content_url'               =>  $node['app_msg_ext_info']['content_url'],
                        'copyright_stat'            =>  $node['app_msg_ext_info']['copyright_stat'],
                        'cover'                     =>  $node['app_msg_ext_info']['cover'],
                        'del_flag'                  =>  $node['app_msg_ext_info']['del_flag'],
                        'digest'                    =>  $node['app_msg_ext_info']['digest'],
                        'fileid'                    =>  $node['app_msg_ext_info']['fileid'],
                        'is_multi'                  =>  $node['app_msg_ext_info']['is_multi'],
                        'multi_app_msg_item_list'   =>  json_encode($node['app_msg_ext_info']['multi_app_msg_item_list']),
                        'source_url'                =>  $node['app_msg_ext_info']['source_url'],
                        'subtype'                   =>  $node['app_msg_ext_info']['subtype'],
                        'comm_msg_info'             =>  json_encode($node['comm_msg_info']),
                        'publish_time'              =>  $node['comm_msg_info']['datetime']
                    ];
                }catch (\Exception $e){
                    print json_encode($e) . "\n";
                }

                print $msgInfoArray['title'] ."\n";
                \Models\Article::model()->updateArticle(['wx_id' => $accountArray['wx_id'], 'article_id' => $msgInfoArray['article_id']], $msgInfoArray);
            }

            \Models\OfficalAccount::model()->updateOfficalAccountInfo(['wx_id' => $accountArray['wx_id'], 'update_time' => $runTimeStr]);
            \Models\OfficalAccount::model()->updateWorderId($accountArray['wx_id'], 0);
        }




//        return ['html'=>$html, '']

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
        //self::$crawler->accept = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
        //self::$crawler->fetch($url);
        $aContext = array(
            'http' => array(
                'header'  => 'Content-type: application/x-www-form-urlencoded\r\nAgent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36',
            )
        );

        $cxContext  = stream_context_create($aContext);
        $result = file_get_contents($url, false,$cxContext);

//        $result = self::$crawler->results;
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