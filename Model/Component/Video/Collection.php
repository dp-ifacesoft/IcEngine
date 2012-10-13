<?php
/**
 * 
 * @desc Коллекция видео
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Component_Video_Collection extends Component_Collection
{
	
	/**
	 * (non-PHPdoc)
	 * @see Component_Collection::getFor()
	 */
	public function getFor (Model $model)
	{
		$this->_model = $model;
		
		$this
		    ->where ('table', $this->_model->table ())
		    ->where ('rowId', $this->_model->key ())
		    ->query ()
		    	->order ('sort');
		    
			
		return $this;
	}
	
}