<?php
/**
 * 
 * @desc Коллекция источников данных
 * @author Yury Shvedov, Ilya Kolesnikov
 * @package IcEngine
 * 
 */
class Data_Source_Collection
{
	
	/**
	 * 
	 * @var array <Data_Source_Abstract>
	 */
	private $_dataSources;
	
	/**
	 * 
	 * @var array
	 */
	private $_results;
	
	public function __construct ()
	{
		$this->_dataSources = (array) func_get_args ();
	}
	
	/**
	 * 
	 * @param string $name
	 * @param Data_Source_Abstract $source
	 */
	public function appendSource ($name, Data_Source_Abstract $source)
	{
		$this->_dataSources [$name] = $source;
		return $this;
	}
	
	/**
	 * 
	 * @param string $name
	 * @return Data_Source_Abstract
	 */
	public function byName ($name)
	{
		return isset ($this->_dataSources [$name]) ? $this->_dataSources [$name] : null;
	}
	
	/**
	 * @return 
	 */
	public function items ()
	{
		return $this->_dataSources;
	}
	
	/**
	 * 
	 * @param string $name
	 * @return Data_Source_Collection
	 */
	public function remove ($name)
	{
		if (isset ($this->_dataSources [$name]))
		{
			unset ($this->_dataSources [$name]);
		}
		return $this;
	}
	
	public function execute ($query, $options = null, $sources_names = null)
	{
		if (!$sources_names)
		{
			return;
		}
		$dataSources = $sources_names !== null ? $sources_names : array_keys ($this->_dataSources);
		
		$options = $options !== null ? $options : new Query_Options ();
		
		foreach ($dataSources as $name)
		{
			 $this->_result [$name] = $this->_dataSources [$name]->execute ($query, $options);
		}
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getResults ()
	{
		return $this->_results;
	}
	
	/**
	 * 
	 * @param string $name
	 * @return Query_Result
	 */
	public function getResult ($name)
	{
		return isset ($this->_results [$name]) ? $this->_results [$name] : null;
	}
}