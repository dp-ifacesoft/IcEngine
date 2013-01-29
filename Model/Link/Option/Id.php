<?php
/**
 *
 * @desc Для выбора Id линка.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Link_Option_Id extends Model_Option
{

	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 */
	public function before ()
	{
		$this->query
			->where ('toTable=?', 		$this->params ['toTable'])
			->where ('toTableId=?', 	$this->params ['toTableId'])
			->where ('fromTable=?', 	$this->params ['fromTable'])
			->where ('fromTableId=?',	$this->params ['fromTableId']);
	}
}