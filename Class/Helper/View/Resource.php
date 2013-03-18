<?php

/**
 * Помощник упаковщика статических ресурсов
 *
 * @author morph
 * @Service("helperViewResource")
 */
class Helper_View_Resource
{
	/**
	 * Метка для css файлов
	 */
	const CSS = 'css';

	/**
	 * Метка для js файлов
	 */
	const JS = 'js';

    /**
     * Метка для jtpl файлов
     */
    const JTPL = 'jtpl';

	/**
	 * Конфигурация
	 *
	 * @var array
	 */
	protected static $config = array(
        'defaultPaths'  => array(
            self::CSS   => array(
                'noPack'            => 'Ice/Static/css/noPack/',
                'adminNoPack'       => 'Admin/Static/css/noPack/',
            ),
            self::JS    => array(
                'noPack'            => 'Ice/Static/js/noPack/',
                'adminNoPack'       => 'Admin/Static/js/noPack/',
                'core'              => 'IcEngine/js/'
            ),
            self::JTPL  => array(
                'default'   => 'Ice/Static/jtpl/',
                'view'      => 'Ice/View/'
            )
        ),
		'packDelegates'	=> array(
			self::CSS	=> 'Helper_View_Resource_Css::pack',
			self::JS    => 'Helper_View_Resource_Js::pack',
            self::JTPL  => 'Helper_View_Resource_Jtpl::pack'
   		),
		'packGroups'	=> array(
			self::CSS	=> '.css',
			self::JS		=> '.js',
            self::JTPL  => '.js'
		),
		'packTemplates'	=> array(
			self::CSS	=> '<style type="text/css">@import url("{$filename}");</style>',
			self::JS		=> '<script type="text/javascript" src="{$filename}"></script>',
            self::JTPL	=> '<script type="text/javascript" src="{$filename}"></script>'
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
		self::JS    	=> array(),
        self::JTPL  => array()
	);

	/**
	 * Добавляет файлы
	 *
	 * @param string $type
	 * @param string $filename
     * @param string $pathName
     * @param array $params
	 */
	public function append($type, $filename = null, $pathName = null,
        $params = array())
	{
        $config = $this->config();
        $args = func_get_args();
        $type = $args[0];
        if (!is_array($args[1][0])) {
            $tmpArgs = array($args[1][0], $args[1][1]);
            if (isset($args[1][2])) {
                $tmpArgs[2] = $args[1][2];
            }
            $args = array($tmpArgs);
        } else {
            array_shift($args);
            $args = reset($args);
        }

        foreach ($args as $argsData) {
            $pathName = isset($argsData[1]) ? $argsData[1] : null;
            $filename = $argsData[0];
            if ($pathName) {
                $paths = $config->defaultPaths;
                if ($paths) {
                    $paths = $paths[$type];
                    $filename = IcEngine::root() . trim($paths[$pathName], '/') .
                        '/' . ltrim($filename, '/');
                }
            }
            array_push(self::$files[$type], array($filename, $params));
        }
	}

	/**
	 * Добавить css файл
	 *
	 * @param string $filename
     * @param string $pathName
     * @param array $params
	 */
	public function appendCss($filename, $pathName = null, $params = array())
	{
		$this->append(self::CSS, func_get_args());
	}

	/**
	 * Добавляет js файлы
	 *
     * @example appendJs('Controller/Loginza.js', 'noPack')
	 * @param string $filename
     * @param string $pathName
     * @param array $params
	 */
	public function appendJs($filename, $pathName = null, $params = array())
	{
		$this->append(self::JS, func_get_args());
	}

    /**
	 * Добавляет jtpl файлы
	 *
	 * @param string $filename
     * @param string $pathName
     * @param array $params
	 */
	public function appendJtpl($filename, $pathName = null, $params = array())
	{
		$this->append(self::JTPL, array($filename, 'default', $params));
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
			$this->appendJs($source);
		}
	}

	/**
	 * Получить конфигурацию
	 *
	 * @return Config_Abstract
	 */
	public function config()
	{
		if (is_array(self::$config)) {
            $serviceLocator = IcEngine::serviceLocator();
            $configManager = $serviceLocator->getService('configManager');
			self::$config = $configManager->get(__CLASS__, self::$config);
		}
		return self::$config;
	}

