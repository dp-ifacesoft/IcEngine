<?php

/**
 * @desc Админка для баз данных
 * @author Илья Колесников
 * @package IcEngine
 */
class Controller_Admin_Database extends Controller_Abstract
{

	protected function getAdminConfig ($class_name)
	{
		$config = Config_Manager::get ('Model_Mapper_' . $class_name);
		return $config ['admin_panel'];
	}

	/**
	 * @desc Config
	 * @var array|Objective
	 */
	protected $_config = array (
		// Роли, имеющие доступ к админке
		'access_roles'		=> array ('admin'),
		'default_limit'		=> 30
	);

	/**
	 * @desc Получить сопряжение полей таблицы на разрешенные
	 * ACL поля для пользователя
	 * @param string $table
	 * @param array $fields
	 * @return array<string>
	 */
	private function __aclFields ($table, $fields, $type = null)
	{
		$acl_fields = $this->__fields ($table, $type);
		$tmp_fields = Helper_Array::column ($fields->__toArray (), 'Field');
		$acl_fields = array_intersect ($acl_fields, $tmp_fields);
		return $acl_fields;
	}

	/**
	 * @desc Получить сопряжение таблиц на разрешенные
	 * ACL таблицы для пользователя
	 * @param array $fields
	 * @return array<string>
	 */
	private function __aclTables ($tables)
	{
		$acl_tables = $this->__tables ();
		$table_names = Helper_Array::column ($tables, 'Name');
		return array_intersect ($table_names, $acl_tables);
	}

	/**
	 * @desc Получить имя класса по таблице и префиксу
	 * @param string $table
	 * @param string $prefix
	 * @return string
	 */
	private function __className ($table, $prefix)
	{
		$class_name = Model_Scheme::tableToModel ($table);
		$prefix = ucfirst ($prefix);

		if (strpos ($class_name, $prefix) === 0)
		{
			$class_name = substr ($class_name, strlen ($prefix));
		}

		return $class_name;
	}

	/**
	 * @desc Получить ACL разрешенные поля таблицы
	 * @param string $table
	 * @return array <string>
	 */
	private function __fields ($table, $type = null)
	{
		$resources = $this->__resources (null, $type);
		$result = array ();
		$name = 'Table/' . $table . '/';
		$len = strlen ($name);

		foreach ($resources as $r)
		{
			if (substr ($r, 0, $len) === $name)
			{
				$tmp = trim (substr ($r, $len), '/');
				$r = strrpos ($tmp, '/');
				if ($r !== false)
				{
					$tmp = substr ($tmp, 0, $r);
				}
				$result [] = $tmp;
			}
		}
		return $result;
	}

	/**
	 * @desc Подготовить поля к выводу
	 * @param string $class_name
	 * @param Objective $fields
	 * @return Objective
	 */
	private function _getValues ($row, $class_name, $fields)
	{
		$field_filters = array ();
		$tmp = $this->getAdminConfig ($class_name)->field_filters;

		if ($tmp)
		{
			$field_filters = $tmp->__toArray ();
		}

		$table = Model_Scheme::table ($class_name);
		foreach ($fields as $i => $field)
		{
			// Это линка
			if (!empty ($field->Link))
			{
				if (isset ($field_filters [$field->Field]))
				{
					foreach ($field_filters [$field->Field] as $field_filter)
					{
						$value = $field_filter ['value'];

						if (strpos ($value, '::') !== false)
						{
							$value = call_user_func ($field_filter ['value']);
						}

						$field->Values = $field->Values->filter (array (
							$field_filter ['field'] => $value
						));
					}
				}
			}

			// Тип поля - enum
			if (strpos ($field->Type, 'enum(') === 0)
			{
				$values = substr ($field->Type, 6, -1);
				$values = explode (',', $values);

				$collection = Model_Collection_Manager::create (
					'Dummy'
				)->reset ();
				foreach ($values as $v)
				{
					$v = trim ($v, "' ");

					$collection->add (new Model_Proxy (
						'Dummy',
						array (
							'id'	=> $v,
							'name'	=> $v
						)
					));
				}

				$field->Values = $collection;
			}

			if ($row)
			{
				$text_value = Model_Manager::byQuery (
					'Text_Value',
					Query::instance ()
						->where ('tv_field_table', $table)
						->where ('tv_field_name', $field->Field)
				);

				//echo DDS::getDataSource ()->getQuery ()->translate () . '<br />';

				// Есть запись для поля таблицы в таблице подстановок
				if ($text_value && $text_value->tv_text_field)
				{
					$collection = $text_value->replace (
						$row, $table, $fields, $field, $field_filters, $class_name
					);
					$field->Values = $collection;
				}
			}
			// Поле - поле для связи
			if (strpos ($field->Field, '__id') !== false)
			{
				$cn = substr ($field->Field, 0, -4);

				$query = Query::factory ('Select');

				if (isset ($field_filters [$field->Field]))
				{
					foreach ($field_filters [$field->Field] as $field_filter)
					{
						$value = $field_filter ['value'];

						if (strpos ($value, '::') !== false)
						{
							$value = call_user_func ($field_filter ['value']);
						}

						$query->where ($field_filter ['field'], $value);
					}
				}

				$field->Values = Model_Collection_Manager::byQuery (
					$cn,
					$query
				);
			}

			// Ссылка на родителя
			if ($field->Field == 'parentId')
			{
				$field->Values = Model_Collection_Manager::create (
					$class_name
				);
			}
		}
		return $fields;
	}

