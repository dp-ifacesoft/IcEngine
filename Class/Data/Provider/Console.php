<?php
/**
 * 
 * @desc Провайдер данных для получения параметров из консоли.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Provider_Console extends Data_Provider_Abstract
{
    
    /**
     * @desc Разобранные параметры консоли.
     * @var array
     */
    protected $_args;
    
    /**
     * @desc Создает и возвращает провайдер.
     * @param array $args Параметры консоли.
     */
    public function __construct (array $args)
    {
        foreach ($args as $arg)
        {
            $p = strpos ($arg, '=');
            if ($p)
            {
                $key = substr ($arg, 0, $p);
                $this->_args [$key] = substr ($arg, $p + 1);
            }
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see Data_Provider_Abstract::get()
     */
	public function get ($key, $plain = false)
	{
		return isset ($this->_args [$key]) ? $this->_args [$key] : null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::getAll()
	 */
	public function getAll ()
	{
		return $this->_args;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Provider_Abstract::set()
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		echo "$key => $value\n";
	}
    
}