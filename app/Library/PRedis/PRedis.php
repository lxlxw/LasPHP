<?php
namespace App\Library\PRedis;

use Illuminate\Support\Arr;

class PRedis {
    /**
     * 所有配置redis实例
     *
     * @var array
     */
    protected $clients;

    /**
     * 连接超时时间
     * @var int
     */
    protected $timeOut;

    /**
     * 连接配置
     * @var array
     */
    protected $options;

    /**
     * 服务器配置
     * @var array
     */
    protected $connections;

    /**
     * $connections里的key
     * @var array
     */
    protected $name;

    /**
     * 根据配置文件初始化client
     * PRedis constructor.
     * @param array $servers
     * @param int $timeOut
     */
    public function __construct(array $servers = [], $timeOut = 3)
    {
        $cluster = (array)Arr::pull($servers, 'cluster');
        $this->options = (array)Arr::pull($servers, 'options');
        $this->connections = $servers;
        $this->timeOut = $timeOut;
    }

    /**
     * 链接redis
     * @param string $name
     * @return mixed|\Redis
     */
    public function connection($name = 'default')
    {
        $this->name = $name;
        $connection = Arr::get($this->connections, $name ?: 'default');
        $redis = Arr::get($this->clients, $name ?: 'default');
        if(is_null($redis) || !is_object($redis) || !$redis instanceof \Redis) {
            $redis = new \Redis();
            //长连接为pconnect,长连接要注意执行close关闭
            $func = Arr::get($connection, 'persistent', false) ? 'pconnect' : 'connect';
            $redis->connect(Arr::get($connection, 'host', ''), Arr::get($connection, 'port'), $this->timeOut);

            //有配置密码的，进行auth操作
            $pwd = Arr::get($connection, 'password', '');
            if($pwd) {
                $redis->auth($pwd);
            }
            $redis->select(Arr::get($connection, 'database'));

            //设置redis的option,如Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE
            foreach($this->options as $key => $val) {
                $redis->setOption($key, $val);
            }

            $this->clients[$name] = $redis;
        }

        return $redis;
    }

    /**
     * 返回当前所有redis实例
     *
     * @return array
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * 执行redis操作命令
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function command($method, array $parameters = [])
    {
        return call_user_func_array([$this->clients[$this->name], $method], $parameters);
    }

    /**
     * 动态执行命令
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->command($method, $parameters);
    }

    /**
     * 析构函数，释放所有redis连接
     *
     *@return mixed
     */
    public function __deconstruct()
    {
        foreach($this->clients as $client) {
            if(!is_null($client) && is_object($client) && $client instanceof \Redis) {
                $client->close();
            }
        }
    }
}
