<?php

/**
 * Провайдер, получающий или сохраняющих результат в файл
 * 
 * @author morph
 */
class Data_Provider_File extends Data_Provider_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function get($key, $plain = false)
	{
        $filename = IcEngine::root() . $this->prefix . md5($key);
        if (!is_file($filename)) {
            return false;
        }
        $content = file_get_contents($filename);
        return json_decode($content, true);
	}
    
	/**
	 * @inheritdoc
	 */
	public function set($key, $value, $expiration = 0, $tags = array())
	{
		$filename = IcEngine::root() . $this->prefix . md5($key);
        $content = json_encode($value);
        file_put_contents($filename, $content);
	}
}