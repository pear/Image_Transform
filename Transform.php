<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
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
 * @package  Image Resize
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
     * Path the the library used
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
      * Default parameters used in the addText methods.
      */
     var $default_text_params = array('text' => 'This is Text',
                                      'x' => 10,
                                      'y' => 20,
                                      'color' => 'red',
                                      'font' => 'Arial.ttf',
        							  'size' => '12',
        							  'angle' => 0,
                                      'resize_first' => false);

    /**
     * Create a new Image_resize object
     *
     * @param string $driver name of driver class to initialize
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
		    return PEAR::raiseError("No image library specified... aborting.  You must call ::factory() with one parameter, the library to load.", true);
		}
        include_once "Image/Transform/Driver/$driver.php";

        $classname = "Image_Transform_Driver_{$driver}";
        $obj =& new $classname;
        return $obj;
    }


    /**
     * Resize the Image in the X and/or Y direction
     * If either is 0 it will keep the original size for this dimension
     *
     * @access public
     *
     * @param mixed $new_x (0, number, percentage 10% or 0.1)
     * @param mixed $new_y (0, number, percentage 10% or 0.1)
     *
     * @return mixed none or PEAR_error
     */
    function resize($new_x = 0, $new_y = 0)
    {
        // 0 means keep original size
        $new_x = (0 == $new_x) ? $this->img_x : $this->_parse_size($new_x, $this->img_x);
        $new_y = (0 == $new_y) ? $this->img_y : $this->_parse_size($new_y, $this->img_y);
        // Now do the library specific resizing.
        return $this->_resize($new_x, $new_y);
    } // End resize


    /**
     * Scale the image to have the max x dimension specified.
     *
     * @param int $new_x Size to scale X-dimension to
     * @return none
     */
    function scaleByX($new_x)
    {
        $new_y = round(($new_x / $this->img_x) * $this->img_y, 0);
        return $this->_resize($new_x, $new_y);
    } // End resizeX

    /**
     * Scale the image to have the max y dimension specified.
     *
     * @access public
     * @param int $new_y Size to scale Y-dimension to
     * @return none
     */
    function scaleByY($new_y)
    {
        $new_x = round(($new_y / $this->img_y) * $this->img_x, 0);
        return $this->_resize($new_x, $new_y);
    } // End resizeY

    /**
     * Scales an image by a percentage, factor to a given length
     *
     * @access public
	 * @see scaleByPercentage, scaleByFactor, scaleByLength
     * @param mixed (number, percentage 10% or 0.1)
     * @return mixed none or PEAR_error
     */
    function scale($size)
    {
        if ((strlen($size) > 1) && (substr($size,-1) == '%')) {
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
     * @param int $size Percentage of original size to scale to
     * @return none
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
     * @return none
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
     * @access public
     * @param int $size Max dimension in pixels
     * @return none
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
    } // End scaleByLength

    /**
     * Alias for scaleMaxLength
     * 
     * @access public
     * @return void 
     */
    function scaleByLength($size){
    	$this->scaleMaxLength($size);
    }
    
    /**
     * Sets the image type (in lowercase letters), the image height and width.
     * 
     * @access private
     * @return mixed True or PEAR_error
     */
    function _get_image_details($image)
    {
    	$data = @GetImageSize($image);
        #1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order,
        # 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC
        if (is_array($data)){
            switch($data[2]){
                case 1:
                    $type = 'gif';
                    break;
                case 2:
                    $type = 'jpeg';
                    break;
                case 3:
                    $type = 'png';
                    break;
                case 4:
                    $type = 'swf';
                    break;
                case 5:
                    $type = 'psd';
					break;
                case 6:
                    $type = 'bmp';
					break;
                case 7:
                case 8:
                    $type = 'tiff';
					break;
                default:
                    return PEAR::raiseError("We do not recognize this image format", true);
            }
            $this->img_x = $data[0];
            $this->img_y = $data[1];
            $this->type = $type;

            return true;
        } else {
            return PEAR::raiseError("Cannot fetch image or images details.", true);
        }
    }


    /**
     * Parse input and convert
     * If either is 0 it will be scaled proportionally
     *
     * @access private
     *
     * @param mixed $new_size (0, number, percentage 10% or 0.1)
     * @param int $old_size
     *
     * @return mixed none or PEAR_error
     */
    function _parse_size($new_size, $old_size)
    {
        if ('%' == $new_size) {
            $new_size = str_replace('%','',$new_size);
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
     * @access private
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return
     */
    function _set_img_x($size)
    {
    	$this->img_x = $size;
    }

    /**
     * Set the image height
     * 
     * @access private
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return
     */
    function _set_img_y($size)
    {
    	$this->img_y = $size;
    }

    /**
     * Set the new image width
     * 
     * @access private
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return
     */
    function _set_new_x($size)
    {
    	$this->new_x = $size;
    }

    /**
     * Set the new image height
     * 
     * @access private
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return
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
     * This looks at the current image type and attempts to determin which
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
     * Place holder for the real resize method
     * used by extended methods to do the resizing
     *
     * @access private
     * @return PEAR_error
     */
    function _resize() {
        return PEAR::raiseError("No Resize method exists", true);
    }

    /**
     * Place holder for the real load method
     * used by extended methods to do the resizing
     *
     * @access public
     * @return PEAR_error
     */
    function load($filename) {
        return PEAR::raiseError("No Load method exists", true);
    }

    /**
     * Place holder for the real display method
     * used by extended methods to do the resizing
     *
     * @access public
     * @param string filename
     * @return PEAR_error
     */
    function display($type, $quality) {
        return PEAR::raiseError("No Display method exists", true);
    }

    /**
     * Place holder for the real save method
     * used by extended methods to do the resizing
     *
     * @access public
     * @param string filename
     * @return PEAR_error
     */
    function save($filename, $type, $quality) {
        return PEAR::raiseError("No Save method exists", true);
    }

    /**
     * Place holder for the real free method
     * used by extended methods to do the resizing
     *
     * @access public
     * @return PEAR_error
     */
    function free() {
        return PEAR::raiseError("No Free method exists", true);
    }

    /**
     * Reverse of rgb2colorname.
     *
     * @access public
     * @return PEAR_error
     *
     * @see rgb2colorname
     */
    function colorhex2colorarray($colorhex) {
        $r = hexdec(substr($colorhex, 1, 2));
        $g = hexdec(substr($colorhex, 3, 2));
        $b = hexdec(substr($colorhex, 4, 2));
        return array($r,$g,$b);
    }

    /**
     * Reverse of rgb2colorname.
     *
     * @access public
     * @return PEAR_error
     *
     * @see rgb2colorname
     */
    function colorarray2colorhex($color) {
        $color = '#'.dechex($color[0]).dechex($color[1]).dechex($color[2]);
        return strlen($color)>6?false:$color;
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
        if (OS_WINDOWS){
            if (isset($_ENV['TEMP'])) {
                return $_ENV['TEMP'];
            }
            if (isset($_ENV['TMP'])) {
                return $_ENV['TMP'];
            }
            if (isset($_ENV['windir'])) {
                return $_ENV['windir'] . '\temp';
            }
            return $_ENV['SystemRoot'] . '\temp';
        }
        if (isset($_ENV['TMPDIR'])) {
            return $_ENV['TMPDIR'];
        }
        return '/tmp';
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
		if (is_null($dirname)) {
			$dirname = File::getTempDir();
		}
        return tempnam($dirname, 'temp.');
    }


    /* Methods to add to the driver classes in the future */
    function addText()
    {
        return PEAR::raiseError("No addText method exists", true);
    }

    function addDropShadow()
    {
        return PEAR::raiseError("No AddDropShadow method exists", true);
    }

    function addBorder()
    {
        return PEAR::raiseError("No addBorder method exists", true);
    }

    function crop()
    {
        return PEAR::raiseError("No crop method exists", true);
    }

    function gamma()
    {
        return PEAR::raiseError("No gamma method exists", true);
    }
}
?>
