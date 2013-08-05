<?php

/**
 * Получение модулей по isMain
 */
class Module_Option_Main extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$value = isset($this->params['value']) 
            ? (int) (bool) $this->params['value'] : 1;
		$this->query->where('isMain', $value);
	}

}