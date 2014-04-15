<?php

/**
 * @author neon
 * @Service("helperImageResize")
 */
class Helper_Image_Resize extends Helper_Abstract
{

    /**
     * Путь куда будут сохранятся картинки, если не указать output
     * @var string
     */
    protected static $tmpPath = '/tmp/';

	/**
	 * Качество сохранения JPEG изображений (от 1 до 100).
	 * @var integer
	 */
	public static $jpegQuality = 90;

    
    public function superCrop($params)
    {
        $input = isset($params['input']) ? $params['input'] : null;
        $output = isset($params['output']) ? $params['output'] : null;
        $crop = $params['crop'];
        if (!$output) {
            return false;
        }
        if (!file_exists($input)) {
            return false;
        }
        if (!is_readable($input)) {
            echo 'not readable' . PHP_EOL;
            return false;
        }
        list($inputWidth, $inputHeight, $inputType) = getimagesize($input);
        if (!$inputWidth || !$inputHeight) {
            echo 'incorrect file' . PHP_EOL;
            return false;
        }
		switch ($inputType) {
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($input);
                $ext = 'gif';
				break;
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg($input);
                $ext = 'jpg';
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($input);
                $ext = 'png';
				break;
			default:
				return false;
		}
        $imageCrop = imagecreatetruecolor(
            $params['widthResult'], $params['heightResult']
        );
        $isGif = $inputType == IMAGETYPE_GIF;
        $isPng = $inputType == IMAGETYPE_PNG;
		if ($isGif || $isPng){
			$transparentIndex = imagecolortransparent($image);
			// If we have a specific transparent color
			if ($transparentIndex >= 0) {
				// Get the original image's transparent color's RGB values
				$transparentColor = imagecolorsforindex(
                    $image, $transparentIndex
                );
				// Allocate the same color in the new image resource
				$transparentIndex = imagecolorallocate (
					$imageCrop,
					$transparentColor['red'],
					$transparentColor['green'],
					$transparentColor['blue']
				);
				// Completely fill the background of the new image with allocated color.
				imagefill($imageCrop, 0, 0, $transparentIndex);
				// Set the background color for new image to transparent
				imagecolortransparent($imageCrop, $transparentIndex);
            // Always make a transparent background color for PNGs that don't
            // have one allocated already
			} elseif ($isPng) {
				// Turn off transparency blending (temporarily)
				imagealphablending($imageCrop, false);
				// Create a new transparent color for image
				$color = imagecolorallocatealpha($imageCrop, 0, 0, 0, 100);
				// Completely fill the background of the new image with allocated color.
				imagefill($imageCrop, 0, 0, $color);
				// Restore transparency blending
				imagesavealpha($imageCrop, true);
			}
		}
        
        //resize исходной картинки
        imagecopyresampled(
            $imageCrop, $image,
            0, 0, $crop['left'], $crop['top'],
            $params['widthResult'], $params['heightResult'], 
            $crop['width'], $crop['height']
        );
		switch ($inputType) {
			case IMAGETYPE_GIF:
				imagegif($imageCrop, $output);
				break;
			case IMAGETYPE_JPEG:
				imagejpeg($imageCrop, $output, self::$jpegQuality);
				break;
			case IMAGETYPE_PNG:
				imagepng($imageCrop, $output);
				break;
			default:
				return false;
		}
        imagedestroy($image);
        imagedestroy($imageCrop);
		return true;
    }
    
    /**
     * $params = array(
     *      "input", "output", "width", "height", "offsetLeft", "offetTop",
     *      "center"
     * )
     * center equal offsetLeft="center" and offsetTop="center"
     * @param array $params
     * @return array
     */
    public function crop($params)
    {
        $crop = array(
            'width'     => $params['width'],
            'height'    => $params['height']
        );
        if (isset($params['center'])) {
            $crop['center'] = true;
        }
        if (isset($params['offsetLeft'])) {
            $crop['offsetLeft'] = $params['offsetLeft'];
        }
        if (isset($params['offsetTop'])) {
            $crop['offsetTop'] = $params['offsetTop'];
        }
        $paramsBuilded = array(
            'input'         => $params['input'],
            'output'        => $params['output'],
            'width'         => $params['width'],
            'height'        => $params['height'],
            'crop'          => $crop,
            'proportional'  => true
        );
        return $this->newResize($paramsBuilded);
    }

    public function cropCustom($params)
    {
        $crop = $params['crop'];
        if (isset($crop['x1'])) {
            $crop['offsetLeft'] = $crop['x1'];
        }
        if (isset($crop['y1'])) {
            $crop['offsetTop'] = $crop['y1'];
        }
        $paramsBuilded = array(
            'input'         => $params['input'],
            'output'        => $params['output'],
            'crop'          => $crop
        );
        return $this->newResize($paramsBuilded);
    }

