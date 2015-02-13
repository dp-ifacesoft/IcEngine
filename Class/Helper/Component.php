<?php

/**
 * Помощник для работы с компонентами
 *
 * @author markov
 * @Service("helperComponent")
 */
class Helper_Component extends Helper_Abstract
{
    /**
     * Возвращает компонент
     */
    public function component($name, $raw)
    {
        $collectionManager = $this->getService('collectionManager');
        return $collectionManager->create('Component_' . $name)
            ->addOptions(
                array(
                    'name'  => '::Table',
                    'table' => $raw['table']
                ),
                array(
                    'name'  => '::Row',
                    'id'    => $raw['id']
                )
            );
    }
}
