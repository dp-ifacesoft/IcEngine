<?php

/**
 * Провайдер данных из адресной строки
 *
 * @author goorus, morph
 */
class Data_Provider_Router extends Data_Provider_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function get($key, $plain = false)
	{
        $locator = IcEngine::serviceLocator();
        $request = $locator->getService('request');
		return $request->param($key);
	}

	/**
	 * @inheritdoc
	 */
	public function getAll()
	{
        $locator = IcEngine::serviceLocator();
        $request = $locator->getService('request');
		return $request->params();
	}

	/**
	 *@inheritdoc
	 */
	public function set($key, $value, $expiration = 0, $tags = array())
	{
        $locator = IcEngine::serviceLocator();
        $request = $locator->getService('request');
		$request->param($key, $value);
	}
}