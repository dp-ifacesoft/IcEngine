<?php

/**
 * Интерфейс над phpMorphy
 * 
 * @author morph
 * @Service("morphy")
 */
class Morphy
{
    /**
     * phpMorph
     * 
     * @var mixed
     */
    protected $morphy;
    
    /**
     * Получить экземпляр phpMorph
     * 
     * @return phpMorphy
     */
    public function get ()
    {
        if ($this->morphy) {
            return $this->morphy;
        }
        $morphyDir = IcEngine::path() . 'Vendor';
        include $morphyDir . '/Morphy/src/common.php';
        $this->morphy = new phpMorphy(
            $morphyDir . '/Morphy/dicts/utf-8',
            'ru_RU',
            array (
                'storage'           => PHPMORPHY_STORAGE_FILE, 
                'predict_by_suffix' => true,
                'predict_by_db'     => true,
                'graminfo_as_text'  => true,
            )
        );
        return $this->morphy;
    }
}