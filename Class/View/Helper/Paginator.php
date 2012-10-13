<?php
/**
 * 
 * @desc Вывод страниц
 * @author Юрий Шведов
 * @tutorial
 * {Paginator data=$collection->getPaginator() tpl="index"}
 *
 */
class View_Helper_Paginator extends View_Helper_Abstract
{
	
	/**
	 * 
	 * @param array $params
	 * 		Параметры, переданные из шаблона.
	 * 		$params['data'] должен быть объектом типа Paginator
	 * @return string
	 */
	public function get (array $params)
	{
	    /**
		 * @var Paginator $paginator
		 */
		$paginator = $params ['data'];
		
		$paginator->buildPages ();
		
		$this->_view->assign ('paginator', $paginator);
		
		$template = 'Widget/Paginator/index';
		
		if (isset ($params ['template']))
		{
			$template = $params ['tempalte'];
		}
		
		if (isset ($params ['tpl']))
		{
			$template = 'Widget/Paginator/' . $params ['tpl'] . '.tpl';
		}
		
		return $this->_view->fetch ($template);
	}
	
}
