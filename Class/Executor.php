<?php

/**
 *
 * @desc Исполнитель.
 * Предназначени для запуска функций/методов и кэширования результатов
 * их работы.
 * @author Юрий
 * @package IcEngine
 *
 */
class Executor
{

	/**
	 * @desc Разделитель частей при формировании ключа для кэширования.
	 * @var string
	 */
	const DELIM = '/';

	/**
	 * @desc Кэшер
	 * @var Data_Provider_Abstract
	 */
	protected static $_cacher;

	/**
	 * @desc Конфиг.
	 * @var array
	 */
	public static $config = array (
		/**
		 * @desc Провайдер данных, используемый для кэширования по умолчанию
		 * (Data_Provider).
		 * @var string
		 */
		'cache_provider'	=> null,
		/**
		 * @desc Описание кэширования для отдельных функций
		 * @var array
		 */
		'functions'			=> array (
		),
		/**
		 * @desc Провайдер поставки тэгов
		 */
		'tag_provider'		=> null,

		'tags'				=> array ()
	);

	/**
	 * @desc Возвращает название функции.
	 * @param function $function Функция.
	 * @return string
	 */
	protected static function _functionName ($function)
	{
		if (is_array ($function))
		{
			if (is_object ($function [0]))
			{
				return get_class ($function [0]) . self::DELIM . $function [1];
			}

			return $function [0] . self::DELIM . $function [1];
		}

		if (is_string ($function))
		{
			return $function;
		}

		return md5 ($function);
	}

	/**
	 * @desc Возвращает ключ для кэширования
	 * @param function $function Кэшируемая функция.
	 * @param array $args Аргкументы функции.
	 * @return string Ключ кэша.
	 */
	protected static function _getCacheKey ($function, array $args)
	{
		$key = self::_functionName ($function) . self::DELIM;

		if ($args)
		{
			$key .= md5 (json_encode ($args));
		}

		return $key;
	}

	/**
	 * @desc Выполнение функции подлежащей кэшированию.
	 * @param function $function Функция.
	 * @param array $args Аргументы функции.
	 * @param Objective $options Опции кэширования.
	 * @return mixed Результат выполнения функции.
	 */
	protected static function _executeCaching ($function, array $args,
		Objective $options)
	{
		$key = self::_getCacheKey ($function, $args);

		$expiration = (int) $options->expiration;

		$cache = self::getCacher ()->get ($key);

		$tag_valid = true;

		if (
			$options->current_tags &&
			(
				!isset ($cache ['t']) ||
				array_diff ($options->current_tags->__toArray (), $cache ['t'])
			)
		)
		{
			$tag_valid = false;
		}

		if ($cache)
		{
			if (
				(
					$cache ['a'] + $expiration > time () ||
					$expiration == 0
				) &&
				$tag_valid
			)
			{
				return $cache ['v'];
			}

			if (!self::$_cacher->lock ($key, 5, 1, 1))
			{
				// ключ уже заблокирова параллельным процессом
				return $cache ['v'];
			}
		}

		$value = self::_executeUncaching ($function, $args);

		$cache_value = array (
			'v' => $value,
			'a' => time ()
		);

		$tags = array ();

		if ($options->current_tags)
		{
			foreach ($options->current_tags as $tag => $e)
			{
				$tags [$tag] = $e;
			}
		}

		if ($tags)
		{
			$cache_value ['t'] = $tags;
		}

		self::$_cacher->set (
			$key,
			$cache_value
		);

		if ($cache)
		{
			self::$_cacher->unlock ($key);
		}

		return $value;
	}

	/**
	 * @desc Выполнение функции без кэширования.
	 * @param function $function Функция.
	 * @param array $args Аргументы функции.
	 * @return mixed Результат выполнения функции.
	 */
	protected static function _executeUncaching ($function, array $args)
	{
		return call_user_func_array ($function, $args);
	}

	/**
	 * @desc Возвращает конфиг. Загружет, если он не был загружен ранее.
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (self::$config))
		{
			self::$config = Config_Manager::get (__CLASS__, self::$config);
		}
		return self::$config;
	}

	/**
	 * @desc Выполняет переданную функцию.
	 * @param function $function Функция.
	 * @param array $args Аргументы функции.
	 * @param Objective $options [optional] Опции кэширования.
	 * 		Если не переданы, будут использованы настройки из конфига.
	 * @return mixed Результат выполнения функции.
	 */
	public static function execute ($function, array $args = array (),
		$options = null)
	{
		// Переданы опции
		if ($options)
		{
			return self::_executeCaching ($function, $args, $options);
		}

		// опции заданы в конфиге
		$fn = self::_functionName ($function);
		if (self::config ()->functions && self::$config->functions [$fn])
		{
			return self::_executeCaching (
				$function, $args,
				self::$config->functions [$fn]
			);
		}

		// без кэширования
		return self::_executeUncaching ($function, $args);
	}

	/**
	 * @desc Возвращает текущий кэшер.
	 * @return Data_Provider_Abstract|null
	 */
	public static function getCacher ()
	{
		if (!self::$_cacher)
		{
			if (self::config ()->cache_provider)
			{
				self::$_cacher = Data_Provider_Manager::get (
					self::config ()->cache_provider
				);
			}
			else
			{
				Loader::load ('Data_Provider_Buffer');
				self::$_cacher = new Data_Provider_Buffer ();
			}
		}
		return self::$_cacher;
	}

	/**
	 * @desc Устаналвивает кэшер.
	 * @param Data_Provider_Abstract $cacher
	 */
	public static function setCacher ($cacher)
	{
		self::$_cacher = $cacher;
	}

}

