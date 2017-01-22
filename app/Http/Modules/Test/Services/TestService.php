<?php
/**
 * test service
 */
namespace App\Http\Modules\Test\Services;

use App\Http\Core\Service;
use App\Http\Common\RedisOp;
use App\Http\Modules\Test\Models\TestModel;

class TestService extends Service
{
    
    protected $_redis;
    
    public function __construct()
    {
        $this->_redis = RedisOp::getInstance('default');
    }
    
    /**
     * test 
     * @param array $p_arrParam
     * @return mixed
     */
    public function test($p_arrParam)
    {
        $ret = $this->_redis->get($p_arrParam['id']);
        if ($ret !== false) {
            return $ret;
        }
        $arr = (new TestModel())->getTestByID($p_arrParam);
        if(false === $arr){
            $this->arrError = ['errno' => 1011, 'errmsg'=> 'username not exist.'];
            return false;
        }
        $this->_redis->set($arr['id'], $arr['name'], 300);
        
        return $arr['name'];
    }
    
    /**
     * run
     * 
     * @return mixed
     */
    public function run()
    {
        return true;
    }
}
