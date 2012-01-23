<?php

namespace Ice;

/**
 *
 * @desc DDS Default Data Source
 *
 * Easy way to call querys to DB like
 * DDS::execute ($query)
 *
 * @author Yury Shvedov
 * @package Ice
 *
 */
class DDS
{

	/**
	 * @desc По умолчанию
	 * @var Data_Source
	 */
	protected static $_source;

	/**
	 * @desc Экранирование
	 * @param string $string
	 * @return string
	 */
	public static function escape ($string)
	{
	    return mysql_real_escape_string ($string);
	}

	/**
	 * @desc Выполняет запрос и возвращает текущний источник
	 * @param Query $query Запрос
	 * @param Query_Options $options Опции
	 * @return Data_Source источник данных
	 */
	public static function execute (Query $query, $options = null)
	{
		return self::$_source->execute ($query, $options);
	}

	/**
	 * @desc Выполняет запрос и возвращает текущний источник.
	 * Источник данных будет определен автоматически.
	 * @param Query $query Запрос
	 * @param Query_Options $options Опции
	 * @return Data_Source источник данных
	 */
	public static function executeAuto (Query $query, $options = null)
	{
		$from = $query->getPart (Query::FROM);
		$from = reset ($from);
		$source = Model_Scheme::getInstance ()
			->getDataSource ($from [Query::TABLE]);
		return $source->execute ($query, $options);
	}

	/**
	 * @desc Возвращает текущий источник по умолчанию
	 * @return Data_Source
	 */
	public static function getDataSource ()
	{
		return self::$_source;
	}

	public static function initAsMysqlAddition ()
	{
		self::$_source = new Data_Source_Mysql ();
	}

	/**
	 * @return boolean
	 */
	public static function inited ()
	{
		return (bool) self::$_source;
	}

	/**
	 *
	 * @param Data_Source $source
	 */
	public static function setDataSource (Data_Source $source)
	{
		self::$_source = $source;
	}

}