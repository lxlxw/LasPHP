<?php

namespace App\Http\Core;

class Service {
    
    /**
     * 错误信息
     * @var integer
     */
    public $arrError = ['errno' => '0' , 'errmsg' => 'success'];

    /**
     * json转array
     * @param $json json格式内容
     * @return array 解析后的数组
     */
    public function jsonToArray($json)
    {
        $arr=array();

        if(!is_object($json)){
            $json = json_decode($json,true);
        }
        if ( $json ) {
            foreach ($json as $key => $val) {
                if (is_object($val)) $arr[$key] = self::jsonToArray($val); //判断类型是不是object
                else $arr[$key] = $val;
            }
        }
        return $arr;
    }
    
    /**
     * 结果返回, 可用于对不同端接口做兼容处理
     * @param $p_result
     * @return array
     * 统一返回字段:
     * {
     *     'errno' : '错误号',
     *     'error' :  '错误信息'
     * }
     */
    public function response($p_result)
    {
        return $p_result;
    } 

}

