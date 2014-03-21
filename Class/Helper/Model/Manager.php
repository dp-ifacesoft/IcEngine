<?php

/**
 * Помощник менеджера моделей
 *
 * @author neon
 * @Service("helperModelManager")
 */
class Helper_Model_Manager extends Helper_Abstract
{
    /**
     * Применить генераторы к модели
     * 
     * @param Model $model
     */
    public function applyGenerators($model)
    {
        $annotations = $model->getAnnotations()['properties'];
        foreach (array_keys($model->scheme()->fields->__toArray()) as 
            $fieldName) {
            if (!isset($annotations[$fieldName]['Data\\Generator'])) {
                continue;
            }
            $generatorData = reset($annotations[$fieldName]['Data\\Generator']);
            list($generatorName, $method) = explode('.', reset($generatorData));
            $params = array();
            if (!is_numeric(key($generatorData))) {
                $params = $generatorName;
                $generatorName = key($generatorData);
            }
            $generator = $this->getService($generatorName);
            if (!$generator) {
                throw new ErrorException('Incorrect data generator');
            }
            $value = call_user_func_array(
                array($generator, $method), array_merge(array($model), $params)
            );
            $model->set($fieldName, $value);
        }
    }
    
    /**
     * Получить название сигнала по умолчанию
     *
     * @param string $methodName
     * @param Model $model
     * @return string
     */
    public function getDefaultSignal($methodName, $model)
    {
        list(, $method) = explode('::', $methodName);
        return strtolower($method) . implode('', explode('_', $model->table()));
    }

    /**
     * Получить имя родительского класса
     *
     * @param string $modelName
     * @return string
     */
    public function getParentClass($modelName, $config)
    {
        $parents = class_parents($modelName);
        foreach ($parents as $parent) {
            if (isset($config['delegee'][$parent])) {
                return $parent;
            }
        }
    }

    /**
     * Вызвать сигнал
     *
     * @param array $signals
     * @param Model $model
     */
    public function notifySignal($signals, $model)
    {
        foreach ((array) $signals as $signalName) {
            $eventManager = $this->getService('eventManager');
            $signal = $eventManager->getSignal($signalName);
            $signal->setData(array('model' => $model));
            $signal->notify();
        }
    }

    /**
	 * Получение данных модели из источника данных.
	 *
     * @param Model $model
	 * @return boolean
	 */
	public function read(Model $model)
	{
		$key = $model->key();
		if (!$key) {
			return false;
		}
        $modelName = $model->table();
        $queryBuilder = $this->getService('query');
		$query = $queryBuilder
			->select('*')
			->from($modelName)
			->where($model->keyField(), $key);
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $data = $dataSource->execute($query)->getResult()->asRow();
        if ($data) {
            $data = array_merge($data, $model->asRow());
            $model->set($data);
            return true;
        }
        return false;
	}

    /**
	 * Сохранение модели в источник данных
     *
	 * @param Model $model
	 * @param boolean $hardInsert
	 */
	public function write(Model $model, $hardInsert = false)
	{
        $modelName = $model->table();
        $key = $model->key();
        $keyField = $model->keyField();
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $queryBuilder = $this->getService('query');
        $modelFields = $model->getFields();
        $schemeFields = $modelScheme->scheme($modelName)->fields;
        foreach (array_keys($modelFields) as $fieldName) {
            if (isset($schemeFields[$fieldName])) {
                continue;
            }
            unset($modelFields[$fieldName]);
        }
        if ($key && !$hardInsert) {
            $query = $queryBuilder
                ->update($modelName)
                ->values($modelFields)
                ->where($keyField, $key)
				->limit(1);
            $dataSource->execute($query);
        } else {
            if (!$key) {
                $key = $modelScheme->generateKey($model);
            }
            if ($key) {
                $model->set($keyField, $key);
                $modelFields[$keyField] = $key;
            } else {
                $model->unsetField($keyField);
                if (isset($modelFields[$keyField])) {
                    unset($modelFields[$keyField]);
                }
            }
            $query = $queryBuilder
                ->insert($modelName)
                ->values($modelFields);
            $result = $dataSource->execute($query)->getResult();
            if (!$key) {
                $key = $result->insertId();
                $model->set($keyField, $key);
            }
        }
	}
}