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
     * @return mixed true or  or a PEAR error object on error
     *
     * @see PEAR::isError()
     */
    function Image_Transform_GD()
    {
        if (!function_exists("ImageTypes"))
            return PEAR::raiseError("libgd not compiled into PHP", true);
        if (!ImageTypes())
            return PEAR::raiseError("No supported image types available", true);
        return;
    } // End function Image

    /**
     * Load image
     *
     * @param string filename
     *
     * @return mixed none or a PEAR error object on error
     * @see PEAR::isError()
     */
    function load($image)
    {
        $this->image = $image;
        $this->_get_image_details($image);
        $functionName = 'ImageCreateFrom' . $this->type;
        $this->imageHandle = $functionName($this->image);
    } // End load

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
     * @return none
     * @see PEAR::isError()
     */
	function addText($params)
    {
		$params = array_merge($this->_get_default_text_params(), $params);
        extract($params);

        if( !is_array($color) ){
            if ($color{0}=='#'){
				// Not already in numberical format, so we convert it.
                $color = $this->colorhex2colorarray( $color );
            } else {
                include_once('Image/Transform/Driver/ColorsDefs.php');
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
     * @param int       $angle      Rotation angle
     * @param array     $options    array(  'autoresize'=>true|false,
     *                                      'color_mask'=>array(r,g,b), named color or #rrggbb
     *                                   )
     * @author Pierre-Alain Joye
     * @return mixed none or a PEAR error object on error
     * @see PEAR::isError()
     */
    function rotate($angle, $options = null)
    {
        if ($options == null){
            $color_mask = array(255,255,0);
        } else {
            extract($options);
        }
        
        if(!is_array($color_mask)){
			// Not already in numberical format, so we convert it.
            if ($color_mask{0} == '#'){
                $color_mask = $this->colorhex2colorarray($color_mask);
            } else {
                include_once('Image/Transform/Driver/ColorsDefs.php');
                $color_mask = isset($colornames[$color_mask])?$colornames[$color_mask]:false;
            }
        }
        
        $mask   = imagecolorresolve($this->imageHandle, $color_mask[0], $color_mask[1], $color_mask[2]);

        // Multiply by -1 to change the sign, so the image is rotated clockwise
        $angle = $angle * -1;
        
        $this->old_image    = $this->imageHandle;
        $this->imageHandle = ImageRotate($this->imageHandle, $angle, $mask);
        return true;
    }


   /**
    * Resize Action
    *
    * For GD 2.01+ the new copyresampled function is used
    * It uses a bicubic interpolation algorithm to get far
    * better result.
    *
    * @param $new_x int  new width
    * @param $new_y int  new height
    *
    * @return true on success or pear error
    * @see PEAR::isError()
    */
    function _resize($new_x, $new_y) {
        if ($this->resized === true) {
            return PEAR::raiseError('You have already resized the image without saving it.  Your previous resizing will be overwritten', null, PEAR_ERROR_TRIGGER, E_USER_NOTICE);
        }
        if (function_exists('ImageCreateTrueColor') && @ImageCreateTrueColor()) {
            $new_img =ImageCreateTrueColor($new_x,$new_y);
        } else {
            $new_img =ImageCreate($new_x,$new_y);
        }
        if (function_exists('ImageCopyResampled') && @ImageCopyResampled()) {
            ImageCopyResampled($new_img, $this->imageHandle, 0, 0, 0, 0, $new_x, $new_y, $this->img_x, $this->img_y);
        } else {
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
     * @return none
     */
    function gamma($outputgamma=1.0) {
        ImageGammaCorrect($this->imageHandle, 1.0, $outputgamma);
    }

    /**
     * Save the image file
     *
     * @param $filename string  the name of the file to write to
     * @param $quality  int     output DPI, default is 75
     * @param $types    string  define the output format, default
     *                          is the current used format
     *
     * @return none
     */
    function save($filename, $type = '', $quality = 75)
    {
        $type           = $type==''? $this->type : $type;
        $functionName   = 'image' . $type;
        $functionName($this->imageHandle, $filename) ;
        if (!$this->keep_settings_on_save) {
            $this->free();
        }
    } // End save


    /**
     * Display image without saving and lose changes
     *
     * @param string type (JPG,PNG...);
     * @param int quality 75
     *
     * @return none
     */
    function display($type = '', $quality = 75)
    {
        if ($type != '') {
            $this->type = $type;
        }
        $functionName = 'Image' . $this->type;
        header('Content-type: image/' . strtolower($this->type));
        $functionName($this->imageHandle, '', $quality);
        $this->imageHandle = $this->old_image;
        if (!$this->keep_settings_on_save) {
            $this->free();
        }
    }

    /**
     * Destroy image handle
     *
     * @return none
     */
    function free()
    {
        $this->imageHandle = $this->old_image;
        $this->resized = false;
        ImageDestroy($this->old_image);
        if ($this->imageHandle){
            ImageDestroy($this->imageHandle);
        }
    }

} // End class ImageGD
?>
