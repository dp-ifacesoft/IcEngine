<?php

namespace IcEngine\Manager;

/**
 * Абстрактный класс менеджера
 *
 * @author morph, goorus
 */
abstract class ManagerAbstract
{
	/**
     * Конфигурация
     *
	 * @var array
	 */
	protected $config = array();

    /**
     * Локатор сервисов
     *
     * @var IcEngine\Service\ServiceLocator
     */
    protected $serviceLocator;

	/**
	 * Получить конфигурацию
	 *
     * @return IcEngine\Core\Objective
	 */
	public function config()
	{
		if (is_array($this->config)) {
            $configManager = $this->getService('configManager');
			$config = $configManager->get(get_class($this), $this->config);
            if ($config) {
                $this->config = $config;
            } else {
                $this->config = new IcEngine\Core\Objective(array());
            }
		}
		return $this->config;
	}

    /**
     * Получить услугу по имени
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        if (!$this->serviceLocator) {
            $this->serviceLocator = new IcEngine\Service\ServiceLocator();
        }
        return $this->serviceLocator->getService($serviceName);
    }

    /**
     * Получить текущий локатор сервисов
     *
     * @return IcEngine\Service\ServiceLocator
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Изменить текущий локатор сервисов
     *
     * @param IcEngine\Service\ServiceLocator $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}