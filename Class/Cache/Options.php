<?php
/**
 * Опции кэширования
 * 
 * @author goorus
 */
class Cache_Options 
{
    
    /**
     * Конфигурация
     * 
     * @var array
     */
	protected $config = array(
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
	
	/**
	 * Применение конфига
	 * 
     * @param Config_Abstract $config
	 * @return Cache_Options
	 */
	public function applyConfig(Config_Array $config)
	{
		$this->config = $config->merge($this->config);
		return $this;
	}
	
	/**
	 * Возвращает время жизни кэша.
	 * 
     * @return integer
	 */
	public function getExpiration()
	{
		return $this->config['expiration'];
	}
	
	/**
	 * Возвращает количество использований кэша.
	 * 
     * @return integer
	 */
	public function getHits()
	{
	    return $this->config['hits'];
	}
    
	/**
     * Задает время жизни кэша.
	 * 
     * @param integer $value
	 * @return Cache_Options
	 */
	public function setExpiration($value)
	{
		$this->config['expiration'] = $value >= 0 ? (int) $value : 0;
		return $this;
	}
	
	/**
	 * Задает количество использований кэша.
	 * 
     * @param integer $value
	 * @return Cache_Options
	 */
	public function setHits($value)
	{
	    $this->config['hits'] = $value > 0 ? (int) $value : 0;
	    return $this; 
	}
    
}