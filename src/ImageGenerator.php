<?php

namespace Volochaev\ImageGeneration;

use Volochaev\ImageGeneration\Image;
class ImageGenerator 
{

	protected const COLORS = [
		'#FFFFFF',
		'#808080',
		'#a9a9a9',
		'#838383',
		'#fafafa',
		'#b6b6b6',
		'#FFFF00',
		'#F6BE00',
		'#FBDB65',
		'#FBDB65',
		'#8b0000',
	];


	public function __construct()
	{
		$now = microtime(true);
		for ($i = 0; $i < 10; $i++) {
			$image = new Image(512, 512, $this->getRandomColor());
			$image->generateAndSave();
		}
		for ($i = 0; $i < 10; $i++) {
			$image = new Image(768, 768, $this->getRandomColor());
			$image->generateAndSave();
		}
	}

	private function getRandomColor()
	{
		$colors = static::COLORS;
		$colorIndex = mt_rand(0,count($colors) - 1);
		return $colors[$colorIndex];
	}
}
