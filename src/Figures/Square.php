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
	public $deg = 0;

	public function __construct($x, $y, $width, $color, $deg = 0)
	{
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $width;
		$this->color = $color;
	}
}
