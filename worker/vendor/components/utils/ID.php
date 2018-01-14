<?php
/**
 * 生成工具类.
 *
 * @author zhangjiaao <zhangjiaao@wepiao.com>
 */

namespace Components\Utils;

/**
 * Class Id.
 */
class ID
{

    /**
     * 生成Uuid.
     *
     * @param string $param 额外参数.
     *
     * @return string
     */
    public static function generateUuid($param = array())
    {
        $extend = "";
        if (is_array($param) && $param) {
            foreach ($param as $value) {
                $extend .= $value;
            }
        } elseif (is_string($param)) {
            $extend = $param;
        }
        $uniqueId = uniqid(rand(), true);
        return  md5($uniqueId . $extend);
    }


     public  static  function  bindToken($data = array(), $offset=''){

         return md5(implode(',', $data) . $offset);

     }
    /**
     * 检查18位身份证号码正确性.
     *
     * @param string $IDCard 身份证号码.
     *
     * @return boolean
     */
    public static function check18IDCard($IDCard)
    {
        if (strlen($IDCard) != 18) {
            return false;
        }
        $calcIDCardCode = function ($IDCardBody) {
            if (strlen($IDCardBody) != 17) {
                return false;
            }

            // 加权因子
            $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            // 校验码对应值
            $code = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            $checksum = 0;

            for ($i = 0; $i < strlen($IDCardBody); $i++) {
                $checksum += substr($IDCardBody, $i, 1) * $factor[$i];
            }

            return $code[$checksum % 11];
        };
        // 身份证主体
        $IDCardBody = substr($IDCard, 0, 17);
        // 身份证最后一位的验证码
        $IDCardCode = strtoupper(substr($IDCard, 17, 1));

        if ($calcIDCardCode($IDCardBody) != $IDCardCode) {
            return false;
        } else {
            return true;
        }
        return false;
    }

    /**
     * 生成唯一uuid. 
     *
     * @param string $prefix 前缀标签.
     *
     * @return uuid string.
     */
    public static function uuid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-';
        $uuid .= substr($chars, 8, 4) . '-';
        $uuid .= substr($chars, 12, 4) . '-';
        $uuid .= substr($chars, 16, 4) . '-';
        $uuid .= substr($chars, 20, 12);

        return $prefix . $uuid;
    }

}
