<?php
/**
 * 
 * @desc Абстрактный класс фильтра
 * @author Юрий
 * @package IcEngine
 *
 */
abstract class Filter_Abstract
{
	
	/**
	 * @desc Обычная фильтрация.
	 * @param string $data
	 * @return string
	 */
	public function filter ($data)
	{
		return $data;
	}
	
	/**
	 * @desc Фильтрация с использование схемы
	 * @param string $field Имя поля.
	 * @param stdClass $data Все данные.
	 * @param stdClass|Objective $scheme Схема.
	 */
	public function filterEx ($field, $data, $scheme)
	{
		return $this->filter ($data->$field);
	}
	
}