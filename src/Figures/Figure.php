<?php

namespace Volochaev\ImageGeneration\Figures;

abstract class Figure
{
	public $type;
	public $x;
	public $y;

	public function __construct($type, $x, $y)
	{
		$this->type = $type;
		$this->$x = $x;
		$this->y = $y;
	}
}