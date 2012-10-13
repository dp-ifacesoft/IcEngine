<?php

class Controller_Task_Queue extends Controller_Abstract
{
	public function index ()
	{
		Loader::load ('Background_Agent_Manager');
		
		for (;;)
		{
			Background_Agent_Manager::instance ()->process ('Task');
			
			sleep (1);
		}
	}
}