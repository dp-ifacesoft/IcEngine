<?php

/**
 * Контроллер плагинов
 */
class Controller_Admin_Plugin extends Controller_Abstract
{
	/**
	 * Кнопка с вызовом контроллера
	 */
	public function button()
	{
		$plugin = $this->_input->receive('plugin');
		$this->_output->send(array(
			'plugin'		=>	$plugin
		));
	}
}