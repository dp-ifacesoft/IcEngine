<?php

if (!class_exists ('Data_Provider_Abstract'))
{
	include dirname (__FILE__) . '/Abstract.php';
}

class Data_Provider_Mongo extends Data_Provider_Abstract
{
	
	/**
	 * 
	 * @var string
	 */
	protected $_databaseName;
	
	/**
	 * 
	 * @var Mongo
	 */
	protected $_connection;
	
	/**
	 * 
	 * @var MongoCollection
	 */
	protected $_collection;
	
	/**
	 * 
	 * @var string
	 */
	protected $_collectionName;
	
	public function __construct (array $config = array ())
	{
		$url = 'mongodb://';
		if (isset ($config ['username']) && isset ($config ['password']))
		{
			$url .= $config ['username'] . ':' . $config ['password'] . '@';
		}
		$url .= $config ['host'];
//		$this->connection = new Mongo ("mongodb://localhost", array ("connect" => false));
		$this->_connection = new Mongo ($url, array ("connect" => true));
		
		$this->_databaseName = $config ['database'];
		$this->_connection->selectDB ($this->_databaseName);
		$this->_collectionName = $config ['collection'];
		$this->_collection = $this->_connection->selectCollection (
			$this->_databaseName,
			$this->_collectionName
		);
		$this->_collection->ensureIndex (
			array ('k' => 1),
			array ('unique' => true)
		);
	}
	
	public function __destruct ()
	{
		if ($this->_connection)
		{
			$this->_connection->close ();
		}
	}
	
	/**
	 * Генерация уникального кода
	 * @return string 
	 */
	protected function _uniqueCode ()
	{
		return time () . uniqid ('', true);
	}
	
	public function add ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('add', $key, $expiration);
		}
		
		$o = array (
			'k'	=> $key,
			'v' => $value
		);
		
		if ($expiration)
		{
			$o ['e'] = time () + $expiration;
		}
		
		if ($tags)
		{
			$o ['t'] = $this->getTags ($tags);
		}
		
		/*
		 
		// Проверяем, чтобы ключ не был установлен. Для 
		// этого к полю ключа "u" добавляем уникальный код
		// В случае, если такого ключа ранее не существовало,
		// то запись будет иметь в поле "u" массив с единственным
		// значением - указанным кодом.
		
		$u = $this->_uniqueCode ();
		
		$o ['u'] = array ($u);
		
		$this->_collection->update (
			array (
				'k' => $key
			),
			array (
				'$set' => array (
					'k' => $key
				),
				'$push' => array (
					'u' => $u
				)
			),
			array ('upsert' => true)
		);
        
		$row = $this->_collection->findOne (
			array ('k' => $key),
			array ('u')
		);
		
		if (
			!is_array ($row) || !is_array ($row ['u']) || 
			!$row ['u'] || $row ['u'][0] != $u
		)
		{
			// Запись была создана другим процессом
			return false;
		}
        
		// запись только что была добавлена этим процессом,
		// устанавливаем значения
		$this->_collection->update (
			array (
				'k' => $key
			), 
			$o,
			array ('upsert' => true)
		);
        */
		
		try
		{
			$r = $this->_collection->insert ($o, array ('safe' => true));
		}
		catch (Exception $e)
		{
			return false;
		}
		
        return true;
	}
	
	public function decrement ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('increment', $key);
		}
		
		$this->_collection->update (
			array ('k' => $key),
			array ('$inc' => array ('v' => -$value)),
			array ("upsert" => true)
		);
	}
	
	public function delete ($keys, $time = 0, $set_deleted = false)
	{
		$keys = (array) $keys;
		
		if ($this->tracer)
		{
			$this->tracer->add ('delete', implode (',', $keys));
		}
		
		foreach ($keys as $key)
		{
			$this->_collection->remove (array ('k' => $key));
		}
	}
	
	public function flush ($delay = 0)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('flush', $delay);
		}
		
		$this->_collection->drop ();
		$this->_collection = $this->_connection->selectCollection (
			$this->_databaseName,
			$this->_collectionName
		);
	}
	
	public function get ($key, $plain = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('get', $key);
		}
		
		$o = $this->_collection->findOne (
			array ('k' => $key)
		);
		
		if (!$o)
		{
			// нет данных
			return null;
		}
		
		if (isset ($o ['e']) && ($o ['e'] < time ()))
		{
			// Истек срок годности
			return null;
		}
		
		if (isset ($o ['t']) && !$this->checkTags ($o ['t']))
		{
			// один или несколько тегов устарели
			return null;
		}
		
		return $o ['v'];
	}
	
	public function getMulti (array $keys, $numeric_index = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('getMulti', implode (',', $keys));
		}
		
		$cursor = $this->_collection->find (
			array ('k' => array ('$in' => $keys))
		);
		
		$result = array ();
		
		if ($numeric_index)
		{
		    $values = array ();
			foreach ($cursor as $i => $row)
			{
				if (isset ($row ['e']) && ($row ['e'] < time ()))
				{
					// вышло время жизни
					continue ;
				}
				if (isset ($row ['t']) && !$this->checkTags ($row ['t']))
				{
					// изменены теги
					continue ;
				}
				$values [$row ['k']] = $row ['v'];
			}
			
			foreach ($keys as $key)
			{
			    $result [] = isset ($values [$key]) ? $values [$key] : null;
			}
		}
		else
		{
			foreach ($cursor as $row)
			{
				if (isset ($row ['e']) && ($row ['e'] < time ()))
				{
					// вышло время жизни
					continue ;
				}
				if (isset ($row ['t']) && !$this->checkTags ($row ['t']))
				{
					// изменены теги
					continue ;
				}
				$result [$row ['k']] = $row ['v'];
			}
		}

		return $result;
	}
	
	public function increment ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('increment', $key);
		}
		
		$this->_collection->update (
			array ('k' => $key),
			array ('$inc' => array ('v' => $value)),
			array ("upsert" => true)
		);
	}
	
	public function keys ($pattern, $server = NULL)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('keys', $pattern);
		}
		
		if ($pattern [0] != '*')
		{
			$pattern = '^' . $pattern;
		}
		
		$last = $pattern [strlen ($pattern) - 1];
		if ($last != '*')
		{
			$pattern .= '$';
		}
		
		$pattern = '/' . $pattern . '/i';
		
		$cursor = $this->_collection->find (
			array ('k' => new MongoRegex ($pattern)),
			array ('k')
		);
		
		$result = array ();
				
		while ($cursor->hasNext ())
		{
			$row = $cursor->getNext ();
			$result [] = $row ['k'];
		}
		
		return $result;
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('set', $key);
		}
		
		$o = array (
			'k' => $key,
			'v' => $value,
			'u' => array (1)
		);
		
		if ($expiration)
		{
			$o ['e'] = time () + $expiration;
		}
		
		if ($tags)
		{
			$o ['t'] = $this->getTags ($tags);
		}
		
		$this->_collection->update (
			array ('k' => $key),
			$o,
			array ("upsert" => true)
		);
	}
    
}