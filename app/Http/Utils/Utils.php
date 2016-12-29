<?php

namespace App\Http\Utils;

use App\Http\Core\Application;

class Utils
{
    /**
     * 校验对象是否为空
     * @return boolean
     */
    public static function isnull()
    {
        $argsNum = func_num_args();
        for($i=0; $i<$argsNum; $i++){
            $str = trim(func_get_arg($i));
            if(is_string($str)){
                if($str == "" || is_null($str)) return true;
            }elseif(is_array($str)){
                if(empty($str)) return true;
            }
        }
        return false;
    }

    /**
     * 获取 array 字段值, 不存在时返回默认值
     * @param mixed $p_defaultValue 如果找不到对应key的value，则返回默认值
     * @param array $p_array 数组
     * @return mixed
     */
    public static function arrayValue($p_defaultValue, $p_array /*, ...$p_keys*/)
    {
        if (!isset($p_array)) {
            return $p_defaultValue;
        }

        $args = func_get_args();
        $p_keys = array_slice($args, 2);

        $arrKeyValue = $p_array;
        foreach ($p_keys as $key)
        {
            //linyuying
            if(isset($arrKeyValue) && !is_array($arrKeyValue) && !is_bool($arrKeyValue))
            {
                $callTreeString = json_encode(debug_backtrace(),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
                Application::getInstance()->log("\nlinyuying log which code call Utils::arrayValue With stdClass:\n".$callTreeString."\n");
                return $p_defaultValue;
            }
            if (!isset($arrKeyValue[$key]))
            {
                return $p_defaultValue;
            }

            $arrKeyValue = $arrKeyValue[$key];
        }

        return $arrKeyValue;
    }

    /**
     * 递归将 array 中所有的 key 都转换为小写
     * @param $p_array
     * @return array
     */
    public static function arrayKeyToLowercase($p_array)
    {
        $arrTmp =[];
        foreach ($p_array as $key => $value)
        {
            if(is_array($value))
            {
                $arrTmp[mb_strtolower($key)] = ArrayUtils::getLowerCaseKeyArray($value);
            }
            else
            {
                $arrTmp[mb_strtolower($key)]=$value;
            }
        }
        return $arrTmp;
    }

    /**
     * 将数组的数字转化成字符串
     * @param $p_array
     *
     * @return array
     */
    public static function convertArrayNumberValueToString($p_array)
    {
        $arrayOutPut = [];
        foreach($p_array as $k=>$v)
        {
            if(is_array($v))
            {
                $arrayOutPut[$k]=self::convertArrayNumberValueToString($v);
            }
            else if(is_null($v))
            {
                $arrayOutPut[$k]='';
            }
            else if(is_numeric($v))
            {
                $arrayOutPut[$k]=''.$v;
            }
            else
            {
                $arrayOutPut[$k]=$v;
            }
        }

        return $arrayOutPut;
    }
    public static function getFileIdFromUrl($url)
    {
        $arrUrlTmp = explode('=',$url);
        if (count($arrUrlTmp)>1)
        {
            return $arrUrlTmp[1];
        }
        return '';
    }

    /**
     * 根据生日获取年龄
     * @param string $birthday
     * @return string 年龄
     */
    public static function getAgeFromBirthday($birthday)
    {
        $birthdayDateTime = date_create("1995-01-01 00:00:00");
        
        if (!empty($birthday)) {
            // 判断是否等于 0000-00-00 00:00:00
            $birthdayDateTimeTmp = date_create($birthday);
            $zeroDateTime = date_create("0000-00-00 00:00:00");
            $diff = $birthdayDateTimeTmp->diff($zeroDateTime);
            if ($diff->y != 0) {
                $birthdayDateTime = $birthdayDateTimeTmp;
            }
        }

        $curDateTime = date_create();
        $diff = $birthdayDateTime->diff($curDateTime);
        $age = $diff->y;

        return $age;
    }

    /**
     * @param $p_sex int 服务器的性别字段
     *
     * @return int app使用的性别
     */
    public static function convertSexToAppGender($p_sex)
    {
        return $p_sex;
    }
    
    /**
     * 检查邮件地址是否合法
     * @param string $str
     * @return number
     */
    public static function check_email($str)
    {
        return preg_match('/^[\w]{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/', $str);
    }

    /**
     * 异步curl
     * @param $strUrl
     * @param $arrParameters
     * @param $strMethod
     * @param $nTimeout
     */
    public static function AsyncHttpCurl(
        $strUrl,
        array $arrParameters=[],
        $strMethod='',
        $nTimeout=0,
        $callback=null
    ){
        $pGm = app('gearman');
        $pGm->setConnection('default');
        $arrParam = [];
        $arrParam['url'] = $strUrl;
        if (!empty($arrParameters)){
            $arrParam['parameters'] = $arrParameters;
        }
        if ($strMethod!=''){
            $arrParam['method'] = $strMethod;
        }
        if ($nTimeout>0){
            $arrParam['timeout'] = $nTimeout;
        }
        return $pGm->addTask('httpCurl', $arrParam);
    }


    /**
     * 解析时间格式为yyyy-mm-dd hh:ii:ss
     * @param string $p_strDateTime 多个时间用分隔符隔开
     * @param string $format
     * @param string $glue 分隔符
     * @return array|bool|string 按时间正序排列
     */
    public static function parseDateTime($p_strDateTime, $format = 'Y-m-d H:i:s', $glue = ',')
    {
        if (strpos($p_strDateTime, $glue)) {
            $mxRes = explode($glue, $p_strDateTime);
            $mxRes = array_map('strtotime', $mxRes);
            if (in_array(false, $mxRes)) {
                return false;
            } else {
                $mxRes = array_map(function($value)use($format){return date($format, $value);}, $mxRes);
                sort($mxRes, SORT_STRING);
            }
        } else {
            $mxRes = strtotime($p_strDateTime);
            if (FALSE === $mxRes) {
                return false;
            } else {
                $mxRes = date($format, $mxRes);
            }
        }

        return $mxRes;
    }

    /**
     * 获取当前日期， 日、每周、每月的第一天
     * @param string $range
     * @param string $date
     * @param string $format
     * @return array|bool|false|int|string
     */
    public static function getValidDate($range, $date, $format = 'Y-m-d')
    {
        switch ($range) {
            case 'day':
                $date = self::parseDateTime($date, $format);
                break;
            case 'week':
                $time = strtotime($date);
                $i = date('w', $time);
                $i = $i > 0 ? ($i - 1) : 6;
                $date = date($format, strtotime($date . ' -' . $i . 'day'));
                break;
            case 'month':
                $format = str_replace('d', '01', $format);
                $date = self::parseDateTime($date, $format);
                break;
            default:;
        }

        return $date;
    }
    
    /**
     * 值转换为string类型
     * @param array $params 需要处理的数组
     * @return array
     */
    public static function valueToString($params)
    {
        if(is_array($params) && !empty($params)){
            foreach($params as $key=>$value){
                if(is_array($value)){
                    $params[$key] = self::value_tostring($value);
                }else{
                    $params[$key]=(string)($value);
                }
            }
        }
        return $params;
    }
}

