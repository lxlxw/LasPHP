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
        if (!$p_query) {
            $query = $this->newQuery();
        } else {
            $query = $p_query;
        }
        foreach ($conditions as $key => $condition) {
            if (!empty($condition)) {
                if ($key === 'raw') {
                    if (count($condition) > 1) {
                        $query->setQuery($query->getQuery()->whereRaw($condition[0], $condition[1]));
                    } else {
                        $query->setQuery($query->getQuery()->whereRaw($condition[0]));
                    }
                } else {
                    if ($condition[0] === 'in' && is_array($condition[1])) {
                        if (count($condition[1]) > 1) {
                            $query->setQuery($query->getQuery()->whereIn($condition[1][0], $condition[1][1]));
                        } else {
                            $query->setQuery($query->getQuery()->whereIn($this->primaryKey, $condition[1][0]));
                        }
                    } elseif ($condition[0] === 'or' && is_array($condition[1])) {
                        if (count($condition[1]) > 2) {
                            $query->setQuery($query->getQuery()->orWhere($condition[1][0], $condition[1][1], $condition[1][2]));
                        } else {
                            $query->setQuery($query->getQuery()->orWhere($condition[1][0], $condition[1][1]));
                        }
                    } elseif ($condition[0] === 'not in' && is_array($condition[1])) {
                        if (count($condition[1]) > 1) {
                            $query->setQuery($query->getQuery()->whereNotIn($condition[1][0], $condition[1][1]));
                        } else {
                            $query->setQuery($query->getQuery()->whereNotIn($this->primaryKey, $condition[1][0]));
                        }
                    } elseif ($condition[0] === 'between' && is_array($condition[1])) {
                        if (count($condition[1]) > 1) {
                            $query->setQuery($query->getQuery()->whereBetween($condition[1][0], $condition[1][1]));
                        } else {
                            $query->setQuery($query->getQuery()->whereBetween($this->primaryKey, $condition[1][0]));
                        }
                    } else {
                        if (count($condition) > 2) {
                            $query->setQuery($query->getQuery()->where($condition[0], $condition[1], $condition[2]));
                        } else {
                            $query->setQuery($query->getQuery()->where($condition[0], $condition[1]));
                        }
                    }
                }
            }
        }
        return $query;
    }
    
    /**
     * 批量插入，主键重复修改部分字段值
     * 
     * @param array $attributes 插入记录二维数组
     * @param array $columns 如果主键重复需要修改的字段
     * @return Ambigous <number, \Illuminate\Database\mixed> 插入结果
     */
    public function insertOrUpdate(array $attributes, array $columns= [])
    {
        $parameters = [];
        $insertSql = $updateSql = "";
        if(empty($attributes))
            return false;
        $keys = array_keys(current($attributes));
        foreach ($attributes as $attribute) {
            $row = "";
            foreach ($keys as $key) {
                $row.= ", ?";
                $parameters[] = $attribute[$key];
            }
            $insertSql .= ", (".substr($row, 1).")";
        }
        if ($columns) {
            foreach ($columns as $key => $column) {
                if (is_numeric($key)) {
                    $updateSql .= ", {$column}=VALUES({$column})";
                } else {
                    $updateSql .= ", {$key}={$column}";
                }
            }
        }
        $query = "INSERT INTO " . $this->getConnection()->getTablePrefix() . $this->table;
        $query.= " (" . implode(',', $keys) . ") ";
        $query.= " VALUES " . substr($insertSql, 1);
        if ($updateSql) {
            $query.= " ON DUPLICATE KEY UPDATE ".substr($updateSql, 1);
        }
        return $this->getConnection()->insert($query, $parameters);
    }
    
    /**
     * 插入记录，如果记录主键已存在，则忽略
     * 
     * @param array $attribute 插入记录关联数组
     * @return Ambigous <boolean, \Illuminate\Database\mixed>
     */
    public function insertOrIgnore(array $attribute)
    {
        $columns = $parameters = [];
        $bindingSql = "";
        foreach ($attribute as $key => $val) {
            $bindingSql.= ", ?";
            $columns[] = $key;
            $parameters[] = $val;
        }
        $query = "INSERT IGNORE INTO " . $this->getConnection()->getTablePrefix() . $this->table;
        $query.= " (" . implode(',', $columns) . ") ";
        $query.= " VALUES (" . substr($bindingSql, 1) . ")";
    
        return $this->getConnection()->insert($query, $parameters);
    }
        


}
