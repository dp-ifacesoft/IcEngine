<?php

/**
 * Транзакция данных. Используется для отложенного направления 
 * данных в(из) транспорт
 * 
 * @author goorus, morph
 */
class Data_Transport_Transaction
{
    /**
     * Буффер транзакции. Данные, которые были направлены в транспорт
     * 
     * @var array
     */
    protected $buffer = array();
    
    /**
     * Транспорт, для которого создана транзакция
     * 
     * @var Data_Transport
     */
    protected $transport;
    
	/**
	 * Конструктор
     * 
	 * @param Data_Transport $transport Трансорт
	 */
    public function __construct(Data_Transport $transport)
    {
        $this->transport = $transport;
    }
    
    /**
     * Получает и возвращает значение из транзации
     * 
     * @param string $key Ключ
     * @return mixed Значение
     */
    public function receive($key)
    {
        return isset($this->buffer[$key]) ? $this->buffer[$key] : null;
    }
    
    /**
     * Запись значения в транзакцию
     * 
     * @param array|string $key Ключ или массив пар (Ключ => Значение)
     * @param mixed $data Значение
     */
    public function send($key, $data = null)
    {
        if (is_array($key)) {
            $this->buffer = array_merge($this->buffer, $key);
        } else {
            $this->buffer[$key] = $data;
        }
    }
    
    /**
	 * Возвращает буффер транзации.
     * 
     * @return array
     */
    public function buffer()
    {
        return $this->buffer;
    }
    
    /**
     * Коммит транзакции. Направляет в транспорт данные, 
     * накопленные в транзакции
     */
    public function commit()
    {
        $this->transport->sendForce($this->buffer);
    }
    
    /**
     * Сброс буфера
     */
    public function flush()
    {
        $this->buffer = array();
    }
}