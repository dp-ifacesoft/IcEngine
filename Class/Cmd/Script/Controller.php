<?php

class Cmd_Script_Controller extends Cmd_Script
{

    /**
     *
     * @var Data_Transport
     */
    protected $_input;

    /**
     *
     * @var Data_Transport
     */
    protected $_output;

    protected function _work (array $args)
    {
	    if (count ($args) < 3)
	    {
	        die ('Controller not received.');
	    }
	    if (count ($args) < 4)
	    {
	        die ('Action not received.');
	    }
	    $controller = Controller_Manager::get ($args [2]);
	    $action = $args [3];
	    $this->_input = new Data_Transport ();
	    $this->_output = $this->_input;
	    $this->_input->appendProvider (new Data_Provider_Console (
	        array_slice ($args, 4)
	    ));
	    $controller->setInput ($this->_input);
	    $controller->setOutput ($this->_input);
	    $controller->{$action} ();
    }

}