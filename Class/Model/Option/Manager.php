<?php

/**
 * Менеджер опций
 *
 * @author morph, goorus
 * @Service("modelOptionManager")
 */
class Model_Option_Manager extends Manager_Abstract
{
    /**
	 * Создание опции
     *
	 * @param string $name
	 * @param Model_Collection $collection
	 * @param array $params
	 */
	public function create($name, Model_Collection $collection, $params)
	{
		$className = $this->getClassName($name, $collection);
		$option =  new $className($collection, $params);
        return $option;
	}
    
    /**
     * Выполняет опции коллекции
     *
     * @param Model_Collection $collection
     * @param Query_Abstract $query
     * @param array $options
     */
	protected function execute($method, $collection, $options = array())
    {
        if (!$options) {
            return;
        }
        foreach ($options as $option) {
            $data = $this->normalize($option);
            if (!$data) {
                continue;
            }
            $option = $this->create($data[0], $collection, $data[1]);
            if (!$option->isSatisfiedBy()) {
                continue;
            }
            $option->query = $collection->query();
            call_user_func(array($option, $method));
        }
    }

    /**
     * Выполняет часть опций after
     *
     * @param Model_Collection $collection
     * @param array $options
     */
    public function executeAfter($collection, $options)
    {
        $this->execute('after', $collection, $options);
    }

    /**
     * Выполняет часть опций before
     *
     * @param Model_Collection $collection
     * @param array $options
     */
    public function executeBefore($collection, $options)
    {
        $this->execute('before', $collection, $options);
    }
    
    /**
	 * Возвращает название класса опции
     *
	 * @param string $option Название опции
	 * @param Model_Collection $collection Коллекция.
	 * @return string
	 */
	public function getClassName($option, $collection)
	{
		$p = strpos($option, '::');
		if ($p === false) {
			// Опция этой модели
			return $collection->table() . '_Option_' . $option;
		} elseif ($p === 0) {
			// Базовые опции всех моделей, например '::Limit'
			return 'Model_Option_' . substr($option, $p + 2);
		}
		// Опция другой модели
		return substr($option, 0, $p) . '_Option_' . substr($option, $p + 2);
	}

    /**
     * Приводит опцию к стандартной форме
     *
     * @param mixed $option
     * @return array
     */
    protected function normalize($option)
    {
        if (is_string($option)) {
            return array($option, null);
        }
        if (is_array($option)) {
            if (isset($option['name'])) {
                $name = $option['name'];
                unset($option['name']);
                return array($name, $option);
            }
            if (isset($option[0]) && is_string($option[0])) {
                return array(
                    $option[0], isset($option[1]) ? $option[1] : null
                );
            }
        }
    }
}