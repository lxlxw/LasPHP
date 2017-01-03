<?php
namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class  RequestAnalysis extends Facade

{
    protected static function getFacadeAccessor()
    {
        return 'RequestAnalysis';
    }
}
