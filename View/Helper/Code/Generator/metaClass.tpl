<?php

/**
 * Meta class for "{$className}"
 * Created at: {$currentDate}
 */
class {$className}_Meta extends Meta
{
    /**
     * @inheritdoc
     */
    public static $classAnnotations = {$data.class};
    
    /**
     * @inheritdoc
     */
    public static $methodsAnnotations = {$data.methods};
    
    /**
     * @inheritdoc
     */
    public static $propertiesAnnotations = {$data.properties};
 }