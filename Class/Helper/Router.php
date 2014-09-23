<?php

/**
 * Хелпер роутера
 * 
 * @author morph
 * @Service("helperRouter")
 */
class Helper_Router extends Helper_Abstract
{
    /**
	 * Отдать в $_REQUEST то, что прилетело из get
     * 
     * @param Request $request
	 */
	public function setParamsFromRequest($request)
	{
		$gets = $request->stringGet();
		if (!$gets) {
            return;
        }
        foreach ((array) explode('&', $gets) as $get) {
            $tmp = explode('=', $get);
            if (!isset($tmp[1])) {
                $tmp[1] = 1;
            }
            $_REQUEST[$tmp[0]] = $_GET[$tmp[0]] = $tmp[1];
            $request->param($tmp[0], $tmp[1]);
        }
	}
    
    /**
     * Установить значение роута
     * 
     * @param string $url
     * @param Request $request
     * @param array $hashRoute
     */
    public function setRouteData($url, $request, $hashRoute)
    {
        $baseMatches = array();
        preg_match_all($hashRoute['pattern'], $url, $baseMatches);
        if (empty($baseMatches[0][0])) {
            return;
        }
        $keys = array_keys($hashRoute['patterns']);
        foreach ($baseMatches as $i => $data) {
            if (!$i) {
                continue;
            }
            $part = $hashRoute['patterns'][$keys[$i - 1]];
            if (!empty($data[0])) {
                if (isset($part['value'])) {
                    $data[0] = $part['value'];
                }
                $request->param($keys[$i - 1], $data[0]);
            } elseif (isset($part['default'])) {
                $request->param($keys[$i - 1], $part['default']);
            }
        }
    }
    
    /**
     * Изменить параметры из роутов
     * 
     * @param Request $request
     * @param array $params
     */
    public function setRouteParams($request, $params)
    {
        foreach ($params as $paramName => $paramValue) {
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
}