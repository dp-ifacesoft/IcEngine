<?php

/**
 * Запрос типа insert
 *
 * @author morph, goorus, neon
 */
class Query_Insert extends Query_Abstract
{
    /**
     * Множественная ли вствка выполняется
     *
     * @var boolean
     */
	protected $multiple = false;

    /**
     * @inheritdoc
     */
    protected $type = Query::INSERT;

	/**
	 * Получить значение флага, на множественную вставку
	 *
	 * @return bool
	 */
	public function getMultiple()
	{
		return $this->multiple;
	}

	/**
	 * @inheritdoc
	 */
	public function getTags()
	{
		$locator = IcEngine::serviceLocator();
		$modelScheme = $locator->getService('modelScheme');
		$tags = array();
		$insert = $this->getPart(Query::INSERT);
		if ($insert) {
	   		$tags[] = $modelScheme->table($insert);
		}
		return array_unique($tags);
	}

	/**
	 * Помечаем запрос, как запрос на множественный INSERT
	 *
	 * @param bool $value
	 * @return void
	 */
	public function setMultiple($value)
	{
		$this->multiple = $value;
	}
}