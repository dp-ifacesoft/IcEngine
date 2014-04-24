<?php

/**
 * Хелпер для пересбора аннотации модели конфигурации схемы или источника
 * данных
 *
 * @author morph
 * @Service("helperModelMigrateRebuild")
 */
class Helper_Model_Migrate_Rebuild extends Helper_Abstract
{
    /**
     * Получить вывод для поля
     *
     * @param Model_Field $field
     * @param Config_Array $fieldScheme
     * @param array<Model_Index> $indexes
     * @return string
     */
    public function fieldOutput($field, $fieldScheme, $indexes)
    {
        $comment = $field->getComment() ?: $fieldScheme['Comment'];
        $indexNames = array();
        foreach ($indexes as $index) {
            if (!in_array($field->getName(), $index->getFields())) {
                continue;
            }
            $indexNames[$index->getType()][] = $index->getName();
        }
        $size = $field->getSize();
        $parts = array();
        if ($size) {
            $parts[] = 'Size=' .
                (is_array($size) ? '{' . implode(',', $size) . '}' : $size);
        }
        if ($field->getNullable()) {
            $parts[] = 'Null';
        } else {
            $parts[] = 'Not_Null';
        }
        if ($field->getAutoIncrement()) {
            $parts[] = 'Auto_Increment';
        }
        $output = "\t/**" . PHP_EOL;
        if ($comment) {
            $output .= "\t * $comment" . PHP_EOL. "\t *" . PHP_EOL;
        }
        $output .= "\t * @Orm\Field\\" . $field->getType() . '(' .
            implode(', ', $parts) . ')';
        if ($indexNames) {
            foreach ($indexNames as $typeName => $indexes) {
                foreach ($indexes as &$index) {
                    $index = '"' . $index . '"';
                }
                $output .= PHP_EOL . "\t * @Orm\Index\\" . $typeName . '(' .
                    implode(', ', $indexes) . ')';
            }
        }
        $output .= PHP_EOL . "\t */" . PHP_EOL . "\tpublic $" .
            $field->getName() . ';' . PHP_EOL . PHP_EOL;
        return $output;
    }

    /**
     * Пересобрать модель основываясь на схеме данных из источника
     * данных
     *
     * @param string $modelModel
     */
    public function rebuild($modelName)
    {
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $dataSchemeDto = $this->getService('dto')->newInstance('Data_Scheme')
            ->setModelName($modelName);
        $dataSource->getScheme(new Data_Scheme($dataSchemeDto));
        $fields = $dataSchemeDto->fields;
        $indexes = $dataSchemeDto->indexes;
        foreach ($fields as $field) {
            $this->rewriteField($modelName, $field, $indexes);
        }
        $this->rewriteScheme($modelName, $dataSchemeDto);
    }

    /**
     * Переписать поле модели
     *
     * @param string $modelName
     * @param Model_Field $field
     * @param array $indexes
     */
    public function rewriteField($modelName, $field, $indexes)
    {
        $loader = IcEngine::getLoader();
        $loader->load($modelName);
        $fileKey = str_replace('_', '/', $modelName) . '.php';
        $filename = $loader->getRequired($fileKey, 'Class');
        if (!$filename) {
            return;
        }
        $content = file_get_contents($filename);
        $contentLength = mb_strlen($content, 'UTF-8');
        $varNeedle = 'public $' . $field->getName() . ';';
        $varPos = mb_strpos($content, $varNeedle, 0, 'UTF-8');
        $firstAppendPos = mb_strpos($content, '{', 0, 'UTF-8') + 2;
        if ($varPos !== false) {
            $reversedContent = strrev($content);
            $reversedVarPos = $contentLength - $varPos;
            $reversedBeginCommentPos = mb_strpos(
                $reversedContent, '**/', $reversedVarPos, 'UTF-8'
            );
            $beginFieldPos = $contentLength - $reversedBeginCommentPos - 3;
            $endFieldPos = mb_strpos(
                $content, ';', $beginFieldPos, 'UTF-8') + 3;
        } else {
            $beginFieldPos = $firstAppendPos;
            $endFieldPos = $beginFieldPos;
        }
        $scheme = $this->getService('modelScheme')->scheme($modelName);
        $fieldScheme = $scheme->fields[$field->getName()];
        $contentFirstPart = mb_substr($content, 0, $beginFieldPos, 'UTF-8');
        $contentLastPart = mb_substr(
            $content, $endFieldPos, $contentLength - $endFieldPos, 'UTF-8'
        );
        $output = $contentFirstPart .
            $this->fieldOutput($field, $fieldScheme, $indexes) .
            $contentLastPart;
        $outputParts = explode(PHP_EOL, $output);
        foreach ($outputParts as &$output) {
            if (strpos($output, "\t/**") === false) {
                continue;
            }
            $output = "\t" . trim($output, " \t");
        }
        $resultOutput = implode(PHP_EOL, $outputParts);
        file_put_contents($filename, $resultOutput);
    }

    /**
     * Перезапись схемы модели
     *
     * @param string $modelName
     */
    public function rewriteScheme($modelName, $dto)
    {
        $scheme = $this->getService('configManager')->get(
            'Model_Mapper_' . $modelName
        );
        $helperConverter = $this->getService('helperConverter');
        $author = null;
        $comment = null;
        static $convertingFields = array(
            'admin', 'languageScheme', 'createScheme'
        );
        $dataConverted = array();
        if ($scheme->count()) {
            $author = $scheme->author;
            $comment = $scheme->comment ?:
                ($dto->info && !empty($dto->info['Comment'])
                    ? $dto->info['Comment'] : '');
            foreach ($convertingFields as $field) {
                $key = ucfirst($field);
                $dataConverted[$key] = $scheme[$field]
                     ? $helperConverter->arrayToString(
                         $scheme[$field]->__toArray()
                     ) : null;
            }
        }
        $schemeDto = $this->getService('dto')->newInstance('Model_Scheme')
            ->setAuthor($author)
            ->setComment($comment);
        $helperAnnotationModel = $this->getService('helperAnnotationModel');
        $annotations = $helperAnnotationModel->getList();
        foreach ($annotations as $annotation) {
            $schemeDto->set(
                $annotation->getField(), 
                $annotation->convertValue($dto, $scheme)
            );
        }
        foreach ($dataConverted as $key => $data) {
            call_user_func(array($schemeDto, 'set' . $key), $data);
        }
        $this->getService('helperModelScheme')->create($modelName, $schemeDto);
    }
}