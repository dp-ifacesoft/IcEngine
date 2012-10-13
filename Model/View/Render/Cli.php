<?php
/**
 * 
 * @desc Редер контроллера консоли
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class View_Render_Cli extends View_Render_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::addHelper()
	 */
	public function addHelper ($helper, $method)
	{
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display ($tpl)
	{
		$this->fetch ($tpl);
	}
	
	public function fetch ($tpl)
	{
		throw new Exception ('хД');
	}
	 
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function render (Controller_Task $task)
	{
		$transaction = $task->getTransaction ();
		$tasks = $transaction->receive ('tasks');
		
		foreach ($tasks as $t)
		{
			$render = $t->getViewRender ();
			$result = 
				$render 
				? $render->render ($t)
				: null;
			
			if (
				isset ($_SERVER ['OS']) && 
				strpos (strtoupper ($_SERVER ['OS']), 'WIN') !== false
			)
			{
				echo iconv (
					'UTF-8',
					'CP866',
					var_export ($t->getTransaction ()->buffer (), true)
				);
			}
			else
			{
				var_dump ($t->getTransaction ()->buffer ());
			}
			
			$this->assign ($t->getAssignVar (), $result);
		}
		
		$render = $tasks [0]->getViewRender ();
		
//		if ($render)
//		{
//			$render->assign ($this->_vars);
//			$render->display ($task->getTemplate ());
//		}
	}
	
}