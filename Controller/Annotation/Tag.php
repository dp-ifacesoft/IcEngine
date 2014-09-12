<?php

/**
 * Контроллер для аннотаций типа "Tag"
 * 
 * @author morph
 */
class Controller_Annotation_Tag extends Controller_Abstract
{
    /**
     * Записать тэги
     * 
     * @Template(null)
     * @Context("controllerTagManager", "helperConverter", "helperCodeGenerator")
     */
    public function flush($context)
    {
        $tags = $context->controllerTagManager->getTags();
        $filename = IcEngine::root() . 'Ice/Config/Controller/Tag/Manager.php';
        if (!$tags) {
            if (is_file($filename)) {
                unlink($filename);
            }
            return;
        }
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $content = $context->helperConverter->arrayToString($tags);
        $output = $context->helperCodeGenerator->fromTemplate(
            'controllerTag', array('content' => $content)
        );
        file_put_contents($filename, $output);
    }
    
    /**
     * Обновить аннотации
     * 
     * @Template(null)
     * @Validator("Not_Null"={"data"})
     * @Context("helperAnnotationTag", "helperArray", "controllerTagManager")
     */
    public function update($data, $context)
    {
        foreach ($data as $controllerAction => $annotationData) {
            $controllerAction = substr($controllerAction, strlen('Controller_'));
            if (!isset($annotationData['Tag'])) {
                continue;
            }
            $subData = $annotationData['Tag'];
            $tagData = $subData['data'][0];
            foreach ($tagData as $tag) {
                $context->controllerTagManager->append($tag, $controllerAction);
            }
        }
    }
}