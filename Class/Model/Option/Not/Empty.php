<?php

/**
 * Получить записи, с непустым полем
 *
 * @author Apostle
 */
class Model_Option_Not_Empty extends Model_Option
{
    /**
     * @inheritdoc
     */
    protected $queryName = 'Not_Empty';
}
