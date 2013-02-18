<?php

/**
 * Контроллер модели
 *
 * @author ..., neon, markov
 */
class Controller_Model extends Controller_Abstract
{
    /**
	 * Сравнение схем моделей и таблиц
	 */
	public function compare($author, $filename, $modelName)
	{
        $helperDataSource = $this->getService('helperDataSource');
        $modelSchemeService = $this->getService('modelScheme');
        $controllerManager = $this->getService('controllerManager');
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
		$this->task->setTemplate(null);
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
        if (!$modelName) {
            foreach ($files as $file) {
                if (!is_file($file)) {
                    continue;
                }
                $file = str_replace(
                    str_replace('/', '_', IcEngine::root()) .
                    'Ice_Config_Model_Mapper_',
                    '',
                    str_replace('/', '_', $file)
                );
                $className = substr($file, 0, -4);
                if ($className == 'Scheme') {
                    continue;
                }
                $models[] = $className;
            }
        } else {
            $models  = array($modelName);
        }
		foreach ($models as $model) {
			$fields = $helperDataSource->fields($model);
			$table = $modelScheme->table($model);
			if (!$fields) {
				echo 'Model "' . $model . '" not have a table. Create?[Y/n] ';
				$a = fgets(STDIN);
				if ($a[0] == 'Y') {
					$controllerManager->call(
						'Model', 'create', array(
							'name'		=> $model,
							'author'	=> $author
						)
					);
				}
			} else {
				$tableFields = $modelSchemeService->makeScheme($fields);
				$modelScheme = $modelSchemeService->getScheme($model);
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
				$helperControllerModel->compareAddedFields($addedFields,
                    $model, $table, $filename
                );
                $helperControllerModel->compareDeletedFields($deletedFields,
                    $model, $table, $filename
                );
                $helperControllerModel->compareRemainingFields($remainingFields,
                    $modelFields, $model, $filename
                );
			}
		}
	}

