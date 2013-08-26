<?php

/**
 * Хелпер для работы с мета данными объекта
 * 
 * @author morph
 * @Service("helperMeta")
 * @Injectible
 */
class Helper_Meta 
{
    /**
     * Имя шаблона
     *
     * @var string
     */
    const TPL_NAME = 'Helper/Code/Generator/metaClass';
    
    /**
     * Хелпер для преобразования массива в строку
     * 
     * @var Helper_Converter
     */
    protected $helper;
    
    /**
     * Путь до хранилища мета-файлов
     * 
     * @var string
     */
    protected $path;
    
    /**
     * Получить мета данные класса
     * 
     * @param string $className
     * @return array
     */
    public function get($className)
    {
        $className = $className . '_Meta';
        if (!IcEngine::getLoader()->tryLoad($className)) {
            return null;
        }
        return array(
            'class'         => $className::$classAnnotations,
            'methods'       => $className::$methodsAnnotations,
            'properties'    => $className::$propertiesAnnotations
        );
    }
    
    /**
     * Получить сет аннотаций
     * 
     * @param string $className
     * @return Annotation_Set
     */
    public function getAnnotationSet($className)
    {
        $className = $className . '_Meta';
        if (!IcEngine::getLoader()->tryLoad($className)) {
            return null;
        }
        return $className::getAnnotationSet();
    }
    
    /**
     * Получить имя класса по имени мета-класса
     * 
     * @param string $metaName
     * @return string
     */
    public function getClassName($metaName)
    {
        return substr($metaName, 0, -5);
    }
    
    /**
     * Получить путь до мета-класса
     * 
     * @param string $className
     * @return string
     */
    public function getClassPath($className)
    {
        $filename = IcEngine::root() . $this->path . 
            str_replace('_', '/', $className) . '.php';
        $dirname = dirname($filename);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }
        return $filename;
    }
    
    /**
     * Получить путь до хранилища
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Получить хелпер
     * 
     * @return Helper_Converter
     */
    public function helper()
    {
        if (is_null($this->helper)) {
            $this->helper = new Helper_Converter();
        }
        return $this->helper;
    }
    
    /**
     * Изменить мета-класс
     * 
     * @param string $className
     * @param array $data
     */
    public function set($className, $data)
    {
        $notEmpty = false;
        foreach ($data as $i => &$section) {
            if (!is_array($section)) {
                continue;
            }
            foreach ($section as $j => $value) {
                if ($value) {
                    $notEmpty = true;
                } else {
                    unset($section[$j]);
                }
            }
            if (!$section) {
                unset($data[$i]);
            }
        }
        if ($notEmpty) {
            $this->write($className . '_Meta', $data);
        }
    }
    
    /**
     * Изменить путь до хранилища
     * 
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
    
    /**
     * Записать файл мета-класса
     * 
     * @param string $className
     * @param array $data
     */
    public function write($className, $data)
    {
        $viewRenderManager = IcEngine::getManager('View_Render');
        $viewRender = $viewRenderManager->byName('Smarty');
        $helper = $this->helper();
        foreach ($data as &$row) {
            $row = $helper->arrayToString($row);
        }
        $viewRender->assign(array(
            'className'     => $this->getClassName($className),
            'data'          => $data,
            'currentDate'   => date('Y-m-d H:i:s')
        ));
        $content = $viewRender->fetch(self::TPL_NAME);
        $filename = $this->getClassPath($className);
        file_put_contents($filename, $content);
    }
}