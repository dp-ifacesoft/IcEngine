<?php

class Controller_Model extends Controller_Abstract
{
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
			Loader::load ('Helper_Code_Generator');

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
				Loader::load ('Helper_Code_Generator');

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
		Loader::load ('Helper_Code_Generator');
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
		Loader::load ('Model_Mapper');
		Loader::load ('Model_Proxy');
		$model = new Model_Proxy (
			$name,
			array (
				Model_Scheme::keyField ($name)	=> $id
			)
		);
		$scheme = Model_Mapper::scheme ($model);
		Loader::load ('Model_Mapper_Scheme_Render_View');
		$view = Model_Mapper_Scheme_Render_View::byName ('Mysql');
		$query = $view->render ($scheme);
		DDS::execute ($query);
		echo 'Query: ' . PHP_EOL;
		echo $query->translate ('Mysql') . PHP_EOL;
	}

	public function fromTable ($name, $author, $comment, $missing = 0)
	{
		$this->_task->setTemplate (null);

		Loader::load ('Helper_Data_Source');

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
			$fields = Helper_Data_Source::fields ('`' . $name . '`');
			$info = Helper_Data_Source::table ('`' . $name . '`');
			$model_name = Model_Scheme::tableToModel ($name);
			$dir = IcEngine::root () . 'Ice/Config/Model/Mapper/';
			$name_dir = explode ('_', $model_name);
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
			$filename = $current_dir . $filename . '.php';
			if (is_file ($filename))
			{
				continue;
			}

			$result_fields = array ();
			$result_keys = array ();
			$query = Query::instance ()
				->show ('KEYS')
				->from ('`' . $name . '`');
			$keys = DDS::execute ($query)->getResult ()->asTable ();

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

			Loader::load ('Helper_Code_Generator');
			$output = Helper_Code_Generator::fromTemplate (
				'scheme',
				array (
					'author'	=> $author,
					'comment'	=> $comment,
					'fields'	=> $result_fields,
					'indexes'	=> $result_keys
				)
			);

			echo 'File: ' . $filename . PHP_EOL;
			file_put_contents ($filename, $output);
		}
	}

	public function missing ()
	{
		Loader::load ('Helper_Data_Source');
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
				'id',
				array ('id')
			)
		);

		Loader::load ('Helper_Code_Generator');
			$output = Helper_Code_Generator::fromTemplate (
				'scheme',
				array (
					'author'	=> $author,
					'comment'	=> $comment,
					'fields'	=> $fields,
					'indexes'	=> $indexes
				)
			);

		echo $output;

		file_put_contents ($filename, $output);
	}
}