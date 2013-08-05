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
		try {
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
            $resultTasks = array();
            if (!$this->task->getIgnore()) {
                /**
                 * Начинаем цикл диспетчеризации и получаем список
                 * выполняемых руот экшинов.
                 */
                $dispatcher = $this->getService('controllerDispatcher');
                $routeActions = $route
                    ? (is_string($route->actions)
                    ? array($route->actions) : $route->actions) : array();
                $actions = $dispatcher->loop(
                    $this->task->getActions() ?: $routeActions
                );
                $controllerManager = $this->getService('controllerManager');
                // Создаем задания для выполнения. В них отдает входные данные.
                $tasks = $controllerManager->createTasks(
                    $actions, $this->getInput()
                );
                if (Tracer::$enabled) {
                    $endTime = microtime(true);
                    Tracer::setDispatcherTime($endTime - $startTime);
                }
                // Выполненяем задания
                $resultTasks = $controllerManager->runTasks($tasks);
            }
			$this->output->send('tasks', $resultTasks);
            if (Tracer::$enabled) {
				$endTime = microtime(true);
				Tracer::setFrontControllerTime($endTime - $subStartTime);
			}
		} catch (Exception $e) {
            $this->getService('errorRender')->render($e);
		}
	}
}
