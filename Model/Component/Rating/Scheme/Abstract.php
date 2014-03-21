<?php
/**
 *
 * @desc Базовая схема рейтинга.
 * @author Юрий
 * @package IcEngine
 *
 */
class Component_Rating_Scheme_Abstract extends Model_Factory_Delegate
{

	/**
	 * @desc Изменение рейтинга
	 * @param string $table Модель
	 * @param integer $row_id Запись
	 * @param integer $value Изменение рейтинга.
	 * Может быть величиной изменения или типом, в зависимости от схемы.
	 * @return Component_Rating
	 */
	public function vote ($table, $row_id, $value)
	{
		$rating = Model_Manager::byQuery(
			'Component_Rating',
			Query::instance ()
			->where ('table', $table)
			->where ('rowId', $row_id)
		);
		if (!$rating)
		{
			$rating = Model_Manager::create (
				'Component_Rating',
				array (
					'table'	=> $table,
					'rowId'	=> $row_id,
					'votes'	=> 0,
					'value'	=> 0,
					'changeTime'	=> Helper_Date::NULL_DATE
				)
			);
		}
        $rating->increment($value);
        return $this->strategy($rating);
	}

    /**
     * Стратегия голосования
     * 
     * @return Rating $rating Возвращает
     */
    public function strategy(Rating $rating)
    {
        return $rating;
    }
    
}