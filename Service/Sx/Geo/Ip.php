<?php

/**
 * Сервис по работе c sxGeoIp
 *
 * @author markov
 * @Service("serviceSxGeoIp")
 */
class Service_Sx_Geo_Ip extends Helper_Abstract
{
    /**
     * Получает город по ip
     * 
     * @param string $ip ip
     * @return City|null
     */
    public function getCity($ip)
    {
        IcEngine::getLoader()->requireOnce('SxGeo/SxGeo.php', 'Vendor');
        $path = IcEngine::path() . 'Vendor/SxGeo/SxGeoCity.dat';
        $SxGeo = new SxGeo($path, SXGEO_BATCH | SXGEO_MEMORY);
        $data = $SxGeo->get($ip);
        if (!$data || !isset($data['city'])) {
            return null;
        }
        $netCityIdsQuery = App::queryBuilder()
            ->select('id')
            ->from('Net_City')
            ->where('name_en', $data['city']['name_en']);
        $netCityIds = App::dds()->execute($netCityIdsQuery)->getResult()->asColumn();
        $city = App::modelManager()
            ->byOptions('City', [
                'name'  => 'Net_City',
                'id'    => $netCityIds
            ]);
        return $city;
    }
}
