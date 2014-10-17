<?php

/**
 * Контроллер консоли.
 * 
 * @author goorus, morph
 */
class Controller_Cli_Simple extends Controller_Abstract
{
	/**
	 * Разобранные переменные из инпута
	 * 
     * @return Data_Transport
	 */
	protected function parsedInput()
	{
		$buffer = new Data_Provider_Buffer();
		$argv = $this->input->receiveAll();
        $bufferData = array();
		foreach ($argv as $arg) {
			$p = strpos($arg, '=');
			if ($p) {
                $key = substr($arg, 0, $p);
                $value = substr($arg, $p + 1);
                if (isset($bufferData[$key])) {
                    if (!is_array($bufferData[$key])) {
                        $bufferData[$key] = array($bufferData[$key]);
                    }
                    $bufferData[$key][] = $value;
                } else {
                    $bufferData[$key] = $value;
                }
			}
		}
        foreach ($bufferData as $key => $value) {
            $buffer->set($key, $value);
        }
		$transport = new Data_Transport();
		return $transport->appendProvider($buffer);
	}

	/**
	 * Запуск контроллера консоли
	 */
	public function index ()
	{
		$controllerDispatcher = $this->getService('controllerDispatcher');
		$controllerManager = $this->getService('controllerManager');
		$errorRender = $this->getService('errorRender');
		try {
			$ca = $this->input->receive(1);
			$actionParts = explode('/', $ca);
			$actionDispatched = $controllerDispatcher->dispatch(
				$actionParts[0] ? $actionParts[0] : $ca,
				!empty($actionParts[1]) ? $actionParts[1] : 'index'
			);
			$action = new Controller_Action($actionDispatched);
			$task = new Controller_Task($action);
			$task->setInput($this->parsedInput());
			/**
			 * Выполненяем задания.
			 * 
             * @var array <Controller_Task>
			 */
			$tasks = $controllerManager->runTasks(array($task));
			$this->output->send('tasks', $tasks);
		} catch (Exception $e) {
			$errorRender->render($e);
		}
	}
}