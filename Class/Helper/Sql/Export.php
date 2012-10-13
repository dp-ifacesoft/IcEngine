<?php
/**
 * 
 * @desc Помощник для экспорта в SQL
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class Helper_Sql_Export
{
	
	/**
	 * @desc Экспорт таблиц в файл
	 * @param string|array $tables
	 * @param string $output 
	 * @param array $options
	 *		recreate=false Удалить и создать таблицы заного
	 */
	public static function export ($tables, $output, $options = array ())
	{
		$tables = (array) $tables;
		$eol = "\r\n";
		
		$time = date ('Y-m-d H:i:s');
		fwrite ($output, "# IcEngine tables export started at $time$eol");
		
		foreach ($tables as $table)
		{
			fwrite ($output, "# exporting: $table$eol");
			
			if (isset ($options ['recreate']) && $options ['recreate'])
			{
				// Дроп таблицы
				fwrite ($output, "DROP TABLE IF EXISTS `$table`;$eol");

				// Создание таблицы
				$row = DDS::execute (
					Query::instance ()
						->show ("CREATE TABLE `$table`")
					)->getResult ()->asRow ();

				fwrite ($output, $row ['Create Table'] . ";$eol");
			}
			
			// Данные из таблицы
			$step = 100;
			$last_count = $step;
			$offset = 0;
			
			$query = Query::instance ()
				->insert ($table);
			
			for ($offset = 0; $last_count == $step; $offset += $step)
			{
				$rows = DDS::execute (
					Query::instance ()
						->select ('*')
						->from ("`$table`")
						->limit ($step, $offset)
				)->getResult ()->asTable ();
				$last_count = count ($rows);
				
				foreach ($rows as $row)
				{
					$query->values ($row);
					fwrite (
						$output,
						$query->translate ('Mysql') . ";$eol"
					);
				}
			}
			
			fwrite ($output, "# export finished: $table$eol$eol");
		}
		
		$time = date ('Y-m-d H:i:s');
		fwrite ($output, "# IcEngine tables export finished at $time$eol");
	}
	
	/**
	 * @desc Экспорт данных таблицы.
	 * @param string $table
	 * @param resource $output
	 * @param array $options 
	 */
	public static function exportTableData ($table, $output, 
		array $options = array ())
	{
		$default = array (
			'step'		=> 100,
			'select'	=> 
				Query::instance ()
					->select ('*')
					->from ($table),
			'insert'	=> 
				Query::instance ()
					->insert ($table),
			'eol'		=> "\r\n",
			'flush'		=> true
		);
		
		$options = array_merge ($default, $options);
		
		$eol = $options ['eol'];
		$step = $options ['step'];
		$select = $options ['select'];
		/* var Query $select */
		
		$last_count = $step;
		$insert = $options ['insert'];
		/* var Query $insert */
		
		for ($offset = 0; $last_count == $step; $offset += $step)
		{
			$select->limit ($step, $offset);
			$rows = DDS::execute ($select)->getResult ()->asTable ();
			foreach ($rows as $row)
			{
				$insert->values ($row);
				fwrite ($output, $insert->translate ('Mysql') . ';' . $eol);
			}
			$last_count = count ($rows);
			if ($options ['flush'])
			{
				fflush ($output);
			}
			echo "($offset)";
		}
	}
	
}
