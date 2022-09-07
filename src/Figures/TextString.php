<?php

namespace Volochaev\ImageGeneration\Figures;

use Volochaev\ImageGeneration\Helpers\HexToRGB;

class TextString extends Figure
{
	private $chars = ['а','б','в','г','д','е','ж','з','к', ' '];
	public $font;
	public $string;

	public function __construct($x, $y, $color)
	{
		$this->x = $x;
		$this->y = $y;
		$this->string = $this->generateRandomString();
		$this->color = is_string($color) ? HexToRGB::translate($color) : $color;
	}

	public function render($image)
	{
		$color = imagecolorallocate($image, $this->color[0], $this->color[1], $this->color[2]);
		imagestring($image, 5, $this->x, $this->y, $this->string, $color);
	}

	private function generateRandomString()
	{
		$length = mt_rand(5, 20);
		$string = '';
		for ($i = 0; $i < $length; $i++) {
			$charIndex = mt_rand(0, count($this->chars));
			$string .= $this->chars[$charIndex];
		}
		return $string;
	}
}