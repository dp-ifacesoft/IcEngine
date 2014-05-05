<?php

/**
 * Расширенный контент
 *
 * @author morph
 */
class Controller_Content_Extending extends Controller_Abstract
{
	/**
	 * Плагин для расширенного контента
	 */
	public function plugin($row)
	{
		if (!$row->key() || $row['name'] == 'Simple') {
			return;
		}
		$extending = $row->extending();
		$task = Controller_Manager::call('Admin_Database', 'row', array(
			'table'	=> $extending->modelName(),
			'rowId'	=> $row->key()
		));
		$transaction = $task->getTransaction();
		$buffer = $transaction->buffer();
		if (!$buffer || empty($buffer['fields'])) {
			return;
		}
		$fields = $buffer['fields']->__toArray();
		unset($fields['id']);
		$this->_output->send(array(
			'fields'	=> $fields,
			'row'		=> $extending,
			'table'		=> $row->modelName()
		));
	}
}