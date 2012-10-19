<?php
/**
 *
 * @desc Менеджер ресурсов представления
 * @author Юрий
 * @package IcEngine
 *
 */
class View_Resource_Manager extends Manager_Abstract
{

	/**
	 * @desc Тип ресурса - CSS.
	 * Файл стилей.
	 * @var string
	 */
	const CSS = 'css';

	/**
	 * Тип ресурса - JS.
	 * Файл javascript.
	 * @var string
	 */
	const JS = 'js';

	/**
	 * @desc Тип ресурса - JTPL.
	 * Шаблоны для javascript.
	 * @var string
	 */
	const JTPL = 'jtpl';

	/**
	 * @var string
	 */
	const JRES = 'jres';

	/**
	 *
	 * @var array
	 */
	protected static $_config = array ();

	/**
	 * @desc Ресурсы.
	 * @var array <View_Resource_Item>
	 */
	protected static $_resources = array ();

	/**
	 * @desc Упаковщики ресурсов.
	 * @var array <View_Resrouce_Packer_Abstract>
	 */
	protected static $_packers = array ();

	/**
	 * @desc Добавление ресурса
	 * @param string|array $data
	 * 		Ссылка на ресурс или массив пар (тип => ссылка)
	 * @param string $type [optional] Тип ресурса
	 * @param array $flags Параметры
	 * @return View_Resource|array
	 */
	public static function add ($data, $type = null, array $options = array ())
	{
		if (is_array ($data))
		{
			$result = array ();
			foreach ($data as $d)
			{
				$result [$d] = self::add ($d, $type, $options);
			}
			return $result;
		}

		if (is_null ($type))
		{
			$type = strtolower (substr (strrchr ($data, '.'), 1));
		}

		if (!isset (self::$_resources [$type]))
		{
			self::$_resources [$type] = array ();
		}
		else
		{
			foreach (self::$_resources [$type] as &$exists)
			{
				if ($exists->href == $data)
				{
					return $exists;
				}
			}
		}

		$options ['href'] = $data;
		$result = new View_Resource ($options);
		self::$_resources [$type][] = $result;

		return $result;
	}

	/**
	 * @desc Возвращает ресурсы указанного типа.
	 * @param string $type Тип
	 * @return array Ресурсы
	 */
	public static function getData ($type)
	{
		if (!isset (self::$_resources [$type]))
		{
			return array ();
		}

		return self::$_resources [$type];
	}

	/**
	 * @desc Загружает ресурсы
	 * @param string $base_url
	 * @param string $base_dir
	 * @param array|objective <string> $dirs
	 * @param string $type
	 */
	public static function load ($base_url, $base_dir, $dirs, $type = null)
	{
		$base_dir = str_replace ('\\', '/', $base_dir);
		$base_dir = rtrim ($base_dir, '/') . '/' ;

		if (!$base_url)
		{
			$base_url = $base_dir;
		}

		foreach ($dirs as $pattern)
		{
			$options = array (
				'source'	=> $pattern,
				'nopack'	=> ($pattern [0] == '-'),
				'filePath'	=> ''
			);

			if ($pattern [0] == '-')
			{
				$pattern = substr ($pattern, 1);
			}

			$dbl_star_pos = strpos ($pattern, '**');
			$star_pos = strpos ($pattern, '*');

			if ($dbl_star_pos !== false)
			{
				// Путь вида "js/**.js"
				// Включает поддиректории.

				// $dirs [i] = "js/**.js"
				$dir = trim (substr ($pattern, 0, $dbl_star_pos), '/');
				// $dir = "js"
				$pattern = substr ($pattern, $dbl_star_pos + 1);
				// $pattern = "*.js"

				$list = array (
					$dir
				);

				$files = array ();

				for ($dir = reset ($list); $dir !== false; $dir = next ($list))
				{
					$subdirs = scandir ($base_dir . $dir);
					$path = $dir ? $dir . '/' : '';

					for ($j = 0, $count = sizeof ($subdirs); $j < $count; $j++)
					{
						if (
							$subdirs [$j][0] == '.' ||
							$subdirs [$j][0] == '_'
						)
						{
							continue;
						}

						$fn = $base_dir . $path . $subdirs [$j];

						if (is_dir ($fn))
						{
							array_push ($list, $path . $subdirs [$j]);
						}
						elseif (fnmatch ($pattern, $fn))
						{
							$files [] = array (
								$base_url . $path . $subdirs [$j],
								$base_dir . $path . $subdirs [$j]
							);
						}
					}
				}

				$base_dir_len = strlen ($base_dir);
				for ($j = 0, $count = sizeof ($files); $j < $count; $j++)
				{
					$options ['source'] = $files [$j][0];
					$options ['filePath'] = $files [$j][1];
					$options ['localPath'] = substr (
						$files [$j][1],
						$base_dir_len
					);
					self::add (
						$files [$j][0],
						$type,
						$options
					);
				}
			}
			elseif ($star_pos !== false)
			{
				// Путь вида "js/*.js"
				// Включает файлы, подходящие под маску в текущей директории

				// $dirs [i] = "js/*.js"
				$dir = trim (substr ($pattern, 0, $star_pos), '/');
				// $dir = "js"
				$pattern = substr ($pattern, $star_pos);
				// $pattern = "*.js"

				$iterator = new DirectoryIterator ($base_dir . '/' . $dir);

				foreach ($iterator as $file)
				{
					$fn = $file->getFilename ();
					if (
						$file->isFile () &&
						$fn [0] != '.' &&
						$fn [0] != '_' &&
						fnmatch ($pattern, $fn)
					)
					{
						$local_path = $dir . '/' . $fn;
						$source = $base_url . $local_path;
						$options ['source'] = $source;
						$options ['filePath'] = $base_dir . $local_path;
						$options ['localPath' ] = $local_path;

						self::add (
							$source,
							$type,
							$options
						);
					}
				}
			}
			else
			{
				// Указан путь до файла: "js/scripts.js"
				$file = $base_url . $pattern;
				$options ['filePath'] = $base_dir . $pattern;
				$options ['localPath'] = $pattern;
				self::add ($file, $type, $options);
			}
		}
	}

