<?php

/**
 * Класс для автоматического подключения классов движка.
 * 
 * @author goorus
 * @Service("loaderAuto")
 */
class Loader_Auto
{
	/**
	 * Подключение автозагрузки классов
	 */
	public function register()
	{
        $loader = IcEngine::getLoader();
        $callable = array($loader, 'load');
		spl_autoload_register($callable);
	}
	
	/**
	 * Отключение автозагрузки классов
	 */
	public function unregister()
	{
        $loader = IcEngine::getLoader();
        $callable = array($loader, 'load');
		spl_autoload_unregister($callable);
	}
}