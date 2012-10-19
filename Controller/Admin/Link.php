<?php

/**
 * @desc Контроллер админки линковки
 * @package IcEngine
 * @author Илья Колесников
 * @copyright i-complex.ru
 */
class Controller_Admin_Link extends Controller_Abstract
{
	public function index ()
	{
		if (!User::getCurrent ()->isAdmin ())
		{
			return $this->replaceAction (
				'Error',
				'accessDenied'
			);
		}

		$tables = Helper_Data_Source::tables ();

		$result = array ();

		foreach ($tables as $table)
		{
			$result [] = array (
				'name'	=> $table->Name,
				'title'	=> $table->Comment
			);
		}

		$this->_output->send (array (
			'tables'	=> $result
		));
	}

	/**
	 * @desc Получаем модели коллекции
	 */
	public function items ()
	{
		$table = $this->_input->receive ('table');

		$class_name = Model_Scheme::tableToModel ($table);

		$result = array ();

		$collection = Model_Collection_Manager::create ($class_name);

		foreach ($collection as $model)
		{
			$result [] = array (
				'id'	=> $model->key (),
				'name'	=> $model->title ()
			);
		}

		$this->_task->setTemplate (null);

		$this->_output->send (array (
			'data'	=> array (
				'items'	=> $result
			)
		));
	}

	/**
	 * @desc Получаем модели, в том числе прилинкованные
	 */
	public function linkRoll ()
	{
		list (
			$table1,
			$table2,
			$row1
		) = $this->_input->receive (
			'table1',
			'table2',
			'row1'
		);

		$class_name1 = Model_Scheme::tableToModel ($table1);
		$class_name2 = Model_Scheme::tableToModel ($table2);

		$model1 = Model_Manager::get (
			$class_name1,
			$row1
		);

		$model2_collection = Model_Collection_Manager::create ($class_name2);

		$linked_models = Helper_Link::linkedItems (
			$model1,
			$class_name2
		);

		$result = array ();

		foreach ($model2_collection as $i=> $model)
		{
			$result [$i] = array (
				'id'		=> $model->key (),
				'name'		=> $model->title (),
				'linked'	=> 0
			);

			$filtered = $linked_models->filter (array (
				Model_Scheme::keyField ($class_name2)	=> $model->key ()
			));

			if ($filtered->count ())
			{
				$result [$i]['linked'] = 1;
			}
		}

		$this->_task->setTemplate (null);

		$this->_output->send (array (
			'data'	=> array (
				'items'	=> $result
			)
		));
	}

	/**
	 * @desc Сохраняем изменения
	 */
	public function save ()
	{
		list (
			$table1,
			$table2,
			$row1,
			$models2
		) = $this->_input->receive (
			'table1',
			'table2',
			'row1',
			'models2'
		);

		$class_name1 = Model_Scheme::tableToModel ($table1);
		$class_name2 = Model_Scheme::tableToModel ($table2);

		$model1 = Model_Manager::get (
			$class_name1,
			$row1
		);

		Helper_Link::unlinkWith (
			$model1,
			$class_name2
		);

		if (!$models2)
		{
			return;
		}

		foreach ($models2 as $model2_id)
		{
			$model2 = Model_Manager::byKey (
				$class_name2,
				$model2_id
			);

			if ($model2)
			{
				Helper_Link::link (
					$model1,
					$model2
				);
			}
		}

		$this->_task->setTemplate (null);
	}

	/**
	 * @desc Получаем список таблиц
	 */
	public function tables ()
	{
		$tables = Helper_Data_Source::tables ();

		$result = array ();

		foreach ($tables as $table)
		{
			$result [] = array (
				'name'	=> $table->Name,
				'title'	=> $table->Comment
			);
		}

		$this->_task->setTemplate (null);

		$this->_output->send (array (
			'data'	=> array (
				'tables'	=> $result
			)
		));
	}
}
