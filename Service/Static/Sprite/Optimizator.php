<?php

/**
 * Сервис для создания спрайта по файлу стилей css
 *
 * @author markov
 * @Service("serviceStaticSpriteOptimizator")
 */
class Service_Static_Sprite_Optimizator extends Service_Abstract
{
    protected $config = [
        'commonCssPath' => '/cache/css.css'
    ];
    
    /**
     * Возвращает общий спрайт
     */
    public function getCommon()
    {
        $config = $this->config();
        $commonSpriteName = md5(rtrim(IcEngine::root(), '/') . $config->commonCssPath);
        $commonSprite = App::staticSpriteManager()->get($commonSpriteName);
        return $commonSprite;
    }
    
    /**
     * Запускает 
     */
    public function run($cssFile)
    {
        return true;
        $cssText = file_get_contents($cssFile);
        $images = App::serviceStaticCssParser()->parseImages($cssText);
        $commonSprite = $this->getCommon();
        $commonSpriteMeta = $commonSprite->getMeta();
        $commonImages = [];
        foreach ($commonSpriteMeta as $item) {
            $commonImages[] = $item['path'];
        }
        $imagesExist = [];
        foreach ($images as $imageUrl) {
            $absoluteImageUrl = rtrim(IcEngine::root(), '/') . $imageUrl;
            if (!file_exists($absoluteImageUrl)) {
                continue;
            }
            if (in_array($imageUrl, $commonImages)) {
                continue;
            }
            $imagesExist[] = $imageUrl;
        }
        $spriteName = md5($cssFile);
        $sprite = App::staticSpriteManager()->get($spriteName);
        $sprite->update($imagesExist);
        $cssTextReplaced = $this->_cssReplace($sprite, $cssText);
        $cssTextReplacedCommon = $this->_cssReplace($commonSprite, $cssTextReplaced);
        file_put_contents($cssFile, $cssTextReplacedCommon);
    }
    
    /**
     * Заменяет в тексте на спрайт
     * 
     * @param Static_Sprite $sprite
     * @param string $cssText
     * @return string
     */
    protected function _cssReplace($sprite, $cssText)
    {
        foreach ($sprite->getMeta() as $item) {
            $regexp = '#sprite:.*?url\(.*?' . $item['path'] . '.*?\)#';
            $replacement = 'background: url(' . $sprite->getSpritePath() 
                . ') no-repeat -' . $item['positionX'] . 'px -' . 
                $item['positionY'] . 'px transparent';
            $cssText = preg_replace($regexp, $replacement, $cssText);
        }
        return $cssText;
    }
}