    /**
     * $params = array(
     *      "width", "height",
     *      "crop"  => array("width", "height", "offsetLeft", "offsetTop", "center"),
     *      "proportional", "fit"
     * )
     * Если заданы width и height и передан crop, если
     *      width = 0 и height = 0, то кроп будет с исходной картинки
     *      если они > 0, то сначало режется картинка, потом с неё кроп
     * fit - не просто сжать, а максимально возможный кусок картинки вписать
     * @param array $params
     * @return false|array
     */
    public function newResize($params)
    {
        $width = isset($params['width']) ? $params['width'] : 0;
        $height = isset($params['height']) ? $params['height'] : 0;
        $crop = isset($params['crop']) ? $params['crop'] : false;
        $proportional = isset($params['proportional']) ?
            $params['proportional'] : false;
        //$fit = isset($params['fit']) ? $params['fit'] : false;
        $notResize = $width <= 0 && $height <= 0;
        $scale = isset($params['scale']) ? $params['scale'] : null;
        $isCrop = is_array($crop);
        $output = isset($params['output']) ? $params['output'] : null;
        if ($notResize && !$isCrop) {
			return false;
		}
        $input = isset($params['input']) ? $params['input'] : null;
        if (!$input) {
            return false;
        }
        if (!file_exists($input)) {
            return false;
        }
        if (!is_readable($input)) {
            echo 'not readable' . PHP_EOL;
            return false;
        }
        list($inputWidth, $inputHeight, $inputType) = getimagesize($input);
        if (!$inputWidth || !$inputHeight) {
            echo 'incorrect file' . PHP_EOL;
            return false;
        }
        //пропорционально
		if ($proportional || $scale) {
            if (!$scale) {
                if (!$width) {
                    $scale = $inputHeight / $height;
                } elseif (!$height) {
                    $scale = $inputWidth / $width;
                } else {
                    $scale = min($inputWidth / $width, $inputHeight / $height);
                }
            }
            echo 'width ' . $width . PHP_EOL;
            echo 'height ' . $height . PHP_EOL;
            $newWidth = ceil($inputWidth / $scale);
            $newHeight = ceil($inputHeight / $scale);
            $widthIsLow = $newWidth < $width;
            $heightIsLow = $newHeight < $height;
            $fixScale = 1;
            if ($widthIsLow) {
                $fixScale = $width / $newWidth;
            } elseif ($heightIsLow) {
                $fixScale = $height / $newHeight;
            }
            $fixWidth = $newWidth * $fixScale;
            $fixHeight = $newHeight * $fixScale;
			/*if ($fit) {

				$final_width = $width;
				$final_height = $height;
			} else {
				$final_width = round ($width_old * $factor);
				$final_height = round ($height_old * $factor);
			}*/
        //сжать/растянуть
		} elseif ($width && $height) {
			$fixWidth = $width;
			$fixHeight = $height;
		} else {
            $fixWidth = $inputWidth;
            $fixHeight = $inputHeight;
        }
        //echo 'Fix Width ' . $fixWidth . PHP_EOL;
        //echo 'Fix Height ' . $fixHeight . PHP_EOL;
        $ext = '';
		switch ($inputType) {
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($input);
                $ext = 'gif';
				break;
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg($input);
                $ext = 'jpg';
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($input);
                $ext = 'png';
				break;
			default:
				return false;
		}
        if (!$output) {
            $output = $this->tmpPath . md5(uniqid('', true));
        }
        $output .= '.' . $ext;
        $containerResized = imagecreatetruecolor($fixWidth, $fixHeight);
        $isGif = $inputType == IMAGETYPE_GIF;
        $isPng = $inputType == IMAGETYPE_PNG;
		if ($isGif || $isPng){
			$transparentIndex = imagecolortransparent($image);
			// If we have a specific transparent color
			if ($transparentIndex >= 0) {
				// Get the original image's transparent color's RGB values
				$transparentColor = imagecolorsforindex(
                    $image, $transparentIndex
                );
				// Allocate the same color in the new image resource
				$transparentIndex = imagecolorallocate (
					$containerResized,
					$transparentColor['red'],
					$transparentColor['green'],
					$transparentColor['blue']
				);
				// Completely fill the background of the new image with allocated color.
				imagefill($containerResized, 0, 0, $transparentIndex);
				// Set the background color for new image to transparent
				imagecolortransparent($containerResized, $transparentIndex);
            // Always make a transparent background color for PNGs that don't
            // have one allocated already
			} elseif ($isPng) {
				// Turn off transparency blending (temporarily)
				imagealphablending($containerResized, false);
				// Create a new transparent color for image
				$color = imagecolorallocatealpha($containerResized, 0, 0, 0, 100);
				// Completely fill the background of the new image with allocated color.
				imagefill($containerResized, 0, 0, $color);
				// Restore transparency blending
				imagesavealpha($containerResized, true);
			}
		}
        //resize исходной картинки
        imagecopyresampled(
            $containerResized, $image,
            0, 0, 0, 0,
            $fixWidth, $fixHeight, $inputWidth, $inputHeight
        );
        if ($isCrop) {
            $imageCrop = imagecreatetruecolor(
                $crop['width'],
                $crop['height']
            );
            $offsetLeft = 0;
            $offsetTop = 0;
            if (isset($crop['center'])) {
                $offsetLeft = ($fixWidth - $crop['width']) / 2;
                $offsetTop = ($fixHeight - $crop['height']) / 2;
            } else {
                if (isset($crop['offsetLeft'])) {
                    $offsetLeft = $crop['offsetLeft'];
                    if ($crop['offsetLeft'] == 'center') {
                        $offsetLeft = ($fixWidth - $crop['width']) / 2;
                    }
                }
                if (isset($crop['offsetTop'])) {
                    $offsetTop = $crop['offsetTop'];
                    if ($crop['offsetTop'] == 'center') {
                        $offsetTop = ($fixHeight - $crop['height']) / 2;
                    }
                }
            }
           /* echo 'source Width ' . $fixWidth . PHP_EOL;
            echo 'source Height ' . $fixHeight . PHP_EOL;
            echo 'crop Width ' . $crop['width'] . PHP_EOL;
            echo 'crop Height ' . $crop['height'] . PHP_EOL;
            echo 'offsetLeft ' . $offsetLeft . PHP_EOL;
            echo 'offsetTop ' . $offsetTop . PHP_EOL;*/
            $sourceWidth = $crop['width'];
            $sourceHeight = $crop['height'];
            if ($notResize) {
                $sourceWidth = $crop['x2'] - $crop['x1'];
                $sourceHeight = $crop['y2'] - $crop['y1'];
            }
            /*echo 'Source Width ' . $sourceWidth . PHP_EOL;
            echo 'Source Height ' . $sourceHeight . PHP_EOL;
            echo 'Offset Left ' . $offsetLeft . PHP_EOL;
            echo 'Offset top ' . $offsetTop . PHP_EOL;
            die;*/
            imagecopyresampled(
                $imageCrop, $containerResized,
                0, 0, $offsetLeft, $offsetTop,
                $crop['width'], $crop['height'],
                $sourceWidth, $sourceHeight
            );
            $containerResized = $imageCrop;
        }
		switch ($inputType) {
			case IMAGETYPE_GIF:
				imagegif($containerResized, $output);
				break;
			case IMAGETYPE_JPEG:
				imagejpeg($containerResized, $output, self::$jpegQuality);
				break;
			case IMAGETYPE_PNG:
				imagepng($containerResized, $output);
				break;
			default:
				return false;
		}
        imagedestroy($image);
        imagedestroy($containerResized);
		return array(
            'width'     => $fixWidth,
            'height'    => $fixHeight,
            'type'      => $inputType,
            'ext'       => $ext
        );
    }

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
            $scale = 1;
            if (!isset($crop['noScale']) || !$crop['noScale']) {
                $scale = $info[0] / $crop['width'];
                if ($scale < 1) {
                    $scale = 1;
            	}
            }
			$crop['x1'] *= $scale;
			$crop['x2'] *= $scale;
			$crop['y1'] *= $scale;
			$crop['y2'] *= $scale;
			$final_width = $crop['x2'] - $crop['x1'];
			$final_height = $crop['y2'] - $crop['y1'];
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
					0, 0,
                    $crop['x1'], $crop['y1'],
					$crop['x2'] - $crop['x1'],
                    $crop['y2'] - $crop['y1'],
                    $crop['x2'] - $crop['x1'],
                    $crop['y2'] - $crop['y1']
				);
                if (isset($crop['final'])) {
                    /**
                     * $dst_image, $src_image,
                     * $dst_x, $dst_y,
                     * $src_x, $src_y,
                     * $dst_w, $dst_h,
                     * $src_w, $src_h
                     */
                    $imageCropResized = imagecreatetruecolor(
                        $crop['final']['width'],
                        $crop['final']['height']
                    );
                    imagecopyresampled(
                        $imageCropResized, $image_resized,
                        0, 0, 0, 0,
                        $crop['final']['width'],
                        $crop['final']['height'],
                        $crop['width'],
                        $crop['height']
                    );
                    $image_resized = $imageCropResized;
                }
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
                $result = imagegif ($image_resized, $output);
                break;
            case IMAGETYPE_JPEG:
                $result = imagejpeg ($image_resized, $output, self::$jpegQuality);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng ($image_resized, $output);
                break;
            default:
                return false;
        }

        imagedestroy($image);
        imagedestroy($image_resized);

        if (!$result)
        {
            return false;
        }
        else
        {
            return array ($final_width, $final_height, $info [2]);
        }
    }

}