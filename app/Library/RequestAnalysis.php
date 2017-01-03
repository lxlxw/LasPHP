<?php
/**
 * 请求报文数据解析处理
 * @author lxw
 * @since 2016-07-11 14:52:00
 */

namespace App\Library;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Container\Container;
use App\Http\Common\Signauture;

class RequestAnalysis
{

    /**
     * 解析后保存的请求参数
     * @var array
     */
    protected $arrRequestMessage = [];

    /**
     * 服务容器
     * @var Application
     */
    protected $pApp;

    /**
     * 初始化通用配置信息
     * @var array
     */
    protected $configs = [];

    /**
     * 初始化通用配置
     * @param array $configs
     */
    public function __construct(Container $pApp)
    {
        $this->pApp = $pApp;
        $this->configs = $pApp['config']['common'];
    }

    /**
     * 获取解析后的报文
     * @return string 返回报文数组
     */
    public function getRequestMessage()
    {
        return empty($this->arrRequestMessage)?[]:$this->arrRequestMessage;
    }

    public function clearRequestMessage()
    {
        unset($this->arrRequestMessage);
        $this->arrRequestMessage = [];
    }

    /**
     * 添加设置值
     * @param $p_arrMessage
     */
    public function setRequestMessage($p_arrMessage)
    {
        $this->arrRequestMessage = array_merge($this->arrRequestMessage,$p_arrMessage);
    }

    /**
     * 请求报文解析
     * @param $p_arrParam 请求的报文
     * @param $p_strAppKey 私钥
     * @return array 返回解析后报文
     */
    public function analysisRequestParam($param, $appKey)
    {
        $arrRequest = $this->getRequestMessage();
        if ( !empty($arrRequest) ) {
            return $this->getRequestMessage();
        }
        if ( !empty($appKey) ) {
            $this->arrRequestMessage = $param;
            //检测sign是否合法
            $bCheckRequest = $this->checkRequestSign($param, $appKey);
            if ( !$bCheckRequest ) {
                throw new HttpException(14, 'Check request sign is not valid', null, [], 14);
            }
            return $this->arrRequestMessage;
        } else {
            throw new HttpException(19, 'request message format error', null, [], 19);
        }
        return $this->arrRequestMessage;
    }

    /**
     * 检测请求参数签名
     * @param array $arrInput
     * @param string $appKey
     * @return bool
     */
    private function checkRequestSign($input, $appKey)
    {
        $strInputSign = !empty($_SERVER['HTTP_SIGN'])?$_SERVER['HTTP_SIGN']:'';
        if ( empty($strInputSign) ) {
            return false;
        }
        $arrInput['appid'] = 100;
        $sign = new Signauture();
        $strSign = $sign->getAuthSignStr($input, $appKey);
        if ( $strInputSign == $strSign ) {
            return true;
        }
        return false;
    }

}
