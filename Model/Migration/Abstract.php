<?php

/**
 * @desc Абстрактный класс миграции
 * @author Илья Колесников
 */
class Migration_Abstract extends Model
{
	/**
	 * @desc Миграция поднята
	 * @var interger
	 */
	const ST_UP = 1;

	/**
	 * @desc Миграция откачена
	 * @var interger
	 */
	const ST_DOWN = 0;

	/**
	 * @desc Параметры, переданные в миграцию контроллером,
	 * который ее запустил
	 * @var array
	 */
	protected $_params;

	/**
	 * @desc Откат миграции
	 */
	public function down ()
	{

	}

	/**
	 * @desc Вернуть данные о последнем выполнение данной миграции
	 * @return array
	 */
	public function getLast ()
	{
		Loader::load ('Helper_Migration');
		$last_data = Helper_Migration::getLastLog ();
		if (!$last_data)
		{
			return;
		}
		$name = $this->getName ();
		foreach ($last_log as $data)
		{
			if ($data ['name'] == $name)
			{
				return $data;
			}
		}
		return null;
	}

	/**
	 * @desc Получить имя миграции
	 * @return string
	 */
	public function getName ()
	{
		return substr (get_class ($this), 10);
	}

	/**
	 * @desc Вернуть параметры миграции
	 * @return array
	 */
	public function getParams ()
	{
		return $this->_params;
	}

	/**
	 * @desc Узнать состояние (поднята/откачена) миграции
	 * @return integer
	 */
	public function getState ()
	{
		Loader::load ('Helper_Migration');
		$queue = Helper_Migration::getQueue ();
		$last_data = Helper_Migration::getLastData ();
		if (!$last_data)
		{
			return self::ST_DOWN;
		}
		$last_name = $last_data ['name'];
		$name = $this->getName ();
		foreach ($queue as $migration_name => $params)
		{
			if (!is_array ($params))
			{
				$migration_name = $parms;
			}
			if ($last_name == $migration_name)
			{
				return self::ST_DOWN;
			}
			if ($name == $migration_name)
			{
				return self::ST_UP;
			}
		}
		return self::ST_DOWN;
	}

	/**
	 * @desc Залогировать выполнение миграции
	 * @param string $action
	 */
	public function log ($action)
	{
        print $action;
        print "\n";
        
		Loader::load ('Helper_Migration');
		Helper_Migration::log (
			$this->getName (),
			$action
		);
	}

	/**
	 * @desc Востановить данные, которые были до миграции
	 * @desc array $data
	 */
	public function restore ($data)
	{

	}

	/**
	 * @desc Изменить параметры миграции
	 * @param array $params
	 */
	public function setParams ($params)
	{
		$this->_params = $params;
	}

	/**
	 * @desc Сохранить данные, которые были до миграции
	 */
	public function store ()
	{

	}

	/**
	 * @desc Поднятие миграции
	 */
	public function up ()
	{

	}
}