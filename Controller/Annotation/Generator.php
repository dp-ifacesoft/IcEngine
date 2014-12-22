<?php

/**
 * Контроллер для аннотаций типа "Generator"
 * 
 * @author morph
 */
class Controller_Annotation_Generator extends Controller_Abstract
{
    /**
     * Распарсить аннотацию
     * 
     * @Context("helperCodeGenerator", "helperAnnotationGenerator")
     * @Template(null)
     * @Validator("Not_Null"={"data"})
     */
    public function update($data, $context) 
    {
        foreach ($data as $classProperty => $subData) {
            list($className, $propertyName) = explode('/', $classProperty);
            $classReflection = new \ReflectionClass($className);
            if (!$classReflection->hasProperty($propertyName)) {
                continue;
            }
            if (!isset($subData['Generator'])) {
                continue;
            }
            $needGetter = !is_array($subData['Generator']['data']) ||
                in_array('get', $subData['Generator']['data'][0]);
            $needSetter = !is_array($subData['Generator']['data']) ||
                in_array('set', $subData['Generator']['data'][0]);
            $propertyReflection = $classReflection->getProperty($propertyName);
            $propertyType = $context->helperAnnotationGenerator->getType(
                $propertyReflection->getDocComment()
            );
            $getterName = 'get' . ucfirst($propertyName);
            $setterName = 'set' . ucfirst($propertyName);
            $outputData = array(
                'propertyName'  => $propertyName,
                'propertyType'  => $propertyType,
                'isStatic'      => $propertyReflection->isStatic()
            );
            $hasMethods = true;
            if ($needGetter && !$classReflection->hasMethod($getterName)) {
                $outputData['getterName'] = $getterName;
                $hasMethods = false;
            }
            if ($needSetter && !$classReflection->hasMethod($setterName)) {
                $outputData['setterName'] = $setterName;
                $hasMethods = false;
            }
            if ($hasMethods) {
                continue;
            }
            $output = $context->helperCodeGenerator->fromTemplate(
                'getSetMethods', $outputData
            );
            $filename = $subData['Generator']['file'];
            $content = file_get_contents($filename);
            $lastPos = mb_strrpos($content, '}', 0, 'UTF-8');
            $newContent = mb_substr($content, 0, $lastPos, 'UTF-8') .
                $output . PHP_EOL . '}';
            file_put_contents($filename, $newContent);
        }
    }
}