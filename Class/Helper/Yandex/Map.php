<?php

/**
 * Хелпер для работы с яндекс картами
 *
 * @author goorus, morph, neon
 * @Service("helperYandexMap")
 */
class Helper_Yandex_Map
{  
	/**
	 * Находит положение по названию (адресу)
	 *
     * @param string $key
	 * @param string $address
	 * @param integer $limit
	 * @param boolean $only_pos
	 * @return stdClass|array|null Объект, содержащий данные о точке.
	 * Позицию можно получить
	 */
	public function geocodePoint($address, $limit = 1, $onlyPos = false)
	{
		$params = array(
			'geocode'	=> $address,		// адрес
			'format'	=> 'json',			// формат ответа
			'results'	=> $limit			// количество выводимых результатов
		);
		$response = json_decode(file_get_contents(
			'http://geocode-maps.yandex.ru/1.x/?' .
			http_build_query($params)
		));
		if (!empty($response->error)) {
			return;
		}
        $collection = $response->response->GeoObjectCollection;
        if (!$collection) {
            return;
        }
		if ($collection->metaDataProperty->GeocoderResponseMetaData->found > 0)
		{
			$r = $response->response->GeoObjectCollection->featureMember[0];
			return $onlyPos
				? explode(' ', (string) $r->GeoObject->Point->pos)
				: $r;
		}
		return null;
	}

	/**
	 * Фукнция возвращает расстояние между двумя точками.
	 * Функция взята из YMaps.GeoCoordSystem
	 *
     * @param array $A Координаты первой точки в градусах
	 * $A [0] (longitude), $A[1] (latitude)
	 * @param array $z
	 * $Z [0] (longitude), $Z[1] (latitude)
	 * @return float
	 */
	public function distance($A, $z)
	{
		$AgetX = $A[0];
		$AgetY = $A[1];
		$zgetX = $z[0];
		$zgetY = $z[1];
		$B = pi () / 180;
		$x = $AgetX * $B;
		$v = $AgetY * $B;
		$w = $zgetX * $B;
		$u = $zgetY * $B;
		$y = 0;
		$thisEpsilon = 1e-10;
		$thisRadius = 6378137;
		$thisE2 = 0.00669437999014;
		if (!(abs($u - $v) < $thisEpsilon && abs($x - $w) < $thisEpsilon)) {
			$C = cos(($v + $u) / 2);
			$t = $thisRadius * sqrt((1 - $thisE2) / (1 - $thisE2 * $C * $C));
			$y = $t * acos(
                sin($v) * sin($u) + cos($v) * cos($u) * cos($w - $x)
            );
		}
		return $y;
	}

	/**
	 * Альтернативный метод расчета расстояния !!! работает не корректно !!! lucifer... 07/02/2014
     *
	 * Функция найдена на просторах инета, в целом результаты не сильно
	 * отличаются от функции яндекса.
	 * @param float $long1
	 * @param float $lat1
	 * @param float $long2
	 * @param float $lat2
	 * @return float
	 */
	public function distanceAlt($long1, $lat1, $long2, $lat2)
	{
		static $D2R = 0.017453;
		static $a   = 6378137.0;
		static $e2  = 0.006739496742337;
		$fdLambda = ($long1 - $long2) * $D2R;
        if ($fdLambda && $lat1 == $lat2) {
            return 0;
        }
		$fdPhi = ($lat1 - $lat2) * $D2R;
		$fPhimean = (($lat1 + $lat2) / 2.0) * $D2R;
		$fTemp = 1 - $e2 * (pow(sin($fPhimean), 2));
		$fRho = ($a * (1 - $e2)) / pow($fTemp, 1.5);
		$fNu = $a / (
			sqrt (1 - $e2 *
			pow(sin($fPhimean), 2))
		);
		$fz = sqrt(
			pow(sin($fdPhi / 2.0), 2) + cos($lat2 * $D2R) *
			cos($lat1 * $D2R) * pow(sin($fdLambda / 2.0), 2)
		);
		$fzSquarted = 2 * asin($fz);
		$fAlpha = cos($lat2 * $D2R) * sin($fdLambda) * 1 / sin($fzSquarted);
		$fAlphaPrepared = asin($fAlpha);
		$fR = ($fRho * $fNu) / (
            ($fRho * pow(sin($fAlphaPrepared), 2)) +
            ($fNu * pow(cos($fAlphaPrepared), 2))
        );
		return $fz * $fR;
	}
}