<?php

/**
 * Источник данных для функционального связывания
 *
 * @author morph
 */
class Data_Link_Source
{
	/**
	 * Алиас для источника данных
	 *
	 * @var string
	 */
	protected $alias;

	/**
	 * Данные
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Конструктор
	 *
	 * @param array $data
	 * @param string $alias
	 */
	public function __construct($data, $alias)
	{
		$this->alias = $alias;
		$this->data = $data;
	}

	/**
	 * Получить данные по ключу
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

	/**
	 * Получить алиас источника данных
	 *
	 * @return string
	 */
	public function getAlias()
	{
		return $this->alias;
	}

	/**
	 * Получить данные
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Изменить алиас источника данных
	 *
	 * @param string $alias
	 */
	public function setAlias($alias)
	{
		$this->alias = $alias;
	}

	/**
	 * Изменить данные
	 *
	 * @param array $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}
}