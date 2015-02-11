<?php

/**
 *
 * @desc Провайдер данных Redis
 * @author Юрий
 * @package IcEngine
 *
 */
class Data_Provider_Redis extends Data_Provider_Abstract
{
	/**
	 * @desc Подключение к редису
	 * @var Redis
	 */
	public $conn = null;

	/**
	 * @desc Максимальное количество выбираемых за раз значений.
	 * Необходимо для обхода бага, когда
	 * в версии Redis под windows стояло жесткое ограничение на 15 значений.
	 * @var integer
	 */
	public $mget_limit = 0;

	/**
	 *
	 * @param array $config
	 */
	public function __construct ($config = array ())
	{
		$redis = new Redis();
		$this->conn = $redis;
		parent::__construct ($config);
		$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);    // use built-in serialize/unserialize
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::_setOption()
	 */
	public function _setOption ($key, $value)
	{
		switch ($key)
		{
			case 'mget_limit':
				$this->mget_limit = $value;
				return true;

			case 'servers':
				foreach ($value as $server)
				{
                            // используем старый формат конфига с множетсвом серверов, но используем только первый
					$this->conn->connect(
						$server ['host'],
						isset ($server ['port']) ? $server ['port'] : null
					);
					break;
				}
				return true;
		}
		return parent::_setOption ($key, $value);
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::add()
	 */
	public function add ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('add', $key, $expiration);
		}

		if ($expiration < 0)
		{
			$expiration = 0;
		}

		$result = $this->conn->setnx (
			$this->keyEncode ($key),
			$value
		);
		if ($expiration) {
			$this->expire($key, $expiration);
		}
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::append()
	 */
	public function append ($key, $value)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('append', $key);
		}

		return $this->conn->append ($this->keyEncode ($key), $value);
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::decrement()
	 */
	public function decrement ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('decrement', $key);
		}

		return $this->conn->decrBy ($this->keyEncode ($key), $value);
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::delete()
	 */
	public function delete ($keys, $time = 0, $set_deleted = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('delete', $keys, $time);
		}

		if ($time < 0)
		{
			$time = 0;
		}

		if (!is_array ($keys))
		{
			if ($set_deleted)
			{
				$this->conn->set (
					$this->keyEncode ($this->prefixDeleted . $keys),
					time ()
				);
			}
			if (isset ($this->locks [$keys]))
			{
				unset ($this->locks [$keys]);
			}

			if (Tracer::$enabled) {
				$startTime = microtime(true);
			}

			$result = $this->conn->delete ($this->keyEncode ($keys), $time);

			if (Tracer::$enabled) {
				$endTime = microtime(true);
				Tracer::incRedisDeleteCount();
				Tracer::incRedisDeleteTime($endTime - $startTime);
			}

			return $result;
		}

		foreach ($keys as $key)
		{
			$tt = $time;
			if (is_array ($key))
			{
				if (isset ($key [1]))
				{
					$tt = $key [1];
				}
				$key = $key [0];
			}

			if (isset ($this->locks [$key]))
			{
				unset ($this->locks [$key]);
			}

			if ($set_deleted)
			{
				$this->conn->set (
					$this->keyEncode ($this->prefixDeleted . $key),
					time ()
				);
			}
			if (Tracer::$enabled) {
				$startTime = microtime(true);
			}

			$this->conn->delete ($this->keyEncode ($key), $tt);

			if (Tracer::$enabled) {
				$endTime = microtime(true);
				Tracer::incRedisDeleteCount();
				Tracer::incRedisDeleteTime($endTime - $startTime);
			}
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::deleteByPattern()
	 */
	public function deleteByPattern ($pattern, $time = 0, $set_deleted = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('deleteByPattern', $pattern);
		}

		$this->delete($this->keys($pattern));
	}

    /**
     * Sets an expiration date (a timeout) on an item. 
     * 
     * @return bool
     */
    public function expire($key, $expiration)
    {
        $key = $this->keyEncode($key);
        return $this->conn->expire($key, $expiration);
    }
    
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::get()
	 */
	public function get ($key, $plain = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('get', $key);
		}

		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}

                $result = $this->conn->get($this->keyEncode($key));

		if (Tracer::$enabled) {
			$endTime = microtime(true);
			Tracer::incRedisGetCount();
			Tracer::incRedisGetTime($endTime - $startTime);
		}

		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getMulti()
	 */
	public function getMulti (array $keys, $numeric_index = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('getMulti', implode (',', $keys));
		}

		$keys = array_map (
			array ($this, 'keyEncode'),
			$keys
		);

		if ($this->mget_limit && count ($keys) > $this->mget_limit)
		{
			// Ограничение на максимальную выборку из кеша.
			// fix redis.c bug -.-
			// http://code.google.com/p/redis/issues/detail?id=24
			$start = 0;
			$r = array ();
			while ($start < count ($keys))
			{
				$subkeys = array_slice ($keys, $start, $this->mget_limit);
				$r = array_merge (
					$r,
					$this->conn->getMultiple($subkeys)
				);
				$start += $this->mget_limit;
			}
		}
		else
		{
			$r = $this->conn->getMultiple($keys);
		}

		if ($numeric_index)
		{
			return array_values ($r);
		}

		return array_combine ($keys, array_values ($r));
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::increment()
	 */
	public function increment ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('increment', $key);
		}

		return $this->conn->incrBy($this->keyEncode ($key), $value);
	}

	/**
	 * @desc Кодирование ключа для корректного сохранения в редисе.
	 * @param string $key
	 * @return string
	 */
	public function keyEncode ($key)
	{
		return $this->prefix . $key;
	}

	/**
	 * @desc Декодирование ключа.
	 * @param string $key
	 * @return string
	 */
	public function keyDecode ($key)
	{
		return substr($key, strlen($this->prefix));
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::keys()
	 */
	public function keys ($pattern, $server = NULL)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('keys', $pattern);
		}

		$mask = $this->keyEncode(rtrim($pattern, '*')) . '*';

		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}

		$r = $this->conn->keys($mask);

		if (Tracer::$enabled) {
			$endTime = microtime(true);
			Tracer::incRedisKeyCount();
			Tracer::incRedisKeyTime($endTime - $startTime);
		}

		if (empty ($r) || (count ($r) == 1 && empty ($r [0])))
		{
			return array ();
		}

		return array_map (array ($this, 'keyDecode'), $r);
	}

	/**
	 * @see Data_Provider_Abstract::publish()
	 */
	public function publish ($channel, $message)
	{
		return $this->conn->publish ($channel, $message);
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set ($key, $value, $expiration = null, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('set', $key, $value, $expiration);
		}

		if (is_null($expiration))
		{
			$expiration = $this->expiration;
		}
		if ($expiration < 0)
		{
			$expiration = 0;
		}

		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}

		$result = $this->conn->set ($this->keyEncode ($key), $value, $expiration);

		if (Tracer::$enabled) {
			$endTime = microtime(true);
			Tracer::incRedisSetCount();
			Tracer::incRedisSetTime($endTime - $startTime);
			Tracer::addRedisSet($this->keyEncode($key), $endTime - $startTime);
		}

		return $result;
	}

	/**
	 * @see Data_Provider_Abstract::subscribe()
	 */
	public function subscribe ($channel)
	{
		return $this->conn->subscribe ($channel);
	}

	/**
	 * @see Data_Provider_Abstract::unsubscribe()
	 */
	public function unsubscribe ($channel)
	{
		return $this->conn->unsubscribe ($channel);
	}
}