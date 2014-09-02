<?php

/**
 * Абстрактный упаковщик ресурсов представления.
 * 
 * @author goorus, morph
 */
abstract class View_Resource_Packer_Abstract
{
	
	/**
	 * Текущий ресурс
	 * 
     * @var View_Resource
	 */
	protected $currentResource;
	
	/**
	 * Время создания упакованного файла.
	 * 
     * @var string
	 */
	protected $cacheTimestamp = 0;
	
	/**
	 * Настройки
	 * 
     * @var array
	 */
	protected $config = array(
		/**
		 * @desc Префикс файла с упакованными ресурсами.
		 * @var string
		 */
		'file_prefix'	=> "/* Packed by IcEngine {\$time} */\n",
		
		/**
		 * @desc Префикс каждого скрипта
		 * @var string
		 */
		'item_prefix' 	=> "/* {\$source} */\n",
	
		/**
		 * @desc Постфикс каждого скрипта
		 * @var string
		 */
		'item_postfix'	=> "\n\n",
	
		/**
		 * @desc Время жизни кэша в секундах.
		 * По истечении этого времени, кэш будет принудительно обнволен,
		 * даже если не зафиксировано изменение исходных файлов.
		 * @var integer
		 */
		'refresh_time'	=> 999999999,
	
		/**
		 * @desc Файл для хранения состояния
		 * @var string
		 */
		'state_file'	=> '',
	
		/**
		 * @desc Исходная кодировка
		 * @var string
		 */
		'charset_base'		=> 'utf-8',
	
		/**
		 * @desc Кодировка
		 * @var string
		 */
		'charset_output'	=> 'utf-8//IGNORE'
	);
	
	/**
	 * Пул конфигов.
	 * 
     * @var array <Config_Array>
	 */
	protected $configPool = array();
	
	/**
	 * Собирает префикс для файла.
	 * 
     * @return string Префикс для файла.
	 */
	public function compileFilePrefix()
	{
		return str_replace(
			'{$time}', date('Y-m-d H:i:s'), $this->config()->file_prefix
		);
	}
	
	/**
	 * Таймстамп создания кэша.
	 * 
     * @return integer
	 */
	public function cacheTimestamp()
	{
		return $this->cacheTimestamp;
	}
	
	/**
	 * Проверяет существование валидного кэша для ресурсов.
	 * 
     * @param array $resources
	 * @param string $resultFile
	 * @return boolean
	 */
	public function cacheValid(array $resources, $resultFile)
	{
		$config = $this->config();
		if (!$resultFile) {
            return false;
        }
        if (!file_exists($resultFile)) {
            return false;
        }
        if (!$config->state_file) {
            return false;
        }
        if (!file_exists($config->state_file)) {
            return false;
        }
		$stateData = file_get_contents($config->state_file);
		$state = json_decode($stateData, true);
		if (!$state) {
			return false;
		}
        if (!isset($state['result_file'], $state['result_time'],
            $state['resources'])) {
            return false;
        }
        if ($state['result_file'] != $resultFile) {
            return false;
        }
        if (!is_array($state['resources'])) {
            return false;
        }
        if (count($state['resources']) != count($resources)) {
            return false;
        }
		$deltaTime = time() - $state['result_time'];
		if ($deltaTime > $config->refresh_time) {
			$deltaTime -= $config->refresh_time;
			if ($deltaTime > $config->refresh_time || rand(0, $deltaTime) == 0) {
                return false;
            }
		}
		foreach ($state['resources'] as $i => $resource) {
            if (!isset($resources[$i])) {
                return false;
            }
            if ($resource['filemtime'] != $resources[$i]->filemtime()) {
                return false;
            }
            if ($resource['file_path'] != $resources[$i]->filePath) {
                return false;
            }
		}
		$this->cacheTimestamp = $state['result_time'];
		return true;
	}
	
	/**
	 * Объединение результатов упаковщика.
	 * 
     * @param array $packages
	 * @return string
	 */
	public function compile(array $packages)
	{
		return $this->compileFilePrefix() . implode("\n", $packages);
	}
	
	/**
	 * Загружает и возвращает конфиг
	 * 
     * @return Objective
	 */
	public function config()
	{
		if (is_array($this->config)) {
            $serviceLocator = IcEngine::serviceLocator();
            $configManager = $serviceLocator->getService('configManager');
			$this->config = $configManager->get(
				get_class($this), $this->config
			);
		}
		return $this->config;
	}
	
	/**
	 * Пакование ресурсов в строку или указанный файл.
	 * 
     * @param array <string> $resources Ресурсы.
	 * @param string $resultFile [optional] Файл для сохранения результата.
     * @param boolean $notForceRepack [optional] Принудительно перепаковать статику
	 * @return mixed|string
	 * 		
	 */
	public function pack(array $resources, $resultFile = '', $config = null,
        $notForceRepack = false)
	{
        if (!$config) {
            $config = $this->config();
        }
		$noCompiledPackages = array();
		if ($this->cacheValid($resources, $resultFile) && !$notForceRepack) {
			return true;
		}

		foreach ($resources as $resource) {
            if (!$resource->exclude) {
				$this->currentResource = $resource;
                $package = $this->packOne($resource);
				$noCompiledPackages[] = $package;
			}
		}
		$packages = $this->compile($noCompiledPackages);
		if ($config->charset_base != $config->charset_output) {
			$packages = iconv(
				$config->charset_base, $config->charset_output, $packages
			);
		}

		if ($resultFile) {
			$this->saveValidState($resources, $resultFile);
			return file_put_contents($resultFile, $packages);
		}
		return $packages;
	}
	
	/**
	 * Паковка одного ресурса.
	 * 
     * @param View_Resource $resource Ресурс.
	 * @return string Запакованная строка, содержащая ресурс.
	 */
	abstract public function packOne(View_Resource $resource);
	
	/**
	 * @desc Возвращание конфигов к исходному состоянию.
	 */
	public function popConfig()
	{
		$this->config = array_pop($this->configPool);
	}
	
	/**
	 * Наложение конфигов.
	 */
	public function pushConfig(Objective $config)
	{
		$this->configPool[] = $this->config();
		$this->config = new Objective(array_merge(
			$this->config->asArray(),
			$config->asArray()
		));
	}
	
	/**
	 * Сохраняет информацию о текущем состоянии файлов.
	 * 
     * @param array $resources
	 * @param string $resultFile
	 */
	public function saveValidState(array $resources, $resultFile)
	{
		$config = $this->config();
		if (!$resultFile) {
            return false;
        }
        if (!$config->state_file) {
            return false;
        }
		$state = array(
			'result_file'	=> $resultFile,
			'result_time'	=> time(),
			'resources'		=> array()
		);
		foreach ($resources as $i => $resource) {
			$state['resources'][$i] = array(
				'file_path'	=> $resource->filePath,
				'filemtime'	=> $resource->filemtime()
			);
		}
		$this->cacheTimestamp = $state['result_time'];
		$stateEncoded = json_encode($state);
		file_put_contents($config->state_file, $stateEncoded);
	}
}