<?php
/**
 * Created by PhpStorm.
 * User: liuweigang
 * Date: 14/01/2018
 * Time: 15:48
 */

namespace Config;

class Db{
    public $read = array(
        'weixin' => array(
            'dsn'      => 'mysql:host=127.0.0.1;port=3306;dbname=weixin',
            'user'     => 'root',
            'password' => '1q2w3e!@#',
            'confirm_link' => true, // required to set to TRUE in daemons.
            'options'  => array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                \PDO::ATTR_TIMEOUT => 3
            )
        )
    );

    public $write = array(
        'weixin' => array(
            'dsn'      => 'mysql:host=127.0.0.1;port=3306;dbname=weixin',
            'user'     => 'root',
            'password' => '1q2w3e!@#',
            'confirm_link' => true, // required to set to TRUE in daemons.
            'options'  => array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                \PDO::ATTR_TIMEOUT => 3
            )
        )
    );
}