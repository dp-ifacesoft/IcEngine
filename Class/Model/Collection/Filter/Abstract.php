<?php
/**
 *
 * @desc Абстрактный класс фильтра коллекции.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Collection_Filter_Abstract
{

	/**
	 * @desc Название фильтра
	 * @var string
	 */
	protected $_name;

	/**
	 * @desc Поля, передаваемые в опцию
	 * @var array
	 */
	public $fields = array (
		// имя в опции		=> имя в транспорте
		// 'id'				=> 'city_id'
	);

	/**
	 * @desc Валидаторы полей для подключения фильтра
	 * @var array
	 */
	public $validators = array (
		// имя в опции		=> валидатор или массив валидаторов
		// 'id'				=> 'Not_Null'
	);

	/**
	 * @desc Создает и возвращает фильтр.
	 * Устанавливает название фильтра.
	 */
	public function __construct ()
	{
		$class = get_class ($this);
		$p = strpos ($class, '_Collection_Filter_');
		$this->_name = substr ($class, $p + 19);
	}

	/**
	 * @desc Добавление в коллекцию опций, определяемых фильтром.
	 * @param Model_Collection $collection
	 * @param array $params
	 */
	protected function _filter (Model_Collection $collection, array $params)
	{
		$params ['name'] = $this->name ();
		$collection->addOptions ($params);
	}

	/**
	 * @desc Проверка полей.
	 * @param array $params Поля.
	 * @return boolean true, если поля проходят валидацию, иначе false.
	 */
	protected function _validate (array $params)
	{
		foreach ($this->validators as $field => $validators)
		{
			$validators = (array) $validators;
			foreach ($validators as $validator)
			{
				$valid = Data_Validator_Manager::validate (
					$validator,
					$params [$field]
				);
				if (!$valid)
				{
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * @desc Наложение фильтра на коллекцию.
	 * @param Model_Collection $collection Коллекция.
	 * @param Data_Transport $data Данные для фильтрации.
	 */
	public function filter (Model_Collection $collection, Data_Transport $data)
	{
		if (!$this->fields)
		{
			$params = array ();
		}
		elseif (count ($this->fields) == 1)
		{
			reset ($this->fields);
			$params = array (
				key ($this->fields) =>
					$data->receive (current ($this->fields))
			);
		}
		else
		{
			$params = array_combine (
				array_keys ($this->fields),
				call_user_func_array (
					array ($data, 'receive'),
					array_values ($this->fields)
				)
			);
		}

		if ($this->_validate ($params))
		{
			$this->_filter ($collection, $params);
		}
	}

	/**
	 * @desc Возвращает название фильтра.
	 * @return string Название фильтра.
	 */
	public function name ()
	{
		return $this->_name;
	}

}