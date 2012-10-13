<?php
/**
 * 
 * @desc Компонент - комментарий
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Component_Comment extends Model_Component
{
	
	/**
	 * @desc Возвращает родительский комментарий.
	 * @return Component_Comment
	 */
	public function getParent ()
	{
		return Model_Manager::get ($this->modelName (), $this->parentId);
	}
	
	/**
	 * @return string
	 */
	public function text ()
	{
		return htmlspecialchars_decode (trim (stripslashes ($this->text)));	
	}
	
	/**
	 * @desc Возвращает уровень комментария относительно корня.
	 * @param integer $rate
	 * 		Множитель. Результат будет домножен на указанную величину.
	 * @return integer
	 */
	public function level ($rate = 1)
	{
	    if ($this->parentId)
	    {
	        return ($this->getParent ()->level () + 1) * $rate;
	    }
	    else
	    {
	        return 0;
	    }
	}
	
}