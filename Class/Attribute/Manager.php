<?php
/**
 *
 * @desc Менеджер атрибутов.
 * Получает и устанавливает значения атрибутов модели.
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class Attribute_Manager extends Manager_Abstract
{

	/**
	 * @desc разделитель для формирования ключа.
	 * @var string
	 */
	const DELIM = '/';

	/**
	 * @desc Таблица аттрибутов
	 * @var string
	 */
    const TABLE = 'Attribute';

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		// Источник
		'source'		=> null,
		// Провайдер, используемый для кэширования
		'provider'		=> null
	);

	/**
	 * @desc Место хранения аттрибутов
	 * @var Data_Source_Abstract
	 */
	protected static $_source;

	/**
	 * @desc Провайдер для кэширования
	 * @var Data_Provider_Abstract
	 */
	protected static $_provider;

	/**
	 * @desc Инициализация
	 */
	public static function init ()
	{
		$config = static::config ();

		if ($config ['source'])
		{
			self::$_source = Data_Source_Manager::get ($config ['source']);
		}
		else
		{
			self::$_source = DDS::getDataSource ();
		}

		if ($config ['provider'])
		{
			self::$_provider = Data_Provider_Manager::get (
				$config ['provider']
			);
		}
	}

	/**
	 * @desc Удаляет все атрибуты модели.
	 * @param Model $model
	 */
	public static function deleteFor (Model $model)
	{
		$source = self::getSource($model->table());
	    $source->execute (
	        Query::instance ()
				->delete ()
				->from (self::TABLE)
				->where ('table', $model->table ())
				->where ('rowId', $model->key ())
	    );

		if (self::$_provider)
		{
			self::$_provider->deleteByPattern (
				$model->table () . '/' .
				$model->key () . '/'
			);
		}
	}

	/**
	 * @desc Получение значения атрибута.
	 * @param Model $model Модель.
	 * @param string $key Название атрибута.
	 * @return mixed Значение атрибута.
	 */
	public static function get (Model $model, $key)
	{
		$table = $model->table ();
		$row = $model->key ();

		if (self::$_provider)
		{
			$prov_key = $table . self::DELIM . $row . self::DELIM . $key;

			$v = self::$_provider->get ($prov_key);

			if ($v)
			{
				return $v ['v'];
			}

		}

		$source = self::getSource($model->table());
		$value = $source->execute (
			Query::instance ()
			->select ('value')
			->from (self::TABLE)
			->where ('table', $table)
			->where ('rowId', $row)
			->where ('key', $key)
		)->getResult ()->asValue ();

		if (self::$_provider)
		{
			$value = json_decode ($value, true);

			self::$_provider->set (
				$prov_key,
				array (
					't'	=> $table,
					'r'	=> $row,
					'k'	=> $key,
					'v'	=> $value
				)
			);

			return $value;
		}

		return json_decode ($value, true);
	}

	/**
	 * @param string $modelName
	 */
	protected static function getSource($modelName)
	{
		$config = static::config();
		$sources = $config->sources;
		if ($sources[$modelName]) {
			$source = Data_Source_Manager::get($sources[$modelName]);
		} else {
			$source = self::$_source;
		}
		return $source;
	}

	/**
	 * @desc Задание значения атрибуту.
	 * @param Model $model Модель.
	 * @param string|array $key Название атрибута.
	 * @param mixed $value Значение атрибута.
	 */
	public static function set (Model $model, $key, $value)
	{
	    $table = $model->table ();
	    $row = $model->key ();

	    $query = Query::instance ()
			->delete ()
			->from (self::TABLE)
			->where ('table', $table)
			->where ('rowId', $row);

	    if (!is_array ($key))
	    {
	        $query->where ('key', $key);
	   		$key = array (
	   			$key => $value
	   		);
	    }
	    else
	    {
            $query->where ('key', array_keys ($key));
	    }

		$source = self::getSource($model->table());
	    $source->execute ($query);

		$pref = $table . self::DELIM . $row . self::DELIM;

		foreach ($key as $k => $v)
		{
			$source->execute (
				Query::instance ()
					->insert (self::TABLE)
					->values (array(
						'table'	=> $table,
						'rowId'	=> $row,
						'key'	=> $k,
						'value'	=> addslashes(json_encode ($v))
					))
			);

			if (self::$_provider)
			{
				self::$_provider->set (
					$pref . $k,
					array (
						't'	=> $table,
						'r'	=> $row,
						'k'	=> $k,
						'v'	=> $v
					)
				);
			}
	    }

	}

}