<?php

/**
 * Менеджер провайдеров данных. По переданному названию создает и 
 * возвращает соответсвующего провайдера.
 * 
 * @author goorus, morph
 * @Service("dataProviderManager")
 */
class Data_Provider_Manager extends Manager_Abstract
{
    /**
	 * @inheritdoc
	 */
	protected $config = array();
    
	/**
	 * Загруженные провайдеры.
	 * 
     * @var array <Data_Provider_Abstract>
	 */
	protected $providers = array();

	/**
	 * Возвращает провайдера.
	 * 
     * @param string $name Название провайдера в конфиге.
	 * @return Data_Provider_Abstract
	 */
	public function get($name) 
    {
		if (isset($this->providers[$name])) {
			return $this->providers[$name];
		}
        $config = $this->config();
        $providerConfig = $config[$name];
        $params = null;
        $providerName = $name;
		if ($providerConfig && $providerConfig['provider']) {
			$providerName = $providerConfig['provider'];
			$params = $providerConfig['params'];
		}
		$className = 'Data_Provider_' . $providerName;
		$provider = new $className($params);
        $this->providers[$name] = $provider;
		return $provider;
	}
    
    /**
     * Изменить провайдера по имени
     * 
     * @param string $name
     * @param Data_Provider_Abstract $provider
     */
    public function set($name, $provider)
    {
        $this->providers[$name] = $provider;
    }
}