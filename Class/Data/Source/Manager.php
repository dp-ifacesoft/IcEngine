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
     * @var array <Data_Source_Abstract>
	 */
	protected $sources = array();

	/**
	 * @inheritdoc
	 */
	protected $config = array(
		/**
		 * @desc Название источника, вместо которого будет браться название домена.
		 * Название домена берется из $SERVER ['HTTP_HOST'].
		 * @var string
		 */
		'source_domain_alias'	=> 'domain',
		/**
		 * @desc Название источника, который будет использован вместо
		 * имени домена, когда невозможно получить $SERVER ['HTTP_HOST'].
		 * @var string
		 */
		'empty_domain_source'	=> 'default',
		/**
		 * @desc Массив источников
		 * @var array
		 */
		'sources'	=> array(
			'default'	=> array(
				'source'	=> 'Abstract',
				'mapper'	=> 'Null',
				'mapper_options'	=> array(

				)
			)
		)
	);

	/**
	 * Получение данных провайдера.
	 *
     * @param string $name
	 * @return Data_Source_Abstract
	 */
	public function get($name)
	{
		$config = $this->config();
		if ($config['source_domain_alias'] == $name) {
			$name = isset ($_SERVER['HTTP_HOST'])
                ? $_SERVER['HTTP_HOST'] : $config['empty_domain_source'];
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
            $sourceConfig = array('source' => $name);
        }
        $sourceClass = 'Data_Source_' . $sourceConfig['source'];
        $source = new $sourceClass;
        $source->setConfig($sourceConfig);
        $this->sources[$name] = $source;
        return $source;
	}

    /**
     * Получить имя источника данных по шаблону
     *
     * @param string $pattern
     * @return Data_Source_Abstract
     */
    protected function getByPattern($pattern)
    {
        $config = $this->config;
        $sources = $config['sources'];
        foreach ($sources as $key => $value) {
            if (fnmatch ($key, $pattern)) {
                return $this->get($key);
            }
        }
    }

    /**
     * Изменить источник данных по имени
     *
     * @param string $name
     * @param Data_Sourec_Abstract $dataSource
     */
    public function set($name, $dataSource)
    {
        $this->sources[$name] = $dataSource;
    }

    /**
     * Инициализация дата маппера
     *
     * @param Data_Source_Abstract $dataSource
     */
    public function setDataMapper($source)
    {
        $sourceConfig = $source->getConfig();
        if (empty($sourceConfig['mapper'])) {
            return;
        }
        $mapperManager = $this->getService('dataMapperManager');
        $mapperConfig = isset($sourceConfig['mapper_options'])
            ? $sourceConfig['mapper_options'] : array();
        $currentMapper = $mapperManager->get(
            $sourceConfig['mapper'], $mapperConfig
        );
        $source->setDataMapper($currentMapper);
    }
}