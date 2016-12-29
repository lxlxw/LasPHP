<?php

namespace App\Http\Core;

use Laravel\Lumen\Application as LumenApplication;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Library\Facades\RequestAnalysis;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use App\Http\Utils\Utils;

class Application extends LumenApplication {

    /**
     * 请求标识  
     * @var string
     */
    protected $serializeId;

    /**
     * 请求信息
     * @var array
     */
    protected $logs = [];
    
    /**
     * 请求参数
     * @var array
     */
    protected $arrParam = [];
    
    /**
     * 请求appid和name
     * @var array
     */
    protected $arrAppParam = [
        'appid'    => '',
        'appname'  => ''
    ];
    
    /**
     * 路由地址
     * @var array
     */
    protected $classPath = [
        'module'    => '',
        'controller'=> '',
        'action'=>''
    ];

    /**
     * log存放子路径
     * @var string
     */
    protected $subLogsPath = 'common/';

    /**
     * 注入应用信息
     * @param string $basePath 项目基础路径
     * @param string $prefix 请求标识前缀
     * @return mixed
     */
    public function __construct($basePath = null, $prefix = null)
    {
        if (! $this->runningInConsole()) {
            $this->serializeId = $this->createSerializeId($prefix);
        }
        parent::__construct($basePath);
    }
    
    /**
     * 获取请求标识
     * @return $serializeId 请求标识
     */
    public function getSerializeId()
    {
        return $this->serializeId;
    }
    
    /**
     * 创建唯一标识
     * @param string $prefix 请求标识前缀
     * @return string 请求标识
     */
    public function createSerializeId($prefix = null)
    {
        $uuid = $_SERVER['REMOTE_ADDR'];
        $uuid.= $_SERVER['REMOTE_PORT'];
        if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
            $uuid.= $_SERVER['HTTP_USER_AGENT'];
        }
        $uuid.= $_SERVER['REQUEST_TIME_FLOAT'];
        return hash('ripemd128', uniqid('', true) . md5($prefix . $uuid));
    }

    /**
     * 获取请求日志信息
     * @param boolean $isStr
     * @return array
     */
    public function getLogs($isStr = false)
    {
        $logs = '';
        foreach ($this->logs as $log) {
            $logs .= $log . PHP_EOL;
        }
        return $isStr ? $logs : $this->logs;
    }

    /**
     * 添加请求日志信息
     * @param string $log
     */
    public function log($log)
    {
        $this->logs[] = $log;
    }

    /**
     * 清除请求日志信息
     */
    public function clearLogs()
    {
        $this->logs = [];
    }
    
    /**
     * 增加sql log
     */
    public function enableSqlLog()
    {
        if(Config::get('common.database_log_enable', false) === true)
        {
            Event::listen('illuminate.query', function($query, $bindings, $time, $name)
            {
                $data = compact('query','bindings', 'time', 'name');
                $this->log('sqllog: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
                $this->make('log')->info($this->getLogs(true));
                $this->clearLogs();
            });
        }
    }

    /**
	 * 扩展匹配多个路由方法
	 * @param array $methods 路由匹配方法数组
     * @param string $uri 路由匹配规则
     * @param mixed $action 回调方法
	 */
	public function match($methods, $uri, $action)
	{
	    foreach ($methods as $method) {
	        $this->addRoute($method, $uri, $action);
	    }
	}
	
    /**
     * 格式化json返回值
     * @param number $errno 错误码
     * @param string $error 错误信息
     * @param array $result 结果集
     * @param string $format 返回格式
     * @return \Symfony\Component\HttpFoundation\Response 返回对象
     */
    public function response($errno = '0', $error = 'success', $result = [], $format = 'json')
    {
    	if(!empty($result)){
            $result = ['result' => Utils::valueToString($result)];
    	}
        $arrFeedbackResult = ['errno' => ''.$errno, 'errmsg' => ''.$error];
        $arrJson = is_array($result) ? array_merge($arrFeedbackResult, $result) : $arrFeedbackResult;
        switch ($format) {
            case 'json' :
                return response()->json(
                    $arrJson,
                    200,
                    ['Content-Type' => 'text/json; charset=utf-8'],
                    JSON_UNESCAPED_UNICODE
                );
            default :
                return $arrFeedbackResult;
        }
    }
    
    /**
     * 获取请求参数
     * @param string $param
	 * @param array $arrParam
	 * @return mixed
     */
    public function getParams($param = '')
    {
        if(!empty($param)){
            $value = isset($this->arrParam[$param]) ? $this->arrParam[$param] : '';
            return $value;
        }
        return $this->arrParam;
    }
    
    /**
     * 设置请求参数
     */
    public function setParams()
    {
        $this->arrParam = $this->request->all();
    }    
     
    /**
     * 验证app签名和对应appid的权限
     * @return mixed
     */
    public function appAuthPermission()
    {
        if(true === Config::get('common.issign')){
            $arrPath = $this->getClassPath();
            $this->analysisRequestParam($this->arrParam, 'xssssss');
            throw new HttpException(14, 'Check request sign is not valid', null, [], 14);
        }
    }
    
    /**
     * 将json格式字符串的请求报文解析为数组返回
     * @param array $p_arrParam 请求的参数
     * @param string $p_strAppKey 私钥
     * @return mixed
     */
    public function analysisRequestParam($p_arrParam, $p_strAppKey)
    {
        return RequestAnalysis::analysisRequestParam($p_arrParam, $p_strAppKey);   
    }

	/**
	 * 获取模块类的全路径
	 * @param string $classType 协议类型inner/outer
	 * @return string
	 */
	public function getClassNamespace($classType)
	{
        $classPath = $this->getClassPath();
        $module = $classPath['module'];
        $controller = $classPath['controller'];
	    return'\App\Http\Modules\\'.$module.'\\'.$classType.'s\\'.$controller.$classType;
	}

    public function setSubLogsPath($p_subLogsPath)
    {
        $this->subLogsPath = $p_subLogsPath;
    }

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	protected function registerLogBindings()
	{
	    $this->singleton('Psr\Log\LoggerInterface', function () {
	        return new Logger('', [$this->getMonologHandler()]);
	    });
	}
	
    /**
     * 设置日志基础路径
     * @return void
     */
	protected function getMonologHandler()
	{
	    $logsPath = $this->make('config')->get('common.log_file_dir');
	    return (new StreamHandler(
	        $logsPath.'/'.$this->subLogsPath.date('Y-m-d').'.log'))->setFormatter(new LineFormatter(null, null, true, true));
	}
	
	/**
	 * @return $arrAppParam
	 */
	public function getAppParam()
	{
	    return $this->arrAppParam;
	}
	
	/**
	 * @param !CodeTemplates.settercomment.paramtagcontent!
	 */
	public function setAppParam($appid, $appname)
	{
	    $this->arrAppParam = [
	        'appid'        => $appid,
	        'appname'      => $appname,
	    ];
	}
	
	/**
	 * @return $classPath
	 */
	public function getClassPath()
	{
	    return $this->classPath;
	}
	
	/**
	 * @param !CodeTemplates.settercomment.paramtagcontent!
	 */
	public function setClassPath($module, $controller, $action)
	{
	    $this->classPath = [
            'module'    => $module,
            'controller'=> $controller,
            'action' => $action,
        ];
	} 

}
