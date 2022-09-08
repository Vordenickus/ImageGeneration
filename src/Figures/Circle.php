<?php

namespace Volochaev\ImageGeneration\Figures;

use Volochaev\ImageGeneration\Helpers\HexToRGB;

class Circle extends Figure
{
	public $type = 'circle';
	public $color;
	public $filled;
	public $diameter;

	public function __construct($x, $y, $diameter, $color, $filled = true) {
		$this->x = $x;
		$this->y = $y;
		$this->diameter = $diameter;
		$this->filled = $filled;
		$this->color = is_string($color) ? HexToRGB::translate($color) : $color;
	}

	public function render($image) {
		$color = imagecolorallocate($image, $this->color[0], $this->color[1], $this->color[2]);
		if ($this->filled) {
			imagefilledellipse(
				$image,
				$this->x,
				$this->y,
				$this->diameter,
				$this->diameter,
				$color
			);
			return;
		}
		imageellipse(
			$image,
			$this->x,
			$this->y,
			$this->diameter,
			$this->diameter,
			$color
		);
	}

	public function rotate($deg) {
		// Круг покрутился
		return;
	}
}