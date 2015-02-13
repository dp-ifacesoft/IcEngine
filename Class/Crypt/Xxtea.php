<?php
/**
 * 
 * @desc XXTEA 
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Crypt_Xxtea extends Crypt_Abstract
{
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $v
	 * @param unknown_type $w
	 */
	protected static function _long2str ($v, $w)
	{
	    $len = count($v);
	    $n = ($len - 1) << 2;
	    if ($w) {
	        $m = $v[$len - 1];
	        if (($m < $n - 3) || ($m > $n)) return false;
	        $n = $m;
	    }
	    $s = array();
	    for ($i = 0; $i < $len; $i++)
	    {
	        $s[$i] = pack("V", $v[$i]);
	    }
	    if ($w) {
	        return substr(join('', $s), 0, $n);
	    } else {
	        return join('', $s);
	    }
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $s
	 * @param unknown_type $w
	 */
	protected static function _str2long($s, $w)
	{
	    $v = unpack ("V*", $s. str_repeat("\0", (4 - strlen($s) % 4) & 3));
	    $v = array_values($v);
	    if ($w) {
	        $v[count($v)] = strlen($s);
	    }
	    return $v;
	}
	
	/**
	 * @desc 
	 * @param integer $n
	 * @return integer
	 */
	protected static function _int32 ($n)
	{
	    while ($n >= 2147483648) $n -= 4294967296;
	    while ($n <= -2147483649) $n += 4294967296;
	    return (int) $n;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::decode()
	 */
	public function decode ($input, $key = null)
	{
	    if ($input == '')
	    {
	        return "";
	    }
	    $v = self::_str2long($input, false);
	    $k = self::_str2long($key, false);
	    if (count($k) < 4) {
	        for ($i = count($k); $i < 4; $i++) {
	            $k[$i] = 0;
	        }
	    }
	    $n = count($v) - 1;
	
	    $z = $v[$n];
	    $y = $v[0];
	    $delta = 0x9E3779B9;
	    $q = floor(6 + 52 / ($n + 1));
	    $sum = self::_int32($q * $delta);
	    while ($sum != 0) {
	        $e = $sum >> 2 & 3;
	        for ($p = $n; $p > 0; $p--) {
	            $z = $v[$p - 1];
	            $mx = self::_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
	            $y = $v[$p] = self::_int32($v[$p] - $mx);
	        }
	        $z = $v[$n];
	        $mx = self::_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
	        $y = $v[0] = self::_int32($v[0] - $mx);
	        $sum = self::_int32($sum - $delta);
	    }
	    return self::_long2str($v, true);
	}

	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::encode()
	 */
	public function encode ($input, $key = null) {
	    if ($input == "")
	    {
	        return "";
	    }
	    $v = self::_str2long($input, true);
	    $k = self::_str2long($key, false);
	    if (count($k) < 4) {
	        for ($i = count($k); $i < 4; $i++) {
	            $k[$i] = 0;
	        }
	    }
	    $n = count($v) - 1;
	
	    $z = $v[$n];
	    $y = $v[0];
	    $delta = 0x9E3779B9;
	    $q = floor(6 + 52 / ($n + 1));
	    $sum = 0;
	    while (0 < $q--) {
	        $sum = self::_int32($sum + $delta);
	        $e = $sum >> 2 & 3;
	        for ($p = 0; $p < $n; $p++) {
	            $y = $v[$p + 1];
	            $mx = self::_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
	            $z = $v[$p] = self::_int32($v[$p] + $mx);
	        }
	        $y = $v[0];
	        $mx = self::_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
	        $z = $v[$n] = self::_int32($v[$n] + $mx);
	    }
	    return self::_long2str($v, false);
	}
	
}