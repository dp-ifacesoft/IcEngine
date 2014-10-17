<?php

/**
 * Более быстрый вариант рандома чем order by rand(), 
 * но медленнее, чем Random_Quick_Straight
 *
 * @author Apostle
 */
class Model_Option_Random_Quick extends Model_Option
{
    /**
	 * @inheritdoc
	 */
	protected $queryName = 'Random_Quick';
}