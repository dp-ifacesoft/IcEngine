<?php
/**
 *
 * @desc Модификатор смарти для подмены ссылок на короткие.
 * @author Yury Shvedov
 * @param string $link Полная ссылка, куда будет осуществляться редирект.
 * @return string Короткая ссылка на редиректор.
 *
 */
function smarty_modifier_tiny_link ($link)
{
	return Tiny_Link::byLink ($link)->shortLink ();
}
