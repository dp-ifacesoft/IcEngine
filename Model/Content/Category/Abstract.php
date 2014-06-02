<?php

/**
 * Абстрактный класс модели контента
 *
 * @author goorus, morph
 */
class Content_Abstract extends Model_Factory_Delegate
{
	/**
	 * Получить базовую модель контента
	 *
	 * @return Content
	 */
	public function base()
	{
		$content = Model_Manager::byKey('Content', $this->key());
		$fields = array();
		if ($content) {
			$fields = $content->getFields();
		}
		return Model_Manager::create('Content', $fields);
	}

	/**
	 * Получить имя фабрики
	 *
	 * @return string
	 */
	public function delegeeName ()
	{
		return substr(get_class($this), strlen('Content_'));
	}

	/**
	 * Расширение модели
	 *
	 * @return Content_Extending
	 */
	public function extending()
	{
		if (!$this->extending) {
			return $this;
		}
		$extending = Model_Manager::byKey($this->extending, $this->key());
		if (!$extending)
		{
			$extending = Model_Manager::create(
				$this->extending,
				array(
					'id'	=> $this->key()
				)
			);
			$extending->save(true);
		}
		return $extending;
	}

	/**
	 * @inheritdoc
	 */
	public function modelName()
	{
		return get_class($this);
	}

	/**
	 * @inheritdoc
	 */
	public function title()
	{
		return $this->title;
	}
}
