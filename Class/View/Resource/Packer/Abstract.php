<?php
/**
 * @desc Абстрактный упаковщик ресурсов представления.
 * @author Юрий
 * @package IcEngine
 *
 */
abstract class View_Resource_Packer_Abstract
{
	
	/**
	 * @desc Текущий ресурс
	 * @var View_Resource
	 */
	protected $_currentResource;
	
	/**
	 * @desc Время создания упакованного файла.
	 * @var string
	 */
	protected $_cacheTimestamp = 0;
	
	/**
	 * @desc Настройки
	 * @var array
	 */
	protected $_config = array (
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
	 * @desc Пул конфигов.
	 * @var array <Config_Array>
	 */
	protected $_configPool = array ();
	
	/**
	 * @desc Собирает префикс для файла.
	 * @return string Префикс для файла.
	 */
	public function _compileFilePrefix ()
	{
		return str_replace (
			'{$time}',
			date ('Y-m-d H:i:s'),
			$this->config ()->file_prefix
		);
	}
	
	/**
	 * @desc Таймстамп создания кэша.
	 * @return integer
	 */
	public function cacheTimestamp ()
	{
		return $this->_cacheTimestamp;
	}
	
	/**
	 * @desc Проверяет существование валидного кэша для ресурсов.
	 * @param array $resources
	 * @param string $result_file
	 * @return boolean
	 */
	public function cacheValid (array $resources, $result_file)
	{
		$config = $this->config ();
		
		if (
			!$result_file || 
			!file_exists ($result_file) || 
			!$config->state_file ||
			!file_exists ($config ['state_file'])
		)
		{
			return false;
		}
		
		$state = file_get_contents ($config ['state_file']);
		$state = json_decode ($state, true);
		
		if (!$state)
		{
			return false;
		}
		
		if (
			!isset ($state ['result_file']) ||
			!isset ($state ['result_time']) ||
			!isset ($state ['resources']) ||
			$state ['result_file'] != $result_file ||
			!is_array ($state ['resources']) ||
			count ($state ['resources']) != count ($resources)
		)
		{
			return false;
		}
		
		$delta_time = time () - $state ['result_time'];
		if ($delta_time > $config ['refresh_time'])
		{
			$delta_time -= $config ['refresh_time'];
			
			if (
				$delta_time > $config ['refresh_time'] ||
				rand (0, $delta_time) == 0
			)
			{
				return false;
			}
		} 
		
		foreach ($state ['resources'] as $i => $res)
		{
			if (
				!isset ($resouces [$i]) ||
				$res ['filemtime'] != $resources [$i]->filemtime () ||
				$res ['file_path'] != $resources [$i]->filePath 
			)
			{
				return false;
			}
		}
		
		$this->_cacheTimestamp = $state ['result_time'];
		
		return true;
	}
	
	/**
	 * @desc Объединение результатов упаковщика.
	 * @param array $packages
	 * @return string
	 */
	public function compile (array $packages)
	{
		return
			$this->_compileFilePrefix () . 
			implode ("\n", $packages);
	}
	
	/**
	 * @desc Загружает и возвращает конфиг
	 * @return Objective
	 */
	public function config ()
	{
		if (is_array ($this->_config))
		{
			$this->_config = Config_Manager::get (
				get_class ($this),
				$this->_config
			);
		}
		return $this->_config;
	}
	
	/**
	 * @desc Пакование ресурсов в строку или указанный файл.
	 * @param array <string> $resources Ресурсы.
	 * @param string $result_file [optional] Файл для сохранения результата.
	 * @return mixed|string
	 * 		
	 */
	public function pack (array $resources, $result_file = '')
	{
		$config = $this->config ();
		
		$packages = array ();
		
		if ($this->cacheValid ($resources, $result_file))
		{
			return true;
		}
		
		foreach ($resources as $resource)
		{
			if (!$resource->exclude)
			{
				$this->_currentResource = $resource;
				$packages [] = $this->packOne ($resource);
			}
		}
		
		$packages = $this->compile ($packages);
		
		if ($config ['charset_base'] != $config ['charset_output'])
		{
			$packages = iconv (
				$config ['charset_base'],
				$config ['charset_output'],
				$packages
			);
		}
        
		if ($result_file)
		{
			$this->saveValidState ($resources, $result_file);
			return file_put_contents ($result_file, $packages);
		}

		return $packages;
	}
	
	/**
	 * @desc Паковка одного ресурса.
	 * @param View_Resource $resource Ресурс.
	 * @return string Запакованная строка, содержащая ресурс.
	 */
	abstract public function packOne (View_Resource $resource);
	
	/**
	 * @desc Возвращание конфигов к исходному состоянию.
	 */
	public function popConfig ()
	{
		$this->config = array_pop ($this->_configPool);
	}
	
	/**
	 * @desc Наложение конфигов.
	 */
	public function pushConfig (Objective $config)
	{
		$this->_configPool [] = $this->config ();
		$this->_config = new Objective (array_merge (
			$this->_config->asArray (),
			$config->asArray ()
		));
	}
	
	/**
	 * @desc Сохраняет информацию о текущем состоянии файлов.
	 * @param array $resources
	 * @param string $result_file
	 */
	public function saveValidState (array $resources, $result_file)
	{
		$config = $this->config ();
		
		if (
			!$result_file ||  
			!$config->state_file
		)
		{
			return false;
		}
		
		$state = array (
			'result_file'	=> $result_file,
			'result_time'	=> time (),
			'resources'		=> array ()
		);
		
		foreach ($resources as $i => $resource)
		{
			$state ['resources'][$i] = array (
				'file_path'	=> $resource->filePath,
				'filemtime'	=> $resource->filemtime ()
			);
		}
		
		$this->_cacheTimestamp = $state ['result_time'];
		
		$state = json_encode ($state);
		file_put_contents ($config->state_file, $state);
	}
	
}