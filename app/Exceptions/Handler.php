<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler {

    /**
     * 服务容器
     * @var Application
     */
    protected $pApp;

    /**
     * 忽略异常类型列表
     * @var array
     */
    protected $dontReport = [

    ];

    /**
     * 非错误异常类型列表
     * @var array
     */
    protected $dontError = [
        HttpException::class,
        ValidationException::class,
    ];

    /**
     * 注入服务容器
     * @param Application $pApp
     */
    public function __construct(Container $pApp)
    {
        $this->pApp = $pApp;
    }

    /**
     * 记录异常信息
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Laravel\Lumen\Exceptions\Handler::report()
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }
        if ($this->shouldntError($e)) {
            $response = $this->pApp->response($e->getCode(), $e->getMessage());
            $this->pApp->log('response: ' . $response->getContent());
        } else {
            $this->pApp->log((string) $e);
        }
        
        $this->pApp->log('runtime:  ' . microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);
        if ($this->shouldntError($e)) {
            $this->pApp->make('log')->info($this->pApp->getLogs(true));
        } else {
            $this->pApp->make('log')->error($this->pApp->getLogs(true));
        }
    }

    /**
     * 输出异常信息
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Laravel\Lumen\Exceptions\Handler::render()
     */
    public function render($request, Exception $e)
    {
        if ($this->shouldntError($e)) {
            $errno = $e->getCode() == 0 ? 99 : $e->getCode();
            return $this->pApp->response($errno, $e->getMessage());
        }
        $message = $e->getMessage();

        if (env('APP_DEBUG', false)) {
            if (preg_match("/^Mozilla|^Opera/i", $request->server('HTTP_USER_AGENT'))) {
                $message .= '<pre>' . $e->getTraceAsString() . '</pre>';
            }else {
                $message .= PHP_EOL . $e->getTraceAsString();
            }
        }
        return response($message);
    }

    /**
     * 判断是否是非错误的异常
     * @param Exception $e
     * @return boolean
     */
    protected function shouldntError(Exception $e)
    {
        foreach ($this->dontError as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        return false;
    }
}
