<?php
/**
 * Помощник для работы со связами многие-ко-многим моделей
 *
 * @author goorus, morph
 */
class Helper_Link
{
    /**
     * Связывает модели
	 *
     * @param string $table1
     * @param integer $key1
     * @param string $table2
     * @param integer $key2
     * @return Link|null
     */
	protected static function _link($table1, $key1, $table2, $key2)
	{
		$query =  Query::instance()
			->where ('fromTable', $table1)
			->where ('fromRowId', $key1)
			->where ('toTable', $table2)
			->where ('toRowId', $key2);
		return Model_Manager::byQuery('Link', $query);
	}

	/**
     * Связывает модели по схеме
	 *
     * @param string $table1
     * @param integer $key1
     * @param string $table2
     * @param integer $key2
     * @return Link|null
     */
	protected static function _schemeLink($scheme, $key1, $key2)
	{
		$link_class = $scheme['link'];
		$query = Query::instance ()
			->where ($scheme['fromKey'], $key1)
			->where ($scheme['toKey'], $key2);
		if (!empty ($scheme['addict'])) {
			foreach ($scheme['addict'] as $field => $value) {
				$query->where($field, $value);
			}
		}
		$link = Model_Manager::byQuery($link_class, $query);
		return $link;
	}

	/**
	 * Проверяет, связаны ли модели
	 *
	 * @param Model $model1
	 * @param Model $model2
	 * @return boolean
	 */
	public static function wereLinked(Model $model1, Model $model2)
	{
		if (strcmp($model1->table(), $model2->table()) > 0) {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }
		$scheme = Model_Scheme::linkScheme($model1->table(), $model2->table());
		if (!$scheme) {
			$link = self::_link(
				$model1->table(), $model1->key(),
				$model2->table(), $model2->key()
			);
		} else {
			$link = self::_schemeLink($scheme, $model1->key(), $model2->key());
		}
	    return (bool) $link;
	}

	/**
	 * Связывает модели
	 *
	 * @param Model $model1
	 * @param Model $model2
	 * @return Link
	 */
	public static function link(Model $model1, Model $model2)
	{
	    if (strcmp($model1->table(), $model2->table()) > 0) {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }
	    $scheme = Model_Scheme::linkScheme($model1->table(), $model2->table());
	    if (!$scheme) {
			$link = self::_link(
				$model1->table(), $model1->key(),
				$model2->table(), $model2->key()
			);
	    } else {
			$link = self::_schemeLink($scheme, $model1->key(), $model2->key());
	    }
	    if (!$link) {
			$className = 'Link';
			if (!$scheme) {
				$data = array(
					'fromTable'	=> $model1->table(),
					'fromRowId'	=> $model1->key(),
					'toTable'	=> $model2->table(),
					'toRowId'	=> $model2->key()
				);
			} else {
				$className = $scheme['link'];
				$data = array(
					$scheme['fromKey']	=> $model1->key(),
					$scheme['toKey']	=> $model2->key()
				);
				if (!empty($scheme['addict'])) {
					foreach ($scheme['addict'] as $key => $value) {
						$data[$key] = $value;
					}
				}
			}
			$link = Model_Manager::create($className, $data);
			$link->save();
	    }
	    return $link;
	}

