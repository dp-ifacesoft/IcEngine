<?php
/**
 *
 * Класс для работы с изображениями
 * @author Юрий
 *
 */
class Helper_Image
{

	/**
	 *
	 * @desc Префикс для оригинала изображения.
	 * 	Будет создан в любом случае, не смотря на конфиг
	 * @var string
	 */
	const ORIGINAL = 'original';

	/**
	 * Аттрибут, где перечислены возможные типы подгружаемых изображений.
	 * @var string
	 */
	const TEMP_CONTENT_ATTRIBUTE = 'images';

	/**
	 * Загружен ли конфиг
	 * @var boolean
	 */
	protected static $_configLoaded = false;

	/**
	 * @desc Код ошибки, возникшей в процессе обработки изображения.
	 * @var integer
	 */
	public static $code;

	/**
	 *
	 * @var array
	 * @desc Конфиг для миниатюрок
	 * @tutorial
	 * 	$ob->config = array (
	 * 		'sizings'	=> array (
	 * 			'avatar'	=> array (
	 * 				'sizes'	=> array (
	 * 					'small'	=> array (
	 * 						'width'	    => 50,
	 * 						'height'	=> 80,
	 * 						'crop'		=> true
	 * 					),
	 * 					'big'	=> array (
	 *						'width'	    => 160,
	 *						'height'    => 200,
	 *						'crop'	    => true
	 *					)
	 *				)
	 *			),
	 *			'gallery_image'	=> array (
	 *				'attributes'	=> array (
	 *					'title'	        => 'string',
	 *					'description'	=> 'string'
	 *				),
	 *				'sizes'	=> array (
	 *					'thumb'	    => array (
	 *						'width'	    => 190,
	 *						'height'	=> 140,
	 *						'crop'		=> true
	 *					),
	 *				)
	 *			)
	 *		)
	 *  );
	 */
	public static $config = array (
		'upload_path'	=> 'uploads/',
		'upload_url'	=> '/uploads/',
		'types'			=> array ()
	);

	/**
	 *
	 * @var string
	 */
	const TEMP_PATH = 'images/tmp/';

	/**
	 * @desc Последнее сообщение об ошибке
	 * @var string
	 */
	public static $lastError = '';

	/**
	 * @desc Шаблон для имени файла
	 * @var string
	 */
	public static $template = '{name}/{prefix}/{key}.{ext}';

	/**
	 * @desc Запись сообщения об ошибке
	 * @param string $message
	 * @param string $template
	 * @return null
	 */
	protected static function _error ($error)
	{
		self::$code = 400;
		self::$lastError = $error;
		return null;
	}

	/**
	 *
	 * @param string $path
	 * @param array $values
	 * @return string
	 */
	protected static function _filename ($path, array $values = array ())
	{
		$filename = self::$template;
		foreach ($values as $key => $value)
		{
			$filename = str_replace ('{' . $key . '}', $value, $filename);
		}
		$path = rtrim ($path, '/') . '/' . $filename;
		$dir = dirname ($path);
		if (!is_dir ($dir))
		{
			mkdir ($dir, 0777, true);
			chmod ($dir, 0777);
		}
		return $path;
	}

	/**
	 *
	 * @param string $type
	 * @return array
	 */
	protected static function _sizing ($type)
	{
		self::initConfig ();

		return
			(isset (self::$config ['types']) && isset (self::$config ['types'][$type])) ?
			self::$config ['types'][$type]->asArray() :
			self::$config ['types']['default']->asArray();
	}

	public static function initConfig ()
	{
		if (self::$_configLoaded)
		{
			return;
		}

		self::$_configLoaded = true;
		self::$config = Config_Manager::get (__CLASS__, self::$config);
	}

    /**
     * Инициализация временного контента для поддержки загрузки изображений
     * @param Temp_Content $tc
     * 		Временный контенто.
     * @param array|string $types
     * 		Наименования типов из конфига.
     */
    public static function initTempContent (Temp_Content $tc, $types)
    {
    	self::initConfig ();
    	$types = (array) $types;

    	$tc_types = array ();
    	foreach ($types as $type)
    	{
    		if (isset (self::$config ['types'][$type]))
    		{
    			$tc_types [$type] = self::$config ['types'][$type];
    		}
    	}

    	$tc->attr (self::TEMP_CONTENT_ATTRIBUTE, $tc_types);
    }

	private function _log ($message)
	{
		$filename = IcEngine::root () . '/log/image.log';
		file_put_contents (
			$filename,
			date () . ' ' . $message . PHP_EOL . PHP_EOL,
			FILE_APPEND
		);
	}

	/**
	 * Загрузка изображения для временного контента.
	 * @param Temp_Content $tc
	 * @param string $type
	 * @return null|Component_Image
	 */
	public static function upload (Temp_Content $tc, $type = null)
	{
		self::$code = 200;

		$sizings = $tc->attr (self::TEMP_CONTENT_ATTRIBUTE);

		if ($sizings && is_array ($sizings))
		{
			if ($type && isset ($sizings [$type]))
			{
				$sizing = $sizings [$type];
			}
			else
			{
				$sizing = reset ($sizings);
				$type = key ($sizings);
			}
		}

		if (!isset ($sizing))
		{
			self::$code = 400;
			throw new Zend_Exception ('Type unsupported.', 400);
			return;
		}

		$sizing = array_merge (self::_sizing ($type), $sizing);

		return self::uploadSimple ($tc->table (), $tc->key (), $type, $sizing);
	}

