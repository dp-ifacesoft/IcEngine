<?php
/**
 * 
 * @desc Рендер для AJAX запросов (не проверялся).
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class View_Render_Ajax extends View_Render_Abstract
{
	
	public function fetch ($tpl)
	{
		$result = $this->_vars;
		$this->_vars = array();
		return $result;
	}
	
	public function display ($tpl)
	{
		reset ($this->_vars);
		echo json_encode (current ($this->_vars));
	}
	
	public function addHelper ($helper, $method)
	{
		
	}
	
}