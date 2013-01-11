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
		$this->route = $route;
		if (!$this->route || !isset($this->route['route'])) {
			return;
		}
		if (!empty($route['params'])) {
			foreach ($route['params'] as $paramName => $paramValue) {
                if (is_string($paramValue) && strpos($paramValue, '::') !== false) {
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
		$firstParamPos = strpos($route['route'], '{');
		if ($firstParamPos !== false && isset($route['patterns']) &&
			isset($route['pattern'])) {
			$baseMatches = array();
			preg_match_all($route['pattern'], $url, $baseMatches);
			if (!empty($baseMatches[0][0])) {
				$keys = array_keys($route['patterns']);
				foreach ($baseMatches as $i => $data) {
					if (!$i) {
						continue;
					}
					if (!empty($data[0])) {
						$request->param($keys[$i - 1], $data[0]);
					} else {
						$part = $route['patterns'][$keys[$i - 1]];
						if (isset($part['default'])) {
							$request->param($keys[$i - 1], $part['default']);
						}
					}
				}
			}
		}
		$this->setParamsFromRequest();
		return $route;
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