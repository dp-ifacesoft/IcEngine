<?php
/**
 *
 * @desc Генератор уникальных ключей
 * @author Yury Shveodv, Ilya Kolesnikov
 * @package IcEngine
 *
 */
class Key_Generator
{

	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected static $_config = array (
		// Провайдер
		'provider'		=> null,
		// Минимальное значение
		'min_value'		=> 1,
		// Максимальное значение
		'max_value'		=> 100000000
	);

	/**
	 * @desc Провайдер для хранения текущего значения
	 * @var Data_Provider_Abstract
	 */
	protected static $_provider;

	/**
	 * @desc Вовзращает конфиги
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
	 * @desc Генерирует новый ключ
	 * @param string|Model $model
	 * @return integer
	 */
	public static function get ($model = 'def')
	{
		if (is_object ($model))
		{
			$model = $model->modelName ();
		}

		$provider = self::provider ();
		$val = $provider->increment ($model);
		if ($val < self::config ()->min_value)
		{
			if (!$provider->lock ($model, 1, 5, 100))
			{
				throw new Exception ('Failed to lock key value');
			}

			$val = self::load ($model, self::config ()->min_value);

			$provider->set ($model, $val);

			$provider->unlock ($model);

			$val = $provider->increment ($model);
		}

		self::save ($model, $val);

		return $val;
	}

	/**
	 * @desc Возвращает название файла с бэкапом ключей.
	 * @return string
	 */
	public static function lastFile ($model)
	{
		$dir = IcEngine::root () . 'Ice/Var/Key/Generator/' .
			urlencode (Helper_Site_Location::getLocation ());

		if (!is_dir ($dir))
		{
			mkdir ($dir, 0666);
			chmod ($dir, 0666);
		}

		return $dir . '/' . urlencode ($model) . '.txt';
	}

	/**
	 * @desc Загрузка значения из надежного хранилища
	 * @param string $model
	 * @param integer $min
	 */
	public static function load ($model, $min)
	{
		$file = self::lastFile ($model);

		if (file_exists ($file))
		{
			$val = file_get_contents ($file);
		}
		else
		{
			$ds = Model_Scheme::dataSource ($model);
			$kf = Model_Scheme::keyField ($model);
			$val = $ds->execute (
				Query::instance ()
					->select ($kf)
					->from ($model)
					->where ("`$kf` < ?", self::config ()->max_value)
					->order (array ($kf => Query::DESC))
					->limit (1)
			)->getResult ()->asValue ();
		}

		return max ($val, $min) + 7; // magic 7
	}

	/**
	 * @desc Провайдер
	 * @return Data_Provider_Abstract
	 */
	public static function provider ()
	{
		if (!self::$_provider)
		{
			self::$_provider = Data_Provider_Manager::get (
				self::config ()->provider
			);
		}
		return self::$_provider;
	}

	/**
	 * @desc Дублирование значения в файл
	 * @param string $model
	 * @param integer $value
	 */
	public static function save ($model, $value)
	{
		$file = self::lastFile ($model);
		file_put_contents ($file, $value);
	}

}