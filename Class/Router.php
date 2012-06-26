<?php
/**
 *
 * @desc Механизм определения роута по адресу
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Router
{

	/**
	 * @var Route
	 */
	private static $_route;

	/**
	 * @desc Обнулить текущий роут
	 */
	public static function clearRoute ()
	{
		self::$_route = null;
	}

	/**
	 * @desc Разбирает запрос и извлекат параметры согласно
	 * @return Route
	 */
	public static function getRoute ()
	{
		if (is_null (self::$_route)) {
			$url = Request::uri ();
            Loader::load ('Route');
			$url = $url ?: '/';
			self::$_route = Route::byUrl($url);
			if (!self::$_route) {
				return;
			}
			$tempVars = array();
			preg_match_all(
				'#' . self::$_route['pattern'] . '#u', $url, $tempVars
			);
			$vars = array();
			for ($i = 1, $count = sizeof($tempVars); $i <= $count; $i++) {
				if (empty($tempVars[$i][0])) {
					continue;
				}
				$vars[] = $tempVars[$i][0];
			}
			$parts = array();
			preg_match_all(
				'#(\:([\w\d]+)|{([\w\d]+)})#', self::$_route['route'], $parts
			);
			$parts = array_merge($parts[2], $parts[3]);
			for ($i = 1, $count = sizeof($parts); $i <= $count; $i++) {
				if (empty($parts[$i])) {
					unset($parts[$i]);
				}
			}
			$parts = array_values($parts);
			for ($i = 0, $count = sizeof($parts); $i < $count; $i++) {
				Request::param(
					$parts[$i],
					isset($vars[$i]) ? $vars[$i] : null
 				);
			}
			if (isset(self::$_route->params)) {
				foreach (self::$_route->params as $param => $value) {
					Request::param($param, $value);
				}
			}
			self::stringGet();
		}
		return self::$_route;
	}

	/**
	 * Отдать в $_REQUEST то, что прилетело из get
	 */
	public static function stringGet()
	{
		$gets = Request::stringGet();
		if ($gets) {
			$gets = (array) explode('&', $gets);
			foreach ($gets as $get) {
				$tmp = explode('=', $get);
				if (!isset($tmp[1])) {
					$tmp[1] = 1;
				}
				$_REQUEST [$tmp [0]] = $_GET [$tmp [0]] = $tmp[1];
			}
		}
	}

}
