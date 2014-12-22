<?php

/**
 * Абстрактный класс миграции
 *
 * @author morph
 */
class Migration_Abstract
{
	/**
	 * Миграция поднята
	 *
     * @var interger
	 */
	const ST_UP = 1;

	/**
	 * Миграция откачена
	 *
     * @var interger
	 */
	const ST_DOWN = 0;

    /**
     * Имя модели
     * 
     * @var string
     */
    public $model;
    
	/**
	 * Параметры, переданные в миграцию контроллером,
	 * который ее запустил
	 *
     * @var array
	 */
	protected $params;

    /**
     * Сервис локатор
     */
    protected $serviceLocator = null;

	/**
	 * Откат миграции
	 */
	public function down()
	{
        return true;
	}

	/**
	 * Вернуть данные о последнем выполнение данной миграции
	 *
     * @return array
	 */
	public function getLast()
	{
		$lastData = $this->getService('helperMigrationQueue')->lastFor($this);
		return $lastData;
	}

	/**
	 * Получить имя миграции
	 *
     * @return string
	 */
	public function getName()
	{
		return substr(get_class($this), strlen('Migration_'));
	}

	/**
	 * Вернуть параметры миграции
	 *
     * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Узнать состояние (поднята/откачена) миграции
	 *
     * @return integer
	 */
	public function getState()
	{
        $helperMigrationQueue = $this->getService('helperMigrationQueue');
		$queue = $helperMigrationQueue->getQueue();
		$lastData = $helperMigrationQueue->lastFor($this);
		if (!$lastData) {
			return self::ST_DOWN;
		}
		$name = $this->getName();
        $helperArray = $this->getService('helperArray');
        $needleMigration = $helperArray->filter($queue, array(
            'name'  => $name
        ));
        if (!$needleMigration) {
            return self::ST_DOWN;
        }
        return $needleMigration[0]['isFinished'] ? self::ST_UP : self::ST_DOWN;
	}

    /**
     * Получить сервис
     *
     * @param string $serviceName
     */
    public function getService($serviceName)
    {
        if (!$this->serviceLocator) {
            $this->serviceLocator = IcEngine::serviceLocator();
        }
        return $this->serviceLocator->getService($serviceName);
    }

	/**
	 * Залогировать выполнение миграции
	 *
     * @param string $action
	 */
	public function log($action)
	{
        $this->getService('helperMigrationLog')->log($this->getName(), $action);
	}

	/**
	 * Востановить данные, которые были до миграции
	 *
     * @desc array $data
	 */
	public function restore($data)
	{

	}

	/**
	 * Изменить параметры миграции
	 *
     * @param array $params
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	 * Сохранить данные, которые были до миграции
	 */
	public function store()
	{

	}

	/**
	 * Поднятие миграции
	 */
	public function up()
	{
        return true;
	}
}