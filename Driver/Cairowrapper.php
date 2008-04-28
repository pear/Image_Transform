<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Cairo implementation for Image_Transform package
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Image
 * @package    Image_Transform
 * @subpackage Image_Transform_Driver_GD
 * @author     Christian Weiske <cweiske@php.net>
 * @copyright  2008 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Image_Transform
 */
require_once 'Image/Transform.php';

/**
 * Cairo implementation for Image_Transform package using pecl's cairo_wrapper
 * extension.
 *
 * @category   Image
 * @package    Image_Transform
 * @subpackage Image_Transform_Driver_Cairowrapper
 * @author     Christian Weiske <cweiske@php.net>
 * @copyright  2008 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Image_Transform
 */
class Image_Transform_Driver_Cairowrapper extends Image_Transform
{
    var $surface = null;

    /**
     * Supported image types
     * @var array
     * @access protected
     */
    var $_supported_image_types = array(
        'png' => 'rw'
    );

    /**
     * Check settings
     */
    function Image_Transform_Driver_Cairowrapper()
    {
        $this->__construct();
    }



    /**
     * Check settings
     *
     * @since PHP 5
     */
    function __construct()
    {
        if (!PEAR::loadExtension('cairo_wrapper')) {
            $this->isError(PEAR::raiseError("cairo_wrapper extension is not available.",
                IMAGE_TRANSFORM_ERROR_UNSUPPORTED));
        }
    }



    /**
     * Loads an image from file
     *
     * @param string $image filename
     *
     * @return bool|PEAR_Error TRUE or a PEAR_Error object on error
     *
     * @access public
     */
    function load($image)
    {
        $this->free();

        $this->image = $image;
        $result = $this->_get_image_details($image);
        if (PEAR::isError($result)) {
            return $result;
        }
        if (!$this->supportsType($this->type, 'r')) {
            return PEAR::raiseError('Image type not supported for input',
                IMAGE_TRANSFORM_ERROR_UNSUPPORTED);
        }

        $this->surface = cairo_image_surface_create_from_png($this->image);
        if (cairo_surface_status($this->surface) != CAIRO_STATUS_SUCCESS) {
            $this->surface = null;
            return PEAR::raiseError('Error while loading image file.',
                IMAGE_TRANSFORM_ERROR_IO);
        }

        return true;
    }//function load(..)



    /**
     * Resize the image
     *
     * @param int   $new_x   New width
     * @param int   $new_y   New height
     * @param array $options Optional parameters
     *
     * @return bool|PEAR_Error TRUE on success or PEAR_Error object on error
     *
     * @access protected
     */
    function _resize($new_x, $new_y, $options = null)
    {
        if ($this->resized === true) {
            return PEAR::raiseError(
                'You have already resized the image without saving it.'
                . ' Your previous resizing will be overwritten',
                null, PEAR_ERROR_TRIGGER, E_USER_NOTICE
            );
        }

        if ($this->new_x == $new_x && $this->new_y == $new_y) {
            return true;
        }

        $xFactor = $new_x / $this->img_x;
        $yFactor = $new_y / $this->img_y;

        $outputSurface = cairo_image_surface_create(
            CAIRO_FORMAT_ARGB32, $new_x, $new_y
        );
        $outputContext = cairo_create($outputSurface);

        cairo_scale($outputContext, $xFactor, $yFactor);

        $inputContext = cairo_create($this->surface);
        cairo_set_source_surface($outputContext, $this->surface, 0, 0);
        cairo_paint($outputContext);

        cairo_destroy($outputContext);
        cairo_destroy($inputContext);

        cairo_surface_destroy($this->surface);

        $this->surface = $outputSurface;

        $this->new_x = $new_x;
        $this->new_y = $new_y;
        return true;
    }//function _resize(..)



    function save($filename, $type = null, $quality = null)
    {
        cairo_surface_write_to_png($this->surface, $filename);
        $this->free();
    }//function save(..)



    /**
     * Returns the surface of the image so it can be modified further
     *
     * @return resource
     *
     * @access public
     */
    function getHandle()
    {
        return $this->surface;
    }//function getHandle()



    /**
     * Frees cairo handles
     *
     * @return void
     *
     * @access public
     */
    function free()
    {
        $this->resized = false;
        if (is_resource($this->surface)) {
            cairo_surface_destroy($this->surface);
        }
        $this->surface = null;
    }//function free()

}//class Image_Transform_Driver_Cairowrapper extends Image_Transform
?>