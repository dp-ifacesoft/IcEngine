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
            'Orm', 'Service', 'Override', 'AutoResync', 'ServiceAccessor'
        ),
        'methods'           => array(
            'Route', 'RouteImport', 'Cache', 'Schedule', 'Tag'
        ),
        'properties'        => array(
            'Service', 'Generator', 'Acl'
        )
    );

    /**
     * Сброк аннотации
     *
     * @Template(null)
     * @Validator("User_Cli")
     */
    public function flush($name, $context)
    {
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
     *
     * @Template(null)
     * @Validator("User_Cli")
     */
    public function update($path, $name, $verbose, $author, $context)
    {
        $helperAnnotationUpdate = $this->getService('helperAnnotationUpdate');
        $classes = $helperAnnotationUpdate->getClasses($path);
        $delegees = $this->config();
        $delegeeData = array();
        foreach ($classes as $i => $class) {
            if ($name && $class['class'] != $name) {
                continue;
            }
            if ($verbose) {
                echo '#' . ($i + 1) . ' ' . $class['class'] .
                    ' (' . $class['file'] . ') done.' . PHP_EOL;
            }
            $className = $class['class'];
            $context->controllerManager->call('Annotation', 'flush', array(
                'name'  => $class['class']
            ));
            ob_start();
            $helperAnnotationUpdate->getDelegees(
                $delegees, $className, $delegeeData, $class['file']
            );
            $content = ob_get_contents();
            ob_end_clean();
            if ($content) {
                echo 'Output: ' . $className . PHP_EOL;
            }
        }
        foreach ($delegeeData as $delegeeName => $data) {
            $controllerName = 'Annotation_' . $delegeeName;
            echo 'Run: ' . $delegeeName . PHP_EOL;
            $context->controllerManager->call(
                $controllerName, 'update', array(
                    'data'      => $data,
                    'author'    => $author
                )
            );
        }
    }
}