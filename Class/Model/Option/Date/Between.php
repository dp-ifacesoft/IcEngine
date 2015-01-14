<?php
/**
 * Опшен на поле типа DATE - чтобы значение было в указанном диапазоне
 *
 * @author LiverEnemy
 */

class Model_Option_Date_Between extends Model_Option
{
    public function before()
    {
        $field      = $this->params['field'];
        $table      = $this->collection->table();
        if (empty($this->params['min']) || empty($this->params['max']))
        {
            throw new Exception(__METHOD__ . ' requires a min and max date params to be set');
        }
        $minDate    = $this->params['min'];
        $maxDate    = $this->params['max'];
        $this->query->where($table . '.' .$field . ' BETWEEN DATE("' . $minDate . '") AND DATE("' . $maxDate . '")');
    }
} 