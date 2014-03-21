<?php

class Cross_Site_Data_Container extends Model
{
    
    /**
     * Передаваемые данные
     * @var array
     */
    public $asArray = array ();
    
    public function save ($hard_insert = false)
    {
        $this->_fields ['data'] = $this->Cross_Site_Data_Coder->encode (
            $this->asArray
        );
        return parent::save ($hard_insert);
    }
    
    public function set ($field, $value = null)
    {
        $fields = is_array ($field) ? $field : array ($field => $value);
        
        if (
            array_key_exists ('data', $fields) &&
            is_array ($fields ['data'])
        )
        {
            $this->asArray = $fields ['data'];
            unset ($fields ['data']);
        }
        
        if (empty ($fields))
        {
            return $this;
        }
    
        parent::set ($fields);
        
        if (
            array_key_exists ('data', $fields) &&
            isset ($this->_fields ['Cross_Site_Data_Coder__id']) &&
            $this->_fields ['Cross_Site_Data_Coder__id']
        )
        {
            $this->asArray = (array) $this->Cross_Site_Data_Coder->decode ($this->_fields ['data']);
        }
    }
    
}