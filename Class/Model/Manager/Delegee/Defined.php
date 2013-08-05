<?php

/**
 * Класс для создания определенных моделей
 *
 * @author morph, goorus
 */
class Model_Manager_Delegee_Defined extends Model_Manager_Delegee_Abstract
{
	/**
	 * Создает предопределенную модель
     *
	 * @param string $model Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $object Объект или данные
	 * @return Model В случае успеха объект, иначе null.
	 */
	public function get($modelName, $key, $object = null)
	{
		$rows = $modelName::$rows;
        $params = is_array($object) ? $object : array();
        $loader = IcEngine::serviceLocator()->getService('loader');
		foreach ($rows as $row){
			if ($row['id'] != $key) {
                continue;
            }
            if (!isset($row['name'])) {
                continue;
            }
            $delegeeName = $modelName . '_' . $row['name'];
            if ($loader->tryLoad($delegeeName)) {
                $modelName = $delegeeName;
            }
            break;
		}
        $model = new $modelName($params);
        return $model;
	}

    /**
     * @inheritdoc
     */
    public function remove($model)
    {
        throw new Exception('Model defined "' . $model->modelName() .
            '" can not remove');
    }

    /**
     * @inheritdoc
     */
    public function set($model, $hardInsert = false)
    {
        throw new Exception('Model defined "' . $model->modelName() .
            '" can not set');
    }
}