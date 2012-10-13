<?php

/**
 * Description of Attribute
 *
 * @author markov
 */
class Controller_Admin_Attribute extends Controller_Abstract
{
	public function index ($table, $rowId) 
	{
		$model = Model_Manager::byKey ($table, $rowId);
		
		if (!$model)
		{
			return;
		}
	
		$attribute_collection = Model_Collection_Manager::byQuery (
			'Attribute',
			Query::instance ()
				->where ('table', $model->modelName ())
				->where ('rowId', $model->key ())
		);
		
		foreach ($attribute_collection as &$attribute)
		{
			$attribute->value = json_decode ($attribute->value);
		}
		
		$this->_output->send (array (
			'attribute_collection'	=> $attribute_collection,
			'model'			=> $model
		));	
	}
	
	/**
	 *
	 * @param type $table название модели
	 * @param type $rowId айдишник модели
	 * @param type $key название атрибута
	 * @param type $value значение атрибута 
	 * @return type 
	 */
	public function add ($table, $rowId, $key, $value)
	{
		$this->_task->setTemplate (null);
		$model = Model_Manager::byKey ($table, $rowId);
		
		if (!$model)
		{
			return;
		}
		
		$model->setAttribute ($key, $value);
		
		$this->_output->send (array (
			'data'		=> array (
				'status'	=> 'ok'
			)
		));
	}
	
//	/**
//	 *
//	 * @param type $id айдишник записи
//	 * @param type $key название атрибута
//	 * @param type $value значение атрибута 
//	 */
//	public function edit ($id, $key, $value)
//	{
//		$attribute = Model_Manager::byKey ('Attribute', $id);
//		
//		if (!$attribute)
//		{
//			return;
//		}
//		
//		$attribute->update (array (
//			'key'		=> $key,
//			'value'	=> $value
//		));
//	}
}
