<?php
/**
 *
 * @desc Проксирующая модель.
 * Используется в случаях, когда невозможно использовать класс самой модели.
 * @author Юрий
 * @package IcEngine
 *
 */
class Model_Proxy extends Model
{

	/**
	 * @desc Представляемая модель.
	 * @var string
	 */
	protected $_modelName;

    /**
	 * Изменяет значение поля
     *
	 * @param string $field Поле
	 * @param mixed $value Значение
	 */
	public function __set($field, $value)
	{
        $fields = $this->fields;
		if (isset($fields[$field])) {
			$this->fields[$field] = $value;
		} else {
			throw new Exception ('Field unexists "' . $field . '".');
		}
	}

	/**
	 *
	 * @param string $modelName
	 * @param array $fields
	 * @param boolean $autojoin
	 */
	public function __construct ($modelName, array $fields = array ())
	{
		$this->_modelName = $modelName;
		parent::__construct ($fields);
	}



	public function load ($key = null)
	{
	    return $this;
	}

	public function modelName ()
	{
		return $this->_modelName;
	}

	public function save ($hard_insert = false)
	{
	    return $this;
	}

}