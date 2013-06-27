<?php

/**
 * Менеджер источников данных. По переданному названию загружает конфиг из
 * директории "{$config}/Data/Source/" и создает соответсвующего провайдера.
 *
 * @author goorus, morph
 * @Service("dataSourceManager")
 */
class Data_Source_Manager extends Manager_Abstract
{
	/**
	 * Загруженные источники.
	 *
     * @var array <Data_Source>
	 */
	protected $sources = array();

	/**
	 * @inheritdoc
	 */
	protected $config = array(
		/**
		 * Название источника, вместо которого будет браться название домена.
		 * Название домена берется из $SERVER ['HTTP_HOST'].
		 *
         * @var string
		 */
		'sourceDomainAlias'	=> 'domain',

		/**
		 * Название источника, который будет использован вместо
		 * имени домена, когда невозможно получить $SERVER ['HTTP_HOST'].
		 *
         * @var string
		 */
		'emptyDomainSource'	=> 'default',

		/**
		 * Массив источников
		 *
         * @var array
		 */
		'sources'	=> array(
			'default'	=> array(
				'driver'	=> 'Null',
				'options'	=> array(

				)
			)
		)
	);

	/**
	 * Получение данных провайдера.
	 *
     * @param string $name
	 * @return Data_Source
	 */
	public function get($name)
	{
		$config = $this->config();
		if ($config['sourceDomainAlias'] == $name) {
			$name = isset($_SERVER['HTTP_HOST'])
                ? $_SERVER['HTTP_HOST'] : $config['emptyDomainSource'];
		}
        if (isset($this->sources[$name])) {
            return $this->sources[$name];
        }
        $sourceConfig = $config['sources'][$name];
        if (!$sourceConfig) {
            $source = $this->getByPattern($name);
            if ($source) {
                $this->sources[$name] = $source;
                return $source;
            }
        } elseif (is_string($sourceConfig)) {
            $source = $this->get($sourceConfig);
            if (is_object($source)) {
                $this->sources[$name] = $source;
            }
            return $source;
        }
        if (!$sourceConfig) {
            $sourceConfig = $config['sources'][$config['emptyDomainSource']];
        }
        $source = new Data_Source();
        $source->setConfig($sourceConfig);
        $this->sources[$name] = $source;
        return $source;
	}

    /**
     * Получить имя источника данных по шаблону
     *
     * @param string $pattern
     * @return Data_Source
     */
    protected function getByPattern($pattern)
    {
        $config = $this->config();
        $sources = $config['sources'];
        foreach (array_keys($sources->__toArray()) as $key) {
            if (fnmatch($key, $pattern)) {
                return $this->get($key);
            }
        }
    }

    /**
     * Инициализация дата маппера
     *
     * @param Data_Source $dataSource
     */
    public function initDataDriver($source)
    {
        $sourceConfig = $source->getConfig();
        if (empty($sourceConfig['driver'])) {
            return null;
        }
        $driverManager = $this->getService('dataDriverManager');
        $driverConfig = isset($sourceConfig['options'])
            ? $sourceConfig['options'] : array();
        $currentDriver = $driverManager->get(
            $sourceConfig['driver'], $driverConfig
        );
        $source->setDataDriver($currentDriver);
    }

    /**
     * Изменить источник данных по имени
     *
     * @param string $name
     * @param Data_Source $dataSource
     */
    public function set($name, $dataSource)
    {
        $this->sources[$name] = $dataSource;
    }
}