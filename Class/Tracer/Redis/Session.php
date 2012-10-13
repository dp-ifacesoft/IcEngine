<?php
/**
 * 
 * @desc Трейсер для отслеживания удаления сессия из редиса.
 * @author Юрий
 * @package IcEngine
 *
 */
class Tracer_Redis_Session extends Tracer_Abstract
{
	
	/**
	 * @desc Файл для сохранения
	 * @var string
	 */
	public $file = 'log/trace.log';
	
	/**
	 * (non-PHPdoc)
	 * @see Tracer_Abstract::add()
	 */
	public function add ($info)
	{
		
		$text = Helper_Date::toUnix () . ' ' . Request::ip () . ' ' . $info;
		for ($i = 1; $i < func_num_args (); ++$i)
		{
			$text .= ' ' . json_encode (func_get_arg ($i));
		}
		
		if (
			strpos ($info, 'del') !== false &&
			class_exists ('User') &&
			class_exists ('Request') &&
			Request::ip () == '80.83.194.201'
		)
		{
			$text .= 
				"\nuser_id:" . User::id () . "\n" .
				"url:" . Request::uri () . "\n";
			
			ob_start ();
				debug_print_backtrace ();
				$text .= ob_get_contents ();
			ob_end_clean ();
		}
		
		$f = fopen ($this->file, 'a');
		fwrite ($f, $text . PHP_EOL);
		fclose ($f);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Tracer_Abstract::filter()
	 */
	public function filter ($filter)
	{
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Tracer_Abstract::full()
	 */
	public function full ()
	{
		return array ();
	}
	
}