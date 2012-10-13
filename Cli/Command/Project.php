<?php

class Cli_Command_Project extends Cli_Command
{
	private static $_dirs = array (
		'Model',
		'Controller',
		'Widget',
		'configs',
		'includes',
		'View',
		'View/Controller',
		'View/Widget'
	);
	
	public function exec ()
	{
		if (!$this->_args)
		{
			return;
		}
		$project_names = array ();
		for ($i = 1, $icount = sizeof ($this->_args); $i < $icount; $i++)
		{
			if ($this->_args [$i])
			{
				$project_names [] = $this->_args [$i]->getValue ();
			}
		}
		foreach ($project_names as $name)
		{
			$this->newProject ($name);
		}
	}
	
	public function newProject ($name)
	{
		if ($name)
		{
			$dir = rtrim (
				realpath (
					rtrim (dirname (__FILE__), '/').'../../../../'), '/'
				).'/';
		
			$base_dir = $dir.ucfirst ($name).'/';
			if (!is_dir ($base_dir))
			{
				mkdir ($base_dir, 0644);
			}
			else
			{
				echo 'Project "'.$name.'" already exists.'."\n";
				return;
			}
			foreach (self::$_dirs as $dir)
			{
				if (!is_dir ($base_dir.$dir))
				{	
					mkdir ($base_dir.$dir, 0644, true);		
					if (is_dir ($base_dir.$dir))
					{
						echo $base_dir.$dir.' created.'."\n";
					}
					else
					{
						echo $base_dir.$dir.' already exists.'."\n";
					}
				}
			}
			echo 'Project "'.$name.'" created.'."\n";
			
		}
	}
	
	public function setArgs (array $args)
	{
		if (count ($args) >= 2)
		{
			$args = array_values ($args);
			$this->_args = array (
				new Cli_Command_Argument ($args [0]),
			);
			include_once (rtrim (dirname (__FILE__), '/').'/Project/Argument.php');
			$tmp = array ();
			for ($i = 1, $icount = sizeof ($args); $i < $icount; $i++)
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

			for ($i = 0, $icount = sizeof ($args); $i < $icount; $i++)
			{
				$value = trim ($args [$i], ' ,"\'');
				if ($value)
				{
					$argument = new Cli_Command_Project_Argument ($value);
					if ($argument->validate ())
					{
						$this->_args [] = $argument;
					}
				}
			}
		}
		return $this;
	}
}