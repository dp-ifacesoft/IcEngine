<?php
/**
 *
 * @desc Опция для отсеивания по id.
 * Ожадаются параметры $ids с массивом первичных ключей или $id с
 * единичным первичным ключом
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Not_Id extends Model_Option
{

	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 */
	public function before ()
	{
		if (isset ($this->params ['ids']) && $this->params ['ids'])
		{
			$this->query->where (
				$this->collection->modelName () . '.id NOT IN (?)',
				array ($this->params ['ids'])
			);
		}
		if (isset ($this->params ['id']) && $this->params ['id'])
		{
			$this->query->where (
				$this->collection->modelName () . '.id != ?',
				array ($this->params ['id'])
			);
		}
	}

}