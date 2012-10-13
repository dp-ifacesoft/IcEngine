<?php
/**
 *
 * @desc Опция для выбора текущего баланса модели.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Balance_Value extends Model_Option
{

	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 */
	public function before ()
	{
		$model = $this->collection->modelName ();
		$kf = $this->collection->keyField ();

		$this->query
			->select ('Component_Balance.value AS balance_value')
			->singleLeftJoin (
				'Component_Balance',
				"Component_Balance.rowId=`$model`.`$kf` AND
				 Component_Balance.table='$model'"
			);
	}

}