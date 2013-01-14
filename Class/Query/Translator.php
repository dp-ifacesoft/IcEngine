<?php
/**
 *
 * @desc Транслятор запросов.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Query_Translator
{
	/**
	 * @desc Подключенные трансляторы.
	 * @var array
	 */
	protected static $_translators = array ();

	/**
	 * @desc Возвращает объект транслятора по имени.
	 * @param string $name Название транслятора.
	 * @return Query_Translator
	 */
	public static function byName ($name)
	{
		if (!isset (self::$_translators [$name]))
		{
			$class_name = 'Query_Translator_' . $name;
			self::$_translators [$name] = new $class_name ();
		}

		return self::$_translators [$name];
	}

	/**
	 * @desc Возвращает объект транслятора по имени и типу.
	 * @param string $name Название транслятора.
	 * @param string $type Тип запроса
	 * @return Query_Translator
	 */
	public static function factory ($name, $type)
	{
		$parts = explode (' ', $type);
		foreach ($parts as &$part)
		{
			$part = strtoupper (substr ($part, 0, 1)) .
				strtolower (substr ($part, 1));
		}
		$type = implode ('_', $parts);
		$name .= '_' . $type;
		if (!isset (self::$_translators [$name]))
		{
			$class_name = 'Query_Translator_' . $name;
			self::$_translators [$name] = new $class_name ();
		}

		return self::$_translators [$name];
	}
}