<?php
use Components\Crawler\Snoopy;
use Components\Crawler\simple_html_dom;
class AccountCrawler{
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
        $accountListArray = \Models\AccountInfo::model()->getAllAccounts(['avail' => 1, 'worker_id' => 0, 'update_time <>' => $runTimeStr], 1);
        $biz = (array) new \Config\Biz;
//        var_dump($biz);
        //return false;
        //$accountListArray = [['title_cn'  =>  '药智网', 'id'=>1, 'wx_id' => 'yaozh008']];
//        $accountInfoArray = [];
        foreach($accountListArray as $key => $accountArray){
            $url = sprintf($biz['sogouWxUrls']['searchOaByTitle'], urlencode($accountArray['title_cn']));
            print $url . "\n";
            if(empty($url))return false;
            if(!empty($agent))self::setAgent($agent);
            if(!empty($cookie))self::setCookie($cookie);
            if(!empty($referer))self::setReferer($referer);

            self::$crawler->agent = self::$agent;
            self::$crawler->cookies = self::$cookie;
            self::$crawler->referer = self::$referer;


            \Models\AccountInfo::model()->updateWorderId($accountArray['id'], $worker_id);
            $content = self::gatherUrl($url);
            file_put_contents('/tmp/'.$accountArray['wx_id'].'.html', $content);
            print $accountArray['wx_id'] . ':' . strlen($content) . "\n";
            if($content === false){
                \Models\AccountInfo::model()->updateAccountInfo(['id' => $accountArray['id']], ['update_time' => $runTimeStr]);
                \Models\AccountInfo::model()->updateWorderId($accountArray['id'], 0);
                return false;
            }

            preg_match('/account_anti_url = "([\w\W]*?)"/', $content, $antiArray);
            //var_dump($antiArray);exit;
            if(!empty($antiArray[1])){
                $anti_url = 'http://weixin.sogou.com' . $antiArray[1];
                self::$crawler->referer = $url;
                $antiRes = self::gatherUrl($anti_url);
                if($antiRes === null){
                    $antiInfoArray = [];
                }else{
                    $antiInfoArray = json_decode($antiRes, true);
//                    var_dump($antiInfoArray);
                }
            }


            $html = self::parseHtml($content);

            //抓取被封检测
            $authBox = $html->find('[name=authform]', 0);
            if($authBox){
                print '疑似访问被封' . "\n";
                \Models\AccountInfo::model()->updateAccountInfo(['id' => $accountArray['id']], ['update_time' => $runTimeStr]);
                \Models\AccountInfo::model()->updateWorderId($accountArray['id'], 0);
                return false;
            }

            $nodes = $html->find('.news-list2 li');
            \Models\AccountInfo::model()->updateAccountInfo(['id' => $accountArray['id']], ['update_time' => $runTimeStr]);

            foreach($nodes as $node){
                $imgBox = $node->find('.img-box', 0);
                $txtBox = $node->find('.txt-box', 0);
                $qrBox = $node->find('.ew-pop', 0);
                $descBox = $node->find('dl', 0);
                $businessBox = $node->find('dl', 1);
                $lastMessageBox = $node->find('dl', 2);
                $accountInfoArray = [];
                $sogouId = $node->getAttribute('d');

                if($txtBox){
                    $wxIdBox = $txtBox->find('[name=em_weixinhao]', 0);
                    $wxId = $wxIdBox->innertext();
                    print 'html:'.$wxId.', db:'.$accountArray['wx_id']."\n";
                    if($wxId != $accountArray['wx_id']){
                        \Models\AccountInfo::model()->updateAccountInfo(['id' => $accountArray['id']], ['update_time' => $runTimeStr]);
                        \Models\AccountInfo::model()->updateAccountInfo(['id' => $accountArray['id']], ['wx_id' => $wxId]);
                        \Models\AccountInfo::model()->updateWorderId($accountArray['id'], 0);
                        continue;
                    }
                    $accountInfoArray[$wxId]['wx_id'] = $wxId;
                    $accountInfoArray[$wxId]['sogou_id'] = $sogouId;
                    $articleCountInfo = isset($antiInfoArray['msg'][$sogouId]) ? $antiInfoArray['msg'][$sogouId] : '0,0';
                    $articleCount = explode(',', $articleCountInfo);
                    $accountInfoArray[$wxId]['wx_monthly_message_num'] = $articleCount[0];
                    $accountInfoArray[$wxId]['wx_message_count'] = $articleCount[1];
                    $wxTitleBox = $txtBox->find('.tit', 0);
                    if($wxTitleBox){
                        $wxTitle = $wxTitleBox->find('em', 0);
                        $wxTitle = $wxTitle->innertext();
                        $wxMlBox = $wxTitleBox->find('a', 0);
                        $wxMlUrl = htmlspecialchars_decode($wxMlBox->getAttribute('href'));
                        $accountInfoArray[$wxId]['wx_message_list_url'] = $wxMlUrl;
                    }
                    $accountInfoArray[$wxId]['wx_name'] = strip_tags($wxTitle);

                }else{
                    print '疑似访问被封' . "\n";
                    \Models\AccountInfo::model()->updateAccountInfo(['id' => $accountArray['id']], ['update_time' => $runTimeStr]);
                    \Models\AccountInfo::model()->updateWorderId($accountArray['id'], 0);
                    return false;
                }

                if($qrBox){
                    $popBox = $qrBox->find('.pop', 0);
                    $qrImg = $popBox->find('img', 0);
                    $accountInfoArray[$wxId]['wx_qrcode'] = htmlspecialchars_decode($qrImg->getAttribute('src'));
                }

                if($imgBox){
                    $img = $imgBox->find('img', 0);
                    if ($img) {
                        $accountInfoArray[$wxId]['wx_headimg_url'] = htmlspecialchars_decode($img->getAttribute('src'));
                        $accountInfoArray[$wxId]['wx_headimg'] = base64_encode(file_get_contents($accountInfoArray[$wxId]['wx_headimg_url']));
                    }
                }

                if($descBox){
                    $desc = $descBox->find('dd', 0)->innertext();
                    $accountInfoArray[$wxId]['wx_desc'] = strip_tags($desc);
                }

                if($businessBox){
                    $business = $businessBox->find('dd', 0)->innertext();
                    $accountInfoArray[$wxId]['wx_business'] = strip_tags($business);
                }

                if($lastMessageBox){
                    $lastMessageTitle = $lastMessageBox->find('a', 0)->innertext();
                    $lastMessageUrl = $lastMessageBox->find('a', 0)->getAttribute('href');
//                    $accountInfoArray[$wxId]['wx_desc'] = $desc;
                }
                print json_encode($accountInfoArray[$wxId]) . "\n\n";
                \Models\OfficalAccount::model()->updateOfficalAccountInfo($accountInfoArray[$wxId]);
                \Models\AccountInfo::model()->updateAccountInfo(['id' => $accountArray['id']], ['update_time' => $runTimeStr]);
                \Models\AccountInfo::model()->updateWorderId($accountArray['id'], 0);
            }
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