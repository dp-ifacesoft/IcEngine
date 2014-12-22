<?php

/**
 * Транслятор запросов.
 *
 * @author morph, goorus
 * @Service("queryTranslator")
 */
class Query_Translator
{
	/**
	 * Подключенные трансляторы.
	 *
     * @var array
	 */
	protected $translators = array();

	/**
	 * Возвращает объект транслятора по имени.
	 *
     * @param string $name Название транслятора.
	 * @return Query_Translator
	 */
	public function byName($name)
	{
		if (!isset($this->translators[$name])) {
			$className = 'Query_Translator_' . $name;
            $translator = new $className;
			$this->translators[$name] = $translator;
		}
		return $this->translators[$name];
	}

	/**
	 * Возвращает объект транслятора по имени и типу.
	 *
     * @param string $name Название транслятора.
	 * @param string $type Тип запроса
	 * @return Query_Translator
	 */
	public function factory($name, $type)
	{
		$parts = explode(' ', $type);
		foreach ($parts as &$part) {
			$part = strtoupper(substr($part, 0, 1)) .
				strtolower(substr($part, 1));
		}
		$type = implode('_', $parts);
		$name .= '_' . $type;
		$translator = $this->byName($name);
		return $translator;
	}
}