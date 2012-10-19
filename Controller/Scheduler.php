<?php

class Controller_Scheduler extends Controller_Abstract
{
	public function index ()
	{
		for (;;)
		{
			Background_Agent_Manager::instance ()->process ('Scheduler');

			sleep (1);
		}
	}
}