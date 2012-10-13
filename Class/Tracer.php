<?php
/**
 *
 * @desc Трейсер
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Tracer
{
	/**
	 * @desc Состояние трейсера.
	 * @var boolean
	 */
	public static $enabled = true;

	/**
	 * @desc Сливать ли в файл для каждой
	 * @var type
	 */
	public static $flushPerSession = true;

	/**
	 * @desc
	 * @var array
	 */
	public static $sessions = array ();

	/**
	 * @desc
	 * @var integer
	 */
	public static $currentSession = 0;

	/**
	 * @desc Начало блока
	 */
	public static function begin ()
	{
		if (!self::$enabled)
		{
			return;
		}

		self::$currentSession++;

		self::$sessions [self::$currentSession] = array (
			'args'	=> func_get_args (),
			'begin'	=> microtime (true),
			'logs'	=> array ()
		);
	}

	/**
	 * @desc Окончание блока
	 */
	public static function end ()
	{
		if (!self::$enabled)
		{
			return;
		}

		$args = func_get_args ();

		if ($args)
		{
			self::log ($args);
		}

		self::$sessions [self::$currentSession]['end'] = microtime (true);

		self::$currentSession--;

		if (self::$flushPerSession)
		{
			self::flush (
				self::$sessions [self::$currentSession + 1],
				self::$currentSession
			);
		}
	}

	/**
	 * @desc запись в лог метки
	 */
	public static function log ()
	{
		if (!self::$enabled)
		{
			return;
		}

		if (!isset (self::$sessions [self::$currentSession]['logs']))
		{
			self::$sessions [self::$currentSession]['logs'] = array ();
		}

		$time = microtime (true);

		$logs = self::$sessions [self::$currentSession]['logs'];

		$current_index = sizeof ($logs);

		$delta = $time - (
			isset ($logs [$current_index - 1])
				? $logs [$current_index - 1]['time']
				: self::$sessions [self::$currentSession]['begin']
			);

		self::$sessions [self::$currentSession]['logs'][] = array (
			'args'	=> func_get_args (),
			'time'	=> $time,
			'delta'	=> $delta
		);
	}

	/**
	 * @desc Вывод полного лога
	 */
	public static function flushAll ()
	{
		if (!self::$enabled)
		{
			return;
		}

		$file_name = IcEngine::root () . 'log/tracer';
		file_put_contents ($file_name, '');

		foreach (self::$sessions as $session)
		{
			self::flush ($session);
		}
	}

	/**
	 * @desc Вывод
	 * @param array $session
	 * @param integer $offset [optional] Смещение
	 */
	public static function flush ($session, $offset = 0)
	{
		if (!self::$enabled)
		{
			return;
		}

		$file_name = IcEngine::root () . 'log/tracer';

		$offset = $offset
			? str_repeat ("\t", $offset)
			: '';

		$output  =
			$offset . 'Begin: ' . date ('Y-m-d H:i:s', $session ['begin']) .
			PHP_EOL .
			$offset . 'End: ' . date ('Y-m-d H:i:s', $session ['end']) .
			PHP_EOL .
			$offset . 'Delta: ' . round ($session ['end'] - $session ['begin'], 4) .
			PHP_EOL .
			$offset . 'Args: ' . json_encode ($session ['args']) .
			PHP_EOL .
			$offset . 'Logs: ' . PHP_EOL;

		foreach ($session ['logs'] as $i => $log)
		{
			$output .= $offset . '#' . $i . ' '.
				round ($log ['time'], 4) . ' ' .
				round ($log ['delta'], 4) . ' ' .
				json_encode ($log ['args']) . PHP_EOL;
		}

		file_put_contents ($file_name, $output, FILE_APPEND);
	}
}