<?php

class Controller_Subscribe_Abstract extends Controller_Abstract
{
	/**
	 *
	 * @var type
	 */
	protected $_backgroundAgent;

	/**
	 * @desc Получить имя рассылки
	 * @return string
	 */
	protected function _subscribeName ()
	{
		return substr (get_class ($this), strlen ('Controller_Subscribe') + 1);
	}

	/**
	 * @desc Запуск рассылки
	 */
	public function backgroundStart ()
	{
			Background_Agent_Manager::instance ()->startAgent (
					$this->_backgroundAgent,
					array (
							'Background_Agent_Resume__id'   => 0
					)
			);
	}

	/**
	 * @desc Продолжение процесса рассылки
	 */
	public function backgroundProcess ()
	{
			Background_Agent_Manager::instance ()->processAgent (
					$this->_backgroundAgent
			);
	}
}
