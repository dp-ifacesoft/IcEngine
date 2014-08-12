<?php

/**
 * Класс для работы с Гео-айпи
 *
 * @author Илья Колесников, neon
 * @Service("helperGeoIP")
 */
class Helper_GeoIP
{
    /**
     * Преобразует строковое представление IP адреса в число
     *
     * @param string $ip
     * @return int
     */
	public function ip2int($ip)
	{
		$ip = explode('.', $ip);
		return $ip[3] + 256 * ($ip[2] + 256 * ($ip[1] + 256 * $ip[0]));
	}

    /**
     * Получить город, которому соответствует IP адрес текущего пользователя
     *
     * @param null $ip
     * @return City|null
     */
	public function getCity($ip = null)
	{
        $locator = IcEngine::serviceLocator();
        $sessionResource = $locator->getService('sessionResource')
            ->newInstance('Geo');
        if (isset($sessionResource->cityId)) {
            if (!$sessionResource->cityId) {
                return;
            }
            return $locator->getService('modelManager')->byKey(
                'City', $sessionResource->cityId
            );
        }
        $netCityId = $this->getNetCityId($ip);
        if (!$netCityId) {
            $sessionResource->cityId = false;
            return;
        }
        $modelManager = $locator->getService('modelManager');
        $city = $modelManager->byOptions(
            'City', array(
                'name'  => 'Net_City',
                'id'    => $netCityId
            )
        );
        if ($city) {
            $sessionResource->cityId = $city->key();
            return $city;
        } else {
            $sessionResource->cityId = false;
        }
	}

    /**
     * Получить город из таблицы Net_City
     *
     * @param string $ip
     * @return array
     */
    public function getNetCity($ip = null)
    {
        $locator = IcEngine::serviceLocator();
        $queryBuilder = $locator->getService('query');
        $dds = $locator->getService('dds');
        $request = $locator->getService('request');
        $ip = $ip !== null ? $ip : $request->ip();
        $regionId = 0;
        try {
            $cityGeoip = geoip_record_by_name($ip);
        } catch (Exception $e) {
            $cityGeoip = null;
        }
        if ($cityGeoip && is_array($cityGeoip) && isset($cityGeoip['region'])) {
            $regionId = $cityGeoip['region'];
        }
        $netCityQuery = $queryBuilder
            ->select('*')
            ->from('Net_City')
            ->where('name_en', $cityGeoip['city'])
            ->where('region', $cityGeoip['region']);
        $netCity = $dds->execute($netCityQuery)->getResult()->asRow();
        return $netCity;
    }
    
    /**
     * Получить id города из таблицы Net_City
     *
     * @param string $ip
     * @return integer
     */
    public function getNetCityId($ip = null)
    {
        $netCity = $this->getNetCity();
        return $netCity ? $netCity['id'] : 0;
    }

    /**
     * Получить город из базы геолокации
     *
     * @param $title
     * @return int
     */
    public function netCityByTitle($title)
    {
        $locator = IcEngine::serviceLocator();
        $queryBuilder = $locator->getService('query');
        $dds = $locator->getService('dds');
        $netCityQuerySelect = $queryBuilder->select('Net_City.id')
            ->from('Net_City')
            ->where('name_ru', $title);
        $netCity = $dds->execute($netCityQuerySelect)
            ->getResult()->asValue();
        return (int) $netCity;
    }
}