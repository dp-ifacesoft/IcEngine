<?php

class Cli_Command_Controller extends Cli_Command
{
	public function exec ()
	{
		if (!$this->_args || empty ($this->_args [1]) || empty ($this->_args [3]))
		{
			return;
		}
		
		$controller_name = $this->_args [1]->getValue ();
		$project_name = $this->_args [3]->getValue ();
		
		$action_names = array ();
		
		if (!empty ($this->_args [5]))
		{
			for ($i = 5, $icount = sizeof ($this->_args); $i < $icount; $i++)
			{
				$action_names [] = $this->_args [$i]->getValue ();
			}
		}
		$this->newController ($project_name, $controller_name, $action_names);
	}
	
	public function newController ($project_name, $controller_name, $action_names)
	{
		if (!$project_name || !$controller_name)
		{
			return;
		}
		$dir = rtrim (
			realpath (
				rtrim (dirname (__FILE__), '/').'../../../../'), 
				'/'
			).'/'.ucfirst ($project_name).'/Controller/';
			
		$template_dir = rtrim (realpath (dirname (__FILE__)), '/').'/Controller/templates/';
		
		$controller_file = $dir.str_replace ('_', '/', $controller_name).'.php';
		$controller_dir = dirname ($controller_file);
		if (!is_dir ($controller_dir))
		{
			mkdir ($controller_dir, 0644, true);
			echo $controller_dir.' created'."\n";
		}
		else
		{
			echo $controller_dir.' already exists'."\n";
		}
		if (!is_file ($controller_file))
		{
			$controller_content = '';
			$controller_template = $template_dir.'controller.tpl';
			if (is_file ($controller_template))
			{
				$controller_content = file_get_contents ($controller_template);
				$controller_content = str_replace (
					'{name}',
					ucfirst ($controller_name),
					$controller_content
				);
				$controller_content = str_replace (
					'{parent}',
					'Controller_Abstract',
					$controller_content
				);
			}	
			file_put_contents ($controller_file, $controller_content);
			echo $controller_file.' created'."\n";
		}
		else
		{
			echo $controller_file.' already exists'."\n";
		}
	}
	
	public function setArgs (array $args)
	{
		if (count ($args) >= 4)
		{
			include_once (rtrim (dirname (__FILE__), '/').'/Controller/Argument.php');
			include_once (rtrim (dirname (__FILE__), '/').'/Project/Argument.php');
			$args = array_values ($args);
			$this->_args = array (
				new Cli_Command_Argument ($args [0])
			);
			
			$controller = new Cli_Command_Controller_Argument ($args [1]);
			if (!$controller->validate ())
			{
				return;
			}
			
			$this->_args [] = $controller;
			$this->_args [] = new Cli_Command_Argument ($args [2]);
			
			$project = new Cli_Command_Project_Argument ($args [3]);
			if (!$project->validate ())
			{
				return;
			}
			
			$this->_args [] = $project;
			
			$this->_args [] = new Cli_Command_Argument ($args [4]);
			
			if (count ($args) > 5)
			{
				$tmp = array ();
				for ($i = 5, $icount = sizeof ($args); $i < $icount; $i++)
				{
					if (strpos ($args [$i], ',') !== false)
					{
						$tmp = array_merge ($tmp, explode (',', $args [$i]));
					}
					else
					{
						$tmp [] = $args [$i];
					}
				}
				$args = $tmp;
				
				$this->_args [] = new Cli_Command_Argument ($args [3]);
				
				for ($i = 0, $icount = sizeof ($args); $i < $icount; $i++)
				{
					$value = trim ($args [$i], ' ,"\'');
					if ($value)
					{
						$argument = new Cli_Command_Controller_Argument ($value);
						if ($argument->validate ())
						{
							$this->_args [] = $argument;
						}
					}
				}
			}
			
		}
		return $this;
	}
}