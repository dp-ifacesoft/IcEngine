<?php
/**
 * Менеджер моделей
 *
 * @author neon
 */
class Model_Manager extends Manager_Abstract
{

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		'delegee'	=> array (
			'Model'			=> 'Simple',
			'Component'		=> 'Simple',
			'Model_Config'		=> 'Config',
			'Model_Defined'		=> 'Defined',
			'Model_Factory'		=> 'Factory'
		)
	);

	/**
	 * @desc Получение условий выборки из запроса
	 * @param Query $query
	 * @return array|null
	 */
	protected static function _prepareSelectQuery (Query $query)
	{
		$where = $query->getPart (Query::WHERE);
		$conditions = array ();
		foreach ($where as $w)
		{
			$condition = $w [Query::WHERE];
			$value = $w [Query::VALUE];

			$p = strpos ($condition, '=?');
			if ($p)
			{
				$condition = substr ($condition, 0, $p);
			}

			$conditions [$condition] = $value;
		}
		return $conditions;
	}

	/**
	 * @desc Получение данных модели из источника данных.
	 * @param Model $object
	 * @return boolean
	 */
	protected static function _read (Model $object)
	{
		$key = $object->key ();

		if (!$key)
		{
			return false;
		}

		$query = Query::instance ()
			->select ('*')
			->from ($object->modelName ())
			->where ($object->keyField (), $key);

		$data = Model_Scheme::dataSource ($object->modelName ())
			->execute ($query)
			->getResult ()->asRow ();

		if ($data)
		{
			// array_merge чтобы не затереть поля, которые были
			// установленны через set
			$object->set (array_merge (
				$data,
				$object->asRow ()
			));
			return true;
		}
		return false;
	}

	/**
	 * @desc Удаление данных модели из источника.
	 * @param Model $object
	 */
	public static function _remove (Model $object)
	{
		if (!$object->key ())
		{
			return ;
		}

		Model_Scheme::dataSource ($object->modelName ())
			->execute (
				Query::instance ()
					->delete ()
					->from ($object->table ())
					->where ($object->keyField (), $object->key ())
			);
	}

	/**
	 * @desc Сохранение модели в источник данных
	 * @param Model $object
	 * @param boolean $hard_insert
	 */
	protected static function _write (Model $object, $hard_insert = false)
	{
		$ds = Model_Scheme::dataSource ($object->modelName ());

		$kf = $object->keyField ();
		$id = $object->key ();

		if ($id && !$hard_insert)
		{
			// Обновление данных
			$ds->execute (
				Query::instance ()
					->update ($object->modelName ())
					->values ($object->getFields ())
					->where ($kf, $id)
			);
		}
		else
		{
			// Вставка
			if ($id)
			{
				$query = Query::instance ()
					->insert ($object->modelName ())
					->values ($object->getFields ());
				$ds->execute ($query);
			}
			else
			{
				// Генерация первичного ключа
				$new_id = Model_Scheme::generateKey ($object);
				if ($new_id)
				{
					// Ключ указан
					$object->set ($kf, $new_id);
					$ds->execute (
						Query::instance ()
							->insert ($object->modelName ())
							->values ($object->getFields ())
					);
				}
				else
				{
					if (!$id)
					{
						$object->unsetField ($kf);
						$id = $ds->execute (
							Query::instance ()
								->insert ($object->modelName ())
								->values ($object->getFields ())
						)->getResult ()->insertId ();

						$object->set ($kf, $id);
					}
					else
					{
						$ds->execute (
							Query::instance ()
								->insert ($object->modelName ())
								->values ($object->getFields ())
						);
					}
				}
			}
		}
	}

	/**
	 * @desc Получение модели по первичному ключу.
	 * @param string $model Имя класса модели.
	 * @param integer $key Значение первичного ключа.
	 * @return Model|null
	 */
	public static function byKey($modelName, $key, $lazy = false)
	{
		if ($lazy) {
			$result = Resource_Manager::get('Model', $modelName . '__' . $key);
			if ($result) {
				return $result;
			}
			$keyField = Model_Scheme::keyField($modelName);
			$model = self::getModel($modelName, array(
				$keyField	=> $key
			));
			$query = Query::instance()
				->select(array($modelName => '*'))
				->where(Model_Scheme::keyField($modelName), $key);
			Unit_Of_Work::push($query, $model, 'Simple');
			$model->setLazy(true);
			return $model;
		} else {
			$result = Resource_Manager::get('Model', $modelName . '__' . $key);
			if ($result) {
				return $result;
			}
			return self::byQuery(
				$modelName,
				Query::instance()
					->where(Model_Scheme::keyField($modelName), $key)
			);
		}
	}

	/**
	 * Получаем пустую модель
	 *
	 * @param string $modelName
	 * @param int|null $key
	 * @return Model
	 */
	private static function getModel($modelName, $fields)
	{
		$parents = class_parents($modelName);
		$first = end($parents);
		$second = prev($parents);
		$config = self::config();
		$keyField = Model_Scheme::keyField($modelName);
		$parent = $second && isset($config['delegee'][$second]) ?
			$second :
			$first;
		$delegee = 'Model_Manager_Delegee_' . $config['delegee'][$parent];
		$key = isset($fields[$keyField]) ? $fields[$keyField] : 0;
		$object = call_user_func(
			array($delegee, 'get'),
			$modelName, $key, null
		);
		$object->set($fields);
		return $object;
	}

	/**
	 * @desc Получение модели по опциям.
	 * @param string $model Название модели.
	 * @param mixed $option Опция
	 * @param mixed $_ [optional]
	 * @return Model|null
	 */
	public static function byOptions ($model, $option)
	{
		$c = Model_Collection_Manager::create ($model)
			->addOptions (array (
				'name'		=> '::Limit',
				'count'		=> 1
			));

		for ($i = 1; $i < func_num_args (); ++$i)
		{
			$c->addOptions (func_get_arg ($i));
		}

		return $c->first ();
	}

	/**
	 * @desc Получение модели по запросу.
	 * @param string $model Название модели.
	 * @param Query_Abstract $query Запрос.
	 * @return Model|null
	 */
	public static function byQuery($model, Query_Abstract $query, $lazy = false)
	{
		if ($lazy) {
			$parents = class_parents($model);
			$first = end($parents);
			$second = prev($parents);
			$config = self::config();
			$parent = $second && isset($config['delegee'][$second]) ?
				$second :
				$first;
			//echo $parent . '<br />';
			if ($parent != 'Model_Defined' && $parent != 'Model_Factory') {
				return self::uowByQuery($model, $query);
			}
			$data = null;
			if (!$query->getPart(Query::SELECT)) {
				$query->select(array($model => '*'));
			}
			if (!$query->getPart(Query::FROM)) {
				$query->from($model, $model);
			}
			$data = Model_Scheme::dataSource($model)
				->execute($query)
				->getResult()
				->asRow();
			if (!$data) {
				return null;
			}
			$object = self::get(
				$model,
				$data[Model_Scheme::keyField($model)],
				$data
			);
			$object->setLazy(true);
			return $object;
		} else {
			$data = null;
			if (!$query->getPart(Query::SELECT)) {
				$query->select(array($model => '*'));
			}
			if (!$query->getPart(Query::FROM)) {
				$query->from($model, $model);
			}
			$data = Model_Scheme::dataSource($model)
				->execute($query)
				->getResult()
				->asRow();

			if (!$data) {
				return null;
			}
			return self::get(
				$model,
				$data[Model_Scheme::keyField($model)],
				$data
			);
		}
	}

	/**
	 * byQuery с использованием Unit of work
	 *
	 * @param string $modelName
	 * @param Query_Abstract $query
	 * @return Model
	 */
	public static function uowByQuery($modelName, Query_Abstract $query)
	{
		if (!$query->getPart(Query::SELECT)) {
			$query->select(array($modelName => '*'));
		}
		if (!$query->getPart(Query::FROM)) {
			$query->from($modelName, $modelName);
		}
		$where = $query->part(QUERY::WHERE);
		$wheres = array();
		$whereFields = array();
		foreach ($where as $value) {
			$wheres[] = $value[QUERY::WHERE] . '=' . $value[QUERY::VALUE];
			$whereFields[$value[QUERY::WHERE]] = $value[QUERY::VALUE];
		}
		$keyHash = implode(':', $wheres);
		$result = Resource_Manager::get('Model', $modelName . '__' . $keyHash);
		if ($result) {
			return $result;
		}
		$whereFieldsPrepared = array();
		foreach($whereFields as $key=>$whereField) {
			$fieldName = trim(strtr($key, array(
				'?'	=> '',
				'<'	=> '',
				'>'	=> ''
			)));
			$whereFieldsPrepared[$fieldName] = $whereField;
		}
		$model = self::getModel($modelName, $whereFieldsPrepared);
		Unit_Of_Work::push($query, $model, 'Simple');
		return $model;
	}

	/**
	 * @desc Создать модель из источника
	 * @param string $model_name
	 * @param array $fields источник значений для полей
	 * @return
	 */
	public static function create ($model_name, $fields)
	{
		$scheme = Model_Scheme::getScheme ($model_name);
		$scheme_fields = $scheme ['fields'];
		$row = array();
		if ($scheme_fields)
		{
			foreach ($scheme_fields as $field => $data)
			{
				$value = isset ($fields [$field])
					? $fields [$field]
					: (
						isset ($data ['default'])
							? $data ['default']
							: null
					);
				$row [$field] = $value;
			}
		}

		$parents = class_parents ($model_name);
		$first = end ($parents);
		$second = prev ($parents);

		$config = self::config ();

		$parent =
			$second && isset ($config ['delegee'][$second]) ?
			$second :
			$first;

		$delegee =
			'Model_Manager_Delegee_' .
			$config ['delegee'][$parent];

		$result = call_user_func (
			array ($delegee, 'get'),
			$model_name, 0, $row
		);

		$result->set ($row);

		return $result;
	}

	/**
	 * @desc Получение данных модели
	 * @param string $model Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $object Объект или данные
	 * @return Model В случае успеха объект, иначе null.
	 */
	public static function get ($model, $key, $object = null)
	{
		$cached = $object != null;
		$result = null;

		if ($object instanceof Model) {
			$cached = true;
			$result = $object;
		} else {
			$result = Resource_Manager::get('Model', $model . '__' . $key);
			if ($result instanceof Model) {
				$cached = true;
				if (is_array($object)) {
					$result->set($object);
				}
			} else {
				// Делегируемый класс определяем по первому или нулевому
				// предку.
				$parents = class_parents ($model);
				$first = end ($parents);
				$second = prev ($parents);

				$config = self::config ();

				$parent =
					$second && isset ($config ['delegee'][$second]) ?
					$second :
					$first;

				$delegee =
					'Model_Manager_Delegee_' .
					$config ['delegee'][$parent];

				$result = call_user_func (
					array ($delegee, 'get'),
					$model, $key, $object
				);

				$result->set ($result->keyField (), $key);

				Resource_Manager::set (
					'Model',
					$model . '__' . $key,
					$result
				);
			}
		}

		$readed = !$cached ? self::_read ($result) : true;

		// В случае factory
		$model = get_class ($result);

		if (!$readed)
		{
			return new $model (
				$key
				? array ($result->keyField () => $key)
				: array ()
			);
		}

		$generic = $result->generic ();

		$result = $generic ? $generic : $result;

		$result = new $model (
			$result->getFields (),
			clone $result
		);

//		Model_Scheme::setScheme ($result);

		return $result->key () ? $result : new $model (array ());
	}

	/**
	 * @desc Удаление модели.
	 * @param Model $object Объект модели.
	 */
	public static function remove (Model $object)
	{
		// из хранилища моделей
		Resource_Manager::set ('Model', $object->resourceKey (), null);
		// Из БД (или другого источника данных)
		self::_remove ($object);
	}

	/**
	 * @desc Сохранение данных модели
	 * @param Model $object Объект модели.
	 * @param boolean $hard_insert Объект будет вставлен в источник данных.
	 */
	public static function set (Model $object, $hard_insert = false)
	{
		self::_write ($object, $hard_insert);
		$updated = $object->getFields ();
		$old = Resource_Manager::get ('Model', $object->resourceKey ());
		if ($old)
		{
			$updated = array_diff ($updated, $old->getFields ());
		}
		Resource_Manager::set ('Model', $object->resourceKey (), $object);
		Resource_Manager::setUpdated ('Model', $object->resourceKey (), $updated);
	}
}
