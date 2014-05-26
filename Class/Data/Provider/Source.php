<?php
/**
 * 
 * @desc Провайдер с хранением значений в БД.
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
class Data_Provider_Source extends Data_Provider_Abstract
{

	/**
	 * @desc Источник данных.
	 * @var Data_Source_Abstract
	 */
	protected $_source;
	
	protected $_config = array (
		/**
		 * 
		 * @var string
		 */
		'expiration_field'	=> 'expiration',
		/**
		 * @desc Если не указан срок истечения
		 * @var string
		 */
		'infinite_expiration'	=> '2030-01-01 00:00:00',
		/**
		 * @desc Поле ключа.
		 * @var string 
		 */
		'key_field'			=> 'id',
		/**
		 * @desc Таблица для хранения значений.
		 * @var string
		 */
		'table'				=> 'variable',
		/**
		 * @desc Поле с тегами.
		 * @var string
		 */
		'tags_field'		=> 'tags',
		/**
		 * @desc Поле со значением.
		 * @var string
		 */
		'value_field'		=> '`value`'
	);
	
	/**
	 * @see Data_Provider_Abstract::_setOption
	 */
	protected function _setOption ($key, $value)
	{
		if ($key == 'table')
		{
			$this->_config ['table'] = $value;
		}
		elseif ($key == 'source')
		{
			$this->_source =
				is_object ($value) ?
				$value :
				Data_Source_Manager::get ($value);
		}
		else
		{
			parent::_setOption ($key, $value);
		}
	}
	
	/**
     * @see Data_Source_Abstract::delete
 	 */
	public function delete ($keys, $time = 0, $set_deleted = false)
	{
		$keys = (array) $keys;
		
		$this->_source->execute (
			Query::instance ()
				->delete ()
				->from ($this->_config ['table'])
				->where ($this->_config ['key_field'] . ' IN (?)', $keys)
		);
	}
	
	/**
     * @see Data_Source_Abstract::deleteByPattern
 	 */
	public function deleteByPattern ($pattern, $time = 0, $set_deleted = false)
	{
		$pattern = $this->keyEncode ($pattern);
		$this->_source->execute (
			Query::instance ()
				->delete ()
				->from ($this->_config ['table'])
				->where ($this->_config ['key_field'] . ' LIKE ?' . $pattern)
		);
	}
	
	/**
	 * @see Data_Provider_Abstract::get
	 */
	public function get ($key, $plain = false)
	{
		return $this->_source->execute (
			Query::instance ()
				->select ($this->_config ['value_field'])
				->from ($this->_config ['table'])
				->where ($this->_config ['key_field'], $this->keyEncode ($key))
		)->getResult ()->asValue ();
	}
	
	/**
	 * @see Data_Provider_Abstract::getAll
	 */
	public function getAll ()
	{
		$pattern = $this->keyEncode ('') . '*';
		$r = $this->_source->execute (
			Query::instance ()
				->select (array (
					$this->_table => array (
						$this->_config ['value_field']	=> 'v',
						$this->_config ['tags_field']	=> 't'
					)
				))
				->from ($this->_config ['table'])
				->where ($this->_config ['key_field'] . ' LIKE ?' . $pattern)
				->where (
					$this->_config ['expiration_field'] . ' < ?',
					Helper_Date::toUnix ()
				)
		)->getResult ()->asColumn ();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getMulti()
	 */
	public function getMulti (array $keys, $numeric_index = false)
	{
		$keys_enc = array_map (
			array ($this, 'keyEncode'),
			$keys
		);
		
		$r = $this->_source->execute (
			Query::instance ()
				->select (array (
					$this->_config ['table'] => array (
						$this->_config ['key_field']	=> 'k',
						$this->_config ['value_field']	=> 'v',
						$this->_config ['tags_field']	=> 't'
					)
				))
				->from ($this->_config ['table'])
				->where (
					$this->_config ['key_field'] . ' IN (?)',
					$keys_enc
				)
				->where (
					$this->_config ['expiration_field'] . ' < ?',
					Helper_Date::toUnix ()
				)
		)->getResult ()->asTable ('k');
		
		$result = array ();
		if ($numeric_index)
		{
			foreach ($keys_enc as $k)
			{
				$result [] = 
					isset ($r [$k]) ?
					$r [$k] :
					null;
			}
		}
		else
		{
			foreach ($keys_enc as $i => $k)
			{
				$result [$keys [$i]] = 
					isset ($r [$k]) ?
					$r [$k] :
					null;
			}
		}
		
		return $result;
	}
	
	/**
	 * @see Data_Provider_Abstract::keys
	 */
	public function keys ($pattern)
	{
		$pattern = $this->keyEncode ($pattern) . '*';
		$r = $this->_source->execute (
			Query::instance ()
				->select ($this->_config ['key_field'])
				->from ($this->_config ['table'])
				->where ($this->_config ['key_field'] . ' LIKE ?' . $pattern)
				->where (
					$this->_config ['expiration_field'] . ' < ?',
					Helper_Date::toUnix ()
				)
		)->getResult ()->asColumn ();
		
		$result = array ();
		
		return array_map (array ($this, 'keyDecode'), $r);
	}
	
	/**
	 * @see Data_Provider_Abstract::set
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		$key = $this->keyEncode  ($key);
		
		$this->_source->execute (
			Query::instance ()
				->delete ()
				->from ($this->_config ['table'])
				->where ($this->_config ['key_field'], $key)
		);
		
		$this->_source->execute (
			Query::instance ()
				->insert ($this->_config ['table'])
				->values (array (
					$this->_config ['key_field']	=> $key,
					$this->_config ['value_field']	=> $value,
					$this->_config ['expiration_field']	=> 
						$expiration ?
						Helper_Date::toUnix (time () + $expiration) :
						$this->config ['infinite_expiration'],
					$this->_config ['tags_field']	=> ''
				))
		);
	}
	
}