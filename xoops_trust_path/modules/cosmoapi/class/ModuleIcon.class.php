<?php

/**
 * module icon
 */
class Cosmoapi_ModuleIcon {

	private static $_mLetters = array(
		'a' => ' ###/#   #/#####/#   #/#   #',
		'b' => '####/#   #/####/#   #/####',
		'c' => ' ###/#   #/#/#   #/ ###',
		'd' => '####/#   #/#   #/#   #/####',
		'e' => '#####/#/####/#/#####',
		'f' => '#####/#/####/#/#',
		'g' => ' ####/#/# ###/#   #/#####',
		'h' => '#   #/#   #/#####/#   #/#   #',
		'i' => '#/#/#/#/#', 
		'j' => ' ####/    #/    #/#   #/ ###',
		'k' => '#   #/#  #/###/#  #/#   #',
		'l' => '#/#/#/#/#####',
		'm' => '#####/# # #/# # #/# # #/# # #',
		'n' => '#   #/##  #/# # #/#  ##/#   #',
		'o' => ' ###/#   #/#   #/#   #/ ###',
		'p' => '####/#   #/####/#/#',
		'q' => ' ###/#   #/#   #/#  ##/ ####',
		'r' => '####/#   #/####/#   #/#   #',
		's' => '#####/#/#####/    #/#####',
		't' => '#####/  #/  #/  #/  #',
		'u' => '#   #/#   #/#   #/#   #/ ###',
		'v' => '#   #/#   #/#   #/ # #/  #',
		'w' => '# # #/# # #/# # #/# # #/#####',
		'x' => '#   #/ # #/  #/ # #/#   #',
		'y' => '#   #/ # #/  #/  #/  #',
		'z' => '#####/   #/  #/ #/#####',
		'0' => ' ###/#  ##/# # #/##  #/ ###',
		'1' => ' #/##/ #/ #/ #',
		'2' => '#####/    #/#####/#/#####',
		'3' => '#####/    #/#####/    #/#####',
		'4' => '#   #/#   #/#####/    #/    #',
		'5' => '#####/#/#### /    #/#### ',
		'6' => '#####/#/#####/#   #/#####',
		'7' => '#####/    #/    #/    #/    #',
		'8' => '#####/#   #/#####/#   #/#####',
		'9' => '#####/#   #/#####/    #/#####',
		' ' => '  /  /  /  /  ',
		'_' => '/////####',
	);

	/**
	 * render module icon
	 *
	 * @param string $fpath base image file
	 * @param string $dirname
	 * @param string $trustDirname
	 */
	public static function render($fpath, $dirname, $trustDirname) {
		self::_prepare();
		if (!file_exists($fpath))
			self::_error404();
		$im = @imagecreatefrompng($fpath);
		if ($im === false)
			self::_error404();
		$mw = 79; // maximum width of drawing area
		$ox = 47; // offset X
		$oy = 12; // offset Y (if same directory name)
		$oy_d = 8; // offset Y for $dirname
		$oy_t = 19; // offset Y for $trustDirname
		$isSameDirname = ($dirname == $trustDirname);
		imagealphablending($im, true);
		$color_d = imagecolorallocate($im , 0, 0, 0);
		$color_t = imagecolorallocate($im , 0xa0, 0xa0, 0xa0);
		// write dirname
		$cw = self::_getStringWidth($dirname);
		while ($cw > $mw) {
			// trim string if over length
			$dirname = substr($dirname, 0, -1);
			$cw = self::_getStringWidth($dirname);
		}
		$x = $ox + ($mw - $cw) / 2;
		$y = $isSameDirname ? $oy : $oy_d;
		self::_writeString($im, $x, $y, $dirname, $color_d);
		if (!$isSameDirname) {
			// write trust dirname if different
			$cw = self::_getStringWidth($trustDirname);
			while ($cw > $mw) {
				// trim string if over length
				$dirname = substr($trustDirname, 0, -1);
				$cw = self::_getStringWidth($dirname);
			}
			$x = $ox + ($mw - $cw) / 2;
			$y = $oy_t;
			self::_writeString($im, $x, $y, $trustDirname, $color_t);
		}
		self::_showImage($im);
		imagecolordeallocate($im, $color_d);
		imagecolordeallocate($im, $color_t);
		imagedestroy($im);
		self::_cleanup();
	}

	/**
	 * show image
	 *
	 * @param resource $im
	 */
	private static function _showImage($im) {
		$icon_cache_limit = 3600 ;
		session_cache_limiter('public');
		header('Expires: ' . date('r', intval(time() / $icon_cache_limit) * $icon_cache_limit + $icon_cache_limit));
		header('Cache-Control: public, max-age=' . $icon_cache_limit);
		header('Last-Modified: ' . date('r', intval(time() / $icon_cache_limit) * $icon_cache_limit));
		header('Content-Type: image/png');
		imagepng($im);
	}

	/**
	 * output 404 Not Found
	 */
	private static function _error404() {
		$error = 'HTTP/1.0 404 Not Found';
		header($error);
		echo $error;
		self::_cleanup();
	}

	/**
	 * prepare
	 */
	private static function _prepare() {
		self::_clearObFilters();
	}

	/**
	 * cleanup
	 */
	private static function _cleanup() {
		register_shutdown_function(array(__CLASS__, 'onShutdown'));
		ob_start();
		exit();
	}

	/**
	 * clear ob filters
	 */
	private static function _clearObFilters() {
		$handlers = ob_list_handlers();
		while (!empty($handlers)) {
			ob_end_clean();
			$handlers = ob_list_handlers();
		}
	}

	/**
	 * get width
	 *
	 * @param resource $im
	 * @param int $x
	 * @param int $y
	 * @param string $ch
	 * @param resource $color
	 */
	private static function _getStringWidth($text) {
		$text = strtolower($text);
		$chars = str_split($text);
		$width = 0;
		foreach ($chars as $ch) {
			$pattern = isset(self::$_mLetters[$ch]) ? self::$_mLetters[$ch] : self::$_mLetters[' '];
			$pattern = explode('/', $pattern);
			$dw = 0;
			foreach ($pattern as $line) {
				$dx = strlen($line);
				$dw = max($dw, $dx);
			}
			$width += $dw + 1;
		}
		if ($width > 0)
			$width--;
		return $width;
	}

	/**
	 * write character
	 *
	 * @param resource $im
	 * @param int $x
	 * @param int $y
	 * @param string $ch
	 * @param resource $color
	 */
	private static function _writeCharacter($im, $x, $y, $ch, $color) {
		$pattern = isset(self::$_mLetters[$ch]) ? self::$_mLetters[$ch] : self::$_mLetters[' '];
		$pattern = array_map('str_split', explode('/', $pattern));
		$dy = 0;
		$width = 0;
		foreach ($pattern as $dots) {
			$dx = 0;
			foreach ($dots as $dot) {
				if ($dot == '#')
					imagesetpixel($im, $x + $dx, $y + $dy, $color);
				$dx++;
				$width = max($width, $dx);
			}
			$dy++;
		}
		return $width;
	}

	/**
	 * write string
	 *
	 * @param resource $im
	 * @param int $x
	 * @param int $y
	 * @param string $text
	 * @param resource $color
	 */
	private static function _writeString($im, $x, $y, $text, $color) {
		$text = strtolower($text);
		$chars = str_split($text);
		foreach ($chars as $ch) {
			$width = self::_writeCharacter($im, $x, $y, $ch, $color);
			$x += $width + 1;
		}
	}

	/**
	 * on shutdown callback handler
	 */
	public static function onShutdown() {
		self::_clearObFilters();
	}

}

