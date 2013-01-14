<?php
/**
 *
 * @desc Абстрактный класс фонового агента.
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
abstract class Background_Agent_Abstract extends Model_Factory_Delegate
{

	/**
	 * @desc Сессия
	 * @var Background_Agent_Session
	 */
	protected $_session = null;

	/**
	 * @desc Параметры процесса
	 * @var array
	 */
	protected $_params = array ();

	/**
	 * (non-PHPdoc)
	 * @see Model::_afterConstruct()
	 */
	protected function _afterConstruct ()
	{

	}

	/**
	 * @desc Завершение процесса
	 */
	abstract protected function _finish ();

	/**
	 * @desc Запись в лог.
	 * Так же обновляет состояние процесса.
	 * @param string $file __FILE__
	 * @param string $line __LINE__
	 * @param string $text Сообщение
	 */
	protected function _log ($file, $line, $text = '')
	{
		$log = new Background_Agent_Log (array (
			'agent'			=> $this->name,
			'sessionId'		=> $this->id,
			'time'			=> Helper_Date::toUnix (),
			'file'			=> basename ($file),
			'line'			=> $line,
			'text'			=> $text
		));
		$log->save ();
		$this->_session->updateState ();
	}

	/**
	 * @desc Процесс
	 */
	abstract protected function _process ();

	/**
	 * @desc Метод вызывается при запуске процесса.
	 * Предназначен для инициализации $_params
	 */
	abstract protected function _start ();

	/**
	 * @desc Остановка процесса.
	 * @param integer $state Код остановки
	 */
	protected function finish ($state = Helper_Process::SUCCESS)
	{
		$this->_log (__FILE__, __LINE__, 'finishing');

		$this->_finish ();

		$this->_log (__FILE__, __LINE__, 'finish ok');

		$this->_session->setParams ($this->_params);
		$this->_session->finish ($state);
	}

    /**
     * @desc Процесс
     * @param array $params
     * 		Параметры.
     * 		Будут сохранены и переданы при следующей итерации.
     */
    public function process (Background_Agent_Session $session)
    {
    	$this->_session = $session;
    	$this->_params = $session->getParams ();

    	$this->_log (__FILE__, __LINE__, 'processing');

    	$this->_process ();

    	$this->_log (__FILE__, __LINE__, 'process ok');

    	$session->setParams ($this->_params);
    }

    /**
     * @desc Запуск процесса.
     * @param mixed $params Параметры запуска.
     * @return array
     * 		Параметры, с которыми будет запущена первая итерация
     */
    public function start (Background_Agent_Session $session)
    {
    	$this->_session = $session;
    	$this->_params = $session->getParams ();

    	$this->_log (__FILE__, __LINE__, 'starting');

    	$this->_start ();

    	$this->_log (__FILE__, __LINE__, 'start ok');

    	$session->setParams ($this->_params);
    }

}