	/**
	 * Упаковывает группу статики
	 *
	 * @param string $type
	 */
	public function createPackFile($type, $key, $fileName = null)
	{
		$config = $this->config();
		$root = IcEngine::root();
		if (!$fileName) {
			$fileName = $root . ltrim($config->path, '/') .
				$key . (isset($config->packGroups[$type]) ?
				$config->packGroups[$type] : '');
		}
		if (is_file($fileName)) {
			return true;
		}
        if (empty(self::$files[$type])) {
			return;
		}
		$content = '';
		foreach (self::$files[$type] as $data) {
            $currentFilename = $data[0];
            if (!is_file($currentFilename)) {
				continue;
			}
			$fileContent = file_get_contents($currentFilename);
			$content .= $this->pack(
                $type, $currentFilename, $fileContent, $data[1]
            );
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
	public function embed($type, $key, $compiledName = null)
	{
		$config = $this->config();
        $serviceLocator = IcEngine::serviceLocator();
        $dataProviderManager = $serviceLocator->getService(
            'dataProviderManager'
        );
		$provider = $dataProviderManager->get($config->provider);
		$lastPackedAt = $provider->get($key);
		if (!$lastPackedAt) {
			$lastPackedAt = time();
			$provider->set($key, $lastPackedAt);
		}
		if ($this->createPackFile($type, $key, $compiledName)) {
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
	public function embedCss()
	{
        $key = $this->resourceKey();
		return $this->embed(self::CSS, $key);
	}

	/**
	 * Внедряет упакованный js файл
	 *
	 * @return string
	 */
	public function embedJs()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $configManager = $serviceLocator->getService('configManager');
		$config = $configManager->get('Controller_Resource');
		$route = $serviceLocator->getService('router')->getRoute();
		$call = $route->actions[0];
        $rules = array();
        $path = '';
        if ($config->js) {
            $rules = $config->js->rules;
            $path = $config->js->defaultPath;
        }
		$out = '';
        $ruleParams = array();
        if ($config->js) {
        	$ruleParams = $config->js->params->__toArray();
        }
		$routeParams = array_keys($route->params->__toArray());
		foreach ($routeParams as $routeParam) {
			if (isset($ruleParams[$routeParam])) {
				$ruleParam = $ruleParams[$routeParam];
				if (isset($ruleParam['path'])) {
					$path = $ruleParam['path'];
				}
				$this->appendJs($path . 'Controller/JSstack.js');
				foreach ($ruleParam['sources'] as $source) {
					if (strstr($source, '_')) {
						$source = str_replace('_', '/', $source) . '.js';
					} elseif (strstr($source, '**')) {
						$this->appendJsMultiple($path, $source);
						continue;
					}
					self::appendJs($path . $source);
				}
				$out .= $this->embed(self::JS, $routeParam);
				$this->resetJs();
			}
		}
		if (isset($rules[$call])) {
			$rule = $rules[$call];
			if (isset($rule['path'])) {
				$path = $rule['path'];
			}
			$this->appendJs($path . 'Controller/JSstack.js');
			foreach ($rule['sources'] as $source) {
				if (strstr($source, '_')) {
					$source = str_replace('_', '/', $source) . '.js';
				} elseif (strstr($source, '**')) {
					$this->appendJsMultiple($path, $source);
					continue;
				}
				$this->appendJs($path . $source);
			}
		}
		$key = $this->resourceKey();
		$out .= $this->embed(self::JS, $key);
		return $out;
	}

    /**
     * Ключи для внедряемого js
     *
     * @return array
     */
	public function embedJsKeys()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $configManager = $serviceLocator->getService('configManager');
		$config = $configManager->get('Controller_Resource');
		$route = $serviceLocator->getService('router')->getRoute();
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
			$keys[] = $this->resourceKey();
		}
		return $keys;
	}

    /**
	 * Внедряет упакованный jtpl файл
	 *
	 * @return string
	 */
	public function embedJtpl()
	{
        $key = $this->resourceKey();
		return $this->embed(self::JTPL, $key . '_jtpl');
	}

	/**
	 * Упаковывает файл статики
	 *
	 * @param string $type
     * @param string $filename
	 * @param string $content
     * @param array @params
	 * @return string
	 */
	public function pack($type, $filename, $content, $params = array())
	{
		$config = $this->config();
		if (isset($config->packDelegates[$type])) {
			list($className, $methodName) = explode(
				'::', $config->packDelegates[$type]
			);
			$content = call_user_func(
				array($className, $methodName),
				$content, $filename, $params
			);
		}
		return $content . PHP_EOL;
	}

	/**
	 * Получить ключ ресурса
	 *
	 * @return string
	 */
	public function resourceKey()
	{
        $serviceLocator = IcEngine::serviceLocator();
		$route = $serviceLocator->getService('router')->getRoute();
		if (isset($route->params) && isset($route->params['resourceGroup'])) {
			return $route->params['resourceGroup'];
		}
		return md5($route->route);
	}

    /**
     * Сбросить файла css
     */
	public function resetCss()
	{
		self::$files[self::CSS] = array();
	}

    /**
     * Сбросить файла js
     */
	public function resetJs()
	{
		self::$files[self::JS] = array();
	}
}