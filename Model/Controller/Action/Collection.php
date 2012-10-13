<?php
/**
 * 
 * @desc Коллекция экшенов контроллеров.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Action_Collection extends Model_Collection
{
	/**
	 * @desc Применить транспорт
	 * @param string $transport_name
	 * @param Data_Transport $transport
	 */
	public function applyTransport ($transport_name, Data_Transport $transport)
	{
		foreach ($this as $item)
		{
			$item->data ($transport_name, $transport);
		}
	}
}