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
     * Отписывает пользователя от рассылки
     * 
     * @param array $data данные 
     */
    public function unsubscribe($data)
    {
        $unsubscriber = App::modelManager()->byOptions('Subscribe_Unsubscriber',
            [
                'name'  => '::Subscribe_Type',
                'id'    => $data['subscribeTypeId']
            ],
            [
                'name'  => '::Identificator',
                'value' => $data['identificator']
            ]
        );
        if ($unsubscriber) {
            return false;
        }
        $unsubscriberNew = App::modelManager()->create('Subscribe_Unsubscriber', [
            'Subscribe_Type__id'    => $data['subscribeTypeId'],
            'identificator'         => $data['identificator']
        ]);
        $unsubscriberNew->save();
    }
    
    
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
    
    /**
     * Возвращает ссылку
     * 
     * @param array $data данные
     * @return string 
     */
    public function getLink($data)
    {
        $key = $this->getKey($data);
        return 'unsubscriber/?identificator=' . urldecode($data['identificator']) . 
            '&subscribeTypeId=' . urldecode($data['subscribeTypeId']) . 
            '&key=' . $key;
    }
    
    /**
     * Проверяет достоверность
     * 
     * @param array $data данные для валидации
     * @return boolean
     */
    public function validateUnsubscriber($data)
    {
        return $this->getKey($data) == $data['key'];
    }
    
    /**
     * Возвращает ключ
     * 
     * @param array $data данные
     * @return string 
     */
    public function getKey($data)
    {
        $impoded = implode('_', [
            'unsubscribeKey', $data['identificator'], $data['subscribeTypeId']
        ]);
        return md5($impoded);
    }
}
