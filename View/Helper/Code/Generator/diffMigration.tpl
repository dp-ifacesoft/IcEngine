$modelName = '{$modelName}';
$dataSource = $this->getService('modelScheme')->dataSource($modelName);
$queryBuilder = $this->getService('query');
{foreach from=$migrations key="i" item="m"}
{$data=$m->getPart(Query::ALTER_TABLE)}
{if !isset($data[Query::FIELD])}
{$data=$m->getPart(Query::CREATE_TABLE)}
{/if}
{if isset($data[Query::FIELD][Query::FIELD])}
{$method=Query::DROP}
{$subData=reset($data[Query::FIELD])}
{$fieldName=$subData[Query::FIELD]}
{else}
{$part=reset($data[Query::FIELD])}
{$method=$part[Query::TYPE]}
{$fieldName=$part[Query::FIELD]}
{if $method==Query::CHANGE}
    {$fieldName=$part[Query::NAME]}
{/if}
{/if}
{if $method!=Query::DROP} 
{$attr=$part['__ATTR__']}
$field{$i} = new \Model_Field('{$fieldName}');
$field{$i}->setType('{$attr[Model_Field::ATTR_TYPE]}');
{if !empty($attr[Model_Field::ATTR_SIZE])}
{$size=$attr[Model_Field::ATTR_SIZE]}
$field{$i}->setSize({if is_array($size)}array({$size|implode:','}){else}{$size}{/if});
{/if}
{if isset($attr[Model_Field::ATTR_DEFAULT])}
$field{$i}->setDefault('{$attr[Model_Field::ATTR_DEFAULT]}');    
{/if}
{if !empty($attr[Model_Field::ATTR_COMMENT])}
$field{$i}->setComment('{$attr[Model_Field::ATTR_COMMENT]}');    
{/if}
{if empty($attr[Model_Field::ATTR_NULL])}
$field{$i}->setNullable(true);
{else}
$field{$i}->setNullable(false);
{/if}
{if !empty($attr[Model_Field::ATTR_AUTO_INCREMENT])}
$field{$i}->setAutoIncrement(true); 
{/if}
{if !empty($attr[Model_Field::ATTR_UNSIGNED])}
$field{$i}->setUnsigned(true);    
{/if}
{/if}
$query{$i} = $queryBuilder
    ->alterTable($modelName)
{if $method==Query::DROP}
    ->dropField('{$fieldName}');
{elseif $method==Query::ADD}
    ->addField($field{$i});
{else}
    {$oldFieldName=$part[Query::FIELD]}
    ->changeField($field{$i},'{$oldFieldName}');
{/if}
$dataSource->execute($query{$i});
{/foreach}