<?php
/**
 *
 * @desc Помощник для работы со связами многие-ко-многим моделей.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Link
{

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
		return Model_Manager::byQuery (
		    'Link',
		    Query::instance ()
			    ->where ('fromTable', $table1)
			    ->where ('fromRowId', $key1)
			    ->where ('toTable', $table2)
			    ->where ('toRowId', $key2)
		);
	}

	protected static function _schemeLink ($scheme, $key1, $key2)
	{
		$link_class = $scheme ['link'];

		$query = Query::instance ()
			->where ($scheme ['fromKey'], $key1)
			->where ($scheme ['toKey'], $key2);

		if (!empty ($scheme ['addict']))
		{
			foreach ($scheme ['addict'] as $field => $value)
			{
				$query
					->where ($field, $value);
			}
		}

		$link = Model_Manager::byQuery (
			$link_class,
			$query
		);

		return $link;
	}

	/**
	 * @desc Проверяет, связаны ли модели.
	 * @param Model $model1
	 * @param Model $model2
	 * @return boolean
	 */
	public static function wereLinked (Model $model1, Model $model2)
	{
		if (strcmp ($model1->modelName (), $model2->modelName ()) > 0)
	    {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }

		$scheme = Model_Scheme::linkScheme (
			$model1->modelName (),
			$model2->modelName ()
		);

		if (!$scheme)
		{
			$link = self::_link (
				$model1->modelName (), $model1->key (),
				$model2->modelName (), $model2->key ()
			);
		}
		else
		{
			$link = self::_schemeLink (
				$scheme,
				$model1->key (),
				$model2->key ()
			);
		}
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
	    if (strcmp ($model1->modelName (), $model2->modelName ()) > 0)
	    {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;

	    }

	    $scheme = Model_Scheme::linkScheme (
			$model1->modelName (),
			$model2->modelName ()
		);

	    if (!$scheme)
	    {
		//	echo '   <b>Нет схемы</b> <br />';

			$link = self::_link (
				$model1->modelName (), $model1->key (),
				$model2->modelName (), $model2->key ()
			);

	    }
	    else
	    {
			$link = self::_schemeLink (
				$scheme,
				$model1->key (),
				$model2->key ()
			);
	    }
	    if (!$link)
	    {
			if (!$scheme)
			{
				Loader::load ('Link');
				$link = new Link (array (
					'fromTable'	=> $model1->table (),
					'fromRowId'	=> $model1->key (),
					'toTable'	=> $model2->table (),
					'toRowId'	=> $model2->key ()
				));

				$link->save ();

			//	print_r($link) . '<br />';
			}
			else
			{
				$link_class = $scheme ['link'];

				Loader::load ($link_class);

				$data = array (
					$scheme ['fromKey']	=> $model1->key (),
					$scheme ['toKey']	=> $model2->key ()
				);

				if (!empty ($scheme['addict']))
				{
					foreach ($scheme['addict'] as $key => $value)
					{
						$data [$key] = $value;
					}
				}

				$link = new $link_class ($data);

				$link->save ();
			}
	    }

	    return $link;
	}

	/**
	 * Возвращает коллекцию связанных с $model моделей типа $linked_model_name.
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
		$result = Model_Collection_Manager::create($model2)
			->addOptions(array(
				'name'	=> '::Key',
				'key'	=> $ids
			));
	    return $result;
	}

	/**
	 * @desc Возвращает первичные ключи связанных моделей.
	 * @param Model $model1
	 * 		Модель.
	 * @param string $linked_model_name
	 * 		Имя связанной модели.
	 * @return array
	 * 		Массив первичных ключей второй модели.
	 */
	public static function linkedKeys (Model $model1, $linked_model_name)
	{
		$collection = self::linkedItems ($model1, $linked_model_name);
		return $collection->column (Model_Scheme::keyField ($linked_model_name));
	}

	/**
	 * @desc Удаляет связь моделей.
	 * @param Model $model1
	 * @param Model $model2
	 */
	public static function unlink (Model $model1, Model $model2)
	{
	    if (strcmp ($model1->modelName (), $model2->modelName ()) > 0)
	    {
			$tmp = $model1;
			$model1 = $model2;
			$model2 = $tmp;
	    }

		 $scheme = Model_Scheme::linkScheme (
			$model1->modelName (),
			$model2->modelName ()
		);

	    if (!$scheme)
	    {
			$link = self::_link (
				$model1->modelName (), $model1->key (),
				$model2->modelName (), $model2->key ()
			);
		}
		else
		{
			$link = self::_schemeLink (
				$scheme,
				$model1->key (),
				$model2->key ()
			);
		}

	    if ($link)
	    {
	        return $link->delete ();
	    }
	}

	/**
	 * Удаление всех связей модели с моделями указанного типа.
	 * @param Model $model1
	 * @param string $model2
	 */
	public static function unlinkWith (Model $model, $linked_model_name)
	{
		$table1 = $model->modelName ();
		$table2 = $linked_model_name;

		if (strcmp ($table1, $table2) > 0)
		{
			$tmp = $table1;
			$table1 = $table2;
			$table2 = $tmp;
		}

		$scheme = Model_Scheme::linkScheme (
			$table1,
			$table2
		);

		if (!$scheme)
		{
			$query =
				Query::instance ()
					->delete ()
					->from ('Link');

			if (strcmp ($model->modelName (), $linked_model_name) > 0)
			{
				$query
					->where ('fromTable', $linked_model_name)
					->where ('toTable', $model->modelName ())
					->where ('toRowId', $model->key ());
			}
			else
			{
				$query
					->where ('fromTable', $model->modelName ())
					->where ('fromRowId', $model->key ())
					->where ('toTable', $linked_model_name);
			}

			DDS::execute ($query);
		}
		else
		{
			$link_class = $scheme ['link'];
 
			$query = Query::instance ()
				->delete ()
				->from ($link_class);

			if (strcmp ($model->modelName (), $linked_model_name) > 0)
			{
				$query
					->where ($scheme ['toKey'], $model->key ());
			}
			else
			{
				$query
					->where ($scheme ['fromKey'], $model->key ());
			}

			if (!empty ($scheme ['addict']))
			{
				foreach ($scheme ['addict'] as $field => $value)
				{
					$query
						->where ($field, $value);
				}
			}

			//echo $query->translate ();

			DDS::execute ($query);
		}
	}
}