	/**
	 * Создание модели
	 *
	 * @param string $name Название модели
	 * @param string $author Автор
	 * @param boolean $withoutTable Создавать ли таблицу
	 * @param string $extends От кого наследовать модель? По умолчанию от Model
	 * @param boolean $withLoad Нужно ли подгружать через Loader::load класс родителя
	 * @param string $comment Комментарий
	 * @param boolean $withoutCollection Создавать ли коллекцию
	 * @param boolean $withoutOptions Создавать ли директорию для опшинов
	 * @param integer $id Айди сущности, если необходимо
	 */
	public function create($name, $author, $withoutTable, $extends, $withLoad,
		$comment, $withoutCollection, $withoutOptions, $id = 0)
	{
		$this->task->setTemplate(null);
		$configManager = $this->getService('configManager');
		$helperCodeGenerator = $this->getService('helperCodeGenerator');
		$helperDate = $this->getService('helperDate');
		$controllerManager = $this->getService('controllerManager');
		$config = $configManager->get('Model_Mapper_' . $name);
		if (!$config) {
			echo 'Scheme for model "' . $name . '" had not found. Exit' .
				PHP_EOL;
			return;
		}
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($name);
        if ($dataSource instanceof Data_Source_Defined) {
            return;
        }
		$dir = IcEngine::root() . 'Ice/Model/';
		$nameDir = explode('_', $name);
		$filename = array_pop($nameDir);
		$currentDir = $dir;
		if ($nameDir) {
			foreach ($nameDir as $dir) {
				$currentDir .= $dir . '/';
				if (!is_dir($currentDir)) {
					mkdir($currentDir);
				}
			}
		}
		$dir = $currentDir . $filename . '/';
		if (!$withoutCollection) {
			if (!is_dir($dir)) {
				mkdir($dir);
			}
		}
		if (!$withoutOptions) {
			if (!is_dir($dir . 'Option')) {
				mkdir($dir . 'Option');
			}
		}
		$filename = $currentDir . $filename . '.php';
		$author = $author ? $author : $config->author;
        $modelCreated = false;
		if (!is_file($filename) && !class_exists($name)) {
			$properties = array();
			if ($config->fields) {
				foreach ($config->fields as $field => $values) {
					$type = $values[0];
					if ($type == 'tinyint') {
						$type = 'boolean';
					} elseif (strpos(strtolower($type), 'int') !== false) {
						$type = 'integer';
					} else {
						$type = 'string';
					}
					$comment = !empty($values[1]['Comment'])
						? $values[1]['Comment'] : '';
					$properties[] = array(
						'type'		=> $type,
						'field'		=> $field,
						'comment'	=> $comment
					);
				}
			}
			$output = $helperCodeGenerator->fromTemplate(
				'model',
				array(
					'extends'	=> $extends ? $extends : 'Model',
					'with_load'	=> $withLoad,
					'comment'	=> $comment ? $comment : $config->comment,
					'date'		=> $helperDate->toUnix(),
					'author'	=> $author,
					'package'	=> 'Vipgeo',
					'category'	=> 'Models',
					'copyright'	=> 'i-complex.ru',
					'name'		=> $name,
					'properties'	=> $properties
				)
			);
			echo 'File: ' . $filename . PHP_EOL;
			file_put_contents($filename, $output);
			echo $output . PHP_EOL . PHP_EOL;
            $modelCreated = true;
		}
		if (!$withoutCollection && !class_exists($name . '_Collection')) {
			$filename = $dir . 'Collection.php';
			if (!is_file($filename)) {
				$output = $helperCodeGenerator->fromTemplate(
					'model',
					array(
						'extends'	=> $extends ? $extends : 'Model_Collection',
						'with_load'	=> $withLoad,
						'comment'	=> 'Collection for model ' . $name,
						'date'		=> $helperDate->toUnix(),
						'author'	=> $author,
						'package'	=> 'Vipgeo',
						'category'	=> 'Collections',
						'copyright'	=> 'i-complex.ru',
						'name'		=> $name . '_Collection'
					)
				);
				echo 'File: ' . $filename . PHP_EOL;
				file_put_contents($filename, $output);
				echo $output . PHP_EOL . PHP_EOL;
			}
		}
		if (!$withoutTable && $config->fields) {
			$controllerManager->call(
				'Model', 'createTable',
				array(
					'name'	=> $name,
					'id'	=> $id
				)
			);
		}
	}

	public function createMissing()
	{
		$controllerManager = $this->getService('controllerManager');
		$task = $controllerManager->call(
			'Model', 'missing', array()
		);
		if (!$task) {
			return;
		}
		$buffer = $task->getTransaction()->buffer();
		if (empty($buffer['missings'])) {
			return;
		}
		$missings = $buffer['missings'];
		foreach ($missings as $model_name) {
			$controllerManager->call(
				'Model', 'create', array(
					'name'	=> $model_name
				)
			);
		}
	}

	public function createOption($model_name, $name, $author)
	{
		$this->task->setTemplate(null);
		$dir = IcEngine::root() . 'Ice/Model/' .
			str_replace('_', '/', $model_name) .
			'/Option/';
		if (!is_array($name)) {
			$name = explode(',', $name);
		}
		$helperCodeGenerator = $this->getService('helperCodeGenerator');
		$helperDate = $this->getService('helperDate');
		foreach ($name as $n) {
			$n = trim($n);
			$filename = $dir . str_replace('_', '/', $n) . '.php';
			$dirname = dirname($filename);
			if (!is_dir($dirname)) {
				mkdir($dirname, 0750, true);
			}
			if (file_exists($filename)) {
				continue;
			}
			echo 'File: ' . $filename . PHP_EOL;
			$output = $helperCodeGenerator->fromTemplate(
				'model_option',
				array (
					'author'		=> $author,
					'model_name'	=> $model_name,
					'name'			=> $n,
					'package'		=> 'Vipgeo',
					'date'			=> $helperDate->toUnix()
				)
			);
			file_put_contents($filename, $output);
		}
	}

