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
		$url = '/' . ltrim($url, '/');
		$route = $this->provider->get($url);
		if ($route) {
			return $route ? new self($route) : null;
		}
        $configManager = $this->getService('configManager');
		$config = $configManager->get(__CLASS__);
		$emptyRoute = $config['empty_route']->__toArray();
		$routes = $this->getList();
		$row = null;
        $request = $this->getService('request');
        $host = $request->host();
        $lastWithHost = false;
		foreach ($routes as $route) {
			if (empty($route['route'])) {
				continue;
			}
			$route = array_merge($emptyRoute, $route);
			$pattern = '#^' . $route['route'] . '$#';
            $hostValid = true;
            $withHost = false;
            if (!empty($route['host'])) {
                $withHost = true;
                $hostValid = $this->checkHost($host['route'], $host);
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
		$this->provider->set($url, $row);
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

	/**
	 * Возвращает объект рендера для роутера
	 *
	 * @return View_Render_Abstract
	 */
	public function viewRender()
	{
        $render = null;
		if (!empty($this->params['View_Render__id'])) {
			$viewRenderId = $this->params['View_Render__id'];
            $modelManager = $this->getService('modelManager');
            $render = $modelManager->byKey('View_Render', $viewRenderId);
		} else {
            $viewRenderManager = $this->getService('viewRenderManager');
			$render = $viewRenderManager->getView();
		}
		return $render;
	}
}
