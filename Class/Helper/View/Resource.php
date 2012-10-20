<?php

/**
 * Помощник упаковщика статических ресурсов
 *
 * @author morph
 */
class Helper_View_Resource
{
	/**
	 * Метка для css файлов
	 */
	const CSS = 'css';

	/**
	 * Метрка для js файлов
	 */
	const JS = 'js';

	/**
	 * Конфигурация
	 *
	 * @var array
	 */
	protected static $config = array(
		'cdn'			=> array(
			self::CSS	=> 'http://www.biokrasota.local/',
			self::JS		=> 'http://www.biokrasota.local/'
		),
		'packDelegates'	=> array(
			self::CSS	=> 'Helper_View_Resource_Css::pack',
			self::JS		=> 'Helper_View_Resource_Js::pack'
		),
		'packGroups'	=> array(
			self::CSS	=> '.css',
			self::JS		=> '.js'
		),
		'packTemplates'	=> array(
			self::CSS	=> '<link href="{$filename}" type="text/css" />',
			self::JS		=> '<script type="text/javascript" src="{$filename}"></script>'
		),
		'path'			=> 'cache/static/',
		'provider'		=> 'Static'
	);

	/**
	 * Добавленные файлы
	 *
	 * @var array
	 */
	protected static $files = array(
		self::CSS	=> array(),
		self::JS		=> array()
	);

	/**
	 * Добавляет файлы
	 *
	 * @param string $type
	 * @param string $filename
	 */
	public static function append($type, $filename)
	{
		array_push(self::$files[$type], $filename);
	}

	/**
	 * Добавить css файл
	 *
	 * @param string $filename
	 */
	public static function appendCss($filename)
	{
		self::append(self::CSS, $filename);
	}

	/**
	 * Добавляет js файлы
	 *
	 * @param string $filename
	 */
	public static function appendJs($filename)
	{
		self::append(self::JS, $filename);
	}

	/**
	 * Получить конфигурацию
	 *
	 * @return Config_Abstract
	 */
	public static function config()
	{
		if (is_array(self::$config)) {
			self::$config = Config_Manager::get(__CLASS__, self::$config);
		}
		return self::$config;
	}

	/**
	 * Упаковывает группу статики
	 *
	 * @param string $type
	 */
	public static function createPackFile($type)
	{
		if (!isset(self::$files[$type])) {
			return;
		}
		$config = self::config();
		$key = self::resourceKey();
		$root = IcEngine::root();
		$filename = $root . ltrim($config->path) .
			$key . (isset($config->packGroups[$type]) ?
			$config->packGroups[$type] : '');
		if (is_file($filename)) {
			return true;
		}
		$content = '';
		foreach (self::$files[$type] as $currentFilename) {
			$currentFilename = $root . ltrim($currentFilename, '/');
			if (!is_file($currentFilename)) {
				continue;
			}
			$fileContent = file_get_contents($currentFilename);
			$content .= self::pack($type, $currentFilename, $fileContent);
		}
		file_put_contents($filename, $content);
		return true;
	}

	/**
	 * Внедряет упакованные файл
	 *
	 * @param string $type
	 * @return string
	 */
	public static function embed($type)
	{
		$config = self::config();
		$key = self::resourceKey();
		$provider = Data_Provider_Manager::get($config->provider);
		$lastPackedAt = $provider->get($key);
		if (!$lastPackedAt) {
			$lastPackedAt = time();
			$provider->set($key, $lastPackedAt);
		}
		if (self::createPackFile($type)) {
			$filename = rtrim($config->cdn[$type], '/') . '/' .
				ltrim($config->path) . $key .
				(isset($config->packGroups[$type]) ?
				$config->packGroups[$type] : '') . '?' . $lastPackedAt;
			$html = str_replace(
				'{$filename}', $filename, $config->packTemplates[$type]
			);
			return $html;
		}
	}

	/**
	 * Внедряет упакованный css файл
	 *
	 * @return string
	 */
	public static function embedCss()
	{
		return self::embed(self::CSS);
	}

	/**
	 * Внедряет упакованный js файл
	 *
	 * @return string
	 */
	public static function embedJs()
	{
		return self::embed(self::JS);
	}

	/**
	 * Упаковывает файл статики
	 *
	 * @param string $type
	 * @param string $filename
	 * @param string $content
	 * @return string
	 */
	public static function pack($type, $filename, $content)
	{
		$config = self::config();
		if (isset($config->packDelegates[$type])) {
			list($className, $methodName) = explode(
				'::', $config->packDelegates[$type]
			);
			$content = call_user_func(
				array($className, $methodName),
				$content
			);
		}
		return '/**' . PHP_EOL . ' * ' . $filename . PHP_EOL . ' */' . PHP_EOL .
			$content . PHP_EOL;
	}

	/**
	 * Получить ключ ресурса
	 *
	 * @return string
	 */
	public static function resourceKey()
	{
		$route = Router::getRoute();
		if (isset($route->params) && isset($route->params['resourceGroup'])) {
			return $route->params['resourceGroup'];
		}
		return md5($route->route);
	}
}