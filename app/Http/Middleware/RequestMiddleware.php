<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Container\Container;
use Illuminate\View\View;
use Illuminate\Http\Response;

class RequestMiddleware{

    /**
     * 服务容器
     * @var Application
     */
    protected $pApp;
    
    /**
     * 注入服务容器
     * @param Container $pApp
     * @author lxw
     * @since 2016年07月11日 
     */
    public function __construct(Container $pApp) {
        $this->pApp = $pApp;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->requestInfo($request);
        $response = $next($request);
        $this->responseInfo($request, $response);
        $this->pApp->make('log')->info($this->pApp->getLogs(true));
        return $response;
    }
    
    /**
     * 记录请求信息
     * @param object $request
     */
    protected function requestInfo($request)
    {
        $this->pApp->log($request->url());
        $this->pApp->log('serializeid: ' . $this->pApp->getSerializeId());
        $this->pApp->log('query: ' . json_encode($request->query->all(), JSON_UNESCAPED_UNICODE));
        $this->pApp->log('request: ' . json_encode($request->request->all(), JSON_UNESCAPED_UNICODE));
        $this->pApp->log('content: ' . $request->getContent());
    }

    /**
     * 记录响应信息
     * @param object $request
     * @param object $response
     */
    protected function responseInfo($request, $response)
    {
        $log = 'response: ' . $response->getContent();
        if ($response instanceof Response) {
            if ($response->getOriginalContent() instanceof View) {
                $view = [
                    'path' => $response->getOriginalContent()->getPath(),
                    'data' => $response->getOriginalContent()->getData()
                ];
                $log = 'view: ' . json_encode($view, JSON_UNESCAPED_UNICODE);
            }
        }
        $this->pApp->log($log);
        $this->pApp->log('runtime: ' . microtime(true) - $request->server('REQUEST_TIME_FLOAT'));
    }
}
