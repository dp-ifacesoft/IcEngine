<?php

/**
 * Спрайт
 *
 * @author markov
 */
class Static_Sprite
{
    public $config = [
        'path'  => 'cache/sprites'
    ];
    
    /**
     * Название спрайта
     * 
     * @var string 
     */
    public $name;
    
    /**
     * Метаданные спрайта
     * 
     * @var array 
     */
    public $meta = [];
    
    public function __construct($spriteName)
    {
        $this->name = $spriteName;
        $this->loadMeta();
    }
    
    /**
     * Различны ли спрайты?
     * 
     * @param array $imagePaths
     * @return type
     */
    public function isEqual($imagePaths)
    {
        $meta = $this->getMeta();
        $metaImages = [];
        foreach ($meta as $item) {
            $metaImages[] = $item['path'];
        }
        $isEqual = !array_diff($metaImages, $imagePaths) && 
            count($metaImages) == count($imagePaths);
        return $isEqual;
    }
    
    /**
     * Возвращает нормализированный список урлов изображений
     * 
     * @param array $imagePaths
     * @return array
     */
    protected function _normalize($imagePaths)
    {
        $imagesUnique = array_unique($imagePaths);
        sort($imagesUnique);
        return $imagesUnique;
    }
    
    /**
     * Обновляет спрайт
     * 
     * @param array $imagePaths изображения
     */
    public function update($imagePaths)
    {
        if (!$imagePaths) {
            return false;
        }
        $imagePathsNormalized = $this->_normalize($imagePaths);
        if ($this->isEqual($imagePathsNormalized)) {
            return false;
        }
        $images = [];
        $imagesUrlAssoc = [];
        foreach ($imagePathsNormalized as $imagePath) {
            $absoluteImagePath = rtrim(IcEngine::root(), '/') . $imagePath;
            $image = $this->getImage($absoluteImagePath);
            $imagesUrlAssoc[] = [
                'image' => $image,
                'path'   => $imagePath
            ]; 
            $images[] = $image;
        }
        $spriteWidth = $this->calculateSpriteWidth($images);
        $spriteHeight = $this->calculateSpriteHeight($images);
        $sprite = imagecreatetruecolor($spriteWidth, $spriteHeight);
        imagealphablending($sprite, false);
        $color = imagecolorallocatealpha($sprite, 0, 0, 0, 100);
        imagefill($sprite, 0, 0, $color);
        imagesavealpha($sprite, true);
        $cursorX = 0;
        $this->meta = [];
        foreach ($imagesUrlAssoc as $item) {
            $itemImage = $item['image'];
            $imageWidth = imagesx($itemImage);
            $imageHeight = imagesy($itemImage);
            imagecopy(
                $sprite, $itemImage, 
                $cursorX, 0, 
                0, 0, 
                $imageWidth, $imageHeight
            );
            imagedestroy($itemImage);
            $this->meta[] = [
                'path' => $item['path'],
                'positionX' => $cursorX,
                'positionY' => 0
            ];
            $cursorX += $imageWidth;
        }
        imagepng($sprite, $this->_getSpriteFilename());
        imagedestroy($sprite);
        $this->writeMeta();
    }
    
    /**
     * Рассчитывает максимальную высоту спрайта
     * 
     * @return integer
     */
    public function calculateSpriteHeight($images)
    {
        $heights = [];
        foreach ($images as $image) {
            $heights[] = imagesy($image);
        }
        return max($heights);
    }
    
    /**
     * Рассчитывает максимальную ширину спрайта
     * 
     * @return integer
     */
    public function calculateSpriteWidth($images)
    {
        $widths = [];
        foreach ($images as $image) {
            $widths[] = imagesx($image);
        }
        return array_sum($widths);
    }
    
    public function getImage($imageUrl)
    {
        static $types = array(
            IMAGETYPE_GIF   => 'gif',
            IMAGETYPE_JPEG  => 'jpeg',
            IMAGETYPE_PNG   => 'png'
        );
        $type = exif_imagetype($imageUrl);
        $currentResource = call_user_func(
            'imagecreatefrom' . $types[$type], $imageUrl
        );
        return $currentResource;
    }
    
    /**
     * Записывает метаданные в файл
     */
    protected function writeMeta()
    {
        $filename = $this->_getMetaFilename();
        $json = json_encode($this->meta);
        file_put_contents($filename, $json);
    }
    
    /**
     * Возвращает путь до файла с метаданными
     * 
     * @return string
     */
    protected function _getMetaFilename()
    {
        return IcEngine::root() . $this->config['path'] . '/' . $this->name . '.json';
    }
    
    /**
     * Возвращает путь до файла со спрайтом
     * 
     * @return string
     */
    protected function _getSpriteFilename()
    {
        return IcEngine::root() . $this->config['path'] . '/' . $this->name . '.png';
    }
    
    /**
     * Возвращает путь до файла со спрайтом
     * 
     * @return string
     */
    public function getSpritePath()
    {
        return '/' . $this->config['path'] . '/' . $this->name . '.png';
    }
    
    /**
     * Возвращает метаданные спрайта
     * 
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }
    
    /**
     * Загружает метаданные спрайта из файла
     * 
     */
    public function loadMeta()
    {
        $filename = $this->_getMetaFilename();
        if (!file_exists($filename)) {
            $this->meta = [];
            return;
        }
        $json = file_get_contents($filename);
        $meta = json_decode($json, true);
        if (!$meta) {
            $this->meta = [];
            return;
        }
        $this->meta = $meta;
    }
}
