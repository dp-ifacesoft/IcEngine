<?php

/**
 * Фидеры данных
 *
 * @author morph
 */
class Data_Link_Feeder
{
	/**
	 * Источник данных
	 *
	 * @var Data_Link_Source
	 */
	protected $dataSources;

	/**
	 * Ключи
	 *
	 * @var array
	 */
	protected $keys;

	/**
	 * Конструктор
	 *
	 * @param array $keys
	 */
	public function __construct($keys)
	{
		$this->keys = (array) $keys;
	}

	/**
	 * Получить значение из источника данных по ключу
	 *
	 * @param Data_Link_Source$dataSource
	 * @param string $key
	 * @return mixed
	 */
	public function get($dataSource, $key)
	{
		return $dataSource->get($key);
	}

	/**
	 * Получить источник данных
	 *
	 * @return Data_Link_Source
	 */
	public function getDataSources()
	{
		return $this->dataSources;
	}

	/**
	 * Получить ключи
	 *
	 * @return array
	 */
	public function getKeys()
	{
		return $this->keys;
	}

	/**
	 * Изменить источник данных
	 *
	 * @param Data_Link_Source $dataSources
	 */
	public function setDataSources($dataSources)
	{
		$this->dataSources = $dataSources;
	}

	/**
	 * Изменить ключи
	 *
	 * @param array $keys
	 */
	public function setKeys($keys)
	{
		$this->keys = $keys;
	}
}