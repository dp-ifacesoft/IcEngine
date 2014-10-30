<?php

/**
 * Агрегатор данных
 *
 * @author morph
 */
class Data_Link_Aggregator
{
	/**
	 * Колбэк
	 *
	 * @var mixed
	 */
	protected $callback;

	/**
	 * Ключ источника
	 *
	 * @var string
	 */
	protected $sourceKey;

	/**
	 * Ключ назначения
	 *
	 * @var string
	 */
	protected $targetKey;

	/**
	 * Конструктор
	 *
	 * @param string $sourceKey
	 * @param string $targetKey
	 * @param mixed $callback
	 */
	public function __construct($sourceKey, $targetKey, $callback = null) {
		$this->sourceKey = $sourceKey;
		$this->targetKey = $targetKey;
		$this->callback = $callback;
	}

	/**
	 * Получить колбэк
	 *
	 * @return mixed
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * Получить ключ источника
	 *
	 * @return string
	 */
	public function getSourceKey()
	{
		return $this->sourceKey;
	}

	/**
	 * Получить ключ назначения
	 *
	 * @return string
	 */
	public function getTargetKey()
	{
		return $this->targetKey;
	}

	/**
	 * Изменить колбэк
	 *
	 * @param mixed $callback
	 */
	public function setCallback($callback)
	{
		$this->callback = $callback;
	}

	/**
	 * Изменить ключ источника
	 *
	 * @param string $sourceKey
	 */
	public function setSourceKey($sourceKey)
	{
		$this->sourceKey = $sourceKey;
	}

	/**
	 * Изменить ключ назначения
	 *
	 * @param string $targetKey
	 */
	public function setTargetKey($targetKey)
	{
		$this->targetKey = $targetKey;
	}
}