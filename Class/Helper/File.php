<?php
/**
 * 
 * @desc Помощник работы с файлами
 * @author Юрий
 * @package IcEngine
 *
 */
class Helper_File
{
	
	/**
	 * @desc Возвращает расширение файла
	 * @param $filename Имя файла
	 * @return string Расширение
	 */
	public static function extention ($filename)
	{
		return strtolower (substr (strrchr ($filename, '.'), 1));
	}
	
	/**
	 * @desc Удаляет переданные файлы
	 * @param string|array $file Путь до файла (файлов).
	 * @param $_ Произвольное количество путей до файлов.
	 * @return integer Количество удаленных файлов
	 */
	public static function delete ($file)
	{
	    $result = 0;
	    $files = is_array ($file) ? $file : func_get_args ();
	    
	    foreach ($files as $file)
	    {
	        if (is_file ($file))
	        {
	            unlink ($file);
	            $result++;
	        }
	    }
	    
	    return $result;
	}
	
}