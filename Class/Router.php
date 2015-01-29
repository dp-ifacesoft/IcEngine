<?php

/**
 * Механизм определения роута по адресу
 *
 * @author goorus, morph
 * @Service("router")
 */
class Router extends Manager_Abstract
{
    /**
     * Хелпер роутера
     * 
     * @var Helper_Router
     * @Inject("helperRouter")
     */
    protected $helper;
    
	/**
	 * Текущий роут
	 *
	 * @var Route
	 */
	private $route;

	/**
	 * Обнулить текущий роут
	 */
	public function clearRoute()
	{
		$this->route = null;
	}

	/**
	 * Разбирает запрос и извлекат параметры согласно
	 *
	 * @return Route
	 */
	public function getRoute()
	{
		if (!is_null($this->route)) {
			return $this->route;
		}
        $request = $this->getService('request');
		$url = $request->uri();
		$route =  $this->getService('route')->byUrl($url);
		if (!$route || !isset($route['route'])) {
			return;
		}
        $this->route = $route;
        $hashRoute = $route->__toArray();
		if (!empty($hashRoute['params'])) {
			$this->helper->setRouteParams($request, $hashRoute['params']);
		}
		$firstParamPos = strpos($hashRoute['route'], '{');
		if ($firstParamPos !== false && isset($hashRoute['patterns']) &&
			isset($hashRoute['pattern'])) {
			$this->helper->setRouteData($url, $request, $hashRoute);
		}
		$this->helper->setParamsFromRequest($request);
		return $this->route;
	}

    /**
     * Изменить хелпер роутера
     * 
     * @param Helper_Router $helper
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;
    }
}