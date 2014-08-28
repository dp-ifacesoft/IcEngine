<?php

/**
 * Провайдер для получения файлов из POST запроса.
 * 
 * @author goorus, morph
 */
class Data_Provider_Files extends Data_Provider_Buffer
{
    /**
     * @inheritdoc
     */
	public function __construct()
    {
        $this->buffer = &$_FILES;
    }
}