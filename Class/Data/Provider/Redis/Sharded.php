<?php
/**
 * Провайдер данных Redis с разносом данных по нескольким серверам для балансирования нагрузки.
 * Выбор сервера осуществляется на основе ключа.
 *
 * @link https://github.com/nicolasff/phpredis
 */
class Data_Provider_Redis_Sharded extends Data_Provider_Abstract
{
    /**
     * Подключение к редису
     *
     * @var Redis
     */
    protected $connections = array();
    /**
     * Добавление значения к ключу.
     * Атомарная операция.
     *
     * @param string $key Ключ
     * @param string $value Строка, которая будет добавлена к текущему значению ключа.
     * @return int Длина нового значения (строки)
     */
    public function append($key, $value)
    {
        if ($this->tracer) {
            $this->tracer->add('append', $key);
        }
        return $this->getConnection($key)->append($this->keyEncode($key), $value);
    }
    /**
     * @inheritdoc
     */
    protected function _setOption($key, $value)
    {
        switch ($key) {
            case 'servers':
                foreach ($value as $server) {
                    $redis = new Redis();
                    $redis->connect($server['host'], isset($server['port']) ? $server['post'] : null);
                    if (isset($server['db'])) {
                        $redis->select($server['db']);
                    }
                    $this->connection = $redis;
                }
                break;
        }
        return parent::_setOption($key, $value);
    }
    /**
     * @inheritdoc
     */
    public function decrement($key, $value = 1)
    {
        return $this->getConnection($key)->decrBy($this->keyEncode($key), $value);
    }
    /**
     * @inheritdoc
     */
    public function delete($keys, $time = 0, $setDeleted = false)
    {
        if (!is_array($keys)) {
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
     * @inheritdoc
     */
    public function deleteByPattern($pattern, $time = 0, $setDeleted = false)
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
     * @inheritdoc
     */
    public function get($key, $plain = false)
    {
        if (Tracer::$enabled) {
            $startTime = microtime(true);
        }
        $connection = $this->getConnection($key);
        $result = $connection->get($this->keyEncode($key));
        if (Tracer::$enabled) {
            $endTime = microtime(true);
            Tracer::incRedisGetCount();
            Tracer::incRedisGetDelta();
            Tracer::incRedisGetTime($endTime - $startTime);
        }
        if (!$plain) {
            $value = $this->valueDecode($result);
        } else {
            $value = $result;
        }
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
     * Получить основное соединение (сокет)
     *
     * @return resource
     */
    public function getMainConnection()
    {
        return reset($this->connections);
    }
    /**
     * @inheritdoc
     */
    public function getMulti(array $keys, $numericIndex = false)
    {
        if (count($keys) == 1) {
            $value = $this->get($keys[0]);
            if ($numericIndex) {
                return array($value);
            }
            return array($keys[0] => $value);
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
            $result = array_merge($result, array_combine($connectionKeys, $items));
        }
        $sortedItems = array();
        foreach ($keys as $key) {
            $sortedItems[$key] = isset($result[$key]) ? $this->valueDecode($result[$key]) : null;
        }
        if ($numericIndex) {
            return array_values($sortedItems);
        }
        return $sortedItems;
    }
    /**
     * Получение текущего значения ключа с одновременной заменой на новое значение.
     * Атомарная операция.
     *
     * @param string $key Ключ
     * @param mixed $value Данные
     * @return string|null Текущее значение ключа
     */
    public function getSet($key, $value)
    {
        $connection = $this->getConnection($key);
        $keyEncoded = $this->keyEncode($key);
        $value = $connection->getSet($keyEncoded, $value);
        if (0 < $this->expiration) {
            $connection->expire($keyEncoded, $this->expiration);
        }
        return $value;
    }
    /**
     * @inheritdoc
     */
    public function increment($key, $value = 1)
    {
        return $this->getConnection($key)->incrBy($this->keyEncode($key), $value);
    }
    /**
     * @inheritdoc
     */
    public function keyEncode($key)
    {
        return $this->prefix . $key;
    }
    /**
     * @inheritdoc
     */
    public function keyDecode($key)
    {
        return substr($key, strlen($this->prefix));
    }
    /**
     * @inheritdoc
     */
    public function keys($pattern, $server = null)
    {
        if (Tracer::$enabled) {
            $startTime = microtime(true);
        }
        $keys = array();
        foreach ($this->connections as $connection) {
            if (strlen($pattern) > 1 && $pattern[strlen($pattern) - 1] === '*') {
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
     * @inheritdoc
     */
    public function publish($channel, $message)
    {
        foreach ($this->connections as $connection) {
            $connection->publish($channel, $message);
        }
    }
    /**
     * @inheritdoc
     */
    public function set($key, $value, $expiration = null, $tags = array())
    {
        if (is_null($expiration)) {
            // используем значение по умолчанию
            $expiration = $this->expiration;
        }
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
     * @inheritdoc
     */
    public function subscribe($channel)
    {
        foreach ($this->connections as $connection) {
            $connection->subscribe($channel);
        }
    }
    /**
     * @inheritdoc
     */
    public function unsubscribe($channel)
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
        return json_decode($value, true);
    }

    /**
     * Кодирует значение
     *
     * @param mixed $value
     * @return mixed
     */
    protected function valueEncode($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
