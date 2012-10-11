<?php

/**
 * Хлебные крошки
 *
 * @author morph
 */
class Bread_Crumb
{
	/**
	 * Список хлебных крошек
	 *
	 * @var array
	 */
	protected static $list = array();

	/**
	 * Добавить хлебную крошку
	 *
	 * @param string $title Текст ссылки
	 * @param string $url Href ссылки
	 */
	public static function append($title, $url)
	{
		self::$list[] = array(
			'url'	=> $url,
			'title'	=> $title
		);
	}

	/**
	 * Очистить хлебные крошки
	 */
	public static function clear()
	{
		self::$list = array();
	}

	/**
	 * Получить список хлебных крошек
	 *
	 * @return array
	 */
	public static function getList()
	{
		return self::$list;
	}

	/**
	 * Пустой ли стэк "хлебных крошек"
	 *
	 * @return boolean
	 */
	public static function isEmpty()
	{
		return empty(self::$list);
	}
}