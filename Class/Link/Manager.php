<?php

class Link_Manager extends Manager_Abstract
{
	protected static function _anyKeys (Model $model, $linked_model_name)
	{
		$keys = array ();

		if ($model->table () == $linked_model_name)
		{
			$keys = array (
				$model->table () .
					'__' .
					$model->key () .
					'/' .
					$linked_model_name .
					'__*'
			);
		}
		else
		{
			$keys = array (
				self::_id (
					$model->table (), $model->key (),
					$linked_model_name, '*'
				)
			);
		}

		return $keys;
	}

	protected static function _id ($table1, $key1, $table2, $key2)
	{
		$table1 .= '__' . $key1;
		$table2 .= '__' . $key2;

		if (strcmp ($table1, $table2) > 0)
	    {
	        $tmp = $table1;
	        $table1 = $table2;
	        $table2 = $tmp;
	    }

		return $table1 . '/' . $table2;
	}

	 /**
     * @desc Связывает модели.
     * @param string $table1
     * @param integer $key1
     * @param string $table2
     * @param integer $key2
     * @return Link|null
     */
	protected static function _link ($table1, $key1, $table2, $key2)
	{
		$id = self::_id (
			$table1, $key1,
			$table2, $key2
		);

		return self::$_provider->get ($id);
	}

	/**
	 * @desc Проверяет, связаны ли модели.
	 * @param Model $model1
	 * @param Model $model2
	 * @return boolean
	 */
	public static function wereLinked (Model $model1, Model $model2)
	{
	    $link = self::_link (
	        $model1->table (), $model1->key (),
	        $model2->table (), $model2->key ()
	    );

	    return (bool) $link;
	}

	/**
	 * @desc Связывает модели.
	 * @param Model $model1
	 * @param Model $model2
	 * @return Link
	 */
	public static function link (Model $model1, Model $model2)
	{
	    $link = self::_link (
	        $model1->table (), $model1->key (),
	        $model2->table (), $model2->key ()
	    );

	    if (!$link)
	    {
			$id = self::_id (
				$model1->table (), $model1->key (),
				$model2->table (), $model2->key ()
			);

	        self::$_provider->set ($id, $id);

			$tmp = explode ('/', $id);
			$tmp = array_reverse ($tmp);
			$id = implode ('/', $tmp);

			self::$_transport->providers ()->set ($id, $id);
	    }
	}

	/**
	 * @desc Возвращает коллекцию связанных с $model моделей
	 * типа $linked_model_name.
	 * @param Model $model1
	 * @param string $model2
	 * @return Model_Collection
	 */
	public static function linkedItems (Model $model, $linked_model_name)
	{
		$result = Model_Collection_Manager::create (
			$linked_model_name
		)
			->reset ();

		$receive_keys = self::linkedKeys (
			$model,
			$linked_model_name
		);

		foreach ($receive_keys as $key)
		{
			$result->add (Model_Manager::byKey (
				$linked_model_name,
				$key
			));
		}

	    return $result;
	}

	/**
	 * @desc Возвращает первичные ключи связанных моделей.
	 * @param Model $model
	 * 		Модель.
	 * @param string $linked_model_name
	 * 		Имя связанной модели.
	 * @return array
	 * 		Массив первичных ключей второй модели.
	 */
	public static function linkedKeys (Model $model, $linked_model_name)
	{
		$keys = self::_anyKeys ($model, $linked_model_name);

		$receive_keys = array ();

		foreach ($keys as $key)
		{
			$receive_keys = array_merge (
				$receive_keys,
				self::$_transport->providers ()->get ($key)
			);
		}

		$pattern = $model->table () . '__' . $model->key ();

		foreach ($receive_keys as &$key)
		{
			$key = str_replace ($pattern, '', $key);
			$key = trim ($key, '/');
			$key = reset (explode ('__', $key));
		}

		return $receive_keys;
	}

	/**
	 * @desc Удаляет связь моделей.
	 * @param Model $model1
	 * @param Model $model2
	 */
	public static function unlink (Model $model1, Model $model2)
	{
	    $id = self::_id (
			$model1->table (), $model1->key (),
			$model2->table (), $model2->key ()
		);

		self::$_transport->providers ()->delete ($id);
	}

	/**
	 * Удаление всех связей модели с моделями указанного типа.
	 * @param Model $model1
	 * @param string $model2
	 */
	public static function unlinkWith (Model $model, $linked_model_name)
	{
		$keys = self::_anyKeys ($model, $linked_model_name);

		foreach ($keys as $key)
		{
			self::$_transport->providers ()->delete ($key);
		}
	}
}