	/**
	 * @desc Залогоровать операцию
	 * @param string $action
	 * @param string $table
	 * @param array<string> $fields
	 * @return void
	 */
	private function __log ($action, $table, $row_id, $fields)
	{
		foreach ((array) $fields as $field => $value)
		{
			$log = new Admin_Log (array (
				'User__id'		=> User::id (),
				'action'		=> $action,
				'table'			=> $table,
				'rowId'			=> $row_id,
				'field'			=> $field,
				'value'			=> $value,
				'createdAt'		=> Helper_Date::toUnix ()
			));

			$log->save ();
		}
	}

	/**
	 * @desc Получить ресурсы Aсl и префиксом Table/
	 * @param null|Acl_Role $role
	 * @return array <string>
	 */
	private function __resources ($role = null, $type = null)
	{
		$query = Query::instance ()
			->select ('name')
			->from ('Acl_Resource')
			->innerJoin (
				'Link',
				'Link.fromRowId=Acl_Resource.id'
			)
			->where ('Link.fromTable', 'Acl_Resource')
			->where ('Link.toTable', 'Acl_Role')
			->where ('Link.toRowId',
				is_null ($role) ?
					$this->__roles ()->column ('id') :
					$role->key ()
			)
			->where ('Acl_Resource.name LIKE "Table/%"');

		$resources = DDS::execute ($query)
			->getResult ()
				->asColumn ('name');

		if ($type)
		{
			foreach ($resources as $i=>$resource)
			{
				if (strpos ($resource, '/' . $type . '/') === false)
				{
					unset ($resources [$i]);
				}
			}
		}
		return (array) $resources;
	}

	/**
	 * @desc Получить все роли пользователя
	 * @return Acl_Role_Collection
	 */
	private function __roles ()
	{
		return Helper_Link::linkedItems (
			User::getCurrent (),
			'Acl_Role'
		);
	}

	/**
	 * @desc Получить ACL разрешенные таблицы
	 * @return array <string>
	 */
	private function __tables ()
	{
		$resources = $this->__resources ();
		$result = array ();

		foreach ($resources as $r)
		{
			$tmp = explode ('/', $r);
			$result [] = $tmp [1];
		}

		return $result;
	}

	/**
	 * @desc Получить роли текущего пользователя
	 * @return array
	 */
	public function __userRoles ()
	{
		$query = Query::instance ()
			->select ('fromRowId')
			->from ('Link')
			->where ('fromTable', 'Acl_Role')
			->where ('toTable', 'User')
			->where ('toTableId', User::id ());

		return DDS::execute ($query)->getResult ()->asColumn ();
	}

