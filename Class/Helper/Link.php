<?php

class Helper_Link
{
	
    /**
     * 
     * @param string $table1
     * @param integer $key1
     * @param string $table2
     * @param integer $key2
     * @return Link|null
     */
	protected static function _link ($table1, $key1, $table2, $key2)
	{        
		return IcEngine::$modelManager->modelBy (
		    'Link',
		    Query::instance ()
		    ->where ('fromTable', $table1)
		    ->where ('fromRowId', $key1)
		    ->where ('toTable', $table2)
		    ->where ('toRowId', $key2)
		);
	}
	
	/**
	 * 
	 * @param Model $model1
	 * @param Model $model2
	 * @return boolean
	 */
	public static function wereLinked (Model $model1, Model $model2)
	{
		if (strcmp ($model1->table (), $model2->table ()) > 0)
	    {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }
	    
	    $link = self::_link (
	        $model1->table (), $model1->key (),
	        $model2->table (), $model2->key ()
	    );
	    
	    return (bool) $link;
	}
	
	/**
	 * 
	 * @param Model $model1
	 * @param Model $model2
	 * @return Link
	 */
	public static function link (Model $model1, Model $model2)
	{
		if (strcmp ($model1->table (), $model2->table ()) > 0)
	    {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }
	    
	    $link = self::_link (
	        $model1->table (), $model1->key (),
	        $model2->table (), $model2->key ()
	    );
	    
	    if (!$link)
	    {
	    	Loader::load ('Link');
	        $link = new Link (array (
	            'fromTable'	=> $model1->table (),
	            'fromRowId'	=> $model1->key (),
	            'toTable'	=> $model2->table (),
	            'toRowId'	=> $model2->key ()
	        ));
	        $link->save ();
	    }
	    
	    return $link;
	}
	
	/**
	 * 
	 * @param Model $model1
	 * @param string $model2
	 * @return Model_Collection
	 */
	public static function linkedItems (Model $model1, $model2)
	{
	    $collection_class = $model2 . '_Collection';
	    
	    Loader::load ($collection_class);
	    $result = new $collection_class ();
	    $key_field_2 = IcEngine::$modelManager->modelScheme ()
	        ->keyField ($model2);
	    
		if (strcmp ($model1->table (), $model2) > 0)
	    {
	        $result
	        	->query ()
		            ->from ('Link')
		            ->where ('Link.fromTable', $model2)
		            ->where ("Link.fromRowId=`$model2`.`$key_field_2`")
		            ->where ('Link.toTable', $model1->table ())
		            ->where ('Link.toRowId', $model1->key ()); 
	    }
	    else
	    {
	        $result
	        	->query ()
		            ->from ('Link')
		            ->where ('Link.fromTable', $model1->table ())
		            ->where ('Link.fromRowId', $model1->key ())
		            ->where ('Link.toTable', $model2)
		            ->where ("Link.toRowId=`$model2`.`$key_field_2`");
	    }
	    
	    return $result;
	}
	
	/**
	 * 
	 * @param Model $model1
	 * @param Model $model2
	 */
	public static function unlink (Model $model1, Model $model2)
	{
	    if (strcmp ($model1->table (), $model2->table ()) > 0)
	    {
	        $model1 = $tmp = $model2;
	        $model2 = $tmp;
	    }
	    
	    $link = self::_link (
	        $model1->table (), $model1->key (),
	        $model2->table (), $model2->key ()
	    );
	    
	    if ($link)
	    {
	        return $link->delete ();
	    }
	    
	    return null;
	}
	
}