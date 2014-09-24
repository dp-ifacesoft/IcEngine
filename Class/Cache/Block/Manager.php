<?php

/**
 * Менеджер блоков кэша
 *
 * @author markov
 * @Service("cacheBlockManager")
 */
class Cache_Block_Manager extends Manager_Abstract
{
    /**
     * Получить блок кэша
     *
     * @param string $controllerAction
     * @param array $params
     * @return array
     */
    public function get($controllerAction, $params = array())
    {
        $controllerAction = str_replace('Controller_', '', $controllerAction);
        $hash = $this->getHash($params);
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $cacheBlockQuery = $queryBuilder
            ->select('*')
            ->select('json', 'controllerAction')
            ->from('Cache_Block')
            ->where('hash', $hash)
            ->where('controllerAction', $controllerAction);
        $block = $dds->execute($cacheBlockQuery)->getResult()->asRow();
        if (!$block) {
            return array();
        }
        $data = json_decode(urldecode($block['json']), true);
        return $data;
    }
    
    /**
     * Получить хэш по умолчанию
     * 
     * @return string
     */
    public function getDefaultHash()
    {
        return $this->getHash(array(), false);
    }

    /**
     * Получить хэш
     *
     * @param array $params
     * @param boolean $replaceOnCurrentHash
     * @return string
     */
    public function getHash($params)
    {
        ksort($params);
        return md5(json_encode($params));
    }

    /**
     * Удалить блок
     *
     * @param string $controllerAction
     * @param array  $params
     * @param bool   $throwUnitOfWork
     */
    public function reset($controllerAction, $params = array(),
        $throwUnitOfWork = false)
    {
        $hash = $this->getHash($params);
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $unitOfWork = $this->getService('unitOfWork');
        $deleteQuery = $queryBuilder
            ->delete()
            ->from('Cache_Block')
            ->where('hash', $hash)
            ->where('controllerAction', $controllerAction);
        if ($throwUnitOfWork) {
            $unitOfWork->push($deleteQuery);
        } else {
            $dds->execute($deleteQuery);
        }
    }

    /**
     * Изменить блок
     *
     * @param string $controllerAction
     * @param array $data
     * @param array $params
     * @param boolean $throwUnitOfWork
     */
    public function set($controllerAction, $data, $params = array(),
        $throwUnitOfWork = false)
    {
        $this->reset($controllerAction, $params,
            $throwUnitOfWork);
        $hash = $this->getHash($params);
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $unitOfWork = $this->getService('unitOfWork');
        $insertQuery = $queryBuilder
            ->insert('Cache_Block')
            ->values(array(
                'controllerAction'  => $controllerAction,
                'hash'              => $hash,
                'json'              => urlencode(json_encode($data)),
                'createdAt'         => date('Y-m-d H:i:s')
            ));
        if ($throwUnitOfWork) {
            $unitOfWork->push($insertQuery);
        } else {
            $dds->execute($insertQuery);
        }
    }
}