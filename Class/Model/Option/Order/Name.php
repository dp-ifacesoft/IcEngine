<?php
/**
 * 
 * @desc Опция для добавления правила упорядочивания в обратном порядке.
 * Возможно передать поле для сортировки, если поле не передано, сортировка
 * будет идти по ключевому полю.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Order_Name extends Model_Option
{
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Option::before()
	 */
	public function before ()
	{
		$field = '`' . $this->collection->modelName () . '`.`name`';
		
		$desc = 
			isset ($this->params ['order']) &&
			strtoupper ($this->params ['order']) == 'DESC'; 
		
		$this->query->order (array (
			$field => $desc ? Query::DESC : Query::ASC
		));
	}
	
}