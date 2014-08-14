<?php

class Smarty extends Subscribe_Render_Abstract
{
	/**
	 * 
	 * @var string
	 */
	protected $_template = '';
	
	
	/**
	 * 
	 * @var string
	 */
	const PATH_TO_SMARTY = '';
	
	/**
	 * 
	 * @var string
	 */
	const DATA_VAR = 'data';
	
	/**
	 * 
	 * @param array $data
	 */
	public function render ($data)
	{
		loader::requireOnce (self::PATH_TO_SMARTY);
		$smarty = Registry::get ('smarty');
		if (!$smarty)
		{
			if (class_exist ('Smarty'))
			{
				$smarty = new Smarty ();
				$config = Registry::get ('config_smarty');
				$smarty->_templateDir = $config->templateDir;
				$smarty->_compileDir = $config->compileDir;
			}
		}
		if (!$smarty)
		{
			return;
		}
		$smarty->assign (self::DATA_VAR, $data);
		return $smarty->fetch ($this->_template);
	}
}