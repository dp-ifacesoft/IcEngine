<?php

class View_Render_Php extends View_Render_Abstract
{
	
	public function fetch ($tpl)
	{
		$result = '';
		foreach ($this->_templatesPathes as $path)
		{
			$file = $path . $tpl;
			if (file_exists($file))
			{
				ob_start();
				include $file;
				$result = ob_get_contents();
				ob_end_clean();
				break;	
			}
		}
		
		return $result;
	}
	
	public function display ($tpl)
	{
		foreach ($this->_templatesPathes as $path)
		{
			$file = $path . $tpl;
			if (file_exists ($file))
			{
				foreach ($this->_vars as $key => $value)
				{
					$$key = $value;
				}
				include $file;
				return;
			}
		}
	}
	
	public function addHelper ($helper, $method)
	{
		
	}
	
}