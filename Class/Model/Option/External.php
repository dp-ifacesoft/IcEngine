<?php

/**
 * Связь модели с внешней моделью
 *
 * @author morph
 */
class Model_Option_External extends Model_Option
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        $fieldName = $this->params['model'] . '__id';
        $this->query->where($fieldName, $this->params['id']);
    }
}