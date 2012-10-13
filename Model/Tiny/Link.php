<?php
/**
 * 
 * @desc Модель для создания коротких адресов
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
class Tiny_Link extends Model
{
	
	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Афлавит, используемый при формировании короткой ссылки.
		 * @var string
		 */
		'abc'	=> '0123456789qwertyuiopasdfghjklzxcvbnmPOIUYTREWQLKJHGFDSAMNBVCXZ_-',
		/**
		 * @desc Адрес, где находится редирктор.
		 * Этот шаблон используется для формирования полного адреса
		 * короткой ссылки, на место {$short} будет подставлен код короткой
		 * ссылки.
		 * @var string
		 */
		'redirectorUrl' => '/r/{$short}'
	);
	
	/**
	 * @desc Конвертирование числа в систему счисления согласно заданному 
	 * в конфигах алфавиту.
	 * длинне алфавита
	 * @param integer $int Число 
	 * @return string Число в системе счисления с основанием равной
	 * длинне алфавита с символами из этого алфавита.
	 */
	public static function intEncode ($int)
	{
		$abc = self::config ()->abc;
		$base = strlen ($abc);
		
		$result = '';
		while ($int > 0)
		{
			$result .= $abc [$int % $base];
			$int = (int) ($int / $base);
		}
		
		return $result;
	}
	
	/**
	 * @desc Декодирование числа.
	 * Декодирует число, полученное intEncode.
	 * @param string $short Закодированное число
	 * @return integer Исходное числа в десятичной системе счисления. 
	 */
	public static function intDecode ($short)
	{
		$abc = array_flip (str_split (self::config ()->abc));
		$base = count ($abc);
		
		$result = 0;
		for ($i = 0, $icount = strlen ($short); $i < $icount; ++$i)
		{
			$c = $short [$i];
			$result += pow ($base, $i) * $abc [$c];
		}
		
		return (int) $result;
	}
	
	/**
	 * @desc Получает модель короткой ссылки, если для такого адреса
	 * короткой ссылки не сущетсвует - создает ее.
	 * @param string $href Исходная ссылка
	 * @return Tiny_Link Модель короткой ссылки.
	 */
	public static function byLink ($href)
	{
		$link = Model_Manager::byQuery (
			__CLASS__,
			Query::instance ()
				->where ('link', $href)
		);
		
		if (!$link)
		{
			$link = new self (array (
				'short'	=> 'temp',
				'link'	=> $href
			));
			$link->save ();
			$link->update (array (
				'short'	=> self::intEncode ($link->key ())
			));
		}
		
		return $link;
	}
	
	/**
	 * @desc Получает модель короткой ссылки по хешу.
	 * @param string $short 
	 * @return Tiny_Link|null
	 */
	public static function byShort ($short)
	{
		$int = self::intDecode ($short);
		return Model_Manager::byKey (__CLASS__, $int);
	}
	
	/**
	 * @desc Возвращает полный адрес короткой ссылки.
	 * Предполагается, что по возвращаемому адресу будет нахоидься скрипт
	 * редиректора, который по короткой направит на реальную страницу.
	 * @return string Короткая ссылка.
	 */
	public function shortLink ()
	{
		return str_replace (
			'{$short}',
			$this->short,
			$this->config ()->redirectorUrl
		);
	}
	
}
