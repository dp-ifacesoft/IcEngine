<?php

class UOW_Query_Delete extends UOW_Query_Abstract
{
	public function build($key, $data)
	{
		$table = $key;
		$keys = array();
		foreach ($data as $where) {
			if (!isset($where['wheres']['id'])) {
				continue;
			}
			$keys[] = $where['wheres']['id'];
		}
		if (!$keys) {
			return;
		}
		$query = 'DELETE FROM ' . 'ice_' . strtolower($table) .
			' WHERE id IN(' . implode(',', $keys) . ')';
		return $query;
	}

	public function push(Query_Abstract $query)
	{
		$from = array_keys($query->getPart('FROM'));
		$table = $from[0];
		$where = $query->getPart('WHERE')?: false;
		$wheres = array();
		if ($where) {
			foreach ($where as $value) {
				$wheres[$value['WHERE']] = $value['VALUE'];
			}
		}
		$uniqName = $table;
		UOW::pushRaw('DELETE', $uniqName, array(
			'wheres'	=> $wheres
		));
	}
}