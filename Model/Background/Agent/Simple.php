<?php

/**
 *
 * @desc Фоновый агент для расчета суммы чисел.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Background_Agent_Simple extends Background_Agent_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Background_Agent_Abstract::_start()
	 */
	protected function _start ()
	{
		$this->_params ['sum'] = 0;
		$max = isset ($this->_params ['max']) ? $this->_params ['max'] : 10;
		$this->_params ['max'] = max ($max, 1);
	}

	/**
	 * (non-PHPdoc)
	 * @see Background_Agent_Abstract::_process()
	 */
	protected function _process ()
	{
		$this->_params ['sum'] += $this->iteration;
		if ($this->_params ['max'] == $this->iteration)
		{
			$this->finish ();
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Background_Agent_Abstract::_finish()
	 */
	protected function _finish ()
	{
		$this->_log (__FILE__, __LINE__, 'Sum is ' . $this->_params ['sum']);
	}

}