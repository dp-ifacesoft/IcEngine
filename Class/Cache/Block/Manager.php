<?php

/**
 * Менеджер блоков кэша
 *
 * @author morph
 * @Service("cacheBlockManager")
 */
class Cache_Block_Manager extends Manager_Abstract
{
    /**
     * Блоки для загрузки
     *
     * @var array
     * @Generator
     */
    protected $blockVector;

    /**
     * Текущий хэш
     *
     * @var string
     * @Generator
     */
    protected $currentHash;

    /**
     * Результат
     *
     * @var array
     */
    protected $data;

    /**
     * Загружены ли полностью по текущего хэшу кэши
     * 
     * @var boolean
     */
    protected $isFullLoaded = array();
    
    /**
     * Менеджер спецификаций блоков
     *
     * @Inject("cacheBlockSpecificationManager")
     * @var Cache_Block_Specification_Manager
     */
    protected $specificationManager;

    /**
     * Добавить блоки для загрузки
     *
     * @param array $blocks
     * @param array $params
     */
    public function addBlocks($blocks, $params = array())
    {
        $hash = $this->getHash($params);
        if (!isset($this->blockVector[$hash])) {
            $this->blockVector[$hash] = array();
        }
        foreach ((array) $blocks as $block => $specificationName) {
            if (!is_numeric($block)) {
                $specification = $this->specificationManager->get(
                    $specificationName
                );
                if (!$specification->isSatisfiedBy()) {
                    continue;
                }
            }
            $this->blockVector[$hash] = $block;
        }
    }

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
        if (isset($this->data[$controllerAction])) {
            if (!is_array($this->data[$controllerAction])) {
                $this->data[$controllerAction] = json_decode(
                    urldecode($this->data[$controllerAction]), true
                );
            }
            return $this->data[$controllerAction];
        } elseif (!empty($this->isFullLoaded[$hash])) {
            $this->data[$controllerAction] = array();
            return array();
        } elseif ($this->data) {
            $this->blockVector = array();
        }
        $hashVector = array($this->getDefaultHash());
        if ($hash != $hashVector[0]) {
            $hashVector[] = $hash;
        }
        $query = $this->getService('query')
            ->select('json', 'controllerAction')
            ->from('Cache_Block')
            ->where('hash', $hashVector);
        if (!empty($this->blockVector[$hash])) {
            $query->where('controllerAction', $this->blockVector[$hash]);
        } else {
            $this->isFullLoaded[$hash] = true;
        }
        $data = $this->getService('dds')->execute($query)->getResult()
            ->asTable();
        if (!$data) {
            $this->data[$controllerAction] = array();
            return array();
        }
        foreach ($data as $row) {
            $this->data[$row['controllerAction']] = $row['json'];
        }
        if (empty($this->data[$controllerAction])) {
            $this->data[$controllerAction] = array();
            return array();
        }
        if (!is_array($this->data[$controllerAction])) {
            $this->data[$controllerAction] = json_decode(
                urldecode($this->data[$controllerAction]), true
            );
        }
        return $this->data[$controllerAction];
    }
    
    /**
     * Получить хэщ по умолчанию
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
    public function getHash($params, $replaceOnCurrentHash = true)
    {
        if ($this->currentHash && $replaceOnCurrentHash) {
            return $this->currentHash;
        }
        ksort($params);
        return md5(json_encode($params));
    }

    /**
     * Инициализация менеджера
     *
     * @param string $strategyName
     * @param array $params
     * @param boolean $setCurrentHash
     */
    public function init($strategyName, $params = array(),
        $setCurrentHash = true)
    {
        $config = $this->config();
        $hash = $this->getHash($params);
        if ($setCurrentHash) {
            $this->currentHash = $hash;
        }
        if ($config[$strategyName] && $config[$strategyName]['blocks']) {
            $this->addBlocks($config[$strategyName]->__toArray(), $params);
            if ($config[$strategyName]['hash']) {
                $hashMethod = $config[$strategyName]['hash'];
                $hashService = $this->getService($hashMethod[0]);
                $method = $hashMethod[1];
                if (!$setCurrentHash) {
                    $this->currentHash = $this->getHash(
                        $hashService->$method()
                    );
                }
            }
        }
    }

    /**
     * Удалить блок
     *
     * @param string $controllerAction
     * @param array $params
     */
    public function reset($controllerAction, $params = array())
    {
        $hash = $this->getHash($params);
        if (!isset($this->blockVector[$hash])) {
            return null;
        }
        foreach ($this->blockVector[$hash] as $i => $blockAction) {
            if ($controllerAction == $blockAction) {
                unset($this->blockVector[$hash][$i]);
            }
        }
    }

    /**
     * Изменить блок
     *
     * @param string $controllerAction
     * @param array $json
     * @param array $params
     * @param boolean $throwUnitOfWork
     */
    public function set($controllerAction, $json, $params = array(),
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
        $insertQuery = $queryBuilder
            ->insert('Cache_Block')
            ->values(array(
                'controllerAction'  => $controllerAction,
                'hash'              => $hash,
                'json'              => urlencode(json_encode($json)),
                'createdAt'         => date('Y-m-d H:i:s')
            ));
        if ($throwUnitOfWork) {
            $unitOfWork->push($insertQuery);
        } else {
            $dds->execute($insertQuery);
        }
    }

    /**
     * Getter for "blockVector"
     *
     * @return array
     */
    public function getBlockVector()
    {
        return $this->blockVector;
    }

    /**
     * Setter for "blockVector"
     *
     * @param array blockVector
     */
    public function setBlockVector($blockVector)
    {
        $this->blockVector = $blockVector;
    }


    /**
     * Getter for "currentHash"
     *
     * @return string
     */
    public function getCurrentHash()
    {
        return $this->currentHash;
    }

    /**
     * Setter for "currentHash"
     *
     * @param string currentHash
     */
    public function setCurrentHash($currentHash)
    {
        $this->currentHash = $currentHash;
    }

}