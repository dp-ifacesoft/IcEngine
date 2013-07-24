<?php

/**
 * Контроллер для аннотаций типа "Service"
 * 
 * @author morph
 */
class Controller_Annotation_Service extends Controller_Abstract
{
    /**
     * Обновить аннотации
     * 
     * @Context("helperCodeGenerator")
     * @Template(null)
     * @Validator("Not_Null"={"data"})
     */
    public function update($data, $context)
    {
        $annotationManager = IcEngine::serviceLocator()->getSource()
            ->getAnnotationManager();
        $services = array();
        foreach ($data as $className => $annotationData) {
            if (!isset($annotationData['Service'])) {
                continue;
            }
            $disableConstruct = false;
            if (strpos($className, '/') === false) {
                $annotation = $annotationManager->getAnnotation($className)
                    ->getData();
                $disableConstruct = isset($annotation['class']['Injectible']);
            }
            $subData = $annotationData['Service'];
            foreach ($subData['data'] as $serviceData) {
                $serviceName = array_shift($serviceData);
                $services[$serviceName] = $serviceData;
                if (empty($services[$serviceName]) && $disableConstruct) {
                    $services[$serviceName]['disableConstruct'] = true;
                }
                $services[$serviceName]['class'] = $className;
                $services[$serviceName]['name'] = $serviceName;
            }
        }
        if (!$services) {
            return;
        }
        $configServices = $this->getService('configManager')->get(
            'Service_Source'
        )->__toArray();
        if ($configServices) {
            foreach ($configServices as $serviceName => $service) {
                if (!isset($services[$serviceName])) {
                    $service['name'] = $serviceName;
                    $services[$serviceName] = $service;
                }
            }
        }
        ksort($services);
        $output = $context->helperCodeGenerator->fromTemplate(
            'service',
            array (
                'services'  => $services
            )
        );
        $filename = IcEngine::root() . 'Ice/Config/Service/Source.php';
        file_put_contents($filename, $output);
    }
}