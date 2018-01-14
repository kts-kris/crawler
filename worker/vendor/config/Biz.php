<?php

namespace Config;

class Biz
{
    public $sogouWxUrls = [
        'searchArticleByTitle'  =>  'http://weixin.sogou.com/weixin?type=2&s_from=input&query=%s&ie=utf8&_sug_=n&_sug_type_=&page=%d',
        'searchOaByTitle'       =>  'http://weixin.sogou.com/weixin?type=1&s_from=input&query=%s&ie=utf8&_sug_=n&_sug_type_=',
    ];

}