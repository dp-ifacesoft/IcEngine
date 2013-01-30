<?php

class Data_Mapper_DBI extends Data_Mapper_Abstract
{

	/**
	 *
	 * @param mixed $result
	 * @param mixed $options
	 * @return boolean
	 */
	protected function _isCurrency ($result, $options)
	{
		if (!$options)
		{
			return true;
		}

		return $options->getNotEmpty () && empty ($result) ? false : true;
	}

	public function execute (Data_Source_Abstract $source, Query $query, $options = null)
	{
		if (!($query instanceof Query))
		{
			return new Query_Result (null);
		}

		$start = microtime (true);

		$sql = $query->translate ('Mysql', $this->_modelScheme);

		$result = null;
		$insert_id = null;

		switch ($query->type()) {
			case Query::DELETE:
				DBI::doQuerySql ($sql);
				$touched_rows = mysql_affected_rows ();
				break;
			case Query::INSERT:
				DBI::doQuerySql ($sql);
				$insert_id = DBI::getInsertId ();
				$touched_rows = mysql_affected_rows ();
				break;
			case Query::SELECT; case Query::SHOW:
				$result = DBI::doQueryAllSql ($sql);
				$touched_rows = count ($result);
				break;
			case Query::UPDATE:
				DBI::doQuerySql ($sql);
				$touched_rows = mysql_affected_rows ();
				break;
		}

		$errno = mysql_errno();
		$error = mysql_error();

		if (!empty($errno))
		{
			throw new Data_Mapper_Mysql_Exception ($error . "\n$sql", $errno);
		}

		if (empty($errno) && is_null($result))
		{
			$result = array ();
		}

		$finish = microtime (true);

		return new Query_Result (array (
			'error'			=> $error,
			'errno'			=> $errno,
			'query'			=> $query,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
			'result'		=> $result,
			'touchedRows'	=> $touched_rows,
			'insertKey'		=> $insert_id,
			'currency'		=> $this->_isCurrency ($result, $options),
			'source'		=> $source
		));
	}
}
