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

Class Image_Transform_Driver_Imagick extends Image_Transform
{
    /**
     * Handler of the imagick image ressource
     * @var array
     */
    var $imageHandle;


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
    function Image_Transform_Driver_Imagick()
    {
        if (!extension_loaded('imagick')) {
            if (!PEAR::loadExtension('imagick')) {
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
        $this->imageHandle = imagick_create();
        if ( !is_resource( $this->imageHandle ) ) {
            return PEAR::raiseError('Cannot initialize imagick image.', true);
        }

        if ( !imagick_read($this->imageHandle, $image) ){
            return PEAR::raiseError('The image file ' . $image . ' does\'t exist', true);
        }
        $this->image = $image;
        $this->_get_image_details($image);
    } // End load

    /**
     * Resize Action
     *
     * @param int   new_x   new width
     * @param int   new_y   new width
     *
     * @return none
     * @see PEAR::isError()
     */
    function _resize($new_x, $new_y)
    {
        if ($img2 = imagick_copy_resize ($this->imageHandle, $new_x, $new_y, IMAGICK_FILTER_CUBIC, 1)){
            $this->oldImage = $this->imageHandle;
            $this->imageHandle =$img2;
            $this->new_x = $new_x;
            $this->new_y = $new_y;
        } else {
            return PEAR::raiseError("Cannot create a new imagick imagick image for the resize.", true);
        }
    } // End resize

    /**
     * rotate
     * Note: color mask are currently not supported
     *
     * @param   int     Rotation angle in degree
     * @param   array   No option are actually allowed
     *
     * @return none
     * @see PEAR::isError()
     */
    function rotate($angle,$options=null)
    {
        if ($img2 = imagick_copy_rotate ($this->imageHandle, $angle)){
            $this->oldImage     = $this->imageHandle;
            $this->imageHandle  = $img2;
            $this->new_x = imagick_get_attribute($img2,'width');
            $this->new_y = imagick_get_attribute($img2,'height');
        } else {
            return PEAR::raiseError("Cannot create a new imagick imagick image for the resize.", true);
        }
    } // End rotate

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
        $default_params = array(
                                'text'          => 'This is a Text',
                                'x'             => 10,
                                'y'             => 20,
                                'size'          => 12,
                                'color'         => 'red',
                                'font'          => 'Arial.ttf',
                                'resize_first'  => false // Carry out the scaling of the image before annotation?
                                );
        $params = array_merge($default_params, $params);
        extract($params);

        $color = is_array($color)?$this->colorarray2colorhex($color):strtolower($color);

        imagick_annotate($this->imageHandle,array(
                    "primitive"     => "text $x,$y ".$text,
                    "pointsize"     => $size,
                    "antialias"     => 0,
                    "fill"          => $color,
                    "font"          => $font,
                    ));
    } // End addText

    /**
     * Save the image file
     *
     * @param $filename string the name of the file to write to
     *
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
            if (!imagick_dump ($this->imageHandle));
        } else {
            header('Content-type: image/' . $type);
            if (!imagick_dump ($this->imageHandle, $this->type));
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
        if(is_resource($this->imageHandle)){
            imagick_free($this->imageHandle);
        }
        if(is_resource($this->oldImage)){
            imagick_free($this->oldImage);
        }
        return true;
    }

} // End class ImageIM
?>