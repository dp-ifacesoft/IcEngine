<?php

/**
 * Провайдер аннотаций
 *
 * @author morph
 */
class Data_Provider_Annotation extends Data_Provider_Abstract
{
    /**
     * Загруженные аннотации
     * 
     * @var array
     */
    protected $annotations = array();
    
    /**
     * Мета хелпер
     * 
     * @var Helper_Meta
     */
    protected $helper;
    
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
        if (isset($this->annotations[$key])) {
            $data = $this->annotations[$key];
            $annotationSet =  new Annotation_Set(
                $data['class'], $data['methods'], $data['properties']
            );
            return $annotationSet;
        }
        if (IcEngine::getLoader()->tryLoad($key . '_Meta')) {
            $annotationSet = $this->helper()->getAnnotationSet($key);
            $this->annotations[$key] = $annotationSet->getData();
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
     * Получить мета-хелпер
     * 
     * @return Helper_Meta
     */
    public function helper()
    {
        if (is_null($this->helper)) {
            $this->helper = new Helper_Meta();
            $this->helper->setPath($this->path);
        }
        return $this->helper;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $expiration = 0, $tags = array())
    {
        $this->helper()->set($key, $value->getData());
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