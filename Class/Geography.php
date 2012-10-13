<?php
	
class Geography
{
	/**********************************************************************
	 *         North
	 *          С.Ш.          (B - широта,  latitude)
	 *           ^
	 *           |
	 *  Ю.Д. ----+---> В.Д.   (L - долгота, longitude)
	 *  West     |     East
	 *           |
	 *          Ю.Ш.
	 *         South
	 **********************************************************************/
	
	// умножить на коэф. для перевода радиан в градусы
	const RAD_DEG = 57.29577951;//180 / Math.PI;
	
	// умножить на коэф. для перевода градусов в радианы
	const DEG_RAD = 0.017453293;//Math.PI / 180;
	
	/**********************************************************************
	 *  Перевод из градусов, минут, секунд в градусы и назад
	 **********************************************************************/
	
	// первая мат функция
	public static function tileToMercator ( $d )
	{
		return array (
			'x'	=> round($d['x'] / 53.5865938 - 20037508.342789),
			'y'	=> round(20037508.342789 - $d['y'] / 53.5865938)
		);
	}
	
	// вторая мат функция
	public static function mercatorToGeo ($p)
	{
		// Предвычисленные коэффициенты согласно WGS84
		$ab = 0.00335655146887969400;
		$bb = 0.00000657187271079536;
		$cb = 0.00000001764564338702;
		$db = 0.00000000005328478445;
		$Rn = 6378137;
       
		$xphi = pi() * 0.5 - 2 * atan(1 / exp($p['y'] / $Rn));
       
		$latitude = 
			$xphi + 
			$ab * sin(2 * $xphi) + 
			$bb * sin(4 * $xphi) + 
			$cb * sin(6 * $xphi) + 
			$db * sin(8 * $xphi);
			
		$longitude = $p['x'] / $Rn;
       
		return array(
			'x'	=> $longitude * 180 / pi(), 
			'y'	=> $latitude * 180 / pi()
		);
		//return new YMaps.GeoPoint(longitude * 180 / Math.PI, latitude * 180 / Math.PI, true);
	}
	
	//третья мат функция
//	public static function fromLatLngToPixel ($a, $b)
//	{
//		$c = 256 * pow(2, $b);
//		$Mw = $c / 2;
//		$Ow = $c / 360;
//		$Pw = $c / (2 * pi());
//           
//		$x = round($Mw + a.getLng() * Ow);
//            var y = Math.min(Math.max(Math.sin(a.getLat() / 180 * Math.PI), -0.9999), 0.9999);
//            y = Mw + 0.5 * Math.log((1 + y) / (1 - y)) * (-Pw);
//            y = Math.round(y);
//           
//            return new YMaps.Point(x, y);
//	}

	/**
	 * 
	 * @param float $deg
	 * @param float $min
	 * @param float $sec
	 * @return float
	 */
	public static function dms2dd($deg, $min, $sec)
	{
		return $deg + $min / 60 + $sec / 3600;
	}
	
	/**
	 * 
	 * @param float $deg
	 * @return array
	 */
	public static function dd2dms($deg)
	{
		//var min:Number = (deg % 1) * 60; ?
		$min = ($deg - (int) $deg) * 60;
		
		return array((int) deg, (int) $min, ($min - (int) $min) * 60);
	}
	
	
	
