<?php
/**
 *
 * @desc Базовая модель для расширений контента.
 * Не допускается, чтобы модель состояла из одного поля - первичного ключа.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */

Loader::load ('Content_Abstract');

class Content_Extending extends Content_Abstract
{

	/**
	 * Действие после удаления
	 */
	public function _afterDelete()
	{

	}

	/**
	 * @desc Возвращает контент, к которому привязано.
	 * @return Content
	 */
	public function content ()
	{
		return Model_Manager::byKey ('Content', $this->id);
	}

	/**
	 * @desc Первое сохранение. Необходимо для инициализации полей, задания
	 * им значений по умолчанию.
	 * @return Content_Extending
	 */
	public function firstSave ()
	{
		return $this->save (true);
	}

	/**
	 * (non-PHPdoc)
	 * @see Model::save()
	 */
	public function save ($hard_insert = false)
	{
		$this->set ('title', $this->content ()->title);
		return parent::save ($hard_insert);
	}

	/**
	 * @inheritdoc
	 *
	 */
	public function delete ()
	{
		if (!$this->key ())
		{
			return ;
		}
		Model_Scheme::dataSource ($this->modelName ())
			->execute (
				Query::instance ()
					->delete ()
					->from (get_class($this))
					->where ($this->keyField (), $this->key ())
		);
	}
}