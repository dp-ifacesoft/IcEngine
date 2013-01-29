<?php

class Helper_Temp_Content 
{
    
    public static $types = array (
        'Image',
        'Video'
    );
    
    /**
     * Переброс медиа от временного контента к модели.
     * 
     * @param Temp_Content $tc
     * @param Model $comment
     * @param Data_Transport $input
     */
    public static function rejoinMedia (Temp_Content $tc, Model $comment, 
        Data_Transport $input)
    {
        $media = $input->receive ('media');
        
        foreach (self::$types as $type)
        {
            $items = $tc->component ($type);
            
            foreach ($items as $item)
    		{
    		    $name = $type . '_' . $item->key ();
    		    if (array_search ($name, $media) !== false)
    		    {
    		        $item->rejoin ($comment);
    		    }
    		    else
    		    {
    		        $item->delete ();
    		    }
    		}
        }
    }
    
}