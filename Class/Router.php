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
			foreach ($hashRoute['params'] as $paramName => $paramValue) {
                if (is_string($paramValue) && strpos($paramValue, '::') 
                    !== false) {
                    list($className, $method) = explode('::', $paramValue);
                    $serviceName = $this->getServiceLocator()->normalizeName(
                        $className
                    );
                    $service = $this->getService($serviceName);
                    $paramValue = $service->$method();
                }
				$request->param($paramName, $paramValue);
			}
		}
		$firstParamPos = strpos($hashRoute['route'], '{');
		if ($firstParamPos !== false && isset($hashRoute['patterns']) &&
			isset($hashRoute['pattern'])) {
			$baseMatches = array();
			preg_match_all($hashRoute['pattern'], $url, $baseMatches);
			if (!empty($baseMatches[0][0])) {
				$keys = array_keys($hashRoute['patterns']);
				foreach ($baseMatches as $i => $data) {
					if (!$i) {
						continue;
					}
					if (!empty($data[0])) {
						$request->param($keys[$i - 1], $data[0]);
					} else {
						$part = $hashRoute['patterns'][$keys[$i - 1]];
						if (isset($part['default'])) {
							$request->param($keys[$i - 1], $part['default']);
						}
					}
				}
			}
		}
		$this->setParamsFromRequest();
		return $this->route;
	}

	/**
	 * Отдать в $_REQUEST то, что прилетело из get
	 */
	public function setParamsFromRequest()
	{
        $request = $this->getService('request');
		$gets = $request->stringGet();
		if ($gets) {
			$gets = (array) explode('&', $gets);
			foreach ($gets as $get) {
				$tmp = explode('=', $get);
				if (!isset($tmp[1])) {
					$tmp[1] = 1;
				}
				$_REQUEST[$tmp[0]] = $_GET [$tmp[0]] = $tmp[1];
				$request->param($tmp[0], $tmp[1]);
			}
		}
	}
}