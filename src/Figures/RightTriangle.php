<?php

namespace Volochaev\ImageGeneration\Figures;

use Volochaev\ImageGeneration\Helpers\HexToRGB;

class RightTriangle extends Figure
{
	public $type = 'triangle';

	public $length;
	public $color;
	public $filled = true;

	public function __construct($x, $y, $length, $color, $filled)
	{
		$this->x = $x;
		$this->y = $y;
		$this->length = $length;
		$this->color = is_string($color) ? HexToRGB::translate($color) : $color;
		$this->filled = $filled;
	}

	public function render($image)
	{
		$color = \imagecolorallocate($image, $this->color[0], $this->color[1], $this->color[2]);

		$points = [
			$this->x, $this->y,
			$this->x + $this->length, $this->y,
			$this->x + $this->length / 2, $this->y + $this->length,
		];

		if ($this->filled) {
			imagefilledpolygon($image,$points, count($points) / 2,$color);
			return;
		}
		imagepolygon($image, $points, count($points) / 2, $color);
	}
}