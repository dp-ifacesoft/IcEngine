<?php

/*
    @class Redis
    @package IMemcacheClient
    @url http://code.google.com/p/imemcacheclient-php/
    @author kak.serpom.po.yaitsam@gmail.com
    @description Connector for Redis (http://code.google.com/p/redis/)
    @license LGPL, BSD-compabible. Adding to the Redis repository permitted.
*/
class Redis_Wrapper
{

	/**
	 * @desc Экземпляр класса
	 * @var Redis
	 */
	protected static $_instance;

	public $servers = array ();

	public $default_port = 6379;

	public $dtags_enabled = TRUE;

	public $pool = array ();

	public function __construct ()
	{
	}

	public function addServer ($host, $port = NULL, $weight = NULL)
	{
		if ($port === NULL)
		{
			$port = $this->default_port;
		}
		$this->servers [$host . ':' . $port] = $weight;
	}

	private function getConnection ($addr)
	{
		if (!isset ($this->pool [$addr]))
		{
			$tmp = explode (':', $addr);
		
			$this->pool [$addr] = new Redis;	
			$this->pool [$addr]->connect (
				$tmp [0], 
				isset ($tmp [1]) ? $tmp [1] : null
			);
		}
		
		return $this->pool [$addr];
	}

	private function getConnectionByKey ($key)
	{
		$index = abs (crc32 ($key) % count ($this->servers));
		
		$tmp = array_keys ($this->servers);
		
		$addr = $tmp [$index];
		
		return $this->getConnection ($addr);
	}

	/**
	 * @desc Удаление ключей по маске
	 * @param string $pattern Маска.
	 */
	public function clearByPattern ($pattern)
	{
		$keys = $this->keys ($pattern . '*');
		if (!$keys)
		{
			return;
		}
		$this->delete ($keys);
	}

	private function disconnect ($k)
	{
		if (! isset ($this->pool [$k]))
		{
			return FALSE;
		}
		$this->pool [$k]->close ();
		unset ($this->pool [$k]);
		$this->pool = array_values ($this->pool);
		return TRUE;
	}
	
	public function ping ($addr)
	{
		return $this->pool ($addr)->ping ();
	}

	public function get ($key, $plain = FALSE)
	{
		$conn = $this->getConnectionByKey ($key);
		
		$val = $conn->get ($key);
		
		if ($val)
		{
			$val = json_decode ($val, true);
		}
		
		return $val;
	}

	public function set ($key, $value, $TTL = null)
	{
		$conn = $this->getConnectionByKey ($key);
		
		if (!is_scalar ($value))
		{
			$value = json_encode ($value);
		}
		
		if ($TTL)
		{
			return $conn->setex ($key, $TTL, $value);
		}
		
		return $conn->set ($key, $value);
	}

	public function expire ($key, $TTL = 0)
	{
		$conn = $this->getConnectionByKey ($key);
		
		return $conn->expire ($key, $TTL);
	}

	public function getTTL ($key)
	{
		$conn = $this->getConnectionByKey ($key);
		
		return $conn->ttl ($key);
	}

	public function add ($key, $value, $TTL = 0)
	{
		return $this->set ($key, $value, $TTL);
	}

	public function replace ($key, $value) // not complete atomic
	{
		$conn = $this->getConnectionByKey ($key);
		
		return $conn->getSet ($key, $value);
	}
	
	public function getMulti ($keys)
	{
		$result = array ();
		
		$conns = array ();
		
		foreach ($keys as $key)
		{
			$conn = $this->getConnectionByKey ($key);
			
			$index = (int) $conn->socket;
			
			if (!isset ($conns [$index]))
			{
				$conns [$index] = array (
					'conn'	=> $conn,
					'keys'	=> array ()
				);
			}
			
			$conns [$index]['keys'][] = $key;
		}
		
		foreach ($conns as $conn)
		{
			$ret = $conn ['conn']->multi ();
			
			foreach ($conn ['keys'] as $key)
			{
				$ret = $ret->get ($key);
			}
			
			$values = $ret->exec ();
			
			$tmp = array ();
			
			foreach ($keys as $i => $key)
			{
				$tmp [$key] = isset ($values [$i]) 
					? json_decode ($values [$i], true)
					: null;
			}
			
			$result = array_merge ($result, $tmp);
		}
		
		return $result;
	}

