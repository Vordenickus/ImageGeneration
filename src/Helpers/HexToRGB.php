<?php

namespace Volochaev\ImageGeneration\Helpers;

class HexToRGB
{
	public static function translate($hex) {
		if (strlen($hex) === 4) {
			$hex = static::formatHex($hex);
		} else if(\strlen($hex) != 7) {
			#TODO: Exception
			return;
		}
		list($r, $g, $b) = \sscanf($hex, "#%02x%02x%02x");
		return [$r, $g, $b];
	}

	private static function formatHex($hex) {
		$newHex = '#';
		for ($i = 1; $i < strlen($hex); $i++) {
			$newHex .= $hex[$i] . $hex[$i];
		}
		return $newHex;
	}
}
