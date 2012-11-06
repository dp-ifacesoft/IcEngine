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
        'defaultPaths'  => array(
            self::CSS   => array(
                'noPack'    => 'Ice/Static/css/noPack/'
            ),
            self::JS    => array(
                'noPack'    => 'Ice/Static/js/noPack/'
            )
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
			self::CSS	=> '<link href="{$filename}" type="text/css" rel="stylesheet" />',
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
		self::JS    	=> array()
	);

	/**
	 * Добавляет файлы
	 *
	 * @param string $type
	 * @param string $filename
	 */
	public static function append($type, $filename, $pathName)
	{
        if ($pathName) {
            $config = self::config();
            $paths = $config->defaultPaths;
            if ($paths) {
                $paths = $paths[$type];
                $filename = IcEngine::root() . trim($paths[$pathName], '/') .
                    '/' . ltrim($filename, '/');
            }
        }
		array_push(self::$files[$type], $filename);
	}

	/**
	 * Добавить css файл
	 *
	 * @param string $filename
	 */
	public static function appendCss($filename, $pathName = null)
	{
		self::append(self::CSS, $filename, $pathName);
	}

	/**
	 * Добавляет js файлы
	 *
	 * @param string $filename
	 */
	public static function appendJs($filename, $pathName = null)
	{
		self::append(self::JS, $filename, $pathName);
	}

	/**
	 * Добавить файлы js по шаблону
	 *
	 * @param string $path базовый путь
	 * @param string $source
	 */
	public static function appendJsMultiple($path, $source)
	{
		$slashRight = strrpos($source, '/');
		$directory = substr($source, 0, $slashRight);
		$regExp = str_replace('**', '.*', substr($source, $slashRight + 1));
		$directoryIterator = new RecursiveDirectoryIterator(
			$path . $directory
		);
		$iterator = new RecursiveIteratorIterator($directoryIterator);
		$sources = array();
		foreach ($iterator as $item) {
			$fileName = $item->getFilename();
			if (preg_match('#' . $regExp . '#si', $fileName)) {
				$sources[] = $item->getPathname();
			}
		}
		function abstractUp($a, $b) {
			if (strstr($a, 'Abstract') &&
				!strstr($b, 'Abstract')) {
				return -1;
			}
			return 1;
		}
		usort($sources, 'abstractUp');
		foreach ($sources as $source) {
			self::appendJs($source);
		}
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
	public static function createPackFile($type, $key, $fileName = null)
	{
		if (empty(self::$files[$type])) {
			return;
		}
		$config = self::config();
		$root = IcEngine::root();
		if (!$fileName) {
			$fileName = $root . ltrim($config->path, '/') .
				$key . (isset($config->packGroups[$type]) ?
				$config->packGroups[$type] : '');
		}
		if (is_file($fileName)) {
			return true;
		}
		$content = '';
		foreach (self::$files[$type] as $currentFilename) {
			if (!is_file($currentFilename)) {
				continue;
			}
			$fileContent = file_get_contents($currentFilename);
			$content .= self::pack($type, $fileContent);
		}
		$resultContent = str_replace('$jsEmbedKey', $key, $content);
		file_put_contents($fileName, $resultContent);
		return true;
	}

	/**
	 * Внедряет упакованные файл
	 *
	 * @param string $type
	 * @return string
	 */
	public static function embed($type, $key, $compiledName = null)
	{
		$config = self::config();
		$provider = Data_Provider_Manager::get($config->provider);
		$lastPackedAt = $provider->get($key);
		if (!$lastPackedAt) {
			$lastPackedAt = time();
			$provider->set($key, $lastPackedAt);
		}
		if (self::createPackFile($type, $key, $compiledName)) {
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
        $key = self::resourceKey();
		return self::embed(self::CSS, $key);
	}

	/**
	 * Внедряет упакованный js файл
	 *
	 * @return string
	 */
	public static function embedJs()
	{
		$config = Config_Manager::get('Controller_Resource');
		$route = Router::getRoute();
		$call = $route->actions[0];
		$rules = $config->js->rules;
		$path = $config->js->defaultPath;
		$out = '';
        $ruleParams = array();
        if ($config->js) {
        	$ruleParams = $config->js->params->__toArray();
        }
		$routeParams = array_keys($route->params);
		foreach ($routeParams as $routeParam) {
			if (isset($ruleParams[$routeParam])) {
				$ruleParam = $ruleParams[$routeParam];
				if (isset($ruleParam['path'])) {
					$path = $ruleParam['path'];
				}
				self::appendJs($path . 'Controller/JSstack.js');
				foreach ($ruleParam['sources'] as $source) {
					if (strstr($source, '_')) {
						$source = str_replace('_', '/', $source) . '.js';
					} elseif (strstr($source, '**')) {
						self::appendJsMultiple($path, $source);
						continue;
					}
					self::appendJs($path . $source);
				}
				$out .= self::embed(self::JS, $routeParam);
				self::resetJs();
			}
		}
		if (isset($rules[$call])) {
			$rule = $rules[$call];
			if (isset($rule['path'])) {
				$path = $rule['path'];
			}
			self::appendJs($path . 'Controller/JSstack.js');
			foreach ($rule['sources'] as $source) {
				if (strstr($source, '_')) {
					$source = str_replace('_', '/', $source) . '.js';
				} elseif (strstr($source, '**')) {
					self::appendJsMultiple($path, $source);
					continue;
				}
				self::appendJs($path . $source);
			}
		}
		$key = self::resourceKey();
		$out .= self::embed(self::JS, $key);
		return $out;
	}

	public static function embedJsKeys()
	{
		$config = Config_Manager::get('Controller_Resource');
		$route = Router::getRoute();
		$call = $route->actions[0];
		$rules = $config->js->rules;
		$keys = array();
		$ruleParams = $config->js->params->asArray();
		$routeParams = array_keys($route->params);
		foreach ($routeParams as $routeParam) {
			if (isset($ruleParams[$routeParam])) {
				$keys[] = $routeParam;
			}
		}
		if (isset($rules[$call])) {
			$keys[] = self::resourceKey();
		}
		return $keys;
	}

	/**
	 * Упаковывает файл статики
	 *
	 * @param string $type
	 * @param string $content
	 * @return string
	 */
	public static function pack($type, $content)
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
		return $content . PHP_EOL;
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

	public static function resetJs()
	{
		self::$files[self::JS] = array();
	}
}