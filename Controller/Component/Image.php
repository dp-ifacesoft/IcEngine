<?php

class Controller_Component_Image extends Controller_Abstract
{
	public function adminPlugin ()
	{
		$image = $this->_input->receive ('row');

		$text = $row->attr ('text');
		$url = $row->attr ('smallUrl');
		
		$this->_output->send (array (
			'row'	=> $row,
			'text'	=> $text,
			'url'	=> $url
		));
	}

	public function setText ()
	{
		list (
			$id,
			$text
		) = $this->_input->receive (
			'id',
			'text'
		);

		$image = Model_Manager::byKey (
			'Component_Image',
			$id
		);

		$image->attr ('text', $text);

		$this->_task->setTemplate (null);
	}
}
