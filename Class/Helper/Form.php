<?php
/**
 *
 * @desc Обработка данных с формы.
 * @author Goorus
 * @package IcEngine
 *
 */
class Helper_Form
{

	/**
	 * @desc Игнорировать данные.
	 * @var string
	 */
	const IGNORE = 'ignore';

	/**
	 * @desc Поле будет прочитано из входных данных контроллера.
	 * @var string
	 */
	const INPUT = 'input';

	/**
	 * @desc Поле будет прочитано из входных данных контроллера, но после
	 * проверки удалено.
	 * @var string
	 */
	const INPUT_IGNORE = 'input,ignore';

	/**
	 * @desc Пример полей
	 * @var array
	 */
	private $_simpleFields = array (
		'email'		=> array (
			'type'	=> 'string',
			'minLength'	=> 5,
			'maxLength'	=> 40,
			'filters'		=> array (
				'Trim',
				'LowerCase'
			),
			'validators'	=> array (
				'Registration_Email'
			),
			'value'		=> 'input'
		),
		'password'	=> array (
			'type'	=> 'string',
			'minLength'	=> 6,
			'maxLength'	=> 250,
			'value'	=> 'input',
			'filters'		=> array (
				'Trim'
			),
			'validators'	=> array (
				'Registration_Password'
			)
		),
		'ip'	=> array (
			'maxTries'	=> 10,
			'value'		=> array ('Request', 'ip'),
			'validators'	=> array (
				'Registration_Ip_Limit'
			)
		),
		'date'	=> array (
			'value'	=> array (
				// При добавлении
				'add'	=> array ('Helper_Date', 'toUnix'),
				// При редактировании
				'edit'	=> 'ignore'
			)
		)
	);

	/**
	 * @desc Получение значения для поля.
	 * @param Objective $data Объект для получения данных.
	 * @param string $field Получаемое поле
	 * @param Data_Transport $input Транспорт - источник данных
	 * @param string|array $value Правило получения
	 * @param boolean $is_edit Это новая запись
	 * 		(может использоваться в правиле)
	 */
	protected static function _recieveValue (Objective $data, $field,
		Data_Transport $input, $value, $is_new = false)
	{
		if (is_array ($value))
		{
			if (count ($value) == 2 && isset ($value [0], $value [1]))
			{
                $service = IcEngine::getServiceLocator()->getService($value[0]);

                if ($service) {
                    $reflectionMethod = new ReflectionMethod(get_class($service), $value[1]);
                    $data->$field = $reflectionMethod->invoke($service);
                    fb($data->$field);
                }

			}
			elseif (isset ($value ['add']) && $is_new)
			{
				self::_recieveValue (
					$data, $field,
					$input,
					$value ['add'], $is_new
				);
			}
			elseif (isset ($value ['edit']) && !$is_new)
			{
				self::_recieveValue (
					$data, $field,
					$input,
					$value ['edit'], $is_new
				);
			}
			elseif (isset ($value ['set']) || array_key_exists ('set', $value))
			{
				$data->$field = $value ['set'];
			}
		}
		elseif ($value == self::INPUT || $value == self::INPUT_IGNORE)
		{
			$data->$field = $input->receive ($field);
		}
	}

	/**
	 * @desc Возвращает только атрибуты.
	 * @param Objective $data
	 * @param array $scheme
	 * @return array
	 */
	public static function extractAttributes (Objective $data, array $scheme)
	{
		$result = array ();
		foreach ($data as $field => $value)
		{
			if (
				isset ($scheme [$field]['@attribute']) &&
				$scheme [$field]['@attribute']
			)
			{
				$result [$field] = $value;
			}
		}
		return $result;
	}

	/**
	 * @desc Возвращает только поля.
	 * @param Objective $data
	 * @param array $scheme
	 * @return array
	 */
	public static function extractFields (Objective $data, array $scheme)
	{
		$result = array ();
		foreach ($data as $field => $value)
		{
			if (
				!isset ($scheme [$field]['@attribute']) ||
				!$scheme [$field]['@attribute']
			)
			{
				$result [$field] = $value;
			}
		}
		return $result;
	}

