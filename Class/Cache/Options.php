<?php
/**
 * 
 * @package IcEngine
 *
 */
class Cache_Options 
{
    
    /**
     * @desc Настройки
     * @var array
     */
	protected $_config = array (
    	/**
    	 * Время жизни кэша.
    	 * @var integer
    	 */
	    'expiration'	=> 0,
	    /**
	     * Ограничение по количеству использований кэша.
	     * @var integer
	     */
	    'hits'			=> 0
	);
	
	public function __construct ()
	{
	    
	}
	
	/**
	 * @desc Применение конфига
	 * @param Config_Abstract $config
	 * @return Cache_Options
	 */
	public function applyConfig (Config_Array $config)
	{
		$this->_config = $config->merge ($this->_config);
		return $this;
	}
	
	/**
	 * @desc Возвращает время жизни кэша.
	 * @return integer
	 */
	public function getExpiration ()
	{
		return $this->_config ['expiration'];
	}
	
	/**
	 * @desc Возвращает количество использований кэша.
	 * @return integer
	 */
	public function getHits ()
	{
	    return $this->_config ['hits'];
	}
    
	/**
	 * @desc Задает время жизни кэша.
	 * @param integer $value
	 * @return Cache_Options
	 */
	public function setExpiration ($value)
	{
		$this->_config ['expiration'] = $value >= 0 ? (int) $value : 0;
		return $this;
	}
	
	/**
	 * @desc Задает количество использований кэша.
	 * @param integer $value
	 * @return Cache_Options
	 */
	public function setHits ($value)
	{
	    $this->_config ['hits'] = $value > 0 ? (int) $value : 0;
	    return $this; 
	}
    
}