<?php
/**
 *
 * @desc Класс хранящий и предоставляющий информацию о схеме моделей.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
abstract class Model_Scheme
{

	/**
	 * @desc Имя ключего поля модели по умолчанию.
	 * @var string
	 */
    const DEFAULT_KEY_FIELD = 'id';

	/**
	 * @desc Схема линка по умолчанию
	 * @var array
	 */
	public static $defaultLinkScheme = array (

	);

	/**
	 * @desc Префикс по умолчанию для всех таблиц
	 * @var string
	 */
	public static $default = array (
		'keyGen'	=> null,
		'prefix'	=> 'ice_'
	);

	/**
	 * @desc Модели
	 * @var array
	 */
	public static $models = array (
		/**
		 * @desc Класс модели.
		 * Обязательно в нижнем регистре.
		 * @var array
		 */
		'abstract'	=> array (
			/**
			 * Ключевое поле.
			 * @var string
			 */
			'key'		=> 'id',
			/**
			 * Префикс таблицы.
			 * @var string
			 */
			'prefix'	=> '',
			/**
			 * Источник данных о модели.
			 * @var string
			 */
			'source'	=> 'user_session',
			/**
			 * Существующие индексы (могут использоваться в источнике).
			 * @var array
			 */
			'indexes'	=> array (
				array ('phpSessionId')
			)
		),
		/**
		 * @desc Название таблицы, которое не должно изменяться
		 * при построении запроса.
		 * @var string
		 */
		'table'	=> '',
		/**
		 * @desc Альтернативное название модели.
		 * @var string
		 */
		'alt_name'	=> 'abstract'
	);

	/**
	 * @desc Схемы моделей
	 * @var array
	 */
	private static $_modelSchemes = array ();

	/**
	 * @desc Схема по умолчанию
	 * @var array
	 */
	private static $_defaultScheme = array (
		'int'		=> array (
			'size'		=> 11,
		),
		'tinyint'	=> array (
			'size'		=> 1
		),
		'varchar'	=> array (
			'collation'	=> 'utf8_general_ci',
			'default'	=> ''
		)
	);

	/**
	 * @desc Создает схему модели на основании полей,
	 * полученных от mysql
	 * @param array $fields
	 * @return array
	 */
	public static function makeScheme($fields)
	{
		$scheme = array();
		if (!$fields) {
			return;
		}
		foreach ($fields as $field) {
			$size = null;
			$auto_inc = false;
			if (!empty($field ['Extra']) || !empty($field['Auto_Increment'])) {
				$auto_inc = true;
			}
			$type = $field['Type'];
			$br_pos = strpos($type, '(');
			if ($br_pos !== false) {
				$size = (int) trim(substr ($type, $br_pos), '()');
				$type = substr($type, 0, $br_pos);
			} elseif (!empty($field['Size'])) {
				$size = $field['Size'];
			}
			$comment = isset($field['Comment']) ? $field['Comment'] : '';
			$default = isset($field['Default']) ? $field['Default'] : null;
			$s = array (
				'type'		=> ucfirst($type),
				'comment'	=> $comment
			);
			if ($auto_inc) {
				$s['auto_inc'] = true;
			}
			if (strpos(strtolower($type), 'text') === false &&
				strpos(strtolower($type), 'date') === false) {
				$s['size'] = $size;
				if (!$auto_inc) {
					if (strpos(strtolower($type), 'int') !== false) {
						if (!is_null($default) && is_numeric($default)) {
							$s['default'] = $default;
						}
					} else {
						if (!is_null($default)) {
							if (strpos(strtolower($type), 'varchar') === false) {
								$s['default'] = $default;
							} else {
								if (strlen($default)) {
									$s['default'] = $default;
								}
							}
						}
					}
				}
			}
			$field = $field['Field'];
			$scheme[$field] = $s;
		}

		return $scheme;
	}

	/**
	 * @desc Инициализация схемы моделей
	 * @param Config_Array $config
	 */
	public static function init (Config_Array $config)
	{
		if ($config->default)
		{
			self::$default = $config->default;
		}

		if ($config->models)
		{
			self::$models = $config->models->__toArray ();
		}
	}

	/**
	 * @desc Генерирует новый ID для модели, если для этого в схеме заданы
	 * правила.
	 * @param Model $model
	 * @return string|null.
	 * 		Сгенерированный ключ или null, если правила не заданы.
	 */
	public static function generateKey (Model $model)
	{
		$name = strtolower ($model->modelName ());

		if (!isset (self::$models [$name], self::$models [$name]['keyGen']))
		{
			if (!isset (self::$default ['keyGen']))
			{
				return null;
			}
			$keygen = explode ('::', self::$default ['keyGen'], 2);
		}
		else
		{
			$keygen = explode ('::', self::$models [$name]['keyGen'], 2);
		}

		if (count ($keygen) != 2)
		{
			return null;
		}

		return call_user_func ($keygen, $model);
	}

	/**
	 * @desc Получение реального имени таблицы в БД.
	 * @param string|Model $model Имя модели или экземпляр класса модели.
	 * @return string Действительное имя таблицы.
	 */
	public static function table ($model)
	{
		if (is_array ($model))
		{
			var_dump ($model);
			echo '<pre>';
			debug_print_backtrace ();
			echo '</pre>';
		}

	    $model = strtolower (
	    	is_object ($model) ? $model->modelName () : $model
	    );

		if (isset (self::$models [$model]))
		{
			if (is_string (self::$models [$model]))
			{
				if (empty (self::$models [$model]))
				{
					return $model;
				}
				$model = self::$models [$model];
			}

			if (isset (self::$models [$model]['table']))
			{
				return self::$models [$model]['table'];
			}
			elseif (isset (self::$models [$model]['prefix']))
			{
				return self::$models [$model]['prefix'] . $model;
			}
		}
		elseif (strpos ($model, '`') !== false)
		{
			return $model;
		}

		return self::$default ['prefix'] . $model;

//		$prefix = isset ($this->prefixes [$model]) ?
//			$this->prefixes [$model] :
//			$this->defaultPrefix;
//
//		$table = defined ($model) ? constant ($model) : $model;
//
//		return $prefix . $table;
	}

	/**
	 * @desc Определеяет название модели по названию таблицы.
	 * @param string $table
	 * @return string
	 */
	public static function tableToModel ($table)
	{
		$prefix = self::$default ['prefix'];

		foreach (self::$models as $name => $model)
		{
			if (isset ($model ['table']) && $model ['table'] == $table)
			{
				$name = explode ('_', $name);

				$name = array_map ('ucfirst', $name);
				return implode ('_', $name);
			}
		}

		$table = explode ('_', $table);

		if ($table [0] . '_' == $prefix)
		{
			array_shift ($table);
		}

		$table = array_map ('ucfirst', $table);
		return implode ('_', $table);
	}

	/**
	 * @desc Источник данных для модели.
	 * @param string $model название модели.
	 * @return Data_Source_Abstract
	 */
	public static function dataSource ($model)
	{
		$model = strtolower ($model);

		if (!isset (self::$models [$model], self::$models [$model]['source']))
		{
			return DDS::getDataSource ();
		}

		$name = self::$models [$model]['source'];

		return Data_Source_Manager::get ($name);
	}

	/**
	 * @desc Возвращает названия полей модели.
	 * @param string $model Название модели.
	 * @return array <string> Массив названий полей.
	 */
	public static function fieldsNames ($model)
	{
		return Helper_Data_Source::fields ($model)->column ('Field');
	}

	/**
	 * @desc Получить схему модели
	 * @param string $model_name
	 * @return array
	 */
	public static function getScheme ($model_name)
	{
		if (!isset (self::$_modelSchemes [$model_name]))
		{
			$scheme = Resource_Manager::get (
				__CLASS__,
				$model_name
			);

			if (!$scheme)
			{
				if (!Loader::tryLoad($model_name)) {
					return;
				}
				$scheme = $model_name::scheme ();
			}
			$comment = null;
			if (empty ($scheme ['fields']))
			{
				$config = Config_Manager::get('Model_Mapper_' . $model_name);
				$comment = $config->comment;
				$fields = array();
				if ($config && $config->fields) {
					$tmp = $config->fields->__toArray();
					if ($tmp) {
						foreach ($tmp as $name => $values) {
							$fields[$name] = array(
								'Field' => $name,
								'Type'	=> $values[0]
							);
							foreach ($values[1] as $key => $value) {
								if (is_numeric($key)) {
									$key = $value;
									$value = true;
								}
								$fields[$name][$key] = $value;
							}
						}
					}
				}

				if ($fields) {
					$fields = self::makeScheme ($fields);
					$scheme = array (
						'comment'	=> $comment,
						'fields'	=> $fields,
						'keys'		=> array ()
					);
				}

			}

			self::setScheme ($model_name, $scheme);
		}

		return self::$_modelSchemes [$model_name];
	}

	/**
	 * @desc Индексы модели.
	 * @param string $model Название модели.
	 * @return array Массив индексов.
	 */
	public static function indexes ($model)
	{
		$model = strtolower ($model);

		if (!isset (self::$models [$model], self::$models [$model]['indexes']))
		{
			return array ();
		}

		return self::$models [$model]['indexes'];
	}

	/**
	 * @desc Ключевое поле для модели.
	 * @param string $model Название модели.
	 * @return string Имя ключевого поля.
	 */
	public static function keyField ($model)
	{
		$model = strtolower ($model);

		if (!isset (self::$models [$model], self::$models [$model]['key']))
		{
			return self::DEFAULT_KEY_FIELD;
		}

		return self::$models [$model]['key'];
	}

	/**
	 * @desc Получить все ссылки модели
	 * @param string $model1
	 * @return array
	 */
	public static function links ($model1)
	{
		$model1 = strtolower ($model1);

		return !empty (self::$models [$model1]['links'])
			? self::$models [$model1]['links']
			: null;
	}

	/**
	 * @desc Возвращает схему связи.
	 * @param string $model1
	 * @param string $model2
	 * @return array
	 */
	public static function linkScheme ($model1, $model2)
	{
		$model1 = strtolower ($model1);

	    return empty (self::$models [$model1]['links'][$model2])
			? self::$defaultLinkScheme
			: self::$models [$model1]['links'][$model2];
	}

	/**
	 * @desc Получить опшины схемы для модели
	 * @param string $model_name
	 * @return array|null
	 */
	public static function modelOptions ($model_name)
	{
		$model_name = strtolower ($model_name);
		return isset (self::$models [$model_name]['options'])
			? self::$models [$model_name]['options']
			: null;
	}

	/**
	 * @desc Изменить схему модели
	 * @param Model $model
	 */
	public static function setScheme ($model, $scheme = null)
	{
		$model_name = $model;

		if ($model instanceof Model)
		{
			$model_name = $model->modelName ();
			$scheme = $model->scheme ();
		}

		if (isset ($scheme ['fields']))
		{
			foreach ($scheme ['fields'] as &$field)
			{
				if (isset (self::$_defaultScheme [$field ['type']]))
				{
					$field = array_merge (
						$field,
						self::$_defaultScheme [$field ['type']]
					);
				}
			}
		}

		self::$_modelSchemes [$model_name] = $scheme;

		Resource_Manager::set (
			__CLASS__,
			$model_name,
			$scheme
		);
	}

}