<?php

/**
 * Абстрактное представление рендера схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 * @ServiceAccessor
 */
abstract class Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * Получить имя схемы
	 * 
     * @return array
	 */
	public function getName()
	{
		return substr(
            get_class($this), strlen('Model_Mapper_Scheme_Render_')
        );
	}
    
	/**
	 * Отрендерить схему модели
	 * 
     * @param Model_Mapper_Scheme_Abstract $scheme
	 * @return string
	 */
	abstract public function render($scheme);
    
    /**
     * Get service by name
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
}