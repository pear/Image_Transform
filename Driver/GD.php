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

require_once "Image/Image_Transform.php";

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
        $this->_get_image_details($image);
        $functionName = 'ImageCreateFrom' . $this->type;
        $this->imageHandle = $functionName($this->image);

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
    
    function rotate($angle)
    {
        if ('-' == $angle{0}) {
    		$angle = 360 - substr($angle, 1);
    	}
        $size = GetImageSize($img_sorgente);
        $tot_x = $this->img_x;
        $tot_y = $this->img_y;
        
        $img_risulta = ImageCreate ($tot_y,$tot_x)
        
        $img_sorgente=ImageCreateFromJpeg($img_sorgente);
        
        for($i_x=0;$i_x<$tot_x;$i_x++){
            for($i_y=0;$i_y<$tot_y;$i_y++){ 
                $ris_x=$tot_y-($i_y+1);
                $ris_y=$i_x;
                imagecopy($img_risulta, $img_sorgente, $ris_x,$ris_y,$i_x,$i_y,1,1);
            } // Y
        } // X

    }
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
        
        $this->new_x = $new_x;
        $this->new_y = $new_y;   
    }
    
    /**
     * Save the image file
     * 
     * @param $filename string the name of the file to write to
     * @return none
     */
    function save($filename, $quality = 75, $type = '')
    {
        $type == '' ? $this->type : $type;
        $functionName = 'Image' . $type;
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
    function free()
    {
        if ($this->imageHandle){
            ImageDestroy($this->imageHandle);
        }
    }
    
} // End class ImageIM
?>