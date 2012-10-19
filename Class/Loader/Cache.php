<?php
/**
 * 
 * @desc Класс для кэширования подключаемых файлов.
 * Подключаемые php модули будут компилироваться в 1 файл.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Loader_Cache
{
	
	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Включение компиляции файлов и их инклуд.
		 * @var boolean
		 */
		'enable'			=> true,
		/**
		 * @desc Проверять файлы после создания кэша.
		 * @var boolean
		 */
		'check_files'		=> true
	);
	
	/**
	 * @desc Имя собранного 
	 * @var string
	 */
	protected static $_compiledFilename;
	
	/**
	 * @desc Имя файла со статистикой
	 * @var string
	 */
	protected static $_dataFilename;
	
	/**
	 * @desc Игнорируемые файлы.
	 * (Подключены до инициализации кэша лоадера).
	 * @var array
	 */
	protected static $_ignoring = array ();
	
	/**
	 * @desc Информация по закешированным файлам.
	 * @var array
	 */
	protected static $_cached = array ();
	
	/**
	 * @desc Транзакции
	 * @var array
	 */
	protected static $_transations = array ();
	
	/**
	 * @desc Генерирует кэш для текущей транзакции.
	 */
	protected static function _generateCache ()
	{
		$compiled = '';
		
		foreach (Loader::$required as $type => $files)
		{
			$data = array ();
			foreach ($files as $file => $ok)
			{
				if (
					(
						// Файл уже в кэше
						!isset (self::$_cached [$type]) || 
						!isset (self::$_cached [$type][$file])
					) &&
					(
						!$ok ||	// Не был подключен
						(
							// Игнорирован
							isset (self::$_ignoring [$type]) &&
							isset (self::$_ignoring [$type][$file]) &&
							self::$_ignoring [$type][$file]
						)
					)
				)
				{
					continue;
				}
				
				$fn = Loader::findFile ($file, $type);
				
				$data [$file] = array (
					't'		=> filemtime ($fn),
					's'		=> filesize ($fn)
				);
				
				$content = file_get_contents ($fn);
				
				$compiled .= str_replace (
					array (
						'dirname(__FILE__)',
						'dirname (__FILE__)'
					),
					'\'' . addslashes (dirname ($fn)) . '\'',
					$content
				);
				
				if (strpos (substr ($compiled, -10, 10), '?>') === false)
				{
					$compiled .= ' ?>';
				}
			}
			
			self::$_cached [$type] = 
				isset (self::$_cached [$type]) ?
					array_merge (self::$_cached [$type], $data) :
					$data;
		}
		
		file_put_contents (
			self::$_dataFilename,
			json_encode (self::$_cached)
		);
		file_put_contents (self::$_compiledFilename, $compiled);
	}
	
	/**
	 * @desc Что-то поменялось
	 * @return boolean
	 */
	protected static function _isSomeChanged ()
	{
		$config = self::config ();
		
		foreach (Loader::$required as $type => $files)
		{
			foreach ($files as $file => $ok)
			{
				if (
					(
						isset (self::$_ignoring [$type]) &&
						isset (self::$_ignoring [$type][$file]) &&
						self::$_ignoring [$type][$file]
					) ||
					!$ok
				)
				{
					continue;
				}
				
				if (
					!isset (self::$_cached [$type]) ||
					!isset (self::$_cached [$type][$file])
				)
				{
					return true;
				}
				
				if ($config ['check_files'])
				{
					$fn		= Loader::findFile ($file, $type);
					$mtime	= $config ['check_filesize'];
					$size	= filesize ($fn);
					if (
						self::$_cached [$type][$file]['t'] != $mtime ||
						self::$_cached [$type][$file]['s'] != $size
					)
					{
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	protected static function _popTransaction ()
	{
		$transaction = array_pop (self::$_transations);
		self::$_ignoring			= $transaction ['ignoring'];
		self::$_dataFilename		= $transaction ['data_file'];
		self::$_compiledFilename	= $transaction ['compiled_file'];
		self::$_cached				= $transaction ['cached'];
	}
	
	protected static function _pushTransaction ()
	{
		self::$_transations [] = array (
			'ignoring'		=> self::$_ignoring,
			'data_file'		=> self::$_dataFilename,
			'compiled_file'	=> self::$_compiledFilename,
			'cached'		=> self::$_cached
		);
	}
	
	/**
	 * @desc Начало транзакции.
	 */
	public static function beginTransaction ($name = null)
	{
		$config = self::config ();
		
		self::_pushTransaction ();
		
		if (!$config ['enable'])
		{
			return ;
		}
		
		$name = $name ? $name : '__null';
		
		$name = urlencode ($name ? $name : '/');
		
		self::$_dataFilename = self::dataPath () . $name;
			
		self::$_compiledFilename = self::compiledPath () . $name . '.php';
		
		self::$_ignoring = Loader::getRequired ();
		
		if (
			file_exists (self::$_dataFilename) &&
			file_exists (self::$_compiledFilename)
		)
		{
			self::$_cached = json_decode (
				file_get_contents (self::$_dataFilename),
				true
			);
			
			foreach (self::$_cached as $type => $files)
			{
				foreach ($files as $file => $info)
				{
					if (
						(
							isset (self::$_ignoring [$type]) &&
							isset (self::$_ignoring [$type][$file]) &&
							self::$_ignoring [$type][$file]
						) ||
						!$config ['check_files']
					)
					{
						continue ; 
					}
					
					$fn = Loader::findFile ($file, $type);
					
					$changed = !file_exists ($fn);
					
					if (!$changed)
					{
						$t = $config ['check_filemtime'] ? filemtime ($fn) : 0;
						$s = $config ['check_filesize'] ? filesize ($fn) : 0;
						
						$changed = $info ['t'] != $t || $info ['s'] != $s;
					}
					
					if ($changed)
					{
//						var_dump ('changed: ', array (
//							'info'	=> $info,
//							'file'	=> $file, 
//							't'		=> $t, 
//							's'		=> $s,
//							'fn'	=> $fn
//						));
						// Один из файлов изменен, собираем статистику заного
						self::$_cached = array ();
						return ;
					}
				}
			}
			
			// Отмечаем, какие файлы загружены
			foreach (self::$_cached as $type => $files)
			{
				$files = array_fill_keys (array_keys ($files), true);
				
				/*Loader::setRequired (
					isset (Loader::getRequired ($type)) ?
						array_merge (Loader::getRequired ($type), $files) :
						$files, $type
				);*/
			}
			self::$_ignoring = Loader::getRequired ();
			
			require self::$_compiledFilename;
		}
	}
	
	/**
	 * @desc Удаление всех скомпилированных файлов и данных о них.
	 */
	public static function clearCompiled ()
	{
		$pathes = array (
			self::dataPath (),
			self::compiledPath ()
		);
		
		foreach ($pathes as $path)
		{
			$handle = opendir ($path);
			if ($handle)
			{
				while (false !== ($file = readdir ($handle)))
				{
					if ($file != "." && $file != "..")
					{
						unlink ($path . $file);
					}
				}
			}
			closedir ($handle);
		}
	}
	
	/**
	 * @desc Директория с собранными кэшами
	 * @return string;
	 */
	public static function compiledPath ()
	{
		return IcEngine::root () . 'cache/Loader/compiled/';
	}
	
	/**
	 * @desc Загружает и возвращает конфиг.
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (self::$_config))
		{
			self::$_config = Config_Manager::get (__CLASS__, self::$_config);
		}
		return self::$_config;
	}
	
	/**
	 * @desc Директория с данными о кэшах.
	 * @return string
	 */
	public static function dataPath ()
	{
		return IcEngine::root () . 'cache/Loader/data/';
	}
	
	/**
	 * @desc Сохранение статистики по подключаемым файлам
	 */
	public static function endTransaction ()
	{
		$config = self::config ();
		
		if (!$config ['enable'])
		{
			self::_popTransaction ();
			return ;
		}
		
		if (self::_isSomeChanged ())
		{
			self::_generateCache ();
		}
		
		self::_popTransaction();
	}
	
}
