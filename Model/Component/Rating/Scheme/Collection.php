<?php
/**
 * 
 * @desc Коллекция схем рейтинга
 * @author Юрий
 * @package IcEngine
 *
 */
class Component_Rating_Scheme_Collection extends Component_Collection
{
	
	/**
	 * (non-PHPdoc)
	 * @see Component_Collection::getFor()
	 */
	public function getFor (Model $model)
	{
		$this->_model = $model;
		
		$this
		    ->where ('table', $this->_model->table ());
			
		return $this;
	}
	
}