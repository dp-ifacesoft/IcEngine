<?php

/**
 * Профиль шифрования
 * 
 * @author goorus
 */
class Crypt_Profile extends Crypt_Abstract
{
	/**
	 * Метод шифрования
     * 
     * @author array
	 */
	protected $encode;
	
	/**
	 * Метод расшифрования
     * 
     * @author morph
	 */
	protected $decode;
	
	/**
	 * Конструктор
     * 
	 * @param array $encode Методы для шифрования.
	 * @param array $decode Методы для дешифрования.
	 */
	public function __construct($encode, $decode)
	{
		$this->encode = $encode ? (array) $encode : array();
		$this->decode = $decode ? (array) $decode : array();
	}
	
	/**
	 * @inheritdoc
	 */
	public function decode($input, $key = null)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $cryptManager = $serviceLocator->getService('cryptManager');
		foreach ($this->decode as $name) {
			$input = $cryptManager->decode($name, $input, $key);
		}
		return $input;
	}
	
	/**
	 * @inheritdoc
	 */
	public function encode($input, $key = null)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $cryptManager = $serviceLocator->getService('cryptManager');
		foreach ($this->encode as $name) {
			$input = $cryptManager->encod ($name, $input, $key);
		}
		return $input;
	}
	
}