<?php

namespace Volochaev\ImageGeneration\Figures;

use Volochaev\ImageGeneration\Helpers\HexToRGB;

class Square extends Figure
{
	public $type = 'square';
	
	public $width;
	public $height;
	public $color;
	public $filled;

	public function __construct($x, $y, $width, $color, $filled = true)
	{
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $width;
		$this->filled = $filled;
		$this->color = is_string($color) ? HexToRGB::translate($color) : $color;
	}

	public function render($image) {
		$color = \imagecolorallocate($image, $this->color[0], $this->color[1], $this->color[2]);
		if ($this->filled) {
			imagefilledrectangle(
				$image,
				$this->x,
				$this->y,
				$this->x + $this->width,
				$this->y + $this->width,
				$color
			);
		} else {
			imagerectangle(
				$image,
				$this->x,
				$this->y,
				$this->x + $this->width,
				$this->y + $this->width,
				$color
			);
		}
	}
}
