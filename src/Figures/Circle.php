<?php

namespace Volochaev\ImageGeneration\Figures;

class Circle extends Figure
{
	public $type = 'circle';

	public function __constructor($x, $y) {
		$this->x = $x;
		$this->y = $y;
	}

	public function render($image) {

	}
}