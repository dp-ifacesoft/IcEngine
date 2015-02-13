    {if !empty($getterName)}

    /**
     * Getter for "{$propertyName}"
     *
     * @return {$propertyType|default:"mixed"}
     */
    public function {$getterName}()
    {
        return {if $isStatic}self::${else}$this->{/if}{$propertyName};
    }
    {/if}
    {if !empty($setterName)}

    /**
     * Setter for "{$propertyName}"
     *
     * @param {$propertyType|default:"mixed"} {$propertyName}
     */
    public function {$setterName}(${$propertyName})
    {
        {if $isStatic}self::${else}$this->{/if}{$propertyName} = ${$propertyName};
    }
    {/if}