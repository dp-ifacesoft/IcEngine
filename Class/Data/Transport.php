<?php
/**
 *
 * @desc Транспорт данных
 * @author Юрий
 * @package IcEngine
 *
 */
class Data_Transport
{

	/**
	 * @desc Фильтры применяемые только на вход транспорта.
	 * @var Filter_Collection
	 */
	protected $_inputFilters;

	/**
	 * @desc Фильтры применяемые только на выход транспорта.
	 * @var Filter_Collection
	 */
	protected $_outputFilters;

    /**
     * @desc Поставщики данных.
     * @var array <Data_Provider_Abstract>
     */
	protected $_providers = array ();

    /**
     * @desc Валидаторы выхода.
     * @var Data_Validator_Collection
     */
	protected $_validators;

	/**
	 * Стек начатых транзакций
	 * @var array
	 */
	protected $_transactions = array ();

	/**
	 * @desc Возвращает экземпляр коллекции фильтров
	 */
	public function __construct ()
	{
		$this->resetFilters ();
		$this->resetValidators ();
	}

    /**
     * @param Data_Provider_Abstract $provider
     * @return Data_Transport
     */
	public function appendProvider (Data_Provider_Abstract $provider)
	{
		$this->_providers [] = $provider;
		return $this;
	}

	/**
	 * Начинает новую транзакцию
	 * @return Data_Transport_Transaction
	 * 		Созданная транзакция
	 */
	public function beginTransaction ()
	{
	    $transaction = new Data_Transport_Transaction ($this);
	    $this->_transactions [] = $transaction;

	    return $transaction;
	}

	/**
	 * @return Data_Transport_Transaction
	 * 		Текущая транзакция
	 */
	public function currentTransaction ()
	{
	    return end ($this->_transactions);
	}

	/**
	 * Заканчивает текущую транзакцию
	 * @return Data_Transport_Transaction
	 * 		Законченная транзакция
	 */
	public function endTransaction ()
	{
	    return array_pop ($this->_transactions);
	}

    /**
     * @desc Входные фильтры.
     * @return Filter_Collection
     */
	public function inputFilters ()
	{
		return $this->_inputFilters;
	}

	/**
	 *
	 * @param integer $index
	 * @return Data_Provider_Abstract
	 */
	public function getProvider ($index)
	{
		return isset($this->_providers [$index])
			? $this->_providers [$index] : null;
	}

    /**
     *
     * @return array
     */
	public function getProviders ()
	{
		return $this->_providers;
	}

    /**
     *
     * @return array
     */
	public function getValidators ()
	{
		return $this->_validators;
	}

	/**
	 * @desc Композит на массив провайдеров
	 * @return Composite
	 */
	public function providers ()
	{
		return new Composite ($this->_providers);
	}

	/**
	 * @desc Инициализация или сброс фильтров.
	 */
	public function resetFilters ()
	{
		$this->_inputFilters = new Filter_Collection ();
		$this->_outputFilters = new Filter_Collection ();
	}

	/**
	 * @desc Инициализация или сброс валидаторов.
	 */
	public function resetValidators ()
	{
		$this->_validators = new Data_Validator_Collection ();
	}

	/**
	 * @desc Выходные фильтры
	 */
	public function outputFilters ()
	{
		return $this->_outputFilters;
	}

    /**
     * @desc Получение данных.
     * @param mixed $_
     * @return mixed
     */
	public function receive ()
	{
		$keys = func_get_args ();
		$results = array ();

		if ($this->_transactions)
		{
			$buffer = end ($this->_transactions)->buffer ();
			foreach ($keys as $key)
			{
				$data = null;
				$chunk = isset ($buffer [$key]) ? $buffer [$key] : null;
				$this->_outputFilters->apply ($chunk);
				if (!is_null ($chunk) && $this->_validators->validate ($chunk))
				{
					$data = $chunk;
				}
				$results [] = $data;
			}
		}
		else
		{
			for ($i = 0, $icount = count ($keys); $i < $icount; $i++)
			{
				$data = null;
				for ($j = 0, $jcount = count ($this->_providers); $j < $jcount; ++$j)
				{
				    /*
				     * @var Data_Provider_Abstract $provider
				     */
					$provider = $this->_providers [$j];
					$chunk = $provider->get ($keys [$i]);
					$this->_outputFilters->apply ($chunk);
					if (!is_null ($chunk) && $this->_validators->validate ($chunk))
					{
						$data = $chunk;
					}
				}
				$results [] = $data;
			}
		}

		return count ($results) == 1 ? $results [0] : $results;
	}

	/**
	 * @desc Получает все значения из всех провайдеров.
	 * Не рекомендуется использовать.
	 * @return array Массив пар (ключ => значение)
	 */
	public function receiveAll ()
	{
		if ($this->_transactions)
		{
			return end ($this->_transactions)->buffer ();
		}

		$result = array ();
		foreach ($this->_providers as $provider)
		{
			$result = array_merge (
				$result,
				$provider->getAll ()
			);
		}
		return $result;
	}


    /**
     * @desc получает модель, заполненную входными данными. Входные данные должны иметь имена
     *      ModelName[field1], ModelName[field2], ..., ModelName[fieldN]
     * @author red
     * @param string $model_name наименование модели
     * @return Model
     */
    public function receiveModel ($model_name)
    {
        $fields = Model_Scheme::fieldsNames ($model_name);
        $values = $this->receive($model_name);

        $model_data = array();
        foreach ($fields as $field)
        {
            $model_data[$field] = $values[$field];
        }

        $model = Model_Manager::create ($model_name, $model_data);
        return $model;
    }


	/**
	 * @desc Возвращает массив пар "ключ - значение"
	 * @param string $_ Название переменной
	 * @return array
	 */
	public function receiveArray ()
	{
		if (func_num_args () == 1)
		{
			return array (
				func_get_arg (0) => $this->receive (func_get_arg (0))
			);
		}

		return array_combine (
			func_get_args (),
			call_user_func_array (
				array ($this, 'receive'),
				func_get_args ()
			)
		);
	}

	/**
	 * @desc Очистка данных всех провайдеров и сброс транзаций.
	 * @return Data_Transport
	 */
	public function reset ()
	{
		$this->_transactions = array ();
		for ($i = 0, $count = sizeof ($this->_providers); $i < $count; ++$i)
		{
			$this->_providers [$i]->clear ();
		}
		return $this;
	}

    /**
     *
     * @param string|array $key
     * @param mixed $data
     * @return Data_Transport
     */
	public function send ($key, $data = null)
	{
		if ($this->_transactions)
		{
			$this->currentTransaction ()->send ($key, $data);
		}
		else
		{
			$this->sendForce ($key, $data);
		}
		return $this;
	}

	/**
	 *
	 * @param string|array $key
	 * @param mixed $data
	 * @return Data_Transport
	 */
	public function sendForce ($key, $data = null)
	{
		if (!is_array ($key))
		{
			$key = array ($key => $data);
		}

		foreach ($key as $k => $v)
		{
            if($v){
                $this->_inputFilters->apply ($v);
                for ($i = 0, $count = sizeof ($this->_providers); $i < $count; $i++)
                {
                    $this->_providers [$i]->set ($k, $v);
                }
            }
		}

		return $this;
	}

    /**
     *
     * @param array|Data_Provider_Abstract $providers
     * @return Data_Transport
     */
	public function setProviders ($providers)
	{
		$this->_providers = array_merge ($this->_providers, (array) $providers);
		return $this;
	}

}