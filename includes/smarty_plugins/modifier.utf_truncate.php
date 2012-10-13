<?php
/**
 * Smarty plugin
 * @package IcEngine
 * @subpackage smarty_plugins
 */


/**
 * Smarty utf_truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     utf_truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @author   Goorus <goors at list dot ru>
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */
function smarty_modifier_utf_truncate ($string, $length = 80, $etc = '…',
    $break_words = false, $middle = false)
{
    if ($length == 0)
    {
        return '';
    }

    if (mb_strlen ($string, 'UTF-8') > $length)
    {
        $length -= min ($length, mb_strlen ($etc, 'UTF-8'));
        if (!$break_words && !$middle)
        {
            $string = preg_replace ('/\s+?(\S+)?$/', '', mb_substr ($string, 0, $length + 1, 'UTF-8'));
        }
        if (!$middle)
        {
            return mb_substr ($string, 0, $length, 'UTF-8') . $etc;
        }
        else
        {
            return mb_substr ($string, 0, $length / 2, 'UTF-8') . $etc . mb_substr ($string, -$length / 2, null, 'UTF-8');
        }
    }
    else
    {
        return $string;
    }
}