	/**********************************************************************
	 *  Проекция:  Гаусса-Крюгера
	 *  Эллипсоид: Красовского
	 *    proj +proj=tmerc +lon_0=33 +x_0=6500000 +ellps=krass
	 *    (пример для 6-й зоны: lon_0 = 6 * n - 3; x_0 = (5 + 10 * n) * 100000)
	 * 
	 *  ВНИМАНИЕ: Х направлен вверх, а У вправо!
	 **********************************************************************/
	/**
	 * 
	 * @param float $B
	 * @param float $L
	 * @param int $zone
	 * @return array
	 */
	public static function bl2xy($B, $L, $zone = -1)
	{
		$b1 = $B * self::DEG_RAD;
		
		// номер шестигранной зоны в проекции Гаусса-Крюгера
		$n = ($zone >= 0) ? $zone : ((int) (6 + $L) / 6);
		
		// средний меридиан (согласно номеру зоны)
		$l0 = ( $L - (6 * $n - 3) ) * self::DEG_RAD;

		$sinB = sin($b1);
		$sinB2 = $sinB * $sinB;
		$sinB4 = $sinB2 * $sinB2;
		$sinB6 = $sinB4 * $sinB2;
		
		$l2 = $l0 * $l0;
		
		$x = 6367558.4698 * $b1 
			- sin(2 * $b1) * (16002.89 + 66.9607 * $sinB2 + 0.3515 * $sinB4 
			- $l2 * (1594561.25 + 5336.535 * $sinB2 + 26.79  * $sinB4 + 0.149  * $sinB6 
			+ $l2 * (672483.4   - 811219.9 * $sinB2 + 5420   * $sinB4 - 10.6   * $sinB6 
			+ $l2 * (278194     - 830174   * $sinB2 + 572434 * $sinB4 - 16010  * $sinB6 
			+ $l2 * (109500     - 574700   * $sinB2 + 863700 * $sinB4 - 398600 * $sinB6 )))));
			
		$y = (5 + 10 * $n) * 100000.0 
			+ $l0 * cos($b1) * (6378245 + 21346.1415 * $sinB2 + 107.159 * $sinB4 + 0.5977 * $sinB6 
			+ $l2 * (1070204.16 - 2136826.66 * $sinB2 + 17.98   * $sinB4 - 11.99  * $sinB6
			+ $l2 * (270806     - 1523417    * $sinB2 + 1327645 * $sinB4 - 21701  * $sinB6 
			+ $l2 * (79690      - 866190     * $sinB2 + 1730360 * $sinB4 - 945460 * $sinB6 ))));
			
		return array($x, $y);
	}
	

	/**
	 * 
	 * @param float $x
	 * @param float $y
	 * @param int $zone
	 * @return array
	 */
	public static function xy2bl($x, $y, $zone = -1)
	{
		// номер шестигранной зоны в проекции Гаусса-Крюгера
		$n = ($zone >= 0) ? $zone : ((int) ($y / 1000000));
		
		// вспомогательная величина
		$a = $x / 6367558.4968;
		
		$sina = sin($a);
		$sina2 = $sina * $sina;
		$sina4 = $sina2 * $sina2;
		
		// геодезическая широта точки, абцисса которой равна абциссе x
		// определяемой точки, а ордината равна нулю, радs
		$b = $a + sin(2 * $a) * (0.00252588685 
			- 0.00001491860 * $sina2 + 0.00000011904 * $sina4);
		
		$sinb = sin($b);
		$sinb2 = $sinb * $sinb;
		$sinb4 = $sinb2 * $sinb2;
		$sinb6 = $sinb4 * $sinb2;
		
		// вспомогательная величина
		$z = ($y - (10 * $n + 5) * 100000) / (6378245 * cos($b));
		$z2 = $z * $z;
		
		$dB = - $z2 * sin(2 * $b) 
			     * (0.251684631 - 0.003369263 * $sinb2 + 0.000011276 * $sinb4
			- $z2 * (0.10500614 - 0.04559916 * $sinb2 + 0.00228901 * $sinb4 - 0.00002987 * $sinb6
			- $z2 * (0.042858 - 0.025318 * $sinb2 + 0.014346 * $sinb4 - 0.001264 * $sinb6
			- $z2 * (0.01672 - 0.00630 * $sinb2 + 0.01188 * $sinb4 - 0.00328 * $sinb6))));
		
		$l = $z * (1 - 0.0033467108 * $sinb2 - 0.0000056002 * $sinb4 - 0.0000000187 * $sinb6
			- $z2 * (0.16778975 + 0.16273586 * $sinb2 - 0.00052490 * $sinb4 - 0.00000846 * $sinb6
			- $z2 * (0.0420025 + 0.1487407 * $sinb2 + 0.0059420 * $sinb4 - 0.0000150 * $sinb6
			- $z2 * (0.01225 + 0.09477 * $sinb2 + 0.03282 * $sinb4 + 0.00034 * $sinb6
			- $z2 * (0.0038 + 0.0524 * $sinb2 + 0.0482 * $sinb4 + 0.0032 * $sinb6)))));
		
		$B = $b + $dB;
		$L = 6 * ($n - 0.5) / self::RAD_DEG + $l;
		
		return array($B * self::RAD_DEG, $L * self::RAD_DEG);
	}
	
	
	
