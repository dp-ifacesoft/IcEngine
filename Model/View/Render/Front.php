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
	protected static $config = array(
		// Render for layout
		'layout_render'	=> 'Smarty'
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
		$this->assign ($transaction->buffer());
		$tasks = $transaction->receive('tasks');
        if ($tasks) {
            foreach ($tasks as $t) {
                $render = $t->getViewRender();
                $result = $render->render($t);
                $this->assign($t->getAssignVar(), $result);
            }
        }
		$config = $this->config();
        $viewRenderManager = $this->getService('viewRenderManager');
		$render = $viewRenderManager->byName($config['layout_render']);
		$vars = $this->_vars;
        if (isset($vars['tasks'])) {
            unset($vars['tasks']);
        }
        $render->assign($vars);
		$render->display($task->getTemplate());
	}

}