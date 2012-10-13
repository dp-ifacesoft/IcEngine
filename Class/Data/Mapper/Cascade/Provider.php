<?php
/**
 *
 * @desc
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Mapper_Cascade_Redis extends Data_Mapper_Cascade_Abstract
{

	/**
	 * @desc
	 * @var Data_Provider_Abstract
	 */
	protected $_provider;

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Cascade_Abstract::lock()
	 */
	public function lock ($key)
	{
		$this->_provider->lock ($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::setOption()
	 */
	public function setOption ($key, $value = null)
	{
		switch ($key)
		{
			case 'provider':
				$this->setProvider (Data_Provider_Manager::get ($value));
				return true;
		}
		return false;
	}

	/**
	 *
	 * @param Data_Provider_Abstract $provider
	 * @return Data_Mapper_Provider
	 */
	public function setProvider (Data_Provider_Abstract $provider)
	{
		$this->_provider = $provider;
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Cascade_Abstract::unlock()
	 */
	public function unlock ($key, Query_Abstract $query, $options, Query_Result $result)
	{
		$this->_provider->set (
			$key,
			array (
				'v' => $result->asTable(),
				'a' => time (),
				't' => $this->_provider->getTags ($tags),
				'f'	=> $result->foundRows ()
			)
		);

		$this->_provider->unlock ($key);
	}

}