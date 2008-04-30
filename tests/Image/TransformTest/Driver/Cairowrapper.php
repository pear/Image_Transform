<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Image_TransformTest_Driver_Cairowrapper::main');
}

chdir(dirname(__FILE__) . '/../../../');
require_once 'Image/TransformTest/Base.php';

/**
 * Test for Cairowrapper driver
 *
 * @author Christian Weiske <cweiske@php.net>
 */
class Image_TransformTest_Driver_Cairowrapper extends Image_TransformTest_Base
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

if (PHPUnit_MAIN_METHOD == 'Image_TransformTest_Driver_Cairowrapper::main') {
    Image_TransformTest_Driver_Cairowrapper::main();
}
?>