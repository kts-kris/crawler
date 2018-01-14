<?php
/**
 * Created by PhpStorm.
 * User: liuweigang
 * Date: 14/01/2018
 * Time: 17:58
 */

namespace Models;

class OfficalAccount extends ModelBase {
    /**
     * 表字段
     *
     * @var array
     */
    public static $fields = array(
        'id',
        'wx_id',
        'sogou_id',
        'wx_name',
        'wx_headimg',
        'wx_headimg_url',
        'wx_qrcode',
        'wx_business',
        'wx_desc',
        'wx_message_cowx_message_count',
        'wx_monthly_message_num',
        'wx_message_list_url',
        'update_time',
        'create_time',
        'avail',
    );

    /**
     * Get table name.
     *
     * @return string Table name.
     */
    public function getTableName()
    {
        return 'offical_account';
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
     * 获取公众账号信息
     * @param $condition
     * @return mixed
     */
    public function getOfficalAccount($condition){
        return $this->getReadDb()
            ->select('*')
            ->from($this->getTableName())
            ->where($condition)
            ->queryAll();
    }

    /**
     * 更新公众号信息，如果不存在则自动更新
     * @param $data
     * @return bool|mixed|string
     */
    public function updateOfficalAccountInfo($data){
        if(isset($data['wx_id'])){
            $res = $this->getOfficalAccount(['wx_id'=>$data['wx_id']]);
//            var_dump($res);
            if(!isset($res[0]['sogou_id'])){
                $data['create_time'] = date('Y-m-d H:i:s', time());
                return $this->getWriteDb()->insert($this->getTableName(), $data);
            }
        }

        return $this->getWriteDb()->update($this->getTableName(), $data, ['wx_id' => $data['wx_id']]);
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