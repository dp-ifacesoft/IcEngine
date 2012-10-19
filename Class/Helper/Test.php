<?php

class Helper_Test
{
	protected static $_config = array (
		'dir'	=> 'Ice/Test/Ice/Scope/'
	);

	/**
	 * @desc Получить конфиг хелпера
	 * @return Objective
	 */
	public static function config ()
	{
		if (!self::$_config)
		{
			self::$_config = Config_Manager::get (
				__CLASS__, self::$_config
			);
		}
		return self::$_config;
	}

	/**
	 * @desc Получить данные ситуации
	 * @param array $method
	 * @return array
	 */
	public static function getData ($method)
	{
		$config = self::config ();
		list ($class_name, $method) = explode ('::', $method);
		$class_name = substr ($class_name, 0, -4);
		$file_name = IcEngine::root () . rtrim ($config ['dir'], '/') . '/' .
			$class_name. '/' .$method . '.php';
		$suite = new Config_Php ($file_name);
		return $suite->__toArray ();
	}

	/**
	 * @desc Тестируем контроллер
	 */
	protected static function _testController ($model_name, array $situation)
	{
		$input = $situation ['input'];
		$output = $situation ['output'];
		$result = Controller_Manager::htmlUncached (
			$controller, $action,
			$input, array ('with_buffer' => true)
		);
		$tests = array ();
		$tests [] = array (!empty ($result ['buffer']), null, 'assertEqual');
		$buffer = $result ['buffer'];
		foreach ($buffer as $var => $value)
		{
			if (!isset ($output [$var]))
			{
				continue;
			}
			$o = $output [$var];
			$fields = array ();
			if (is_array (reset ($o)))
			{
				$fields = array_keys (reset ($o));
			}
			elseif (is_array ($o))
			{
				$fields = array_keys ($o);
			}
			$v = function ($v)
			{
				return $v;
			};
			$sit = array (
				'input'		=> $v,
				'input_arg'	=> $value,
				'output'	=> array (
					'fields'	=> $fields,
					'value'		=> $o
				)
			);
			if ($value instanceof Model)
			{
				$tests = array_merge (
					$tests,
					self::_testModel (
						$value->modelName (),
						$test,
						$sit
					)
				);
			}
			elseif ($value instanceof Model_Collection)
			{
				$tests = array_merge (
					$tests,
					self::_testCollection (
						$value->modelName (),
						$test,
						$sit
					)
				);
			}
			else
			{
				$tests = array_merge (
					$tests,
					self::_testScalar (
						$model_name,
						$test,
						$sit
					)
				);
			}
		}
		return $tests;
	}

	/**
	 * @desc Тестируем на скаляр
	 */
	protected static function _testScalar ($model_name, array $situation)
	{
		$params = array ();
		$name = $situation ['input'];
		if (is_array ($name))
		{
			$params = reset ($name);
			$name = reset (array_values ($name));
		}
		if (!is_callable ($name))
		{
			$callee_class = $model_name;
			$callee_method = $name;
			if (strpos ($name, '::') !== false)
			{
				list ($callee_class, $callee_method) = explode ('::', $name);
				if (!$callee_class)
				{
					$callee_class = $model_name;
				}
			}
			$value = call_user_func_array (
				array ($callee_class, $callee_method),
				$params
			);
		}
		else
		{
			$value = $name ($situation ['input_arg']);
		}
		$output = $situation ['output']['value'];
		$tests = array ();
		$tests [] = array ($value, $output);
		return $tests;
	}

	/**
	 * @desc Тестируем коллекцию
	 */
	protected static function _testCollection ($model_name, array $situation)
	{
		$input = $situation ['input'];
		$tests = array ();
		if (!is_array ($input))
		{
			$input = array ($input);
		}
		$collection = null;
		if (count ($input) == 1)
		{
			$name = $input;
			$params = array ();
			if (is_array (reset ($input)))
			{
				$params = reset ($input);
				$name = reset (array_keys ($input));
			}
			if (strpos ($name, '::') !== false)
			{
				list ($callee_class, $callee_method) = explode ('::', $name);
				if (!$callee_class)
				{
					$callee_class = $model_name;
				}
				$collection = call_user_func_array (
					array ($callee_class, $callee_method),
					$params
				);
			}
			elseif (is_callable ($name))
			{
				$model = $name ($situation ['input_arg']);
			}
		}
		else
		{
			$collection = Model_Collection_Manager::create ($model_name);
			foreach ($input as $option_name => $params)
			{
				if (is_numeric ($option_name))
				{
					$option_name = $params;
					$params = array ();
				}
				$collection->addOptions (
					array_merge (
						array ('name'	=> $option_name),
						$params
					)
				);
			}
		}
		$fields = $situation ['output']['fields'];
		$rows = $situation ['output']['value'];
		$output = Model_Collection_Manager::create ($model_name)->reset ();
		foreach ($rows as $model)
		{
			$model = Model_Manager::create (
				$model_name,
				$model
			);
			$output->add ($model);
		}
		$tests [] = array (
			$collection->count (),
			$output->count (),
			'assertEqual'
		);
		foreach ($collection as $i => $ci)
		{
			$co = $output->item ($i);
			foreach ($fields as $f)
			{
				$tests [] = array ($ci->sfield ($f), $co->sfield ($f));
			}
		}
		return $tests;
	}

