<?php

class Controller_CodeGen extends Controller_Abstract
{
	public function createController ($name, $actions)
	{
		$actions = explode (',', $actions);
		$actions = array_map ('trim', $actions);
		
		if ($actions)
		{
			sort ($actions);
		}
		
		Loader::load ('Helper_Code_Generator');
		Helper_Code_Generator::createController ($name, $actions);
		
		$this->_task->setTemplate (null);
	}
	
	public function createModel ($name)
	{
		Loader::load ('Helper_Code_Generator');
		Helper_Code_Generator::createModel ($name);
		
		$this->_task->setTemplate (null);
	}
	
	public function createProject ($config_path)
	{
		Loader::load ('Helper_Code_Generator');
		Loader::load ('Config_Php');
		
		$config = new Config_Php ($config_path);
		
		if ($config)
		{
			$config = $config->__toArray ();
		}
		
		Helper_Code_Generator::createProject ($config);
		
		$this->_task->setTemplate (null);
	}
}