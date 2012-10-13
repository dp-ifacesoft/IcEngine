<?php
/**
 * 
 * @desc Контроллер для редиректа по коротким ссылкам.
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
class Controller_Tiny_Link extends Controller_Abstract
{
	
	/**
	 * @desc Редирект по короткой ссылке.
	 * @param string $short
	 */
	public function index ()
	{
		$short = $this->_input->receive ('short');
		Loader::load ('Tiny_Link');
		$link = Tiny_Link::byShort ($short);
		
		if ($link)
		{
			// Успешный редирект
			Loader::load ('Helper_Header');
			Helper_Header::redirect ($link->link);
			die ();
		}
		
		$this->replaceAction ('Error', 'notFound');
	}
	
}