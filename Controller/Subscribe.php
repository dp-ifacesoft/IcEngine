<?php

class Controller_Subscribe extends Controller_Abstract
{

    protected function _codeFail ()
    {
        $this->_task->setTemplate ('Controller/Subscribe/codeFail.tpl');
    }

    /**
     * @return Subscribe_Subscriber_Join|null
     */
    protected function _checkJoin ()
    {
        $code = trim ($this->_input->receive ('code'), '/\\');

        if (!$code)
        {
            $this->_codeFail ();
            return null;
        }

        $join = Model_Manager::byQuery (
            'Subscribe_Subscriber_Join',
            Query::instance ()
            	->where ('code', $code)
        );

        if (!$join)
        {
            $this->_codeFail ();
            return null;
        }

        return $join;
    }

    public function activate ()
    {
        $join = $this->_checkJoin ();

        if (!$join)
        {
            return ;
        }

        $join->update (array (
            'active'	=> 1,
            'code'		=> ''
        ));

        $this->_output->send ('join', $join);
    }

    public function deactivate ()
    {
        $join = $this->_checkJoin ();

        if (!$join)
        {
            return ;
        }

        $subscriber = Model_Manager::byKey (
        	'Subscribe_Subscriber',
        	$join->Subscribe_Subscriber->key ()
        );

        if ($subscriber)
        {
	        Subscribe_Subscriber_Attribute::deleteFor (
	        	$join->Subscribe_Subscriber
	        );
        	$subscriber->delete ();
        }
        $join->delete ();
    }

	public function index ()
	{

	}

}