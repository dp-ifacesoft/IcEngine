<?php
/**
 *
 * @desc Опция для выбора по первичному ключу
 * @param string|integer|array ['key']
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Key extends Model_Option
{

	/**
	 * (non-PHPdoc)
	 * @see Model_Option::before()
	 */
	public function before ()
	{
		$field =
			$this->collection->modelName () .
			'.' .
			$this->collection->keyField ();
		if ($this->params['field']) {
			$field = $this->params['field'];
		}
		$this->query->where ($field, $this->params ['key']);
	}

}