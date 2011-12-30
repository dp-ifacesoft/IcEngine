<?php

namespace Ice;

class Model_Collection_Indexed extends Model_Collection
{

    /**
     * @desc Получение объектов коллекции по набору полей.
     * Для корректной работы, необходимо существование схемы модели.
     *
     * @param array $conditions Индексируемые поля для выбора
     */
    public function selectBy (array $conditions)
    {
		Model_Manager::getInstance ()
			->byQuery ($this->modelName (), $conditions);
    }

}