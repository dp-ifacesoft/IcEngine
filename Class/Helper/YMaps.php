<?php
/**
 *
 * @desc Хелпер для работы с яндекс картами.
 * @author Yury Shvedov
 * @package IcEngine
 * @Service("helperYMaps")
 */
class Helper_YMaps
{
	/**
	 * @desc Получить названия ближайших метро
	 * @param type $key
	 * @param type $point
	 * @param type $limit
	 */
	public static function closestMetros ($key, $point, $limit)
	{
		$params = array (
			'key'		=> $key,
			'geocode'	=> $point['long'] . ',' . $point['lat'],
			'results'	=> $limit
		);

        $response = file_get_contents (
			'http://api-maps.yandex.ru/modules/1.1/metro/src/xml/closest.xml?' .
			http_build_query ($params)
		);

		if (!$response)
		{
			return null;
		}

        // яндекс возвращает JS-коллбек, выдираем из него вызовы 
        // функции GR с описанием ближайших метро
        preg_match_all('/GR\(\[.+?\}{5}\)/', $response, $matches);

        $closest_metros = array();

        if (sizeof($matches) > 0)
        {
            foreach ($matches[0] as $match)
            {
                // ищем координаты
                preg_match('/GR\(\[([\.0-9]+),([\.0-9]+)\]/', $match, $founds);

                str_replace(',', '.', $founds[1]);
                str_replace(',', '.', $founds[2]);

                $coordX =  + $founds[1];
                $coordY =  + $founds[2];

                // линия и станция метро
                preg_match('/ThoroughfareName:\'(.+?)\'.+PremiseName:\'(.+?)\'/', $match, $founds);
                $metro_line = $founds[1];
                $metro_station = $founds[2];

                $closest_metros[] = array(
                    'coordX' => $coordX,
                    'coordY' => $coordY,
                    'line' => $metro_line,
                    'station' => $metro_station,
                );
            }
        }

        return $closest_metros;
        
//		$matches = array ();
//		preg_match_all (
//			"#метро ([^']+)#u",
//			$response,
//			$matches
//		);
//
//		$result = array ();
//		if (!empty ($matches [1][0]))
//		{
//			foreach ($matches [1] as $metro)
//			{
//				$result [] = trim ($metro);
//			}
//		}
//		return array_values (array_unique ($result));
	}

	/**
	 * @desc Находит положение по названию (адресу)
	 * @param string $key
	 * @param string $address
	 * @param integer $limit
	 * @param boolean $only_pos
	 * @return stdClass|array|null Объект, содержащий данные о точке.
	 * Позицию можно получить
	 */
	public static function geocodePoint ($key, $address, $limit = 1,
		$only_pos = false)
	{
		$params = array (
			'geocode'	=> $address,		// адрес
			'format'	=> 'json',			// формат ответа
			'results'	=> $limit,			// количество выводимых результатов
			'key'		=> $key,			// ваш api key
		);
		$response = json_decode (file_get_contents (
			'http://geocode-maps.yandex.ru/1.x/?' .
			http_build_query ($params)
		));

		if (isset ($response->error) && $response->error)
		{
			return null;
		}

		if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0)
		{
			$r = $response->response->GeoObjectCollection->featureMember [0];
			return
				$only_pos
				? explode (' ', (string) $r->GeoObject->Point->pos)
				: $r;
		}

		return null;
	}

	/**
	 * @desc Фукнция возвращает расстояние между двумя точками.
	 * Функция взята из YMaps.GeoCoordSystem
	 * @param array $A Координаты первой точки в градусах
	 * $A [0] широта (longitude), $a долгота (latitude)
	 * @param array $z
	 * $z [0] широта (longitude), $z долгота (latitude)
	 * @return float
	 */
	public static function distance ($A, $z)
	{
		$AgetX = $A [0];
		$AgetY = $A [1];

		$zgetX = $z [0];
		$zgetY = $z [1];

		$B = pi () / 180;
		$x = $AgetX * $B;
		$v = $AgetY * $B;
		$w = $zgetX * $B;
		$u = $zgetY * $B;
		$y = 0;

		$this_epsilon = 1e-10;
		$this_radius = 6378137;
		$this_e2 = 0.00669437999014;

		if (
			!(
				Abs ($u - $v) < $this_epsilon &&
				Abs ($x - $w) < $this_epsilon
			)
		)
		{
			$C = cos (($v + $u) / 2);
			$t = $this_radius * sqrt ((1 - $this_e2) / (1 - $this_e2 * $C * $C));
			$y =
				$t * acos (
					sin ($v) * sin ($u) +
					cos ($v) * cos ($u) * cos ($w - $x)
				);
		}

		return $y;
	}

	/**
	 * @desc Альтернативный метод расчета расстояния.
	 * Функция найдена на просторах инета, в целом результаты не сильно
	 * отличаются от функции яндекса.
	 * @param float $long1
	 * @param float $lat1
	 * @param float $long2
	 * @param float $lat2
	 * @return float
	 */
	public static function distanceAlt ($long1, $lat1, $long2, $lat2)
	{
		static $D2R = 0.017453;
		static $R2D = 57.295781;
		static $a  = 6378137.0;
		static $b  = 6356752.314245;
		static $e2 = 0.006739496742337;
		static $f  = 0.003352810664747;

		$fdLambda = ($long1 - $long2) * $D2R;
		$fdPhi  = ($lat1 - $lat2) * $D2R;

		$fPhimean = (($lat1 + $lat2) / 2.0) * $D2R;

		$fTemp  = 1 - $e2 * (pow (sin ($fPhimean), 2));
		$fRho  = ($a * (1 - $e2)) / pow ($fTemp, 1.5);
		$fNu  = $a / (
			sqrt (1 - $e2 *
			pow (sin ($fPhimean), 2))
		);

		$fz   = sqrt (
			pow (sin ($fdPhi / 2.0), 2) + cos ($lat2 * $D2R) *
			cos ($lat1 * $D2R) * pow (sin ($fdLambda / 2.0), 2)
		);
		$fz   = 2 * asin ($fz);

		$fAlpha  = cos ($lat2 * $D2R) * sin ($fdLambda) * 1 / sin ($fz);
		$fAlpha  = asin ($fAlpha);

		$fR   =
			($fRho * $fNu) / (
				($fRho * pow (sin ($fAlpha), 2)) +
				($fNu * pow (cos ($fAlpha), 2))
			);

		return $fz * $fR;
	}

	/**
	 * @desc Получение с расписани яндекса времени в пути.
	 * @param string $from Город отправления
	 * @param string $to Город прибытия
	 * @param array $options Параметры поиска
	 * $options ['type'] Типы: null - все, place - авиа, train - поезд
	 * $options ['when'] дата отправления
	 * @return array Массив с вариантами в формате
	 *  array (рейс, отправление, прибытие, в пути, дни курсирования)
	 */
	public static function timeInWay ($from, $to, $options)
	{
		$options = array_merge (
			array (
				'type'	=> null,
				'when'	=> 'на все дни'
			),
			$options
		);

		$url =
			'http://rasp.yandex.ru/search/' .
			($options ['type'] ? $options ['type'] . '/' : '') .
			'?toName=' . urlencode ($to) .
			//'&toId=c213' .
			'&fromName=' . urlencode ($from) .
			//'&fromId=' .
			'&when=' . urlencode ($options ['when']);

		$html = file_get_contents ($url);

		echo "$url\n";

		return self::parseYandexRasp ($html);
	}

}