	public function increment ($key, $number = 1)
	{
		$conn = $this->getConnectionByKey ($key);
		
		return $conn->incrBy ($key, $number);
	}

	/**
	 * @desc Возвращает экземпляр класса
	 * @return Redis
	 */
	public static function instance ()
	{
		if (! self::$_instance)
		{
			self::$_instance = new self ();
		}
		return self::$_instance;
	}

	public function decrement ($key, $number = 1)
	{
		$conn = $this->getConnectionByKey ($key);
		
		return $conn->decrBy ($key, $number);
	}
	
	/**
	 * @desc Удаление одного или нескольких ключей.
	 * @param string|array $keys
	 * @return mixed Ответ редиса
	 */
	public function delete ($keys)
	{
		$keys = (array) $keys;
		
		$conns = array ();
		
		foreach ($keys as $key)
		{
			$conn = $this->getConnectionByKey ($key);
			
			$index = (int) $conn->socket;
			
			if (!isset ($conns [$index]))
			{
				$conns [$index] = array (
					'conn'	=> $conn,
					'keys'	=> array ()
				);
			}
			
			$conns [$index]['keys'][] = $key;
		}
		
		foreach ($conns as $conn)
		{
			$conn ['conn']->delete ($conn ['keys']);
		}
		
	}

	public function exists ($key)
	{
		$conn = $this->getConnectionByKey ($key);
		
		return $conn->exists ($key);
	}

	public function type ($key)
	{
		$conn = $this->getConnectionByKey ($key);
		
		return $conn->type ($key);
	}

	public function keys ($pattern)
	{
		$keys = array ();
		
		foreach ($this->pool as $conn)
		{
			$keys = array_merge (
				$keys,
				$conn->keys ($pattern)
			);
		}
		
		return $keys;
	}

	public function randomKey ()
	{
		$conn = reset ($this->pool);
		
		return $conn->randomKey ();
	}

	public function rename ($key, $newkey)
	{
		$conn = $this->getConnectionByKey ($key);
		
		return $conn->rename ($key, $newkey);
	}

	public function renamenx ($key, $newkey)
	{
		$conn = $this->getConnectionByKey ($key);
		
		return $conn->renameNx ($key, $newkey);
	}

	public function push ($key, $value, $right = true)
	{
		if ($right)
		{
			return $this->rpush ($key, $value);
		}
		
		return $this->lpush ($key, $value);
	}

	public function lpush ($key, $value)
	{
		$conn = $this->getConnectionByKey ($key);
		
		return $conn->lpush ($key, $value);
	}

	public function rpush ($key, $value)
	{
		$conn = $this->getConnectionByKey ($key);
		
		return $conn->rpush ($key, $value);
	}

	public function cpush ($maxsize, $key, $value, $right = true)
	{
		return $this->requestByKey ($key, 
		($right ? 'CRPUSH' : 'CLPUSH') . ' ' . $maxsize . ' ' . $key . ' ' .
		 strlen ($value) . "\r\n" . $value);
	}

	public function clpush ($maxsize, $key, $value)
	{
		return $this->cpush ($maxsize, $key, $value, FALSE);
	}

	public function crpush ($maxsize, $key, $value)
	{
		return $this->cpush ($maxsize, $key, $value, TRUE);
	}

	public function ltrim ($key, $start, $end)
	{
		return $this->requestByKey ($key, 'LTRIM ' . $key . ' ' . $start . ' ' .
		 $end);
	}

	public function lindex ($key, $index)
	{
		return $this->requestByKey ($key, 'LINDEX ' . $key . ' ' . $index);
	}

