<?php

/**
 * Собирает схему модели по аннотациям
 * 
 * @author morph
 */
class Controller_Annotation_Orm extends Controller_Abstract
{
    /**
     * Создать/обновить схему модели 
     */
    public function create($className, $context)
    {
        IcEngine::getLoader()->load($className);
        $classReflection = new \ReflectionClass($className);
        $annotationManager = IcEngine::serviceLocator()->getSource()
            ->getAnnotationManager();
        $annotation = $annotationManager->getAnnotation($className)
            ->getData();
        if (!isset($annotation['class']['Orm\\Entity'])) {
            return;
        }
        echo $className . PHP_EOL; 
        $entity = $annotation['class']['Orm\\Entity'][0]; 
        if (is_array($entity)) {
            $scheme = $this->getService('modelScheme'); 
            $models = $scheme->getModels();
            $schemeModelName = strtolower($className);
            if (!isset($models[$schemeModelName])) {
                $models[$schemeModelName] = array();
            }
            foreach ($entity as $field => $value) {
                unset($entity[$field]);
                $entity[strtolower($field)] = $value;
            }
            $models[$schemeModelName] = array_merge(
                $models[$schemeModelName], $entity
            );
            ksort($models);
            $behavior = $scheme->getBehavior();
            $schemeConfig = $this->getService('configManager')->get(
                'Model_Scheme_' . $behavior
            );
            $default = $schemeConfig->default;
            $output = Helper_Code_Generator::fromTemplate(
                'modelScheme',
                array (
                    'default'   => $default,
                    'models'    => $models
                )
            );
            $result = array();
            $lines = explode(PHP_EOL, $output);
            foreach ($lines as $line) {
                $baseLine = $line;
                $line = str_replace(array("\n", "\r"), '', trim($line));
                if (!$line) {
                    continue;
                }
                $result[] = $baseLine;
            }
            $filename = IcEngine::root() . 'Ice/Config/Model/Scheme/' . 
                $behavior . '.php';
            file_put_contents($filename, implode(PHP_EOL, $result));
        }
        $fields = array();
        $references = array();
        $indexes = array();
        $booleanOnlyArgs = array(
            'Unsigned', 'Auto_Increment', 'Not_Null', 'Null'
        );
        foreach ($annotation['properties'] as $property => $data) {
            if (!$data) {
                continue;
            }
            $keys = array_keys($data);
            if (!$keys || strpos($keys[0], 'Orm\\') === false) {
                continue;
            }
            foreach ($keys as $key) {
                if (strpos($key, 'Orm\\') === false) {
                    continue;
                }
                $fieldData = $data[$key][0];
                list(, $type, $value) = explode('\\', $key, 3);
                if ($type == 'Field') {
                    $fieldComment = '';
                    $propertyReflection = $classReflection->getProperty(
                        $property
                    );
                    $doc = $propertyReflection->getDocComment();
                    foreach (explode(PHP_EOL, $doc) as $line)
                    {
                        $line = trim($line, '* ');
                        if (!$line || $line[0] == '/') {
                            continue;
                        } elseif ($line[0] == '@') {
                            break;
                        }
                        $fieldComment .= $line;
                    }
                    foreach ($booleanOnlyArgs as $arg) {
                        if (isset($fieldData[$arg])) {
                            $fieldData[$arg] = true;
                        }
                    }
                    $fieldData['Comment'] = $fieldComment;
                    $fields[$property] = array($value, $fieldData);
                } elseif ($type == 'Index') {
                    if ($value == 'Primary') {
                        $indexes[$property] = array(
                            $value, array($property)
                        );
                    } else {
                        if (!is_array($fieldData)) {
                            $fieldData = array($property);
                        }
                        foreach ($fieldData as $index) {
                            if (!isset($indexes[$index])) {
                                $indexes[$index] = array(
                                    $value, array()
                                );
                            }
                            $indexes[$index][1][] = $property; 
                        }
                    }
                } elseif ($type == 'Reference') {
                    $references[$property] = array($value, $fieldData);
                }
            }
        }
        $classComment = '';
        $classDoc = $classReflection->getDocComment();
        foreach (explode(PHP_EOL, $classDoc) as $line) {
            $line = trim($line, '* ');
            if (!$line || $line[0] == '/') {
                continue;
            } elseif ($line[0] == '@') {
                break;
            }
            $classComment .= $line;
        }
        $modelScheme = array(
            'fields'        => $fields,
            'references'    => $references,
            'indexes'       => $indexes,
            'comment'       => $classComment
        );
        $this->output->send(array(
            'modelScheme'    => $modelScheme
        ));
    }
    
    /**
     * Распарсить аннотацию
     * 
     * @Context("controllerManager")
     */
    public function update($data, $author, $context)
    {
        foreach ($data as $className => $values) {
            $classData = reset($values);
            $className = $classData['class'];
            $task = $context->controllerManager->call(
                'Model', 'scheme', array(
                    'name'      => $className,
                    'author'    => $author
                )
            );
            $buffer = $task->getTransaction()->buffer();
            if (!empty($buffer['success']) && empty($values[0]['source'])) {
                $context->controllerManager->call(
                    'Model', 'create', array(
                        'name'  => $className
                    )
                );
            }
        }
    }
}