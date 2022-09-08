<?php

namespace Volochaev\ImageGeneration\Figures;

abstract class Figure
{
	public $type;
	public $x;
	public $y;

	public function __construct($x, $y)
	{
		$this->x = $x;
		$this->y = $y;
	}

	public abstract function render($image);

	public abstract function rotate($deg);
}
