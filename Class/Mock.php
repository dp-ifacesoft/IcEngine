<?php

/**
 * @desc Заместитель классов
 * @author Илья Колесников
 */
class Mock
{
	/**
	 * @desc Зарегистированные методы
	 * @var array <string>
	 */
	protected static $_methods = array ();
	
	/**
	 * @desc Название класса, который будет замещаться
	 * @var string
	 */
	private static $_className;
	
	/**
	 * (non-PHPDoc)
	 * @param string $class_name
	 */
	public function __construct ($class_name)
	{
		$this->_className = $class_name;
	}
	
	/**
	 * (non-PHPDoc)
	 * @param string $key
	 * @return mixed
	 */
	public function __get ($key)
	{
		if (isset (self::$_methods [__METHOD__]))
		{
			return call_user_func_array (
				self::$_methods [__METHOD__],
				array ($this)
			);
		}
		
		return null;
	}
	
	/**
	 * (non-PHPDoc)
	 * @param string $key
	 * @param mixed $value 
	 */
	public function __set ($key, $value)
	{
		
	}
	
	/**
	 * (non-PHPDoc)
	 */
	private function __clone ()
	{
		
	}
	
	/**
	 * (non-PHPDoc)
	 * @param string $method
	 * @param array <mixed> $params
	 * @return mixed
	 */
	public function __call ($method, $params)
	{
		if (isset (self::$_methods [$method]))
		{
			return call_user_func_array (
				self::$_methods [$method],
				$params
			);
		}
	}
	
	/**
	 * @desc Регистрирует метод для заменителя
	 * @param string $method_name
	 * @param $method 
	 */
	public function register ($method_name, $method)
	{
		self::$_methods [$method_name] = $method; 
	}
}