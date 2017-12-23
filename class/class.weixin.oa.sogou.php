<?php
/**
 * Created by PhpStorm.
 * User: liuweigang
 * Date: 23/12/2017
 * Time: 11:49
 */

require_once(dirname(__FILE__) . '/class.base.php');

class CrawlerWeixinSogou extends CrawlerBase {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 网络IO，执行抓取
     */
    public function doCrawl() {
        if (!isset($this->crawl_config['keywords'])) {
            throw new InvalidArgumentException("keywords required for weixin sogou");
        }

        if (empty($this->crawl_config['keywords'])) {
            throw new InvalidArgumentException("keywords cannot be empty for weixin sogou");
        }
        $kw = $this->crawl_config['keywords'];
        $colorKw = $this->color->getColoredString($kw, 'red', 'yellow');
        $this->log('开始检索关键字' . $colorKw);

        $this->snoopy->agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";

        $crawl_url = 'http://weixin.sogou.com/weixin?type=1&s_from=input&query=' . urlencode($kw) . '&ie=utf8&_sug_=n&_sug_type_=';
        $this->log("开始请求地址:$crawl_url");

        while($crawl_url !== NULL){
            $this->snoopy->fetch($crawl_url);

            if ($this->snoopy->results === null) {
                $this->log($this->color->getColoredString("本次请求没有取得任何返回信息，请求URL:{$crawl_url}", 'red'));
                return false;
            }

            $weibo_result = $this->snoopy->results;
            if ($this->crawl_config['debug']) {
                file_put_contents(dirname(__FILE__).'/html.log', $weibo_result);
            }
//				$this->log("请求返回结果:$weibo_result");
            $this->log("请求返回结果文本长度: " . strlen($weibo_result));

            $content = str_get_html($weibo_result);

            //$scriptNodes = $content->find('script', 11);
            //$anti_box = $scriptNodes->innertext();
            preg_match('/account_anti_url = "([\w\W]*?)"/', $weibo_result, $antiArray);
            //var_dump($antiArray);exit;
            if(!empty($antiArray[1])){
                $anti_url = 'http://weixin.sogou.com' . $antiArray[1];
                $this->snoopy->referer = $crawl_url;
                $this->snoopy->fetch($anti_url);
                if($this->snoopy->results === null){
                    $this->log('请求Anti失败'. $anti_url);
                }else{
                    $this->log('请求Anti成功'. $anti_url);
                }
            }

            //先看看内容的结果数
            $page_nodes = $content->find('.p-fy', 0);
            if($page_nodes){
                $totleNum_box = $page_nodes->find('.mun', 0);
                $totleStr = $totleNum_box->innertext();
                preg_match('/resultbarnum:(\d*?)-/', $totleStr, $totleNumArray);
                $totleNum = !empty($totleNumArray[1]) ? $totleNumArray[1] : 1;
                $colorTotleNum = $this->color->getColoredString($totleNum, 'green', 'black');
                $this->log('关键字 '. $colorKw.' 共搜索到'. $totleNum .'条结果');
                $nextPageUrl = $this->parseNextPageUrl($page_nodes);
                if($nextPageUrl !== NULL){
                    $this->snoopy->referer = $crawl_url;
                    $crawl_url = $nextPageUrl;
                    $this->log('已发现下一页:'. $nextPageUrl);
                }else{
                    $crawl_url = NULL;
                }
            }

            $this->parseList($content);
            $colorCountNum = $this->color->getColoredString(count($this->crawl_messages),'yellow');
            $this->log('已完成抓取结果数:'.$colorCountNum.'条, 共计'.$totleNum.'条（未登录状态下只可获取100条）');
            usleep(rand(1000000,3000000));
        }
    }


    /**
     * 找到下一页的URL
     * @param $content
     * @return string
     */
    public function parseNextPageUrl($content){
        $nextPage_box = $content->find('.np', 0);
        $url = NULL;
        if($nextPage_box){
            $url = 'http://weixin.sogou.com/weixin' . htmlspecialchars_decode($nextPage_box->getAttribute('href'));
        }
        return $url;
    }

