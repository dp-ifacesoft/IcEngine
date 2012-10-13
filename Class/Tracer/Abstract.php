<?php
/**
 * 
 * Абстрактный класс для ведения трейс лога.
 * @author Юрий
 *
 */
abstract class Tracer_Abstract
{
	
	/**
	 * 
	 * @param string $info
	 * @param string $_ [optional]
	 */
	abstract public function add ($info);
	
	/**
	 * @desc Фильтр вызовов
	 * @param string $filter
	 * @return array
	 */
	abstract public function filter ($filter);
	
	/**
	 * 
	 * @return array
	 */
	abstract public function full ();
	
}