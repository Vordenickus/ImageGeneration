<?php


namespace Volochaev\ImageGeneration\Helpers;


class LoadImage
{
	public static function loadImage($image)
	{
		switch (static::getExt($image)) {
			case 'png':
				return imagecreatefrompng($image);
			case 'jpeg':
			case 'jpg':
				return imagecreatefromjpeg($image);
			case 'giff':
			case 'gif':
				return imagecreatefromgif($image);
			default:
				return false;
		}
	}


	private static function getExt($image)
	{
		$image = explode('.', $image);
		if (count($image) > 1) {
			return $image[count($image) - 1];
		}
		return '';
	}
}
