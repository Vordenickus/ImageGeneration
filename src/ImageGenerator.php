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

	protected $amount = 30;
	protected $test;

	public function __construct()
	{
		$this->createDir();
		$this->clear();
		for ($i = 0; $i < $this->amount / 3; $i++) {
			$image = new Image(512, 512, $this->getRandomColor());
			$image->addFigures();
			$image->drawFigures();
			$image->addPivots();
			$image->generateAndSave();
		}
		for ($i = 0; $i < $this->amount / 3; $i++) {
			$image = new Image(768, 768, $this->getRandomColor());
			$image->addFigures();
			$image->drawFigures();
			$image->addPivots();
			$image->generateAndSave();
		}
		for ($i = 0; $i < $this->amount / 3; $i++) {
			$image = new Image(1024, 1024, $this->getRandomColor());
			$image->addFigures();
			$image->drawFigures();
			$image->addPivots();
			$image->generateAndSave();
		}
	}

	private function getRandomColor()
	{
		$colors = static::COLORS;
		$colorIndex = mt_rand(0,count($colors) - 1);
		return $colors[$colorIndex];
	}

	private function clear() {
		$dir = __DIR__ . "/../dataset/";
		$objects = scandir(__DIR__ . "/../dataset/");
		foreach($objects as $object) {
			if ($object != '.' && $object !== '..' && $object !== '.gitignore') {
				unlink($dir . $object);
			}
		}
	}

	private function createDir() {
		if (is_dir(__DIR__ . "/../dataset")) {
			return;
		}
		mkdir(__DIR__ . '/../dataset');
	}
}