	/**
	 * @desc Простая загрузка изображения по ссылке
	 * @param string $table
	 * @param integer $row_id
	 * @param string $type
	 * @param array $sizing
	 * @return Component_Image|null
	 */
	public static function uploadByUrl ($url, $table, $rowId, $type, $sizing = null)
	{
		$info = getimagesize($url);
		if (!$info) {
			return;
		}

		$_FILES['image'] = array(
			'name'		=> $url,
			'tmp_name'	=> $url,
			'type'		=> $info['mime'],
			'size'		=> 1,
			'error'		=> false
		);
		return Helper_Image::uploadSimple($table, $rowId, $type);
	}

	/**
	 * @desc Простая загрузка изображения.
	 * @param string $table
	 * @param integer $row_id
	 * @param string $type
	 * @param array $sizing
	 * @return Component_Image|null
	 */
	public static function uploadSimple ($table, $row_id, $type, $sizing = null)
	{
		//$this->_log ('test');
		$file = Request::fileByIndex (0);

		$host = Helper_Site_Location::getLocation ();
		if ($host == 'localhost')
		{
			$host = '';
		}
		else
		{
			$host = 'http://' . $host;
		}

		if (!$file)
		{
			self::$code = 400;
			return self::_error ('not_received');
		}

		if (!$sizing)
		{
			$sizing = self::_sizing ($type);
		}

		$image = new Component_Image (array (
			'table'			=> $table,
			'rowId'			=> $row_id,
			'date'			=> Helper_Date::toUnix (),
			'name'			=> $type,
			'author'		=> '',
			'text'			=> '',
			'largeUrl'		=> '',
			'smallUrl'		=> '',
			'originalUrl'	=> '',
			'User__id'		=> User::id (),
            'sort'          => 0,
            'active'        => 1
		));
		$image->save ();
		$dst_path = self::$config ['upload_path'];

		$original = self::_filename (
			$dst_path,
			array (
				'name'		=> $type,
				'key'		=> $image->key (),
				'prefix'	=> self::ORIGINAL,
				'ext'		=> $file->extension
			)
		);

   		if (!$file->save ($original))
		{
			self::$code = 500;
			return self::_error ('unable_to_move');
		}

   	 	$info = getimagesize ($original);

		if (!$info)
		{
			unlink ($original);
			$image->delete ();

			self::$code = 400;
			return self::_error ('unable_get_size');
		}

   	 	$info = Helper_Image_Resize::resize (
   	 		$original, $original,
   	 		$info [0], $info [1]
   	 	);

		if (!$info)
		{
			self::$code = 400;
			unlink ($original);
			$image->delete ();

			return self::_error ('unable_to_resize');
		}

		$filenames = array ();

		if (!empty ($sizing ['sizes']))
		{
			$created = array ();

			foreach ($sizing ['sizes'] as $prefix => $size)
			{
				$filename = self::_filename (
					$dst_path,
					array (
						'name'		=> $type,
						'key'		=> $image->key (),
						'prefix'	=> $prefix,
						'ext'		=> $file->extension
					)
				);

				$thumb = Helper_Image_Resize::resize (
					$original,
					$filename,
					min ($info [0], $size ['width']),
					min ($info [1], $size ['height']),
					isset ($size ['proportional']) ? $size ['proportional']: true,
					isset ($size ['crop']) ? $size ['crop'] : false,
					isset ($size ['fit']) ? $size ['fit']: false
				);

				if (!$thumb)
				{
					foreach ($filenames as $fn)
					{
						unlink ($fn);
					}
					$image->delete ();

					return self::_error ('unable_to_resize');
				}
				$filenames [$prefix] = $filename;
			}
		}

		$attributes = array ();

		if (!empty ($sizing ['attributes']))
		{
			foreach ($sizing ['attributes'] as $key => $v)
			{
				$attributes [$key] = Request::post ('attr_' . $key);
			}
		}


		$sizing ['sizes'] [self::ORIGINAL] = array (
			'width'		=> $info [0],
			'height'	=> $info [1]
		);

		$filenames [self::ORIGINAL] = $original;

		$i = 0;
		foreach ($sizing ['sizes'] as $key => $size)
		{
			$tmp = array (
				$key . 'Url'	=> str_replace (
					self::$config ['upload_path'],
					//$host . self::$config ['upload_url'],
					self::$config ['upload_url'],
					$filenames [$key]
				),
				$key . 'Width'	=> $size ['width'],
				$key . 'Height'	=> $size ['height']
			);
			$attributes = array_merge ($attributes, $tmp);
			$i++;
		}

		$image->update($attributes);

		return $image;
	}

}
