<?php

namespace Volochaev\ImageGeneration;

use Volochaev\ImageGeneration\Figures\Square;

class Image
{
	public $width;
	public $height;
	protected $background;
	protected $image;
	protected $figures = [];
	public $rotateChance = 0;

	public function __construct($width, $height, $background)
	{
		$this->width = $width;
		$this->height = $height;
		$this->background = $background;
		$this->image = imagecreate($this->width, $this->height);
		$background = imagecolorallocate($this->image, $background[0], $background[1], $background[2]);
		$this->addFigure('f');
		$this->drawFigures();
	}

	public function addFigure($figure)
	{
		for ($i = 0; $i < 20; $i++) {
			$this->figures[] = Square::getRandomSquare(50, 512, 512, [0,0,0]);
		}
	}


	public function drawFigures()
	{
		foreach ($this->figures as $figure) {
			$color = \imagecolorallocate($this->image, $figure->color[0], $figure->color[1], $figure->color[2]);
			if ($figure->type === 'square') {
				imagefilledrectangle(
					$this->image,
					$figure->x,
					$figure->y,
					$figure->x + $figure->width,
					$figure->y + $figure->height,
					$color
				);
			}
			$this->rotate();
		}
	}

	public function random() {
		return \rand(0, 512 - 20);
	}

	public function rotate()
	{
		if ($this->rotateChance > 0 && rand(0, 100) <= $this->rotateChance) {
			$deg = rand(0, 360);
			$white = \imagecolorallocate($this->image, 255, 255, 255);
			$this->image = \imagerotate($this->image, $deg, $white);
		}
	}


	public function generateAndSave()
	{
		$dir = __DIR__ . '/../dataset/';

		$binaryStream = fopen('php://memory', 'r+');
		try {
			\imagepng($this->image, $binaryStream);
			\rewind($binaryStream);
			$stringData = \stream_get_contents($binaryStream);
			$name = $dir . $this->width . 'x' . $this->height . hash('sha256', $stringData) . '.png';
			\imagepng($this->image, $name);
		} finally {
			\fclose($binaryStream);
		}
	}
}
