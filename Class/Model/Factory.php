<?php

class Model_Factory 
{
	
	/**
	 * 
	 * @param string $model
	 * @param string $key
	 * @param array $object
	 * @return string
	 */
	public function delegateClass ($model, $key, $object)
	{
	    if (is_array ($object) && isset ($object ['name']))
	    {
		    return $model . '_' . $object ['name'];
	    }
	    
		return $model . '_' . DDS::execute (
		    Query::instance ()
		    ->select ('name')
		    ->from ($this->table ())
		    ->where ('id=?', $key)
		)->getResult ()->asValue ();
	}
	
	/**
	 * 
	 * @return string
	 */
	public function table ()
	{
		return get_class ($this);
	}
	
}