<?php
require_once 'Image/Transform/Base.php';

/**
 * Base class for image transform driver tests
 *
 * @author Christian Weiske <cweiske@php.net>
 */
class Image_Transform_Driver_IMTest extends Image_Transform_Base
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



    /**
     * Resize image from 4x4 to 2x2
     *
     * @return void
     */
    public function testResize()
    {
        $this->nMaxAverageDiff = 51;
        return parent::testResize();
    }//public function testResize()

}

?>