<?php

/**
 * Meta class for "{$className}"
 * Created at: {$currentDate}
 */
class {$className}_Meta extends Meta
{
    {if !empty($data.class)}
    /**
     * @inheritdoc
     */
    public static $classAnnotations = {$data.class};
    
    {/if}
    {if !empty($data.methods)}
    /**
     * @inheritdoc
     */
    public static $methodsAnnotations = {$data.methods};
    
    {/if}
    {if !empty($data.properties)}
    /**
     * @inheritdoc
     */
    public static $propertiesAnnotations = {$data.properties};
    {/if}
 }