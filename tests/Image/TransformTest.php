<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Image_TransformTest::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

chdir(dirname(__FILE__) . '/../');
require_once 'Image/TransformTest/Driver/Cairowrapper.php';
require_once 'Image/TransformTest/Driver/GD.php';
require_once 'Image/TransformTest/Driver/IM.php';
require_once 'Image/TransformTest/Driver/Imagick3.php';
require_once 'Image/TransformTest/Driver/Imlib.php';
require_once 'Image/TransformTest/Driver/NetPBM.php';

/**
* Image_Transform driver tests.
*
* NOTE:
* When run uninstalled, this only works if you create two symlinks
*  in the Image_Transform root directory, pointing to itself:
* Image and Transform. This is to compensate the non-uniform directory layout
*  in CVS.
*
* @author Christian Weiske <cweiske@php.net>
*/
class Image_TransformTest
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }



    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Image_Transform Tests');
        /** Add testsuites, if there is. */
        $suite->addTestSuite('Image_TransformTest_Driver_Cairowrapper');
        $suite->addTestSuite('Image_TransformTest_Driver_GD');
        $suite->addTestSuite('Image_TransformTest_Driver_IM');
        $suite->addTestSuite('Image_TransformTest_Driver_Imagick3');
        $suite->addTestSuite('Image_TransformTest_Driver_Imlib');
        $suite->addTestSuite('Image_TransformTest_Driver_NetPBM');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Image_TransformTest::main') {
    Image_TransformTest::main();
}
?>