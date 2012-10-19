<?php
/**
 *
 * @desc Контроллер компонента рейтинга
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Component_Rating extends Controller_Abstract
{
	/**
	 * @desc Голосование
	 * @param string $table
	 * @param integer $row_id
	 * @param integer $value Оценка
	 * @return Component_Rating $rating
	 */
	public function vote ()
	{
		list (
			$table,
			$row_id,
			$value
		) = $this->_input->receive (
			'table',
			'row_id',
			'value'
		);
	
		$rating = Component_Rating::voteFor ($table, $row_id, $value);

		$this->_output->send (array (
			'rating'	=> $rating
		));
	}

	/**
	 * @desc Кнопки голосования.
	 * @param Model $model Оцениваемая модель.
	 */
	public function pollBar ()
	{
		$model = $this->_input->receive ('model');
		$rating = $model->component ('Rating');
		$this->_output->send (array (
			'model'		=> $model,
			'rating'	=> $rating
		));
	}

}