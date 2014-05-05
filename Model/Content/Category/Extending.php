<?php

/**
 * Базовая модель для расширений контента.
 * Не допускается, чтобы модель состояла из одного поля - первичного ключа
 *
 * @author goorus, morph, neon
 */
class Content_Extending extends Content_Abstract
{

	/**
	 * Действие после удаления
	 */
	public function afterDelete()
	{

	}

	/**
	 * Действие после сохранение контента
	 *
	 * @param Data_Transport $transport
	 */
	public function afterSave($transport)
	{

	}

	/**
	 * Действие на сохранение плагина
	 */
	public static function afterSavePlugin($content)
	{
		$extending = $content->extending();
		$modelName = $extending->modelName();
		$scheme = Config_Manager::get('Model_Mapper_' . $modelName);
		$fields = $scheme->fields->__toArray();
		$column = Request::post('column');
		unset($fields['id']);
		$toUpdate = array();
		foreach ($fields as $fieldName) {
			if (isset($column[$fieldName])) {
				$toUpdate[$fieldName] = $column[$fieldName];
			}
		}
		if ($toUpdate) {
			$extending->update($toUpdate);
		}
	}

	/**
	 * Действия до показа формы создания контента
	 *
	 * @param Data_Transport $transport
	 */
	public function beforeCreate($transport)
	{

	}

	/**
	 * Получить адрес для категории
	 *
	 * @param Content_Category $category
	 */
	public function categoryUrl($category)
	{
		return $category->url;
	}

	/**
	 * Возвращает контент, к которому привязано
	 *
	 * @return Content
	 */
	public function content()
	{
		return Model_Manager::byKey('Content', $this->key());
	}

	/**
	 * Получить ссылку на статью
	 */
	public function defaultUrl()
	{

	}

	/**
	 * Перекрываем удаления, для вызова доп функций
	 */
	public function delete()
	{
		parent::delete ();
		$this->afterDelete ();
	}

	/**
	 * @return Content_Abstract
	 */
	public function firstSave()
	{
		return $this->save(true);
	}
}