<?php

namespace App\Http\Common;

use Illuminate\Container\Container;

class RedisOp{

    protected $predis;
    
    /**
     * 构造函数
     *
     * @param boolean $isUseCluster 是否采用 M/S 方案
     */
    public function __construct($p_redis)
    {
        $this->predis = $p_redis;
    }

    public static function getInstance($strRedisConnection)
    {
        $predis = Container::getInstance()->make('predis')->connection($strRedisConnection);
        $pResult = new RedisOp($predis);
        return $pResult;
    }
       
    /**
     * 写缓存
     *
     * @param string $key 组存KEY
     * @param string $value 缓存值
     * @param int $expire 过期时间， 0:表示无过期时间
     */
    public function set($key, $value, $expire = 0)
    {
        $pipe = $this->predis->multi(\Redis::PIPELINE);
        if($expire == 0){
            $pipe->set($key, $value);
        }else{
            $pipe->setex($key, $expire, $value);
        }
        $pipe->exec();
    }

    /**
     * 读缓存
     *
     * @param string $key 缓存KEY,支持一次取多个 $key = array('key1','key2')
     * @return string || boolean  失败返回 false, 成功返回字符串
     */
    public function get($key)
    {
        if (!$this->predis->exists($key)) {
            return false;
        }
        return $this->predis->get($key);
    }

    /**
     * 在当前链路上切换redis数据库索引
     *
     * @param integer $idx 数据库索引值
     * @return boolean 仅当操作成功时返回true
     */
    public function select($idx)
    {
        return $this->predis->select($idx);
    }

    /**
     * 获取某个数据库某个key对应的值
     *
     * @param integer $dbidx 数据库索引值
     * @param string $key 缓存KEY,支持一次取多个 $key = array('key1','key2')
     * @return mix 当操作失败时返回false，否则返回key对应的值
     */
    public function getDbValue($dbidx, $key)
    {
        if (!$this->select($dbidx)) {
            return false;
        }
        return $this->get($key);
    }

    /**
     * 设置某个数据库某个key对应的值
     *
     * @param integer $dbidx 数据库索引值
     * @param string $key 组存KEY
     * @param string $value 缓存值
     * @param int $expire 过期时间， 0:表示无过期时间
     * @return mix 当操作失败时返回false，否则设置结果
     */
    public function setDbValue($dbidx, $key, $value, $expire=0)
    {
        if (!$this->select($dbidx)) {
            return false;
        }

        return $this->set($key, $value, $expire);
    }

    /**
     * 设置哈希数据
     * @param unknown_type $name
     * @param unknown_type $key
     * @param unknown_type $value
     * @param unknown_type $expire
     * @return unknown
     */
    public function hset($name,$key,$value,$expire = 0)
    {
        $pipe = $this->predis->multi(\Redis::PIPELINE);
        if(is_array($value)){
            $hset =$pipe->hset($name,$key,serialize($value));
        } else {
        	$hset = $pipe->hset($name,$key,$value);
        }
        if ( $expire > 0 ) {
            $pipe->expire($name, $expire);
        }
        $pipe->exec();
    }

    /**
     * 获取哈希数据
     * @param unknown_type $name
     * @param unknown_type $key
     * @param unknown_type $serialize
     * @param bool $arr 要获取的数据是数组还是字符串
     * @return unknown
     */
    public function hget($name, $key = null, $serialize = true, $arr = true)
    {
        $pipe = $this->predis->multi(\Redis::PIPELINE);
        if ($key) {
            $pipe->hget($name, $key);
        } else {
            $pipe->hgetAll($name);
        }
        if($arr){
            $result = head($pipe->exec());
        }else{
            $result = $pipe->exec();
        }
        if ( $serialize and $key ) {
            $result = unserialize($result);
        }
        return $result;
    }


    /**
     * 删除哈希数据
     * @param unknown_type $name
     * @param unknown_type $key
     */
    public function hdel($name, $key = null)
    {
        $pipe = $this->predis->multi(\Redis::PIPELINE);
        if($key) {
            $pipe->hdel($name,$key);
        } else {
            $pipe->hdel($name);
        }
        $pipe->exec();
    }

	/**
	 * 向名称为$key的hash中批量添加元素
	 * @param string $key
	 * @param array $arrColumnValue Key-Value 表
	 * @return boolean
	 */
	public function hMSet($key, $arrColumnValue, $expire = 0)
	{
        $pipe = $this->predis->multi(\Redis::PIPELINE);
        $pipe->hMSet($key, $arrColumnValue);
        if ( $expire > 0 ) {
            $pipe->expire($key, $expire);
        }
        $pipe->exec();
	}
	
	/**
	 * 返回名称为{$key}的hash中{field1,field2,...｝对应的value 
	 * @param string $key
	 * @param string $arrColumn
	 * @return
	 */
	public function hMGet($key, $arrColumn)
	{
        $pipe = $this->predis->multi(\Redis::PIPELINE);
        $pipe->hMGet($key, $arrColumn);
        return $pipe->exec();
	}
	
	/**
	 * 返回名称为{$key}的hash中所有的键（field）及其对应的value
	 * @param string $key
	 * @return array
	 */
	public function hGetAll($key)
	{
        $pipe = $this->predis->multi(\Redis::PIPELINE);
        $pipe->hGetAll($key);
        return $pipe->exec();
	}

    /**
     * @param $strKey
     * @param $nValue
     * @param int $nExpire
     */
    public function incrBy($strKey, $nValue, $nExpire = 0)
    {
        $pipe = $this->predis->multi(\Redis::PIPELINE);
        $pipe->incrBy($strKey,$nValue);
        if ($nExpire > 0)
            $pipe->expire($strKey, $nExpire);
        return $pipe->exec();
    }

    /**
     * @param $strKey
     * @param $strFieldName
     * @param $nValue
     * @param $nExpire
     */
    public function hIncBy($strKey, $strFieldName, $nValue, $nExpire)
    {
        $pipe = $this->predis->multi(\Redis::PIPELINE);
        $pipe->hIncrBy($strKey,$strFieldName,$nValue);
        if ($nExpire > 0)
            $pipe->expire($strKey, $nExpire);
        return $pipe->exec();
    }

    public function exists($strKey)
    {
        return $this->predis->exists($strKey);
    }

}
