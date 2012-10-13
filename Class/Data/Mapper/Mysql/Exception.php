<?php

if (!class_exists ('Zend_Exception'))
{
    Loader::load ('Zend_Exception');
}

class Data_Mapper_Mysql_Exception extends Zend_Exception
{
	
}