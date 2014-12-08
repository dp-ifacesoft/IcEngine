<?php

/**
 * Контроллер для аннотаций типа "Event"
 * 
 * @author morph
 */
class Controller_Annotation_Event extends Controller_Abstract
{
    /**
     * Обновить аннотации
     * 
     * @Template(null)
     * @Context("helperCodeGenerator", "helperConverter")
     * @Validator("Not_Null"={"data"})
     */
    public function update($data, $context)
    {
        $config = $context->configManager->get('Event_Manager')->__toArray();
        foreach ($config as $signalName => $slots) {
            if (!is_array($slots)) {
                $config[$signalName] = (array) $slots;
            }
        }
        foreach ($data as $className => $annotationData) {
            if (strpos($className, 'Event_Slot_') === false) {
                continue;
            }
            $slotName = substr($className, strlen('Event_Slot_'));
            $annotationData = reset($annotationData['Event\\On']['data']);
            foreach ($annotationData as $signalName) {
                if (!isset($config[$signalName])) {
                    $config[$signalName] = array();
                } elseif (!is_array($config[$signalName])) {
                    $config[$signalName] = (array) $config[$signalName];
                }
                if (!in_array($slotName, $config[$signalName])) {
                    $config[$signalName][] = $slotName;
                }
            }
        }
        $filename = IcEngine::root() . 'Ice/Config/Event/Manager.php';
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $content = $context->helperConverter->arrayToString($config);
        $output = $context->helperCodeGenerator->fromTemplate(
            'controllerTag',
            array (
                'content'  => $content
            )
        );
        file_put_contents($filename, $output);
    }
}