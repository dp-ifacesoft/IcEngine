<?php

/**
 * Глобальный опшен для получения сущности, посредством поиска по полю title
 *
 * @author neon
 */
class Model_Option_Title extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        $this->query->where('title LIKE ?', '%' . $this->params['value'] . '%');
    }
}