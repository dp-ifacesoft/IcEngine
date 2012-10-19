<?php


/**
 * @desc Агент для запуска gc
 * @author Илья Коленисков
 * @package IcEngine
 * @copyright i-complex.ru
 */
class Background_Agent_Collector_Garbage extends Background_Agent_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see Background_Agent_Abstract::_finish()
	 */
	public function _finish ()
	{

	}

	/**
	 * (non-PHPdoc)
	 * @see Background_Agent_Abstract::_process()
	 */
	public function _process ()
	{

	}

	/**
	 * (non-PHPdoc)
	 * @see Background_Agent_Abstract::_start()
	 */
	public function _start ()
	{
		if (empty ($this->_params ['name']))
		{
			return;
		}

		$name = $this->_params ['name'];

		$collector = Collector_Garbage_Manager::byName ($name);

		$collector->process ();
	}
}