    /**
     * 匹配列表
     * @param $content
     */
    public function parseList($content){

        $nodes = $content->find('.news-list2 li');
//        var_dump($nodes);
//        file_put_contents(dirname(__FILE__).'/html.log', $weibo_result);
//        exit;
        foreach ($nodes as $node) {
            $obj = [];

            $img_box = $node->find('.img-box', 0);
            $txt_box = $node->find('.txt-box', 0);
            $desc_box = $node->find('dd', 0);
            $auth_box = $node->find('dd', 1);
            $lastMessage_box = $node->find('dd', 2);

            if ($img_box) {
                $img = $img_box->find('img', 0);
                if ($img) {
                    $obj['pics'] = [];
                    $obj['pics'][] = htmlspecialchars_decode($img->getAttribute('src'));
                }
            }

            $title = $txt_box->find('a', 0);
            if ($title) {
                $obj['title'] = strip_tags($title->innertext());
                $obj['MessageListLink'] = htmlspecialchars_decode($title->getAttribute('href'));
            }

            $info = $txt_box->find('.info', 0);
            if ($info) {
                $weixinAccount = $info->find('label', 0);
//                var_dump($weixinAccount);exit;
                $obj['weixinAccount'] = strip_tags($weixinAccount->innertext());
            }

            $qrcode = $txt_box->find('.ew-pop', 0);
            if ($qrcode) {
                $qrcodeImg = $qrcode->find('img', 0);
                $obj['created_at'] = htmlspecialchars_decode($qrcodeImg->getAttribute('src'));
            }

            if($desc_box){
                $obj['description'] = $desc_box->innertext();
            }

            if($auth_box){
                $obj['authInfo'] = $auth_box->innertext();
            }

            if($lastMessage_box){
                $titleTag = $lastMessage_box->find('a', 0);
                $obj['lastMessages']['title'] = $titleTag->innertext();
                $obj['lastMessages']['link'] = htmlspecialchars_decode($titleTag->getAttribute('href'));
            }

            $this->crawl_messages[] = $obj;
        }
    }

    public function doKeywordCheck() {
        if (isset($this->crawl_config['keyword_check'])) {
            $keyword_filter = $this->crawl_config['keyword_check'];
            if (!empty($keyword_filter)) {
                foreach ($this->crawl_messages as $ssk => $ssc) {
                    foreach ($keyword_filter as $kf) {
                        if (mb_strpos($ssc['title'], $kf) !== false) {
                            $this->log('正文筛出关键字匹配成功，删除数据:' . print_r($ssc, true));
                            unset($this->crawl_messages[$ssk]);
                        }
                    }
                }
            }
        }
    }

    public function doPublicTimeCheck() {
        if (isset($this->crawl_config['public_time_check'])) {
            foreach ($this->crawl_messages as $ssk => $ssc) {
                if ($ssc['created_at_time'] < $this->crawl_config['public_time_check']) {
                    $this->log('不满足发布时间限制，删除数据:' . print_r($ssc, true));
                    unset($this->crawl_messages[$ssk]);
                }
            }
        }
    }

    public function parseAccount(){
        /**
         * 匹配头 <li id="sogou_vr_11002301_box_
         *
         * 匹配尾巴 </li>
         */
        $accountDomArray = array();
        preg_match_all('((?<=<li id="sogou_vr_11002301_box_)[\w\W]*?(?=</li))', $this->crawl_messages, $accountDomArray);
        print_r($accountDomArray);
    }

    /*
    public function doImageCheck() {
        //微信搜索数据不检测图片
    }

    public function doVideoCheck() {
        //微信搜索数据不检测视频
    }
    */

    public function doMessage() {
//		$this->log('抓取结果:' . print_r($this->crawl_messages, true));
        $this->save('offical_account', $this->crawl_messages);
        $colorNum = $this->color->getColoredString(count($this->crawl_messages),'red', 'green');
        $this->log('抓取结果:' . $colorNum);
    }
}