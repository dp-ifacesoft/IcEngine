<?php

class Helper_Smarty
{

	/**
	 * Объект шаблонизатора
	 * @var Smarty
	 */
	protected static $_smarty;

	/**
	 *
	 * @var array
	 */
	public static $config = array (

		/**
		 * Директория для скопилированных шаблонов Smarty
		 * @var string
		 */
		'compile_dir'		=> 'cache/templates',

		'plugins_pahes'		=> array (

		),

		/**
		 * Директория Smarty
		 * @var string
		 */
		'smarty_class_path'	=> 'smarty/Smarty.class.php',

		/**
		 * Пути до шаблонов
		 * @var array
		 */
		'templates_pathes'	=> array ()
	);

	/**
	 * @return Smarty
	 */
	public static function newSmarty ()
	{
		if (
			class_exists ('Smarty') ||
			Loader::requireOnce (self::$config ['smarty_class_path'], 'includes')
		)
		{
			$smarty = new Smarty ();

			$compile_dir = self::$config ['compile_dir'];

			$smarty->compile_dir = rtrim ($compile_dir, '\\/') . '/';
			$smarty->template_dir = self::$config ['templates_pathes'];

			$smarty->plugins_dir = array (
				IcEngine::path () . 'includes/smarty_plugins/',
				Ice_Implementator::path () . 'includes/smarty_plugins/',
				IcSocialnet_Implementator::path () . 'includes/smarty_plugins/'
			);
		}
		
		Helper_Smarty_Filter_Dblbracer::register ($smarty);

		return $smarty;
	}

	/**
	 * @return Smarty
	 */
	public static function get ()
	{
		if (!self::$_smarty)
		{
			self::$_smarty = self::newSmarty ();
		}
		return self::$_smarty;
	}

}