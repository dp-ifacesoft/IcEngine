<?php

namespace Ice;

Loader::load ('Data_Adapter_Abstract');

/**
 *
 * @desc Адаптер для работы с MongoDB
 * @author Юрий Шведов, Илья Колесников
 * @package Ice
 *
 */
class Data_Adapter_Mongo extends Data_Adapter_Abstract
{
	/**
	 * @desc Текущая коллекция
	 * @var MongoCollection
	 */
	protected $_collection;

	/**
	 * @see Data_Adapter_Abstract::_connectionOptions
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

	/*
	 * @desc Результат выполнения скрипта
	 */
	protected $_result = null;


	/**
	 * @see Data_Adapter_Abstract::_queryMethods
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
	 * @see Data_Adapter_Abstract::_translatorName
	 * @var string
	 */
	protected $_translatorName = 'Mongo';

	/**
	 * @desc Запрос на удаление
	 */
	public function _executeDelete (Query $query, Query_Options $options)
	{
		$this->_collection = $this->connect ()->selectCollection (
			$this->_connectionOptions ['database'],
			$this->_query ['collection']
		);

		$this->_collection->remove (
			$this->_query ['criteria'],
			$this->_query ['options']
		);
		$this->_touchedRows = 1;
	}

	/**
	 * @desc Запрос на вставку
	 */
	public function _executeInsert (Query $query, Query_Options $options)
	{
		$this->_collection = $this->connect ()->selectCollection (
			$this->_connectionOptions ['database'],
			$this->_query ['collection']
		);

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
	public function _executeSelect (Query $query, Query_Options $options)
	{
		$this->_collection = $this->connect ()->selectCollection (
			$this->_connectionOptions ['database'],
			$this->_query ['collection']
		);

		if ($this->_query ['find_one'])
		{
			$this->_result = array (
				$this->_collection->findOne ($this->_query ['query'])
			);
		}
		else
		{
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
	public function _executeShow (Query $query, Query_Options $options)
	{
		$this->_collection = $this->connect ()->selectCollection (
			$this->_connectionOptions ['database'],
			$this->_query ['collection']
		);

		$show = strtoupper ($this->_query ['show']);
		if ($show == 'DELETE_INDEXES')
		{
			$this->_result = array ($this->_collection->deleteIndexes ());
		}
		elseif ($show == 'ENSURE_INDEXES')
		{
			// Создание индексов
			$result = Model_Scheme::getInstance ()
				->getScheme ($this->_query ['model']);
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
	public function _executeUpdate (Query $query, Query_Options $options)
	{
		$this->_collection = $this->connect ()->selectCollection (
			$this->_connectionOptions ['database'],
			$this->_query ['collection']
		);

		$this->_collection->update (
			$this->_query ['criteria'],
			$this->_query ['newobj'],
			$this->_query ['options']
		);
		//Mysql::update ($tags, $sql);
		$this->_touchedRows = 1; // unknown count
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
			Loader::load ('Crypt_Manager');
			$this->_connectionOptions [$key] = Crypt_Manager::autoDecode ($value);
			return;
		}
		return parent::setOption ($key, $value);
	}

}