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
// +----------------------------------------------------------------------+
//
// $Id$
//
// Image Transformation interface using command line ImageMagick
// Use the latest cvs version of imagick PECL
//

require_once "Image/Transform.php";

Class Image_Transform_PhpIM extends Image_Transform
{
    /**
     * Handler of the imagick image ressource
     * @var array
     */
    var $ImageHandle;


    /**
     * Handler of the image ressource before
     * the last transformation
     * @var array
     */
    var $oldImage;

    /**
     *
     *
     */
    function Image_Transform_IM()
    {
        if (!extension_loaded('gd')) {
            if (!dl('imagick.so')) {
                return PEAR::raiseError('The imagick extension can not be found.', true);
            }
        }
        return true;
    } // End Image_IM

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
        if (!$this->imageHandle = imagick_create()) {
            return PEAR::raiseError('Cannot initialize imagick image.', true);
        }

        if ( !imagick_read($this->ImageHandle, $image) ){
            return PEAR::raiseError('The image file ' . $image . ' does\'t exist', true);
        }
        $this->image = $image;
        $this->_get_image_details($image);
    } // End load

    /**
     * Resize Action
     *
     * @return none
     * @see PEAR::isError()
     */
    function _resize($new_x, $new_y)
    {
        if ($img2 = imagick_copy_resize ($this->ImageHandle, $new_x, $new_y, IMAGICK_FILTER_CUBIC, 1)){
            $this->oldImage = $this->ImageHandle;
            $this->ImageHandle =$img2;
            $this->new_x = $new_x;
            $this->new_y = $new_y;
        } else {
            return PEAR::raiseError("Cannot create a new imagick imagick image for the resize.", true);
        }
    } // End resize

    /**
     * rotate
     *
     */
    function rotate($angle)
    {
        if ($img2 = imagick_copy_rotate ($this->ImageHandle, $angle)){
            $this->oldImage     = $this->ImageHandle;
            $this->ImageHandle  = $img2;
            $this->new_x = imagick_get_attribute($img2,'width');
            $this->new_y = imagick_get_attribute($img2,'height');
        } else {
            return PEAR::raiseError("Cannot create a new imagick imagick image for the resize.", true);
        }
    } // End rotate

    /**
     * addText
     *
     */
    function addText($params)
    {
        $default_params = array(
                                'text'          => 'This is Text',
                                'x'             => 10,
                                'y'             => 20,
                                'size'          => 12,
                                'color'         => 'red',
                                'font'          => 'Arial.ttf',
                                'resize_first'  => false // Carry out the scaling of the image before annotation?
                                );
        $params = array_merge($default_params, $params);
        extract($params);

        $color = strtolower($color);

        imagick_annotate($this->imageHandle,array(
                    "primitive"     => "text $x,$y ".$text,
                    "pointsize"     => $size,
                    "antialias"     => 0,
                    "fill"          => eregi('[a-zA-Z]',$color)?,$color:"#"$color;
                    "font"          => $font,
                    ));
    } // End addText

    /**
     * Save the image file
     *
     * @param $filename string the name of the file to write to
     * @return none
     */
    function save($filename, $quality = 75, $type = '')
    {
        if ($type == '') {
            $type = strtoupper($type);
            imagick_write($this->imageHandle,$filename,$type);
        } else {
            imagick_write($this->imageHandle,$filename);
        }
        imagick_free($handle);
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
        if ($type == '') {
            header('Content-type: image/' . $this->type);
            if (!imagick_dump ($this->imageHandle);
        } else {
            header('Content-type: image/' . $type);
            if (!imagick_dump ($this->imageHandle, $this->type);
        }
        $this->free();
    }


    /**
     * Destroy image handle
     *
     * @return none
     */
    function free()
    {
        if(is_ressource($this->imageHandle)){
            imagick_free($this->imageHandle);
        }
        if(is_ressource($this->oldImage)){
            imagick_free($this->oldImage);
        }
        return true;
    }

} // End class ImageIM
?>