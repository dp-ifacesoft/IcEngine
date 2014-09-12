<?php

if (!class_exists ('Acl_Role_Type_Abstract'))
{
    include dirname (__FILE__) . '/Abstract.php';
}

class Acl_Role_Type_Agency extends Acl_Role_Type_Abstract
{
    /**
     * 
     * @var integer
     */
    const ID = 100;
}