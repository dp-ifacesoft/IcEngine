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
class Model_Option_Order_Desc extends Model_Option
{
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Option::before()
	 */
	public function before ()
	{
		$field = isset ($this->params ['field']) ?
			$this->params ['field'] :
			(
				'`' . $this->collection->modelName () . '`.`' . 
				$this->collection->keyField () . '`'
			);
		
		$this->query->order (array ($field => Query::DESC));
	}
	
}