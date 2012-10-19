<?php

abstract class Cmd_Script
{

    /**
     * Ключ для запуска скрипта
     * @var string
     */
	const SECRET = 'secret';

	/**
	 *
	 * @param array $args
	 * @return mixed
	 */
	abstract protected function _work (array $args);

	/**
	 * Запуск скрипта на выполнение.
	 * @param string $class
	 * 		Название скрипта.
	 * @return Cmd_Script
	 */
	public static function run ($class, $args = null)
	{
		$script = new $class ();
		return $script->work (!is_null ($args) ? $args : $argv);
	}

	/**
	 *
	 * @param array $args
	 * @return mixed
	 */
	public function work (array $args)
	{
		if (count ($args) < 2)
		{
			die ('Secret not received.');
		}

		if (self::SECRET != substr ($args [1], 0, strlen (self::SECRET)))
		{
		    die ('Secret incorrect.');
		}

		return $this->_work ($args);
	}

}