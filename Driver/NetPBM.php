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
// Image Transformation interface using command line NetPBM
//

require_once "Image/Transform.php";

Class Image_Transform_Driver_NetPBM extends Image_Transform
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
    function Image_Transform_Driver_NetPBM()
    {
        if (!defined('IMAGE_TRANSFORM_LIB_PATH')) {
            include_once 'System/Command.php';
            $path = str_replace('pnmscale','',escapeshellcmd(System_Command::which('pnmscale') ));
            define('IMAGE_TRANSFORM_LIB_PATH', $path);
        }
        return true;
    } // End function Image_NetPBM

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
        $this->command[] = IMAGE_TRANSFORM_LIB_PATH . "pnmscale -width $new_x -height $new_y";

        $this->_set_new_x($new_x);
        $this->_set_new_y($new_y);
    } // End resize

    /**
     * rotate
     *
     * @since
     * @param int $angle The angle to rotate the image through
     */
    function rotate($angle)
    {
        if ('-' == $angle{0}) {
    		$angle = 360 - substr($angle, 1);
    	}
        $this->command[] = IMAGE_TRANSFORM_LIB_PATH . "pnmrotate $angle";
    } // End rotate

    /**
     * addText
     *
     * TODO: Fix the position of the text
     *       Actually, ppmlabel draw the string in hor. middle
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
                                'text' => 'This is Text',
                                'x' => 10,
                                'y' => 20,
                                'color' => 'red',
                                'font' => 'Arial.ttf',
								'size' => '12',
								'angle' => 0,
                                'resize_first' => false // Carry out the scaling of the image before annotation?
                                );
         $params = array_merge($default_params, $params);
         extract($params);
         $this->command[] = 'ppmlabel -angle '.$angle.' -colour '.$color.' -size '.$size.' -text "'.$text.'" -x '.$x.' -y '.$y;
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
            $type = $this->type;
        }
        $type == '' ? $this->type : $type;
        $cmd = IMAGE_TRANSFORM_LIB_PATH . $this->type . 'topnm ' . $this->image  . '|' . implode('|', $this->command) . '|';
        $arg = "";
        switch($type){
        	case 'gif':
        		$cmd .= IMAGE_TRANSFORM_LIB_PATH . "ppmquant 256|".
                        IMAGE_TRANSFORM_LIB_PATH . 'ppmtogif >' .  $filename;
        		break;
        	case 'jpeg':
        		$arg = "--quality=$quality";
            default:
                $cmd .= IMAGE_TRANSFORM_LIB_PATH . 'pnmto' . $type .
                        ' ' . $arg . '>' . $filename;
                break;
        } // switch
        exec($cmd);
        $this->command = array();
    } // End save

    /**
     * Display image without saving and lose changes
     *
     * @param string $type (JPG,PNG...);
     * @param int $quality 75
     *
     * @return none
     */
    function display($type = '', $quality = 75)
    {
        if ($type == '') {
            $type = $this->type;
        }
        header('Content-type: image/' . $type);
        $cmd = IMAGE_TRANSFORM_LIB_PATH . $this->type . 'topnm ' . $this->image  . '|' . implode('|', $this->command) . '|';
        $arg = '';
        switch($type){
        	case 'jpeg':
        		$arg = "--quality=$quality";
        		break;
        	case 'gif':
        		$cmd .=  IMAGE_TRANSFORM_LIB_PATH . "ppmquant 256|";
        		break;
        	default:
                break;
        } // switch
        $cmd .= IMAGE_TRANSFORM_LIB_PATH . 'pnmto' . $type . ' ' . $arg;
        //echo $cmd ."\n";
        passthru($cmd);
        $this->command = array();
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
