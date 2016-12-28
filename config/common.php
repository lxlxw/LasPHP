<?php
/**
 * system params
 * @author lxw
 * @since 2016-07-11 14:52:00
 */

switch (env('APP_ENV', 'local')) {
    case 'local' :
        $common = array(
            //sign open & close
        	'issign' => true,
            //log根目录存放路径
            'log_file_dir' => '/home/www/log/',
        );

        break;
    case 'tests' :
        $common = array(
            //sign open & close
            'issign' => true,
            //log根目录存放路径
            'log_file_dir' => '/home/www/log/',
        );
        break;
    case 'gray':
        $common = array(
            //sign open & close
            'issign' => true,
            //log根目录存放路径
            'log_file_dir' => '/home/www/log/',
        );

        break;
    case 'production' :
        $common = array(
            //sign open & close
            'issign' => true,
            //log根目录存放路径
            'log_file_dir' => '/home/www/log/',
        );
          break;
    default :
        $common = array(
            //sign open & close
            'issign' => true,
            //log根目录存放路径
            'log_file_dir' => '/home/www/log/',
        );

        break;
}

return $common;
