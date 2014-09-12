<?php

/**
 * Рендер данных для библиотеки JsHttpRequest
 * 
 * @author goorus
 */
class View_Render_JsHttpRequest extends View_Render_Abstract
{
	/**
	 * Экземпляр бекэнда.
	 * 
     * @var JsHttpRequest
	 */
	protected $request;

	/**
	 * @inheritdoc
	 */
	public function __construct()
	{
		IcEngine::getLoader()->load('JsHttpRequest', 'Vendor');
		$this->request = new JsHttpRequest('UTF-8');
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function fetch($tpl)
	{
		$result = $this->vars;
		$this->vars = array();
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display($tpl)
	{
		$GLOBALS['_RESULT'] = $this->vars;
		die();
	}

    /**
     * @inheritdoc
     */
	public function render(Controller_Task $task)
	{
		$buffer = $task->getTransaction()->buffer();
		$this->vars = $buffer;
		$this->display($task->getTemplate());
	}
}