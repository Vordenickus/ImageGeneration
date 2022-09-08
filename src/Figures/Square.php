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
	private $vertices;


	public function __construct($x, $y, $width, $color, $filled = true)
	{
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $width;
		$this->filled = $filled;
		$this->vertices = [
			$this->x, $this->y,
			$this->x + $this->width, $this->y,
			$this->x + $this->width, $this->y + $this->height,
			$this->x, $this->y + $this->height,
		];
		$this->color = is_string($color) ? HexToRGB::translate($color) : $color;
	}


	public function render($image) {
		$color = imagecolorallocate($image, $this->color[0], $this->color[1], $this->color[2]);
		if ($this->filled) {
			imagefilledpolygon($image, $this->vertices, 4, $color);
		} else {
			imagepolygon($image, $this->vertices, 4, $color);
		}
	}


	public function rotate($deg)
	{
		$factor = $this->width;
		$vertices = [
			[$this->vertices[0], $this->vertices[1]],
			[$this->vertices[2], $this->vertices[3]],
			[$this->vertices[4], $this->vertices[5]],
			[$this->vertices[6], $this->vertices[7]],
		];
		$newVertices = [];
		$sin = sin(deg2rad($deg));
		$cos = cos(deg2rad($deg));


		foreach ($vertices as $vertex) {
			$x = $vertex[0];
			$y = $vertex[1];
			$newVertices[] = ($x * $cos - $y * $sin) + $factor * 4;
			$newVertices[] = ($x * $sin + $y * $cos) - $factor * 4;
		}

		$this->vertices = $newVertices;
	}
}
