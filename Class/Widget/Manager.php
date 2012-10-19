<?php
/**
 *
 * @desc Менеджер виджетов.
 * @author Юрий
 * @package IcEngine
 * @deprecated Следует использовать Controller_Manager
 *
 */
class Widget_Manager
{

	/**
	 * @desc Шаблон, не передающийся в рендер.
	 * @var string
	 */
	const NULL_TEMPLATE = 'NULL';

	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	public static $config = array (

		'widgets'	=> array ()

	);

	/**
	 * @desc Время работы последнего виджета
	 * @var float
	 */
	public static $lastWidgetTime;

	/**
	 * @desc Настройки кэширования для виджета
	 * @param string $widget
	 * @param string $method
	 * @return Objective
	 */
	protected static function _cacheConfig ($widget, $method)
	{
		if (is_array (self::$config))
		{
			self::$config = Config_Manager::get (__CLASS__, self::$config);
		}

		$config = self::$config->widgets [$widget . '::' . $method];
		return $config ? $config : self::$config->widgets [$widget];
	}

	/**
	 * @desc Получение виджета по названию.
	 * @param string $name Название виджета.
	 * @return Widget_Abstract Виджет.
	 */
	protected static function _get ($name)
	{
		$widget = Resource_Manager::get ('Widget', $name);

		if (!$widget)
		{
			$class = 'Widget_' . $name;
			$widget = new $class ();
			Resource_Manager::set ('Widget', $name, $widget);
		}

		if (!$widget)
		{
			throw new Zend_Exception ("Widget not found: $name.");
			return;
		}

		return $widget;
	}

	/**
	 * @desc Вызов виджета.
	 * @param string $name Название виджета.
	 * @param string $method Метод.
	 * @param boolean $html_only
	 * 		Вернуть только html.
	 * 		Если false, будет возвращен массив со всеми результатами.
	 * @param array $args Параметры.
	 * @return string|array
	 */
	public static function call ($name, $method = 'index',
		array $args = array (), $html_only = true)
	{
		$cache_config = self::_cacheConfig ($name, $method);

		return Executor::execute (
			array (__CLASS__, 'callUncached'),
			array ($name, $method, $args, $html_only),
			$cache_config
		);
	}

	/**
	 * @desc Вызов виджета без кэширования.
	 * @param string $name Название виджета.
	 * @param string $method Метод.
	 * @param array $args Параметры.
	 * @param boolean $html_only=true Вернуть только html.
	 */
	public static function callUncached ($name, $method = 'index',
		array $args = array (), $html_only = true)
	{
		$microtime = microtime (true);
		$widget = self::_get ($name);

		$widget->getInput ()->beginTransaction ()->send ($args);
		$widget->getOutput ()->beginTransaction ();

		$result = array (
			'return'	=> $widget->{$method} ()
		);

		$tpl = $widget->template ($method);

		$widget->getInput ()->endTransaction ();
		$output = $widget->getOutput ()->endTransaction ();

		$result ['data'] = (array) $output->receive ('data');

		if ($tpl && $tpl != self::NULL_TEMPLATE)
		{
			$view = View_Render_Manager::pushViewByName ('Smarty');

			try
			{
				$view->assign ($output->buffer ());
				$result ['html'] = $view->fetch ($tpl);
			}
			catch (Exception $e)
			{
				$msg =
					'[' . $e->getFile () . '@' .
					$e->getLine () . ':' .
					$e->getCode () . '] ' .
					$e->getMessage () . PHP_EOL;

				error_log (
					$msg . PHP_EOL .
					$e->getTraceAsString () . PHP_EOL,
					E_USER_ERROR, 3
				);

				$result ['error'] = 'Widget_Manager: Error in template.';
				$result ['html'] = '';
			}

			View_Render_Manager::popView ();
		}
		else
		{
			$result ['html'] = '';
		}

		self::$lastWidgetTime = microtime (true) - $microtime;

		return $html_only ? $result ['html'] : $result;
	}

	/**
	 * @desc Возвращает только html результат работы контроллера.
	 * Аналогично вызову метода call с html_only=true.
	 * @param string $method Название виджета или виджета и экшена.
	 * @param array $args Параметры.
	 * @return string Результат работы экшена.
	 * @tutorial
	 * 		html ('Widget', array ('param'	=> 'val'));
	 * 		html ('Widget/action')
	 */
	public static function html ($method, array $args = array ())
	{
		$w = explode ('/', $method);
		if (count ($w) == 1)
		{
			$w [1] = 'index';
		}

		$cache_config = self::_cacheConfig ($w [0], $w [1]);

		return Executor::execute (
			array (__CLASS__, 'callUncached'),
			array ($w [0], $w [1], $args, true),
			$cache_config
		);
	}

}