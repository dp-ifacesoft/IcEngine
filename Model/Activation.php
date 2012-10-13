<?php
/**
 * 
 * @desc Активация чего-либо, требующая код подтверждения от пользователя.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Activation extends Model
{
	
	/**
	 * @desc Время завершения по умолчанию.
	 * @var string
	 */
	const EMPTY_FINISH_TIME = '2000-01-01';
	
	/**
	 * @desc Находит активацию по коду, при условии, что она не 
	 * истекла по времени.
	 * @param string $code Код активации.
	 * @return Activation|null Найденная активация.
	 */
	public static function byCode ($code, $type = '')
	{
		return Model_Manager::byQuery (
			'Activation',
			Query::instance ()
				->where ('type', $type)
				->where ('code', $code)
				->where ('expirationTime<?', Helper_Date::toUnix ())
		);
	}
	
	/**
	 * @desc Создает и возвращает новую активацию.
	 * @param array $params Параметры активации.
	 * $params ['address'] Адрес для отправки сообщения.
	 * $params ['type'] [optional] Тип (smsauth/review и т.п.)
	 * $params ['code'] Код активации.
	 * $params ['User__id'] [optional] Если не передан, id текущего.
	 * $params ['expirationTime'] Время, когда активация станет 
	 * недействительной (в формате UNIX).
	 * $params ['callbackMessage'] Сообщение, попадающие в Message_Queue
	 * 	после успешной активации.
	 * @return Activation Созданная активация.
	 */
	public static function create (array $params)
	{
		$activation = new Activation (array (
			'address'			=> $params ['address'],
			'type'				=> 
				isset ($params ['type']) ? 
					$params ['type'] :
					'',
			'code'				=> $params ['code'],
			'finished'			=> 
				isset ($params ['finished']) ?
					$params ['finished'] :
					0,
			'createTime'		=> Helper_Date::toUnix (),
			'finishTime'		=> self::EMPTY_FINISH_TIME,
			'expirationTime'	=> 
				isset ($params ['expirationTime']) ?
					$params ['expirationTime'] :
					'2040-01-01 00:00:00',
			'User__id'			=> 
				isset ($params ['User__id']) ?
					$params ['User__id'] :
					User::id (),
			'createIp'			=> Request::ip (),
			'finishIp'			=> '',
			'day'				=> Helper_Date::eraDayNum (),
			'callbackMessage'	=> 
				isset ($params ['callbackMessage']) ?
					$params ['callbackMessage'] :
					''
		));
		return $activation->save ();
	}
	
	/**
	 * @desc Успешное окончание активации
	 * @return Activation Эта активация.
	 */
	public function activate ()
	{
		$this->update (array (
			'finished'		=> 1,
			'finishTime'	=> Helper_Date::toUnix (),
			'finishIp'		=> Request::ip ()
		));
		
		// Сообщение об успешной активации
		if ($this->callbackMessage)
		{
			IcEngine::$messageQueue->push (
				$this->callbackMessage,
				array (
					'activation'	=> $this
				)
			);
		}
		
		return $this;
	}
	
}