	/**********************************************************************
	 *  Проекция:  Меркатора
	 *  Эллипсоид: WGS84
	 *    proj +proj=merc +ellps=WGS84
	 **********************************************************************/
	const PI_2 = 1.570796327;
	const MAX_LAT = 89.5;
	
	const R_MAJOR = 6378137.0;
	const R_MINOR = 6356752.3142;
	const ECCENT = 0.081819191; //Math.sqrt(1 - Math.pow(R_MINOR / R_MAJOR, 2));
	const ECCNTH = 0.040909595; //ECCENT * 0.5;
	
	/**
	 * @param float $longitude
	 * @return float
	 */
	public static function merc_x($longitude)
	{
		return $longitude * self::DEG_RAD * self::R_MAJOR;
	}
	
	/**
	 * @param float $longitude
	 * @return float
	 */
	public static function unmerc_x($longitude)
	{
		return $longitude * self::RAD_DEG / self::R_MAJOR;
	}
	
	/**
	 * 
	 * @param float $latitude
	 * @return float
	 */
	public static function merc_wgs84_y($latitude)
	{
		if ($latitude >  self::MAX_LAT) $latitude =  self::MAX_LAT;
		if ($latitude < -self::MAX_LAT) $latitude = -self::MAX_LAT;
		
		$phi = $latitude * self::DEG_RAD;
		$con = self::ECCENT * sin($phi);
		$con = pow( (1.0 - $con) / (1.0 + $con), self::ECCNTH );
		
		return -self::R_MAJOR * log( tan(0.5 * (self::PI_2 - $phi)) / $con );
	}
	
	/**
	 * 
	 * @param float $y
	 * @return float
	 */
	public static function unmerc_wgs84_y($y)
	{
		$ts = exp(-$y / self::R_MAJOR);
		$phi = self::PI_2 - 2.0 * atan($ts);
		
		$i = 0;
		$dPhi = 1;
		
		while ( ($dPhi >= 0 ? $dPhi : -$dPhi) > 0.000000001 && $i++ < 15 )
		{
			$con = self::ECCENT * sin($phi);
			$dPhi = self::PI_2 - 2.0 * atan ($ts * pow((1.0 - $con) / 
				(1.0 + $con), self::ECCNTH)) - $phi;
			$phi += $dPhi;
		}
		
		return $phi * self::RAD_DEG; 
	}
	
	/**********************************************************************
	 *  Проекция:  Меркатора
	 *  Сфера:     ? (Google)
	 *    proj +proj=merc +a=6378137
	 **********************************************************************/
	/**
	 * @param float $latitude
	 * @return float
	 */
	public static function merc_sphere_y($latitude)
	{
		if ($latitude >  self::MAX_LAT) $latitude =  self::MAX_LAT;
		if ($latitude < -self::MAX_LAT) $latitude = -self::MAX_LAT;
		
		return -self::R_MAJOR * log( tan(0.5 * (self::PI_2 - $latitude * self::DEG_RAD)) );
	}
	
	/**
	 * 
	 * @param float $y
	 * @return float
	 */
	public static function unmerc_sphere_y($y)
	{
		return (self::PI_2 - 2.0 * atan( exp(-$y / self::R_MAJOR) )) * self::RAD_DEG; 
	}
	
}
