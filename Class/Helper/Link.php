<?php
/**
 * Помощник для работы со связами многие-ко-многим моделей
 *
 * @author goorus, morph
 * @Service("helperLink")
 */
class Helper_Link extends Manager_Abstract
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
	protected function simpleLink($table1, $key1, $table2, $key2)
	{
		$query = $this->getService('query')
			->where('fromTable', $table1)
			->where('fromRowId', $key1)
			->where('toTable', $table2)
			->where('toRowId', $key2);
        $modelManager = $this->getService('modelManager');
		return $modelManager->byQuery('Link', $query);
	}

	/**
	 * Связывает модели
	 *
	 * @param Model $model1
	 * @param Model $model2
     * @param boolean autocreate
	 * @return Link
	 */
	public function link(Model $model1, Model $model2, $autocreate = true)
	{
	    if (strcmp($model1->table(), $model2->table()) > 0) {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }
	    $link = $this->simpleLink(
            $model1->table(), $model1->key(),
            $model2->table(), $model2->key()
        );
	    if (!$link && $autocreate) {
			$className = 'Link';
            $data = array(
                'fromTable'	=> $model1->table(),
                'fromRowId'	=> $model1->key(),
                'toTable'	=> $model2->table(),
                'toRowId'	=> $model2->key()
            );
			$link = $this->getService('modelManager')->create(
                $className, $data
            );
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
	public function linkedItems(Model $model1, $model2)
	{
		$table1 = $model1->table();
		$table2 = $model2;
		if (strcmp($table1, $table2) > 0)
		{
			$tmp = $table1;
			$table1 = $table2;
			$table2 = $tmp;
		}
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $dir = strcmp($model1->modelName(), $model2) > 0
            ? 'from' : 'to';
        $otherDir = $dir == 'to' ? 'from' : 'to';
        $query = $queryBuilder
            ->select($dir . 'RowId')
            ->from('Link')
            ->where($dir . 'Table', $model2)
            ->where($otherDir . 'RowId', $model1->key())
            ->where($otherDir . 'Table', $model1->table());
        $ids = $dds->execute($query)->getResult()->asColumn();
        $collectionManager = $this->getService('collectionManager');
		if (!$ids) {
			$result = $collectionManager->create($model2)->reset();
		} else {
			$result = $collectionManager->create($model2)
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
	public function linkedKeys(Model $model1, $linked_model_name)
	{
		$collection = $this->linkedItems($model1, $linked_model_name);
        $modelScheme = $this->getService('modelScheme');
		return $collection->column($modelScheme->keyField($linked_model_name));
	}

	/**
	 * Удаляет связь моделей
	 *
	 * @param Model $model1
	 * @param Model $model2
	 */
	public function unlink(Model $model1, Model $model2)
	{
	    if (strcmp($model1->table(), $model2->table()) > 0) {
			$tmp = $model1;
			$model1 = $model2;
			$model2 = $tmp;
	    }
        $link = $this->simplelink(
            $model1->table(), $model1->key(),
            $model2->table(), $model2->key(),
            false
        );
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
	public function unlinkWith(Model $model1, $model2)
	{
		$table1 = $model1->table();
		$table2 = $model2;
		if (strcmp($table1, $table2) > 0) {
			$tmp = $table1;
			$table1 = $table2;
			$table2 = $tmp;
		}
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
        $query = $queryBuilder
            ->delete()
            ->from('Link');
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
        $dds->execute($query);
	}

    /**
	 * Проверяет, связаны ли модели
	 *
	 * @param Model $model1
	 * @param Model $model2
	 * @return boolean
	 */
	public function wereLinked(Model $model1, Model $model2)
	{
		if (strcmp($model1->table(), $model2->table()) > 0) {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }
        $link = $this->simpleLink(
            $model1->table(), $model1->key(),
            $model2->table(), $model2->key(),
            false
        );
	    return (bool) $link;
	}
}