	/**
	 * @desc Проверяет, есть ли у текущего пользователя доступ
	 * к экшенам этого контроллера
	 * @return boolean true, если пользователь имеет доступ, иначе false.
	 */
	protected function _checkAccess ()
	{
		$user = User::getCurrent ();
		$roles = $this->config ()->access_roles;

		foreach ($roles as $role)
		{
			if ($user->hasRole ($role))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @desc Удаление записи
	 */
	public function delete ()
	{
		list (
			$table,
			$row_id
		) = $this->_input->receive (
			'table',
			'row_id'
		);

		$prefix = Model_Scheme::$default ['prefix'];
		$class_name = $this->__className ($table, $prefix);
		$fields = Helper_Data_Source::fields ('`' . $table . '`');
		$acl_fields = $this->__aclFields ($table, $fields);

		if (!$acl_fields || !User::id ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		/*
		 * @var Model $row
		 */
		$row = Model_Manager::byKey (
			$class_name,
			$row_id
		);

		if ($row)
		{
			$row->delete ();

			$this->__log (
				__METHOD__,
				$table,
				$row_id,
				array (
					'id'	=> $row_id
				)
			);
		}

		Helper_Header::redirect ('/cp/table/' . $table . '/');
	}

	private function filters ($collection, $class_name)
	{
		$filters = null;

		if (!empty ($this->getAdminConfig ($class_name)->filters))
		{
			$filters = $this->getAdminConfig ($class_name)->filters;
		}

		if ($filters)
		{
			$filters = $filters->__toArray ();

			foreach ($filters as $field => $value)
			{
				if (strpos ($value, '::') !== false)
				{
					$value = call_user_func ($value);
				}
				$collection->where ($field, $value);
			}
		}
		return $collection;
	}

	/**
	 * @desc Список таблиц
	 */
	public function index ()
	{
		$tables = Helper_Data_Source::tables ();
		$tmp_tables = $this->__aclTables ($tables);

		if (!$tmp_tables || !User::id ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		$result = array ();

		foreach ($tables as $table)
		{
			$table ['Rate'] = 0;

			if (in_array ($table ['Name'], $tmp_tables))
			{
				$rate = Table_Rate::byTable ($table ['Name']);
				$table ['Rate'] = $rate->value;
				$result [] = $table;
			}
		}

		Helper_Array::mosort ($result, 'Rate DESC,Comment');

		$tmp = array ();
		foreach ($result as $i => $r)
		{
			if (!$r ['Comment'])
			{
				$tmp [] = $r;
				unset ($result [$i]);
			}
		}

		Helper_Array::mosort ($tmp, 'Name');
		$result = array_merge ($result, $tmp);

		$this->_output->send (array (
			'tables'	=> $result,
		));
	}

	/**
	 * @desc Поля записи
	 */
	public function row ($table, $row_id)
	{
		Table_Rate::byTable ($table)->inc ();
		$fields = Helper_Data_Source::fields ('`' . $table . '`');
		$acl_fields = $this->__aclFields ($table, $fields, $row_id != 0 ? 'edit' : 'create');

		if (!$acl_fields || !User::id ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		$prefix = Model_Scheme::$default ['prefix'];
		$class_name = $this->__className ($table, $prefix);
		$row = Model_Manager::get (
			$class_name,
			$row_id
		);

		$auto_select = array ();

		if (!empty ($this->getAdminConfig ($class_name)->auto_select))
		{
			$auto_select = $this->getAdminConfig ($class_name)->auto_select;
		}

		if ($auto_select)
		{
			$auto_select = $auto_select->__toArray ();
		}

		$exists_links = Model_Scheme::links ($class_name);
		$link_models = array ();

		if ($exists_links)
		{
			foreach ($exists_links as $link_name => $data)
			{
				$row->set (
					$link_name,
					Helper_Link::linkedItems ($row, $link_name)
				);

				$link_models [$link_name] = Model_Collection_Manager::create (
					$link_name
				);

				$link_models [$link_name] = $this->filters($link_models [$link_name], $link_name);

				$link_table = Model_Scheme::table ($link_name);

				$table_info = Helper_Data_Source::table ($link_table);

				$field = new Objective (array (
					'Link'		=> true,
					'Field'		=> $link_name,
					'Values'	=> array (),
					'Comment'	=> !empty ($table_info ['Comment'])
						? $table_info ['Comment'] : null
				));

				$field->Values = $link_models [$link_name];

				$fields [] = $field;
			}
		}

		$fields = $this->_getValues ($row, $class_name, $fields);
		$modificators = array ();

		$tmp = $this->getAdminConfig ($class_name)->modificators;

		if ($tmp)
		{
			$modificators = $tmp->__toArray ();
		}

		foreach ($fields as $i=> $field)
		{
			if (
				!isset ($exists_links [$field ['Field']]) &&
				!in_array ($field ['Field'], $acl_fields)
			)
			{
				unset ($fields [$i]);
				continue;
			}

			if (!$row->key () && $field ['Field'] != $row->keyField ())
			{
				$row->set ($field ['Field'], $field ['Default']);
			}

			// Автовыбор
			if (isset ($auto_select [$field ['Field']]) && !$row->key ())
			{
				$value = $auto_select [$field ['Field']];

				if (strpos ($value, '::') !== false)
				{
					$value = call_user_func ($value);
				}

				$row->set ($field ['Field'], $value);
			}

			// Модификатор
			if (isset ($modificators [$field ['Field']]))
			{
				$tmp = $modificators [$field ['Field']];
				if (strpos ($tmp, '::'))
				{
					$tmp = explode ('::', $tmp);
				}
				$value = call_user_func ($tmp, $row->sfield ($field ['Field']));
				$row->set ($field ['Field'], $value);
			}
		}

		// Получаем эвенты
		$events  = array ();

		if (!empty ($this->getAdminConfig ($class_name)->events))
		{
			$events = $this->getAdminConfig ($class_name)->events;
		}

		if ($events)
		{
			$events = $events->__toArray ();
		}

		// Получаем плагины
		$plugins = array ();

		if (!empty ($this->getAdminConfig ($class_name)->plugins))
		{
			$plugins = $this->getAdminConfig ($class_name)->plugins;
		}

		if ($plugins)
		{
			$plugins = $plugins->__toArray ();
		}

		// Получаем список вкладок
		$tabs = array ();

		if (!empty ($this->config ()->tabs))
		{
			$tabs = $this->config ()->tabs->__toArray ();
		}

		$this->_output->send (array (
			'row'			=> $row,
			'fields'		=> $fields,
			'link_models'	=> $link_models,
			'table'			=> $table,
			'tabs'			=> $tabs,
			'events'		=> $events,
			'plugins'		=> $plugins,
			'keyField'		=> Model_Scheme::keyField ($class_name)
		));
	}

	/**
	 * @desc Список записей
	 */
	public function table ()
	{
		$tables = Helper_Data_Source::tables ();
		$tmp_tables = $this->__aclTables ($tables->__toArray ());

		list (
			$table,
			$limitator
		) = $this->_input->receive (
			'table',
			'limitator'
		);

		Table_Rate::byTable ($table)->inc ();
		$acl_fields = $this->__fields ($table);

		if (!in_array ($table, $tmp_tables) || !$acl_fields || !User::id ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		$prefix = Model_Scheme::$default ['prefix'];
		$class_name = $this->__className ($table, $prefix);
		$collection = Model_Collection_Manager::create ($class_name);

		// Получаем фильтры
		$collection = $this->filters($collection, $class_name);

		// Сортируем коллекцию, если есть конфиг для сортировки
		$sort = null;

		if (!empty ($this->getAdminConfig ($class_name)->sort))
		{
			$sort = $this->getAdminConfig ($class_name)->sort;
		}

		if ($sort)
		{
			$collection->addOptions (array (
				'name'	=> '::Order_Asc',
				'field'	=> $sort
			));
		}

		$search = array ();
		if (!$limitator)
		{
			// Накладываем лимиты

			$limit = $this->config ()->default_limit;

			if (!empty ($this->getAdminConfig ($class_name)->limits))
			{
				$limit = $this->getAdminConfig ($class_name)->limits;
			}

			if ($limit)
			{
				$_GET ['limit'] = $limit;
			}

			$search = Request::get ('search');
			if ($search)
			{
				foreach ($search as $f=>$v)
				{
					if (!$v)
					{
						continue;
					}
					if (is_numeric ($v))
					{
						$collection->where ($f, $v);
					}
					else
					{
						$collection->where ($f . ' LIKE ?', '%' . $v . '%');
					}
				}
			}

			$paginator = Paginator::fromGet ();
			$collection->setPaginator ($paginator);
			$collection->load ();

			$paginator_html = Controller_Manager::html (
				'Paginator/index',
				array (
					'data'	=> $paginator,
					'tpl'	=> 'admin'
				)
			);

			$this->_output->send (array (
				'paginator_html'	=> $paginator_html
			));
		}
		else
		{
			list ($field, $value) = explode ('/', $limitator);

			$collection = $collection->filter (array (
				$field => $value
			));
		}

		$acl_fields = array ();
		$class_fields = array ();

		if (!empty ($this->getAdminConfig ($class_name)->fields))
		{
			$class_fields = $this->getAdminConfig ($class_name)->fields;
		}

		$fields = null;
		$sfields = array ();
		$fields = Helper_Data_Source::fields ('`' . $table . '`');
		$acl_fields = $this->__aclFields ($table, $fields);

		foreach ($fields as $i => $field)
		{
			if (!in_array ($field ['Field'], $acl_fields))
			{
				unset ($fields [$i]);
			}
			else
			{
				$sfields [] = $field ['Field'];
			}
		}

		$search_fields = $this->_getValues (null, $class_name, clone $fields);
		$config_search_fields = $this->getAdminConfig ($class_name)->search_fields;
		if ($config_search_fields)
		{
			$config_search_fields = $config_search_fields->__toArray ();
			foreach ($search_fields as $i=>$v)
			{
				if (!in_array ($v->Field, $config_search_fields))
				{
					unset ($search_fields [$i]);
				}
				}
		}

		if ($class_fields)
		{
			$class_fields = $class_fields->__toArray ();

			foreach ($fields as $i => $field)
			{
				if (!in_array ($field ['Field'], $class_fields))
				{
					unset ($fields [$i]);
				}
				else
				{
					$sfields [] = $field ['Field'];
				}
			}
		}

		$sfields = array_unique ($sfields);
		$title = null;

		if (!empty ($this->getAdminConfig ($class_name)->titles))
		{
			$title = $this->getAdminConfig ($class_name)->titles;
		}

		$links = array ();

		if (!empty ($this->getAdminConfig ($class_name)->links))
		{
			$links = $this->getAdminConfig ($class_name)->links;
		}

		if ($links)
		{
			$links = $links->__toArray ();
		}

		$includes = array ();

		if (!empty ($this->getAdminConfig ($class_name)->includes))
		{
			$includes = $this->getAdminConfig ($class_name)->includes;
		}

		$limitators = array ();

		if (!empty ($this->getAdminConfig ($class_name)->limitators))
		{
			$limitators = $this->getAdminConfig ($class_name)->limitators;
		}

		if ($limitators)
		{
			$limitators = $limitators->__toArray ();
		}

		if ($includes)
		{
			foreach ($collection as $item)
			{
				$old = array ();

				foreach ($includes as $field => $model)
				{
					$ffield = Model_Scheme::keyField ($model);

					if (strpos ($model, '/') !== false)
					{
						list ($model, $ffield) = explode ('/', $model);
					}

					$model = Model_Manager::byQuery (
						$model,
						Query::instance ()
							->where ($ffield, $item->$field)
					);

					if ($model)
					{
						$old [$field] = $item->$field;
						$item->$field = $model->title ();
					}
				}

				if ($old)
				{
					$item->data ('old', $old);
				}
			}
		}

		$styles = array ();

		if (!empty ($this->getAdminConfig ($class_name)->styles))
		{
			$styles = $this->getAdminConfig ($class_name)->styles;
		}

		$link_styles = array ();

		if (!empty ($this->getAdminConfig ($class_name)->link_styles))
		{
			$link_styles = $this->getAdminConfig ($class_name)->link_styles;
		}

		$field_filters = array ();
		if (
			isset ($this->getAdminConfig ($class_name)->field_filters)
		)
		{
			$field_filters = $this->getAdminConfig ($class_name)->field_filters
				->__toArray ();
		}

		$this->_output->send (array (
			'collection'		=> $collection,
			'fields'			=> $fields,
			'search'			=> $search,
			'search_fields'		=> $search_fields,
			'sfields'			=> $sfields,
			'class_name'		=> $class_name,
			'table'				=> $table,
			'limitators'		=> $limitators,
			'limitator'			=> $limitator,
			'title'				=> !empty ($title) ? $title : $this->config ()->default_title,
			'links'				=> $links,
			'keyField'			=> Model_Scheme::keyField ($class_name),
			'styles'			=> $styles,
			'link_styles'		=> $link_styles
		));
	}

	/**
	 * @desc Сохранение записи
	 */
	public function save ()
	{
		list (
			$table,
			$row_id,
			$column
		) = $this->_input->receive (
			'table',
			'row_id',
			'column'
		);

//		print_r ($_POST);

		$prefix = Model_Scheme::$default ['prefix'];
		$class_name = $this->__className ($table, $prefix);
		$fields = Helper_Data_Source::fields ('`' . $table . '`');
		$acl_fields = $this->__aclFields ($table, $fields);

		if (!$acl_fields || !User::id ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		/* @var $row Model */
		$row = Model_Manager::get (
			$class_name,
			$row_id
		);

		$exists_links = Model_Scheme::links ($class_name);
		$links_to_save = array ();

		if (!is_array ($column))
		{
			return;
		}

		foreach ($column as $field => $value)
		{
			if (isset ($exists_links [$field]))
			{
				$links_to_save [$field] = $value;
				unset ($column [$field]);
			}
		}

		foreach ($column as $field => $value)
		{
			if (!in_array ($field, $acl_fields))
			{
				unset ($column [$field]);
			}
		}

		$modificators = array ();

		$tmp = $this->getAdminConfig ($class_name)->modificators;

		if ($tmp)
		{
			$modificators = $tmp->__toArray ();
		}

		$updated_fields = $column;

		foreach ($updated_fields as $field => $value)
		{
			if (isset ($modificators [$field]))
			{
				$value = call_user_func (
					$modificators [$field],
					$value
				);
				//echo $value . '<br />';
				$column [$field] = $value;
				$updated_fields [$field] = $value;
			}
		}

		if ($row->key ())
		{
			foreach ($column as $field => $value)
			{
				if ($value === $row->field ($field))
				{
					unset ($updated_fields [$field]);
				}
			}

			if ($updated_fields)
			{
				$row->update ($updated_fields);
//				print_r ($updated_fields);
//				echo DDS::getDataSource ()->getQuery ()->translate ();
			}
		}
		else
		{
			if ($updated_fields)
			{
				$row->set (Model_Scheme::keyField ($row->modelName ()), null);
				$row->set ($updated_fields);
				$row->save ();
//				print_r ($row);
//				echo DDS::getDataSource ()->getQuery ()->translate ();
			}
		}

		foreach ($links_to_save as $link => $links)
		{
			Helper_Link::unlinkWith ($row, $link);

			if (!is_array ($links))
			{
				continue;
			}

			foreach ($links as $link_id)
			{
				$link_row = Model_Manager::byKey (
					$link,
					$link_id
				);

				if ($link_row)
				{
					Helper_Link::link ($row, $link_row);
				}
			}
		}

		$this->__log (
			__METHOD__,
			$table,
			$row_id,
			$updated_fields
		);

		$after_save = $this->getAdminConfig ($class_name)->afterSave;

		if ($after_save)
		{
			foreach ($after_save as $action)
			{
				list ($controller, $action) = explode ('::', $action);

				Controller_Manager::call (
					$controller,
					$action,
					array (
						'table'			=> $table,
						'row'			=> $row
					)
				);
			}
		}
//		print_r ($updated_fields);
//		echo DDS::getDataSource ()->getQuery ()->translate ();

		Helper_Header::redirect ('/cp/table/' . $table . '/');
	}
}
