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
        if (!empty($ret))
            return $ret;
        
        //TODO: db class
        $arr = (new TestModel())->getTestByID($p_arrParam);
        var_dump($arr);exit;
        
        
        if($p_arrParam['id'] != 1){
            $this->arrError = ['errno' => 1011, 'errmsg'=> 'username not exist.'];
            return false;
        }
        return ['name' => $ret];
    }

}
