<?php

/**
 *
 * @desc Позволяет держать шаблоны смарти в базе.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class View_Render_Smarty_Cacher_Mysql extends View_Render_Smarty_Cacher_Abstract
{

	protected function _cacheClear ($key, $cache_id, $compile_id, $tpl_file,
		&$cache_content
	)
	{
		// clear cache info
		if(empty($cache_id) && empty($compile_id) && empty($tpl_file)) {
			// clear them all
			$results = mysqli_query(DDS::getDataSource()->getDataMapper()->linkIdentifier(), 'delete from CACHE_PAGES');
		} else {
			$results = mysqli_query(DDS::getDataSource()->getDataMapper()->linkIdentifier(), "delete from CACHE_PAGES where CacheID='$cache_id'");
		}
		if(!$results) {
			$smarty_obj->_trigger_error_msg('cache_handler: query failed.');
		}
		return $results;
	}

	protected function _cacheRead ($key, $cache_id, $compile_id, $tpl_file,
		&$cache_content
	)
	{
		// read cache from database
		$results = mysqli_query(DDS::getDataSource()->getDataMapper()->linkIdentifier(), "select CacheContents from CACHE_PAGES where CacheID='$CacheID'");
		if(!$results)
		{
			$smarty_obj->_trigger_error_msg('cache_handler: query failed.');
		}
		$row = mysql_fetch_array($results,MYSQL_ASSOC);

		if($use_gzip && function_exists('gzuncompress'))
		{
			$cache_content = gzuncompress($row['CacheContents']);
		}
		else
		{
			$cache_content = $row['CacheContents'];
		}

		return $results;
	}

	protected function _cacheWrite ($key, $cache_id, $compile_id, $tpl_file,
		&$cache_content
	)
	{
		// save cache to database

		if($use_gzip && function_exists("gzcompress"))
		{
			// compress the contents for storage efficiency
			$contents = gzcompress($cache_content);
		}
		else
		{
			$contents = $cache_content;
		}
		$results = mysqli_query(
            DDS::getDataSource()->getDataMapper()->linkIdentifier(), "replace into CACHE_PAGES values(
						'$cache_id',
						'".addslashes($contents)."')
					");
		if(!$results)
		{
			$smarty_obj->_trigger_error_msg('cache_handler: query failed.');
		}
		return $results;
	}

}