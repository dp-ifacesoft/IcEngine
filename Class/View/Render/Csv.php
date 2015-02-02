<?php

/**
 * Рендер xml
 * 
 * @author nastya
 */
class View_Render_Csv extends View_Render_Abstract
{
    /**
     * @inheritdoc
     */
	public function fetch($tpl)
	{
        
	}

    /**
     * @inheritdoc
     */
	public function display($tpl)
	{
        header('Content-type: text/csv');
        $buffer = IcEngine::getTask()->getTransaction()->buffer();
        if (empty($buffer['tasks'])) {
            return;
        }
        $task = reset($buffer['tasks']);
        $vars = $task->getTransaction()->buffer();
        if ($vars) {
            echo array_pop($vars);
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