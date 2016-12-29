<?php

/**
 * 路由、参数过滤
 * @author lxlxw.me
 * @since 2016年07月11日 
 */
use Symfony\Component\HttpKernel\Exception\HttpException;

//普通传参数模式,如 /User/Test/Test[?appid=&sign=]
$app->match(['GET', 'POST'],
    '/{module}/{controller}/{action}', 
    function ($module,$controller,$action) use ($app) {
        $app->setClassPath($module,$controller,$action);
        $classname = $app->getClassNamespace('Controller');
        if (! class_exists($classname)) {
            throw new HttpException(11, 'protocol does not exist.', null, [], 11);
        }
        if (! method_exists($classname, $action)) {
            throw new HttpException(12, 'protocol method does not exist.', null, [], 12);
        }
        $app->setSubLogsPath($module.'/');
        $app->setParams();
        
        //TODO: add middleware
        //$app->appAuthPermission();
        
        return call_user_func_array(array (new $classname($app), $action), []);
    });
    
