<?php

/**
 * Провайдер acl, берущий данные из acl-аннотаций модели
 * 
 * @author morph
 */
class Acl_Provider_Annotation extends Acl_Provider_Abstract
{
    /**
     * @inheritdoc
     */
    public function forModel($model)
    {
        return $this->getService('helperModelAclAnnotation')
            ->forModel($model);
    }
}