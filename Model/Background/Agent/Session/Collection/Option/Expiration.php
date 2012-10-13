<?php
/**
 * 
 * @desc Опция для получения истекших по времени процессов
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Background_Agent_Session_Collection_Option_Expiration extends Model_Collection_Option_Abstract
{
	
	/**
	 * Вызывается перед выполнением запроса.
	 * Переменная <i>$query</i> отличается от запроса, возвращаемого методом
	 * <i>$colleciton->query()</i>. По умолчанию эта переменная - клон
	 * изначального запроса коллекции, на который наложены опции.
	 * @param Model_Collection $collection
	 * @param Query $query
	 * @param array $params
	 */
	public function before (Model_Collection $collection, 
		Query $query, array $params)
	{
		$query->where (
			'TIME_TO_SEC(TIMEDIFF(updateTime, NOW()))>?', 
			$params ['expiration']
		);
	}
	
}
//SELECT TIME_TO_SEC(TIMEDIFF('2010-08-20 12:01:00', '2010-08-20 12:00:00'))