<?php

/**
 *
 * @desc Позволяет держать шаблоны смарти в редисе
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class View_Render_Smarty_Cacher_Redis extends View_Render_Smarty_Cacher_Abstract
{

	/**
	 * @see View_Render_Smarty_Cacher_Abstract::_cacheClear
	 */
	public function cacheClear ($key, $cache_id, $compile_id, $tpl_file)
	{
		if (empty ($cache_id) && empty ($compile_id) && empty ($tpl_file))
		{
			// Полная очистка
			$this->_provider->deleteByPattern ('*');
		}
		else
		{
			$this->_provider->delete ($key);
		}
	}

	/**
	 * @see View_Render_Smarty_Cacher_Abstract::_cacheRead
	 */
	public function cacheRead ($key, $cache_id, $compile_id, $tpl_file,
		&$cache_content
	)
	{
		// read cache from database
		$data = $this->_provider->get ($key);

		if (!$data)
		{
			return ;
		}

		if ($data ['m'] && $data ['m'] != filemtime ($tpl_file))
		{
			return ;
		}

		$cache_content = $data ['d'];
	}

	/**
	 * @see View_Render_Smarty_Cacher_Abstract::_cacheWrite
	 */
	public function cacheWrite ($key, $cache_id, $compile_id, $tpl_file,
		&$cache_content
	)
	{
		$data = array (
			't'	=> time (),
			'm'	=> file_exists ($tpl_file) ? filemtime ($tpl_file) : 0,
			'd'	=> $cache_content
		);
		$this->_provider->set ($key, $data);
	}

}