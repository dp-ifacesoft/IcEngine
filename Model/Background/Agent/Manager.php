<?php
/**
 *
 * @desc Менеджер фоновых процессов.
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class Background_Agent_Manager extends Manager_Abstract
{

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (

		//
		'default_agent_resume_id'	=> 0,

		/**
		 * @desc Время в секундах после последней активности процесса,
		 * после которого состояние процесса будет установлено в ERROR.
		 * @var integer
		 */
		'process_to_error_time'		=> 300,

		/**
		 * @desc Время в секундах после последней активности процесса,
		 * после которого процесс будет перезапущен
		 * @var integer
		 */
		'process_to_restart_time'	=> 600,
	);

	/**
	 * @desc Экзмепляр менджера
	 * @var Background_Agent_Manager
	 */
	protected static $_instance;

	private function __construct ()
	{

	}

	/**
	 * @desc Поиск зависших сессий. Сессии, чье время последнего апдейта
	 * превышает заданное будут помечены как fail.
	 * @return integer Количество зависших сессий
	 */
	public function checkErrors ()
	{
		$time_limit = (int) $this->config ()->process_to_error_time;

		$sessions = new Background_Agent_Session_Collection ();
		$sessions->addOptions (array (
			array (
				'name'	  	  => 'processExpiration',
				'time_limit'  => $time_limit
			)
		));

		foreach ($sessions as $session)
		{
			$session->updateState (Helper_Process::FAIL);
		}

		return $sessions->count ();
	}

	/**
	 * @desc Перезапуск сессий, помеченных как зависшие.
	 * @return integer Количество перезапущенных процессов.
	 */
	public function checkRestarts ()
	{
		$time_limit = (int) $this->config ()->process_to_restart_time;

		$sessions = new Background_Agent_Collection ();
		$sessions->addOptions (array (
			array (
				'name'			=> 'restartExpiration',
				'time_limit'	=> $time_limit
			)
		));

		foreach ($sessions as $session)
		{
			/**
			 * @var Background_Agent
			 */
			$session->updateState (Helper_Process::PAUSE);
			$this->resumeSession ($session);
		}

		return $sessions->count ();
	}

	public static function instance ()
	{
		if (!self::$_instance)
		{
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * @desc Запуск рабочей итерации любой незавершенной сесси фонового
	 * агента заданного класса.
	 * @param string $name Название фонового агента.
	 */
	public function processAgent ($name)
	{
		$agent = Model_Manager::byQuery (
			'Background_Agent',
			Query::instance ()
				->where ('name', $name)
		);

		if (!$agent)
		{
			return;
		}

		/**
		 * @desc Незавершенная сессия.
		 * @var Background_Agent_Session
		 */
		$session = Model_Manager::byQuery (
			'Background_Agent_Session',
			Query::instance ()
				->where ('Background_Agent__id', $agent->key())
				->where ('state', Helper_Process::PAUSE)
		);

		if ($session)
		{
			$session->process ();
		}
		else
		{
			echo "no background agent sessions\n";
		}
	}

	/**
	 * @desc Перезапустить агента
	 * @param Background_Agent_Session $session
	 */
	public static function resumeSession (Background_Agent_Session $session)
	{
		if ($session->Background_Agent_Resume)
		{
			$session->Background_Agent_Resume->resume ($session);
		}
	}

	/**
	 * @desc Запуск фонового агента
	 * @param string $name Название.
	 * @param array $params Параметры.
	 */
	public function startAgent ($name, array $params = array ())
	{
		$agent = Model_Manager::byQuery (
			'Background_Agent',
			Query::instance ()
				->where ('name', $name)
		);

		if (!$agent)
		{
			return;
		}

		$session = new Background_Agent_Session (array (
			'Background_Agent__id'			=> $agent->id,
			'startTime'						=> Helper_Date::toUnix (),
			'iteration'						=> 0,
			'finishTime'					=> Helper_Date::toUnix (),
			'updateTime'					=> Helper_Date::toUnix (),
			'params'						=> json_encode ($params),
			'state'							=> Helper_Process::NONE,
			'Background_Agent_Resume__id'	=>
				isset ($params ['Background_Agent_Resume__id']) ?
					$params ['Background_Agent_Resume__id'] :
					$this->config ()->default_agent_resume_id
		));

		$session->start ();
		$this->resumeSession ($session);
	}

}
