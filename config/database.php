<?php
/**
 * 根据环境加载配置
 * @author lxw
 * @since 2016-07-11 14:52:00
 */
switch (env('APP_ENV', 'local')) {
    case 'local' :
        $database = [
            // 数据库配置
            'connections' => [
                'kmdbcenter' => [
                    'driver' => 'mysql',
                    'host' => '127.0.0.1',
                    'port' => 3306,
                    'database' => 'test',
                    'username' => 'root',
                    'password' => '',
                    'prefix' => 'km_tbl_',
                ],
            ],
            // 缓存配置
            'redis' => [
                'cluster' => false,
                'default' => [
                    'host'     => '127.0.0.1',
                    'port'     => 6379,
                    'database' => 0,
                    'password' => null
                ]

            ],
            'default' => 'kmdbcenter',
            'fetch' => PDO::FETCH_ASSOC,
            'migrations' => 'migrations',
        ];
        break;
    // 测试环境
    case 'tests' :
        $database = [
            'connections' => []
        ];
        break;
    // 生产环境
    case 'production' :
        $database = [
            'connections' => []
        ];
        break;
    default :
        $database = [
            'connections' => []
        ];
        break;

}

foreach ($database['connections'] as $key => $val) {
    $database['connections'][$key] = array_merge($val, [
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        //'prefix'    => env('DB_PREFIX', 'km_tbl_'),
        'timezone' => env('DB_TIMEZONE', '+08:00'),
        'strict' => false,
    ]);
}

return $database;
