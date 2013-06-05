<?php

/**
 * Роут
 *
 * @author goorus, morph
 * @Service("route")
 */
class Route extends Objective
{
    /**
     * Конфигурация
     * 
     * @var array
     */
    protected $config = array(
        'emptyRoute'    => array(
            'route'     => '',
            'params'    => array(
                'viewRender'   => 'Smarty'
            ),
            'patterns'  => array(),
            'actions'   => array(), 
            'weight'    => 0
        )
    );
    
	/**
	 * Загружены ли роуты из конфига
	 *
	 * @var boolean
	 */
	protected $fromConfigLoaded = false;

	/**
	 * Лист роутов
	 *
	 * @var array
	 */
	protected $list = array();
    
    /**
     * Провайдер для кэширования роутов
     * 
     * @Service(
     *      "routeCache",
     *      args={"Route_Cache"},
     *      source={
     *          name="dataProviderManager",
     *          method="get"
     *      }
     * )
     * @var Data_Provider_Abstract
     */
    protected $provider;

	/**
	 * Добавить роут
	 *
	 * @param array $route
	 */
	public function addRoute($route)
	{
		$this->list[] = $route;
	}

	/**
	 * Получить роут по урлу
	 *
	 * @param string $url
	 * @return Route
	 */
	public function byUrl($url)
	{
        if (is_null($url)) {
            return null;
        }
		$url = '/' . ltrim($url, '/');
        $request = $this->getService('request');
        $host = $request->host();
        $cacheKey = $host . $url;
		$route = $this->provider->get($cacheKey);
		if ($route) {
			return $route ? new self($route) : null;
		}
        $configManager = $this->getService('configManager');
        if (is_array($this->config)) {
            $this->config = $configManager->get(__CLASS__, $this->config);
        }
		$emptyRoute = $this->config['emptyRoute']->__toArray();
		$routes = $this->getList();
		$row = null;
        $lastWithHost = false;
		foreach ($routes as $route) {
			if (!is_array($route) || empty($route['route'])) {
				continue;
			}
            if (!is_array($route['actions'])) {
                $route['actions'] = (array) $route['actions'];
            }
			$route = array_merge($emptyRoute, (array) $route);
            if (!is_array($route)) {
                file_put_contents(
                    IcEngine::root() . 'log/route',
                    print_r($route, true) . PHP_EOL,
                    FILE_APPEND
                );
            }
			$pattern = '#^' . $route['route'] . '$#';
            $hostValid = true;
            $withHost = false;
            if (!empty($route['host'])) {
                $withHost = true;
                $hostValid = $this->checkHost($route['host'], $host);
            }
			if (!empty($route['patterns'])) {
				foreach ($route['patterns'] as $var => $routeData) {
					$replace = $routeData['pattern'];
					$var = '{$' . $var . '}';
					if (!empty($routeData['optional'])) {
						$replace = '(?:' . $replace . ')?';
					}
					$pattern = str_replace($var, $replace, $pattern);
				}
			}
            if (!isset($route['weight'])) {
                $route['weight'] = 0;
            }
			if (preg_match($pattern, $url) && (
				!$row || (int) $route['weight'] > (int) $row['weight'])) {
                if ($hostValid && !$lastWithHost) {
                    $row = array_merge($emptyRoute, $route);
                    $row['pattern'] = $pattern;
                    if ($withHost) {
                        $lastWithHost = true;
                    }
                }
			}
		}
		$this->provider->set($cacheKey, $row);
		return $row ? new self($row) : null;
	}

    /**
     * Проверить хост на соответствие шаблону
     *
     * @param string $pattern
     * @param string $host
     * @return boolean
     */
    protected function checkHost($pattern, $host)
    {
        if (!$pattern) {
            return true;
        }
        return preg_match($pattern, $host);
    }

	/**
	 * Получить список роутов
	 *
	 * @return array
	 */
	public function getList()
	{
		if (!$this->fromConfigLoaded) {
			$configManager = $this->getService('configManager');
			$config = $configManager->get(__CLASS__);
			$this->list = array_merge(
				$config['routes']->__toArray(), $this->list
			);
			$this->fromConfigLoaded = true;
		}
		return $this->list;
	}
    
    /**
     * Получить сервис по имени
     * 
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
}   