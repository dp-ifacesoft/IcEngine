<?php

/**
 * Фронт контроллер
 *
 * @author goorus, morph
 */
class Controller_Front extends Controller_Abstract
{
	/**
	 * Запускаем фронт контролер
	 */
	public function index()
	{
        if (Tracer::$enabled) {
			$subStartTime = microtime(true);
		}
		$route = $this->getService('router')->getRoute();
        $this->task->setRoute($route);
        $this->task->setOutput($this->output);
        if (Tracer::$enabled) {
			$subEndTime = microtime(true);
			Tracer::setRoutingTime($subEndTime - $subStartTime);
		}
		try
		{
            if (Tracer::$enabled) {
				$startTime = microtime(true);
			}
            // Получаем стратегию для фронт контроллера
            $strategy = $this->task->getStrategy();
            if ($strategy) {
                $strategy->run($this->task);
                if ($strategy->getIgnore()) {
                    return;
                }
            }
			/**
			 * Начинаем цикл диспетчеризации и получаем список
			 * выполняемых руот экшинов.
			 */
            $dispatcher = $this->getService('controllerDispatcher');
			$actions = $dispatcher->loop($route->actions());
            $controllerManager = $this->getService('controllerManager');
			// Создаем задания для выполнения. В них отдает входные данные.
			$tasks = $controllerManager->createTasks($actions, $this->getInput());
			if (Tracer::$enabled) {
				$endTime = microtime(true);
				Tracer::setDispatcherTime($endTime - $startTime);
			}
			// Выполненяем задания
			$resultTasks = $controllerManager->runTasks($tasks);
			$this->output->send('tasks', $resultTasks);
		} catch (Exception $e) {
            Error::render($e);
		}
	}
}
