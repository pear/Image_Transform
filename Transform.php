<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Peter Bowyer <peter@mapledesign.co.uk>                      |
// |          Alan Knowles <alan@akbkhome.com>                            |
// |          Vincent Oostindie <vincent@sunlight.tmfweb.nl>              |
// |          Philippe Jausions <Philippe.Jausions@11abacus.com>          |
// +----------------------------------------------------------------------+
//
// $Id$
//
// Image Transformation interface
//

require_once 'PEAR.php';

/**
 * The main "Image_Transform" class is a container and base class which
 * provides the static method for creating an Image object as well as
 * some utility functions (maths) common to all parts of Image_Transform.
 *
 *
 * @package  Image_Transform
 * @version  1.00
 * @author   Peter Bowyer <peter@mapledesign.co.uk>
 * @since    PHP 4.0
 */
Class Image_Transform
{
    /**
     * Name of the image file
     * @var string
     */
    var $image = '';
    /**
     * Type of the image file (eg. jpg, gif png ...)
     * @var string
     */
    var $type = '';
    /**
     * Original image width in x direction
     * @var int
     */
    var $img_x = '';
    /**
     * Original image width in y direction
     * @var int
     */
    var $img_y = '';
    /**
     * New image width in x direction
     * @var int
     */
    var $new_x = '';
    /**
     * New image width in y direction
     * @var int
     */
    var $new_y = '';
    /**
     * Path to the library used
     * e.g. /usr/local/ImageMagick/bin/ or
     * /usr/local/netpbm/
     */
    var $lib_path = '';
    /**
     * Flag to warn if image has been resized more than once before displaying
     * or saving.
     */
    var $resized = false;

    /**
     * @var array General options
     * @access protected
     */
    var $_options = array(
        'quality'     => 75,
        'scaleMethod' => 'smooth',
        'canvasColor' => array(255, 255, 255)
        );

	/**
	 * Flag for whether settings should be discarded on saving/display of image
	 * @var bool
	 * @see Image_Transform::keepSettingsOnSave
	 */
	 var $keep_settings_on_save = false;

    /**
     * Supported image types
     * @access protected
     * @access protected
     * @var array
     */
    var $_supported_image_types = array();

    /**
     * Initialization error tracking
     * @access private
     * @var object
     **/
    var $_error = null;

    /**
     * associative array that tracks existence of programs
     * (for drivers using shell interface and a tiny performance
     * improvement if the clearstatcache() is used)
     * @access protected
     * @var array
     */
    var $_programs = array();

     /**
      * Default parameters used in the addText methods.
      */
     var $default_text_params = array('text' => 'Default text',
                                      'x'     => 10,
                                      'y'     => 20,
                                      'color' => 'red',
                                      'font'  => 'Arial.ttf',
        							  'size'  => '12',
        							  'angle' => 0,
                                      'resize_first' => false);

    /**
     * Create a new Image_Transform object
     *
     * @param string $driver name of driver class to initialize. If no driver
     *               is specified the factory will attempt to use 'Imagick' first
     *               then 'GD' second, then 'Imlib' last
     *
     * @return mixed a newly created Image_Transform object, or a PEAR
     * error object on error
     *
     * @see PEAR::isError()
     * @see Image_Transform::setOption()
     */
    function &factory($driver)
    {
		if ('' == $driver) {
            $aExtensions = array(
                'imagick' => 'Imagick2',
                'gd'      => 'GD',
                'imlib'   => 'Imlib');
            foreach ($aExtensions as $sExt => $sDriver) {
                if (PEAR::loadExtension($sExt)) {
                    $driver = $sDriver;
                    break;
                }
            }
            if (!$driver) {
                return PEAR::raiseError('No image library specified... aborting.  You must call ::factory() with a proper library to load.', true);
            }
		}
        @include_once 'Image/Transform/Driver/' . basename($driver) . '.php';

        $classname = "Image_Transform_Driver_{$driver}";
        if (!class_exists($classname)) {
            return PEAR::raiseError('Image library not supported... aborting.', true);
        }
        $obj =& new $classname;

        // Check startup error
        if ($error =& $obj->isError()) {
            $obj =& $error;
        }
        return $obj;
    }

    /**
     * Returns/set an error when the instance couldn't initialize properly
     *
     * @access protected
     * @param  object PEAR Object when setting an error
     * @return mixed FALSE or PEAR error object
     */
    function &isError($error = null)
    {
        if (!is_null($error)) {
            $this->_error =& $error;
        }
        return $this->_error;
    }

    /**
     * Resize the Image in the X and/or Y direction
     * If either is 0 it will keep the original size for this dimension
     *
     * @access public
     *
     * @param mixed $new_x (0, number, percentage 10% or 0.1)
     * @param mixed $new_y (0, number, percentage 10% or 0.1)
     * @param array $options Options
     *
     * @return TRUE or PEAR Error object on error
     */
    function resize($new_x = 0, $new_y = 0, $options = null)
    {
        // 0 means keep original size
        $new_x = (0 == $new_x) ? $this->img_x : $this->_parse_size($new_x, $this->img_x);
        $new_y = (0 == $new_y) ? $this->img_y : $this->_parse_size($new_y, $this->img_y);
        // Now do the library specific resizing.
        return $this->_resize($new_x, $new_y);
    } // End resize


    /**
     * Scale the image to the specified width
     *
     * This method preserves the aspect ratio
     *
     * @access public
     * @param int $new_x Size to scale X-dimension to
     * @return TRUE or PEAR Error object on error
     */
    function scaleByX($new_x)
    {
        $new_y = round(($new_x / $this->img_x) * $this->img_y, 0);
        return $this->_resize($new_x, $new_y);
    } // End scaleByX

    /**
     * Alias for resize()
     *
     * @see resize()
     */
    function scaleByXY($new_x = 0, $new_y = 0, $options = null)
    {
        return $this->resize($new_x, $new_y, $options);
    } // End scaleByXY

    /**
     * Scale the image to the specified height.
     *
     * This method preserves the aspect ratio
     *
     * @access public
     * @param int $new_y Size to scale Y-dimension to
     * @return TRUE or PEAR Error object on error
     */
    function scaleByY($new_y)
    {
        $new_x = round(($new_y / $this->img_y) * $this->img_x, 0);
        return $this->_resize($new_x, $new_y);
    } // End scaleByY

    /**
     * Scales an image by a percentage, factor or a given length
     *
     * This method preserves the aspect ratio
     *
     * @access public
     * @param mixed (number, percentage 10% or 0.1)
     * @return TRUE or PEAR Error object on error
     * @see scaleByPercentage, scaleByFactor, scaleByLength
     */
    function scale($size)
    {
        if ((strlen($size) > 1) && (substr($size, -1) == '%')) {
            return $this->scaleByPercentage(substr($size, 0, -1));
        } elseif ($size < 1) {
            return $this->scaleByFactor($size);
        } else {
            return $this->scaleByLength($size);
        }
    } // End scale

    /**
     * Scales an image to a percentage of its original size.  For example, if
     * my image was 640x480 and I called scaleByPercentage(10) then the image
     * would be resized to 64x48
     *
     * @access public
     * @param  int $size Percentage of original size to scale to
     * @return TRUE or PEAR Error object on error
     */
    function scaleByPercentage($size)
    {
        return $this->scaleByFactor($size / 100);
    } // End scaleByPercentage

    /**
     * Scales an image to a factor of its original size.  For example, if
     * my image was 640x480 and I called scaleByFactor(0.5) then the image
     * would be resized to 320x240.
     *
     * @access public
     * @param float $size Factor of original size to scale to
     * @return TRUE or PEAR Error object on error
     */
    function scaleByFactor($size)
    {
        $new_x = round($size * $this->img_x, 0);
        $new_y = round($size * $this->img_y, 0);
        return $this->_resize($new_x, $new_y);
    } // End scaleByFactor

    /**
     * Scales an image so that the longest side has the specified dimension.
     *
     * This method preserves the aspect ratio
     *
     * @access public
     * @param int $size Max dimension in pixels
     * @return TRUE or PEAR Error object on error
     */
    function scaleMaxLength($size)
    {
         if ($this->img_x >= $this->img_y) {
            $new_x = $size;
            $new_y = round(($new_x / $this->img_x) * $this->img_y, 0);
        } else {
            $new_y = $size;
            $new_x = round(($new_y / $this->img_y) * $this->img_x, 0);
        }
        return $this->_resize($new_x, $new_y);
    } // End scaleMaxLength

    /**
     * Alias for scaleMaxLength
     *
     * @access public
     * @return TRUE or PEAR Error object on error
     * @see scaleMaxLength()
     */
    function scaleByLength($size)
    {
    	return $this->scaleMaxLength($size);
    }
    
    /**
     * Fit the image in the specified box
     *
     * If the image is bigger than the box specified by $width and $height,
     * it will be scaled down to fit inside of it.
     * If the image is smaller, nothing is done.
     *
     * @return bool|PEAR_Error TRUE or PEAR Error object on error
     * @access public
     */
    function fit($width, $height)
    {
        if ($this->img_x <= $width
            && $this->img_y <= height) {
            return true;
        }
        return ($this->img_y <= $height)
                ? $this->scaleByX($width)
                : $this->scaleByY($height);
    }

    /**
     * Set one options
     *
     * @access public
     * @param  string Name of option
     * @param  mixes  Value of option
     * @return void
     * @see setOptions()
     */
    function setOption($name, $value)
    {
        $this->_options[$name] = $value;
    }

    /**
     * Set multiple options at once
     *
     * Associative array of options:
     *  - quality     (Integer: 0: poor - 100: best)
     *  - scaleMethod ('smooth', 'pixel')
     *
     * @access public
     * @param  array $options Array of options
     * @return void
     */
    function setOptions($options)
    {
        $this->_options = array_merge($this->_options, $options);
    }

    /**
     * Sets the image type (in lowercase letters), the image height and width.
     *
     * @access protected
     * @return mixed True or PEAR_error
     * @see PHP_Compat::image_type_to_mime_type()
     * @link http://php.net/getimagesize
     */
    function _get_image_details($image)
    {
        $data = @getimagesize($image);
        //  1 = GIF,   2 = JPG,  3 = PNG,  4 = SWF,  5 = PSD,  6 = BMP,
        //  7 = TIFF (intel byte order),   8 = TIFF (motorola byte order),
        //  9 = JPC,  10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF,
        // 15 = WBMP, 16 = XBM
        if (!is_array($data)) {
            return PEAR::raiseError("Cannot fetch image or images details.", true);
        }

        switch ($data[2]) {
            case IMAGETYPE_GIF:
                $type = 'gif';
                break;
            case IMAGETYPE_JPEG:
                $type = 'jpeg';
                break;
            case IMAGETYPE_PNG:
                $type = 'png';
                break;
            case IMAGETYPE_SWF:
                $type = 'swf';
                break;
            case IMAGETYPE_PSD:
                $type = 'psd';
                break;
            case IMAGETYPE_BMP:
                $type = 'bmp';
                break;
            case IMAGETYPE_TIFF_II:
            case IMAGETYPE_TIFF_MM:
                $type = 'tiff';
                break;
            case IMAGETYPE_JPC:
                $type = 'jpc';
                break;
            case IMAGETYPE_JP2:
                $type = 'jp2';
                break;
            case IMAGETYPE_JPX:
                $type = 'jpx';
                break;
            case IMAGETYPE_JB2:
                $type = 'jb2';
                break;
            case IMAGETYPE_SWC:
                $type = 'swc';
                break;
            case IMAGETYPE_IFF:
                $type = 'iff';
                break;
            case IMAGETYPE_WBMP:
                $type = 'wbmp';
                break;
            case IMAGETYPE_XBM:
                $type = 'xbm';
                break;
            default:
                return PEAR::raiseError("Cannot recognize image format", true);
        }
        $this->img_x = $this->new_x = $data[0];
        $this->img_y = $this->new_y = $data[1];
        $this->type  = $type;

        return true;
    }


    /**
     * Returns the matching IMAGETYPE_* constant for a given image type
     *
     * @access protected
     * @param  mixed $type String (GIF, JPG,...)
     * @return mixed String or integer or input on error
     * @see PHP_Compat::image_type_to_mime_type()
     **/
    function _convert_image_type($type)
    {
        switch (strtolower($type)) {
            case 'gif':
                return IMAGETYPE_GIF;
            case 'jpeg':
            case 'jpg':
                return IMAGETYPE_JPEG;
            case 'png':
                return IMAGETYPE_PNG;
            case 'swf':
                return IMAGETYPE_SWF;
            case 'psd':
                return IMAGETYPE_PSD;
            case 'bmp':
                return IMAGETYPE_BMP;
            case 'tiff':
                return IMAGETYPE_TIFF_II;
                //IMAGETYPE_TIFF_MM;
            case 'jpc':
                return IMAGETYPE_JPC;
            case 'jp2':
                return IMAGETYPE_JP2;
            case 'jpx':
                return IMAGETYPE_JPX;
            case 'jb2':
                return IMAGETYPE_JB2;
            case 'swc':
                return IMAGETYPE_SWC;
            case 'iff':
                return IMAGETYPE_IFF;
            case 'wbmp':
                return IMAGETYPE_WBMP;
            case 'xbm':
                return IMAGETYPE_XBM;
            default:
                return $type;
        }

        return (isset($types[$t = strtolower($type)])) ? $types[$t] : $type;
    }


    /**
     * Parse input and convert
     * If either is 0 it will be scaled proportionally
     *
     * @access protected
     *
     * @param mixed $new_size (0, number, percentage 10% or 0.1)
     * @param int $old_size
     *
     * @return mixed Integer or PEAR_error
     */
    function _parse_size($new_size, $old_size)
    {
        if ('%' == $new_size) {
            $new_size = str_replace('%', '', $new_size);
            $new_size = $new_size / 100;
        }
        if ($new_size > 1) {
            return (int) $new_size;
        } elseif ($new_size == 0) {
            return (int) $old_size;
        } else {
            return (int) round($new_size * $old_size, 0);
        }
    }

    /**
     * Returns an angle between 0 and 360 from any angle value
     *
     * @access protected
     * @param  float $angle The angle to correct
     * @return float The angle
     */
    function _rotation_angle($angle)
    {
        $angle %= 360;
        return ($angle < 0) ? $angle + 360 : $angle;
    }

    /**
     * Returns the current value of $this->default_text_params.
     *
     * @access private
     * @return array $this->default_text_params The current text parameters
     */
    function _get_default_text_params()
    {
        return $this->default_text_params;
    }

    /**
     * Set the image width
     *
     * @access protected
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return void
     */
    function _set_img_x($size)
    {
    	$this->img_x = $size;
    }

    /**
     * Set the image height
     *
     * @access protected
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return void
     */
    function _set_img_y($size)
    {
    	$this->img_y = $size;
    }

    /**
     * Set the new image width
     *
     * @access protected
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return void
     */
    function _set_new_x($size)
    {
    	$this->new_x = $size;
    }

    /**
     * Set the new image height
     *
     * @access protected
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return void
     */
    function _set_new_y($size)
    {
    	$this->new_y = $size;
    }

    /**
     * Get the type of the image being manipulated
     *
     * @access public
     * @return string $this->type the image type
     */
    function getImageType()
    {
        return $this->type;
    }

    /**
     * Get the MIME type of the image being manipulated
     *
     * @access public
     * @param  string $type Image type to get MIME type for
     * @return string The MIME type if available, or an empty string
     * @see PHP_Compat::image_type_to_mime_type()
     * @link http://php.net/image_type_to_mime_type
     */
    function getMimeType($type = null)
    {
        return image_type_to_mime_type($this->_convert_image_type(($type) ? $type : $this->type));
    }

    /**
     * Return the image width
     *
     * @access public
     * @return int The width of the image
     */
    function getImageWidth()
    {
        return $this->img_x;
    }


	/**
	 * Return the image height
	 *
	 * @access public
	 * @return int The width of the image
	 */
	function getImageHeight()
	{
		return $this->img_y;
	}


    /**
     * Return the image size and extra format information
     *
     * @access public
     * @return array The width and height of the image
     * @see PHP::getimagesize()
     */
    function getImageSize()
    {
        return array(
            $this->img_x,
            $this->img_y,
            $this->type,
            'height="' . $this->img_y . '" width="' . $this->img_x . '"',
            'mime' => $this->getMimeType());
    }

    /**
     * This looks at the current image type and attempts to determine which
	 * web-safe format will be most suited.  It does not work brilliantly with
	 * *.png images, because it is very difficult to know whether they are
	 * 8-bit or greater.  Guess I need to have fatter code here :-)
	 *
     * @access public
     * @return string web-safe image type
     */
    function getWebSafeFormat()
    {
    	switch($this->type){
    		case 'gif':
            case 'png':
    			return 'png';
    			break;
    		default:
    			return 'jpeg';
    	} // switch
    }

    /**
     * Handle space in path and Windows/UNIX difference
     *
     * @access protected
     * @param  string $path Base dir
     * @param  string $command Command to execute
     * @param  string $args Arguments to pass to the command
     * @return string A prepared string suitable for exec()
     */
    function _prepare_cmd($path, $command, $args = '')
    {
        if (!OS_WINDOWS
            || !preg_match('/\s/', $path)) {
            return $path . $command . ' ' . $args;
        }
        return 'start /D "' . $path . '" /B ' . $command . ' ' . $args;
    }

    /**
     * Place holder for the real resize method
     * used by extended methods to do the resizing
     *
     * @access protected
     * @return PEAR_error
     */
    function _resize()
    {
        return PEAR::raiseError('Resize method not supported by driver', true);
    }

    /**
     * Load an image file to work with
     *
     * Place holder for the real load method
     * used by extended methods to do the resizing
     *
     * @access public
     * @return PEAR_error
     */
    function load($filename) {
        return PEAR::raiseError('load() method not supported by driver', true);
    }

    /**
     * Output the image to standard output
     *
     * Place holder for the real display method
     * used by extended methods to do the resizing
     *
     * @access public
     * @param string $type Format of image to save as
     * @param mixed  $quality Format-dependent
     * @return PEAR_error
     */
    function display($type, $quality = null) {
        return PEAR::raiseError('display() method not supported by driver', true);
    }

    /**
     * Returns if the driver supports a given image type
     *
     * @access public
     * @param  string $type Image type (GIF, PNG, JPEG...)
     * @param  string $mode 'r' for read, 'w' for write, 'rw' for both
     * @return TRUE if type (and mode) is supported FALSE otherwise
     */
    function supportsType($type, $mode = 'rw') {
        return (strpos(@$this->_supported_image_types[strtolower($type)], $mode) === false) ? false : true;
    }

    /**
     * Saves image to file
     *
     * Place holder for the real save method
     * used by extended methods to do the resizing
     *
     * @access public
     * @param string $filename Filename to save image to
     * @param string $type Format of image to save as
     * @param mixed  $quality Format-dependent
     * @return PEAR_error
     */
    function save($filename, $type, $quality = null) {
        return PEAR::raiseError('save() method not supported by driver', true);
    }

    /**
     * Release resource
     *
     * Place holder for the real free method
     * used by extended methods to do the resizing
     *
     * @access public
     * @return PEAR_error
     */
    function free() {
        return PEAR::raiseError('free() method not supported by driver', true);
    }

    /**
     * Convert a color string into an array of RGB values
     *
     * @param  string $colorhex A color following the #FFFFFF format
     * @access public
     * @return array 3-element array with 0-255 values
     *
     * @see rgb2colorname
     * @see colorarray2colorhex
     */
    function colorhex2colorarray($colorhex) {
        $r = hexdec(substr($colorhex, 1, 2));
        $g = hexdec(substr($colorhex, 3, 2));
        $b = hexdec(substr($colorhex, 5, 2));
        return array($r, $g, $b, 'type' => 'RGB');
    }
    
    function _send_display_headers($type)
    {
        // Find the filename of the original image:
        $filename = explode('.', basename($this->image));
        $filename = $filename[0];
        header('Content-type: ' . $this->getMimeType($type));
        header ('Content-Disposition: inline; filename=' . $filename . '.' . $type );
    }

    /**
     * Convert an array of RGB value into a #FFFFFF format color.
     *
     * @param  array  $color 3-element array with 0-255 values
     * @access public
     * @string mixed A color following the #FFFFFF format or FALSE
     *               if the array couldn't be converted
     *
     * @see rgb2colorname
     * @see colorhex2colorarray
     */
    function colorarray2colorhex($color) {
        $r = dechex($color[0]);
        $g = dechex($color[1]);
        $b = dechex($color[2]);
        $color = '#' . ((!isset($r{1})) ? '0' : '') . $r
                     . ((!isset($g{1})) ? '0' : '') . $g
                     . ((!isset($b{1})) ? '0' : '') . $b;
        return (strlen($color) != 7) ? false : $color;
    }


    /*** These snitched from the File package.  Saves including another class! ***/
    /**
     * Returns the temp directory according to either the TMP, TMPDIR, or TEMP env
     * variables. If these are not set it will also check for the existence of
     * /tmp, %WINDIR%\temp
     *
     * @access public
     * @return string The system tmp directory
     */
    function getTempDir()
    {
        include_once 'System.php';
        return System::tmpdir();
    }

    /**
     * Returns a temporary filename using tempnam() and the above getTmpDir() function.
     *
     * @access public
     * @param  string $dirname Optional directory name for the tmp file
     * @return string          Filename and path of the tmp file
     */
    function getTempFile($dirname = NULL)
    {
		return tempnam((is_null($dirname)) ? System::tmpdir() : $dirname, 'temp.');
    }

	function keepSettingsOnSave($bool)
	{
		$this->keep_settings_on_save = $bool;
	}


    /* Methods to add to the driver classes in the future */
    function addText()
    {
        return PEAR::raiseError('addText() method not supported by driver', true);
    }

    function addDropShadow()
    {
        return PEAR::raiseError('addDropShadow() method not supported by driver', true);
    }

    function addBorder()
    {
        return PEAR::raiseError('addBorder() method not supported by driver', true);
    }

    /**
     * Crop an image
     *
     * @access public
     *
     * @param int width Cropped image width
     * @param int height Cropped image height
     * @param int x X-coordinate to crop at
     * @param int y Y-coordinate to crop at
     *
     * @return mixed TRUE or a PEAR error object on error
     **/
    function crop($width, $height, $x = 0, $y = 0)
    {
        return PEAR::raiseError('crop() method not supported by driver', true);
    }

    function canvasResize()
    {
        return PEAR::raiseError('canvasResize() method not supported by driver', true);
    }

    /**
     * Corrects the gamma of an image
     *
     * @access public
     * @param float $outputgamma Gamma correction factor
     * @return mixed TRUE or a PEAR error object on error
     **/
    function gamma($outputgamma = 1.0)
    {
        return PEAR::raiseError('gamma() method not supported by driver', true);
    }

    function rotate($angle, $options = null)
    {
        return PEAR::raiseError('rotate() method not supported by driver', true);
    }

    /**
     * Horizontal mirroring
     *
     * @access public
     * @return TRUE or PEAR Error object on error
     * @see flip()
     **/
    function mirror()
    {
        return PEAR::raiseError('mirror() method not supported by driver', true);
    }

    /**
     * Vertical mirroring
     *
     * @access public
     * @return TRUE or PEAR Error object on error
     * @see mirror()
     **/
    function flip()
    {
        return PEAR::raiseError('flip() method not supported by driver', true);
    }

    /**
     * Converts an image into greyscale colors
     *
     * @access public
     * @return mixed TRUE or a PEAR error object on error
     **/
    function greyscale()
    {
        return PEAR::raiseError('greyscale() method not supported by driver', true);
    }

    /**
     * @see greyscale()
     **/
    function grayscale()
    {
        return $this->greyscale();
    }
}

?>