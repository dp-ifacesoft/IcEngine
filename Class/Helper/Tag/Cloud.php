<?php

/**
 * @desc Хэлпер для облака тэгов. В память о Ромке :)
 * @author Роман Кузнецов. Переписал: Илья Колесников
 */
class Helper_Tag_Cloud
{
	/**
	 * @desc Определить размеры шрифта по полю count модели. Шрифты записываются в data('font_size') модели.
	 * @param Model_Collection $collection Коллекция
	 * @param integer $min_size минимальный размер шрифта.
	 * @param integer $max_size максимальный размер шрифта.
	 * @param integer $size_step Шаг шрифта (сколько прибавляется в каждом диапазоне)
	 * @return Model_Collection
	 */
	public static function fontSize (Model_Collection $collection, $min_size = 12,
		$max_size = 30, $size_step = 2)
	{
		if (!$collection->count ())
		{
			return;
		}

		// Количество шагов(диапазонов)
		$steps	= ceil (($max_size - $min_size) / $size_step);
		$range	= 1;

		$size	= $min_size;
		$start	= 1;

		$size_array = array ();

		for ($i = 0; $i < $steps; $i++)
		{
			$end = $start + $range;

			$size_array [$size] = array(
				'start' => $start,
				'end'   => $end
			);

			$end++;
			$start = $end;
			$size += $size_step;
		}

		foreach ($collection as $item)
		{
			$count = (int) ($item->data ('count')
				? $item->data ('count')
				: $item->sfield ('count')
			);
			$values = array_values ($size_array);
			$last = array_pop ($values);
			if ($count <= $last ['end'])
			{
				foreach ($size_array as $key => $size)
				{
					if (
						$count >= $size ['start'] &&
						$count <= $size ['end']
					)
					{
						$item->data ('font_size', $key);
					}
				}
			}
			else
			{
				$item->data ('font_size', $max_size);
			}
		}
		return $collection;
	}
}