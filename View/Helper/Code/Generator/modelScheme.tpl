<?php


return array(
{if $default}
    'default'   => array(
{foreach from=$default item="i" key="field" name="field"}
        '{$field}'          => {if !is_array($i)}{if is_numeric($i) || is_bool($i)}{$i}{else}'{$i}'{/if}{if !$smarty.foreach.field.last},
{/if}
{else}array(
{foreach from=$i item="j" key="subfield" name="subfield"}
            '{$subfield}'   => {if is_numeric($j) || is_bool($j)}{$j}{else}'{$j}'{/if}{if !$smarty.foreach.subfield.last},
            {/if}
{/foreach}
 
        ){if !$smarty.foreach.field.last},
        {/if}
{/if}
{/foreach}

    ),
{/if}
    'models'        => array(
{foreach from=$models item="model" name="models" key="modelName"}
        '{$modelName}'   => array(

{foreach from=$model item="i" name="field" key="field"}
            '{$field}'          => {if !is_array($i)}{if is_numeric($i) || is_bool($i) || in_array($i, array('true', 'false'))}{$i}{else}'{$i}'{/if}{if !$smarty.foreach.field.last},
{/if}

{else}array(
{foreach from=$i item="j" key="subfield" name="subField"}
                array({foreach from=$j item="k" name="subSubField"}'{$k}'{if !$smarty.foreach.subSubField.last}, {/if}{/foreach}){if !$smarty.foreach.subField.last},
{/if}

{/foreach}
               
            ){if !$smarty.foreach.field.last},
{/if}
{/if}
{/foreach}

        ){if !$smarty.foreach.models.last},
{/if}
{/foreach}
    
    )
);