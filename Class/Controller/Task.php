<?php
/**
 *
 * @desc Задание на выполнение контроллера.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Controller_Task
{
	/**
	 * @desc Переменная, куда попадет результат
	 * работы контролера
	 * @var string
	 */
	protected $_assignVar = 'content';

	/**
	 * @desc Экшин
	 * @var Controller_Action
	 */
	protected $_controllerAction;

	/**
	 * @desc Игнорировать текущее задание
	 * @var boolean
	 */
	protected $_ignore = false;

	/**
	 * @desc
	 * @var integer
	 */
	protected $_index;

	/**
	 * @desc Входные данные
	 * @var Data_Transport
	 */
	protected $_input;

	/**
	 * @desc Название шаблона
	 * @var string
	 */
	protected $_template;

	/**
	 * @desc Результат выполнения экшена
	 * @var Data_Transport_Transaction
	 */
	protected $_transaction;

	/**
	 * @desc Рендера
	 * @var View_Render_Abstract
	 */
	protected $_viewRender;

	/**
	 *
	 * @param Route_Action|Controller_Action $action
	 */
	public function __construct ($action)
	{
		if ($action instanceof Route_Action)
		{
			$this->_assignVar = $action->assign;

			$this->_viewRender = $action->Route->viewRender ();

			$action = $action->Controller_Action;
		} else {
			$this->_viewRender = View_Render_Manager::getView ();
		}
		$this->_controllerAction = $action;
		if ($action) {
			$this->_template =
				'Controller/' .
				str_replace('_', '/', $action->controller) . '/' .
				$action->action;
		}
	}

	/**
	 * @desc Получить экшин
	 * @return Controller_Action
	 */
	public function controllerAction ()
	{
		return $this->_controllerAction;
	}

	/**
	 * @desc Возвращает название переменной, в которую будет присвоено
	 * результат рендера.
	 * @return string
	 */
	public function getAssignVar ()
	{
		return $this->_assignVar;
	}

	/**
	 * @desc Узнать игнорируется ли текущая задача
	 * @return boolean
	 */
	public function getIgnore ()
	{
		return $this->_ignore;
	}

	/**
	 * @desc Получить порядковый номер задания
	 * в очереди заданий
	 * @return integer
	 */
	public function getIndex ()
	{
		return $this->_index;
	}

	/**
	 * @desc Получить транспорт входных данных
	 * @return Data_Transport
	 */
	public function getInput ()
	{
		return $this->_input;
	}

	/**
	 * @desc Возвращает имя шаблона.
	 * @return string
	 */
	public function getTemplate ()
	{
		return $this->_template;
	}

	/**
	 * @desc Получить транзакцию экшина
	 * @return Data_Transport_Transaction
	 */
	public function getTransaction ()
	{
		return $this->_transaction;
	}

	/**
	 * @desc Получить рендер
	 * @return View_Render_Abstract
	 */
	public function getViewRender ()
	{
		return $this->_viewRender;
	}

	/**
	 * @desc Задать шаблон на основе названия класса
	 * @param string $class Класс или метод (контроллера).
	 * @param string $template Шаблон.
	 */
	public function setClassTpl ($class, $template = '')
	{
		$template = $template ? ('/' . ltrim ($template, '/')) : '';

		$this->setTemplate (
			str_replace (array ('_', '::'), '/', $class) . $template
		);
	}

	/**
	 * @desc Установить флаг игнорирования текущего задания
	 * @param boolean $value
	 */
	public function setIgnore ($value)
	{
		$this->_ignore = (bool) $value;
	}

	/**
	 * @desc Установить порядковый номер заания
	 * в очереди заданий
	 * @param integer $value
	 */
	public function setIndex ($value)
	{
		$this->_index = $value;
	}

	/**
	 * @desc Установить транспорт для входных данных
	 * @param Data_Transport $input
	 */
	public function setInput (Data_Transport $input)
	{
		$this->_input = $input;
	}

	/**
	 * @desc Установка шаблона для рендера.
	 * @param string $value
	 */
	public function setTemplate ($value)
	{
		$this->_template = $value;
	}

	/**
	 * @desc Изменить транзакцию текущего экшина
	 * @param Data_Transport_Transaction $value
	 */
	public function setTransaction (Data_Transport_Transaction $value)
	{
		$this->_transaction = $value;
	}

	/**
	 * @desc Изменить рендер
	 * @param View_Render_Abstract $viewRender
	 */
	public function setViewRender (View_Render_Abstract $viewRender)
	{
		$this->_viewRender = $viewRender;
	}

}