	public function createTable($name, $id)
	{
		$modelScheme = $this->getService('modelScheme');
		$modelMapper = $this->getService('modelMapper');
		$modelMapperSchemeRenderView = $this->getService(
			'modelMapperSchemeRenderView'
		);
		$dds = $this->getService('dds');
		$model = new Model_Proxy(
			$name,
			array(
				$modelScheme->keyField($name)	=> $id
			)
		);
		$scheme = $modelMapper->scheme($model);
		$view = $modelMapperSchemeRenderView->byName('Mysql');
		$query = $view->render($scheme);
		$dds->execute($query);
		echo 'Query: ' . PHP_EOL;
		echo $query->translate('Mysql') . PHP_EOL;
	}

	public function fromTable($name, $author, $comment, $missing = 0,
		$rewrite = 0, $ds = NULL)
	{
		$dds = $this->getService('dds');
		$helperDataSource = $this->getService('helperDataSource');
		$modelScheme = $this->getService('modelScheme');
		$configManager = $this->getService('configManager');
		$queryBuilder = $this->getService('query');
		$helperConverter = $this->getService('helperConverter');
		$helperCodeGenerator = $this->getService('helperCodeGenerator');
        if (!$ds) {
            $ds = $dds->getDataSource();
        }
		$this->task->setTemplate(null);
		if (!$missing) {
			$names = array($name);
		} else {
			$comment = '';
			$tables = $helperDataSource->tables();
			$names = array();
			foreach ($tables as $table) {
				$names[] = $table['Name'];
			}
		}
		foreach ($names as $name) {
			$fields = $helperDataSource->fields('`' . $name . '`', $ds);
			$info = $helperDataSource->table('`' . $name . '`', $ds);
			$modelName = $modelScheme->tableToModel($name);
			$dir = IcEngine::root() . 'Ice/Config/Model/Mapper/';
			$nameDir = explode('_', $modelName);
			$filename = array_pop($nameDir);
			$currentDir = $dir;
			$existsScheme = $configManager->get('Model_Mapper_' . $modelName);
			$references = array();
			$adminPanel = array();
            $languageScheme = array();
            $createScheme = array();
			if ($existsScheme) {
				$author = $author ? $author : $existsScheme->author;
				$comment = $comment ? $comment : $existsScheme->comment;
				$references = $existsScheme->references;
				$adminPanel = $existsScheme->admin_panel;
                $languageScheme = $existsScheme->languageScheme;
                $createScheme = $existsScheme->createScheme;
			}
			if ($nameDir) {
				foreach ($nameDir as $dir) {
					$currentDir .= $dir . '/';
					if (!is_dir($currentDir)) {
						mkdir($currentDir);
					}
				}
			}
			$filename = $currentDir . $filename . '.php';
			if (is_file($filename) && !$rewrite) {
				continue;
			}
			$resultFields = array();
			$resultKeys = array();
			$query = $queryBuilder->show('KEYS')
				->from('`' . $name . '`');
			$keys = $ds->execute($query)->getResult()->asTable();
			foreach ($fields as $field) {
				$unsigned = false;
				$type = $field['Type'];
				$size = null;
				$values = array();
				$tmp = explode('(', $type);
				if (isset($tmp[1])) {
					$type = $tmp[0];
					if (strpos($tmp[1], ' ') !== false && $type != 'enum') {
						$unsigned = true;
						$size = substr($tmp[1], 0, strpos($tmp[1], ')'));
					} else {
						$size = rtrim($tmp[1], ')');
					}
					if (strpos($size, ',') !== false) {
						$size = explode(',', $size);
					}
					if ($type == 'enum') {
						$values = $size;
						$size = '';
					}
				}
				$resultFields[$field['Field']] = array(
					ucfirst($type),
					array()
				);
				if ($size) {
					$resultFields[$field['Field']][1]['Size'] = $size;
				}
				if ($values) {
					$resultFields[$field['Field']][1]['Enum'] = $values;
				}
				if (
					strpos($field['Type'], 'text') === false &&
					(
						strpos($field['Type'], 'date') === false ||
						!empty($field['Default'])
					)
				) {
					if (
						strpos(strtolower($type), 'int') === false ||
						is_numeric($field['Default'])
					) {
						$resultFields[$field['Field']][1]['Default'] =
							$field['Default'];
					}

				}
				if (!empty($field['Comment']))
				{
					$resultFields[$field['Field']][1]['Comment'] = addslashes(
						$field['Comment']
					);
				}
				if ($unsigned) {
					$resultFields[$field['Field']][1][] = 'Unsigned';
				}
				if ($field['Null'] == 'YES') {
					$resultFields[$field['Field']][1][] = 'Not_Null';
				} else {
					$resultFields[$field['Field']][1][] = 'Null';
				}
				if ($field['Extra'] == 'auto_increment') {
					$resultFields[$field['Field']][1][] = 'Auto_Increment';
				}
			}
			foreach ($keys as $key) {
				$name = $key['Key_name'];
				if (!isset($resultKeys[$name])) {
					$resultKeys[$name] = $key;
				}
				if (!is_array($resultKeys[$name]['Column_name'])) {
					$resultKeys[$name]['Column_name'] = array();
				}
				$resultKeys[$name]['Column_name'][] = $key['Column_name'];
			}
			$keys = array_values($resultKeys);
			$resultKeys = array();
			foreach ($keys as $key) {
				$keyName = $key['Key_name'] != 'PRIMARY'
					? $key['Key_name'] : 'id';
				$keyName .= '_index';
				$type = $key['Non_unique'] ? 'Key' : 'Unique';
				$type = $key['Key_name'] == 'PRIMARY' ? 'Primary' : $type;
				$resultKeys[$keyName] = array(
					$type,
					$key['Column_name']
				);
			}
			if (!$comment && !empty($info['Comment'])) {
				$comment = $info['Comment'];
			}
			if (!empty($adminPanel)) {
				$adminPanel =  '\'admin_panel\' => ' .
					$helperConverter->arrayToString($adminPanel);
			}
            if (!empty($languageScheme)) {
				$languageScheme =  '\'languageScheme\' => ' .
					$helperConverter->arrayToString($languageScheme);
			}
            if (!empty($createScheme)) {
				$createScheme =  '\'createScheme\' => ' .
					$helperConverter->arrayToString($createScheme);
			}
			$output = $helperCodeGenerator->fromTemplate(
				'scheme',
				array(
					'author'            => $author,
					'comment'           => $comment,
					'fields'            => $resultFields,
					'indexes'           => $resultKeys,
					'references'        => $references,
					'admin_panel'       => $adminPanel,
                    'languageScheme'    => $languageScheme,
                    'createScheme'      => $createScheme
				)
			);
			echo 'File: ' . $filename . PHP_EOL;
			file_put_contents($filename, $output);
		}
	}

