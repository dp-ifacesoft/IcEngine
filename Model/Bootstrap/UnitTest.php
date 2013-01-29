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

		$this->initMessageQueue ();

		$this->initDds ();
		
		$this->initAttributeManager ();

		$this->initModelScheme ('Vipgeo');

		$this->initModelManager ();

		$this->initAcl ();

		$this->initUser ();

		$this->initView ();
	}

}