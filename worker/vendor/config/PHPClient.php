<?php
/**
 * Rpc 客户端配置.
 * @author zhangjiaaoz <zhangjiaao@wepiao.com>
 */

namespace config;

class PHPClient
{
    public $Item = array(
        'uri' => array(
//            'tcp://127.0.0.1:12017'
            'tcp://item.wkm.api.wesai.com:80'
        ),
        'user' => 'Test',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
    );
    public $User = array(
        'uri' => array(
            //'tcp://127.0.0.1:12016'
            'tcp://user.wkm.api.wesai.com:80'
        ),
        'user' => 'Test',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
    );
    public $UserProxy = array(
        'uri' => array(
            'tcp://userproxy.wkm.api.wesai.com:80'
        ),
        'user' => 'Test',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
    );
    public $Order = array(
        'uri' => array(
//            'tcp://127.0.0.1:12018'
            'tcp://order.wkm.api.wesai.com:80'
        ),
        'user' => 'Test',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
    );
    public $ShowOrder = array(
        'uri' => array(
            'tcp://10.3.20.48:2018', // showorder | weying php service
            'tcp://10.3.20.49:2018', // showorder | weying php service
        ),
        'user' => 'Api',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537A}',
    );
    public $Com = array(
        'uri' => array(
//            'tcp://127.0.0.1:12019'
            'tcp://com.wkm.api.wesai.com:80'
        ),
        'user' => 'Test',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
    );
    public $Cart = array(
        'uri' => array(
//            'tcp://127.0.0.1:12020'
            'tcp://cart.wkm.api.wesai.com:80'
        ),
        'user' => 'Test',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
    );
    public $Marketing = array(
        'uri' => array(
//            'tcp://127.0.0.1:12021'
            'tcp://marketing.wkm.api.wesai.com:80'
        ),
        'user' => 'Test',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
    );
    public $Admin   = array(
        'uri' => array(
//            'tcp://127.0.0.1:12022'
            'tcp://admin.wkm.api.wesai.com:80'
        ),
        'user' => 'Test',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
    );
    public $Task = array(
        'uri' => array(
            'tcp://127.0.0.1:12026'
        ),
        'user' => 'Test',
        'secret' => '{1BA09530-F9E6-478D-9965-7EB31A59537E}',
    );
    public $Java = array(
        'lang'      => 'java',
        'uri'       => array(
            'http://openapi.api.wesai.com/api/json'
        ),
        'user' => '',
        // app id
        'appid'      => '2c90a45a49a7e03c0149a7e0eb140002',
        // app key
        'secret'    => '679c5257-f14e-4240-a801-698a679d07b5',
    );

}
