<?php

/**
 * Хлебные крошки
 *
 * @author morph
 * @Service("breadCrumb")
 */
class Bread_Crumb
{
	/**
	 * Список хлебных крошек
	 *
	 * @var array
	 */
	protected $list = array();
    
    /**
     * Html справа от крошек
     * @var string 
     */
    protected $rightHtml = '';

	/**
	 * Добавить хлебную крошку
	 *
	 * @param string $title Текст ссылки
	 * @param string $url Href ссылки
     * @return  Bread_Crumb для цепных вызовов
	 */
	public function append($title, $url = null)
	{
		$this->list[] = array(
			'url'	=> $url,
			'title'	=> $title
		);
        return $this;
	}

	/**
	 * Очистить хлебные крошки
     * @return  Bread_Crumb  для цепных вызовов
	 */
	public function clear()
	{
		$this->list = array();
        $this->rightHtml = '';
        return $this;
	}

	/**
	 * Получить список хлебных крошек
	 *
	 * @return array
	 */
	public function getList()
	{
		return $this->list;
	}

	/**
	 * Пустой ли стэк "хлебных крошек"
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		return empty($this->list);
	}
    
    /**
     * Html справа от крошек(например, кнопка)
     */
    public function setRightHtml($html)
    {
        $this->rightHtml = $html;
    }
    
    /**
     * Html справа от крошек(например, кнопка)
     */
    public function getRightHtml()
    {
        return $this->rightHtml;
    }
}