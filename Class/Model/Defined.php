<?php

/**
 * Модель ячейки данных. Может быть уже заполнена
 * 
 * @author morph
 */
class Model_Defined extends Model
{
	/**
	 * Модели
     * 
     * @param array
	 */
    public static $rows = array();

	/**
	 * (non-PHPDoc)
	 * @see Model::delete
	 */
	public function delete()
	{
		throw new Exception('It\'s a defined model');
	}

	/**
	 * (non-PHPDoc)
	 * @see Model::save
	 */
	public function save($hard_insert = false)
	{
		throw new Exception('It\'s a defined model');
	}

	/**
	 * (non-PHPDoc)
	 * @see Model::update
	 */
	public function update(array $data, $hard = false)
	{
		throw new Exception('It\'s a defined model');
	}
}
