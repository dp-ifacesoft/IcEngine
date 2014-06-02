<?php
/**
 *
 * @desc Базовый класс контроллера.
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Abstract
{

	/**
	 * @desc Последний вызванный экшен.
	 * В случае, если был вызван replaceAction, может отличаться
	 * от $_task.
	 * @var string
	 */
	protected $_currentAction;

	/**
	 * @desc Текущая задача.
	 * @var Controller_Task
	 */
	protected $_task;

	/**
	 * @desc Входные данные.
	 * @var Data_Transport
	 */
	protected $_input;

	/**
	 * @desc Выходные данные.
	 * @var Data_Transport
	 */
	protected $_output;

	/**
	 * @desc Конфиг контроллера.
	 * @var array
	 */
	protected $_config = array ();

    /**
     * @author red
     * @desc Стек ошибок контроллера
     * @var array
     */
    protected $_errors = array ();

	/**
	 * @desc Создает и возвращает контроллер.
	 */
	public function __construct ()
	{
	}

    /**
     * @author red
     * @desc Возвращает количество ошибок в стеке контроллера
     * Используется в Controller_Manager::call и Controller_Abstract::replaceAction
     * для того, чтобы определить, надо ли вызывать экшен контроллера после выполнения
     * $controller->_beforeAction (если стек ошибок вырос при выполнении _beforeAction,
     * экшен и _afterAction не выполняться не будут)
     * @return boolean
     */
    public function hasErrors ()
    {
        return (bool) count ($this->_errors);
    }

	public function _helperReturn () {}

	/**
	 * @desc Временный контент для сохраняемых данных.
	 * @return Temp_Content|null
	 */
	public function _inputTempContent ()
	{
		$tc = Temp_Content::byUtcode ($this->_input->receive ('utcode'));

		if (!$tc)
		{
			$this->replaceAction ('Error', 'obsolete');
			return;
		}

		return $tc;
	}

	/**
	 * @desc Получение данных с формы
	 * @param array|Objective $scheme
	 * @param boolean|Temp_Content Использовать ли временный контент или
	 * 		сам временный котенты
	 * @param boolean|string $by_parts Если true, данные будут возвращены
	 * массивом array ('feilds' => Objective, 'attributes' => Objective),
	 * если false - все данные объектом Objective, если 'fields' или
	 * 'attributes' - только соответствующая часть.
	 * @return Objective|array
	 */
	public function _inputFormData ($scheme, $use_tc = null, $by_parts = true)
	{
		// временный контент
		if ($use_tc)
		{
			if (!($use_tc instanceof Temp_Content))
			{
				$use_tc = Temp_Content::byUtcode (
					$this->_input->receive ('utcode')
				);

				if (!$use_tc)
				{
					return $this->replaceAction ('Error', 'obsolete');
				}
			}
		}

		$data = Helper_Form::receiveFields (
			$this->_input,
			$scheme,
			$use_tc
		);

		Helper_Form::filter ($data, $scheme);

		$valid = Helper_Form::validate ($data, $scheme);
		if (is_array ($valid))
		{
			// ошибка валидации
			$this->_task->setClassTpl (reset ($valid));

			// TODO пиздец!
			$this->_output->send (array (
				'registration'	=> $valid,
				'data'			=> array (
					'field'			=> key ($valid),
					'error'			=> current ($valid)
				)
			));

			return false;
		}

		Helper_Form::unsetIngored ($data, $scheme);

		if (!$by_parts)
		{
			return $data;
		}

		$data = Helper_Form::extractParts ($data, $scheme);

		return isset ($data [$by_parts]) ? $data [$by_parts] : $data;
	}

	/**
	 * @desc Сохранение данных с формы
	 * @param Temp_Content $tc
	 * @param array $scheme
	 * @param string|Model $model_class [optional]
	 * 		Имя класса модели или модель.
	 * 		Если не задано, будет использвано имя контроллера.
	 * 		Пример: для контроллера <i>Controller_Sample</i>, результатом
	 * 		будет модель класса <i>Sample</i>.
	 * @return Model|null Сохраненная модель, либо null в случае ошибки.
	 */
	public function _savePostModel (Temp_Content $tc, $scheme,
		$model_class = null)
	{
		$data = Helper_Form::receiveFields ($this->_input, $scheme);

		Helper_Form::filter ($data, $scheme);

		$valid = Helper_Form::validate ($data, $scheme);

		if (is_array ($valid))
		{
			$this->_task->setTemplate (
				str_replace (array ('::', '_'), '/', reset ($valid))
			);
			$field = key ($valid);
			$this->_output->send (array (
				'field'			=> $field,
				'field_title'	=>
					isset ($scheme [$field]['title']) ?
						$scheme [$field]['title'] :
						null,
				'data'		=> array (
					'field'	=> key ($valid),
					'error'	=> current ($valid)
				)
			));
			return null;
		}

		if ($model_class instanceof Model)
		{
			$model = $model_class;
		}
		else
		{
			if (!$model_class)
			{
				$model_class = $this->name ();
			}
			$model = Model_Manager::get ($model_class, $tc->rowId);
		}

		$parts = Helper_Form::extractParts ($data, $scheme);

		$model->update ($parts ['fields']);


		if ($parts ['attributes'])
		{
			$model->attr ($parts ['attributes']);
		};

		return $model;
	}

	/**
	 * @desc Завершение работы контроллера ошибкой.
	 * @param string $text Текст ошибки. Не отображается пользователю,
	 * виден в консоли отладки.
	 * @param string $method Экшен, в котором произошла ошибка (__METHOD__)
	 * или шаблон (в этому случае метод будет взять из _currentAction).
	 * Необходим для определения шаблона. Если не передан, будет
	 * взято из $text.
	 * @param string $tpl [optional] Шаблон.
	 */
	protected function _sendError ($text, $method = null, $tpl = true)
	{
        /**
         * @author red
         * @desc добавляем ошибку в стек
         */
        $this->_errors [] = array ($text, $method, $tpl);

		$this->_output->send (array (
			'error'	=> array (
				'text'	=> $text,
				'tpl'	=> $tpl
			),
		));

		if (!$method)
		{
            /**
             * @author red
             * не надо выполнять никаких действий, просто фиксируем ошибку в стеке
             */
            if (!$tpl)
            {
                return;
            }

			$method = $text;
		}

		if (! is_bool($tpl))
		{
			$this->_task->setClassTpl ($method, $tpl);
		}
		elseif ($method)
		{
			if (strpos ($method, '/') === false)
			{
				$this->_task->setClassTpl (
					$this->_currentAction,
					'/' . ltrim ($method, '/')
				);
			}
			else
			{
				$this->_task->setClassTpl ($method);
			}
		}
	}

	/**
	 * @desc Загружает и возвращает конфиг для контроллера
	 * @return Objective
	 */
	public function config ()
	{
		if (is_array ($this->_config))
		{
			$this->_config = Config_Manager::get (
				get_class ($this),
				$this->_config
			);
		}
		return $this->_config;
	}

	/**
	 * @desc Возвращает текущую задачу контролера
	 * @return Controller_Task
	 */
	public function getTask ()
	{
		return $this->_task;
	}

	/**
	 * @return Data_Transport
	 */
	public function getInput ()
	{
		return $this->_input;
	}

	/**
	 * @return Data_Transport
	 */
	public function getOutput ()
	{
		return $this->_output;
	}

	/**
	 * @desc Имя контроллера (без приставки Controller_)
	 * @return string
	 */
	final public function name ()
	{
		return substr (get_class ($this), 11);
	}

	/**
	 * @desc Заменить текущий экшн с передачей всех параметров
	 */
	public function replaceAction ($controller, $action = 'index')
	{
		if ($controller instanceof Controller_Abstract)
		{
			$other = $controller;
			$controller = $other->name ();
		}
		else
		{
			$other = Controller_Manager::get ($controller);
		}

		$this->_task->setTemplate (
			'Controller/' .
			str_replace ('_', '/', $controller) .
			'/' . $action
		);

		if ($controller == get_class ($this))
		{
			// Этот же контроллер
			return $this->$action ();
		}
		else
		{
			$other = Controller_Manager::get ($controller);
			$other->setInput ($this->_input);
			$other->setOutput ($this->_output);
			$other->setTask ($this->_task);
           // $other->_beforeAction ($action);

            // _beforeAction не генерировал ошибки, можно продолжать
            if (! $other->hasErrors ())
            {
                $other->$action ();
                //$other->_afterAction ($action);
            }
		}
	}

	/**
	 *
	 * @param Controller_Task $task
	 * @return Controller_Abstract
	 */
	public function setTask ($task)
	{
		$this->_task = $task;
		return $this;
	}

	/**
	 * @desc Устанавливает транспорт входных данных.
	 * @param Data_Transport $input
	 * @return Controller_Abstract
	 */
	public function setInput ($input)
	{
		$this->_input = $input;
		return $this;
	}

	/**
	 * @desc Устанавливает транспорт выходных данных.
	 * @param Data_Transport $output
	 * @return Controller_Abstract
	 */
	public function setOutput ($output)
	{
		$this->_output = $output;
		return $this;
	}

}