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
// | Authors: Alan Knowles <alan@akbkhome.com>                            |
// |          Peter Bowyer <peter@mapledesign.co.uk>                      |
// |          Philippe Jausions <Philippe.Jausions@11abacus.com>          |
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
     * @return mixed TRUE or a PEAR error object on error
     * @see http://www.imagemagick.org/www/formats.html
     */
    function Image_Transform_Driver_Imagick2()
    {
        $this->__construct();
    } // End Image_Transform_Driver_Imagick2

    /**
     * @return mixed TRUE or a PEAR error object on error
     * @see http://www.imagemagick.org/www/formats.html
     */
    function __construct()
    {
        if (PEAR::loadExtension('imagick')) {
            include('Image/Transform/Driver/Imagick/ImageTypes.php');
        } else {
            $this->isError(PEAR::raiseError('Couldn\'t find the imagick extension.', true));
        }
    }

    /**
     * Load image
     *
     * @access public
     * @param string $image filename
     *
     * @return mixed TRUE or a PEAR error object on error
     * @see PEAR::isError()
     */
    function load($image)
    {
        $this->imageHandle = imagick_readimage($image);
        if (imagick_iserror($this->imageHandle)) {
            return $this->raiseError('Couldn\'t load image.');
        }

        $this->image = $image;
        $result = $this->_get_image_details($image);
        if (PEAR::isError($result)) {
            return $result;
        }
        
        return true;
    } // End load

    /**
     * Resize Action
     *
     * @access private
     *
     * @param int   $new_x   New width
     * @param int   $new_y   New height
     * @param mixed $options Optional parameters
     *
     * @return TRUE or PEAR error object on error
     * @see PEAR::isError()
     */
    function _resize($new_x, $new_y, $options = null)
    {
        if (!imagick_resize($this->imageHandle, $new_x, $new_y, IMAGICK_FILTER_UNKNOWN , 1)) {
            return $this->raiseError('Couldn\'t resize image.');
        }

        $this->new_x = $new_x;
        $this->new_y = $new_y;
        return true;

    } // End resize

    /**
     * rotate
     * Note: color mask are currently not supported
     *
     * @access public
     * @param   int     Rotation angle in degree
     * @param   array   No option are actually allowed
     *
     * @return TRUE or a PEAR error object on error
     * @see PEAR::isError()
     */
    function rotate($angle, $options = null)
    {
        if (($angle % 360) == 0) {
            return true;
        }
        if (!imagick_rotate($this->imageHandle, $angle)) {
            return $this->raiseError('Cannot create a new imagick image for the rotation.', true);
        }

        $this->new_x = imagick_getwidth($this->imageHandle);
        $this->new_y = imagick_getheight($this->imageHandle);
        return true;

    } // End rotate

    /**
     * addText
     *
     * @access public
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
     * @return TRUE on success or PEAR error object on error
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
        
        foreach ($cmds as $cmd => $v) {
            if (!call_user_func('imagick_' . $cmd, $this->imageHandle, $parms[$v])) {
                return $this->raiseError("Problem with adding Text::{$v} = {$parms[$v]}");
            }
        }
        if (!imagick_drawannotation($this->imageHandle, $params['x'], $params['y'], $params['text'])) {
            return $this->raiseError("Problem with adding Text");
        }

        return true;
         
    } // End addText


    /**
     * Save the image to a file
     *
     * @access public
     * @param $filename string the name of the file to write to
     * @return TRUE on success, PEAR Error object on error
     */
    function save($filename, $type = '', $quality = null)
    {
        if ($type
            && !imagick_convert($this->imageHandle, $type)) {
            return $this->raiseError('Couldn\'t save image to file (conversion failed).');
        }
        $quality = (is_null($quality)) ? $this->_options['quality'] : $quality;
        imagick_setcompressionquality($this->imageHandle, $quality);
        if (!imagick_write($this->imageHandle, $filename)) {
            return $this->raiseError('Couldn\'t save image to file.');
        }
        imagick_free($this->imageHandle);

        return true;

    } // End save

    /**
     * Display image without saving and lose changes
     *
     * This method adds the Content-type HTTP header
     *
     * @access public
     * @param string type (JPG,PNG...);
     * @param int quality 75
     *
     * @return mixed TRUE or a PEAR Error object on error
     */
    function display($type = '', $quality = null)
    {        
        $quality = (is_null($quality)) ? $this->_options['quality'] : $quality;
        imagick_setcompressionquality($this->imageHandle, $quality);

        if ($type != '' && $type != $this->type) {
            imagick_convert($this->imageHandle, $type);
        }
        if (!($image = imagick_image2blob($this->imageHandle))) {
            return $this->raiseError('Couldn\'t display image.');
        }
        header('Content-type: ' . imagick_getmimetype($this->imageHandle));
        echo $image;
        $this->free();
        return true;
    }

    /**
     * Adjust the image gamma
     *
     * @access public
     * @param float $outputgamma
     * @return mixed TRUE or a PEAR error object on error
     */
    function gamma($outputgamma = 1.0) {
        if ($outputgamma != 1.0) {
            imagick_gamma($this->imageHandle, $outputgamma);
        }
        return true;
    }

    
    /**
     * Crop image
     *
     * @access public
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
        if (!imagick_crop($this->imageHandle, $x, $y, $x + $width, $y + $height)) {
            return $this->raiseError('Couldn\'t crop image.');
        }

        // I think that setting img_x/y is wrong, but scaleByLength() & friends
        // mess up the aspect after a crop otherwise.
        $this->new_x = $width;
        $this->new_y = $height;

        return true;
    }

    /**
     * Horizontal mirroring
     *
     * @access public
     * @return TRUE on success, PEAR Error object on error
     */
    function mirror() 
    {
        if (!imagick_flop($this->imageHandle)) {
            return $this->raiseError('Couldn\'t mirror the image.');
        }
        return true;
    }

    /**
     * Vertical mirroring
     *
     * @access public
     * @return TRUE on success, PEAR Error object on error
     */
    function flip() 
    {
        if (!imagick_flip($this->imageHandle)) {
            return $this->raiseError('Couldn\'t flip the image.');
        }
        return true;
    }

    /**
     * Destroy image handle
     *
     * @access public
     * @return void
     */
    function free()
    {
        imagick_destroyhandle($this->imageHandle);
        $this->imageHandle = null;
    }
    
    /**
     * RaiseError Method - shows imagick Raw errors.
     *
     * @access protected
     * @param string message = prefixed message..
     * @return PEAR error object
     */
    function raiseError($message) 
    {
        return PEAR::raiseError("$message\n" .
            "Reason: ".  imagick_failedreason($this->imageHandle). "\n".
            "Description: ". imagick_faileddescription($this->imageHandle) ."\n");
    }
    
} // End class Image_Transform_Driver_Imagick2

?>