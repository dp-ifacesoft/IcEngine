<?php

/**
 * Менеджер транспортов данных
 *
 * @author goorus, morph
 * @Service("dataTransportManager")
 */
class Data_Transport_Manager extends Manager_Abstract
{
	/**
	 * @inheritdoc
	 */
	protected $config = array(
		/**
		 * Транспорты
		 * 
         * @var array
		 */
		'transports'	=> array(
			'cliInput'		=> array(
				'providers'	=> array(
					'Cli'
				)
			),
			/**
			 * Транспорт входных данные по умолчанию
			 * 
             * @var array
			 */
			'defaultInput'	=> array(
				/**
				 * @desc Провайдеры, входящие в транспорт
				 * @var array
				 */
				'providers'	=> array(
					'Router',
					'Request'
				)
			),
			/**
			 * Транспорт исходящих данных
			 * 
             * @var array
			 */
			'defaultOutput'	=> array()
		)
	);

	/**
	 * Инициализированные транспорты.
	 *
     * @var array
	 */
	protected $transports = array();

	/**
	 * Получить конфигурация транспорта
     *
	 * @param string $name
	 * @return array
	 */
	public function configFor($name)
	{
        $config = $this->config();
        $transportConfig = $config->transports[$name];
		while (is_string($transportConfig)) {
			$transportConfig = $config->transports[$transportConfig];
		}
		return $transportConfig;
	}

	/**
	 * Получить транспорт по имени
     *
	 * @param string $name
	 * @return Data_Transport
	 */
	public function get($name)
	{
		if (isset($this->transports[$name])) {
			return $this->transports[$name];
		}
		$transportConfig = $this->configFor($name);
		$transport = new Data_Transport();
		if ($transportConfig && $transportConfig->providers) {
            $dataProviderManager = $this->getService('dataProviderManager');
			foreach ($transportConfig->providers as $provider) {
				$provider = $dataProviderManager->get($provider);
                $transport->appendProvider($provider);
			}
		}
        $dataFilterManager = $this->getService('dataFilterManager');
        if ($transportConfig && $transportConfig->inputFilters) {
            $inputFilters = array();
            foreach ($transportConfig->inputFilters as $filterName) {
                $inputFilters[] = $dataFilterManager->get($filterName);
            }
            $transport->setInputFilters($inputFilters);
        }
        $this->transorts[$name] = $transport;
		return $transport;
	}

    /**
     * Изменить транспорт по имени
     *
     * @param string $name
     * @param Data_Transport $transport
     */
    public function set($name, $transport)
    {
        $this->transports[$name] = $transport;
    }
}