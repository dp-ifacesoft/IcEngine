<?php
/**
 * 
 * @desc Асбтрактный класс кэшера смарти.
 * @author Юрий Шведов
 *
 */
class View_Render_Smarty_Cacher_Abstract
{
	
	const CACHE_PROVIDER = 'smarty_cache';
	
	/**
	 * @desc 
	 * @var Data_Provider_Abstract
	 */
	protected $_provider;
	
	/**
	 * @desc Экзмепляр Smarty.
	 * @var Smarty
	 */
	protected $_smarty;
	
	/**
	 * @desc Очистка кэша.
	 * @param string $key
	 * @param string $cache_id
	 * @param string $compile_id
	 * @param string $tpl_file
	 * @param string $cache_content
	 */
	public function cacheClear ($key, $cache_id, $compile_id, $tpl_file, 
		&$cache_content
	)
	{
		
	}
	
	/**
	 * @desc Метод, вызываемый из смарти.
	 * @param string $action read|write|clear Операция.
	 * @param Smarty $smarty_obj Экзмепляр смарти.
	 * @param string $cache_content The cached content. Upon a write, 
	 * Smarty passes the cached content in these parameters. Upon a read,
	 * Smarty expects your function to accept this parameter by reference
	 * and populate it with the cached data. Upon a clear, pass a dummy
	 * variable here since it is not used. 
	 * @param string $tpl_file Исходный файл шаблона.
	 * @param string $cache_id 
	 * @param string $compile_id
	 * @param integer $exp_time
	 */
	public function cacheHalderFunc ($action, &$smarty_obj, &$cache_content, 
		$tpl_file = null, $cache_id = null, $compile_id = null, 
		$exp_time = null
	)
	{
		// create unique cache id
		$key = md5 ($tpl_file . $cache_id . $compile_id);
		$method = 'cache' . strtoupper ($action [0]) . substr ($action, 1);
		return $this->$method ($key, $cache_content);
	}
	
	/**
	 * @desc Получение кэша.
	 * @param string $key
	 * @param string $cache_id
	 * @param string $compile_id
	 * @param string $tpl_file
	 * @param string $cache_content
	 */
	public function cacheRead ($key, $cache_id, $compile_id, $tpl_file, 
		&$cache_content
	)
	{
		
	}
	
	/**
	 * @desc Запись в кэш скомпилированного шаблона.
	 * @param string $key
	 * @param string $cache_id
	 * @param string $compile_id
	 * @param string $tpl_file
	 * @param string $cache_content
	 */
	public function cacheWrite ($key, $cache_id, $compile_id, $tpl_file, 
		&$cache_content
	)
	{
		
	}
	
	/**
	 * @desc Инициализация и подключение кэшера.
	 * @param View_Render_Smarty $render
	 */
	public function init (View_Render_Smarty $render)
	{
		$this->_provider = Data_Provider_Manager::get (self::CACHE_PROVIDER);
		$this->_smarty = $render->smarty ();
		$this->_smarty->cache_handler_func = array ($this, 'cacheHalderFunc');
	}
	
}