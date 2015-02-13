<?php

/**
 * Контроллер для аннотаций типа "ServiceAccessor"
 * 
 * @author morph
 */
class Controller_Annotation_ServiceAccessor extends Controller_Abstract
{
    /**
     * Распарсить аннотацию
     * 
     * @Context("helperCodeGenerator")
     * @Template(null)
     * @Validator("Not_Null"={"data"})
     */
    public function update($data, $context) 
    {
        foreach ($data as $className => $subData) {
            $classReflection = new \ReflectionClass($className);
            if ($classReflection->hasMethod('getService')) {
                continue;
            }
            $output = $context->helperCodeGenerator->fromTemplate(
                'getService', array()
            );
            $filename = $subData['ServiceAccessor']['file'];
            $content = file_get_contents($filename);
            $lastPos = mb_strrpos($content, '}', 0, 'UTF-8');
            $newContent = mb_substr($content, 0, $lastPos, 'UTF-8') .
                $output . PHP_EOL . '}';
            file_put_contents($filename, $newContent);
        }
    }
}