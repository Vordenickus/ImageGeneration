<?php

namespace Volochaev\ImageGeneration;

use Volochaev\ImageGeneration\Config\ConfigLoader;
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
		'qr_mark',
		'qr'
	];

	protected $backgrounds;
	protected $amountOfImages;
	protected $amountOfQr;
	protected $amountOfFigures;
	protected $cleadDir;


	public function __construct()
	{
		$this->backgrounds = $this->loadBackgrounds();
		$this->amountOfQr = ConfigLoader::cfg('AMOUNT_OF_QR') ?? 1;
		$this->amountOfFigures = ConfigLoader::cfg('AMOUNT_OF_FIGURES') ?? -1;
		$this->amountOfImages = ConfigLoader::cfg('AMOUNT_OF_IMAGES') ?? 1;
		$this->cleadDir = ConfigLoader::cfg("CLEAR_DIR") !== '' ? (bool) ConfigLoader::cfg("CLEAR_DIR") : false;
		$this->verbose = ConfigLoader::cfg("VERBOSE");
	}


	public function generateDataset()
	{
		$this->createDir();
		if ($this->cleadDir) {
			if ($this->verbose) print('Удаление старого датасета' . PHP_EOL);
			$this->clear();
		}
		$amount = $this->amountOfImages;
		$this->generateLabelMap();
		$image = null;
		if ($this->verbose) print("Начало генерации датасета размером в $amount изображений" . PHP_EOL);
		$now = microtime(true);
		$prediction = 0;
		for ($i = 0; $i < $amount; $i++) {
			$image = null;
			if ($i < $amount / 3) {
				$image = new Image(416, 768, $this->getRandomColor(), $this->getBackground());
			} elseif ($i < $amount / 3 * 2) {
				$image = new Image(624, 1152, $this->getRandomColor(), $this->getBackground());
			} else {
				$image = new Image(832, 1536, $this->getRandomColor(), $this->getBackground());
			}
			$image->addFigures($this->amountOfFigures);
			$image->drawFigures();
			$image->addPivots();
			$image->addQr($this->amountOfQr);
			$image->generateAndSave();
			if ($this->verbose) {
				if ($i == round($amount / 4)) {
					$elapsed = round(microtime(true) - $now, 2);
					$prediction = round($elapsed * 3, 2);
					print("25%, прошло $elapsed с, осталось ~ $prediction с" . PHP_EOL);
				} else if ($i == round($amount / 2)) {
					$elapsed = round(microtime(true) - $now, 2);
					$prediction = $elapsed;
					print("50%, прошло $elapsed с, осталось ~ $prediction с" . PHP_EOL);
				} elseif ($i == round($amount / 4 * 3)) {
					$elapsed = round(microtime(true) - $now, 2);
					$prediction = round($elapsed / 3, 2);

					print("75%, прошло $elapsed с, осталось ~ $prediction с" . PHP_EOL);
				}
			}
		}
		$elapsed = round(microtime(true) - $now, 2);
		if($this->verbose) print("Генерация завершена за $elapsed s" . PHP_EOL);
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
		if($this->verbose) print('Нарушена файловая структура. Восстановление' . PHP_EOL);
		return mkdir(__DIR__ . '/../dataset');
	}


	private function checkBackgroundDir()
	{
		if (is_dir(__DIR__ . '/../backgrounds/')) {
			return true;
		}
		return mkdir(__DIR__ . '/../backgrounds/');
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
