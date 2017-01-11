<?php
namespace App\Http\Modules\Test\Models;

use App\Http\Core\Model;

class TestModel extends Model
{
    protected $table = 'test';

    /**
     * 组装[test]表条件
     * @param array $p_arrParam
     * @return array
     */
    private function _buildSqlCondition($p_arrParam)
    {
        $arrCondition = [];
        if (!empty($p_arrParam['id'])) {
            $arrCondition[] = ['id', $p_arrParam['id']];
        }
        return $arrCondition;
    }
    
    /**
     * 根据主键获取远程自动升级的文件包
     * @param array $p_arrParam
     * @param array $p_arrFiled
     * @return array
     */
    public function getTestByID($p_arrParam, $p_arrFiled = ['*'])
    {
        $arrWhere = $this->_buildSqlCondition($p_arrParam);
        
        $resultArr = $this->multiwhere($arrWhere)
        ->limit(1)
        ->get($p_arrFiled)
        ->toArray();
        return empty($resultArr) ? false : head($resultArr);
    }

    /**
     *更新
     * @param array $p_arrWhere
     * @param array $p_arrUpdate
     * @return boolean
     */
    public function updateTest($p_arrWhere, $p_arrUpdate)
    {
        if(empty($p_arrWhere) || empty($p_arrUpdate))
            return false;
        return $this->newQuery()->where($p_arrWhere)->update($p_arrUpdate);
    }

}