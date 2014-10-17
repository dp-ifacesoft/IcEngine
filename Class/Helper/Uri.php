<?php

/**
 * Помощник работы с URI
 *
 * @author Гурус, neon
 * @Service("helperUri")
 */
class Helper_Uri
{
	/**
	 * Генерирует ссылку по роуту
	 *
	 * @param string $routeName
	 * @param array $params
	 * @return string
	 */
	public function fromRoute($routeName, $params = array())
	{
        $locator = IcEngine::serviceLocator();
        $routeService = $locator->getService('route');
		$route = $routeService->byName($routeName);
		if (!$route) {
			return;
		}
		if (empty($route['patterns'])) {
			return $route['route'];
		}
		$parts = $route['patterns'];
		$result = $route['route'];
		foreach ($parts as $partName => $part) {
			$paramPart = isset($params[$partName]) ? $params[$partName] : null;
			if (!$paramPart) {
				if (!empty($part['optional']) && isset($part['default'])) {
					$paramPart = $part['default'];
				}
			}
			if ($paramPart) {
				$paramPart = preg_replace(
					'#\(([^\)]+)#', $paramPart, $part['pattern']
				);
			}
			$result = str_replace(
				'{$' . $partName , '}', $paramPart, $result
			);
		}
		return $result;
	}

	/**
	 * Возвращает домен верхнего уровня
	 *
	 * @param string $server_name [optional]
	 * 		Адрес домена (www.vipgeo.ru, vipgeo.com, localhost)
	 * 		Если адрес домена не задан, будет использована
	 * 		переменная $_SERVER['SERVER_NAME']
	 * @return string
	 * 		Домен верхнего уровня (ru, com, localhost)
	 */
	public static function highDomain($server_name = null)
	{
		if (!$server_name) {
			$server_name = $_SERVER['SERVER_NAME'];
		}
		$p = strrchr($server_name, '.');
		if ($p === false) {
			return $server_name;
		}
		return strtolower(substr($p, 1));
	}

	/**
	 * Формирование URL с заданными GET параметрами
	 *
	 * @param array $gets
	 * 		Значения GET параметров, которые необходимо установить.
	 * 		Если параметр задан как null, то он не будет включен
	 * 		в формируемый URI
	 * @param boolean $clear
	 * 		Очистить адрес от старых GET параметров
	 * @param string $url
	 * 		Адрес страницы, возможно с GET параметрами.
	 * 		Если не указан, будет использовано $_SERVER ['REQUEST_URI']
	 * @return string
	 * 		Адрес страницы с заданными GET параметрами
	 */
	public static function replaceGets(array $gets = array(), $clear = false,
		$url = null)
	{
		if(is_null ($url)) {
			$url = $_SERVER['REQUEST_URI'];
		}
		// Удаляемые
		$deleting = array();
		// кодируем параметры и запоминаем удаляемые
		foreach ($gets as $k => &$v) {
			if (is_null ($v)) {
				$deleting[$k] = true;
				unset($gets[$k]);
			} else {
				$v = urlencode($k) . '=' . urlencode($v);
			}
		}
		unset($v);
		$p = strpos($url, '?');
		if ($p !== false) {
			// В url уже пристствуют GET параметры
			$get_part = substr($url, $p + 1);
			$url = substr($url, 0, $p);
			if (!$clear) {
				$get_part = explode('&', $get_part);
				foreach ($get_part as $get) {
					$p = strpos($get, '=');
					if ($p == false) {
						$k = $get;
						$v = '';
					} else {
						$k = substr($get, 0, $p);
						$v = substr($get, $p + 1);
					}
					if (!isset($gets[$k]) &&
                        !isset($deleting[$k]) && ($k || $v)) {
						$gets[$k] = $k . '=' . $v;
					}
				}
			}
		}
		if ($gets) {
			return $url . '?' . implode('&', $gets);
		}
		return $url;
	}

    /**
     * Получить основном домен
     *
     * @param string $server_name
     * @return string
     */
	public function mainDomain($server_name = null)
	{
		if (!$server_name) {
			$server_name = $_SERVER['SERVER_NAME'];
		}
		$f = strrpos($server_name, '.');
		if (!$f) {
			return $server_name;
		}
		$f = strlen($server_name) - $f;
		$s = strrpos($server_name, '.', - $f - 1);
		return ($s === false) ? $server_name : substr($server_name, $s + 1);
	}

	/**
	 * Возвращает полный адрес для редиректа.
     *
	 * @param string $uri Полный или относительный адрес редиректа.
	 * @return string Полный адрес перехода.
	 */
	public function validRedirect($uri)
	{
		if (empty($uri)) {
			return 'http://' . $_SERVER['HTTP_HOST'];
		}
		if (substr($uri, 0, 1) == '/') {
			return 'http://' . $_SERVER['HTTP_HOST'] . $uri;
		}
		return $uri;
	}

    /**
     * Получить поддомен
     *
     * @param string $host
     * @return string
     */
    public function subdomain($host = null)
    {
        if (!$host) {
			$host = $_SERVER['HTTP_HOST'];
		}
		$parts = strrchr($host, '.');
		if ($parts === false) {
			return $host;
		}
        $regexp = '#^([^\.]+)\.([^\.]+)\.(.*?)$#';
        $matches = array();
        preg_match_all($regexp, $host, $matches);
        if (empty($matches[1][0])) {
            return 'www';
        }
		return $matches[1][0];
    }
}
