<?php

/**
 * Хелпер для коллекции
 *
 * @author neon
 * @Service("helperCollection")
 */
class Helper_Collection
{
	/**
	 * Переназначение коллекции на модель
	 *
	 * @param Model $model
	 * @param Model_Collection $collection
	 * @return void
	 */
	public static function rejoin($model, $collection)
	{
		$collection->update(array(
			'table'	=> $model->modelName(),
			'rowId'	=> $model->key()
		));
	}

	/**
	 * Упорядочивание списка для вывода дерева по полю parentId
	 *
	 * @param Model_Collection $collection
	 * @param boolean $include_unparented Оставить элементы без предка.
	 * Если false, элементы будут исключены из списка.
	 *
	 * @return Model_Collection
	 */
	public static function sortByParent($collection,
		$include_unparented = false)
	{
		$list = $collection->items();
		if (empty($list)) {
			// Список пуст
			return $collection;
		}
		$firstIDS = $collection->column('id');
		$parents = array();
		$child_of = 0;
		$result = array();
		$i = 0;
        $keyField = $collection->keyField();
		$index = array(0 => 0);
		$full_index = array(-1 => '');
		do {
			$finish = true;
			for ($i = 0; $i < count($list); ++$i) {
				if ($list[$i]['parentId'] == $child_of) {
					//
					if (!isset($index[count($parents)])) {
						$index[count($parents)] = 1;
					} else {
						$index[count($parents)]++;
					}
					$n = count($result);
					$result[$n] = $list[$i];
					$result[$n]['data']['level'] = count($parents);
					$result[$n]['data']['index'] = $index[count($parents)];
					$parents_count = count($parents);
					if ($parents_count > 0) {
						$full_index = $full_index[$parents_count - 1] .
							$index[count($parents)];
					} else {
						$full_index = (string) $index[count($parents)];
					}
					$result[$n]['data']['full_index'] = $full_index;
					$result[$n]['data']['broken_parent'] = false;
					$full_index[$parents_count] = $full_index . '.';
					array_push($parents, $child_of);
					$child_of = $list[$i][$keyField];
					for ($j = $i; $j < count($list) - 1; $j++) {
						$list[$j] = $list[$j + 1];
					}
					array_pop($list);
					$finish = false;
					break;
				}
			}
			// Элементы с неверно указанным предком
			if ($finish && count($parents) > 0) {
				$index[count($parents)] = 0;
				$child_of = array_pop($parents);
				$finish = false;
			}
		} while (!$finish);
		/**
		 * чтобы не портить сортировку, если таковая есть у
		 * коллекции, с использованием элементов без родителей
		 *
		 * сортируем по level 0, докидываем дочерних
		 */
		if ($include_unparented) {
			//out досортированный
			$newResult = array();
			//без родителей, неотсортированные
			$listIDS = array();
			//отсортированные родители: level = 0
			$resultIDS = array();
			//отсортированные дочерние: level > 0
			$resultSubIDS = array();
			for ($i = 0; $i < count($list); $i++) {
				$listIDS[$list[$i][$keyField]] = $i;
			}
			for ($i = 0; $i < count($result); $i++) {
				if ($result[$i]->parentId == 0) {
					$parentId = $result[$i][$keyField];
					$resultIDS[$result[$i][$keyField]] = $i;
				} else {
					$resultSubIDS[$parentId][$result[$i][$keyField]] = $i;
				}
			}
			for ($i = 0; $i < count($firstIDS); $i++) {
				if (isset($resultIDS[$firstIDS[$i]])) {
					$newResult[] = $result[$resultIDS[$firstIDS[$i]]];
					if (isset($resultSubIDS[$firstIDS[$i]])) {
						foreach ($resultSubIDS[$firstIDS[$i]] as $index) {
							$newResult[] = $result[$index];
						}
					}
				} elseif (isset($listIDS[$firstIDS[$i]])) {
					$newResult[] = $list[$listIDS[$firstIDS[$i]]];
				}
			}
			$result = $newResult;
		}
		$collection->setItems($result);
		return $collection;
	}
}