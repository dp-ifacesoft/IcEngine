<?php

/**
 * Более быстрый вариант рандома чем order by rand() по порядку (напр. 5,6,7)
 *
 * @author Apostle
 */
class Model_Option_Random_Quick_Straight extends Model_Option
{
    /**
	 * @inheritdoc
	 */
	protected $queryName = 'Random_Quick_Straight';
}
