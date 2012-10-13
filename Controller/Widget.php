<?php

class Controller_Widget extends Controller_Abstract
{
	
    const DEFAULT_METHOD = 'index';
    
	public function ajax ()
	{
		$widget = explode ('/', $this->_input->receive ('call'));
		
		$method = isset ($widget [1]) ? $widget [1] : self::DEFAULT_METHOD;
		$widget = $widget [0]; 
		
		$this->_output->send (array (
			'widget'	=> $widget,
			'back'		=> $this->_input->receive ('back')
		));
		
        $result = Widget_Manager::call (
            $widget,
            $method,
            (array) $this->_input->receive ('params'),
            false
        );
		
		$this->_output->send ('result', $result);
	}
	
	public function display ()
	{
		$widget = $this->_input->receive ('widget');
		$method = $this->_input->receive ('method');

		echo Widget_Manager::callUncached (
            $widget,
            $method ? $method : self::DEFAULT_METHOD,
            array (),
            true
        );
        
        die ();
	}
	
	public function returnError ($text)
	{
		$this->_output->send ('error', $text);
	}
	
}