<?php

/**
 *
 * @desc Мэппер для работы с Mongodb, с кэшированием запросов.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Mapper_Mongo_Cached extends Data_Mapper_Mongo
{
	/**
	 * @desc Кэшер запросов.
	 * @var Data_Provider_Abstract
	 */
	protected $_cacher;

	/**
	 * @desc Получение хэша запроса
	 * @return string
	 */
	protected function _queryHash ()
	{
		return md5 (json_encode ($this->_query));
	}

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

		$tags = $query->getTags ();

		for ($i = 0, $count = sizeof ($tags); $i < $count; ++$i)
		{
			$this->_cacher->tagDelete ($tags [$i]);
		}
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

		$tags = $query->getTags ();

		for ($i = 0, $count = sizeof ($tags); $i < $count; ++$i)
		{
			$this->_cacher->tagDelete ($tags [$i]);
		}
	}

	/**
	 * @desc Запрос на выбор
	 * @param Query_Abstract $query
	 * @param Query_Option $options
	 */
	public function _executeSelect (Query_Abstract $query, Query_Options $options)
	{
		$key = $this->_queryHash ();

		$expiration = $options->getExpiration ();

		$cache = $this->_cacher->get ($key);

		$use_cache = false;

		if ($cache)
		{
			if (
	   			($cache ['a'] + $expiration > time () || $expiration == 0) &&
				$this->_cacher->checkTags ($cache ['t'])
			)
			{
	  			$use_cache = true;
			}

			if (!$this->_cacher->lock ($key, 5, 1, 1))
			{
				$use_cache = true;
			}
		}

		if ($use_cache)
		{
			$this->_foundRows = $cache ['f'];
			return $cache ['v'];
		}

		if (class_exists ('Tracer'))
		{
			Tracer::begin (
				__CLASS__,
				__METHOD__,
				__LINE__
			);
		}

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

		if (class_exists ('Tracer'))
		{
			Tracer::end ($this->_sql);
		}

		$tags = $query->getTags ();

		$this->_cacher->set (
			$key,
			array (
				'v' => $this->_result,
				'a' => time (),
				't' => $this->_cacher->getTags ($tags),
				'f'	=> $this->_foundRows
			)
		);

		if ($cache)
		{
			$this->_cacher->unlock ($key);
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
			// Удаление индексов
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

		$tags = $query->getTags ();

		for ($i = 0, $count = sizeof ($tags); $i < $count; ++$i)
		{
			$this->_cacher->tagDelete ($tags [$i]);
		}
	}

	/**
	 * @return Data_Provider_Abstract
	 */
	public function getCacher ()
	{
		return $this->_cacher;
	}

	/**
	 *
	 * @param Data_Provider_Abstract $cacher
	 */
	public function setCacher (Data_Provider_Abstract $cacher)
	{
		$this->_cacher = $cacher;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Mysqli::setOption()
	 */
	public function setOption ($key, $value = null)
	{
		switch ($key)
		{
			case 'cache_provider':
				$this->setCacher (Data_Provider_Manager::get ($value));
				return;
			case 'expiration':
				$this->getDefaultOptions ()->setExpiration ($value);
				return;
		}
		return parent::setOption ($key, $value);
	}

}
