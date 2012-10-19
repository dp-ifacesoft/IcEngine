<?php
/**
 *
 * @desc Контроллер консоли.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Controller_Cli extends Controller_Abstract
{

	/**
	 * @desc Разобранные переменные из инпута
	 * @return Data_Transport
	 */
	protected function _parsedInput ()
	{
		$buffer = new Data_Provider_Buffer ();
		$argv = $this->_input->receiveAll ();

		foreach ($argv as $arg)
		{
			$p = strpos ($arg, '=');
			if ($p)
			{
				$buffer->set (
					substr ($arg, 0, $p),
					substr ($arg, $p + 1)
				);
			}
		}

		$transport = new Data_Transport ();
		return $transport->appendProvider ($buffer);
	}

	/**
	 * @desc Запуск контроллера консоли
	 */
	public function index ()
	{
		try
		{
			$ca = $this->_input->receive (1);
			$action = explode ('/', $ca);

			$action = Controller_Dispatcher::dispatch (
				$action [0] ? $action [0] : $ca,
				isset ($action [1]) && $action [1] ? $action [1] : 'index'
			);

			$action = new Controller_Action ($action);

			$task = new Controller_Task ($action);
			$task->setInput ($this->_parsedInput ());

			/**
			 * @desc Выполненяем задания.
			 * @var array <Controller_Task>
			 */
			$tasks = Controller_Manager::runTasks (array ($task));

			$this->_output->send ('tasks', $tasks);
		}
		catch (Zend_Exception $e)
		{
			Error::render ($e);
		}
	}

}
