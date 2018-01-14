<?php
/**
 * Redis的配置文件.
 *
 * @author yongf <yongf@jumei.com>
 */

Namespace Config;

/**
 * Redis的配置文件.
 */
class Redis
{

    // cache
    public $default = array(
        'nodes' => array(
            array('master' => "127.0.0.1:6379", 'slave' => "127.0.0.1:6379"),
        ),
        'auth' => array ('127.0.0.1:6379'=>''),
        'db' => 0
    );

}
