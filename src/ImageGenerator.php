<?php

namespace Volochaev\ImageGeneration;

use Volochaev\ImageGeneration\Image;
class ImageGenerator
{
	protected const COLORS = [
		'#FFFFFF',
		'#808080',
		'#a9a9a9',
		'#838383',
		'#fafafa',
		'#b6b6b6',
		'#FFFF00',
		'#F6BE00',
		'#FBDB65',
		'#FBDB65',
		'#8b0000',
	];

	protected const LABELS = [
		'upper_left',
		'upper_right',
		'bottom_left',
		'bottom_right',
	];

	protected $backgrounds;


	public function __construct()
	{
		$this->backgrounds = $this->loadBackgrounds();
	}


	public function generateDataset($amount, $cleadDir = true)
	{
		$this->createDir();
		if ($cleadDir) {
			$this->clear();
		}
		$this->generateLabelMap();
		$image = null;
		for ($i = 0; $i < $amount; $i++) {
			$image = null;
			if ($i < $amount / 3) {
				$image = new Image(512, 512, $this->getRandomColor(), $this->getBackground());
			} elseif ($i < $amount / 3 * 2) {
				$image = new Image(768, 768, $this->getRandomColor(), $this->getBackground());
			} else {
				$image = new Image(1024, 1024, $this->getRandomColor(), $this->getBackground());
			}
			$image->addFigures();
			$image->drawFigures();
			$image->addPivots();
			$image->addQr(1);
			$image->generateAndSave();
		}
	}


	private function getRandomColor()
	{
		$colors = static::COLORS;
		$colorIndex = rand(0, count($colors) - 1);
		return $colors[$colorIndex];
	}

	private function getBackground()
	{
		$background = '';
		$backgroundsSize = count($this->backgrounds);
		if ($backgroundsSize) {
			$randomIndex = rand(0, $backgroundsSize - 1);
			return $this->backgrounds[$randomIndex];
		}
		return $background;
	}


	private function generateLabelMap()
	{
		$path = __DIR__ . '/../dataset/_darknet.labels';
		$stream = fopen($path, 'w+');
		try {
			foreach (static::LABELS as $label) {
				fwrite($stream, $label . PHP_EOL);
			}
		} finally {
			fclose($stream);
		}
	}


	private function clear()
	{
		$dir = __DIR__ . "/../dataset/";
		$objects = scandir(__DIR__ . "/../dataset/");
		foreach($objects as $object) {
			if ($object != '.' && $object !== '..' && $object !== '.gitignore') {
				unlink($dir . $object);
			}
		}
	}


	private function createDir()
	{
		if (is_dir(__DIR__ . "/../dataset")) {
			return true;
		}
		return mkdir(__DIR__ . '/../dataset');
	}


	private function checkBackgroundDir()
	{
		if (is_dir(__DIR__ . '/../backgrounds/')) {
			return true;
		}
		return mkdir(__DIR__ . '/../dataset');
	}

	private function loadBackgrounds()
	{
		$this->checkBackgroundDir();
		$path = __DIR__ . '/../backgrounds/';
		$files = scandir($path);
		$objects = [];
		foreach ($files as $file) {
			$ext = explode('.', $file);
			if (count($ext) > 1) {
				$ext = $ext[count($ext) - 1];
				$regex = '/(png|jpg|gif|jpeg)/';
				if (preg_match($regex, $file)) {
					$objects[] = $path . $file;
				}
			}
		}
		return $objects;
	}
}
