<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
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
// $Id$
//
// Image Transformation interface using the GD library
//

require_once "Image_Transform/Image_Transform.php";

Class Image_Transform_GD extends Image_Transform
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
        $this->type = $this->_get_type();
        $functionName = 'ImageCreateFrom' . $this->type;
        $this->imageHandle = $functionName($this->image);
        $this->_get_size();
    } // End load

	
	function addText($params)
    {
		$default_params = array(
                                'text' => 'This is Text',
                                'x' => 10,
                                'y' => 20,
                                'color' => 'red',
                                'font' => 'Arial.ttf',
								'size' => '12',
								'angle' => 0;
                                'resize_first' => false // Carry out the scaling of the image before annotation?
                                );
		$params = array_merge($default_params, $params);
        extract($params);
        if ('ttf' == substr($font, -3)) {
			ImageTTFText($this->imageHandle, $size, $angle, $x, $y, $color, $font, $text);
        } else {
        	ImagePSText($this->imageHandle, $size, $angle, $x, $y, $color, $font, $text);
        }
	} // End addText
	
	
/*
    function resize($new_x, $new_y = '')
    {
        if ($new_y == '') {
            $new_y = ($new_x / $this->img_x) * $this->img_y;
        }
        $new_img = ImageCreateTrueColor($new_x,$new_y);
        ImageCopyResampled($new_img, $this->imageHandle, 0, 0, 0, 0, $new_x, $new_y, $this->img_x, $this->img_y);
        $this->old_image = $this->imageHandle;
        $this->new_x = $new_x;
        $this->new_y = $new_y;
        $this->imageHandle = $new_img;
    } // End resize
    
    function scale($size)
    {
         if ($this->img_x >= $this->img_y) {
             $new_x = $size;
            $new_y = ($new_x / $this->img_x) * $this->img_y;
         } else {
             $new_y = $size;
            $new_x = ($new_y / $this->img_y) * $this->img_x;
         }
         $this->_resize($new_x, $new_y);
    } // End scale
    
    
    */
    
    
   /**
    * Resize Action
    *
    * @return none
    * @see PEAR::isError()
    */
    function _resize($new_x, $new_y) {
        if ($this->resized === true) {
            PEAR::raiseError('You have already resized the image without saving it.  Your previous resizing will be overwritten', null, PEAR_ERROR_TRIGGER, E_USER_NOTICE);
        }
        $new_img = ImageCreateTrueColor($new_x,$new_y);
        ImageCopyResampled($new_img, $this->imageHandle, 0, 0, 0, 0, $new_x, $new_y, $this->img_x, $this->img_y);
        $this->old_image = $this->imageHandle;
        $this->imageHandle = $new_img;
        $this->resized = true;
    }
    
    /**
     * Save the image file
     * 
     * @param $filename string the name of the file to write to
     * @return none
     */
    function save($filename)
    {
        $functionName = 'Image' . $this->type;
        $functionName($this->imageHandle, $filename) ;
        $this->imageHandle = $this->old_image;
        $this->resized = false;
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
        $this->resized = false;
    }
    
    /**
     * Destroy image handle
     *
     * @return none
     */
    function destroy()
    {
        if ($this->imageHandle){
            ImageDestroy($this->imageHandle);
        }
    }
    
    /**
     * get the image type
     *
     * @return string (gif,jpeg,png)
     */
    function _get_type () { // Can I pass $image by reference?
        $data = GetImageSize($this->image);

        switch($data[2]){
            case '1':
                $type = 'gif';
                break;
            case '2':
                $type = 'jpeg';
                break;
            case '3':
                $type = 'png';
                break;
        }
        return $type;
    }

    /**
     * get the image size (into img_x and img_y)
     *
     * @return none
     */
    function _get_size()
    {
        $size = GetImageSize($this->image);
        $this->img_x = $size[0];
        $this->img_y = $size[1];
    } // End _get_size
    
} // End class ImageIM
?>