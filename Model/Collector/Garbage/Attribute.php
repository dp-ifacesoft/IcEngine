<?php

/**
 * @desc Gc для удаление аттриботов моделей, которых уже нет
 * @author Илья Колесников
 * @packager IcEngine
 * @copyright i-complex.ru
 */
class Collector_Garbage_Attribute extends Collector_Garbage_Abstract
{
	protected static $_config = array (
		// Количество атрибутов, обрабатываемых за раз
		'step'	=> 1000
	);

	public function process ()
	{
		$last_id = (int) $this->data;

		$step = max (1000, self::config ()->step);

		$query = Query::instance ()
			->select ('table', 'rowId')
			->distinct (true)
			->from ('Attribute')
			->where ('id>=?', $last_id)
			->limit ($step);

		$rows = DDS::execute ($query)
			->getResult ()
				->asTable ();

		if (!$rows)
		{
			$query = Query::instance ()
				->select ('id')
				->from ('Attribute')
				->order ('id')
				->limit (1);

			$last_id = DDS::exectute ($query)
				->getResult ()
					->asValue ();
		}
		else
		{
			list ($table, $rowId) = array_values ($rows [count ($rows)-1]);

			$query = Query::instance ()
				->select ('id')
				->from ('Attribute')
				->where ('table', $table)
				->where ('rowId', $rowId)
				->order ('id DESC')
				->limit (1);

			$last_id = DDS::execute ($query)
				->getResult ()
					->asValue ();

			foreach ($rows as $row)
			{
				$model = Model_Manager::byKey (
					$row ['table'],
					$row ['rowId']
				);

				if (!$model)
				{
					$query = Query::instance ()
						->delete ()
						->from ('Attribute')
						->where ('table', $row ['table'])
						->where ('rowId', $row ['rowId']);

					DDS::execute ($query);
				}
			}
		}

		$this->update (array (
			'data'	=> $last_id
		));
	}
}
