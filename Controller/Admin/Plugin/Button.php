<?php

class Controller_Admin_Plugin_Button extends Controller_Abstract
{
	public function index($plugin)
	{
		$this->_output->send(array(
			'plugin'	=>	$plugin
		));
	}
}