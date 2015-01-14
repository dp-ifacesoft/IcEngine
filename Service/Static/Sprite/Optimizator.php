<?php

/**
 * Сервис для создания спрайта по файлу стилей css
 *
 * @author markov
 * @Service("serviceStaticSpriteOptimizator")
 */
class Service_Static_Sprite_Optimizator extends Service_Abstract
{
    /**
     * Запускает 
     */
    public function run($cssFile)
    {
        $cssText = file_get_contents($cssFile);
        $images = App::serviceStaticCssParser()->parseImages($cssText);
        $imagesExist = [];
        foreach ($images as $imageUrl) {
            $absoluteImageUrl = rtrim(IcEngine::root(), '/') . $imageUrl;
            if (!file_exists($absoluteImageUrl)) {
                continue;
            }
            $imagesExist[] = $imageUrl;
        }
        $spriteName = md5($cssFile);
        $sprite = App::staticSpriteManager()->get($spriteName);
        $sprite->update($imagesExist);
        $meta = $sprite->getMeta();
        $cssTextReplaced = $cssText;
        foreach ($meta as $item) {
            $regexp = '#sprite:.*?url\(.*?' . $item['path'] . '.*?\)#';
            $replacement = 'background: url(' . $sprite->getSpritePath() 
                . ') no-repeat -' . $item['positionX'] . 'px -' . 
                $item['positionY'] . 'px transparent';
            $cssTextReplaced = preg_replace($regexp, $replacement, $cssTextReplaced);
        }
        file_put_contents($cssFile, $cssTextReplaced);
    }
}
