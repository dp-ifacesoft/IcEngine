<?php

class Subscribe_Subscriber_Join extends Model
{
    
	/**
	 * @return string
	 */
	protected function _genConfirmCode ()
	{
	    return uniqid (time (), true);
	}
    
    /**
     * @return Subscribe_Subscriber_Join
     */
    public function regenCode ()
    {
        return $this->update (array (
            'code'	=> $this->_genConfirmCode ()
        ));
    }
    
}