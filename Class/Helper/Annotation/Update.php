<?php

/**
 * Хелпер для обновления аннотаций
 *
 * @author morph
 * @Service("helperAnnotationUpdate")
 */
class Helper_Annotation_Update extends Helper_Abstract
{
    /**
     * Получить имена классов для обновления
     *
     * @param string $path
     * @return array
     */
    public function getClasses($path)
    {
        $classes = array();
        if ($path) {
            $paths = array(IcEngine::root() . $path);
        } else {
            $loader = IcEngine::getLoader();
            $paths = array_merge(
                $loader->getPaths('Class'),
                $loader->getPaths('Model'),
                $loader->getPaths('Controller')
            );
        }
        foreach ($paths as $path) {
            if (!$path || !is_dir($path)) {
                continue;
            }
            if (strpos($path, 'Class') === false &&
                strpos($path, 'Controller') === false &&
                strpos($path, 'Model') === false) {
                continue;
            }
            ob_start();
            system('find ' . $path . '** | grep .php');
            $content = ob_get_contents();
            ob_end_clean();
            $files = explode(PHP_EOL, $content);
            foreach ($files as $file) {
                if (!$file || !is_file($file)) {
                    continue;
                }
                if (substr($file, -4, 4) != '.php') {
                    continue;
                }
                $content = file_get_contents($file);
                if (strpos($content, 'namespace IcEngine\\') !== false) {
                    continue;
                }
                $matches = array();
                preg_match_all(
                    '#class\s+([A-Z][A-Za-z_0-9]+)#', $content, $matches
                );
                if (empty($matches[1][0])) {
                    continue;
                }
                $classes[$file] = array(
                    'class' => $matches[1][0],
                    'file'  => $file
                );
            }
        }
        ksort($classes);
        return array_values($classes);
    }

    /**
     * Получить делигаты
     *
     * @param array $delegees
     * @param string $className
     * @param array $delegeeData
     * @param string $filename
     * @return array
     */
    public function getDelegees($delegees, $className, &$delegeeData, $filename)
    {
        $helperAnnotation = $this->getService('helperAnnotation');
        $annotationManager = $helperAnnotation->getManager();
        if (!$annotationManager->compare($className)) {
            $annotationSet = $annotationManager->getSource()->get($className);
            $annotationManager->getRepository()->set($className, $annotationSet);
            $annotation = $annotationSet->getData();
        } else {
            $annotation = $annotationManager->getAnnotation($className)
                ->getData();
        }
        $moduleName = !empty($annotation['class']['Module'])
            ? reset($annotation['class']['Module'][0]) : null;
        foreach ($annotation as $delegeeType => $annotationData) {
            if (!isset($delegees[$delegeeType]) || !$annotationData) {
                continue;
            }
            foreach ($annotationData as $annotationName => $data) {
                foreach ($delegees[$delegeeType] as $delegee) {
                    if (strpos($annotationName, $delegee) === 0) {
                        if (is_string($data)) {
                            $annotationName = $data;
                            $data = array(0);
                        }
                        $keys = array_keys($data);
                        if (is_numeric($keys[0])) {
                            $delegeeData[$delegee][$className]
                            [$annotationName] = array(
                                'class' => $className,
                                'data'  => $data,
                                'file'  => $filename
                            );
                        }
                    } elseif ($data) {
                        $key = $className . '/' . $annotationName;
                        if (!is_array($data)) {
                            continue;
                        }
                        foreach ($data as $subAnnotationName => $subData) {
                            if (is_numeric($subAnnotationName)) {
                                continue;
                            }
                            if (strpos($subAnnotationName, $delegee) ===
                                false) {
                                continue;
                            }
                            $delegeeData[$delegee][$key][$subAnnotationName] =
                               array( 
                                    'class'     => $className,
                                    'part'      => $annotationName,
                                    'module'    => $moduleName,
                                    'data'      => $subData,
                                    'file'      => $filename
                                );
                        }
                    }
                }
            }
        }
        return $delegeeData;   
    }
}