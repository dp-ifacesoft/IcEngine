<?php
/**
 * 
 * @desc Абстрактный класс валидатора
 * @author Юрий
 * @package IcEngine
 *
 */
abstract class Data_Validator_Abstract 
{
	
	const INVALID = 'invalid';
	
	/**
	 * @desc Валидация строки
	 * @param string $data Данные.
	 * @return true|string
	 * 		true, если данные прошли валидацию или 
	 * 		строка ошибки.
	 */
	public function validate ($data)
	{
		return true;
	}
	
	/**
	 * @desc Валидация поля с использованием схемы
	 * @param string $field
	 * 		Название поля.
	 * @param stdClass $data
	 * 		Все данные.
	 * @param stdClass|Objective $scheme
	 * 		Схема.
	 * @return true|string
	 * 		true, если данные прошли валидацию или
	 * 		строка ошибки.
	 */
	public function validateEx ($field, $data, $scheme)
	{
		return
			$this->validate ($data->$field) === true ? 
			true : get_class ($this) . '/' . self::INVALID;
	}
	
}