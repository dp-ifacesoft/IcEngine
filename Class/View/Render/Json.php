<?php

/**
 * Рендер json
 * 
 * @author markov
 */
class View_Render_Json extends View_Render_Abstract
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
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        $buffer = IcEngine::getTask()->getTransaction()->buffer();
        if (empty($buffer['tasks'])) {
            return;
        }
        $task = reset($buffer['tasks']);
        $vars = $task->getTransaction()->buffer();
        if ($vars) {
            echo json_encode($vars);
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