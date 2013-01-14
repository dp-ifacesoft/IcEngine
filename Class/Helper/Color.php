<?php
/**
 * 
 * @desc Помощник для работы с цветами.
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
class Helper_Color
{
	
	/**
	 * @desc Возвращает массив значений R, G, B для цвета
	 * @param string $css Цвет CSS
	 * @return array Массив [0..2] в порядке R, G, B
	 */
	public static function cssToRgb ($css)
	{
		$hexcolor = '000000';
		$p = 0;
		
		for ($i = 0; $i < strlen ($css) && $p < strlen ($hexcolor); $i++)
		{
			if (strpos ('-01234567890abcdefABCDEF', $css [$i]) > 0)
			{
				$hexcolor [$p] = $css [$i];
				$p++;
			}
		}
		
		return array (
			0 => hexdec (substr ($hexcolor, 0, 2)),
			1 => hexdec (substr ($hexcolor, 2, 2)),
			2 => hexdec (substr ($hexcolor, 4, 2))
		);
	}
	
	/**
	 *
	 * @param type $rs		int R составляющая цвета начала диапозона
	 * @param type $gs		int G составляющая цвета начала диапозона
	 * @param type $bs		int B составляющая цвета начала диапозона
	 * @param type $rf		int R составляющая цвета конца диапозона
	 * @param type $gf		int G составляющая цвета конца диапозона
	 * @param type $bf		int B составляющая цвета конца диапозона
	 * @param type $rateMin минимальное значение рейтинга
	 * @param type $rateMax максисмальное значение рейтинга
	 * @param type $rateValue  значение текущее рейтинга
	 */
	public  static function colorForRate ($rs, $gs, $bs, $rf, $gf, $bf, $rateMin, $rateMax, $rateValue)
	{
		// преращение цветов
		$dr =$rf-$rs;
		$dg =$gf-$gs;
		$db =$bf-$bs;
		// диапозон изменения рейтинга
		$range = $rateMax - $rateMin;
		
		$outR = ($rateValue-$rateMin)/$range*$dr+$rs;
		$outG = ($rateValue-$rateMin)/$range*$dg+$gs;
		$outB = ($rateValue-$rateMin)/$range*$db+$bs;
		
		return 'rgb(' . round($outR) . ',' . round($outG) . ',' . round($outB) . ')';
		
	}
	
}