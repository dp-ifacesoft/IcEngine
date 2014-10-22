<?php
/**
 *
 * @desc Коллекция опций модели.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Collection
{

    /**
     * @desc Коллекция, к которой привязаны опции.
     * Необходима для определения названий классов опций.
     * @var Model_Collection
     */
	protected $_collection;

	/**
	 * @desc Опции
	 * @var array <Model_Option>
	 */
	protected $_items = array ();

	/**
	 * @desc Создает и возвращает коллекцию опций.
	 * @param Model_Collection $collection
	 */
	public function __construct ($collection)
	{
		$this->_collection = $collection;
	}

	/**
	 * @desc Добавление опции
	 * @param mixed $item
	 * @return Model_Option
	 */
	public function add ($item)
	{
		// Случай, если передано только название
		if (is_string ($item))
		{
			$item = array ('name' => $item);
		}

		if (is_array ($item))
	    {
			$class = Model_Option::getClassName (
				$item ['name'],
				$this->_collection
			);
			// Неизвестно, старая это или новая опция.
			if (Loader::tryLoad ($class))
			{
				// Это новая опция
				$item = new $class (
					$this->_collection,
					$item
				);
			}
			else
			{
				$item = Model_Option::create (
					'::Old',
					$this->_collection,
					$item
				);
			}

	    }

	    if ($item instanceof Model_Collection_Option_Abstract)
	    {
	        $item = new Model_Option_Old (
	        	$this->_collection,
	        	array (
					'option'	=> $item
				)
	        );
	    }

		if (!($item instanceof Model_Option))
		{
			throw new Zend_Exception ('Unsupported type: ' . gettype ($item));
		}

	    return $this->_items [] = $item;
	}

	/**
	 *
	 * @param Model_Collection $collection
	 * @param Query $query
	 * @rturn mixed
	 */
	public function executeAfter (Query_Abstract $query)
	{
		foreach ($this->_items as $option)
		{
			/* @var Model_Option $option */
			$option->query = $query;
			$option->after ();
		}
	}

	/**
	 *
	 * @param Model_Collection $collection
	 * @param Query $query
	 */
	public function executeBefore (Query_Abstract $query)
	{
		foreach ($this->_items as $option)
		{
			/* @var Model_Option $option */
			$option->query = $query;
			$option->before ();
		}
	}

	/**
	 * @return Model_Collection
	 */
	public function getCollection ()
	{
		return $this->_collection;
	}

	/**
	 * @return array
	 */
	public function getItems ()
	{
		return $this->_items;
	}

	/**
	 * @desc
	 * @param mixed $options
	 */
	public function setItems ($options)
	{
		$this->_items = array ();
		$options = (array) $options;
		for ($i = 0, $count = count ($options); $i < $count; ++$i)
		{
		    $this->add ($options [$i]);
		}
	}
}
