<?php
/**
 *
 * @desc Рендер данных для библиотеки JsHttpRequest
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class View_Render_JsHttpRequest extends View_Render_Abstract
{

	/**
	 * @desc Экземпляр бекэнда.
	 * @var JsHttpRequest
	 */
	protected $_request;

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::_afterConstruct()
	 */
	protected function _afterConstruct ()
	{
		Loader::load('JsHttpRequest', 'includes');
		$this->_request = new JsHttpRequest ('UTF-8');
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function fetch ($tpl)
	{
		$result = $this->_vars;
		$this->_vars = array ();
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display ($tpl)
	{
		$GLOBALS ['_RESULT'] = $this->_vars;
//		if (
//			count ($GLOBALS ['_RESULT']) == 1 &&
//			isset ($GLOBALS ['_RESULT']['content']) &&
//			is_array ($GLOBALS ['_RESULT']['content'])
//		)
//		{
//			$GLOBALS ['_RESULT'] = $GLOBALS ['_RESULT']['content'];
//		}
		die ();
	}

	public function render (Controller_Task $task)
	{
		$buffer = $task->getTransaction ()->buffer ();
		$this->_vars = $buffer;
		$this->display ($task->getTemplate ());
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::addHelper()
	 */
	public function addHelper ($helper, $method)
	{

	}

}