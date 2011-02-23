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
	 * @desc Конфиг
	 * @var array
	 */
	public static $config = array (
		'default'		=> array (
			
		)
	);
	
	/**
	 * @desc Возвращает транспорт согласно конфигу.
	 * @param Objective $conf
	 * @return Data_Transport
	 */
	protected static function _initTransport (Objective $conf)
	{
		Loader::load ('Data_Transport');
		$transport = new Data_Transport ();
		
		$providers = $conf ['providers'];
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
			self::$config = Config_Manager::get (__CLASS__, self::$config);
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
	 * @desc Сохраняет ресурс
	 * @param string $type
	 * @param string $name
	 * @param mixed $resource
	 */
	public static function set ($type, $name, $resource)
	{
		self::$_resources [$type][$name] = $resource;
		self::transport ($type)->send ($name, $resource);
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