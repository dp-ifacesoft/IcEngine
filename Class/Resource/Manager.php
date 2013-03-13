<?php

/**
 * Менеджер ресурсов
 * 
 * @author goorus, morph
 * @Service("resourceManager")
 */
class Resource_Manager extends Manager_Abstract
{
	/**
	 * Транспорты для ресурсов по типам.
	 * 
     * @var array
	 */
	protected $transports = array();

	/**
	 * Загруженные ресурсы
	 * 
     * @var array
	 */
	protected $resources = array();

	/**
	 * Обновленные в процессе ресурсы.
	 * Необходимо для предотвращения постоянной записи неизменяемых ресурсов.
	 * 
     * @var array <boolean>
	 */
	protected $updatedResources = array();

	/**
	 * @inheritdoc
	 */
	protected $config = array (
		/**
		 * @desc По умолчанию
		 * @var array
		 */
		'default'	=> array(),
		/**
		 * @desc Во избезажании рекурсивного вызова Config_Manager'a
		 * @var array
		 */
		'Resource_Manager'	=> array()
	);

	/**
	 * Возвращает транспорт согласно конфигу.
	 * 
     * @param Objective $conf
	 * @return Data_Transport
	 */
	protected function initTransport($config)
	{
		$transport = new Data_Transport();
		$providers = $config->providers;
        if (!$providers) {
            return $transport;
        }
        if (is_string($providers)) {
            $providers = array($providers);
        }
        $dataProviderManager = $this->getService('dataProviderManager');
        foreach ($providers as $name) {
            $provider = $dataProviderManager->get($name);
            $transport->appendProvider($provider);
        }
		return $transport;
	}

	/**
	 * @inheritdoc
	 */
	public function config()
	{
		if (is_array($this->config)) {
            $configManager = $this->getService('configManager');
			$this->config = $configManager->getReal(__CLASS__, $this->config);
		}
		return $this->config;
	}

	/**
	 * Возвращает Ресурс указанного типа по идентификатору.
	 * 
     * @param string $type Тип ресурса.
	 * @param string $name|array Идентификатор ресурса или ресурсов.
	 * @return mixed
	 */
	public function get($type, $name)
	{
        if (!isset($this->resources[$type])) {
            $this->resources[$type] = array();
        }
		if (!isset($this->resources[$type][$name])) {
            $resource = $this->transport($type)->receive($name);
			$this->resources[$type][$name] = $resource;
		}
		return $this->resources[$type][$name];
	}
    
    /**
     * Получить ресурсы по типу
     * 
     * @param string $type
     * @return integer
     */
    public function getByType($type)
    {
        return isset($this->resources[$type])
            ? $this->resources[$type] : array();
    }
    
	/**
	 * Получить обновленные ресурсы
	 * 
     * @param string $type
	 */
	public function getUpdated($type)
	{
		return $this->updatedResources[$type];
	}

	/**
	 * Слить объекты хранилища
	 */
	public function save()
	{
		foreach ($this->resources as $type => $resources) {
			foreach ($resources as $name => $resource) {
                if (!isset($this->updatedResources[$type],
                    $this->updatedResources[$type][$name])) {
                    continue;
                }
				$this->transport($type)->send($name, $resource);
			}
		}
	}

	/**
	 * Сохраняет ресурс
	 * 
     * @param string $type
	 * @param string $name
	 * @param mixed $resource
	 */
	public function set($type, $name, $resource)
	{
        if (!isset($this->updatedResources[$type])) {
            $this->updatedResources[$type] = array();
        }
        if (!isset($this->resources[$type])) {
            $this->resources[$type] = array();
        }
		$this->updatedResources[$type][$name] = true;
		if (Tracer::$enabled) {
			if ($type == 'Model') {
				if (!isset($this->resources[$type][$name])) {
					Tracer::incDeltaModelCount();
					Tracer::incTotalModelCount();
				}
			}
		}
		$this->resources[$type][$name] = $resource;
	}

    /**
     * Изменить ресурс по типу
     * 
     * @param string $type
     * @param array $resources
     */
    public function setResources($type, $resources)
    {
        $this->resources[$type] = $resources;
    }
    
	/**
	 * Обновить ресурс
	 * 
     * @param string $type
	 * @param string $name
	 * @param mixed $updated
	 */
	public function setUpdated($type, $name, $updated)
	{
        if (!isset($this->updatedResources[$type])) {
            $this->updatedResources[$type] = array();
        }
		$this->updatedResources[$type][$name] = $updated;
	}

	/**
	 * Возвращает транспорт для ресурсов указанного типа.
	 * 
     * @param string $type Тип ресурса.
	 * @return Data_Transport Транспорт данных.
	 */
	public function transport($type)
	{
		if (!isset($this->transports[$type])) {
			$config = $this->config()->$type ?: $this->config->default;
			$this->transports[$type] = $this->initTransport($config);
		}
		return $this->transports[$type];
	}
}