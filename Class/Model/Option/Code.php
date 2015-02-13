<?php

/**
 * Опшен для получения сущности по полю code, для активации
 *
 * @author neon
 */
class Model_Option_Code extends Model_Option
{

    /**
     * @inhertidoc
     */
    public function before()
    {
        $this->query->where('code', $this->params['code']);
    }

}