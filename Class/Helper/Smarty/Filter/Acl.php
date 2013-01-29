<?php
/**
 * 
 * @desc Помощник для показа дополнительных секций администратора.
 * Код, расположенный между неподходящими блоками acl будет вырезан
 * из шаблона и не 
 * @tutorial
 * 		{acl role="admin"}
 * 			some protected data
 * 		{aclelse}
 * 			if access denied
 * 		{/acl}
 * @author Юрий
 * @package IcEngine
 * @deprecated Вместо этого фильтра будет использоваться smarty_block_acl,
 * т.к. в противном случае при кэшировании шаблона происходит кэширование
 * прав доступа (т.к. кэширование идет после наложения фильтров).
 *
 */
class Helper_Smarty_Filter_Acl
{
	
	/**
	 * @desc Регулярное выражение, по которому ищутся защищенные блоки.
	 * @var string
	 */
	const REGEX	= '#\{acl(\}|\s{1,}[^}]{1,}\})((?:[^[]|\{(?!/?acl})|(?R))+|)\{/acl}#';
	
	/**
	 * @desc Проверяет имеет ли пользователь доступ
	 * @param string $rules
	 * @return boolean
	 */
	protected static function _accessGranded ($rules)
	{
		$rules = preg_split ("#\\s#", trim ($rules, " \t\r\n}"));
    	
		foreach ($rules as $rule)
		{
			if (strncmp ($rule, 'role=', 5) == 0)
			{
				// Указаны роли, для которых доступ открыт
				
				$roles = explode (
					',', 
					trim (
						substr ($rule, 5), 
						'"\''
					)
				);
				
				foreach ($roles as $role)
				{
					$role = Acl_Role::byName ($role);
					if (!User::getCurrent ()->hasRole ($role))
					{
						// Пользователь не имеет указанной роли
						return false;
					}
				}
			}
			elseif (strncmp ($rule, 'auth=', 5) == 0)
			{
				$auth = trim (
					substr ($rule, 5), 
					'"\''
				);
				
				if ($auth != User::authorized ())
				{
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * @desc Регистрация фильтра в смарти.
	 * @param Smarty $smarty
	 */
	public static function register (Smarty $smarty)
	{
		$smarty->register_prefilter (array (__CLASS__, 'filterAcl'));
	}
	
	/**
	 * @desc Реализует ACL для шаблона.
	 * Этот метод вызывается из смарти перед обработкой шаблона.
	 * @param string $tpl_source Исходный код шаблона.
	 * @param Smarty $smarty Экземпляр смарти
	 * @return string Результат фильтрации.
	 */
	public static function filterAcl ($tpl_source, Smarty $smarty)
	{
		if (!$tpl_source)
		{
			return;
		}
		
		return self::pregReplace ($tpl_source);
	}
	
	/**
	 * @desc Разбор строки на наличие тегов доступа.
	 * @param string|array $input
	 * @return string
	 */
	public static function pregReplace ($input)
	{
	    if (is_array ($input))
	    {
			$access = self::_accessGranded ($input [1]);
			
			$else_pos = strpos ($input [2], '{aclelse}');
			
			if ($access)
			{
				if ($else_pos === false)
				{
					return $input [2];
				}
				
				$input = substr ($input [2], 0, $else_pos);
			}
			elseif ($else_pos !== false)
			{
				$input = substr ($input [2], $else_pos + 9);//strlen ('{aclelse}'));
			}
			else
			{
				return '';
			}
	    }
	
	    return preg_replace_callback (self::REGEX, __METHOD__, $input);
	}
	
	/**
	 * @desc Метод для тестирования разобра строки
	 */
	public static function pregReplaceTest ($input)
	{
		static $deep = 0;
		
	    if (is_array ($input))
	    {
	    	echo $deep . ':', var_export ($input, true), '<br />';
	    	
	    	$rules = preg_split ("#\\s#", trim ($input [1], " \t\r\n}"));
	    	
	    	echo 'rules: ', var_export ($rules, true), '<br />';
	    	
	        $input = '{a' . $deep .'}' . $input [2] . '{/a' . $deep . '}';
	    }
	
	    ++$deep;
	    $r = preg_replace_callback (self::REGEX, __METHOD__, $input);
	    --$deep;
	    return $r;
	}
	
}

// Тесты
//$tests = array (
//	'{acl}{/acl}',
//	'{acl}foo{/acl}',
//	'{acl role="bar"}{/acl}',
//	'{acl role="bar"}foo{/acl}',
//	'{acl role="bar"}foo{acl role="admin" role="guest"}biz{/acl}{/acl}'
//);
//
//foreach ($tests as $test)
//{
//	echo 'input: ', $test , '<br />';
//	$test = Helper_Smarty_Filter_Acl::pregReplaceTest ($test);
//	echo '<br />result: ', $test, "\n<hr />\n";
//}