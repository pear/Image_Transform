<?php
// Call Image_TransformTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Image_TransformTest_Driver_NetPBM::main');
}

chdir(dirname(__FILE__) . '/../../../');
require_once 'Image/TransformTest/Base.php';

/**
 * Base class for image transform driver tests
 *
 * @author Christian Weiske <cweiske@php.net>
 */
class Image_TransformTest_Driver_NetPBM extends Image_TransformTest_Base
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

// Call Image_TransformTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Image_TransformTest_Driver_NetPBM::main') {
    Image_TransformTest_Driver_NetPBM::main();
}
?>