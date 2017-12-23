<?php
/**
 * Created by PhpStorm.
 * User: liuweigang
 * Date: 23/12/2017
 * Time: 14:05
 */

use Components\Db\Connection;

class Worker
{
    private static function getWriteDB()
    {
        return Connection::instance()->write('weixin');
    }

    public static function saveByDB($table, $data)
    {
        self::getWriteDB()->insert($table, $data);
        return array('code'=>200, 'message'=>'ok');
    }
}