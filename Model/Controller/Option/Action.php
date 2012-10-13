<?php
/**
 * 
 * @desc
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class Controller_Option_Action extends Model_Option
{
	
	public function before ()
	{
		$this->query->where ('action', $this->params ['is']);
	}
	
}
