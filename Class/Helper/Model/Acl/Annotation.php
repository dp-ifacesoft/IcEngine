<?php

/**
 * Хелпер конфигурации acl модели для работы с аннотациями
 * 
 * @author morph
 * @Service("helperModelAclAnnotation")
 */
class Helper_Model_Acl_Annotation extends Helper_Abstract
{
    /**
     * Получить аннотации acl для модели
     * 
     * @param mixed $model
     * @return array
     */
    public function forModel($model)
    {
        $modelName = is_string($model) ? $model : $model->modelName();
        $data = $this->getService('helperAnnotation')
            ->getAnnotation($modelName)
            ->getData();
        $fieldScheme = $this->getService('modelScheme')->scheme($modelName)
            ->fields;
        $resultFields = array();
        $classAnnotation = $data['class'];
        $propertyAnnotations = $data['properties'];
        $defaultAccessType = array();
        $allAcessTypes = $this->getService('helperModelAcl')->getAccessTypes();
        if (isset($classAnnotation['Acl\\Role'])) {
            $defaultAccessType = reset($classAnnotation['Acl\\Role']);
        }
        foreach (array_keys($fieldScheme->__toArray()) as $fieldName) {
            $currentPropertyAnnotation = isset($propertyAnnotations[$fieldName])
                ? $propertyAnnotations[$fieldName] : array();
            $resultFields[$fieldName] = 
                isset($currentPropertyAnnotation['Acl\\Role'])
                    ? reset($currentPropertyAnnotation['Acl\\Role'])
                    : $defaultAccessType;
            foreach ($resultFields[$fieldName] as $roleName => $accessTypes) {
                if (is_numeric($roleName)) {
                    unset($resultFields[$fieldName][$roleName]);
                    $resultFields[$fieldName][$accessTypes] = $allAcessTypes;
                } else {
                    $resultFields[$fieldName][$roleName] = array_intersect(
                        $accessTypes, $allAcessTypes
                    );
                }
            }
        }
        return $resultFields;
    }
    
    /**
     * Перезаписать аннотации модели
     * 
     * @param mixed $model
     * @param array $data
     */
    public function rewrite($model, $data)
    {
        $modelName = is_string($model) ? $model : $model->modelName();
        $loader = IcEngine::getLoader();
        $filename = $loader->findFile(
            $this->getService('helperModel')->makePath($modelName), 'Class'
        );
        if (!$filename) {
            return null;
        }
        $allAccessTypes = $this->getService('helperModelAcl')
            ->getAccessTypes();
        $countAllAccessTypes = count($allAccessTypes);
        $helperCodeGenerator = $this->getService('helperCodeGenerator');
        $content = file_get_contents($filename);
        $subject = '@Acl\\Role(';
        $subjectCount = count($subject);
        foreach ($data as $fieldName => $roles) {
            foreach ($roles as $roleName => $accessTypes) {
                if (!array_diff($allAccessTypes, $accessTypes) &&
                    $countAllAccessTypes == count($accessTypes)) {
                    unset($roles[$roleName]);
                    $roles[] = $roleName;
                }
            }
            $reversedContent = strrev($content);
            $length = strlen($reversedContent);
            $startPos = strpos($content, 'public $' . $fieldName);
            if ($startPos === false) {
                continue;
            }
            $endPos = $length - 
                strpos($reversedContent, '**/', $length - $startPos);
            $subContent = substr(
                $content, $endPos - 3, $startPos - $endPos + 3
            );
            $line = $helperCodeGenerator->fromTemplate('modelAclLine', array(
                'roles' => $roles
            ));
            $subStartPos = strpos($subContent, $subject);
            if ($subStartPos !== false) {
                $subEndPos = strpos($subContent, ')', $subStartPos);
                $firstPart = substr(
                    $subContent, 0, $subStartPos + $subjectCount - 1
                );
                $endPart = substr($subContent, $subEndPos);
                $subContent = $firstPart . $subject . $line . $endPart;
            } else {
                $subStartPos = strpos($subContent, '*/');
                $firstPart = substr($subContent, 0, $subStartPos);
                $subContent = $firstPart . 
                    "* @Acl\Role(" . $line . ')' . PHP_EOL . "     */" . 
                    PHP_EOL . '    ';
            }
            $firstPart = substr($content, 0, $endPos - 3);
            $endPart = substr($content, $startPos);
            $content = $firstPart . $subContent . $endPart;
        }
        file_put_contents($filename, $content);
    }
}