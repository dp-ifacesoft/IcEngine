<?php

/**
 * Менеджер загрузчиков
 * 
 * @author goorus, morph
 */
class Bootstrap_Manager
{
	/**
	 * Загрузчики
	 * 
     * @var array <Bootstrap_Abstract>
	 */
	protected $bootstraps;

	/**
	 * Текущий загрузчик
	 * 
     * @var Bootstrab_Abstract
	 */
	protected $current;

    /**
	 * Возвращает текущий загрузчик.
	 * 
     * @return Bootstrap_Abstract
	 */
	public function current()
	{
		return $this->current;
	}
    
	/**
	 * Создает и возвращает загрузчик.
	 * 
     * @param string $name Название загрузчика.
	 * @param string $path [optional] Путь до загрузчика.
	 * @return Bootstrap_Abstract Экземпляр загрузчика.
	 */
	public function get($name, $path = null)
	{
		if (!isset($this->bootstraps[$name])) {
			$className = 'Bootstrap_' . $name;
            $bootstrap = new $className($path);
			$this->bootstraps[$name] = $bootstrap;
		}
		if (!$this->current) {
			$this->current = $this->bootstraps[$name];
		}
		return $this->bootstraps[$name];
	}
    
    /**
     * Изменить текущий загрузчик
     * 
     * @param Bootstrap_Abstract $bootstrap
     */
    public function setCurrent($bootstrap)
    {
        $this->current = $bootstrap;
    }
}