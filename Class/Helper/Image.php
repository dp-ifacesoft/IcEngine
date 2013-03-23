<?php
/**
 *
 * Класс для работы с изображениями
 *
 * @Service("helperImage")
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
	protected $_configLoaded = false;

	/**
	 * @desc Код ошибки, возникшей в процессе обработки изображения.
	 * @var integer
	 */
	public $code;

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
	public $config = array (
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
	public $lastError = '';

	/**
	 * @desc Шаблон для имени файла
	 * @var string
	 */
	public $template = '{name}/{prefix}/{key}.{ext}';

	/**
	 * @desc Запись сообщения об ошибке
	 * @param string $message
	 * @param string $template
	 * @return null
	 */
	protected function _error ($error)
	{
		$this->code = 400;
		$this->lastError = $error;
		return null;
	}

	/**
	 *
	 * @param string $path
	 * @param array $values
	 * @return string
	 */
	protected function _filename($path, array $values = array())
	{
		$filename = $this->template;
		foreach ($values as $key => $value) {
			$filename = str_replace ('{' . $key . '}', $value, $filename);
		}
		$path = rtrim($path, '/') . '/' . $filename;
		$dir = dirname($path);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
			chmod($dir, 0777);
		}
		return $path;
	}

	/**
	 *
	 * @param string $type
	 * @return array
	 */
	protected function _sizing($type)
	{
		$this->initConfig();
		return
			(isset ($this->config['types']) &&
            isset ($this->config['types'][$type])) ?
			$this->config['types'][$type]->asArray() :
			$this->config['types']['default']->asArray();
	}

	public function initConfig()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $configManager = $serviceLocator->getService('configManager');
		if ($this->_configLoaded)
		{
			return;
		}
		$this->_configLoaded = true;
		$this->config = $configManager->get(__CLASS__, $this->config);
	}

    /**
     * Инициализация временного контента для поддержки загрузки изображений
     * @param Temp_Content $tc
     * 		Временный контенто.
     * @param array|string $types
     * 		Наименования типов из конфига.
     */
    public function initTempContent(Temp_Content $tc, $types)
    {
    	$this->initConfig();
    	$types = (array) $types;

    	$tc_types = array();
    	foreach ($types as $type)
    	{
    		if (isset ($this->config['types'][$type]))
    		{
    			$tc_types[$type] = $this->config['types'][$type];
    		}
    	}
    	$tc->attr(Helper_Image::TEMP_CONTENT_ATTRIBUTE, $tc_types);
    }

	protected function _log($message)
	{
		$filename = IcEngine::root() . '/log/image.log';
		file_put_contents(
			$filename,
			date() . ' ' . $message . PHP_EOL . PHP_EOL,
			FILE_APPEND
		);
	}

	/**
	 * Загрузка изображения для временного контента.
	 * @param Temp_Content $tc
	 * @param string $type
	 * @return null|Component_Image
	 */
	public function upload(Temp_Content $tc, $type = null)
	{
    	$this->code = 200;
		$sizings = $tc->attr(Helper_Image::TEMP_CONTENT_ATTRIBUTE);
		if ($sizings && is_array($sizings))
		{
			if ($type && isset($sizings[$type])) {
				$sizing = $sizings[$type];
			}
			else {
				$sizing = reset($sizings);
				$type = key($sizings);
			}
		}
		if (!isset ($sizing)) {
			$this->code = 400;
			throw new Zend_Exception('Type unsupported.', 400);
			return;
		}
		$sizing = array_merge($this->_sizing($type), $sizing);
		return $this->uploadSimple($tc->table(), $tc->key(), $type, $sizing);
	}

	/**
	 * @desc Простая загрузка изображения по ссылке
	 * @param string $table
	 * @param integer $row_id
	 * @param string $type
	 * @param array $sizing
	 * @return Component_Image|null
	 */
	public function uploadByUrl($url, $table, $rowId, $type, $sizing = null)
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
		return $this->uploadSimple($table, $rowId, $type);
	}

	/**
	 * @desc Простая загрузка изображения.
	 * @param string $table
	 * @param integer $row_id
	 * @param string $type
	 * @param array $sizing
	 * @return Component_Image|null
	 */
	public function uploadSimple($table, $row_id, $type, $sizing = null)
	{
		//$this->_log ('test');
        $locator = IcEngine::serviceLocator();
        $request = $locator->getService('request');
		$helperSiteLocation = $locator->getService('siteLocation');
        $modelManager = $locator->getService('modelManager');
        $helperImageResize = $locator->getService('helperImageResize');
		$host = $helperSiteLocation->getLocation();
        $user = $locator->getService('user')->getCurrent();
        $helperDate = $locator->getService('date');
        $file = $request->fileByIndex(0);
		if ($host == 'localhost') {
			$host = '';
		}
		else {
			$host = 'http://' . $host;
		}
		if (!$file) {
			$this->code = 400;
			return $this->_error ('not_received');
		}
		if (!$sizing) {
			$sizing = $this->_sizing($type);
		}
		$image = $modelManager->create('Component_Image', array(
			'table'			=> $table,
			'rowId'			=> $row_id,
			'date'			=> $helperDate->toUnix(),
			'author'		=> '',
			'text'			=> '',
            'name'          => $type,
			'largeUrl'		=> '',
			'smallUrl'		=> '',
			'originalUrl'	=> '',
			'User__id'		=> $user->key()
		));
		$image->save();
		$dst_path = $this->config['upload_path'];
		$original = $this->_filename(
			$dst_path,
			array (
				'name'		=> $type,
				'key'		=> $image->key(),
				'prefix'	=> self::ORIGINAL,
				'ext'		=> $file->extension
			)
		);
   		if (!$file->save($original)) {
			$this->code = 500;
			return $this->_error('unable_to_move');
		}
   	 	$info = getimagesize($original);
		if (!$info) {
			unlink($original);
			$image->delete();
			$this->$code = 400;
			return $this->_error('unable_get_size');
		}
   	 	$info = $helperImageResize->resize(
   	 		$original, $original,
   	 		$info[0], $info[1]
   	 	);
		if (!$info) {
			$this->code = 400;
			unlink($original);
			$image->delete();
			return $this->_error('unable_to_resize');
		}
		$filenames = array();

		if (!empty ($sizing['sizes']))
		{
			$created = array();
			foreach ($sizing['sizes'] as $prefix => $size) {
				$filename = $this->_filename(
					$dst_path,
					array (
						'name'		=> $type,
						'key'		=> $image->key(),
						'prefix'	=> $prefix,
						'ext'		=> $file->extension
					)
				);

				$thumb = $helperImageResize->resize(
					$original,
					$filename,
					min($info[0], $size['width']),
					min($info[1], $size['height']),
					isset($size['proportional']) ? $size['proportional']: true,
					isset($size['crop']) ? $size['crop'] : false,
					isset($size['fit']) ? $size['fit']: false
				);

				if (!$thumb) {
					foreach ($filenames as $fn) {
						unlink($fn);
					}
					$image->delete();

					return $this->_error('unable_to_resize');
				}
				$filenames [$prefix] = $filename;
			}
		}
		$attributes = array();
		if (!empty ($sizing['attributes'])) {
			foreach ($sizing['attributes'] as $key => $v) {
				$attributes[$key] = $request->post('attr_' . $key);
			}
		}
		$sizing ['sizes'][self::ORIGINAL] = array(
			'width'		=> $info[0],
			'height'	=> $info[1]
		);
        $filenames [self::ORIGINAL] = $original;
		$i = 0;
		foreach ($sizing ['sizes'] as $key => $size)
		{
			$tmp = array (
				$key . 'Url' => str_replace(
					$this->config['upload_path'],
					$this->config ['upload_url'],
					$filenames [$key]
				),
				$key . 'Width'	=> $size ['width'],
				$key . 'Height'	=> $size ['height']
			);
			$attributes = array_merge($attributes, $tmp);
			$i++;
		}
		$image->update($attributes);
		return $image;
	}
}
