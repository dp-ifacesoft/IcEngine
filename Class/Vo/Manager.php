<?php

/**
 * Абстрактный Value object 
 *
 * @author markov
 * @Service("voManager")
 */
class Vo_Manager
{
    /**
     * @param string $name название Vo
     * @param array $data данные
     * @return Vo 
     */
    public function create($name, $data) 
    {
        $className = 'Vo_' . $name;
        $vo = new $className($data);
        return $vo;
    }
}
