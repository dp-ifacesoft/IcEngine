<?php

/**
 * Сервис отписки от рассылок
 *
 * @author markov
 * @Service("serviceUnsubscribe")
 */
class Service_Unsubscribe extends Service_Abstract
{
    /**
     * Возвращает идентификаторы, отписавшихся от рассылки
     * 
     * @param integer $subscribeTypeId id типа рассылки
     * @return array
     */
    public function getUnsubscribersByTypeId($subscribeTypeId)
    {
        $unsubscribersQuery = App::queryBuilder()
            ->select('identificator')
            ->from('Subscribe_Unsubscriber')
            ->where('Subscribe_Type__id', $subscribeTypeId);
        $unsubscribers = App::dds()->execute($unsubscribersQuery)->getResult()->asTable();
        return $unsubscribers;
    }
}
