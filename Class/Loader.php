<?php
/**
 *
 * @desc Загрузчик модулей и классов.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Loader
{

	/**
	 * @desc Пути.
	 * @var array
	 */
	protected static $_pathes = array ();

	/**
	 * @desc Подключенные.
	 * @var array
	 */
	protected static $_required = array ();

	/**
	 * @desc Добавление пути.
	 * @param string $type Тип.
	 * @param string $path Путь.
	 */
	public static function addPath ($type, $path)
	{
		if (!isset (self::$_pathes [$type]))
		{
			self::$_pathes [$type] = array ($path);
		}
		else
		{
			self::$_pathes [$type][] = $path;
		}
	}

	/**
	 * @desc Добавление путей.
	 * @param array $pathes
	 */
	public static function addPathes (array $pathes)
	{
		foreach ($pathes as $type => $path)
		{
			$path = (array) $path;

			if (isset (self::$_pathes [$type]))
			{
				self::$_pathes [$type] = array_merge (
					self::$_pathes [$type],
					$path
				);
			}
			else
			{
				self::$_pathes [$type] = $path;
			}
		}
	}

	/**
	 * @desc Возвращает полный путь до файла.
	 * Если файла не существует, возвращается false.
	 * @param string $file Искомый файл.
	 * @param string $type Тип.
	 * @return string Если файл найден, полный путь до файла. Иначе false.
	 */
	public static function findFile ($file, $type = 'Class')
	{
		foreach (self::$_pathes [$type] as $path)
		{
			$fn = $path . $file;
			if (file_exists ($fn))
			{
				return $fn;
			}
		}

		return false;
	}

	/**
	 * @desc Возвращает все пути для указанного типа.
	 * @param string $type
	 * @return array
	 */
	public static function getPathes ($type)
	{
		return  isset (self::$_pathes [$type]) ?
			self::$_pathes [$type] :
			array ();
	}

	/**
	 * @desc Проверяет был ли уже подключен файл
	 * @param string $file
	 * @param string $type
	 * @return bool
	 */
	public static function getRequired ($file, $type)
	{
		return isset (self::$_required [$type][$file]);
	}

	/**
	 * @desc Подключение файла.
	 * @param string $file
	 * @param string $type
	 * @return boolean
	 */
	public static function requireOnce ($file, $type)
	{
		if (isset (self::$_required [$type][$file]))
		{
			return true;
		}

		for ($i = count (self::$_pathes [$type]) - 1; $i >= 0; --$i)
		{
			$fn = self::$_pathes [$type][$i] . $file;
			if (file_exists ($fn))
			{
				self::$_required [$type][$file] = true;
				require_once $fn;
				if (class_exists('Tracer') && Tracer::$enabled) {
					Tracer::incLoadedClassCount();
				}
				return true;
			}
		}

		$autoloaders = spl_autoload_functions();
		if (!$autoloaders || (count($autoloaders) == 1 &&
			$autoloaders[0][0] == 'Loader')) {
			echo '<pre>Not found: ' . $file . "\n";
			echo "\n\n";
			debug_print_backtrace ();
			echo '</pre>';
			die();
		}

		return false;
	}

	/**
	 * @desc Заного устанавливает пути до файлов. Предыдущие пути будут
	 * удалены.
	 * @param string $type Тип.
	 * @param string|array $path Путь или массив патей.
	 */
	public static function setPath ($type, $path)
	{
		self::$_pathes [$type] = (array) $path;
	}

	/**
	 * @desc Делает отметку о подключении файла.
	 * @param string $file Файл.
	 * @param string $type Тип.
	 * @param boolean $required [optional]
	 */
	public static function setRequired ($file, $type, $required = true)
	{
		self::$_required [$type][$file] = $required ? true : null;
	}

	/**
	 * @desc Попытка подключить указанный класс. В случае ошибки
	 * не возникает исключения.
	 * @param string $class Название класса.
	 * @param string $type [optional]
	 * @return boolean true в случае, если файл класса подключен или класс
	 * уже подключен, иначе false.
	 */
	public static function tryLoad ($class, $type = 'Class')
	{
		if (class_exists ($class, false))
		{
			return true;
		}

		$file = str_replace ('_', '/', $class) . '.php';

		for ($i = count (self::$_pathes [$type]) - 1; $i >= 0; --$i)
		{
			$fn = self::$_pathes [$type][$i] . $file;

			if (file_exists ($fn))
			{
				self::$_required [$type][$file] = true;
				require_once $fn;
				return true;
			}
		}

		return false;
	}

	/**
	 * @desc Подключение класса.
	 * @param string $class_name Название класса.
	 * @param string $type [optional]
	 * @return boolean true, если удалось подключить, иначе false.
	 */
	public static function load ($class, $type = 'Class')
	{
		if (class_exists ($class, false))
		{
			return true;
		}

		return self::requireOnce (
			str_replace ('_', '/', $class) . '.php',
			$type
		);
	}

	/**
	 * @desc Загрузка всех классов, переданных в параметрах.
	 * @param string $class...
	 */
	public static function multiLoad ()
	{
		foreach (func_get_args () as $class)
		{
			if (!class_exists ($class, false))
			{
				self::requireOnce (
					str_replace ('_', '/', $class) . '.php',
					'Class'
				);
			}
		}
	}

}
