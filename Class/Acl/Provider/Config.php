<?php

/**
 * Провайдер acl, берущий данные из acl-конфига модели
 * 
 * @author morph
 */
class Acl_Provider_Config extends Acl_Provider_Abstract
{
    /**
     * @inheritdoc
     */
    public function forModel($model)
    {
        return $this->getService('helperModelAclConfig')->forModel($model);
    }
}