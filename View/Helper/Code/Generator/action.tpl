   
   /**
    * {$comment}
    * {if isset($params)}{foreach from=$params item=param}
    * @param type param{/foreach}{/if}

    */
    public function {$name} ({if isset($params)}{foreach from=$params item=param name=params}{if !$smarty.foreach.params.first}, {/if}${$param}{/foreach}{/if})
    {
        
    }
    