	/**
	 * @desc Тестируем модель
	 */
	protected static function _testModel ($model_name, array $situation)
	{
		$input = $situation ['input'];
		$tests = array ();
		if (!is_array ($input))
		{
			$input = array ($input);
		}
		$model = null;
		if (count ($input) == 1)
		{
			$name = $input;
			$params = array ();
			if (is_array (reset ($name)))
			{
				$params = reset ($input);
				$name = reset (array_keys ($input));
			}
			if (is_array ($name))
			{
				$name = reset ($name);
			}
			if (strpos ($name, '::') !== false)
			{
				list ($callee_class, $callee_method) = explode ('::', $name);
				if (!$callee_class)
				{
					$callee_class = $model_name;
				}
				$model = call_user_func_array (
					array ($callee_class, $callee_method),
					$params
				);
			}
			elseif (is_callable ($name))
			{
				$model = $name ($situation ['input_arg']);
			}
			else
			{
				$model = Model_Manager::byOption (
					$model_name,
					array_merge (
						array ('name'	=> $name),
						$params
					)
				);
			}
		}
		else
		{
			$collection = Model_Collection_Manager::create ($model_name);
			foreach ($input as $option_name => $params)
			{
				if (is_numeric ($option_name))
				{
					$option_name = $params;
					$params = array ();
				}
				$collection->addOptions (
					array_merge (
						array ('name'	=> $option_name),
						$params
					)
				);
			}
			$model = $collection->first ();
		}
		$fields = $situation ['output']['fields'];
		$row = $situation ['output']['value'];
		$output = Model_Manager::create (
			$model_name,
			$row
		);
		foreach ($fields as $f)
		{
			$tests [] = array (
				$model->sfield ($f),
				$output->sfield ($f)
			);
		}
		return $tests;
	}

	/**
	 * @desc Цикл тестирования
	 * @param PHPUnit_Framework_TestCase $test текущий тест
	 * @param string $method тестовая ситуация
	 */
	public static function test (PHPUnit_Framework_TestCase $test, $method)
	{
		DDS::setDataSource (Data_Source_Manager::get ('UnitTest'));
		self::prepareBehavior ($method);
		$data = self::getData ($method);
		$situations = $data ['situations'];
		foreach ($situations as $sit)
		{
			self::createModels ($method);
			$method = !empty ($sit->method) ? $sit->method : 'assertEquals';
			$model_name = !empty ($sit ['model_name']) ? $sit ['model_name'] : null;
			$type = $sit ['type'];
			$method_name = '_test' . ucfirst ($type);
			$tests = self::$method_name ($model_name, $sit);
			if ($tests)
			{
				foreach ($tests as $data)
				{
					$call_method = $method;
					if (isset ($data [2]))
					{
						$call_method = $data [2];
						unset ($data [2]);
					}
					call_user_func_array (
						array ($test, $call_method), $data
					);
				}
			}
		}

		DDS::setDataSource (Data_Source_Manager::get ('default'));
	}

	/**
	 * @desc Создание моделей для теста
	 * @param string $method тестовая ситуация
	 */
	public static function createModels ($method)
	{
		$data = self::getData ($method);

		if (!empty ($data ['generator']))
		{
			list ($generator_class, $generator_method) =
				explode ('::', $data ['generator']);
			call_user_func (array (
				$generator_class, $generator_method
			));
		}

		if (!empty ($data ['models']))
		{
			foreach ($data ['models'] as $model_name => $suite)
			{
				$query = Query::instance ()->truncateTable ($model_name);
				DDS::execute ($query);
				foreach ($suite as $model)
				{
					Model_Manager::create (
						$model_name,
						$model
					)->save (true);
				}
			}
		}
	}

	/**
	 * @desc Подготовка базы данных для теста
	 */
	public static function prepareBehavior ($method)
	{
		$data = self::getData ($method);
		if (empty ($data ['models']))
		{
			return;
		}
		$models = $data ['models'];
		foreach (array_keys ($models) as $model_name)
		{
			echo $model_name . PHP_EOL;
			$table = Model_Scheme::table ($model_name);
			echo $table . PHP_EOL;
			$info = Helper_Data_Source::table ($table);
			if ($info)
			{
				$query = Query::instance ()->dropTable ($model_name);
				echo $query->translate () . PHP_EOL;
				DDS::execute ($query);
			}
			Controller_Manager::call (
				'Model', 'create',
				array (
					'name'	=> $model_name
				)
			);
		}
	}
}