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
//

require_once "Image/Transform.php";

Class Image_Transform_Drivers_IM extends Image_Transform
{
    /**
     * associative array commands to be executed
     * @var array
     */
    var $command = array();

    /**
     *
     *
     */
    function Image_Transform_IM()
    {
        include_once 'System/Command.php';
        if (!defined('IMAGE_TRANSFORM_LIB_PATH')) {
            define('IMAGE_TRANSFORM_LIB_PATH', escapeshellcmd(System_Command::which('convert')) . '/');
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
        if (!file_exists($image)) {
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
        if (isset($this->command['resize'])) {
            return PEAR::raiseError("You cannot scale or resize an image more than once without calling save or display", true);
        }
        $this->command['resize'] = "-geometry ${new_x}x${new_y}!";

        $this->new_x = $new_x;
        $this->new_y = $new_y;
    } // End resize

    /**
     * rotate
     *
     */
    function rotate($angle)
    {
        if ('-' == $angle{0}) {
    		$angle = 360 - substr($angle, 1);
    	}
        // convert -mattecolor red -frame 20x20+12+8 magick.png magick2.png
         $this->command['rotate'] = "-rotate $angle";
    } // End rotate

    /**
     * addText
     *
     */
    function addText($params)
    {
        $default_params = array(
                                'text' => 'This is Text',
                                'x' => 10,
                                'y' => 20,
                                'color' => 'red',
                                'font' => 'Arial.ttf',
                                'resize_first' => false // Carry out the scaling of the image before annotation?
                                );
         $params = array_merge($default_params, $params);
         extract($params);
         if (true === $resize_first) {
             // Set the key so that this will be the last item in the array
            $key = 'ztext';
         } else {
            $key = 'text';
         }
         $this->command[$key] = "-font $font -fill $color -draw 'text $x,$y \"$text\"'";
         // Producing error: gs: not found gs: not found convert: Postscript delegate failed [No such file or directory].
    } // End addText

    /**
     * Save the image file
     *
     * @param $filename string the name of the file to write to
     * @return none
     */
    function save($filename, $quality = 75, $type = '')
    {
        $type == '' ? $this->type : $type;
        $cmd = 'ulimit;' . IMAGE_TRANSFORM_LIB_PATH . 'convert ' . implode(' ', $this->command) . " -quality $quality "  . escapeshellarg($this->image) . ' ' . escapeshellarg($filename) . ' 2>&1';
        passthru($cmd);
		#print $cmd;
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
            passthru(IMAGE_TRANSFORM_LIB_PATH . 'convert ' . implode(' ', $this->command) . " -quality $quality "  . escapeshellarg($this->image) . ' ' . strtoupper($this->type) . ":-");
        } else {
            header('Content-type: image/' . $type);
            passthru(IMAGE_TRANSFORM_LIB_PATH . 'convert ' . implode(' ', $this->command) . " -quality $quality "  . escapeshellarg($this->image) . ' ' . strtoupper($type) . ":-");
        }
    }


    /**
     * Destroy image handle
     *
     * @return none
     */
    function free()
    {
        return true;
    }

} // End class ImageIM
?>