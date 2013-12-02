<?php

/**
 * Провайдер acl, берущий данные из таблицы связей ролей и ресурсов, построенных
 * на совмещение полей и типов доступа
 * 
 * @author morph
 */
class Acl_Provider_Table extends Acl_Provider_Abstract
{
    /**
     * @inheritdoc
     */
    public function forModel($model)
    {
        return $this->getService('helperModelAclTable')->forModel($model);
    }
}