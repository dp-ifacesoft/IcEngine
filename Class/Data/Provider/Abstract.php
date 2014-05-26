<?php
/**
 *
 * @desc Абстрактный класс провайдера данных.
 * @author Goorus
 * @package IcEngine
 *
 */
class Data_Provider_Abstract
{

	/**
	 * Локи установленные текущим экземпляром скрипта.
	 * Хранятся для снятия в конце работы.
	 * @var array
	 */
	public $locks			= array ();

	/**
	 * @desc Трейсер
	 * @var Tracer_Abstract
	 */
	public $tracer;

	/**
	 * @desc Префикс ключей.
	 * @var string
	 */
	public $prefix			= '';

	/**
	 * Префикс удаленных записей.
	 * При удалении записи, ее можно пометить как удаленную,
	 * а позже проверить состояние.
	 * @var string
	 */
	public $prefixDeleted	= '_dld_';

	/**
	 * Префикс для локов.
	 * При локе ключа, создается новый ключ с указанным префиксом.
	 * @var string
	 */
	public $prefixLock		= '_lck\\';

	/**
	 * @desc Префикс для тегов.
	 * @var string
	 */
	public $prefixTag		= '_tag\\';

	/**
	 * @desc Создает и возвращает провайдер данных.
	 * @param array $config Параметры провайдера.
	 */
	public function __construct ($config = null)
	{
		if (!$config)
		{
			return;
		}

		foreach ($config as $opt_name => $opt_value)
		{
			$this->setOption ($opt_name, $opt_value);
		}
	}

	/**
	 * @desc Установка параметров.
	 * @param string $key Параметр.
	 * @param string $value Значение.
	 */
	protected function _setOption ($key, $value)
	{
		if ($key == 'tracer')
		{
			$class = 'Tracer_' . $value;
			$this->tracer = new $class ();
		}
		elseif ($key == 'prefix')
		{
			$this->prefix = $value;
		}
	}

