<?php

namespace Volochaev\ImageGeneration\Figures;

use Volochaev\ImageGeneration\Helpers\HexToRGB;

class RightTriangle extends Figure
{
	public $type = 'triangle';
	public $length;
	public $color;
	public $filled = true;
	public $vertices;


	public function __construct($x, $y, $length, $color, $filled)
	{
		$this->x = $x;
		$this->y = $y;
		$this->length = $length;
		$this->color = is_string($color) ? HexToRGB::translate($color) : $color;
		$this->vertices = [
			$this->x, $this->y,
			$this->x + $this->length, $this->y,
			$this->x + $this->length / 2, $this->y + $this->length,
		];
		$this->filled = $filled;
	}


	public function render($image)
	{
		$color = imagecolorallocate($image, $this->color[0], $this->color[1], $this->color[2]);

		if ($this->filled) {
			imagefilledpolygon($image, $this->vertices, 3 ,$color);
			return;
		}
		imagepolygon($image, $this->vertices, 3, $color);
	}


	public function rotate($deg) {
		$factor = $this->length;

		$vertices = [
			[$this->vertices[0], $this->vertices[1]],
			[$this->vertices[2], $this->vertices[3]],
			[$this->vertices[4], $this->vertices[5]]
		];

		$newVertices = [];

		$sin = sin(deg2rad($deg));
		$cos = cos(deg2rad($deg));

		$newVertices = [];

		foreach ($vertices as $vertex) {
			$x = $vertex[0];
			$y = $vertex[1];
			$newVertices[] = ($x * $cos - $y * $sin) + $factor * 6;
			$newVertices[] = ($x * $sin + $y * $cos) - $factor * 6; 
		}

		$this->vertices = $newVertices;
	}
}
