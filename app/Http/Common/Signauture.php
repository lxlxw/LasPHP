<?php
/**
 * created by PhpStorm.
 * by: lxw
 * date: 2016/03/09 19:27
 */

namespace App\Http\Common;

final class Signauture {

    /**
     * 默认授权令牌
     * @var String
     */
    private static $token = '';

    public function getAuthSignStr(array $inputParam, $appKey = '') {
        $trans = '';
        $inputParam = array_change_key_case($inputParam, CASE_LOWER);
        if (ksort($inputParam)) {
            foreach ($inputParam as $key => $value) {
                $val = trim($value);
                if ($val === null || $val === '') {
                    continue;
                }
                $trans .= $key . $val;
            }
            $sign = md5($trans . $appKey);
        }
        return strtoupper($sign);
    }
}
