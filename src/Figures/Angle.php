<?php

namespace Volochaev\ImageGeneration\Figures;

use Volochaev\ImageGeneration\Helpers\HexToRGB;

class Angle extends Figure
{
	protected $width;
	protected $height;
	protected $vertices = [];
	protected $triangle = [];
	protected $color;
	protected $triangleLength;

	public function __construct($x, $y, $width, $height, $color)
	{
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $height;
		$this->color = is_string($color) ? HexToRGB::translate($color) : $color;
		$this->triangleLength = $width / 3;
		$this->triangle = $this->createTriangle();
		$this->vertices = [
			[$x, $y],
			[$x + $width, $y],
			[$x, $y + $height]
		];
	}


	public function createTriangle()
	{
		$margin = 10;
		// Вершины треугольника
		$vertices = [
			$this->x + $margin, $this->y + $margin,
			$this->x + $margin + $this->triangleLength, $this->y + $margin,
			$this->x + $margin, $this->y + $margin + $this->triangleLength
		];
		return $vertices;
	}


	public function render($image)
	{
		$color = imagecolorallocate($image, $this->color[0], $this->color[1], $this->color[2]);
		imageline(
			$image,
			$this->vertices[0][0],
			$this->vertices[0][1],
			$this->vertices[1][0],
			$this->vertices[1][1],
			$color,
		);
		imageline(
			$image,
			$this->vertices[0][0],
			$this->vertices[0][1],
			$this->vertices[2][0],
			$this->vertices[2][1],
			$color,
		);
		imagefilledpolygon(
			$image,
			$this->triangle,
			count($this->triangle) / 2,
			$color
		);
	}


	public function rotate($deg)
	{
		$newVertices = [];
		//$area = $this->width * $this->height;

		$vertex = [];

		$sin = sin(deg2rad($deg));
		$cos = cos(deg2rad($deg));
		$this->triangle = $this->rotateSubTriangle($this->triangle, $sin, $cos, $this->width);

		foreach ($this->vertices as $vertex) {
			$x = $vertex[0];
			$y = $vertex[1];

			$newVertices[] = [
			(($x * $cos - $y * $sin) + $this->width), //x
				(($x * $sin + $y * $cos) + $this->width) //y
			];
		}

		$this->vertices = $newVertices;
	}


	protected function rotateSubTriangle($vertices, $sin, $cos, $area)
	{
		$newVertices = [];
		$bufferX = 0;
		for ($i = 0, $limit = count($vertices); $i < $limit; $i++) {
			if ($i % 2 === 0) {
				$bufferX = $vertices[$i];
				continue;
			}
			$x = $bufferX;
			$y = $vertices[$i];
			$newVertices[] = ($x * $cos - $y * $sin) + $area;
			$newVertices[] = ($x * $sin + $y * $cos) + $area;
		}

		return $newVertices;
	}
}
