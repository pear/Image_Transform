<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Cairo implementation for Image_Transform package
 *
 * PHP version 5
 *
 * @category   Image
 * @package    Image_Transform
 * @subpackage Image_Transform_Driver_Cairowrapper
 * @author     Christian Weiske <cweiske@php.net>
 * @copyright  2008 The PHP Group
 * @license    http://www.gnu.org/copyleft/lesser.html LGPL
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Image_Transform
 */
require_once 'Image/Transform.php';
require_once 'Image/Transform/Exception.php';

/**
 * Cairo implementation for Image_Transform package using pecl's cairo_wrapper
 * extension.
 *
 * Supports png files only.
 *
 * @category   Image
 * @package    Image_Transform
 * @subpackage Image_Transform_Driver_Cairowrapper
 * @author     Christian Weiske <cweiske@php.net>
 * @copyright  2008 The PHP Group
 * @license    http://www.gnu.org/copyleft/lesser.html LGPL
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Image_Transform
 */
class Image_Transform_Driver_Cairowrapper extends Image_Transform
{
    var $surface = null;

    /**
     * Supported image types
     *
     * @var    array
     * @access protected
     */
    var $_supported_image_types = array(
        'png' => 'rw'
    );

    /**
     * Create object and check if cairo_wrapper is loaded
     */
    function __construct()
    {
        if (!PEAR::loadExtension('cairo_wrapper')) {
            throw new Image_Transform_Exception(
                'cairo_wrapper extension is not available.',
                IMAGE_TRANSFORM_ERROR_UNSUPPORTED
            );
        }
    }



    /**
     * Loads an image from file
     *
     * @param string $image filename
     *
     * @return bool
     * @throws Image_Transform_Exception
     * @access public
     */
    function load($image)
    {
        $this->free();

        $this->image = $image;
        $result = $this->_get_image_details($image);

        if (!$this->supportsType($this->type, 'r')) {
            throw new Image_Transform_Exception('Image type not supported for input',
                IMAGE_TRANSFORM_ERROR_UNSUPPORTED);
        }

        $this->surface = cairo_image_surface_create_from_png($this->image);
        if (cairo_surface_status($this->surface) != CAIRO_STATUS_SUCCESS) {
            $this->surface = null;
            throw new Image_Transform_Exception('Error while loading image file.',
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
     * @return bool
     * @throws Image_Transform_Exception
     * @access protected
     */
    function _resize($new_x, $new_y, $options = null)
    {
        if ($this->resized === true) {
            throw new Image_Transform_Exception(
                'You have already resized the image without saving it.'
                . ' Your previous resizing will be overwritten'
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

        cairo_set_source_surface($outputContext, $this->surface, 0, 0);
        cairo_paint($outputContext);

        cairo_destroy($outputContext);

        cairo_surface_destroy($this->surface);

        $this->surface = $outputSurface;

        $this->new_x = $new_x;
        $this->new_y = $new_y;
        return true;
    }//function _resize(..)



    /**
     * Saves the scaled image into a file.
     *
     * @param string $filename The filename to save to
     * @param mixed  $type     ignored
     * @param mixed  $quality  ignored
     *
     * @return bool
     *
     * @access public
     */
    function save($filename, $type = null, $quality = null)
    {
        cairo_surface_write_to_png($this->surface, $filename);
        $this->free();
        return true;
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



    /**
     * Mirrors the image vertically
     * Uses an affine transformation matrix to flip the image.
     *
     * @return void
     */
    function flip()
    {
        $outputSurface = cairo_image_surface_create(
            CAIRO_FORMAT_ARGB32, $this->img_x, $this->img_y
        );
        $outputContext = cairo_create($outputSurface);
        //                            xx, yx, xy, yy, x0, y0
        $matrix = cairo_matrix_create(1,  0,  0,  -1,  0, $this->img_y);

        cairo_set_matrix($outputContext, $matrix);
        cairo_set_source_surface($outputContext, $this->surface, 0, 0);
        cairo_paint($outputContext);

        cairo_destroy($outputContext);
        cairo_surface_destroy($this->surface);

        $this->surface = $outputSurface;
    }//function flip()



    /**
     * Mirrors the image horizontally.
     * Uses an affine transformation matrix to mirror the image.
     *
     * 123 -> 321
     *
     * @return void
     */
    function mirror()
    {
        $outputSurface = cairo_image_surface_create(
            CAIRO_FORMAT_ARGB32, $this->img_x, $this->img_y
        );
        $outputContext = cairo_create($outputSurface);
        //                            xx, yx, xy, yy, x0, y0
        $matrix = cairo_matrix_create(-1, 0,  0,  1, $this->img_x, 0);

        cairo_set_matrix($outputContext, $matrix);
        cairo_set_source_surface($outputContext, $this->surface, 0, 0);
        cairo_paint($outputContext);

        cairo_destroy($outputContext);
        cairo_surface_destroy($this->surface);

        $this->surface = $outputSurface;
    }//function mirror()

}//class Image_Transform_Driver_Cairowrapper extends Image_Transform
?>