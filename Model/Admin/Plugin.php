<?php

/**
 * Модель плагина для админки
 * 
 * @author neon
 */
class Admin_Plugin extends Model
{
	/**
	 * Получить список по типу
	 * @param array $pluginsConfig
	 * @return array
	 */
	public static function get($pluginsConfig)
	{
		$result = array(
			'General' => array(),
			'Element' => array()
		);
		foreach($pluginsConfig as $pluginType=>$plugins)
		{
			//hash ?
			$throughId = 0;
			if(!empty($plugins) && is_object($plugins))
			{
				foreach($plugins as $call=>$plugin)
				{
					if(($posSlash = strpos($call, '/')) && $posSlash !== false)
					{
						$action = substr($call, $posSlash + 1);
						$controller = substr($call, 0, $posSlash);
					}
					else
					{
						$controller = $call;
						$action = 'index';
					}

					$result[$pluginType][] = array(
						'Controller'	=>	$controller,
						'Action'		=>	$action,
						'call'			=>	$controller .
							DIRECTORY_SEPARATOR . $action,
						'pluginId'		=>	++$throughId,
						'params'		=>	$plugin
					);
				}
			}
		}
		return $result;
	}
}
