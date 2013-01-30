<?php

class Controller_Terminate extends Controller_Abstract
{
	public function _beforeAction ($action)
	{
		
	}
	
	/**
	 * @desc Вызывает die ().
	 */
	public function now ()
	{
		die ();
	}
	
}