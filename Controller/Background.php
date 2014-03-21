<?php
/**
 *
 * @desc Контроллер фоновых процессов.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Background extends Controller_Abstract
{

    /**
     *
     * @var Background_Agent_Manager
     */
    protected $_manager;

	/**
	 *
	 * @return Background_Agent
	 */
	protected function _getAgent ()
	{
		$id = $this->_input->receive ('id');
		$agent = Model_Manager::byKey ('Background_Agent', $id);
		if (!$agent)
		{
			$this->_output->send (array (
				'error'	=> 'Agent not found.'
			));
		}
		return $agent;
	}

	/**
	 *
	 * @return Background_Agent_Session
	 */
	protected function _getSession ()
	{
		list (
			$session_id,
			$session_key
		) = $this->_input->receive (
			'session_id',
			'session_key'
		);

		$session = Model_Manager::byQuery (
			'Background_Agent_Session',
			Query::instance ()
			->where ('id', $session_id)
			->where ('key', $session_key)
		);

		if (!$session)
		{
			$this->_output->send (array (
				'error'	=> 'Session not found.'
			));
		}
		return $session;
	}

	/**
	 * @return Background_Agent_Manager
	 */
	protected function _manager ()
	{
	    if (!$this->_manager)
	    {
	        $this->_manager = new Background_Agent_Manager ();
	    }
	    return $this->_manager;
	}

	public function agents ()
	{
		$agents = new Background_Agent_Collection ();
		$this->_output->send (array (
			'agents'	=> $agents
		));
	}

	public function resetState ()
	{
		$agent = $this->_getAgent ();
		if (!$agent)
		{
			return;
		}

		$agent->resetState ();
	}

	/**
	 * @desc Вызвать следующую итерацию работы сессии и возобновить.
	 */
	public function resume ()
	{
		$session = $this->_getSession ();
		if (!$session)
		{
			return;
		}

		$session->process ();
		$this->_manager ()->resumeSession ($session);
		die ();
	}

	public function stop ()
	{
		$session = $this->_getSession ();
		if (!$session)
		{
			return;
		}

		$session->stop ();
	}

}