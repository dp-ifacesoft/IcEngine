<?php
/**
 *
 * @desc Базовый класс коллекции моделей
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Model_Collection implements ArrayAccess, IteratorAggregate, Countable
{
	/**
	 * @desc Клонировать дату
	 * @var integer
	 */
	const ASSIGN_DATA 		= 'Data';

	/**
	 * @desc Клонировать фильтры
	 * @var integer
	 */
	const ASSIGN_FILTERS 	= 'Filters';

	/**
	 * @desc Клонировать модели
	 * @var integer
	 */
	const ASSIGN_MODELS 	= 'Models';

	/**
	 * @desc Клонировать опшинсы
	 * @var integer
	 */
	const ASSIGN_OPTIONS 	= 'Options';

	/**
	 * @desc Клонировать пагинатор
	 * @var integer
	 */
	const ASSIGN_PAGINATOR 	= 'Paginator';

	/**
	 * @desc Клонировать запрос
	 * @var integer
	 */
	const ASSIGN_QUERY 		= 'Query';

	/**
	 * @desc Добавленные элементы
	 * @var string
	 */
	const DIFF_EDIT_ADD		= 'added';

	/**
	 * @desc Неизмененные элементы
	 * @var string
	 */
	const DIFF_EDIT_NO		= 'not_changed';

	/**
	 * @desc Удаленные элементы
	 * @var string
	 */
	const DIFF_EDIT_DEL		= 'removed';

	/**
	 * @desc Для создаваемых моделей включен autojoin.
	 * @var boolean
	 */
	protected $_autojoin	= true;

	/**
	 * @desc Связанные данные
	 * @var array
	 */
	protected $_data		= array ();

	/**
	 * @desc Элементы коллекции
	 * @var array
	 */
	protected $_items;

	/**
	 * @desc Опции
	 * @var Model_Collection_Option_Collection
	 */
	protected $_options;

	/**
	 * @desc Текущий паджинатор
	 * @var Paginator
	 */
	protected $_paginator;

	/**
	 * @desc Последний выполненный запрос
	 * @var Query_Abstract
	 */
	protected $_lastQuery;

	/**
	 * @desc Текущий запрос
	 * @var Query_Abstract
	 */
	protected $_query;

	/**
	 * @desc Результат последнего выполненного запроса
	 * @var Query_Result
	 */
	protected $_queryResult;

	/**
	 * Итератор коллекции
	 *
	 * @var Model_Collection_Iterator
	 */
	protected $iterator;

	/**
	 * @desc Создает и возвращает коллекцию моделей.
	 * Так же подключает связанный класс модели.
	 */
	public function __construct ()
	{
		$this->_options = new Model_Option_Collection ($this);
	}

	/**
	 * @desc Преобразование коллекции к массиву
	 * @return array
	 */
	public function __toArray ()
	{
		$result = array (
			'class'	=> get_class ($this),
			'items'	=> array (),
			'data'	=> $this->_data
		);
		foreach ($this as $item)
		{
			$result ['items'][] = $item->__toArray ();
		}
		return $result;
	}

	/**
	 *
	 * @desc Добавить модель в коллекцию
	 * @param Model|Model_Collection|array $item
	 * @return Model_Collection
	 * @throws Zend_Exception
	 */
	public function add ($item)
	{
		if ($item instanceof Model)
		{
			$this->_items [] = $item;
		}
		elseif ($item instanceof Model_Collection)
		{
			foreach ($item as $model)
			{
				$this->_items [] = $model;
			}
		}
		elseif (is_array ($item))
		{
			$key_field = $this->keyField ();
			$keys = array_keys ($item);
			if (isset ($item [$key_field]))
			{
				// Ести ключевое поле
				$item = Model_Manager::get (
					$this->modelName (),
					$item [$key_field],
					$item
				);
			}
			elseif (is_numeric ($keys [0]))
			{
				foreach ($item as $data)
				{
					$this->add ($data);
				}
			}
			else
			{
				// Ключевое поле не задано
				$class = $this->modelName ();
				$item = new $class ($item);
			}

			$this->_items [] = $item;
		}
		else
		{
			//var_dump ($item);
			throw new Model_Exception ('Model add error');
		}
		return $this;
	}

	/**
	 * @desc Добавление одного или нескольких фильтров.
	 * @param Data_Transport $data Транспорт входных данных контроллера.
	 * @param string $filter Название фильтра.
	 * @return Model_Collection
	 */
	public function addFilters (Data_Transport $data, $filter)
	{
		$arg_count = func_num_args ();
		for ($i = 1; $i < $arg_count; ++$i)
		{
			$filter = func_get_arg ($i);
			$p = strpos ($filter, '::');
			$filter = $p
				?
					substr ($filter, 0, $p) .
					'_Collection_Filter_' .
					substr ($filter, $p + 2)
				:
					$this->modelName () .
					'_Collection_Filter_' .
					$filter;

			Model_Collection_Filter_Manager::get ($filter)
				->filter ($this, $data);
		}
		return $this;
	}

	/**
	 * @desc Добавление нескольких опций к коллекции аналогично.
	 * @param array|string $options
	 * @param $_
	 * @return Model_Collection Эта коллекция
	 */
	public function addOptions ($options)
	{
		if (
			(is_array ($options) && !empty ($options ['name'])) ||
			!is_array ($options)
		)
		{
			$options = func_get_args ();
		}

		foreach ($options as $option)
		{
			$this->_options->add ($option);
		}

		return $this;
	}

	/**
	 * @desc Клонировать модель
	 * @param Model_Collection $source
	 * @param array $flags
	 * @return Model_Collection
	 */
	public function assign (Model_Collection $source, array $flags = array ())
	{
		if (!$flags)
		{
			$flags = array (
				self::ASSIGN_DATA,
				self::ASSIGN_FILTERS,
				self::ASSIGN_MODELS,
				self::ASSIGN_OPTIONS,
				self::ASSIGN_PAGINATOR,
				self::ASSIGN_QUERY
			);
		}

		for ($i = 0, $icount = sizeof ($flags); $i < $icount; $i++)
		{
			$method_name = 'assign' . $flags [$i];

			if (is_callable (array ($this, $method_name)))
			{
				$this->$method_name ($source);
			}
		}

		return $this;
	}

	/**
	 * @desc Клонировать дату коллекции
	 * @param Model_Collection $source
	 */
	public function assignData (Model_Collection $source)
	{
		$this->data ($source->data ());
	}

	/**
	 * @desc Клонировать фильтры
	 * @param Model_Collection $source
	 */
	public function assignFilters (Model_Collection $source)
	{
		// TODO
	}

	/**
	 * @desc Клонировать модели
	 * @param Model_Collection $source
	 */
	public function assignModels (Model_Collection $source)
	{
		$this->setItems ($source->items ());
	}

	/**
	 * @desc Клонировать опшины
	 * @param Model_Collection $source
	 */
	public function assignOptions (Model_Collection $source)
	{
		$this
			->getOptions ()
			->setItems (
				$source
					->getOptions ()
					->getItems ()
			);
	}

	/**
	 * @desc Клонировать пагинатор
	 * @param Model_Collection $source
	 */
	public function assignPaginator (Model_Collection $source)
	{
		$paginator = $source->getPaginator ();
		if ($paginator)
		{
			$this
				->setPaginator (
					clone $paginator
				);
		}
	}

	/**
	 * @desc Клонировать запрос
	 * @param Model_Collection $source
	 */
	public function assignQuery (Model_Collection $source)
	{
		$query = $source->query ();
		$this
			->setQuery (
				clone $query
			);
	}

    /**
     * Действия до загрузки коллекции
     */
    public function beforeLoad()
    {
        $keyField = $this->keyField();
		$query = clone $this->query();
        $args = func_get_args();
		$modelName = $this->table();
        if (!$args || (count($args) == 1 && empty($args[0]))) {
			$query->select($modelName . '.*');
            $query->select(array($modelName => $keyField));
		} else {
            foreach ($args as $arg) {
                $query->select($arg);
            }
            if (!in_array($keyField, $args)) {
                $query->select(array($modelName => $keyField));
            }
		}
		$query->from($modelName);
		if ($this->_paginator) {
			$query->calcFoundRows();
			$query->limit(
				$this->_paginator->pageLimit,
				$this->_paginator->offset()
            );
		}
		$schemeOptions = Model_Scheme::modelOptions($modelName);
		if ($schemeOptions) {
			$this->addOptions($schemeOptions);
		}
		$this->_options->executeBefore($query);
		$this->_lastQuery = $query;
    }

	/**
	 * @desc Имя базового класса (без суффикса "_Collection")
	 * @return string
	 */
	public function className ()
	{
		return substr (get_class ($this), 0, -11);
	}

	/**
	 * @desc Получить значение поля для всех моделей коллеции
	 * @param string|array $name
	 * @return array
	 */
	public function column ($name)
	{
		$columns = (array) $name;
		$result = array();
		$columnCount = count($columns);
		foreach ($this->items() as $i => $item) {
			foreach ($columns as $column) {
				if ($columnCount > 1) {
					$result[$i][$column] = $item[$column];
				} else {
                    $result[$i] = $item[$column];
				}
			}
		}
		return $result;
	}

	/**
	 * @desc Количество моделей коллеции
	 * @return integer
	 */
	public function count ()
	{
		return count ($this->items ());
	}

	/**
	 * Получить текущий итератор коллекции
	 *
	 * @return Model_Collection_Iterator
	 */
	public function currentIterator()
	{
		return $this->iterator;
	}

	/**
	 * @desc Устанавливает или получает связанные данные объекта
	 * @param string $key [optional] Ключ
	 * @param mixed $value [optional]
	 * 		Значение (не обязательно)
	 * @return mixed
	 * 		Текущее значение
	 */
	public function data ($key = null)
	{
		if (func_num_args () == 0 || $key === null)
		{
			return $this->_data;
		}

		if (func_num_args () == 1)
		{
			if (is_array ($key))
			{
				$this->_data = array_merge (
					$this->_data,
					$key
				);
				return;
			}

			return isset ($this->_data [$key]) ? $this->_data [$key] : null;
		}

		$this->_data [$key] = func_get_arg (1);
	}

	/**
	 * @desc Удаление всех объектов коллекции
	 */
	public function delete ()
	{
		$items = &$this->items ();
		foreach ($items as $item)
		{
			$item->delete ();
		}
		$this->_items = array ();
	}

	/**
	 * @desc Получить различные элементы двух коллекцийю
	 * @param Model_Collection $collection
	 * @return Model_Collection
	 */
	public function diff (Model_Collection $collection)
	{
		$model_name = $this->modelName ();
		$kf_this = Model_Scheme::keyField ($model_name);
		$kf_collection = Model_Scheme::keyField ($collection->modelName ());
		$array_this = $this->column ($kf_this);
		$array_collection = $collection->column ($kf_collection);
		$diff = array_diff ($array_this, $array_collection);

		$result = Model_Collection_Manager::create ($model_name)
			->reset ();

		for ($i = 0, $icount = sizeof ($diff); $i < $icount; $i++)
		{
			$result->add (Model_Manager::byKey (
				$model_name,
				$diff [$i]
			));
		}

		return $result;
	}

	/**
	 * @desc Исключает из коллекции элемент с указанным индексом.
	 * @param integer $index Индекс элемента в списке.
	 * @return Model_Collection
	 */
	public function exclude ($index)
	{
		if (is_array ($this->_items) && isset ($this->_items [$index]))
		{
			unset ($this->_items [$index]);
		}
		return $this;
	}

	/**
	 * Фильтрация. Возвращает экземпляр новой коллекции
     *
	 * @param array $fields
	 * @return Model_Collection
	 */
	public function filter($fields)
	{
        $modelName = $this->modelName();
        $keyField = Model_Scheme::keyField($modelName);
		$collection = Model_Collection_Manager::create($modelName)
			->reset();
        $args = func_get_args();
        if (!is_array($fields) && isset($args[0]) && isset($args[1])) {
            $fields = array(
                $args[0]    => $args[1]
            );
        }
        $result = Helper_Array::filter($this->items(), $fields);
		if ($result) {
            $ids = Helper_Array::column($result, $keyField);
            foreach ($ids as $id) {
                foreach ($this->_items as $model) {
                    if ($id != $model->key()) {
                        continue;
                    }
                    $collection->add($model);
                }
            }
        }
        return $collection;
	}

	/**
	 * @desc Возвращает первый элемент коллекции.
	 * @return Model
	 */
	public function first ()
	{
		if (!is_array ($this->_items))
		{
			$this->load();
		}
		if (empty ($this->_items))
		{
			return null;
		}
		return reset ($this->_items);
	}

	/**
	 * @desc Создать коллекцию из массива с данными моделей.
	 * @param array $rows Массив моделей.
	 * @param boolean $clear Очистить коллекцию перед добавлением.
	 * @return Model_Collection
	 */
	public function fromArray (array $rows, $clear = true)
	{
		$model = $this->modelName ();
		if ($clear)
		{
			$this->_items = array ();
		}

		$kf = $this->keyField ();
		foreach ($rows as $row)
		{
			$key = isset ($row ['id']) ? $row ['id'] : $row [$kf];
			$this->_items [] = Model_Manager::get ($model, $key, $row);
		}
		return $this;
	}

	/**
	 * @desc Создать коллекцию из запроса
	 * @param Query_Abstract $query
	 * @param boolean $clear Очистить коллекцию, перед добавлением
	 * @return Model_Collection
	 */
	public function fromQuery (Query_Abstract $query, $clear = true)
	{
		$rows = DDS::execute ($query)->getResult ()->asTable ();
		return $this->fromArray ((array) $rows, $clear);
	}

	/**
	 * @desc Включен ли автоджоин
	 * @return boolean
	 */
	public function getAutojoin ()
	{
		return $this->_autojoin;
	}

	/**
	 *
	 * @desc Получить коллекцию опшинов
	 * @return Model_Collection_Option_Collection
	 */
	public function getOptions ()
	{
		return $this->_options;
	}

	/**
	 * @see items
	 */
	public function getItems ()
	{
		return $this->_items;
	}

	/**
	 * (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator ()
	{
		$this->items ();
		return new ArrayIterator ($this->_items);
	}

	/**
	 * @desc Вернуть текущий пагинатор
	 * @return Paginator
	 */
	public function getPaginator ()
	{
		return $this->_paginator;
	}

	/**
	 *
	 * @desc Ищет в коллекции эквивалентную заданой модель, и,
	 * если находит, то возвращает ее
	 * @param Model $item
	 * @return null|Model
	 */
	public function has (Model $item)
	{
		foreach ($this as $i)
		{
			if ($i === $item)
			{
				return $i;
			}
		}
	}

    /**
     *
     * @desc Ищет в коллекции эквивалентную по полям (если $fields пустой массив - по совпадению
     *  первичных ключей) заданой модель, и, если находит, то возвращает ее (из коллекции в которой ищется)
     * @param Model $item
     * @param array $fields
     * @return null|Model
     */
    public function hasByFields (Model $item, $fields = array())
    {
		$model = null;

		foreach ($this as $i)
		{
			if (empty ($fields))
			{
				if (
					$i->modelName () == $item->modelName () &&
					$i->key () == $item->key ()
				)
				{
					$model = $i;
				}
			}
			else
			{
				$model = $i;

				foreach ($fields as $field)
				{
					if ($i->sfield ($field) != $item->sfield ($field))
					{
						$model = null;
						break;
					}
				}
			}
			if ($model)
			{
				break;
			}
		}
		return $model;
    }

	/**
	 * @desc Возвращает модель из коллекции
	 * @param integer $index Индекс
	 * @return Model|null
	 */
	public function item ($index)
	{
		if (!is_array ($this->_items))
		{
			$this->load ();
		}

		if ($index < 0)
		{
			$index += count ($this->_items);
		}

		return isset ($this->_items [$index]) ? $this->_items [$index] : null;
	}

	/**
	 * @desc Получить элементы модели
	 * @return array <Model>
	 */
	public function &items ()
	{
		if (!is_array ($this->_items))
		{
			$this->load ();
		}
		return $this->_items;
	}

	/**
	 * Получить итератор коллекции
	 *
	 * @return Model_Collection_Iterator
	 */
	public function iterator($isFactory = false)
	{
		Loader::load('Model_Collection_Iterator');
		if (!$this->iterator) {
			$this->iterator = new Model_Collection_Iterator($this, $isFactory);
		}
		if (!is_array($this->_items))
		{
			$this->load();
		}
		return $this->iterator;
	}

	/**
	 * @desc Пустая ли коллекция
	 * @return boolean
	 */
	public function isEmpty ()
	{
		if (!is_array ($this->_items))
		{
			$this->load ();
		}
		return empty ($this->_items);
	}

	/**
	 * @desc Проверяет, чтобы модель была приджойнен хотя бы к одному элементу
	 * коллекции.
	 * @param Model $model Модель.
	 * @return boolean
	 */
	public function isJoinedSome (Model $model)
	{
		foreach ($this as $item)
		{
			if (Helper_Link::wereLinked ($item, $model))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @desc Проверяет, чтобы модель была приджойнена ко всем элеметам коллекции.
	 * @param Model $model Модель.
	 * @return boolean
	 */
	public function isJoinedAll (Model $model)
	{
		foreach ($this as $item)
		{
			if (!Helper_Link::wereLinked ($item, $model))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @desc Приджойнить модель ко всем элементам коллекции
	 * @param Model $model
	 * @return Model_Collection
	 */
	public function join (Model $model)
	{
		foreach ($this as $item)
		{
			Helper_Link::link ($item, $model);
		}
		return $this;
	}

	/**
	 * @desc Имя ключевого поля.
	 * @return string
	 */
	public function keyField ()
	{
		return Model_Scheme::keyField ($this->modelName ());
	}

	/**
	 * @desc Получить последнюю модель коллекции.
	 * @return Model
	 */
	public function last ()
	{
		if (!is_array ($this->_items))
		{
			$this->load ();
		}
		if (empty ($this->_items))
		{
			return null;
		}
		return end ($this->_items);
	}

	/**
	 * @desc Последний выполенный запрос коллекции.
	 * Если запрос еще не сформирован, запрос будет сформирован и коллекция
	 * будет загружена.
	 * @return Query Зарос коллекции.
	 */
	public function lastQuery ()
	{
		if (!$this->_lastQuery)
		{
			$this->load ();
		}
		return $this->_lastQuery;
	}

	/**
	 * @desc Загрузка данных
	 * @return Model_Collection
	 */
	public function load ($columns = array ())
	{
		$this->beforeLoad($columns);
        $query = $this->_lastQuery;
		Model_Collection_Manager::load ($this, $query);
		$this->_options->executeAfter ($query);

		if ($this->_paginator)
		{
			$this->_paginator->fullCount = $this->_data ['foundRows'];
		}

		return $this;
	}

	/**
	 * @desc Для каждого объекта коллекции будет вызвана функция $function
	 * и результат выполнения записан в данные объекта под именем $data
	 *
	 * @param function $function
	 * @param string $data
	 */
	public function mapToData ($function, $data)
	{
		$items = &$this->items();
		foreach ($items as $item)
		{
			$item->data ($data, call_user_func ($function, $item));
		}
	}

	/**
	 * @desc Название модели (без суффикса "_Collection")
	 * @return string
	 */
	public function modelName ()
	{
		return substr (get_class ($this), 0, -11);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet ($offset, $value)
	{
		if (is_null ($offset))
		{
			$this->_items [] = $value;
		}
		else
		{
			$this->_items [$offset] = $value;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists ($offset)
	{
		return isset ($this->_items [$offset]);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset ($offset)
	{
		unset ($this->_items [$offset]);
	}

	/**
	 * @param offset
	 * @return Model
	 */
	public function offsetGet ($offset)
	{
		return $this->item ($offset);
	}

	/**
	 * @desc Возвращает текущий запрос.
	 * @return Query_Abstract
	 */
	public function query ()
	{
		if (!$this->_query)
		{
			$this->_query = Query::factory ('Select');
		}
		return $this->_query;
	}

	/**
	 * @desc Получить результат запроса коллекции
	 * @return Query_Result
	 */
	public function queryResult ($result = null)
	{
		if ($result)
		{
			$this->_queryResult = $result;
		}
		else
		{
			if (!$this->_queryResult)
			{
				$this->load ();
			}
			return $this->_queryResult;
		}
	}

    /**
     * Получить чистые данные
     *
     * @param array $columns
     * @return array
     */
    public function raw($columns = array())
    {
        $result = array();
        if ($this->_items) {
            if (is_array($this->_items[0])) {
                $result = Helper_Array::column($this->_items, $columns);
            } else {
                $fullResult = array();
                foreach ($this->_items as $item) {
                    $fullResult[] = $item->asRow();
                }
                $result = Helper_Array::column($fullResult, $columns);
            }
        } else {
            if ($columns) {
                call_user_func_array(array($this, 'beforeLoad'), $columns);
            } else {
                $this->beforeLoad(array());
            }
            $pack = Model_Collection_Manager::callDelegee(
                $this, $this->_lastQuery
            );
            if ($pack) {
                $this->_items = $pack['items'];
            }
            $this->_options->executeAfter($this->_lastQuery);
            if ($this->_paginator) {
                $this->_paginator->fullCount = $this->data('foundRows');
            }
            $result = Helper_Array::column($this->_items, $columns);
        }
        return (array) $result;
    }

	/**
	 * Удаляет опшин по имени
     *
	 * @param string $name
	 */
	public function removeOption($name)
	{
		$this->_options->remove($name);
	}

	/**
	 * @desc Очищает коллекцию.
	 * @return Model_Collection Эта коллекция.
	 */
	public function reset ()
	{
		$this->_items = array ();
		return $this;
	}

	/**
	 * Сбросить итератор коллекции
	 */
	public function resetIterator()
	{
		$this->iterator = null;
	}

	/**
	 * @desc Реверсировать последовательность моделей коллекции
	 * @return Model_Collection
	 */
	public function reverse ()
	{
		$this->_items = array_reverse ($this->_items);
		return $this;
	}

	/**
	 * @desc Сохраняет модели коллекции
	 * @return Model_Collection
	 */
	public function save ()
	{
		foreach ($this as $item)
		{
			$item->save ();
		}
		return $this;
	}

	/**
	 * Возращает индекс первого вхождения модели в коллекцию
	 *
	 * @param Model $model
	 * @param integer $offset Смещение поиска от начала коллекции
	 * @return integer - если модель найдена, false - в противном случае
	 */
	public function search($model, $offset = 0)
	{
		foreach ($this->items() as $i => $item) {
			if ($item === $model && $i >= $offset) {
				return $i;
			}
		}
		return false;
	}

	/**
	 * @desc Установить select-часть запроса коллекции
	 * @param string|array $columns
	 * @return Model_Collection
	 */
	public function select ($columns)
	{
		call_user_func_array (
			array ($this->query (), 'select'),
			func_get_args ()
		);

		return $this;
	}

	/**
	 * @desc Меняет поля модели
	 * @param mixed (string,sting|array<string>) $fields
	 * @return Model_Collection
	 */
	public function set ($fields)
	{
		$args = func_get_args ();
		if (count ($args) == 2)
		{
			$args = array ($args [0] => $args [1]);
		}
		foreach ($this as $item)
		{
			foreach ((array) $args as $field => $value)
			{
				$item->field ($field, $value);
			}
		}
		return $this;
	}

	/**
	 * @desc Устанавливает автоджойн моделей для создаваемых объектов.
	 *
	 * @param boolean $value
	 * @return Model_Collection
	 */
	public function setAutojoin ($value)
	{
		$this->_autojoin = $value;
		return $this;
	}

	/**
	 * @desc Заменить модели коллекции.
	 * @param array<Model> $items
	 * @return Model_Collection
	 */
	public function setItems ($items)
	{
		$this->_items = $items;
		return $this;
	}

	/**
	 *
	 * @desc Изменить опшин
	 * @param unknown_type $option
	 */
	public function setOption ($option)
	{
		//$this->_options->setOption ($option);
	}

	/**
	 * @desc Изменить паджинатор коллекции
	 * @param Paginator $paginator
	 */
	public function setPaginator ($paginator)
	{
		$this->_paginator = $paginator;
		if ($paginator) {
			$this->_paginator->fullCount =
				is_array($this->_items) ?
					count ($this->_items) :
					0;
		}
	}

	/**
	 * @desc Подмена запроса коллекции.
	 * @param Query_Abstract $query Новый запрос
	 * @return Model_Collection Эта коллекция
	 */
	public function setQuery (Query_Abstract $query)
	{
		$this->_query = $query;
	}

	/**
	 * @desc Вернуть первый элемент коллекции, удалив его из коллекции.
	 * @return Model|null
	 */
	public function shift ()
	{
		return array_shift ($this->_items);
	}

	/**
	 * @desc Перемешивает элементы коллекции в случайном порядке.
	 * @return Model_Collection
	 */
	public function shuffle ()
	{
		$this->items ();
		shuffle ($this->_items);
		return $this;
	}

	/**
	 * @desc Оставить часть элементов коллекции.
	 * @param integer $offset
	 * @param integer $length
	 * @return Model_Collection Эта коллекция.
	 */
	public function slice ($offset, $length)
	{
		$this->items ();
		$this->_items = array_slice ($this->_items, $offset, $length);
		return $this;
	}

	/**
	 * @desc Сортировка коллекции.
	 * @param string $fields Список полей для сортировки.
	 * Например: "id", "id DESC", "id, rating DESC".
	 * @return Model_Collection
	 */
	public function sort ($fields)
	{
		$items = &$this->items ();
		Helper_Array::mosort (
			$items,
			implode (',', func_get_args ())
		);
		return $this;
	}

	/**
	 * @desc Упорядочивание списка для вывода дерева по полю parentId
	 * @param boolean $include_unparented Оставить элементы без предка.
	 * Если false, элементы будут исключены из списка.
	 *
	 * @return Model_Collection
	 */
	public function sortByParent ($include_unparented = false)
	{
		$list = &$this->items ();

		if (empty ($list))
		{
			// Список пуст
			return $this;
		}
		$firstIDS = $this->column('id');
		$parents = array ();
		$child_of = $list [0]->parentRootKey ();
		$result = array ();
		$i = 0;
		$index = array (0 => 0);
		$full_index = array (-1 => '');

		do
		{
			$finish = true;

			for ($i = 0; $i < count ($list); ++$i)
			{
				if ($list [$i]->parentKey () == $child_of)
				{
					//
					if (!isset ($index [count ($parents)]))
					{
						$index [count ($parents)] = 1;
					}
					else
					{
						$index [count ($parents)]++;
					}

					$n = count ($result);
					$result [$n] = $list [$i];
					$result [$n]->data ('level', count ($parents));
					$result [$n]->data ('index', $index [count ($parents)]);
					$parents_count = count ($parents);

					if ($parents_count > 0)
					{
						$full_index = $full_index [$parents_count - 1] .
							$index [count ($parents)];
					}
					else
					{
						$full_index = (string) $index [count ($parents)];
					}

					$result [$n]->data ('full_index', $full_index);
					$result [$n]->data ('broken_parent', false);

					$full_index [$parents_count] = $full_index . '.';

					array_push ($parents, $child_of);
					$child_of = $list [$i]->key ();

					for ($j = $i; $j < count ($list) - 1; $j++)
					{
						$list [$j] = $list [$j + 1];
					}
					array_pop ($list);
					$finish = false;
					break;
				}
			}

			// Элементы с неверно указанным предком
			if ($finish && count ($parents) > 0)
			{
				$index [count ($parents)] = 0;
				$child_of = array_pop ($parents);
				$finish = false;
			}
		} while (!$finish);
		/**
		 * чтобы не портить сортировку, если таковая есть у
		 * коллекции, с использованием элементов без родителей
		 *
		 * сортируем по level 0, докидываем дочерних
		 */
		if ($include_unparented) {
			//out досортированный
			$newResult = array();
			//без родителей, неотсортированные
			$listIDS = array();
			//отсортированные родители: level = 0
			$resultIDS = array();
			//отсортированные дочерние: level > 0
			$resultSubIDS = array();
			for ($i = 0; $i < count($list); $i++){
				$listIDS[$list[$i]->key()] = $i;
			}
			for ($i = 0; $i < count($result); $i++)
			{
				if ($result[$i]->parentId == 0)
				{
					$parentId = $result[$i]->key();
					$resultIDS[$result[$i]->key()] = $i;
				} else {
					$resultSubIDS[$parentId][$result[$i]->key()] = $i;
				}
			}
			for ($i = 0; $i < count($firstIDS); $i++){
				if (isset($resultIDS[$firstIDS[$i]])) {
					$newResult[] = $result[$resultIDS[$firstIDS[$i]]];
					if (isset($resultSubIDS[$firstIDS[$i]])) {
						foreach ($resultSubIDS[$firstIDS[$i]] as $index)
						{
							$newResult[] = $result[$index];
						}
					}
				} elseif (isset($listIDS[$firstIDS[$i]])) {
					$newResult[] = $list[$listIDS[$firstIDS[$i]]];
				}
			}
			$result = $newResult;
		}
		$this->_items = $result;
		return $this;
	}

	/**
	 * @desc Имя таблицы по умолчанию для коллекции
	 * @return string
	 */
	public function table ()
	{
		return $this->modelName ();
	}

	/**
	 * @desc Получить колекцию уникальный элементов
	 * @return Model_Collection
	 */
	public function unique ()
	{
		$model_name = $this->modelName ();
		$kf = Model_Scheme::keyField ($model_name);
		$keys = array_unique ($this->column ($kf));

		$collection = $this->assign ($this);
		$collection->reset ();

		foreach ($keys as $key)
		{
			$model = Model_Manager::byKey (
				$model_name,
				$key
			);
			if ($model)
			{
				$collection->add ($model);
			}
		}

		$collection->data ($this->data ());

		return $collection;
	}

	/**
	 * @desc Обновление всех элементов коллекции
	 * @param array $data
	 */
	public function update (array $data)
	{
		$items = &$this->items ();
		foreach ($items as $item)
		{
			$item->update ($data);
		}
	}

	/**
	 * @desc Добавление условия отбора.
	 * @param string $condition
	 * @param string $value [optional]
	 * @return Model_Collection Эта коллекция
	 */
	public function where ($condition)
	{
		call_user_func_array (
			array ($this->query (), 'where'),
			func_get_args ()
		);

		return $this;
	}
}