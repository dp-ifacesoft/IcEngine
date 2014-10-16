<?php

/**
 * Опции запроса по умолчанию
 * 
 * @author goorus, morph
 */
class Query_Options extends Cache_Options
{
    /**
     * Текущие тэги запроса
     * 
     * @var array
     */
	private $tags;

	/**
	 * Запрос пуст?
     * 
	 * @var boolean
	 */
	private $notEmpty;

	/**
	 * Опции для некэширвования
     * 
	 * @var Query_Options
	 */
	private $nocache;

    /**
     * Конструктор
     */
	public function __construct()
	{
		$this->tags = array ();
		$this->notEmpty = false;
	}

	/**
	 * Узнать пуст ли запрос
     * 
	 * @return boolean
	 */
	public function getNotEmpty()
	{
		return $this->notEmpty;
	}

	/**
     * Поулчить тэги запроса
     * 
	 * @return array
	 */
	public function getTags()
	{
		return $this->tags;
	}

	/**
	 * Изменить тэги запроса
     * 
	 * @param array|string $tags
     * @return Query_Options
	 */
	public function setTags($tags)
	{
		$this->tags = (array)$tags;
		return $this;
	}

	/**
	 * Изменить флаг того, что запрос не пуст
     * 
	 * @param boolean $empty
	 */
	public function setNotEmpty($empty)
	{
		$this->notEmpty = !((bool) $empty);
	}

	/**
     * Получить новые пустые опции для некэширования
     * 
	 * @return Query_Options
	 */
	public function nocache()
	{
	    if (!$this->nocache) {
	        $this->nocache = new Query_Options();
	    }
	    return $this->nocache;
	}
}