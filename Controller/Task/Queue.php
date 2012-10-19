<?php

class Controller_Task_Queue extends Controller_Abstract
{
	public function index ()
	{
		for (;;)
		{
			Background_Agent_Manager::instance ()->process ('Task');

			sleep (1);
		}
	}
}