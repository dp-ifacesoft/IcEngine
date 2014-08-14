<?php
/**
 * 
 * @desc Фильтр для сериализации модели
 * @author Юрий
 * @package IcEngine
 *
 */
class Filter_Model_Serialize
{
	
	/**
	 * @desc Десириализация модели в строку.
	 * В сериализованнам виде будет записанно название класса, 
	 * а не название модели (прим. View_Render_Front вместо View_Render),
	 * так как при десиаризации будет вызываться Loader.
	 * В случае, если объект был удален, в $data будет передано null.
	 * @param Model $data
	 * @return string
	 */
	public function filter ($data)
	{	
		$data = $data->generic () ? $data->generic () : $data;

		return
			is_object ($data) ?
			get_class ($data) . ':' . json_encode ($data->getFields ()) :
			null;
	}
	
}