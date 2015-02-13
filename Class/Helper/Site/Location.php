<?php

/**
 * Помощник для построения зависимостей от положения сайта
 *
 * @author goorus, morph
 * @Service("helperSiteLocation")
 */
class Helper_Site_Location extends Manager_Abstract
{
	/**
	 * Определение положения
	 *
     * @var string
	 */
	protected $location = null;

	/**
	 * @inheritdoc
	 */
	protected $config = array (
		'127.0.0.1'	=> array (
			'host'	=> 'localhost'
		)
	);

	/**
	 * Возвращает значение параметра для текущего положения
	 *
     * @param string $params
	 * @return mixed
	 */
	public function get($param)
	{
		$location = $this->getLocation();
        while (is_string($this->config[$location])) {
			$location = $this->config[$location];
		}
		if (strpos($param, '::') !== false) {
			list($location, $param) = explode('::', $param);
		}
		return $this->config[$location][$param];
	}

	/**
	 * Возвращает положение
	 *
     * @return string
	 */
	public function getLocation()
	{
		if (is_array($this->config)) {
			$this->load();
		}
		return $this->location;
	}

	/**
	 * Загрузка данных о положении из файла.
	 */
	public function load()
	{
		if (!$this->location) {
            $this->location = $this->getService('helperBehavior')->get();
			if (!$this->location) {
				$this->location = $_SERVER['HTTP_HOST'];
			}
		}
        $configManager = $this->getService('configManager');
        $this->config = $configManager->get(__CLASS__, $this->config);
	}

	/**
	 * Устанавливает положение.
	 *
     * @param string $value
	 */
	public function setLocation($value)
	{
		$this->location = $value;
	}
}