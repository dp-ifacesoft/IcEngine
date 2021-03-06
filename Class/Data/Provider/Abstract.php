<?php

/**
 * Абстрактный класс провайдера данных.
 *
 * @author goorus, morph, neon
 */
class Data_Provider_Abstract
{

    /**
     * Время жизни данных по умолчанию.
     * Применяются только положительные значения.
     * 
     * @var int
     */
    public $expiration = -1;

    /**
     * Локи установленные текущим экземпляром скрипта.
     * Хранятся для снятия в конце работы
     * 
     * @var array
     */
    public $locks = array();

    /**
     * Трейсер
     * 
     * @var Tracer_Abstract
     */
    public $tracer;

    /**
     * Префикс ключей
     * 
     * @var string
     */
    public $prefix = '';

    /**
     * Префикс удаленных записей.
     * При удалении записи, ее можно пометить как удаленную,
     * а позже проверить состояние
     * 
     * @var string
     */
    public $prefixDeleted = '_dld_';

    /**
     * Префикс для локов.
     * При локе ключа, создается новый ключ с указанным префиксом
     * 
     * @var string
     */
    public $prefixLock = '_lck\\';

    /**
     * @desc Префикс для тегов
     * 
     * @var string
     */
    public $prefixTag = '_tag\\';

    /**
     * Создает и возвращает провайдер данных.
     *
     * @param array $config Параметры провайдера.
     */
    public function __construct($config = null)
    {
        if (!$config) {
            return;
        }
        foreach ($config as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    /**
     * Установка параметров.
     *
     * @param string $key Параметр.
     * @param string $value Значение.
     */
    protected function _setOption($key, $value)
    {
        switch ($key) {
            case 'prefix': 
                $this->prefix = $value;
                break;
            case 'expiration':
                $this->expiration = $value;
                break;
        }
    }

    /**
     * Если ключа $key не существует, он будет создан, а функция вернет true.
     * Если ключ уже существует, верентся false.
     *
     * @param string $key Ключ
     * @param mixed $value Значение
     * @param integer $expiration Время жизни ключа в секундах
     * @param array $tags Тэги
     * @return boolean false, если ключ уже существует
     */
    public function add($key, $value, $expiration = 0, $tags = array())
    {
        if ($this->tracer) {
            $this->tracer->add('add', $key, $expiration);
        }
    }

    /**
     * Добавление значения к ключу.
     *
     * @param string $key Ключ
     * @param string $value Строка, которая будет добавлена к текущему
     * значению ключа
     */
    public function append($key, $value)
    {
        if ($this->tracer) {
            $this->tracer->add('append', $key);
        }
        $v = $this->get($key);
        return $this->set($key, $value . $v);
    }

    /**
     * Проверка доступности провайдера.
     *
     * @return boolean
     */
    public function available()
    {
        return true;
    }

    /**
     * Проверка тегов на актуальность
     *
     * @param array $tags Массив пар (тег => время_создания)
     * @return boolean true, если все теги актуальны, иначе false.
     */
    public function checkTags($tags = array())
    {
        if (!is_array($tags) || empty($tags)) {
            return true;
        }
        $tagsKeys = array_keys($tags);
        $tagsValues = array_values($tags);
        foreach ($tagsKeys as &$key) {
            $key = $this->prefixTag . $key;
        }
        $currentValues = $this->getMulti($tagsKeys, true);
        if (count($tagsKeys) != count($currentValues)) {
            return false;
        }
        for ($i = 0, $count = count($tagsValues); $i < $count; $i++) {
            if ($tagsValues[$i] != $currentValues[$i]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Уменьшает значение ключа на указанную величину
     *
     * @param string $key Ключ
     * @param integer $value Величина, на которую будет уменьшено
     * значение ключа
     */
    public function decrement($key, $value = 1)
    {
        if ($this->tracer) {
            $this->tracer->add('decrement', $key, $value);
        }
        $current = $this->get($key);
        $this->set($key, $current - $value);
    }

    /**
     * Удаление одного или нескольких ключей
     *
     * @param string|array $keys Ключ или массив ключей
     * @param integer $time Время блокировки ключа, после удаления (в секундах).
     * @param boolean $setDeleted Пометить ключ как удаленный.
     * Если true, будет создан новый ключ, существование ключа будет
     * возможно проверить методом isDeleted
     */
    public function delete($keys, $time = 0, $setDeleted = false)
    {
        $keys = (array) $keys;
        if ($this->tracer) {
            $this->tracer->add('delete', implode(',', $keys));
        }
    }

    /**
     * Удаление одного или нескольких ключей по шаблону
     *
     * @param string $pattern Маска ключа.
     * @param integer $time Время блокировки ключа, после
     * удаления (в секундах).
     * @param boolean $setDeleted Пометить ключ как удаленный.
     * Если true, будет создан новый ключ, существование ключа будет
     * возможно проверить методом isDeleted.
     * @return integer|null Количественно найденных ключей. Может отличаться
     * от реально удаленного количства ключей ничего не возвращать.
     */
    public function deleteByPattern($pattern, $time = 0, $setDeleted = false)
    {
        $keys = $this->keys($pattern);
        $this->delete($keys, $time, $setDeleted);
        return count($keys);
    }

    /**
     * Очистка кеша. Все ключи будут удалены.
     * Внимание! В большинстве случаев это приводит к полной очистке кэша,
     * а не только данных этого провайдера. Так если используется один
     * мемкеш (или редис), будут затерты данные всех провайдеров. Для
     * очистки одного провайдера, следует использовать deleteByPattern ('*')
     * 
     * @param integer $delay
     */
    public function flush($delay = 0)
    {
        if ($this->tracer) {
            $this->tracer->add('flush', $delay);
        }
    }

    /**
     * Получение значения ключа
     *
     * @param string $key Ключи
     * @param boolean $plain Получение значения в том виде, в каком он хранится.
     * @return string|null Текущее значение ключа, если ключа не существует,
     * null.
     */
    public function get($key, $plain = false)
    {
        if ($this->tracer) {
            $this->tracer->add('get', $key);
        }
        return null;
    }

    /**
     * Получение всех значений провайдера.
     * <b>Внимание</b>: реализовано не для всех провайдеров.
     *
     * @return array Массив пар (ключ => значение)
     */
    public function getAll()
    {
        return $this->getMulti($this->keys('*'));
    }

    /**
     * Получение значений нескольких ключей
     * 
     * @param array $keys
     * 		Список ключей
     * @param boolean $numeric_index
     * 		Если true, ключами результата будут будут индексы ключей,
     * 		иначе - ключи.
     * @return array
     * 		Массив значений ключей, аналогичный вызову метода get для каждого ключа.
     */
    public function getMulti(array $keys, $numericIndex = false)
    {
        if ($this->tracer) {
            $this->tracer->add('getMulti', implode(',', $keys));
        }
        $result = array();
        if ($numericIndex) {
            foreach ($keys as $i => $key) {
                $result[$i] = $this->get($key);
            }
        } else {
            foreach ($keys as $key) {
                $result[$key] = $this->get($key);
            }
        }
        return $result;
    }

    /**
     * Получение текущего значения ключа с одновременной заменой на новое значение.
     * Для реальных провайдеров эта операция может быть атомарной,
     * для абстрактного это лишь последовательность двух действий.
     *
     * @param string $key Ключ
     * @param mixed $vslue Данные
     * @return string|null Текущее значение ключа
     */
    public function getSet($key, $value)
    {
        $value = $this->get($key);
        $this->set($key, $value, $this->expiration);
        return $value;
    }

    /**
     * Статистика по провайдеру
     * 
     * @return array
     * 		Содержание статистики зависит от конкретной реализации провайдера
     */
    public function getStats()
    {
        
    }

    /**
     * Установка и получение значений тегов
     * 
     * @param array $tags
     * 		Названия тегов
     * @return array
     * 		Массив пар тегов (тег => время_создания).
     */
    public function getTags($tags = array())
    {
        $result = array();
        if (!$tags || !is_array($tags)) {
            return array();
        }
        foreach ($tags as $tag) {
            if (!$tag) {
                continue;
            }
            $tagKey = $this->prefixTag . $tag;
            $tagValue = $this->get($tagKey);
            if (!$tagValue) {
                $tagValue = microtime(true);
                $this->set($tagKey, $tagValue);
            }
            $result[$tag] = $tagValue;
        }
        return $result;
    }

    /**
     * Увеличение значения ключа на указанную величину
     * 
     * @param string $key Ключ
     * @param integer $value Величина
     * @return  Новое значение
     */
    public function increment($key, $value = 1)
    {
        if ($this->tracer) {
            $this->tracer->add('increment', $key);
        }
    }

    /**
     * Проверяет, помечен ли ключ как удаленный.
     * 
     * @param string $key Ключ
     * @return integer|false Метка времени удаления.
     * Если ключ не помечен удаленным, то false.
     */
    public function isDeleted($key)
    {
        return $this->get($this->prefixDeleted . $key);
    }

    /**
     * Попытка блокировки. После выполнения необходимых операций обязательно
     * вызвать delete с результатом этого метода, иначе возможно "подвисание"
     * параллельных процессов, работающих с этим же ключом.
     *
     * @param string $key
     * 		Ключ
     * @param integer $expiration
     * 		Максимальное время блокировки в секундах.
     * 		Если скрипт самостоятельно не удалит ключ блокировки, по истечение
     * 		этого времени, он будет считаться незаблокированным.
     * @param integer $repeats
     * 		Количество попыток блокировки, после которого
     * 		метод вернет false.
     * @param integer $interval
     * 		Интервал между попытками блокировки ключа (в милисекундах).
     * @return string|false
     * 		Ключ блокировки или false в случае неудачи
     */
    public function lock($key, $expiration = 30, $repeats = 5, $interval = 500)
    {
        $lockKey = $this->prefixLock . $key;
        do {
            $result = $this->add($lockKey, time(), $expiration);
            if ($result) {
                return $lockKey;
            }
            usleep($interval * 1000);
        } while (--$repeats > 0);
        if ($lockKey) {
            $this->locks[$lockKey] = time() + $expiration;
        }
        return $lockKey;
    }

    /**
     * Декодирование ключа
     * 
     * @param string $key
     * @return string
     */
    public function keyDecode($key)
    {
        return substr($key, strlen($this->prefix));
    }

    /**
     * Кодирование ключа для корректного сохранения в редисе
     * 
     * @param string $key
     * @return string
     */
    public function keyEncode($key)
    {
        return $this->prefix . $key;
    }

    /**
     * Получение массива ключей, соответствующих маске
     * 
     * @param string $pattern
     * 		Маска.
     * 		Примеры:
     * 		1) "image_*"
     * 		2) "user_*_phone"
     * 		3) "*"
     * @param string $server=null Сервер
     * @return array Массив ключей, подходящих под маску
     */
    public function keys($pattern, $server = null)
    {
        if ($this->tracer) {
            $this->tracer->add('keys', $pattern);
        }
        return array();
    }

    /**
     * Выполнение операции mset (multi set)
     * 
     * @param array $values
     * @param array $values
     */
    public function mset(array $values)
    {
        foreach ($values as $kkey => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Добавляет в начало
     * 
     * @param string $key Ключ
     * @param string $value Строка, которая будет добавлена к текущему
     * значению ключа.
     */
    public function prepend($key, $value)
    {
        if ($this->tracer) {
            $this->tracer->add('prepend', $key);
        }
        $currentValue = $this->get($key);
        $this->set($key, $value . $currentValue);
    }

    /**
     * Публикация сообщения в канал
     * 
     * @param string $channel
     * @param string $message
     */
    public function publish($channel, $message)
    {
        if ($this->tracer) {
            $this->tracer->add('pusblich', $channel . '/' . $message);
        }
    }

    /**
     * Устанавливает значение ключа.
     * Дополнительных проверок не выполняется.
     * 
     * @param string $key Ключ.
     * @param string $value Значение.
     * @param integer $expiration Время жизни ключа.
     * @param array $tags Теги.
     */
    public function set($key, $value, $expiration = null, $tags = array())
    {
        if ($this->tracer) {
            $this->tracer->add('set', $key);
        }
    }

    /**
     * Установка параметров
     * 
     * @param string|array $key Параметр.
     * @param string $value [optional] Значение.
     */
    public function setOption($key)
    {
        if (func_num_args() > 1) {
            $this->_setOption($key, func_get_arg(1));
            return;
        }
        foreach ($key as $k => $v) {
            $this->_setOption($k, $v);
        }
    }

    /**
     * Подписка на канал
     * 
     * @param string $channel
     */
    public function subscribe($channel)
    {
        if ($this->tracer) {
            $this->tracer->add('subscribe', $channel);
        }
    }

    /**
     * Удаление тега
     * 
     * Все связанные ключи будут считаться недействительными.
     * @param string $tag
     * 		Тег
     */
    public function tagDelete($tag)
    {
        $this->delete($this->prefixTag . $tag);
    }

    /**
     * Снятие блокировки с ключа
     * 
     * @param string $key
     * 		Заблокированный ключ
     */
    public function unlock($key)
    {
        $lockKey = $this->prefixLock . $key;
        $this->delete($lockKey);
    }

    /**
     * Удаление оставшихся локов скрипта.
     */
    public function unlockAll()
    {
        foreach ($this->locks as $lock => $expiration) {
            if ($expiration < time()) {
                $this->delete($lock);
            }
        }
    }

    /**
     * Отписаться канала
     * 
     * @param string $channel
     */
    public function unsubscribe($channel)
    {
        if ($this->tracer) {
            $this->tracer->add('unsubscribe', $channel);
        }
    }

}
