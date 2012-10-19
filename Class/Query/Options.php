<?php

class Query_Options extends Cache_Options
{

	private $_tags;

	/**
	 *
	 * @var boolean
	 */
	private $_notEmpty;

	/**
	 *
	 * @var Query_Options
	 */
	private $_nocache;

	public function __construct ()
	{
	    parent::__construct ();
		$this->_tags = array ();
		$this->_notEmpty = false;
	}

	/**
	 *
	 * @return boolean
	 */
	public function getNotEmpty ()
	{
		return $this->_notEmpty;
	}

	/**
	 * @return array
	 */
	public function getTags ()
	{
		return $this->_tags;
	}

	/**
	 *
	 * @param array|string $tags
	 */
	public function setTags ($tags)
	{
		$this->_tags = (array) $tags;
		return $this;
	}

	/**
	 *
	 * @param boolean $empty
	 */
	public function setNotEmpty ($empty)
	{
		$this->_empty = (bool) $empty;
	}

	/**
	 * @return Query_Options
	 */
	public static function nocache ()
	{
	    if (!$this->_nocache)
	    {
	        $this->_nocache = new Query_Options ();
	    }
	    return $this->_nocache;
	}

}
