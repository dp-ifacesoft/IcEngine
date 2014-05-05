<?php
/**
 *
 * @desc Хелпер для работы с яндекс картами.
 * @author Yury Shvedov
 * @package IcEngine
 *
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
	 * @desc Парсинг страницы с расписанием яндекса на время вылета,
	 * прилета и продолжительности
	 * @param type $html
	 * @return array Массив с найденными результатами в формате
	 * array (
	 *		0 => направление (Москва - Новокузнецк)
	 *		1 => Время отправления
	 *		2 => Время прибытия
	 *		3 => Время в пути
	 * )
	 */
	public static function parseYandexRasp ($html)
	{
		$html = str_replace (array ("\r", "\n"), '', $html);
		//file_put_contents ('/var/1.txt', $html);
		$regexp = '#<tbody class="js-datetime-group">.+?' .
			'<div class="b-timetable__tripname">(.+?)</div>' . // рейс
			'.+?' .
			'<div class="b-timetable__time">(.+?)<strong[^>]*>(.+?)</strong>' . // время отпр
			'.+?' .
			'<div class="b-timetable__time">(.+?)<strong[^>]*>(.+?)</strong>' . // время приб
			'.+?' .
			'<div class="b-timetable__pathtime">(.+?)</div>' . // время в пути
			'.+?' .
			//'<div class="b-timetable__days [^>]*>(.+?)</div>' . // дни курсирования (может не быть, бывает цена в рублях)
			//'.+?' .
		'</tbody>#m';

		//$regexp = '#<tbody class="js-datetime-group">.+?<div class="b-timetable__tripname">(.+?)</div>.+?<div class="b-timetable__time">(.+?)<strong[^>]*>(.+?)</strong>.+?<div class="b-timetable__time">(.+?)<strong[^>]*>(.+?)</strong>.+?<div class="b-timetable__pathtime">(.+?)</div>.+?<div class="b-timetable__days [^>]*>(.+?)</div>.+?</tbody>#g';

//		array(7) {
//			[0]=> array(1) {
//				[0]=> string(12360) "<tbody class="js-datetime-group">                                <tr class="b-timetable__row {'seats': 'n', 'depTime': 'evening', 'carrier': '96', 'stationFrom': '9600216', 'arrTime': 'night', 'stationTo': '9623569'}">                    <td class="b-timetable__column b-timetable__column_type_ico b-timetable__column_position_first"><a class="b-link" href="/thread/7R-4101A_c96_agent/?station_to=9623569&point_from=c213&departure=2011-07-20&point_to=c237&station_from=9600216"><span class="b-transico b-transico_type_plane"><img class="b-transico__i"                                                                                       src="http://static.rasp.yandex.net/2.6.36/blocks/b-transico/b-transico.png"                                                                                       alt=""/><img class="b-transico__print" src="http://static.rasp.yandex.net/2.6.36/blocks/b-transico/b-transico__plane.png" alt=""/></span></a></td>                <td class="b-timetable__column b-timetable__column_type_trip">                                                                                             <div class="b-timetable__tripname">            <a class="b-link" href="/thread/7R-4101A_c96_agent/?station_to=9623569&point_from=c213&departure=2011-07-20&point_to=c237&station_from=9600216"><strong>7R 4101</strong> <span class="g-nowrap">Москва</span> — <span class="g-nowrap">Новокузнецк</span></a>        </div>        <div class="b-timetable__description">            Airbus А320, <a class="b-link" href="/info/company/96">Руслайн</a>                                        <img alt="Электронный билет" src="http://static.rasp.yandex.net/2.6.36/i/eticket.png" class="b-ico b-ico_type_eticket js-et-marker g-hidden">                    </div>                                                                </td>                    <td class="b-timetable__column b-timetable__column_type_departure b-timetable__column_state_current">                <div class="b-timetable__time">                            <span class="js-datetime js-no-repeat {'shifts': {'213': 0}, 'local': 'July 20, 2011 22:10:00', 'col': 'dep'}"><strong>22:10</strong>, 20&nbsp;июля</span>                    </div>                        <div class="b-timetable__platform">                <a class="b-link b-link_theme_gray" href="/info/station/9600216/">Домодедово</a>        </div>            </td>                    <td class="b-timetable__column b-timetable__column_type_arrival">                <div class="b-timetable__time">                            <span class="js-datetime js-no-repeat {'shifts': {'213': -180}, 'local': 'July 21, 2011 05:30:00', 'col': 'arr'}"><strong>05:30</strong>, 21&nbsp;июля</span>                    </div>                        <div class="b-timetable__platform">                <a class="b-link b-link_theme_gray" href="/info/station/9623569/">Спиченково</a>        </div>            </td>                    <td class="b-timetable__column b-timetable__column_type_time {raw: 260}">                        <div class="b-timetable__pathtime">        	        	            4 <span class="b-timetable__mark">ч</span>	        	            20 <span class="b-timetable__mark">мин</span>	        	        </div>                                                    </td>                                                            <td class="b-timetable__column b-timetable__column_type_price">                    <div class="js-tariffs {link: 'http://clck.yandex.ru/redir/dtype=stred/pid=168/cid=70831/*http://rasp.yandex.ru/buy/?station_to=9623569&thread=7R-4101A_c96_agent&station_from=9600216&date=2011-07-20&point_to=c237&point_from=c213'}" id="js-s-7R-4101-0720">                                    &nbsp;                                            </div>                            <div class="b-spin b-spin_size_10 js-tariffs-spinner-plane_2011-07-20"><img class="b-ico" src="//yandex.st/lego/_/La6qi18Z8LwgnZdsAr1qy1GwCwo.gif" alt="" width="10" /></div>                        </div>    </td>                            </tr>                                            <tr class="b-timetable__row {'seats': 'n', 'depTime': 'evening', 'carrier': '30', 'stationFrom': '9600216', 'arrTime': 'night', 'stationTo': '9623569'}">                    <td class="b-timetable__column b-timetable__column_type_ico b-timetable__column_position_first"><a class="b-link" href="/thread/U6-101A_c30_agent/?station_to=9623569&point_from=c213&departure=2011-07-20&point_to=c237&station_from=9600216"><span class="b-transico b-transico_type_plane"><img class="b-transico__i"                                                                                       src="http://static.rasp.yandex.net/2.6.36/blocks/b-transico/b-transico.png"                                                                                       alt=""/><img class="b-transico__print" src="http://static.rasp.yandex.net/2.6.36/blocks/b-transico/b-transico__plane.png" alt=""/></span></a></td>                <td class="b-timetable__column b-timetable__column_type_trip">                                                                                             <div class="b-timetable__tripname">            <a class="b-link" href="/thread/U6-101A_c30_agent/?station_to=9623569&point_from=c213&departure=2011-07-20&point_to=c237&station_from=9600216"><strong>U6 101</strong> <span class="g-nowrap">Москва</span> — <span class="g-nowrap">Новокузнецк</span></a>        </div>        <div class="b-timetable__description">            Airbus А320, <a class="b-link" href="/info/company/30">Уральские Авиалинии</a>                                        <img alt="Электронный билет" src="http://static.rasp.yandex.net/2.6.36/i/eticket.png" class="b-ico b-ico_type_eticket js-et-marker g-hidden">                    </div>                                                                </td>                    <td class="b-timetable__column b-timetable__column_type_departure b-timetable__column_state_current">                <div class="b-timetable__time">                            <span class="js-datetime js-no-repeat {'shifts': {'213': 0}, 'local': 'July 20, 2011 22:10:00', 'col': 'dep'}"><strong>22:10</strong></span>                    </div>                        <div class="b-timetable__platform">                <a class="b-link b-link_theme_gray" href="/info/station/9600216/">Домодедово</a>        </div>            </td>                    <td class="b-timetable__column b-timetable__column_type_arrival">                <div class="b-timetable__time">                            <span class="js-datetime js-no-repeat {'shifts': {'213': -180}, 'local': 'July 21, 2011 05:30:00', 'col': 'arr'}"><strong>05:30</strong></span>                    </div>                        <div class="b-timetable__platform">                <a class="b-link b-link_theme_gray" href="/info/station/9623569/">Спиченково</a>        </div>            </td>                    <td class="b-timetable__column b-timetable__column_type_time {raw: 260}">                        <div class="b-timetable__pathtime">        	        	            4 <span class="b-timetable__mark">ч</span>	        	            20 <span class="b-timetable__mark">мин</span>	        	        </div>                                                    </td>                                                            <td class="b-timetable__column b-timetable__column_type_price">                    <div class="js-tariffs {link: 'http://clck.yandex.ru/redir/dtype=stred/pid=168/cid=70831/*http://rasp.yandex.ru/buy/?station_to=9623569&thread=U6-101A_c30_agent&station_from=9600216&date=2011-07-20&point_to=c237&point_from=c213'}" id="js-s-U6-101-0720">                                    &nbsp;                                            </div>                            <div class="b-spin b-spin_size_10 js-tariffs-spinner-plane_2011-07-20"><img class="b-ico" src="//yandex.st/lego/_/La6qi18Z8LwgnZdsAr1qy1GwCwo.gif" alt="" width="10" /></div>                        </div>    </td>                            </tr>                                            <tr class="b-timetable__row b-timetable__row_position_last {'seats': 'n', 'depTime': 'evening', 'carrier': '23', 'stationFrom': '9600216', 'arrTime': 'morning', 'stationTo': '9623569'}">                    <td class="b-timetable__column b-timetable__column_type_ico b-timetable__column_position_first"><a class="b-link" href="/thread/S7-809A_c23_agent/?station_to=9623569&point_from=c213&departure=2011-07-20&point_to=c237&station_from=9600216"><span class="b-transico b-transico_type_plane"><img class="b-transico__i"                                                                                       src="http://static.rasp.yandex.net/2.6.36/blocks/b-transico/b-transico.png"                                                                                       alt=""/><img class="b-transico__print" src="http://static.rasp.yandex.net/2.6.36/blocks/b-transico/b-transico__plane.png" alt=""/></span></a></td>                <td class="b-timetable__column b-timetable__column_type_trip">                                                                                             <div class="b-timetable__tripname">            <a class="b-link" href="/thread/S7-809A_c23_agent/?station_to=9623569&point_from=c213&departure=2011-07-20&point_to=c237&station_from=9600216"><strong>S7 809</strong> <span class="g-nowrap">Москва</span> — <span class="g-nowrap">Новокузнецк</span></a>        </div>        <div class="b-timetable__description">            Airbus A319, <a class="b-link" href="/info/company/23">S7 Airlines</a>                                        <img alt="Электронный билет" src="http://static.rasp.yandex.net/2.6.36/i/eticket.png" class="b-ico b-ico_type_eticket js-et-marker g-hidden">                    </div>                                                                </td>                    <td class="b-timetable__column b-timetable__column_type_departure b-timetable__column_state_current">                <div class="b-timetable__time">                            <span class="js-datetime js-no-repeat {'shifts': {'213': 0}, 'local': 'July 20, 2011 23:15:00', 'col': 'dep'}"><strong>23:15</strong></span>                    </div>                        <div class="b-timetable__platform">                <a class="b-link b-link_theme_gray" href="/info/station/9600216/">Домодедово</a>        </div>            </td>                    <td class="b-timetable__column b-timetable__column_type_arrival">                <div class="b-timetable__time">                            <span class="js-datetime js-no-repeat {'shifts': {'213': -180}, 'local': 'July 21, 2011 06:25:00', 'col': 'arr'}"><strong>06:25</strong></span>                    </div>                        <div class="b-timetable__platform">                <a class="b-link b-link_theme_gray" href="/info/station/9623569/">Спиченково</a>        </div>            </td>                    <td class="b-timetable__column b-timetable__column_type_time {raw: 250}">                        <div class="b-timetable__pathtime">        	        	            4 <span class="b-timetable__mark">ч</span>	        	            10 <span class="b-timetable__mark">мин</span>	        	        </div>                                                    </td>                                                            <td class="b-timetable__column b-timetable__column_type_price">                    <div class="js-tariffs {link: 'http://clck.yandex.ru/redir/dtype=stred/pid=168/cid=70831/*http://rasp.yandex.ru/buy/?station_to=9623569&thread=S7-809A_c23_agent&station_from=9600216&date=2011-07-20&point_to=c237&point_from=c213'}" id="js-s-S7-809-0720">                                    &nbsp;                                            </div>                            <div class="b-spin b-spin_size_10 js-tariffs-spinner-plane_2011-07-20"><img class="b-ico" src="//yandex.st/lego/_/La6qi18Z8LwgnZdsAr1qy1GwCwo.gif" alt="" width="10" /></div>                        </div>    </td>                            </tr>                            </tbody>"
//			}
//			[1]=> array(1) {
//				[0]=> string(292) "            <a class="b-link" href="/thread/7R-4101A_c96_agent/?station_to=9623569&point_from=c213&departure=2011-07-20&point_to=c237&station_from=9600216"><strong>7R 4101</strong> <span class="g-nowrap">Москва</span> — <span class="g-nowrap">Новокузнецк</span></a>        "
//			}
//			[2]=> array(1) {
//				[0]=> string(139) "                            <span class="js-datetime js-no-repeat {'shifts': {'213': 0}, 'local': 'July 20, 2011 22:10:00', 'col': 'dep'}">"
//			}
//			[3]=> array(1) {
//				[0]=> string(5) "22:10"
//			}
//			[4]=> array(1) {
//				[0]=> string(142) "                            <span class="js-datetime js-no-repeat {'shifts': {'213': -180}, 'local': 'July 21, 2011 05:30:00', 'col': 'arr'}">"
//			}
//			[5]=> array(1) {
//				[0]=> string(5) "05:30"
//			}
//			[6]=> array(1) {
//				[0]=> string(161) "        	        	            4 <span class="b-timetable__mark">ч</span>	        	            20 <span class="b-timetable__mark">мин</span>	        	        "
//			}

		preg_match_all (
			$regexp,
			$html,
			$matches
		);

		$results = array ();

		if ($matches)
		{
			$keys = array_keys ($matches [0]);
			foreach ($keys as $key)
			{
				$in_way = strip_tags ($matches [6][$key]);
				// $in_way = '4 ч    20 мин'
				$in_way = str_replace ('ч', ':', $matches [6][$key]);
				// $in_way = '4 :    20 мин'/ '4: 5 мин' / '4:'
				$in_way = preg_replace ('/[^0-9:]/', '', $in_way);
				// $in_way '4:20' / '4:5'
				$in_way = explode (':', $in_way);
				while (strlen ($in_way [1]) < 2)
				{
					$in_way [1] = '0' . $in_way [1];
				}
				$in_way = $in_way [0] . ':' . $in_way [1];
				$results [] = array (
					trim (strip_tags ($matches [1][$key])),
					strip_tags ($matches [3][$key]),
					strip_tags ($matches [5][$key]),
					$in_way
				);
			}
		}

		return $results;
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
