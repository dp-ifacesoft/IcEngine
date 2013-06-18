<?php

/**
 * Редер контроллера консоли
 * 
 * @author goorus, morph
 */
class View_Render_Cli extends View_Render_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display($tpl)
	{
		$this->fetch($tpl);
	}
	
    /**
     * @inheritdoc
     */
	public function fetch ($tpl)
	{
		
	}
	 
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function render(Controller_Task $task)
	{
		$tasks = $task->getTransaction()->buffer()['tasks'];
        foreach ($tasks as $task) {
            $task->getViewRender()->render($task);
        }
	}
	
}