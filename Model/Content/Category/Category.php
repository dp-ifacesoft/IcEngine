<?php

Loader::load ('Model_Child');

/**
 * Категория контента
 *
 * @author goorus, morph
 */
class Content_Category extends Model_Child
{
	/**
	 * Возвращает контент, содержащийся в этом разделе
	 *
	 * @return Model_Collection
	 */
	public function contents()
	{
		return Model_Collection_Manager::create('Content')
			->addOptions(
				array(
					'name'	=> 'Extending',
					'model'	=> $this->controller
				),
				array(
					'name'	=> 'Content_Category',
					'id'	=> $this->key()
				)
			);
	}

	/**
	 * Получить имя модели расшения контента
	 *
	 * @return string
	 */
	public function extendingModel()
	{
		return substr($this->controller, strlen('Content_'));
	}

	/**
	 * @inheritdoc
	 */
	public function title()
	{
		return $this->title;
	}
}