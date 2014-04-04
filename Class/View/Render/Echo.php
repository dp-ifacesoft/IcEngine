<?php

/**
 * Рендер данных для echo-отображения их в поток вывода
 * 
 * @author morph
 */
class View_Render_Echo extends View_Render_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function fetch($tpl)
	{

	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display($tpl)
	{
        $buffer = IcEngine::getTask()->getTransaction()->buffer();
        if (empty($buffer['tasks'])) {
            return;
        }
        $task = reset($buffer['tasks']);
        $vars = $task->getTransaction()->buffer();
        if ($vars) {
            echo reset($vars);
        }
        die;
	}

    /**
     * @inheritdoc
     */
	public function render(Controller_Task $task)
	{
		$this->display(null);
	}
}