<?php

/**
 * Хелпер для синхронизации схемы моделей и аннотаций модели
 *
 * @author morph
 * @Service("helperModelMigrateSync")
 */
class Helper_Model_Migrate_Sync extends Helper_Abstract
{
    /**
     * Получить комментарий класса
     *
     * @param string $modelName
     * @return string
     */
    public function getModelComment($modelName)
    {
        $classReflection = new \ReflectionClass($modelName);
        $classComment = '';
        $classDoc = $classReflection->getDocComment();
        foreach (explode(PHP_EOL, $classDoc) as $line) {
            $line = trim($line, "*\t ");
            if (!$line || $line[0] == '/') {
                continue;
            } elseif ($line[0] == '@') {
                break;
            }
            $classComment .= $line;
        }
        return $classComment;
    }
    
    /**
     * Ресинхронизации схемы и аннотаций
     *
     * @param string $modelName
     */
    public function resync($modelName)
    {
        $info = array('comment' => $this->getModelComment($modelName));
        $helperAnnotationModel = $this->getService('helperAnnotationModel');
        $annotations = $helperAnnotationModel->getList();
        if (!$annotations) {
            return false;
        }
        $dto = $this->getService('dto')->newInstance('Data_Scheme')
            ->setModelName($modelName)
            ->setInfo($info);
        $hasData = false;
        foreach ($annotations as $annotation) {
            $data = $annotation->getData($modelName);
            if ($data) {
                $hasData = true;
            }
            $dto->set($annotation->getField(), $data);
        }
        if (!$hasData) {
            return false;
        }
        $this->getService('helperModelMigrateRebuild')
            ->rewriteScheme($modelName, $dto);
        $this->getService('configManager')->reset('Model_Mapper_' . $modelName);
        return true;
    }
}