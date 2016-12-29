<?php
/**
 * test service
 */
namespace App\Http\Modules\Test\Services;

use App\Http\Core\Service;

class TestService extends Service
{
    /**
     * test 
     * @param array $p_arrParam
     * @return mixed
     */
    public function test($p_arrParam)
    {
        if($p_arrParam['id'] != 1){
            $this->arrError = ['errno' => 1011, 'errmsg'=> 'username not exist.'];
            return false;
        }
        return ['name' => 'testname'];
    }

}
