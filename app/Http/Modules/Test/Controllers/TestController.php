<?php
/**
 * test api
 */
namespace App\Http\Modules\Test\Controllers;

use App\Http\Core\Controller;
use App\Http\Modules\Test\Services\TestService;

class TestController extends Controller
{
    protected $arrData = [];

    protected $TestService;

    public function __construct($pApp)
    {
        parent::__construct($pApp);

        $this->arrData = $pApp->getParams();
        
        $this->TestService = new TestService();
    }
    
    /**
     * @method get
     * @param ['id' => '1', 'name' => 'xxx']
     * @return {'errno' : '0', 'errmsg' : 'success'}
     */
    public function test()
    {
        $result = $this->TestService->test($this->arrData);
        return $this->response($this->TestService->arrError['errno'], $this->TestService->arrError['errmsg'], $result);
    }
    
    /**
     * @method get
     * 
     * @return {'errno' : '0', 'errmsg' : 'success'}
     */
    public function run()
    {
        $result = $this->TestService->run($this->arrData);
        return $this->response();
    }

}