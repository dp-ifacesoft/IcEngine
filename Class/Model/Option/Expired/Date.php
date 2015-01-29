<?php

/**
 * Выводить по дате истечения
 *
 * @author markov
 */
class Model_Option_Expired_Date extends Model_Option 
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        $date = new DateTime();
        $date->sub(new DateInterval('P1M'));
        $dateFormated = $date->format('Y-m-d');
        $this->query
            ->where($this->params['field'] . ' != ?', '0000-00-00')
            ->where($this->params['field'] . ' < ?', $dateFormated);
    }
}