	public function _valDump ($value)
	{
		if (is_bool ($value) || is_numeric($value))
		{
			return var_export ($value, true);
		}
		if (is_array ($value))
		{
			return 'Array (' . count ($value) . ')';
		}
		if (is_object ($value))
		{
			return get_class ($value);
		}
		if (is_null ($value))
		{
			return 'null';
		}
		return gettype ($value) . '(' . strlen ($value) . ') "' . substr ($value, 0, 30) . '"';
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
	public function add ($key, $value, $expiration = 0, $tags = array())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('add', $key, $expiration);
		}
	}

	/**
     * Добавление значения к ключу.
     * @param string $key
     * 		Ключ
     * @param string $value
     * 		Строка, которая будет добавлена к текущему значению ключа
	 */
	public function append ($key, $value)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('append', $key);
		}

		$v = $this->get ($key);
		return $this->set ($key, $value . $v);
	}

	/**
	 * Проверка доступности провайдера.
	 * @return boolean
	 */
	public function available ()
	{
		return true;
	}

	/**
	 * Проверка тегов на актуальность
	 * @param array $tags
	 * 		Массив пар (тег => время_создания)
	 * @return boolean
	 * 		true, если все теги актуальны, иначе false.
	 */
	public function checkTags ($tags = array ())
	{
		if (!is_array ($tags) || empty ($tags))
		{
			return true;
		}

		$tags_keys = array_keys ($tags);
		$tags_vals = array_values ($tags);

		foreach ($tags_keys as &$key)
		{
			$key = $this->prefixTag . $key;
		}

		$current_values = $this->getMulti ($tags_keys, true);

		if (count ($tags_keys) != count ($current_values))
		{
		    debug_print_backtrace ();
		    return false;
		}

		for ($i = 0, $count = count ($tags_vals); $i < $count; $i++)
		{
			if ($tags_vals [$i] != $current_values [$i])
			{
				return false;
			}
		}

		return true;
	}

	/**
     * Уменьшает значение ключа на указанную величину
     * @param string $key
     * 		Ключ
     * @param integer $value
     * 		Величина, на которую будет уменьшено значение ключа
	 */
	public function decrement ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('decrement', $key, $value);
		}

		$current = $this->get ($key);
		$this->set ($key, $current - $value);
	}

	/**
     * Удаление одного или нескольких ключей
     *
     * @param string|array $keys
     * 		Ключ или массив ключей
     * @param integer $time
     * 		Время блокировки ключа, после удаления (в секундах).
     * @param boolean $set_deleted
     *  	Пометить ключ как удаленный.
     *  	Если true, будет создан новый ключ, существование ключа будет
     * 		возможно проверить методом isDeleted
 	 */
	public function delete ($keys, $time = 0, $set_deleted = false)
	{
		$keys = (array) $keys;

		if ($this->tracer)
		{
			$this->tracer->add ('delete', implode (',', $keys));
		}
	}

	/**
     * @desc Удаление одного или нескольких ключей.
     * @param string $pattern Маска ключа.
     * @param integer $time Время блокировки ключа, после
     * удаления (в секундах).
     * @param boolean $set_deleted Пометить ключ как удаленный.
     * Если true, будет создан новый ключ, существование ключа будет
     * возможно проверить методом isDeleted.
     * @return integer|null Количественно найденных ключей. Может отличаться
     * от реально удаленного количства ключей ничего не возвращать.
 	 */
	public function deleteByPattern ($pattern, $time = 0, $set_deleted = false)
	{
		$keys = $this->keys ($pattern);
		$this->delete (
			$keys,
			$time,
			$set_deleted
		);
		return count ($keys);
	}

	/**
     * @desc Очистка кеша. Все ключи будут удалены.
     * Внимание! В большинстве случаев это приводит к полной очистке кэша,
     * а не только данных этого провайдера. Так если используется один
     * мемкеш (или редис), будут затерты данные всех провайдеров. Для
     * очистки одного провайдера, следует использовать deleteByPattern ('*').
     * @param integer $delay
	 */
	public function flush ($delay = 0)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('flush', $delay);
		}
	}

	/**
     * Получение значения ключа
     * @param string $key
     * 		Ключи
     * @param boolean $plain
     * 		Получение значения в том виде, в каком он хранится.
     * @return
     * 		Текущее значение ключа.
     * 		Если ключа не существует - null.
	 */
	public function get ($key, $plain = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('get', $key);
		}

		return null;
	}

	/**
	 * Получение всех значений провайдера.
	 * <b>Внимание</b>: реализовано не для всех провайдеров.
	 * @return array
	 * 		Массив пар (ключ => значение)
	 */
	public function getAll ()
	{
		return $this->getMulti ($this->keys ('*'));
	}

	/**
     * Получение значений нескольких ключей
     * @param array $keys
     * 		Список ключей
     * @param boolean $numeric_index
     * 		Если true, ключами результата будут будут индексы ключей,
     * 		иначе - ключи.
     * @return array
     * 		Массив значений ключей, аналогичный вызову метода get для каждого ключа.
 	 */
	public function getMulti (array $keys, $numeric_index = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('getMulti', implode (',', $keys));
		}

		$result = array();

		if ($numeric_index)
		{
			foreach ($keys as $i => $key)
			{
				$result [$i] = $this->get ($key);
			}
		}
		else
		{
			foreach ($keys as $key)
			{
				$result [$key] = $this->get ($key);
			}
		}

		return $result;
	}

	/**
     * Статистика по провайдеру
     * @return array
     * 		Содержание статистики зависит от конкретной реализации провайдера
 	 */
	public function getStats ()
	{

	}

	/**
	 * Установка и получение значений тегов.
	 * @param array $tags
	 * 		Названия тегов
	 * @return array
	 * 		Массив пар тегов (тег => время_создания).
	 */
	public function getTags ($tags = array ())
	{
		$result = array ();
		if (is_array ($tags) && !empty ($tags))
		{
			foreach ($tags as $tag)
			{
				if (!$tag)
				{
					continue;
				}
				$key_tag = $this->prefixTag . $tag;
				$v = $this->get ($key_tag);
				if (!$v)
				{
					$v = microtime (true);
					$this->set ($key_tag, $v);
				}
				$result [$tag] = $v;
			}
		}
		return $result;
	}

	/**
     * @desc Увеличение значения ключа на указанную величину
     * @param string $key Ключ
     * @param integer $value Величина
	 * @return  Новое значение
	 */
	public function increment ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('increment', $key);
		}
	}

	/**
	 * @desc Проверяет, помечен ли ключ как удаленный.
	 * @param string $key Ключ
	 * @return integer|false Метка времени удаления.
	 * Если ключ не помечен удаленным, то false.
	 */
	public function isDeleted ($key)
	{
		return $this->get ($this->prefixDeleted . $key);
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
	public function lock ($key, $expiration = 30, $repeats = 5, $interval = 500)
	{
		$lock_key = $this->prefixLock . $key;

		do {
			$r = $this->add ($lock_key, time (), $expiration);
			if ($r)
			{
				return $lock_key;
			}
			usleep ($interval * 1000);
		} while (--$repeats > 0);

		if ($lock_key)
		{
			$this->locks [$lock_key] = time () + $expiration;
		}

		return $lock_key;
	}

	/**
	 * @desc Декодирование ключа.
	 * @param string $key
	 * @return string
	 */
	public function keyDecode ($key)
	{
		return substr ($key, strlen ($this->prefix));
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
     * @desc Получение массива ключей, соответствующих маске
     * @param string $pattern
     * 		Маска.
     * 		Примеры:
     * 		1) "image_*"
     * 		2) "user_*_phone"
     * 		3) "*"
     * @param string $server=null Сервер
     * @return array Массив ключей, подходящих под маску
	 */
	public function keys ($pattern, $server = NULL)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('keys', $pattern);
		}
		return array ();
	}

	public function mset (array $values)
	{
		foreach ($values as $k => $v)
		{
			$this->set ($k, $v);
		}
	}

	/**
     * @desc Добавляет в начало
     * @param string $key Ключ
     * @param string $value Строка, которая будет добавлена к текущему
	 * значению ключа.
	 */
	public function prepend ($key, $value)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('prepend', $key);
		}

		$v = $this->get ($key);
		$this->set ($key, $v . $value);
	}

	/**
	 * @desc Публикация сообщения в канал
	 * @param string $channel
	 * @param string $message
	 */
	public function publish ($channel, $message)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('pusblich', $channel . '/' . $message);
		}
	}

	/**
     * @desc Устанавливает значение ключа.
     * Дополнительных проверок не выполняется.
     * @param string $key Ключ.
     * @param string $value Значение.
     * @param integer $expiration Время жизни ключа.
     * @param array $tags Теги.
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('set', $key);
		}
	}

	/**
	 * @desc Установка параметров.
	 * @param string|array $key Параметр.
	 * @param string $value [optional] Значение.
	 */
	public function setOption ($key)
	{
		if (func_num_args () > 1)
		{
			$this->_setOption ($key, func_get_arg (1));
			return ;
		}
		foreach ($key as $k => $v)
		{
			$this->_setOption ($k, $v);
		}
	}

	/**
	 * @desc Подписка на канал
	 * @param string $channel
	 */
	public function subscribe ($channel)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('subscribe', $channel);
		}
	}

	/**
	 * Удаление тега.
	 * Все связанные ключи будут считаться недействительными.
	 * @param string $tag
	 * 		Тег
	 */
	public function tagDelete ($tag)
	{
		$this->delete ($this->prefixTag . $tag);
	}

	/**
	 * Снятие блокировки с ключа
	 * @param string $key
	 * 		Заблокированный ключ
	 */
	public function unlock ($key)
	{
		$lock_key = $this->prefixLock . $key;
		$this->delete ($lock_key);
	}

	/**
	 * Удаление оставшихся локов скрипта.
	 */
	public function unlockAll ()
	{
		foreach ($this->locks as $lock => $exp)
		{
			if ($exp < time ())
			{
				$this->delete ($lock);
			}
		}
	}

	/**
	 * @desc Отписаться канала
	 * @param string $channel
	 */
	public function unsubscribe ($channel)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('unsubscribe', $channel);
		}
	}

}