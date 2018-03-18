<?php
/**
 * Script generating Persian CAPTCHAs
 *
 * @author  Jose Rodriguez <jose.rodriguez@exec.cl>
 * @author  Desuneko (added Imagick support)
 * @author  Nima HeydariNasab (added Persian support)
 * @license GPLv3
 * @package captcha
 * @version 0.5
 *
 */

/**
 * SimpleCaptcha class
 *
 */

require 'FarsiGD.php';
require 'RandomColor.php';

session_start();

$captcha = new SimpleCaptcha();

$captcha->CreateImage();

class SimpleCaptcha {

	/** 
     * Difficulty level (if used, keep 'Wave configuracion' on default)
     * normal = 1 
     * closer to 0 is more easy, for ex: 0.4
     * closer to 2 is more hard, for ex: 1.8
     * smaller then 0 and bigger than 2 is caped to min or max
     */
    public $difficulty = 2;

	/** Width of the image */
	public $width  = 200;

	/** Height of the image */
	public $height = 70;

	/** Dictionary word file (empty for random text) */
	public $wordsFile = 'words/fa.txt';

	/**
	 * Path for resource files (fonts, words, etc.)
	 *
	 * "resources" by default. For security reasons, is better move this
	 * directory to another location outise the web server
	 *
	 */
	public $resourcesPath = 'resources';

	/** Min word length (for non-dictionary random text generation) */
	public $minWordLength = 3;

	/**
	 * Max word length (for non-dictionary random text generation)
	 * 
	 * Used for dictionary words indicating the word-length
	 * for font-size modification purposes
	 */
	public $maxWordLength = 6;

	/** Sessionname to store the original text */
	public $session_var = 'captcha';

	/** Background color in RGB-array */
	public $backgroundColor = array(255, 255, 255);

	/** Foreground colors in RGB-array */
	public $colors;

	/** Shadow color in RGB-array or null */
	public $shadowColor = null; //array(0, 0, 0);

	/** Horizontal line through the text */
	public $lineWidth = 0;

	/**
	 * Font configuration
	 *
	 * - font: TTF file
	 * - spacing: relative pixel space between character
	 * - minSize: min font size
	 * - maxSize: max font size
	 */
	public $fonts = array(
		'IranSANS' => array('minSize' => 18, 'maxSize' => 22, 'font' => 'IranSANS.ttf'),
		'Roya' => array('minSize' => 20, 'maxSize' => 25, 'font' => 'Roya.ttf'),
		'Tahrir' => array('minSize' => 20, 'maxSize' => 25, 'font' => 'Tahrir.ttf')
	);

	/** Wave configuracion in X and Y axes */
	public $Yperiod    = 12;
	public $Yamplitude = 14;
	public $Xperiod    = 11;
	public $Xamplitude = 5;

	/** letter rotation clockwise */
	public $maxRotation = 3;

	/**
	 * Internal image size factor (for better image quality)
	 * 1: low, 2: medium, 3: high
	 */
	public $scale = 2;

	/** 
	 * Blur effect for better image quality (but slower image processing).
	 * Better image results with scale=3
	 */
	public $blur = false;

	/** Debug? */
	public $debug = false;
	
	/** Image format: jpeg or png */
	public $imageFormat = 'png';


	/** GD image */
	public $im;

	public $useImageMagick = true;

	/** Get image as <img> element with base64 string **/
	public $getImageAsBase64String = false;

	public function __construct($config = array()) {
		$this->colors = $this->randomRGBColors();
	}

	public function CreateImage() {
		$ini = microtime(true);

		// cap difficulty
        if($this->difficulty >2) $this->difficulty = 2;
        if($this->difficulty<=0) $this->difficulty = 0.1;

		/** Initialization */
		$this->ImageAllocate();
		
		/** Text insertion */
		$text = $this->GetCaptchaText();
		$fontcfg  = $this->fonts[array_rand($this->fonts)];
		$this->WriteText($text, $fontcfg);

		$_SESSION[$this->session_var] = $text;

		/** Transformations */
		if (!empty($this->lineWidth)) {
			$this->WriteLine();
		}
		$this->WaveImage();
		if (($this->useImageMagick) && ($this->blur))
		{
			$this->im->gaussianBlurImage(3, 1);
		} elseif ($this->blur && function_exists('imagefilter')) {
			imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);
		}
		$this->ReduceImage();


