<?php

namespace Volochaev\ImageGeneration\Figures;

class Square extends Figure
{
	public $x2;
	public $y2;
	public $type = 'square';
	
	public $width;
	public $height;
	public $color;

	public function __construct($x, $y, $width, $color)
	{
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $width;
		$this->color = $color;
	}

	public static function getRandomSquare($width, $xLimit, $yLimit, $color)
	{
		$xLimit -= $width;
		$yLimit -= $width;

		$x = rand(0, $xLimit);
		$y = rand(0, $yLimit);

		return new Square($x, $y, $width, $color);
	}
}