	/**
	 * @desc Разделяет атрибуты и поля.
	 * @param Objective $data
	 * @param array $scheme
	 * @return array
	 * 		$result ['fields'] array поля
	 * 		$result ['attributes'] array атрибуты
	 */
	public static function extractParts (Objective $data, array $scheme)
	{
		$attrs = array ();
		$fields = array ();

		foreach ($data as $field => $value)
		{
			if (
				isset ($scheme [$field]['@attribute']) &&
				$scheme [$field]['@attribute']
			)
			{
				$attrs [$field] = $value;
			}
			else
			{
				$fields [$field] = $value;
			}
		}

		return array (
			'fields'		=> $fields,
			'attributes'	=> $attrs
		);
	}

	/**
	 * @desc Фильтрация значений.
	 * @param Objective $data Данные для фильтрации.
	 * @param array|Objective $scheme
	 */
	public static function filter (Objective $data, $scheme)
	{
		$obj_scheme = (object) $scheme;

		foreach ($scheme as $field => $info)
		{
			if (isset ($info ['value']) && $info ['value'] == self::IGNORE)
			{
				continue ;
			}

			$filters = array ();

			if (isset ($info ['filters']))
			{
				if ($info ['filters'] instanceof Objective)
				{
					$filters = $info ['filters']->__toArray ();
				}
				else
				{
					$filters = (array) $info ['filters'];
				}
			}

			foreach ($filters as $filter)
			{
				$data->$field = IcEngine::getServiceLocator()->getService('filterManager')->filterEx (
					$filter, $field, $data, $obj_scheme
				);
			}
		}
	}

	/**
	 * @desc Чтение данных из инпута согласно правилам.
	 * @param Data_Transport $input Входной поток.
	 * @param array|Objective $fields Поля.
	 * @param Temp_Content $tc Временный контент
	 * @return Objective Прочитанные поля.
	 */
	public static function receiveFields (Data_Transport $input, $fields,
		Temp_Content $tc = null)
	{
		$data = new Objective ();

		foreach ($fields as $field => $info)
		{
			self::_recieveValue (
				$data, $field,
				$input,
				isset ($info ['value']) ? $info ['value'] : self::INPUT,
				!$tc || !$tc->rowId
			);
		}

		return $data;
	}

	/**
	 * @desc Удаление из выборки полей, отмеченных как игнорируемые.
	 * @param Objective $data
	 * @param array|Objective $scheme
	 */
	public static function unsetIngored (Objective $data, $scheme)
	{
		foreach ($data as $key => $value)
		{
			if (
				!isset ($scheme [$key]) ||
				$scheme [$key]['value'] == self::IGNORE ||
				$scheme [$key]['value'] == self::INPUT_IGNORE
			)
			{
				unset ($data [$key]);
			}
		}
	}

	/**
	 * @desc Проверка корректности данных с формы.
	 * @param Objective $data
	 * 		Данные, полученные с формы.
	 * @param array|Objective $scheme
	 * 		Данные по полям.
	 * @return true|array
	 * 		true если данные прошли валидацию полностью.
	 * 		Иначе - массив
	 * 		array ("Поле" => "Ошибка")
	 */
	public static function validate (Objective $data, $scheme)
	{
		$obj_scheme = (object) $scheme;

		foreach ($scheme as $field => $info)
		{
			if (isset ($info ['value']) && $info ['value'] == self::IGNORE)
			{
				continue ;
			}

			$validators = array ();

			if (isset ($info ['validators']))
			{
				$validators =
					$info ['validators'] instanceof Objective ?
					$info ['validators']->__toArray () :
					(array) $info ['validators'];
			}

			foreach ($validators as $validator)
			{
				$result = Data_Validator_Manager::validateEx (
					$validator, $field, $data, $obj_scheme
				);

				if ($result !== true)
				{
					return array ($field => $result);
				}
			}
		}

		return true;
	}

}