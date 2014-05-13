<?php

/**
 * Хелпер для описания состояния процесса.
 * 
 * @author goorus, morph
 */
class Helper_Process
{
	/**
	 * @desc Пустой статус
	 * @var integer
	 */
	const NONE = 0;
	
	/**
	 * @desc Выполняется
	 * @var integer
	 */
	const ONGOING = 1;
	
	/**
	 * @desc Прервано из-за ошибки
	 * @var integer
	 */
	const FAIL = 2;
	
	/**
	 * @desc Успешно выполнено
	 * @var integer
	 */
	const SUCCESS = 3;
	
	/**
	 * @desc Временно остановлено
	 * @var integer
	 */
	const PAUSE = 4;
	
	/**
	 * @desc Остановлен
	 * @var integer
	 */
	const STOPED = 5;
	
}