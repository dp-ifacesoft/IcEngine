<?php
/**
 *
 * @desc Теги.
 * Позволяют привязывать к моделям слова и позже находить их.
 * @author Yury Shvedov
 * @package IcEngine
 *
 * @property string $name Название тега
 * @property string $translit Тег транслитом (для формирования ссылок)
 * @property string $table Тазвание модели
 * @property integer $rowId ПК модели
 *
 */
class Component_Tag extends Model_Component
{

	/**
	 * @desc Возвращает коллекцию тегов по транслиту.
	 * @param string $tag Тег.
	 * @return Component_Tag_Collection Коллекция тегов.
	 */
	public static function byTranslit ($tag)
	{
		$tags = Model_Collection_Manager::create ('Component_Tag')
			->addOptions (array (
				'name'		=> 'Translit',
				'translit'	=> $tag
			));

		return $tags;
	}

	/**
	 * @desc Получить коллекцию моделей для тега.
	 * @param string $tag Тег.
	 * @param Model $exclude [optional] Модель, которая не будет включена
	 * в результат.
	 * @return Model_Collection Коллекция моделей с эти тегом.
	 */
	public static function getModels ($tag, $exclude = null)
	{
		$tag = Helper_Translit::translit ($tag, 'en');

		$tags = self::byTranslit ($tag, $exclude);

		$result = Model_Collection_Manager::create ('Proxy')
			->reset ();

		$included = array ();
		foreach ($this as $tag)
		{
			$item = Model_Manager::byKey ($tag->table, $tag->rowId);
			$res_name = $item->resourceKey ();
			if (!isset ($included [$res_name]))
			{
				$included [$res_name] = true;
				$result->add ($item);
			}
		}

		return $result;
	}

	/**
	 * @desc Переопределяет теги для модели.
	 * @param string|array $tags массив тегов или теги через запятую
	 * @param Model|string $model Модели или название модели
	 * @param mixed $id [optional] первичный ключ модели, обязателен, если
	 * передано название модели.
	 */
	public static function setForModel ($tags, $model, $id = null)
	{
		if (is_object ($model))
		{
			$table = $model->table ();
			$id = $model->key ();
		}
		else
		{
			$table = $model;
		}

		if (is_array ($tags))
		{
			$tags = implode (',', $tags);
		}
		$tags = mb_strtolower ($tags, 'UTF-8');
		$tags = implode (',', $tags);

		// Ключи массива - теги в транслите, так теги будут уникальными
		$by_trans = array ();
		foreach ($tags as $tag)
		{
			$tag = trim ($tag);
			if (tag)
			{
				$translit = Helper_Translit::translit ($tag, 'en');
				$by_trans [$translit] = array (
					'translit'	=> $translit,
					'name'		=> $tag,
					'table'		=> $table,
					'rowId'		=> $id
				);
			}
		}

		// Существующие теги
		$exists = Model_Collection_Manager::create ('Component_Tag')
			->addOptions (array (
				'name'		=> 'Component::Model',
				'table'		=> $table,
				'row_id'	=> $id
			));

		// сравниваем существующие и новые теги
		foreach ($exists as $i => $tag)
		{
			if (isset ($by_trans [$tag->translit]))
			{
				// Тег уже привязан к записи
				unset ($by_trans [$tag->translit]);
			}
			else
			{
				// Тег больше не нужен
				$tag->delete ();
			}
		}

		// Создаем новые теги
		foreach ($by_trans as $tag)
		{
			$tag = new Component_Tag ($tag);
			$tag->save ();
		}
	}

}