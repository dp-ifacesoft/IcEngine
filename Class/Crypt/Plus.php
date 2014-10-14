<?php

/**
 * Элементарный алгоритм "шифрования":)
 *
 * @author Apostle
 */
class Crypt_Plus extends Crypt_Abstract
{
    /**
     * @inheritdoc
     */
    public function encode($input, $key = null)
    {
        if ($this->isEmail($input)) {
            return $this->encodeEmail($input);
        }
        $words = preg_split('#\s+#', $input);
        return "'" . implode("'+'", $words) . "'";
    }
    
    /**
     * @inheritdoc
     */
    public function decode($input, $key = null)
    {
        $search = ["'","+"];
        $input = str_replace($search, '', $input);
        return $input;
    }
    
    /**
     * Является ли e-mail'ом
     * @param string $input
     * @return boolean
     */
    public function isEmail($input)
    {
        return preg_match('#(?:mailto:)?[a-z0-9]+@[a-z0-9]+\\.[a-z0-9]+#', $input);
    }
    
    /**
     * Зашифоровать email
     * @param string $input исходный текст
     * @return string Зашифорованный email
     */
    public function encodeEmail($input)
    {
        $input = str_replace('@', "'+'@'+'", $input);
        $input = str_replace('mailto:', "'mail'+'to:'+'", $input);
        $input = str_replace('.ru', "'+'.ru'", $input);
        return $input;
    }
}