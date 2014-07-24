<?php
/**
 * Провайдер данных Redis
 *
 * @link https://github.com/nicolasff/phpredis
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
                $this->connections[$server['host']] = $redis;
                $redis->connect($server['host'], isset($server['port']) ? $server['post'] : null);
            }
            return true;
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

    /**
     * Add one or more members to a sorted set or update its score if it already exists
     * Example: zAdd($key, $score1, $value1, $score2, $value2, ...)
     * 
     * @param string $key
     * @param double $incValue
     * @param string $member
     * @return int  1 if the element is added. 0 otherwise.
     */
    public function zAdd($key, $score, $value)
    {
        return $this->getMainConnection()->zAdd(func_get_args());
    }
    /**
     * Get the number of members in a sorted set
     * 
     * @param string $key
     * @return long The set's cardinality
     */
    public function zSize($key)
    {
        return $this->getMainConnection()->zSize($key);
    }
    /**
     *  Count the members in a sorted set with scores within the given values
     * 
     * @param string $key
     * @param double $start "+inf" or "-inf" string also valid, null is interpreted as "-inf"
     * @param double $end   "+inf" or "-inf" string also valid, null is interpreted as "+inf"
     * @return int The size of a corresponding zRangeByScore.
     */
    public function zCount($key, $start, $end)
    {
        if (is_null($start)) {
            $start = '-inf';
        }
        if (is_null($end)) {
            $end = '+inf';
        }
        return $this->getMainConnection()->zCount($key, $start, $end);
    }
    /**
     *
     * Increment the score of a member in a sorted set
     * 
     * @param string $key
     * @param double $incValue
     * @param string $member
     * @return double The new value
     */
    public function zIncrBy($key, $incValue, $member)
    {
        return $this->getMainConnection()->zIncrBy($key, $incValue, $member);
    }
    /**
     * Intersect multiple sorted sets and store the resulting sorted set in a new key
     * 
     * @param string $keyOutput
     * @param array $zSetKeys
     * @param array $weights
     * @param string $aggregateFunction ("SUM", "MIN", or "MAX"), "SUM" is default
     */
    public function zIntersect($keyOutput, array $zSetKeys, array $weights, $aggregateFunction=null)
    {
        return $this->getMainConnection()->zInter($keyOutput, $zSetKeys, $weights, $aggregateFunction);
    }
    /**
     * Return a range of members in a sorted set, by index in the range [start, end].
     * Start and stop are interpreted as zero-based indices: 
     * 0 the first element, 1 the second ... -1 the last element, -2 the penultimate ...
     * 
     * @param string $key
     * @param int $start
     * @param int $end
     * @param bool $withScores default=false
     * @return array Values in specified range.
     */
    public function zRange($key, $start, $end, $withScores=false)
    {
        return $this->getMainConnection()->zRange($key, $start, $end, $withScores);
    }
    /**
     * Return a range of members in a sorted set, by index in the range [start, end],
     * with scores ordered from high to low.
     * Start and stop are interpreted as zero-based indices:
     * 0 the first element, 1 the second ... -1 the last element, -2 the penultimate ...
     * 
     * @param string $key
     * @param int $start
     * @param int $end
     * @param bool $withScores default=false
     * @return array Values in specified range.
     */
    public function zRevRange($key, $start, $end, $withScores=false)
    {
        return $this->getMainConnection()->zRevRange($key, $start, $end, $withScores);
    }
    /**
     * Returns the elements of the sorted set stored at the specified key which have scores in the range [start,end]. 
     * Adding a parenthesis before start or end excludes it from the range. +inf and -inf are also valid limits. 
     * 
     * @param string $key
     * @param double $start "+inf" or "-inf" string also valid, null is interpreted as "-inf"
     * @param double $end   "+inf" or "-inf" string also valid, null is interpreted as "+inf"
     * @param int $limit default no limit
     * @param int $offset default zero offset
     * @param bool $withScores default=false
     * @return array containing the values in specified range.
     */
    public function zRangeByScore($key, $start, $end, $limit=null, $offset=null, $withScores=false)
    {
        $options = [];
        if (isset($limit, $offset)) {
            $options['limit'] = [$offset, $limit];
        }
        if ($withScores) {
            $options['withscores'] = true;
        }
        if (empty($options)) {
            $options = null;
        }
        if (is_null($start)) {
            $start = '-inf';
        }
        if (is_null($end)) {
            $end = '+inf';
        }
        return $this->getMainConnection()->zRangeByScore($key, $start, $end, $options);
    }
    /**
     * Returns the elements of the sorted set stored at the specified key which have scores in the range [start,end]. 
     * Adding a parenthesis before start or end excludes it from the range. +inf and -inf are also valid limits. 
     * zRevRangeByScore returns items in reverse order, when the start and end parameters are swapped.     
     * 
     * @param string $key
     * @param double $start "+inf" or "-inf" string also valid, null is interpreted as "+inf"
     * @param double $end   "+inf" or "-inf" string also valid, null is interpreted as "-inf"
     * @param int $limit default no limit
     * @param int $offset default zero offset
     * @param bool $withScores default=false
     * @return array containing the values in specified range.
     */
    public function zRevRangeByScore($key, $start, $end, $limit=null, $offset=0, $withScores=false)
    {
        $options = [];
        if (isset($limit, $offset)) {
            $options['limit'] = [$offset, $limit];
        }
        if ($withScores) {
            $options['withscores'] = true;
        }
        if (empty($options)) {
            $options = null;
        }
        if (is_null($start)) {
            $start = '+inf';
        }
        if (is_null($end)) {
            $end = '-inf';
        }
        return $this->getMainConnection()->zRevRangeByScore($key, $start, $end, $options);
    }
    /**
     * Determine the index of a member in a sorted set,
     * starting at 0 for the item with the smallest score.
     * 
     * @param string $key
     * @param string $member
     * @return int
     */
    public function zRank($key, $member)
    {
        return $this->getMainConnection()->zRank($key, $member);
    }
    /**
     * Determine the index of a member in a sorted set.
     * zRevRank starts at 0 for the item with the largest score.
     * 
     * @param string $key
     * @param string $member
     * @return int
     */
    public function zRevRank($key, $member)
    {
        return $this->getMainConnection()->zRevRank($key, $member);
    }
    /**
     * Remove one or more members from a sorted set.
     * Example: zDelete($key, $member1, $member2, $member3)
     * 
     * @param string $key
     * @param string $member
     * @return int 1 on success, 0 on failure.
     */
    public function zDelete($key, $member)
    {
        return $this->getMainConnection()->zDelete(func_get_args());
    }
    /**
     * Remove all members in a sorted set within the given indexes
     * 
     * @param string $key
     * @param int $start from 0
     * @param int $end
     * @return int The number of values deleted from the sorted set
     */
    public function zDeleteRangeByRank($key, $start, $end)
    {
        return $this->getMainConnection()->zDeleteRangeByRank($key, $start, $end);
    }
    /**
     * Remove all members in a sorted set within the given scores
     * 
     * @param string $key
     * @param double $start  "+inf" or "-inf" string also valid
     * @param double $end    "+inf" or "-inf" string also valid
     * @return int The number of values deleted from the sorted set
     */
    public function zDeleteRangeByScore($key, $start, $end)
    {
        return $this->getMainConnection()->zDeleteRangeByScore($key, $start, $end);
    }
    /**
     * Get the score associated with the given member in a sorted set
     * 
     * @param string $key
     * @param string $member
     * @return double
     */
    public function zScore($key, $member)
    {
        return $this->getMainConnection()->zScore($key, $member);
    }
    /**
     * Add multiple sorted sets and store the resulting sorted set in a new key
     * Creates an union of sorted sets given in second argument. 
     * The result of the union will be stored in the sorted set defined by the first argument.
     * 
     * @param string $keyOutput
     * @param array $zSetKeys
     * @param array $weights
     * @param string $aggregateFunction ("SUM", "MIN", or "MAX"), "SUM" is default
     * @return int The number of values in the new sorted set.
     */
    public function zUnion($keyOutput, array $zSetKeys, array $weights, $aggregateFunction=null)
    {
        return $this->getMainConnection()->zUnion($keyOutput, $zSetKeys, $weights, $aggregateFunction);
    }
}
