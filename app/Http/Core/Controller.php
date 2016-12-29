<?php

namespace App\Http\Core;

use Illuminate\Container\Container;
use Laravel\Lumen\Routing\Controller AS LumenController;
use App\Exceptions\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Library\Facades\RequestAnalysis;

class Controller extends LumenController {
    
    /**
     * 服务容器
     * @var Application
     */
    protected $pApp;
    
    /**
     * 注入服务容器
     * @param Container $pApp
     */
	public function __construct(Container $pApp)
	{
	    $this->pApp = $pApp;
	    $this->inputValidate($this->pApp->getParams());
	}
	
	/**
	 * 处理输入参数
	 * @param array @requestparam
	 */
	public function inputValidate($requestParam)
	{
	    $class = $this->pApp->getClassNamespace('Validation');
        $arrPath = $this->pApp->getClassPath();
        if (! class_exists($class)) {
            throw new HttpException(15, 'validation class['.$arrPath['controller'].'Validation] does not exist.', null, [], 15);
        }
        if (! method_exists($class,$arrPath['action']) ) {
            throw new HttpException(16, 'validation method['.$arrPath['action'].'] does not exist.', null, [], 16);
        }
	    $validation = call_user_func_array(array($class, $arrPath['action']), array ());
	    $this->inputRules($requestParam, $validation['rules']);
	    $this->inputFilters($requestParam, $validation['filters']);
	}
	
	/**
	 * 输入参数格式验证
	 * @param object $request
	 * @param array $rules
	 */
	public function inputRules($request, $rules)
	{
	    $validator = $this->pApp->make('validator')->make($request, $rules);

	    if($validator->fails()) {
	        foreach ($validator->failed() as $key => $val) {
                $message = $validator->messages()->first();
                throw new ValidationException($message, 1001);
	        }
	    }
	}
	
	/**
	 * 输入参数过滤
	 * @param object $request
	 * @param array $filters
	 */
	public function inputFilters($request, $filters)
	{
	    foreach ($filters as $key => $filter) {
	        $para = $request[$key];
	        if ($para !== null) {
	            foreach ($filter as $func) {
	                //$request->merge(array($key => $func($para)));
                    $arrFun = explode(":",$func);//支持最多2个参数模式
                    switch(count($arrFun)){
                        case 2:{
                            $keyValue = $arrFun[0]($para,$arrFun[1]);
                            break;
                        }
                        case 3:{
                            $keyValue = $arrFun[0]($para,$arrFun[1],$arrFun[3]);
                            break;
                        }
                        default:{
                            $keyValue = $arrFun[0]($para);
                            break;
                        }
                    }
                    $arrMessage = array($key => $keyValue);
                    RequestAnalysis::setRequestMessage($arrMessage);
	            }
	        }
	    }
	}
    
    /**
     * 返回信息
     * @param string $errno
     * @param string $error
     * @param array $result
     * @return array
     */
	public function response($errno, $error, $result = [])
	{
		return $this->pApp->response($errno, $error, $result);
	}

}
