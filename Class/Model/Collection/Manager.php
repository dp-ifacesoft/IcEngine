<?php

/**
 * Менеджер коллекций
 *
 * @author morph, goorus
 * @Service("collectionManager")
 */
class Model_Collection_Manager extends Manager_Abstract
{
	/**
     * @inheritdoc
	 */
	protected $config = array(
		'cache_provider'	=> 'mysqli_cache',
		'delegee'			=> array(
			'Model'				=> 'Simple',
			'Model_Defined'		=> 'Defined',
			'Model_Factory'		=> 'Simple',
            'Model_Sync'        => 'Simple'
		)
	);

	/**
	 * Возвращает коллекцию по запросу.
     *
	 * @author goorus
	 * @param string $modelName Модель коллекции.
	 * @param Query_Abstract $query Запрос.
	 * @return Model_Collection
	 */
	public function byQuery($modelName, Query_Abstract $query)
	{
		$collection = $this->create($modelName);
		$collection->setQuery($query);
		return $collection;
	}

    /**
     * Вызрать делигата коллекции и получить данные
     *
     * @param Model_Collection $collection
     * @param Query_Abstract $query
     * @return array
     */
    public function callDelegee($collection, $query)
    {
        $modelName = $collection->modelName();
        // Делегируемый класс определяем по первому или нулевому
        // предку.
        $parents = class_parents($modelName);
        $parent = reset($parents);
        $config = $this->config();
        foreach ($parents as $current) {
            if (isset($config['delegee'][$current])) {
                $parent = $current;
                break;
            }
        }
        $delegee = 'Model_Collection_Manager_Delegee_' .
            $config['delegee'][$parent];
        $pack = call_user_func(array($delegee, 'load'), $collection, $query);
        return $pack;
    }

	/**
	 * Создает коллекцию по имени.
     *
	 * @param string $modelName Модель колекции.
	 * @return Model_Collection Коллекция.
	 */
	public function create($modelName)
	{
        $collection = new Model_Collection();
        $collection->setModelName($modelName);
		return $collection;
	}

	/**
	 * Получить коллекцию из хранилища по запросу и опшинам
     *
	 * @param Model_Collection
	 * @param Query_Abstract $query
	 */
	public function load(Model_Collection $collection, Query_Abstract $query)
	{
		$modelName = $collection->modelName();
        $keyField = $collection->keyField();
        $data = $this->callDelegee($collection, $query);
        $addicts = $collection->data('addicts');
		$iterator = $collection->currentIterator();
		if ($iterator) {
			return $iterator->setData($data['items']);
		}
        $modelManager = $this->getService('modelManager');
		foreach ($data['items'] as $i => $item) {
            if (isset($item[$keyField])) {
                $data['items'][$i] = $modelManager->get(
                    $modelName, $item[$keyField], $item
                );
            } else {
                unset($data['items'][$i]);
                continue;
            }
			if (!empty($addicts[$i])) {
				$data['items'][$i]->data($addicts[$i]);
			}
		}
		$collection->setItems($data['items']);
	}
}