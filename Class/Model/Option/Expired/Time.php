<?php

/**
 * Выводить по истечению времени
 *
 * @author markov
 */
class Model_Option_Expired_Time extends Model_Option 
{
    /**
     * @inheritdoc
     */
    public function before()
    {
        $date = new DateTime();
        $date->sub(new DateInterval('PT1M'));
        $dateFormated = $date->format('Y-m-d H:i:s');
        $this->query
            ->where($this->params['field'] . ' != ?', '0000-00-00')
            ->where($this->params['field'] . ' < ?', $dateFormated);
    }
}
