<?php

class Controller_Model extends Controller_Abstract
{
	/**
	 * Сравнение схем моделей и таблиц
	 */
	public function compare($author, $filename)
	{
		$this->_task->setTemplate(null);
		$dir = IcEngine::root() . 'Ice/Config/Model/Mapper/';
		$exec = 'find ' . $dir . '*';
		ob_start();
		system($exec);
		$content = ob_get_contents();
		ob_end_clean();
		if (!$content) {
			return;
		}
		$files = explode(PHP_EOL, $content);
		if (!$files) {
			return;
		}
		$models = array();
		foreach ($files as $file) {
			if (!is_file($file)) {
				continue;
			}
			$file = str_replace(
				str_replace('/', '_', IcEngine::root()) .
				'Ice_Config_Model_Mapper_',
				'',
				str_replace ('/', '_', $file)
			);
			$className = substr($file, 0, -4);
			if ($className == 'Scheme') {
				continue;
			}
			$models[] = $className;
		}
		foreach ($models as $model) {
			$fields = Helper_Data_Source::fields($model);
			$table = Model_Scheme::table($model);
			if (!$fields) {
				echo 'Model "' . $model . '" not have a table. Create? [Y/n] ';
				$a = fgets(STDIN);
				if ($a[0] == 'Y') {
					Controller_Manager::call(
						'Model', 'create', array(
							'name'		=> $model,
							'author'	=> $author
						)
					);
				}
			} else {
				$tableFields = Model_Scheme::makeScheme($fields);
				$modelScheme = Model_Scheme::getScheme($model);
				if (!$modelScheme) {
					continue;
				}
				$modelFields = $modelScheme['fields'];
				$addedFields = array_diff_key($modelFields, $tableFields);
				$deletedFields = array_diff_key($tableFields, $modelFields);
				$remainingFields = array_diff_key(
					$tableFields, array_merge(
						array_keys($addedFields),
						array_keys($deletedFields)
					)
				);
				if ($addedFields) {
					foreach ($addedFields as $fieldName => $data) {
						echo 'In model "' . $model . '" had added field "' .
							$fieldName . '". Create? [Y/n] ';
						$a = fgets(STDIN);
						if ($a[0] == 'Y') {
							$field = new Model_Field($fieldName);
							$field->setNullable(false)
								->setType($data['type'])
								->setComment($data['comment']);
							if (!empty($data['default'])) {
								$field->setDefault($data['default']);
							}
							if (!empty($data['size'])) {
								$field->setSize($data['size']);
							}
							if (!empty($data['auto_inc'])) {
								$field->setAutoIncrement(true);
							}
							$query = Query::instance()
								->alterTable($table)
								->add($field);
							if ($filename) {
								file_put_contents(
									$filename,
									$query->translate() . PHP_EOL,
									FILE_APPEND
								);
							} else {
								DDS::execute($query);
							}
						}
					}
				}
				if ($deletedFields) {
					foreach ($deletedFields as $fieldName => $data) {
						echo 'In model "' . $model . '" had deleted field "' .
							$fieldName . '". Delete? [Y/n] ';
						$a = fgets(STDIN);
						if ($a[0] == 'Y') {
							$field = new Model_Field($fieldName);
							$query = Query::instance()
								->alterTable($table)
								->drop($field);
							if ($filename) {
								file_put_contents(
									$filename,
									$query->translate() . PHP_EOL,
									FILE_APPEND
								);
							} else {
								DDS::execute($query);
							}
						}
					}
				}
				if ($remainingFields) {
					foreach ($remainingFields as $fieldName => $tableData) {
						if (!isset($modelFields[$fieldName])) {
							continue;
						}
						$fieldData = $modelFields[$fieldName];
						$changedAttributes = @array_diff_assoc(
							$tableData, $fieldData
						);
						$modelChangedFields = @array_diff_assoc(
							$fieldData, $tableData
						);
						if (count($modelChangedFields) !=
							count($changedAttributes)) {
							foreach ($modelChangedFields as $attrName => $value) {
								if (isset($changedAttributes[$attrName])) {
									continue;
								}
								echo 'In model "' . $model .
									'" had changed field "' .
									$fieldName . '" with added attribute "' .
									$attrName .	'". Apply changes? [Y/n] ';
								$a = fgets(STDIN);
								if ($a[0] == 'Y') {
									$field = new Model_Field($fieldName);
									$field->setNullable(false)
										->setType($tableData['type'])
										->setComment($tableData['comment']);
									if (!empty($tableData['default'])) {
										$field->setDefault($tableData['default']);
									}
									if (!empty($tableData['size'])) {
										$field->setSize($tableData['size']);
									}
									if (!empty($tableData['auto_inc'])) {
										$field->setAutoIncrement(true);
									}
									if ($attrName == 'auto_inc') {
										$attrName = 'auto_increment';
									}
									$attrName = 'ATTR_' . strtoupper($attrName);
									$attr = constant('Model_Field::' . $attrName);
									$field->setAttr($attr, $value);
									$query = Query::instance()
										->alterTable($table)
										->change($fieldName, $field);
									if ($filename) {
										file_put_contents(
											$filename,
											$query->translate() . PHP_EOL,
											FILE_APPEND
										);
									} else {
										DDS::execute($query);
									}
								}
							}
						}
						if ($changedAttributes) {
							foreach ($changedAttributes as $attrName => $value) {
								if (isset($modelChangedFields[$attrName])) {
									continue;
								}
								if (!isset($fieldData[$attrName])) {
									continue;
								}
								echo 'In model "' . $model .
									'" had changed field "' .
									$fieldName . '" with changed attribute "' .
									$attrName .	'". Apply changes? [Y/n] ';
								$a = fgets(STDIN);
								if ($a[0] == 'Y') {
									$field = new Model_Field($fieldName);
									$field->setNullable(false)
										->setType($tableData['type'])
										->setComment($tableData['comment']);
									if (!empty($tableData['default'])) {
										$field->setDefault($tableData['default']);
									}
									if (!empty($tableData['size'])) {
										$field->setSize($tableData['size']);
									}
									if (!empty($tableData['auto_inc'])) {
										$field->setAutoIncrement(true);
									}
									$oldAttr = $attrName;
									if ($attrName == 'auto_inc') {
										$attrName = 'auto_increment';
									}
									$attrName = 'ATTR_' . strtoupper($attrName);
									$attr = constant('Model_Field::' . $attrName);
									if (isset($fieldData[$oldAttr])) {
										$field->setAttr(
											$attr, $fieldData[$oldAttr]
										);
									}
									$query = Query::instance()
										->alterTable($table)
										->change($fieldName, $field);
									if ($filename) {
										file_put_contents(
											$filename,
											$query->translate() . PHP_EOL,
											FILE_APPEND
										);
									} else {
										DDS::execute($query);
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @desc Создание модели
	 * @param string $name Название модели
	 * @param string $author Автор
	 * @param boolean $without_table Создавать ли таблицу
	 * @param string $extends От кого наследовать модель? По умолчанию от Model
	 * @param boolean $with_load Нужно ли подгружать через Loader::load класс родителя
	 * @param string $comment Комментарий
	 * @param boolean $without_collection Создавать ли коллекцию
	 * @param boolean $without_options Создавать ли директорию для опшинов
	 * @param integer $id Айди сущности, если необходимо
	 */
	public function create ($name, $author, $without_table, $extends, $with_load,
		$comment, $without_collection, $without_options, $id = 0)
	{
		$this->_task->setTemplate (null);

		$config = Config_Manager::get ('Model_Mapper_' . $name);
		if (!$config)
		{
			echo 'Scheme for model "' . $name . '" had not found. Exit' . PHP_EOL;
			return;
		}
		$dir = IcEngine::root () . 'Ice/Model/';
		$name_dir = explode ('_', $name);
		$filename = array_pop ($name_dir);
		$current_dir = $dir;
		if ($name_dir)
		{
			foreach ($name_dir as $dir)
			{
				$current_dir .= $dir . '/';
				if (!is_dir ($current_dir))
				{
					mkdir ($current_dir);
				}
			}
		}
		$dir = $current_dir . $filename . '/';
		if (!$without_collection)
		{
			if (!is_dir ($dir))
			{
				mkdir ($dir);
			}
		}
		if (!$without_options)
		{
			if (!is_dir ($dir . 'Option'))
			{
				mkdir ($dir . 'Option');
			}
		}
		$filename = $current_dir . $filename . '.php';

		$author = $author ? $author : $config->author;

		if (!is_file ($filename))
		{
			$properties = array ();
			if ($config->fields)
			{
				foreach ($config->fields as $field => $values)
				{
					$type = $values [0];
					if ($type == 'tinyint')
					{
						$type = 'boolean';
					}
					elseif (strpos (strtolower ($type), 'int') !== false)
					{
						$type = 'integer';
					}
					else
					{
						$type = 'string';
					}
					$comment = !empty ($values [1]['Comment'])
						? $values [1]['Comment'] : '';
					$properties [] = array (
						'type'		=> $type,
						'field'		=> $field,
						'comment'	=> $comment
					);
				}
			}

			$output = Helper_Code_Generator::fromTemplate (
				'model',
				array (
					'extends'	=> $extends ? $extends : 'Model',
					'with_load'	=> $with_load,
					'comment'	=> $comment ? $comment : $config->comment,
					'date'		=> Helper_Date::toUnix (),
					'author'	=> $author,
					'package'	=> 'Vipgeo',
					'category'	=> 'Models',
					'copyright'	=> 'i-complex.ru',
					'name'		=> $name,
					'properties'	=> $properties
				)
			);

			echo 'File: ' . $filename . PHP_EOL;
			file_put_contents ($filename, $output);

			echo $output . PHP_EOL . PHP_EOL;
		}

		if (!$without_collection)
		{
			$filename = $dir . 'Collection.php';

			if (!is_file ($filename))
			{
				$output = Helper_Code_Generator::fromTemplate (
					'model',
					array (
						'extends'	=> $extends ? $extends : 'Model_Collection',
						'with_load'	=> $with_load,
						'comment'	=> 'Collection for model ' . $name,
						'date'		=> Helper_Date::toUnix (),
						'author'	=> $author,
						'package'	=> 'Vipgeo',
						'category'	=> 'Collections',
						'copyright'	=> 'i-complex.ru',
						'name'		=> $name . '_Collection'
					)
				);

				echo 'File: ' . $filename . PHP_EOL;
				file_put_contents ($filename, $output);

				echo $output . PHP_EOL . PHP_EOL;
			}
		}

		if (!$without_table && $config->fields)
		{
			Controller_Manager::call (
				'Model', 'createTable',
				array (
					'name'	=> $name,
					'id'	=> $id
				)
			);
		}
	}

	public function createMissing ()
	{
		$task = Controller_Manager::call (
			'Model', 'missing', array ()
		);
		if (!$task)
		{
			return;
		}
		$buffer = $task->getTransaction ()->buffer ();
		if (empty ($buffer ['missings']))
		{
			return;
		}
		$missings = $buffer ['missings'];
		foreach ($missings as $model_name)
		{
			Controller_Manager::call (
				'Model', 'create', array (
					'name'	=> $model_name
				)
			);
		}
	}

	public function createOption ($model_name, $name, $author)
	{
		$this->_task->setTemplate (null);
		$dir = IcEngine::root () . 'Ice/Model/' .
			str_replace ('_', '/', $model_name) .
			'/Option/';
		if (!is_array ($name))
		{
			$name = explode (',', $name);
		}
		foreach ($name as $n)
		{
			$n = trim ($n);
			$filename = $dir . str_replace ('_', '/', $n) . '.php';
			$dirname = dirname ($filename);
			if (!is_dir ($dirname))
			{
				mkdir ($dirname, 0750, true);
			}
			if (file_exists ($filename))
			{
				continue;
			}
			echo 'File: ' . $filename . PHP_EOL;
			$output = Helper_Code_Generator::fromTemplate (
				'model_option',
				array (
					'author'		=> $author,
					'model_name'	=> $model_name,
					'name'			=> $n,
					'package'		=> 'Vipgeo',
					'date'			=> Helper_Date::toUnix ()
				)
			);
			file_put_contents ($filename, $output);
		}
	}

	public function createTable ($name, $id)
	{
		$model = new Model_Proxy (
			$name,
			array (
				Model_Scheme::keyField ($name)	=> $id
			)
		);
		$scheme = Model_Mapper::scheme ($model);
		$view = Model_Mapper_Scheme_Render_View::byName ('Mysql');
		$query = $view->render ($scheme);
		DDS::execute ($query);
		echo 'Query: ' . PHP_EOL;
		echo $query->translate ('Mysql') . PHP_EOL;
	}

	public function fromTable ($name, $author, $comment, $missing = 0, $rewrite = 0, $ds = NULL)
	{

        if (!$ds) {
            $ds = DDS::getDataSource();
        }

		$this->_task->setTemplate (null);

		if (!$missing)
		{
			$names = array ($name);
		}
		else
		{
			$comment = '';
			$tables = Helper_Data_Source::tables ();
			$names = array ();
			foreach ($tables as $table)
			{
				$names [] = $table ['Name'];
			}
		}

		foreach ($names as $name)
		{
			$fields = Helper_Data_Source::fields ('`' . $name . '`', $ds);
			$info = Helper_Data_Source::table ('`' . $name . '`', $ds);
			$model_name = Model_Scheme::tableToModel ($name);
			$dir = IcEngine::root () . 'Ice/Config/Model/Mapper/';
			$name_dir = explode ('_', $model_name);
			$filename = array_pop ($name_dir);
			$current_dir = $dir;
			$exists_scheme = Config_Manager::get ('Model_Mapper_' . $model_name);
			$references = array ();
			$admin_panel = array ();
			if ($exists_scheme)
			{
				$author = $author ? $author : $exists_scheme->author;
				$comment = $comment ? $comment : $exists_scheme->comment;
				$references = $exists_scheme->references;
				$admin_panel = $exists_scheme->admin_panel;
			}
			if ($name_dir)
			{
				foreach ($name_dir as $dir)
				{
					$current_dir .= $dir . '/';
					if (!is_dir ($current_dir))
					{
						mkdir ($current_dir);
					}
				}
			}
			$filename = $current_dir . $filename . '.php';
			if (is_file ($filename) && !$rewrite)
			{
				continue;
			}

			$result_fields = array ();
			$result_keys = array ();
			$query = Query::instance ()
				->show ('KEYS')
				->from ('`' . $name . '`');

            //$scheme = Model_Scheme::getScheme('Sn_User_Dialog');
            //print_r($scheme);die;

            //$ds = Data_Source_Manager::

			$keys = $ds->execute ($query)->getResult ()->asTable ();

			foreach ($fields as $field)
			{
				$unsigned = false;
				$type = $field ['Type'];
				$size = null;
				$values = array ();
				$tmp = explode ('(', $type);
				if (isset ($tmp [1]))
				{
					$type = $tmp [0];
					if (strpos ($tmp [1], ' ') !== false && $type != 'enum')
					{
						$unsigned = true;
						$size = substr ($tmp [1], 0, strpos ($tmp [1], ')'));
					}
					else
					{
						$size = rtrim ($tmp [1], ')');
					}
					if (strpos ($size, ',') !== false)
					{
						$size = explode (',', $size);
					}
					if ($type == 'enum')
					{
						$values = $size;
						$size = '';
					}
				}
				$result_fields [$field ['Field']] = array (
					ucfirst ($type),
					array ()
				);
				if ($size)
				{
					$result_fields [$field ['Field']][1]['Size'] = $size;
				}
				if ($values)
				{
					$result_fields [$field ['Field']][1]['Enum'] = $values;
				}
				if (
					strpos ($field ['Type'], 'text') === false &&
					(
						strpos ($field ['Type'], 'date') === false ||
						!empty ($field ['Default'])
					)
				)
				{
					if (
						strpos (strtolower ($type), 'int') === false ||
						is_numeric ($field ['Default'])
					)
					{
						$result_fields [$field ['Field']][1]['Default'] =
							$field ['Default'];
					}

				}
				if (!empty ($field ['Comment']))
				{
					$result_fields [$field ['Field']][1]['Comment'] = addslashes (
						$field ['Comment']
					);
				}
				if ($unsigned)
				{
					$result_fields [$field ['Field']][1][] = 'Unsigned';
				}
				if ($field ['Null'] == 'YES')
				{
					$result_fields [$field ['Field']][1][] = 'Not_Null';
				}
				else
				{
					$result_fields [$field ['Field']][1][] = 'Null';
				}
				if ($field ['Extra'] == 'auto_increment')
				{
					$result_fields [$field ['Field']][1][] = 'Auto_Increment';
				}
			}

			foreach ($keys as $key)
			{
				$name = $key ['Key_name'];
				if (!isset ($result_keys [$name]))
				{
					$result_keys [$name] = $key;
				}
				if (!is_array ($result_keys [$name]['Column_name']))
				{
					$result_keys [$name]['Column_name'] = array ();
				}
				$result_keys [$name]['Column_name'][] = $key ['Column_name'];
			}
			$keys = array_values ($result_keys);

			$result_keys = array ();

			foreach ($keys as $key)
			{
				$key_name = $key ['Key_name'] != 'PRIMARY'
					? $key ['Key_name'] : 'id';
				$key_name .= '_index';
				$type = $key ['Non_unique'] ? 'Key' : 'Unique';
				$type = $key ['Key_name'] == 'PRIMARY' ? 'Primary' : $type;
				$result_keys [$key_name] = array (
					$type,
					$key ['Column_name']
				);
			}

			if (!$comment && !empty ($info ['Comment']))
			{
				$comment = $info ['Comment'];
			}

			if (!empty($admin_panel)) {
				$admin_panel =  '\'admin_panel\' => ' . Helper_Converter::arrayToString($admin_panel);
			}

			$output = Helper_Code_Generator::fromTemplate (
				'scheme',
				array (
					'author'		=> $author,
					'comment'		=> $comment,
					'fields'		=> $result_fields,
					'indexes'		=> $result_keys,
					'references'	=> $references,
					'admin_panel'	=> $admin_panel
				)
			);

			echo 'File: ' . $filename . PHP_EOL;
			file_put_contents ($filename, $output);
		}
	}

	public function missing ()
	{
		$tables = Helper_Data_Source::tables ();
		$exists_models = array ();
		foreach ($tables as $table)
		{
			$exists_models [] = Model_Scheme::tableToModel ($table->Name);
		}
		$dir = IcEngine::root () . 'Ice/Config/Model/Mapper/';
		$exec = 'find ' . $dir . '*';
		ob_start ();
		system ($exec);
		$content = ob_get_contents ();
		ob_end_clean ();
		if (!$content)
		{
			return;
		}
		$files = explode (PHP_EOL, $content);
		if (!$files)
		{
			return;
		}
		$config_models = array ();
		foreach ($files as $file)
		{
			if (!is_file ($file))
			{
				continue;
			}
			$class_name = str_replace (
				IcEngine::root () . 'Ice/Config/Model/Mapper/',
				'',
				$file
			);
			$class_name = substr (str_replace ('/', '_', $class_name), 0, -4);
			if ($class_name == 'Scheme')
			{
				continue;
			}
			$config_models [] = $class_name;
		}
		$result = array ();
		foreach ($config_models as $model)
		{
			if (!in_array ($model, $exists_models))
			{
				$result []  = $model;
			}
		}
		$this->_output->send (array (
			'missings'	=> $result
		));
		print_r ($result);
	}

	/**
	 * @desc Создает схему модели
	 * @param string $name
	 * @param string $comment
	 * @param string $author
	 */
	public function scheme ($name, $comment, $author)
	{
		$this->_task->setTemplate (null);

		if (!$name)
		{
			echo 'Scheme must contains name.' . PHP_EOL;
			return;
		}
		$dir = IcEngine::root () . 'Ice/Config/Model/Mapper/';
		$name_dir = explode ('_', $name);
		$filename = array_pop ($name_dir) . '.php';
		if (is_file ($filename))
		{
			return;
		}
		$current_dir = $dir;
		if ($name_dir)
		{
			foreach ($name_dir as $dir)
			{
				$current_dir .= $dir . '/';
				if (!is_dir ($current_dir))
				{
					mkdir ($current_dir);
				}
			}
		}
		$filename = $current_dir . $filename;

		echo 'File: ' . $filename . PHP_EOL . PHP_EOL;

		$fields = array (
			'id'	=> array (
				'Int',
				array (
					'Size'	=> 11,
					'Not_Null',
					'Auto_Increment'
				)
			)
		);

		$indexes = array (
			'id'	=> array (
				'Primary',
				array ('id')
			)
		);

		$admin_panel = "'admin_panel' => array (
			// Сортировки
//			'sort'		=> '',
//
//			'search_fields'	=> array (
//
//			),
//
//			// Выводимые поля
//			'fields'  => array ('id'),
//
//			// Стили
//			'styles'	=> array (
//
//			),
//
//			// Стили ссылок
//			'link_styles' => array (
//
//			),
//
//			// Поля, являющиеся ссылками
//			'links'		=> array (
//
//			),
//
//			// Лимиты
//			'limit'	=> 1000,
//
//			// Эвенты
//			'event' => array (
//
//			),
//
//			// Фильтры
//			'filter'	=> array (
//
//			),
//
//			// Фильтры значений при подстановки
//			'field_filters'	=> array (
//
//			),
//
//			// Плагины
//			'plugin'	=> array (
//
//			),
//
//			// Заголовок
//			'title'	=>  'title',
//
//			// Подстановки
//			'includes'	=> array (
//
//			),
//
//			// Ограничители
//			'limitators'	=> array (
//
//			),
//
//			// Автозаполнители
//			'auto_select'  => array (
//
//			),
//
//			// Модификаторы
//			'modificators' => array (
//
//			),
//
//			'afterSave'	=> array (
//
//			)
		)";
			$output = Helper_Code_Generator::fromTemplate (
				'scheme',
				array (
					'author'	=> $author,
					'comment'	=> $comment,
					'fields'	=> $fields,
					'indexes'	=> $indexes,
					'admin_panel'	=> $admin_panel
				)
			);

		echo $output;

		file_put_contents ($filename, $output);
	}

	/**
	 * @desc Обновляет схему модели
	 */
	public function schemeUpdate ($name, $comment, $author, $fields, $indexes, $admin_panel)
	{
		$this->_task->setTemplate (null);

		if (!$name)
		{
			echo 'Scheme must contains name.' . PHP_EOL;
			return;
		}
		$dir = IcEngine::root () . 'Ice/Config/Model/Mapper/';
		$name_dir = explode ('_', $name);
		$filename = array_pop ($name_dir) . '.php';
		var_dump ($filename);

		$scheme = Config_Manager::get ('Model_Mapper_' . $name);

		$current_dir = $dir;
		if ($name_dir)
		{
			foreach ($name_dir as $dir)
			{
				$current_dir .= $dir . '/';
				if (!is_dir ($current_dir))
				{
					mkdir ($current_dir);
				}
			}
		}
		$filename = $current_dir . $filename;

		echo 'File: ' . $filename . PHP_EOL . PHP_EOL;

		$fields = !$fields ? $scheme ['fields'] : $fields;

		$indexes = !$indexes ? $scheme ['indexes'] : $indexes;

		$admin_panel = !$admin_panel ? $scheme ['admin_panel'] : $admin_panel;

		$admin_panel = "'admin_panel' => " . $admin_panel;

		$output = Helper_Code_Generator::fromTemplate (
				'scheme',
				array (
					'author'	=> $author,
					'comment'	=> $comment,
					'fields'	=> $fields,
					'indexes'	=> $indexes,
					'admin_panel'	=> $admin_panel
				)
			);

		echo $output;

		file_put_contents ($filename, $output);
	}

}