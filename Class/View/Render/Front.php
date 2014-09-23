<?php

/**
 * Редер фронт контроллера
 * 
 * @author goorus, morph
 */
class View_Render_Front extends View_Render_Abstract
{
	/**
	 * @inheritdoc
	 */
	protected $config = array(
		// Render for layout
		'layoutRender'	=> 'Smarty'
	);

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::addHelper()
	 */
	public function addHelper($helper, $method)
	{
        
	}

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
	public function fetch($tpl)
	{
		throw new Exception('Front controller render cannot fetches templates');
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function render(Controller_Task $task)
	{
		$transaction = $task->getTransaction();
        $tasks = array();
        if ($transaction) {
            $this->assign($transaction->buffer());
            $tasks = $transaction->receive('tasks');
        }
        if ($tasks) {
            foreach ($tasks as $currentTask) {
                $render = $currentTask->getViewRender();
                $result = $render->render($currentTask);
                $this->assign($currentTask->getAssignVar(), $result);
            }
        }
		$config = $this->config();
        $viewRenderManager = $this->getService('viewRenderManager');
		$render = $viewRenderManager->byName($config['layoutRender']);
		$vars = $this->vars;
        if (isset($vars['tasks'])) {
            unset($vars['tasks']);
        }
        $render->assign($vars);
		$render->display($task->getTemplate());
	}

}