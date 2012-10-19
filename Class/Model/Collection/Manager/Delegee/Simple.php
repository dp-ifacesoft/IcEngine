<?php
/**
 *
 * @desc Базовый загрузчик для менеджера коллекций.
 *
 */
class Model_Collection_Manager_Delegee_Simple
{

	public static function load (Model_Collection $collection, Query_Abstract $query)
	{
		$model = $collection->modelName ();

		// Выполняем запрос, получаем элементы коллеции
		$query_result =
			Model_Scheme::dataSource ($model)
				->execute ($query)
					->getResult ();

		$collection->queryResult ($query_result);

		// Если установлен флаг CALC_FOUND_ROWS,
		// то назначаем ему значение
		if ($query->getPart (Query::CALC_FOUND_ROWS))
		{
			$collection->data ('foundRows', $query_result->foundRows ());
		}

		$scheme = Model_Scheme::getScheme ($model);

		$fields = array_keys ($scheme ['fields']);

		$table = $query_result->asTable ();

		$items = array ();
		$addicts = array ();

		foreach ($table as $i => $item)
		{
			foreach ($item as $field => $value)
			{
				if (!in_array ($field, $fields))
				{
					$addicts [$i][$field] = $value;
				}
				else
				{
					if (!isset ($items [$i]))
					{
						$items [$i] = array ();
					}
					$items [$i][$field] = $value;
				}
			}
		}

		$collection->data ('addicts', $addicts);

		return array (
			'items'	=> $items,
		);
	}

}