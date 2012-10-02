<?php
/**
 * 
 * @desc Контроллер пагинатор.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Paginator extends Controller_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
	public function index ()
	{
		list (
			$paginator,
			$template,
			$tpl
		) = $this->_input->receive (
			'data',
			'template',
			'tpl'
		);

	    /* @var $paginator Paginator */
		$paginator->buildPages ();
		
		$this->_output->send ('paginator', $paginator);
		
		if ($template)
		{
			$this->_task->setTemplate ($template);
		}
		
		if ($tpl)
		{
			$this->_task->setClassTpl (__METHOD__, $tpl);
		}
	}
	
}