		if ($this->debug) {
			if ($this->useImageMagick)
			{
				$draw = new ImagickDraw();
				$draw->setFontSize(9);
				$draw->setFillColor(new ImagickPixel("black"));
				$this->im->annotateImage($draw, 10, 10, 0, "$text {$fontcfg['font']} ".round((microtime(true)-$ini)*1000)."ms");
			} else {
				imagestring($this->im, 1, 1, $this->height-8,
					"$text {$fontcfg['font']} ".round((microtime(true)-$ini)*1000)."ms",
					$this->GdFgColor
				);
			}
		}


		/** Output */
		$this->WriteImage();
		$this->Cleanup();
	}

	/**
	 * Creates the image resources
	 */
	protected function ImageAllocate() {

		$color = $this->colors[array_rand($this->colors)];

		if ($this->useImageMagick)
		{
			if (!empty($this->im)) {
				$this->im->destroy();
			}

			$this->im = new Imagick();
			$this->GdBgColor = new ImagickPixel("rgb(".
				$this->backgroundColor[0].", ".
				$this->backgroundColor[1].", ".
				$this->backgroundColor[2].")"
			);
			$this->im->newImage($this->width*$this->scale, $this->height*$this->scale, $this->GdBgColor);
			$this->im->setImageBackgroundColor($this->GdBgColor);
			$this->im->setImageMatteColor($this->GdBgColor);
			// Foreground color
			$this->GdFgColor = new ImagickPixel("rgb(".
				$color[0].", ".
				$color[1].", ".
				$color[2].")"
			);

			// Shadow color
			if (!empty($this->shadowColor) && is_array($this->shadowColor) && sizeof($this->shadowColor) >= 3) {
				$this->GdShadowColor = new ImagickPixel("rgb(".
					$this->shadowColor[0].", ".
					$this->shadowColor[1].", ".
					$this->shadowColor[2].")"
				);
			}

		} else {
			if (!empty($this->im)) {
				imagedestroy($this->im);
			}

			$this->im = imagecreatetruecolor($this->width*$this->scale, $this->height*$this->scale);

			// Background color
			$this->GdBgColor = imagecolorallocate($this->im,
				$this->backgroundColor[0],
				$this->backgroundColor[1],
				$this->backgroundColor[2]
			);
			imagefilledrectangle($this->im, 0, 0, $this->width*$this->scale, $this->height*$this->scale, $this->GdBgColor);

			// Foreground color
			$this->GdFgColor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);

			// Shadow color
			if (!empty($this->shadowColor) && is_array($this->shadowColor) && sizeof($this->shadowColor) >= 3) {
				$this->GdShadowColor = imagecolorallocate($this->im,
					$this->shadowColor[0],
					$this->shadowColor[1],
					$this->shadowColor[2]
				);
			}
		}

	}

	/**
	 * Text generation
	 *
	 * @return string Text
	 */
	protected function GetCaptchaText() {
		$text1 = $this->GetDictionaryCaptchaText();
		$text2 = $this->GetDictionaryCaptchaText();
		if (empty($text1) && empty($text2)) {
			$text1 = $this->GetRandomCaptchaText();
			$text2 = $this->GetRandomCaptchaText();
		}
		return $text1 . ' ' . $text2;
	}

	/**
	 * Random text generation
	 *
	 * @return string Text
	 */
	protected function GetRandomCaptchaText($length = null) {
		if (empty($length)) {
			$length = rand($this->minWordLength, $this->maxWordLength);
		}

		$words  = 'بپتثجچحخدذرزژسشصضطظعغفقکگلمنه';
		$vocals = 'اوی';

		$text  = '';
		$vocal = mt_rand(0, 1);
		for($i = 0; $i < $length; $i++) {
			if ($vocal) {
				$text .= mb_substr($vocals, mt_rand(0, mb_strlen($vocals) - 1), 1);
			} else {
				$text .= mb_substr($words, mt_rand(0, mb_strlen($words) - 1), 1);
			}
			$vocal = !$vocal;
		}
		return $text;
	}

	/**
	 * Random dictionary word generation
	 *
	 * @param boolean $extended Add extended "fake" words
	 * @return string Word
	 */
	function GetDictionaryCaptchaText($extended = false) {
		if (empty($this->wordsFile)) {
			return false;
		}

		// Full path of words file
		if (substr($this->wordsFile, 0, 1) == '/') {
			$wordsfile = $this->wordsFile;
		} else {
			$wordsfile = $this->resourcesPath.'/'.$this->wordsFile;
		}

		if (!file_exists($wordsfile)) {
			return false;
		}

		$words = file($wordsfile, FILE_IGNORE_NEW_LINES);
        $text = $words[array_rand($words)];


		/** Change ramdom volcals */
		if ($extended) {
			$text = preg_split('//u', $text);
			$vocals = array('ا', 'و', 'ی');
			foreach ($text as $i => $char) {
				if (mt_rand(0, 1) && in_array($char, $vocals)) {
					$text[$i] = $vocals[array_rand($vocals)];
				}
			}
			$text = implode('', $text);
		}

		return $text;
	}

	/**
	 * Horizontal line insertion
	 */
	protected function WriteLine() {
		if ($this->useImageMagick)
		{
			$x1 = $this->width*$this->scale*.15;
			$x2 = $this->textFinalX;
			$y1 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
			$y2 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
			$width = $this->lineWidth/2*$this->scale;
			$draw = new ImagickDraw();
			$draw->setFillColor($this->GdFgColor);
			for ($i = $width*-1; $i <= $width; $i++) {
				$draw->line($x1, $y1+$i, $x2, $y2+$i);
			}
			$this->im->drawImage($draw);
		} else {
			$x1 = $this->width*$this->scale*.15;
			$x2 = $this->textFinalX;
			$y1 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
			$y2 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
			$width = $this->lineWidth/2*$this->scale;

			for ($i = $width*-1; $i <= $width; $i++) {
				imageline($this->im, $x1, $y1+$i, $x2, $y2+$i, $this->GdFgColor);
			}
		}
	}

	/**
	 * Text insertion
	 */
	protected function WriteText($text, $fontcfg = array()) {
		if (empty($fontcfg)) {
			// Select the font configuration
			$fontcfg  = $this->fonts[array_rand($this->fonts)];
		}

		// Full path of font file
		$fontfile = $this->resourcesPath.'/fonts/'.$fontcfg['font'];

		if ($this->useImageMagick)
		{
			$draw = new ImagickDraw();
			$draw->setFont($fontfile);
			$gd = new FarsiGD();
			$degree = rand($this->maxRotation*-1, $this->maxRotation)*$this->difficulty;
			$fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize'])*$this->scale*1.5;
			$draw->setFontSize($fontsize);
			$draw->setTextEncoding('UTF-8');
			$draw->setTextAntialias(true);
			$draw->setGravity(Imagick::GRAVITY_CENTER);
			if ($this->shadowColor) {
				$draw->setFillColor($this->GdShadowColor);
				$this->im->annotateImage($draw, 0, 0, $degree, $gd->persianText($text, 'fa', 'normal'));
			}
			$draw->setFillColor($this->GdFgColor);
			$this->im->annotateImage($draw, 0, 0, $degree, $gd->persianText($text, 'fa', 'normal'));
		} else {
			for ($i=0; $i<$length; $i++) {
				$degree   = rand($this->maxRotation*-1, $this->maxRotation);
				$fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize'])*$this->scale;
				$letter   = substr($text, $i, 1);
			
				if ($this->shadowColor) {
					$coords = imagettftext($this->im, $fontsize, $degree,
					$x+$this->scale, $y+$this->scale,
					$this->GdShadowColor, $fontfile, $letter);
				}
				$coords = imagettftext($this->im, $fontsize, $degree,
					$x, $y,
					$this->GdFgColor, $fontfile, $letter);
				
				
				$x += ($coords[2]-$x) + ($fontcfg['spacing']*$this->scale);
			}
		}
		$this->textFinalX = $x;
	}

	/**
	 * Wave filter
	 */
	protected function WaveImage() {
		// create wave difficulty
        $wdf = 1;
        if($this->difficulty<1) $wdf = 1/$this->difficulty*(0.9/$this->difficulty);
        if($this->difficulty>1) $wdf = (1/($this->difficulty*1.7))+0.5;
		if ($this->useImageMagick)
		{
			$this->im->waveImage($this->Xamplitude, $this->scale*$this->Xperiod*rand(1,3)*5*$wdf);
			$this->im->rotateImage(new ImagickPixel('none'), 90); 
			$this->im->waveImage($this->Yamplitude, $this->scale*$this->Yperiod*rand(1,2)*5*$wdf);
			$this->im->rotateImage(new ImagickPixel('none'), -90); 
			$this->im->setImageBackgroundColor($this->GdBgColor);
			$this->im = $this->im->flattenImages();
		} else {
			// X-axis wave generation
			$xp = $this->scale*$this->Xperiod*rand(1,3) * $wdf;
			$k = rand(1, 100);
			for ($i = 0; $i < ($this->width*$this->scale); $i++) {
				imagecopy($this->im, $this->im,
					$i-1, sin($k+$i/$xp) * ($this->scale*$this->Xamplitude),
					$i, 0, 1, $this->height*$this->scale);
			}

			// Y-axis wave generation
			$k = rand(0, 100);
			$yp = $this->scale*($this->Yperiod)*rand(1,2) * $wdf;
			for ($i = 0; $i < ($this->height*$this->scale); $i++) {
				imagecopy($this->im, $this->im,
					sin($k+$i/$yp) * ($this->scale*$this->Yamplitude), $i-1,
					0, $i, $this->width*$this->scale, 1);
			}
		}
	}

	/**
	 * Reduce the image to the final size
	 */
	protected function ReduceImage() {
		if ($this->useImageMagick)
		{
			$this->im->scaleImage($this->width, $this->height);
		} else {
			$imResampled = imagecreatetruecolor($this->width, $this->height);
			imagecopyresampled($imResampled, $this->im,
				0, 0, 0, 0,
				$this->width, $this->height,
				$this->width*$this->scale, $this->height*$this->scale
			);
			imagedestroy($this->im);
			$this->im = $imResampled;
		}
	}

	/**
	 * File generation
	 */
	protected function WriteImage() {
		if ($this->useImageMagick)
		{
			if ($this->debug)
			{
				$this->im->borderImage(new ImagickPixel("rgb(220,220,220)"), 1, 1);
			}
			$this->im->setImageFormat('png');
			if ($this->getImageAsBase64String)
			{
				echo "<img src='data:image/jpg;base64,".base64_encode($this->im->getImageBlob())."' alt='Captcha'/>";
			} else {
				header("Content-type: image/png");
				echo $this->im->getImageBlob();
			}
		} else {
			if ($this->imageFormat == 'png' && function_exists('imagepng')) {
				header("Content-type: image/png");
				imagepng($this->im);
			} else {
				header("Content-type: image/jpeg");
				imagejpeg($this->im, null, 80);
			}
		}
	}

	/**
	 * Cleanup
	 */
	protected function Cleanup() {
		if ($this->useImageMagick)
		{
			$this->im->destroy();
		} else {
			imagedestroy($this->im);
		}
	}

	public static function randomRGBColors($count = 5) {
	  $rc = new RandomColor();
	  $colors = $rc::many($count, array('format'=>'rgb', 'luminosity'=>'dark'));
	  foreach($colors as $key => &$color) {
	    $color = array($color['r'], $color['g'], $color['b']);
	  }
	  return $colors;
    }
}

?>