	/**
	 * @desc Возвращает упаковщик ресурсов для указанного типа.
	 * @param string $type
	 * @return View_Resource_Packer_Abstract
	 */
	public static function packer ($type)
	{
		if (!isset (self::$_packers [$type]))
		{
			$class = 'View_Resource_Packer_' . ucfirst ($type);
			self::$_packers [$type] = new $class ();
		}
		return self::$_packers [$type];
	}

	/**
	 * @desc Загружает ресурсы
	 * @param string $base_dir
	 * @param string $pattern
	 * @param string $type
	 * @return array Массив с загруженными ресурсами.
	 */
	public static function patternLoad ($base_dir, $pattern, $type = null)
	{
		$base_dir = str_replace ('\\', '/', $base_dir);
		$base_dir = rtrim ($base_dir, '/') . '/' ;
		$base_url = '/';

		$result = array ();

		$options = array (
			'source'	=> $pattern,
			'nopack'	=> ($pattern [0] == '-'),
			'filePath'	=> '',
			'exclude'	=> false,
			'src'		=> ''
		);

		if ($pattern [0] == '-')
		{
			$pattern = substr ($pattern, 1);
		}
		elseif ($pattern [0] == '^')
		{
			$options ['exclude'] = true;
			$pattern = substr ($pattern, 1);
		}

		$dbl_star_pos = strpos ($pattern, '**');
		$star_pos = strpos ($pattern, '*');

		if ($dbl_star_pos !== false)
		{
			// Путь вида "js/**.js"
			// Включает поддиректории.

			// $dirs [i] = "js/**.js"
			$dir = trim (substr ($pattern, 0, $dbl_star_pos), '/');
			// $dir = "js"
			$pattern = substr ($pattern, $dbl_star_pos + 1);
			// $pattern = "*.js"

			$list = array (
				$dir
			);

			$files = array ();

			for ($dir = reset($list); $dir !== false; $dir = next($list)) {
				$subdirs = scandir($base_dir . $dir);
				$path = $dir ? $dir . '/' : '';

				for ($j = 0, $count = sizeof ($subdirs); $j < $count; $j++)
				{
					if (
						$subdirs [$j][0] == '.' ||
						$subdirs [$j][0] == '_'
					)
					{
						continue;
					}

					$fn = $base_dir . $path . $subdirs [$j];

					if (is_dir ($fn))
					{
						array_push ($list, $path . $subdirs [$j]);
					}
					elseif (fnmatch ($pattern, $fn))
					{
						$files [] = array (
							$base_url . $path . $subdirs [$j],
							$base_dir . $path . $subdirs [$j]
						);
					}
				}
			}

			$base_dir_len = strlen ($base_dir);
			for ($j = 0, $count = sizeof ($files); $j < $count; $j++)
			{
				$file = $files [$j][0];
				$options ['source'] = $file;
				$options ['filePath'] = $files [$j][1];
				$options ['localPath'] = substr (
					$files [$j][1],
					$base_dir_len
				);
				$result [$file] = self::add ($file, $type, $options);
			}
		}
		elseif ($star_pos !== false)
		{
			// Путь вида "js/*.js"
			// Включает файлы, подходящие под маску в текущей директории

			// $dirs [i] = "js/*.js"
			$dir = trim (substr ($pattern, 0, $star_pos), '/');
			// $dir = "js"
			$pattern = substr ($pattern, $star_pos);
			// $pattern = "*.js"

			$iterator = new DirectoryIterator ($base_dir . '/' . $dir);

			foreach ($iterator as $file)
			{
				$fn = $file->getFilename ();
				if (
					$file->isFile () &&
					$fn [0] != '.' &&
					$fn [0] != '_' &&
					fnmatch ($pattern, $fn)
				)
				{
					$local_path = $dir . '/' . $fn;
					$file = $base_url . $local_path;
					$options ['source'] = $file;
					$options ['filePath'] = $base_dir . $local_path;
					$options ['localPath' ] = $local_path;
					$result [$file] = self::add ($file, $type, $options);
				}
			}
		}
		else
		{
			// Указан путь до файла: "js/scripts.js"
			$file = $base_url . $pattern;
			$options ['filePath'] = $base_dir . $pattern;
			$options ['localPath'] = $pattern;
			$result [$file] = self::add ($file, $type, $options);
		}

		return $result;
	}

}