<?php

namespace App\Http\Core\BaseModel;

use Illuminate\Database\Eloquent\Model AS LumenModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Container\Container;

class Model extends LumenModel {

    /**
     * 表缓存有效期
     * @var int
     */
    const CACHE_TABLE_EXPIRES = 604800;

    /**
     * 查询缓存有效期
     * @var int
     */
    const CACHE_QUERY_EXPIRES = 3600;

    /**
     * 主键字段
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 自动创建更新时间戳字段
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (env('APP_DEBUG', false)) {
            $this->getConnection()->enableQueryLog();
        }
    }
    
    /**
     * 开启事务
     * 
     * @return void
     */
    public function beginTransaction() {
        $this->getConnection()->beginTransaction();
    }
    
    /**
     * 回滚事务
     * 
     * @return void
     */
    public function rollBack() {
        $this->getConnection()->rollBack();
    }
    
    /**
     * 提交事务
     * 
     * @return void
     */
    public function commit() {
        $this->getConnection()->commit();
    }
    
    /**
     * 获取sql执行语句
     * 
     * @return void
     */
    public function getSql()
    {
        return $this->getConnection()->getQueryLog();
    }
    
    /**
     * 获取sql执行的最后一条语句
     * 
     * @return void
     */
    public function getLastSql()
    {
        $sqlLog = $this->getConnection()->getQueryLog();
        return end($sqlLog);
    }
    
    /**
     * 构造一组查询条件
     * @param array $conditions 查询条件数组
     * @param Builder $query 查询构造器
     * @return Builder 查询构造器
     */
    public function multiwhere(array $conditions = [], Builder $p_query = null)
    {
        //TODO:
    }
    
    /**
     * 批量插入，主键重复修改部分字段值
     * @param array $attributes 插入记录二维数组
     * @param array $columns 如果主键重复需要修改的字段
     * @return Ambigous <number, \Illuminate\Database\mixed> 插入结果
     */
    public function insertOrUpdate(array $attributes, array $columns= [])
    {
        $query = $parameters = '';
        //TODO:
        return $this->getConnection()->insert($query, $parameters);
    }
        


}
