<?php

/**
 * Urldecode
 *
 * @author morph
 */
class Data_Filter_Url_Decode extends Data_Filter_Abstract
{
    /**
     * @inheritdoc
     */
	public function filter($data)
	{
		return urldecode($data);
	}
}