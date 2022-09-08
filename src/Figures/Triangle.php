<?php

use Volochaev\ImageGeneration\Figures\Figure;
use Volochaev\ImageGeneration\Helpers\HexToRGB;

class Triangle extends Figure
{
	private $vertices = [];

	public function __construct($verticies, $filled, $color)
	{
		$this->x = $verticies[0];
		$this->y = $verticies[1];
		$this->vertices = $verticies;
		$this->filled = $filled;
		$this->color = is_string($color) ? HexToRGB::translate($color) : $color;
	}

	public function render($image)
	{
		if ($this->filled) {
			imagefilledpolygon($image, $this->vertices, count($this->vertices) / 2, $this->color);
			return;
		}
		imagepolygon($image, $this->vertices, count($this->vertices) / 2, $this->color);
	}

	public function rotate($deg)
	{
		$vertices = [
			[$this->vertices[0], $this->vertices[1]],
			[$this->vertices[2], $this->verices[3]],
			[$this->vertices[4], $this->vertices[5]]
		];

		$factor = $this->getSquare($vertices);

		$sin = sin(deg2rad($deg));
		$cos = cos(deg2rad($deg));

		$newVerticies = [];
		foreach ($vertices as $vertex) {
			$x = $vertex[0];
			$y = $vertex[1];
			$newVerticies[] = ($x * $cos - $y * $sin) + $factor;
			$newVerticies[] = ($x * $sin + $y * $cos) - $factor;
		}

		$this->vertices = $newVerticies;
	}

	public function getSquare($verticies)
	{
		$vertexA = $verticies[0];
		$vertexB = $verticies[1];
		$vertexC = $verticies[2];
		return 
		((($vertexA[0] * ($vertexB[1] - $vertexC[1])) +
		 ($vertexB[0] * ($vertexC[1] - $vertexA[1])) +
		 ( $vertexC[0] * ($vertexA[1] - $vertexB[1]))
		) / 2); 
	}
}
