<?php
/**
 * 
 * @desc Шифрование методом двойного квадрата.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Crypt_Double_Square extends Crypt_Abstract
{
	
	/**
	 * @desc Обратимое шифрование методом "Двойного квадрата".
	 * (Reversible crypting of "Double square" method)
	 * @param string $input Строка с исходным текстом
	 * @param boolean $decrypt Флаг для дешифрования
	 * @return string Строка с результатом шифрования|дешифрования
	 * @author runcore
	 */
	protected static function _dsCrypt ($input, $decrypt = false)
	{
		$o = $s1 = $s2 = array (); // Arrays for: Output, Square1, Square2
		// формируем базовый массив с набором символов
		$basea = array ('?','(','@',';','$','#',"]","&",'*');  // base symbol set
		$basea = array_merge ($basea, range('a','z'), range('A','Z'), range(0,9) );
		$basea = array_merge ($basea, array('!',')','_','+','|','%','/','[','.',' ') );
		$dimension = 9; // of squares
		for ($i = 0; $i < $dimension; ++$i)
		{
			// create Squares
			for ($j = 0; $j < $dimension; ++$j)
			{
				$s1 [$i][$j] = $basea [$i * $dimension + $j];
				$s2 [$i][$j] = str_rot13 ($basea[($dimension*$dimension-1) - ($i*$dimension+$j)]);
			}
		}
		unset ($basea);
		$m = floor (strlen ($input) / 2) * 2; // !strlen%2
		$symbl = $m == strlen ($input) ? '' : $input [strlen ($input) - 1]; // last symbol (unpaired)
		$al = array ();
		// crypt/uncrypt pairs of symbols
		for ($ii=0; $ii < $m; $ii += 2) {
			$symb1 = $symbn1 = strval ($input [$ii]);
			$symb2 = $symbn2 = strval ($input [$ii + 1]);
			$a1 = $a2 = array();
			for($i=0;$i<$dimension;$i++) { // search symbols in Squares
				for($j=0;$j<$dimension;$j++) {
					if ($decrypt) {
						if ($symb1===strval($s2[$i][$j]) ) $a1=array($i,$j);
						if ($symb2===strval($s1[$i][$j]) ) $a2=array($i,$j);
						if (!empty($symbl) && $symbl===strval($s2[$i][$j])) $al=array($i,$j);
					}
					else {
						if ($symb1===strval($s1[$i][$j]) ) $a1=array($i,$j);
						if ($symb2===strval($s2[$i][$j]) ) $a2=array($i,$j);
						if (!empty($symbl) && $symbl===strval($s1[$i][$j])) $al=array($i,$j);
					}
				}
			}
			if (sizeof($a1) && sizeof($a2)) {
				$symbn1 = $decrypt ? $s1[$a1[0]][$a2[1]] : $s2[$a1[0]][$a2[1]];
				$symbn2 = $decrypt ? $s2[$a2[0]][$a1[1]] : $s1[$a2[0]][$a1[1]];
			}
			$o [] = $symbn1 . $symbn2;
		}
		if (!empty ($symbl) && sizeof ($al)) // last symbol
		{
			$o [] = $decrypt ? $s1 [$al[1]][$al[0]] : $s2 [$al[1]][$al[0]];
		}
		return implode ('', $o);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::encode()
	 */
	public function decode ($input, $key = null)
	{
		return self::_dsCrypt ($input, true);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::encode()
	 */
	public function encode ($input, $key = null)
	{
		return self::_dsCrypt ($input);
	}
	
}