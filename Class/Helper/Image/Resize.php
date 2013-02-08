<?php

/**
 * @Service("helperImageResize")
 */
class Helper_Image_Resize
{

	/**
	 * Качество сохранения JPEG изображений (от 1 до 100).
	 * @var integer
	 */
	public static $jpegQuality = 90;

	/**
	 * Изменение размера изображения
	 *
	 * @param string $input
	 * 		Файл с исходным изображением
	 * @param string $output
	 * 		Файл для сохранения результата
	 * @param integer $width
	 * 		Конечная ширина
	 * @param integer $height
	 * 		Высота
	 * @param boolean $proportional
	 * 		Если true, пропорции изображения будут сохранены.
	 * @param boolean|string $crop
	 * 		Параметры обрезки.
	 * 		false - края не обрезаются.
	 * 		true - края будут обрезаны на одинаковую величину с выступающей оси,
	 * 		изображение будет выравнено по центру.
	 * 		"up" - при необходимости будет обрезан нижний край изображения, так
	 * 		чтобы верхняя часть сохранилась.
	 * @param boolean $fit
	 * 		Если true, конечное изображение будет иметь заданные размеры, игнорируя
	 * 		начальные пропорции. В этом случае параметр $proportional не учитывается
	 *
	 * @return array|false
	 * 		Данные об итоговом изображении (аналогично getimagesize)
	 * 		или false в случае неудачи.
	 */
	public function resize(
		$input, $output, $width = 0, $height = 0,
		$proportional = false, $crop = true, $fit = false
	)
	{
		if ($height <= 0 && $width <= 0 && !is_array($crop))
		{
			return false;
		}
		$info = getimagesize ($input);
		$image = '';
		$final_width = 0;
		$final_height = 0;
		list ($width_old, $height_old) = $info;

		if ($proportional)
		{
			if ($width == 0)
			{
				$factor = $height / $height_old;
			}
			elseif ($height == 0)
			{
				$factor = $width / $width_old;
			}
			else
			{
				$factor = min($width / $width_old, $height / $height_old);
			}

			if ($fit)
			{
				$final_width = $width;
				$final_height = $height;
			}
			else
			{
				$final_width = round ($width_old * $factor);
				$final_height = round ($height_old * $factor);
			}

			if ($final_width > $width_old || $final_height > $height_old)
			{
				$final_width = $width_old;
				$final_height = $height_old;
			}

		}
		else
		{
			$final_width = ($width <= 0) ? $width_old : $width;
			$final_height = ($height <= 0) ? $height_old : $height;
		}

		if (is_array($crop)) {

			$scale = $info[0]/$crop['width'];
			if ($scale<1) {
				$scale = 1;
			}
			$crop['x1'] *= $scale;
			$crop['x2'] *= $scale;
			$crop['y1'] *= $scale;
			$crop['y2'] *= $scale;
			$final_width = $crop['x2']-$crop['x1'];
			$final_height = $crop['y2']-$crop['y1'];
		}

		switch ($info[2])
		{
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif ($input);
				break;
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg ($input);
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng ($input);
				break;
			default:
				return false;
		}

		$image_resized = imagecreatetruecolor ($final_width, $final_height);
		if (($info [2] == IMAGETYPE_GIF) || ($info [2] == IMAGETYPE_PNG))
		{
			$trnprt_indx = imagecolortransparent ($image);
			// If we have a specific transparent color
			if ($trnprt_indx >= 0)
			{
				// Get the original image's transparent color's RGB values
				$trnprt_color = imagecolorsforindex ($image, $trnprt_indx);
				// Allocate the same color in the new image resource
				$trnprt_indx = imagecolorallocate (
					$image_resized,
					$trnprt_color ['red'],
					$trnprt_color ['green'],
					$trnprt_color ['blue']
				);
				// Completely fill the background of the new image with allocated color.
				imagefill ($image_resized, 0, 0, $trnprt_indx);
				// Set the background color for new image to transparent
				imagecolortransparent ($image_resized, $trnprt_indx);
			}
			// Always make a transparent background color for PNGs that don't have one allocated already
			elseif ($info[2] == IMAGETYPE_PNG)
			{
				// Turn off transparency blending (temporarily)
				imagealphablending ($image_resized, false);
				// Create a new transparent color for image
				$color = imagecolorallocatealpha ($image_resized, 0, 0, 0, 100);
				// Completely fill the background of the new image with allocated color.
				imagefill ($image_resized, 0, 0, $color);
				// Restore transparency blending
				imagesavealpha ($image_resized, true);
			}
		}

		if ($crop)
		{
			// растягиваем фото по оси Х
			$scalex = //($width_old > $height_old);
				($height_old / $final_height) < ($width_old / $final_width);

			if (is_array($crop)) {
				imagecopyresampled (
					$image_resized, $image,
					0, 0, $crop['x1'], $crop['y1'],
					$crop['x2']-$crop['x1'], $crop['y2']-$crop['y1'],$crop['x2']-$crop['x1'], $crop['y2']-$crop['y1']
				);
			} else
			if ($crop == 'up')
			{
				if ($scalex)//($width_old > $height_old)
				{
					$src_width = ($height_old / $final_height) * $final_width;
					$src_x = $width_old / 2 - $src_width / 2;
					imagecopyresampled(
						$image_resized, $image,
						0, 0, $src_x, 0,
						$final_width, $final_height, $src_width, $height_old
					);
				}
				else
				{
					$src_height = ($width_old / $final_width) * $final_height;
					$src_y = $height_old / 2 - $src_height / 2;
					imagecopyresampled(
						$image_resized, $image,
						0, 0, 0, 0,
						$final_width, $final_height, $width_old, $src_height
					);
				}
			}
			elseif ($scalex)//($width_old > $height_old)
			{
				$src_width = ($height_old / $final_height) * $final_width;
				$src_x = $width_old / 2 - $src_width / 2;
				imagecopyresampled(
					$image_resized, $image,
					0, 0, $src_x, 0,
					$final_width, $final_height, $src_width, $height_old
				);
			}
			else
			{
				$src_height = ($width_old / $final_width) * $final_height;
				$src_y = $height_old / 2 - $src_height / 2;
				imagecopyresampled (
					$image_resized, $image,
					0, 0, 0, $src_y,
					$final_width, $final_height, $width_old, $src_height
				);
			}
		}
		else
		{
			imagecopyresampled (
				$image_resized, $image,
				0, 0, 0, 0,
				$final_width, $final_height, $width_old, $height_old
			);
		}

		switch ($info [2])
		{
			case IMAGETYPE_GIF:
				imagegif ($image_resized, $output);
				break;
			case IMAGETYPE_JPEG:
				imagejpeg ($image_resized, $output, self::$jpegQuality);
				break;
			case IMAGETYPE_PNG:
				imagepng ($image_resized, $output);
				break;
			default:
				return false;
		}

        imagedestroy($image);
        imagedestroy($image_resized);

		return array ($final_width, $final_height, $info [2]);
	}

}