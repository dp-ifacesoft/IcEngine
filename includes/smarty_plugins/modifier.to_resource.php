<?php
/**
 * @desc Экспорт модели в js Ice.Resource_Manager
 * @param Model $model
 */
function smarty_modifier_to_resource (Model $model)
{
	return
		'<script type="text/javascript">' .
			'Ice.Resource_Manager.set("Model", "' . 
			$model->resourceKey() . '", ' .
			json_encode ($model->toJs ()) . ');' .
		'</script>';
}