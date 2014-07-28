<?php

/**
 * Абстрактный класс хелпера представления.
 * 
 * @author goorus, morph
 */
abstract class View_Helper_Abstract extends Helper_Abstract
{
	/**
	 * Отображение
     * 
	 * @var View_Render_Abstract
	 */
	protected $view;
	
	/**
	 * Конструктор
     * 
	 * @param View_Render_Abstract $render
	 * 		Рендерер, для которого вызывается хелпер
	 */
	public function __construct($view = null)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $viewRenderManager = $serviceLocator->getService('viewRenderManager');
	    $this->view = $view ? $view : $viewRenderManager->getView();
	}

	/**
	 * Получить результат хелпера
     * 
	 * @param array $params
	 * 		Параметры, переданные из шаблона
	 * @return string
	 * 		Результат работы хелпера
	 */
	abstract public function get(array $params);
}