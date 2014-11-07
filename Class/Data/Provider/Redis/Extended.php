<?php
/**
 * Провайдер данных Redis
 *
 * @link https://github.com/nicolasff/phpredis
 * @author goorus, morph, sergb
 */
class Data_Provider_Redis_Extended extends Data_Provider_Abstract
{
    /**
     * Подключение к редису
     *
     * @var Redis
     */
    protected $connection;
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
        return $this->connection->append($this->keyEncode($key), $value);
    }
    /**
     * @inheritdoc
     */
    protected function _setOption($key, $value)
    {
        switch ($key) {
            case 'servers':
                $server = $value->__toArray();
                if (isset($server[0])) {
                    $server = reset($server);
                }
                $redis = new Redis();
                $redis->connect($server['host'], isset($server['port']) ? $server['port'] : null);
                if (isset($server['db'])) {
                    $redis->select($server['db']);
                }
                $this->connection = $redis;
                break;
        }
        return parent::_setOption($key, $value);
    }
    /**
     * @inheritdoc
     */
    public function decrement($key, $value = 1)
    {
        return $this->connection->decrBy($this->keyEncode($key), $value);
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
            $result = $this->connection->delete($this->keyEncode($keys));
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
     * @inheritdoc
     */
    public function get($key, $plain = false)
    {
        if (Tracer::$enabled) {
            $startTime = microtime(true);
        }
        $result = $this->connection->get($this->keyEncode($key));
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
        $items = $this->connection->mGet($keys);
        if (!$items) {
            return $result;
        }
        $result = array_merge($result, array_combine($keys, $items));
        $decodedItems = array();
        foreach ($keys as $key) {
            $decodedItems[$key] = isset($result[$key]) ? $this->valueDecode($result[$key]) : null;
        }
        if ($numericIndex) {
            return array_values($decodedItems);
        }
        return $decodedItems;
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
        $keyEncoded = $this->keyEncode($key);
        $value = $this->connection->getSet($keyEncoded, $value);
        if (0 < $this->expiration) {
            $this->connection->expire($keyEncoded, $this->expiration);
        }
        return $value;
    }
    /**
     * @inheritdoc
     */
    public function increment($key, $value = 1)
    {
        return $this->connection->incrBy($this->keyEncode($key), $value);
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
        if (strlen($pattern) > 1 && $pattern[strlen($pattern) - 1] === '*') {
            $pattern = rtrim($pattern, '*');
        }
        $key = $this->keyEncode($pattern) . '*';
        $keys = $this->connection->keys($key);
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
        $this->connection->publish($channel, $message);
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
        $value = $this->valueEncode($value);
        $key = $this->keyEncode($key);
        if ($expiration) {
            $result = $this->connection->setex($key, $expiration, $value);
        } else {
            $result = $this->connection->set($key, $value);
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
        $this->connection->subscribe($channel);
    }
    /**
     * @inheritdoc
     */
    public function unsubscribe($channel)
    {
        $this->connection->unsubscribe($channel);
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
     * The time to live in seconds. 
     * In Redis 2.6 or older the command returns -1 if the key does not exist or if the key exist but has no associated expire.
     * Starting with Redis 2.8 if the key has no ttl, -1 will be returned, and -2 if the key doesn't exist.
     * 
     * @param string $key
     * @return int
     */
    public function ttl($key)
    {
        $key = $this->keyEncode($key);
        return $this->connection->ttl($key);
    }
    /**
     * Verify if the specified key exists.
     * 
     * @return bool
     */
    public function exists($key)
    {
        $key = $this->keyEncode($key);
        return $this->connection->exists($key);
    }
    /**
     * Sets an expiration date (a timeout) on an item. 
     * 
     * @return bool
     */
    public function expire($key, $expiration)
    {
        $key = $this->keyEncode($key);
        return $this->connection->expire($key, $expiration);
    }
    public function discard()
    {
        return $this->connection->discard();
    }
    
    public function exec()
    {
        return $this->connection->exec();
    }
    /**
     * Verify if the specified key exists.
     * 
     * @return bool
     */
    public function multi()
    {
        return $this->connection->multi();
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
        $args = func_get_args();
        $args[0] = $this->keyEncode($args[0]);
        return call_user_func_array([$this->connection, 'zAdd'], $args);
    }
    
    /**
     * Add one or more members to a sorted set or update its score if it already exists
     * 
     * @param string $key
     * @param array $values
     * @return array
     */
    public function sAdd($key, $values)
    {
        array_unshift($values, $this->keyEncode($key));
        call_user_func_array([$this->connection, 'sAdd'], $values);
    }
    
    /**
     * Возвращает пересечение
     * 
     * @param array $keys
     * @return array
     */
    public function sInter($keys)
    {
        $keysEncoded = [];
        foreach ($keys as $key) {
            $keysEncoded[] = $this->keyEncode($key);
        }
        return call_user_func_array([$this->connection, 'sInter'], $keysEncoded);
    }
    
    /**
     * Add one or more members to a sorted set or update its score if it already exists
     * 
     * @param string $key
     * @param array $values
     * @return array
     */
    public function zAddArray($key, $values)
    {
        $params = [];
        $params[] = $this->keyEncode($key);
        foreach ($values as $item) {
            $params[] = $item['score'];
            $params[] = $item['value'];
        }
        return call_user_func_array([$this->connection, 'zAdd'], $params);
    }
    
    /**
     * Get the number of members in a sorted set
     * 
     * @param string $key
     * @return long The set's cardinality
     */
    public function zSize($key)
    {
        $key = $this->keyEncode($key);
        return $this->connection->zSize($key);
    }
    /**
     *  Count the members in a sorted set with scores within the given values
     * 
     * @param string $key
     * @param double $start "+inf" or "-inf" string also valid, null is interpreted as "-inf"
     * @param double $end   "+inf" or "-inf" string also valid, null is interpreted as "+inf"
     * @return int The size of a corresponding zRangeByScore.
     */
    public function zCount($key, $start=null, $end=null)
    {
        $key = $this->keyEncode($key);
        if (is_null($start)) {
            $start = '-inf';
        }
        if (is_null($end)) {
            $end = '+inf';
        }
        return $this->connection->zCount($key, $start, $end);
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
        $key = $this->keyEncode($key);
        return $this->connection->zIncrBy($key, $incValue, $member);
    }
    /**
     * Intersect multiple sorted sets and store the resulting sorted set in a new key
     * 
     * @param string $keyOutput
     * @param array $zSetKeys
     * @param array $weights
     * @param string $aggregateFunction ("SUM", "MIN", or "MAX"), "SUM" is default
     */
    public function zIntersect($keyOutput, array $zSetKeys, array $weights=null, $aggregateFunction=null)
    {
        $keyOutput = $this->keyEncode($keyOutput);
        $keys = [];
        foreach ($zSetKeys as $key) {
            $keys[] = $this->keyEncode($key);
            echo $this->keyEncode($key) . '   ';
        }
        return $this->connection->zInter($keyOutput, $keys, $weights, $aggregateFunction);
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
    public function zRange($key, $start=0, $end=-1, $withScores=false)
    {
        $key = $this->keyEncode($key);
        $return = $this->connection->zRange($key, $start, $end, $withScores);
        return $return;
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
    public function zRevRange($key, $start=0, $end=-1, $withScores=false)
    {
        $key = $this->keyEncode($key);
        return $this->connection->zRevRange($key, $start, $end, $withScores);
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
    public function zRangeByScore($key, $start=null, $end=null, $limit=null, $offset=null, $withScores=false)
    {
        $key = $this->keyEncode($key);
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
        return $this->connection->zRangeByScore($key, $start, $end, $options);
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
    public function zRevRangeByScore($key, $start=null, $end=null, $limit=null, $offset=0, $withScores=false)
    {
        $key = $this->keyEncode($key);
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
        return $this->connection->zRevRangeByScore($key, $start, $end, $options);
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
        $key = $this->keyEncode($key);
        return $this->connection->zRank($key, $member);
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
        $key = $this->keyEncode($key);
        return $this->connection->zRevRank($key, $member);
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
        $args = func_get_args();
        $args[0] = $this->keyEncode($args[0]);
        return call_user_func_array([$this->connection, 'zDelete'], $args);
    }
    /**
     * Remove all members in a sorted set within the given indexes
     * 
     * @param string $key
     * @param int $start from 0
     * @param int $end
     * @return int The number of values deleted from the sorted set
     */
    public function zDeleteRangeByRank($key, $start=0, $end=-1)
    {
        $key = $this->keyEncode($key);
        return $this->connection->zDeleteRangeByRank($key, $start, $end);
    }
    /**
     * Remove all members in a sorted set within the given scores
     * 
     * @param string $key
     * @param double $start  "+inf" or "-inf" string also valid
     * @param double $end    "+inf" or "-inf" string also valid
     * @return int The number of values deleted from the sorted set
     */
    public function zDeleteRangeByScore($key, $start=0, $end=-1)
    {
        $key = $this->keyEncode($key);
        return $this->connection->zDeleteRangeByScore($key, $start, $end);
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
        $key = $this->keyEncode($key);
        return $this->connection->zScore($key, $member);
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
        $keyOutput = $this->keyEncode($keyOutput);
        $keys = [];
        foreach ($zSetKeys as $key) {
            $keys[] = $this->keyEncode($key);
        }
        return $this->connection->zUnion($keyOutput, $keys, $weights, $aggregateFunction);
    }
}
