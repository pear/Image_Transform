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
// |          Philippe Jausions <Philippe.Jausions@11abacus.com>          |
// +----------------------------------------------------------------------+
//
//    Usage :
//    $img    = Image_Transform::factory('GD');
//    $angle  = -78;
//    $img->load('magick.png');
//
//    if($img->rotate($angle,array('autoresize'=>true,'color_mask'=>array(255,0,0)))){
//        $img->addText(array('text'=>"Rotation $angle",'x'=>0,'y'=>100,'font'=>'/usr/share/fonts/default/TrueType/cogb____.ttf'));
//        $img->display();
//        $img->free();
//    } else {
//        echo "Error";
//    }
//
//
// $Id$
//
// Image Transformation interface using the GD library
//

require_once "Image/Transform.php";

Class Image_Transform_Driver_GD extends Image_Transform
{
	/**
	 * Holds the image file for manipulation
	 */
    var $imageHandle = '';

	/**
	 * Holds the original image file
	 */
    var $old_image = '';

    /**
     * Check settings
     *
     * @return mixed true or a PEAR error object on error
     *
     * @see PEAR::isError()
     */
    function Image_Transform_Driver_GD()
    {
        $this->__construct();
    } // End function Image


    /**
     * Check settings
     *
     * @return mixed true or a PEAR error object on error
     *
     * @see PEAR::isError()
     */
    function __construct()
    {
        if (!PEAR::loadExtension('gd')) {
            $this->isError(PEAR::raiseError("GD library is not available.", true));
        } else {
            $types = ImageTypes();
            if ($types & IMG_PNG) {
                $this->_supported_image_types['png'] = 'rw';
            }
            if (($types & IMG_GIF)
                || function_exists('imagegif')) {
                $this->_supported_image_types['gif'] = 'rw';
            } elseif (function_exists('imagecreatefromgif')) {
                $this->_supported_image_types['gif'] = 'r';
            }
            if ($types & IMG_JPG) {
                $this->_supported_image_types['jpeg'] = 'rw';
            }
            if ($types & IMG_WBMP) {
                $this->_supported_image_types['wbmp'] = 'rw';
            }
            if (!$this->_supported_image_types) {
                $this->isError(PEAR::raiseError("No supported image types available", true));
            }
        }

    } // End function Image


    /**
     * Load image
     *
     * @param string $image filename
     *
     * @return mixed TRUE or a PEAR error object on error
     * @see PEAR::isError()
     */
    function load($image)
    {
        $this->image = $image;
        $result = $this->_get_image_details($image);
        if (PEAR::isError($result)) {
            return $result;
        }
        if (!$this->supportsType($this->type, 'r')) {
            return PEAR::raiseError('Image type not supported for input', true);
        }

        $functionName = 'ImageCreateFrom' . $this->type;
        $this->imageHandle = $functionName($this->image);
        return true;

    } // End load

    
    /**
     * Add a border of constant width around an image
     *
     * @param int $border_width Width of border to add
     * @author Peter Bowyer
     * @return bool true
     */
    function addBorder($border_width, $color = '')
    {
        $this->new_x = $this->img_x + 2*$border_width;
        $this->new_y = $this->img_y + 2*$border_width;
        
        if (function_exists('ImageCreateTrueColor') && $this->true_color) {
            $new_img =ImageCreateTrueColor($new_x, $new_y);
        }
        if (!$new_img) {
            $new_img =ImageCreate($new_x, $new_y);
            imagepalettecopy($new_img, $this->imageHandle);
        }
        
        if ($color) {
            if (!is_array($color)) {
                if ($color{0} == '#') {
    				// Not already in numberical format, so we convert it.
                    $color = $this->colorhex2colorarray($color);
                } else {
                    include_once 'Image/Transform/Driver/ColorsDefs.php';
                    $color = isset($colornames[$color])?$colornames[$color]:false;
                }
            }
            if ($this->true_color) {
                $c = imagecolorresolve($this->imageHandle, $color[0], $color[1], $color[2]);
                imagefill($new_img, 0, 0, $c);
            } else {
                imagecolorset($new_img, imagecolorat($new_img, 0, 0), $color[0], $color[1], $color[2]);
            }
        }
        ImageCopy($new_img, $this->imageHandle, $border_width, $border_width, 0, 0, $this->img_x, $this->img_y);
        $this->imageHandle = $new_img;
        $this->resized = true;

        return true;    
    }
    
    
    /**
     * addText
     *
     * @param   array   options     Array contains options
     *                              array(
     *                                  'text'  The string to draw
     *                                  'x'     Horizontal position
     *                                  'y'     Vertical Position
     *                                  'Color' Font color
     *                                  'font'  Font to be used
     *                                  'size'  Size of the fonts in pixel
     *                                  'resize_first'  Tell if the image has to be resized
     *                                                  before drawing the text
     *                              )
     *
     * @return TRUE or a PEAR error object on error
     * @see PEAR::isError()
     */
	function addText($params)
    {
		$params = array_merge($this->_get_default_text_params(), $params);
        extract($params);

        if (!is_array($color)) {
            if ($color{0} == '#') {
				// Not already in numberical format, so we convert it.
                $color = $this->colorhex2colorarray($color);
            } else {
                include_once 'Image/Transform/Driver/ColorsDefs.php';
                $color = isset($colornames[$color])?$colornames[$color]:false;
            }
        }

        $c = imagecolorresolve ($this->imageHandle, $color[0], $color[1], $color[2]);

        if ('ttf' == substr($font, -3)) {
			ImageTTFText($this->imageHandle, $size, $angle, $x, $y, $c, $font, $text);
        } else {
        	ImagePSText($this->imageHandle, $size, $angle, $x, $y, $c, $font, $text);
        }
        return true;
	} // End addText


    /**
     * Rotate image by the given angle
     * Uses a fast rotation algorythm for custom angles
     * or lines copy for multiple of 90 degrees
     *
     * @param int   $angle   Rotation angle
     * @param array $options array(
     *                             'color_mask' => array(r ,g, b), named color or #rrggbb
     *                            )
     * @author Pierre-Alain Joye
     * @return mixed TRUE or a PEAR error object on error
     * @see PEAR::isError()
     */
    function rotate($angle, $options = null)
    {
        if (($angle % 360) == 0) {
            return true;
        }
        
        $options = array_merge($this->_options, $options);
        $color_mask = $options['canvasColor'];

        if (!is_array($color_mask)) {
            // Not already in numberical format, so we convert it.
            if ($color_mask{0} == '#'){
                $color_mask = $this->colorhex2colorarray($color_mask);
            } else {
                include_once('Image/Transform/Driver/ColorsDefs.php');
                $color_mask = (isset($colornames[$color_mask])) ? $colornames[$color_mask] : false;
            }
        }

        $mask   = imagecolorresolve($this->imageHandle, $color_mask[0], $color_mask[1], $color_mask[2]);

        $this->old_image   = $this->imageHandle;

        // Multiply by -1 to change the sign, so the image is rotated clockwise
        $this->imageHandle = ImageRotate($this->imageHandle, $angle * -1, $mask);
        return true;
    }


    /**
     * Crop image by size and start coordinates
     *
     * @param int width Cropped image width
     * @param int height Cropped image height
     * @param int x X-coordinate to crop at
     * @param int y Y-coordinate to crop at
     *
     * @return mixed TRUE or a PEAR error object on error
     */
    function crop($width, $height, $x = 0, $y = 0)
    {
        if (function_exists('ImageCreateTrueColor')) {
            $new_img =ImageCreateTrueColor($new_x, $new_y);
        }
        if (!$new_img) {
            $new_img =ImageCreate($new_x, $new_y);
        }
        imagecopy($new_img, $this->imageHandle, 0, 0, $x, $y, $width, $height);

        $this->old_image = $this->imageHandle;
        $this->imageHandle = $new_img;
        $this->resized = true;

        $this->new_x = $width;
        $this->new_y = $height;
        return true;
    }


    /**
     * Convert the image to greyscale
     *
     * @return mixed TRUE or a PEAR error object on error
     */
    function greyscale() {
        imagecopymergegray($this->imageHandle, $this->imageHandle, 0, 0, 0, 0, $this->new_x, $this->new_y, 0);
        return true;
    }


   /**
    * Resize Action
    *
    * For GD 2.01+ the new copyresampled function is used
    * It uses a bicubic interpolation algorithm to get far
    * better result.
    *
    * @access private
    *
    * @param int   $new_x   New width
    * @param int   $new_y   New height
    * @param mixed $options Optional parameters
    *
    * @return TRUE on success or PEAR Error object on error
    * @see PEAR::isError()
    */
    function _resize($new_x, $new_y, $options = null)
    {
        $options = array_merge($this->_options, $options);

        if ($this->resized === true) {
            return PEAR::raiseError('You have already resized the image without saving it.  Your previous resizing will be overwritten', null, PEAR_ERROR_TRIGGER, E_USER_NOTICE);
        }
        
        if (function_exists('ImageCreateTrueColor')) {
            $new_img =ImageCreateTrueColor($new_x, $new_y);
        }
        if (!$new_img) {
            $new_img =ImageCreate($new_x, $new_y);
        }
        
        if ($options['scaleMethod'] != 'pixel' && function_exists('ImageCopyResampled')) {
            $icr_res = ImageCopyResampled($new_img, $this->imageHandle, 0, 0, 0, 0, $new_x, $new_y, $this->img_x, $this->img_y);
        } 
        if (!$icr_res) {
            ImageCopyResized($new_img, $this->imageHandle, 0, 0, 0, 0, $new_x, $new_y, $this->img_x, $this->img_y);
        }
        $this->old_image = $this->imageHandle;
        $this->imageHandle = $new_img;
        $this->resized = true;

        $this->new_x = $new_x;
        $this->new_y = $new_y;
        return true;
    }

    /**
     * Adjust the image gamma
     *
     * @param float $outputgamma
     *
     * @return mixed TRUE or a PEAR error object on error
     */
    function gamma($outputgamma = 1.0)
    {
        if ($outputgamma != 1.0) {
            ImageGammaCorrect($this->imageHandle, 1.0, $outputgamma);
        }
        return true;
    }

    /**
     * Save the image file
     *
     * @param string $filename the name of the file to write to
     * @param string $types    define the output format, default
     *                          is the current used format
     * @param int    $quality  output DPI, default is 75
     *
     * @return TRUE on success or PEAR error object on error
     */
    function save($filename, $type = '', $quality = null)
    {
        $type = ($type == '') ? $this->type : $type;
        if (!$this->supportsType($type, 'w')) {
            return PEAR::raiseError('Image type not supported for output', true);
        }

        $functionName   = 'image' . $type;
        $functionName($this->imageHandle, $filename, $quality) ;
        if (!$this->keep_settings_on_save) {
            $this->free();
        }
        return true;

    } // End save


    /**
     * Display image without saving and lose changes.
     *
     * This method adds the Content-type HTTP header
     *
     * @param string $type (JPG, PNG...);
     * @param int    $quality 75
     *
     * @return TRUE or PEAR Error object on error
     */
    function display($type = '', $quality = null)
    {
        $type    = ($type == '') ? $this->type : $type;
        $quality = (is_null($quality)) ? $this->_options['quality'] : $quality;
        if (!$this->supportsType($type, 'w')) {
            return PEAR::raiseError('Image type not supported for output', true);
        }
        $functionName = 'Image' . $type;
        header('Content-type: ' . $this->getMimeType($type));
        $functionName($this->imageHandle, '', $quality);
        $this->imageHandle = $this->old_image;
        if (!$this->keep_settings_on_save) {
            $this->free();
        }
        return true;
    }

    /**
     * Destroy image handle
     *
     * @return void
     */
    function free()
    {
        $this->resized = false;
        ImageDestroy($this->imageHandle);
        if ($this->old_image){
            ImageDestroy($this->old_image);
        }
    }

} // End class ImageGD

?>
