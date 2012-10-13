<?php

class Helper_Data_Source
{

	/**
	 * @desc Получить информацию о колонке таблицы
	 * @param string $table
	 * @param string $field
	 * @return Objective
	 */
	public static function field ($table, $field, $ds = null)
	{
        if (!$ds) {
            $ds = DDS::getDataSource();
        }

		$query = Query::instance ()
			->show ('FULL COLUMNS')
			->from ($table)
			->where ('Field', $field);

		$status = $ds->execute (
			$query
		)
			->getResult ()
				->asRow ();

		return new Objective ($status);
	}

	/**
	 * @desc Получить список колонок таблицы
	 * @param string $table
	 * @return Objective
	 */
	public static function fields ($table, $ds = null)
	{
        if (!$ds) {
            $ds = DDS::getDataSource();
        }

		$query = Query::instance ()
			->show ('FULL COLUMNS')
			->from ($table);

		$status = $ds->execute (
			$query
		)
			->getResult ()
				->asTable ();
		if (!$status)
		{
			return;
		}
		return new Objective ($status);
	}

	/**
 	 * @desc Получить информацию по таблице
 	 * @return Objective
 	 */
	public static function table ($table, $ds = null)
 	{
        if (!$ds) {
            $ds = DDS::getDataSource();
        }
        
 		$query = Query::instance ()
 			->show ('TABLE STATUS')
			->where ('Name', $table)
			->resetPart (Query::FROM);

 		$status = $ds->execute (
 			$query
 		)
			->getResult ()
				->asRow ();
		if (!$status)
		{
			return;
		}
 		return new Objective ($status);
 	}

	/**
	 * @desc Получить список таблиц текущей базы данных
	 * @return Objective
	 */
	public static function tables ($ds = null)
	{
        if (!$ds) {
            $ds = DDS::getDataSource();
        }
        
		$query = Query::instance ()
			->show ('TABLE STATUS')
			->resetPart (Query::FROM);

		$status = $ds->execute (
			$query
		)
			->getResult ()
				->asTable ();

		return new Objective ($status);
	}
}