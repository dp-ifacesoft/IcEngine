<?php

/**
 * Провайдер аннотаций
 *
 * @author morph
 */
class Data_Provider_Annotation extends Data_Provider_Abstract
{
    /**
     * Путь до директории с аннотациями
     *
     * @var string
     */
    protected $path = 'Ice/Var/Annotation/';

    /**
	 * @inheritdoc
	 */
	public function _setOption($key, $value)
	{
		switch ($key) {
			case 'path': $this->path = $value; break;
		}
		return parent::_setOption($key, $value);
	}

    /**
     * @inheritdoc
     */
    public function get($key, $plain = false)
    {
        $filename = IcEngine::root() . $this->path . $key;
        if (file_exists($filename)) {
            $json = json_decode(file_get_contents($filename), true);
            $annotationSet =  new Annotation_Set(
                $json['class'], $json['methods'], $json['properties']
            );
            return $annotationSet;
        }
    }

    /**
     * Получить путь до директории с аннотациями
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $expiration = 0, $tags = array())
    {
        $filename = IcEngine::root() . $this->path . $key;
		if (!file_exists(IcEngine::root() . $this->path)) {
			mkdir(IcEngine::root() . $this->path);
		}
        file_put_contents($filename, json_encode($value->getData()));
    }

    /**
     * Изменить путь до директории с аннотациями
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
}