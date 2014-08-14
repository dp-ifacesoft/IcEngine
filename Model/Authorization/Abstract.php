<?php
/**
 * 
 * @desc Абстрактный класс для авторизаций.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Authorization_Abstract extends Model_Factory_Delegate
{
	
	/**
	 * @desc Авторизация пользователя по данным с формы.
	 * @param array $data
	 * @return User|string Авторизованный пользователь или код ошибки.
	 */
	public abstract function authorize ($data);
	
	/**
	 * @desc Проверяет, зарегистрирован ли пользователь с таким логином.
	 * @param string $login
	 * @return boolean
	 */
	public abstract function isRegistered ($login);
	
	/**
	 * @desc Проверка логина на валидность.
	 * @param string $login
	 * @return boolean
	 */
	public abstract function isValidLogin ($login);
	
	/**
	 * @desc Находит пользователя по данным с формы авторизации.
	 * Авторизация не происходит.
	 * @param array $data Данные с формы
	 * @return User|null Найденный пользователь или null.
	 */
	public abstract function findUser ($data);
	
}