<?php
/**
 * 
 * @desc Работа с данными Memcached
 * @author Юрий
 * @package IcEngine
 *
 */
class Data_Provider_Memcached extends Data_Provider_Abstract
{
	
	/**
	 * @desc Соединение с memcached
	 * @var Memcached
	 */
	public $conn = null;
	
	/**
	 * Максимальное количество выбираемых за раз значений.
	 * Необходимо для обхода бага, когда
	 * в версии Redis под windows стояло жесткое ограничение на 15 значений.
	 * @var integer
	 */
	public $mget_limit = 999;
	
	/**
	 * 
	 * @param array $config
	 */
	public function __construct ($config = array ())
	{
		$this->conn = new Memcached ();
		$this->setOption (Memcached::OPT_COMPRESSION, 0);
		$this->setOption (
			Memcached::OPT_DISTRIBUTION, 
			Memcached::OPT_LIBKETAMA_COMPATIBLE
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::add()
	 */
	public function add ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('add', $key, $expiration);
		}
		
		if ($expiration < 0)
		{
			$expiration = 0;
		}
		
		return $this->conn->add (
			$this->keyEncode ($key),
			$value, $expiration
		);
	}
	
	/**
	 * @desc Добавление сервера
	 * @param string $host
	 * @param integer $port
	 * @param integer $weight
	 * @return boolean
	 */
	public function addServer ($host, $port = null, $weight = null)
	{
		$this->servers [] = array ($host, $port, $weight);
		return $this->conn->addServer ($host, $port, $weight);
	}
	
	/**
	 * @desc Добавление списка серверов 
	 * @param array $a
	 */
	public function addServers (array $a)
	{
		foreach ($a as $s)
		{
			$this->addServer ($s [0], $s [1], isset ($s [2]) ? $s [2] : null);
		}
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::append()
	 */
	public function append ($key, $value)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('append', $key);
		}
		
		return $this->conn->append ($this->keyEncode ($key), $value);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::decrement()
	 */
	public function decrement ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('decrement', $key);
		}
		
		return $this->conn->decrement ($this->keyEncode ($key), $value);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::delete()
	 */
	public function delete ($keys, $time = 0, $set_deleted = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('delete', $keys, $time);
		}
		
		if ($time < 0)
		{
			$time = 0;
		}
		
		if (!is_array ($keys))
		{
			if ($set_deleted)
			{
				$this->conn->set (
					$this->keyEncode ($this->prefix_deleted . $keys),
					time ()
				);
			}
			if (isset ($this->locks [$keys]))
			{
				unset ($this->locks [$keys]);
			}
			return $this->conn->delete ($this->keyEncode ($keys), $time);
		}
		
		foreach ($keys as $key)
		{
			$tt = $time;
			if (is_array ($key))
			{
				if (isset ($key [1]))
				{
					$tt = $key [1];
				}
				$key = $key [0];
			}
			
			if (isset ($this->locks [$key]))
			{
				unset ($this->locks [$key]);
			}
			
			if ($set_deleted)
			{
				$this->conn->set (
					$this->keyEncode ($this->prefix_deleted . $key),
					time ()
				);
			}
			$this->conn->delete ($this->keyEncode ($key), $tt);
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::flush()
	 */
	public function flush ($delay = 0)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('flush', $delay);
		}
		
		return $this->conn->flush ($delay);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::get()
	 */
	public function get ($key, $plain = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('get', $key);
		}
		
		return $this->conn->get ($this->keyEncode ($key), $plain);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getMulti()
	 */
	public function getMulti (array $keys, $numeric_index = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('getMulti', implode (',', $keys));
		}
		
		$keys = array_map (
			array ($this, 'keyEncode'),
			$keys
		);
		
		if ($this->mget_limit && count ($keys) > $this->mget_limit)
		{
			// Ограничение на максимальную выборку из кеша. 
			// fix redis.c bug -.-
			// http://code.google.com/p/redis/issues/detail?id=24
			$start = 0;
			$r = array ();
			while ($start < count ($keys))
			{
				$subkeys = array_slice ($keys, $start, $this->mget_limit);
				$r = array_merge (
					$r, 
					$this->conn->getMulti ($subkeys)
				);
				$start += $this->mget_limit;
			}
		}
		else
		{
			$r = $this->conn->getMulti ($keys);
		}
		
		if ($numeric_index)
		{
			return array_values ($r);
		}
		
		return array_combine ($keys, array_values ($r));
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getStats()
	 */
	public function getStats ()
	{
		return $this->conn->getStats ();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::increment()
	 */
	public function increment ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('increment', $key);
		}
		
		return $this->conn->increment ($this->keyEncode ($key), $value);
	}
	
	/**
	 * @desc Кодирование ключа для корректного сохранения в редисе.
	 * @param string $key
	 * @return string
	 */
	public function keyEncode ($key)
	{
		return urlencode ($this->prefix . $key);
	}
	
	/**
	 * @desc Декодирование ключа.
	 * @param string $key
	 * @return string
	 */
	public function keyDecode ($key)
	{
		return substr (urldecode ($key), strlen ($this->prefix));
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::keys()
	 */
	public function keys ($pattern, $server = null)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('keys', $pattern);
		}
		
		$mask = $this->keyEncode ($pattern);
		$mask = str_replace ('%2A', '*', $mask);
		$r = $this->conn->keys ($mask, empty ($server) ? '' : $server);
		
		if (empty ($r) || (count ($r) == 1 && empty ($r [0])))
		{
			return array ();
		}
		
		return array_map (array ($this, 'keyDecode'), $r);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::prepend()
	 */
	public function prepend ($key, $value)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('prepend', $key);
		}
		
		return $this->conn->prepend ($this->keyEncode ($key), $value);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('set', $key, $value, $expiration);
		}
		
		if ($expiration < 0)
		{
			$expiration = 0;
		}
		
		return $this->conn->set ($this->keyEncode ($key), $value, $expiration, $tags);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::setOption()
	 */
	public function setOption ($key, $value)
	{
		switch ($key)
		{
			case 'mget_limit':
				$this->mget_limit = $value;
				return true;
				
			case 'servers':
				foreach ($value as $server)
				{
					$this->addServer (
						$server ['host'],
						isset ($server ['port']) ? $server ['port'] : null,
						isset ($server ['weight']) ? $server ['weight'] : null
					);
				}
				return true;
		}
		
		if (parent::setOption ($key, $value))
		{
			return true;
		}
		
		return $this->conn->setOption ($key, $value);
	}
	
}