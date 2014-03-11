<?php
/**
 *
 * @desc Менеджер рендеринга
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
abstract class View_Render_Manager extends Manager_Abstract
{

	/**
	 * @desc Представления по имени.
	 * @var array <View_Render_Abstract>
	 */
	private static $_views = array ();

	/**
	 * @desc Стэк представлений.
	 * @var array <View_Render_Abstract>
	 */
	private static $_viewStack = array ();

	/**
	 *
	 * @var string
	 */
	private static $_templateExtension = '.tpl';

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Рендер по умолчанию
		 * @var string
		 */
		'default_view'		=> 'Smarty'
	);

	/**
	 * @desc Возвращает рендер по названию.
	 * @param string $name
	 * @return View_Render_Abstract
	 */
	public static function byName ($name)
	{
		if (isset (self::$_views [$name]))
		{
			return self::$_views [$name];
		}

		$view = Model_Manager::byQuery (
			'View_Render',
			Query::instance ()
				->where ('name', $name)
		);

		if (!$view)
		{
			$class_name = 'View_Render_' . $name;

			$view = new $class_name (array (
				'id'	=> null,
				'name'	=> $name
			));
		}

		return self::$_views [$name] = $view;
	}

	/**
	 * @desc Выводит результат работы шаблонизатора в браузер.
	 */
	public static function display ($tpl)
	{
		self::getView ()->display ($tpl);
	}

	/**
	 * @desc Возвращает текущий рендер.
	 * @return View_Render_Abstract
	 */
	public static function getView ()
	{
		if (!self::$_viewStack)
		{
			$config = self::config ();

			self::pushViewByName ($config ['default_view']);
			//self::$_view = new View_Render (array('name' => self::$_defaultView));
		}

		return end (self::$_viewStack);
	}

	/**
	 * @return string
	 */
	public static function getTemplateExtension ()
	{
		return self::$_templateExtension;
	}

	/**
	 * @return View_Render_Abstract
	 */
	public static function popView ()
	{
		$view = array_pop (self::$_viewStack);
		$view->popVars ();
		return $view;
	}

	/**
	 *
	 * @param View_Render_Abstract $view
	 * @return View_Render_Abstract
	 */
	public static function pushView (View_Render_Abstract $view)
	{
		self::$_viewStack [] = $view;
		$view->pushVars ();
		return $view;
	}

	/**
	 *
	 * @param integer $id
	 * @return View_Render_Abstract
	 */
	public static function pushViewById ($id)
	{
		$view = Model_Manager::byKey ('View_Render', $id);
		return self::pushView ($view);
	}

	/**
	 *
	 *
	 * @param string $name
	 * @return View_Render_Abstract
	 */
	public static function pushViewByName ($name)
	{
		$view = self::byName ($name);
		return self::pushView ($view);
	}

	/**
	 *
	 * @param string $value
	 */
	public static function setTemplateExtension ($value)
	{
		self::$_templateExtension = $value;
	}

	/**
	 * @desc Обработка шаблонов из стека.
	 * @param array $outputs
	 */
	public static function render (array $outputs)
	{
		return self::getView ()->render ($outputs);
	}

}
