<?php

/**
 * Менеджер спрайтов
 *
 * @author markov
 * @Service("staticSpriteManager")
 */
class Static_Sprite_Manager
{
    /**
     * Возвращает спрайт
     * 
     * @param string $spriteName название спрайта
     * @return Static_Sprite
     */
    public function get($spriteName)
    {
        return new Static_Sprite($spriteName);
    }
}
