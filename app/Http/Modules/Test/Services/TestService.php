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
    
    /**
     * test 
     * @param array $p_arrParam
     * @return mixed
     */
    public function test($p_arrParam)
    {
        $this->_redis = RedisOp::getInstance('default');
        
        $ret = $this->_redis->get($p_arrParam['id']);
        if ($ret !== false) {
            return ['name' => $ret];
        }
        
        //TODO: db class
        
        if($p_arrParam['id'] != 1){
            $this->arrError = ['errno' => 1011, 'errmsg'=> 'username not exist.'];
            return false;
        }
        return ['name' => 'testname'];
    }

}
