<?php

class View_Render_Post extends View_Render_Abstract
{

	public function fetch ($tpl)
	{
		$result = $this->_vars;
		$this->_vars = array ();
		return $result;
	}

	public function display ($tpl)
	{
        $redirect = '/';
        if ($this->_vars)
        {
	        $this->_vars = reset ($this->_vars);
	        $redirect = isset ($this->_vars ['redirect'])
	        	 ? $this->_vars ['redirect'] : '/';
        }
		Helper_Header::redirect ($redirect);
        die;
	}

	public function addHelper ($helper, $method)
	{

	}

}