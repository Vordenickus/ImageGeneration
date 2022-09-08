<?php

namespace Volochaev\ImageGeneration\Figures;


class Poligon extends Figure
{
	protected $color;
	protected $filled;
	protected $vertices;

	public function __construct($vertices, $filled, $color)
	{
		$this->x = $vertices[0];
		$this->y = $vertices[1];
		$this->vertices = $vertices;
		$this->filled = $filled;
		$this->color = $color;
	}

	public function render($image)
	{
		if ($this->filled) {
			imagefilledpolygon($image, $this->vertices, count($this->vertices), $this->color);
			return;
		}
		imagepolygon($image, $this->vertices, count($this->vertices) / 2, $this->color);
	}

	public function rotate($deg)
	{
		$newVertices = [];
		$vertex = [];

		$cos = cos(deg2rad($deg));
		$sin = sin(deg2rad($deg));

		for ($i = 0, $limit = count($this->vertices); $i <= $limit; $i++) {
			if ($i % 2 === 0 || $i === 0) {
				$vertex['x'] = $this->vertices[$i];
				continue;
			}
			$vertex['y'] = $this->vertices[$i];

			$x = $vertex['x'];
			$y = $vertex['y'];

			$newVertices[] = ($x * $cos - $y * $sin);
			$newVertices[] = ($x * $sin - $y * $cos);
		}

		$this->vertices = $newVertices;
	}

	public function getSquare()
	{
		$vertices = $this->reformatVertices($this->vertices);
		$amountOfVertites = count($vertices);
		$sum = 0;
		$firstVertex = [$vertices[0], $vertices[1]];
		$lastVertex = [$vertices[$amountOfVertites - 1], $vertices[$amountOfVertites - 2]];
		$oldVertex = [];
		for ($i = 2, $limit = $amountOfVertites - 2; $i < $limit; $i++) {
			if ($i % 2 === 0) {
				$oldVertex = $vertices[$i];
				continue;
			}
			$vertex = $vertices[$i];
			$sum += ($oldVertex[0] * $vertex[1]) - ($oldVertex[1] * $vertex[0]);
		}
		$sum += ($lastVertex[0] * $firstVertex[1]) - ($lastVertex[1] * $firstVertex[0]);
		return $sum / 2;
	}


	private function reformatVertices($vertices)
	{
		$vertex = [];
		for ($i = 0, $limit = count($vertices); $i < $limit; $i++) {
			if ($i % 2 === 0 || $i === 0) {
				$vertex = [];
				$vertex[0] = $this->vertices[$i];
				continue;
			}
			$vertex[1] = $this->vertices[$i];
			$vertices[] = $vertex;
		}
		return $vertex;
	}
}
