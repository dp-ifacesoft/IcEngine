<?php
/**
 * 
 * @desc Абстрактная коллекция для одиночного компонента
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Component_Single_Collection extends Component_Collection
{
	
	/**
	 * @desc Создаение компонента, если его не существует
	 * @return Model_Component
	 */
	protected function _createSingle ()
	{
		$component_name = $this->modelName ();
		$component = new $component_name (array (
			'table'		=> $this->_model->modelName (),
			'rowId'		=> $this->_model->key ()
		));
		return $component->save ();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Collection::load()
	 */
	public function load ($columns = array ())
	{
		parent::load ($columns);
		
		if (!$this->_items)
		{
			$this->_items = array (
				$this->_createSingle ()
			);
		}
		
		return $this;
	}
	
}