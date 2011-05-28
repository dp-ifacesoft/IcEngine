<?php
/**
 * 
 * @desc Абстрактный класс рендера.
 * @author Гурус
 * @package IcEngine
 *
 */
abstract class View_Render_Abstract extends Model_Factory_Delegate
{
	
	/**
	 * @desc Пути к директориям шаблонов.
	 * @var array <string>
	 */
	protected $_templatesPathes = array ();
	
	
	/**
	 * @desc Переменные шаблонизатора.
	 * @var array
	 */
	protected $_vars = array ();
	
	/**
	 * @desc Стек переменных.
	 * @var array
	 */
	protected $_varsStack = array ();
	
	/**
	 * (non-PHPdoc)
	 * @see Model::_afterConstruct()
	 */
	protected function _afterConstruct ()
	{
		if (!isset ($this->_fields ['id']))
		{
			$this->_fields ['id'] = 1;	
		}
	}
	
	/**
	 * @desc Добавление хелпера
	 * @param mixed $helper
	 * @param string $method
	 */
	abstract public function addHelper ($helper, $method);
	
	/**
	 * @desc Добавление пути до директории с шаблонами
	 * @param string $path Директория с шаблонами.
	 */
	public function addTemplatesPath ($path)
	{
		$dir = rtrim ($path, '/');
		$this->_templatesPathes [] = $dir . '/';
	}
	
	/**
	 * @desc Устанавливает значение переменной в шаблоне
	 * @param string|array $key Имя переменной или массив 
	 * пар (переменная => значение).
	 * @param mixed $value [optional] Новое значение переменной.
	 */
	public function assign ($key, $value = null)
	{
		if (is_array ($key))
		{
			$this->_vars = array_merge ($this->_vars, $key);
		}
		elseif (empty ($key))
		{
			Loader::load ('View_Render_Exception');
			throw new View_Render_Exception ('Empty key field.');
		}
		else
		{
			$this->_vars [$key] = $value;
		}
	}
	
	/**
	 * @desc Выводит результат работы шаблонизатор в браузер.
	 * @param string $tpl
	 */
	abstract public function display ($tpl = null);
	
	/**
	 * @desc Обрабатывает шаблон и возвращает результат.
	 * @param string $tpl Шаблон
	 * @return mixed Результат работы шаблонизатора. 
	 */
	abstract public function fetch ($tpl);
	
	/**
	 * @return string
	 */
	public function getLayout ()
	{
		return $this->config ['layout'];
	}
	
	/**
	 * @desc Возвращает массив путей до шаблонов.
	 * @return array
	 */
	public function getTemplatesPathes ()
	{
		return $this->_templatesPathes;
	}
	
	/**
	 * @desc Возвращает значение переменной шаблонизатора.
	 * @param string $key Имя переменной.
	 * @return mixed Значение переменной.
	 */
	public function getVar ($key)
	{
		return $this->_vars [$key];
	}
	
	/**
	 * @desc Восстанавливает значения переменных шаблонизатора
	 */
	public function popVars ()
	{
		$this->_vars = array_pop ($this->_varsStack);
	}
	
	/**
	 * @desc Сохраняет текущие значения переменных шаблонизатора и очищает их.
	 */
	public function pushVars ()
	{
		$this->_varsStack [] = $this->_vars;
		$this->_vars = array ();
	}
	
	/**
	 * @desc Обработка шаблонов из стека.
	 * @param array $outputs
	 */
	public function render (array $outputs)
	{
		Loader::load ('Message_Before_Render');
		Message_Before_Render::push ($this);
		
		// Рендерим в обратном порядке		
		$outputs = array_reverse ($outputs);
		
		/**
		 * @var Controller_Task $item
		 */
		foreach ($outputs as $output)
		{
			/**
			 * 
			 * @var $transaction Data_Transport_Transaction
			 */
			$transaction = $output->getTransaction ();
			
			$this->assign ($transaction->buffer ());
			
			$template = $output->getTemplate ();
			$result = $template ? $this->fetch ($template) : null;
			
			$this->assign (
				$output->getAssignVar (),
				$result
			);
		}
		
		Loader::load ('Message_After_Render');
		Message_After_Render::push ($this);
	}
	
	/**
	 * 
	 * @param string $value
	 * @return View_Render_Abstract
	 */
	public function setLayout ($value)
	{
		$this->_config ['layout'] = $value;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Factory_Delegate::table()
	 */
	public function table ()
	{
		return 'View_Render';
	}
	
}