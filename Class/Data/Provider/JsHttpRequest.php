<?php

/**
 * Провайдер для работы с JsHttpRequest
 * 
 * @author goorus, morph
 */
class Data_Provider_JsHttpRequest extends Data_Provider_Buffer
{
    /**
     * @inheritdoc
     */
	public function __construct()
    {
        $this->buffer = &$GLOBALS['_RESULT'];
    }
}