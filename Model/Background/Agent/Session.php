<?php
/**
 *
 * @desc Сессия фонового агента
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Background_Agent_Session extends Model
{

	/**
	 * @desc Параметры
	 * @var string
	 */
	protected $_params = array ();

	/**
	 * (non-PHPdoc)
	 * @see Model::_afterConstruct()
	 */
	protected function _afterConstruct ()
	{
		$this->_params = json_decode ($this->params, true);
		if (!isset ($this->_fields ['id']))
		{
			$this->_fields ['key'] = time () . rand (10000, 99999);
		}
	}

	/**
	 * @desc Остановка. Вызывается из агента.
	 */
	public function finish ($state = Helper_Process::SUCCESS)
	{
		$this->updateState ($state);
	}

	/**
	 * @desc Параметры
	 * @return array
	 */
	public function getParams ()
	{
		return $this->_params;
	}

	/**
	 * @desc Процесс
	 */
	public function process ()
	{
		$this->updateState (Helper_Process::ONGOING);

		$this->Background_Agent->process ($this);

		$update = array (
			'updateTime'	=> Helper_Date::toUnix (),
			'params'		=> json_encode ($this->_params),
			'iteration'		=> $this->iteration + 1
		);

		if ($this->state == Helper_Process::ONGOING)
		{
			$update ['state'] = Helper_Process::PAUSE;
		}

    	$this->update ($update);
	}

	/**
	 * (non-PHPdoc)
	 * @see Model::save()
	 */
	public function save ($hard_insert = false)
	{
		$this->params = json_encode ($this->_params);
		return parent::save ($hard_insert);
	}

	/**
	 * @desc Устанавливает параметры
	 * @param array $params
	 */
	public function setParams (array $params)
	{
		$this->_params = $params;
	}

	/**
	 * @desc Запуск
	 * @param array $params
	 */
	public function start ()
	{
		$this->Background_Agent->start ($this);

		$this->update (array (
			'iteration'		=> 0,
			'state'			=> Helper_Process::PAUSE,
			'updateTime'	=> Helper_Date::toUnix ()
		));
	}

	/**
	 * @desc Остановка процесса
	 */
	public function stop ()
	{
		$this->updateState (Helper_Process::STOPED);
	}

	/**
	 * @desc Обновить состояние процесса.
	 * При длительных процессах необходимо периодически вызывать для
	 * предотвращения пометки процесса как зависшего.
	 * @param integer $state Новое состояние (если необходимо изменить)
	 */
	public function updateState ($state = null)
	{
		$time = Helper_Date::toUnix ();

		// не обновляем чаще, чем раз в секунду
		if (
			$time == $this->updateTime &&
			($state == null || $state == $this->state)
		)
		{
			return ;
		}

		$this->update (array (
			'state'				=> $state == null ? $this->state : $state,
			'updateTime'		=> $time
		));
	}

}