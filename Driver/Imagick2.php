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
// | Authors: Alan Knowles <alan@akbkhome.com>                      |
// |          Peter Bowyer <peter@mapledesign.co.uk>                      |
// +----------------------------------------------------------------------+
//
// $Id$
//
// Image Transformation interface using command line ImageMagick
// Use the latest cvs version of imagick PECL
//
// EXPERIMENTAL - please report bugs
//

require_once "Image/Transform.php";

Class Image_Transform_Driver_Imagick2 extends Image_Transform
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
    function Image_Transform_Driver_Imagick2()
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
        $this->imageHandle = imagick_readimage($image);
        if ( !is_resource( $this->imageHandle ) ) {
            return $this->raiseError('Cannot initialize imagick image.');
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
        
        
        if (imagick_resize ($this->imageHandle, $new_x, $new_y, IMAGICK_FILTER_UNKNOWN , 1)){
            $this->new_x = $new_x;
            $this->new_y = $new_y;
        } else {
            return $this->raiseError("Cannot create a new imagick imagick image for the resize.");
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
        if (imagick_rotate ($this->imageHandle, $angle)){
           
            $this->new_x = imagick_get_attribute($img2,'width');
            $this->new_y = imagick_get_attribute($img2,'height');
        } else {
            return $this->raiseError("Cannot create a new imagick imagick image for the resize.", true);
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
        static $default_params = array(
                                'text'          => 'This is a Text',
                                'x'             => 10,
                                'y'             => 20,
                                'size'          => 12,
                                'color'         => 'red',
                                'font'          => 'Helvetica',
                                'resize_first'  => false // Carry out the scaling of the image before annotation?
                                );
        $params = array_merge($default_params, $params);
        

        $params['color']= is_array($params['color'])?$this->colorarray2colorhex($params['color']):strtolower($params['color']);
        
        
        static $cmds = array(
            'setfillcolor' => 'color',
            'setfontsize'  => 'size',
            'setfontface'  => 'font'
        );
        imagick_begindraw($this->imageHandle ) ;
        
        foreach($cmds as $cmd=>$v) {
            if (!call_user_func('imagick_'.$cmd,$this->imageHandle,$parms[$v])) {
                return $this->raiseError("problem with adding Text::{$v} = {$parms[$v]}");
            }
        }
        if (!imagick_drawannotation($this->imageHandle,$params['x'],$params['y'],$params['text'])) {
            return $this->raiseError("problem with adding Text");
        }
         
        
         
    } // End addText

    /**
     * Save the image file
     *
     * @param $filename string the name of the file to write to
     *
     * @return none
     */
    function save($filename, $type='', $quality = 75)
    {
        // needs error handling!!
        if (strlen($type)) {
            imagick_convert($this->imageHandle,$type);
        }
        imagick_write($this->imageHandle,$filename);
        imagick_free($this->imageHandle);
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
        
        // not sure about the 'idea' behind this !! - image2blob!!!
        
        if ($type == '') {
            header('Content-type: image/' .imagick_getimagetype( $this->imageHandle));
            if (!imagick_image2blob($this->imageHandle));
        } else {
            header('Content-type: image/' . $type);
            imagick_convert($this->imageHandle,$type);
            if (!imagick_image2blob($this->imageHandle));
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
        $this->imageHandle = null;
        return true;
    }
    
    /**
     * RaiseError Method - shows imagick Raw errors.
     *
     * @param string message = prefixed message..
     * @return pear error
     */
    function raiseError($message) {
        return PEAR::raiseError("$message\n" .
            "Reason: ".  imagick_failedreason($this->imageHandle). "\n".
            "Description: ". imagick_faileddescription($this->imageHandle) ."\n");
    }
    
    
    

} // End class ImageIM
?>
