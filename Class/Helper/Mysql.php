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