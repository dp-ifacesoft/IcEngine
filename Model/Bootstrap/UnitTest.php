<?php
/**
 * 
 * @desc 
 * @author Юрий Шведов
 * 
 */
class Bootstrap_UnitTest extends Bootstrap_Abstract
{
	
	/**
	 * @desc 
	 * @param string $path 
	 */
	public function __construct ()
	{
		parent::__construct (__FILE__);
	}
	
	/**
	 * @desc Запускает загрузчик.
	 */
	public function _run ()
	{
		$this->addLoaderPathes ();
		
		Loader::load ('Config_Manager');
		Loader::Load ('Helper_Site_Location');
		Loader::load ('Zend_Exception');
		Loader::load ('Registry');
		Loader::load ('Request');
		
		Loader::load ('Manager_Abstract');
		
		$this->initMessageQueue ();
		
		$this->initDds ();
		
		Loader::multiLoad (
			'Executor',
			'Helper_Action',
			'Helper_Date',
			'Helper_Link',
			'Model',
			'Model_Child',
			'Model_Content',
			'Model_Component',
			'Model_Collection',
			'Model_Defined',
			'Model_Factory',
			'Model_Factory_Delegate',
			'Component',
			'Controller_Abstract',
			'Controller_Front',
			'Controller_Manager',
			'Router',
			'View_Render',
			'View_Render_Manager',
			'View_Helper_Abstract',
			'Authorization_Abstract'
		);
		
		$this->initAttributeManager ();
		
		$this->initModelScheme ('Vipgeo');
		
		$this->initModelManager ();
		
		$this->initAcl ();
		
		$this->initUser ();
		
		$this->initView ();
	}
	
}