	/**
	 * Возвращает коллекцию связанных с $model моделей типа $linked_model_name
	 *
	 * @param Model $model1
	 * @param string $model2
	 * @return Model_Collection
	 */
	public static function linkedItems (Model $model1, $model2)
	{
		$table1 = $model1->table();
		$table2 = $model2;
		if (strcmp($table1, $table2) > 0)
		{
			$tmp = $table1;
			$table1 = $table2;
			$table2 = $tmp;
		}
		$scheme = Model_Scheme::linkScheme($table1, $table2);
		$ids = array();
		if (!$scheme) {
			$dir = strcmp($model1->modelName(), $model2) > 0
				? 'from' : 'to';
			$otherDir = $dir == 'to' ? 'from' : 'to';
			$query = Query::instance()
				->select($dir . 'RowId')
				->from('Link')
				->where($dir . 'Table', $model2)
				->where($otherDir . 'RowId', $model1->key())
				->where($otherDir . 'Table', $model1->table());
			$ids = DDS::execute($query)->getResult()->asColumn();
		} else {
			$link_class = $scheme['link'];
			$query = Query::factory('Select');
			$column = null;
			if (strcmp($model1->table(), $model2) > 0) {
				$column = $scheme['fromKey'];
				$query
					->from($link_class)
					->where($scheme['toKey'], $model1->key());
			} else {
				$column = $scheme['toKey'];
				$query
					->from($link_class)
					->where($scheme['fromKey'], $model1->key());
			}
			$query->select($column);

			if (!empty($scheme['addict'])) {
				foreach ($scheme['addict'] as $field => $value) {
					$query->where ($field, $value);
				}
			}
			$ids = DDS::execute($query)->getResult ()->asColumn($column);
		}
		if (!$ids) {
			$result = Model_Collection_Manager::create($model2)->reset();
		} else {
			$result = Model_Collection_Manager::create($model2)
				->addOptions(array(
					'name'	=> '::Key',
					'key'	=> $ids
				));
		}
	    return $result;
	}

	/**
	 * Возвращает первичные ключи связанных моделей
	 *
	 * @param Model $model1
	 * 		Модель.
	 * @param string $linked_model_name
	 * 		Имя связанной модели.
	 * @return array
	 * 		Массив первичных ключей второй модели.
	 */
	public static function linkedKeys(Model $model1, $linked_model_name)
	{
		$collection = self::linkedItems($model1, $linked_model_name);
		return $collection->column(Model_Scheme::keyField($linked_model_name));
	}

	/**
	 * Удаляет связь моделей
	 *
	 * @param Model $model1
	 * @param Model $model2
	 */
	public static function unlink(Model $model1, Model $model2)
	{
	    if (strcmp($model1->table(), $model2->table()) > 0) {
			$tmp = $model1;
			$model1 = $model2;
			$model2 = $tmp;
	    }
		$scheme = Model_Scheme::linkScheme($model1->table(), $model2->table());
	    if (!$scheme) {
			$link = self::_link(
				$model1->table(), $model1->key(),
				$model2->table(), $model2->key()
			);
		} else {
			$link = self::_schemeLink($scheme, $model1->key(), $model2->key());
		}
	    if ($link) {
	        return $link->delete();
	    }
	}

	/**
	 * Удаление всех связей модели с моделями указанного типа
	 *
	 * @param Model $model1
	 * @param string $model2
	 */
	public static function unlinkWith(Model $model1, $model2)
	{
		$table1 = $model1->table();
		$table2 = $model2;
		if (strcmp($table1, $table2) > 0) {
			$tmp = $table1;
			$table1 = $table2;
			$table2 = $tmp;
		}
		$scheme = Model_Scheme::linkScheme($table1, $table2);
		if (!$scheme) {
			$query = Query::instance ()
				->delete ()
				->from ('Link');
			if (strcmp($model1->table(), $model2) > 0) {
				$query
					->where('fromTable', $model2)
					->where('toTable', $model1->table())
					->where('toRowId', $model1->key());
			} else {
				$query
					->where('fromTable', $model1->table())
					->where('fromRowId', $model1->key())
					->where('toTable', $model2);
			}
			DDS::execute($query);
		} else {
			$link_class = $scheme['link'];
			$query = Query::instance()
				->delete()
				->from($link_class);
			if (strcmp ($model1->table(), $model2) > 0) {
				$query->where ($scheme['toKey'], $model1->key());
			} else {
				$query->where ($scheme['fromKey'], $model1->key());
			}
			if (!empty($scheme['addict'])) {
				foreach ($scheme['addict'] as $field => $value) {
					$query->where($field, $value);
				}
			}
			DDS::execute($query);
		}
	}
}