	public function missing()
	{
        $helperDataSource = $this->getService('helperDataSource');
        $modelSchemeService = $this->getService('modelScheme');
		$tables = $helperDataSource->tables();
		$exists_models = array();
		foreach ($tables as $table)
		{
			$exists_models[] = $modelSchemeService->tableToModel($table->Name);
		}
		$dir = IcEngine::root() . 'Ice/Config/Model/Mapper/';
		$exec = 'find ' . $dir . '*';
		ob_start();
		system ($exec);
		$content = ob_get_contents();
		ob_end_clean();
		if (!$content)
		{
			return;
		}
		$files = explode(PHP_EOL, $content);
		if (!$files)
		{
			return;
		}
		$configModels = array();
		foreach ($files as $file)
		{
			if (!is_file ($file))
			{
				continue;
			}
			$className = str_replace(
				IcEngine::root() . 'Ice/Config/Model/Mapper/',
				'',
				$file
			);
			$className = substr(str_replace('/', '_', $className), 0, -4);
			if ($className == 'Scheme')
			{
				continue;
			}
			$configModels[] = $className;
		}
		$result = array();
		foreach ($configModels as $model)
		{
			if (!in_array($model, $exists_models))
			{
				$result[] = $model;
			}
		}
		$this->output->send(array(
			'missings'	=> $result
		));
		print_r($result);
	}

