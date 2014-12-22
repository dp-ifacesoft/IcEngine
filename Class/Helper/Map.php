<?php

/**
 * Помощник для работы с картами
 *
 * @author Apostle
 * @Service("helperMap")
 */
class Helper_Map extends Helper_Abstract
{
     /**
     * Пересчет координат из георграфических в проекцию Меркатора
     * @param type $longitude долгота
     * @param type $latitude широта
     * @return array $mercator координаты WGS84
     */
    public function geoToWGS84($longitude, $latitude) {
        if (!$longitude || !$latitude) {
            return false;
        }
        if($latitude > 89.5) {
            $lat = 89.5;
        } elseif ($latitude < -89.5) {
            $lat = -89.5;
        } else {
            $lat = $latitude;
        }
        $radLat = deg2rad($lat);
        $radLong = deg2rad($longitude);
        $equatorCircle= 6378137.0;
        $zeroMeridianCircle = 6356752.3142;
        $geoElipsCompression = ($equatorCircle - $zeroMeridianCircle) / $equatorCircle;
        $e = sqrt(2*$geoElipsCompression - pow($geoElipsCompression, 2));
        $xMerkator = $equatorCircle*$radLong;
        $yMerkator = $equatorCircle*log(tan(pi()/4 + $radLat/2)*pow((1-$e*sin($radLat))/(1+$e*sin($radLat)),$e/2));
        $mercator = array(
            'x' => $xMerkator,
            'y' => $yMerkator
        );
    return $mercator;    
    }
    
        /**
     * Проверяет один объект, входит ли тот в необходимый радиус вокруг другого
     * @param float $x - х координата точки вокруг которой строится круг
     * @param float $y - у координата
     * @param integer $r - радиус поиска 
     * @param float $x1 - х координа объекта, входящего или невходящего в круг
     * @param float $y1 - у координата
     * @return bool - входит ли
     */
    public function IsObjectInRange($x, $y, $r, $x1, $y1) {
        if (sqrt(pow(($x1 - $x), 2) + pow(($y1-$y), 2)) <= $r) {
            return true;
        }
        return false;
    }
    
}