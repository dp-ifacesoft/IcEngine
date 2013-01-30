<?php

Loader::load("Helper_Diff");
Loader::load("Helper_Diff_Renderer");

class Controller_Diff_Renderer extends Controller_Abstract
{
	public function index()
	{
		$renderer = $this->_input->receive("renderer");
		$params = $this->_input->receiveAll();
		$this->_output->send($params);
	}
}