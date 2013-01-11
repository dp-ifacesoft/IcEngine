<?php

/**
 * Помощник для контроллера моделей
 *
 * @author markov
 * @Service("helperControllerModel")
 */
class Helper_Controller_Model extends Controller_Abstract
{
    public function compareAddedFields(&$addedFields, &$model, &$table, 
        &$filename) 
    {
        $serviceLocator = IcEngine::serviceLocator();
        $queryBuilder = $serviceLocator->getService('query');
        $dds = $serviceLocator->getService('dds');
        if ($addedFields) {
            foreach ($addedFields as $fieldName => $data) {
                echo 'In model "' . $model . '" had added field "' .
                    $fieldName . '". Create?[Y/n] ';
                $a = fgets(STDIN);
                if ($a[0] == 'Y') {
                    $field = new Model_Field($fieldName);
                    $field->setNullable(false)
                        ->setType($data['type'])
                        ->setComment($data['comment']);
                    if (!empty($data['default'])) {
                        $field->setDefault($data['default']);
                    }
                    if (!empty($data['size'])) {
                        $field->setSize($data['size']);
                    }
                    if (!empty($data['auto_inc'])) {
                        $field->setAutoIncrement(true);
                    }
                    $query = $queryBuilder
                        ->alterTable($table)
                        ->add($field);
                    if ($filename) {
                        file_put_contents(
                            $filename,
                            $query->translate() . PHP_EOL,
                            FILE_APPEND
                        );
                    } else {
                        $dds->execute($query);
                    }
                }
            }
        }
    }
    
    public function compareDeletedFields(&$deletedFields, &$model, &$table, 
        &$filename) 
    {
        $serviceLocator = IcEngine::serviceLocator();
        $queryBuilder = $serviceLocator->getService('query');
        $dds = $serviceLocator->getService('dds');
        if ($deletedFields) {
            foreach ($deletedFields as $fieldName => $data) {
                echo 'In model "' . $model . '" had deleted field "' .
                    $fieldName . '". Delete?[Y/n] ';
                $a = fgets(STDIN);
                if ($a[0] == 'Y') {
                    $field = new Model_Field($fieldName);
                    $query = $queryBuilder
                        ->alterTable($table)
                        ->drop($field);
                    if ($filename) {
                        file_put_contents(
                            $filename,
                            $query->translate() . PHP_EOL,
                            FILE_APPEND
                        );
                    } else {
                        $dds->execute($query);
                    }
                }
            }
        }
    }
    
    public function compareRemainingFields(&$remainingFields, &$modelFields,
        &$model, &$filename) 
    {
        $serviceLocator = IcEngine::serviceLocator();
        $queryBuilder = $serviceLocator->getService('query');
        $dds = $serviceLocator->getService('dds');
        if ($remainingFields) {
            foreach ($remainingFields as $fieldName => $tableData) {
                if (!isset($modelFields[$fieldName])) {
                    continue;
                }
                $fieldData = $modelFields[$fieldName];
                $changedAttributes = @array_diff_assoc(
                    $tableData, $fieldData
                );
                $modelChangedFields = @array_diff_assoc(
                    $fieldData, $tableData
                );
                if (count($modelChangedFields) !=
                    count($changedAttributes)) {
                    foreach ($modelChangedFields as $attrName => $value) {
                        if (isset($changedAttributes[$attrName])) {
                            continue;
                        }
                        echo 'In model "' . $model .
                            '" had changed field "' .
                            $fieldName . '" with added attribute "' .
                            $attrName .	'". Apply changes?[Y/n] ';
                        $a = fgets(STDIN);
                        if ($a[0] == 'Y') {
                            $field = new Model_Field($fieldName);
                            $field->setNullable(false)
                                ->setType($tableData['type'])
                                ->setComment($tableData['comment']);
                            if (!empty($tableData['default'])) {
                                $field->setDefault($tableData['default']);
                            }
                            if (!empty($tableData['size'])) {
                                $field->setSize($tableData['size']);
                            }
                            if (!empty($tableData['auto_inc'])) {
                                $field->setAutoIncrement(true);
                            }
                            if ($attrName == 'auto_inc') {
                                $attrName = 'auto_increment';
                            }
                            $attrName = 'ATTR_' . strtoupper($attrName);
                            $attr = constant('Model_Field::' . $attrName);
                            $field->setAttr($attr, $value);
                            $query = $queryBuilder
                                ->alterTable($table)
                                ->change($fieldName, $field);
                            if ($filename) {
                                file_put_contents(
                                    $filename,
                                    $query->translate() . PHP_EOL,
                                    FILE_APPEND
                                );
                            } else {
                                $dds->execute($query);
                            }
                        }
                    }
                }
                if ($changedAttributes) {
                    foreach ($changedAttributes as $attrName => $value) {
                        if (isset($modelChangedFields[$attrName])) {
                            continue;
                        }
                        if (!isset($fieldData[$attrName])) {
                            continue;
                        }
                        echo 'In model "' . $model .
                            '" had changed field "' .
                            $fieldName . '" with changed attribute "' .
                            $attrName .	'". Apply changes?[Y/n] ';
                        $a = fgets(STDIN);
                        if ($a[0] == 'Y') {
                            $field = new Model_Field($fieldName);
                            $field->setNullable(false)
                                ->setType($tableData['type'])
                                ->setComment($tableData['comment']);
                            if (!empty($tableData['default'])) {
                                $field->setDefault($tableData['default']);
                            }
                            if (!empty($tableData['size'])) {
                                $field->setSize($tableData['size']);
                            }
                            if (!empty($tableData['auto_inc'])) {
                                $field->setAutoIncrement(true);
                            }
                            $oldAttr = $attrName;
                            if ($attrName == 'auto_inc') {
                                $attrName = 'auto_increment';
                            }
                            $attrName = 'ATTR_' . strtoupper($attrName);
                            $attr = constant('Model_Field::' . $attrName);
                            if (isset($fieldData[$oldAttr])) {
                                $field->setAttr(
                                    $attr, $fieldData[$oldAttr]
                                );
                            }
                            $query = $queryBuilder
                                ->alterTable($table)
                                ->change($fieldName, $field);
                            if ($filename) {
                                file_put_contents(
                                    $filename,
                                    $query->translate() . PHP_EOL,
                                    FILE_APPEND
                                );
                            } else {
                                $dds->execute($query);
                            }
                        }
                    }
                }
            }
        }
    }
}
