<?php

/**
 * Генератор простых инкрементирующихся последовательностей
 * 
 * @author morph
 */
class Sequence_Increment extends Sequence_Abstract
{
    /**
     * @inheritdoc
     */
    public function next($prev = null)
    {
        return ++$prev;
    }
}