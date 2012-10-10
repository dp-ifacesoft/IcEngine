<?php

/**
 * 
 * @desc Хелпер для мускула. 
 * @author Илья
 * @package IcEngine
 * 
 * @method getModels 
 *
 */
class Helper_Mysql
{

	const SQL_ESCAPE = '`';
	const SQL_QUOTE	 = '"';
	const SQL_WILDCARD = '*';

	/**
	 * @desc Обособляет название mysql терма, если в этом есть необходимость.
	 * Функция вернет исходную строку, если в ней присутствуют спец. символы
	 * (точки, скобки, кавычки, знаки мат. операций и т.п.)
	 * @param string $value Название терма.
	 * @return string Резултат обособления.
	 */
	public static function escape ($value)
	{
		if (
			strpos ($value, self::SQL_WILDCARD) === false &&
			strpos ($value, '(') === false &&
			strpos ($value, ' ') === false &&
			strpos ($value, '.') === false &&
			strpos ($value, '<') === false &&
			strpos ($value, '>') === false &&
			strpos ($value, '`') === false
		)
		{
			return self::SQL_ESCAPE .
				addslashes (iconv ('UTF-8', 'UTF-8//IGNORE', $value)) .
				self::SQL_ESCAPE;
		}
		return $value;
	}

	/**
	 * @desc Заключает выражение в кавычки
	 * @param mixed $value
	 * @return string
	 */
	public static function quote ($value)
	{
		if (is_array ($value))
		{
			debug_print_backtrace ();
			die ();
		}
		return self::SQL_QUOTE .
			addslashes (iconv ('UTF-8', 'UTF-8//IGNORE', stripslashes ($value))) .
			self::SQL_QUOTE;
	}

	/**
	*
	* @desc Получает список моделей по схеме. Для полученных моделей
	* дописывает комментарий, если он есть в БД.
	* @param Config_Array $config
	* @return array<string>
	*/
	public static function getModels (Config_Array $config)
	{
		$result = mysql_query (
				'SHOW TABLE STATUS'
		);
	
		if (is_resource ($result) && mysql_num_rows ())
		{
			$tables = array ();
			while (($row = mysql_fetch_assoc ($result)) !== false)
			{
				$tables [$row ['Name']] = $row;
			}
			if (!$config || empty ($config->models))
			{
				return;
			}
			$models = array ();
			foreach ($config->models as $model=>$data)
			{
				$table = empty ($data->table) ? $model : $data->table;
				foreach ($tables as $name=>$values)
				{
					if ($table == $name)
					{
						$models [] = array (
								'table'		=> $table,
								'model'		=> $model,
								'comment'	=> !empty ($values ['Comment']) ?
						$values ['Comment'] : $table
						);
					}
				}
			}
			return $models;
		}
	}
	
}