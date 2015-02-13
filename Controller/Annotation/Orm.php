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
     *
     * @Context(
     *      "helperAnnotationOrm", "modelScheme", "helperModelMigrateSync",
     *      "helperModelTable"
     * )
     * @Template(null)
     * @Validator("Not_Null"={"data"})
     */
    public function update($data, $context)
    {
        foreach (array_keys($data) as $className) {
            $annotationManager = IcEngine::serviceLocator()->getSource()
                ->getAnnotationManager();
            $annotation = $annotationManager->getAnnotation($className)
                ->getData();
            if (!isset($annotation['class']['Orm\\Entity'])) {
                continue;
            }
            $entity = $annotation['class']['Orm\\Entity'][0];
            if (is_array($entity)) {
                $context->helperAnnotationOrm->rewriteModelScheme(
                    $className, $entity
                );
            }
            $scheme = $context->configManager->get(
                'Model_Mapper_' . $className
            );
            if (!$scheme->fields || 
                !$context->helperAnnotationOrm->compare($className)) {
                echo $className . PHP_EOL;
                $context->helperModelMigrateSync->resync($className);
            }
            if (!$scheme->fields) {
                $context->helperModelTable->create($className);
            }
        }
    }
}