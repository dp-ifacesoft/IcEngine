<?php

/**
 * Абстрактный класс рендера.
 * 
 * @author goorus, morph
 */
abstract class View_Render_Abstract
{
    /**
     * Конфигурация
     * 
     * @var array
     */
    protected $config = array();
    
	/**
	 * Пути к директориям шаблонов.
	 * 
     * @var array <string>
	 */
	protected $templatesPathes = array();


	/**
	 * Переменные шаблонизатора.
	 * 
     * @var array
	 */
	protected $vars = array();

	/**
	 * Стек переменных.
	 * 
     * @var array
	 */
	protected $varsStack = array();

	/**
	 * Добавление хелпера
	 * 
     * @param mixed $helper
	 * @param string $method
	 */
	public function addHelper($helper, $method)
	{

	}

	/**
	 * Добавление пути до директории с шаблонами
	 * 
     * @param string $path Директория с шаблонами.
	 */
	public function addTemplatesPath($path)
	{
		$dir = rtrim($path, '/');
		$this->templatesPathes[] = $dir . '/';
	}

	/**
	 * Устанавливает значение переменной в шаблоне
	 * 
     * @param string|array $key Имя переменной или массив
	 * пар (переменная => значение).
	 * @param mixed $value [optional] Новое значение переменной.
	 */
	public function assign($key, $value = null)
	{
		if (is_array($key)) {
			$this->vars = array_merge($this->vars, $key);
		} elseif (empty($key)) {
			throw new Exception('Empty key field.');
		} else {
			$this->vars[$key] = $value;
		}
	}
    
    /**
     * Получить конфигурацию
     * 
     * @return Config_Array
     */
	public function config()
	{
		if (is_array($this->config)) {
			$configManager = $this->getService('configManager');
            $this->config = $configManager->get(
                get_class($this), $this->config
			);
		}
		return $this->config;
	}


	/**
	 * Выводит результат работы шаблонизатор в браузер.
	 * 
     * @param string $tpl
	 */
	abstract public function display($tpl);

	/**
	 * Обрабатывает шаблон и возвращает результат.
	 * 
     * @param string $tpl Шаблон
	 * @return mixed Результат работы шаблонизатора.
	 */
	abstract public function fetch($tpl);

    /**
     * Получить сервис по имени
     * 
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
    
	/**
	 * Возвращает массив путей до шаблонов
     * 
	 * @return array
	 */
	public function getTemplatesPathes()
	{
		return $this->templatesPathes;
	}

	/**
	 * Возвращает значение переменной шаблонизатора.
	 * 
     * @param string $key Имя переменной.
	 * @return mixed Значение переменной.
	 */
	public function getVar($key)
	{
		return $this->vars[$key];
	}

	/**
	 * Восстанавливает значения переменных шаблонизатора
	 */
	public function popVars()
	{
		$this->vars = array_pop($this->varsStack);
	}

	/**
	 * Сохраняет текущие значения переменных шаблонизатора и очищает их.
	 */
	public function pushVars ()
	{
		$this->varsStack[] = $this->vars;
		$this->vars = array();
	}

	/**
	 * Обработка шаблонов из стека.
	 * 
     * @param array $outputs
	 * @return mixed
	 */
	public function render(Controller_Task $task)
	{
		$transaction = $task->getTransaction();
		$this->assign($transaction->buffer());
		$template = $task->getTemplate();
		$result = $template ? $this->fetch($template) : null;
		return $result;
	}
}