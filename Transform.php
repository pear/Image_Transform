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
// |          Vincent Oostindie <vincent@sunlight.tmfweb.nl>              |
// +----------------------------------------------------------------------+
//
// $Id$
//
// Image Transformation interface
//

require_once 'PEAR.php';

/*
if(!defined(IMAGE_TRANSFORM_LIB_PATH)){
	define('IMAGE_TRANSFORM_LIB_PATH', '/usr/local/ImageMagick/bin/');
}*/
/**
 * The main "Image_Resize" class is a container and base class which
 * provides the static methods for creating Image objects as well as 
 * some utility functions (maths) common to all parts of Image Resize.
 *
 * The object model of DB is as follows (indentation means inheritance):
 *
 * Image_Resize The base for each Image implementation.  Provides default
 * |            implementations (in OO lingo virtual methods) for
 * |            the actual Image implementations as well as a bunch of
 * |            maths methods.
 * |
 * +-Image_GD   The Image implementation for the PHP GD extension .  Inherits 
 *              Image_Resize
 *              When calling DB::setup for GD images the object returned is an 
 *              instance of this class.
 *
 * @package  Image Resize
 * @version  1.00
 * @author   Peter Bowyer <peter@mapledesign.co.uk>
 * @since    PHP 4.0
 */
Class Image_Transform
{
    /**
     * Name of the image file
     * @var string
     */
    var $image = '';
    /**
     * Type of the image file (eg. jpg, gif png ...)
     * @var string
     */    
    var $type = '';
    /**
     * Original image width in x direction
     * @var int 
     */
    var $img_x = '';
    /**
     * Original image width in y direction
     * @var int 
     */
    var $img_y = '';
    /**
     * New image width in x direction
     * @var int 
     */
    var $new_x = '';
    /**
     * New image width in y direction
     * @var int 
     */
    var $new_y = '';
    /**
     * Path the the library used
     * e.g. /usr/local/ImageMagick/bin/ or
     * /usr/local/netpbm/
     */
    var $lib_path = '';
    /**
     * Flag to warn if image has been resized more than once before displaying
     * or saving.
     */
     var $resized = false;
    
    /**
     * Create a new Image_resize object
     *
     * @param mixed $options An associative array of option names and
     * their values. See Image_Resize::setOption for more information on
     * setup options.
     *
     * @return mixed a newly created Image_Resize object, or a PEAR
     * error object on error
     *
     * @see PEAR::isError()
     * @see Image_Resize::setOption()
     */
    function &factory($driver)
    {
        include_once "Transform/$driver.php";

        $classname = "Image_Transform_{$driver}";
        $obj =& new $classname;
        return $obj;
    }
    
    
    /**
     * Resize the Image in the X and/or Y direction
     * If either is 0 it will be scaled proportionally
     *
     * @access public
     *
     * @param mixed $new_x (0, number, percentage 10% or 0.1) 
     * @param mixed $new_y (0, number, percentage 10% or 0.1)     
     *
     * @return mixed none or PEAR_error
     */
    function resize($new_x = 0, $new_y = 0)
    {
        // 0 means keep original size
        $new_x = (0 == $new_x) ? $this->img_x : $this->_parse_size($new_x, $this->img_x);
        $new_y = (0 == $new_y) ? $this->img_y : $this->_parse_size($new_y, $this->img_y);
        // Now do the library specific resizing.
        $this->_resize($new_x, $new_y);
    } // End resize
    
    
    /**
     * Scale the image to have the max x dimension specified.
     * 
     * @param int $new_x Size to scale X-dimension to
     * @return none
     */
    function scaleMaxX($new_x)
    {
        $new_y = round(($new_x / $this->img_x) * $this->img_y, 0);
        $this->_resize($new_x, $new_y);
    } // End resizeX
    
    /**
     * Scale the image to have the max y dimension specified.
     * 
     * @access public
     * @param int $new_y Size to scale Y-dimension to
     * @return none
     */
    function scaleMaxY($new_y)
    {
        $new_x = round(($new_y / $this->img_y) * $this->img_x, 0);
        $this->_resize($new_x, $new_y);
    } // End resizeY
    
    /**
     * Scale Image to a maximum or percentage
     *
     * @access public
     * @param mixed (number, percentage 10% or 0.1) 
     * @return mixed none or PEAR_error
     */
    function scale($size)
    {
        $strlen = strlen($size);
        if ($strlen > 0 && '%' == $size{-1}) {
            $this->scaleByPercentage(substr($size, 0, -1));
        } elseif ($size < 1) {
            $this->scaleByFactor($size);
        } else {
            $this->scaleByLength($size);
        }
    } // End scale

    /**
     * Scales an image to a percentage of its original size.  For example, if
     * my image was 640x480 and I called scaleByPercentage(10) then the image
     * would be resized to 64x48
     * 
     * @access public
     * @param int $size Percentage of original size to scale to
     * @return none
     */
    function scaleByPercentage($size)
    {
        $this->scaleByFactor($size / 100);
    } // End scaleByPercentage
    
    /**
     * Scales an image to a factor of its original size.  For example, if
     * my image was 640x480 and I called scaleByFactor(0.5) then the image
     * would be resized to 320x240.
     * 
     * @access public
     * @param float $size Factor of original size to scale to
     * @return none
     */
    function scaleByFactor($size)
    {
        $new_x = round($size * $this->img_x, 0);
        $new_y = round($size * $this->img_y, 0);
        $this->_resize($new_x, $new_y);
    } // End scaleByFactor
    
    /**
     * Scales an image so that the longest side has this dimension.
     *
     * @access public
     * @param int $size Max dimension in pixels
     * @return none
     */
    function scaleByLength($size)
    {
         if ($this->img_x >= $this->img_y) {
            $new_x = $size;
            $new_y = round(($new_x / $this->img_x) * $this->img_y, 0);
        } else {
            $new_y = $size;
            $new_x = round(($new_y / $this->img_y) * $this->img_x, 0);
        }
        $this->_resize($new_x, $new_y);
    } // End scaleByLength
    
    
    /**
     * Parse input and convert
     * If either is 0 it will be scaled proportionally
     *
     * @access private
     *
     * @param mixed $new_size (0, number, percentage 10% or 0.1) 
     * @param int $old_size     
     *
     * @return mixed none or PEAR_error
     */
    function _parse_size($new_size, $old_size) {
        if ('%' == $new_size) {
            $new_size = str_replace('%','',$new_size);
            $new_size = $new_size / 100;
        }
        if ($new_size > 1) {
            return (int) $new_size;
        } elseif ($new_size == 0) {
            return (int) $old_size;
        } else {
            return (int) round($new_size * $old_size, 0);
        }
    }

    /**
     * Set the image width
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return 
     */
    function _set_img_x($size){
    	$this->img_x = $size;
    }
    
    /**
     * Set the image height
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return 
     */
    function _set_img_y($size){
    	$this->img_y = $size;
    }
    
    /**
     * Set the image width
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return 
     */
    function _set_new_x($size){
    	$this->new_x = $size;
    }
    
    /**
     * Set the image height
     * @param int $size dimension to set
     * @since 29/05/02 13:36:31
     * @return 
     */
    function _set_new_y($size){
    	$this->new_y = $size;
    }
    /**
     *
     * @access public
     * @return string web-safe image type
     **/
    function getWebSafeFormat(){
    	switch($this->type){
    		case 'gif': 
    #        case 'png':
    			return 'gif';
    			break;
    		default:
    			return 'jpeg';
    	} // switch
    }
    
    /**
     * Place holder for the real resize method
     * used by extended methods to do the resizing
     *
     * @access private
     * @return PEAR_error
     */
    function _resize() {
        return PEAR::raiseError("No Resize method exists", true);
    }
    
    /**
     * Place holder for the real load method
     * used by extended methods to do the resizing
     *
     * @access public
     * @return PEAR_error
     */
    function load($filename) {
        return PEAR::raiseError("No Load method exists", true);
    }
    
    /**
     * Place holder for the real display method
     * used by extended methods to do the resizing
     *
     * @access public
     * @param string filename
     * @return PEAR_error
     */
    function display($type, $quality) {
        return PEAR::raiseError("No Display method exists", true);
    }
    
    /**
     * Place holder for the real save method
     * used by extended methods to do the resizing
     *
     * @access public
     * @param string filename
     * @return PEAR_error
     */
    function save($filename) {
        return PEAR::raiseError("No Save method exists", true);
    }

    /**
     * Place holder for the real free method
     * used by extended methods to do the resizing
     *
     * @access public
     * @return PEAR_error
     */
    function free() {
        return PEAR::raiseError("No Save method exists", true);
    }
        
    /* Methods to add to the driver classes in the future */
    function addText()
    {
        /* Should be nearly implemented now!) */
    }
    
    function addDropShadow()
    {
    
    }
    
    function addBorder()
    {
    
    }
    
    function crop()
    {
    
    }
}
?>