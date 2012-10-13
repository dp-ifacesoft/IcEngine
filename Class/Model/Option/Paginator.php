<?php
/**
 * 
 * @desc Подключение пагинатора для коллекции
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class Model_Option_Paginator extends Model_Option
{
	
	public function before ()
	{
		Loader::load ('Paginator');
		if (isset ($this->params ['input']))
		{
			$pg = Paginator::fromInput ($this->params ['input']);
		}
		else
		{
			$pg = new Paginator (
				$this->params ['page'],
				$this->params ['limit']
			);
		}
		$this->collection->setPaginator ($pg);
		$this->query->calcFoundRows ();
		$this->query->limit ($pg->pageLimit, $pg->offset ());
	}
	
}
