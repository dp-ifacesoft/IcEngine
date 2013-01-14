<?php
/**
 * @desc class Memcached 
 * Класс не используется, необходим для Code Assistant.
 * @author Goorus
 * @package IcEngine
 */
class Memcached 
{
	
	/**
	 * @param string $persistent_id
	 */
	public function __construct ($persistent_id = null) {}
	
	/**
	 * @param string $key
	 * @param mixed $value
	 * @param integer $expiration [optional]
	 * @return boolean
	 */
	public function add ($key, $value, $expiration = null) {}
	
	/**
	 * @param string $server_key
	 * @param string $key
	 * @param mixed $value 
	 * @param integer $expiration [optional]
	 * @return boolean
	 */
	public function addByKey ($server_key, string $key, $value, $expiration) {}
	
	/**
	 * @param string $host
	 * @param integer $port
	 * @param integer $weight
	 * @return boolean
	 */
	public function addServer ($host, $port, $weight = 0) {}
	
	/**
	 * @param array $servers
	 * @return boolean
	 */
	public function addServers ( array $servers ) {}
	/**
	 * 
	 * @param unknown_type $key
	 * @param unknown_type $value
	 * @return boolean
	 */
	public function append ( string $key , string $value ) {}
	/**
	 * 
	 * @param unknown_type $server_key
	 * @param unknown_type $key
	 * @param unknown_type $value
	 * @return boolean
	 */
	public function appendByKey ( string $server_key , string $key , string $value ) {}
	
	/**
	 * @param float $cas_token
	 * @param string $key
	 * @param mixed $value
	 * @param integer $expiration [optional]
	 * @return boolean
	 */
	public function cas (float $cas_token, string $key , mixed $value , int $expiration = null ) {}
	
	/**
	 * @param float $cas_token
	 * @param string $server_key
	 * @param string $key
	 * @param mixed $value
	 * @param integer $expiration [optional]
	 * @return boolean
	 */
	public function casByKey (float $cas_token, string $server_key, string $key, mixed $value, int $expiration) {}
	
	/**
	 * @param integer $key
	 * @param integer $offset=1 [optional]
	 * @return integer
	 */
	public function decrement ( string $key, int $offset = 1 ) {}
	
	/**
	 * @param string $key
	 * @param integer $time=0 [optional]
	 * @return boolean
	 */
	public function delete ( string $key , int $time = 0 ) {}
	
	/**
	 * @param string $server_key
	 * @param string $key
	 * @param integer $time=0 [optional]
	 * @return boolean
	 */
	public function deleteByKey ( string $server_key , string $key , int $time = 0 ) {}
	
	/**
	 * @return array
	 */
	public function fetch () {}
	
	/**
	 * @return array
	 */
	public function fetchAll () {}
	
	/**
	 * @param integer $delay
	 * @return boolean
	 */
	public function flush ($delay = 0) {}
	
	/**
	 * @param string $key
	 * @param callback $cache_db [optional]
	 * @param float &$cas_token [optional]
	 * @return mixed
	 */
	public function get ( string $key , callback $cache_cb , float &$cas_token ) {}
	
	/**
	 * @param string $server_key
	 * @param string $key
	 * @param callback $cache_db [optional]
	 * @param float &$cas_token [optional]
	 * @return mixed
	 */
	public function getByKey ( string $server_key , string $key , callback $cache_cb , float &$cas_token ) {}
	
	/**
	 * @param array $keys
	 * @param boolean $with_cas [optional]
	 * @param callback $value_cb [optional]
	 * @return boolean
	 */
	public function getDelayed ( array $keys , bool $with_cas , callback $value_cb ) {}
	
	/**
	 * @param string $server_key
	 * @param array $keys
	 * @param boolean $with_cas [optional]
	 * @param callback $value_cb [optional]
	 * @return boolean
	 */
	public function getDelayedByKey ( string $server_key , array $keys , bool $with_cas , callback $value_cb ) {}
	
	/**
	 * @param array $keys
	 * @param array &$cas_tokens [optional]
	 * @param int $flags [optional]
	 * @return mixed
	 */
	public function getMulti ( array $keys , array &$cas_tokens , int $flags ) {}
	
	/**
	 * @param string $server_key
	 * @param array $keys
	 * @param string &$cas_tokens 
	 * @param integer $flags
	 * @return array
	 */
	public function getMultiByKey ( string $server_key , array $keys , string &$cas_tokens , int $flags  ) {}
	
	/**
	 * @return mixed
	 */
	public function getOption ( int $option ) {}
	
	/**
	 * @return integer
	 */
	public function getResultCode () {}
	
	/**
	 * @return string
	 */
	public function getResultMessage () {}
	
	/**
	 * @return array
	 */
	public function getServerByKey ( string $server_key ) {}
	
	/**
	 * @return array
	 */
	public function getServerList () {}
	
	/**
	 * @return array
	 */
	public function getStats () {}
	
	/**
	 * @return array
	 */
	public function getVersion () {}
	
	/**
	 * @param string $key
	 * @param integer $offset = 1 [optional]
	 * @return integer
	 */
	public function increment ( string $key , int $offset = 1 ) {}
	
	/**
	 * 
	 * @param string $key
	 * @param string $value
	 * @return boolean
	 */
	public function prepend ( string $key , string $value ) {}
	
	/**
	 * 
	 * @param string $server_key
	 * @param string $key
	 * @param string $value
	 * @return boolean
	 */
	public function prependByKey ( string $server_key , string $key , string $value ) {}
	
	/**
	 * @param string $key
	 * @param mixed $value,
	 * @param intger $expiration [optional]
	 * @return boolean
	 */
	public function replace ( string $key , mixed $value , int $expiration ) {}
	
	/**
	 * @param string $server_key
	 * @param string $key
	 * @param mixed $value
	 * @param integer $expiration [optinal]
	 * @return boolean
	 */
	public function replaceByKey ( string $server_key , string $key , mixed $value , int $expiration ) {}
	
	/**
	 * @param string $key
	 * @param mixed $value
	 * @param integer $expiration [optional]
	 * @return boolean
	 */
	public function set ( string $key , mixed $value , int $expiration ) {}
	
	/**
	 * @param string $server_key
	 * @param string $key
	 * @param mixed $value 
	 * @param integer $expiration [optional]
	 * @return boolean
	 */
	public function setByKey ( string $server_key , string $key , mixed $value , int $expiration ) {}
	
	/**
	 * @param array $items 
	 * @param integer $expiration [optional]
	 * @return boolean
	 */
	public function setMulti ( array $items , int $expiration ) {}
	
	/**
	 * @param string $server_key
	 * @param array $items
	 * @param integer $expiration [optional]
	 * @return boolean
	 */
	public function setMultiByKey ( string $server_key , array $items , int $expiration ) {}
	
	/**
	 * 
	 * @param integer $option
	 * @param mixed $value
	 * @return boolean
	 */
	public function setOption ($option, $value ) {}
	
}