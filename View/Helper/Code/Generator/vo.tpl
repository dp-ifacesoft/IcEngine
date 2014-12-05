<?php

class {$name} extends Vo 
{
{foreach from=$fields item="field"}
    public function get{ucfirst($field)}()
    {
        return $this->getField('{$field}');
    }
{/foreach}
}