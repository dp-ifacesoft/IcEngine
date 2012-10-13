<?php

class Controller_Scheduler extends Controller_Abstract
{
	public function index ()
	{
		Loader::load ('Background_Agent_Manager');
		
		for (;;)
		{
			Background_Agent_Manager::instance ()->process ('Scheduler');
			
			sleep (1);
		}
	}
}