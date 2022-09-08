<?php

namespace Volochaev\ImageGeneration\Figures;

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
		$color = imagecolorallocate($image, $this->color[0], $this->color[1], $this->color[2]);
		if ($this->filled) {
			imagefilledpolygon($image, $this->vertices, count($this->vertices) / 2, $color);
			return;
		}
		imagepolygon($image, $this->vertices, count($this->vertices) / 2, $color);
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

	public static function constructInArea($x, $y, $width, $height, $filled, $color)
	{
		$vertices = [];
		for ($i = 1; $i <= 6; $i++) {
			if ($i % 2 > 0) {
				$vertices[] = rand($x, $x + $width);
			} else {
				$vertices[] = rand($y, $y + $height);
			}
		}

		return new Triangle($vertices, $filled, $color);
	}
}
