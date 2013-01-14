<?php
/**
 * 
 * @desc Абстрактный класс для возобновления процессов.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Background_Agent_Resume_Abstract extends Model_Factory_Delegate
{
	
	/**
	 * @desc Возобновляет сессию.
	 * @param Background_Agent_Session $session
	 */
	abstract public function resume (Background_Agent_Session $session);
	
}