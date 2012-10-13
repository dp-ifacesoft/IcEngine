<?php
/**
 * Объектный пул предназначен для хранения неиспользуемых объектов.
 * После окончания использования объекта, необходимо вызвать его метод free,
 * в результате чего объект попадет в пул, откуда может быть изъят (Object_Pool::pop)
 * для повторного использования.
 * 
 * Не используется.
 * 
 * Как показали тесты, выигрыша в скорости/потреблении памяти нет.
 * 
 * @package IcEngine
 * @deprecated
 * 
 */

abstract class Object_Pool
{
	
	/**
	 * Пул освобожденных объектов
	 * @var array
	 */
	public static $_pool = array ();
	
	private function __construct ()
	{
	}
	
	private function __clone ()
	{
	}
	
	/**
	 * Помещает объект в пул
	 * 
	 * @param Object_Interface $object
	 * 		Объект, который может позже быть использован
	 */
	public static function push ($object)
	{
		$className = get_class ($object);
		self::$_pool [$className][] = $object;
	}
	
	/**
	 * Возвращает вновь созданный или более не используемый объект заданного класса.
	 * 
	 * @param string $className
	 * 		Имя класса
	 * @return Object_Interface
	 * 		Взятый из пула, либо вновь созданный объект
	 */
	public static function pop ($className)
	{
		if (!isset (self::$_pool [$className]) || empty (self::$_pool [$className]))
		{
			return new $className;
		}
		$object = array_pop (self::$_pool [$className]);
		$object->reset ();
		return $object;
	} 
}