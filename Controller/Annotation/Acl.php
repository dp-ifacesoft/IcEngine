<?php

/**
 * Контроллер для аннотаций типа "Acl"
 * 
 * @author morph
 */
class Controller_Annotation_Acl extends Controller_Abstract
{
    /**
     * Распарсить аннотацию
     * 
     * @Context(
     *      "helperModelAcl", "helperModelAclSync", "helperModelAclAnnotation"
     * )
     * @Template(null)
     * @Validator("Not_Null"={"data"})
     */
    public function update($data, $context) 
    {
        foreach (array_keys($data) as $classProperty) {
            list($className,) = explode('/', $classProperty);
            if (!$context->helperModelAcl->compare($className)) {
                $context->helperModelAclSync->fromAnnotation($className);
            }
        }
    }
}