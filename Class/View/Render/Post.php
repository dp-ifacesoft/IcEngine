<?php

/**
 * Рендер post-запросов
 * 
 * @author morph
 */
class View_Render_Post extends View_Render_Abstract
{
    /**
     * @inheritdoc
     */
	public function fetch($tpl)
	{
		$result = $this->vars;
		$this->vars = array();
		return $result;
	}

    /**
     * @inheritdoc
     */
	public function display($tpl)
	{
        $redirect = '/';
        if ($this->vars) {
	        $this->vars = reset($this->vars);
	        $redirect = isset($this->vars['redirect'])
	        	 ? $this->vars['redirect'] : '/';
        }
		$this->getService('helperHeader')->redirect($redirect);
        die;
	}
}