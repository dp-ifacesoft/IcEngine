<?php

/**
 * Аннотации объектов
 *
 * @author morph
 */
class Controller_Annotation extends Controller_Abstract
{
    /**
     * @inheritdoc
     */
    protected $config = array(
        'class'             => array(
            'Orm', 'Service', 'Override', 'AutoResync'
        ),
        'methods'           => array(
            'Route', 'Cache', 'Schedule'
        ),
        'properties'        => array(
            'Service'
        )
    );

    /**
     * Сброк аннотации
     */
    public function flush($name, $context)
    {
        $this->task->setTemplate(null);
        $user = $context->user->getCurrent();
        if ($user->key() >= 0 && !$user->hasRole('editor')) {
            return;
        }
        $config = $context->configManager->get('Data_Provider_Manager');
        $annotationConfig = $config['Annotation'];
        $filename = IcEngine::root() . $annotationConfig['params']['path'] .
            $name;
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * Обновить аннотации
     */
    public function update($path, $verbose, $author, $context)
    {
        $this->task->setTemplate(null);
        $user = $context->user->getCurrent();
        if ($user->key() >= 0 && !$user->hasRole('editor')) {
            return;
        }
        $classes = array();
        if ($path) {
            $paths = array(IcEngine::root() . $path);
        } else {
            $paths = array_merge(
                IcEngine::getLoader()->getPaths('Class'),
                IcEngine::getLoader()->getPaths('Model'),
                IcEngine::getLoader()->getPaths('Controller')
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
            system('find ' . $path . '** | grep -e "\.php$"');
            $content = ob_get_contents();
            ob_end_clean();
            $files = explode(PHP_EOL, $content);
            foreach ($files as $file) {
                if (!$file || !is_file($file)) {
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
                $classes[] = array(
                    'class' => $matches[1][0],
                    'file'  => $file
                );
            }
        }
        if (!$classes) {
            return;
        }
        $delegees = $this->config();
        $delegeeData = array();
        $annotationManager = IcEngine::serviceLocator()->getSource()
            ->getAnnotationManager();
        foreach ($classes as $i => $class) {
            IcEngine::getLoader()->load($class['class']);
            if ($verbose) {
                echo '#' . ($i + 1) . ' ' . $class['class'] .
                    ' (' . $class['file'] . ') done.' . PHP_EOL;
            }
            $className = $class['class'];
            $context->controllerManager->call('Annotation', 'flush', array(
                'name'  => $class['class']
            ));
            $annotation = $annotationManager->getAnnotation($class['class'])
                ->getData();
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
                                    'data'  => $data
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
                                $delegeeData[$delegee][$key]
                                [$subAnnotationName] =
                                array(
                                    'class'     => $className,
                                    'part'      => $annotationName,
                                    'module'    => $moduleName,
                                    'data'      => $subData
                                );
                            }
                        }
                    }
                }
            }
        }
        foreach ($delegeeData as $delegeeName => $data) {
            $controllerName = 'Annotation_' . $delegeeName;
            $context->controllerManager->call(
                $controllerName, 'update', array(
                    'data'      => $data,
                    'author'    => $author
                )
            );
        }
    }
}