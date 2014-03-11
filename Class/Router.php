<?php

/**
 * Механизм определения роута по адресу
 *
 * @author goorus, morph
 */
class Router
{
	/**
	 * Текущий роут
	 *
	 * @var Route
	 */
	private static $route;

	/**
	 * Обнулить текущий роут
	 */
	public static function clearRoute()
	{
		self::$route = null;
	}

	/**
	 * Разбирает запрос и извлекат параметры согласно
	 *
	 * @return Route
	 */
	public static function getRoute()
	{
		if (!is_null(self::$route)) {
			return self::$route;
		}
		$url = Request::uri();
		$route = Route::byUrl($url);
		self::$route = $route;
		if (!self::$route || !isset(self::$route['route'])) {
			return;
		}
		if (!empty($route['params'])) {
			foreach ($route['params'] as $paramName => $paramValue) {
                if (is_string($paramValue) && strpos($paramValue, '::')) {
                    $paramValue = call_user_func($paramValue);
                }
				Request::param($paramName, $paramValue);
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
						Request::param($keys[$i - 1], $data[0]);
					} else {
						$part = $route['patterns'][$keys[$i - 1]];
						if (isset($part['default'])) {
							Request::param($keys[$i - 1], $part['default']);
						}
					}
				}
			}
		}
		self::setParamsFromRequest();
		return $route;
	}

	/**
	 * Отдать в $_REQUEST то, что прилетело из get
	 */
	public static function setParamsFromRequest()
	{
		$gets = Request::stringGet();
		if ($gets) {
			$gets = (array) explode('&', $gets);
			foreach ($gets as $get) {
				$tmp = explode('=', $get);
				if (!isset($tmp[1])) {
					$tmp[1] = 1;
				}
				$_REQUEST [$tmp[0]] = $_GET [$tmp[0]] = $tmp[1];
				Request::param($tmp[0], $tmp[1]);
			}
		}
	}

}