	/**
	 * Создает схему модели
     *
	 * @param string $name
	 * @param string $comment
	 * @param string $author
	 */
	public function scheme($name, $comment, $author, $references,
        $fields, $indexes)
	{
        $helperCodeGenerator = $this->getService('helperCodeGenerator');
		$this->task->setTemplate(null);
		if (!$name) {
			echo 'Scheme must contains name.' . PHP_EOL;
			return;
		}
		$dirname = explode('_', $name);
		$filename = array_pop($dirname) . '.php';
        $dir = IcEngine::root() . 'Ice/Config/Model/Mapper/' .
            implode('/', $dirname) . '/';
		if (is_file($dir . $filename)) {
			return;
		}
		if (!is_dir($dir)) {
            mkdir ($dir, 0644, true);
        }
		$filename = $dir . $filename;
		echo 'File: ' . $filename . PHP_EOL . PHP_EOL;
        $task = $this->getService('controllerManager')->call(
            'Annotation_Orm', 'create', array(
                'className' => $name
            )
        );
        $buffer = $task->getTransaction()->buffer();
        if (isset($buffer['modelScheme'])) {
            $data = $buffer['modelScheme'];
            $fields = $data['fields'];
            $indexes = $data['indexes'];
            $references = $data['references'];
            $pos = strpos($data['comment'], 'Created at:');
            $comment = substr($data['comment'], 0, $pos);
        }
        if (!$fields) {
            $fields = array(
                'id'	=> array(
                    'Int',
                    array(
                        'Size'	=> 11,
                        'Not_Null',
                        'Auto_Increment'
                    )
                )
            );
        }
        if (!$indexes) {
            $indexes = array(
                'id'	=> array(
                    'Primary',
                    array('id')
                )
            );
        }
        $adminPanel = $helperCodeGenerator->fromTemplate(
            'schemeAdminPanel', array()
        );
        $output = $helperCodeGenerator->fromTemplate('scheme',
            array(
                'author'        => $author,
                'comment'       => $comment,
                'fields'        => $fields,
                'indexes'       => $indexes,
                'references'    => $references,
                'adminPanel'	=> $adminPanel
            )
        );
		echo $output;
		file_put_contents($filename, $output);
        $this->output->send(array(
            'success'   => true
        ));
	}

	/**
	 * Обновляет схему модели
	 */
	public function schemeUpdate($name, $comment, $author, $fields, $indexes,
        $adminPanel)
	{
		$this->_task->setTemplate (null);
        $configManager = $this->getService('configManager');
        $helperCodeGenerator = $this->getService('helperCodeGenerator');
		if (!$name)
		{
			echo 'Scheme must contains name.' . PHP_EOL;
			return;
		}
		$dir = IcEngine::root() . 'Ice/Config/Model/Mapper/';
		$dirname = explode('_', $name);
		$filename = array_pop($dirname) . '.php';
		var_dump($filename);
		$scheme = $configManager->get('Model_Mapper_' . $name);
		$currentDir = $dir;
		if ($dirname)
		{
			foreach ($dirname as $dir)
			{
				$currentDir .= $dir . '/';
				if (!is_dir($currentDir))
				{
					mkdir($currentDir);
				}
			}
		}
		$filename = $currentDir . $filename;
		echo 'File: ' . $filename . PHP_EOL . PHP_EOL;
		$fields = !$fields ? $scheme['fields'] : $fields;
		$indexes = !$indexes ? $scheme['indexes'] : $indexes;
		$adminPanel = !$adminPanel ? $scheme['admin_panel'] : $adminPanel;
		$adminPanel = "'admin_panel' => " . $adminPanel;
		$output = $helperCodeGenerator->fromTemplate('scheme',
				array(
					'author'	=> $author,
					'comment'	=> $comment,
					'fields'	=> $fields,
					'indexes'	=> $indexes,
					'adminPanel'	=> $adminPanel
				)
			);
		echo $output;
		file_put_contents($filename, $output);
	}

}