<?php

class Observer
{
	
	/**
	 * Объекты
	 * @var array
	 */
	private static $_objects = array ();
	
	/**
	 * Отслеживаемые события
	 * @var array
	 */
	private static $_events = array ();
	
	/**
	 * Добавление обработчика на событие объекта
	 * 
	 * @param mixed $object
	 * 		Объект, для которого устанавливается событие
	 * @param string $event
	 * 		Событие, на которое будет вызываться метод
	 * @param function $method
	 * 		Метод, который будет вызываться при наступлении события
	 */
	public function appendObject ($object, $event, $method)
	{
		$o = array_search ($object, self::$_objects);
		if ($o === false)
		{
			$o = count (self::$_objects);
			self::$_objects [$o] = $object;
			self::$_events [$o] = array ();
		}
		
		if (isset(self::$_events [$o][$event]))
		{
			self::$_events [$o][$event][] = $method;
		}
		else
		{
			self::$_events [$o][$event] = array ($method);
		}
	}
	
	
	/**
	 * Оповещение из объекта о наступлении события
	 * 
	 * @param mixed $object
	 * 		Объект
	 * @param string $event
	 * 		Событие
	 * @param array $args
	 * 		Параметры, которые будут переданы обработчикам
	 * @param array $results
	 * 		В эту переменную будут переданны результаты работы обработчиков
	 * @param function $callback
	 * 		Вызывается после вызова каждого обработчика
	 * @return null|integer|true
	 * 		null Если событий не назначено
	 * 		true Если выполнены все события и процесс не был прерван
	 * 		integer Шаг, на котором была прервана цепочка вызовов
	 */
	public static function call ($object, $event, array $args, array &$results, $callback = null)
	{
		$o = array_search($object, self::$_objects);
		if (!isset (self::$_events[$o]) || !isset(self::$_events [$o][$event]))
		{
			return null;
		}
		
		$results = array ();
		
		foreach (self::$_events [$o][$event] as $i => $method)
		{
			$result = call_user_func_array($method, $args);
			$results [] = $result;
			
			if (!$callback)
			{
				continue;
			}
			
			if (call_user_func($callback, $args, $result))
			{
				return $i;
			}
			
		}
		
		return true;
	}
}