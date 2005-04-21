<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * GD implementation for Image_Transform package
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Image
 * @package    Image_Transform
 * @author     Alan Knowles <alan@akbkhome.com>
 * @author     Peter Bowyer <peter@mapledesign.co.uk>
 * @author     Philippe Jausions <Philippe.Jausions@11abacus.com>
 * @copyright  2002-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Image_Transform
 */

require_once "Image/Transform.php";

/**
 * GD implementation for Image_Transform package
 *
 * Usage :
 *    $img    =& Image_Transform::factory('GD');
 *    $angle  = -78;
 *    $img->load('magick.png');
 *
 *    if ($img->rotate($angle, array(
 *               'autoresize' => true,
 *               'color_mask' => array(255, 0, 0)))) {
 *        $img->addText(array(
 *               'text' => 'Rotation ' . $angle,
 *               'x' => 0,
 *               'y' => 100,
 *               'font' => '/usr/share/fonts/default/TrueType/cogb____.ttf'));
 *        $img->display();
 *    } else {
 *        echo "Error";
 *    }
 *    $img->free();
 *
 * @category   Image
 * @package    Image_Transform
 * @author     Alan Knowles <alan@akbkhome.com>
 * @author     Peter Bowyer <peter@mapledesign.co.uk>
 * @author     Philippe Jausions <Philippe.Jausions@11abacus.com>
 * @copyright  2002-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Image_Transform
 * @since      PHP 4.0
 */
class Image_Transform_Driver_GD extends Image_Transform
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
     */
    function Image_Transform_Driver_GD()
    {
        $this->__construct();
    } // End function Image


    /**
     * Check settings
     *
     * @since PHP 5
     */
    function __construct()
    {
        if (!PEAR::loadExtension('gd')) {
            $this->isError(PEAR::raiseError("GD library is not available.",
                IMAGE_TRANSFORM_ERROR_UNSUPPORTED));
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
                $this->isError(PEAR::raiseError("No supported image types available", IMAGE_TRANSFORM_ERROR_UNSUPPORTED));
            }
        }

    } // End function Image


    /**
     * Loads an image from file
     *
     * @param string $image filename
     * @return bool|PEAR_Error TRUE or a PEAR_Error object on error
     * @access public
     */
    function load($image)
    {
        $this->image = $image;
        $result = $this->_get_image_details($image);
        if (PEAR::isError($result)) {
            return $result;
        }
        if (!$this->supportsType($this->type, 'r')) {
            return PEAR::raiseError('Image type not supported for input',
                IMAGE_TRANSFORM_ERROR_UNSUPPORTED);
        }

        $functionName = 'ImageCreateFrom' . $this->type;
        $this->imageHandle = $functionName($this->image);
        return true;

    } // End load


    /**
     * Adds a border of constant width around an image
     *
     * @param int $border_width Width of border to add
     * @author Peter Bowyer
     * @return bool TRUE
     * @access public
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
     * @param   array   $params     Array contains options
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
     * @return bool|PEAR_Error TRUE or a PEAR_Error object on error
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
     * Rotates image by the given angle
     *
     * Uses a fast rotation algorythm for custom angles
     * or lines copy for multiple of 90 degrees
     *
     * @param int   $angle   Rotation angle
     * @param array $options array(
     *                             'color_mask' => array(r ,g, b), named color or #rrggbb
     *                            )
     * @author Pierre-Alain Joye
     * @return bool|PEAR_Error TRUE or a PEAR_Error object on error
     * @access public
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
     * Crops image by size and start coordinates
     *
     * @param int width Cropped image width
     * @param int height Cropped image height
     * @param int x X-coordinate to crop at
     * @param int y Y-coordinate to crop at
     *
     * @return bool|PEAR_Error TRUE or a PEAR_Error object on error
     * @access public
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
     * Converts the image to greyscale
     *
     * @return bool|PEAR_Error TRUE or a PEAR_Error object on error
     * @access public
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
    * @param int   $new_x   New width
    * @param int   $new_y   New height
    * @param mixed $options Optional parameters
    *
    * @return bool|PEAR_Error TRUE on success or PEAR_Error object on error
    * @access private
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
     * Adjusts the image gamma
     *
     * @param float $outputgamma
     *
     * @return bool|PEAR_Error TRUE or a PEAR_Error object on error
     * @access public
     */
    function gamma($outputgamma = 1.0)
    {
        if ($outputgamma != 1.0) {
            ImageGammaCorrect($this->imageHandle, 1.0, $outputgamma);
        }
        return true;
    }

    /**
     * Saves the image to a file
     *
     * @param string $filename the name of the file to write to
     * @param string $types    define the output format, default
     *                          is the current used format
     * @param int    $quality  output DPI, default is 75
     *
     * @return bool|PEAR_Error TRUE on success or PEAR_Error object on error
     * @access public
     */
    function save($filename, $type = '', $quality = null)
    {
        $type = ($type == '') ? $this->type : $type;
        if (!$this->supportsType($type, 'w')) {
            return PEAR::raiseError('Image type not supported for output',
                IMAGE_TRANSFORM_ERROR_UNSUPPORTED);
        }

        $functionName   = 'image' . $type;
        $functionName($this->imageHandle, $filename, $quality) ;
        if (!$this->keep_settings_on_save) {
            $this->free();
        }
        return true;

    } // End save


    /**
     * Displays image without saving and lose changes.
     *
     * This method adds the Content-type HTTP header
     *
     * @param string $type (JPG, PNG...);
     * @param int    $quality 75
     *
     * @return bool|PEAR_Error TRUE or PEAR_Error object on error
     * @access public
     */
    function display($type = '', $quality = null)
    {
        $type    = ($type == '') ? $this->type : $type;
        $quality = (is_null($quality)) ? $this->_options['quality'] : $quality;
        if (!$this->supportsType($type, 'w')) {
            return PEAR::raiseError('Image type not supported for output',
                IMAGE_TRANSFORM_ERROR_UNSUPPORTED);
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
     * Destroys image handle
     *
     * @access public
     */
    function free()
    {
        $this->resized = false;
        ImageDestroy($this->imageHandle);
        if ($this->old_image){
            ImageDestroy($this->old_image);
        }
    }

}

?>