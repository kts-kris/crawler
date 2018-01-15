<?php
/**
 * Created by PhpStorm.
 * User: liuweigang
 * Date: 14/01/2018
 * Time: 22:47
 */

namespace Models;

class Article extends ModelBase{
    /**
     * 表字段
     *
     * @var array
     */
    public static $fields = array(
        'id',
        'wx_id',
        'title',
        'author',
        'content',
        'publish_time',
        'create_time',
        'update_time',
        'offical_account_id',
        'avail',
    );

    /**
     * Get table name.
     *
     * @return string Table name.
     */
    public function getTableName()
    {
        return 'article';
    }

    /**
     * Get primary key.
     *
     * @return string Current table's primary key.
     */
    public function primaryKey()
    {
        return 'id';
    }


    public function getArticle($condition, $limit = null){
        $sql = $this->getReadDb()->
            select('*')->
            from($this->getTableName())->
            where($condition);
        if(!empty($limit))$sql = $sql->limit($limit);
        return $sql->queryAll();
    }

    public function updateArticle($condition, $data){
        $res = $this->getArticle($condition);
        if(empty($res[0]['wx_id'])){
            $data['create_time'] = date('Y-m-d H:i:s', time());
            $res = $this->getWriteDb()->insert($this->getTableName(), $data);
            print $this->getWriteDb()->getLastSql() . "\n";
            return $res;
        }
        return $this->update($condition, $data);
    }
    /**
     * 更新任务
     * @param array $condition
     * @param $data
     * @return int
     */
    public function updateWorderId($wxId, $workerId){
        return $this->update(['wx_id' => $wxId], ['worker_id' => $workerId]);
    }
}