	public function pop ($key, $right = TRUE)
	{
		return $this->requestByKey ($key, ($right ? 'RPOP' : 'LPOP') . ' ' .
		 $key);
	}

	public function lpop ($key)
	{
		return $this->pop ($key, FALSE);
	}

	public function rpop ($key)
	{
		return $this->pop ($key, TRUE);
	}

	public function llen ($key)
	{
		return $this->requestByKey ($key, 'LLEN ' . $key);
	}

	public function lrange ($key, $start, $end)
	{
		return $this->requestByKey ($key, 'LRANGE ' . $key . ' ' . $start . ' ' .
		 $end);
	}

	public function sort ($key, $query = NULL)
	{
		return $this->requestByKey ($key, 
		'SORT ' . $key . ($query === NULL ? '' : ' ' . $query));
	}

	public function lset ($key, $value, $index)
	{
		return $this->requestByKey ($key, 
		'LSET ' . $key . ' ' . $index . ' ' . strlen ($value) . "\r\n" . $value);
	}

	public function lrem ($key, $count, $value)
	{
		return $this->requestByKey ($key, 
		'LREM ' . $key . ' ' . $count . ' ' . strlen ($value) . "\r\n" . $value);
	}

	public function sadd ($key, $value)
	{
		return $this->requestByKey ($key, 
		'SADD ' . $key . ' ' . strlen ($value) . "\r\n" . $value);
	}

	public function srem ($key, $value)
	{
		return $this->requestByKey ($key, 
		'SREM ' . $key . ' ' . strlen ($value) . "\r\n" . $value);
	}

	public function sismember ($key, $value)
	{
		return $this->requestByKey ($key, 
		'SISMEMBER ' . $key . ' ' . strlen ($value) . "\r\n" . $value);
	}

	public function sinter ($keys, $bykey)
	{
		return $this->requestByKey ($bykey, 'SINTER ' . implode (' ', $keys));
	}

	public function sinterstore ($keys, $bykey)
	{
		return $this->requestByKey ($bykey, 
		'SINTERSTORE ' . $bykey . ' ' . implode (' ', $keys));
	}

	public function smembers ($key)
	{
		return $this->requestByKey ($key, 'SMEMBERS ' . $key);
	}

	public function scard ($key)
	{
		return $this->requestByKey ($key, 'SCARD ' . $key);
	}

	public function smove ($srckey, $dstkey, $member)
	{
		return $this->requestByKey ($key, 
		'SMOVE ' . $srckey . ' ' . $dstkey . ' ' . $member);
	}

	public function selectdb ($dbname, $server = '*')
	{
		return $this->requestByServer ($server, 'SELECT ' . $dbname);
	}

	public function move ($key, $dbname)
	{
		return $this->requestByKey ($key, 'MOVE ' . $key . ' ' . $dbname);
	}

	public function save ($bg = FALSE, $server = '*')
	{
		return $this->requestByServer ($server, ($bg ? 'BGSAVE' : 'SAVE'));
	}

	public function lastsave ($server = '*')
	{
		return $this->requestByServer ($server, 'LASTSAVE');
	}

	public function flush ($all = FALSE, $server = '*')
	{
		return $this->requestByServer ($server, ($all ? 'FLUSHALL' : 'FLUSHDB'));
	}

	public function info ($server = '*')
	{
		$r = $this->requestByServer ($server, 'INFO');
		if ($server !== '*')
		{
			$r = array (
				$server => $r
			);
		}
		$result = array ();
		foreach ($r as $srv => $reply)
		{
			$info = array ();
			foreach (explode ("\r\n", $reply) as $l)
			{
				if ($l === '')
				{
					continue;
				}
				list ($k, $v) = explode (':', $l, 2);
				$_v = strpos ($v, '.') !== false ? (float) $v : (int) $v;
				$info [$k] = (string) $_v == $v ? $_v : $v;
			}
			$result [$srv] = $info;
		}
		return $result;
	}
}