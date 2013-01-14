<?php
/**
 *
 * @desc Менеджер ресурсов
 * @author Юрий
 * @package IcEngine
 *
 */
class Resource_Manager
{

	/**
	 * @desc Транспорты для ресурсов по типам.
	 * @var array
	 */
	protected static $_transports = array ();

	/**
	 * @desc Загруженные ресурсы
	 * @var array
	 */
	protected static $_resources = array ();

	/**
	 * @desc Обновленные в процессе ресурсы.
	 * Необходимо для предотвращения постоянной записи неизменяемых ресурсов.
	 * @var array <boolean>
	 */
	protected static $_updatedResources = array ();

	/**
	 * @desc Конфиг
	 * @var array
	 */
	public static $config = array (
		/**
		 * @desc По умолчанию
		 * @var array
		 */
		'default'	=> array (),
		/**
		 * @desc Во избезажании рекурсивного вызова Config_Manager'a
		 * @var array
		 */
		'Resource_Manager'	=> array ()
	);



	/**
	 * @desc Возвращает транспорт согласно конфигу.
	 * @param Objective $conf
	 * @return Data_Transport
	 */
	protected static function _initTransport (Objective $conf)
	{
		$transport = new Data_Transport ();

		$providers = $conf->providers;

		if ($providers)
		{
			if (is_string ($providers))
			{
				$providers = array ($providers);
			}

			foreach ($providers as $name)
			{
				$transport->appendProvider (
					Data_Provider_Manager::get ($name)
				);
			}

			// Входные фильтры
			if ($conf->inputFilters)
			{
				foreach ($conf->inputFilters as $filter)
				{
					$transport->inputFilters ()->append (
						Filter_Manager::get ($filter)
					);
				}
			}

			// Выходные фильтры
			if ($conf->outputFilters)
			{
				foreach ($conf->outputFilters as $filter)
				{
					$transport->outputFilters ()->append (
						Filter_Manager::get ($filter)
					);
				}
			}
		}

		return $transport;
	}

	/**
	 * @desc Возвращает конфиг. Загружает, если он не был загружен ранее.
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (self::$config))
		{
			self::$config = Config_Manager::getReal (__CLASS__, self::$config);
		}

		return self::$config;
	}

	/**
	 * @desc Возвращает Ресурс указанного типа по идентификатору.
	 * @param string $type Тип ресурса.
	 * @param string $name|array Идентификатор ресурса или ресурсов.
	 * @return mixed
	 */
	public static function get ($type, $name)
	{
		if (!isset (self::$_resources [$type][$name]))
		{
			self::$_resources [$type][$name] =
				self::transport ($type)->receive ($name);
		}

		return self::$_resources [$type][$name];
	}

	/**
	 * @desc Получить обновленные ресурсы
	 * @param string $type
	 */
	public static function getUpdated ($type)
	{
		return self::$_resources [$type];
	}

	/**
	 * @desc Слить объекты хранилища
	 */
	public static function save ()
	{
		foreach (self::$_resources as $type => $resources)
		{
			foreach ($resources as $name => $resource)
			{
				if (isset (self::$_updatedResources [$type][$name]))
				{
					self::transport ($type)->send ($name, $resource);
				}
			}
		}
	}

	/**
	 * @desc Сохраняет ресурс
	 * @param string $type
	 * @param string $name
	 * @param mixed $resource
	 */
	public static function set ($type, $name, $resource)
	{
		self::$_updatedResources [$type][$name] = true;

		if (Tracer::$enabled) {
			if ($type == 'Model') {
				if (!isset(self::$_resources[$type][$name])) {
					Tracer::incDeltaModelCount();
					Tracer::incTotalModelCount();
				}
			}
		}
		self::$_resources [$type][$name] = $resource;
	}

	/**
	 * @desc Обновить ресурс
	 * @param string $type
	 * @param string $name
	 * @param mixed $updated
	 */
	public static function setUpdated ($type, $name, $updated)
	{
		self::$_updatedResources [$type][$name] = $updated;
	}

	/**
	 * @desc Возвращает транспорт для ресурсов указанного типа.
	 * @param string $type Тип ресурса.
	 * @return Data_Transport Транспорт данных.
	 */
	public static function transport ($type)
	{
		if (!isset (self::$_transports [$type]))
		{
			$conf = self::config ()->$type;
			self::$_transports [$type] =
				$conf ?
				self::_initTransport ($conf) :
				self::_initTransport (self::$config->default);
		}

		return self::$_transports [$type];
	}

}