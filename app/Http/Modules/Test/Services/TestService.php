<?php
/**
 * test service
 */
namespace App\Http\Modules\Test\Services;

use App\Http\Core\Service;
use App\Http\Common\RedisOp;

class TestService extends Service
{
    
    protected $_redis;
    
    public function __construct()
    {
        $this->_redis = RedisOp::getInstance('default');
        var_dump($this->_redis);
    }
    
    /**
     * test 
     * @param array $p_arrParam
     * @return mixed
     */
    public function test($p_arrParam)
    {
        
        $ret = $this->_redis->get($p_arrParam['id']);
        if (false === $ret) {
            //TODO: db class
        }
        
        
        if($p_arrParam['id'] != 1){
            $this->arrError = ['errno' => 1011, 'errmsg'=> 'username not exist.'];
            return false;
        }
        return ['name' => $ret];
    }

}
