<?php
/**
 * 
 * @desc Quine http://ru.wikipedia.org/wiki/Куайн_(программирование)
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
class Quine
{
	
	const SRC = '<?php
/**
 * 
 * @desc Quine http://ru.wikipedia.org/wiki/Куайн_(программирование)
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
class Quine
{
	
	const SRC = ;
	
	const POS = 186;
	
	public static function get ()
	{
		return
			substr (self::SRC, 0, self::POS) . 
			chr (39) . self::SRC . chr (39) .
			substr (self::SRC, self::POS);
	}
	
}

echo Quine::get ();';
	
	const POS = 186;
	
	public static function get ()
	{
		return 
			substr (self::SRC, 0, self::POS) . 
			chr (39) . self::SRC . chr (39) .
			substr (self::SRC, self::POS);
	}
	
}

echo Quine::get ();