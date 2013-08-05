<?php

/**
 * Проксирующая модель. Используется в случаях, когда невозможно использовать
 * класс самой модели.
 *
 * @author goorus, morph
 */
class Model_Proxy extends Model
{

	/**
	 * Представляемая модель.
	 *
     * @var string
	 */
	protected $modelName;

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
			throw new Exception('Field unexists "' . $field . '".');
		}
	}

	/**
     * Конструктор
     *
	 * @param string $modelName
	 * @param array $fields
	 * @param boolean $autojoin
	 */
	public function __construct ($modelName, array $fields = array ())
	{
		$this->modelName = $modelName;
		parent::__construct ($fields);
	}

    /**
     * @inheritdoc
     */
    public function delete()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
	public function load($key = null)
	{
	    return $this;
	}

    /**
     * @inheritdoc
     */
	public function modelName()
	{
		return $this->modelName;
	}

    /**
     * @inheritdoc
     */
	public function save($hardInsert = false)
	{
	    return $this;
	}

    public function scheme()
    {
        return $this->getService('modelScheme')->scheme($this->modelName);
    }

    /**
     * @inheritdoc
     */
    public function update(array $data, $hardUpdate = false)
    {
        return $this;
    }
}