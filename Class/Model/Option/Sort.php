<?php
/**
 * 
 * @desc Опция для сортировки по полю "sort".
 * Если $params ['order'] == 'desc', данные будут отсортированы в обратном
 * порядке.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Sort extends Model_Option
{
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Option::before()
	 */
	public function before ()
	{
		if (
			isset ($this->params ['order']) &&
			strtoupper ($this->params ['order']) == 'DESC'
		)
		{
			$this->query->order (
				'`' . $this->collection->modelName () . '`.`sort` DESC'
			);
		}
		else
		{
			$this->query->order (
				'`' . $this->collection->modelName () . '`.`sort`'
			);
		}
	}
	
}