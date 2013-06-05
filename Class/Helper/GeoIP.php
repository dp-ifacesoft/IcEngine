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
        $netCity = $this->getNetCity($ip);
        if (!$netCity) {
            $sessionResource->cityId = false;
            return;
        }
        $modelManager = $locator->getService('modelManager');
        $city = $modelManager->byOptions(
            'City', array(
                'name'  => 'Net_City',
                'id'    => $netCity['Net_City__id']
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
        $request = $locator->getService('request');
		$ip = $this->ip2int($ip !== null ? $ip : $request->ip());
        $dds = $locator->getService('dds');
		$queryBuilder = $locator->getService('query');
        $netCityQuerySelect = $queryBuilder
            ->select('*')
            ->from('Net_City_Ip')
            ->where('begin_ip <= ?', $ip)
            ->where('end_ip >= ?', $ip);
        $netCity = $dds->execute($netCityQuerySelect)
            ->getResult()->asRow();
        return $netCity;
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
        $netCityQuerySelect = $queryBuilder->select('id')
            ->from('Net_City')
            ->where('name_ru', $title);
        $netCity = $dds->execute($netCityQuerySelect)
            ->getResult()->asValue();
        return (int) $netCity;
    }
}