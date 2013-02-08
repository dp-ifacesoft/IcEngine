<?php

/**
 * Абстрактный класс опций модели.
 *
 * @author goorus, morph
 */
class Model_Option
{
	/**
	 * Коллекция, на которую наложен опшн
     *
	 * @var Model_Collection
	 */
	public $collection;

	/**
	 * Название опции
     *
	 * @var string
	 */
	public $name;

	/**
	 * Опции
     *
	 * @var array
	 */
	public $params;

	/**
	 * Имя части запроса
	 *
	 * @var string
	 */
	protected $queryName;

	/**
	 * Запрос, выполняемый коллекцией
     *
	 * Переменная $query отличается от запроса, возвращаемого методом
	 * <i>$colleciton->query()</i>. По умолчанию эта переменная - клон
	 * изначального запроса коллекции, на который наложены опции.
	 * @var Query
	 */
	public $query;

	/**
	 * Создает и возвращает опцию
	 */
	public function __construct(Model_Collection $collection, $params)
	{
		$class = get_class($this);
		$delim = '_Model_Option_';
		$pos = strrpos($class, $delim);
		$this->name = substr($class, $pos + strlen ($delim));
		$this->collection = $collection;
		$this->params = $params;
	}

	/**
	 * Вызывается после выполения запроса.
	 */
	public function after()
	{

	}

	/**
	 * Вызывается перед выполнением запроса.
	 */
	public function before()
	{
		if ($this->queryName) {
			$className = 'Query_Part_' . $this->queryName;
			$modelName = $this->collection->modelName();
			$queryPart = new $className($modelName, $this->params);
			$queryPart->inject($this->query);
		}
	}
    
    /**
     * Удовлетворяет ли опция условиям применения
     * 
     * @return boolean
     */
    public function isSatisfiedBy()
    {
        return true;
    }
}