/**
 *
 * @desc Исполнитель.
 * Предназначени для запуска функций/методов и кэширования результатов
 * их работы.
 * @author Юрий
 * @package IcEngine
 *
 */
class Executor1
{

	/**
	 * @desc Разделитель частей при формировании ключа для кэширования.
	 * @var string
	 */
	const DELIM = '/';

	/**
	 * @desc Кэшер
	 * @var Data_Provider_Abstract
	 */
	protected static $_cacher;

	/**
	 * @desc Конфиг.
	 * @var array
	 */
	public static $config = array (
		/**
		 * @desc Провайдер данных, используемый для кэширования по умолчанию
		 * (Data_Provider).
		 * @var string
		 */
		'cache_provider'	=> null,
		/**
		 * @desc Описание кэширования для отдельных функций
		 * @var array
		 */
		'functions'			=> array (
		),
		/**
		 * @desc Провайдер поставки тэгов
		 */
		'tag_provider'		=> null,

		'tags'				=> array ()
	);

	/**
	 * @desc Возвращает название функции.
	 * @param function $function Функция.
	 * @return string
	 */
	protected static function _functionName ($function)
	{
		if (is_array ($function))
		{
			if (is_object ($function [0]))
			{
				return get_class ($function [0]) . self::DELIM . $function [1];
			}

			return $function [0] . self::DELIM . $function [1];
		}

		if (is_string ($function))
		{
			return $function;
		}

		return md5 ($function);
	}

	/**
	 * @desc Возвращает ключ для кэширования
	 * @param function $function Кэшируемая функция.
	 * @param array $args Аргкументы функции.
	 * @return string Ключ кэша.
	 */
	protected static function _getCacheKey ($function, array $args)
	{
		$key = self::_functionName ($function) . self::DELIM;

		if ($args)
		{
			$arg = array_shift ($args);
			$key .= md5 (json_encode ($arg));

			if ($args)
			{
				$key .= self::DELIM . md5 (json_encode ($args));
			}
		}

		return $key;
	}

	/**
	 * @desc Выполнение функции подлежащей кэшированию.
	 * @param function $function Функция.
	 * @param array $args Аргументы функции.
	 * @param Objective $options Опции кэширования.
	 * @return mixed Результат выполнения функции.
	 */
	protected static function _executeCaching ($function, array $args,
		Objective $options)
	{
		$key = self::_getCacheKey ($function, $args);

		$expiration = (int) $options->expiration;

		$cache = self::getCacher ()->get ($key);

		$tag_valid = true;

		if (
			$options->current_tags &&
			(
				!isset ($cache ['t']) ||
				array_diff ($options->current_tags->__toArray (), $cache ['t'])
			)
		)
		{
			$tag_valid = false;
		}

		if ($cache)
		{
			if (
				(
					$cache ['a'] + $expiration > time () ||
					$expiration == 0
				) &&
				$tag_valid
			)
			{
				return $cache ['v'];
			}

			if (!self::$_cacher->lock ($key, 5, 1, 1))
			{
				// ключ уже заблокирова параллельным процессом
				return $cache ['v'];
			}
		}

		$value = self::_executeUncaching ($function, $args);

		$cache_value = array (
			'v' => $value,
			'a' => time ()
		);

		$tags = array ();

		if ($options->current_tags)
		{
			foreach ($options->current_tags as $tag => $e)
			{
				$tags [$tag] = $e;
			}
		}

		if ($tags)
		{
			$cache_value ['t'] = $tags;
		}

		self::$_cacher->set (
			$key,
			$cache_value
		);

		if ($cache)
		{
			self::$_cacher->unlock ($key);
		}

		return $value;
	}

	/**
	 * @desc Выполнение функции без кэширования.
	 * @param function $function Функция.
	 * @param array $args Аргументы функции.
	 * @return mixed Результат выполнения функции.
	 */
	protected static function _executeUncaching ($function, array $args)
	{
		return call_user_func_array ($function, $args);
	}

	/**
	 * @desc Возвращает конфиг. Загружет, если он не был загружен ранее.
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (self::$config))
		{
			self::$config = Config_Manager::get (__CLASS__, self::$config);
		}
		return self::$config;
	}

	/**
	 * @desc Выполняет переданную функцию.
	 * @param function $function Функция.
	 * @param array $args Аргументы функции.
	 * @param Objective $options [optional] Опции кэширования.
	 * 		Если не переданы, будут использованы настройки из конфига.
	 * @return mixed Результат выполнения функции.
	 */
	public static function execute ($function, array $args = array (),
		$options = null)
	{
		// Переданы опции
		if ($options)
		{
			return self::_executeCaching ($function, $args, $options);
		}

		// опции заданы в конфиге
		$fn = self::_functionName ($function);
		if (self::config ()->functions && self::$config->functions [$fn])
		{
			return self::_executeCaching (
				$function, $args,
				self::$config->functions [$fn]
			);
		}

		// без кэширования
		return self::_executeUncaching ($function, $args);
	}

	/**
	 * @desc Возвращает текущий кэшер.
	 * @return Data_Provider_Abstract|null
	 */
	public static function getCacher ()
	{
		if (!self::$_cacher)
		{
			if (self::config ()->cache_provider)
			{
				self::$_cacher = Data_Provider_Manager::get (
					self::config ()->cache_provider
				);
			}
			else
			{
				Loader::load ('Data_Provider_Buffer');
				self::$_cacher = new Data_Provider_Buffer ();
			}
		}
		return self::$_cacher;
	}

	/**
	 * @desc Устаналвивает кэшер.
	 * @param Data_Provider_Abstract $cacher
	 */
	public static function setCacher ($cacher)
	{
		self::$_cacher = $cacher;
	}

}
