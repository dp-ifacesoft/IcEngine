<?php

/**
 * Аннотации модели Orm\Field
 * 
 * @author morph
 */
class Annotation_Model_Orm_Field extends Annotation_Model_Abstract
{
    /**
     * @inheritdoc
     */
    protected $field = 'fields';
    
    /**
     * @inheritdoc
     */
    public function compare($annotationValue, $schemeValue)
    {
        $annotationFieldsKeys = array_keys($annotationValue);
        $schemeFieldsKeys = array_keys($schemeValue);
        ksort($annotationFieldsKeys);
        ksort($schemeFieldsKeys);
        if (array_diff($annotationFieldsKeys, $schemeFieldsKeys)) {
            return false;
        }
        static $compareFieldArray = array(
            'Unsigned'          => Model_Field::ATTR_UNSIGNED, 
            'Binary'            => Model_Field::ATTR_BINARY, 
            'Auto_Increment'    => Model_Field::ATTR_AUTO_INCREMENT
        );
        foreach ($annotationValue as $fieldName => $field) {
            if (!isset($schemeValue[$fieldName])) {
                return false;
            }
            $schemeField = $schemeValue[$fieldName];
            $schemeFieldData = !empty($schemeField[1]) 
                ? $schemeField[1] : array();
            $field = $field->getAttrs();
            if ($schemeField[0] != $field[Model_Field::ATTR_TYPE]) {
                return false;
            } elseif (isset($schemeFieldData['Size']) &&
                $field[Model_Field::ATTR_SIZE] != $schemeFieldData['Size']) {
                return false;
            } elseif (empty($schemeFieldData['Comment']) && 
                !empty($field[Model_Field::ATTR_COMMENT])) {
                return false;
            } elseif (isset($schemeFieldData['Comment']) &&
                $field[Model_Field::ATTR_COMMENT] != 
                $schemeFieldData['Comment']) {
                return false;
            } elseif (!isset($schemeFieldData['Default']) &&
                isset($field[Model_Field::ATTR_DEFAULT])) {
                return false;
            } elseif (isset($schemeFieldData['Default']) &&
                $schemeFieldData['Default'] 
                != $field[Model_Field::ATTR_DEFAULT]) {
                return false;
            } elseif (in_array('Null', $schemeFieldData) && 
                !empty($field[Model_Field::ATTR_NOT_NULL])) {
                return false;
            } elseif (in_array('Not_Null', $schemeFieldData) && 
                !empty($field[Model_Field::ATTR_NULL])) {
                return false;
            } elseif (!$this->compareFieldArray(
                $field, $schemeFieldData, $compareFieldArray)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Сравнить атрибуты схемы и аннотации в прямом и обратном порядке по
     * массиву, переданному третьим аргументом
     * 
     * @param array $field
     * @param array $schemeFieldData
     * @param array $compareArray
     * @boolean
     */
    public function compareFieldArray($field, $schemeFieldData, $compareArray)
    {
        foreach ($compareArray as $attrScheme => $attrAnnotation) {
            if (!in_array($attrScheme, $schemeFieldData) && 
                !empty($field[$attrAnnotation])) {
                return false;
            } elseif (in_array($attrScheme, $schemeFieldData) &&
                empty($field[$attrAnnotation])) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function convertValue($dto, $scheme)
    {
        $schemeFields = array();
        foreach ($dto->fields as $field) {
            $attrs = array();
            if ($field->getSize()) {
                $attrs['Size'] = $field->getSize();
            }
            $default = $field->getDefault();
            if (!is_null($default)) {
                $attrs['Default'] = $default;
            }
            $comment = $field->getComment();
            if ($comment) {
                $attrs['Comment'] = $comment;
            }
            $notNull = !$field->getNullable();
            if ($notNull) {
                $attrs[] = 'Not_Null';
            }
            if ($field->getAutoIncrement()) {
                $attrs[] = 'Auto_Increment';
            }
            if ($field->getUnsigned()) {
                $attrs[] = 'Unsigned';
            }
            if ($field->getAttr('Rename')) {
                $attrs['Rename'] = $field->getAttr('Rename')['from'];
            }
            $schemeFields[$field->getName()] = array(
                $field->getType(), $attrs
            );
        }
        return $schemeFields;
    }
    
    /**
     * @inheritdoc
     */
    public function getData($modelName)
    {
        $serviceLocator = IcEngine::serviceLocator();
        $helperAnnotationModelField = $serviceLocator->getService(
            'helperAnnotationModelField'
        );
        $data = $helperAnnotationModelField->getAnnotations($modelName);
        $resultFields = array();
        $classReflection = new \ReflectionClass($modelName);
        $profile = $this->getProfile($modelName);
        foreach ($data as $propertyName => $annotations) {
            foreach ($annotations as $annotationName => $annotation) {
                $arrayAnnotation = (array) $annotation;
                $annotation = reset($arrayAnnotation);
                if (strpos($annotationName, 'Orm\\Field') === false) {
                    if (strpos($annotationName, 'Orm\\State') !== false) {
                        list(,,$state) = explode('\\', $annotationName);
                        $resultFields[$propertyName]->setAttr(
                            $state, $annotation
                        );
                    }
                    continue;
                }
                if (isset($resultFields[$propertyName])) {
                    continue;
                }
                $propertyReflection = $classReflection->getProperty(
                    $propertyName
                );
                $comment = null;
                $doc = $propertyReflection->getDocComment();
                foreach (explode(PHP_EOL, $doc) as $line) {
                    $line = trim($line, "\t *");
                    if (!$line || $line[0] == '/') {
                        continue;
                    } elseif ($line[0] == '@') {
                        break;
                    }
                    $comment .= $line;
                }
                list(,,$type) = explode('\\', $annotationName);
                if (!is_array($annotation)) {
                    if (!isset($profile[$type])) {
                        continue;
                    }
                    $annotation = $profile[$type];
                } elseif (isset($profile[$type])) {
                    $annotation = array_merge($annotation, $profile[$type]);
                }
                $field = new Model_Field($propertyName);
                $field
                    ->setAutoIncrement(in_array('Auto_Increment', $annotation))
                    ->setType($type)
                    ->setSize(
                        !empty($annotation['Size']) ? $annotation['Size'] : 0
                    )
                    ->setNullable(!in_array('Not_Null', $annotation))
                    ->setDefault(isset($annotation['Default'])
                        ? $annotation['Default'] : null)
                    ->setUnsigned(in_array('Unsigned', $annotation))
                    ->setComment($comment);
                $resultFields[$propertyName] = $field;
            }
        }
        return $resultFields;
    }
    
    /**
     * Получить профиль схемы
     * 
     * @param string $modelName
     * @return array
     */
    public function getProfile($modelName)
    {
        $serviceLocator = IcEngine::serviceLocator();
        $annotations = $serviceLocator->getService('helperAnnotation')
            ->getAnnotation($modelName)->getData();
        $annotationClass = $annotations['class'];
        if (!isset($annotationClass['Orm\\Profile'])) {
            return array();
        }
        $configManager = $serviceLocator->getService('configManager');
        $config = $configManager->get('Orm_Profile');
        $result = array();
        foreach ($annotationClass['Orm\\Profile'] as $profile) {
            $profile = reset($profile);
            if (!$config[$profile]) {
                continue;
            }
            $result = array_merge(
                $result, $config[$profile]->fields->__toArray()
            );
        }
        return $result;
    }
}