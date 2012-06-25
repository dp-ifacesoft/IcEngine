<?php
/**
 *
 * @desc Фронт контроллер.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Controller_Front extends Controller_Abstract
{
	/**
	 * @desc Запускаем фронт контролер.
	 */
	public function index ()
	{
		Loader::load ('Router');
		Loader::load ('Controller_Dispatcher');

		/**
		 * @desc Начинаем роутинг.
		 * @var route
		 */
		$route = Router::getRoute ();

		try
		{
			/**
			 * @desc Начинаем цикл диспетчеризации и получаем список
			 * выполняемых руот экшинов.
			 * @var Route_Action_Collection
			 */
			$actions = Controller_Dispatcher::loop (
				$route->actions ()
			);

			/**
			 * @desc Создаем задания для выполнения.
			 * В них отдает входные данные.
			 * @var array <Controller_Task>
			 */
			$tasks = Controller_Manager::createTasks (
				$actions,
				$this->getInput ()
			);

			/**
			 * @desc Выполненяем задания.
			 * @var array <Controller_Task>
			 */
			$tasks = Controller_Manager::runTasks ($tasks);

			$this->_output->send ('tasks', $tasks);
		}
		catch (Zend_Exception $e)
		{
			Loader::load ('Error');
			Error::render ($e);
		}
	}

}
