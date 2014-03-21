<?php
/**
 * 
 * @desc Опция для выбора адреса получателя рассылки
 * @author Yury Shveodv
 * @package IcEngine
 * 
 */
class Subscribe_Subscriber_Status_Option_Select_Contact extends Model_Option
{
	
	public function before ()
	{
		$this->query
			->select ('Subscribe_Subscriber.contact')
			->leftJoin (
				'Subscribe_Subscriber',
				'Subscribe_Subscriber.id=Subscribe_Subscriber_Status.Subscribe_Subscriber__id'
			);
	}
	
}
