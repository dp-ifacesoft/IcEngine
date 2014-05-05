<?php

/**
 *
 * @desc Класс управления сообщениями внутри процесса.
 * Настройки по умолчанию считываются из config/Message/Queue.php
 * @author Юрий
 * @package IcEngine
 *
 */
abstract class Message_Queue
{

	/**
	 * @desc Все сообщения.
	 * @var array <Message_Queue_Abstract>
	 */
	protected static $_items;

	/**
	 * @desc Сообщения по типам.
	 * @var array <array <Message_Queue_Abstract>>
	 */
	protected static $_byType = array ();

	/**
	 * @desc Обработчики событий
	 * @var array <callback>
	 */
	protected static $_handlers = array ();

	/**
	 * @desc Сбрасывает колбэки сообщений на прописанные в конфиге.
	 */
	public static function flush ()
	{
		$config = Config_Manager::get (__CLASS__);

		if ($config->callbacks)
		{
			foreach ($config->callbacks as $name => $callback)
			{
				self::setCallback (
					$callback->type,
					$callback->function->asArray (),
					is_numeric ($name) ? null : $name
				);
			}
		}
	}

	/**
	 * @desc Возвращает массив сообщений указанного типа.
	 * @param integer $type Необходимый тип сообщений.
	 * @return array <Message_Queue_Abstract> Массив сообщений.
	 */
	public static function byType ($type)
	{
		if (!isset (self::$_byType [$type]))
		{
			return array ();
		}

		return self::$_byType [$type];
	}

	/**
	 * @desc Вызывается сторонними методами при наступлении события.
	 * @param string $type Тип события.
	 * @param array $data [optional] Дополнительные данные
	 * @return Message_Abstract
	 *
	 */
	public static function push ($type, array $data = array ())
	{
		$class = 'Message_' . $type;
		if (strpos ($class, '::') !== false || !Loader::load ($class))
		{
		    $class = 'Message_Abstract';
		}

		$n = count (self::$_items);
		$data ['index'] = $n;

		$message = new $class ($data, $type);

		self::$_items [$n] = $message;

		self::$_byType [$type][] = $message;

		if (isset (self::$_handlers [$type]))
		{
			foreach (self::$_handlers [$type] as $function)
			{
			    $message->notify ($function);
			}
		}

		return $message;
	}

	/**
	 * @desc Сообщение о загрузке контента.
	 * @param Model $model
	 * @param array $extends
	 * @return Message_After_Load_Content
	 */
	public static function pushAfterLoadContent (Model $model, array $extends = array ())
	{
		return self::push (
			'After_Load_Content',
			array_merge (
				$extends,
				array (
					'model'    => $model
				)
			)
		);
	}

	/**
	 *
	 * @param string $type
	 * @param integer $offset Отступ с конца списка
	 * @return Message_Abstract Найденное сообщение. Если не найдено - null.
	 */
	public static function last ($type, $offset = null)
	{
		if (is_null ($offset))
		{
			$offset = count (self::$_items) - 1;
		}

		for ($i = $offset; $i >= 0; $i--)
		{
			if (self::$_items [$i]->type () == $type)
			{
				return self::$_items [$i];
			}
		}

		return null;
	}

	/**
	 *
	 * @param integer $offset
	 * @return Message_After_Load_Content
	 */
	public static function lastAfterLoadContent ($offset = null)
	{
		return self::last ('After_Load_Content', $offset);
	}

	/**
	 * @desc Устновка нового callback'a для события.
	 * @param integer $type Тип события
	 * @param callback $function Функция, которая будет вызвана при
	 * наступлении события.
	 * @param string|null $name Названия колбэка (предотвращает повторное
	 * добавление обработчика).
	 * @param boolean $call_for_old Вызов для предыдущих.
	 * В случае, если события данного типа уже наступали (до добавления
	 * обработчика), для каждого из них колбэк будет вызван.
	 */
	public static function setCallback ($type, $function, $name = null,
		$call_for_old = false)
	{
		if (!isset (self::$_handlers [$type]))
		{
			self::$_handlers [$type] = array ();
		}

		if (!$name)
		{
			self::$_handlers [$type][] = $function;
		}
		else
		{
			self::$_handlers [$type][$name] = $function;
		}

		if ($call_for_old)
		{
			$olds = self::byType ($type);
			foreach ($olds as $message)
			{
			    $message->notify ($function);
			}
		}
	}

}