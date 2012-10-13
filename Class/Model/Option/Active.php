<?php
/**
 *
 * @desc Опция для выбора только активных моделей.
 * Если $params ['active'] == false, будут выбраны неактивные.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Active extends Model_Option
{

	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 */
	public function before ()
	{
		if (isset ($this->params ['active']) && !$this->params ['active'])
		{
			$this->query->where ('`' . $this->collection->modelName () . '`.`active`', 0);
		}
		else
		{
			$this->query->where ('`' . $this->collection->modelName () . '`.`active`', 1);
		}
	}

}