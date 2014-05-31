<?php

/**
 * Абстрактный класс фильтра
 *
 * @author goorus
 */
abstract class Data_Filter_Abstract
{
	/**
	 * Обычная фильтрация.
	 *
	 * @param string $data
	 * @return string
	 */
	public function filter($data)
	{
		return $data;
	}
    
    /**
     * Получить имя фильтра
     * 
     * @return string
     */
    public function getName()
    {
        return substr(get_class($this), strlen('Data_Filter_'));
    }
}