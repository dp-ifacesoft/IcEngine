<?php

/**
 * Провайдер данных Redis
 *
 * @author goorus, morph
 */
class Data_Provider_Redis extends Data_Provider_Abstract
{
	/**
	 * Подключение к редису
	 *
     * @var Redis
	 */
	protected $connections = array();

    /**
     * Уже полученные ключи
     *
     * @var array
     */
    public static $keys = array();

	/**
	 * @inheritdoc
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::_setOption()
	 */
	public function _setOption($key, $value)
	{
		switch ($key) {
			case 'servers':
				foreach ($value as $server) {
                    $redis = new Redis();
                    $this->connections[$server['host']] = $redis;
                    $redis->connect(
                        $server['host'],
                        isset($server['port']) ? $server['post'] : null
                    );
				}
				return true;
		}
		return parent::_setOption($key, $value);
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::decrement()
	 */
	public function decrement($key, $value = 1)
	{
		return $this->getConnection($key)->decrBy(
            $this->keyEncode($key), $value
        );
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::delete()
	 */
	public function delete($keys, $time = 0, $set_deleted = false)
	{
		if (!is_array($keys)) {
			if (isset($this->locks[$keys])) {
				unset($this->locks[$keys]);
			}
			if (Tracer::$enabled) {
				$startTime = microtime(true);
			}
            $connection = $this->getConnection($keys);
			$result = $connection->delete($this->keyEncode($keys));
			if (Tracer::$enabled) {
				$endTime = microtime(true);
				Tracer::incRedisDeleteCount();
				Tracer::incRedisDeleteTime($endTime - $startTime);
			}
			return $result;
		}
		foreach ($keys as $key) {
            $this->delete($key);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::deleteByPattern()
	 */
	public function deleteByPattern($pattern, $time = 0, $set_deleted = false)
	{
        $this->delete($this->keys($pattern));
	}

    /**
     * Отфильтровать ключу для конкретного соединения
     *
     * @param array $keys
     * @param integer $index
     * return array
     */
    protected function filterKeys($keys, $index)
    {
        $count = count($this->connections);
        if ($count == 1) {
            return $keys;
        }
        $result = array();
        foreach ($keys as $key) {
            $keyIndex = abs(crc32($key)) % $count;
            if ($keyIndex == $index) {
                $result[] = $keys;
            }
        }
        return $result;
    }

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::get()
	 */
	public function get($key, $plain = false)
	{
        if (isset(self::$keys[$key])) {
            return self::$keys[$key];
        }
		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}
        $connection = $this->getConnection($key);
		$result = $connection->get($this->keyEncode($key));
		if (Tracer::$enabled) {
			$endTime = microtime(true);
			Tracer::incRedisGetCount();
			Tracer::incRedisGetTime($endTime - $startTime);
		}
		$value = $this->valueDecode($result);
        self::$keys[$key] = $value;
        return $value;
	}

	/**
	 * Получить соединение (сокет)
	 *
     * @param string $key
     * @return resource
	 */
	public function getConnection($key)
	{
        $count = count($this->connections);
        if ($count == 1) {
            return reset($this->connections);
        }
		$index = abs(crc32($key)) % $count;
        return $this->connections[$index];
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getMulti()
	 */
	public function getMulti(array $keys, $numeric_index = false)
	{
        if (count($keys) == 1) {
            $value = $this->get($keys[0]);
            if ($numeric_index) {
                return array($value);
            }
            return array($key => $value);
        }
        $result = array();
        $keys = array_map(array($this, 'keyEncode'), $keys);
        foreach ($this->connections as $i => $connection) {
            $connectionKeys = $this->filterKeys($keys, $i);
            if (!$connectionKeys) {
                continue;
            }
            $items = $connection->mGet($connectionKeys);
            if (!$items) {
                return;
            }
            $result = array_merge($result, array_combine(
                $connectionKeys, $items
            ));
        }
        $sortedItems = array();
        foreach ($keys as $key) {
            $sortedItems[$key] = isset($result[$key])
                ? $this->valueDecode($result[$key]) : null;
        }
        if ($numeric_index) {
            return array_values($sortedItems);
        }
        return $sortedItems;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::increment()
	 */
	public function increment($key, $value = 1)
	{
		return $this->getConnection($key)->incrBy(
            $this->keyEncode($key), $value
        );
	}

	/**
	 * Кодирование ключа для корректного сохранения в редисе.
	 *
     * @param string $key
	 * @return string
	 */
	public function keyEncode($key)
	{
		return urlencode($this->prefix . $key);
	}

	/**
	 * Декодирование ключа.
	 *
     * @param string $key
	 * @return string
	 */
	public function keyDecode($key)
	{
		return substr(urldecode($key), strlen($this->prefix));
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::keys()
	 */
	public function keys($pattern, $server = null)
	{
		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}
        $keys = array();
        foreach ($this->connections as $connection) {
            if (strlen($pattern) > 1 &&
                $pattern[strlen($pattern) - 1] === '*') {
                $pattern = rtrim($pattern, '*');
            }
            $key = $this->keyEncode($pattern) . '*';
            $connectionKeys = $connection->keys($key);
            if (!$connectionKeys) {
                continue;
            }
            $keys = array_merge($keys, $connectionKeys);
        }
		if (Tracer::$enabled) {
			$endTime = microtime(true);
			Tracer::incRedisKeyCount();
			Tracer::incRedisKeyTime($endTime - $startTime);
		}
        return array_map(array($this, 'keyDecode'), $keys);
	}

	/**
	 * @see Data_Provider_Abstract::publish()
	 */
	public function publish($channel, $message)
	{
        foreach ($this->connections as $connection) {
            $connection->publish($channel, $message);
        }
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set($key, $value, $expiration = 3600, $tags = array())
	{
		if ($expiration < 0) {
			$expiration = 0;
		}
		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}
        $connection = $this->getConnection($key);
        $value = $this->valueEncode($value);
        $key = $this->keyEncode($key);
        if ($expiration) {
            $result = $connection->setex($key, $expiration, $value);
        } else {
            $result = $connection->set($key, $value);
        }
		if (Tracer::$enabled) {
			$endTime = microtime(true);
			Tracer::incRedisSetCount();
			Tracer::incRedisSetTime($endTime - $startTime);
		}
		return $result;
	}

	/**
	 * @see Data_Provider_Abstract::subscribe()
	 */
	public function subscribe($channel)
	{
        foreach ($this->connections as $connection) {
            $connection->subscribe($channel);
        }
	}

	/**
	 * @see Data_Provider_Abstract::unsubscribe()
	 */
	public function unsubscribe ($channel)
	{
		foreach ($this->connections as $connection) {
            $connection->unsubscribe($channel);
        }
	}

    /**
     * Расшифровывает значение
     *
     * @param string $value
     * @return mixed
     */
    protected function valueDecode($value)
    {
        return json_decode(urldecode($value), true);
    }

    /**
     * Кодирует значение
     *
     * @param mixed $value
     * @return mixed
     */
    protected function valueEncode($value)
    {
        return urlencode(json_encode($value));
    }
}