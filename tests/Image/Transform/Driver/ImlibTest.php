<?php
require_once 'Image/Transform/Base.php';

/**
 * Imagick3 driver test
 *
 * @author Christian Weiske <cweiske@php.net>
 */
class Image_Transform_Driver_ImlibTest extends Image_Transform_Base
{
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        parent::mainImpl(__CLASS__);
    }

}

?>