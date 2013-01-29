<?php

class Model_Plugin_Comment_Count 
{
	public function calc ($model)
	{
		return $model->attr ('commentCount');
	}
}