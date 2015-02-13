<?php

/**
 * Рендер xml
 * 
 * @author markov
 */
class View_Render_Xml extends View_Render_Abstract
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
        header('Content-type: text/xml');
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