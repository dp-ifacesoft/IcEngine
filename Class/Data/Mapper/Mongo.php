<?php
/**
 *
 * @desc Мэппер для работы с MongoDB
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Mapper_Mongo extends Data_Mapper_Abstract
{

	/**
	 * @desc Соединение с монго.
	 * @var Mongo
	 */
	protected $_connection;

	/**
	 * @desc Текущая коллекция
	 * @var MongoCollection
	 */
	protected $_collection;

	/**
	 * @desc Параметры соединения
	 * @var array
	 */
	public $_connectionOptions = array (
		'host'		=> 'localhost',
		'username'	=> '',
		'password'	=> '',
		'database'	=> 'unknown',
		'charset'	=> 'utf8',
		'options'	=> array ()
	);

	/**
	 * @desc Последний оттранслированный запрос.
	 * @var array
	 */
	protected $_query = '';

	protected $_result = null;
	protected $_touchedRows = 0;
	protected $_foundRows = 0;
	protected $_insertId = null;

	/**
	 * Обработчики по видам запросов.
	 * @var array
	 */
	protected $_queryMethods = array (
		Query::SELECT	=> '_executeSelect',
		Query::SHOW		=> '_executeShow',
		Query::DELETE	=> '_executeDelete',
		Query::UPDATE	=> '_executeUpdate',
		Query::INSERT	=> '_executeInsert'
	);

	/**
	 * @desc Запрос на удаление
	 */
	public function _executeDelete (Query_Abstract $query, Query_Options $options)
	{
		$this->_collection->remove (
			$this->_query ['criteria'],
			$this->_query ['options']
		);
		$this->_touchedRows = 1;
	}

	/**
	 * @desc Запрос на вставку
	 */
	public function _executeInsert (Query_Abstract $query, Query_Options $options)
	{
		if (isset ($this->_query ['a']['_id']))
		{
			$this->_insertId = $this->_query ['a']['_id'];
			$this->_collection->update (
				array (
					'_id'		=> $this->_insertId
				),
				$this->_query ['a'],
				array (
					'upsert'	=> true
				)
			);
		}
		else
		{
			$this->_collection->insert ($this->_query ['a']);
			$this->_insertId = $this->_query ['a']['_id'];
		}

		$this->_touchedRows = 1;
	}

	/**
	 * @desc Запрос на выбор
	 */
	public function _executeSelect (Query_Abstract $query, Query_Options $options)
	{
		if ($this->_query ['find_one'])
		{
			$this->_result = array (
				$this->_collection->findOne ($this->_query ['query'])
			);
		}
		else
		{
//			fb (json_encode ($q ['query']));

			$r = $this->_collection->find ($this->_query ['query']);

			if ($this->_query [Query::CALC_FOUND_ROWS])
			{
				$this->_foundRows = $r->count ();
			}

			if ($this->_query ['sort'])
			{
				$r->sort ($this->_query ['sort']);
			}
			if ($this->_query ['skip'])
			{
				$r->skip ($this->_query ['skip']);
			}
			if ($this->_query ['limit'])
			{
				$r->limit ($this->_query ['limit']);
			}
			//$result = Mysql::select ($tags, $sql);
			$this->_touchedRows = $r->count (true);

			$this->_result = array ();
			foreach ($r as $tr)
			{
				$this->_result [] = $tr;
			}
			// Так не работает, записи начинают повторяться
			// $this->_result = $r;
		}
	}

	/**
	 * @desc
	 * @param Query $query
	 * @param Query_Options $options
	 */
	public function _executeShow ()
	{
		$show = strtoupper ($this->_query ['show']);
		if ($show == 'DELETE_INDEXES')
		{
			$this->_result = array ($this->_collection->deleteIndexes ());
		}
		elseif ($show == 'ENSURE_INDEXES')
		{
			// Создание индексов
			$result = Model_Scheme::getScheme ($this->_query ['model']);
			$this->_result = $result ['keys'];
			foreach ($this->_result as $key)
			{
				$temp = array ();
				$options = array ();
				if (isset ($key ['primary']))
				{
					$temp = (array) $key ['primary'];
					$options ['unique'] = true;
				}
				elseif (isset ($key ['index']))
				{
					$temp = (array) $key ['index'];
				}

				$keys = array ();
				foreach ($temp as $index)
				{
					$keys [$index] = 1;
				}

				$this->_collection->ensureIndex ($keys, $options);
			}
		}
	}

	/**
	 * @desc Обновление
	 */
	public function _executeUpdate (Query_Abstract $query, Query_Options $options)
	{
		$this->_collection->update (
			$this->_query ['criteria'],
			$this->_query ['newobj'],
			$this->_query ['options']
		);
		//Mysql::update ($tags, $sql);
		$this->_touchedRows = 1; // unknown count
	}

	/**
	 *
	 * @param mixed $result
	 * @param mixed $options
	 * @return boolean
	 */
	protected function _isCurrency ($result, $options)
	{
		if (!$options)
		{
			return true;
		}

		return $options->getNotEmpty () && empty ($result) ? false : true;
	}

	/**
	 * @desc Подключение к БД
	 * @param Objective|array $config [optional]
	 * @return Mongo
	 */
	public function connect ($config = null)
	{
		if ($this->_connection)
		{
			return $this->_connection;
		}

		if ($config)
		{
			$this->setOption ($config);
		}

		$url = 'mongodb://';
		if (
			$this->_connectionOptions ['username'] &&
			$this->_connectionOptions ['password']
		)
		{
			$url .=
				$this->_connectionOptions ['username'] . ':' .
				$this->_connectionOptions ['password'] . '@';
		}
		$url .= $this->_connectionOptions ['host'];

		$options = array (
			'connect'	=> true
		);

		if (isset ($this->_connectionOptions ['options']['replicaSet']))
		{
			$options ['replicaSet']	= $this->_connectionOptions ['options']['replicaSet'];
		}

		$this->_connection = new Mongo ($url, $options);
		$this->_connection->selectDB ($this->_connectionOptions ['database']);

		return $this->_connection;
	}

	public function execute (Data_Source_Abstract $source, Query_Abstract $query,
		$options = null)
	{
		if (!($query instanceof Query_Abstract))
		{
			return new Query_Result (null);
		}

		$start = microtime (true);

		$clone = clone $query;

		$where = $clone->getPart (Query::WHERE);
		$this->_filters->apply ($where, Query::VALUE);
		$clone->setPart (Query::WHERE, $where);

		$this->_query = $clone->translate ('Mongo');

		//echo $this->_connectionOptions ['database'] . ' ' . $this->_query ['collection'] . PHP_EOL;

		$this->_collection = $this->connect ()->selectCollection (
			$this->_connectionOptions ['database'],
			$this->_query ['collection']
		);

		$this->_result = array ();
		$this->_touchedRows = 0;
		$this->_foundRows = 0;
		$this->_insertId = null;

		if (!$options)
		{
			$options = $this->getDefaultOptions ();
		}

		$m = $this->_queryMethods [$query->type ()];
		$this->{$m} ($query, $options);
		//echo $m . PHP_EOL;
		$finish = microtime (true);

		return new Query_Result (array (
			'error'			=> '',
			'errno'			=> 0,
			'query'			=> $clone,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
			'foundRows'		=> $this->_foundRows,
			'result'		=> $this->_result,
			'touchedRows'	=> $this->_touchedRows,
			'insertKey'		=> $this->_insertId,
			'currency'		=> $this->_isCurrency ($this->_result, $options),
			'source'		=> $source
		));
	}

	/**
	 * @desc Возвращает ресурс соединения с mysql.
	 * @return resource
	 */
	public function linkIdentifier ()
	{
		$this->connect ();
		return $this->_linkIdentifier;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::setOption()
	 */
	public function setOption ($key, $value = null)
	{
		if (is_array ($key) || !is_scalar ($key))
		{
			foreach ($key as $k => $v)
			{
				$this->setOption ($k, $v);
			}
			return;
		}

		if (isset ($this->_connectionOptions [$key]))
		{
			$this->_connectionOptions [$key] = Crypt_Manager::autoDecode ($value);
			return;
		}
		return parent::setOption ($key, $value);
	}

}
