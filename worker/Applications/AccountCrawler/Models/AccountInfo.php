<?php
/**
 * Created by PhpStorm.
 * User: liuweigang
 * Date: 14/01/2018
 * Time: 15:24
 */

namespace Models;

class AccountInfo extends ModelBase{
    /**
     * 表字段
     *
     * @var array
     */
    public static $fields = array(
        'id',
        'wx_id',
        'title_cn',
        'business',
        'business_type',
        'article_count',
        'create_time',
        'update_time',
        'avail'
    );

    /**
     * Get table name.
     *
     * @return string Table name.
     */
    public function getTableName()
    {
        return 'account_info';
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

    /**
     * 获取公众号
     * @param array $condition
     * @return mixed
     */
    public function getAllAccounts($condition=array('avail'=>1), $limit=null){
        $sql = $this->getReadDb()
            ->select('*')
            ->from($this->getTableName())
            ->where($condition);
        if(!empty($limit))$sql = $sql->limit($limit);
        $res = $sql->queryAll();
        print $this->getReadDb()->getLastSql() ."\n";
        return $res;
    }

    public function updateAccountInfo($condition, $data){
        return $this->update($condition, $data);
    }

    /**
     * 更新任务
     * @param array $condition
     * @param $data
     * @return int
     */
    public function updateWorderId($accountId, $workerId){
        return $this->update(['id' => $accountId], ['worker_id' => $workerId]);
    }

}