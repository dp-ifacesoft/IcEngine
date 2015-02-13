<?php

/**
 * Абстракный транслятор запросов
 *
 * @author goorus, morph, neon
 */
class Query_Translator_Abstract
{
    /**
     * Схема моделей
     * 
     * @var Model_Scheme
     */
    protected static $modelScheme;
    
	/**
	 * Возвращает имя транслятора
     *
	 * @return string
	 */
	public function getName()
	{
		return substr(get_class($this), strlen('Query_Translator_'));
	}
    
    /**
     * Получить (инициализировать) схему моделей
     * 
     * @return Model_Scheme
     */
    public function modelScheme()
    {
        if (is_null(self::$modelScheme)) {
            self::$modelScheme = IcEngine::serviceLocator()->getService(
                'modelScheme'
            );
        }
        return self::$modelScheme;
    }

	/**
	 * Транслирует запрос.
     *
	 * @param Query_Abstract $query Запрос.
	 * @return mixed Результат трансляции.
	 */
	public function translate(Query_Abstract $query)
	{
		$type = $query->getName();
		$parts = explode('_', $type);
		foreach ($parts as &$part) {
			$part = substr(strtoupper($part), 0, 1).
				substr(strtolower($part), 1);
		}
		$resultType = implode('', $parts);
        $callable = array($this, 'doRender' . $resultType);
		return call_user_func($callable, $query);
	}
}