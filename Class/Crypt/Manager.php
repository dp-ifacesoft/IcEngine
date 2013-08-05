<?php

/**
 * Менеджер алгоритмов шифрования.
 * 
 * @author goorus
 * @Service("cryptManager")
 */
class Crypt_Manager extends Manager_Abstract
{
	/**
	 * Разделитель для провайла
	 * 
     * @var string
	 */
	const PROFILE_DELIMETER = '://';

	/**
	 * Загруженные алгоритмы шифрования.
	 * 
     * @var array
	 */
	protected $crypts = array();

	/**
	 * Автодекодирование строки.
	 * 
     * @param string $content
	 * @param string $key [optional]
	 * @return string Результат дешифрования.
	 */
	public function autoDecode($input, $key = null)
	{
		if (is_string($input)) {
			$p = strpos($input, self::PROFILE_DELIMETER);
			if ($p !== false) {
				$crypt = substr($input, 0, $p);
				$input = substr($input, $p + strlen(self::PROFILE_DELIMETER));
				return $this->decode($crypt, $input, $key);
			}
		}
		return $input;
	}

	/**
	 * Дешифрование указанными методом.
	 * 
     * @param string $crypt Метод шифрования.
	 * @param string $content
	 * @param string $key [optional]
	 * @return string
	 */
	public function decode($crypt, $input, $key = null)
	{
		return $this->get($crypt)->decode($input, $key);
	}

	/**
	 * Шифрование указанным методом.
	 * 
     * @param string $crypt
	 * @param string $input
	 * @param string $key
	 * @return string
	 */
	public function encode($crypt, $input, $key = null)
	{
		return $this->get($crypt)->encode($input, $key);
	}

	/**
	 * Возвращает экземпляр класс, реализующего алгоритм шифрования.
	 * 
     * @param string $name Название алгоритма шифрования.
	 * @return Crypt_Abstract
	 */
	public function get($name)
	{
		if (!isset($this->crypts[$name])) {
			$className = 'Crypt_' . $name;
            $crypt = new $className;
			$this->crypts[$name] = $crypt;
		}
		return $this->crypts[$name];
	}

	/**
	 * Проверяет соответсвует ли строка заданному значению
	 * 
     * @param string $input Строка.
	 * @param string $pattern Шаблон для сравнения.
	 * @param string $key [optional]
	 * @return boolean
	 */
	public function isMatch($input, $pattern, $key = null)
	{
		$p = strpos($pattern, self::PROFILE_DELIMETER);
		if ($p !== false) {
			$crypt = substr($pattern, 0, $p);
			$pattern = substr($pattern, $p + strlen(self::PROFILE_DELIMETER));
			return $this->encode($crypt, $input, $key) == $pattern;
		}
		return $input == $pattern;
	}
}