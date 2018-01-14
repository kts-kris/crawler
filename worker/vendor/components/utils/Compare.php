<?php
/**
 * 转换工具类.
 *
 * @author zhangjiaao <zhangjiaao@wepiao.com>
 */

namespace Components\Utils;

/**
 * Class Id.
 */
class Compare
{
    /**
     * 将rpc字段进行转换(一维转换成驼峰格式).
     *
     * @param array $row 数据单行.
     *
     * @return array
     */
    public static function toFormatHump(&$row)
    {
        if(is_array($row) && $row) {
            $newRow = array();
            foreach ($row as $key => $value) {
                $newKey = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
                $newKey = lcfirst($newKey);
                $newRow[$newKey] = $value;
            }
            $row = $newRow;
        }
    }

    /**
     * 将rpc字段进行转换(二维转换成驼峰格式).
     *
     * @param array $rows 数据多行.
     *
     * @return array
     */
    public static function toBatchFormatHump(&$rows)
    {
        if(is_array($rows) && $rows) {
            foreach ($rows as  &$row) {
                self::toFormatHump($row);
            }
        }
    }

}