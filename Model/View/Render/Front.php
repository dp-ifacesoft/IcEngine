<?php
/**
 * 
 * @desc Редер фронт контроллера
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class View_Render_Front extends View_Render_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		// Render for layout
		'layout_render'	=> 'Smarty'
	);
	
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
		$this->assign ($transaction->buffer ());
		$tasks = $transaction->receive ('tasks');
		
		foreach ($tasks as $t)
		{
			$render = $t->getViewRender ();
			$result = $render->render ($t);
			$this->assign ($t->getAssignVar (), $result);
		}
		
		$config = $this->config ();
		$render = View_Render_Manager::byName ($config ['layout_render']);
		
		$render->assign ($this->_vars);
		
		$render->display ($task->getTemplate ());
	}
	
}